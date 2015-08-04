<?php

 
/**
 * 这个类实现图片批量重新生成水印
 * @version 0.1
 * @package image.lib
 */
class image_rebuild{
	/**
	 * 批量重新生成水印图片
	 * @param int 批量开始的游标的位置
	 * @param array 过滤条件信息
	 * @return int 返回处理过的图片的总数
	 */
    function run(&$cursor_id,$params){
          //每次最多处理2个
        $limit = 2;
        $model = app::get('image')->model('image');
        $db = kernel::database();
        if($params['filter']['image_id']=='_ALL_'||$params['filter']['image_id']=='_ALL_'){
            unset($params['filter']['image_id']);
        }
        $where = $model->_filter($params['filter']);
        $where .= ' and last_modified<='.$params['queue_time'];
        $rows = $db->select('select image_id from sdb_image_image where '.$where.' order by last_modified desc limit '.$limit);
        foreach($rows as $r){
			if($params['watermark'] == 'false')
			{
				$params['watermark'] = false;
			}
            $model->rebuild($r['image_id'],$params['size'],$params['watermark']);

        }
        $r = $db->selectrow('select count(*) as c from sdb_image_image where '.$where);
        return $r['c'];

    }
}
