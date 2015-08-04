<?php
 
 
class b2c_ctl_admin_member_msgbox extends desktop_controller{

    var $workground = 'b2c.workground.member';

    function index(){
          $member_comments = $this->app->model('member_comments');
          $member_comments->set_type('msgtoadmin');
          $this->finder('b2c_mdl_member_comments',array(
          'title'=>app::get('b2c')->_('站内消息'),
            'use_buildin_recycle'=>true,
            'use_buildin_filter'=>true,
            'base_filter' =>array('for_comment_id' => 0,'has_sent'=>'true'),//增加过滤，只显示已经发送的站内信@lujy
            'finder_aliasname'=>'msgbox',
            'finder_cols'=>'author,title,comment,time',
            'delete_confirm_tip'=>app::get('b2c')->_('删除后会员发件箱中也会删除,确定删除吗?')
          ));

    }
    
   function to_reply(){
      $this->begin("javascript:finderGroup["."'".$_GET["finder_id"]."'"."].refresh()");
      $comment_id = $_POST['comment_id'];
      $comment = $_POST['reply_content'];
      if($comment_id&&$comment){
         $member_comments = kernel::single('b2c_message_msg');
         if($member_comments->to_reply($_POST)){
            $this->end(true,app::get('b2c')->_('回复成功')); 
         }
         else{
             $this->end(false,app::get('b2c')->_('回复失败')); 
         }
      }
      else{
         $this->end(false,app::get('b2c')->_('内容不能为空'));
      }
  } 
  
 
}
