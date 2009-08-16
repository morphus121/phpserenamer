<?php
/**
 *
 * @author adriengallou
 *
 */
class srWindow extends GtkWindow
{

  const WIDTH  = 800;
  const HEIGHT = 600;
  const TITRE  = 'SeRenamer PHP';

  /**
   *
   * @var srWindow
   */
  private static $instance = null;

  /**
   * (ne peut pas être en private)
   * @var srComboBoxProviders
   */
  protected $comboboxProviders;

  /**
   *
   * @var GtkEntry
   */
  protected $userFormat;

  /**
   *
   * @return srWindow
   */
  public static function getInstance()
  {
    if(is_null(self::$instance))
    {
      self::$instance = new srWindow();
    }
    return self::$instance;
  }

  public function __construct()
  {
    parent::__construct();

    $this->set_title(self::TITRE);
    $this->connect_simple('destroy', array('gtk', 'main_quit'));
    $this->set_default_size(self::WIDTH, self::HEIGHT);
    $this->set_border_width(0);
    $this->set_position(GTK::WIN_POS_CENTER);

    $box = new GtkVBox();
    $box->pack_start(srMenu::getInstance(), 0, 0);
    $box->pack_start($this->getBouttons(), 0);
    $box->pack_start(srListeEpisodes::getInstance());
    $box->pack_start($this->getBouttonsBas(), 0);

    $this->add($box);
  }

  /**
   *
   * @return GtkHbox
   */
  private function getBouttons()
  {
    $box = new GtkHButtonBox();
    //@see http://gtk.php.net/manual/en/gtk.enum.buttonboxstyle.php
    $box->set_layout(Gtk::BUTTONBOX_START);
    $boutton1 = new GtkButton('Ajouter Dossier');
    $boutton1->set_image(GtkImage::new_from_stock(Gtk::STOCK_OPEN, Gtk::ICON_SIZE_BUTTON));
    $boutton1->connect_simple('released', array('srWindow', 'clicOuvrirDossier'));
    $box->pack_start($boutton1);

    $boutton1 = new GtkButton('Ajouter Fichier');
    $boutton1->set_image(GtkImage::new_from_stock(Gtk::STOCK_NEW, Gtk::ICON_SIZE_BUTTON));
    $boutton1->connect_simple('released', array('srWindow', 'clicOuvrirFichier'));
    $box->pack_start($boutton1);

    $boutton1 = new GtkButton();
    $boutton1->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_BUTTON));
    $box->pack_start($boutton1);

    //format
    $lblFormat = new GtkLabel('Format : ');
    $box->pack_start($lblFormat);
    $this->userFormat = new GtkEntry();
    $this->userFormat->set_text('%n - [%sx%k] - %t');
    $box->pack_start($this->userFormat);

    //site
    $lblFormat = new GtkLabel('Site : ');
    $box->pack_start($lblFormat);
    $this->comboboxProviders = new srComboBoxProviders();
    $box->pack_start($this->comboboxProviders);

    return $box;
  }

  private function getBouttonsBas()
  {
    $box = new myHButtonBox();
    //@see http://gtk.php.net/manual/en/gtk.enum.buttonboxstyle.php
    $box->set_layout(Gtk::BUTTONBOX_SPREAD);
    //$box->ajouter_boutton('vider',Gtk::STOCK_CLEAR);
    $boutton1 = new GtkButton('Vider');
    $boutton1->set_image(GtkImage::new_from_stock(Gtk::STOCK_CLEAR, Gtk::ICON_SIZE_BUTTON));
    $boutton1->connect_simple('released', array('srWindow', 'clicVider'));
    $box->pack_start($boutton1);

    $boutton1 = new GtkButton('Apercu');
    $boutton1->set_image(GtkImage::new_from_stock(Gtk::STOCK_ZOOM_FIT, Gtk::ICON_SIZE_BUTTON));
    $boutton1->connect_simple('released', array('srWindow', 'clicApercu'));
    $box->pack_start($boutton1);

    $boutton1 = new GtkButton('Renommer');
    $boutton1->set_image(GtkImage::new_from_stock(Gtk::STOCK_APPLY, Gtk::ICON_SIZE_BUTTON));
    $boutton1->connect_simple('released', array('srWindow', 'clicRenommer'));
    $box->pack_start($boutton1);

    return $box;
  }

  public function clicVider()
  {
    srListeStore::getInstance()->clear();
  }

  //private static $seriesTrouves = array();
  public function clicApercu()
  {
    // srWindow::getInstance()->setup_app();
    //ON CHANGE TEMPORAIREMENT LA FONCTION DE L'APRECU
    //
    srListeStore::getInstance()->foreach(array('srWindow','toto'));
    srListeStore::getInstance()->chercherNouveauxTitres();
    //self::$seriesTrouves = array();
  }

  public function clicRenommer()
  {
    srListeStore::getInstance()->renommer();
  }

  public function toto($store, $path, $iter)
  {
    try
    {
      $serie = $store->get_value($iter, 0);
      if(!correspondanceNoms::getInstance()->hasKey(self::getInstance()->getSelectedProvider(), $serie))
      {
        $oInfosProviderSerie = infosProviderFactory::createInfosProvider('serie', self::getInstance()->getSelectedProvider());
        $series = $oInfosProviderSerie->getSeries($serie);
        if(count($series) > 1)
        {
          srWindow::getInstance()->lancer_dialog($serie, $series);
        }
        else
        {
          correspondanceNoms::getInstance()->setSerie(self::getInstance()->getSelectedProvider(), $serie, $series[0]);
        }
      }
      $store->set($iter, 6, srListeStore::STATUS_OK);
    }
    catch(SerieNonFoundException $ex)
    {
      $store->set($iter, 6, srListeStore::STATUS_ERROR);
    }
  }

  public function lancer_dialog($serie, $series)
  {
    $dialog = new srDialog('toto', null, Gtk::DIALOG_MODAL);
    $dialog->setSerie($serie, $series);
    $dialog->run();
  }

  public static function clicOuvrirDossier()
  {
    $dialog = new GtkFileChooserDialog("Choisir dossier", null, Gtk::FILE_CHOOSER_ACTION_SELECT_FOLDER,
    array(Gtk::STOCK_OK, Gtk::RESPONSE_OK), null);

    $dialog->show_all();
    if ($dialog->run() == Gtk::RESPONSE_OK)
    {
      $selected_folder = $dialog->get_filename();
      srListeStore::getInstance()->remplirFromChemin($selected_folder);
    }
    $dialog->destroy();
  }

  public static function clicOuvrirFichier()
  {
    $dialog = new GtkFileChooserDialog("Choisir fichier", null, Gtk::FILE_CHOOSER_ACTION_OPEN,
    array(Gtk::STOCK_OK, Gtk::RESPONSE_OK), null);

    $dialog->show_all();
    if ($dialog->run() == Gtk::RESPONSE_OK)
    {
      $selected_file = $dialog->get_filename();
      srListeStore::getInstance()->remplirFromFilePath($selected_file);
    }
    $dialog->destroy();
  }

  public static function getSelectedProvider()
  {
    return self::getInstance()->comboboxProviders->getSelectedProvider();
  }

  public static function getUserPattern()
  {
    return self::getInstance()->userFormat->get_text();
  }

}