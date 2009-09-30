<?php
include_once(dirname(__FILE__).'/myBaseTask.class.php');
/**
 *
 * @author agallou
 *
 */
class debianPackageTask extends myBaseTask
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
    $fs = $this->getFilesystem();
    $fs->mkdirs('tmp/');
    $this->logSection('Export de la version', $arguments['version']);
    exec(sprintf(
      'svn export http://phpserenamer.googlecode.com/svn/tags/%s/%s tmp/usr/lib/phpserenamer',
      $this->majorFromVersion($arguments['version']),
      $arguments['version']
    ));
    exec('cp -r tmp/usr/lib/phpserenamer/data/debianPackage/* tmp/');
    file_put_contents('tmp/DEBIAN/control', $this->getDebianControlFile($arguments['version']));
    $this->logSection('deb+', sprintf('phpserenamer_%s_all.deb', $arguments['version']));
    exec('dpkg --build tmp ./');
    //$fs->removeRecusively('tmp/');
  }

  protected function majorFromVersion($version)
  {
    $tab = explode('.', $version);
    return $tab[0];
  }

  private function getDebianControlFile($version)
  {
    $var = '';
    $var .= <<<EOF
Package: phpserenamer

EOF;
    $var .= sprintf('Version: %s', $version) . "\n";
    $var .= <<<EOF
Section: base
Priority: optional
Architecture: all
Depends: php5-gtk2
Maintainer: adriengallou@gmail.com
Description: Renommez vos séries.

EOF;
    return $var;
  }
}