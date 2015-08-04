<?php

class cellphone_mdl_column extends dbeav_model{

     //var $has_tag = true;
     
    function __construct($app){
        parent::__construct($app);
        //使用meta系统进行存储
       $this->use_meta();
    }
     
    
   function getcollist($coltype,$col= '*'){
 
	$curtime=time();
	if($col=='*'){
	
	$sql = 'select * from sdb_cellphone_column where is_active=true and columntype_id='.intval($coltype).' and  start_time<= '.$curtime.' and end_time>= '.$curtime;
	$result = $this->db->select($sql);
    return $result;
	
	}
	else{
	$sql = 'select '.$col.' from sdb_cellphone_column where is_active=true and columntype_id='.intval($coltype).' and start_time<= '.$curtime.' and end_time>= '.$curtime;
	
    $result = $this->db->select($sql);
    return $result;
	}
	
	
	}
}