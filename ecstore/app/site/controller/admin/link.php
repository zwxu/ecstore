<?php

 

class site_ctl_admin_link extends site_admin_controller 
{
    
    /*
     * workground
     * @var string
     */
    var $workground = 'site.wrokground.theme';

    /*
     * 列表
     * @public
     */
    public function index() 
    {
        $this->finder('site_mdl_link', array(

            'title' => app::get('site')->_('友情链接'),

            'base_filter' => array(),
            'actions'=>array(
                array(
                    'label' => app::get('site')->_('添加友情链接'), 
                    'href' => 'index.php?app=site&ctl=admin_link&act=add', 
                    'target' => 'dialog::{frameable:true, title:\''. app::get('site')->_('添加友情链接').'\', width:600, height:300}',
                ),
            ),
        ));
    }//End Function
    
    
    public function add() {
        $this->pagedata['info']['orderlist'] = 0;
        $this->display("admin/link/base.html");
    }
    
    public function edit() {
        $link_id = $this->_request->get_get('link_id');
        $oML = $this->app->model("link");
        $aData = $oML->getList('*', array('link_id'=>$link_id));
        $this->pagedata['info'] = $aData[0];
        $this->display("admin/link/base.html");
    }
    
    public function save() {
        $this->begin('index.php?app=site&ctl=admin_link&act=index');
        $oML = $this->app->model("link");
        $params = $this->_request->get_post('info');
        if($oML->save($params)){
            $this->end(true, app::get('site')->_('保存成功'));
        } else {
            $this->end(false, app::get('site')->_('保存失败'));
        }
    }
    
}//End Class
