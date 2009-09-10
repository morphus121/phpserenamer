<?php
class srConfig
{

  private static $fichier = 'config/project.yml';

  public function get($clef)
  {
    $array = sfYaml::load(self::$fichier);
    $array = srutils::flattenArray($array);
    return $array['all_' . $clef];
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
    $array = srutils::flattenArray($array);
    $array['all_' . $clef] = $valeur;
    $array = srUtils::unFlattenArray($array);
    $fp = fopen(sfConfig::get('sr_root_dir') . self::$fichier, 'w');
    fwrite($fp, sfYaml::dump($array, 4));
  }


}