<?php



class site_ctl_admin_widget_proinstance extends site_admin_controller
{
    
    public function index() 
    {
        $this->finder(
            'site_mdl_widgets_proinstance',
            array(
                'title'=>app::get('site')->_('挂件实例列表'),
                'use_buildin_set_tag' => true,
                'use_buildin_filter' => true,
                'actions'=>array(
                    array(
                        'label'=>app::get('site')->_('添加实例'),
                        'href'=>'index.php?app=site&ctl=admin_widget_proinstance&act=editor&level=system',
                        'target'=>'open::{width:950,height:700,top:100,left:100}'
                    ),
                ),
                'base_filter' => array('level'=>'system'),
            )
        );
    }//End Function

    public function editor() 
    {
        $id = $this->_request->get_get('id');
        $level = $this->_request->get_get('level');
        $flag = $this->_request->get_get('flag');
        $theme = ($this->_request->get_get('theme')) ? $this->_request->get_get('theme') : '';
        
        header('Content-Type: text/html; charset=utf-8');
        $this->path[] = array('text'=>app::get('site')->_('挂件实例编辑'));
        $this->pagedata['level'] = $level;
        $this->pagedata['flag'] = $flag;
        $this->pagedata['theme'] = $theme;

        $this->pagedata['shopadmin'] = kernel::router()->app->base_url(1);
        
        if($id > 0){
            $this->pagedata['instance'] = app::get('site')->model('widgets_proinstance')->select()->where('widgets_id = ?', $id)->instance()->fetch_row();
            $this->pagedata['theme'] = $this->pagedata['instance']['flag'];
        }

        return $this->singlepage('admin/widget/editor.html');
    }//End Function

    public function preview() 
    {
        $_SESSION['_tmp_wg_proinsert'] = array();
        kernel::single('base_session')->close();
        header('Content-Type: text/html; charset=utf-8');
        $smarty = kernel::single('site_controller');
        $smarty->tmpl_cachekey('widgets_instance', 'v0.6');
        $smarty->pagedata['id'] = $this->_request->get_get('id');

        $theme = $this->_request->get_get('theme');
        if($theme){
            $smarty->pagedata['theme_css'] = kernel::base_url(1) . '/themes/' . $theme . '/images/css.css';
        }

        $smarty->_compiler()->set_view_helper('function_header', 'site_widget_helper');
        $smarty->_compiler()->set_view_helper('function_footer', 'site_widget_helper');
        $smarty->_compiler()->set_compile_helper('compile_widget', kernel::single('site_widget_complier'));

        $smarty->display('admin/widget/preview.html');
    }//End Function

    public function do_add_widgets(){

        $widgets = $this->_request->get_get('widgets');
        $widgets_app = $this->_request->get_get('widgets_app');
        $widgets_theme = $this->_request->get_get('widgets_theme');
        $theme = $this->_request->get_get('theme');

        $this->pagedata['widgets_type'] = $widgets;
        $this->pagedata['widgets_app'] = $widgets_app;
        $this->pagedata['widgets_theme'] = $widgets_theme;
        $this->pagedata['widget_editor'] = kernel::single('site_widget_proinstance')->editor($widgets, $widgets_app, $widgets_theme, $theme);

        $this->pagedata['theme'] = $theme;

        $this->display('admin/widget/do_add_widgets.html');
    }

    public function do_edit_widgets(){

        $widgets_id = $this->_request->get_get('widgets_id');

        if(is_numeric($widgets_id)){
            $widgetObj = app::get('site')->model('widgets_proinstance')->getList('*', array('widgets_id'=>$widgets_id));
            $widgetObj = $widgetObj[0];
        }else{
            $widgetObj = $_SESSION['_tmp_wg_proinsert'];
        }

        $theme = ($widgetObj['flag']) ? $widgetObj['flag'] : '';

        $this->pagedata['widget_editor'] = kernel::single('site_widget_proinstance')->editor($widgetObj['widgets_type'],$widgetObj['app'],$widgetObj['theme'],$theme,$widgetObj['params']);
        $this->pagedata['widgets_type'] = $widgetObj['widgets_type'];

        $this->pagedata['widgets_id'] = $widgets_id;

        $this->pagedata['widgets_title'] = $widgetObj['title'];
        $this->pagedata['widgets_border']=$widgetObj['border'];
        $this->pagedata['widgets_classname']=$widgetObj['classname'];
        $this->pagedata['widgets_domid']=$widgetObj['domid'];
        $this->pagedata['widgets_app'] = $widgetObj['app'];
        $this->pagedata['widgets_theme'] = $widgetObj['theme'];

        $this->pagedata['widgets_tpl']=$widgetObj['tpl'];

        $this->pagedata['widgetsTpl'] = str_replace('\'','\\\'',kernel::single('site_widget_proinstance')->admin_wg_border(array('title'=>$widgetObj['title'],'html'=>'loading...'),$theme));

        $this->pagedata['theme'] = $theme;
        $this->display('admin/widget/do_edit_widgets.html');
    }

    public function insert_widget(){
        
        header('Content-Type: text/html;charset=utf-8');
       
        $widgets = $this->_request->get_get('widgets');
        $widgets_app = $this->_request->get_get('widgets_app');
        $widgets_theme = $this->_request->get_get('widgets_theme');
        $theme = $this->_request->get_get('theme');
        $domid = $this->_request->get_get('domid');

        $wg = $this->_request->get_post('__wg');
    
        $set = array(
            'flag' => $theme,
            'widgets_type' => $widgets,
            'app' => $widgets_app,
            'theme' => $widgets_theme,
            'title' => $wg['title'],
            'border' => $wg['border'],
            'tpl' => $wg['tpl'],
            'domid' => $wg['domid']?$wg['domid']:$domid,
            'classname' => $wg['classname'],
        );

        $post = $this->_request->get_post();
        unset($post['__wg']);

        $set['params'] = $post;

        $_SESSION['_tmp_wg_proinsert'] = $set;
        $data = kernel::single('site_widget_proinstance')->admin_wg_border(
            array(  'title'=>$set['title'],
                    'domid'=>$set['domid'],
                    'border'=>$set['border'],
                    'widgets_type'=>$set['widgets_type'],
                    'html'=> kernel::single('site_widget_proinstance')->fetch($set, true),
            ),
            $theme,true);
        $data = str_replace('%THEME%', kernel::base_url(1).'/themes/'.$theme, $data);
        echo $data;
    }

    public function save_widget() 
    {
        $this->begin();        
        $id = $this->_request->get_post('instance_id');
        $name = $this->_request->get_post('instance_name');
        $memo = $this->_request->get_post('instance_memo');
        $level = $this->_request->get_post('instance_level');
        $flag = $this->_request->get_post('instance_flag');

        if(empty($name)){
            $this->end(false, '请填写实例名称');
        }

        $sdata = $_SESSION['_tmp_wg_proinsert'];
        
        $sdata['name'] = $name;
        $sdata['memo'] = $memo;
        $sdata['level'] = $level;
        $sdata['flag'] = $flag;

        if($sdata['level'] == 'theme'){
            $rows = app::get('site')->model('widgets_proinstance')->getList('widgets_id', array('level'=>'theme', 'flag'=>$flag, 'name'=>$name));
            if(!empty($rows) && $id!==$rows[0]['widgets_id']){
                $this->end(false, '实例名称已经被使用，请修改');
            }
        }

        if(is_numeric($id)){
            $sdata['modified'] = time();
            if(app::get('site')->model('widgets_proinstance')->update($sdata, array('widgets_id'=>$id))){
                if($sdata['level'] == 'theme'){
                    kernel::single('site_theme_tmpl')->touch_theme_tmpl($sdata['flag']);
                }
                $this->end(true, '保存成功');
            }else{
                $this->end(false, '保存失败');
            }
        }else{
            if(empty($_SESSION['_tmp_wg_proinsert'])){
                $this->end(false, '请添加实例');
            }
            if($insert_id = app::get('site')->model('widgets_proinstance')->insert($sdata)){
                $this->end(true, '保存成功@'.$insert_id);
            }else{
                $this->end(false, '保存失败');
            }
        }
    }//End Function

    public function createcode() 
    {
        $this->pagedata['url'] = app::get('site')->router()->gen_url(array(
            'app' => 'site',
            'ctl' => 'proinstance',
            'act' => 'index',
            'arg0' => $this->_request->get_get('id'),
            'full' => 1,
        ));
        $this->display('admin/widget/create_code.html');
    }//End Function

    public function delete() 
    {
        $this->begin();
        $id = $this->_request->get_get('id');
        if(empty($id)){
            $this->end(false, '实例不存在');
        }
        if(app::get('site')->model('widgets_proinstance')->delete(array('widgets_id'=>$id))){
            $this->end(true, '删除成功');
        }else{
            $this->end(false, '删除失败');
        }
    }//End Function

}//End Class