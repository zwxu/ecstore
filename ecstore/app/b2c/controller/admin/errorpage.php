<?php
 
class b2c_ctl_admin_errorpage extends site_admin_controller
{

     function errorpage($code){
        $this->path[] = array('text'=>__('系统错误页内容'));
        $templete='errorpage.html';
        switch($code){
            case '404':
                $this->pagedata['pagename'] = __('无法找到页面');
                $this->pagedata['code'] = '404';
                $this->pagedata['errorpage'] = app::get('b2c')->getConf('errorpage.p404');
                break;
            case '500':
                $this->pagedata['pagename'] = __('系统发生错误');
                $this->pagedata['code'] = '500';
                $this->pagedata['errorpage'] = app::get('b2c')->getConf('errorpage.p500');
                break;
            case 'searchempty':
                $this->pagedata['pagename'] = __('搜索为空时显示内容');
                $this->pagedata['code'] = 'searchempty';
                $this->pagedata['errorpage'] = app::get('b2c')->getConf('errorpage.searchempty');
                $templete='searchempty.html';
                break;
        }

        $this->page('admin/'.$templete);
    }

    function saveErrorPage(){
        $this->begin();

        switch($_POST['code']){
            case '404':
                app::get('b2c')->setConf('errorpage.p404',$_POST['errorpage']);
                break;
            case '500':
                app::get('b2c')->setConf('errorpage.p500',$_POST['errorpage']);
                break;
            case 'searchempty':
                app::get('b2c')->setConf('errorpage.searchempty',$_POST['errorpage']);
                break;
        }
        $this->end(true,app::get('site')->_("设置成功！"));
    }



}//End Class
