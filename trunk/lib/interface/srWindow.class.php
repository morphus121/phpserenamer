<?php
class srWindow extends GtkWindow
{
  private static $instance = null;

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

	  $this->set_title('SeRenamer PHP');
	  $this->connect_simple('destroy', array('gtk', 'main_quit'));
	  $this->set_default_size(800,600);
	  $this->set_border_width(0);
	  $this->set_position(GTK::WIN_POS_CENTER);

	  $box = new GtkVBox();
    $box->pack_start(srMenu::getInstance(),0,0);
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
    $format = new GtkEntry();
    $box->pack_start($format);

    //site
    $lblFormat = new GtkLabel('Site : ');
    $box->pack_start($lblFormat);
    $box->pack_start($this->getComboBoxProviders());

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

	private function getComboBoxProviders()
	{
	  //TODO get_active_iter() pour récupération ensuite
    $list = array('imdb');
    $model = new GtkListStore(GObject::TYPE_STRING);
    $combobox = new GtkComboBox();
    $combobox->set_model($model);
    $cellRenderer = new GtkCellRendererText();
    $combobox->pack_start($cellRenderer);
    $combobox->set_attributes($cellRenderer, 'text', 0);
    $model->clear();
    foreach($list as $choice) {
      $model->append(array($choice));
    }
    $combobox->set_active_iter($model->get_iter(0));
    return $combobox;
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
      if(!correspondanceNoms::getInstance()->hasKey('imdb',$serie))
      {
        $oInfosProviderSerieImdb = new infosProviderSerieImdb();
        $series = $oInfosProviderSerieImdb->getSeries($serie);
        if(count($series) > 1)
        {
          srWindow::getInstance()->lancer_dialog($serie, $series);
        }
        else
        {
          correspondanceNoms::getInstance()->setSerie('imdb', $serie, $series[0]);
        }
      }
    }
    catch(SerieNonFoundException $ex)
    {
      $store->set($iter, 6, "erreur");
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
    $dialog = new srFileChooserDialog("Choisir dossier", null, Gtk::FILE_CHOOSER_ACTION_SELECT_FOLDER,
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
    $dialog = new srFileChooserDialog("Choisir fichier", null, Gtk::FILE_CHOOSER_ACTION_OPEN,
        array(Gtk::STOCK_OK, Gtk::RESPONSE_OK), null);

    $dialog->show_all();
    if ($dialog->run() == Gtk::RESPONSE_OK)
    {
      $selected_file = $dialog->get_filename();
      srListeStore::getInstance()->remplirFromFilePath($selected_file);
    }
    $dialog->destroy();
  }
}