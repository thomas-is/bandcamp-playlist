<?php

namespace NoLibForIt\Nohup;

/**
  *   @abstract
  *       Handle process in the background
  *       The proc dir structure is as follow:
  *           ./proc/00000001/command
  *                     ^     exitcode
  *                     |     pid
  *            numeral id     stdout
  *                           stderr
  *                           signal
  *   @uses   /bin/sh
  *           /bin/ps
  *           /bin/kill
  *           /usr/bin/nohup
  *           config.php
  */
class Process {

  const DS   = DIRECTORY_SEPARATOR;

  const STATE_QUEUED  = "QUEUED"     ;
  const STATE_RUNNING = "RUNNING"    ;
  const STATE_DONE    = "DONE"       ;
  const STATE_ERROR   = "ERROR"      ;

  const SYS_REQUIRED = [
    'grep',
    'kill',
    'nohup',
    'ps',
    'sed',
  ];

  private int $_id = 0;

  /**
    *   @param
    *     int     $arg    id (existing process)
    *     string  $arg    command to setup (new process)
    *     string  $label  label to set up  (new process)
    *   command MUST be a single shell command
    *   command MUST NOT contain ";" or "&"
    *   by default process is set up but not started
    *
    */
  public function __construct( string|int $arg , string $label = "" ) {

    foreach( self::SYS_REQUIRED as $bin ) {
      if( empty(shell_exec("which $bin")) ) {
        $this-panic("can't find $bin");
      }
    }

    if ( is_int($arg) ) {
      $this->_id = $arg;
      if( ! file_exists($this->storage()) ) {
        $this-panic("storage not found: {$this->storage()}");
      }
    }

    if ( is_string($arg) ) {
      $this->_id = 1;
      while( file_exists($this->storage()) ) {
        $this->_id++;
      }
      if ( ! mkdir($this->storage()) ) {
        $this-panic("can't init storage {$this->storage()}");
      }
      $this->set( "command", $arg   );
      $this->set( "label",   $label );
    }

  }

  private function panic( string $message ) {
    throw new \Exception( __CLASS__ . " $message" );
  }

  /**
   *   @return string "/absolute/path/to/proc/$_id"
   */
  private function storage() {
    return Env::baseDir() . self::DS . sprintf("%'.08d",$this->_id);
  }

  /**
    *   @param  string  $property
    *   @return string|int|null
    */
  private function get( string $property ) {
    if( ! file_exists( $this->storage() . self::DS . $property ) ) {
      return null;
    }
    $value = file_get_contents( $this->storage() . self::DS . $property );
    if ( is_numeric($value) ) {
      return (int) $value;
    }
    return empty($value) ? null : $value;
  }

  public function id()       { return $this->_id;             }
  public function command()  { return $this->get("command");  }
  public function label()    { return $this->get("label");    }
  public function signal()   { return $this->get("signal");   }
  public function exitcode() { return $this->get("exitcode"); }
  public function pid()      {
    $pid = $this->get("pid");
    $out = trim(shell_exec("ps -o pid,stat | sed 's/^ *//g' | grep '^$pid '"));
    return empty($out) ? null : $pid;
  }
  /**
    * @return bool
    */
  public function isRunning() {
    return ! empty($this->pid());
  }

  /**
    *   @param  string $property, string|int $value
    *   @return int number of chars written
    */
  private function set( string $property, string|int $value) {
    return (int) file_put_contents(
      $this->storage() . self::DS . $property,
      (string) $value
    );
  }
  public function setLabel( string $value) {
    $this->set('label',$value);
  }


  /**
    *   @return string $state
    */
  public function state() {

    /* has a valid exit code ? */
    if( is_int($this->exitcode()) ) {
      return $this->exitcode() == 0  ? "DONE" : "ERROR";
    }

    /* has a valid signal ? */
    if( $this->signal() ) {
      return $this->signal();
    }

    /* has a valid pid ? */
    if( is_int($this->pid()) ) {
      return "RUNNING";
    }

    return "QUEUED";

  }

  /**
    *  safely remove storage
    */
  public function clean() {

    if( ! file_exists($this->storage()) ) { return; }
    if( $this->isRunning()              ) { return; }

    foreach( glob($this->storage().self::DS."*") as $file ) {
      unlink($file);
    }

    rmdir($this->storage());

  }

  /**
   *   @return int $pid on success
   *           false    on error
   *
   *   execute $command in the background with nohup
   *   stdin and stdout are redirected to corresponding files in proc/_id
   */
  public function start() {

    /* did process already exit ? */
    if( $this->exitcode() ) {
      return false;
    }
    /* is process already running ? */
    if( $this->pid() ) {
      return false;
    }
    /* does process have a command ? */
    if( empty($this->command()) ) {
      return false;
    }

    $stdout   = $this->storage() . self::DS . "stdout";
    $stderr   = $this->storage() . self::DS . "stderr";
    $exitcode = $this->storage() . self::DS . "exitcode";

    /* $! to get the pid , $? to get exit code later on */
    $pid = (int) trim(shell_exec("nohup sh -c '{$this->command()}; echo $? > $exitcode' > $stdout 2> $stderr & echo $!"));

    /* did exec fail ? (no pid) */
    if( empty($pid) ) {
      return false;
    }
    $this->set( "pid", $pid );

    return $this->pid();

  }


  /**
   *   @param  string  $signal (@see man kill)
   *   @return false   if process is not running
   *           true    if signal has been sent
   */
  private function sendsig( string $signal ) {

    if( empty($this->pid()) ) { return false; }

    exec( "kill -$signal " . $this->pid() );
    $this->set( "signal", $signal );

    return true;

  }

  /** "TERM" aka ^C (soft kill) */
  public function cancel() { return $this->sendsig("TERM"); }

  /** "KILL" aka "kill -9" (hard kill) */
  public function kill()   { return $this->sendsig("KILL"); }

}

?>
