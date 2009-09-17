<?php
include(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(null, new lime_output_color());


$tab = array(
  'toto' => array(
    'test'  => 65,
    'test2' => 32,
  ),
  'titi' => array(
    'abc'   => 112,
    'def'   => 80
  )
);

$tabRetour = array(
  'toto_test'  => 65,
  'toto_test2' => 32,
  'titi_abc'   => 112,
  'titi_def'   => 80,
);

$t->is(srUtils::flattenArray($tab), $tabRetour, 'flattenArray fonctionne');
$t->is(srutils::unFlattenArray($tabRetour), $tab, 'unflattenArray fonctionne');


$tab = array(
  'toto' => array(
    'test'  => array(
      'rty' => 65,
      'uio' => 456,
    ),
    'test2' => 32,
  ),
  'titi' => array(
    'abc'   => 112,
    'def'   => 80
  )
);

$tabRetour = array(
  'toto_test_rty' => 65,
  'toto_test_uio' => 456,
  'toto_test2'    => 32,
  'titi_abc'      => 112,
  'titi_def'      => 80,
);

$t->is(srUtils::flattenArray($tab), $tabRetour, 'flattenArray fonctionne');
$t->is(srutils::unFlattenArray($tabRetour), $tab, 'unflattenArray fonctionne');

$tab = array(
  "all" =>
  array(
    "default" =>
    array(
      "folder" => "tiiiiiiiiiiiiiiii"
    )
  )
);

$tabRetour = array(
  'all_default_folder' => 'tiiiiiiiiiiiiiiii'
);


$t->is(srUtils::flattenArray($tab), $tabRetour, 'flattenArray fonctionne');
$t->is(srutils::unFlattenArray($tabRetour), $tab, 'unflattenArray fonctionne');