<?php
include(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(null, new lime_output_color());

$oInfos = new infosProviderSerieImdb();

$t->diag('getSeries(\'24\')');
$t->is($oInfos->getSeries('24'), array('"24" (2001)'),
  '$oInfos->getSeries() retourne les bons résultats');

$t->diag('getSeries()');
$t->isa_ok($oInfos->getSeries('rommates'), 'array',
    'getSeries() retourne un tableau');
$t->is($oInfos->getSeries('rommates'), array('"Roommates" (2007)','"Roommates" (2009)'),
    '$oInfos->getSeries() retourne les bons résultats');

$t->diag('getEpisode()');
$t->isa_ok($oInfos->getEpisode('"Roommates" (2009)',1,1), 'string',
    'getSeries() retourne une chaine');
$t->is($oInfos->getEpisode('"Roommates" (2009)',1,1), 'The Roommate',
    '$oInfos->getEpisode() retourne le bon titre');
$t->is($oInfos->getEpisode('"Roommates" (2009)',1,2), 'The Tarot',
    '$oInfos->getEpisode() retourne le bon titre');
$t->is($oInfos->getEpisode('"Roommates" (2009)',1,3), 'The Lie',
    '$oInfos->getEpisode() retourne le bon titre');
$t->is($oInfos->getEpisode('"Roommates" (2009)',1,4), 'The Break-In',
    '$oInfos->getEpisode() retourne le bon titre');

$t->is($oInfos->getEpisode('"Stargate SG-1" (1997)',1,4), 'The Broca Divide',
    '$oInfos->getEpisode() retourne le bon titre');
$t->is($oInfos->getEpisode('"Stargate SG-1" (1997)',10,20), 'Unending',
    '$oInfos->getEpisode() retourne le bon titre');


$t->diag('getSeries()');
$t->isa_ok($oInfos->getSeries('royal.pains'), 'array',
    'getSeries() retourne un tableau');
$t->is($oInfos->getSeries('royal.pains'), array('Royal Pains'),
    '$oInfos->getSeries() retourne les bons résultats');