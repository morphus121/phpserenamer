<?php
/**
 *
 * @author adriengallou
 *
 */
class srMenu
{

  /**
   * @see http://gtk.php.net/manual/en/gtk.enum.stockitems.php (images)
   * @see http://gtk.php.net/manual/en/appendix.keysyms.php (shortcuts)
   * @return array
   */
  public static function getMenuDefinition()
  {
    return array(
      srUtils::getTranslation('_Actions', 'menu') => array(
        array(
          'nom'   => srUtils::getTranslation('Add _folder', 'menu'),
          'image' => Gtk::STOCK_OPEN,
          'accel' => array('activate', Gdk::KEY_d, Gdk::CONTROL_MASK, 7),
        ),
        array(
          'nom'   => srUtils::getTranslation('Add f_ile', 'menu'),
          'image' => Gtk::STOCK_NEW,
          'accel' => array('activate', Gdk::KEY_f, Gdk::CONTROL_MASK, 7),
        ),
        array(
          'nom'   => srUtils::getTranslation('_Delete Element', 'menu'),
          'image' => Gtk::STOCK_CLOSE,
          'accel' => array('activate', Gdk::KEY_e, Gdk::CONTROL_MASK, 7),
        ),
        array(
          'nom'   => srUtils::getTranslation('_Quit', 'menu'),
          'image' => Gtk::STOCK_QUIT,
          'accel' => array('activate', Gdk::KEY_q, Gdk::CONTROL_MASK, 7),
        )
      ),
      srUtils::getTranslation('_Edit', 'menu')    => array(
        array(
          'nom'   => srUtils::getTranslation('_Change all seasons', 'menu'),
          'accel' => array('activate', Gdk::KEY_c, Gdk::CONTROL_MASK, 7),
        ),
        array(
          'nom'   => srUtils::getTranslation('_Change all episodes', 'menu'),
          'accel' => array('activate', Gdk::KEY_v, Gdk::CONTROL_MASK, 7),
        ),
        array(
          'nom'   => srUtils::getTranslation('_Parameters', 'menu'),
          'image' => Gtk::STOCK_PROPERTIES
        )
      ),
      srUtils::getTranslation('_Help', 'menu')    => array(
        array(
          'nom'   => srUtils::getTranslation('_About', 'menu'),
          'image' => Gtk::STOCK_DIALOG_QUESTION,
          'accel' => array('activate', Gdk::KEY_F1, 0, 7),
        )
      ),
    );
  }

  public static function getInstance()
  {
    return self::setup_menu(self::getMenuDefinition());
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
            $menu_item = new GtkImageMenuItem($submenu['nom']);
            $menu_item->set_image(GtkImage::new_from_stock($submenu['image'], Gtk::ICON_SIZE_MENU));
          }
          else
          {
            $menu_item = new GtkMenuItem($submenu);
          }
          if(array_key_exists('accel', $submenu) && count($submenu['accel']) == 4)
          {
            $menu_item->add_accelerator($submenu['accel'][0], srAccelGroup::getInstance(), $submenu['accel'][1], $submenu['accel'][2], $submenu['accel'][3]);
          }
          $menu->append($menu_item);
          $menu_item->connect('activate', array('srMenu','on_menu_select'));
        }
      }
    }
    return $menubar;
  }

  // process menu item selection
  function on_menu_select($menu_item)
  {
    $item = $menu_item->child->get_label();
    switch($item)
    {
      case srUtils::getTranslation('Add _folder', 'menu'):
        srWindow::clicOuvrirDossier();
        break;
      case srUtils::getTranslation('Add f_ile', 'menu'):
        srWindow::clicOuvrirFichier();
        break;
      case srUtils::getTranslation('_Delete Element', 'menu'):
        srListeStore::getInstance()->remove(srListeStore::getInstance()->get_iter(srTreeView::getSelectedPath()));
        break;
      case srUtils::getTranslation('_Quit', 'menu'):
        Gtk::main_quit();
        break;
      case srUtils::getTranslation('_Parameters', 'menu'):
        $p = srParametres::getInstance();
        $p->show_all();
        break;
      case srUtils::getTranslation('_Change all seasons', 'menu'):
        $d = srChangeAllSeasonsDialog::getInstance();
        $d->run();
        break;
      case srUtils::getTranslation('_Change all episodes', 'menu'):
        $d = srChangeAllEpisodesDialog::getInstance();
        $d->run();
        break;
      case srUtils::getTranslation('_About', 'menu'):
        $dlg = new GtkAboutDialog();
        $dlg->set_name('phpserenamer');
        $dlg->set_version(srUtils::getVersion());
        $dlg->set_comments(
        srUtils::getTranslation('TV show name', 'about') . ' : %n' . "\n"
        . srUtils::getTranslation('Season number', 'about') . ' : %s' . "\n"
        . srUtils::getTranslation('Season number with zero before if less than 10', 'about') . ' : %j' . "\n"
        . srUtils::getTranslation('Episode number', 'about') . ' : %e' . "\n"
        . srUtils::getTranslation('Episode number with zero before if less than 10', 'about') . ' : %k' . "\n"
        . srUtils::getTranslation('Episode name', 'about') . ' : %t' . "\n"
        );
        $dlg->set_license("GPL v2");
        $dlg->set_website('http://code.google.com/p/phpserenamer/');
        $dlg->set_icon_from_file(sfConfig::get('sr_logo'));
        $dlg->set_logo(GdkPixbuf::new_from_file(sfConfig::get('sr_logo')));
        $dlg->set_authors(array('Adrien Gallou <adriengallou@gmail.com>'));
        $translators = array(
          'Russian' => 'Khilo Max <hilomax@gmail.com>',
          'French'  => 'Adrien Gallou <adriengallou@gmail.com>',
        );
        $str = '';
        foreach($translators as $language => $translator)
        {
          $str .= sprintf("%s - %s\n", $language, $translator);
        }
        $dlg->set_translator_credits($str);
        $dlg->run();
        $dlg->destroy();
        break;
    }
  }

}