<?php

include("../config.php");

$top = new NoLibForIt\Nohup\Table;
if ( isset($_GET['start']) ) {
  $top->start((int)$_GET['start']);
}

if ( isset($_GET['clean']) ) {
  $top->clean();
}

if( isset($_GET['gencovers']) ) {
    $p = new NoLibForIt\Nohup\Process(
      "bash ".BIN.DS."gencovers.bash download",
      "Covers"
    );
    $p->start();
}

header("Content-Type: application/json; charset=UTF-8");
die( json_encode($top->get()) );

?>
