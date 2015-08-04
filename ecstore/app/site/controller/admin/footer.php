<?php
class site_ctl_admin_footer extends site_admin_controller  {

    /*
     * workground
     * @var string
     */
    var $workground = 'site_ctl_admin_footer';

    public function index(){
        $this->path[] = array('text'=>app::get('site')->_('网页底部信息'));
        $this->pagedata['footEdit'] = $this->app->getConf('system.foot_edit');
        $this->page('admin/footer/base.html');
    }
    

    function saveFoot(){
        $this->begin();
        if($this->app->setConf('system.foot_edit',$_POST['footEdit'])){
            $this->end(true, app::get('site')->_('保存成功'));
        }
        $this->end(false, app::get('site')->_('保存失败'));
    }
}

