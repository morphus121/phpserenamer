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
   * Raccourci vers la méthode get du browser
   *
   * @return sfWebBrowser
   */
  public function get($url)
  {
    return $this->browser->get($url);
  }

  /**
   *
   * @return string
   */
  abstract public function getName();
}