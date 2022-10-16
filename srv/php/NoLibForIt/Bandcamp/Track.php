<?php

namespace NoLibForIt\Bandcamp;

class Track {


    public $num;
    public $title;
    public $duration;
    public $mp3url;

    public function __construct( array $data ){
      $this->num       = (int)    @$data['track_num'];
      $this->title     = (string) @$data['title'];
      $this->mp3url    = (string) @$data['file']['mp3-128'];
      $this->duration  = (int)    @$data['duration'];
    }

    /**
    *   HTML getters
    */
    public function htmlNum()      { return sprintf("%02d", $this->num); }
    public function htmlTitle()    { return htmlspecialchars($this->title); }
    public function htmlDuration() { return date("i:s", $this->duration); }
    /**
    *   FS getters
    */
    public function fileNum()    { return $this->htmlNum(); }
    public function fileTitle()  { return Bandcamp::safeString($this->title); }
    public function fileSuffix() {
        return DIRECTORY_SEPARATOR
             . $this->fileNum()
             . Bandcamp::DELIMITER
             . $this->fileTitle()
             . ".mp3";
    }


}

