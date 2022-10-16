<?php


define( 'DS'            , DIRECTORY_SEPARATOR            );
define( 'DIR_ROOT'      ,  __DIR__                       );
define( 'DIR_PHP'       ,  DIR_ROOT . DS . "php"         );
define( 'DIR_BIN'       ,  DIR_ROOT . DS . "bin"         );
define( 'DIR_PROC'      ,  DIR_ROOT . DS . "proc"        );
define( 'DIR_WEBROOT'   ,  DIR_ROOT . DS . "webroot"     );
define( 'DIR_DOWNLOAD'  ,  DIR_WEBROOT . DS . "playlist" );
define( 'SRC_PLAYLIST'  ,  "playlist"                    );
define( 'SRC_IMG'       ,  "pics"                        );
define( 'SRC_JS'        ,  "js"                          );
define( 'SRC_CSS'       ,  "css"                         );
define( 'HTML_CHARSET'  ,  "utf8"                        );

require_once("autoloader.php");

?>
