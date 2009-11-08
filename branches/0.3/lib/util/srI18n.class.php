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
      if($ret != 0 && !(array_key_exists(4, $output) || array_key_exists(2, $output)))
      {
        return null;
      }

      $lang = strtolower(trim(array_pop(explode('REG_SZ', $output[4]))));
      if(!strlen(trim($lang)))
      {
        $lang = strtolower(trim(array_pop(explode('REG_SZ', $output[2]))));
      }
    }
    else
    {
      if(array_key_exists('LANGUAGE', $_ENV))
      {
        $lang = array_shift(explode('.',$_ENV['LANGUAGE']));
      }
      elseif(array_key_exists('LANG', $_ENV))
      {
        $lang = array_shift(explode('.',$_ENV['LANG']));
      }
    }
    return $lang;
  }

}