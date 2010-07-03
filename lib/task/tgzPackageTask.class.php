<?php
/**
 * tgzPackageTask
 *
 * PHP version 5
 *
 * @package Tasks
 * @author  Adrien Gallou <adriengallou@gmail.com>
 * @version SVN: <svn_id>
 */

require_once(dirname(__FILE__).'/myBaseTask.class.php');

/**
 * tgzPackageTask
 *
 * @package Interface
 * @author  Adrien Gallou <adriengallou@gmail.com>
 * @version Release: <package_version>
 */
class tgzPackageTask extends myBaseTask
{

  /**
   * (non-PHPdoc)
   *
   * @see lib/vendor/symfony/task/sfTask#configure()
   * @return void
   */
  protected function configure()
  {
    $this->addArgument('version', sfCommandArgument::REQUIRED, 'Tag à utiliser');

    $this->namespace           = 'sr';
    $this->name                = 'tgz-package';
    $this->briefDescription    = 'Création du tgz';
    $this->detailedDescription = 'Création du tgz';

    $this->addOption('no-delete', null, sfCommandOption::PARAMETER_NONE, 'Ne pas supprimer les fichiers temporaires');
    $this->addOption('add-folder', null, sfCommandOption::PARAMETER_OPTIONAL, 'Dossier dans data contenant les fichiers à ajouter');
    $this->addOption('use-current', null, sfCommandOption::PARAMETER_NONE, 'Utiliser les fichiers data du project en cours et non ceux chekoutés');
    $this->addOption('to-google-code', null, sfCommandOption::PARAMETER_NONE, 'Envoyer le fichier vers google code');
  }

  /**
   * (non-PHPdoc)
   *
   * @param string[] $arguments arguments
   * @param string[] $options   options
   *
   * @see lib/vendor/symfony/task/sfTask#execute()
   * @return int
   */
  protected function execute($arguments = array(), $options = array())
  {
    $fs          = $this->getFilesystem();
    $versionName = sprintf('phpserenamer-%s', $arguments['version']);
    $sourcesDir  = 'tmp/';
    $dataDir     = ($options['use-current']) ? 'data/' : $sourcesDir . 'usr/lib/phpserenamer/data';

    //si le le dernier build à échoué on supprime le contenu des sources et du
    //build pour éviter les problèmes de fichiers qui restent dans le dossier
    $fs->removeRecusively($sourcesDir);

    //Création du dossier source
    $fs->mkdirs($sourcesDir);

    //Export de la version voulue
    $this->logSection('Export de la version', $arguments['version']);
    exec(sprintf(
      'svn export http://phpserenamer.googlecode.com/svn/tags/%s/%s %s/usr/lib/phpserenamer',
      $this->majorFromVersion($arguments['version']),
      $arguments['version'],
      'tmp/'
    ));

    //On ajoute les fichiers nécéssaires au tgz
    $fs->sh(sprintf('cp -R %s/tgz/* %s', $dataDir, $sourcesDir));

    //Si on à passé l'option on copie le répertoire
    if ($options['add-folder'])
    {
      $addPath  = $dataDir . $options['add-folder'];
      $makeFile = $sourcesDir . 'Makefile';

      //On ignore les fichiers commendant par un _
      $fs->sh(sprintf('cp -R %s/[^_*]* %s', $addPath, $sourcesDir));

      //On ajoute à la partie uninstall du makefile les nouveaux fichiers
      $this->addFilesToUninstallMakefile($addPath, $makeFile);
    }

    //On supprimes les éventuels fichiers .svn
    $fs->sh(sprintf('rm -rf `find %s -type d -name .svn`', $sourcesDir));

    //Suppression des fichiers inutiles
    $this->deleteUnusedFilesAndFolders($sourcesDir . DIRECTORY_SEPARATOR . 'usr/lib/phpserenamer/');

    //On crée le tar.gz
    $this->logSection('tar.gz+', $sourcesDir . $versionName . '.tag.gz');
    $cwdir = getcwd();
    chdir($sourcesDir);
    exec(sprintf('tar -cf %1$s.tar %2$s', $versionName, '*'));
    exec(sprintf('gzip -f %s.tar', $versionName));
    chdir($cwdir);

    //Copie du tgz dans le repertoire builds du project
    $fs->mkdirs('builds/');
    $filePath =  sprintf('builds/%s.tar.gz', $versionName);
    $fs->copy($sourcesDir . $versionName . '.tar.gz', $filePath);

    if (!$options['no-delete'])
    {
      $fs->removeRecusively($sourcesDir);
    }
    if ($options['to-google-code'])
    {
      $task      = new uploadGoogleCodeTask($this->dispatcher, $this->formatter);
      $taskArguments = array('file' => $filePath, 'project' => 'phpserenamer');
      $taskOptions   = array(sprintf('--summary="v %s - tarball"', $arguments['version']));
      $task->run($taskArguments, $taskOptions);
    }
  }

  /**
   * Retourne la version majeure d'après un numéro de version
   *
   * @param  string $version version
   *
   * @return string
   */
  protected function majorFromVersion($version)
  {
    $tab = explode('.', $version);
    return $tab[0];
  }

  /**
   *
   *
   * @param string $addPath
   * @param string $makeFile
   *
   * @return void
   */
  protected function addFilesToUninstallMakefile($addPath, $makeFile)
  {
    $cont  = file_get_contents($makeFile);
    $files = sfFinder::type('file')->ignore_version_control()
                                   ->prune('_scripts')->in($addPath);
    $str   = '';
    $start = strlen(realpath($addPath));
    foreach ($files as $file)
    {
      $str .= "\t" . sprintf('rm -f %s', substr($file, $start)) . "\n";
    }
    $cont .= $str;
    file_put_contents($makeFile, $cont);
  }

  /**
   * Supprime tous les fichiers n'ayant pas d'intérêt
   *
   * @param string $path chemin de base de suppression
   *
   * @return void
   */
  protected function deleteUnusedFilesAndFolders($path)
  {
    $list = $this->getFilesAndFoldersToDelete();
    foreach ($list as $toDelete)
    {
      $this->getFilesystem()->removeRecusively($path . $toDelete);
    }
  }

  /**
   * Retourne la liste des fichiers et dossiers à supprimer
   *
   * @return string[]
   */
  protected function getFilesAndFoldersToDelete()
  {
    return array_map('trim', file(sfConfig::get('sf_root_dir') . '/config/filesToDelete.txt'));
  }

}
