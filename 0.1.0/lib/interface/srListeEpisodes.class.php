<?php
class srListeEpisodes
{
  public function getInstance()
  {
    $scrolled_win = new GtkScrolledWindow();
    $scrolled_win->set_policy( Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scrolled_win->add_with_viewport(srTreeView::getInstance());
    return $scrolled_win;
  }
}
