<?php
/**
 *
 * @author adriengallou
 *
 */
class srParametres extends GtkDialog
{

  const WIDTH  = 350;
  const HEIGHT = 200;

  /**
   *
   * @var srParametres
   */
  private static $instance = null;

  /**
   *
   * @var GtkEntry
   */
  public $defaultFolder;

  /**
   *
   * @var srComboBoxProviders
   */
  public $defaultProvider;

  /**
   *
   * @var GtkEntry
   */
  public $defaultPattern;

  /**
   *
   * @var GtkComboBox
   */
  public $defaultLanguage;

  /**
   *
   * @var GtkCheckButton
   */
  public $replaceSpaces;

  /**
   *
   * @var GtkCheckButton
   */
  public $openRecursively;

  /**
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    $this->set_title(srUtils::getTranslation('Parameters'));
    $this->set_default_size(self::WIDTH, self::HEIGHT);
    $this->set_border_width(0);
    $this->set_position(GTK::WIN_POS_CENTER);
    $this->set_resizable(false);
    $this->set_icon_from_file(sfConfig::get('sr_logo'));

    $this->add_button(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL);
    $this->add_button(Gtk::STOCK_OK, Gtk::RESPONSE_OK);

    $box = $this->vbox;
    $box->pack_start($this->getReglageDossierParDefaut(), 0, 0);
    $box->pack_start($this->getReglageProviderParDefaut(), 0, 0);
    $box->pack_start($this->getReglagePatternParDefaut(), 0, 0);
    $box->pack_start($this->getReglageLangageParDefaut(), 0, 0);
    $box->pack_start($this->getReglageRemplacementEspacesParPoints(), 0, 0);
    $box->pack_start($this->getReglageOpenFoldersRecursively(), 0, 0);

    $this->connect_simple('destroy', array('srParametres', 'onDestroy'));
    $this->connect('close',array('srParametres','onDestroy'));
    $this->connect('response',array('srParametres','valider'));
  }

  /**
   *
   * @return GtkVButtonBox
   */
  public function getReglageDossierParDefaut()
  {
    $box = new GtkVButtonBox();

    $frame = new GtkFrame(srUtils::getTranslation('Default folder'));
    $this->defaultFolder = new GtkFileChooserButton(srUtils::getTranslation('Choose the default folder'), Gtk::FILE_CHOOSER_ACTION_SELECT_FOLDER);
    if(is_dir(srConfig::get('default_folder'))) $this->defaultFolder->set_current_folder(srConfig::get('default_folder'));
    $this->defaultFolder->set_size_request(320, -1);
    $frame->add($this->defaultFolder);

    $box->pack_start($frame);

    return $box;
  }

  /**
   *
   * @return GtkVButtonBox
   */
  public function getReglageProviderParDefaut()
  {
    $box = new GtkVButtonBox();

    $frame = new GtkFrame(srUtils::getTranslation('Default provider'));
    $this->defaultProvider = new srComboBoxProviders();
    $this->defaultProvider->setSelectedProvider(srConfig::get('default_provider'));
    $this->defaultProvider->set_size_request(320, -1);
    $frame->add($this->defaultProvider);

    $box->pack_start($frame);

    return $box;
  }

  /**
   * %n - [%sx%k] - %t
   * @return GtkVButtonBox
   */
  public function getReglagePatternParDefaut()
  {
    $box = new GtkVButtonBox();

    $frame = new GtkFrame(srUtils::getTranslation('Default pattern'));

    $this->defaultPattern = new GtkEntry();
    $this->defaultPattern->set_text(srConfig::get('default_pattern'));
    $this->defaultPattern->set_size_request(320, -1);
    $frame->add($this->defaultPattern);
    $box->pack_start($frame);

    return $box;
  }

  public function getReglageLangageParDefaut()
  {
    $box = new GtkVButtonBox();

    $frame = new GtkFrame(srUtils::getTranslation('Language'));

    $this->defaultLanguage = new GtkComboBox(new GtkListStore());
    $list  = array_values(srUtils::getLanguagesNames());
    sort($list);
    $model = new GtkListStore(GObject::TYPE_STRING);
    $this->defaultLanguage->set_model($model);
    $cellRenderer = new GtkCellRendererText();
    $this->defaultLanguage->pack_start($cellRenderer);
    $this->defaultLanguage->set_size_request(320, -1);
    $this->defaultLanguage->set_attributes($cellRenderer, 'text', 0);
    $model->clear();
    foreach($list as $choice)
    {
      $model->append(array(ucfirst($choice)));
    }
    $posDefaultLanguage = array_search(srUtils::getLanguageNameFromCode(srConfig::get('default_language')), $list);
    $this->defaultLanguage->set_active_iter($model->get_iter($posDefaultLanguage));


    $frame->add($this->defaultLanguage);
    $box->pack_start($frame);

    return $box;
  }

  public function getReglageRemplacementEspacesParPoints()
  {
    $box = new GtkVButtonBox();

    $frame = new GtkFrame();

    $this->replaceSpaces = new GtkCheckButton(srUtils::getTranslation('Replace spaces by points'));
    if(srConfig::get('replaceSpaces')) $this->replaceSpaces->clicked();
    $frame->add($this->replaceSpaces);
    $frame->set_size_request(320, -1);
    $box->pack_start($frame);
    return $box;
  }


  public function getReglageOpenFoldersRecursively()
  {
    $box = new GtkVButtonBox();

    $frame = new GtkFrame();

    $this->openRecursively = new GtkCheckButton(srUtils::getTranslation('Open folders recursively'));
    if(srConfig::get('openRecursively')) $this->openRecursively->clicked();
    $frame->add($this->openRecursively);
    $frame->set_size_request(320, -1);
    $box->pack_start($frame);
    return $box;
  }

  /**
   *
   * @return srParametres
   */
  public static function getInstance()
  {
    if(is_null(self::$instance))
    {
      self::$instance = new srParametres();
    }
    return self::$instance;
  }

  /**
   *
   * @param $widget
   * @param $event
   * @return void
   */
  public function onDestroy($widget, $event)
  {
    self::$instance->destroy();
    self::$instance = null;
  }

  /**
   *
   * @param $widget
   * @param $event
   * @return void
   */
  public function valider($widget, $event)
  {
    if($event == Gtk::RESPONSE_OK)
    {
      srConfig::set('default_folder', self::getInstance()->defaultFolder->get_current_folder());
      srConfig::set('default_provider', self::getInstance()->defaultProvider->getSelectedProvider());
      srConfig::set('default_pattern', self::getInstance()->defaultPattern->get_text());
      srWindow::getInstance()->setUserPattern(srConfig::get('default_pattern'));
      srConfig::set('default_language', srUtils::getCodeFromLanguage(self::getInstance()->defaultLanguage->get_active_text()));
      srConfig::set('replaceSpaces', self::getInstance()->replaceSpaces->get_active());
      srConfig::set('openRecursively', self::getInstance()->openRecursively->get_active());
    }
    self::getInstance()->destroy();
  }

}