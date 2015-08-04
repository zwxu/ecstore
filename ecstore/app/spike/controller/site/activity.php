<?php

class spike_ctl_site_activity extends b2c_frontpage{

    public function __construct(&$app){
        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('会员中心').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('会员中心_').'_'.$shopname;
            $this->description = app::get('b2c')->_('会员中心_').'_'.$shopname;
        }
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'get_goods_spec') );
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->verify_member();
        $this->pagesize = 10;
        $this->action = $this->_request->get_act_name();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        $this->load_info();
    }
    
    function verify_member(){
        kernel::single('base_session')->start();
        $this->member_id = $_SESSION['account'][pam_account::get_account_type('b2c')];
        if(app::get('b2c')->member_id = $_SESSION['account'][pam_account::get_account_type(app::get('b2c')->app_id)]){
            $obj_member = app::get('b2c')->model('members');
            $data = $obj_member->dump(array('member_id'=>app::get('b2c')->member_id),'member_id,seller');
            if($data){
                //登陆受限检测
                $res = $this->loginlimit(app::get('b2c')->member_id,$redirect);
                if($res){
                    $this->redirect($redirect);
                }else{
                    if($data['seller'] == 'seller'){
                        return true;
                    }else{
                        $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error'));
                    }
                }
            }else{
                $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error'));
            }
        }else{
            $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error'));
        }

    }

     function getMember(){
        if($this->member_id){
            echo json_encode(array('status'=>'true'));
        }else{
            echo json_encode(array('status'=>'false'));
            
        }
    }

     protected function output($app_id='spike'){
        $this->pagedata['member'] = $this->member;
        $this->pagedata['cpmenu'] = $this->get_cpmenu();
        $this->pagedata['top_menu'] = $this->get_headmenu();
        $this->pagedata['current'] = $this->action;
        if( $this->pagedata['_PAGE_'] ){
            $this->pagedata['_PAGE_'] = 'site/member/'.$this->pagedata['_PAGE_'];
        }else{
           $this->pagedata['_PAGE_'] = 'site/member/'.$this->action_view;
        }
        foreach(kernel::servicelist('member_index') as $service){
            if(is_object($service)){
                if(method_exists($service,'get_member_html')){
                    $aData[] = $service->get_member_html();
                }
            }
        }
        $this->pagedata['app_id'] = $app_id;
        $this->pagedata['_MAIN_'] = 'site/member/main.html';
        $this->pagedata['get_member_html'] = $aData;
        $member_goods = app::get('b2c')->model('member_goods');
        $this->pagedata['sto_goods_num'] = $member_goods->get_goods($this->app->member_id);
        $this->set_tmpl('member');
        $this->page('site/member/main.html');
    }
    
    /*
     * 显示平台发起的活动
     * 
     */
    public function attend(){
        $store_region = $this->member['store_info']['store_region'];
        $store_region = array_filter(explode(',',$store_region));
        $store_cat = $this->member['store_info']['store_cat'];
        $store_grade = $this->member['store_info']['store_grade'];
        $oActivity = $this->app->model('activity');
        $nowTime = time();
        $filter = array(
                    'act_open'=>'true',
                    'apply_start_time|sthan'=>$nowTime,
                    'apply_end_time|bthan'=>$nowTime
                );
        $activityInfo = $oActivity->getList('*',$filter);
        $now = time();
        foreach($activityInfo as $k=>$v){
            if($now > $v['end_time']){
                unset($activityInfo[$k]);
            }
        }
        foreach($activityInfo as $k=>$v){
            if($store_region){
                $businee_type = array();
                $businee_type =array_filter(explode(',',$v['business_type']));
                $ret = array_intersect($businee_type,$store_region);
                if(!$ret){
                    unset($activityInfo[$k]);
                }
            }

            $act_store_cat = explode(',',$v['store_type']);
            if(!in_array($store_cat,$act_store_cat)){
                unset($activityInfo[$k]);
            }

            $act_store_grade = array_filter(explode(',',$v['store_lv']));
            if(!in_array($store_grade,$act_store_grade)){
                unset($activityInfo[$k]);
            }
        }
        foreach($activityInfo as $key=>$value){
            $activityInfo[$key]['start_time'] = date('Y-m-d',$value['start_time']);
            $activityInfo[$key]['end_time'] = date('Y-m-d',$value['end_time']);
        }

        //加载活动tab start 
        $business_activity_cat = kernel::service('business_activity_cat');
        if($business_activity_cat){
            $activityTab = $business_activity_cat->loadActivityCat();
            $this->pagedata['activity_tab'] = $activityTab;
            $this->pagedata['activity_tab_cur'] = 'spike';
        }
        //加载活动tab end 

        $this->pagedata['activity'] = $activityInfo;
        $this->pagedata['_PAGE_'] = 'activity.html';
        $this->output();
        
    }
    /*
     * 申请参加活动 
     * 
     */
   public function toAttend($id){
        $url = $this->gen_url( array('app'=>'spike','ctl'=>'site_activity','act'=>'attend') );
        $this->begin($url);
        $oActivity = $this->app->model('activity');
        $nowTime = time();
        $filter = array(
                    'act_id'=>intval($id),
                    'act_open'=>'true',
                    'apply_start_time|sthan'=>$nowTime,
                    'apply_end_time|bthan'=>$nowTime
                );
        $activityInfo = $oActivity->dump($filter,'*');

        if(!$this->checkAttendActivity()){
            $this->end(false,'由于您没有及时发货暂停申请团购活动！');
        }

        if(!$activityInfo || empty($activityInfo)){
            $this->end(false,'没有您要参加的活动！');
        }
        //店铺经营范围
        $store_region = $this->member['store_info']['store_region'];
        $store_region = array_filter(explode(',',$store_region));
        //活动的经营范围
        $businee_type =array_filter(explode(',',$activityInfo['business_type']));
        $ret = array_intersect($businee_type,$store_region);

        if(!$ret || empty($ret)){
            $this->end(false,'您不能参加这个活动！');
        }

        //店铺分类，店铺等级判断
        $store_cat = $this->member['store_info']['store_cat'];
        $store_grade = $this->member['store_info']['store_grade'];
        $act_store_cat = explode(',',$activityInfo['store_type']);
        if(!in_array($store_cat,$act_store_cat)){
            $this->end(false,'您不能参加这个活动！');
        }

        $act_store_grade = array_filter(explode(',',$activityInfo['store_lv']));
        if(!in_array($store_grade,$act_store_grade)){
            $this->end(false,'您不能参加这个活动！');
        }

        //判断是否在申请时间内
        $activityInfo['start_time'] = date('Y-m-d',$activityInfo['start_time']);
        $activityInfo['end_time'] = date('Y-m-d',$activityInfo['end_time']);
        $this->pagedata['actInfo'] = $activityInfo;

        $this->pagedata['_PAGE_'] = 'attend.html';
        //begin 获取店铺
        $member_id = $this->member_id;
        $storemember = app::get('business')->model('storemember');
        $storemanger = app::get('business')->model('storemanger');
        
        $store_id = $storemember->getList('store_id',array('member_id'=>$member_id));
        if(!$store_id){
            $store_id = $storemanger->getList('store_id',array('account_id'=>$member_id));
        }
        $filter = array(
            'act_type'=>'normal',
            'marketable'=>'true',
            'store_id'=>$store_id[0]['store_id'],
            'cat_id'=>$ret
        );
        $this->pagedata['store_id'] = $store_id[0]['store_id'];
        //end 2013/6/6 
        $this->pagedata['filter'] = $filter;
        $this->pagedata['return_url'] = $this->gen_url( array('app'=>'spike','ctl'=>'site_activity','act'=>'get_goods_info') );
        $this->pagedata['submit_url'] = $this->gen_url( array('app'=>'spike','ctl'=>'site_activity','act'=>'saveToAttend') );
        $this->output();
       //$oActivity
   }
    /*
     * 参加活动的申请 
     * 
     */
   public function myAttend(){
        $businessActivity = $this->app->model('spikeapply');
        $aGood = app::get('b2c')->model('goods');
        $activity = $this->app->model('activity');
        $member = $this->member;
        //begin
        $member_id = $this->member_id;
        $storemember = app::get('business')->model('storemember');
        $storemanger = app::get('business')->model('storemanger');

        $store_id = $storemember->getList('store_id',array('member_id'=>$member_id));
        if(!$store_id){
            $store_id = $storemanger->getList('store_id',array('account_id'=>$member_id));
        }
        //end add 2013/6/6
        $busineeActivityInfo = $businessActivity->getList('*',array('store_id'=>$store_id[0]['store_id']));
        foreach($busineeActivityInfo as $k=>$v){
            $goods = $aGood->getList('name,bn,price',array('goods_id'=>$v['gid']));
            $busineeActivityInfo[$k]['good'] = $goods[0];
            $actInfo = $activity->getList('*',array('act_id'=>$v['aid']));
            $busineeActivityInfo[$k]['actInfo'] = $actInfo[0];
            if($v['status'] == '2'){
                $args = array($v['gid'],'','',$v['id']);
                $busineeActivityInfo[$k]['goodsLink'] = $this->gen_url( array('app'=>'spike','ctl'=>'site_product','act'=>'index','args'=>$args) );
            }else{
                $busineeActivityInfo[$k]['goodsLink'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$v['gid']));
            }
        }

        //加载活动申请tab start 
        $business_activity_cat = kernel::service('business_activity_apply_tag');
        if($business_activity_cat){
            $activityTab = $business_activity_cat->loadActivityApplyTag();
            $this->pagedata['activity_tab'] = $activityTab;
            $this->pagedata['activity_tab_cur'] = 'spike';
        }
        //加载活动申请tab end 
        $this->pagedata['busiAct'] = $busineeActivityInfo;
        $this->pagedata['_PAGE_'] = 'businessAct.html';
        $this->output();
   }
    /*
     * 编辑活动申请 
     * 
     */
   public function editAttend($id){
        $url = $this->gen_url( array('app'=>'spike','ctl'=>'site_activity','act'=>'attend') );
        $this->begin($url);
        $businessActivity = $this->app->model('spikeapply');
        $nowTime = time();
        $filter = array('id'=>$id);

        $member_id = $this->member_id;
        //添加过滤条件
        $busineeActivityInfo = $businessActivity->dump($filter,'*');
        $sto= kernel::single("business_memberstore",$member_id);        
        $store_id = $sto->storeinfo['store_id'];
        $busineeActivityInfo = $businessActivity->dump($filter,'*');
        if($store_id != $busineeActivityInfo['store_id']){
            $this->end(false,'您没有权限编辑该活动！');
        }

        if(!$busineeActivityInfo || empty($busineeActivityInfo) || $store_id != $busineeActivityInfo['store_id'] || $busineeActivityInfo['status'] != 3){
            $this->end(false,'没有您可编辑的活动！');
        }
        $this->pagedata['businessAct'] = $busineeActivityInfo;//end 申请信息

        $oActivity = $this->app->model('activity');
        $activityInfo = $oActivity->dump(array('act_id'=>$busineeActivityInfo['aid']),'*');
        $activityInfo['start_time'] = date('Y-m-d',$activityInfo['start_time']);
        $activityInfo['end_time'] = date('Y-m-d',$activityInfo['end_time']);
        $this->pagedata['actInfo'] = $activityInfo;//end 活动信息
    
        $goods = app::get('b2c')->model('goods')->getList('price,store',array('goods_id'=>$busineeActivityInfo['gid']));
        $goods = $goods[0];
        $goods['store'] = intval($goods['store']);
        $mdl_products = app::get('b2c')->model('products');
        $freez = $mdl_products->getList('sum(freez) as freez',array('goods_id'=>$busineeActivityInfo['gid']));
        $goods['store'] = $goods['store']-$freez['0']['freez'];
        $this->pagedata['goods'] = $goods;
        //end 商品信息

        //店铺经营范围
        $store_region = $this->member['store_info']['store_region'];
        $store_region = array_filter(explode(',',$store_region));
        //活动的经营范围
        $businee_type =array_filter(explode(',',$activityInfo['business_type']));
        $ret = array_intersect($businee_type,$store_region);

        if(!$ret || empty($ret)){
            $this->end(false,'您不能参加这个活动！');
        }
        $filter = array(
            'act_type'=>'normal',
            'marketable'=>'true',
            'store_id'=>$store_id,
            'cat_id'=>$ret
        );

        $goods_ids = app::get('b2c')->model('goods')->getList('goods_id',$filter);

        $ids_arr = array();
        if($goods_ids){
            foreach($goods_ids as $k=>$v){
                $ids_arr[] = $v['goods_id'];
            }
        }
        $ids_arr[] = $busineeActivityInfo['gid'];
        $this->pagedata['filter'] = array('goods_id'=>$ids_arr);

        $this->pagedata['_PAGE_'] = 'attend.html';
        $this->pagedata['return_url'] = $this->gen_url( array('app'=>'spike','ctl'=>'site_activity','act'=>'get_goods_info') );
        $this->pagedata['submit_url'] = $this->gen_url( array('app'=>'spike','ctl'=>'site_activity','act'=>'saveToAttend') );
        $this->output();
   }

    /*
     * 退出活动 
     * 
     */
   public function quitActivity($id){
        $id = intval($id);
        $businessactivity = app::get('spike')->model('spikeapply');
        $url = $this->gen_url( array('app'=>'spike','ctl'=>'site_activity','act'=>'myAttend') );
        $this->begin($url);
        
        if(!$id){
            $this->end(false,'请选择一个申请！');
        }
        if(!$this->isStoreActivity($id)){
            $this->end(false,'退出失败！');
        }

        $business = $businessactivity->dump(array('id'=>$id),'gid,status');

        if($business['status'] == 2){
            $this->end(false,'审核通过的活动无法退出！');
        }

        $re = $businessactivity->delete(array('id'=>$id));
        if($re){
            $oGoods = app::get('b2c')->model('goods');
            $goods = array('act_type'=>'normal');
            if($business['gid']){
                $garr = $oGoods->dump(array('goods_id'=>$business['gid']),'act_type,goods_id');
                if($garr['act_type'] == 'spike'){
                    $rs = $oGoods->update($goods,array('goods_id'=>$business['gid']));
                }
            }
            $this->end(true,'退出成功！');
        }
        $this->end(false,'退出失败！');
   }
     /*
     * 保存申请信息 
     * 
     */
     public function saveToAttend(){
        $this->begin();
        $url = $this->gen_url( array('app'=>'spike','ctl'=>'site_activity','act'=>'myAttend') );
        $member = $this->member;
        $data = $this->_request->get_post();

        $data['member_id'] = $member['member_id'];
        $data['store_id'] = $member['store_id'];

        if(!$member['member_id']){
           $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error'));
        }

        if(!$this->checkAttendActivity()){
            $this->end(false,'由于您没有及时发货暂停申请团购活动！');
        }

        if(!$data['gid']){
           $this->end(false,'请选择一件商品',null,false,true);
        }
        $oGoods = app::get('b2c')->model('goods');
        $goods_arr = $oGoods->dump(array('goods_id'=>$data['gid']),'act_type,goods_id,cat_id,price,store');
        $data['cat_id'] = $goods_arr['category']['cat_id'];
        $mdl_products = app::get('b2c')->model('products');
        $freez = $mdl_products->getList('sum(freez) as freez',array('goods_id'=>$data['gid']));
        $goods_arr['store'] = $goods_arr['store']-$freez['0']['freez'];
        

        if($data['nums']&&$data['nums']>$goods_arr['store']){
           $this->end(false,'参加活动的商品数量不能大于库存',null,false,true);
        }

        if($data['aid']){
            $actObj = app::get('spike')->model('activity');
            $nowTime = time();
            $filter = array(
                    'act_id'=>intval($data['aid']),
                    'act_open'=>'true',
                    'apply_start_time|sthan'=>$nowTime,
                    'apply_end_time|bthan'=>$nowTime
                );
            $act = $actObj->dump($filter,'act_id');
            if(!$act){
                $this->end(false,'活动不存在',null,false,true);
            }
            //if($act['nums'] > 0 && $act['nums'] !='' && $data['nums'] != ''){
                //if($data['nums'] < $act['nums']){
                    //$this->end(false,'参加活动数量不能小于平台设置最低库存',null,false,true);
                //}
            //}

            //if($goods_arr['store'] < $act['nums']){
                //$this->end(false,'商品库存不能小于平台设置最低库存',null,false,true);
            //}
        }else{
            $this->end(false,'活动不存在',null,false,true);
        }

        if($data['nums'] !='' && $data['personlimit'] != ''){
            if($data['personlimit'] > $data['nums']){
                $this->end(false,'每人限购数量不能大于参加活动的商品数量',null,false,true);
            }
        }

        if($data['price'] >= $goods_arr['price']){
            $this->end(false,'活动价格不得高于或等于原价',null,false,true);
        }

        //编辑时判断是否修改商品
        $applyObj = app::get('spike')->model('spikeapply');
        if(isset($data['id'])){
            $apply = $applyObj->dump(array('id'=>$data['id'],'status'=>'3'),'id,gid');
            if($apply){
                if($apply['gid'] != $data['gid']){
                    $gArr = $oGoods->dump(array('goods_id'=>$data['gid']),'act_type,goods_id,cat_id,price,store');
                    if($gArr['act_type'] !='normal'){
                        $this->end(false,'该商品已参加过该活动或其他活动！',null,false,true);
                    }else{
                        $goodsOld = array();
                        $goodsOld['act_type'] = 'normal';
                        $goodsOld['store_freeze']= 0;
                        $oGoods->update($goodsOld,array('goods_id'=>$apply['gid']));
                    }
                }
            }else{
                $this->end(false,'没有你要修改的活动',null,false,true);
            }
        }else{
            if($goods_arr['act_type'] !='normal'){
                $this->end(false,'该商品已参加过该活动或其他活动！',null,false,true);
            }
            $apply = $applyObj->dump(array('aid'=>$data['aid'],'gid'=>$data['gid']),'id');
            if($apply){
                $this->end(false,'已经存在该活动',null,false,true);
            }
        }

        $object = kernel::single('spike_business_activity');
        $rs = $object->addBusinessActivity($data);
        if($rs){
           $goods['act_type'] = 'spike';
           $oGoods->update($goods,array('goods_id'=>$data['gid']));
           $this->end(true,'申请成功,等待审核中',$url,false,true);
        }
        $this->end(false,'申请失败',null,false,true);
    }

    private function get_headmenu() {
        /**
         * 会员中心的头部连接
         */
        $arr_main_top = array('member_center' => array('label' => app :: get('b2c') -> _('会员首页'),
                'app' => 'b2c',
                'ctl' => 'site_member',
                'link' => 'index',
                'args' => array(),
                ),
            'logout' => array('label' => app :: get('b2c') -> _('退出'),
                'app' => 'b2c',
                'ctl' => 'site_passport',
                'link' => 'logout',
                'args' => array(),
                ),
            'orders_nopayed' => array('label' => app :: get('b2c') -> _('待付款订单'),
                'app' => 'b2c',
                'ctl' => 'site_member',
                'link' => 'orders',
                'args' => array('nopayed'),
                ),
            'member_notify' => array('label' => app :: get('b2c') -> _('到货通知'),
                'app' => 'b2c',
                'ctl' => 'site_member',
                'link' => 'notify',
                'args' => array(),
                ),
            'member_comment' => array('label' => app :: get('b2c') -> _('到货通知'),
                'app' => 'b2c',
                'ctl' => 'site_member',
                'link' => 'comment',
                'args' => array(),
                ),
            );

        $obj_menu_extends = kernel :: servicelist('b2c.member_menu_extends');
        if ($obj_menu_extends) {
            foreach ($obj_menu_extends as $obj) {
                if (method_exists($obj, 'get_extends_top_menu'))
                    $obj -> get_extends_top_menu($arr_main_top, array('0' => 'b2c', '1' => 'site_member', '2' => 'index'));
            } 
        } 
        return $arr_main_top;
    } 

	private function get_cpmenu(){
        // 判断是否开启预存款
        $mdl_payment_cfgs = app::get('ectools')->model('payment_cfgs');
        $payment_info = $mdl_payment_cfgs->getPaymentInfo('deposit');
        $arr_blance = array();
        $arr_recharge_blance = array();
        $arr_point_history = array();
        $arr_point_coupon_exchange = array();
        $this->pagedata['point_usaged'] = "false";

        if ($payment_info['app_staus'] == app::get('ectools')->_('开启'))
        {
            $arr_blance = array('label'=>app::get('b2c')->_('我的预存款'),'app'=>'b2c','ctl'=>'site_member','link'=>'balance');
            $arr_recharge_blance = array('label'=>app::get('b2c')->_('预存款充值'),'app'=>'b2c','ctl'=>'site_member','link'=>'deposit');
        }

        $site_get_policy_method = $this->app->getConf('site.get_policy.method');
        if ($site_get_policy_method != '1')
        {
            $arr_point_history = array('label'=>app::get('b2c')->_('我的积分'),'app'=>'b2c','ctl'=>'site_member','link'=>'my_point');
            $arr_point_coupon_exchange = array('label'=>app::get('b2c')->_('积分兑换优惠券'),'app'=>'b2c','ctl'=>'site_member','link'=>'couponExchange');
            $this->pagedata['point_usaged'] = "true";
        }

        $arr_bases = array(
            array('label'=>app::get('b2c')->_('我是买家'),
            'mid'=>0,
            'items'=>array(
                        array('label'=>app::get('b2c')->_('我的订单'),'app'=>'b2c','ctl'=>'site_member','link'=>'orders'),
                        $arr_point_history,
                        $arr_point_coupon_exchange,
                        array('label'=>app::get('b2c')->_('我的优惠券'),'app'=>'b2c','ctl'=>'site_member','link'=>'coupon'),
                        $arr_blance,
                        $arr_recharge_blance,
                        array('label'=>app::get('b2c')->_('到货通知'),'app'=>'b2c','ctl'=>'site_member','link'=>'notify'),
                        array('label'=>app::get('b2c')->_('我的咨询'),'app'=>'b2c','ctl'=>'site_member','link'=>'ask'),
                        array('label'=>app::get('b2c')->_('我的评论'),'app'=>'business','ctl'=>'site_comment','link'=>'selfdiscuss'),
                        array('label'=>app::get('b2c')->_('最近购买的商品'),'app'=>'b2c','ctl'=>'site_member','link'=>'buy'),
                        array('label'=>app::get('b2c')->_('个人信息'),'app'=>'b2c','ctl'=>'site_member','link'=>'setting'),
                        array('label'=>app::get('b2c')->_('修改密码'),'app'=>'b2c','ctl'=>'site_member','link'=>'security'),
                        array('label'=>app::get('b2c')->_('收货地址'),'app'=>'b2c','ctl'=>'site_member','link'=>'receiver'),
            )
        ),
        );

        $obj_menu_extends = kernel::servicelist('business.member_menu_extends');
        if ($obj_menu_extends)
        {
            foreach ($obj_menu_extends as $obj)
            {
                if (method_exists($obj, 'get_extends_menu'))
                    $obj->get_extends_menu($arr_bases, array('0'=>'business', '1'=>'site_member', '2'=>'index'));
            }
        }

        return $arr_bases;
    }

    private function load_info(){
       #获取会员基本信息
        $obj_member = app::get('b2c')->model('members');
        $obj_pam_account = app::get('pam')->model('account');
        $member_id = app::get('b2c')->member_id;
        $member_info = $obj_member->getList('*',array('member_id'=>$member_id));
        $pam_account = $obj_pam_account->getList('*',array('account_id'=>$member_id));
        //$member_sdf = $obj_member->dump($this->app->member_id,"*",array(':account@pam'=>array('*')));
        if (!$member_info||!$pam_account) return;

        /** 重新组合sdf **/
        $member_info[0]['birthday'] = $member_info[0]['b_year'].'-'.$member_info[0]['b_month'].'-'.$member_info[0]['b_day'];
        $member_sdf = array(
            'pam_account'=>array(
                'account_id'=>$pam_account[0]['account_id'],
                'account_type'=>$pam_account[0]['account_type'],
                'login_name'=>$pam_account[0]['login_name'],
                'login_password'=>$pam_account[0]['login_password'],
                'disabled'=>$pam_account[0]['disabled'],
                'createtime'=>$pam_account[0]['createtime'],
            ),
            'member_lv'=>array(
                'member_group_id'=>$member_info[0]['member_lv_id'],
            ),
            'contact'=>array(
                'name' => $member_info[0]['name'],
                'lastname' => $member_info[0]['lastname'],
                'firstname' => $member_info[0]['firstname'],
                'area' => $member_info[0]['area'],
                'addr' => $member_info[0]['addr'],
                'phone' =>
                array (
                  'mobile' => $member_info[0]['mobile'],
                  'telephone' => $member_info[0]['tel'],
                ),
                'email' => $member_info[0]['email'],
                'zipcode' => $member_info[0]['zip'],
            ),
            'score'=>array(
                'total'=>$member_info[0]['point'],
                'freeze'=>$member_info[0]['point_freeze'],
            ),
            'order_num'=>$member_info[0]['order_num'],
            'refer_id'=>$member_info[0]['refer_id'],
            'refer_url'=>$member_info[0]['refer_url'],
            'b_year'=>$member_info[0]['b_year'],
            'b_month'=>$member_info[0]['b_month'],
            'b_day'=>$member_info[0]['b_day'],
            'profile'=>array(
                'gender'=>$member_info[0]['sex'],
                'birthday'=>$member_info[0]['birthday'],
            ),
            'addon'=>$member_info[0]['addon'],
            'wedlock'=>$member_info[0]['wedlock'],
            'education'=>$member_info[0]['education'],
            'vocation'=>$member_info[0]['vocation'],
            'interest'=>$member_info[0]['interest'],
            'advance'=>array(
                'total'=>$member_info[0]['advance'],
                'freeze'=>$member_info[0]['advance_freeze'],
            ),
            'point_history'=>$member_info[0]['point_history'],
            'score_rate'=>$member_info[0]['score_rate'],
            'reg_ip'=>$member_info[0]['reg_ip'],
            'vocation'=>$member_info[0]['vocation'],
            'regtime'=>$member_info[0]['regtime'],
            'state'=>$member_info[0]['state'],
            'vocation'=>$member_info[0]['vocation'],
            'pay_time'=>$member_info[0]['pay_time'],
            'biz_money'=>$member_info[0]['biz_money'],
            'fav_tags'=>$member_info[0]['fav_tags'],
            'custom'=>$member_info[0]['custom'],
            'currency'=>$member_info[0]['cur'],
            'vocation'=>$member_info[0]['vocation'],
            'lang'=>$member_info[0]['lang'],
            'unreadmsg'=>$member_info[0]['unreadmsg'],
            'disabled'=>$member_info[0]['disabled'],
            'remark'=>$member_info[0]['remark'],
            'vocation'=>$member_info[0]['vocation'],
            'remark_type'=>$member_info[0]['remark_type'],
            'login_count'=>$member_info[0]['login_count'],
            'experience'=>$member_info[0]['experience'],
            'foreign_id'=>$member_info[0]['foreign_id'],
            'member_refer'=>$member_info[0]['member_refer'],
            'source'=>$member_info[0]['source'],
        );

        /** 访问member相关的meta **/
        $member_meta = dbeav_meta::get_meta_column($obj_member->table_name(1));
        foreach ((array)$member_meta['metaColumn'] as $meta_column){
            $obj_meta_value = new dbeav_meta($obj_member->table_name(1),$meta_column);
            $arr_meta_value = $obj_meta_value->value->db->select('SELECT * FROM '.$obj_meta_value->value->table.' WHERE `mr_id`='.$obj_meta_value->mr_id.' AND `pk`='.$this->app->member_id);
            if ($arr_meta_value)
                $member_sdf['contact'][$meta_column] = $arr_meta_value[0]['value'];
            else
                $member_sdf['contact'][$meta_column] = '';
        }

        $service = kernel::service('pam_account_login_name');
        if(is_object($service)){
            if(method_exists($service,'get_login_name')){
                $member_sdf['pam_account']['login_name'] = $service->get_login_name($member_sdf['pam_account']);
            }
        }
        $this->member['member_id'] = $member_sdf['pam_account']['account_id'];
        $this->member['uname'] =  $member_sdf['pam_account']['login_name'];
        $this->member['name'] = $member_sdf['contact']['name'];
        $this->member['sex'] =  $member_sdf['profile']['gender'];
        $this->member['point'] = $member_sdf['score']['total'];
        $this->member['usage_point'] = $this->member['point'];
        $obj_extend_point = kernel::service('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            // 当前会员拥有的积分
            $obj_extend_point->get_real_point($this->member['member_id'], $this->member['point']);
            // 当前会员实际可以使用的积分
            $obj_extend_point->get_usage_point($this->member['member_id'], $this->member['usage_point']);
        }
        $this->member['experience'] = $member_sdf['experience'];
        $this->member['email'] = $member_sdf['contact']['email'];
        $this->member['member_lv'] = $member_sdf['member_lv']['member_group_id'];
        $this->member['advance'] = $member_sdf['advance'];

        #获取会员等级
        $obj_mem_lv = app::get('b2c')->model('member_lv');
        $levels = $obj_mem_lv->getList('name,disabled',array('member_lv_id'=>$member_sdf['member_lv']['member_group_id']));
        //$levels = $obj_mem_lv->dump($member_sdf['member_lv']['member_group_id']);
        if($levels[0]['disabled']=='false'){
            $this->member['levelname'] = $levels[0]['name'];
        }
        #获取待付款订单数
        $orders = app::get('b2c')->model('orders');
        $un_pay_orders = $orders->getList('order_id',array('member_id' => $this->member['member_id'],'pay_status' => 0,'status'=>'active'));
        $this->member['un_pay_orders'] = count($un_pay_orders);
        #获取回复信息
        $mem_msg = app::get('b2c')->model('member_comments');
        $object_type = array('msg','discuss','ask');
        $aData = $mem_msg->getList('*',array('to_id' => $this->member['member_id'],'for_comment_id' => 'all','object_type'=> $object_type,'has_sent' => 'true','inbox' => 'true','mem_read_status' => 'false','display' => 'true'));
        unset($mem_msg);
        $this->member['un_readmsg'] = count($aData);
        
        //store
        $storemember = app::get('business')->model('storemember');
        $storemanger = app::get('business')->model('storemanger');

        $sto= kernel::single("business_memberstore",$member_id);

        if($sto->isshoper == 'true'){
            $store_id = $sto->storeinfo['store_id'];
        }
        if($sto->isshopmember == 'true'){
            $store_id = $sto->storeinfo[0]['store_id'];
        }
        $this->member['store_id'] = $store_id;
        $store_info = $storemanger->dump(array('store_id'=>$store_id),'shop_name,store_region,store_grade');
        $store_info['store_cat'] = $sto->storeinfo['issue_type'];
        $this->member['store_info'] = $store_info;

    }

   /*
     * return goods info
     */
    public function get_goods_info()
    {
        $data = $_POST['data'];
        $mdl_products = app::get('b2c')->model('products');
        $freez = $mdl_products->getList('sum(freez) as freez',array('goods_id'=>$data[0]));
        $arr = app::get('b2c')->model('goods')->dump(array('goods_id'=>$data[0]),'name,price,store,goods_id,image_default_id,brief,freight_bear' );
        $arr['store'] = $arr['store'] - $freez['0']['freez'];
        echo json_encode( array('name'=>$arr['name'],'price'=>$arr['price'],'store'=>(INT)$arr['store'],'goods_id'=>$arr['goods_id'],'image'=>$arr['image_default_id'], 'brief'=>$arr['brief'],'freight_bear'=>$arr['freight_bear']) );
    }

    private function isStoreActivity($act_id){
        $applyObj = $this->app->model('spikeapply');
        $activity = $applyObj->dump(array('id'=>intval($act_id)),'store_id');
        if($activity && !empty($activity)){
            if($activity['store_id']== $this->member['store_id']){
                return true;
            }
        }

        return false;
    }

    private function checkAttendActivity(){
        $member_info = $this->member;
        $store_id = $member_info['store_id'];

        $object = app::get('business')->model('activity');
        $nowTime = time();
        $filter = array(
                      'act_type'=>'spike',
                      'store_id'=>$store_id,
                      'start_time|sthan'=>$nowTime,
                      'end_time|bthan'=>$nowTime
                    );
        $rs = $object->getList('*',$filter);
        if(empty($rs)){
            return true;
        }

        return false;
    }

    function detailActivity(){
        if(!isset($_GET['id'])){
        
        }
        $id = intval($_GET['id']);
        $applyObj = app::get('spike')->model('spikeapply');
        $info = $applyObj->loadActInfoById($id);
        if($info){
            $act_open = array('true'=>'开启','false'=>'关闭');
            $status = array('1'=>'未审核','2'=>'审核通过','3'=>'审核未通过');
            $info['start_time'] = date('Y-m-d H:i:s',$info['start_time']);
            $info['end_time'] = date('Y-m-d H:i:s',$info['end_time']);

            $goodsObj = app::get('b2c')->model('goods');
            $goods = $goodsObj->dump(array('goods_id'=>$info['gid']),'name');
            $info['goods_name'] = $goods['name'];
            $info['act_open'] = $act_open[$info['act_open']];
            $info['status'] = $status[$info['status']];
        }

        $this->pagedata['activityInfo'] = $info;
        $this->page('site/member/activityInfo.html',true);
    }

}