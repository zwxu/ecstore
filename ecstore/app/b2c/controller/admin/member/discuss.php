<?php
 

class b2c_ctl_admin_member_discuss extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $member_comments = $this->app->model('member_comments');
        $member_comments->set_type('discuss');
        $this->finder('b2c_mdl_member_comments',array(
        'title'=>app::get('b2c')->_('评论列表'),
        'use_buildin_recycle'=>true,
        'use_buildin_filter'=>true,
        'base_filter' =>array('for_comment_id' => 0),
        //'actions'=>array(
         //   array('label'=>'评论设置','href'=>'index.php?app=b2c&ctl=admin_member_discuss&act=setting')
          //  ),
        ));

    }
    function setting(){
        $member_comments = kernel::single('b2c_message_disask');
        $aOut = $member_comments->get_setting('discuss');
          if(!$aOut['verifyCode']['discuss']){
            $aOut['verifyCode']['discuss']='off';
        }
        $aOut['aSwitch']['discuss'] = array('on'=>app::get('b2c')->_('开启'), 'off'=>app::get('b2c')->_('关闭'));
        $aOut['aPower']['discuss'] = array('null'=>app::get('b2c')->_('非会员可发表评论'), 'member'=>app::get('b2c')->_('注册会员可发表评论'), 'buyer'=>app::get('b2c')->_('只有购买过此商品的会员才可发表评论'));
        $aOut['verifyLCode']['discuss'] = array('on'=>app::get('b2c')->_('开启'), 'off'=>app::get('b2c')->_('关闭'));
        foreach(kernel::servicelist('comment_list') as $service){
            $this->pagedata['html'][]= $service->get_Html();
        }
         $this->pagedata['setting']= $aOut;
        echo  $this->fetch('admin/member/discuss_setting.html');
    }

    function to_setting(){
        $this->begin();
        $member_comments = kernel::single('b2c_message_disask');
        $aOut = $member_comments->to_setting('discuss',$_POST);
        foreach(kernel::servicelist('comment_list') as $service){
         $service->save_setting($_POST);
        }
        $this->end('success',app::get('b2c')->_('设置成功'));
    }

#回复评论
    function to_reply(){
      $this->begin("javascript:finderGroup["."'".$_GET["finder_id"]."'"."].refresh()");
      $comment_id = $_POST['comment_id'];
      $comment = $_POST['reply_content'];
      if($comment_id&&$comment){
         $member_comments = kernel::single('b2c_message_disask');
         $row = $member_comments->dump($comment_id);
         $author_id = $row['author_id'];
         unset($row['goods_point']);
         if($this->app->getConf('comment.display.discuss') == 'reply'){
            $aData = $row;
            $aData['display'] = 'true';
            $goods_point = $this->app->model('comment_goods_point');
            $goods_point->set_status($comment_id,'true');
            $_is_add_point = app::get('b2c')->getConf('member_point');
            if($_is_add_point && $author_id){
                $obj_member_point = $this->app->model('member_point');
                $obj_member_point->change_point($author_id,$_is_add_point,$_msg,'comment_discuss',2,$row['type_id'],$author_id,'comment');
            }
            $member_comments->save($aData);
         }
         $sdf['comment_id']= '';
         $sdf['for_comment_id'] = $comment_id;
         $sdf['object_type'] = "discuss";
         $sdf['to_id'] = $author_id;
         $sdf['author_id'] = null;
         $sdf['author'] = app::get('b2c')->_('管理员');
         $sdf['title'] = '';
         $sdf['contact'] = '';
         $sdf['display'] = 'true';
         $sdf['time'] = time();
         $sdf['comment'] = $comment;
         if($member_comments->send($sdf,'discuss')){
             $comments = $this->app->model('member_comments');
             $data['member_id'] = $author_id;
             $comments->fireEvent('discussreply',$data,$author_id);
            $this->end(true,app::get('b2c')->_('操作成功'));
         }
         else{
            $this->end(false,app::get('b2c')->_('操作失败'));
         }
      }
      else{
         $this->end(false,app::get('b2c')->_('内容不能为空'));
      }
    }




}
