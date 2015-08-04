<?php

 

class content_service_nodetplsource 
{
	/**
	* 获取最后修改时间
	* @param int $id 节点id
	* @return time
	*/
    public function last_modified($id) 
    {
        $info = kernel::single('content_article_node')->get_node($id);
        return $info['uptime'];
    }//End Function

	/**
	* 根据ID获得节点内容
	* @param int $id 节点ID
	* @return array
	*/
    public function get_file_contents($id) 
    {
        $info = kernel::single('content_article_node')->get_node($id);
        return $info['content'];
    }//End Function 
}//End Class 18:55 2010-6-9
