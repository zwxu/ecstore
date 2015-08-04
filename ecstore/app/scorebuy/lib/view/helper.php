<?php

 
class scorebuy_view_helper{

    function __construct($app){
        $this->app = $app;
    }

    function function_goodsspecScore($params) {
        return kernel::single("scorebuy_goods_detail_spec")->show($params['goods_id'], $aGoods, array('spec_node_new'=>$params['spec_node_new'],'spec_node'=>$params['spec_node']));
    }

    function function_header($params, &$smarty)
    {
        $url = kernel::base_url();

        /** 取到要得到的js **/
        $app_score = app::get('scorebuy');

        /** 不同的页面扩展不同的css **/
        $ext_filename = $smarty->_request->get_app_name() . '_' . $smarty->_request->get_ctl_name() . '.html';
        if (file_exists($app_score->app_dir.'/view/site/common/ext/'.$ext_filename))
            $smarty->pagedata['extends_header'] .= $smarty->fetch('site/common/ext/'.$ext_filename,'scorebuy');
        /** end **/

        return $smarty->fetch('site/common/header.html', app::get('scorebuy')->app_id);
    }

}
