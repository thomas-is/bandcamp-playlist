<?php

namespace NoLibForIt\Bandcamp;


class Bandcamp {


  private $html;

  public $artist    ;
  public $album     ;
  public $cover     ;
  public $released  ;
  public $url       ;

  public $tracks    ;

  public const DELIMITER = "_";
  public const NFO =
    DIR_ROOT
    . DIRECTORY_SEPARATOR
    . DIR_DOWNLOAD
    . DIRECTORY_SEPARATOR;


  public static function safeString( string $s) {
    return preg_replace("/[^a-zA-Z0-9]/","_",$s);
  }

  /**
   * extract $value of the first $key="$value" in $this->html
   * @param  string  $key
   * @return string  value
   */
  private function valueOf( $key ) {
    $start = strpos($this->html, "$key=\"") + strlen("$key=\"");
    $end   = strpos($this->html, '"', $start);
    return substr($this->html, $start, $end-$start);
  }

  /**
   * extract $value of the first $key="$value" in $this->html,
   * decode html entities and json decode
   * @param  string  $key
   * @return array
   */
  private function decode( $key ) {
    return (array) json_decode(
      html_entity_decode($this->valueOf($key))
      ,true
    );
  }


  public function __construct( $url ){

    $this->html = file_get_contents( $url );

    if ( empty($this->html) ) {
      throw new \Exception("no contents at $url");
    }

    $tralbum         = $this->decode("data-tralbum");

    $this->artist    = @$tralbum['artist'];
    $this->album     = @$tralbum['packages'][0]['album_title'];
    $this->cover     = $this->valueOf('<meta property="og:image" content');
    $this->released  = @$tralbum['album_release_date'];
    $this->url       = @$tralbum['url'];

    $this->tracks = [];
    foreach ( (array) @$tralbum['trackinfo'] as $trackinfo ) {
      $this->tracks[] = new Track($trackinfo);
    }


  }

  public function htmlArtist()   { return htmlspecialchars($this->artist);   }
  public function htmlAlbum()    { return htmlspecialchars($this->album);    }
  public function htmlReleased() {
    return $this->released
      ? date("Y-m-d",strtotime($this->released))
      : "??-??-??";
  }
  /*
   *   FS formated getters
   */
  private function fileArtist() {
    return self::safeString($this->artist);
  }
  private function fileAlbum()  {
    return self::safeString($this->album);
  }
  private function fileReleased()  {
    return $this->released
      ? date("Ymd",strtotime($this->released))
      : "00000000";
  }
  public function filePrefix() {
    return $this->fileArtist()
      . DIRECTORY_SEPARATOR
      . $this->fileReleased()
      . Bandcamp::DELIMITER
      . $this->fileAlbum();
  }
  public function fileCover() {
    return $this->filePrefix()
      . DIRECTORY_SEPARATOR
      . "cover.jpg";
  }
  public function download(){
    $d = new DownloadAs($this->cover,$this->fileCover());
    file_put_contents(
        self::NFO
        . "nfo.json"
        , $this->nfo()
        );
    foreach($this->tracks as $track) {
      $url = $track->mp3url;
      $file = $this->filePrefix().$track->fileSuffix();
      $d = new DownloadAs($url,$file);
    }
  }

  /*
   *   NFO json export
   */
  public function nfo() {
    $js = array();
    $js['url']          = $this->url;
    $js['artist']       = $this->artist;
    $js['released']     = $this->released;
    $js['album']        = $this->album;
    $js['cover']        = $this->filePrefix().DIRECTORY_SEPARATOR."cover.jpg";
    $js['tracks']       = array();
    foreach( $this->tracks as $track ) {
      $jstrack = array();
      $jstrack['num'     ] = $track->num;
      $jstrack['title'   ] = $track->title;
      $jstrack['duration'] = $track->duration;
      if( !empty($track->mp3url) ) {
        $jstrack['mp3' ] = $this->filePrefix() . $track->fileSuffix();
      }
      $js['tracks'][] = $jstrack;
    }
    return json_encode($js);
  }



}

?>
