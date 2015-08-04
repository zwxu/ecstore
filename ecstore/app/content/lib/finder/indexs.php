<?php

 
/**
* finder 显示类
*/
class content_finder_indexs 
{
	/**
	* 构造方法 实例化APP类
	* @param object $app
	*/
    function __construct(&$app) 
    {
        $this->app = $app;
    }//End Function
    
	/**
	* 显示在finder列表上的标题
	* @var string 
	*/
    public $column_edit='编辑';
	
	/**
	* 显示在finder列表上的标题的宽度
	* @var string 
	*/
    public $column_edit_width='40';
	
	/**
	* 列表编辑的显示数据
	* @param array $row finder上的一条记录
	* @return string
	*/
    public function column_edit($row){
        return '<a href="index.php?app=content&ctl=admin_article_detail&act=edit&article_id=' . $row['article_id'] . '" target="_blank" >'.app::get('content')->_('编辑').'</a>';
    }
	
	/**
	* 显示在finder列表上的标题
	* @var string 
	*/
    public $column_preview='预览';
	
	/**
	* 显示在finder列表上的标题的宽度
	* @var string 
	*/
    public $column_preview_width='40';
	
	/**
	* 列表预览的显示数据
	* @param array $row finder上的一条记录
	* @return string
	*/
    public function column_preview($row){
        return '<a href="' .  app::get('site')->router()->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'index', 'arg0'=>$row['article_id'])) . '" target="_blank" >'.app::get('content')->_('预览').'</a>';
    }
}//End Class
