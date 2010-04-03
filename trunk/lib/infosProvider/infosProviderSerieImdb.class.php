<?php
/**
 *
 * @author adriengallou
 *
 */
class infosProviderSerieImdb extends infosProviderSerieBase
{

  /**
   * (non-PHPdoc)
   * @see lib/infosProvider/infosProviderSerieBase#getSeries($serie)
   */
  public function getSeries($serie)
  {

    $this->get($this->rechercheParTitre($serie));
    //TODO faire seulement dans le deuxième cas
    $oDomDocument = $this->browser->getResponseDom();
    $xpath = new DOMXPath($oDomDocument);

    //Ici on peut faire face à deux situations
    // - soit la recherche à donné plusieurs résulats : liste donc les résultats
    //suivits par 'TV series' entre parenthèses
    // - soit la recherche n'a renvoyé qu'un résultat et l'on est donc renvoyé
    //sur la page de la série, on recherche alors le titre et le met dans un tableau
    //TODO troisème cas : plus de 500 résultats ?

    //1er cas : plusieurs résultats
    if(preg_match('/Displaying/',$this->browser->getResponseText()))
    {

      //TODO ne pas faire quelques tableaux mais les compter tous et tous les
      //parcourir à la recherche de séries ?
      $posTable = array();

      //On recherche quel est la position du tableau voulu
      if($pos = strpos($this->browser->getResponseText(),'Popular Titles'))
      {
        $posTable[] = count(explode('<table>',substr($this->browser->getResponseText(),0,$pos)));
        $posTable[] = count(explode('<table>',substr($this->browser->getResponseText(),0,$pos))) + 1;
      }

      if($pos = strpos($this->browser->getResponseText(),'<b>Titles ('))
      {
        $posTable[] = count(explode('<table>',substr($this->browser->getResponseText(),0,$pos))) + 1;
      }

      if($pos = strpos($this->browser->getResponseText(),'Titles (Partial Matches)'))
      {
        $posTable[] = count(explode('<table>',substr($this->browser->getResponseText(),0,$pos)));
      }

      $posTable = array_unique($posTable);
      $liste    = array();
      foreach($posTable as $pos)
      {
        $liste = array_merge($liste,$this->rechercheDansUnTableau($pos));
      }

    }
    //2ème cas : un résultat
    else
    {
      $query = '//h1';
      $oDomNodeList = $xpath->query($query);
      if($oDomNodeList->length == 0)
      {
        throw new SerieNonFoundException();
      }
      $oDomNode = $oDomNodeList->item(0);
      $titre = $oDomNode->childNodes->item(0)->wholeText;
      $liste[] = substr(trim($titre),1,-1);
    }

    if(!count($liste))
    {
      throw new SerieNonFoundException();
    }

    return $this->sortByRelevance($liste, $serie);
  }

  /**
   * Permet de pouvoir effectuer plusieurs recherches dans un tableau,
   * tels que popular titles, exact titles....
   *
   * @param $positionTableau
   * @return array[int]=>string liste des series du tableau
   */
  private function rechercheDansUnTableau($positionTableau)
  {
    $oDomDocument = $this->browser->getResponseDom();
    //ON fait un DOMXpath 2 fois ??
    $xpath = new DOMXPath($oDomDocument);
    $liste = array();
    $query = sprintf('//table[%s]', $positionTableau);
    //TODO directement passer par xpath pour avoir les td qui nous intéressent
    $oDomNodeList = $xpath->query($query);
    if($oDomNodeList->length == 0)
    {
      throw new SerieNonFoundException();
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
      //TODO un seul preg_match (ex mini-series : impact)
      if(preg_match('/(.*)\(TV mini-series\)/',$unResultat,$matches))
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
    $this->get($this->rechercheParTitre($serie));

    $oDomDocument = $this->browser->getResponseDom();
    $xpath = new DOMXPath($oDomDocument);
    $query = '//a[@class="tn15more inline"]';
    $oDomNodeList = $xpath->query($query);
    if($oDomNodeList->length == 0)
    {
      //TODO Lancer vraiement cette exception ?
      throw new SerieNonFoundException();
    }

    //On recherche le lien pour la liste des épisodes
    $lien = null;
    for($i=0;$i<$oDomNodeList->length;$i++)
    {
      $a = $oDomNodeList->item($i);
      $value = $a->nodeValue;
      if(strtolower($value) == 'full episode list')
      {
        $lien = $a->getAttributeNode('href')->value;
      }
    }
    //TODO exception si lien NULL

    $this->get('http://www.imdb.com' . $lien);
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

  public function nettoyerNomSerie($serie)
  {
    //TODO type numérique à 4 caractère pour le contenu des parenthèses
    $matches = array();
    //if(preg_match('/"(\s*)"\s*\(.*\)/',$serie, $matches))
    if(preg_match('/"(.*)".*/',$serie,$matches))
    {
      $serie = $matches[1];
    }
    return parent::nettoyerNomSerie($serie);
  }

  /**
   * Retourne l'url de recherche par titre
   */
  private function rechercheParTitre($titre)
  {
    return sprintf('http://www.imdb.com/find?s=tt&q=%s&x=24&y=13', urlencode($titre));
  }
}