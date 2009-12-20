<?php
/**
 * Classe srProgressBar
 *
 * PHP version 5
 *
 * @package Interface
 * @author  Adrien Gallou <adriengallou@gmail.com>
 * @version SVN: <svn_id>
 */

/**
 * srProgressBar
 *
 * @package Interface
 * @author  Adrien Gallou <adriengallou@gmail.com>
 * @version Release: <package_version>
 *
 */
class srProgressBar extends GtkProgressBar
{

  /**
   *
   * @var srProgressBar
   */
  private static $instance = null;

  /**
   * Retourne l'instance de srProgressBar
   *
   * @return srProgressBar
   */
  public static function getInstance()
  {
    if (is_null(self::$instance))
    {
      $reflection = new ReflectionClass(__CLASS__);
      $progress   = $reflection->newInstance();
      $progress->initialize();
      self::$instance = $progress;
    }
    return self::$instance;
  }

  /**
   * Initialisation du srProgressBar
   *
   * @return void
   */
  protected function initialize()
  {
    $this->set_pulse_step(0.1);
    $this->set_visible(false);
  }

  /**
   * Lancement de la progressbar
   *
   * @return void
   */
  public static function start()
  {
    self::getInstance()->show();
  }

  /**
   * On fait avancer la progressbar avec un éventuel message
   *
   * @param string $message message à afficher dans la progressbar
   *
   * @return void
   */
  public static function progress($message = '')
  {
    self::getInstance()->pulse();
    self::getInstance()->set_text($message);
    while (Gtk::events_pending())
    {
      Gtk::main_iteration();
    }
  }

  /**
   * On fait avancer la progressbar avec un message qui sera traduit
   *
   * @param string $message
   *
   * @return void
   */
  public static function progressTranslated($message = '')
  {
    self::progress(srUtils::getTranslation($message, 'progressbar'));
  }

  /**
   * Fin de la progressbar
   *
   * @return void
   */
  public static function stop()
  {
    self::getInstance()->hide();
    self::getInstance()->set_fraction(0);
  }

}