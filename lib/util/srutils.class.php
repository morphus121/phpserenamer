<?php
class srUtils
{
  public static function nameForFileSystem($nom)
  {
    return str_replace(array(':'), '', $nom);
  }

}