<?php


class content_ctl_admin_article_single extends content_admin_controller 
{

    public function editor() 
    {
        $article_id = $this->_request->get_get('article_id');
        $detail = kernel::single('content_article_detail')->get_detail($article_id);
        if($detail['indexs']['type'] != 2)  die();
        $this->pagedata['detail'] = $detail;
        $this->pagedata['shopadmin'] = kernel::router()->app->base_url(1);
        $this->pagedata['theme'] = kernel::single('site_theme_base')->get_default();
        $this->pagedata['site_url'] = app::get('site')->router()->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'index', 'arg0'=>$detail['indexs']['article_id']));
        $this->singlepage('admin/article/single/editor.html');
    }//End Function

    public function preview() 
    {
        $article_id = $this->_request->get_get('article_id');
        $layout = $this->_request->get_get('layout');

        $theme = kernel::single('site_theme_base')->get_default();
        
        kernel::single('content_article_single')->editor($article_id, $layout);
        kernel::single('base_session')->close();

        $render = new base_render(app::get('content'));
        $render->force_compile = true;

        $render->_compiler()->set_view_helper('function_header', 'content_article_helper');
        $render->_compiler()->set_view_helper('function_footer', 'content_article_helper');
        $render->_compiler()->set_compile_helper('compile_widgets', kernel::single('content_article_complier'));

        $render->pagedata['include'] = 'content:'.$article_id;

        $render->pagedata['theme'] = $theme;

        $render->display('admin/article/single/frame.html', 'content');
    }//End Function

    public function layout() 
    {
        $article_id = $this->_request->get_get('article_id');

        $this->pagedata['layouts'] = kernel::single('content_article_single')->get_layout_list();
        $this->pagedata['article_id'] = $article_id;
        $this->display('admin/article/single/layout.html');
    }//End Function


}//End Class
