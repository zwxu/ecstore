<?php
/*
 * @package content
 * @subpackage article
 * @author edwin.lzh@gmail.com
 * @license 
 */
class content_article_node 
{
	/**
	* @var string 版本
	*/
    const VERSION = '0.1';
	
	/**
	* @var array 节点数组
	* @access private
	*/
    private $_node_objects = array();
	/**
	* @var array 节点数组
	* @access private
	*/
    private $_node_maps = array();

	private $_all_nodes = null;

	/**
	* 构造方法,实例化MODEL
	*/
    function __construct() 
    {
        $this->model = app::get('content')->model('article_nodes');
        $this->layout_res_dir = app::get('content')->res_dir.'/layout';
    }//End Function
    
	/**
	* 获取node地址
	* @param int $node_id node Id
	* @param bool $kvstore 是否KV
	* @return string 
	*/
    public function get_node_path_url($node_id, $kvstore=false) 
    {
        $node = $this->get_node($node_id, $kvstore);
        if(empty($node))    return '';
        $nodeArr = explode(',', $node['node_path']);
		$contains_ = false;
        foreach($nodeArr AS $id){
            $node = $this->get_node($id, $kvstore);
			$node_pagename = str_replace('-','_',$node['node_pagename']);
            if(substr_count($node_pagename,'_')>0){
				$contains_ = true;
			}
			$node_url[] = $node_pagename;
        }
        return @join(($contains_?',':'_'), $node_url);
    }//End Function
    
    /**
	* 获取node链接和标题
	* @param int $node_id node Id
	* @param bool $kvstore 是否KV
	* @return array 
	*/
    public function get_node_path($node_id, $kvstore=false) 
    {
        $node = $this->get_node($node_id, $kvstore);
        if(empty($node))    return '';
        /*if(trim($node['content'])=='')return array(
                        'link'  => 'javascript:void(0);', 
                        'title' =>  $node['node_name'],
                    );*/
        $obj = app::get('site')->router();

        $nodeArr = explode(',', $node['node_path']);
        foreach($nodeArr AS $id){
            $node = $this->get_node($id, $kvstore);
            $path[] = array(
                        'link'  =>  (   ($node['homepage']=='true') 
                                        ? $obj->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'i', 'arg0'=>$id)) 
                                        : $obj->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$id)) 
                                    ), 
                        'title' =>  $node['node_name'],
                    );
        }
        return $path;
    }//End Function
    
	/**
	* 存KV node_info_ 的值
	* @param int $article_id 文章ID
	* @param string $value KV存入的值
	*/
    public function store_node_kvstore($node_id, $value) 
    {
        return base_kvstore::instance('cache/content/nodes')->store('node_info_'.$node_id, $value);
    }//End Function
	
	/**
	* 获取 KV node_info_... 的值
	* @param int $article_id文章ID
	* @param string $value KV存入的值
	*/
    public function fetch_node_kvstore($node_id, &$value) 
    {
        return base_kvstore::instance('cache/content/nodes')->fetch('node_info_'.$node_id, $value);
    }//End Function
	
	/**
	* 删除 KV node_info_... 的值
	* @param int $article_id文章ID
	*/
    public function delete_node_kvstore($node_id) 
    {
        return base_kvstore::instance('cache/content/nodes')->store('node_info_'.$node_id, array(), 1);
    }//End Function

	/**
	* 取KV content.kvstore_nodes_change 的值
	*/
    public function fetch_nodes_change() 
    {
        return app::get('content')->getConf('content.kvstore_nodes_change');
    }//End Function
	
	/**
	* 设置KV content.kvstore_nodes_change 的值
	*/
    public function store_nodes_change() 
    {
        return app::get('content')->setConf('content.kvstore_nodes_change', time());
    }//End Function

	/**
	* 获取单条node的值
	* @param int $node_id node Id
	* @param bool $kvstore 是否启用KV
	* @return array 单条node数组
	*/
    public function get_node($node_id, $kvstore=false) 
    {
        $node_id = intval($node_id);
        if($kvstore===false || !isset($this->_node_objects[$node_id])){
            if($kvstore===true && $this->fetch_node_kvstore($node_id, $value)===true){
                $this->fetch_nodes_change();     //判断缓存过期
            }else{
                $value = $this->model->select()->where('node_id = ?', $node_id)->instance()->fetch_row();
                if($kvstore !== false)   $this->store_node_kvstore($node_id, $value);
            }
            $this->_node_objects[$node_id] = $value;
        }else{
            $this->fetch_nodes_change();     //判断缓存过期
        }
        return $this->_node_objects[$node_id];
    }//End Function    
	
    /**
	* 父节点下的子节点数据
	* @param int $parent_id 父节点id
	* @return 节点数组值
	*/
    public function get_nodes($parent_id=0) 
    {
        $parent_id = intval($parent_id);
		if(is_null($this->_all_nodes)){
			$this->_all_nodes = array();
			$nodes = app::get('content')->model('article_nodes')->select()->order('ordernum ASC')->instance()->fetch_all();
			foreach($nodes AS $node){
				$this->_all_nodes[$node['parent_id']][] = $node;
			}
		}
		return $this->_all_nodes[$parent_id]; 
        //return $this->model->select()->where('parent_id = ?', $parent_id)->order('ordernum ASC')->instance()->fetch_all();
    }//End Function

    /**
	* 节点的map
	* @param int $node_id 节点id
	* @param int $setp 路径
	* @return array 节点路由
	*/
    public function get_maps($node_id=0, $step=null) 
    {
        $step_key = (is_null($step)) ? 'all' : $step;
        if(!isset($this->_node_maps[$node_id][$step_key])){
            $rows = $this->get_nodes($node_id);
            $step = ($step==null) ? $step : $step-1;
            foreach($rows AS $k=>$v){
                if($v['has_children']=='true' && ($step==null || $step>=0)){
                    $rows[$k]['childrens'] = $this->get_maps($v['node_id'], $step);
                }
            }
            $this->_node_maps[$node_id][$step_key] = $rows;
        }else{
            $this->fetch_nodes_change();     //判断缓存过期
        }
        return $this->_node_maps[$node_id][$step_key];
    }//End Function
    
    /**
	* 获取节点的map
	* @param string $node_id
	* @param int $setp 路径
	* @param array
	*/
    public function get_nodeindex_selectmaps( $node_id=0,$step=null ) {
        $rows = $this->get_maps($node_id, $step);
        return $this->parse_selectmaps($rows);
    }//End Function
	
    /**
	* 获取节点的map
	* @param string $node_id
	* @param int $setp 路径
	* @param array
	*/
    public function get_selectmaps($node_id=0, $step=null) 
    {
        $rows = $this->get_maps($node_id, $step);
        return $this->parse_selectmaps($rows);
    }//End Function
	
    /**
	* 获取节点的map
	* @param string $node_id
	* @param int $setp 路径
	* @param array
	*/
    public function get_listmaps($node_id=0, $step=null) 
    {
        $rows = $this->get_maps($node_id, $step);
        return $this->parse_listmaps($rows);
    }//End Function

	/**
	* 格式化节点的map 是否首页，标题名
	* @param array $rows 节点MAP
	* @param array
	*/
    private function parse_selectmaps($rows) 
    {
        $data = array();
        foreach((array)$rows AS $k=>$v){
            $data[] = array('node_id'=>$v['node_id'], 'homepage'=>$v['homepage'],'step'=>$v['node_depth'], 'node_name'=>$v['node_name']);
            if($v['childrens']){
                $data = array_merge($data, $this->parse_selectmaps($v['childrens']));
            }
        }
        return $data;
    }//End Function
	
	/**
	* 格式化节点的map 是否首页，标题名
	* @param array $rows 节点MAP
	* @param array
	*/
    private function parse_listmaps($rows) 
    {
        $data = array();
        foreach((array)$rows AS $k=>$v){
            $children = $v['childrens'];
            if(isset($v['childrens']))  unset($v['childrens']);
            $data[] = $v;           
            if($children){
                $data = array_merge($data, $this->parse_listmaps($children));
            }
        }
        return $data;
    }//End Function
    
	/**
	* 编辑节点
	* @param int 
	* @param string $layout 布局
	*/
    public function editor($id, $layout) 
    {
        $bodys = kernel::single('content_article_node')->get_node($id);
        if(empty($bodys['content'])){
            $data['content'] = file_get_contents($this->layout_res_dir . '/1-column/layout.html');
            app::get('content')->model('article_nodes')->update($data, array('node_id'=>$id));
        }else{
            if($layout){
                $data['content'] = file_get_contents($this->layout_res_dir . '/' . $layout . '/layout.html');
                if(app::get('content')->model('article_nodes')->update($data, array('node_id'=>$id))){
                    //app::get('content')->model('article_indexs')->update(array('uptime'=>time()), array('article_id'=>$article_id));
                    $setting = $this->get_layout($layout);
                    $setting['slotsNum'] = intval($setting['slotsNum']);
                    if($setting['slotsNum']>0){
                        $setting['slotsNum']--;
                        $db = kernel::database();
                        $db->exec("update sdb_site_widgets_instance set core_slot=".$db->quote($setting['slotsNum'])." where core_slot>".intval($setting['slotsNum'])." and core_file='content_node:".$id."'");
                    }
                }
            }
        }
        return true;
    }//End Function
	
	/**
	* 所有布局
	* @return array
	*/
    public function get_layout_list(){
        $handle = opendir($this->layout_res_dir);
        $t = array();

        while(false!==($file=readdir($handle))){
            if(in_array($file, array('.', '..', '.svn')))   continue;

            $layouts[$file] = require($this->layout_res_dir . '/' . $file . '/layout_' . $file . '.php');
        }
        closedir($handle);
        return $layouts;
    }//End Function
	
	/**
	* 单个布局
	* @param string $layout 布局
	* @return string
	*/
    public function get_layout($layout) 
    {
        $layouts = $this->get_layout_list();
        return $layouts[$layout];
    }//End Function
    
    
    
}//End Class
