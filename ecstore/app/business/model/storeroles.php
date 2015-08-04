<?php
class business_mdl_storeroles extends dbeav_model{
  
    
    function is_exists($role_name,$store_id) 
    { 
        $role_name=trim($role_name);
        $row_data = $this->getList('role_id',array('role_name'=>$role_name,'store_id'=>$store_id));
        if($row_data)
            return true;
        else
            return false;
    }
   


   ####检查工作组名称
   function check_gname($name,$role_id,$store_id=null){
      # $result = $this->db->select("select * from sdb_desktop_roles where role_name='$name'");
      if($store_id){
        $filter=array('role_name'=>$name,'store_id'=>$store_id);
      }else {
        $filter=array('role_name'=>$name);
      }
       $result = $this->getList('role_id',$filter);
       if($result ){

           if($result[0]['role_id'] <> $role_id)
           {
                return $result[0]['role_id'];
           } else {
                return false;
           }
       }
       else{
           return false;
       }
   }
   
   function validate($aData,&$msg){
        if($aData['role_name']==''){
            $msg = app::get('desktop')->_("工作组名称不能为空");
            return false;
        }
        if(!$aData['workground']){
            $msg = app::get('business')->_("请至少选择一个权限");
            return false;
        }

         $opctl = &$this->app->model('storeroles');
         $result = $opctl->check_gname($aData['role_name'], $aData['role_id'],$aData['store_id']);
         if($result){
             $msg = app::get('business')->_("该名称已经存在");
             return false;    
          }
         return true;
     }


   function del_rec($role_id,&$message) {
         if($role_id){
             $filter = array('role_id'=>$role_id);
             if($this->delete($filter)){
                  $message = app::get('business')->_("删除成功");
                   return true;
             }
             else{
                 $message = app::get('business')->_("删除失败");
                   return true;
             }

        }else{
            $message = app::get('business')->_("参数有误");
             return false;
        }

    }


    function get_cpmenu() {
        /*
       $arr_return_product = array();
        $arr_return_money = array();
           // if (app::get('aftersales')->getConf('site.is_open_return_product'))
            //{
                $arr_return_product = array('label' => app::get('b2c')->_('退货管理'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'seller_returns_reship');
                $arr_return_money = array('label' => app::get('b2c')->_('退款管理'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'seller_returns_refund');
            //}
        
            $arr_extends = array(); 
            $arr_extends = array(
                array('label' => app::get('b2c')->_('交易管理'),
                    'mid' => 7,
                    'items' => array(
                        array('label' => app::get('b2c')->_('订单管理'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'seller_order'),
                        $arr_return_product,
                        $arr_return_money,
                        array('label' => app::get('b2c')->_('评论管理'), 'app' => 'business', 'ctl' => 'site_comment', 'link' => 'busydiscuss'),
                        )
                    ),
                array('label' => app::get('b2c')->_('物流管理'),
                    'mid' => 8,
                    'items' => array(
                        array('label' => app::get('b2c')->_('发货/退货地址'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'dlyaddress'),
                        array('label' => app::get('b2c')->_('物流公司'), 'app' => 'business', 'ctl' => 'site_webcall', 'link' => 'dlycorp'),
                        array('label' => app::get('b2c')->_('运费模板'), 'app' => 'business', 'ctl' => 'site_webcall', 'link' => 'dlytype'),
                        )
                    ),
                array('label' => app::get('b2c')->_('宝贝管理'),
                    'mid' => 9,
                    'items' => array(
                        array('label' => app::get('b2c')->_('发布宝贝'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'goods_add'),
                        array('label' => app::get('b2c')->_('出售中的宝贝'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'goods_onsell'),
                        array('label' => app::get('b2c')->_('仓库中的宝贝'), 'app' => 'business', 'ctl' => 'site_member', 'link' => 'goods_instock'),
                        array('label' => app::get('b2c')->_('宝贝分类管理'), 'app' => 'business', 'ctl' => 'site_goods_cat', 'link' => 'return_goodcat'),
                        array('label' => app::get('b2c')->_('宝贝品牌管理'), 'app' => 'business', 'ctl' => 'site_brand', 'link' => 'return_brand'),
                        )
                    ),
                array('label' => app::get('b2c')->_('店铺管理'),
                    'mid' => 10,
                    'items' => array(
                        array('label' => app::get('b2c')->_('查看店铺'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'storeinfo'),
                        array('label' => app::get('b2c')->_('店铺基本设置'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'editstore'),
                        array('label' => app::get('b2c')->_('店员管理'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'storemember'),
                        // array('label' => app::get('b2c')->_('店铺名片')),
                        array('label' => app::get('b2c')->_('咨询管理'), 'app' => 'business', 'ctl' => 'site_consult', 'link' => 'consult_manage'),
                        // array('label' => app::get('b2c')->_('店铺提醒')),
                        // array('label' => app::get('b2c')->_('商家成长')),
                        // array('label' => app::get('b2c')->_('合作伙伴管理'), 'app' => 'business', 'ctl' => 'site_partner', 'link' => 'partner_manage'),
                        array('label' => app::get('b2c')->_('优惠券管理'), 'app' => 'business', 'ctl' => 'site_store', 'link' => 'storecoupon'),
                        // array('label' => app::get('b2c')->_('店铺服务')),
                        array('label' => app :: get('business') -> _('模版设置'), 'app' => 'business', 'ctl' => 'site_theme', 'link' => 'theme'),
                        array('label' => app::get('b2c')->_('站内信'), 'app' => 'business', 'ctl' => 'site_storemsg', 'link' => 'store_msg'),
                        array('label' => app::get('b2c')->_('在线客服管理'), 'app' => 'business', 'ctl' => 'site_webcall', 'link' => 'manage'),
                        )
                    ),
                );
	
            
           $arr_menus = $arr_extends;

           */

		   //begin 扩展商店菜单
		   $obj_menu_extends = kernel::servicelist('business.member_menu_extends');
		   if($obj_menu_extends){
			   foreach ($obj_menu_extends as $obj){
					if (method_exists($obj, 'get_extends_menu'))
						$obj->get_extends_menu($arr_menus, array('0'=>'business', '1'=>'site_member', '2'=>'index','3'=>'background'));
				}
			}


            $arr_shop_view = array('target'=>'_blank','label' => app::get('b2c')->_('店铺首页'), 'app' => 'business', 'ctl' => 'site_shop', 'link' => 'view', 'args' => array($sto->storeinfo['store_id']));
            foreach ($arr_menus as $mkey => $mval) {
                 if($mval['label'] == '店铺管理'){
                     array_unshift($arr_menus[$mkey]['items'], $arr_shop_view);
                     break;
                 }
             }

        return  $arr_menus;
    } 

}
