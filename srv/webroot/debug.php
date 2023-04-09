<?php
  include("../config.php");
  $bandcamp = new NoLibForIt\Bandcamp\Bandcamp("https://motherenginerock.bandcamp.com/album/hangar-2");
  header("Content-Type: application/json");
  die(json_encode($bandcamp));
?>
