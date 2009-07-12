<?php
abstract class infosProviderBase
{
  /**
   *
   * @var sfWebBrowser
   */
  protected $browser;


  public function __construct()
  {
    $this->browser = new sfWebBrowser();
  }
}