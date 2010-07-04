<?php
class srAccelGroup
{
  /**
   *
   * @var GtkAccelGroup
   */
  private static $instance = null;

  public function getInstance()
  {
    if(is_null(self::$instance))
    {
      self::$instance = new GtkAccelGroup();
    }
    return self::$instance;
  }

}