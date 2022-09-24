<?php

/**
 *   @abstract
 *
 *       Handle process in the background
 *
 *       Requires    _ UNIX-like OS
 *                   _ coreutils (@see @uses)
 *                   _ a rw directory (@see Config::PROC)
 *
 *       The proc dir structure is as follow:
 *           ./proc/00000000/command
 *                     ^     exitcode
 *                     |     pid
 *            numeral id     stdout
 *                           stderr
 *                           signal
 *
 *   @uses   /bin/sh
 *           /bin/ps
 *           /bin/kill
 *           /usr/bin/nohup
 *           config.php
 */
class Process {

  /**
   *   DS shortcut
   */
  const DS   = DIRECTORY_SEPARATOR;
  /**
   *   absolute path to proc/
   */
  const PROC = Config::ROOT.self::DS.Config::PROC.self::DS;

  /**
   *   (int) $id
   *   related to proc/$id
   *   this is NOT the pid
   */
  private $id;

  /**
   *   __construct()
   *   @param  mixed   int     $id of a proc dir
   *                   string  $command to setup
   *
   *   $command MUST be a single shell command
   *   $command MUST NOT contain ";" or "&"
   *
   *   by default process is set up but not started
   *
   */
  public function __construct( mixed $arg ){

    if ( is_int($arg) ) {
      $this->id = $arg;
      return;
    }

    if ( is_string($arg) ) {
      $id = 0;
      while( file_exists($this->proc($id)) ) {
        $id++;
      }
      if ( ! mkdir($this->proc($id)) ) {
        throw new \Exception(__CLASS__." can't mkdir {$this->proc($id)}");
      }
      $this->id = $id;
      $this->proc_set( "command", $arg );
    }

  }

  /**
   *   id()
   *   @return string  id (int on 8 chars filled with zeros)
   */
  public function id() { return sprintf( "%'.08d", $this->id ); }

  /**
   *   get_tile()
   *   @return string  content of proc/id/title
   */
  public function get_title() {
    return $this->proc_get("title");
  }

  /**
   *   set_tile($title)
   *   @param  string  title (doh!)
   *   @return boolean true on sucess, false on error
   */
  public function set_title( string $title ) {
    return $this->proc_set("title",$title);
  }

  /**
   *   state()
   *   @return false   if proc/000000id/pid is not set
   *           NULL    if pid is not in ps
   *           string  $state as given by ps
   *
   *   $state will be "S" most of the time as the process
   *   is running in the background (@see man ps)
   */
  public function state() {

    $exitcode = $this->proc_get("exitcode"); 

    if( $exitcode !== false ) {
      $exitcode = (int) $exitcode;
      if( $exitcode == 0 ) {
        return "DONE";
      }
      return "ERROR";
    }

    $pid = $this->proc_get("pid");

    if( $pid === false ) {
      return "QUEUED";
    }

    exec( "ps -p $pid -o state", $op );

    if( @$op[1] ) {
      return "RUNNING";
    }

    $signal = $this->proc_get("signal");

    return $signal ?? false;

  }

  /**
   *   is_running()
   *   @return bool
   */
  public function is_running(){

    if( $this->proc_get("exitcode") !== false ) {
      return true;
    }

    $pid = $this->proc_get("pid");

    if( $pid === false) {
      return false;
    }

    exec("ps -p $pid -o state",$op);

    return @$op[1] ? true : false;

  }

  /**
   *   rm -rf proc/000000id if process is not running
   */
  public function clean() {

    if( $this->state() == "RUNNING" || (! file_exists($this->proc())) ) {
      return;
    }

    foreach( glob($this->proc().self::DS."*") as $file ) {
      unlink($file);
    }
    rmdir($this->proc());

  }

  /**
   *   start()
   *   @return int $pid on success
   *           false    on error
   *
   *   execute $command in the background with nohup
   *   stdin and stdout are redirected to corresponding files in proc/id
   */
  public function start() {

    // did process exit ?
    if( $this->proc_get("exitcode") !== false) {
      return false;
    }

    // is process already running ?
    if( $this->proc_get("pid") ) {
      return false;
    }

    // does process have a command ?
    if( ! $this->proc_get("command") ) {
      return false;
    }

    $stdout   = $this->proc().self::DS."stdout";
    $stderr   = $this->proc().self::DS."stderr";
    $exitcode = $this->proc().self::DS."exitcode";

    /**
     *   $! to get the pid
     *   $? to get exit code later on
     */
    exec("nohup sh -c '$command\necho $? > $exitcode' > $stdout 2> $stderr & echo $!", $op);

    // did exec fail ? (no pid)
    if( ! isset($op[0]) ) {
      return false;
    }

    $this->proc_set( "pid", (int) $op[0] );

    return (int) $op[0];

  }


  /**
   *   proc()
   *   @param  [int $id] optional
   *   @return string "proc/$id"
   *           null   if $id and $this->id are not set
   *
   *   TODO: rename the function
   */
  private function proc( $id = NULL ) {

    if( $id === NULL ) {
      $id = $this->id;
    }

    if( $id === NULL ) {
      return NULL;
    }

    return self::PROC.sprintf("%'.08d",$id);

  }

  /**
   *   proc_get($property)
   *   @param  string  $property
   *   @return mixed   content of proc/$id/$property
   */
  public function proc_get($property) {
    if( $this->id === NULL ) {
      return NULL;
    }
    return @file_get_contents($this->proc().self::DS.$property);
  }

  /**
   *   proc_set($property,$value)
   *   @param  string $property, mixed $value
   *   @return int    on sucess
   *           false  on error
   *
   *   writes $value to proc/$id/$property
   */
  private function proc_set($property,$value) {
    if( $this->id === NULL ) {
      return NULL;
    }
    return @file_put_contents($this->proc().self::DS.$property,$value.PHP_EOL);
  }

  /**
   *   sendsig($signal)
   *   @param  string  $signal (@see man kill)
   *   @return false   if process is not running
   *           true    if signal has been sent
   */
  private function sendsig( $signal ) {

    if( ! $this->is_running() ) {
      return false;
    }

    exec( "kill -$signal " . $this->proc_get("pid") );
    $this->proc_set( "signal", $signal );
    return true;

  }

  /**
   *   sendsig("TERM") aka ^C (soft kill)
   */
  public function cancel() { return $this->sendsig("TERM"); }

  /**
   *   sendsig("KILL") aka "kill -9" (hard kill)
   */
  public function kill()   { return $this->sendsig("KILL"); }

}

?>
