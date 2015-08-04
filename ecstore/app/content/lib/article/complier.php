<?php

/**
* 关键加载
*/
class content_article_complier 
{
    /**
	* 模版标签
	* @param array $tag_args 标签数组
	* @param object $smarty smarty实例
	* @return 返回HTML标签
	*/
    function compile_widgets($tag_args, &$smarty){
        if($tag_args['id']){
            $id = ','.$tag_args['id'];
        }
        return '$s=$this->_files[0];
        $i = intval($this->_wgbar[$s]++);
        echo \'<div class="shopWidgets_panel" base_file="\'.$s.\'" base_slot="\'.$i.\'" base_id='.$tag_args['id'].'  >\';
        kernel::single(\'site_theme_widget\')->admin_load($s,$i'.$id.');echo \'</div>\';';

    }
}//End Class
