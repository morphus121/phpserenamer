<?php
/**
 *
 * @author adriengallou
 *
 */
abstract class myBaseTask extends sfBaseTask
{

  /**
   * Returns the filesystem instance.
   *
   * @return myFilesystem A myFilesystem instance
   */
  public function getFilesystem()
  {
    if (!isset($this->filesystem))
    {
      if (is_null($this->commandApplication) || $this->commandApplication->isVerbose())
      {
        $this->filesystem = new myFilesystem($this->dispatcher, $this->formatter);
      }
      else
      {
        $this->filesystem = new myFilesystem();
      }
    }

    return $this->filesystem;
  }

}
