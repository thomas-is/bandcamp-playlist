<?php

namespace \NoLibForIt\Bandcamp

class Init {


  private $html;
  private array $tralbum = [];

  public function __construct( $url, $maxlen = 400000 ){
    $this->html = @file_get_contents( $url, NULL, NULL, NULL, $maxlen );
    if ( empty($this->html) ) {
      throw new \Exception("no contents!");
    }
    $this->parse();
  }

  private function getRawDataValue( $key ) {
    $start = strpos($this->html, "$key=\"") + strlen("$key=\"");
    $end   = strpos($this->html, '"', $start);
    return substr($this->html, $start, $end-$start);
  }

  /** @return array */
  private function getDataValue( $key ) {
    return (array) json_decode(
      html_entity_decode($this->getRawDataValue($key))
      ,true
    );
  }

  private function parse() {

    $this->cover_src     = $this->getRawDataValue('<meta property="og:image" content');
    $this->tralbum       = $this->getDataValue("data-tralbum");

    $this->url           = @$this->tralbum['url'];
    $this->artist        = @$this->tralbum['artist'];
    $this->release_date  = @$this->tralbum['album_release_date']
                            ? date("Y-m-d",strtotime($this->tralbum['album_release_date']))
                            : "??-??-??";
    $this->album_title   = @$this->tralbum['packages'][0]['album_title'];

    $this->tracks = [];
    foreach ( (array) @$this->tralbum['trackinfo'] as $atom ) {
      $track = [];
      $track['num']   = @$atom['track_num'];
      $track['title'] = @$atom['title'];
      $track['url']   = @$atom['file']['mp3-128'];
      $this->tracks[] = $track;
    }

  }

}


?>
