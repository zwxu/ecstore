<?php


class site_ctl_admin_base_setting extends site_admin_controller {

    /*
     * workground
     * @var string
     */
    var $workground = 'site_ctl_admin_base_setting';

    public function index() {
        $all_settings = array(
            app::get('site')->_('基本信息') => array (
                'site.name',
                'system.site_icp',
                'system.foot_edit',
            ),

            app::get('site')->_ ('高级设置') => array (
                'base.site_page_cache',
                'base.site_params_separator',
                'base.enable_site_uri_expanded',
                'base.site_uri_expanded_name',
                'base.check_uri_expanded_name',
            ),
        );
        $html = kernel::single ( 'site_base_setting' )->process ( $all_settings );
        $this->pagedata ['_PAGE_CONTENT'] = $html;
        $this->page ();
    } //End Function


}//End Class
