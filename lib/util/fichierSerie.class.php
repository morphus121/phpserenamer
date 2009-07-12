<?php
class fichierSerie
{
  private $fichier;
  private $serie;
  private $saison;
  private $episode;
//TODO ajout de l'extension

  public function __construct($fichier)
  {
    $tab = explode('.', $fichier);
    $this->setSerie(strtolower($tab[0]));
    if(strpos($tab[1], 'x'))
    {
      $tab2 = explode('x',$tab[1]);
      $this->setSaison((int)$tab2[0]);
      $this->setEpisode((int)$tab2[1]);
    }
    else
    {
      //TODO longueur pour les saison > 9
      $this->setSaison((int)substr($tab[1],0,1));
      $this->setEpisode((int)substr($tab[1],1,2));
    }
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

}