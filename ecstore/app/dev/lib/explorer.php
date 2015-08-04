<?php

 
class dev_explorer{
    
    function set_checker(&$checker){
        if( $checker instanceof dev_interface_checker){  #检查器要实现dev_interface_checker接口
            $this->checker = $checker;
        }else{
             throw new Exception('checker illegal');
        }
    }
    
    function start($dir){
        chdir($dir);
        $this->recursion('.');
    }

    private function recursion($directory){
        $dir = @opendir($directory);     
        while ($file = @readdir($dir)) {
            if(substr($file,0,1) == '.') continue;      
            if(call_user_func_array(array($this->checker,'exception'),array($directory,$file))) continue;      
            if(is_dir("$directory/$file")){     
                $this->recursion("$directory/$file"); 
            }else{
                call_user_func_array(array($this->checker,'worker'),array($directory,$file));
            }        
        }    
    }

}
