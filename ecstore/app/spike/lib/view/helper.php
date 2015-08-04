<?php

 
class spike_view_helper{

    function __construct($app){
        $this->app = $app;
    }

    function function_goodsspecSpike($params) {
        return kernel::single("spike_goods_detail_spec")->show($params['goods_id'], $aGoods, array('spec_node_new'=>$params['spec_node_new'],'spec_node'=>$params['spec_node']));
    }

    function function_header($params, &$smarty)
    {
        $url = kernel::base_url();

        /** 取到要得到的js **/
        $app_spike = app::get('spike');

        /** 不同的页面扩展不同的css **/
        $ext_filename = $smarty->_request->get_app_name() . '_' . $smarty->_request->get_ctl_name() . '.html';
        if (file_exists($app_spike->app_dir.'/view/site/common/ext/'.$ext_filename))
            $smarty->pagedata['extends_header'] .= $smarty->fetch('site/common/ext/'.$ext_filename,'spike');
        /** end **/

        return $smarty->fetch('site/common/header.html', app::get('spike')->app_id);
    }

}
