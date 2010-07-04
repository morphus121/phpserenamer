<?php

# FROZEN_SF_LIB_DIR: /var/www/production/sfweb/www/cache/symfony-for-release/1.2.7/lib

require_once dirname(__FILE__).'/../lib/vendor/symfony/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  protected $plugins = array();

  public function setup()
  {
    // for compatibility / remove and enable only the plugins you want
    //$this->enableAllPluginsExcept(array('sfDoctrinePlugin', 'sfCompat10Plugin', 'sfPropelPlugin'));

    $this->plugins = array('sfWebBrowserPlugin');
    ini_set("php-gtk.codepage", "UTF-8");
    $this->loadProjectConfig();
  }

  protected function loadProjectConfig()
  {
    static $load = false;
    if (!$load && $this instanceof sfApplicationConfiguration)
    {
      require $this->getConfigCache()->checkConfig('config/project.yml');
      $load = true;
    }
  }

  public function getAllPluginPaths()
  {
    $tab = array(
      'sfWebBrowserPlugin' => sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . 'sfWebBrowserPlugin',
    );
    return $tab;
  }

}
