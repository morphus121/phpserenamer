<?php
class srTreeView extends GtkTreeView
{
  private static $treeView = null;

  /**
   *
   * @return srTreeView
   */
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
    $this->set_enable_search(false);

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
    //get the row and column
    $path_array = $view->get_path_at_pos($event->x, $event->y);
    $path       = $path_array[0][0];
    $col        = $path_array[1];

    //Left mouse button
    if ($event->button==1)
    {
      if($event->type == Gdk::_2BUTTON_PRESS)
      {
        if(is_object($col) && in_array($col->get_title(), array(
          srUtils::getTranslation('Old name'),
          srUtils::getTranslation('New name'),
        )))
        {
          self::openFileFromPath($path);
        }
      }
      return false;
    }
    //Middle mouse button
    if ($event->button == 2)
    {
      return true;
    }
    //Right mouse button
    if ($event->button == 3)
    {
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
      srUtils::getTranslation('Open file'),
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
        self::getInstance()->focusCell($path, 0);
        break;
      case srUtils::getTranslation('Define season'):
        self::getInstance()->focusCell($path, 1);
        break;
      case srUtils::getTranslation('Define episode'):
        self::getInstance()->focusCell($path, 2);
      case srUtils::getTranslation('Open file'):
        self::openFileFromPath($path);
        break;
      default:
       echo "popup menu selected: $item\n";
    }

  }

  /**
   *
   * @param  $path
   * @param  $numColonne
   * @return void
   */
  public function focusCell($path, $numColonne)
  {
    $column = $this->get_columns();
    $cellRenderers = $column[$numColonne]->get_cell_renderers();
    $this->set_cursor_on_cell($path, $column[$numColonne], $cellRenderers[0], true);
  }

  /**
   * Open the the file from the gtk path
   *
   * @param  $path path gtk
   * @return void
   */
  private static function openFileFromPath($path)
  {
    $dossier = srListeStore::getInstance()->get_value(srListeStore::getInstance()->get_iter($path), 5);
    $fichier = srListeStore::getInstance()->get_value(srListeStore::getInstance()->get_iter($path), 3);
    $fs = new myFilesystem();
    $fs->openFile($dossier . DIRECTORY_SEPARATOR . $fichier);
  }

}