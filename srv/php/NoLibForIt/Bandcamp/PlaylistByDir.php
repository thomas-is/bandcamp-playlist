<?php

namespace NoLibForIt\Bandcamp;


class PlaylistByDir {

  public array $albums = [];

  public function __construct(){

    foreach( self::ls() as $artist ){
      foreach( self::ls($artist) as $album) {
        $json = self::getPath( $artist, $album ) . "nfo.json";
        $nfo = json_decode(@file_get_contents($json),true);
        if ( empty($nfo) ) {
          continue;
        }
        $this->albums[] = $nfo;
      }
    }

  }

  private static function getPath( string $artist, string $album ) {
    return DIR_DOWNLOAD
      . DIRECTORY_SEPARATOR
      . $artist
      . DIRECTORY_SEPARATOR
      . $album
      . DIRECTORY_SEPARATOR;
  }

  private static function ls( string $dir = "" ) {
    $baseDir = DIR_DOWNLOAD . DIRECTORY_SEPARATOR . $dir;
    $stack = [];
    foreach( scandir($baseDir) as $entry ) {
      if (
        strpos( $entry, "." ) === 0
        || strpos( $entry, Bandcamp::DELIMITER ) === 0
        || ! is_dir( $baseDir . DIRECTORY_SEPARATOR . $entry )
      ) { continue; }
      $stack[] = $entry;
    }
    return $stack;
  }


}

?>
