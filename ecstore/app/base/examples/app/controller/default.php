<?php
 
class base_ctl_default extends base_controller{
    
    function index(){
        $this->pagedata['project_name'] = '';
        $this->display('default.html');
    }
    
}