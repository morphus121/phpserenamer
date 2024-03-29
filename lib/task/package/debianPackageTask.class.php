<?php
include_once(dirname(__FILE__).'/../myBaseTask.class.php');
include_once(dirname(__FILE__).'/basePackageTask.class.php');
/**
 *
 * @author agallou
 *
 */
class debianPackageTask extends basePackageTask
{

  /**
   * (non-PHPdoc)
   * @see lib/vendor/symfony/task/sfTask#configure()
   */
  protected function configure()
  {
    $this->addArgument('version',sfCommandArgument::REQUIRED, 'Tag à utiliser');

    $this->namespace           = 'sr';
    $this->name                = 'debian-package';
    $this->briefDescription    = 'Création du debian package';
    $this->detailedDescription = 'Création du debian package';

    $this->addOption('to-ppa'     , null, sfCommandOption::PARAMETER_NONE, 'Envoi le paquet sur le ppa');
    $this->addOption('no-delete'  , null, sfCommandOption::PARAMETER_NONE, 'Ne pas supprimer les fichiers temporaires');
    $this->addOption('use-current', null, sfCommandOption::PARAMETER_NONE, 'Utiliser les fichiers data du project en cours et non ceux chekoutés');
    $this->addOption('to-google-code', null, sfCommandOption::PARAMETER_NONE, 'Envoyer le fichier vers google code');
  }

  /**
   * (non-PHPdoc)
   * @see lib/vendor/symfony/task/sfTask#execute()
   */
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

    $dataFolderPrefix = ($options['use-current']) ? '' : 'tmp/usr/lib/phpserenamer/';
    //On ignore les fichiers commendant par un _
    $fs->sh(sprintf('cp -R %sdata/ubuntu/* tmp/', $dataFolderPrefix));
    $fs->sh(sprintf('rm -rf tmp/_*'));

    //On ignore les fichiers commendant par un _
    $fs->sh(sprintf('cp -R %sdata/ubuntu/_scripts/* tmp/', $dataFolderPrefix));
    //$fs->sh(sprintf('cp -R %sdata/%s/[^_*]* %s', $addPath, $sourcesDir));

    //$fs->sh(sprintf('cp -R %sdata/ubuntu/_scripts/* tmp/', $dataFolderPrefix));
    file_put_contents('tmp/debian/control', $this->getDebianControlFile($arguments['version']));

    //Suppression des fichiers inutiles
    $this->deleteUnusedFilesAndFolders($dataFolderPrefix);

    $cwdir = getcwd();
    chdir('tmp/');
    $cmd = 'dpkg-buildpackage -rfakeroot';
    if($options['to-ppa'])
    {
      $cmd .= ' -S ';
      $this->logSection('deb+', 'Préparation des sources');
    }
    else
    {
      $this->logSection('deb+', sprintf('phpserenamer_%s_all.deb', $arguments['version']));
    }
    exec(sprintf('export DEBEMAIL=ecrire@adrien-gallou.fr;export DEBFULLNAME="Adrien Gallou"; dch --create -v %1$s -D jaunty -u low --package phpserenamer version %1$s', $arguments['version']));
    passthru($cmd);
    chdir($cwdir);

    if($options['to-ppa'])
    {
      $this->logSection('deb+', 'Envoi vers le ppa');
      passthru(sprintf('dput ppa:adriengallou/ppa-phpserenamer phpserenamer_%s_source.changes', $arguments['version']));
    }

    if(!$options['no-delete'])
    {
      $fs->removeRecusively('tmp/');
      exec('rm -f *.dsc *.tar.gz *.changes *.upload');
    }
    if ($options['to-google-code'])
    {
      $filePath  = sprintf('phpserenamer_%s_all.deb', $arguments['version']);
      $task      = new uploadGoogleCodeTask($this->dispatcher, $this->formatter);
      $taskArguments = array('file' => $filePath, 'project' => 'phpserenamer');
      $taskOptions   = array(sprintf('--summary="v %s - ubuntu"', $arguments['version']));
      $task->run($taskArguments, $taskOptions);
    }
  }

  /**
   *
   * @param  string $version
   * @return string
   */
  protected function majorFromVersion($version)
  {
    $tab = explode('.', $version);
    return $tab[0];
  }

  /**
   *
   * @param  string $version
   * @return string
   */
  private function getDebianControlFile($version)
  {
    $var = '';
    $var .= <<<EOF
Source: phpserenamer
Section: utils
Priority: optional
Maintainer: Adrien GALLOU <ecrire@adrien-gallou.fr>
Build-Depends: debhelper (>= 7)
Standards-Version: 3.8.0
Homepage: http://code.google.com/p/phpserenamer/


EOF;
    $var .= sprintf('Version: %s', $version) . "\n";
    $var .= <<<EOF
Package: phpserenamer
Architecture: all
Depends:
Description: Renommez vos séries.

EOF;
    return $var;
  }

}
