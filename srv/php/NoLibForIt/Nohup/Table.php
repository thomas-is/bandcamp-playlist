<?php

namespace NoLibForIt\Nohup;

class Table {

  const JOB_KEYS = [
    "command"  ,
    "exitcode" ,
    "label"    ,
    "pid"      ,
    "signal"   ,
    "state"    ,
  ];

  private array $job = [];

  public function ps() {
    return shell_exec("ps");
  }
  /**  @return bool */
  private function action( string $action, int $id ) {
    return empty($this->job[$id]) ? false : (bool) $this->job[$id]->$action();
  }

  public function start ( int $id ) { return $this->action("start" , $id); }
  public function cancel( int $id ) { return $this->action("cancel", $id); }
  public function kill  ( int $id ) { return $this->action("kill"  , $id); }


  private function init() {

    $this->job = [];
    foreach(scandir( Env::baseDir() ) as $dir) {
      if( is_numeric($dir) ) {
        $this->job[ (int) $dir ] = new Process( (int) $dir );
      }
    }

  }


  /** @return array */
  public function get() {
    $this->init();

    $top = [];
    foreach( $this->job as $job ) {
      $top[$job->id()] = [];
      foreach( self::JOB_KEYS as $key ) {
        $top[$job->id()][$key] = $job->$key();
      }
    }
    return $top;
  }

  /** @return void */
  public function clean() {
    $this->init();
    foreach( $this->job as $job ) {
      $job->clean();
    }
  }

}

?>
