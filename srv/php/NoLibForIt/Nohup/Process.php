<?php

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
  /* absolute path to proc/                   */
  const PROC = Config::ROOT.self::DS.Config::PROC;


  const STATE_QUEUED  = "QUEUED"     ;
  const STATE_RUNNING = "RUNNING"    ;
  const STATE_DONE    = "DONE"       ;
  const STATE_ERROR   = "ERROR"      ;

  private int $id = 0;

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

    if( empty(shell_exec("which nohup")) ) {
      $this-panic("can't find nohup!");
    }

    if ( is_int($arg) ) {
      $this->id = $arg;
      if( ! file_exists($this->storage()) ) {
        $this-panic("storage not found: {$this->storage()}");
      }
    }

    if ( is_string($arg) ) {
      $this->id = 1;
      while( file_exists($this->storage()) ) {
        $this->id++;
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
   *   @return string "/absolute/path/to/proc/$id"
   */
  private function storage() {
    return self::PROC . self::DS . sprintf("%'.08d",$this->_id);
  }

  /**
    *   @param  string  $property
    *   @return string
    */
  private function get( string $property ) {
    return (string) file_get_contents( $this->storage() . self::DS . $property );
  }

  /**
    * @param  string    $value
    * @return int|null
    */
  private function asInt( string $value ) {
    return strlen($value) == 0 ? null : (int) $value;
  }

  public function command()  { return $this->get("command");                }
  public function label()    { return $this->get("label");                  }
  public function signal()   { return $this->get("signal");                 }
  public function exitcode() { return $this->asInt($this->get("exitcode")); }
  public function pid()      { return $this->asInt($this->get("pid"));      }

  /**
    *   @param  string $property, string|int $value
    *   @return int number of chars written
    */
  private function set( string $property, string|int $value) {
    return (int) file_put_contents(
      $this->storage() . self::DS . $property,
      (string) $value . PHP_EOL
    );
  }


  /**
    *   @return string $state
    *   as given by ps
    *   State will be "S" most of the time as the process
    *   is running in the background (@see man ps)
    */
  public function state() {

    if( is_int($this->exitcode()) ) {
      /* valid exit code */
      return $this->exitcode() == 0  ? "DONE" : "ERROR";
    }

    if( ! is_int($this->pid()) ) {
      /* no PID */
      return "QUEUED";
    }

    /* defined PID */
    exec( "ps -p {$this->pid()} -o state", $op );

    if( isset($op[1]) ) {
      /* valid PID */
      return "RUNNING";
    }

    /* invalid PID */
    return $this->signal();

  }

  /**
    * @return bool
    */
  public function isRunning() {
    exec("ps -p {$this->pid()} -o state",$op);
    return isset($op[1]);
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
   *   stdin and stdout are redirected to corresponding files in proc/id
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
    if( ! $this->get("command") ) {
      return false;
    }

    $stdout   = $this->storage() . self::DS . "stdout";
    $stderr   = $this->storage() . self::DS . "stderr";
    $exitcode = $this->storage() . self::DS . "exitcode";

    /* $! to get the pid , $? to get exit code later on */
    exec("nohup sh -c '$command\necho $? > $exitcode' > $stdout 2> $stderr & echo $!", $op);

    /* did exec fail ? (no pid) */
    if( ! isset($op[0]) ) {
      return false;
    }

    $this->set( "pid", (string) $op[0] );

    return (int) $op[0];

  }


  /**
   *   @param  string  $signal (@see man kill)
   *   @return false   if process is not running
   *           true    if signal has been sent
   */
  private function sendsig( string $signal ) {

    if( ! $this->is_running() ) { return false; }

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
