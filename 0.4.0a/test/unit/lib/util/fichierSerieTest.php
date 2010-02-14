<?php
include(dirname(__FILE__).'/../../../bootstrap/unit.php');

include(dirname(__FILE__).'/../../../../lib/util/fichierSerie.class.php');

$t = new lime_test(null, new lime_output_color());

testerFichierSerie($t,
  'Castle.2009.S01E04.Hell.Hath.No.Fury.PROPER.HDTV.XviD-FQM.avi',
  'castle', 1, 4, 'avi'
);

testerFichierSerie($t,
  'roommates.109.hdtv-0tv.avi',
  'roommates', 1, 9, 'avi'
);

testerFichierSerie($t,
  'Roommates.1x03.The.Lie.HDTV.XviD-0TV.[tvu.org.ru].avi',
  'roommates', 1, 3, 'avi'
);

testerFichierSerie($t,
  'Roommates.1x03.The.Lie.HDTV.XviD-0TV.[tvu.org.ru].srt',
  'roommates', 1, 3, 'srt'
);

testerFichierSerie($t,
  'Roommates.1x05.The.Set-Up.HDTV.XviD-FQM.[tvu.org.ru].avi',
  'roommates', 1, 5, 'avi'
);

testerFichierSerie($t,
  'Roommates.1x05.The.Set-Up.HDTV.XviD-FQM.[tvu.org.ru].srt',
  'roommates', 1, 5, 'srt'
);

testerFichierSerie($t,
  'royal.pains.s01e05.720p.hdtv.x264-ctu.mkv',
  'royal.pains', 1, 5, 'mkv'
);


testerFichierSerie($t,
  'Les Chemins de l\'étrange - [1x03] - Porte-bonheur.avi',
  'les chemins de l\'étrange', 1, 3, 'avi'
);

//test nom de série avec esperluette
testerFichierSerie($t,
  'Law & Order Criminal Intent - [1x22] - Tuxedo Hill.avi',
  'law & order criminal intent', 1, 22, 'avi'
);

function testerFichierSerie(lime_test $t, $fichier, $serie, $saison, $episode, $extension)
{
	$t->info($fichier);
	$fichier = new fichierSerie($fichier);
	$t->is($fichier->getSerie(), $serie, 'serie ok');
	$t->is($fichier->getSaison(), $saison, 'saison ok');
	$t->is($fichier->getEpisode(), $episode, 'episode ok');
	$t->is($fichier->getExtension(), $extension, 'extension ok');
}