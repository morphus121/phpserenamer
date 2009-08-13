<?php
/**
 *
 * @author adriengallou
 *
 */
class infosProviderFactory
{

  /**
   * Crée un objet infoProvider*
   *
   * @param  string              $type    serie ou film
   * @param  string              $provider
   * @return infosProvider*
   */
  public static function createInfosProvider($type, $provider)
  {
    if(!in_array($type, array('serie', 'film')))
    {
      throw new sfException(sprintf('Le type %s n\'est pas un type acceptable', $type));
    }

    //TODO adapter le sr_providers aux types ?
    if(!in_array($provider, sfConfig::get('sr_providers')))
    {
      throw new sfException(sprintf('Le provider %s n\'est pas un provider acceptable', $provider));
    }

    $classe = sprintf('infosProvider%s%s', ucfirst($type), ucfirst($provider));
    if(!class_exists($classe))
    {
    	throw new sfException('La classe n\'existe pas.');
    }
    return new $classe;

  }

  //TODO getConst pour récupérer les constantes des classes,
  //pour permettre d'indiquer le nom affiché

}