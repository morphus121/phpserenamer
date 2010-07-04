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
    if(!file_exists(self::dealWithEncoding($filename)))
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
      if(sfToolkit::isUTF8($filename))
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
    if (self::isOsWindows())
    {
      if (!is_file($folder))
      {
        exec(sprintf('rmdir /S /Q %s', $folder));
      }
      else
      {
        exec(sprintf('del /Q %s', $folder));
      }
    }
    else
    {
      exec(sprintf('rm -rf %s', $folder));
    }
  }

  /**
   * Renames a file.
   *
   * @param string $origin  The origin filename
   * @param string $target  The new filename
   */
  public function rename($origin, $target)
  {
    $origin = self::dealWithEncoding($origin);
    $target = self::dealWithEncoding($target);

    $this->logSection('rename', $origin.' > '.$target);
    return rename($origin, $target);
  }

  /**
   * Put content into a file
   *
   * @param  string $filename
   * @param  string $content
   * @return void
   */
  public function filePutContent($filename, $content)
  {
    $this->logSection('file+', $filename);
    file_put_contents($filename, $content);
  }


}