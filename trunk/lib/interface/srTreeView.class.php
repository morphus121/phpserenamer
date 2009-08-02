<?php
class srTreeView extends GtkTreeView
{
  private static $treeView = null;

  private static $colonnes = array(
    'Serie',
    'Saison',
    'Episode',
    'Ancien nom',
    'Nouveau nom'
  );

  public static function getInstance()
  {
    if(is_null(self::$treeView))
    {
      $model = srListeStore::getInstance();
      self::$treeView = new srTreeView($model);
      self::$treeView->initialize();
    }
    return self::$treeView;
  }

  public function initialize()
  {
    //$this->set_fixed_height_mode(false);
    $this->connect('button-press-event', array('srTreeView','on_button'));

    for($i=0;$i<count(self::$colonnes);$i++)
    {
      $renderer = new GtkCellRendererText();
      $renderer->set_property('editable',($i > 2) ? false : true);
      $renderer->connect('edited',  array('srTreeView','callback_text_cell_edited'), $i);
      $column = new GtkTreeViewColumn(self::$colonnes[$i], $renderer, 'text', $i);
      $column->set_cell_data_func($renderer, array('srTreeView','format_col'), $i);
      $this->append_column($column);
    }

  }

  //alterne les couleurss
  function format_col($column, $cell, $model, $iter, $col_num)
  {
    $path = $model->get_path($iter);
    $row_num = $path[0];
    $row_color = ($row_num%2==1) ? '#dddddd' : '#ffffff';
    $cell->set_property('cell-background', $row_color);
  }

  /*****************************************************************************
   * Callbacks
   ****************************************************************************/

  function callback_text_cell_edited($cellrenderertext, $path, $new_text, $colonne)
  {
    $model = srListeStore::getInstance();
    $iter = $model->get_iter($path);
    $model->set($iter,$colonne,$new_text);
  }

  function on_button($view, $event)
  {
    if ($event->button==1) return false; // note 2
    if ($event->button==2) return true; // do nothing
    if ($event->button==3) { // it's the right mouse button!
        // get the row and column
        $path_array = $view->get_path_at_pos($event->x, $event->y);
        $path = $path_array[0][0];
        $col = $path_array[1];
        if(is_object($col)) //Seulement si l'on clique sur du texte
        {
          $col_title = $col->get_title();
          self::popup_menu($path, $col_title, $event); // displays the popup menu
        }
        return false;
    }
}


	/*****************************************************************************
	 * Gestion du menu
	 ****************************************************************************/

  private static $menu_definition = array(
    'Ajouter fichier',
    'Ajouter dossier',
    '<hr>',
    'Definir Serie',
    'Definir Saison',
    'Definir Episode',
    '<hr>',
    'Supprimer',
    'Vider la liste'
  );
  private static $menu;

  private static function popup_menu($path, $col_titre, $event)
  {
    self::$menu = new GtkMenu();
    foreach(self::$menu_definition as $menuitem_definition) {
        if ($menuitem_definition=='<hr>') {
            self::$menu->append(new GtkSeparatorMenuItem());
        } else {
            //$menu_item = new GtkImageMenuItem($menuitem_definition);
            $menu_item = new GtkMenuItem($menuitem_definition);
            self::$menu->append($menu_item);
            $menu_item->connect('activate', array('srTreeView','on_popup_menu_select'), $path);
        }
    }
    self::$menu->show_all();
    self::$menu->popup();
  }
	// process popup menu item selection
	function on_popup_menu_select($menu_item, $path)
  {
    $item = $menu_item->child->get_label();
	  switch($item)
	  {
      case 'Vider la liste':
        srListeStore::getInstance()->clear();
	      break;
      case 'Supprimer':
        srListeStore::getInstance()->remove(srListeStore::getInstance()->get_iter($path));
        break;
      case 'Definir Serie':
        //var_dump(srTreeView::getInstance()->get_selection());
        //var_dump(srTreeView::getInstance()->get_dest_row_at_pos($path, 0));
        //srTreeView::getInstance()->get_column($path)->focus_cell(0);
        //@see http://gtk.php.net/manual1/fr/html/gdk.enum.gdkeventtype.html (pour le -1)
        //$renderers = srTreeView::getInstance()->get_column(0)->get_cell_renderers();
        //$renderers[0]->activate(new GdkEvent(-1),$renderers[0], 0);
        //var_dump(srListeStore::getInstance()->get_value(srListeStore::getInstance()->get_iter($path), 0));
        break;
	    default:
	     echo "popup menu selected: $item\n";
	  }

	}


}