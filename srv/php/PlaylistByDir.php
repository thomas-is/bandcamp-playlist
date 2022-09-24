<?php

class PlaylistByDir {

    const DS = DIRECTORY_SEPARATOR;
    const PLAYLIST = Config::PLAYLIST.self::DS;
    const IMG = Config::IMG.self::DS;

    private $nfo;

    public function __construct(){

        $artists = $this->listdir();

        $albums = array();
        foreach( $artists as $artist )
            $albums[$artist] = $this->listdir($artist);

        $this->nfo = array();
        foreach( $artists as $artist){
            foreach( $albums[$artist] as $album) {
                $js  = self::PLAYLIST.$artist.self::DS.$album.self::DS."nfo.json";
                $nfo = json_decode(@file_get_contents($js),true);
                if(!$nfo) {
                    $nfo = array();
                    $nfo['artist'] = $artist;
                    $nfo['album'] = $album;
                    $nfo['released'] = "????";
                    $nfo['tracks'] = array();
                    $nfo['path'] = self::PLAYLIST.$artist.self::DS.$album.self::DS;
                    foreach($this->listmp3($artist.self::DS.$album) as $mp3) {
                        $nfo['tracks'][] = array(
                            'num'   => substr($mp3,0,2),
                            'title' => substr($mp3,5,-4),
                            'mp3'   => $artist.self::DS.$album.self::DS.$mp3
                        );
                    }
                }
                $nfo['path'] = self::PLAYLIST.$artist.self::DS.$album.self::DS;
                $this->nfo[] = $nfo;
            }
        }
    }

    public function artists() {
        $artists = array();
        if( empty($this->nfo) ) return $artists;
        foreach( $this->nfo as $nfo)
            $artists[] = htmlspecialchars($nfo['artist']);
        return $artists;
    }


    public function root() { return self::PLAYLIST; }
    public function get_nfo() { return $this->nfo; }


    private function listdir( $dir = "" ){
        $dir = self::PLAYLIST.$dir;
        $stack = array();
        $entries = scandir( $dir );
        foreach( $entries as $entry ) {
            if( strpos($entry,".") !== 0 && strpos($entry,"_") !== 0 ) {
                if( is_dir("$dir/$entry") ) $stack[] = $entry;
            }
        }
        return $stack;
    }
    private function listmp3( $dir ){
        $dir = self::PLAYLIST.$dir;
        $stack = array();
        $entries = scandir( $dir );
        foreach( $entries as $entry ) {
            if( strpos($entry,".") !== 0 && strpos($entry,"_") !== 0 ) {
                if( strpos($entry,".mp3") ) $stack[] = $entry;
            }
        }
        return $stack;
    }


}

?>
