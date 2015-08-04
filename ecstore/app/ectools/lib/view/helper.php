<?php

 
class ectools_view_helper{
    
    function __construct($app){
        $this->app = $app;
    }
    function modifier_barcode($data){
        return kernel::single('ectools_barcode')->get($data);
    }
}
