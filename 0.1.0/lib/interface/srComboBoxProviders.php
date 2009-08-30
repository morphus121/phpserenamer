<?php
/**
 *
 * @author adriengallou
 *
 */
class srComboBoxProviders extends GtkComboBox
{

  /**
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct(new GtkListStore());
    $list  = sfConfig::get('sr_providers');
    $model = new GtkListStore(GObject::TYPE_STRING);
    $this->set_model($model);
    $cellRenderer = new GtkCellRendererText();
    $this->pack_start($cellRenderer);
    $this->set_attributes($cellRenderer, 'text', 0);
    $model->clear();
    foreach($list as $choice) {
      $model->append(array($choice));
    }
    $this->set_active_iter($model->get_iter(0));

  }

  /**
   * Retourne le provider sélectionné
   *
   * @return string
   */
  public function getSelectedProvider()
  {
    return $this->get_active_text();
  }

}