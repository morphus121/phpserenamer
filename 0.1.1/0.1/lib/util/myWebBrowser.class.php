<?php
/**
 *
 * @author adriengallou
 *
 */
class myWebBrowser extends sfWebBrowser
{

  const EXPIRATION = 2592000; //30 jours

  /**
   * Get mettant les pages en cache
   *
   * @param string url
   * @param array  paramètres de la requete
   * @param array  headers
   *
   * @return sfWebBrowser
   */
  public function get($uri, $parameters = array(), $headers = array())
  {
    $fc          = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir')));
    $encoded_uri = urlencode($uri);
    $clef        = sprintf('%smyWebBrowser', $encoded_uri);

    if($fc->has($clef) && (time() < (self::EXPIRATION + $fc->getLastModified($clef))))
    {
      $this->setResponseCode(200);
      $this->setResponseHeaders();
      $this->setResponseText($fc->get($clef));

      $this->responseDom = new DomDocument('1.0', 'utf8');
      $this->responseDom->validateOnParse = true;
      @$this->responseDom->loadHTML($this->getResponseText());

      return $this;
    }
    else
    {
      $ret = parent::get($uri, $parameters, $headers);
      $fc->set($clef, $this->getResponseText());
      return $ret;
    }
  }
}