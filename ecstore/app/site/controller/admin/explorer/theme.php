<?php



class site_ctl_admin_explorer_theme extends site_admin_controller
{
    /*
     * workground
     * @var string
     */
    var $workground = 'site.wrokground.theme';

    private function get_theme_dir($theme, $open_path='')
    {
        return kernel::single('site_theme_tmpl_file')->get_theme_dir($theme, $open_path);
    }//End Function

    /*
     * 目录浏览
     */
    public function directory()
    {
        $theme = $this->_request->get_get('theme');
        $open_path = $this->_request->get_get('open_path');
        $is_part = $this->_request->get_get('part');

        $this->get_directory_body($theme,$open_path,'');

        $this->pagedata['theme'] = $theme;
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
		// $this->pagedata['list'] = kernel::single('site_theme_tmpl')->get_edit_list($theme);
		//echo '<pre>';print_r($this->pagedata['list']);exit;
        foreach ((array)$this->pagedata['list'] as $k=>$list){
            foreach ($list as $key=>$li){
                if (!$li||!$li['tmpl_path']) continue;
                $file_name = THEME_DIR. '/' . $theme . '/'.$li['tmpl_path'];

                if (filesize($file_name)) continue;
                unset($this->pagedata['list'][$k][$key]);
            }
        }
        $this->pagedata['types'] = kernel::single('site_theme_tmpl')->get_name();
        $this->pagedata['types']['chanel'] = '频道页';
        $this->pagedata['open_path'] = $open_path;
        $this->pagedata['last_path'] = strrpos($open_path, '-') ? substr($open_path, 0, strrpos($open_path, '-')) : ($open_path ? ' ' : '');
        $this->pagedata['pagehead_active'] = 'source';
        if (!$open_path&&!$is_part){
            $this->singlepage('admin/explorer/theme/directory.html');
        }else{
             echo $this->fetch('admin/explorer/theme/theme_directory_body.html');exit;
         }
    }//End Function

    /**
     * 获取目录树的主题
     */
    private function get_directory_body($theme='',$open_path='',$msg='',$act='')
    {
        /** 加入目录限制 **/
        if(!$this->check($theme,$msg))   $this->_error($msg);

        $fileObj = kernel::single('site_explorer_file');
        $fileObj->set_theme($theme);
        $dir = $this->get_theme_dir($theme, $open_path);
        $filter=array(
                 'id' => $atheme,
                 'dir' => $dir,
                 'show_bak' => false,
                 'type' => 'all'
             );
        $file = $fileObj->file_list($filter);

        $file = $fileObj->parse_filter($file);
        $this->pagedata['file'] = array_reverse($file);
        $this->pagedata['url'] = sprintf('index.php?app=%s&ctl=%s&act=%s&theme=%s',
            $this->_request->get_get('app'),
            $this->_request->get_get('ctl'),
            $act?$act:$this->_request->get_get('act'),
            $this->_request->get_get('theme')
        );
    }

    /*
     * 文件详情
     */
    public function detail()
    {
        $theme = $this->_request->get_get('theme');
        $open_path = $this->_request->get_get('open_path');
        $file_name = $this->_request->get_get('file_name');
        if(!$this->check($theme,$msg))   $this->_error($msg);

        $fileObj = kernel::single('site_explorer_file');
        $fileObj->set_theme($theme);
        $dir = $this->get_theme_dir($theme, $open_path);
        $file_name = trim($file_name);
        $get_file = kernel::single('site_theme_tmpl_file')->get_file($dir, $file_name);


        $filter=array(
                 'id' => $theme,
                 'dir' => $dir,
                 'show_bak' => true,
                 'type' => 'all'
             );
        $filenameInfo = pathinfo($file_name);
        $this->pagedata['file_baklist'] = $fileObj->get_file_baklist($filter, $file_name);
        $this->pagedata['theme'] = $theme;
        $this->pagedata['open_path'] = $open_path;
        $this->pagedata['file_name'] = $file_name;
        $file_content = $fileObj->get_file($get_file);
        if(in_array($filenameInfo['extension'], array('css', 'html', 'js', 'xml'))){
            if($filenameInfo['extension'] == 'css'){
                $file_content = kernel::single('site_theme_tmpl_file')->get_content($file_content);
            }
            $this->pagedata['file_content']  = $file_content;
            if($filenameInfo['extension']=='js'){
                $filenameInfo['extension'] = 'javascript';
            }
            $this->pagedata['mode'] = 'text/'.$filenameInfo['extension'];/*php mode: application/x-httpd-php */
            $this->display('admin/explorer/theme/tpl_source.html');
        }else{
            $this->pagedata['file_url'] = kernel::single('site_theme_tmpl_file')->get_full_file_url($theme, $file_content, $open_path, $file_name);
            $this->display('admin/explorer/theme/tpl_image.html');
        }
    }//End Function

    /*
     * 保存文件
     */
    public function svae_source()
    {
        $this->begin();
        $theme = $this->_request->get_post('theme');
        $open_path = $this->_request->get_post('open_path');
        $file_name = $this->_request->get_post('file_name');

        if(!$this->check($theme,$msg))   $this->_error($msg);

        $has_bak = ($this->_request->get_post('has_bak')) ? true : false;
        $has_clearcache = ($this->_request->get_post('has_clearcache')) ? true : false;
        $file_source = $this->_request->get_post('file_source');

        $fileObj = kernel::single('site_explorer_file',$theme);
        $fileObj->set_theme($theme);
        $dir = $this->get_theme_dir($theme, $open_path);

        $get_file = kernel::single('site_theme_tmpl_file')->get_file($dir, $file_name);

        if($has_bak){
            $fileObj->backup_file($get_file);
        }
        $fileObj->save_source($get_file, $file_source);
        if($has_clearcache){
            if(!ECAE_MODE) @touch($dir . '/' . $file_name);
            kernel::single('site_theme_base')->set_theme_cache_version($theme);
        }
        $this->end(true, app::get('site')->_('保存成功'));
    }//End Function

    /*
     * 保存图片文件
     */
    public function save_image()
    {
        $this->begin();
        $theme = $this->_request->get_post('theme');
        $open_path = $this->_request->get_post('open_path');
        $file_name = $this->_request->get_post('file_name');

        if(!$this->check($theme,$msg))   $this->_error($msg);

        $has_bak = ($this->_request->get_post('has_bak')) ? true : false;

        $fileObj = kernel::single('site_explorer_file',$theme);
        $dir = $this->get_theme_dir($theme, $open_path);

        $get_file = kernel::single('site_theme_tmpl_file')->get_file($dir, $file_name);

        if($has_bak){
            $fileObj->backup_file($get_file);
        }
        $file_name = kernel::single('site_theme_tmpl_file')->get_file($dir, $file_name);
        $fileObj->save_image($file_name, $_FILES['upfile']);

        $this->end(true, app::get('site')->_('保存成功'));
    }//End Function

    /*
     * 删除文件
     */
    public function delete_file()
    {
        $this->begin();
        $theme = $this->_request->get_get('theme');
        $open_path = $this->_request->get_get('open_path');
        $file_name = $this->_request->get_get('file_name');

        if(!$this->check($theme,$msg))   $this->_error($msg);

        $dir = $this->get_theme_dir($theme, $open_path);
        $fileObj = kernel::single('site_explorer_file',$theme);
        $file_name = trim($file_name);
        $file_name = kernel::single('site_theme_tmpl_file')->get_file($dir, $file_name);
        $fileObj->delete_file($file_name);

        $this->end(true, app::get('site')->_('删除成功'));
    }//End Function

    /*
     * 恢复文件
     */
    public function recover_file()
    {
        $this->begin();
        $theme = $this->_request->get_get('theme');
        $open_path = $this->_request->get_get('open_path');
        $file_name = $this->_request->get_get('file_name');

        if(!$this->check($theme,$msg))   $this->_error($msg);

        $dir = $this->get_theme_dir($theme, $open_path);
        $fileObj = kernel::single('site_explorer_file',$theme);

        $file_name = kernel::single('site_theme_tmpl_file')->get_file($dir, $file_name);
        $fileObj->recover_file($file_name);
        $this->end(true, app::get('site')->_('恢复成功'));
    }//End Function

}//End Class
