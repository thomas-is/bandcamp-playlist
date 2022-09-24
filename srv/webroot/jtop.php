<?php

include("../config.php");

$top = new ToP;
if ( isset($_GET['start']) ) {
  $top->start((int)$_GET['start']);
}

if ( isset($_GET['clean']) ) {
  $top->clean();
}

if( isset($_GET['gencovers']) ) {
    $p = new Process("bash ".Config::ROOT.Config::DS.Config::BIN.Config::DS."gencovers.bash download");
    $p->set_title("Covers");
    $p->start();
}

header("Content-Type: application/json; charset=UTF-8");
die( json_encode($top->state()) );

?>
