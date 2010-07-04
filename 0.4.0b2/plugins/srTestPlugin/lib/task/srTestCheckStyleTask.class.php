<?php

class srTestCheckStyleTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('xml', null, sfCommandOption::PARAMETER_REQUIRED, 'fichier xml')
    ));
    $this->namespace           = 'sr-test';
    $this->name                = 'checkstyle';
    $this->briefDescription    = 'Launches checkstyle tests';
    $this->detailedDescription = 'Launches checkstyle tests';
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $files = sfFinder::type('file')->name('*.php')
                                   ->prune('cache')
                                   ->prune('data')
                                   ->prune('web')
                                   ->prune('vendor')
                                   ->prune('sf*')
                                   ->in(sfConfig::get('sf_root_dir'));

    $cmd = sprintf(
      '%s %s/plugins/srTestPlugin/lib/vendor/codesniffer/scripts/phpcs %s --report=checkstyle --report-file=%s',
      sfToolkit::getPhpCli(),
      sfConfig::get('sf_root_dir'),
      implode(' ', $files),
      $options['xml']
    );
    exec($cmd);
  }
}