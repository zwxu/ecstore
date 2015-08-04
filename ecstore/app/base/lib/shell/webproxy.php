<?php

 
class base_shell_webproxy extends base_shell_loader{
    
    function __construct(){
        header('Content-type: text/html;charset=utf-8');
        ignore_user_abort(false);
        ob_implicit_flush(1);
        ini_set('implicit_flush',true);
        
        set_error_handler(array(&$this,'error_handle'));
        chdir('data');
        kernel::$console_output = true;
        
        while(ob_get_level()){
            ob_end_flush();
        }
        
        echo str_repeat("\0",1024);
        //$this->buildin_commander = new base_shell_buildin($this);
        parent::__construct();
    }
    
    function exec_command($command){
        echo '<pre>';
        echo '>'. $command."\n";
        parent::exec_command($command);
        echo '</pre>';
    }
    
    function error_handle($code,$msg,$file,$line){
        
        if($code == ($code & (E_ERROR ^ E_USER_ERROR ^ E_USER_WARNING))){
            echo 'ERROR: ',$code,':',$msg,'  @',basename($file),':',$line."\n\n";
            if($code == ($code & (E_ERROR ^ E_USER_ERROR))){
                exit;
            }
        }
        return true;
    }

}
