<?php
class myFinder extends sfFinder
{
  //TODO dans un config symfony ?
  private static $extensionsAcceptes = array(
    'mkv',
    'avi',
    'ogm',
    'srt'
  );

  private static function initialize(myFinder $finder)
  {
    foreach(self::$extensionsAcceptes as $extension)
    {
      $finder->name(sprintf('*.%s', $extension));
    }
    return $finder;
  }

  public static function type($name)
  {
    $finder = new myFinder();
    self::initialize($finder);
    return $finder->setType($name);
  }

}