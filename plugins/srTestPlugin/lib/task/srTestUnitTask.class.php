<?php

class srTestUnitTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY, 'The test name'),
    ));
    $this->addOptions(array(
      new sfCommandOption('xml', null, sfCommandArgument::OPTIONAL, 'fichier xml')
    ));
    $this->namespace           = 'sr-test';
    $this->name                = 'unit';
    $this->briefDescription    = 'Launches unit tests';
    $this->detailedDescription = 'Launches unit tests';
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (count($arguments['name']))
    {
      foreach ($arguments['name'] as $name)
      {
        $files = sfFinder::type('file')->follow_link()->name(basename($name).'Test.php')->in(sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.'unit'.DIRECTORY_SEPARATOR.dirname($name));
        foreach ($files as $file)
        {
          include($file);
        }
      }
    }
    else
    {
      require_once(sfConfig::get('sf_symfony_lib_dir').'/vendor/lime/lime.php');

      $h = new lime_harness(new lime_output_color());
      $h->base_dir = sfConfig::get('sf_test_dir').'/unit';

      // register unit tests
      $finder = sfFinder::type('file')->follow_link()->name('*Test.php');
      $h->register($finder->in($h->base_dir));

      $h->run();
      if ($options['xml'])
      {
        file_put_contents($options['xml'], $h->to_xml());
      }
      return 0;
    }
  }
}
