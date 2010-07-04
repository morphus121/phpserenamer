<?php
/**
 *
 * @author adriengallou
 *
 */
class srChangeAllEpisodesDialog extends GtkDialog
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
  protected $start;

  protected $pas;

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
    $this->connect('response',array('srChangeAllEpisodesDialog','callback_exit'));
    $this->set_icon_from_file(sfConfig::get('sr_logo'));
    $this->set_size_request(320, -1);
    $alignement = new GtkAlignment(0.5, 0.5, 0.5, 0.5);
    $this->ajoutElement($alignement, true, true);
    $this->start = $this->getPas();
    $this->pas   = $this->getPas();
    $this->ajoutElement(new GtkLabel(srUtils::getTranslation('Start')), true);
    $this->ajoutElement($this->start, true);
    $this->ajoutElement(new GtkLabel(srUtils::getTranslation('Increment')), true);
    $this->ajoutElement($this->pas, true);
    $this->show_all();
    $this->set_has_separator(false);
  }

  public function getPas()
  {
    $pas = GtkSpinButton::new_with_range(0, 999, 1);
    $pas->set_value(1);
    $pas->set_numeric(true);
    $pas->connect_simple('activate', array('srChangeAllEpisodesDialog', 'callback_exit'));
    return $pas;
  }

  public function getStart()
  {
    return $this->start->get_text();
  }

  public function getValeurPas()
  {
    return $this->pas->get_value();
  }

  /**
   *
   * @return srChangeAllSeasonsDialog
   */
  public function getInstance()
  {
    if(is_null(self::$instance))
    {
      self::$instance = new srChangeAllEpisodesDialog(srUtils::getTranslation('Change all episodes'), null, Gtk::DIALOG_MODAL);
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
    $start = srChangeAllEpisodesDialog::getInstance()->getStart();
    $pas   = srChangeAllEpisodesDialog::getInstance()->getValeurPas();
    srListeStore::getInstance()->changeAllEpisodes($start, $pas);
    srChangeAllEpisodesDialog::getInstance()->destroy();
    self::$instance = null;
  }

}