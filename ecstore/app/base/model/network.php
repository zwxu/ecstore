<?php

 

class base_mdl_network extends base_db_model{

    function invite($url,$message=null){
        $self_url = $this->system->base_url();
        $key = md5(rand(0,time()));
        $res = $this->call($url,'guest_ping',$key,$message);
        if($res){
            $data = array(
                    'node_name'=>$res->name,
                    'node_url'=>$res->url,
                    'link_status'=>'wait',
                    'sitekey'=>$key,
                    'node_detail'=>$res->desc,
                );
            $this->insert($data);
            return true;
        }else{
            return false;
        }
    }

    function call(){
        if(!$this->json_rpc){
            $this->json_rpc = &$this->app->load('json_rpc');
        }
        $args = func_get_args();
        $url = array_shift($args);
        $func = array_shift($args);
        array_unshift($args,$this->system->base_url());
        return $this->json_rpc->call($url,$func,$args);
    }
  
}
