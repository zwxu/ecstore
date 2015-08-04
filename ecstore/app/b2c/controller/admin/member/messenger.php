<?php


define('MANUAL_SEND','MANUAL_SEND');
class b2c_ctl_admin_member_messenger extends desktop_controller {

    //var $workground = 'b2c.workground.member';

     public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->path[] = array('text'=>app::get('b2c')->_('邮件短信配置'));
        $messenger = &$this->app->model('member_messenger');
        $action = $messenger->actions();
        foreach($action as $act=>$info){
            $list = $messenger->getSenders($act);
            foreach($list as $msg){
                $this->pagedata['call'][$act][$msg] = true;
            }
        }
        $sms = kernel::single('b2c_messenger_sms');
        $this->pagedata['actions'] = $action;
        $this->pagedata['sms_url'] = $sms->extraVars();

        if(app::get('sms')->is_installed()){//亿美软通短信标识
            $this->pagedata['sms_app'] = 1;
        }

        $this->_show('admin/messenger/index.html');
    }

    function edtmpl($action,$msg){
        $messenger = &$this->app->model('member_messenger');
        $info = $messenger->getParams($msg);
        if($this->pagedata['hasTitle'] = $info['hasTitle']){
            $this->pagedata['title'] = $messenger->loadTitle($action,$msg);
        }

        $this->pagedata['body'] = $messenger->loadTmpl($action,$msg);
        $this->pagedata['type'] = $info['isHtml']?'html':'textarea';
        $this->pagedata['messenger'] = $msg;
        $this->pagedata['action'] = $action;

        $actions = $messenger->actions();
        $this->pagedata['varmap'] = $actions[$action]['varmap'];
        $this->pagedata['action_desc'] = $actions[$action]['label'];
        $this->pagedata['msg_desc'] = $info['name'];
        $this->singlepage('admin/messenger/edtmpl.html');
    }

    function saveTmpl(){
        $this->begin();
        $messenger = &$this->app->model('member_messenger');
        $ret = $messenger->saveContent($_POST['actdo'],$_POST['messenger'],array(
            'content'=>htmlspecialchars_decode($_POST['content']),
            'title'=>$_POST['title']
        ));
        if($ret){
            $this->end(true,app::get('b2c')->_('操作成功'));
        }else{
             $this->end(false,app::get('b2c')->_('操作失败'));
        }
    }

    function save(){
    	$this->begin('');
        $messenger = &$this->app->model('member_messenger');
        if ($messenger->saveActions($_POST['actdo'])) {
             $this->end(true,app::get('b2c')->_('操作成功'));
        }else{
              $this->end(false,app::get('b2c')->_('操作失败'));
        }
    }

    function outbox($sender){
        $this->path[] = array('text'=>app::get('b2c')->_('发件箱'));
        $messenger = &$this->app->model('member_messenger');
        $this->pagedata['oubox'] = $messenger->outbox($sender);
        $this->pagedata['sender']=$sender;
        $this->_show('messenger/outbox.html');
    }

    function _show($tmpl){
        $messenger = &$this->app->model('member_messenger');
        $this->pagedata['messenger'] = $messenger->getList();
        $this->pagedata['__show_page__'] = $tmpl;
        $this->page('admin/messenger/page.html');
    }





}
?>
