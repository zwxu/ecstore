<?php
class b2c_goods_description_comments{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid, &$aGoods=null, $type='ask',$custom_view=""){
        $render = $this->app->render();

        $objComment = kernel::single('business_message_disask'); 
        $commentList = $objComment->getGoodsIndexComments($gid,$type);
        if(is_array($gid)) $gid = $gid['type_id']; 
        $aComment['list'][$type] = $commentList['data'];
        $aComment['page']['start'] = $commentList['start'];
        $aComment['page']['end'] = $commentList['end'];
        $aComment[$type.'Count'] = $commentList['total'];
        $aComment[$type.'current'] = $commentList['current_page'];
        $aComment[$type.'totalpage'] = $commentList['page'];
        //获取提示文字 @lujy
        $aComment[] = $objComment->get_setting($type);

        for($i=0;$i<$commentList['page'];$i++){
            $aComment[$type.'Page'][] = $i;
        }
        $aId = array();
        if ($commentList['total']){
            foreach($aComment['list'][$type] as $rows){
                $aId[] = $rows['comment_id'];
            }
            if(count($aId)){
                $addition = array();
                $temp = app::get('b2c')->model('member_comments')->getList('comment_id,for_comment_id',array('for_comment_id' => $aId,'comments_type'=>'3','display' => 'true'));
                foreach((array)$temp as $rows){
                    $aId[] = $rows['comment_id'];
                    $addition[$rows['for_comment_id']][] = $rows['comment_id'];
                }
            }
            if(count($aId)) $aReply = (array)$objComment->getCommentsReply($aId, true);
            reset($aComment['list'][$type]);
            foreach($aComment['list'][$type] as $key => $rows){
                foreach($aReply as $rkey => $rrows){
                    if($rows['comment_id'] == $rrows['for_comment_id']){
                        $aComment['list'][$type][$key]['items'][] = $aReply[$rkey];
                    }elseif(!empty($addition) && isset($addition[$rows['comment_id']]) && in_array($rrows['for_comment_id'],$addition[$rows['comment_id']])){
                        $aComment['list'][$type][$key]['items'][] = $aReply[$rkey];
                    }
                }
                reset($aReply);
            }
        }else{
            $aComment['null_notice'][$type] = $this->app->getConf('comment.null_notice.'.$type);
        }

        $goods_point_status = app::get('b2c')->getConf('goods.point.status');
        $render->pagedata['point_status'] = $goods_point_status ? $goods_point_status: 'on';
        $basic_setting = $objComment->get_basic_setting();
        if( $basic_setting['display'] == 'soon'){
            $submit_msg = $this->app->getConf('comment.submit_display_notice.'.$type);
        }
        else {
            $submit_msg = $this->app->getConf('comment.submit_hidden_notice.'.$type);
        }
        $render->pagedata['base_setting'] = $basic_setting;
        $render->pagedata['submit_msg'] = $submit_msg;

        //todo 确认gask_type的作用
        $gask_type = unserialize($this->app->getConf('gask_type'));
        if($gask_type){
            foreach($gask_type as $key => $val){
                $gask_type[$key]['total'] = $objComment->get_ask_total($gid,$val['type_id'],'ask');
            }
            $render->pagedata['gask_type'] = $gask_type;
        }


        $render->pagedata['comment'] = $aComment;

        $aGoods['goods_id'] = $gid;
        $render->pagedata['goods'] = $aGoods;

		$file = $custom_view ? $custom_view : 'site/product/description/'.$type.'.html';
		if($custom_view){
			return $render->fetch($file,'',true);
        }
        return $render->fetch($file);
    }

}

