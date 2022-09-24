<?php
/**
*   collection of objects 
**/
class collection {

    private $_class;
    private $_collection = array();
    /**
    *   __construct($class)
    *   @param string $class
    */
    public function __construct($class) {
        if(!class_exists($class)) trigger_error("$class doesn't exists");
        $this->_class = $class;
    }
    /**
    *   hydrate($entries)
    *   @param  array( array("class_key"=>value,..), ...)
    */
    public function hydrate($entries) {
        $this->_collection = array();
        if(empty($entries)) return;
        $class = $this->_class;
        foreach($entries as $entry) $this->_collection[] = new $class($entry);
    }
    /*
    *   dehydrate()
    *   @return array( array("class_key"=>value,..), ...)
    */
    public function dehydrate(){
        $result = array();
        if(empty($this->_collection)) return $result;
        foreach( $this->_collection as $obj ) $result[] = $obj->getAll();
        return $result;
    }
    /**
    *   indexed($key)
    *   @param  string $key 
    *   @return array( int $id => $value, ... )
    */
    public function indexed($key){
        $result = array();
        foreach($this->_collection as $obj) $result[$obj->get('id')]=$obj->get($key);
        return $result;
    }
    /**
    *   with($key,$value)
    *   @param  string $key, mixed $value
    *   @return array(objects) where $key==$value
    */
    public function with($key,$value) {
        $result = array();
        if(empty($this->_collection)) return $result;
        foreach( $this->_collection as $obj ){
            if($obj->get($key)==$value) $result[]=$obj;
        }
        return $result;
    }

    /**
    *   @param  int $id
    *   @return first object with id==$id
    *           NULL if not found
    */
    public function id($id) { $obj=$this->with("id",$id); return @$obj[0]; }

    /*
    *   @return array(objects)
    */
    public function all(){ return $this->_collection; }

}


?>
