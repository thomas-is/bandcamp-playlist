<?php


interface Playlist {

    /**
    *   artist()
    *   @return array of HTML strings
    *       [ "artist 1", "artist 2", ... ] 
    */
    public function artists();

    /**
    *   albums($artist)
    *   @param  string $artist
    *   @return array of HTML strings
    *       [ "album #1", "album #2", ... ] 
    */
    public function albums($artist);

    /**
    *   songs($artist,$album)
    *   @param  string $artist, string $album
    *   @return array of $song as follow :
    *   array(
    *       ["num"=>"01","title"=>"FôbaR","mp3"=>"/path/01_F_baR.mp3" ]
    *       ...
    *   )
    *   $songs must be ordered by $song["num"]
    *   $song['num'] must be a 2 chars HTML sting
    *   $song['title'] must be an HTML sting
    *   $song['mp3'] is an src attribute relative to web root
    *   $song['mp3'] is empty if file doesn't exist
    */
    public function songs($artist,$album);

    /**
    *   cover($artist,$album,$px)
    *   @param      string $artist, string $album [, int $px]
    *   @return     string $cover
    *
    *   $cover is an src attribute relative to web root
    */
    public function cover($artist,$album,$px);

}


?>
