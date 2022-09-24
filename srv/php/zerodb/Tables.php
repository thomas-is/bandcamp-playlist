<?php

namespace zerodb;

class Tables {

    const users = array(
        'id'    => 'ID',
        'login' => '',
        'group' => 'ENUM',
        'hash'  => 'HASH'
    );

    const songs = array(
        'id'        => 'ID',
        'titre'     => '',
        'album_id'  => 'REF',
        'artist_id' => 'REF'
    );

}

?>
