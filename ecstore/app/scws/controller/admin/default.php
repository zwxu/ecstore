<?php

class scws_ctl_admin_default extends desktop_controller 
{
    public function index() 
    {
        $this->pagedata['dict'] = app::get('scws')->getConf('dict');
        $this->pagedata['rule'] = app::get('scws')->getConf('rule');
        $this->page('admin/index.html');
    }//End Function

    public function save() 
    {
        $this->begin();
        app::get('scws')->setConf('dict', $_POST['dict']);
        app::get('scws')->setConf('rule', $_POST['rule']);
        $this->end(true, '保存成功');
    }//End Function
    
}//End Class