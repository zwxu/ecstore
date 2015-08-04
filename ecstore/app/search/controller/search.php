<?php

class search_ctl_search extends desktop_controller {

    private $_conf_pref = 'service.';

    function index() {
        $this->finder('search_mdl_search', array(
            'title' =>  app::get('site')->_('索引管理'),
            'base_filter' => array(),
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => false,
            'use_buildin_export' => false,
            'use_buildin_selectrow'=>false,
            'actions'=>array(
                array(
                    'label' => app::get('site')->_('分词器默认配置'),
                    'href' => 'index.php?app=search&ctl=search&act=set_default_segment',
                    'target' => 'dialog::{frameable:true, title:\''.app::get('site')->_('分词器默认配置').'\', width:600, height:400}',
                ),
            ),

        ));
    }

    function set_default() {
    	$this->begin('index.php?app=search&ctl=search&act=index');
        $method = $_GET['method'];
        $type = $_GET['type'];
        $name = $_GET['name'];
    	if($method == 'open'){
    	    app::get('base')->setConf('server.'.$type, $name);
    	}else{
    	    app::get('base')->setConf('server.'.$type,'');
    	}
        $this->end(true, $this->app->_('搜索方式保存成功'));
    }

    function set_default_segment() {
        $filter = array();
        $config = app::get('base')->getConf('server.search_segment');
        $arr_search = app::get('base')->model('app_content')->getList('*', array(
                'content_type' => 'service',
                'content_name' => 'search_segment',
        ));
        foreach($arr_search AS $key=>$val){
            $arr_search[$key]['name'] = kernel::single($val['content_path'])->name;
        }
        $this->pagedata['search_name'] = $config ? $config : 'search_service_segment_cjk';
        $this->pagedata['arr_search'] = $arr_search;
        $this->page('search/index.html');

    }

    function save_segment() {
    	$this->begin('index.php?app=search&ctl=search&act=index');
        app::get('base')->setConf('server.search_segment', $_POST['select']);
        $this->end(true, $this->app->_('分词器保存成功'));
    }


    function start() {
        $default = $_GET['default'];
        $service = $_GET['service'];
        $default = 1;
        $content_name = $_GET['content_name'];
        app::get('base')->setConf($this->_conf_pref.$content_name, $service);
    }

    function reindex() {
        $type = $_GET['type'];
        $name = $_GET['name'];
    	$this->begin();
        search_core::segment();
    	foreach(kernel::servicelist($type) as $service){
    		if(get_class($service) == $name){
    		    $status = $service->reindex($msg);
                break;
            }
    	}
    	if($status)
            $this->end(true, $this->app->_($msg));
        else
            $this->end(false, $this->app->_($msg));
    }

    function status() {
        $type = $_GET['type'];
        $name = $_GET['name'];
    	$this->begin();
        search_core::segment();
    	foreach(kernel::servicelist($type) as $service){
    		if(get_class($service) == $name){
    		    $status = $service->status($msg);
                break;
            }
    	}
    	if($status)
            $this->end(true, $this->app->_($msg));
        else
            $this->end(false, $this->app->_($msg));
    }

    function optimize() {
        $type = $_GET['type'];
        $name = $_GET['name'];
    	$this->begin();
        search_core::segment();
    	foreach(kernel::servicelist($type) as $service){
    		if(get_class($service) == $name){
    		    $status = $service->optimize($msg);
                break;
            }
    	}
    	if($status)
            $this->end(true, $this->app->_($msg));
        else
            $this->end(false, $this->app->_($msg));
    }

    function clear() {
        $type = $_GET['type'];
        $name = $_GET['name'];
    	$this->begin();
        search_core::segment();
    	foreach(kernel::servicelist($type) as $service){
    		if(get_class($service) == $name){
    		    $status = $service->clear($msg);
                break;
            }
    	}
    	if($status)
            $this->end(true, $this->app->_($msg));
        else
            $this->end(false, $this->app->_($msg));
    }

}