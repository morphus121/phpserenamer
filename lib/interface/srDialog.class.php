<?php
class srDialog extends GtkDialog
{

  private $serieee = null;
  //TODO ut$iliser le model
  private $series;
  private $combobox;

  public function __construct($title = null)
  {
    parent::__construct($title);
    $this->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $this->set_size_request(400, 200);
    $this->add_button(Gtk::STOCK_OK, Gtk::RESPONSE_OK);
    $this->connect('response',array('srDialog','validerDialog'));

  }

  private function ajoutElement($element, $expand = true)
  {
    $top_area = $this->vbox;
    $top_area->pack_start($element, $expand);
  }

  public function setSerie($serie, array $series)
  {
    $this->ajoutElement(new GtkLabel('pour la série : ' . $serie),false);
    $this->combobox = self::getComboBoxSeries($series);
    $this->ajoutElement($this->combobox);
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

  private static function getComboBoxSeries(array $series)
  {
    //TODO get_active_iter() pour récupération ensuite
    $model = new GtkListStore(GObject::TYPE_STRING);
    $combobox = new GtkComboBox();
    $combobox->set_model($model);
    $cellRenderer = new GtkCellRendererText();
    $combobox->pack_start($cellRenderer);
    $combobox->set_attributes($cellRenderer, 'text', 0);
    $model->clear();
    foreach($series as $choice) {
      $model->append(array($choice));
    }
    $combobox->set_active_iter($model->get_iter(0));
    return $combobox;
  }



  public function validerDialog($widget, $event)
  {
    //TODO ne pas hardocder imdb
    correspondanceNoms::getInstance()->setSerie('imdb',$this->serieee,$this->series[$this->combobox->get_active()]);
    $this->destroy();
  }
}