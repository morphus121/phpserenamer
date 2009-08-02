<?php
abstract class infosProviderBase
{
  public static $pagesEnCache = array();

  /**
   *
   * @var sfWebBrowser
   */
  protected $browser;


  public function __construct()
  {
    $this->browser = new myWebBrowser();
  }

  /**
   * Il est préférable de passer par cette fonction plutot que de faire un
   * $this->browser->get($url) car toutes les reponseDom passant par cette fonction
   * seront mises en cache, l'utilisation de la fonction getResponseDom sera donc plus rapide
   *
   * @return sfWebBrowser
   */
  public function get($url)
  {
    $this->browser->get($url);
//    $clef= md5(serialize($this->browser->getUrlInfo()));
//    if(!array_key_exists($clef, self::$pagesEnCache))
//    {
//      self::$pagesEnCache[$clef] = clone $this->browser;
//    }
//    return $this->browser = self::$pagesEnCache[$clef];
  }


}