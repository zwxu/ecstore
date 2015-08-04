<?php

 

class base_mdl_queue extends base_db_model{

    var $limit = 100; //最大任务并发
    var $task_timeout = 300; //单次任务超时

    function flush(){
        $base_url = kernel::base_url();
        foreach($this->db->select('select queue_id from sdb_base_queue limit '.$this->limit) as $r){
            $this->runtask($r['queue_id']);
        }
    }

    function runtask($task_id){
        $http = new base_httpclient;
        $_POST['task_id'] = $task_id;
        $url =  kernel::openapi_url('openapi.queue','worker',array('task_id'=>$task_id));
        kernel::log('Spawn [Task-'.$task_id.'] '.$url);
        
        //99%概率不会有问题
        $http->hostaddr = $_SERVER["SERVER_ADDR"]?$_SERVER["SERVER_ADDR"]:'127.0.0.1';
        $http->hostport = $_SERVER["SERVER_PORT"]?$_SERVER["SERVER_PORT"]:'80';
        $http->set_timeout(2);
        kernel::log($http->post($url,$_POST));
    }
      
}
