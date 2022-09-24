<?php


/**
*   @abstract
*
*   tMagicGetSet expects the following syntax
*
*   class Foo {
        cons VARS = array( 'key' => 'type', ... )
*       private $key;
*       public function get_key();
*       public function set_key($value);
*   }
*
*/
trait tMagicGetSet {

    /**
    *   @return array of var names 
    */
    //static function VARS() { return array_keys(get_class_vars(__CLASS__)); }

    /**
    *   __construct()
    *   @param  [ optional array("class_key"=>value,...) ]
    */
    public function __construct($data=false) { $this->setAll($data); }   
    /**
    *   __get()
    *   binds $this->key to $this->get_key()
    *   @see get($key)
    */
    public function __get($key) {
//        error_log("DEBUG ".__CLASS__."->__get($key)");
        return $this->get($key);
    }
    /**
    *   __set()
    *   binds $this->key=$value to $this->set_key($value)
    *   @see set($key,$value)
    */
    public function __set($key,$value) {
//        error_log("DEBUG ".__CLASS__."->__set($key,$value)");
        return $this->set($key,$value);
    }
    /**
    *   get($key)
    *   @param  string  $key
    *   @return $this->get_$key  (if method exists)
    *           NULL             (if not)
    */
    public function get($key) {
        $method ="get_$key";
        if(method_exists($this,$method)) return $this->$method();
//        error_log("No method: ".__CLASS__."->get_$key()");
    }
    /**
    *   set($key,$value)
    *   @param  string $key, mixed $value
    *   @return $this->set_$key($value)
    */
    public function set($key,$value ){
        $method = "set_$key";
        if(method_exists($this,$method)) return $this->$method($value);
//        error_log("No method: ".__CLASS__."->set_$key()");
    }
    /**
    *   getAll()
    *   @return array( "class_key" => value, ... )
    */
    public function getAll() {
        $data = array();
        foreach(self::VARS() as $key) $data[__CLASS__."_$key"]=$this->get($key);
        return $data;
    }
    /*
    *   setAll($assoc)
    *   @param  array( "class_key" => value, ... )
    *   calls $this->set( "key", value )
    */
    public function setAll($assoc){
        if(!is_array($assoc)) return;
        if(empty($assoc)) return;
        foreach( $assoc as $key => $value ){
            $tag=explode("_",$key,2);
            if(@$tag[0]==__CLASS__) $this->set(@$tag[1],$value);
        }
    }

    /*
    *   FIXME should not be there, makes $this->date publicly writable
    *   set_date($s)
    *   @param string $s
    *   set $this->date to SQL format "Y-m-d"
    */
    public function set_date( $s )  { $this->_date = date("Y-m-d",strtotime($s)); }

}


?>
