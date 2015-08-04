<?php

class groupbuy_ctl_site_cart extends b2c_ctl_site_cart{
    public function __construct(&$app) {
        parent::__construct(app::get('b2c'));
    }


    public function addCheck($type='goods'){
         /**
         * 处理信息和验证过程
         */
        if(!$this->member_status ) {
            $msg = app::get('b2c')->_('您没有登录或者登录超时！');
            echo json_encode( array('status'=>'nologin','error'=>$msg) );exit;
        }

        $arr_objects = array();
        if ($objs = kernel::servicelist('group_cart_object_apps'))
        {
            foreach ($objs as $obj)
            {
                if ($obj->need_validate_store()){
                    $arr_objects[$obj->get_type()] = $obj;
                }
            }
        }

        $data = $this->_request->get_params(true);
        /**
         * 处理校验各自的数据是否可以加入购物车
         */
        if (!$arr_objects[$type])
        {
            $msg = app::get('b2c')->_('加入购物车类型错误！');
            echo json_encode( array('status'=>'faile','error'=>$msg) );exit;
        }

        if (method_exists($arr_objects[$type], 'get_data'))
            if (!$aData = $arr_objects[$type]->get_data($data,$msg))
            {
                echo json_encode( array('status'=>'faile','error'=>'参数错误，购买失败') );exit;
            }
       
        // 进行各自的特殊校验
        if (method_exists($arr_objects[$type], 'check_object'))
        {
            if (!$arr_objects[$type]->check_object($aData,$msg))
            {
                echo json_encode( array('status'=>'faile','error'=>'参数错误，购买失败') );exit;
            }
        }

        //检查库存
        $obj_cart_object = kernel::single('b2c_cart_objects');
        
        if (!$obj_cart_object->check_store($arr_objects[$type], $aData, $msg))
        {
           echo json_encode( array('status'=>'faile','error'=>$msg) );exit;
        }

        //检查每人限购 
        $member_id = app::get('b2c')->member_id;
        $applyObj = app::get('groupbuy')->model('groupapply');
        $aid = $applyObj->getOnActIdByGoodsId($aData['goods']['goods_id']);
        if($aid){
            $obj_business_activity = kernel::single('groupbuy_business_activity');
            if (!$obj_business_activity->checkPersonLimit($aData['goods']['num'],$member_id,$aid,&$msg)){
               echo json_encode( array('status'=>'faile','error'=>$msg) );exit;
            }
        }else{
            echo json_encode( array('status'=>'faile','error'=>'不存在此活动，或活动已经结束') );exit;
        }

        //检查买家是否是店家
        $checkSeller = kernel::service('business_check_goods_isMy');
        if($checkSeller){
            if(!$checkSeller->check_isSeller($msg)){
                echo json_encode( array('status'=>'faile','error'=>$msg) );exit;
            }
        }

        $sign = true;
        $check_objects = kernel::servicelist('business_check_goods_isMy');
        if($check_objects){
            foreach($check_objects as $check_object){
                $check_object->check_goods_isMy($aData['goods']['goods_id'],$msg,$sign);
            }
            if(!$sign){
                echo json_encode( array('status'=>'faile','error'=>$msg) );exit;
            }
        }
        echo json_encode( array('status'=>'succ','error'=>'chenggong') );exit;
      
	}

    public function add($type='goods'){
        /**
         * 处理信息和验证过程
         */
        $arr_objects = array();
        if ($objs = kernel::servicelist('group_cart_object_apps'))
        {
            foreach ($objs as $obj)
            {
                if ($obj->need_validate_store()){
                    $arr_objects[$obj->get_type()] = $obj;
                }
            }
        }

        $data = $this->_request->get_params(true);

        /**
         * 处理校验各自的数据是否可以加入购物车
         */
        if (!$arr_objects[$type])
        {
            $msg = app::get('b2c')->_('加入购物车类型错误！');
            if($_POST['mini_cart']){
                echo json_encode( array('error'=>$msg) );exit;
            } else {
                $fail_url = $arr_objects[$type]->get_fail_url($data);
                $this->begin($fail_url);
                $this->end(false, $msg);
            }
        }

        if (method_exists($arr_objects[$type], 'get_data'))
            if (!$aData = $arr_objects[$type]->get_data($data,$msg))
            {
                if($_POST['mini_cart']){
                    echo json_encode( array('error'=>$msg) );exit;
                } else {
                    $fail_url = $arr_objects[$type]->get_fail_url($data);
                    $this->begin($fail_url);
                    $this->end(false, $msg);
                }
            }
            
        // 进行各自的特殊校验
        if (method_exists($arr_objects[$type], 'check_object'))
        {
            if (!$arr_objects[$type]->check_object($aData,$msg))
            {
                if($_POST['mini_cart']){
                    echo json_encode( array('error'=>$msg) );exit;
                } else {
                    $fail_url = $arr_objects[$type]->get_fail_url($data);
                    $this->begin($fail_url);
                    $this->end(false, $msg);
                }
            }
        }
        $obj_cart_object = kernel::single('b2c_cart_objects');
        if (!$obj_cart_object->check_store($arr_objects[$type], $aData, $msg))
        {
            if($_POST['mini_cart']){
                echo json_encode( array('error'=>$msg) );exit;
            } else {
                $fail_url = $arr_objects[$type]->get_fail_url($data);
                $this->begin($fail_url);
                $this->end(false, $msg);
            }
        }


        /** end **/
       $oCartGoods=kernel::single('groupbuy_cart_object_goods');
       $oCartCoupon=kernel::single('groupbuy_cart_object_coupon');
      // $oCartGoods->no_database = true;

      
       if($type=='goods'){//商品
            //检查每人限购
            $member_id = app::get('b2c')->member_id;
            $applyObj = app::get('groupbuy')->model('groupapply');

            $aid = $applyObj->getOnActIdByGoodsId($aData['goods']['goods_id']);
            if($aid){
                $obj_business_activity = kernel::single('groupbuy_business_activity');
                if (!$obj_business_activity->checkPersonLimit($aData['goods']['num'],$member_id,$aid,&$msg)){
                   $fail_url = $this->gen_url( array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'index'));
                   $this->begin($fail_url);
                   $this->end(false, $msg);
                }
            }else{
                $fail_url = $this->gen_url( array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'index'));
                $this->begin($fail_url);
                $this->end(false, '不存在此活动，或活动已经结束');
            }

           if(!$obj_ident = $oCartGoods->add_object($aData,$msg)){
                $fail_url = $arr_objects[$type]->get_fail_url($data);
                $this->begin($fail_url);
                $this->end(false, $msg);
           }else{
                $_SESSION['S[Cart_Fastbuy]']['goods']=$aData;
                $this->redirect($this->gen_url( array('app'=>'groupbuy','act'=>'checkout','ctl'=>'site_cart','arg1'=>1)));
           }
       }else{//优惠券
           if(!$obj_ident = $oCartCoupon->add_object($aData,$msg)){
                $fail_url = $arr_objects[$type]->get_fail_url($data);
                $this->begin($fail_url);
                $this->end(false, $msg);
           }else{
                $status=$oCartGoods->add_object($_SESSION['S[Cart_Fastbuy]']['goods']); //添加商品
                //$status=$oCartCoupon->add_object($aData); //添加优惠券
                $_SESSION['S[Cart_Fastbuy]']['coupon']=$aData;
                $url = array('app'=>'b2c', ctl=>'site_cart','act'=>'checkout');
                $this->_common(1);
                $arr_json_data = array(
                    'success'=>app::get('b2c')->_('优惠券使用成功！'),
                    'data'=>$this->pagedata['aCart']['object']['coupon'],
                    'md5_cart_info'=>kernel::single('b2c_cart_objects')->md5_cart_objects(),
                );
                echo json_encode($arr_json_data);exit;
              //  $this->redirect( $url );
           }
       }
        
    } 

    public function checkout($isfastbuy=0){
        /**
         * 取到扩展参数
         */
        $isfastbuy = 1;
        $arr_args = func_get_args();
        $arr_args = array(
            'get' => $arr_args,
            'post' => $_POST,
        );
        $this->pagedata['json_args'] = json_encode($arr_args);

        //检查买家是否是店家
        $checkSeller = kernel::service('business_check_goods_isMy');
        if($checkSeller){
            if(!$checkSeller->check_isSeller($msg)){
                $this->splash('failed', 'back', app::get('b2c')->_('店家不能购买商品，请更换账号！'));
            }
        }

        // 判断顾客登录方式.
        $login_type = app::get('b2c')->getConf('site.login_type');
        $is_member_buy = app::get('b2c')->getConf('security.guest.enabled');

        $arrMember = $this->get_current_member();
        //团购不需要判断登陆
        if($arr_args['get'][0] != 'group'){
            //todo暂时修改为不管是跳转登录还是弹出框登录都统一为跳转到登陆页@lujy
            if (!$arrMember['member_id'] && (($_COOKIE['S']['ST_ShopEx-Anonymity-Buy'] != 'true') || $is_member_buy != 'true'))
                //if (!$arrMember['member_id'] && (($login_type == 'href' && $_COOKIE['S']['ST_ShopEx-Anonymity-Buy'] != 'true') || $is_member_buy != 'true'))
                $this->redirect(array('app'=>'groupbuy','ctl'=>'site_cart','act'=>'loginBuy','arg0'=>'1'));
        }

        //  立即购买标识 begin
        $this->fastbuy=$isfastbuy;
        $this->pagedata['fastbuy']=$isfastbuy;
        //  立即购买标识 end


        // 初始化购物车数据
        $this->_common();
        $this->begin(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index'));

        //检查每人限购 
        $goods_id = $this->pagedata['aCart']['object']['goods'][0]['obj_items']['products'][0]['goods_id'];
        $member_id = app::get('b2c')->member_id;
        $gnum = $this->pagedata['aCart']['object']['goods'][0]['quantity'];
        $applyObj = app::get('groupbuy')->model('groupapply');
        $aid = $applyObj->getOnActIdByGoodsId($goods_id);
        if($aid){
            $obj_business_activity = kernel::single('groupbuy_business_activity');
            if (!$obj_business_activity->checkPersonLimit($gnum,$member_id,$aid,&$msg)){
               $this->end(false, $msg);
            }
        }else{
            $this->end(false, '不存在此活动，或活动已经结束');
        }
        //检查每人限购

        // 购物车是否为空
        if ($this->pagedata['is_empty'])
        {
            $this->end(false, app::get('b2c')->_('购物车为空！'));
        }

        // 购物是否满足起订量和起订金额
        if ((isset($this->pagedata['aCart']['cart_status']) && $this->pagedata['aCart']['cart_status'] == 'false') && (isset($this->pagedata['aCart']['cart_error_html']) && $this->pagedata['aCart']['cart_error_html'] != ""))
        {
            $this->end(false, $this->pagedata['aCart']['cart_error_html']);
        }
        $this->pagedata['shipping_url'] = $this->gen_url( array('app'=>'groupbuy','act'=>'shipping','ctl'=>'site_cart') );
        $this->pagedata['total_url'] = $this->gen_url( array('app'=>'groupbuy','act'=>'total','ctl'=>'site_cart') );
        $this->checkout_result();
    }

    /**
     * checkout 结果页面
     * @params int
     * @return null
     */
    public function checkout_result($isfastbuy=0)
    {
        $this->pagedata['checkout'] = 1;
        $this->pagedata['md5_cart_info'] = kernel::single("b2c_cart_objects")->md5_cart_objects();

        $arrMember = $this->get_current_member();
        /** 判断请求的参数是否是group **/
        $arr_request_params = $this->_request->get_params();
        if ($arr_request_params[0] == 'group')
        {
            $this->pagedata['is_group_orders'] = 'true';
        }
        else
        {
            $this->pagedata['is_group_orders'] = 'false';
        }

        /**
         * 额外设置的地址checkbox是否显示
         */
        $is_recsave_display = 'true';
        $is_rec_addr_edit = 'true';
        $app_id = 'b2c';
        $obj_recsave_checkbox = kernel::servicelist('b2c.checkout_recsave_checkbox');
        $arr_extends_checkout = array();
        if ($obj_recsave_checkbox)
        {
            foreach($obj_recsave_checkbox as $object)
            {
                if(!is_object($object)) continue;

                if( method_exists($object,'get_order') )
                    $index = $object->get_order();
                else $index = 10;

                while(true) {
                    if( !isset($arr_extends_checkout[$index]) )break;
                    $index++;
                }
                $arr_extends_checkout[$index] = $object;
            }
            ksort($arr_extends_checkout);
        }
        if ($arr_extends_checkout)
        {
            foreach ($arr_extends_checkout as $obj)
            {
                if ( method_exists($obj,'check_display') )
                    $obj->check_display($is_recsave_display);
                if ( method_exists($obj,'check_edit') )
                    $obj->check_edit($is_rec_addr_edit);
                if ( method_exists($obj,'check_app_id') )
                    $obj->check_app_id($app_id);
            }
        }
        $this->pagedata['is_recsave_display'] = $is_recsave_display;
        $this->pagedata['is_rec_addr_edit'] = $is_rec_addr_edit;
        $this->pagedata['app_id'] = $app_id;

        // 如果会员已登录，查询会员的信息
        $obj_member_addrs = $this->app->model('member_addrs');
        $obj_dltype = $this->app->model('dlytype');
        $addr = array();
        $member_point = 0;
        $shipping_method = '';
        $shipping_id = 0;
        $arr_shipping_method = array();
        $payment_method = 0;
        $def_addr = 0;
        $arr_def_addr = array();
        $str_def_currency = $arrMember['member_cur'] ? $arrMember['member_cur'] : "";
        if ($arrMember['member_id'])
        {
            // 得到当前会员的积分
            $obj_members = $this->app->model('members');
            $arr_member = $obj_members->dump($arrMember['member_id'], 'point,addon');
            $member_point = $arr_member['point'];
            if (isset($arr_member['addon']) && $arr_member['addon'])
            {
                $arr_addon = unserialize(stripslashes($arr_member['addon']));
                if ($arr_addon)
                {
                    $obj_session = kernel::single('base_session');
                    $obj_session->start();
                    if ($arr_addon['def_addr']['usable'])
                    {
                        if (!isset($_COOKIE['purchase']['addr']['usable']))
                        {
                            $arr_addon['def_addr']['usable'] = '';
                            $str_addon = serialize($arr_addon);
                            $obj_members->update(array('addon'=>$str_addon), array('member_id'=>$arrMember['member_id']));
                        }
                        elseif ($_COOKIE['purchase']['addr']['usable'] != md5($obj_session->sess_id().$arrMember['member_id']))
                        {
                            $arr_addon['def_addr']['usable'] = '';
                            $str_addon = serialize($arr_addon);
                            $obj_members->update(array('addon'=>$str_addon), array('member_id'=>$arrMember['member_id']));
                        }
                    }
                    $tmp_cnt = $obj_member_addrs->count(array('member_id'=>$arrMember['member_id'],'def_addr'=>'1'));
                    if ($arr_addon['def_addr']  && ((isset($arr_addon['def_addr']['usable']) && $arr_addon['def_addr']['usable'] == md5($obj_session->sess_id().$arrMember['member_id'])) || $tmp_cnt == 0))
                    {
                        $def_addr = $arr_addon['def_addr']['addr_id'] ? $arr_addon['def_addr']['addr_id'] : 0;
                        $arr_area = explode(':', $arr_addon['def_addr']['area']);
                        $def_area = $arr_area[2];
                        $arr_def_addr = $arr_addon['def_addr'];
                        $arr_def_addr['addr_id'] = $arr_addon['def_addr']['addr_id'];
                        $arr_def_addr['def_addr'] = $arr_addon['def_addr']['def_addr'];
                        $arr_def_addr['addr_region'] = $arr_addon['def_addr']['area'];
                        $arr_def_addr['addr'] = $arr_addon['def_addr']['addr'];
                        $arr_def_addr['zip'] = $arr_addon['def_addr']['zip'];
                        $arr_def_addr['name'] = $arr_addon['def_addr']['name'];
                        $arr_def_addr['mobile'] = $arr_addon['def_addr']['mobile'];
                        $arr_def_addr['tel'] = $arr_addon['def_addr']['tel'] ? $arr_addon['def_addr']['tel'] : '';
                        $arr_def_addr['day'] = $arr_addon['def_addr']['day'] ? $arr_addon['def_addr']['day'] : '';
                        $arr_def_addr['specal_day'] = $arr_addon['def_addr']['specal_day'] ? $arr_addon['def_addr']['specal_day'] : '';
                        $arr_def_addr['time'] = $arr_addon['def_addr']['time'] ? $arr_addon['def_addr']['time'] : '';
                        if ($arr_def_addr['day'] == app::get('b2c')->_('任意日期') && $arr_def_addr['time'] == app::get('b2c')->_('任意时间段'))
                        {
                            unset($arr_def_addr['day']);
                            unset($arr_def_addr['time']);
                        }
                    }
                }
            }

            $addrMember = array(
                'member_id' => $arrMember['member_id'],
            );
            $addrlist = $obj_member_addrs->getList('*',array('member_id'=>$arrMember['member_id']));
            $is_checked = false;
            $is_def = false;
            foreach($addrlist as $key=>$rows)
            {
                if(empty($rows['tel'])){
                    $str_tel = app::get('b2c')->_('手机：').$rows['mobile'];
                }else{
                    $str_tel = app::get('b2c')->_('电话：').$rows['tel'];
                }
                if ((isset($arr_def_addr['addr_id']) && $rows['addr_id'] == $arr_def_addr['addr_id']) || (!$arr_def_addr && $rows['def_addr']))
                {
                    $is_def = true;
                    $is_checked = true;
                }
                $addr[] = array('addr_id'=> $rows['addr_id'],'def_addr'=>$is_def ? 1 : 0,'addr_region'=> $rows['area'],
                                'addr_label'=> $rows['addr'].app::get('b2c')->_(' (收货人：').$rows['name'].' '.$str_tel.app::get('b2c')->_(' 邮编：').$rows['zip'].')');
                if ($rows['def_addr'])
                {
                    $def_addr = $rows['addr_id'];
                    $arr_area = explode(':', $rows['area']);
                    $def_area = $arr_area[2];
                    $arr_def_addr_member = array(
                        'addr_id'=> $rows['addr_id'],
                        'def_addr'=>$rows['def_addr'],
                        'addr_region'=> $rows['area'],
                        'addr'=> $rows['addr'],
                        'zip' => $rows['zip'],
                        'name' => $rows['name'],
                        'mobile' => $rows['mobile'],
                        'tel' => $rows['tel'],
                    );
                }
                else
                {
                    if ($key == 0 && !$def_area)
                    {
                        $arr_area = explode(':', $rows['area']);
                        $def_area = $arr_area[2];
                    }
                }
            }
            if ($arr_def_addr && !$is_checked)
                $this->pagedata['other_addr_checked'] = 'true';
            if ($addrlist && !$arr_def_addr && !$is_checked)
            {
                $def_addr = $addrlist[0]['addr_id'];
                $arr_area = explode(':', $addrlist[0]['area']);
                $def_area = $arr_area[2];
                $arr_def_addr_member = $addrlist[0];
                $arr_def_addr_member['addr_id'] = $addrlist[0]['addr_id'];
                $arr_def_addr_member['def_addr'] = 1;
                $arr_def_addr_member['addr_region'] = $addrlist[0]['area'];
                $arr_def_addr_member['addr'] = $addrlist[0]['addr'];
                $arr_def_addr_member['zip'] = $addrlist[0]['zip'];
                $arr_def_addr_member['name'] = $addrlist[0]['name'];
                $arr_def_addr_member['mobile'] = $addrlist[0]['mobile'];
                $arr_def_addr_member['tel'] = $addrlist[0]['tel'];
                $addr[0]['def_addr'] = 1;
            }
        }

        // shipping, payment and default address
        if ((!$def_addr || !$str_def_currency) && !$arrMember['member_id'])
        {
            if ($_COOKIE['purchase']['addon'])
            {
                $arr_addon = unserialize(stripslashes($_COOKIE['purchase']['addon']));
                if (!$def_addr)
                {
                    if (isset($arr_addon['member']['ship_area']) && $arr_addon['member']['ship_area'])
                    {
                        $def_addr = 0;
                        $arr_area = explode(':', $arr_addon['member']['ship_area']);
                        $def_area = $arr_area[2];
                        $arr_def_addr = $arr_addon['member'];
                        $arr_def_addr['addr_region'] = $arr_addon['member']['ship_area'];
                        $arr_def_addr['addr'] = $arr_addon['member']['ship_addr'];
                        $arr_def_addr['zip'] = $arr_addon['member']['ship_zip'] ? $arr_addon['member']['ship_zip'] : '';
                        $arr_def_addr['name'] = $arr_addon['member']['ship_name'];
                        $arr_def_addr['email'] = $arr_addon['member']['ship_email'];
                        $arr_def_addr['mobile'] = $arr_addon['member']['ship_mobile'];
                        $arr_def_addr['tel'] = $arr_addon['member']['ship_tel'] ? $arr_addon['member']['ship_tel'] : '';
                        $arr_def_addr['day'] = $arr_addon['member']['day'] ? $arr_addon['member']['day'] : '';
                        $arr_def_addr['specal_day'] = $arr_addon['member']['specal_day'] ? $arr_addon['member']['specal_day'] : '';
                        $arr_def_addr['time'] = $arr_addon['member']['time'] ? $arr_addon['member']['time'] : '';
                        if ($arr_def_addr['day'] == app::get('b2c')->_('任意日期') && $arr_def_addr['time'] == app::get('b2c')->_('任意时间段'))
                        {
                            unset($arr_def_addr['day']);
                            unset($arr_def_addr['time']);
                        }
                        $this->pagedata['addr'] = array(
                            'area'=> $arr_addon['member']['ship_area'],
                            'addr'=> $arr_addon['member']['ship_addr'],
                            'zipcode' => $arr_addon['member']['ship_zip'] ? $arr_addon['member']['ship_zip'] : '',
                            'name' => $arr_addon['member']['ship_name'],
                            'email' => $arr_addon['member']['ship_email'],
                            'phone' => array(
                                'mobile'=>$arr_addon['member']['ship_mobile'],
                                'telephone' => $arr_addon['member']['ship_tel'] ? $arr_addon['member']['ship_tel'] : ''
                            ),
                        );
                    }
                }
            }
        }

        $obj_dlytype = $this->app->model('dlytype');
        $arr_shipping_info = $obj_dlytype->get_shiping_info($shipping_id, $this->pagedata['aCart']["subtotal"]);
        $this->pagedata['def_addr'] = $def_addr ? $def_addr : 0;
        $this->pagedata['def_area'] = $def_area ? $def_area : 0;
        
        if($addrlist){
            foreach($addrlist as $k=>$v){
                $area = array();
                $area = explode(':',$v['area']);
                $addrlist[$k]['_area'] = $area[2];
                $area = explode('/',$area[1]);

                if(in_array($area[0],array('北京','天津','上海','重庆'))){
                    $area[0] = '';
                }

                $addrlist[$k]['area_arr'] = $area;
                if($v['def_addr']==1){
                    $addr_default_addr = $addrlist[$k];
                    //unset($addrlist[$k]);
                }
            }
            if(!$addr_default_addr){
                $addrlist[0]['def_addr'] = 1;
                $addr_default_addr = $addrlist[0];
                //unset($addrlist[0]);
            }
        }

        $this->pagedata['addrlist'] = $addrlist;
        $this->pagedata['default_addr'] = $addr_default_addr;
        $defaule_area_id = explode(':',$addr_default_addr['area']);
        $this->pagedata['area_id'] = $defaule_area_id[2];
        $this->pagedata['address']['member_id'] = $arrMember['member_id'];
        $this->pagedata['def_arr_addr'] = $arr_def_addr ? $arr_def_addr : $arr_def_addr_member;
        $this->pagedata['def_arr_addr_member'] = $arr_def_addr_member;
        $this->pagedata['def_arr_addr_other'] = $arr_def_addr;
        $this->pagedata['site_checkout_zipcode_required_open'] = $this->app->getConf('site.checkout.zipcode.required.open');
        /** 是否开启配送时间的限制 */
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        // 是否有默认的当前的配送方式和支付方式
        $this->pagedata['shipping_method'] = (isset($_COOKIE['purchase']['shipping']) && $_COOKIE['purchase']['shipping']) ? unserialize($_COOKIE['purchase']['shipping']) : '';
        $this->pagedata['arr_def_payment'] = (isset($_COOKIE['purchase']['payment']) && $_COOKIE['purchase']['payment']) ? unserialize($_COOKIE['purchase']['payment']) : '';

        /**
         取到优惠券的信息
         */
        if ($arrMember['member_id'])
        {
            $oCoupon = kernel::single('b2c_coupon_mem');
            $aData = $oCoupon->get_list_m($arrMember['member_id']);
            if( is_array($aData) ) {
                foreach( $aData as $_key => $_val ) {
                    if( $_val['memc_used_times'] ) unset($aData[$_key]);
                }
            }
            $this->pagedata['coupon_lists'] = $aData;
        }

        $currency = app::get('ectools')->model('currency');
        $this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');

        if (!$str_def_currency)
        {
            $arrDefCurrency = $currency->getDefault();
            $str_def_currency = $arrDefCurrency['cur_code'];
        }
        else
        {
            $arrDefCurrency = $currency->getcur($str_def_currency);
        }

        $aCur = $currency->getcur($str_def_currency);
        $this->pagedata['current_currency'] = $str_def_currency;

        $obj_payments = kernel::single('ectools_payment_select');
        /** 判断是否有货到付款的支付方式 **/
        $shipping_has_cod = $obj_dlytype->shipping_has_cod();
        $this->pagedata['shipping_has_cod'] = $shipping_has_cod;
        $this->pagedata['payment_html'] = $obj_payments->select_pay_method($this, $arrDefCurrency, $arrMember['member_id']);

        // 得到税金的信息
        $this->pagedata['trigger_tax'] = $this->app->getConf("site.trigger_tax");
        $this->pagedata['tax_ratio'] = $this->app->getConf("site.tax_ratio");

        $demical = $this->app->getConf('system.money.operation.decimals');

        $total_item = $this->objMath->number_minus(array($this->pagedata['aCart']["subtotal"], $this->pagedata['aCart']['discount_amount_prefilter']));
        //$total_item = $this->pagedata['aCart']["subtotal"];
        // 取到商店积分规则
        $policy_method = $this->app->getConf("site.get_policy.method");
        switch ($policy_method)
        {
            case '1':
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
            case '2':
                $subtotal_consume_score = round($this->pagedata['aCart']['subtotal_consume_score']);
                $policy_rate = $this->app->getConf('site.get_rate.method');
                $subtotal_gain_score = round($this->objMath->number_plus(array(0, $this->pagedata['aCart']['subtotal_gain_score'])));
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            case '3':
                $subtotal_consume_score = round($this->pagedata['aCart']['subtotal_consume_score']);
                $subtotal_gain_score = round($this->pagedata['aCart']['subtotal_gain_score']);
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            default:
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
        }

        $total_amount = $this->objMath->number_minus(array($this->pagedata['aCart']["subtotal"], $this->pagedata['aCart']['discount_amount']));
        if ($total_amount < 0)
            $total_amount = 0;
        // 是否可以用积分抵扣
        $obj_point_dis = kernel::service('b2c_cart_point_discount');
        if ($obj_point_dis)
        {
            $obj_point_dis->set_order_total($total_amount);
            $this->pagedata['point_dis_html'] = $obj_point_dis->get_html($arrMember['member_id']);
            $this->pagedata['point_dis_js'] = $obj_point_dis->get_javascript($arrMember['member_id']);
        }
        // 得到cart total支付的信息
        $this->pagedata['order_detail'] = array(
            'cost_item' => $total_item,
            'total_amount' => $total_amount,
            'currency' => $this->app->getConf('site.currency.defalt_currency'),
            'pmt_order' => $this->pagedata['aCart']['discount_amount_order'],
            'pmt_amount' => $this->pagedata['aCart']['discount_amount'],
            'totalConsumeScore' => $subtotal_consume_score,
            'totalGainScore' => $subtotal_gain_score,
            'totalScore' => $totalScore,
            'cur_code' => $strDefCurrency,
            'cur_display' => $strDefCurrency,
            'cur_rate' => $aCur['cur_rate'],
            'final_amount' => $currency->changer($total_amount, $this->app->getConf("site.currency.defalt_currency"), true),
        );

        if ($arrMember['member_id'])
        {
            $this->pagedata['order_detail']['totalScore'] = $member_point;
        }
        else
        {
            $this->pagedata['order_detail']['totalScore'] = 0;
            $this->pagedata['order_detail']['totalGainScore'] = 0;    //如果是非会员购买获得积分为0，@lujy
        }

        $odr_decimals = $this->app->getConf('system.money.decimals');
        $total_amount = $this->objMath->get($this->pagedata['order_detail']['total_amount'], $odr_decimals);
        $this->pagedata['order_detail']['discount'] = $this->objMath->number_minus(array($this->pagedata['order_detail']['total_amount'], $total_amount));
        $this->pagedata['order_detail']['total_amount'] = $total_amount;
        $this->pagedata['order_detail']['current_currency'] = $strDefCurrency;

        // 获得商品的赠品信息
        $arrM_info = array();
        foreach ($this->pagedata['aCart']['object']['goods'] as $arrGoodsInfo)
        {
            if (isset($arrGoodsInfo['gifts']) && $arrGoodsInfo['gifts'])
            {
                $this->pagedata['order_detail']['gift_p'][] = array(
                    'storage' => $arrGoodsInfo['gifts']['storage'],
                    'name' => $arrGoodsInfo['gifts']['name'],
                    'nums' => $arrGoodsInfo['gifts']['nums'],
                );
            }

            // 得到商品购物信息的必填项目
            $goods_id = $arrGoodsInfo['obj_items']['products'][0]['goods_id'];
            $product_id = $arrGoodsInfo['obj_items']['products'][0]['product_id'];
            // 得到商品goods表的信息
            $objGoods = $this->app->model('goods');
            $arrGoods = $objGoods->dump($goods_id, 'type_id');
            if (isset($arrGoods) && $arrGoods && $arrGoods['type']['type_id'])
            {
                $objGoods_type = $this->app->model('goods_type');
                $arrGoods_type = $objGoods_type->dump($arrGoods['type']['type_id'], '*');

                if ($_COOKIE['checkout_b2c_goods_buy_info'])
                {
                    //$this->pagedata['has_goods_minfo'] = true;
                    $goods_need_info = json_decode($_COOKIE['checkout_b2c_goods_buy_info'], 1);

                }
                if ($arrGoods_type['minfo'])
                {
                    if ($arrGoodsInfo['obj_items']['products'][0]['spec_info'])
                        $arrM_info[$product_id]['name'] = $arrGoodsInfo['obj_items']['products'][0]['name'] . '(' . $arrGoodsInfo['obj_items']['products'][0]['spec_info'] . ')';
                    else
                        $arrM_info[$product_id]['name'] = $arrGoodsInfo['obj_items']['products'][0]['name'];
                    $arrM_info[$product_id]['nums'] = $this->objMath->number_multiple(array($arrGoodsInfo['obj_items']['products'][0]['quantity'],$arrGoodsInfo['quantity']));

                    foreach ($arrGoods_type['minfo'] as $key=>$arr_minfo)
                    {
                        if (isset($goods_need_info[$product_id][$key]) && $arr_minfo['label'] == $goods_need_info[$product_id][$key]['name'])
                        {
                            $arr_minfo['value'] = $goods_need_info[$product_id][$key]['val'][0];
                        }else{
                            $no_value = true;
                        }
                        $arrM_info[$product_id]['minfo'][] = $arr_minfo;
                    }
                }
            }
        }

        if($no_value){
            $this->pagedata['has_goods_minfo'] = false;
        }else{
            $this->pagedata['has_goods_minfo'] = true;
        }
        $this->pagedata['minfo'] = $arrM_info;
        $this->pagedata['is_checkout'] = 1;
        $this->pagedata['base_url'] = kernel::base_url().'/';
        // checkout result 页面添加项目埋点
        foreach( kernel::servicelist('b2c.checkout_add_item') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'addItem') ) {
                    $services->addItem($this);
                }
            }
        }

        $shop['url']['shipping'] = app::get('site')->router()->gen_url(array('app'=>'groupbuy','ctl'=>'site_cart','act'=>'shipping'));
        $shop['url']['total'] = app::get('site')->router()->gen_url(array('app'=>'groupbuy','ctl'=>'site_cart','act'=>'total'));
        $shop['url']['region'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_tools','act'=>'selRegion'));
        $shop['url']['payment'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'payment'));
        $shop['url']['purchase_shipping'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'purchase_shipping'));
        $shop['url']['purchase_def_addr'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'purchase_def_addr'));
        $shop['url']['purchase_payment'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'purchase_payment'));
        $shop['url']['diff'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'diff'));
        $shop['base_url'] = $url;
        $this->pagedata['fastbuyShopDefine'] = json_encode($shop);
        
        $this->pagedata['updateStoreTotalUrl'] = app::get('site')->router()->gen_url(array('app'=>'groupbuy','ctl'=>'site_cart','act'=>'updateStoreTotal'));

        $this->pagedata['aCart']['inAct'] = 'true';
        $this->page('site/cart/checkout.html',false,'groupbuy');
    }

    public function updateStoreTotal(){
        $this->_common(0);
        $data = $this->_request->get_post();
        $store_id = $data['store_id'];
        $arr_cart = $this->pagedata['aCart'];
        $shippings = $this->pagedata['arr_shipp'];

        $object = kernel::single('b2c_order_dlytype');
        $shippings = $object->get_store_dlytype($this,$data['area_id'],'',$store_id,$arr_cart['subtotal_weight_store'],$arr_cart['subtotal_store_good_price'],$arr_cart['store_free_shipping'],$arr_cart);
        foreach($shippings as $v){
            if($data['shipping_id']==$v['dt_id']){
                $shipping = $v;
            }
        }
        if($data['is_protect'] === 'true' || $data['is_protect'] === '1' || $data['is_protect'] === true){
            $objMath = kernel::single('ectools_math');
            $cost_protect = $objMath->number_multiple(array($arr_cart['subtotal_store_good_price'][$data['store_id']], $shipping['protect_rate']));
            
            $cost_protect = $cost_protect>$shipping['minprice']?$cost_protect:$shipping['minprice'];//保价费
        }else{
            $cost_protect = 0;
        }//是否保价
         if (isset($arr_cart['promotion']) && $arr_cart['promotion'])
        {
            if($arr_cart['is_free_shipping'][$store_id]==true) {
                   $this->pagedata['discount'][$store_id] = $shipping['money'];
            }
        }

        $subtotal_price_store = $arr_cart['subtotal_price_store'];
        $subtotal_price_store[$data['store_id']] = $arr_cart['subtotal_store_good_price'][$data['store_id']]+$shipping['money']+$cost_protect;
        $store_ship[$data['store_id']] = $shipping['money'];
        $store_protect[$data['store_id']] = $cost_protect;
        $this->pagedata['store_protect'] = $store_protect;
        $this->pagedata['subtotal_price_store'] = $subtotal_price_store;
        $app_id = app::get('business');
        $this->pagedata['store_id'] = $data['store_id'];
        $str_html = $this->fetch("site/cart/item/goods/business_total.html",'groupbuy');
        echo $str_html;exit;
    }
    
     // 删除&清空
    public function remove() {
        $msg = '';
        $this->remove_cart_object($msg);

        $this->_cart_main($msg);
    }
	public function remove_store_coupon($store_id){
		$data = $this->_request->get_params(true);
		$_SESSION['S[Cart_Fastbuy]']['coupon']=array();
		
	}

     private function _cart_main($msg='',$json_type='all') {
       if (!$msg) $msg = app::get('b2c')->_('购物车修改成功！');
       $obj_currency = app::get('ectools')->model('currency');
       $this->pagedata['ajax_html'] = $this->ajax_html;
      
       $this->_common(1);
       if( !$this->pagedata['is_empty'] ) {
       $this->pagedata['aCart']['promotion_subtotal'] = $this->objMath->number_minus(array($this->pagedata['aCart']['subtotal'], $this->pagedata['aCart']['subtotal_discount']));
            $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
            $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');
            switch ($json_type)
            {
                case 'mini':
                    $arr_json_data = array(
                        'sub_total'=>array(
                            'promotion_subtotal'=>$obj_currency->changer_odr($aCart['promotion_subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                        ),
                        'is_checkout'=>false,
                        'number'=>array(
                                'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                                'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                        ),
                        'error_msg'=>$this->pagedata['error_msg'],
                    );
                    $view = 'site/cart/view.html';
                    //$this->view();exit;
                break;
                case 'middle':
                    $arr_json_data = array(
                        'sub_total'=>array(
                            'subtotal_prefilter_after'=>$obj_currency->changer_odr($aCart['subtotal_prefilter_after'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                            'promotion_subtotal'=>$obj_currency->changer_odr($aCart['promotion_subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                        ),
                        'is_checkout'=>false,
                        'number'=>array(
                                'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                                'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                        ),
                        'error_msg'=>$this->pagedata['error_msg'],
                    );
                    if ($this->pagedata['aCart']['discount_amount_order'] > 0)
                        $arr_json_data['sub_total']['discount_amount_order'] = $obj_currency->changer_odr($this->pagedata['aCart']['discount_amount_order'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset);
                    else
                        $arr_json_data['sub_total']['discount_amount_order'] = 0;
                    $view = 'site/cart/middle_index.html';
                break;
                case 'all':
                default:
                    $arr_json_data = array(
                        'success'=> $msg,
                        'sub_total'=>array(
                            'subtotal_prefilter_after'=>$obj_currency->changer_odr($this->pagedata['aCart']['subtotal_prefilter_after'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                            'promotion_subtotal'=>$obj_currency->changer_odr($this->pagedata['aCart']['promotion_subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                        ),
                        'unuse_rule'=>$this->pagedata['unuse_rule'],
                        'is_checkout'=>false,
                        'edit_ajax_data'=>$this->pagedata['edit_ajax_data'],
                        'promotion'=>$this->pagedata['aCart']['promotion'],
                        'error_msg'=>$this->pagedata['error_msg'],
                        'number'=>array(
                            'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                            'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                        ),
                    );
                    if ($this->pagedata['aCart']['discount_amount_order'] > 0)
                        $arr_json_data['sub_total']['discount_amount_order'] = $obj_currency->changer_odr($this->pagedata['aCart']['discount_amount_order'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset);
                    else
                        $arr_json_data['sub_total']['discount_amount_order'] = 0;
                    $view = 'site/cart/index.html';
                break;
            }
        }else{
            $arr_json_data = array(
                'is_empty' => 'true',
                'number'=>array(
                    'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                    'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                ),
            );
       }

        $md5_cart_info = kernel::single('b2c_cart_objects')->md5_cart_objects();
        $arr_json_data['md5_cart_info'] = $md5_cart_info;
        $this->pagedata = $arr_json_data;
        if($json_type=='mini'){
          $this->view();
        }else{
            $this->page($view);
        }
    }

     private function remove_cart_object(&$msg='',$type='all'){
        $aParams = $this->_request->get_params(true);
        $mCartObject = $this->app->model('cart_objects');
        $this->ajax_html = true;  //用于返回页面识别。当无商品是跳转至cart_empty

        $view = "";
        switch ($type){
            case 'mini':
                $view = "site/cart/view.html";
            break;
            case 'middle':
                $view = "site/cart/middle_index.html";
            break;
            case 'all':
            default:
                $view = "site/cart/index.html";
            break;
    }

        if ($aParams[0] == 'coupon'){
            $ident = $aParams['cpn_ident'];

            $_SESSION['S[Cart_Fastbuy]']['coupon'] = null;
           
        }else{
        // 清空购物车
        if($aParams[0] == 'all' || empty($aParams['modify_quantity'])) {
            $obj_type = null;
                if (!$mCartObject->remove_object('', null, $msg)){
                    // 不带入参清空所有的
                    $error_json = array(
                        'error'=>$msg,
                    );
                    $this->pagedata = $error_json;
                    $this->page($view);
                }
        } else {
                // 删除单一商品.
                if($aParams['modify_quantity'] && is_array($aParams['modify_quantity'])){
                    foreach ($aParams['modify_quantity'] as $obj_ident=>$arr_object){
                        if ($arr_object['quantity']){
                            // 删除整个商品对象.
                            if (!$mCartObject->remove_object($aParams[0], $obj_ident, $msg)){
                                $error_json = array(
                                    'error'=>$msg,
                                );
                                $this->pagedata = $error_json;
                                if($type=='mini'){
                                    $this->view();
                                }else{
                                    $this->page($view);
                                }
                    }
                        }else{
                            // 删除购物车对象中的附属品，配件和赠品等.
                            foreach ($arr_object as $obj_type=>$arr_quantity){
                                if (!$mCartObject->remove_object_part($obj_type, $obj_ident, $arr_quantity, $msg)){
                                    $error_json = array(
                                        'error'=>$msg,
                                    );
                                    $this->pagedata = $error_json;
                                    $this->page($view);
            }
        }
    }
                        }
                    }
                }
        }
    }

     public function _common($flag=0) {
        // 购物车数据信息
        if(true){
            // 立即购买
            kernel::single('groupbuy_cart_fastbuy_goods')->get_fastbuy_arr(
                $_SESSION['S[Cart_Fastbuy]']['goods'],
                $_SESSION['S[Cart_Fastbuy]']['coupon'],
                $aCart
                );
        }else{
            $aCart = $this->mCart->get_objects();
        }


        //$this->begin( $this->gen_url( array('app'=>'b2c','act'=>'index','ctl'=>'site_product','arg1'=>$aCart['object']['goods'][0]['params']['goods_id']) ) );
        //判断购物车有没有自己的商品
        foreach($aCart['object']['goods'] as $k=>$v){
            $check_objects = kernel::servicelist('business_check_goods_isMy');
            $sign = true;
            if($check_objects){
                foreach($check_objects as $check_object){
                    $check_object->check_goods_isMy($v['params']['goods_id'],$msg,$sign);
                }
                if(!$sign){
                    //$this->end(false,'商品数据异常');
                    $this->splash('failed', $this->gen_url( array('app'=>'b2c','act'=>'index','ctl'=>'site_product','arg1'=>$aCart['object']['goods'][0]['params']['goods_id']) ) , app::get('b2c')->_('不能购买自己的商品'));
                }
            }
        }
        //end 

        $this->_item_to_disabled( $aCart,$flag ); //处理购物扯删除项
        $this->store_total($aCart);
        $this->store_coupons($aCart);
        $this->pagedata['aCart'] = $aCart;
        $this->pagedata['isfastbuy'] = 1;
        if( $this->show_gotocart_button ) $this->pagedata['show_gotocart_button'] = 'true';

        if( $this->ajax_update === true ) {
            foreach(kernel::servicelist('b2c_cart_object_apps') as $object) {
                if( !is_object($object) ) continue;

                //应该判断是否实现了接口
                if( !method_exists( $object,'get_update_num' ) ) continue;
                if( !method_exists( $object,'get_type' ) ) continue;

                $this->pagedata['edit_ajax_data'] = $object->get_update_num( $aCart['object'][$object->get_type()],$this->update_obj_ident );
                if( $this->pagedata['edit_ajax_data'] ) {
                    $this->pagedata['edit_ajax_data'] = json_encode( $this->pagedata['edit_ajax_data'] );
                    if( $object->get_type()=='goods' ) {
                        $this->pagedata['update_cart_type_godos'] = true;
                        if( !method_exists( $object,'get_error_html' ) ) continue;
                        $this->pagedata['error_msg'] = $object->get_error_html( $aCart['object']['goods'],$this->update_obj_ident );
                    }
                    break;
                }
            }
        }



        // 购物车是否为空
        $this->pagedata['is_empty'] = $this->mCart->is_empty($aCart);
        //ajax_html 删除单个商品是触发
        if($this->ajax_html && $this->mCart->is_empty($aCart)) {
            $arr_json_data = array(
                'is_empty' => 'true',
                'number'=>array(
                    'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                    'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                ),
            );
            $this->pagedata = $arr_json_data;
            $this->page('site/cart/cart_empty.html', true);
            return ;
        }

        // 购物车数据项的render
        $this->pagedata['item_section'] = $this->mCart->get_group_item_render();

        // 购物车数据项的render
        $this->pagedata['item_goods_section'] = $this->mCart->get_item_goods_render();

        // 优惠信息项render
        $this->pagedata['solution_section'] = $this->mCart->get_solution_render();

        //未享受的订单规则
        $this->pagedata['unuse_rule'] = $this->mCart->get_unuse_solution_cart($aCart);


        if( $this->member_status ) {
            /*
            $arr_member = $this->get_current_member();
            $aData = $this->app->model('member_goods')->get_favorite( $arr_member['member_id'], 0, 100);
            $objProduct = $this->app->model('products');
            $oGoodsLv = &$this->app->model('goods_lv_price');
            $oMlv = &$this->app->model('member_lv');
            $mlv = $oMlv->db_dump( $this->member['member_lv'],'dis_count' );

            $aProduct = $aData['data'];
            if($aProduct){
                foreach ($aProduct as $key => &$val) {
                    $temp = $objProduct->getList('product_id, spec_info, price, freez, store, goods_id',array('goods_id'=>$val['goods_id'],'goods_type'=>array('normal','gift')));
                    if( $arr_member['member_lv'] ){
                        $tmpGoods = array();
                        foreach( $oGoodsLv->getList( 'product_id,price',array('goods_id'=>$val['goods_id'],'level_id'=> $arr_member['member_lv'] ) ) as $k => $v ){
                            $tmpGoods[$v['product_id']] = $v['price'];
                        }
                        foreach( $temp as &$tv ){
                            $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$tv['price'] ));
                        }
                        $val['price'] = $tv['price'];
                    }
                    $val['spec_desc_info'] = $temp;
                }
            }

            $this->pagedata['member_goods'] = $aProduct;
            #$this->pagination($nPage,$aData['page'],'favorite');
            $imageDefault = app::get('image')->getConf('image.set');
            $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
            $setting['buytarget'] = $this->app->getConf('site.buy.target');
            $this->pagedata['setting'] = $setting;
            */
        } else {
            $this->pagedata['login'] = 'nologin';
        }

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
    }

     private function _item_to_disabled( &$aCart,$flag ) {

        foreach( kernel::servicelist('b2c_cart_object_apps') as $object ) {
            if( !is_object($object) ) continue;
            $o[$object->get_type()] = $object;
        }

        $arr_cart_disabled_session = $_SESSION['cart_objects_disabled_item'];
        foreach( (array)$aCart['object'] as $_obj_type => $_arr_by_obj_type ) {
            $tmp = $arr_cart_disabled_session[$_obj_type];
            if( isset($arr_cart_disabled_session[$_obj_type]) ) {
                if( !$o[$_obj_type] ) continue;
                if( !method_exists( $o[$_obj_type],'apply_to_disabled' ) ) continue;
                $aCart['object'][$_obj_type] = $o[$_obj_type]->apply_to_disabled( $_arr_by_obj_type, $tmp, $flag );
                $_SESSION['cart_objects_disabled_item'][$_obj_type] = $tmp;
            } else {
                if( $flag )
                    unset($_SESSION['cart_objects_disabled_item'][$obj_type]);
            }
        }
    }

	public function loginBuy($isfastbuy=0)
    {
        $this->pagedata['guest_enabled'] = $this->app->getConf('security.guest.enabled');
        #if (!$isfastbuy)
        #    $this->__tmpl = 'site/cart/loginBuy.html';
        #else
            $this->__tmpl = 'site/cart/loginbuy_fast.html';
        if( $this->check_login() ) {
            $this->begin( $this->gen_url( array('app'=>'b2c','act'=>'index','ctl'=>'site_cart') ) );
            $this->end( true,'您已经是登录状态！');
        }
        $this->pagedata['no_right'] = 1;
        if(!isset($_SESSION['next_page']))
            $_SESSION['next_page'] = $this->gen_url(array('app'=>'groupbuy','ctl'=>'site_cart','act'=>'checkout'));

        $this->pagedata['toUrl'] = $_SESSION['next_page'];
        $oAP = $this->app->controller('site_passport');
        $oAP->gen_login_form(1);
        $this->pagedata['isfastbuy'] = $isfastbuy;
        $this->pagedata['base_path'] = kernel::base_url();
        foreach(kernel::servicelist('openid_imageurl') as $object)
        {
            if(is_object($object))
            {
                if(method_exists($object,'get_image_url'))
                {
                    $this->pagedata['login_image_url'][] = $object->get_image_url();
                }
            }
        }
        $this->set_tmpl('login');
        if ($isfastbuy)
            $this->page($this->__tmpl);
        else
            $this->page($this->__tmpl, true);
    }


}