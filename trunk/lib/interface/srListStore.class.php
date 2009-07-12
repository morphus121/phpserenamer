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
	    self::$model = new srListeStore(64, 64, 64, 64, 64);
    }
    return self::$model;
  }

  public function remplirFromChemin($chemin)
  {
    $fichiers = myFinder::type('file')->ignore_version_control()->maxdepth(0)->follow_link()->relative()->in($chemin);
    $compteur = 0;
    foreach($fichiers as $fichier)
    {
      $oFichierSerie = new fichierSerie($fichier);
      $ligne = array(
        $oFichierSerie->getSerie(),
        $oFichierSerie->getSaison(),
        $oFichierSerie->getEpisode(),
        $fichier,
        '',
      );
      $this->append($ligne);
      $compteur++;
    }
  }

  public function tempNouveauNom($store, $path, $iter)
  {
    $oFichierSerie = new fichierSerie($store->get_value($iter, 3));
    $nouveau = sprintf(
      '%s - [%sx%s] - %s',
      $store->get_value($iter, 0),
      $store->get_value($iter, 1),
      str_pad($store->get_value($iter, 2),2,'0',STR_PAD_LEFT),
      ''
    );
    $this->set($iter, 4, $nouveau);
  }

  public function chercherNouveauxTitres()
  {
    $this->foreach(array('srListeStore', 'tempNouveauNom'));
  }

}