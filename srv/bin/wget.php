<?php

    /**
    *   basic wget implementation
    *   @syntax wget.php url path
    */

    if ( $argc<2 ) {
      trigger_error("Not enough arguments",E_USER_ERROR);
    }

    $url  = $argv[1];
    $dst  = $argv[2];
    $size = NULL;

    function err( $s ) {
      fwrite(STDERR, "$s" . PHP_EOL );
    }

    err("DST = $dst");

    if ( file_exists($dst) ) {
        err("File exists: $dst");
        exit(1);
    }

    $context = stream_context_create( [ 'http' => [ 'method' => 'HEAD' ] ] );

    if ( ! $fp = fopen( $url, 'r', false, $context ) ) {
        err("Unable to open URL: $url");
        exit(1);
    }

    $meta = stream_get_meta_data($fp);
    fclose($fp);

    if ( isset($meta['wrapper_data']) ) {
      foreach ( $meta['wrapper_data'] as $line ) {
        if ( strpos(strtolower($line),"content-length: ") === 0 ) {
          $size = (int) substr($line,16);
          break;
        }
      }
    }

    if ( ! $src_fp = fopen($url,'rb') ) {
      err("Unable to open URL: $url");
      exit(1);
    }

    if ( ! $dst_fp = fopen($dst,'wb') ) {
      err("Unable to open file: $dst");
      exit(1);
    }

    $count = 0;
    while ( ! feof($src_fp) ) {
      $bytes = fwrite( $dst_fp, fread($src_fp,1024*8), 1024*8 );
      if ( $bytes ) {
        $count += $bytes;
      }
      if ( $size) {
        printf("%'.08d %'.08d $dst".PHP_EOL,$count,$size);
      } else {
        printf("%'.08d ???????? $dst".PHP_EOL,$count);
      }
    }

    fclose($src_fp);
    fclose($dst_fp);
    echo "DONE $dst".PHP_EOL;
    exit(0);
?>
