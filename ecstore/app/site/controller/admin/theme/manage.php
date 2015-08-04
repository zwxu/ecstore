<?php



class site_ctl_admin_theme_manage extends site_admin_controller
{

    /*
     * workground
     * @var string
     */
    var $workground = 'site.wrokground.theme';

    //列表
    public function index()
    {
        //默认读取一下themes文件夹，获取文件夹内已有模板@lujy
        kernel::single('site_theme_install')->check_install();
        $theme_preview = kernel::base_url(1) . strrchr(THEME_DIR,'/');
		$default_theme = kernel::single('site_theme_base')->get_default();
        $o_themes = app::get('site')->model('themes')->getList('*', array('theme'=>$default_theme));
        $this->pagedata['site_url'] = app::get('site')->base_url(1);

        if ($o_themes){
            $this->pagedata['current_theme'] = $o_themes[0];
            /** 获取当前模版的信息 **/
            $current_sytle = kernel::single('site_theme_base')->get_theme_style($o_themes[0]['theme']);
            $preview = ($current_sytle['preview']) ? $current_sytle['preview'] : 'preview.jpg';

            $this->pagedata['current']['is_themme_bk'] = kernel::single('site_theme_tmpl_file')->is_themme_bk($o_themes[0]['theme'], 'theme_bak.xml');
            $src = kernel::single('site_theme_tmpl_file')->get_src($o_themes[0]['theme'], $preview);
            $preview_prefix = kernel::single('site_theme_tmpl_file')->preview_prefix($o_themes[0]['theme']);
            $this->pagedata['current_theme_preview_img'] = $src;

            $styles = kernel::single('site_theme_base')->get_theme_styles($o_themes[0]['theme']);
            foreach($styles as $key=>$style){
                $style['preview'] = kernel::single('site_theme_tmpl_file')->get_src($o_themes[0]['theme'], $style['preview']);
                $preview_prefix = kernel::single('site_theme_tmpl_file')->preview_prefix($o_themes[0]['theme']);
                $styles[$key] = $style;
            }

            $this->pagedata['styles'] = $styles;
            $this->pagedata['preview_prefix'] = $preview_prefix;
            $this->pagedata['current'] = $current_sytle;
            $this->pagedata['current']['active_color'] = $current_sytle['color'];

            //设置编辑默认页面
            $defaultIndexFile = kernel::single('site_theme_tmpl')->get_default('index',$default_theme);  
            $nodefaultindex = $this->app->model('themes_tmpl')->getList('tmpl_path',array('theme'=>$default_theme,'tmpl_type'=>'index'));  
            $this->pagedata['current']['default_index_file'] = $defaultIndexFile ? $defaultIndexFile : $nodefaultindex[0]['tmpl_path'];
        }
        /** 获取所有已安装的模版 **/
        $all_themes = app::get('site')->model('themes')->getList('*', array('is_used'=>'false'));

        foreach ($all_themes as $k=>$arr_theme){
            $arr_style = kernel::single('site_theme_base')->get_theme_style($arr_theme['theme']);
            $preview = ($arr_style['preview']) ? $arr_style['preview'] : 'preview.jpg';


            $all_themes[$k]['is_themme_bk'] = kernel::single('site_theme_tmpl_file')->is_themme_bk($arr_theme['theme'],'theme_bak.xml');
            $src = kernel::single('site_theme_tmpl_file')->get_src($arr_theme['theme'], $preview);
            $preview_prefix = kernel::single('site_theme_tmpl_file')->preview_prefix($arr_theme['theme']);
            $all_themes[$k]['preview'] = $src;

            $styles = kernel::single('site_theme_base')->get_theme_styles($arr_theme['theme']);

            foreach($styles as $key=>$style){
                $style['preview'] = kernel::single('site_theme_tmpl_file')->get_src($o_themes[0]['theme'], $style['preview']);
                $preview_prefix = kernel::single('site_theme_tmpl_file')->preview_prefix($o_themes[0]['theme']);
                $styles[$key] = $style;
            }

            $all_themes[$k]['styles'] = $styles;
            $all_themes[$k]['preview_prefix'] = $preview_prefix;
            $all_themes[$k]['active_color'] = $arr_style['color'];
        }
        $this->pagedata['all_themes'] = $all_themes;

        $this->page('admin/theme/manage/index.html');
        //$this->finder('site_mdl_themes',array('title'=>app::get('site')->_('模板管理'), 'actions'=>$actions,'use_buildin_recycle'=>false));

    }//End Function

    function note(){
        $theme = $this->_request->get_get('theme');
        if(!$this->check($theme,$msg))   $this->_error($msg);

        $this->pagedata['theme'] = $theme;

        $this->display('admin/theme/manage/note.html');
    }//End Function

    function save_note(){
        $this->begin('index.php?app=site&ctl=admin_theme_manage&act=index');

        $theme = $this->_request->get_post('theme');
        if(!$this->check($theme,$msg))   $this->_error($msg);

        $filter = array(
            'theme'=>$theme
        );
        if (!app::get('site')->model('themes')->update(array('info'=>$this->_request->get_post('info')),$filter)){
            $this->end(false,app::get('site')->_('备注设置失败！'));
        }else{
            $this->end(true,app::get('site')->_('备注设置成功！'));
        }
    }//End Function

    function detail(){
        $params = $this->_request->get_params(true);
        if (!$params['id']){
            header("Content-type: text/html; charset=utf-8");
            echo '{error:"'.app::get('site')->_('没有指定具体的模板！').',redirect:null"}';exit;
        }
        $data = app::get('site')->model('themes')->getList('*', array('theme'=>$params['id']));

        $theme = $data[0]['theme'];
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
		$this->pagedata['is_super'] = $is_super;
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
        $this->pagedata['pagehead_active'] = 'pagem';
		
        //设置可视化编辑页面（默认and非默认）
        $defaultIndexFile = kernel::single('site_theme_tmpl')->get_default('index',$theme); 
        $nodefaultindex = $this->app->model('themes_tmpl')->getList('tmpl_path',array('theme'=>$theme,'tmpl_type'=>'index'));        
        $this->pagedata['current']['default_index_file'] = $defaultIndexFile ? $defaultIndexFile : $nodefaultindex[0]['tmpl_path'];

        $this->singlepage('admin/theme/tmpl/frame.html');
    }
    function instance(){
        $this->singlepage('admin/theme/tmpl/instance.html');
    }
    function addinstance(){
        $this->display('admin/theme/tmpl/addinstance.html');
    }

    protected function check($theme,&$msg='')
    {
        if(empty($theme)){
            $msg = app::get('site')->_('缺少参数');
            return false;
        }
        /** 权限校验 **/
        if($theme && preg_match('/(\..\/){1,}/', $theme)){
            $msg = app::get('site')->_('非法操作');
            return false;
        }
        return true;
    }//End Function

    //flash上传
    public function swf_upload()
    {
        $this->pagedata['ssid'] = kernel::single('base_session')->sess_id();
        $this->pagedata['swf_loc'] = kernel::router()->app->res_url;
        $this->pagedata['upload_max_filesize'] = kernel::single('site_theme_install')->ini_get_size('upload_max_filesize');
        $this->display('admin/theme/manage/swf_upload.html');
    }//End Function

    public function install_online()
    {
        $params = $this->_request->get_post();
        if(isset($params['url']) && isset($params['tpl_name']) && isset($params['fullsize'])){
            $params['name'] = ($params['tpl_name']) ? $params['tpl_name'] : basename($params['url']);       //如果没有传入文件名，则使用basename
            $downObj = kernel::single('site_utility_download');
            $ident = $downObj->set_task($params);
            $this->pagedata['ident'] = $ident;
            $this->pagedata['success_url'] = 'index.php?app=site&ctl=admin_theme_manage&act=install';
            $this->singlepage('admin/download/process.html');
        }
    }//End Function

    public function upload()
    {
        $themeInstallObj = kernel::single('site_theme_install');
        $res = $themeInstallObj->install($_FILES['Filedata'],$msg);
        if($res){
            $img = kernel::single('site_theme_tmpl_file')->get_src($res['theme'],'preview.jpg');
            echo '<img src="'.$img.'" onload="$(this).zoomImg(50,50);" />';
        }else{
            echo $msg;
        }
    }//End Function

    public function install()
    {
        set_time_limit(120);
        $ident = $this->_request->get_get('ident');
        $downObj = kernel::single('site_utility_download');
        $ident = $downObj->dl($ident);
        if(!$ident){
            echo '<script>failure("'.app::get('site')->_('下载出错').'")</script>';
            exit;
        }
        $task_info = $downObj->get_task($ident);
        if(empty($task_info))   $this->_error();
        $file = $downObj->get_work_dir() . '/' . $ident . '/' . $task_info['name'];

        $msg =app::get('site')->_('无法找到安装文件');

        if(is_file($file)){
            $fileInfo['tmp_name'] = $file;
            $fileInfo['name'] = time();
            $fileInfo['error'] = '0';
            $fileInfo['size'] = filesize($file);
            $themeInstallObj = kernel::single('site_theme_install');
            $res = $themeInstallObj->install($fileInfo, $msg);
        }
        if($res){
            $theme_url = defined('THEMES_IMG_URL') ? THEMES_IMG_URL : kernel::base_url(1) . '/themes';
            $img = $theme_url . '/' . $res['theme'] . '/preview.jpg';
            $this->pagedata['img'] = '<img src="'.$img.'" />';
            $this->pagedata['msg'] = app::get('site')->_('模板安装成功，您可以在模板列表中启用它。');
        }else{
            $this->pagedata['msg'] = $msg;
        }
        $this->singlepage('admin/download/result.html');
    }//End Function

    public function set_default()
    {
        $this->begin();
        $theme = $this->_request->get_get('theme');
        if(!$this->check($theme,$msg))   $this->_error($msg);
        if($theme){
            if(kernel::single('site_theme_base')->set_default($theme)){
                $this->end(true, app::get('site')->_('设置成功'));
            }else{
                $this->end(false, app::get('site')->_('设置失败'));
            }
        }
    }//End Function

    public function set_style()
    {
        $this->begin();
        $theme = $this->_request->get_get('theme');
        $style_id = $this->_request->get_get('style_id');
        if(!$this->check($theme,$msg))   $this->_error($msg);
        if($theme){
            $styles = kernel::single('site_theme_base')->get_theme_styles($theme);
            if(is_array($styles) && array_key_exists($style_id, $styles)){
                if(kernel::single('site_theme_base')->set_theme_style($theme, $styles[$style_id]))
                    $this->end(true, app::get('site')->_('设置成功'));
            }
            $this->end(false, app::get('site')->_('设置失败'));
        }
    }//End Function

    public function bak() {
        $this->begin();
        $theme = $this->_request->get_get('theme');
        if(!$this->check($theme,$msg))   $this->_error($msg);
        $data = kernel::single('site_theme_tmpl')->make_configfile($theme);

        if(kernel::single('site_theme_tmpl_file')->bak_save($theme, $data)){
            $this->end(true, app::get('site')->_('备份成功！'));
        }else{
            $this->end(false, app::get('site')->_('备份失败！'));
        }
    }

    public function reset() {
        $this->begin();
        $theme = $this->_request->get_get('theme');
        $loadxml = $this->_request->get_get('rid');
        if(!$this->check($theme,$msg))   $this->_error($msg);
        if(kernel::single("site_theme_install")->init_theme($theme, true, false, $loadxml)) {
            $this->end(true, app::get('site')->_('还原成功！'));
        } else {
            $this->end(false, app::get('site')->_('还原失败！'));
        }
    }

    public function delete()
    {
        $this->begin();
        $get = $this->_request->get_get();
        foreach ((array)$get['theme'] as $theme){
            if(!$this->check($theme,$msg))   $this->_error($msg);
        }
        if(app::get('site')->model('themes')->delete_file(array('theme'=>$get['theme']))){
            $this->end(true, app::get('site')->_('删除成功'), 'index.php?app=site&ctl=admin_theme_manage&act=index');
        }else{
            $this->end(false, app::get('site')->_('删除失败'));
        }
    }//End Function

    public function download()
    {
        $theme = $this->_request->get_get('theme');
        if(!$this->check($theme,$msg))   $this->_error($msg);
        kernel::single('site_theme_tmpl')->output_pkg($theme);
        exit;
    }//End Function

    public function cache_version()
    {
        $theme = $this->_request->get_get('theme');
        if(!$this->check($theme,$msg))   $this->_error($msg);
        $this->begin();
        $this->end(kernel::single('site_theme_tmpl')->touch_theme_tmpl($theme));
    }//End Function

    public function maintenance()
    {
        $theme = $this->_request->get_get('theme');
        if (!$theme){
            if(is_dir(THEME_DIR)){
                kernel::single('site_theme_base')->maintenance_theme_files(THEME_DIR);
                kernel::single('site_theme_base')->update_theme_tmpl(THEME_DIR);
            }
        }else{
            kernel::single('site_theme_base')->maintenance_theme_files($theme);
            kernel::single('site_theme_base')->update_theme_tmpl($theme);
        }
    }//End Function

}//End Class
