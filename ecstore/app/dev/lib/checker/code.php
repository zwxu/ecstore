<?php

 

class dev_checker_code implements dev_interface_checker {
    
    public function worker($directory,$file){
        $file_postion = "$directory/$file";
        $content = file_get_contents($file_postion);
        preg_match_all('/->(display|page)\((.*)\);/',$content,$ret);
        var_dump($ret);
        die();
        return true;
    }
    
    public function exception($directory,$file){
        $file_postion = "$directory/$file";
        if(is_file($file_postion)){ 
            if(substr($file,-3,3) != 'php') return true;
        }
        if(is_dir($file_postion)){
            return false;
        }
        return false;
    }
    
}
