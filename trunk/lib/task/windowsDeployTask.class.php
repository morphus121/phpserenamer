<?php
/**
 *
 * @author agallou
 *
 */
class windowsDeployTask extends sfBaseTask
{

  protected function configure()
  {
    $this->addArgument('version',sfCommandArgument::REQUIRED, 'Tag à utiliser');

    $this->namespace           = 'sr';
    $this->name                = 'windows-deploy';
    $this->briefDescription    = 'Création de l\'installation windows';
    $this->detailedDescription = 'Création de l\'installation windows';
  }
  

  protected function execute($arguments = array(), $options = array())
  {
    $tortoiseProcBin = 'C:\PROGRA~1\TortoiseSVN\bin\TortoiseProc.exe';
    $innoSetupBin    = 'C:\PROGRA~1\INNOSE~1\Compil32.exe';
    $url             = sprintf('http://phpserenamer.googlecode.com/svn/tags/%s/%s', $this->majorFromVersion($arguments['version']), $arguments['version']);
    $pathOfExport    = sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
    $fileOnGtkPhpNet = 'php-gtk-2.0.1-win32-nts.zip';
    
    //Vérification de l'existence de tortoise
    if(!file_exists($tortoiseProcBin))
    {
      throw new sfException('Executable tortoise non trouvé');
    }
    
    //Vérification de innoSetup
    if(!file_exists($innoSetupBin))
    {
      throw new sfException('Executable innoSetup non trouvé');
    }
    
    $fs = $this->getFilesystem();
    
    //Création du dossier temporaire
    $fs->mkdirs('tmp/');
    
    //Export  des sources
    $this->logSection('Export de la version', $arguments['version']);
  	$cmd = sprintf(
      //bizzarement l'export ne fonctionne  pas, checkout à la place. 
      //Pas de problème car les dossiers .svn sont ignorés par innosetup
      //'%s /command:export   /closeonend:1 /notempfile /url:"%s" /path:"%s"',
      '%s /command:checkout /closeonend:1 /notempfile /url:"%s" /path:"%s"',
      $tortoiseProcBin,
      $url,
      $pathOfExport
    );
	
    passthru($cmd);
    
    //Récupération de php-gtk
    $this->logSection('Récupération de phpgtk-2', $fileOnGtkPhpNet);
    $browser = new sfWebBrowser();
    $browser->get(sprintf('http://gtk.php.net/do_download.php?download_file=%s', $fileOnGtkPhpNet));
    file_put_contents('tmp.php-gtk.zip', $browser->getResponseText());
    
    //extraction de l'archive php-gtk
    $this->logSection('Extraction de phpgtk-2', $fileOnGtkPhpNet);
    include(sfConfig::get('sf_root_dir') . '\lib\vendor\symfony\plugins\sfPropelPlugin\lib\vendor\phing\lib\Zip.php');
    $zip = new Archive_Zip('tmp.php-gtk.zip');
    $zip->extract(array('add_path' => './tmp/lib/vendor/'));
    
    //suppression de l'archive
    $fs->remove('tmp.php-gtk.zip');
    
    //copier le fichier des images du bouton depuis le dossier data dans le dossier tmp
    $fs->copy('data/windows/php-gtk-2.0.1/gtkrc', 'tmp/lib/vendor/php-gtk2/etc/gtk-2.0/gtkrc');
    //Création du script
    file_put_contents('script.iss', $this->getInnoSetupScript($arguments['version']));
    //Création de l'installation
    $cmd = sprintf('%s /cc %s', $innoSetupBin, 'script.iss');
    $fs->sh($cmd);
    
    //suppression du script
    $fs->remove('script.iss');
    
    //Suppresion du répertoire temporaire
    $fs->sh('rmdir /S /Q tmp');
    
    //On déplace l'executable à la racine
    $outputExe = sprintf('phpserenamer-%s.exe', $arguments['version']);
    $fs->copy('./Output/' . $outputExe, $outputExe);
    $fs->remove('./Output/' . $outputExe);
    $fs->remove('./Output');
  }

  protected function majorFromVersion($version)
  {
    $tab = explode('.', $version);
    return $tab[0];
  }


  protected function getInnoSetupScript($version)
  {
    $var = '';
    $var .= <<<EOF
[Setup]
AppId={{856EE7F8-8BF2-4C41-8F91-DD667BB8C922}
AppName=phpserenamer

EOF;
    $var .= sprintf('AppVerName=phpserenamer %s', $version) . "\n";
    $var .= <<<EOF
AppPublisherURL=http://code.google.com/p/phpserenamer/
AppSupportURL=http://code.google.com/p/phpserenamer/
AppUpdatesURL=http://code.google.com/p/phpserenamer/
DefaultDirName={pf}\phpserenamer
DefaultGroupName=phpserenamer

EOF;
    $var .= sprintf('OutputBaseFilename=phpserenamer-%s', $version) . "\n";
    $var .= <<<EOF
Compression=lzma
SolidCompression=yes

[Languages]
Name: "french"; MessagesFile: "compiler:Languages\French.isl"

[Files]

EOF;
    $var .= sprintf(
      'Source: "%s*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs',
      './tmp/'
    ) . "\n";
    $var .= <<<EOF
[Icons]
Name: "{group}\phpserenamer"; Filename: "{app}\lib\vendor\php-gtk2\php-win.exe"; Parameters: "{app}\symfony main"; IconFilename: "{app}\data\logo\favicon.ico"

[Registry]
Root: HKCR; Subkey: "Directory\shell\phpserenamer"; ValueType: string; ValueName: ""; ValueData: "phpserenamer"; Flags: createvalueifdoesntexist
Root: HKCR; Subkey: "Directory\shell\phpserenamer\command"; ValueType: string; ValueName: ""; ValueData: """{app}\lib\\vendor\php-gtk2\php-win.exe"" ""{app}\symfony"" main ""%1"""; Flags: createvalueifdoesntexist
EOF;
return $var;
  }
}