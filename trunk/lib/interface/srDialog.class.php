<?php
/**
 *
 * @author adriengallou
 *
 */
class srDialog extends GtkDialog
{

  private $serieee = null;
  //TODO ut$iliser le model
  private $series;
  private $combobox;

  public function __construct($title = null, $parent = null, $flags = null)
  {
    parent::__construct($title, $parent, $flags);
    $this->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $this->set_size_request(320, self::getHeightFromWidth(320));
    $this->add_button(Gtk::STOCK_OK, Gtk::RESPONSE_OK);
    $this->set_resizable(false);
    $this->connect('response',array('srDialog','validerDialog'));
    $this->set_icon_from_file(sfConfig::get('sr_logo'));
    $this->vbox->set_spacing(32);
    $alignement = new GtkAlignment(0.5, 0.5, 0.5, 0.5);
    $this->ajoutElement($alignement, true, true);

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

  public function setSerie($serie, array $series)
  {
    $this->ajoutElement(new GtkLabel('Pour la série :'), true);
    $this->ajoutElement(new GtkLabel($serie), true);

    $this->combobox = self::getComboBoxSeries($series);
    $this->ajoutElement($this->combobox, false);
    $this->serieee = $serie;
    $this->series = $series; //TODO ne pas avoir à faire cela
    $this->show_all();
    $this->set_has_separator(false);
  }

  public function run()
  {
    if(is_null($this->serieee))
    {
      throw new sfException('faire un setSerie avant de faire un run');
    }
    parent::run();
  }

  public static function getComboBoxSeries(array $series)
  {
    //TODO get_active_iter() pour récupération ensuite
    $model = new GtkListStore(GObject::TYPE_STRING);
    $combobox = new GtkComboBox($model);
    $cellRenderer = new GtkCellRendererText();
    $combobox->pack_start($cellRenderer);
    $combobox->set_attributes($cellRenderer, 'text', 0);
    $combobox->set_size_request(320, 40);
    $model->clear();
    foreach($series as $choice) {
      $model->append(array($choice));
    }
    $combobox->set_active_iter($model->get_iter(0));
    return $combobox;
  }



  public function validerDialog($widget, $event)
  {
    correspondanceNoms::getInstance()->setSerie(srWindow::getInstance()->getSelectedProvider(),$this->serieee,$this->series[$this->combobox->get_active()]);
    $this->destroy();
  }
}