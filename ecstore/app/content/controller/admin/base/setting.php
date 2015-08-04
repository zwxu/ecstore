<?php


class content_ctl_admin_base_setting extends site_admin_controller 
{
    
    public function index() 
    {
        $all_settings = array(
            app::get('content')->_('基础设置') => array(
                'base.use_node_path_url',
            ),
        );

        $html = '<h2 class="head-title">'.app::get('content')->_('文章配置').'</h2>';

        $html .= kernel::single('content_base_setting')->process($all_settings);
        $this->pagedata['_PAGE_CONTENT'] = $html;
        $this->page();
    }//End Function

}//End Class
