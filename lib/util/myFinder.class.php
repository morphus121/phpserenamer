<?php
class myFinder extends sfFinder
{
  //TODO dans un config symfony ?
  private static $extensionsAcceptes = array(
    'mkv',
    'avi',
    'ogm',
    'srt',
    'mp4',
    'vob',
  );

  private static function initialize(myFinder $finder)
  {
  	$extensions = array_merge(self::$extensionsAcceptes, array_map('strtoupper', self::$extensionsAcceptes));
    foreach($extensions as $extension)
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