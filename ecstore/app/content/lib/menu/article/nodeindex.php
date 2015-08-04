<?php

/**
* 节点导航类,实现site_interface_menu接口
* 
*/
class content_menu_article_nodeindex implements site_interface_menu 
{
	/**
	* 添加菜单,选择节点首页后的生成的HTML表单
	* @access public
	* @param array $config 配置信息
	* @return array '文章ID'=>array('select'=>...)
	*/
    public function inputs($config=array()){
        $selectmaps = kernel::single('content_article_node')->get_nodeindex_selectmaps();
        if(is_array($selectmaps)){
            foreach($selectmaps as $key => $select){
                if( $select['homepage']!='true' ) continue;
                $options[$select['node_id']] = str_repeat(' ', $select['step']-1) . $select['node_name'];
            }
        }

        $inputs = array(
            app::get('content')->_("文章节点") => array('type'=>'select', 'title'=>'node_id', 'required'=>true, 'name'=>'node_id', 'value'=>$config['node_id'], 'options'=>$options),
        );
        return $inputs;
    }
	
	/**
	* 设置params 和config的值
	* @param array $post post数组
	*/
    public function handle($post){
        
        $this->params['node_id'] = $post['node_id'];

        $this->config = $this->params;
    }
	
	/**
	* 获取params的值
	* @return array
	*/
    public function get_params(){
        return $this->params;
    }
	
	/**
	* 获取config的值
	* @return array
	*/
    public function get_config(){
        return $this->config;
    }
}//End Class
