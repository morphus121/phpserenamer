<?php
include_once(dirname(__FILE__).'/myBaseTask.class.php');
/**
 *
 * @author adriengallou
 *
 */
class rpmPackageTask extends myBaseTask
{

  /**
   * (non-PHPdoc)
   * @see lib/vendor/symfony/task/sfTask#configure()
   */
  protected function configure()
  {
    $this->addArgument('version',sfCommandArgument::REQUIRED, 'Tag à utiliser');
    $this->addArgument('release',sfCommandArgument::OPTIONAL, 'Release'       , 1);

    $this->namespace           = 'sr';
    $this->name                = 'rpm-package';
    $this->briefDescription    = 'Création du rpm';
    $this->detailedDescription = 'Création du rpm';

    $this->addOption('no-delete', null, sfCommandOption::PARAMETER_NONE, 'Ne pas supprimer les fichiers temporaires');
  }

  /**
   * (non-PHPdoc)
   * @see lib/vendor/symfony/task/sfTask#execute()
   */
  protected function execute($arguments = array(), $options = array())
  {
    $fs          = $this->getFilesystem();
    $versionName = sprintf('phpserenamer-%s-%s', $arguments['version'], $arguments['release']);
    $versionNoRe = sprintf('phpserenamer-%s', $arguments['version']);
    $sourcesDir  = self::getRpmSourcesDir() . 'phpserenamer-' . $arguments['version'];
    $buildDir    = self::getRpmBuildDir() . 'phpserenamer-' . $arguments['version'];
    $specFile    = self::getRpmSpecsDir() . $versionName . '.spec';

    //Création du fichier spec
    $fs->filePutContent($specFile, $this->getSpecFile($arguments['version'], $arguments['release']));

    //si le le dernier build à échoué on supprime le contenu des sources et du
    //build pour éviter les problèmes de fichiers qui restent dans le dossier
    $fs->removeRecusively($sourcesDir);
    $fs->removeRecusively($buildDir);

    //Création du dossier source
    $fs->mkdirs($sourcesDir);

    //Export de la version voulue
    $this->logSection('Export de la version', $arguments['version']);
    exec(sprintf(
      'svn export http://phpserenamer.googlecode.com/svn/tags/%s/%s %s/usr/lib/phpserenamer',
      $this->majorFromVersion($arguments['version']),
      $arguments['version'],
      $sourcesDir
    ));

    //On copie les fichiers de data/mandriva
    $fs->sh(sprintf('cp -R data/mandriva/* %s', $sourcesDir));
    $fs->removeRecusively($sourcesDir . '/_scripts/');

    //TODO suppression des fichiers inutiles

    //On supprimes les éventuels fichiers .svn
    $fs->sh(sprintf('rm -rf `find %s -type d -name .svn`', $sourcesDir));

    //On crée le tar.gz
    $this->logSection('tar.gz+', self::getRpmSourcesDir() . $versionName . '.tag.gz');
    $cwdir = getcwd();
    chdir(self::getRpmSourcesDir());
    exec(sprintf('tar -cf %1$s.tar %2$s', $versionName, $versionNoRe));
    exec(sprintf('gzip -f %s.tar', $versionName));

    //Création du rpm
    $rpm  = self::getRpmRpmsDir() . $versionName . '.i586.rpm';
    $srpm = self::getRpmSrpmsDir() . $versionName . '.src.rpm';
    $this->logSection('rpm+', $rpm);
    $fs->sh(sprintf('rpmbuild -ba --nodeps %s', $specFile));
    chdir($cwdir);

    //Copie des sources et du "binaire" dans le repertoire builds du project
    $fs->mkdirs('builds/');
    $fs->copy($rpm, sprintf('builds/%s.i586.rpm', $versionName));
    $fs->copy($srpm, sprintf('builds/%s.src.rpm', $versionName));

    if(!$options['no-delete'])
    {
      $fs->remove($rpm);
      $fs->remove($srpm);
      $fs->removeRecusively($sourcesDir);
      $fs->remove(self::getRpmSourcesDir() . $versionName . '.tar.gz');
      $fs->remove($specFile);
      $fs->removeRecusively($buildDir);
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


  protected function getSummary()
  {
    return 'Rennomez vos séries';
  }

  protected function getMaintainer()
  {
    return 'Adrien GALLOU <ecrire@adrien-gallou.fr>';
  }

  protected function getLicense()
  {
    return 'GPL v2';
  }

  protected function getDescription()
  {
    return 'Description longue';
  }

  protected function getUrl()
  {
    return 'http://phpserenamer.googlecode.com/';
  }

  public static function getRpmSpecsDir()
  {
    return $_ENV['HOME'] . '/rpmbuild/SPECS/';
  }

  public static function getRpmSourcesDir()
  {
    return $_ENV['HOME'] . '/rpmbuild/SOURCES/';
  }

  public static function getRpmBuildDir()
  {
    return $_ENV['HOME'] . '/rpmbuild/BUILD/';
  }

  public static function getRpmRpmsDir($arch = 'i586')
  {
    return $_ENV['HOME'] . '/rpmbuild/RPMS/' . $arch . DIRECTORY_SEPARATOR;
  }

  public static function getRpmSrpmsDir()
  {
    return $_ENV['HOME'] . '/rpmbuild/SRPMS/';
  }

  public function getPostScript()
  {
    //TODO permettre d'utiliser les scripts du tag
    return file_get_contents('data/mandriva/_scripts/post');
  }

  public function getPreUnScript()
  {
    return file_get_contents('data/mandriva/_scripts/preun');
  }

  public function getPostUnScript()
  {
    return file_get_contents('data/mandriva/_scripts/postun');
  }

  /**
   *
   * @param  string $version
   * @param  string $release
   * @return string
   */
  private function getSpecFile($version, $release)
  {
    $summary     = $this->getSummary();
    $license     = $this->getLicense();
    $description = $this->getDescription();
    $url         = $this->getUrl();
    $post        = $this->getPostScript();
    $preun       = $this->getPreUnScript();
    $postun      = $this->getPostUnScript();

    $var = '';
    $var .= <<<EOF
Summary: $summary
Name: phpserenamer
Version: $version
Release: $release
Group: File tools
License: $license
AutoReqProv: no
Source: phpserenamer-$version-$release.tar.gz
BuildRoot: /var/tmp/%{name}-buildroot
Requires: php-gtk2
URL: $url

%description
$description

%prep
%setup -q

%build

%install
cp -rvf \$RPM_BUILD_DIR/phpserenamer-$version \$RPM_BUILD_ROOT

%clean
if( [ \$RPM_BUILD_ROOT != '/' ] ); then rm -rf \$RPM_BUILD_ROOT; fi;

%files
/.

%pre

%post
$post

%preun
$preun

%postun
$postun

EOF;
    return $var;
  }
}
