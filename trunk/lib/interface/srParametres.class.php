<?php
/**
 *
 * @author adriengallou
 *
 */
class srParametres extends GtkDialog
{

  const WIDTH  = 350;
  const HEIGHT = 200;
  const TITRE  = 'Paramètres';

  /**
   *
   * @var srParametres
   */
  private static $instance = null;

  /**
   *
   * @var GtkEntry
   */
  public $defaultFolder;

  /**
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    $this->set_title(self::TITRE);
    $this->set_default_size(self::WIDTH, self::HEIGHT);
    $this->set_border_width(0);
    $this->set_position(GTK::WIN_POS_CENTER);
    $this->set_resizable(false);

    $this->add_button(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL);
    $this->add_button(Gtk::STOCK_OK, Gtk::RESPONSE_OK);

    $box = $this->vbox;
    $box->pack_start($this->getReglageDossierParDefaut(), 0, 0);

    $this->connect_simple('destroy', array('srParametres', 'onDestroy'));
    $this->connect('close',array('srParametres','onDestroy'));
    $this->connect('response',array('srParametres','valider'));
  }

  /**
   *
   * @return GtkVButtonBox
   */
  public function getReglageDossierParDefaut()
  {
    $box = new GtkVButtonBox();

    $frame = new GtkFrame('Dossier par défaut');
    $this->defaultFolder = new GtkFileChooserButton('Choissiez le dossier par défaut', Gtk::FILE_CHOOSER_ACTION_SELECT_FOLDER);
    if(is_dir(srConfig::get('default_folder'))) $this->defaultFolder->set_current_folder(srConfig::get('default_folder'));
    $this->defaultFolder->set_size_request(320, -1);
    $frame->add($this->defaultFolder);

    $box->pack_start($frame);

    return $box;
  }

  /**
   *
   * @return srParametres
   */
  public static function getInstance()
  {
    if(is_null(self::$instance))
    {
      self::$instance = new srParametres();
    }
    return self::$instance;
  }

  /**
   *
   * @param $widget
   * @param $event
   * @return void
   */
  public function onDestroy($widget, $event)
  {
    self::$instance->destroy();
    self::$instance = null;
  }

  /**
   *
   * @param $widget
   * @param $event
   * @return void
   */
  public function valider($widget, $event)
  {
    if($event == Gtk::RESPONSE_OK)
    {
      srConfig::set('default_folder', self::getInstance()->defaultFolder->get_current_folder());
    }
    self::getInstance()->destroy();
  }

}