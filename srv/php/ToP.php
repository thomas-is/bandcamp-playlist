<?php

class ToP {

    private $proc = array();

    const DS   = DIRECTORY_SEPARATOR;
    const PROC = Config::ROOT.self::DS.Config::PROC;

    public function __construct() {
        $pdirs = array();
        foreach(scandir(self::PROC) as $dir) 
            if( is_numeric($dir) ) $pdirs[] = $dir;
        if( empty($pdirs) ) return;
        foreach($pdirs as $id) 
            $this->proc[(int) $id] = new Process( (int) $id );
    }
    public function start($id)  { return @$this->proc[(int)$id]->start();  } 
    public function cancel($id) { return @$this->proc[(int)$id]->cancel(); }
    public function kill($id)   { return @$this->proc[(int)$id]->kill(); }
    public function state() {
        $top = array();
        if( empty($this->proc) ) return $top;
        foreach($this->proc as $proc)
            $top[$proc->id()] = array("state"=>$proc->state(), "title"=>$proc->get_title() );
        return $top;
    }
    public function clean() {
        if( empty($this->proc) ) return;
        foreach($this->proc as $proc) $proc->clean();
    }

}    

?>
