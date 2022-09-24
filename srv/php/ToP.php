<?php

class ToP {

  private $proc = array();

  const DS   = DIRECTORY_SEPARATOR;
  const PROC = Config::ROOT.self::DS.Config::PROC;

  public function __construct() {

    if( empty(shell_exec("which nohup")) ) {
      throw new \Exception(__CLASS__." can't find nohup!");
    }

    $pdirs = [];

    foreach(scandir(self::PROC) as $dir) {
      if( is_numeric($dir) ) { $pdirs[] = $dir; }
    }

    foreach($pdirs as $id) {
      $this->proc[ (int) $id ] = new Process( (int) $id );
    }

  }

  private function safeAction( string $action, int $id ) {
    if( empty($this->proc[$id]) ) {
      return null;
    }
    return $this->proc[$id]->$action();
  }

  public function start ( int $id ) { return $this->safeAction("start" , $id); }
  public function cancel( int $id ) { return $this->safeAction("cancel", $id); }
  public function kill  ( int $id ) { return $this->safeAction("kill"  , $id); }

  public function state() {

    $top = [];

    foreach($this->proc as $proc) {
      $top[$proc->id()] = [
        "state" =>  $proc->state(),
        "title" =>  $proc->get_title()
      ];
    }

    return $top;
  }


  public function clean() {

    foreach($this->proc as $proc) {
      $proc->clean();
    }

  }

}

?>
