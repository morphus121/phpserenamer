<?php
class srConfig
{

  /**
   *
   * @var string
   */
  private static $fichier = 'config/project.yml';

  /**
   *
   * @var array
   */
  private static $config;

  /**
   *
   * @var int
   */
  private static $lastModification = 0;

  /**
   *
   * @return array
   */
  private static function getConfig($force = false)
  {
    $modification = filemtime(self::$fichier);
    if($modification != self::$lastModification || $force)
    {
      $array = sfYaml::load(self::$fichier);
      if(!is_null($array))
      {
        $array = srUtils::flattenArray($array);
      }
      self::$config           = $array;
      self::$lastModification = $modification;
    }
    return self::$config;
  }

  /**
   *
   * @param  string $clef
   * @return string
   */
  public function get($clef)
  {
    $array = self::getConfig();
    $value = (array_key_exists('all_' . $clef, $array)) ? $array['all_' . $clef] : false;

    switch($clef)
    {
      case 'default_language':
        //If language not defined, try to determine it
        if(!strlen($value))
        {
          $value = srI18n::determineUserLanguage();
        }

        //if language not in accepted languages we use english
        //we also convert code to "real code"
        try
        {
          $value = srUtils::getRealCode($value);
        }
        catch(sfException $ex)
        {
          $value = 'en';
        }
        break;
    }

    return $value;
  }

  /**
   *
   * @param  string $clef (sans le all)
   * @param  mixed  $valeur
   * @return mixed
   */
  public function set($clef, $valeur)
  {
    $array = self::getConfig(true);
    $array['all_' . $clef] = $valeur;
    $array = srUtils::unFlattenArray($array);
    $fp = fopen(sfConfig::get('sr_root_dir') . self::$fichier, 'w');
    fwrite($fp, sfYaml::dump($array, 4));
  }

}