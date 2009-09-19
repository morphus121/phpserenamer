<?php
class srTreeView extends GtkTreeView
{
  private static $treeView = null;

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

  private static function getColonnes()
  {
    return array(
      srUtils::getTranslation('Tv show'),
      srutils::getTranslation('Season'),
      srUtils::getTranslation('Episode'),
      srUtils::getTranslation('Old name'),
      srUtils::getTranslation('New name')
    );
  }

  public function initialize()
  {
    //$this->set_fixed_height_mode(false);
    $this->connect('button-press-event', array('srTreeView','on_button'));

    $colonnes = self::getColonnes();
    for($i=0;$i<count($colonnes);$i++)
    {
      $renderer = new GtkCellRendererText();
      $renderer->set_property('editable',($i > 2) ? false : true);
      $renderer->connect('edited',  array('srTreeView','callback_text_cell_edited'), $i);
      $column = new GtkTreeViewColumn($colonnes[$i], $renderer, 'text', $i);
      $column->set_cell_data_func($renderer, array('srTreeView','format_col'), $i);
      $this->append_column($column);
    }

  }

  public function getSelectedPath()
  {
    $tab = self::getInstance()->get_selection()->get_selected_rows();
    return $tab[1][0][0];
  }

  //alterne les couleurss
  function format_col($column, $cell, $model, $iter, $col_num)
  {
    $path = $model->get_path($iter);
    $row_num = $path[0];
    $row_color = ($row_num%2==1) ? '#dddddd' : '#ffffff';
    if($model->get_value($iter, 6) == srListeStore::STATUS_ERROR)
    {
    	$row_color = '#930000';
    }
    $cell->set_property('cell-background', $row_color);
  }

  /*****************************************************************************
   * Callbacks
   ****************************************************************************/

  function callback_text_cell_edited($cellrenderertext, $path, $new_text, $colonne)
  {
    $model = srListeStore::getInstance();
    $iter = $model->get_iter($path);
    $old_text = $model->get($iter, $colonne);
    $old_text = $old_text[0];
    $model->set($iter,$colonne,$new_text);
    if($colonne == 0)
    {
      $model->renommerSerieSiMemeNom($old_text, $new_text);
    }
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

  public static function getMenuDefinition()
  {
    return array(
      srUtils::getTranslation('Add file'),
      srUtils::getTranslation('Add folder'),
      '<hr>',
      srUtils::getTranslation('Define tv show'),
      srUtils::getTranslation('Define season'),
      srUtils::getTranslation('Define episode'),
      '<hr>',
      srUtils::getTranslation('Delete'),
      srUtils::getTranslation('Empty list')
    );
  }

  private static $menu;

  private static function popup_menu($path, $col_titre, $event)
  {
    self::$menu = new GtkMenu();
    foreach(self::getMenuDefinition() as $menuitem_definition)
    {
      if($menuitem_definition == '<hr>')
      {
        self::$menu->append(new GtkSeparatorMenuItem());
      }
      else
      {
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
      case srUtils::getTranslation('Empty list'):
        srListeStore::getInstance()->clear();
	      break;
      case srUtils::getTranslation('Delete'):
        srListeStore::getInstance()->remove(srListeStore::getInstance()->get_iter($path));
        break;
      case srUtils::getTranslation('Add folder'):
      	srWindow::clicOuvrirDossier();
      	break;
      case srUtils::getTranslation('Add file'):
      	srWindow::clicOuvrirFichier();
      	break;
      case srUtils::getTranslation('Define tv show'):
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