<?php

 

class dev_checker_bom implements dev_interface_checker {
    
    public function worker($directory,$file){
        $file_postion = "$directory/$file";
        $content = file_get_contents($file_postion);
        if(substr($content,0,3)=="\xEF\xBB\xBF"){
            print     $file_postion;
            print "\n";
        }
        return true;
    }
    
    public function exception($directory,$file){
        $file_postion = "$directory/$file";
        if(is_file($file_postion)){ 
            if(preg_match('/.\/home\/cache\/.*\/[0-9a-f]{32}\.php/',$file_postion)){            //排除缓存文件
                return true;
            }
            if(in_array($file,array('cachedata.php','system.log.php'))){
                return true;
            }
        }
        if(is_dir($file_postion)){
            if(in_array($file,array('forumdata','attachment'))){
                return true;
            }
        }
        return false;
    }
    
}
