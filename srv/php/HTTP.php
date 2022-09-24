<?php

class HTTP {

    const  VERSION = "1.1";

    public static function error( $code, $msg = NULL ) {
        header("HTTP/".HTTP::VERSION." $code");
        die($msg);
    }

    public static function redirect( $location ) {
        header("HTTP/".HTTP::VERSION." 301");
        header("Location: $location");
        die();
    }

    public static function https() {
        if ( $_SERVER['REQUEST_SCHEME'] != "https" ) {
            self::redirect( 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
        }
    }

    public static function json( $data ) {
        header("Content-Type: application/json; charset=UTF-8");
        die( json_encode($data) );
    }


}



?>
