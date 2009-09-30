<?php
/**
 *
 * @author adriengallou
 *
 */
class srUtils
{

  const VERSION = '0.2.0';

  /**
   * Retourne le numéro de version de l'application
   *
   * @return string
   */
  public static function getVersion()
  {
    return self::VERSION;
  }

  /**
   * Enlève d'une chaine tout les caractères interdits dans un nom de fichier
   *
   * @param  string $nom
   * @return string
   */
  public static function nameForFileSystem($nom)
  {
    return str_replace(
    array("/", 'ñ'),
    array("-", 'n'),
    str_replace(array(':', '?'), '', $nom)
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

  public static function flattenArray(array $array, $keyBegin = '')
  {
    $flat = array();
    foreach ($array as $key => $value)
    {
      $laClef = ($keyBegin == '') ? $key : $keyBegin . '_' . $key;
      if (is_array($value)) $flat = array_merge($flat, self::flattenArray($value, $laClef));
      else $flat[$laClef] = $value;
    }
    return $flat;
  }

  public static function unFlattenArray(array $array)
  {
    $tab = array();
    foreach($array as $clef => $valeur)
    {
      $tabClef  = explode('_', $clef);
      $firstKey = array_shift($tabClef);
      $moinsUn  = count($tabClef) - 1;
      $tempTab  = array($tabClef[$moinsUn] => $valeur);
      for($i = $moinsUn-1; $i >= 0; $i--)
      {
        $tempTab = array($tabClef[$i] => $tempTab);
      }
      $tab[$firstKey] = (isset($tab[$firstKey])) ? array_merge_recursive($tab[$firstKey], $tempTab) : $tempTab;
    }
    return $tab;
  }

  public static function getTranslation($var, $dictionary = 'messages')
  {
    return sfContext::getInstance()->getI18N()->__($var, array(), $dictionary);
  }

  public static function getLanguages()
  {
    return array(
      'ru' => 'russian',
      'fr' => 'french',
      'en' => 'english',
    );
  }

  public static function getLanguageNameFromCode($code)
  {
    $languages = self::getLanguages();
    if(!array_key_exists($code, $languages))
    {
      throw new sfException('Code not found');
    }
    return $languages[$code];
  }

  public static function getCodeFromLanguage($language, $cs = false)
  {
    return array_search(($cs) ? $language : strtolower($language), self::getLanguages());
  }

  public static function getGoldenNumber()
  {
    return (1+sqrt(5))/2;
  }

}