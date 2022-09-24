<?php

namespace zerodb;

class Query {

    const DB       = \Config::SQL_DATABASE;
    const USERNAME = \Config::SQL_USERNAME;
    const PASSWORD = \Config::SQL_PASSWORD;
    const CHARSET  = \Config::SQL_CHARSET;
    private $_class;
    private $_property;
    private $_select;
    private $_from;
    private $_where;
    private $_query;
    private $_args = array();
    private $_pdo;
    private $_answer;

    public function ask() {
        $this->_query = "";
        if( $this->_select ) $this->_query .= $this->_select;
        if( $this->_from   ) $this->_query .= $this->_from;
        if( $this->_union  ) $this->_query .= $this->_union;
        if( $this->_where  ) $this->_query .= $this->_where;
        error_log($this->_query);
        $this->_answer = $this->_execute($this->_query,$this->_args);
        return $this->_answer;
    }

    public function select($tags) {
        $this->_select = NULL;
        if( ! is_array($tags) ) trigger_error("Not an array",E_USER_ERROR);
        if( empty($tags) )      trigger_error("Empty array",E_USER_ERROR);
        $select=array();
        foreach($tags as $tag) {
            $e = explode("_",$tag,2);
            $this->_validate( @$e[0], @$e[1] );
            if( $this->_class and $this->_property )
                $select[]="{$this->_class}s.{$this->_property} AS {$this->_class}_{$this->_property}";
        }
        if( empty($select) ) return;
        $this->_select = "SELECT ".implode(", ",$select)."\n";
    }

    public function from($class) {
        $this->_from = NULL;
        $this->_validate($class);
        if( $this->_class )
            $this->_from = "FROM ".self::DB.".{$this->_class}s\n";
    }
    public function union($other,$tag) {
        $this->_union = NULL;
        $e = explode("_",$other,2);
        $this->_validate( @$e[0], @$e[1] );
        if( $this->_class and $this->_property ) {
            $this->_union = "INNER JOIN ".self::DB.".{$this->_class}s";
            $this->_union .= " ON {$this->_class}s.{$this->_property}";
        } else {
            $this->_union = NULL;
            return;
        }
        $e = explode("_",$tag,2);
        $this->_validate( @$e[0], @$e[1] );
        if( $this->_class and $this->_property )
            $this->_union .= " = {$this->_class}s.{$this->_property}\n";
        else {
            $this->_union = NULL;
            return;
        }
    }

    public function where($tags) {
        $this->_where = NULL;
        $this->_args  = array();
        if( ! is_array($tags) ) trigger_error("Not an array",E_USER_ERROR);
        if( empty($tags) )      trigger_error("Empty array",E_USER_ERROR);
        $where = array();
        $args  = array();
        foreach($tags as $tag => $value) {
            $e = explode("_",$tag,2);
            $this->_validate( @$e[0], @$e[1] );
            if( $this->_class and $this->_property ) {
                $where[]="{$this->_class}s.{$this->_property} = :{$this->_class}_{$this->_property}";
                $args["{$this->_class}_{$this->_property}"] = $value;
            }
        }
        if( empty($where) ) return;
        $this->_where = "WHERE ".implode("\nAND ",$where)."\n";
        $this->_args  = $args;
    }

    private function _validate($class,$property=NULL) {
        $this->_class    = NULL;
        $this->_property = NULL;
        $def = __NAMESPACE__."\\Tables::$class"."s";
        if( defined($def) )
            $this->_class = $class;
        else error_log("Invalid class: $class");
        if($property)
            if( in_array($property,array_keys(constant($def))) )
                 $this->_property = $property ;
            else error_log("Invalid $class->$property");
    }

    private function _connect() {
        if( $this->_pdo ) return;
        try {
            $this->_pdo = new \PDO("mysql:host=localhost;charset=".self::CHARSET,self::USERNAME,self::PASSWORD);
            $this->_pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES,false);
            $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
        }
        catch( PDOException $excep ) {
            error_log($excep);
            // 503 - Service unavailable
            HTTP::error(503);
        }
    }

    private function _execute( $command, $data = array() ) {
        $this->_connect();
        try {
            $statement = $this->_pdo->prepare($command);
            if( ! empty($data) )
                foreach($data as $key => &$val)
                    $statement->bindParam(":$key", $val);
            $statement->execute($data);
            if( $statement->columnCount() == 0 ) return array();
            return $statement->fetchAll(\PDO::FETCH_ASSOC); 
        }
        catch( PDOException $excep ) {
            error_log($excep);
            // 503 - Service unavailable
            HTTP::error(503);
        }
    }

}

?>
