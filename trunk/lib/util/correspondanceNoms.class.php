<?php
class correspondanceNoms
{

  private static $tableau  = array();
  private static $instance = null;

  /**
   *
   * @return correspondanceNoms
   */
  public function getInstance()
  {
    if(is_null(self::$instance))
    {
      self::$instance = new correspondanceNoms();
    }
    return self::$instance;
  }

  public function __construct()
  {
    //TODO récupérer liste des sites possibles
    $sites = array('imdb');
    foreach($sites as $site)
    {
      self::$tableau[$site] = array();
    }
  }

  public function setSerie($site, $nomLocal, $nomSite)
  {
    self::$tableau[$site][$nomLocal] = $nomSite;
  }

  public function hasKey($site, $nomLocal)
  {
    return array_key_exists($nomLocal, self::$tableau[$site]);
  }

  public function getNom($site, $nomLocal)
  {
    if($this->hasKey($site, $nomLocal))
    {
      return self::$tableau[$site][$nomLocal];
    }
    else
    {
      return false;
    }
  }


}