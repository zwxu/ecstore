<?php

class aftersales_mdl_refunds extends ectools_mdl_refunds{
    
    public function __construct($app){
        $this->app = app::get('ectools');
        $this->db = kernel::database();
        
        $this->schema = $this->get_schema();
        $this->metaColumn = $this->schema['metaColumn'];
        $this->idColumn = $this->schema['idColumn'];
        $this->textColumn = $this->schema['textColumn'];
        $this->skipModifiedMark = ($this->schema['ignore_cache']===true) ? true : false;
        if(  !is_array( $this->idColumn ) && array_key_exists( 'extra',$this->schema['columns'][$this->idColumn] )  ){
            $this->idColumnExtra = $this->schema['columns'][$this->idColumn]['extra'];
        }
        $this->use_meta();
    }
}
