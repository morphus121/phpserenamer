<?php
/**
 *
 * @author adriengallou
 *
 */
class srChangeAllSeasonsDialog extends GtkDialog
{

  /**
   *
   * @var srChangeAllSeasonsDialog
   */
  static $instance = null;

  /**
   *
   * @var string
   */
  protected $season;

  /**
   *
   * @param $title
   * @param $parent
   * @param $flags
   * @return void
   */
  public function __construct($title = null, $parent = null, $flags = null)
  {
    parent::__construct($title, $parent, $flags);
    $this->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $this->add_button(Gtk::STOCK_OK, Gtk::RESPONSE_OK);
    $this->set_resizable(false);
    $this->connect('response',array('srChangeAllSeasonsDialog','callback_exit'));
    $this->set_icon_from_file(sfConfig::get('sr_logo'));
    $this->set_size_request(320, -1);
    $alignement = new GtkAlignment(0.5, 0.5, 0.5, 0.5);
    $this->ajoutElement($alignement, true, true);
    $this->season = $this->getInputBox();
    $this->ajoutElement($this->getLabel(), true);
    $this->ajoutElement($this->season, true);
    $this->show_all();
    $this->set_has_separator(false);
  }

  protected function getInputBox()
  {
    $seriesNumber = new GtkEntry();
    $seriesNumber->connect_simple('activate', array('srChangeAllSeasonsDialog', 'callback_exit'));
    $seriesNumber->set_text(1);
    return $seriesNumber;
  }

  public function getLabel()
  {
    $label = new GtkLabel(srUtils::getTranslation('Change all seasons'));
    return $label;
  }

  public function getSeason()
  {
    return $this->season->get_text();
  }

  /**
   *
   * @return srChangeAllSeasonsDialog
   */
  public function getInstance()
  {
    if(is_null(self::$instance))
    {
      self::$instance = new srChangeAllSeasonsDialog(srUtils::getTranslation('Change all seasons'), null, Gtk::DIALOG_MODAL);
    }
    return self::$instance;
  }

  public function getHeightFromWidth($width)
  {
    return  $width / srUtils::getGoldenNumber();
  }

  private function ajoutElement($element, $expand = true)
  {
    $top_area = $this->vbox;
    $top_area->pack_start($element, $expand, 0, 0);
  }

  /**
   *
   * @return void
   */
  public function callback_exit()
  {
    srListeStore::getInstance()->changeAllSeasons(srChangeAllSeasonsDialog::getInstance()->getSeason());
    srChangeAllSeasonsDialog::getInstance()->destroy();
    self::$instance = null;
  }

}