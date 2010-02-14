<?php
/**
 *
 * @author adriengallou
 *
 */
class infosProviderSerieAllocine extends infosProviderSerieBase
{

  /**
   * (non-PHPdoc)
   * @see lib/infosProvider/infosProviderSerieBase#getSeries($serie)
   */
  public function getSeries($serie)
  {
    $serie = str_replace('.', ' ', $serie);
    $serie = preg_replace('/(^the\s)|(\sthe\s)|(\sthe$)/', '', $serie);

    if(!count($tab = $this->getTableauIdSerie($serie)))
    {
      throw new SerieNonFoundException();
    }
    $series = array();
    foreach($tab as $val)
    {
      $series[] = $val['nomTrouve'];
    }
    return $series;
  }

  /**
   * (non-PHPdoc)
   * @see lib/infosProvider/infosProviderSerieBase#getEpisode($serie, $saison, $episode)
   */
  public function getEpisode($serie, $saison, $episode)
  {
    $tableau = $this->getTableauIdSerie($serie);

    $id = null;
    foreach($tableau as $tab)
    {
      if(strtolower($tab['nomTrouve']) == strtolower($serie))
      {
        $id = $tab['idTrouve'];
      }
    }

    if(is_null($id))
    {
      throw new SerieNonFoundException();
    }
    $lienSaison = $this->getLienSaison($id, $saison);

    $this->browser->get($lienSaison);

    $oDomDocument = $this->browser->getResponseDom();
    $xpath = new DOMXPath($oDomDocument);

    $query = '//a';
    $oDomNodeList = $xpath->query($query);

    if($oDomNodeList->length == 0)
    {
      throw new EpisodeNonFoundException();
    }

    //On crée un tableau episode -> serie
    $tab = array();
    for($i=0; $i <= $oDomNodeList->length;$i++)
    {
      $oDomNode = $oDomNodeList->item($i);

      if(!is_null($oDomNode->attributes) )
      {
        $countAttributes = 0;
        foreach ($oDomNode->attributes as $attrName => $attrNode)
        {
          $countAttributes++;
        }
        if($countAttributes)
        {
          if(preg_match('/Episode.*/',trim($oDomNode->nodeValue)))
          {
            $tab[] = trim($oDomNode->nodeValue);
          }
        }
      }
    }

    $episodes = array();
    for($i=0;$i<count($tab);$i++)
    {
      $temp  = explode(':', $tab[$i]);
      $num = trim(substr($temp[0], 9));
      $unEpisode = trim($temp[1]);
      $episodes[$num] = $unEpisode;
    }

    if(!array_key_exists($episode, $episodes))
    {
      throw new EpisodeNonFoundException();
    }

    return $episodes[$episode];

  }

//TODO lien saison en cours ?
  private function getLienSaison($numeroAllocineSerie, $numSaison)
  {
    $url = sprintf('http://www.allocine.fr/seriespage_seasonepisodes_last?cseries=%s', $numeroAllocineSerie);

    $this->browser->get($url);

    $oDomDocument = $this->browser->getResponseDom();
    if(is_null($oDomDocument))
    {
      throw new SerieNonFoundException();
    }
    $xpath = new DOMXPath($oDomDocument);

    $query = '//div[@class="filterseasonszone"]/ul/li/a';
    $oDomNodeList = $xpath->query($query);

    for($i=0; $i <= $oDomNodeList->length;$i++)
    {
      $oDomNode = $oDomNodeList->item($i);
      if(trim($oDomNode->nodeValue) == $numSaison)
      {
        return sprintf('http://www.allocine.fr%s', $oDomNode->attributes->getNamedItem('href')->nodeValue);
      }
    }
//echo $this->browser->getResponseBody();
    throw new SerieNonFoundException();
  }

  /**
   * Renvoi un tableau de type :
   *  array[] => array(
   *    'nomCherche' => 'nomRecherche',
   *    'idTrouve'   => 'idTrouve',
   *    'nomTrouve'  => 'nomTrouve'
   *  )
   *  ...
   *
   * @param  $serie
   * @return array
   */
  private function getTableauIdSerie($serie)
  {
    $this->get($this->rechercheParTitre($serie));

    $oDomDocument = $this->browser->getResponseDom();
    $xpath = new DOMXPath($oDomDocument);

    $query = '//div/a';
    $oDomNodeList = $xpath->query($query);
    if($oDomNodeList->length == 0)
    {
      throw new SerieNonFoundException();
    }

    for($i=0; $i <= $oDomNodeList->length;$i++)
    {
      $oDomNode = $oDomNodeList->item($i);

      if(!is_null($oDomNode->nodeValue) && $oDomNode->nodeValue != 'Et la réponse est...')
      {
        $matches = array();
        if(preg_match('/\/series\/ficheserie_gen_cserie=(.*)\.html/',$oDomNode->attributes->getNamedItem('href')->nodeValue, $matches))
        {
          $liste[] = array(
            'nomCherche' => $serie,
            'nomTrouve'  => trim(($oDomNode->nodeValue)),
            'idTrouve'   => $matches[1]
          );
        }
      }
    }

    return $liste;
  }

  public function nettoyerNomSerie($serie)
  {
    return parent::nettoyerNomSerie($serie);
  }

  /**
   * Retourne l'url de recherche par titre
   */
  private function rechercheParTitre($titre)
  {
    return sprintf('http://www.allocine.fr/recherche/6/?q=%s', urlencode($titre));
  }
}