<?php
class srConfig
{

  private static $fichier = 'config/project.yml';

  public function get($clef)
  {
    $array = sfYaml::load(self::$fichier);
    $array = srUtils::flattenArray($array);
    $value = $array['all_' . $clef];

    switch($clef)
    {
      case 'default_language':
        if(!strlen($value)) $value = 'en';
        if(!in_array($value, srutils::getLanguages())) $value = 'en';
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