<?php

class business_member_menuextends {
    public function __construct($app) {
        $this->app = $app;
    } 

    public function get_extends_menu(&$arr_menus, $args = array()) {

        $arr_return_product = array();
        $arr_return_money = array();
        if (app::get('aftersales')->getConf('site.is_open_return_product'))
        {
            $arr_return_product = array('label' => app::get('b2c')->_('退货管理'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'seller_returns_reship');
            $arr_return_money = array('label' => app::get('b2c')->_('退款管理'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'seller_returns_refund');
        }

        $arr_extends = array(); { // if($this->app->getConf('site.is_open_seller'))
            $arr_extends = array(
                array('label' => app::get('b2c')->_('交易管理'),
                    'mid' => 7,
                    'items' => array(
                        array('label' => app::get('b2c')->_('订单管理'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'seller_order'),
                        $arr_return_product,
                        $arr_return_money,
                        array('label' => app::get('b2c')->_('评论管理'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'busydiscuss'),
                        )
                    ),
                array('label' => app::get('b2c')->_('物流管理'),
                    'mid' => 8,
                    'items' => array(
                        array('label' => app::get('b2c')->_('物流工具'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'dlycorp'),
                        )
                    ),
                array('label' => app::get('b2c')->_('宝贝管理'),
                    'mid' => 9,
                    'items' => array(
                        array('label' => app::get('b2c')->_('发布宝贝'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'goods_type_select'),
                        array('label' => app::get('b2c')->_('批量发布宝贝'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'goods_import'),
                        array('label' => app::get('b2c')->_('虚拟卡管理'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'my_entity'),
                        array('label' => app::get('b2c')->_('出售中的宝贝'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'goods_onsell'),
                        array('label' => app::get('b2c')->_('仓库中的宝贝'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'goods_instock'),
                        array('label' => app::get('b2c')->_('预警中的宝贝'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'goods_alert'),
                        array('label' => app::get('b2c')->_('宝贝分类管理'), 'app' => 'business', 'ctl' => 'site_goods_cat', 'link' => 'return_goodcat'),
                        array('label' => app::get('b2c')->_('宝贝品牌管理'), 'app' => 'business', 'ctl' => 'site_brand', 'link' => 'return_brand'),
                        )
                    ),
                array('label' => app::get('b2c')->_('店铺管理'),
                    'mid' => 10,
                    'items' => array(
                        array('label' => app::get('b2c')->_('查看店铺'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'storeinfo'),
                        array('label' => app::get('b2c')->_('基本设置'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'editstore'),
                        array('label' => app::get('b2c')->_('角色管理'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'storeroles'),
                        array('label' => app::get('b2c')->_('店员管理'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'storemember'),

                        array('label' => app :: get('business') -> _('模版设置'), 'app' => 'business', 'ctl' => 'site_theme', 'link' => 'theme'),
                        array('label' => app :: get('business') -> _('保证金'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'earnest_manger'),
                        )
                    ),
                 array('label' => app::get('b2c')->_('客服中心'),
                    'mid' => 10,
                    'items' => array(
                        array('label' => app::get('b2c')->_('咨询管理'), 'app' => 'business', 'ctl' => 'site_consult', 'link' => 'consult_manage'),
                        array('label' => app::get('b2c')->_('站内信'), 'app' => 'business', 'ctl' => 'site_storemsg', 'link' => 'store_msg'),
                        array('label' => app::get('b2c')->_('在线客服管理'), 'app' => 'business', 'ctl' => 'site_webcall', 'link' => 'manage'),
                        )
                    ),

                    array('label' => app::get('b2c')->_('营销中心'),
                        'mid'=>10,
                        'items'=>array(
                            array('label' => app::get('b2c')->_('优惠券'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'storecoupon'),
                            array('label'=>app::get('timedbuy')->_('活动报名'),'app'=>'business','ctl'=>'site_activity','link'=>'attend'),
                            array('label'=>app::get('timedbuy')->_('我参加的活动'),'app'=>'business','ctl'=>'site_activity','link'=>'myAttend'),
                        )
                    ),

                 array('label' => app::get('b2c')->_('商户信息'),
                    'mid' => 10,
                    'items' => array(
                        array('label'=>app::get('b2c')->_('商户信息'),'app'=>'business','ctl'=>'site_member','link'=>'setting'),
                        array('label'=>app::get('b2c')->_('修改密码'),'app'=>'business','ctl'=>'site_member','link'=>'security'),

                        )
                    ),

                );
	
            
            // -------------------------------根据角色设置移除对应菜单（未划分具体范围，暂定）----------------------------------------
            $arr_old = $arr_menus;
            //$arr_menus = array_merge($arr_menus, $arr_extends);
            $arr_menus = $arr_extends;

		   //begin 扩展商店菜单
		   $obj_menu_extends = kernel::servicelist('business_menu_extends');
		   if($obj_menu_extends){
			  foreach ($obj_menu_extends as $obj){
					if (method_exists($obj, 'get_extends_menu'))
						$obj->get_extends_menu($arr_menus, array('0'=>'business', '1'=>'site_member', '2'=>'index'));
				}
			}

            if($args[3] !='background'){

                $obj_members = app :: get('b2c') -> model('members');
                $member = $obj_members -> get_current_member(); 

                $sto= kernel::single("business_memberstore", $member['member_id']);
                $sto->process($member['member_id']);

                if ($sto->isshoper == 'true' || $sto->isshopmember == 'true')
                {
                    $arr_shop_view = array('target'=>'_blank','label' => app::get('b2c')->_('店铺首页'), 'app' => 'business', 'ctl' => 'site_shop', 'link' => 'view', 'args' => array($sto->storeinfo['store_id']));

                    foreach ($arr_menus as $mkey => $mval) {
                        if($mval['label'] == '店铺管理'){
                            array_unshift($arr_menus[$mkey]['items'], $arr_shop_view);
                            break;
                        }
                    }
                }

                // 不是店长。
                if ($sto->isshoper == 'false') {
                    
                    // 是否是店员
                    if ($sto->isshopmember=='true') {
                        $roles_id = $sto->storeinfo[0]['role_id'];
                        $storeroles_model = &$this -> app -> model('storeroles');
                        $workgrounds = $storeroles_model -> getList('*', array('role_id'=>$roles_id), 0, -1);
                       
                        $workground = unserialize($workgrounds[0]['workground']);
                        
                        foreach($arr_menus as $key => &$tree) {
                            foreach($tree['items'] as $k => &$t) {
                                $permission = "app=" . $t['app'] . "&ctl=" . $t['ctl'] . "&act=" . $t['link'];

                                if (in_array($permission, $workground)) {
                                } else {
                                    // $t['checked'] = 1;
                                    unset($tree['items'][$k]);
                                } 
                            }
                            if(count($tree['items']) <=0){
                                unset($arr_menus[$key]);
                            } 
                        }
                        

                    } else {
                        // 普通会员
                         $arr_store = array(
                            array('label' => app::get('b2c')->_('我要开店'),
                                'mid' => 7,
                                'items' => array(
                                    array('label' => app::get('b2c')->_('前去开店'), 'app' => 'business', 'ctl' => 'site_storeapply', 'link' => 'index'),
                                    )
                                ),
                          );
                        $arr_menus =  $arr_store;
                    } 
                } 

            }
            // -----------------------------------------------------------------------------------------------
            return true;
        } 
        return false;
    } 
}