<?php

/*
 * @package site
 * @author edwin.lzh@gmail.com
 * @license
 */
class site_ctl_admin_seo extends site_admin_controller
{
    /*
     * workground
     * @var string
     */
    var $workground = 'seo_ctl_admin_seo';

    /*
     * �б�
     * @public
     */
    public function index(){
        $this->finder('site_mdl_seo', array(
            'title' =>  app::get('site')->_('SEO网店优化'),
            'base_filter' => array(),
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => false,
            'use_buildin_export' => false,
            'use_buildin_selectrow'=>false,
            'actions'=>array(
                array(
                    'label' => app::get('site')->_('SEO默认配置'),
                    'href' => 'index.php?app=site&ctl=admin_seo&act=set_defaut_seo',
                    'target' => 'dialog::{frameable:true, title:\''.app::get('site')->_('SEO默认配置').'\', width:600, height:400}',
                ),
            ),

        ));

    }

    function set_defaut_seo(){
        $seo['param'] = kernel::single('site_seo_base')->get_default_seo();
        $render = $this->app->render();
        $render->pagedata['param'] = $seo['param'];
        return $this->page('admin/seo/default.html');
    }


    public function saveseo($id){
        $this->begin();
        if($id == 'default'){
            if($this->app->setConf('page.default_title',$_POST['seo_title'])&&$this->app->setConf('page.default_keywords',$_POST['seo_keywords'])&&$this->app->setConf('page.default_description',$_POST['seo_content'])){
                $this->end(true, app::get('site')->_('添加成功'));
            }else{
                $this->end(false, app::get('site')->_('添加失败'));
            }
        }
        $data['param'] = $_POST;
        $data['update_modified'] = time();
        if($id > 0){
            if(app::get('site')->model('seo')->update($data, array('id'=>$id))){
                $this->end(true, app::get('site')->_('保存成功'));
            }else{
                $this->end(false, app::get('site')->_('保存失败'));
            }
        }else{
            if(app::get('site')->model('seo')->insert($data)){
                $this->end(true, app::get('site')->_('添加成功'));
            }else{
                $this->end(false, app::get('site')->_('添加失败'));
            }
        }
    }
}//End Class
