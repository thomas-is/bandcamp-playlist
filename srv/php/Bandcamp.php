<?php

/*
*   @abstract   bandcamp.com parser
*   @uses        BandCampTrack
*/

class Bandcamp {

    private $url;
    private $maxlen;
    private $contents;
    private $cover_src;
    private $artist;
    private $album;
    private $released;
    private $tracks = array() ;

    const DELIMITER = "_";
    const NFO = Config::ROOT.Config::DS.Config::DOWNLOAD.Config::DS;

    static function FS_encode($s) {
        return preg_replace("/[^a-zA-Z0-9]/","_",$s);
    }

    public function __construct( $url, $maxlen = 400000 ){
        if( ! $url ) { return; }
        $url = parse_url( (string) $url);
        if( ! $url ) { return; }
        /*
        *   If not specified, set scheme to "https"
        *   and parse again
        */
        if( ! isset($url['scheme']) ) { 
            $url = "https://".$url['path'];
            $url = parse_url($url);
        }
        if( ! isset($url['host']) ) { $url['host'] = "";}
        if( ! isset($url['path']) ) { $url['path'] = ""; } 
        if( $url['scheme'] != "http" && $url['scheme'] != "https" ){
            trigger_error("unsupported scheme ".$url['scheme'],E_USER_WARNING);
            return;
        }
        $this->url = $url['scheme']."://".$url['host'].$url['path'];
        $this->maxlen = (int) $maxlen;
        $this->parse();
    }

    public function download(){
        if( ! $this->contents ) return;
        $url  = $this->cover_src;
        $file = $this->get_FS_cover();
        $d = new DownloadAs($url,$file);
        file_put_contents(self::NFO.$this->get_FS_prefix().DIRECTORY_SEPARATOR."nfo.json",$this->nfo());
        foreach($this->tracks as $track) {
            $url = $track->get_mp3url();
            $file = $this->get_FS_prefix().$track->get_FS_suffix();
            $d = new DownloadAs($url,$file);
        }
    }


    /*
    *   getters
    */
    public function get_url()       { return $this->url; }
    public function get_cover_src() { return $this->cover_src; }
    public function get_artist()    { return $this->artist; }
    public function get_album()     { return $this->album; }
    public function get_released()  { return $this->released; }
    public function get_tracks()    { return $this->tracks; }
    /**
    *   setters for user edition
    */
    public function set_artist($s) { $this->artist = (string) $s; }
    public function set_album($s)  { $this->album  = (string) $s; }
    /*
    *   HTML formated getters
    */
    public function get_HTML_artist()    { return htmlspecialchars($this->artist); }
    public function get_HTML_album()     { return htmlspecialchars($this->album); }
    public function get_HTML_released()  {
        if( $this->released ) { return date("d/m/Y",strtotime($this->released)); }
        return "??/??/????";
    }
    /*
    *   FS formated getters
    */
    private function get_FS_artist()    { return $this->FS_encode($this->artist); }
    private function get_FS_album()     { return $this->FS_encode($this->album); }
    private function get_FS_released()  {
        if( $this->released ) { return date("Ymd",strtotime($this->released)); }
        return "00000000";
    }
    public function get_FS_prefix() {
        return $this->get_FS_artist().DIRECTORY_SEPARATOR.$this->get_FS_released().Bandcamp::DELIMITER.$this->get_FS_album();
    }
    public function get_FS_cover() {
        return $this->get_FS_prefix().DIRECTORY_SEPARATOR."cover.jpg";
    }



    /*
    *   NFO json export
    */
    public function nfo() {
        $js = array();
        $js['url']          = $this->url;
        $js['artist']       = $this->get_HTML_artist();
        $js['released']     = $this->get_released();
        $js['album']        = $this->get_HTML_album();
        $js['cover']        = $this->get_FS_prefix().DIRECTORY_SEPARATOR."cover.jpg";
        $js['tracks']       = array();
        foreach( $this->tracks as $track ) {
            $jstrack = array();
            $jstrack['num'     ] = $track->get_HTML_num();
            $jstrack['title'   ] = $track->get_HTML_title();
            $jstrack['duration'] = $track->get_duration();
            if( !empty($track->get_mp3url()) )
                $jstrack['mp3' ] = $this->get_FS_prefix().$track->get_FS_suffix();
            $js['tracks'][] = $jstrack;
        }
        return json_encode($js);
    }

    /**
    *   parse()
    *   @return bool    true on success, false on error.
    *   download contents and calls parse()
    *   trigger a warning if contents size reached maxlen
    */
    private function parse() {

        $this->contents = @file_get_contents( $this->url, NULL, NULL, NULL, $this->maxlen );

        if( ! $this->contents ) return false;

        if( strlen($this->contents) == $this->maxlen ){
            trigger_error("maxlen reached.",E_USER_WARNING);
        }

        if( $this->slice('<meta property="og:type" content="','"') != "album" ) {
            trigger_error("not an album.",E_USER_WARNING);
        }

        $this->cover_src = $this->slice( '<meta property="og:image" content="' , '"' );
        //  found after "var EmbedData"
        $this->artist    = $this->slice( 'artist: "'         , '"' );
        $this->album     = $this->slice( 'album_title: "'    , '"' );
        $this->released  = $this->slice( 'release_date: "'   , '"' );
        $trackinfo       = $this->slice( '&quot;trackinfo&quot:'       , '}],' ) . "}]";

        if( $this->released ) {
             $this->released = date("Y-m-d",strtotime($this->released));
        }

        $this->contents = true;

        $trackinfo = json_decode($trackinfo);

        if( ! $trackinfo ) {
            trigger_error("invalid trackinfo.",E_USER_WARNING);
            return false;
        }

        foreach( $trackinfo as $track ){
            $track = (array) $track;
            $track['file'] = (array) $track['file'];
            $this->tracks[] = new BandcampTrack(
                array(        
                    'num'       => $track['track_num'],
                    'title'     => $track['title'],
                    'mp3url'    => $track['file']['mp3-128'],
                    'duration'  => $track['duration']
                )
            );
        }
        return true;
    }

    /**
    *   parse() helpers
    */
    private function slice($open,$close){
        $offset = $this->jumpafter($open);
        if( $offset === NULL ) { return NULL; }
        $next = strpos($this->contents,$close,$offset);
        if( $next === false ) {
            trigger_error("[ $open ] [ $close ] not found",E_USER_WARNING);
            return NULL;
        }
        $length = $next-$offset;
        return substr($this->contents,$offset,$length);
    }
    private function jumpafter($tag){
        $n = strpos($this->contents,$tag);
        if( $n === false ) { return NULL; }
        return $n + strlen($tag);
    }

}

class BandcampTrack{

    const DELIMITER = Bandcamp::DELIMITER;

    private $num;
    private $title;
    private $duration;
    private $mp3url;

    public function __construct( $data ){
        if( ! is_array($data) ) return;
        if( isset($data['num']      ) ) { $this->num      = (int)    $data['num'];       } 
        if( isset($data['title']    ) ) { $this->title    = (string) $data['title'];     } 
        if( isset($data['duration'] ) ) { $this->duration = (float)  $data['duration'];  } 
        if( isset($data['mp3url']   ) ) { $this->mp3url   = (string) $data['mp3url'];    } 
    }

    /**
    *   getters
    */
    public function get_num()           { return $this->num; }
    public function get_title()         { return $this->title; }
    public function get_duration()      { return $this->duration; }
    public function get_mp3url()        { return $this->mp3url; }
    /**
    *   setter for user edition
    */
    public function set_title($s)       { $this->title = (string) $s; }
    /**
    *   HTML formatted getters
    */
    public function get_HTML_num()      { return sprintf("%02d",$this->num); }
    public function get_HTML_title()    { return htmlspecialchars($this->title); }
    public function get_HTML_duration() { return date("i:s",$this->duration); }
    /**
    *   FS formatted getters
    */
    public function get_FS_num()        { return $this->get_HTML_num(); }
    public function get_FS_title()      { return Bandcamp::FS_encode($this->title); }
    public function get_FS_suffix() {
        return DIRECTORY_SEPARATOR.$this->get_FS_num().self::DELIMITER.$this->get_FS_title().".mp3";
    }
}

?>
