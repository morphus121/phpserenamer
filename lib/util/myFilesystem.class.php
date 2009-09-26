<?php
/**
 *
 * @author adriengallou
 *
 */
class myFilesystem extends sfFilesystem
{

  /**
   *
   * @param  $filename
   * @return void
   */
  public function openFile($filename)
  {
    if(!file_exists($filename))
    {
      throw new sfException('File not found');
    }
    if(self::isOsWindows())
    {
      pclose(popen('"' . $filename . '"', 'r'));
    }
    elseif(!is_null($opener = self::getLinuxFileOpener()))
    {
      $this->sh(sprintf('%s "%s" > /dev/null &', $opener, $filename));
    }
  }

  /**
   *
   * @return string|null
   */
  private static function getLinuxFileOpener()
  {
    $opener = null;
    if(file_exists('/usr/bin/gnome-open'))
    {
      $opener = 'gnome-open';
    }
    elseif (file_exists('/usr/bin/kde-open'))
    {
      $opener = 'kde-open';
    }
    return $opener;
  }

  /**
   *
   * @return boolean
   */
  public static function isOsWindows()
  {
    return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
  }

  /**
   * Remove a folder recursively without asking any question
   *
   * @param  $folder
   * @return void
   */
  public function removeRecusively($folder)
  {
    $this->logSection('dir-', $folder);
    exec(sprintf('rm -rf %s', $folder));
  }

}