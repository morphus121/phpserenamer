<?php
include(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(null, new lime_output_color());

$oInfos = new infosProviderSerieAllocine();

$t->diag('getSeries(\'Urgences\')');
$t->is($oInfos->getSeries('Urgences'), array(
  'Urgences',
  'Urgence Disparitions',
  'Équipe médicale d\'urgence',
  'Golden Hour : urgences extrêmes'
),
'$oInfos->getSeries() retourne les bons résultats');


$t->diag('getEpisode(\'Urgences\', 1, 1)');
$t->is($oInfos->getEpisode('Urgences', 1, 1), 'Pilote - 1ère partie', '$oInfos->getEpisdoe() retourne les bons résultats');

$t->diag('getEpisode(\'Urgences\', 1, 3)');
$t->is($oInfos->getEpisode('Urgences', 1, 3), 'Jour J', '$oInfos->getEpisdoe() retourne les bons résultats');


$t->diag('getSeries(\'Mysterious Ways\')');
$t->is($oInfos->getSeries('Mysterious Ways'), array('Les Chemins de l\'étrange',),
'$oInfos->getSeries() retourne les bons résultats');

$t->diag('getSeries(\'mysterious.ways\')');
$t->is($oInfos->getSeries('mysterious.ways'), array('Les Chemins de l\'étrange',),
'$oInfos->getSeries() retourne les bons résultats');

$t->diag('getEpisode(\'Les Chemins de l\'étrange\', 1, 1)');
$t->is($oInfos->getEpisode('Les Chemins de l\'étrange', 1, 1), 'Sous la glace', '$oInfos->getEpisdoe() retourne les bons résultats');
