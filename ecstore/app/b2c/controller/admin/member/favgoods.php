<?php
 
 
class b2c_ctl_admin_member_favgoods extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';

    function index(){
        $this->finder('b2c_mdl_member_favgoods',array(
            'title'=>app::get('b2c')->_('收藏商品'),
            'actions'=>array( 
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_export'=>false,
            ) ));
    }


}
