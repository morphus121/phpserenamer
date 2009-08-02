<?php
class myWebBrowser extends sfWebBrowser
{

  protected $cache_expiration = 2592000; // 30 days default cache

//  /**
//   * return current cache expiration in seconds
//   *
//   * @return integer
//   */
//  public function getCacheExpiration() {
//    return $this->cache_expiration;
//  }
//
//  /**
//   * set cache expiration
//   *
//   * @param integer $seconds
//   * @return integer
//   */
//  public function setCacheExpiration($seconds) {
//    $this->cache = $seconds;
//    return $this->cache_expiration;
//  }

  /**
   * cached get
   *
   * @param string The request uri
   * @param array  The request parameters (associative array)
   * @param array  The request headers (associative array)
   *
   * @return sfWebBrowser The current browser object
   */
  public function get($uri, $parameters = array(), $headers = array())
  {
    $c = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir')));
    $encoded_uri = urlencode($uri);
    $clef = $encoded_uri . 'myWebBrowser';
    if($c->has($clef) && (time() < ($this->cache_expiration + $c->getLastModified($clef))))
    {
      $this->setResponseCode(200);
      $this->setResponseHeaders();
      $this->setResponseText($c->get($clef));

      //TODO pourquoi ca ne marche pas en parssant par le getResponseDom ?
      $this->responseDom = new DomDocument('1.0', 'utf8');
      $this->responseDom->validateOnParse = true;
      @$this->responseDom->loadHTML($this->getResponseText());

      return $this;
    }
    else
    {
      $ret = parent::get($uri, $parameters, $headers);
      $c->set($clef, $this->getResponseText());
      return $ret;
    }
  }
}