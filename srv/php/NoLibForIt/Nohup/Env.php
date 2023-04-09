<?php

namespace NoLibForIt\Nohup;

class Env {

  const KEY_BASE_DIR = "DIR_PROC";

  private static function get( string $key ) {
    if( defined($key) ) { return constant($key); }
    if( getenv($key) )  { return getenv($key);  }
    throw new \Exception("Undefined $key");
  }

  public static function baseDir() {
    return self::get( self::KEY_BASE_DIR  );
  }

}

?>
