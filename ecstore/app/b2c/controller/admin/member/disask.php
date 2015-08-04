<?php
 
 
class b2c_ctl_admin_member_disask extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';
    
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function basic_setting(){
        
        $member_comments = kernel::single('b2c_message_disask');
        $aOut = $member_comments->get_basic_setting('discuss');
        $this->pagedata['setting']= $aOut;
        
        if($_POST['base_setting'] == 'true'){
            $this->pagedata['base_setting'] = 'true';
             echo $this->fetch('admin/member/basic_setting.html');
        }
        else $this->page('admin/member/basic_setting.html');
    }
    
    function to_setting(){
        $this->begin();
        $member_comments = kernel::single('b2c_message_disask');
        $aOut = $this->save_setting($_POST);
        $this->end('success',app::get('b2c')->_('设置成功'));
    }

#回复评论
    function save_setting($aData){
        if($aData['indexnum'] <=0) $aData['indexnum'] = 5;
        $this->app->setConf('comment.display.discuss', $aData['display']);
        $this->app->setConf('comment.display.ask', $aData['display']);
        $this->app->setConf('comment.display_lv', $aData['display_lv']);
        $this->app->setConf('comment.switch_reply', $aData['switch_reply']);
        $this->app->setConf('comment.index.listnum', $aData['indexnum']);
        $this->app->setConf('comment.verifyCode.discuss', $aData['verifyCode']);
        $this->app->setConf('comment.verifyCode.ask', $aData['verifyCode']);
        $this->app->setConf('comment.verifyCode.msg', $aData['verifyCode']);
    }
 
}
