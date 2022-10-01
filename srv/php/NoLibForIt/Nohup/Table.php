<?php

namespace NoLibForIt\Nohup;

class Table {

  private $job = array();

  const DS   = DIRECTORY_SEPARATOR;
  const PROC = Config::ROOT.self::DS.Config::PROC;

  public function __construct() {
    foreach(scandir(self::PROC) as $dir) {
      if( is_numeric($dir) ) {
        $this->job[ (int) $dir ] = new Process( (int) $dir );
      }
    }
  }

  /**
    * @return bool
    */
  private function action( string $action, int $id ) {
    return empty($this->job[$id]) ? false : (bool) $this->job[$id]->$action();
  }

  public function start ( int $id ) { return $this->action("start" , $id); }
  public function cancel( int $id ) { return $this->action("cancel", $id); }
  public function kill  ( int $id ) { return $this->action("kill"  , $id); }

  public function state() {
    $top = [];
    foreach( $this->job as $job ) {
      $top[$job->uid()] = [
        "state" =>  $job->state(),
        "label" =>  $job->label()
      ];
    }
    return $top;
  }


  public function clean() {
    foreach( $this->job as $job ) {
      $job->clean();
    }
  }

}

?>
