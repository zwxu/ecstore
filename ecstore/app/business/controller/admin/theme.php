<?php
class business_ctl_admin_theme extends desktop_controller {
    public function __construct($app) {
        parent :: __construct($app);
    }

    function index() {
        $this -> finder('business_mdl_theme', 
            array('title' => '店铺列表',
                'actions' => array(
                    array(
                        'label'=>app::get('business')->_('添加模版'),
                        'href'=>'index.php?app=business&ctl=admin_theme&act=save_page',
                        'target'=>'dialog::{title:\''.app::get('business')->_('添加模版').'\',width:460,height:400}'
                    )
                )
            )
        );
    }

    function save_page($theme_id){
        $cur_sys_theme = kernel::single('site_theme_base')->get_default();
        $shops = app::get('site')->model('themes_tmpl')->getList('*',array('theme'=>$cur_sys_theme,'tmpl_type'=>'shop'));
        $gallerys = app::get('site')->model('themes_tmpl')->getList('*',array('theme'=>$cur_sys_theme,'tmpl_type'=>'shopgallery'));
        $this->pagedata['shops'] = $shops;
        $this->pagedata['gallerys'] = $gallerys;
        if($theme_id&&$theme = app::get('business')->model('theme')->getRow('*',array('theme_id'=>$theme_id))){
            $this->pagedata['theme'] = $theme;
        }
        $this->display('admin/theme/theme.html');
    }

    function save(){
        $this->begin();
        if(app::get('business')->model('theme')->save($_POST)){
            $this->end(true, app::get('business')->_('添加成功！'));
        }else{
            $this->end(false, app::get('business')->_('添加失败！'));
        }
    }
} 
