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
      pclose(popen(sprintf('"%s"', self::dealWithEncoding($filename)), 'r'));
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
   * Returns the good string path for the filesystem without
   * taking care of the encoding passed in parameter
   *
   * @param  string $filename
   * @return string
   */
  private static function dealWithEncoding($filename)
  {
    if(self::isOsWindows())
    {
      if(!file_exists($filename) && file_exists(utf8_decode($filename)))
      {
        return utf8_decode($filename);
      }
    }
    return $filename;
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