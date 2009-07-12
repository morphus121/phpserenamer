<?php
class infosProviderSerieImdb extends infosProviderSerieBase
{

  /**
   * (non-PHPdoc)
   * @see lib/infosProvider/infosProviderSerieBase#getSeries($serie)
   */
  public function getSeries($serie)
  {
    $liste = array();
    $this->browser->get($this->rechercheParTitre($serie));
    $oDomDocument = $this->browser->getResponseDom();
    $xpath = new DOMXPath($oDomDocument);
    //TODO directement passer par xpath pour avoir les td qui nous intéressent ?
    $query = '//table[2]';
    $oDomNodeList = $xpath->query($query);
    if($oDomNodeList->length == 0)
    {
      throw new sfException('Pas de résultat trouvé');
    }
    $oDomNode = $oDomNodeList->item(0);
    foreach($oDomNode->childNodes as $tr)
    {
      $unResultat = $tr->childNodes->item(2)->nodeValue;
      $matches = array();
      if(preg_match('/(.*)\(TV series\)/',$unResultat,$matches))
      {
        $liste[] = trim($matches[1]);
      }
    }
    return $liste;
  }

  /**
   * (non-PHPdoc)
   * @see lib/infosProvider/infosProviderSerieBase#getEpisode($serie, $saison, $episode)
   */
  public function getEpisode($serie, $saison, $episode)
  {
    $this->browser->get($this->rechercheParTitre(urlencode($serie)));
    $oDomDocument = $this->browser->getResponseDom();
    $xpath = new DOMXPath($oDomDocument);
    $query = '//a[@class="tn15more inline"]';
    $oDomNodeList = $xpath->query($query);
    if($oDomNodeList->length == 0)
    {
      throw new sfException('Pas de résultat trouvé');
    }

    //On recherche le lien pour la liste des épisodes
    $lien = null;
    for($i=0;$i<$oDomNodeList->length;$i++)
    {
      $a = $oDomNodeList->item($i);
      $value = $a->nodeValue;
      if($value == 'full episode list')
      {
        $lien = $a->getAttributeNode('href')->value;
      }
    }
    //TODO exception si lien NULL

    $this->browser->get('http://www.imdb.com' . $lien);
    $oDomDocument = $this->browser->getResponseDom();
    $xpath = new DOMXPath($oDomDocument);
    $query = '//h3';
    $oDomNodeList = $xpath->query($query);
    //On recherche l'épisode
    $titre = null;
    for($i=0;$i<$oDomNodeList->length;$i++)
    {
      $a = $oDomNodeList->item($i);
      $value = $a->nodeValue;
      $recherche = sprintf('Season %s, Episode %s', $saison, $episode);
      if(substr($value, 0, strpos($value, ':')) == $recherche)
      {
        $titre = substr($value, (strpos($value, ':')+1));
      }
    }
    return trim($titre);
  }

  /**
   * Retourne l'url de recherche par titre
   */
  private function rechercheParTitre($titre)
  {
    return sprintf('http://www.imdb.com/find?s=tt&q=%s&x=24&y=13', $titre);
  }
}