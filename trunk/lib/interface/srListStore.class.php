<?php
/**
 * @implements GtkTreeModel
 */
class srListeStore extends GtkListStore
{
  private static $model = null;

  /**
   *
   * @return srListeStore
   */
  public static function getInstance()
  {
    if(is_null(self::$model))
    {
      //@see http://gtk.php.net/manual/en/gtk.enum.type.php
      //Le cinquième élément ! (c'est un champ nom affiché utilisé pour conserver
      //le chemin complet du fichier)
      //Le sixième élément indique si la ligne est en erreur ou non
	    self::$model = new srListeStore(64, 64, 64, 64, 64, 64, 64);
    }
    return self::$model;
  }

  public function remplirFromChemin($chemin)
  {
    $fichiers = myFinder::type('file')->ignore_version_control()->maxdepth(0)->follow_link()->relative()->in($chemin);
    $compteur = 0;
    foreach($fichiers as $fichier)
    {
    	$this->remplirFromFilePath($chemin . DIRECTORY_SEPARATOR . $fichier);
      $compteur++;
    }
  }

  public function remplirFromFilePath($filePath)
  {
    $oFichierSerie = new fichierSerie(pathinfo($filePath, PATHINFO_BASENAME));
    $ligne = array(
      $oFichierSerie->getSerie(),
      $oFichierSerie->getSaison(),
      $oFichierSerie->getEpisode(),
      pathinfo($filePath, PATHINFO_BASENAME),
      '',
      $filePath,
      false //pas en erreur
    );
    $this->append($ligne);
  }

  public function tempNouveauNom($store, $path, $iter)
  {
    try
    {
	    $oFichierSerie = new fichierSerie($store->get_value($iter, 3));
	    $oInfosProviderSerieImdb = new infosProviderSerieImdb();
	    $nomSerie = correspondanceNoms::getInstance()->getNom('imdb', $store->get_value($iter, 0));
	    $saison = $store->get_value($iter, 1);
	    $episode = $store->get_value($iter, 2);
	    //TODO pattern by user !!
	    $nouveau = sprintf(
	      '%s - [%sx%s] - %s.%s',
	      $oInfosProviderSerieImdb->nettoyerNomSerie($nomSerie),
	      $saison,
	      str_pad($episode,2,'0',STR_PAD_LEFT),
	      $oInfosProviderSerieImdb->getEpisode($nomSerie, $saison, $episode),
	      $oFichierSerie->getExtension()
	    );
	    $nouveau = srUtils::nameForFileSystem($nouveau);
	    $this->set($iter, 4, $nouveau);
    }
    catch(SerieNonFoundException $ex)
    {
      $this->set($iter, 4, '');
    }
  }

  public function chercherNouveauxTitres()
  {
    $this->foreach(array('srListeStore', 'tempNouveauNom'));
  }

  public function renommer()
  {
    $this->foreach(array('srListeStore', 'renommerUnEpisode'));
    $this->clear();
  }

  public function renommerUnEpisode($store, $path, $iter)
  {
    $origin = $store->get_value($iter, 5) . DIRECTORY_SEPARATOR . $store->get_value($iter, 3);
    $new    = $store->get_value($iter, 5) . DIRECTORY_SEPARATOR . $store->get_value($iter, 4);
    if($origin != $new)
    {
	    if(!rename($origin, $new))
	    {
	      srLog::add(sprintf('Erreur lors du rennomage de "%s" en "%s"', $origin, $new));
	    }
    }
  }

}