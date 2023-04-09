<?php

namespace NoLibForIt\Nohup;

/**
*   @uses
*     DIR_DOWNLOAD
*     DIR_PROC
*     DIR_BIN
*/

class DownloadAs {

    const DS        = DIRECTORY_SEPARATOR;
    const DOWNLOAD  = DIR_DOWNLOAD;
    const PROC      = DIR_PROC;
    const EXE       = "/usr/bin/php -f " . DIR_BIN . self::DS . "wget.php";

    private $command;
    private $path;
    private $proc;

    public function __construct( $url, $saveas ){

      $this->path = self::DOWNLOAD;

      $dirnames = explode(self::DS,$saveas);
      $filename = array_pop($dirnames);

      foreach($dirnames as $dirname) {
        $this->path_add($dirname);
      }

      $url  = "\"$url\"";
      $dest = "\"{$this->path}$filename\"";

      $this->command = self::EXE." $url $dest";
      $this->proc = new Process($this->command);
      $this->proc->setLabel("$saveas");

    }

    public function id()  {
      return $this->proc->id();
    }

    private function safe_mkdir( $path ) {

      if ( file_exists($path) && is_dir($path) ) {
        return;
      }

      if( ! mkdir($path) ) {
        trigger_error("Can't create directory $path",E_USER_ERROR);
      }

    }

    private function path_add( $dirname ) {

      if (
        empty($dirname)
        || $dirname == "."
        || $dirname == ".."
      ) {
        return;
      }

      $this->safe_mkdir( $this->path . $dirname );
      $this->path .= $dirname . self::DS;

    }

}

?>
