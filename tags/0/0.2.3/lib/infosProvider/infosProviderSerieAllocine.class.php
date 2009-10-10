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

    $this->browser->get($this->getLienSaison($id, $saison));

    $oDomDocument = $this->browser->getResponseDom();
    $xpath = new DOMXPath($oDomDocument);

    $query = '//h4';
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
          if(in_array($oDomNode->attributes->getNamedItem('style')->nodeValue, array('color:#000000', 'color:gray')))
          {
            $tab[] = $oDomNode->nodeValue;
          }
        }
      }
    }

    $episodes = array();
    for($i=0;$i<count($tab);$i = $i + 2)
    {
      $num = trim(substr($tab[$i], 8));
      $unEpisode = trim($tab[$i + 1]);
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
  	$url = sprintf('http://www.allocine.fr/series/episodes_gen_cserie=%s.html', $numeroAllocineSerie);
    $this->browser->get($url);

    $oDomDocument = $this->browser->getResponseDom();
    $xpath = new DOMXPath($oDomDocument);

    $query = '//a';
    $oDomNodeList = $xpath->query($query);

    for($i=0; $i <= $oDomNodeList->length;$i++)
    {
      $oDomNode = $oDomNodeList->item($i);

      if($oDomNode->nodeValue == sprintf('Saison %s', $numSaison))
      {
        return sprintf('http://www.allocine.fr%s', $oDomNode->attributes->getNamedItem('href')->nodeValue);
      }
    }

    //pour l'éventuelle dernière saison
    $query = '//span';
    $oDomNodeList = $xpath->query($query);

    for($i=0; $i <= $oDomNodeList->length;$i++)
    {
      $oDomNode = $oDomNodeList->item($i);

      if($oDomNode->nodeValue == sprintf('Saison %s', $numSaison))
      {
        return $url;
      }
    }

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

    $query = '//h4/a';
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
            'nomTrouve'  => $oDomNode->nodeValue,
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
    return sprintf('http://www.allocine.fr/recherche/?motcle=%s&x=0&y=0&rub=6', urlencode($titre));
  }
}