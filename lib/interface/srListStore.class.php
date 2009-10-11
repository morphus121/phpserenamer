<?php
/**
 * @implements GtkTreeModel
 */
class srListeStore extends GtkListStore
{

  const STATUS_OK    = 'ok';
  const STATUS_ERROR = 'error';

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
      //Le sixième élément indique si la ligne est en erreur ou no
      self::$model = new srListeStore(64, 64, 64, 64, 64, 64, 64);
    }
    return self::$model;
  }

  public function remplirFromChemin($chemin)
  {
    $maxdepth = (srConfig::get('openRecursively')) ? 1000000 : 0;
    $fichiers = myFinder::type('file')->ignore_version_control()->maxdepth($maxdepth)->follow_link()->relative()->in($chemin);
    $compteur = 0;
    foreach($fichiers as $fichier)
    {
      $this->remplirFromFilePath($chemin . DIRECTORY_SEPARATOR . $fichier);
      $compteur++;
    }
  }

  public function remplirFromFilePath($filePath)
  {
    //this is used for windows OS, and the function_exists is to not to have to
    //install the php-xml extension.
    if(function_exists('utf8_encode') && myFilesystem::isOsWindows())
    {
      $filePath = utf8_encode($filePath);
    }
    $oFichierSerie = new fichierSerie(pathinfo($filePath, PATHINFO_BASENAME));
    $ligne = array(
    $oFichierSerie->getSerie(),
    $oFichierSerie->getSaison(),
    $oFichierSerie->getEpisode(),
    pathinfo($filePath, PATHINFO_BASENAME),
      '',
    pathinfo($filePath, PATHINFO_DIRNAME),
    false //pas en erreur
    );
    $this->append($ligne);
  }

  public function tempNouveauNom($store, $path, $iter)
  {
    try
    {
      $oFichierSerie       = new fichierSerie($store->get_value($iter, 3));
      $oInfosProviderSerie = infosProviderFactory::createInfosProvider('serie', srWindow::getInstance()->getSelectedProvider());
      $nomSerie            = correspondanceNoms::getInstance()->getNom(srWindow::getInstance()->getSelectedProvider(), $store->get_value($iter, 0));
      $saison              = $store->get_value($iter, 1);
      $episode             = $store->get_value($iter, 2);
      $userPattern         = srWindow::getUserPattern();

      $nouveau = str_replace(array(
	     '%n',
	     '%s',
	     '%j',
	     '%e',
	     '%k',
	     '%t'
	     ), array(
	     $oInfosProviderSerie->nettoyerNomSerie($nomSerie),
	     (string)$saison,
	     str_pad($saison, 2, '0', STR_PAD_LEFT),
	     (string)$episode,
	     str_pad($episode, 2, '0', STR_PAD_LEFT),
	     $oInfosProviderSerie->getEpisode($nomSerie, $saison, $episode)
	     ), $userPattern);

	     $nouveau = srUtils::nameForFileSystem(sprintf('%s.%s', $nouveau, $oFichierSerie->getExtension()));
	     if(srConfig::get('replaceSpaces'))
	     {
	       $nouveau = str_replace(' ', '.', $nouveau);
	     }
    }
    catch(SerieNonFoundException $ex)
    {
      $nouveau = '';
    }

    $this->set($iter, 4, $nouveau);
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
    $etat   = $store->get_value($iter, 7);

    if($origin != $new && $etat != self::STATUS_ERROR)
    {
      $fs = new myFilesystem();
      if(!$fs->rename($origin, $new))
      {
        srLog::add(sprintf('Erreur lors du rennomage de "%s" en "%s"', $origin, $new));
      }
    }
  }

  public function renommerSerieSiMemeNom($oldText, $newText)
  {
    $this->foreach(array('srListeStore', 'rennomerUneSerieSiMemeNom'), $oldText, $newText);
  }

  public function rennomerUneSerieSiMemeNom($store, $path, $iter, $oldText, $newText)
  {
    if($store->get_value($iter, 0) == $oldText)
    {
      $store->set($iter, 0, $newText);
    }
  }

}