<?php
/**
 * Classe srListeStore
 *
 *  PHP version 5
 *
 * @package Interface
 * @author  Adrien Gallou <adriengallou@gmail.com>
 * @version SVN: <svn_id>
 */
/**
 * srListeStore
 *
 * @package Interface
 * @author  Adrien Gallou <adriengallou@gmail.com>
 * @version Release: <package_version>
 * @implements GtkTreeModel
 */
class srListeStore extends GtkListStore
{

  /**
   *
   * @var string
   */
  const STATUS_OK = 'ok';

  /**
   *
   * @var string
   */
  const STATUS_ERROR = 'error';

  /**
   *
   * @var srListeStore
   */
  private static $model = null;

  /**
   * Retourne l'instance
   *
   * @return srListeStore
   */
  public static function getInstance()
  {
    if (is_null(self::$model))
    {
      //@see http://gtk.php.net/manual/en/gtk.enum.type.php
      //Le cinquième élément !(c'est un champ nom affiché utilisé pour conserver
      //le chemin complet du fichier)
      //Le sixième élément indique si la ligne est en erreur ou no
      self::$model = new srListeStore(64, 64, 64, 64, 64, 64, 64);
    }
    return self::$model;
  }

  /**
   * Remplit la liste d'après un dossier
   *
   * @param string $chemin chemin du dossier à ajouter
   *
   * @return void
   */
  public function remplirFromChemin($chemin)
  {
    $maxdepth = (srConfig::get('openRecursively')) ? 1000000 : 0;
    $fichiers = myFinder::type('file')->ignore_version_control()
                                      ->maxdepth($maxdepth)->follow_link()
                                      ->relative()->in($chemin);
    $compteur = 0;
    foreach ($fichiers as $fichier)
    {
      $this->remplirFromFilePath($chemin . DIRECTORY_SEPARATOR . $fichier);
      $compteur++;
    }
  }

  /**
   * Ajoute un fichier à la liste
   *
   * @param string $filePath chemin du fichier
   *
   * @return void
   */
  public function remplirFromFilePath($filePath)
  {
    $baseName      = pathinfo($filePath, PATHINFO_BASENAME);
    $oFichierSerie = new fichierSerie($baseName);
    $serie         = $oFichierSerie->getSerie();
    //this is used for windows OS, and the function_exists is to not to have to
    //install the php-xml extension.
    if (function_exists('utf8_encode') && myFilesystem::isOsWindows())
    {
      if (!sfToolkit::isUTF8($serie))
      {
        $serie = utf8_encode($serie);
      }
      if (!sfToolkit::isUTF8($baseName))
      {
        $baseName = utf8_encode($baseName);
      }
    }
    $ligne = array(
    $serie,
    $oFichierSerie->getSaison(),
    $oFichierSerie->getEpisode(),
    $baseName,
      '',
    pathinfo($filePath, PATHINFO_DIRNAME),
    false //pas en erreur
    );
    $this->append($ligne);
  }

  /**
   * Ajoute le nouveau nom dans la colonne correspondante pour une ligne
   *
   * @param GtkTreeModel $store model
   * @param GtkTreePath  $path  path
   * @param GtkTreeIter  $iter  iter
   *
   * @see http://gtk.php.net/manual/en/gtk.gtktreemodel.method.foreach.php
   *
   * @return void
   */
  public function tempNouveauNom($store, $path, $iter)
  {
    try
    {
      $correspondanceNoms  = correspondanceNoms::getInstance();
      $selectedProvider    = srWindow::getInstance()->getSelectedProvider();
      $oFichierSerie       = new fichierSerie($store->get_value($iter, 3));
      $text = srUtils::getTranslation('Preview of new filename for file', 'progressbar');
      srProgressBar::progress(sprintf('%s "%s"', $text, $store->get_value($iter, 3)));
      $oInfosProviderSerie = infosProviderFactory::createInfosProvider('serie',
        $selectedProvider);
      $nomSerie            = $correspondanceNoms->getNom($selectedProvider,
        $store->get_value($iter, 0));
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

       $nouveau = sprintf('%s.%s', $nouveau, $oFichierSerie->getExtension());
       $nouveau = srUtils::nameForFileSystem($nouveau);
      if (srConfig::get('replaceSpaces'))
      {
         $nouveau = str_replace(' ', '.', $nouveau);
      }
    }
    catch(SerieNonFoundException $ex)
    {
      $nouveau = '';
      $this->set($iter, 6, self::STATUS_ERROR);
    }
    catch(EpisodeNonFoundException $ex)
    {
      $nouveau = '';
      $this->set($iter, 6, self::STATUS_ERROR);
    }

    $this->set($iter, 4, $nouveau);
  }

  /**
   * Ajoute le nouveau nom à toutes les colonnes
   *
   * @return void
   */
  public function chercherNouveauxTitres()
  {
    $this->foreach(array('srListeStore', 'tempNouveauNom'));
  }

  /**
   * Renomme touts les fichiers puis vide la liste
   *
   * @return unknown_type
   */
  public function renommer()
  {
    $this->foreach(array('srListeStore', 'renommerUnEpisode'));
    $this->clear();
  }

  /**
   * Renomme un épisode
   *
   * @param GtkTreeModel $store model
   * @param GtkTreePath  $path  path
   * @param GtkTreeIter  $iter  iter
   *
   * @return void
   */
  public function renommerUnEpisode($store, $path, $iter)
  {
    $sep    = DIRECTORY_SEPARATOR;
    $origin = $store->get_value($iter, 5) . $sep . $store->get_value($iter, 3);
    $new    = $store->get_value($iter, 5) . $sep . $store->get_value($iter, 4);
    $etat   = $store->get_value($iter, 7);

    if ($origin != $new && $etat != self::STATUS_ERROR)
    {
      $fs = new myFilesystem();
      if (!$fs->rename($origin, $new))
      {
        $format = 'Erreur lors du rennomage de "%s" en "%s"';
        srLog::add(sprintf($format, $origin, $new));
      }
    }
  }

  /**
   * Lorsque l'on renomme une série on appelle cette fonction pour boucler
   * sur toutes les lignes et renommer les séries qui se portaient le même nom
   *
   * @param string $oldText ancien nom
   * @param string $newText nouveau nom
   *
   * @return void
   */
  public function renommerSerieSiMemeNom($oldText, $newText)
  {
    $call = new sfCallable(array('srListeStore', 'rennomerUneSerieSiMemeNom'));
    $this->foreach($call->getCallable(), $oldText, $newText);
  }

  /**
   * fonction appelée lorsque l'on boucle sur toutes les lignes pour renommer
   * les série qui ont le même nom
   *
   * @param GtkTreeModel $store   model
   * @param GtkTreePath  $path    path
   * @param GtkTreeIter  $iter    iter
   * @param string       $oldText ancien nom
   * @param string       $newText nouveau nom
   *
   * @return void
   */
  public function rennomerUneSerieSiMemeNom($store, $path, $iter, $oldText, $newText)
  {
    if ($store->get_value($iter, 0) == $oldText)
    {
      $store->set($iter, 0, $newText);
    }
  }

  /**
   * Change le numéro de saison d'une ligne
   *
   * @param GtkTreeModel $store  store
   * @param GtkTreePath  $path   path
   * @param GtkTreeIter  $iter   iter
   * @param int          $season saison
   *
   * @return void
   */
  public function changeOneSeason($store, $path, $iter, $season)
  {
    $store->set($iter, 1, $season);
  }

  /**
   * Change le numéro d'épisode d'une ligne
   *
   * @param GtkTreeModel $store model
   * @param GtkTreePath  $path  path
   * @param GtkTreeIter  $iter  iter
   * @param int          $start debut
   * @param int          $pas   pas
   *
   * @return void
   */
  public function changeOneEpisode($store, $path, $iter, $start, $pas)
  {
    $episode = $start + ($path[0] * $pas);
    $store->set($iter, 2, $episode);
  }

  /**
   * Modifie toutes les saisons par celle passée en paramètre
   *
   * @param int $season saison à appliquer sur toutes les lignes
   *
   * @return void
   */
  public function changeAllSeasons($season)
  {
    $this->foreach(array('srListeStore', 'changeOneSeason'), $season);
  }

  /**
   * Modifie tous les épisodes d'après le début et le pas passés en paramètre
   *
   * @param int $start start
   * @param int $pas   pas
   *
   * @return void
   */
  public function changeAllEpisodes($start, $pas)
  {
    $this->foreach(array('srListeStore', 'changeOneEpisode'), $start, $pas);
  }


}