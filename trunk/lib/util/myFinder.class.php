<?php
class myFinder extends sfFinder
{
  private static function initialize(myFinder $finder)
  {
    $finder->name('*.mkv');
    $finder->name('*.avi');
    return $finder;
  }

  public static function type($name)
  {
    $finder = new myFinder();
    self::initialize($finder);
    return $finder->setType($name);
  }

}