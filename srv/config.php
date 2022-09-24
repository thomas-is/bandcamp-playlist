<?php

/**
 *   Glogal configuration
 */
class Config {

  const DS            = DIRECTORY_SEPARATOR;

  const MAINTENANCE   = false;

  const VERSION       = "0.02";

  const ROOT          = __DIR__;
  const WEBROOT       = self::ROOT.self::DS."webroot";

  /* relative to ROOT */
  const PHP           = "php";
  const BIN           = "bin";
  const PROC          = "proc";
  const DOWNLOAD      = "playlist";

  /* relative to WEBROOT */
  const PLAYLIST      = "playlist";
  const IMG           = "pics";
  const JS            = "js";
  const CSS           = "css";

  const HTML_CHARSET  = "utf8";

  static function autoload($class) {
    $file = self::ROOT.self::DS.self::PHP;
    foreach(explode("\\",$class) as $name) {
      if(!empty($name)) $file.=self::DS.$name;
    }
    $file.=".php";
    if (file_exists($file)) {
      require_once($file);
    } else {
      error_log("[".__FILE__."::".__FUNCTION__."] $class not found: $file");
    }

  }

}

spl_autoload_register("Config::autoload");

if(Config::MAINTENANCE) {
  header("HTTP/1.1 503");
  die("<h1>503 Service unavailable</h1>");
}

?>
