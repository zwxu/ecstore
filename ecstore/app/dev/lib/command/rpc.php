<?php

 
class dev_command_rpc extends base_shell_prototype{

    function command_call(){
        $args = func_get_args();
        $method = array_shift($args);
        $params = array();
        foreach($args as $set){
            if(preg_match('/^([a-z\_0-9]+)=(.+)$/',$set,$match)){
                $params[$match[1]] = $match[2];
            }
        }
        $var = app::get('base')->remote(1)->call($method,$params);
    }

}
