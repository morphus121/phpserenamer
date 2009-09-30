<?php
class srConfig
{

  private static $fichier = 'config/project.yml';

  public function get($clef)
  {
    $array = sfYaml::load(self::$fichier);
    $array = srUtils::flattenArray($array);
    $value = (array_key_exists('all_' . $clef, $array)) ? $array['all_' . $clef] : false;

    switch($clef)
    {
      case 'default_language':
        //If language not defined, try to determine it
        if(!strlen($value))
        {
          $value = srI18n::determineUserLanguage();
        }
        //if language not in accepted languages,
        if(!in_array($value, array_keys(srutils::getLanguages())))
        {
          $value = 'en';

        }
        break;
    }

    return $value;
  }

  /**
   *
   * @param $clef (sans le all)
   * @param $valeur
   * @return unknown_type
   */
  public function set($clef, $valeur)
  {
    $array = sfYaml::load(self::$fichier);
    $array = srUtils::flattenArray($array);
    $array['all_' . $clef] = $valeur;
    $array = srUtils::unFlattenArray($array);
    $fp = fopen(sfConfig::get('sr_root_dir') . self::$fichier, 'w');
    fwrite($fp, sfYaml::dump($array, 4));
  }


}