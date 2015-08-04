<?php

 

class dev_checker_webshell implements dev_interface_checker {
    var  $webshell_pattern = "/(iframe)|( exec\()|(system\()|(shell_exec)|(proc_open) |(wscript.shell)/i";
    var $max_file_size = 2000000;
   
     public function worker($directory,$file){
        $handle=file(trim($directory."/".$file));
        $notes_length=count($handle);
        for($i=0; $i<$notes_length; $i++){
            $content = $handle[$i];
            if(preg_match($this->webshell_pattern,$content,$arr)){
                print ("warning webshell code ".$arr[0]." found in ".$directory."/".$file." line ". ++$i." \n");
            }
        }
        unset($handle);
        
        return true;
    }
    
    public function exception($directory,$file){
        $file_postion = "$directory/$file";
        if(is_file($file_postion)){ 
            $mime=pathinfo($file_postion);
		    if($mime["extension"] != "php"){
	            return true;	     
		    }
            if(preg_match('/\/cache\/.*\/[0-9a-f]{32}\.php/',$file_postion)){            //排除缓存文件
                return true;
            }
            if(in_array($file,array('cachedata.php','system.log.php'))){
                return true;
            }
            if(filesize($file_postion) > $this->max_file_size){
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
