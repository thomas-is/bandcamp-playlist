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
      "sh ".DIR_BIN.DIRECTORY_SEPARATOR."gencovers.sh ".DIR_DOWNLOAD ,
      "Covers"
    );
    $p->start();
}

header("Content-Type: application/json; charset=UTF-8");
die( json_encode($top->get()) );

?>
