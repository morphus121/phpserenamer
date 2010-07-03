<?php
include(dirname(__FILE__).'/../bootstrap/unit.php');

include(dirname(__FILE__).'/../../lib/infosProvider/infosProviderBase.class.php');
include(dirname(__FILE__).'/../../lib/infosProvider/infosProviderSerieBase.class.php');
include(dirname(__FILE__).'/../../lib/infosProvider/infosProviderSerieAllocine.class.php');

include(dirname(__FILE__).'/../../lib/util/myWebBrowser.class.php');

//include(dirname(__FILE__).'/../../plugins/sfWebBrowserPlugin/lib/myWebBrowser.class.php');

$t = new lime_test(null, new lime_output_color());

$oInfos = new infosProviderSerieAllocine();

$t->diag('getSeries(\'cold case\')');
$t->is($oInfos->getSeries('cold case'), array(
  'Cold Case : affaires classées',
), '$oInfos->getSeries() retourne les bons résultats');

$t->diag('getSeries(\'Urgences\')');
$t->is($oInfos->getSeries('Urgences'), array(
  'Urgences',
  'Golden Hour : urgences extrêmes',
  'Urgence Disparitions',
  'Équipe médicale d\'urgence',
),
'$oInfos->getSeries() retourne les bons résultats');


$t->diag('getEpisode(\'Urgences\', 1, 1)');
$t->is($oInfos->getEpisode('Urgences', 1, 1), 'Pilote - 1ère partie', '$oInfos->getEpisode() retourne les bons résultats');

$t->diag('getEpisode(\'Urgences\', 1, 3)');
$t->is($oInfos->getEpisode('Urgences', 1, 3), 'Jour J', '$oInfos->getEpisode() retourne les bons résultats');


$t->diag('getSeries(\'Mysterious Ways\')');
$t->is($oInfos->getSeries('Mysterious Ways'), array('Les Chemins de l\'étrange'),
'$oInfos->getSeries() retourne les bons résultats');

$t->diag('getSeries(\'mysterious.ways\')');
$t->is($oInfos->getSeries('mysterious.ways'), array('Les Chemins de l\'étrange'),
'$oInfos->getSeries() retourne les bons résultats');

$t->diag('getEpisode(\'Les Chemins de l\'étrange\', 1, 1)');
$t->is($oInfos->getEpisode('Les Chemins de l\'étrange', 1, 1), 'Sous la glace', '$oInfos->getEpisode() retourne les bons résultats');

$t->diag('getSeries(\'journeyman\')');
$t->is($oInfos->getSeries('journeyman'), array(
  'Journeyman',
), '$oInfos->getSeries() retourne les bons résultats');

$t->diag('getEpisode(\'journeyman\', 1, 1)');
$t->is($oInfos->getEpisode('journeyman', 1, 1), 'Retour vers le passé', '$oInfos->getEpisode() retourne les bons résultats');
$t->is($oInfos->getEpisode('Journeyman', 1, 1), 'Retour vers le passé', '$oInfos->getEpisode() retourne les bons résultats');

$t->diag('getSeries(\'the shield\')');
$t->is($oInfos->getSeries('the shield'), array(
  'The Shield',
), '$oInfos->getSeries() retourne les bons résultats');

$t->is($oInfos->getEpisode('The Shield', 6, 1), 'Rien de personnel', '$oInfos->getEpisode() retourne les bons résultats');
$t->is($oInfos->getEpisode('The Shield', 6, 10), 'Enfer à Farmington', '$oInfos->getEpisode() retourne les bons résultats');
$t->is($oInfos->getEpisode('The Shield', 7, 1), 'Poids mort', '$oInfos->getEpisode() retourne les bons résultats');
$t->is($oInfos->getEpisode('The Shield', 7, 13), 'Retour au bercail', '$oInfos->getEpisode() retourne les bons résultats');
