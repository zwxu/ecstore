<?php



class site_ctl_admin_theme_tmpl extends site_admin_controller
{

    /*
     * workground
     * @var string
     */
    var $workground = 'site.wrokground.theme';

    private function get_theme_dir($theme, $open_path='')
    {
        return realpath(THEME_DIR . '/' . $theme . '/' . str_replace(array('-','.'), array('/','/'), $open_path));
    }//End Function

    public function index()
    {
        $theme = $this->_request->get_get('theme');
        $this->pagedata['list'] = kernel::single('site_theme_tmpl')->get_edit_list($theme);
        $this->pagedata['types'] = kernel::single('site_theme_tmpl')->get_name();
        $this->pagedata['theme'] = $theme;
        $this->display('admin/theme/tmpl/index.html');
    }//End Function

    public function add()
    {
        $theme = $this->_request->get_get('theme');
        if(!$this->check($theme,$msg))   $this->_error($msg);

        $this->pagedata['theme'] = $theme;
        $this->pagedata['type'] = $this->_request->get_get('type')?$this->_request->get_get('type'):'index';
        $this->pagedata['types'] = kernel::single('site_theme_tmpl')->get_name();
        $this->pagedata['types']['gallery'] = '商品列表/频道页';

        $this->pagedata['content'] = kernel::single('site_theme_tmpl_file')->get_source_code($theme,$this->pagedata['type']);
		
		//begin
		$cat = app::get('b2c')->model('goods_cat')->getList("*",array('parent_id'=>0,'disabled'=>'false'));
		$cats = array();
		foreach($cat as $k=>$v){
			$cats[$v['cat_id']] = $v['cat_name'];
		}
		$this->pagedata['cat'] = $cats;
		//echo '<pre>';print_r($cats);exit;
		//end by lijun 模板类型
        $this->display('admin/theme/tmpl/add.html');
    }//End Function

    public function add_source_page()
    {
        $theme = $this->_request->get_get('theme');
        if(!$this->check($theme,$msg))   $this->_error($msg);

        $this->pagedata['theme'] = $theme;
        $this->pagedata['type'] = $this->_request->get_get('type');
        $this->pagedata['types'] = kernel::single('site_theme_tmpl')->get_name();

        $this->pagedata['content'] = kernel::single('site_theme_tmpl_file')->get_source_code($theme,$this->pagedata['type']);

        echo $this->fetch('admin/theme/tmpl/add_resource.html');exit;
    }//End Function

    public function set_default()
    {
        $this->begin();
        $id = $this->_request->get_get('id');
        if($id > 0 && is_numeric($id)){
            $data = $this->app->model('themes_tmpl')->getList('*', array('id'=>$id));
            $data = $data[0];
            if($data['id']){
                kernel::single('site_theme_tmpl')->set_default($data['tmpl_type'], $data['theme'], $data['tmpl_path']);
                $this->end(true, app::get('site')->_('设置成功'));
            }
        }else {
            $this->end(false, app::get('site')->_('设置失败'));
        }
    }//End Function

    /*
     * 添加模版
     */
    public function insert_tmpl()
    {
        $this->begin();
        $data['theme'] = $this->_request->get_post('theme');
        if(!$this->check($data['theme'],$msg))   $this->_error($msg);

        $data['tmpl_type'] = $this->_request->get_post('tmpl_type');
        $data['tmpl_name'] = $this->_request->get_post('tmpl_name');
        $data['tmpl_path'] = $this->_request->get_post('tmpl_path');
        $data['content'] = $this->_request->get_post('content');

		if($data['tmpl_type']=='gallery'){
			$data['type'] = $this->_request->get_post('type');
		} 
		

        if(kernel::single('site_theme_tmpl')->insert_tmpl($data,$msg)){
            $this->end(true, $msg);
        }else{
            $this->end(false, $msg);
        }
    }//End Function

    /*
     * 添加相似
     */
    public function copy_tmpl()
    {
        $this->begin();
        $theme = $this->_request->get_get('theme');
        $file_name = $this->_request->get_get('tmpl');

        if(!$this->check($theme,$msg))   $this->end(false, $msg);
        $tmpl = kernel::single('site_theme_tmpl');
        $result = $tmpl->copy_tmpl($file_name, $theme);
        if($result){
            $this->end(true, app::get('site')->_('添加成功'));
        }else{
            $this->end(false, app::get('site')->_('添加失败'));
        }

    }//End Function

    /*
     * 删除模版文件
     */
    public function delete_tmpl()
    {
        $this->begin();
        $theme = $this->_request->get_get('theme');
        $file_name = $this->_request->get_get('tmpl');

        if(!$this->check($theme,$msg))   $this->end(false,$msg);

        //数据库
        if(kernel::single('site_theme_tmpl')->delete_tmpl($file_name, $theme)){

            //物理
            $dir = $this->get_theme_dir($theme, '/');
            $fileObj = kernel::single('site_explorer_file',$theme);
            $fileObj->delete_file($dir . '/' . $file_name);

            $filter=array(
                     'id' => $theme,
                     'dir' => $dir,
                     'show_bak' => true,
                     'type' => 'all'
                 );
            $file_baklist = $fileObj->get_file_baklist($filter, $file_name);
            if(is_array($file_baklist)){
                foreach($file_baklist AS $fileinfo){
                    $fileObj->delete_file($dir . '/' . $fileinfo['name']);
                }
            }

            $this->end(true, app::get('site')->_('删除成功'));
        }else{
            $this->end(false,app::get('site')->_('删除失败'));
        }
    }//End Function

}//End Class
