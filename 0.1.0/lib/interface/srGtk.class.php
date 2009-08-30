<?php
class srGtk extends Gtk
{
  public static function main()
  {
    $w = srWindow::getInstance();
    $w->show_all();
    parent::main();
  }
}