<?php
class fichierSerie
{
  private $fichier;
  private $serie;
  private $saison;
  private $episode;

  public function __construct($fichier)
  {
    $this->fichier = $fichier;
    $tab = explode('.', $this->fichier);
    $matches = array();

    //(attention, la série peut avoir un nom avec un point !)

    //fichiers de type serie.1x03.episode.HDTV.XviD-team
    if(preg_match('/^([\w|.|\']*)\.(\d*)x(\d*)\..*$/',$fichier,$matches))
    {
      $this->setSerie(strtolower($matches[1]));
      $this->setSaison((int)$matches[2]);
      $this->setEpisode((int)$matches[3]);
    }
    //serie.103.hdtv-team
    elseif(preg_match('/^([\w|.|\']*)\.(\d*)\..*$/', $fichier, $matches))
    {
      $this->setSerie(strtolower($matches[1]));
      //il peu parfois y avoir 10 saisons donc 4 caractères
      if(strlen($matches[2]) == 3)
      {
        $saison = substr($matches[2], 0, 1);
        $episode = substr($matches[2], 1, 2);
      }
      else
      {
        $saison = substr($matches[2], 0, 2);
        $episode = substr($matches[2], 2, 2);
      }
      $this->setSaison((int)$saison);
      $this->setEpisode((int)$episode);
    }
    //serie.s01e05.720p.hdtv.x264-ctu
    elseif(preg_match('/^([\w|.|\']*)\.[s|S](\d*)[e|E](\d*).*$/', $fichier, $matches))
    {
      $this->setSerie(strtolower($matches[1]));
      $this->setSaison((int)$matches[2]);
      $this->setEpisode((int)$matches[3]);
    }
    //série déjà passée dans serenamer
    //The Philanthropist - [1x01] - Pilot
    elseif(preg_match('/^([\w|.[\s|\']*)\s-\s\[(\d*)x(\d*)\].*$/', $fichier, $matches))
    {
      $this->setSerie(strtolower($matches[1]));
      $this->setSaison((int)$matches[2]);
      $this->setEpisode((int)$matches[3]);
    }



//    else
//    {
//      $this->setSerie(strtolower($tab[0]));
//      //TODO longueur pour les saison > 9
//      $this->setSaison((int)substr($tab[1],0,1));
//      $this->setEpisode((int)substr($tab[1],1,2));
//    }
  }

  /**
   *
   * @param  string $serie nom de la série
   * @return void
   */
  private function setSerie($serie)
  {
    $this->serie = $serie;
  }

  /**
   *
   * @param int $saison numéro de la saison
   * @return unknown_type
   */
  private function setSaison($saison)
  {
    $this->saison = $saison;
  }

  private function setEpisode($episode)
  {
    $this->episode = $episode;
  }

  /**
   *
   * @return string
   */
  public function getSerie()
  {
    return $this->serie;
  }

  /**
   *
   * @return int
   */
  public function getSaison()
  {
    return $this->saison;
  }

  /**
   *
   * @return int
   */
  public function getEpisode()
  {
    return $this->episode;
  }

  public function getExtension()
  {
    //TODO utiliser fileinfo
    return substr($this->fichier,(strrpos($this->fichier, '.')+1));
  }

}