<?php

 
/**
* 文章导航类,实现site_interface_menu接口
* 
*/
class content_menu_article_index implements site_interface_menu 
{
	/**
	* 添加菜单,选择文章页后的生成的HTML表单
	* @param array $config 配置信息
	* @return array '文章ID'=>array('type'=>...)
	*/
    public function inputs($config=array()){
        
        $inputs = array(
            app::get('content')->_("文章ID") => array('type'=>'input', 'size'=>5, 'title'=>'article_id', 'name'=>'article_id', 'value'=>$config['article_id']),
        );
        return $inputs;
    }
	/**
	* 设置params 和config的值
	* @param array $post post数组
	*/
    public function handle($post){
        
        $this->params['article_id'] = $post['article_id'];

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
