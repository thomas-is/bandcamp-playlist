<?php

namespace NoLibForIt\Bandcamp;


class PlaylistByDir {

  public $entries;

  public static function getPath( string $artist, string $album ) {
    return DIR_DOWNLOAD
      . DIRECTORY_SEPARATOR
      . $artist
      . DIRECTORY_SEPARATOR
      . $album
      . DIRECTORY_SEPARATOR;
  }

  public function __construct(){

    $artists = $this->ls();

    $albums = [];
    foreach( $artists as $artist ) {
      $albums[$artist] = $this->ls($artist);
    }

    $this->entries = [];
    foreach( $artists as $artist ){
      foreach( $albums[$artist] as $album) {
        $json = self::getPath( $artist, $album ) . "nfo.json";
        $nfo = json_decode(@file_get_contents($json),true);
        if ( empty($nfo) ) {
          continue;
        }
        $this->entries[] = $nfo;
      }
    }
  }

  private function ls( string $dir = "" ) {
    $stack = [];
    foreach( scandir(DIR_DOWNLOAD.DIRECTORY_SEPARATOR.$dir) as $entry ) {
      if (
        strpos($entry,".") === 0
        || strpos($entry,Bandcamp::DELIMITER) === 0
        || ! is_dir(DIR_DOWNLOAD.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.$entry)
      ) { continue; }
      $stack[] = $entry;
    }
    return $stack;
  }

  private function mp3( string $artist, string $album ) {
    $stack = [];
    foreach( scandir(self::getPath($artist,$album)) as $entry ) {
      if (
        strpos($entry,".") === 0
        || strpos($entry,"_") === 0
        || ! strpos($entry,".mp3")
      ) { continue; }
      $stack[] = $entry;
    }
    return $stack;
  }


}

?>
