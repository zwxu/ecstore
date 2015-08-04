<?php

 

class content_ctl_admin_node_single extends content_admin_controller 
{

    public function editor() 
    {
        $id = $this->_request->get_get('node_id');
        $info = kernel::single('content_article_node')->get_node($id);
        
        if(!$info['homepage'])  die();
        $this->pagedata['detail'] = $info;
        $this->pagedata['shopadmin'] = kernel::router()->app->base_url(1);
        $this->pagedata['theme'] = kernel::single('site_theme_base')->get_default();
        $this->pagedata['site_url'] = app::get('site')->router()->gen_url(array('app'=>'content', 'ctl'=>'site_article', 'act'=>'i', 'arg0'=>$info['node_id']));
        $this->singlepage('admin/node/single/editor.html');
    }//End Function

    public function preview() 
    {
        $id = $this->_request->get_get('node_id');
        $layout = $this->_request->get_get('layout');

        $theme = kernel::single('site_theme_base')->get_default();
        
        kernel::single('content_article_node')->editor($id, $layout);
        kernel::single('base_session')->close();

        $render = new base_render(app::get('content'));
        $render->force_compile = true;

        $render->_compiler()->set_view_helper('function_header', 'content_article_helper');
        $render->_compiler()->set_view_helper('function_footer', 'content_article_helper');
        $render->_compiler()->set_compile_helper('compile_widgets', kernel::single('content_article_complier'));

        $render->pagedata['include'] = 'content_node:'.$id;

        $render->pagedata['theme'] = $theme;

        $render->display('admin/node/single/frame.html', 'content');
    }//End Function

    public function layout() 
    {
        $node_id = $this->_request->get_get('node_id');

        $this->pagedata['layouts'] = kernel::single('content_article_node')->get_layout_list();
        $this->pagedata['node_id'] = $node_id;
        $this->display('admin/node/single/layout.html');
    }//End Function


}//End Class
