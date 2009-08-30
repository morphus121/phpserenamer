<?php
class myHButtonBox extends GtkHButtonBox
{

  public function ajouter_boutton($libelle, $image = null)
  {
    $boutton = new GtkButton($libelle);
    if(!is_null($image))
    {
      $boutton->set_image(GtkImage::new_from_stock($image, Gtk::ICON_SIZE_BUTTON));
    }
    $this->pack_start($boutton);
  }
}