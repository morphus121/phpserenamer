<?php
class infosProviderFactory
{

  private static $acceptedSources = array(
    'serie' => array(
      'imdb'     => 'infosProviderSerieImdb',
    ),
  );

  /**
   *
   * @param $type serie ou film
   * @param $id
   * @return infosProviderSerieImdb|infosProviderFilmImdb
   */
  public static function createInfosProvider($type, $source, $id)
  {
    if(!array_key_exists($type,self::$acceptedSources))
    {
      throw new sfException(sprintf('Le type %s n\'est pas un type acceptable',$type));
    }

    if(!array_key_exists($source,self::$acceptedSources[$type]))
    {
      throw new sfException(sprintf('La source %s n\'est pas une source acceptable',$source));
    }

    return new self::$acceptedSources[$type][$source]($id);
  }
}