<?php
/**
 *
 * @author adriengallou
 *
 */
class srUtils
{
	/**
	 * Enlève d'une chaine tout les caractères interdits dans un nom de fichier
	 *
	 * @param  string $nom
	 * @return string
	 */
  public static function nameForFileSystem($nom)
  {
    return str_replace(
      array("/"),
      array("-"),
      str_replace(array(':'), '', $nom)
    );
  }

  /**
   * Retourne la liste des providers en fonction des classes présentes dans le
   * dossier lib
   *
   * @return array[] => string
   */
  public static function getProvidersFromClassesNames()
  {
    $files = sfFinder::type('file')
      ->prune('plugins')
      ->prune('vendor')
      ->prune('skeleton')
      ->prune('default')
      ->name('infosProviderSerie*\.class\.php')
      ->in(sfConfig::get('sf_lib_dir'))
    ;
    $providers = array();
    foreach($files as $filePath)
    {
    	$matches = array();
    	if(preg_match('/infosProviderSerie(.*)\.class/', ($file = pathinfo($filePath, PATHINFO_FILENAME)), $matches))
    	{
    	 if(($name = $matches[1]) != 'Base')
	    	{
	    		$providers[] = strtolower($name);
	    	}
    	}
    }
    return $providers;
  }

}