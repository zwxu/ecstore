<?php

 

class site_ctl_admin_explorer_app extends site_admin_controller 
{
    /*
     * workground
     * @var string
     */
    var $workground = 'site_ctl_admin_explorer_app';

    /*
     * app::get('site')->_(验证是否可被编辑)
     * @param string $app_id
     * @param string $content_path
     * @return boolean
     * 
     */
    private function check($app_id, $content_path) 
    {
        return app::get('site')->model('explorers')->select()->columns('id')->where('app = ?', $app_id)->where('path = ?', str_replace('-', '/', $content_path))->instance()->fetch_one() ? true : false;
    }//End Function

    /*
     * app::get('site')->_(app模版目录)
     */
    public function index() 
    {
        $this->finder('site_mdl_explorers', array(
            'title' => app::get('site')->_('APP资源管理'),
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => false,
            'use_buildin_export' => false,
        ));
    }//End Function

    /*
     * app::get('site')->_(目录浏览)
     */
    public function directory() 
    {
        $app_id = $this->_request->get_get('app_id');
        $content_path = $this->_request->get_get('content_path');
        $open_path = trim($this->_request->get_get('open_path'));

        if(!$this->check($app_id, $content_path))
            $this->_redirect('index.php?app=site&ctl=admin_explorer_app&act=index');  //app::get('site')->_(验证是否可以浏览)
        
        $fileObj = kernel::single('site_explorer_file');
        $dir = realpath(APP_DIR . '/' . $app_id . '/' . str_replace('-', '/', $content_path) . '/' . str_replace(array('-','.'), array('/','/'), $open_path));   //open_pathapp::get('site')->_(不允许有)'./'&'../'
        $filter=array(
                 'id' => $app_id,
                 'dir' => $dir,
                 'show_bak' => false,
                 'type' => 'all'
             );
        $file = $fileObj->file_list($filter);
        $file = $fileObj->parse_filter($file);
        $this->pagedata['file'] = array_reverse($file);
        $this->pagedata['url'] = sprintf('index.php?app=%s&ctl=%s&act=%s&app_id=%s&content_path=%s',
            $this->_request->get_get('app'),
            $this->_request->get_get('ctl'),
            $this->_request->get_get('act'), 
            $this->_request->get_get('app_id'),
            $this->_request->get_get('content_path')
        );
        $this->pagedata['app_id'] = $app_id;
        $this->pagedata['content_path'] = $content_path;
        $this->pagedata['open_path'] = $open_path;
        $this->pagedata['last_path'] = strrpos($open_path, '-') ? substr($open_path, 0, strrpos($open_path, '-')) : ($open_path ? ' ' : '');
        $this->page('admin/explorer/app/directory.html');
    }//End Function

    /*
     * app::get('site')->_(文件详情)
     */
    public function detail() 
    {
        $app_id = $this->_request->get_get('app_id');
        $content_path = $this->_request->get_get('content_path');
        $open_path = $this->_request->get_get('open_path');
        $file_name = $this->_request->get_get('file_name');

        if(!$this->check($app_id, $content_path))   $this->_error();
        $fileObj = kernel::single('site_explorer_file');
        $dir = realpath(APP_DIR . '/' . $app_id . '/' . str_replace('-', '/', $content_path) . '/' . str_replace(array('-','.'), array('/','/'), $open_path));   //open_pathapp::get('site')->_(不允许有)'./'&'../'
        $filter=array(
                 'id' => $app_id,
                 'dir' => $dir,
                 'show_bak' => true,
                 'type' => 'all'
             );
        $filenameInfo = pathinfo($file_name);
        $this->pagedata['file_baklist'] = $fileObj->get_file_baklist($filter, $file_name);
        $this->pagedata['app_id'] = $app_id;
        $this->pagedata['content_path'] = $content_path;
        $this->pagedata['open_path'] = $open_path;
        $this->pagedata['file_name'] = $file_name;
        if(in_array($filenameInfo['extension'], array('css', 'html', 'js', 'xml'))){
            $this->pagedata['file_content']  = $fileObj->get_file($dir . '/' . $file_name);
            $this->display('admin/explorer/app/tpl_source.html');
        }else{
            $this->pagedata['file_url'] = kernel::base_url(1) .  rtrim(str_replace('//', '/', '/app/' . $app_id . '/' . str_replace('-', '/', $content_path) . '/' . str_replace(array('-','.'), array('/','/'), $open_path) . '/' . $file_name));
            $this->display('admin/explorer/app/tpl_image.html');
        }
    }//End Function

    /*
     * app::get('site')->_(保存文件)
     */
    public function svae_source() 
    {
        $this->begin();
        $app_id = $this->_request->get_post('app_id');
        $content_path = $this->_request->get_post('content_path');
        $open_path = $this->_request->get_post('open_path');
        $file_name = $this->_request->get_post('file_name');

        if(!$this->check($app_id, $content_path))   $this->_error();

        $has_bak = ($this->_request->get_post('has_bak')) ? true : false;
        $file_source = $this->_request->get_post('file_source');

        $fileObj = kernel::single('site_explorer_file');
        $dir = realpath(APP_DIR . '/' . $app_id . '/' . str_replace('-', '/', $content_path) . '/' . str_replace(array('-','.'), array('/','/'), $open_path));   //open_pathapp::get('site')->_(不允许有)'./'&'../'
        if($has_bak){
            $fileObj->backup_file($dir . '/' . $file_name);
        }
        $fileObj->save_source($dir . '/' . $file_name, $file_source);
        $this->end(true, app::get('site')->_('保存成功'));
    }//End Function

    /*
     *app::get('site')->_( 保存图片文件)
     */
    public function save_image() 
    {
        $this->begin();
        $app_id = $this->_request->get_post('app_id');
        $content_path = $this->_request->get_post('content_path');
        $open_path = $this->_request->get_post('open_path');
        $file_name = $this->_request->get_post('file_name');

        if(!$this->check($app_id, $content_path))   $this->_error();

        $has_bak = ($this->_request->get_post('has_bak')) ? true : false;

        $fileObj = kernel::single('site_explorer_file');
        $dir = realpath(APP_DIR . '/' . $app_id . '/' . str_replace('-', '/', $content_path) . '/' . str_replace(array('-','.'), array('/','/'), $open_path));   //open_pathapp::get('site')->_(不允许有)'./'&'../'
        if($has_bak){
            $fileObj->backup_file($dir . '/' . $file_name);
        }
        $fileObj->save_image($dir . '/' . $file_name, $_FILES['upfile']);
        $this->end(true, app::get('site')->_('保存成功'));
    }//End Function

    /*
     * app::get('site')->_(删除文件)
     */
    public function delete_file() 
    {
        $this->begin();
        $app_id = $this->_request->get_get('app_id');
        $content_path = $this->_request->get_get('content_path');
        $open_path = $this->_request->get_get('open_path');
        $file_name = $this->_request->get_get('file_name');

        if(!$this->check($app_id, $content_path))   $this->_error();

        $dir = realpath(APP_DIR . '/' . $app_id . '/' . str_replace('-', '/', $content_path) . '/' . str_replace(array('-','.'), array('/','/'), $open_path));
        $fileObj = kernel::single('site_explorer_file');
        $fileObj->delete_file($dir . '/' . $file_name);
        $this->end(true, app::get('site')->_('删除成功'));
    }//End Function

    /*
     * app::get('site')->_(恢复文件)
     */
    public function recover_file() 
    {
        $this->begin();
        $app_id = $this->_request->get_get('app_id');
        $content_path = $this->_request->get_get('content_path');
        $open_path = $this->_request->get_get('open_path');
        $file_name = $this->_request->get_get('file_name');
        
        if(!$this->check($app_id, $content_path))   $this->_error();

        $dir = realpath(APP_DIR . '/' . $app_id . '/' . str_replace('-', '/', $content_path) . '/' . str_replace(array('-','.'), array('/','/'), $open_path));
        
        $fileObj = kernel::single('site_explorer_file');
        $fileObj->recover_file($dir . '/' . $file_name);
        $this->end(true, app::get('site')->_('恢复成功'));
    }//End Function

}//End Class
