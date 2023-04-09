<?php

namespace NoLibForIt\Nohup;

class Env {

  public static function baseDir() {
    return self::get( 'DIR_PROC' );
  }

  private static function get( string $key ) {

    if( defined($key) ) {
      return constant($key);
    }

    if( getenv($key) )  {
      return getenv($key);
    }

    throw new \Exception("Undefined $key");

  }

}

?>
