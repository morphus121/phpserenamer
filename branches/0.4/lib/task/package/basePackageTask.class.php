<?php
abstract class basePackageTask extends myBaseTask
{

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