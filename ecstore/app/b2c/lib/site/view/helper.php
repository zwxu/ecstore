<?php



class b2c_site_view_helper
{

    function function_header($params, &$smarty)
    {
        if($smarty->app->app_id != 'b2c' && $smarty->app->app_id != 'groupactivity' && $smarty->app->app_id != 'gift' && $smarty->app->app_id != 'business'){
            return "";
        }

        $url = kernel::base_url();

        /** 取到要得到的js **/
        $app_b2c = app::get('b2c');
        $smarty->pagedata['ec_res_url'] = $app_b2c->res_url;
        if ($smarty->app->app_id == 'site') return $smarty->fetch('site/common/resources.html','b2c');

        /** 不同的页面扩展不同的css **/
        $ext_filename = $smarty->_request->get_app_name() . '_' . $smarty->_request->get_ctl_name() . '.html';
        if (file_exists($app_b2c->app_dir.'/view/site/common/ext/'.$ext_filename))
            $smarty->pagedata['extends_header'] .= $smarty->fetch('site/common/ext/'.$ext_filename,'b2c');
        /** end **/

        //修改不能加载其他依赖于B2C的app的css文件引入--@lujy---start
        if(isset($smarty->app_current) && !empty($smarty->app_current)){
            $app_other_id= $smarty->app_current->app_id;
            $app_other_dir= $smarty->app_current->app_dir;
            $ext_other_filename = $smarty->_request->get_app_name() . '_' . $smarty->_request->get_ctl_name() . '.html';
            if (file_exists($app_other_dir.'/view/site/common/ext/'.$ext_other_filename)){
                $other_extends_header .= $smarty->fetch('site/common/ext/'.$ext_other_filename,$app_other_id);
            }
        }
        $smarty->pagedata['extends_header'] = $smarty->pagedata['extends_header'] . $other_extends_header;
        //--end

        $shop['url']['shipping'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'shipping'));
        $shop['url']['total'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'total'));
        $shop['url']['region'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_tools','act'=>'selRegion'));
        $shop['url']['payment'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'payment'));
        $shop['url']['purchase_shipping'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'purchase_shipping'));
        $shop['url']['purchase_def_addr'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'purchase_def_addr'));
        $shop['url']['purchase_payment'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'purchase_payment'));
        $shop['url']['get_default_info'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'get_default_info'));
        $shop['url']['diff'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'diff'));
        $shop['base_url'] = $url;
        $shop['url']['fav_url'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'ajax_fav'));

        $smarty->pagedata['shopDefine'] = json_encode($shop);
        $smarty->pagedata['TITLE'] = &$smarty->title;

        $smarty->pagedata['KEYWORDS'] = &$smarty->keywords;
        $smarty->pagedata['DESCRIPTION'] = &$smarty->description;
        if($smarty->nofollow == '是')
             $smarty->pagedata['NOFOLLOW'] = 'true';
        else
             $smarty->pagedata['NOFOLLOW'] = 'false';
        if($smarty->noindex == '是')
             $smarty->pagedata['NOINDEX'] = 'true';
        else
             $smarty->pagedata['NOINDEX'] = 'false';

        $obj_base_component_request = kernel::single('base_component_request');
        $ctl_name = $obj_base_component_request->get_ctl_name();
        $act_name = $obj_base_component_request->get_act_name();
        $app_name = $obj_base_component_request->get_app_name();
        if($ctl_name =='site_product' && $act_name == 'index' && $app_name == 'b2c')
        $smarty->pagedata['HTS'] = 'true';

        return $smarty->fetch('site/common/header.html', app::get('b2c')->app_id);
    }



    function function_footer($params, &$smarty)
    {

         if(app::get('openid')->is_actived())
        {
            $smarty->pagedata['openid_open'] = 'true';
            $Certid = base_certificate::get('certificate_id');
            $smarty->pagedata['cert_id'] = $Certid;
            $smarty->pagedata['openid_lg_url'] = kernel::base_url(1);
        }
        else
        {
            $smarty->pagedata['openid_open'] = 'false';
        }
        $smarty->pagedata['login_type'] = app::get('b2c')->getConf('site.login_type');
        $smarty->pagedata['app'] = app::get('b2c')->getConf('site.login_type');
        if($smarty->app->app_id !='b2c'){
            $shop['url']['region'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_tools','act'=>'selRegion'));
            $smarty->pagedata['shopDefine'] = json_encode($shop);
        }
        foreach (kernel::servicelist('b2c.footer.shop.set.extends') as $obj){
            if (method_exists($obj,'header_shop_set_extends'))
                $obj->header_shop_set_extends($smarty->pagedata['shopDefine']);
        }
        $html= $smarty->fetch('site/common/footer.html',app::get('b2c')->app_id);

        return $html;
    }

}//结束
