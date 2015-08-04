<?php

 
class b2c_finder_member_shopbbs{    
    function __construct(&$app){
        $this->app=$app;
        $this->ui = new base_component_ui($this);
    }    
    
    var $detail_basic = '基本信息';
    function detail_basic($comment_id){ 
        $app = app::get('b2c');
        $mem_com = $app->model('member_comment');
        $msg_data = $mem_com->get_msg($comment_id);
        $reply_msg_data = $mem_com->get_reply_msg($comment_id); 
        #print_r($reply_msg_data);//exit;    
        $render = $app->render();
        $render->pagedata['message'] = $msg_data;
        $render->pagedata['revert'] = $reply_msg_data;
        #$render->pagedata['object_type'] = $gask_data['object_type'];
        return $render->fetch('admin/member/shopbbs_items.html');
    }  
    
}
