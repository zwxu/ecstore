<?php

 
class b2c_finder_member_gask{    
    function __construct(&$app){
        $this->app=$app;
        $this->ui = new base_component_ui($this);
    }    
    
    var $detail_basic = '基本信息';
    function detail_basic($comment_id){ 
        $app = app::get('b2c');
        $render = $app->render();
        $mem_com = $app->model('member_comments');
        $mem_com->set_admin_readed($comment_id);
        $goods = $app->model('goods');
        $row = $mem_com->getList('*',array('comment_id' => $comment_id));
        $gask_data = $row[0];
        $gask_data['addon'] = unserialize($gask_data['addon']);
        if($gask_data['type_id']){
            $goods_point = app::get('business')->model('comment_goods_point'); // modified by cam
            $gask_data['goods_point'] = $goods_point->get_single_point($gask_data['type_id']);
            $render->pagedata['singlepoint']  = $goods_point->get_comment_point($comment_id);
        }
        $render->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $reply_data = $mem_com->getList('*',array('for_comment_id' => $comment_id)); 
        $goods_data=$goods->getList('name,thumbnail_pic,udfimg,marketable,view_count,view_w_count,buy_count,buy_w_count,image_default_id,comments_count',array('goods_id'=>$gask_data['type_id']));
        $goods_data = current($goods_data);
        $gask_data['goodname'] = $goods_data['name'];
        if($goods_data){
            $render->pagedata['url'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','full'=>1,'act'=>'index','arg'=>$gask_data['type_id']));
            $render->pagedata['goods'] = $goods_data;
        }
		$gask_type = unserialize($this->app->getConf('gask_type'));
		if(is_array($gask_type) && $gask_data['gask_type'])
		{
			foreach($gask_type as $gask_k => $gask_v)
			{
				if($gask_v['type_id'] == $gask_data['gask_type'])
				{
					$gask_data['gask_type'] = $gask_v['name'];
					break;
				}
			}
		}
		if(is_array($gask_type) === false) unset($gask_data['gask_type']);
		$render->pagedata['comment'] = $gask_data;
        $render->pagedata['reply'] = $reply_data;
        $render->pagedata['object_type'] = $mem_com->type;
        $imageDefault = app::get('image')->getConf('image.set');
        $render->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        return $render->fetch('admin/member/'.$mem_com->type.'.html',$app->app_id);
    }  
    
}
