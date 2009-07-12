<?php
/**
 *
 * @author agallou
 *
 */
class mainTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));
    $this->addArgument('path',sfCommandArgument::REQUIRED, 'Chemin à parcourir');

    $this->namespace           = '';
    $this->name                = 'main';
    $this->briefDescription    = '';
    $this->detailedDescription = 'Lancement de l\'interface graphique';
  }

  protected function execute($arguments = array(), $options = array())
  {
    srListeStore::getInstance()->remplirFromChemin($arguments['path']);
    srGtk::main();
  }
}