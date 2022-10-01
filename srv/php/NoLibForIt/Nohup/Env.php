<?php

namespace NoLibForIt\Nohup;

class Env {

  const KEY_BASE_DIR = "NOLIBFORIT_NOHUP_DIR";

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
