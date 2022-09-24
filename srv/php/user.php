<?php


class user {

    use tMagicGetSet;

    const VARS = array(
        'id'    => 'ID',
        'login' => '',
        'group' => 'ENUM',
        'hash'  => 'HASH'
    );

    private $id;
    private $login;
    private $group;
    private $hash;

    public function get_id()         { return $this->id;      } 
    public function get_login()      { return $this->login;   } 
    public function get_group()      { return $this->group;   } 
    public function get_hash()       { return $this->hash;    } 

    public function set_id($n)       { $this->id    = (int) $n;     } 
    public function set_login($s)    { $this->login = (string) $s;  } 
    public function set_group($s)    { $this->group = (string) $s;  } 
    public function set_hash($s)     { $this->hash  = (string) $s;  } 

    public function set_password($s) { $this->hash  = password_hash((string)$s,PASSWORD_BCRYPT); }

}

?>
