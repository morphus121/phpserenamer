<?php
/**
 *
 * @author agallou
 *
 */
class debianPackageTask extends sfBaseTask
{

  protected function configure()
  {
    $this->addArgument('version',sfCommandArgument::REQUIRED, 'Tag à utiliser');

    $this->namespace           = 'sr';
    $this->name                = 'debian-package';
    $this->briefDescription    = 'Création du debian package';
    $this->detailedDescription = 'Création du debian package';
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfFilesystem::mkdirs('tmp/');
    exec('cp -r data/debianPackage/* tmp/');
    $this->logSection('Export de la version', $arguments['version']);
    exec(sprintf(
      'svn export http://phpserenamer.googlecode.com/svn/tags/%s/%s tmp/usr/lib/phpserenamer',
      $this->majorFromVersion($arguments['version']),
      $arguments['version']
    ));
    $this->logSection('deb+', sprintf('phpserenamer_%s_all.deb', $arguments['version']));
    exec('dpkg --build tmp ./');
    $this->logSection('dir-', 'tmp/');
    exec('rm -rf tmp/');
  }

  protected function majorFromVersion($version)
  {
    $tab = explode('.', $version);
    return $tab[0];
  }
}