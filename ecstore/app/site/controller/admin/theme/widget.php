<?php



class site_ctl_admin_theme_widget extends site_admin_controller
{

    /*
     * workground
     * @var string
     */
    var $workground = 'site.wrokground.theme';

    public function editor()
    {
        $theme = $this->_request->get_get('theme');
        $file = $this->_request->get_get('file');
		//echo '<pre>';print_r($theme);exit;
        header('Content-Type: text/html; charset=utf-8');
        $this->path[] = array('text'=>app::get('site')->_('模板可视化编辑'));
        $this->pagedata['views'] = kernel::single('site_theme_base')->get_view($theme);
        $this->pagedata['widgetsLib'] = kernel::single('site_theme_widget')->get_libs($theme);
		$list = kernel::single('site_theme_tmpl')->get_edit_list($theme);

        foreach ($list['gallery'] as $lkey => $lval) {
            if($lval['type']){
                $list['chanel'][] = $lval;
                unset($list['gallery'][$lkey]);
            }
        }
        
		//begin  按照权限过滤模板文件
		$user = kernel::single('desktop_user');
		$user_id = $user->get_id();
		$is_super = $user->is_super();
		if(!$is_super){
			$roles = app::get('desktop')->model('hasrole')->getList('role_id',array('user_id'=>$user_id));
			$opctl = app::get('desktop')->model('roles');
			$workground = array();
			foreach($roles as $k=>$v){
				$sdf_roles = $opctl->dump($v['role_id']);
				$sdf_roles = unserialize($sdf_roles['workground']);
				$workground = array_merge($workground,$sdf_roles);
			}
			foreach($workground as $key=>$value){
				if(substr($value,0,4)=='cat_'){
					$cat_type[] = substr($value,4);
				}
			}
			//echo '<pre>';print_r($cat_type);exit;
			
			foreach($list as $key=>$value){
				foreach($value as $k=>$v){
					if(!in_array($v['id'],$cat_type)){
						unset($list[$key][$k]);
					}
				}
			}
		}
		
        $this->pagedata['list'] = $list;
        $this->pagedata['types'] = kernel::single('site_theme_tmpl')->get_name();
        $this->pagedata['types']['chanel'] = '频道页';

        $this->pagedata['theme'] = $theme;
        $this->pagedata['view'] = $file;
        $this->pagedata['viewname'] = kernel::single('site_theme_tmpl')->get_list_name($file);

        $this->pagedata['shopadmin'] = kernel::router()->app->base_url(1);

        $this->pagedata['site_url'] = app::get('site')->base_url(1);
        $this->pagedata['pagehead_active'] = 'preview';
        $this->pagedata['save_url'] = kernel::router()->app->base_url(1).'?app=site&ctl=admin_theme_widget&act=do_preview';
        $this->pagedata['preview_url'] = app::get('site')->base_url(1);

        return $this->singlepage('admin/theme/widget/editor.html');
    }//End Function

    public function preview()
    {
        $theme = $this->_request->get_get('theme');
        $file = $this->_request->get_get('file');
        /** 清空widgets数据缓存 **/
        if ($_SESSION['WIDGET_TMP_DATA'][$theme.'/'.$file]) $_SESSION['WIDGET_TMP_DATA'][$theme.'/'.$file] = array();
        if ($_SESSION['WIDGET_TMP_DATA'][$theme.'/block/header.html']) $_SESSION['WIDGET_TMP_DATA'][$theme.'/block/header.html'] = array();
        if ($_SESSION['WIDGET_TMP_DATA'][$theme.'/block/footer.html']) $_SESSION['WIDGET_TMP_DATA'][$theme.'/block/footer.html'] = array();

        header('Content-Type: text/html; charset=utf-8');
        kernel::single('base_session')->close();
        $smarty = kernel::single('site_controller');
        $smarty->tmpl_cachekey('widgets_modifty_'.$theme , true);

        $smarty->pagedata['theme_dir'] = kernel::base_url() . '/themes/' . $theme . '/';
        $smarty->pagedata['theme'] = $theme;

        $smarty->_compiler()->set_compile_helper('compile_main', kernel::single('site_theme_complier'));
        $smarty->_compiler()->set_view_helper('function_header', 'site_theme_helper');
        $smarty->_compiler()->set_view_helper('function_footer', 'site_theme_helper');
        $smarty->_compiler()->set_compile_helper('compile_widgets', kernel::single('site_theme_complier'));
        $smarty->set_theme($theme);
        $smarty->display_tmpl(urldecode($file));
    }//End Function

    public function add_widgets_page()
    {
        $theme = $this->_request->get_get('theme');
        $this->pagedata['theme'] = $theme;
        $this->pagedata['widgetsLib'] = kernel::single('site_theme_widget')->get_libs($theme);
        $theme_url = kernel::base_url() . strrchr(THEME_DIR,'/') . '/' . $theme . '/';
        $app_base_url = kernel::base_url().'/app/';
        $themesFileObj=app::get('site')->model('themes_file');
        // $widgetsFileObj=app::get('site')->model('widgets_file');
        $storager = kernel::single('base_storager');

        if ($this->pagedata['widgetsLib']['usual']){
            foreach((array)$this->pagedata['widgetsLib']['usual'] as $key=>$widgets){
                if ($widgets['theme']){

                    $rs=$themesFileObj->getList('content',array('fileuri'=>$widgets['theme'].':'.'widgets/'.$widgets['name'].'/images/icon.jpg'));

                    if ($rs[0]['content']) {
                        $ident = $storager->parse($rs[0]['content']);
                        $src = $ident['url'];
                        ecae_kvstore_write('test',$src);
                        $this->pagedata['widgetsLib']['usual'][$key]['img'] = $src;
                    }else{
                        $this->pagedata['widgetsLib']['usual'][$key]['img'] = $this->app->res_url.'/images/widgets/icon.jpg';
                    }

                    $rs=$themesFileObj->getList('content',array('fileuri'=>$widgets['theme'].':'.'widgets/'.$widgets['name'].'/images/widget.jpg'));

                    if ($rs[0]['content']) {
                        $ident = $storager->parse($rs[0]['content']);
                        $src = $ident['url'];
                        $this->pagedata['widgetsLib']['usual'][$key]['bimg'] = $src;
                    }else{
                        $this->pagedata['widgetsLib']['usual'][$key]['bimg'] = $this->app->res_url.'/images/widgets/widget.jpg';
                    }
                }else{//获取系统级挂件信息
                    $this->pagedata['widgetsLib']['usual'][$key]['img'] = $this->app->res_url.'/images/widgets/icon.jpg';
                    $this->pagedata['widgetsLib']['usual'][$key]['bimg'] = $this->app->res_url.'/images/widgets/widget.jpg';
                }
            }
        }

        $this->display('admin/theme/widget/add_widgets_page.html');
    }//End Function

    public function add_widgets_page_extend()
    {
        $theme = $this->_request->get_get('theme');
        $type = $this->_request->get_get('type');
        $catalog = $this->_request->get_post('catalog');

        $this->pagedata['theme'] = $theme;
        $this->pagedata['widgetsLib'] = kernel::single('site_theme_widget')->get_libs_extend($theme, $catalog);
        $theme_url = kernel::base_url() . strrchr(THEME_DIR,'/') . '/' . $theme . '/';

        $themesFileObj=app::get('site')->model('themes_file');
        // $widgetsFileObj=app::get('site')->model('widgets_file');
        $storager = kernel::single('base_storager');

        if ($this->pagedata['widgetsLib']['list'])
            foreach((array)$this->pagedata['widgetsLib']['list'] as $key=>$widgets){


                if ($widgets['theme']){

                    $rs=$themesFileObj->getList('content',array('fileuri'=>$widgets['theme'].':'.'widgets/'.$widgets['name'].'/images/icon.jpg'));

                    if ($rs[0]['content']) {
                        $ident = $storager->parse($rs[0]['content']);
                        $src = $ident['url'];
                        ecae_kvstore_write('test',$src);
                        $this->pagedata['widgetsLib']['list'][$key]['img'] = $src;
                    }else{
                        $this->pagedata['widgetsLib']['list'][$key]['img'] = $this->app->res_url.'/images/widgets/icon.jpg';
                    }

                    $rs=$themesFileObj->getList('content',array('fileuri'=>$widgets['theme'].':'.'widgets/'.$widgets['name'].'/images/widget.jpg'));

                    if ($rs[0]['content']) {
                        $ident = $storager->parse($rs[0]['content']);
                        $src = $ident['url'];
                        $this->pagedata['widgetsLib']['list'][$key]['bimg'] = $src;
                    }else{
                        $this->pagedata['widgetsLib']['list'][$key]['bimg'] = $this->app->res_url.'/images/widgets/widget.jpg';
                    }
                }else{//获取系统级挂件信息
                    $this->pagedata['widgetsLib']['list'][$key]['img'] = $this->app->res_url.'/images/widgets/icon.jpg';
                    $this->pagedata['widgetsLib']['list'][$key]['bimg'] = $this->app->res_url.'/images/widgets/widget.jpg';
                }
            }

        $this->display('admin/theme/widget/add_widgets_page_extend.html');
    }//End Function

    public function get_widgets_info()
    {
        $type = $this->_request->get_get('type');
        $widgets = $this->_request->get_get('widgets');
        $widgets_app = $this->_request->get_get('widgets_app');
        $widgets_theme = $this->_request->get_get('widgets_theme');
        if($widgets){
            $this->pagedata['widgetsInfo'] = kernel::single('site_theme_widget')->get_this_widgets_info($widgets, $widgets_app, $widgets_theme);
            $this->pagedata['widgets'] = $widgets;

        }
        $this->pagedata['theme'] = app::get('site')->getConf('current_theme');
        $this->display('admin/theme/widget/get_widgets_info.html');
    }//End Function

    public function do_add_widgets(){

        $widgets = $this->_request->get_get('widgets');
        $widgets_app = $this->_request->get_get('widgets_app');
        $widgets_theme = $this->_request->get_get('widgets_theme');
        $theme = $this->_request->get_get('theme');
        $this->pagedata['widget_editor'] = kernel::single('site_theme_widget')->editor($widgets, $widgets_app, $widgets_theme, $theme);

        $this->pagedata['widgets_type'] = $widgets;
        $this->pagedata['widgets_app'] = $widgets_app;
        $this->pagedata['widgets_theme'] = $widgets_theme;
        $this->pagedata['theme'] = $theme;

        $this->pagedata['i']=is_array($_SESSION['_tmp_wg_insert'])?count($_SESSION['_tmp_wg_insert']):0;
        $this->pagedata['basic_config'] = kernel::single('site_theme_base')->get_basic_config($theme);

        $this->display('admin/theme/widget/do_add_widgets.html');
    }

    public function do_edit_widgets(){

//        header("Cache-Control:no-store, no-cache, must-revalidate"); //强制刷新IE缓存
        $widgets_id = $this->_request->get_get('widgets_id');
        $theme = $this->_request->get_get('theme');

        if(is_numeric($widgets_id)){
            $widgetObj = app::get('site')->model('widgets_instance')->getList('*', array('widgets_id'=>$widgets_id));
            $widgetObj = $widgetObj[0];
        }elseif(preg_match('/^tmp_([0-9]+)$/i',$widgets_id,$match)){
            $widgetObj = $_SESSION['_tmp_wg_insert'][$match[1]];
        }

        $this->pagedata['widget_editor'] = kernel::single('site_theme_widget')->editor($widgetObj['widgets_type'],$widgetObj['app'],$widgetObj['theme'],$theme,$widgetObj['params']);
        $this->pagedata['widgets_type'] = $widgetObj['widgets_type'];

         $this->pagedata['widgetsTpl'] = str_replace('\'','\\\'',kernel::single('site_theme_widget')->admin_wg_border(array('title'=>$widgetObj['title'],'html'=>'loading...'),$theme));


        $this->pagedata['widgets_id'] = $widgets_id;
        $this->pagedata['widgets_title'] = $widgetObj['title'];
        $this->pagedata['widgets_border']=$widgetObj['border'];
        $this->pagedata['widgets_classname']=$widgetObj['classname'];
        $this->pagedata['widgets_domid']=$widgetObj['domid'];
        $this->pagedata['widgets_app'] = $widgetObj['app'];
        $this->pagedata['widgets_theme'] = $widgetObj['theme'];

        $this->pagedata['widgets_tpl']=$widgetObj['tpl'];


        $this->pagedata['theme'] = $theme;
        $this->pagedata['basic_config'] = kernel::single('site_theme_base')->get_basic_config($theme);
        $this->display('admin/theme/widget/do_edit_widgets.html');
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
        $set['_domid'] = $set['domid'];

        $i=is_array($_SESSION['_tmp_wg_insert'])?count($_SESSION['_tmp_wg_insert']):0;
        $_SESSION['_tmp_wg_insert'][$i] = $set;
        $data = kernel::single('site_theme_widget')->admin_wg_border(
            array(  'title'=>$set['title'],
                    'domid'=>$set['domid'],
                    'border'=>$set['border'],
                    'widgets_type'=>$set['widgets_type'],
                    'html'=> kernel::single('site_theme_widget')->fetch($set, true),
                    'border'=>$set['border']
            ),
            $theme,true);
        $data = str_replace('%THEME%', kernel::base_url(1).'/themes/'.$theme, $data);
        echo $data;
    }

    public function save_widget()
    {
        header('Content-Type: text/html;charset=utf-8');

        $widgets_id = $this->_request->get_get('widgets_id');
        $widgets = $this->_request->get_get('widgets');
        $widgets_app = $this->_request->get_get('widgets_app');
        $widgets_theme = $this->_request->get_get('widgets_theme');
        $theme = $this->_request->get_get('theme');
        $domid = $this->_request->get_get('domid');

        $wg = $this->_request->get_post('__wg');

        if($widgets_type=='html')   $widgets_type='usercustom';
        $set = array(
            'widgets_type'=>$widgets,
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
        $set['_domid'] = $set['domid'];

        if(is_numeric($widgets_id)){
            $sdata = $set;
            kernel::single('site_theme_widget')->save_widgets($widgets_id, $sdata);
            $set['widgets_id'] = $widgets_id;
        $_SESSION['_tmp_wg_update'][$widgets_id] = $set;
        }elseif(preg_match('/^tmp_([0-9]+)$/i',$widgets_id,$match)){
            $_SESSION['_tmp_wg_insert'][$match[1]] = $set;
        }

        $data = kernel::single('site_theme_widget')->admin_wg_border(
            array(  'widgets_id'=>$widgets_id,
                    'title'=>$set['title'],
                    'domid'=>$set['domid'],
                    'border'=>$set['border'],
                    'widgets_type'=>$set['widgets_type'],
                    'html'=> kernel::single('site_theme_widget')->fetch($set, true),
                    'border'=>$set['border']
            ),
            $theme,true);
        $data = str_replace('%THEME%', kernel::base_url(1).'/themes/'.$theme, $data);
        echo $data;
    }//End Function


    public function do_preview()
    {
        $widgets = $this->_request->get_post('widgets');
        $html = $this->_request->get_post('html');
        $files = $this->_request->get_post('files');

        if(is_array($widgets)){

            foreach($widgets as $widgets_id=>$base){
                $aTmp=explode(':',$base);
                $base_id=array_pop($aTmp);
                $base_slot=array_pop($aTmp);
                $base_file=implode(':',$aTmp);
                if($html[$widgets_id]){
                    $widgetsSet[$widgets_id] = array(
                        'core_file'=>$base_file,
                        'core_slot'=>$base_slot,
                        'core_id'=>$base_id,
                        'border'=>'__none__',
                        'params'=>array('html'=>stripslashes($html[$widgets_id]))
                    );
                }else{
                    $widgetsSet[$widgets_id] = array('core_file'=>$base_file,'core_slot'=>$base_slot,'core_id'=>$base_id);
                }
            }
        }

        if(false !== ($map = kernel::single('site_theme_widget')->save_preview_all($widgetsSet,$files))){
            setcookie('site[preview]', 'true', 0, kernel::base_url() . '/');
            $map = array(
                'success'=>true
            );
            echo json_encode($map);
        }else{
            echo json_encode(false);
        }
    }//End Function

    public function save_all()
    {
        $widgets = $this->_request->get_post('widgets');
        $html = $this->_request->get_post('html');
        $files = $this->_request->get_post('files');

        if(is_array($widgets)){

            foreach($widgets as $widgets_id=>$base){
                $aTmp=explode(':',$base);
                $base_id=array_pop($aTmp);
                $base_slot=array_pop($aTmp);
                $base_file=implode(':',$aTmp);
                if($html[$widgets_id]){
                    $widgetsSet[$widgets_id] = array(
                        'core_file'=>$base_file,
                        'core_slot'=>$base_slot,
                        'core_id'=>$base_id,
                        'border'=>'__none__',
                        'params'=>array('html'=>stripslashes($html[$widgets_id]))
                    );
                }else{
                    $widgetsSet[$widgets_id] = array('core_file'=>$base_file,'core_slot'=>$base_slot,'core_id'=>$base_id);
                }
            }
        }

        if(false !== ($map = kernel::single('site_theme_widget')->save_all($widgetsSet,$files))){
            echo json_encode($map);
        }else{
            echo json_encode(false);
        }
    }//End Function

}//End Class
