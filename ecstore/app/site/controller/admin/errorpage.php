<?php

/*
 * @package site
 * @author edwin.lzh@gmail.com
 * @license
 */
class site_ctl_admin_errorpage extends site_admin_controller
{

     function index(){
        $arr_page_list = kernel::single('site_errorpage_list')->getList();
        $this->pagedata['list'] = $arr_page_list;
        $this->page( 'admin/errorpage/index.html' );

        #case 'searchempty':
        #    $this->pagedata['pagename'] = __('搜索为空时显示内容');
        #    $this->pagedata['code'] = 'searchempty';
        #    $this->pagedata['errorpage'] = app::get('b2c')->getConf('errorpage.searchempty');
        #    $templete='searchempty.html';
    }
    
    public function edit() {
        $key = $_GET['key'];
        if( $key ) {
            $errorpage = $this->app->getConf($key);
            $info = $this->pagedata['info'] = kernel::single('site_errorpage_list')->getList($key);
            if( !$errorpage ) $errorpage = $info['errormsg'];
            $this->pagedata['errorpage'] = $errorpage;
            
            $this->singlepage('admin/errorpage/edit.html');
        } else {
            $this->begin();
            $this->end( false,'key值错误 ！' );
        }
    }
    
    private function get_index_url() {
        return app::get('desktop')->router()->gen_url( array('app'=>'site','ctl'=>'admin_errorpage','act'=>'index') );
    }

    function save(){
        $this->begin();
        $this->app->setConf( $_POST['key'],$_POST['errorpage'] );
        $this->end(true,app::get('site')->_("保存成功"));
    }



}//End Class
