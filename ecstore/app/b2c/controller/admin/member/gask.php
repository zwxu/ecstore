<?php
 
 
class b2c_ctl_admin_member_gask extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';
    
    public function __construct($app)
    {
        parent::__construct($app);
    }

    function index(){  
        $member_comments = $this->app->model('member_comments');
        $member_comments->set_type('ask');
           $this->finder('b2c_mdl_member_comments',array(

            'title'=>app::get('b2c')->_('咨询列表'),
            'allow_detail_popup'=>false,
            'use_buildin_filter'=>true,
            'base_filter' =>array('for_comment_id' => 0),
            'actions'=>array(),'use_view_tab'=>true,
            ));

    }
  
    public function _views(){
        $gask_type = unserialize($this->app->getConf('gask_type'));
        if($gask_type){
            $member_comments = $this->app->model('member_comments');
            $show_menu = array();
            $show_menu[0]['label'] = app::get('b2c')->_('全部');
            $show[0]['optional'] = '';
            $show_menu[0]['filter'] = '';
            $show_menu[0]['href'] = "index.php?app=b2c&ctl=admin_member_gask&act=index&view=0";
            $show_menu[0]['addon'] = $member_comments->count($show_menu[0]['filter']);
            foreach($gask_type as $key => $val){
                $result['label'] = $val['name'];
                $result['optional'] = '';
                $result['filter'] = array('gask_type' => $val['type_id']);
                $result['href'] = "index.php?app=b2c&ctl=admin_member_gask&act=index&view=".($key+1);
                $result['addon'] = $member_comments->count($result['filter']);
                $show_menu[] = $result;
            }
            return $show_menu;
        }
        else{
            return null;
        }
        
    }
    function setting(){
        $member_comments = kernel::single('b2c_message_disask');
        $aOut = $member_comments->get_setting('ask');
         if(!$aOut['verifyCode']['ask']){
            $aOut['verifyCode']['ask']='off';
        }
        $aOut['aSwitch']['ask'] = array('on'=>app::get('b2c')->_('开启'), 'off'=>app::get('b2c')->_('关闭'));
        $aOut['aPower']['ask'] = array('null'=>app::get('b2c')->_('所有顾客都可咨询'), 'member'=>app::get('b2c')->_('只有注册会员才能咨询'));
        $aOut['verifyLCode']['ask'] = array('on'=>app::get('b2c')->_('开启'), 'off'=>app::get('b2c')->_('关闭'));
        $this->pagedata['setting']= $aOut;
         foreach(kernel::servicelist('ask_list') as $service){
            $this->pagedata['html'][]= $service->get_Html();
        }
        echo $this->fetch('admin/member/gask_setting.html');exit;
    }
    
    function to_setting(){
        $this->begin();
        $member_comments = kernel::single('b2c_message_disask');
        $aOut = $member_comments->to_setting('ask',$_POST);
        foreach(kernel::servicelist('ask_list') as $service){
         $service->save_setting($_POST);
        }
        $this->end('success',app::get('b2c')->_('设置成功'));
    }
    #回复咨询
    function to_reply(){
        $this->begin("javascript:finderGroup["."'".$_GET["finder_id"]."'"."].refresh()");
        #$this->begin("javascript:finderGroup['411c04.refresh()']");
        $comment_id = $_POST['comment_id'];
        $comment = $_POST['reply_content'];
        if($comment_id&&$comment){
            $member_comments = kernel::single('b2c_message_disask');
            $sdf = $member_comments->dump($comment_id);
            if($this->app->getConf('comment.display.ask') == 'reply'){
                $aData = $sdf;
                $aData['display'] = 'true';
                $member_comments->save($aData);
            }
            $sdf['comment_id']= '';
            $sdf['for_comment_id'] = $comment_id;
            $sdf['object_type'] = "ask";
            $sdf['to_id'] = $sdf['author_id'];
            $sdf['author_id'] = null;
            $sdf['author'] = app::get('b2c')->_('管理员');
            $sdf['title'] = '';
            $sdf['contact'] = '';
            $sdf['display'] = 'true';
            $sdf['lastreply'] = time();
            $sdf['time'] = time();
            $sdf['reply_name'] = app::get('b2c')->_('管理员');
            $sdf['comment'] = $comment;
            if($member_comments->send($sdf,'ask')){
                $comments = $this->app->model('member_comments');
                 $data['member_id'] = $sdf['to_id'];
                 $comments->fireEvent('gaskreply',$data,$sdf['to_id']);
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
    
 #设置显示
    function to_display($comment_id=0,$comment_display){ 
        if(!$comment_id) {echo  "参数错误";exit;}
        $this->begin("javascript:finderGroup["."'".$_GET["finder_id"]."'"."].refresh()");
         if($comment_display=="true"){
             $comment_display = "false";
         }
         else{
             $comment_display = "true";
         }
         $member_comments = kernel::single('b2c_message_disask');
         $goods_point = $this->app->model('comment_goods_point');
         $sdf = $member_comments->dump($comment_id);
         unset($sdf['goods_point']);
         $sdf['display'] = $comment_display;
         if($member_comments->save($sdf)){
            if($sdf['display']=='true' && $sdf['object_type'] == 'discuss' && $sdf['author_id']){
                $_is_add_point = app::get('b2c')->getConf('member_point');
                if($_is_add_point){
                    $obj_member_point = $this->app->model('member_point');
                    $obj_member_point->change_point($sdf['author_id'],$_is_add_point,$_msg,'comment_discuss',2,$sdf['type_id'],$sdf['author_id'],'comment');
                }
            }
            $goods_point->set_status($comment_id,$comment_display);
            $this->end(true,app::get('b2c')->_('操作成功'));
         }
         else{
             $this->end(fasle,app::get('b2c')->_('操作失败'));
         }        
    }
    
   
    
    #删除
    function delete_message($comment_id){
      $this->begin("javascript:finderGroup["."'".$_GET["finder_id"]."'"."].refresh()");
      $member_comment = kernel::single('b2c_message_disask');
      $aData = $member_comment->dump($comment_id);
      $member_id = $aData['author_id'];
      $reply = $member_comment->get_reply($comment_id);
      $aComId = array();
      $aComId[] = $comment_id;
      foreach($reply as $v){
         $aComId[] = $v['comment_id'];
      }
      if($member_comment->delete(array('comment_id' => $aComId))){
        $comments = $this->app->model('member_comments');
        $data['member_id'] = $member_id;
        $comments->fireEvent('delete',$data,$member_id);
        $this->end(true,app::get('b2c')->_('操作成功'));
      }
      else{
         $this->end(fasle,app::get('b2c')->_('操作失败'));
      }
    }
    
    function delete_reply($comment_id){
        $this->begin("javascript:finderGroup["."'".$_GET["finder_id"]."'"."].refresh()");
        $member_comment = kernel::single('b2c_message_disask');
        if($member_comment->delete(array('comment_id' => $comment_id))){
            $this->end(true,app::get('b2c')->_('操作成功'));
        }
        else{
            $this->end(fasle,app::get('b2c')->_('操作失败'));
        }
    }
}
