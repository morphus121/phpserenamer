<?php
class srGtk extends Gtk
{
  public static function main()
  {
	  $w = new srWindow();
		$w->show_all();
    parent::main();
  }
}