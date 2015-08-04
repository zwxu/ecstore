<?php
set_time_limit(0);
$root_dir = realpath(dirname(__FILE__).'/');
require_once($root_dir."/config/config.php");
define('APP_DIR',ROOT_DIR."/app/");
@include_once(APP_DIR.'/base/defined.php');

require_once(APP_DIR.'/base/kernel.php');
if(!kernel::register_autoload()){
    require(APP_DIR.'/base/autoload.php');
}

class sphinx_search{

    var $name = 'sphinx搜索';
    var $servicename = 'B2C商品';
    var $description = '基于sphinx开发的搜索引擎';
    var $arr_query=array();
    function __construct(){
		try{
			$is_exists = class_exists('SphinxClient');
		}catch(Exception $e){
			require(ROOT_DIR.'/app/sphinx/sphinxapi/sphinxapi.php');
		}
        
    	$searchConf = unserialize(app::get('sphinx')->getConf('sphinx_search_goods'));
        $this->hosts = preg_split('/[,;\s]+/', $searchConf['sphinx_server']);
        //print_r($this->hosts);
	    $this->index = $searchConf['sphinx_index'];
	    $this->timeout = $searchConf['sphinx_time']?$searchConf['sphinx_time']:3;
        $this->max_limit = $searchConf['sphinx_max_limit'] ? $searchConf['sphinx_max_limit'] : 1000;
        $this->obj = new SphinxClient();
    }

    function get_server()
    {
        $key = array_rand($this->hosts);
        return $this->hosts[$key];
    }//End Function

    function create(){
        $hosts = $this->get_server();
        list($server, $port) = explode(':', $hosts);
    	$this->obj->SetServer($server, intval($port));
        $this->obj->SetConnectTimeout($this->timeout);
        $this->obj->setMatchMode(SPH_MATCH_EXTENDED2);
    }

    function link(){
        $hosts = $this->get_server();
        list($server, $port) = explode(':', $hosts);
    	$this->obj->SetServer($server, intval($port));
        $this->obj->SetConnectTimeout($this->timeout);
        $this->obj->setMatchMode(SPH_MATCH_EXTENDED2);
    }

    function query(){
        $this->link();
        $this->obj->SetFilter('store_id',array(322));
        $this->obj->setLimits(0, 10000, 10000);
        $this->obj->AddQuery('');
        echo '<pre>';
        echo $this->obj->GetLastError();
        print_r($this->obj);
        $result=$this->obj->RunQueries();
        print_r(array_keys($result[0]['matches']));
        echo '</pre>';
    }

}
$sphinx=new sphinx_search();
$sphinx->query();