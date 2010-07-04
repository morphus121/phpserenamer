<?php
/**
 * Classe infosProviderSerieThetvdbBase
 *
 * PHP version 5
 *
 * @package Interface
 * @author  Adrien Gallou <adriengallou@gmail.com>
 * @version SVN: <svn_id>
 */

/**
 * infosProviderSerieThetvdbBase
 *
 * @package InfosProvider
 * @author  Adrien Gallou <adriengallou@gmail.com>
 * @version Release: <package_version>
 *
 */
abstract class infosProviderSerieThetvdbBase extends infosProviderSerieBase
{

  /**
   *
   * @var string
   */
  const API_KEY = 'B9CF6C18B878F7A4';

  /**
   * Retourne le code de la langue utilisé pour rechercher les noms des épisodes
   *
   * @return string
   */
  abstract protected function getLanguage();

  /**
   * (non-PHPdoc)
   *
   * @param string $serie série à rechercher
   *
   * @see lib/infosProvider/infosProviderSerieBase#getSeries
   *
   * @return array[int]=>string liste des series
   */
  public function getSeries($serie)
  {
    //thetvdb n'aime pas les points
    $serie  = str_replace('.', ' ', $serie);
    $tab    = $this->getTableauIdSerie($serie);
    $series = array();
    foreach ($tab as $val)
    {
      $series[] = $val['nomTrouve'];
    }
    return $this->sortByRelevance($series, $serie);
  }

  /**
   * (non-PHPdoc)
   *
   * @param string $serie   résultat de la méthode getSeries
   * @param int    $saison  numéro de la saison
   * @param int    $episode numéro de l'épisode
   *
   * @see lib/infosProvider/infosProviderSerieBase#getEpisode
   *
   * @return string nom de l'épisode selon la série, la saison et l'épisode
   */
  public function getEpisode($serie, $saison, $episode)
  {
    $tableau      = $this->getTableauIdSerie($serie);
    $randomMirror = $this->getRandomMirror();
    $lang         = $this->getLanguage();
    $format       = '%s/api/%s/series/%s/default/%s/%s/' . $lang . '.xml';

    $id = null;
    foreach ($tableau as $tab)
    {
      if (strtolower($tab['nomTrouve']) == strtolower($serie))
      {
        $id = $tab['idTrouve'];
      }
    }

    if (is_null($id))
    {
      throw new SerieNonFoundException();
    }

    $url = sprintf($format, $randomMirror, self::API_KEY, $id, $saison, $episode);
    $this->get($url);
    $oDomDocument = $this->browser->getResponseDom();
    if (is_null($oDomDocument))
    {
      throw new EpisodeNonFoundException();
    }
    $xpath        = new DOMXPath($oDomDocument);
    $oDomNodeList = $xpath->query('//episodename');
    if ($oDomNodeList->length == 0)
    {
      throw new EpisodeNonFoundException();
    }
    $oDomNode = $oDomNodeList->item(0);
    return $oDomNode->nodeValue;
  }

  /**
   * Renvoi la liste des mirroirs disponibles pour theTvDB
   *
   * @return string[]
   */
  protected function getMirrors()
  {
    $url = sprintf('http://www.thetvdb.com/api/%s/mirrors.xml', self::API_KEY);
    $this->get($url);
    $oDomDocument = $this->browser->getResponseDom();
    $xpath        = new DOMXPath($oDomDocument);
    $oDomNodeList = $xpath->query('//mirror/mirrorpath');
    $mirrors      = array();
    for ($i=0; $i< $oDomNodeList->length; $i++)
    {
      $mirrors[] = $oDomNodeList->item($i)->nodeValue;
    }
    return $mirrors;
  }

  /**
   * Retourne l'url d'un des mirrors, au hazard
   *
   * @return string
   */
  protected function getRandomMirror()
  {
    $mirrors = $this->getMirrors();
    $clef    = rand(0, count($mirrors) - 1);
    return $mirrors[$clef];
  }

  /**
   * Renvoi un tableau contenant la liste des séries trouvées pour un nom donné
   *
   * @param string $serie nom de la série
   *
   * @return array[]
   */
  protected function getTableauIdSerie($serie)
  {
    $randomMirror = $this->getRandomMirror();
    $format       = '%s/api/GetSeries.php?seriesname=%s';
    $url          = sprintf($format, $randomMirror, urlencode($serie));
    $this->get($url);
    $oDomDocument = $this->browser->getResponseDom();
    if (is_null($oDomDocument))
    {
      throw new SerieNonFoundException();
    }
    $xpath        = new DOMXPath($oDomDocument);
    $oDomNodeList = $xpath->query('//series');
    if ($oDomNodeList->length == 0)
    {
      throw new SerieNonFoundException();
    }
    $liste = array();
    for ($i=0; $i< $oDomNodeList->length; $i++)
    {
      $serie      = $oDomNodeList->item($i);
      $seriesid   = $serie->getElementsByTagName('seriesid')->item(0)->nodeValue;
      $seriesName = $serie->getElementsByTagName('seriesname')->item(0)->nodeValue;
      $liste[]    = array(
        'nomCherche' => $serie,
        'nomTrouve'  => $seriesName,
        'idTrouve'   => $seriesid,
      );
    }
    return $liste;
  }

  /**
   * (non-PHPdoc)
   * @see lib/infosProvider/infosProviderSerieBase#getName()
   */
  public function getName()
  {
    return 'TheTvDB - ' . strtoupper($this->getLanguage());
  }
}