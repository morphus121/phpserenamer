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
    $model = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING);
    $this->set_model($model);
    $cellRenderer = new GtkCellRendererText();
    $this->pack_start($cellRenderer);
    $this->set_attributes($cellRenderer, 'text', 0);
    $this->set_attributes($cellRenderer, 'text', 1);
    $model->clear();
    foreach($list as $key => $name)
    {
      $model->append(array($key, $name));
    }
    $this->set_active_iter($model->get_iter(0));

  }

  /**
   * Définit le provider sélectionné
   *
   * @return string
   */
  public function setSelectedProvider($provider)
  {
    $list         = sfConfig::get('sr_providers');
    $listInt      = array_values($list);
    $providerName = $list[$provider];
    return $this->set_active(array_search($providerName, $listInt));
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