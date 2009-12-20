<?php
include(dirname(__FILE__).'/../../../bootstrap/unit.php');

include(dirname(__FILE__).'/../../../../lib/infosProvider/infosProviderBase.class.php');
include(dirname(__FILE__).'/../../../../lib/infosProvider/infosProviderSerieBase.class.php');
include(dirname(__FILE__).'/../../../../lib/infosProvider/infosProviderSerieThetvdben.class.php');

include(dirname(__FILE__).'/../../../../lib/util/myWebBrowser.class.php');

$t = new lime_test(null, new lime_output_color());

$oInfos = new infosProviderSerieThetvdben();

$t->diag('getSeries(\'24\')');
$t->is($oInfos->getSeries('24'), array (
  '24',
  '24 Hour Design',
  '24 h Berlin - Ein Tag im Leben',
  'BBC News 24',
  'Truth in 24',
  'Final 24',
  'Pure 24',
  '24 Hour Quiz',
  'BBC News 24: Click',
), '$oInfos->getSeries() retourne les bons résultats');

$t->diag('getSeries(\'Roommates\')');
$t->isa_ok($oInfos->getSeries('Roommates'), 'array',
    'getSeries() retourne un tableau');
$t->is($oInfos->getSeries('roommates'),  array (
  'Roommates',
), '$oInfos->getSeries() retourne les bons résultats');

$t->diag('getEpisode()');
$t->isa_ok($oInfos->getEpisode('Roommates', 1, 1), 'string',
    'getSeries() retourne une chaine');
$t->is($oInfos->getEpisode('Roommates', 1, 1), 'The Roommate',
    '$oInfos->getEpisode() retourne le bon titre');
$t->is($oInfos->getEpisode('Roommates', 1, 2), 'The Tarot',
    '$oInfos->getEpisode() retourne le bon titre');
$t->is($oInfos->getEpisode('Roommates', 1, 3), 'The Lie',
    '$oInfos->getEpisode() retourne le bon titre');
$t->is($oInfos->getEpisode('Roommates', 1, 4), 'The Break-In',
    '$oInfos->getEpisode() retourne le bon titre');


$t->diag('getSeries(\'Stargate SG-1\')');
$t->is($oInfos->getSeries('Stargate SG-1'), array (
  'Stargate SG-1',
), '$oInfos->getSeries() retourne les bons résultats');

$t->is($oInfos->getEpisode('Stargate SG-1',1,4), 'Emancipation',
    '$oInfos->getEpisode() retourne le bon titre');
$t->is($oInfos->getEpisode('Stargate SG-1',10,20), 'Unending',
    '$oInfos->getEpisode() retourne le bon titre');


$t->diag('getSeries()');
$t->isa_ok($oInfos->getSeries('royal.pains'), 'array',
    'getSeries() retourne un tableau');
$t->is($oInfos->getSeries('royal.pains'), array('Royal Pains'),
    '$oInfos->getSeries() retourne les bons résultats');

$t->diag('getSeries()');
$t->is($oInfos->getSeries('skins'), array('Skins'),
    '$oInfos->getSeries() retourne les bons résultats');

$t->diag('getSeries(\'the shield\')');
$t->is($oInfos->getSeries('the shield'), array(
  'The Shield',
), '$oInfos->getSeries() retourne les bons résultats');

$t->is($oInfos->getEpisode('The Shield', 6, 1), 'On the Jones', '$oInfos->getEpisode() retourne les bons résultats');
$t->is($oInfos->getEpisode('The Shield', 6, 10), 'Spanish Practices', '$oInfos->getEpisode() retourne les bons résultats');
$t->is($oInfos->getEpisode('The Shield', 7, 1), 'Coefficient of Drag', '$oInfos->getEpisode() retourne les bons résultats');
$t->is($oInfos->getEpisode('The Shield', 7, 13), 'Family Meeting', '$oInfos->getEpisode() retourne les bons résultats');

$t->diag('getSeries(\'journeyman\')');
$t->is($oInfos->getSeries('journeyman'), array(
  'Journeyman',
), '$oInfos->getSeries() retourne les bons résultats');

$t->diag('getEpisode(\'journeyman\', 1, 1)');
$t->is($oInfos->getEpisode('journeyman', 1, 1), 'Pilot - A Love of a Lifetime', '$oInfos->getEpisode() retourne les bons résultats');

