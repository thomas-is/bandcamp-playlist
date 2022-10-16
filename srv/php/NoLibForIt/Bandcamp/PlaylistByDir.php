<?php

namespace NoLibForIt\Bandcamp;


class PlaylistByDir {

  private $nfo;

  public static function getPath( string $artist, string $album ) {
    return SRC_PLAYLIST
      . $artist
      . DIRECTORY_SEPARATOR
      . $album
      . DIRECTORY_SEPARATOR;
  }

  public function __construct(){

    $artists = $this->listdir();

    $albums = [];
    foreach( $artists as $artist ) {
      $albums[$artist] = $this->listdir($artist);
    }

    $this->nfo = array();
    foreach( $artists as $artist ){
      foreach( $albums[$artist] as $album) {
        $json = self::getPath( $artist, $album ) . "nfo.json";
        $nfo = json_decode(@file_get_contents($json),true);
        if( empty($nfo) ) {
          $nfo = [];
          $nfo['artist']   = $artist;
          $nfo['album']    = $album;
          $nfo['released'] = "????";
          $nfo['tracks']   = [];
          $nfo['path']     = self::getPath($artist,$album);
          foreach($this->listmp3($artist,$album) as $mp3) {
            $nfo['tracks'][] = [
              'num'   => substr($mp3,0,2),
              'title' => substr($mp3,5,-4),
              'mp3'   =>
                $artist
                . DIRECTORY_SPERARATOR
                . $album
                . DIRECTORY_SEPARATOR
                . $mp3
            ];
          }
        }
        $nfo['path'] = self::getPath($artist,$album);
        $this->nfo[] = $nfo;
      }
    }
  }

  public function artists() {
    $artists = [];
    if( empty($this->nfo) ) {
      return $artists;
    }
    foreach( $this->nfo as $nfo) {
      $artists[] = htmlspecialchars($nfo['artist']);
    }
    return $artists;
  }


  public function get_nfo() { return $this->nfo; }


  private function listdir( string $dir = "" ){
    $stack = [];
    foreach( scandir(SRC_PLAYLIST.$dir) as $entry ) {
      if ( strpos($entry,".") === 0
          || strpos($entry,Bandcamp::DELIMITER) === 0
          || ! is_dir($dir.DIRECTORY_SEPARATOR.$entry)
         ) {
        continue;
      }
      $stack[] = $entry;
    }
    return $stack;
  }

  private function listmp3( string $artist, string $album ) {
    $stack = [];
    foreach( scandir(self::getPath($artist,$album)) as $entry ) {
      if ( strpos($entry,".") === 0
          || strpos($entry,"_") === 0
          || ! strpos($entry,".mp3")
         ) {
        continue;
      }
      $stack[] = $entry;
    }
    return $stack;
  }


}

?>
