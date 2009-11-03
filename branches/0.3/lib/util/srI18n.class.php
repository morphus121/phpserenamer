<?php
class srI18n
{

  public static function determineUserLanguage()
  {
    $lang = null;
    if(myFilesystem::isOsWindows())
    {
      $ret = null;
      $output = array();
      exec('reg QUERY "HKCU\Control Panel\International" /v sLanguage', $output, $ret);
      if($ret != 0 && !array_key_exists(4, $output))
      {
        return null;
      }

      $lang = strtolower(trim(array_pop(explode('REG_SZ', $output[4]))));
    }
    else
    {
      $lang = array_shift(explode('.',$_ENV["LANGUAGE"]));
    }
    return $lang;
  }

}