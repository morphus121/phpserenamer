<?php
class srMenu
{
  //@see http://gtk.php.net/manual/en/gtk.enum.stockitems.php
  private static $menu_definition = array(
    'Aide' => array(
      'A propos',
    ),
    'Actions' => array(
      '' => array(
        'nom'   => '_Open',
        'image' => Gtk::STOCK_OPEN
      ),
      array(
        'nom'   => '_New',
        'image' => Gtk::STOCK_SAVE
      ),
      array(
        'nom'   => '_Close',
        'image' => Gtk::STOCK_CLOSE
      ),
      array(
        'nom'   => '_Quit',
        'image' => Gtk::STOCK_QUIT
      )
    ),
  );

  public static function getInstance()
  {
    return self::setup_menu(self::$menu_definition);
  }

	private function setup_menu($menus)
	{
    $menubar = new GtkMenuBar();
    foreach($menus as $toplevel => $sublevels)
    {
      $menubar->append($top_menu = new GtkMenuItem($toplevel));
      $menu = new GtkMenu();
      $top_menu->set_submenu($menu);
      foreach($sublevels as $submenu)
      {
        if ($submenu=='<hr>')
        {
          $menu->append(new GtkSeparatorMenuItem());
        }
        else
        {
          if(is_array($submenu))
          {
            $menu_item = new GtkImageMenuItem($submenu['image']);
          }
          else
          {
            $menu_item = new GtkMenuItem($submenu);
          }
          $menu->append($menu_item);
          $menu_item->connect('activate', array('srMenu','on_menu_select'));
        }
      }
    }
    return $menubar;
	}

	// process radio menu selection
	public function on_toggle($radio)
	{
	  $label = $radio->child->get_label();
	  $active = $radio->get_active();
	  echo("radio menu selected: $label\n");
	}

	// process menu item selection
	function on_menu_select($menu_item)
	{
	  $item = $menu_item->child->get_label();
	  echo "menu selected: $item\n";
	  if ($item=='_Quitter') Gtk::main_quit();
	}

}