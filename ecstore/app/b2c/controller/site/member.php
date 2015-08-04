<?php


class b2c_ctl_site_member extends b2c_frontpage{

    function __construct(&$app,$verify=true){
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
        if($verify === true){
            $this->verify_member();
        }
        $this->pagesize = 10;
        $this->action = $this->_request->get_act_name();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";


       
        
        // 卖家和卖家分开
        $minfo=$this->get_current_member();
        if($minfo['member_id']){
            $is_seller=($minfo['seller']=='seller');
            $thisClass=get_class($this);
            //卖家限制
            if($is_seller && get_class($this)=='b2c_ctl_site_member' && !in_array($this->action,array('checkOldPwd', 'member'))){
                $this->splash('failed', $this->gen_url(array('app'=>'site','ctl'=>'default','act'=>'index')), app::get('b2c')->_('卖家不能进入【会员中心】！'));
            }//买家限制
            if(!$is_seller && get_class($this)=='business_ctl_site_member'){
                $this->splash('failed', $this->gen_url(array('app'=>'site','ctl'=>'default','act'=>'index')), app::get('b2c')->_('买家不能进入【店铺管理】！'));
            }
        }
        //end
        $this->load_info();
        /** end **/
    }

    private function get_headmenu()
    {
        /**
         * 会员中心的头部连接
         */
        $arr_main_top = array(
            'member_center'=>array(
                'label'=>app::get('b2c')->_('会员首页'),
                'app'=>'b2c',
                'ctl'=>'site_member',
                'link'=>'index',
                'args'=>array(),
            ),
            'logout'=>array(
                'label'=>app::get('b2c')->_('退出'),
                'app'=>'b2c',
                'ctl'=>'site_passport',
                'link'=>'logout',
                'args'=>array(),
            ),
            'orders_nopayed'=>array(
                'label'=>app::get('b2c')->_('待付款订单'),
                'app'=>'b2c',
                'ctl'=>'site_member',
                'link'=>'orders',
                'args'=>array('nopayed'),
            ),
            'member_notify'=>array(
                'label'=>app::get('b2c')->_('到货通知'),
                'app'=>'b2c',
                'ctl'=>'site_member',
                'link'=>'notify',
                'args'=>array(),
            ),
            'member_comment'=>array(
                'label'=>app::get('b2c')->_('到货通知'),
                'app'=>'b2c',
                'ctl'=>'site_member',
                'link'=>'comment',
                'args'=>array(),
            ),
        );

        $obj_menu_extends = kernel::servicelist('b2c.member_menu_extends');
        if ($obj_menu_extends)
        {
            foreach ($obj_menu_extends as $obj)
            {
                if (method_exists($obj, 'get_extends_top_menu'))
                    $obj->get_extends_top_menu($arr_main_top, array('0'=>'b2c', '1'=>'site_member', '2'=>'index'));
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
        $arr_return_product = array();
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

        if (app::get('aftersales')->getConf('site.is_open_return_product'))
        {
            $arr_return_product = array('label'=>app::get('b2c')->_('退款退货管理'),'app'=>'aftersales','ctl'=>'site_member','link'=>'return_list');
        }
        $arr_bases = array(
            array('label'=>app::get('b2c')->_('我的收藏夹'),
                'mid'=>0,
                'items'=>array(
                    array('label'=>app::get('b2c')->_('商品收藏'),'app'=>'b2c','ctl'=>'site_member','link'=>'favorite'),
                    array('label'=>app::get('b2c')->_('店铺收藏'),'app'=>'business','ctl'=>'site_favorite','link'=>'sfavorite'),
                )
            ),
            array('label'=>app::get('b2c')->_('我的咨询'),
                'mid'=>0,
                'items'=>array(
                    array('label'=>app::get('b2c')->_('我的评论'),'app'=>'b2c','ctl'=>'site_member','link'=>'comment'),
                    array('label'=>app::get('b2c')->_('我的咨询'),'app'=>'b2c','ctl'=>'site_member','link'=>'ask'),
                    array('label'=>app::get('b2c')->_('浏览历史'),'app'=>'b2c','ctl'=>'site_member','link'=>'view_history'),
                    // array('label'=>app::get('b2c')->_('浏览历史')),
                )
            ),
            array('label'=>app::get('b2c')->_('我的购买'),
                'mid'=>0,
                'items'=>array(
                    array('label'=>app::get('b2c')->_('最近购买的商品'),'app'=>'b2c','ctl'=>'site_member','link'=>'buy'),
                    // array('label'=>app::get('b2c')->_('商品评分'),'app'=>'business','ctl'=>'site_comment','link'=>'selfdiscuss'),
                )
            ),
            array('label'=>app::get('b2c')->_('交易管理'),
                'mid'=>0,
                'items'=>array(
                    array('label'=>app::get('b2c')->_('我的订单'),'app'=>'b2c','ctl'=>'site_member','link'=>'orders'),
                    array('label'=>app::get('b2c')->_('我的优惠券'),'app'=>'b2c','ctl'=>'site_member','link'=>'coupon'),
                    //array('label'=>app::get('b2c')->_('我购买的虚拟物品'),'app'=>'b2c','ctl'=>'site_member','link'=>'my_entity'),
                    //$arr_point_history,
                    //$arr_point_coupon_exchange,
                    $arr_blance,
                    $arr_recharge_blance,
                    $arr_return_product,
                    array('label'=>app::get('b2c')->_('站内信'),'app'=>'business','ctl'=>'site_msg','link'=>'my_msg'),
                    array('label'=>app::get('b2c')->_('邮件订阅'),'app'=>'b2c','ctl'=>'site_member','link'=>'emails'),
                    array('label'=>app::get('b2c')->_('我的购物车'),'app'=>'b2c','ctl'=>'site_cart','link'=>'1'),
                )
            ),
            array('label'=>app::get('b2c')->_('账户中心'),
                'mid'=>0,
                'items'=>array(
		            array('label'=>app::get('b2c')->_('账户信息'),'app'=>'b2c','ctl'=>'site_member','link'=>'setting'),
                    array('label'=>app::get('b2c')->_('修改密码'),'app'=>'b2c','ctl'=>'site_member','link'=>'security'),
                    array('label'=>app::get('b2c')->_('收货地址'),'app'=>'b2c','ctl'=>'site_member','link'=>'receiver'),
				    $arr_point_history,
                    // array('label'=>app::get('b2c')->_('帮助中心')),
                    // array('label'=>app::get('b2c')->_('讨论圈')),
                )
            ),
        );

        $obj_menu_extends = kernel::servicelist('b2c.member_menu_extends');
        if ($obj_menu_extends)
        {
            foreach ($obj_menu_extends as $obj)
            {
                if (method_exists($obj, 'get_extends_menu'))
                    $obj->get_extends_menu($arr_bases, array('0'=>'b2c', '1'=>'site_member', '2'=>'index'));
            }
        }
      
        $oMsg = kernel::single('b2c_message_msg');
        $no_read = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $no_read = count($no_read);
         if($arr_bases){
            foreach($arr_bases as &$v){
                foreach($v['items'] as &$v1){
                    if($v1['link']=='my_msg'){
                        $v1['label'] = app::get('b2c')->_('站内信').'('.$no_read.')';
                    }
                }
            }
        }
        //--end
		
        return $arr_bases;
    }

    protected function output($app_id='b2c'){
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
        $member_goods = $this->app->model('member_goods');
        $this->pagedata['sto_goods_num'] = $member_goods->get_goods($this->app->member_id);
        $this->set_tmpl('member');
        $this->page('site/member/main.html');
    }

	private function load_info(){
       #获取会员基本信息
        $obj_member = &$this->app->model('members');
		$obj_pam_account = app::get('pam')->model('account');
		$member_info = $obj_member->getList('*',array('member_id'=>$this->app->member_id));
		$pam_account = $obj_pam_account->getList('*',array('account_id'=>$this->app->member_id));
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
        $obj_mem_lv = &$this->app->model('member_lv');
		$levels = $obj_mem_lv->getList('name,disabled',array('member_lv_id'=>$member_sdf['member_lv']['member_group_id']));
        //$levels = $obj_mem_lv->dump($member_sdf['member_lv']['member_group_id']);
        if($levels[0]['disabled']=='false'){
            $this->member['levelname'] = $levels[0]['name'];
        }
        #获取待付款订单数
        $orders = $this->app->model('orders');
        $un_pay_orders = $orders->getList('order_id',array('member_id' => $this->member['member_id'],'pay_status' => 0,'status'=>'active'));
        $this->member['un_pay_orders'] = count($un_pay_orders);
        #获取回复信息
        $mem_msg = $this->app->model('member_comments');
        $object_type = array('msg','discuss','ask');
        $aData = $mem_msg->getList('*',array('to_id' => $this->member['member_id'],'for_comment_id' => 'all','object_type'=> $object_type,'has_sent' => 'true','inbox' => 'true','mem_read_status' => 'false','display' => 'true'));
        unset($mem_msg);
        $this->member['un_readmsg'] = count($aData);

    }

    function pagination($current,$totalPage,$act,$arg='',$app_id='b2c',$ctl='site_member'){ //本控制器公共分页函数
        if (!$arg)
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>array(($tmp = time())))),
                'token'=>$tmp,
                );
        else
        {
            $arg = array_merge($arg, array(($tmp = time())));
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>$arg)),
                'token'=>$tmp,
                );
        }
    }

    function get_start($nPage,$count){
        $maxPage = ceil($count / $this->pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start = ($nPage-1) * $this->pagesize;
        $start = $start<0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }

    function setting(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('个人信息'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $member_model = &$this->app->model('members');
        $mem = $member_model->dump($this->app->member_id);
        $cur_model = app::get('ectools')->model('currency');
        $cur = $cur_model->curAll();
        foreach((array)$cur as $item){
           $options[$item['cur_code']] = $item['cur_name'];
        }
        $cur['options'] = $options;
        $cur['value'] = $mem['currency'];
        $this->pagedata['currency'] = $cur;
        $mem_schema = $member_model->_columns();
        $attr =array();
            foreach($this->app->model('member_attr')->getList() as $item){
            if($item['attr_show'] == "true") $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item){
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath){
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1){
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                    foreach($a_temp  as $value){
                        $name .= '['.$value.']';
                    }
                }
            }else{
                $name = $item['attr_column'];
            }
            if($item['attr_group'] == 'defalut'){
             switch($attr[$key]['attr_column']){
                    case 'area':
                    $attr[$key]['attr_value'] = $mem['contact']['area'];
                    break;
                     case 'birthday':
                    $attr[$key]['attr_value'] = $mem['profile']['birthday'];
                    break;
                    case 'name':
                    $attr[$key]['attr_value'] = $mem['contact']['name'];
                    break;
					
					case 'nickname':
                    $attr[$key]['attr_value'] = $mem['nickname'];
					break;
					case 'idcard':
                    $attr[$key]['attr_value'] = $mem['idcard'];
					break;
					
                    case 'mobile':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['mobile'];
                    break;
                    case 'tel':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['telephone'];
                    break;
                    case 'zip':
                    $attr[$key]['attr_value'] = $mem['contact']['zipcode'];
                    break;
                    case 'addr':
                    $attr[$key]['attr_value'] = $mem['contact']['addr'];
                    break;
                    case 'sex':
                    $attr[$key]['attr_value'] = $mem['profile']['gender'];
                    break;
                    case 'pw_answer':
                    $attr[$key]['attr_value'] = $mem['account']['pw_answer'];
                    break;
                    case 'pw_question':
                    $attr[$key]['attr_value'] = $mem['account']['pw_question'];
                    break;
                   }
           }
          if($item['attr_group'] == 'contact'||$item['attr_group'] == 'input'||$item['attr_group'] == 'select'){
              $attr[$key]['attr_value'] = $mem['contact'][$attr[$key]['attr_column']];
              if($item['attr_sdfpath'] == ""){
              $attr[$key]['attr_value'] = $mem[$attr[$key]['attr_column']];
              if($attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_value'] = unserialize($mem[$attr[$key]['attr_column']]);
              }
          }
          }

          $attr[$key]['attr_column'] = $name;
          if($attr[$key]['attr_column']=="birthday"){
              $attr[$key]['attr_column'] = "profile[birthday]";
          }

          if($attr[$key]['attr_type'] =="select" ||$attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
          }

        }
       // echo '<pre>';print_r($attr);exit;
        foreach($attr as $k=>&$v){
            if($v['attr_name'] == '旺旺'){
                $v['attr_valtype']='alphanum';
            }
        }
        $this->pagedata['attr'] = $attr;		
        $this->pagedata['email'] = $mem['contact']['email'];
		$this->pagedata['mobile'] = $mem['contact']['phone']['mobile'];
        $this->output();
    }

    function save_setting(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>"site_member",'act'=>"setting"));
        $member_model = &$this->app->model('members');
        foreach($_POST as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $_POST[$aTmp[1]] = serialize($val);
            }
        }

        $_POST = $this->check_input($_POST);
        
        $aMem = $member_model->dump($this->app->member_id,'*',array(':account@pam'=>array('*')));
        $use_pass_data['login_name'] = $aMem['pam_account']['login_name'];
        $use_pass_data['createtime'] = $aMem['pam_account']['createtime'];
        if((empty($_POST['contact']['passwd']))||(pam_encrypt::get_encrypted_password(trim($_POST['contact']['passwd']),pam_account::get_account_type($this->app->app_id),$use_pass_data) != $aMem['pam_account']['login_password'])){
            $this->splash('failed', '', app::get('b2c')->_('密码错误，请输入正确的密码'),'','',true);
        } else {
            unset($_POST['contact']['passwd']);
        }

        if($_POST['contact']['email']&&$member_model->is_exists_email($_POST['contact']['email'],$this->app->member_id)){
            $this->splash('failed', '', app::get('b2c')->_('邮箱已经存在'),'','',true);
        }

        if($_POST['contact']['phone']['mobile'] && !preg_match('/^1[3458][0-9]{9}$/',$_POST['contact']['phone']['mobile'])){
            $this->splash('failed', '', app::get('b2c')->_('手机输入格式不正确'),'','',true);
        }

        //--防止恶意修改
        $arr_colunm = array('contact','profile','pam_account','currency');
        $attr = $this->app->model('member_attr')->getList('attr_column');
        foreach($attr as $attr_colunm){
            $colunm = $attr_colunm['attr_column'];
            $arr_colunm[] = $colunm;
        }
        foreach($_POST as $post_key=>$post_value){
            if( !in_array($post_key,$arr_colunm) ){
                unset($_POST[$post_key]);
            }
        }
        //---end

        $_POST['member_id'] = $this->app->member_id;

		//同步到ucenter yindingsheng
		if( $member_object = kernel::service("uc_user_edit")) {
			if(!$member_object->uc_user_edit($_POST)){
				$this->splash('failed', '', app::get('b2c')->_('提交失败'),'','',true);
			}
		}
		//同步到ucenter yindingsheng

        if($member_model->save($_POST)){

            //增加会员同步 2012-05-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->modifyActive($_POST['member_id']);
            }

            $this->splash('success', $url , app::get('b2c')->_('提交成功'),'','',true);
        }else{
            $this->splash('failed', '', app::get('b2c')->_('提交失败'),'','',true);
        }
    }
	public function orders_member()
	{
		$render = new base_render(app::get('b2c'));
		$type=$_POST['type'];
        $order = &$this->app->model('orders');
		// $this->pagedata['type'] =$type;
       if($type=='notify')
		{
             $oMem = &$this->app->model('member_goods');
             $aData = $oMem->get_gnotify($this->app->member_id,$this->member['member_lv']);
             $this->pagedata['notify'] = $aData['data'];
             $this->pagination($nPage,$aData['page'],'notify');
             $setting['buytarget'] = $this->app->getConf('site.buy.target');
             $imageDefault = app::get('image')->getConf('image.set');
             $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
             $this->pagedata['setting'] = $setting;
             $this->pagedata['member_id'] = $this->app->member_id;
			  echo $render->fetch('site/member/notify.html');
	    }
		else{
		$sql = $this->get_search_order_ids($type,'');
		$arrayorser = $order->db->select($sql);
		
		if(empty($arrayorser)){
			$msg='没有找到相应的订单！';
		}else{
			$aData = $order->fetchByMember($this->app->member_id,'','','',$arrayorser);
		}

        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $imageDefault = app::get('image')->getConf('image.set');
        $applySObj = app::get('spike')->model('spikeapply');
        $applyGObj = app::get('groupbuy')->model('groupapply');
        $applyScoreObj = app::get('scorebuy')->model('scoreapply');
        
        foreach($aData['data'] as $k=>$v) {
            //获取订单支付时间
            $obj_payment = app::get('ectools')->model('refunds');
            $payment_id = $obj_payment->get_payment($v['order_id']);
            $pay_time = app::get('ectools')->model('payments')->getRow('t_payed',array('payment_id'=>$payment_id['bill_id']));
            $aData['data'][$k]['pay_time'] = $pay_time['t_payed'];
            $obj_aftersales = app::get('aftersales')->model('return_product');
            $ord_id = $obj_aftersales->getRow('return_id',array('order_id'=>$v['order_id'],'status'=>'3','refund_type'=>'2'));
            if($ord_id){
                $aData['data'][$k]['need_send'] = 1;
            }else{
                $aData['data'][$k]['need_send'] = 0;
            }
            $ord_id = $obj_aftersales->getRow('return_id',array('order_id'=>$v['order_id'],'status'=>'11','refund_type'=>'2'));
            if($ord_id){
                $aData['data'][$k]['need_edit'] = 1;
            }else{
                $aData['data'][$k]['need_edit'] = 0;
            }
            //end
            foreach($v['goods_items'] as $k2=>$v2) {
                if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aData['data'][$k]['goods_items'][$k2]['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
                $act_id = '';
                //秒杀详细页参数
                switch($v['order_type']){
                    case 'spike':
                        $act_id = $applySObj->getOnActIdByGoodsId($v2['product']['goods_id']);
                        break;
                    case 'group':
                        $act_id = $applyGObj->getOnActIdByGoodsId($v2['product']['goods_id']);
                        break;
                    case 'score':
                        $act_id = $applyScoreObj->getOnActIdByGoodsId($v2['product']['goods_id']);
                        break;
                    case 'normal':
                        break;
                }
               
                if($act_id){
                    $aData['data'][$k]['goods_items'][$k2]['product']['args'] = array($v2['product']['goods_id'],'','',$act_id);
                }
            }
        }

        //添加订单html埋点
        foreach($aData['data'] as $k=>$v){
            foreach(kernel::servicelist('business.member_orders') as $service){
                if(is_object($service)){
                    if(method_exists($service,'get_orders_html')){
                        $aData['data'][$k]['html'] .= $service->get_orders_html($v);
                    }
                    if(method_exists($service,'get_orders_status_html')){
                        $aData['data'][$k]['status_html'] .= $service->get_orders_status_html($v);
                    }
                }
            }
        }
		 foreach($aData['data'] as $k=>$v){
            foreach(kernel::servicelist('business.member_comment') as $service){
                if(is_object($service)){
                    if(method_exists($service,'get_orders_html')){
                        $aData['data'][$k]['comment'] .= $service->get_orders_html($v);
                    }
                }
            }
        }
		$this->pagedata['msg']=$msg;
        $this->pagedata['orders'] = $aData['data'];
		//echo '<pre>';print_r($aData['data']);exit;
		$this->pagedata['res_url'] = $this->app->res_url;
		echo $render->fetch('site/member/orders_member.html');
		}
	}

    /**
     * Member order list datasource
     * @params int equal to 1
     * @return null
     */
    public function orders($type='',$order_id='',$goods_name='',$goods_bn='',$time='',$pay_status='',$nPage=1)
    {
        //进入页面是需要调用订单操作脚本
        //kernel::single('b2c_orderautojob')->order_auto_operation($this->app->member_id,'');
        
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的订单'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $order = &$this->app->model('orders');
        
        $order_id = trim($order_id);
        $goods_name = trim($goods_name);
        $goods_bn = trim($goods_bn);

		$sql = $this->get_search_order_ids($type,$time);
		$arrayorser = $order->db->select($sql);
		$search_order=$this->search_order($order_id,$goods_name,$goods_bn);
		$arr;
		foreach($arrayorser as $key=>$value){
			foreach($search_order as $k=>$v){
				if($value['order_id']==$v['order_id']){
					$arr[]=$value;
				}
			}
		}
		
		$arrayorser=$arr;
		if(empty($arrayorser)){
			$msg='没有找到相应的订单！';
		}else{
			$aData = $order->fetchByMember($this->app->member_id,$nPage-1,'','',$arrayorser);
		}

        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $imageDefault = app::get('image')->getConf('image.set');
        $applySObj = app::get('spike')->model('spikeapply');
        $applyGObj = app::get('groupbuy')->model('groupapply');
        $applyScoreObj = app::get('scorebuy')->model('scoreapply');
        
        foreach($aData['data'] as $k=>$v) {
            //获取订单支付时间
            $obj_payment = app::get('ectools')->model('refunds');
            $payment_id = $obj_payment->get_payment($v['order_id']);
            $pay_time = app::get('ectools')->model('payments')->getRow('t_payed',array('payment_id'=>$payment_id['bill_id']));
            $aData['data'][$k]['pay_time'] = $pay_time['t_payed'];
            $obj_aftersales = app::get('aftersales')->model('return_product');
            $ord_id = $obj_aftersales->getRow('return_id',array('order_id'=>$v['order_id'],'status'=>'3','refund_type'=>'2'));
            if($ord_id){
                $aData['data'][$k]['need_send'] = 1;
            }else{
                $aData['data'][$k]['need_send'] = 0;
            }
            $ord_id = $obj_aftersales->getRow('return_id',array('order_id'=>$v['order_id'],'status'=>'11','refund_type'=>'2'));
            if($ord_id){
                $aData['data'][$k]['need_edit'] = 1;
            }else{
                $aData['data'][$k]['need_edit'] = 0;
            }
            //end
            foreach($v['goods_items'] as $k2=>$v2) {
                if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aData['data'][$k]['goods_items'][$k2]['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
                $act_id = '';
                //秒杀详细页参数
                switch($v['order_type']){
                    case 'spike':
                        $act_id = $applySObj->getOnActIdByGoodsId($v2['product']['goods_id']);
                        break;
                    case 'group':
                        $act_id = $applyGObj->getOnActIdByGoodsId($v2['product']['goods_id']);
                        break;
                    case 'score':
                        $act_id = $applyScoreObj->getOnActIdByGoodsId($v2['product']['goods_id']);
                        break;
                    case 'normal':
                        break;
                }
               
                if($act_id){
                    $aData['data'][$k]['goods_items'][$k2]['product']['args'] = array($v2['product']['goods_id'],'','',$act_id);
                }
            }

            //获取买家/卖家
            $obj_members = app::get('pam')->model('account');
            $buy_name = $obj_members->getRow('login_name',array('account'=>$v['member_id']));
            $aData['data'][$k]['buy_name'] = $buy_name['login_name'];

            $obj_strman = app::get('business')->model('storemanger');
            $seller_id = $obj_strman->getRow('account_id,store_idcardname',array('store_id'=>$v['store_id']));
            $seller_name = $obj_members->getRow('login_name',array('account_id'=>$seller_id['account_id']));
            $aData['data'][$k]['seller_name'] = $seller_name['login_name'];
            $aData['data'][$k]['seller_real_name'] = $seller_id['store_idcardname'];
        }

        //添加订单html埋点
        foreach($aData['data'] as $k=>$v){
            foreach(kernel::servicelist('business.member_orders') as $service){
                if(is_object($service)){
                    if(method_exists($service,'get_orders_html')){
                        $aData['data'][$k]['html'] .= $service->get_orders_html($v);
                    }
                    if(method_exists($service,'get_orders_status_html')){
                        $aData['data'][$k]['status_html'] .= $service->get_orders_status_html($v);
                    }
                }
            }
        }
		$this->pagedata['msg']=$msg;
        $this->pagedata['orders'] = $aData['data'];

        //下拉框数据 --start 
        $this->pagedata['select']['time']['options'] = $this->get_select_date();
        $this->pagedata['select']['time']['value'] = $time;
        //下拉框数据 --end

		//获取传过来的参数
        $this->pagedata['type'] =$type;
		$this->pagedata['order_id'] = $order_id;
		$this->pagedata['goods_name'] = $goods_name;
		$this->pagedata['goods_bn'] = $goods_bn;
		$this->pagedata['time'] = $time;
       
        //修改分页链接参数 --start 
		
        $arr_args = array($type,$order_id,$goods_name,$goods_bn,$time,$pay_status);
		
        //--end
        $this->pagination($nPage,$aData['pager']['total'],'orders',$arr_args);
        $this->pagedata['res_url'] = $this->app->res_url;

        $this->output();
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string tpl
     * @return null
     */
    protected function get_order_details(&$aData,$tml='member_orders')
    {
        if (isset($aData['data']) && $aData['data'])
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }

            foreach ($aData['data'] as &$arr_data_item)
            {
                $this->get_order_detail_item($arr_data_item,$tml);
            }
        }
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string 模版名称
     * @return null
     */
    protected function get_order_detail_item(&$arr_data_item,$tpl='member_order_detail')
    {
        if (isset($arr_data_item) && $arr_data_item)
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }


            $arr_data_item['goods_items'] = array();
            $obj_specification = $this->app->model('specification');
            $obj_spec_values = $this->app->model('spec_values');
            $obj_goods = $this->app->model('goods');
            if (isset($arr_data_item['order_objects']) && $arr_data_item['order_objects'])
            {
                foreach ($arr_data_item['order_objects'] as $k=>$arr_objects)
                {
					//echo '<pre>';print_r($arr_objects['order_items']);exit;
                    $index = 0;
                    $index_adj = 0;
                    $index_gift = 0;
                    $image_set = app::get('image')->getConf('image.set');
                    if ($arr_objects['obj_type'] == 'goods')
                    {
                        foreach ($arr_objects['order_items'] as $arr_items)
                        {
                            if (!$arr_items['products'])
                            {
                                $o = $this->app->model('order_items');
                                $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                            }

                            if ($arr_items['item_type'] == 'product')
                            {
                                if ($arr_data_item['goods_items'][$k]['product'])
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k]['product']['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $arr_items['quantity'];

                                $arr_data_item['goods_items'][$k]['product']['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k]['product']['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k]['product']['price'] = $arr_items['price'];
								$arr_data_item['goods_items'][$k]['product']['mktprice'] = $arr_items['products']['price']['mktprice']['price'];
                                $arr_data_item['goods_items'][$k]['product']['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k]['product']['quantity']);
                                $arr_data_item['goods_items'][$k]['product']['amount'] = $arr_items['amount'];
                                $arr_goods_list = $obj_goods->getList('image_default_id', array('goods_id' => $arr_items['goods_id']));
                                $arr_goods = $arr_goods_list[0];
                                if (!$arr_goods['image_default_id'])
                                {
                                    $arr_goods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k]['product']['thumbnail_pic'] = $arr_goods['image_default_id'];
                                //团购秒杀链接
                                if($arr_data_item['order_type']=='group' || $arr_data_item['order_type']=='spike' || $arr_data_item['order_type']=='score'){
                                    switch($arr_data_item['order_type']){
                                        case 'group':
                                            $appName = 'groupbuy';
                                            break;
                                        case 'spike':
                                            $appName = 'spike';
                                            break;
                                        case 'score':
                                            $appName = 'scorebuy';
                                            break;
                                        default:
                                            $appName = 'b2c';
                                    }
                                    $args = array($arr_items['goods_id'],'','',$arr_data_item['act_id']);
                                    
                                    $arr_data_item['goods_items'][$k]['product']['link_url'] = $this->gen_url(array('app'=>$appName,'ctl'=>'site_product','act'=>'index','args'=>$args));
                                }else{
                                    $arr_data_item['goods_items'][$k]['product']['link_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$arr_items['goods_id']));
                                }
                                if ($arr_items['addon'])
                                {
                                    $arrAddon = $arr_addon = unserialize($arr_items['addon']);
                                    if ($arr_addon['product_attr'])
                                        unset($arr_addon['product_attr']);
                                    $arr_data_item['goods_items'][$k]['product']['minfo'] = $arr_addon;
                                }else{
                                    unset($arrAddon,$arr_addon);
                                }
                                if ($arrAddon['product_attr'])
                                {
                                    foreach ($arrAddon['product_attr'] as $arr_product_attr)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k]['product']['attr']) && $arr_data_item['goods_items'][$k]['product']['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")) !== false)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] = substr($arr_data_item['goods_items'][$k]['product']['attr'], 0, strrpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")));
                                    }
                                }
                            }
                            elseif ($arr_items['item_type'] == 'adjunct')
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);


                                if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj])
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $arr_items['quantity'];

                                if (!$arrGoods['image_default_id'])
                                {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']);
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['price'] = $arr_items['price'];
								$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['mktprice'] = $arr_items['products']['price']['mktprice']['price'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['link_url'] = $arrGoods['link_url'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['amount'] = $arr_items['amount'];

                                if ($arr_items['addon'])
                                {
                                    $arr_addon = unserialize($arr_items['addon']);

                                    if ($arr_addon['product_attr'])
                                    {
                                        foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                        }
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")) !== false)
                                    {
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")));
                                    }
                                }

                                $index_adj++;
                            }
                            else
                            {
                                // product gift.
                                if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                                {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift])
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['name'] = $arr_items['name'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['price'] = $arr_items['price'];
									$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['mktprice'] = $arr_items['products']['price']['mktprice']['price'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity']);
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'])
                                    {
                                        if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                                $index_gift++;
                            }
                        }
                    }
                    else
                    {
                        if ($arr_objects['obj_type'] == 'gift')
                        {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {
                                foreach ($arr_objects['order_items'] as $arr_items)
                                {
                                    if (!$arr_items['products'])
                                    {
                                        $o = $this->app->model('order_items');
                                        $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                        $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                                    }

                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if (!isset($arr_items['products']['product_id']) || !$arr_items['products']['product_id'])
                                        $arr_items['products']['product_id'] = $arr_items['goods_id'];

                                    if ($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']])
                                        $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }

                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['name'] = $arr_items['name'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['price'] = $arr_items['price'];
									$arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['mktprice'] = $arr_items['products']['price']['mktprice']['price'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['point'] = intval($arr_items['score']*$arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']);
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['nums'] = $arr_items['quantity'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr']) && $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'])
                                    {
                                        if (strpos($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] = substr($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], 0, strrpos($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {

                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                $arr_data_item['extends_items'][] = $str_service_goods_type_obj->get_order_object($arr_objects, $arr_Goods,$tpl);
                            }
                        }
                    }
                }
            }

        }
    }

    /**
     * Generate the order detail
     * @params string order_id
     * @return null
     */
    public function orderdetail($order_id=0)
    {
        if (!isset($order_id) || !$order_id)
        {
            $this->begin(array('app' => 'b2c','ctl' => 'site_member', 'act'=>'index'));
            $this->end(false, app::get('b2c')->_('订单编号不能为空！'));
        }

        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, '*', $subsdf);
        $objMath = kernel::single("ectools_math");

        if(!$sdf_order||$this->app->member_id!=$sdf_order['member_id']){
            $this->_response->set_http_response_code(404);
            $this->_response->set_body(app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
            return;
        }
        if($sdf_order['member_id']){
            $member = &$this->app->model('members');
            $aMember = $member->dump($sdf_order['member_id'], 'email');
            $sdf_order['receiver']['email'] = $aMember['contact']['email'];
        }

        // 处理收货人地区
        $arr_consignee_area = array();
        $arr_consignee_regions = array();
        if (strpos($sdf_order['consignee']['area'], ':') !== false)
        {
            $arr_consignee_area = explode(':', $sdf_order['consignee']['area']);
            if ($arr_consignee_area[1])
            {
                if (strpos($arr_consignee_area[1], '/') !== false)
                {
                    $arr_consignee_regions = explode('/', $arr_consignee_area[1]);
                }
            }

            $sdf_order['consignee']['area'] = (is_array($arr_consignee_regions) && $arr_consignee_regions) ? $arr_consignee_regions[0] . $arr_consignee_regions[1] . $arr_consignee_regions[2] : $sdf_order['consignee']['area'];
        }

        // 订单的相关信息的修改
        $obj_other_info = kernel::servicelist('b2c.order_other_infomation');
        if ($obj_other_info)
        {
            foreach ($obj_other_info as $obj)
            {
                $this->pagedata['discount_html'] = $obj->gen_point_discount($sdf_order);
            }
        }
        foreach(kernel::servicelist('business.member_orders') as $service){
            if(is_object($service)){
                if(method_exists($service,'get_orders_status_html')){
                    $sdf_order['status_html'] .= $service->get_orders_status_html($sdf_order, 'buyerdetail');
                }
            }
        }
        $sdf_order['isNeedAddress'] = true;
        $sdf_order['isNeedDelivery'] = true;
        if (isset($sdf_order['order_kind']) && $sdf_order['order_kind'] == '3rdparty') {
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if ($processor->goodsKindDetail() == $sdf_order['order_kind_detail'] && $processor->isCustom('order_delivery')) {
                    $sdf_order['isNeedAddress'] = $processor->isNeedAddress();
                    $sdf_order['isNeedDelivery'] = $processor->isNeedDelivery();
                    break;
                }
            }
        }
        
        
        $this->pagedata['order'] = $sdf_order;

        $order_items = array();
        $gift_items = array();
        $this->get_order_detail_item($sdf_order,'member_order_detail');
        $this->pagedata['order'] = $sdf_order;

        /** 去掉商品优惠 **/
        if ($this->pagedata['order']['order_pmt'])
        {
            foreach ($this->pagedata['order']['order_pmt'] as $key=>$arr_pmt)
            {
                if ($arr_pmt['pmt_type'] == 'goods')
                {
                    unset($this->pagedata['order']['order_pmt'][$key]);
                }
            }
        }
        /** end **/

        // 得到订单留言.
        $oMsg = &kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');

        $this->pagedata['ordermsg'] = $arrOrderMsg;
        $this->pagedata['res_url'] = $this->app->res_url;

        // 生成订单日志明细
        //$oLogs =&$this->app->model('order_log');
        //$arr_order_logs = $oLogs->getList('*', array('rel_id' => $order_id));
        $arr_order_logs = $objOrder->getOrderLogList($order_id);

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        $this->pagedata['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($this->pagedata['order']['payinfo']['pay_app_id']);
        
        //物流跟踪安装并且开启
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        $logisticst_service = kernel::service('b2c_change_orderloglist');
        if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
            $this->pagedata['services']['logisticstack'] = $logisticst_service;
        }
        
        $this->pagedata['orderlogs'] = $arr_order_logs['data'];
        // 添加html埋点
        foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'fetchHtml') ) {
                    $services->fetchHtml($this,$order_id,'site/invoice_detail.html');
                }
            }
        }
        $this->output();
    }

    /**
     * 会员中心订单提交页面
     * @params string order id
     * @params boolean 支付方式的选择
     */
    public function orderPayments($order_id,$selecttype=false,$from=false)
    {
        $objOrder = &$this->app->model('orders');
        $sdf = $objOrder->dump($order_id);
        $objMath = kernel::single("ectools_math");
        if(!$sdf){
            exit;
        }
        $sdf['total'] = $sdf['cur_amount'];
        $sdf['cur_amount'] = $objMath->number_minus(array($sdf['cur_amount'], $sdf['payed']));
        $sdf['total_amount'] = $objMath->number_div(array($sdf['cur_amount'], $sdf['cur_rate']));

        $this->pagedata['order'] = $sdf;
        // 货到付款不能进入此页面
        if ($sdf['payinfo']['pay_app_id'] == '-1')
        {
            $this->begin(array('app' => 'b2c','ctl' => 'site_member', 'act'=>'orderdetail', 'arg0'=>$order_id));
            $this->end(false, app::get('b2c')->_('配送方式只支持货到付款'));
        }

        if($selecttype){
            $selecttype = 1;
        }else{
            $selecttype = 0;
        }
        $this->pagedata['order']['selecttype'] = $selecttype;
        $opayment = app::get('ectools')->model('payment_cfgs');
        $this->pagedata['payments'] = $opayment->getListByCode($sdf['currency']);

        $system_money_decimals = $this->app->getConf('system.money.decimals');
        $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');
        foreach ($this->pagedata['payments'] as $key=>&$arrPayments)
        {
            if (!$sdf['member_id'])
            {
                if (trim($arrPayments['app_id']) == 'deposit')
                {
                    unset($this->pagedata['payments'][$key]);
                    continue;
                }
            }

            if ($arrPayments['app_id'] == $this->pagedata['order']['payinfo']['pay_app_id'])
            {
                $arrPayments['cur_money'] = $objMath->formatNumber($this->pagedata['order']['cur_amount'], $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['total_amount'] = $objMath->formatNumber($this->pagedata['order']['total_amount'], $system_money_decimals, $system_money_operation_carryset);
            }
            else
            {
                $arrPayments['cur_money'] = $this->pagedata['order']['cur_amount'];
                $cur_discount = $objMath->number_multiple(array($sdf['discount'], $this->pagedata['order']['cur_rate']));
                if ($this->pagedata['order']['payinfo']['cost_payment'] > 0)
                {
                    if ($this->pagedata['order']['cur_amount'] > 0)
                        $cost_payments_rate = $objMath->number_div(array($arrPayments['cur_money'], $objMath->number_plus(array($this->pagedata['order']['cur_amount'], $this->pagedata['order']['payed']))));
                    else
                        $cost_payments_rate = 0;
                    $cost_payment = $objMath->number_multiple(array($objMath->number_multiple(array($this->pagedata['order']['payinfo']['cost_payment'], $this->pagedata['order']['cur_rate'])), $cost_payments_rate));
                    $arrPayments['cur_money'] = $objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $arrPayments['cur_money'] = $objMath->number_minus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']))));
                    $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }
                else
                {
                    $arrPayments['cur_money'] = $objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $cost_payment = $objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']));
                    $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }

                $arrPayments['total_amount'] = $objMath->formatNumber($objMath->number_div(array($arrPayments['cur_money'], $this->pagedata['order']['cur_rate'])), $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['cur_money'] = $objMath->formatNumber($arrPayments['cur_money'], $system_money_decimals, $system_money_operation_carryset);
            }
        }

        $objCur = app::get('ectools')->model('currency');
        $aCur = $objCur->getFormat($this->pagedata['order']['currency']);
        $this->pagedata['order']['cur_def'] = $aCur['sign'];

        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result'));
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['form_action'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'dopayment','arg0'=>'order'));
        $obj_order_payment_html = kernel::servicelist('b2c.order.pay_html');
        $app_id = 'b2c';
        if ($obj_order_payment_html)
        {
            foreach ($obj_order_payment_html as $obj)
            {
                $obj->gen_data($this, $app_id);
            }
        }
        
        if ($sdf['cur_amount'] == '0')
        {
            // 模拟支付流程
            $objPay = kernel::single("ectools_pay");
            $sdffds = array(
                'payment_id' => $objPay->get_payment_id(),
                'order_id' => $sdf['order_id'],
                'rel_id' => $sdf['order_id'],
                'op_id' => $sdf['member_id'],
                'pay_app_id' => $sdf['payinfo']['pay_app_id'],
                'currency' => $sdf['currency'],
                'payinfo' => array(
                    'cost_payment' => $sdf['payinfo']['cost_payment'],
                ),
                'pay_object' => 'order',
                'member_id' => $sdf['member_id'],
                'op_name' => $this->user->user_data['account']['login_name'],
                'status' => 'ready',
                'cur_money' => $sdf['cur_amount'],
                'money' => $sdf['total_amount'],
            );
            $is_payed = $objPay->gopay($sdffds, $msg);
            if (!$is_payed){
                $msg = app::get('b2c')->_('订单自动支付失败！');
                $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')));
            }

            $obj_pay_lists = kernel::servicelist("order.pay_finish");
            $is_payed = false;
            foreach ($obj_pay_lists as $order_pay_service_object)
            {
                $is_payed = $order_pay_service_object->order_pay_finish($sdffds, 'succ', 'font',$msg);
            }
        }
		//平台客服qq
        $qq=app::get('b2c')->getConf('member.ServiceQQ');
		$this->pagedata['qq']=$qq;
        //begin 获取银行信息
		$bankInfo = kernel::single('b2c_banks_info')->getBank();
		$this->pagedata['bankinfo'] = $bankInfo;
		//	echo '<pre>';print_r($bankInfo);exit;
        if($from){
            //$this->set_tmpl('order_index');
            $this->page('site/member/orderPayments.html',false,'b2c');
        }else{
            $this->page('site/member/orderPayments.html',false,'b2c');
        }

    }

    function deposit(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('预存款充值'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $oCur = app::get('ectools')->model('currency');
        $currency = $oCur->getDefault();
        $this->pagedata['currencys'] = $currency;
        $this->pagedata['currency'] = $currency['cur_code'];
        $opay = app::get('ectools')->model('payment_cfgs');
        $aOld = $opay->getList('*', array('status' => 'true', 'platform'=>'ispc', 'is_frontend' => true));

        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];

        $aData = array();
        foreach($aOld as $val){
        if(($val['app_id']!='deposit') && ($val['app_id']!='offline'))$aData[] = $val;
        }
        $this->pagedata['total'] = $this->member['advance']['total'];
        $this->pagedata['payments'] = $aData;
        $this->pagedata['member_id'] = $this->app->member_id;
        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'balance'));
        $bankInfo = kernel::single('b2c_banks_info')->getBank();
		$this->pagedata['bankinfo'] = $bankInfo;
        $this->output();
    }

    public function balance($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的预存款'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $member = &$this->app->model('members');
        $mem_adv = &$this->app->model('member_advance');
        $items_adv = $mem_adv->get_list_bymemId($this->app->member_id);
        $count = count($items_adv);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $mem_adv->getList('*',array('member_id' => $this->member['member_id']),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'balance');
        $this->pagedata['advlogs'] = $params['data'];
        $data = $member->dump($this->app->member_id,'advance');
        $this->pagedata['total'] = $data['advance']['total'];
        // errorMsg parse.
        $this->pagedata['errorMsg'] = json_decode($_GET['errorMsg']);
        $this->output();
    }


    function pointHistory($nPage=1) {
        $userId = $this->app->member_id;
        $oPointHistory = &$this->app->model('point_history');
        $obj_memberberPoint = &$this->app->model('trading/memberPoint');
        $this->pagedata['historys'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'pointHistory');
        $this->output();
    }

    function favorite($type='',$nPage=1){
        $this->path[] = array('title'=>app::get('business')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('我的收藏'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
		
        $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$nPage,'',$type);
        $imageDefault = app::get('image')->getConf('image.set');
        $aProduct = $aData['data'];

        $oGoods = app::get('b2c')->model('goods');
        $oMGoods = app::get('b2c')->model('member_goods');

        foreach($aProduct as &$value){

            $goods = $oGoods->getList('bn',array('goods_id'=>$value['goods_id']));
            $value['bn'] = $goods[0]['bn'];

            $mgoods = $oMGoods->getList('create_time',array('goods_id'=>$value['goods_id'],'member_id'=>$this->app->member_id,'type'=>'fav'));
            $value['create_time'] = $mgoods[0]['create_time'];
        }
        
        
        $oImage = app::get('image')->model('image');
        foreach($aProduct as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
            }

            if(!$oImage->getList("image_id",array('image_id'=>$v['thumbnail_pic']))) {
                $aProduct[$k]['thumbnail_pic'] = $imageDefault['S']['default_image'];
            }
        }
        $this->pagedata['favorite'] = $aProduct;
        $arr_args = array($type);
        $this->pagination($nPage,$aData['page'],'favorite',$arr_args);
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['current_page'] = $nPage;
        /** 接触收藏的页面地址 **/
        $this->pagedata['fav_ajax_del_goods_url'] = $this->gen_url(array('app'=>'business','ctl'=>'site_favorite','act'=>'ajax_del_fav','args'=>array('goods')));

        $this->output();
    }
    
    function member() {
        $minfo=$this->get_current_member();
        $is_seller=($minfo['seller']=='seller');
        if ($is_seller) {
            $this->redirect(array('app'=>'business','ctl'=>'site_member','act'=>'index'));
        } else {
            $this->redirect(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        }
    }

    function index() {

        //进入页面是需要调用订单操作脚本
        // kernel::single('b2c_orderautojob')->order_auto_operation($this->app->member_id);
      
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $GLOBALS['runtime']['path'] = $this->path;
        $oMem = &$this->app->model('members');
        $oRder = &$this->app->model('orders');
        $oMem_lv = $this->app->model('member_lv');
        $this->pagedata['switch_lv'] = $oMem_lv->get_member_lv_switch($this->member['member_lv']);
        $order = $oRder->getList('*',array('member_id' => $this->app->member_id));
        $order_total = count($order);
        $aInfo = $oMem->dump($this->app->member_id);
        $order = &$this->app->model('orders');
		$oGoods = $this->app->model("goods");
       // $aData = $order->fetchByMember($this->app->member_id,$nPage-1);
        //$this->get_order_details($aData, 'member_latest_orders');

        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];
        #获取咨询评论回复
        $obj_mem_msg = kernel::single('b2c_message_disask');
        $this->member['unreadmsg'] = $obj_mem_msg->calc_unread_disask($this->member['member_id']);

        // 额外的会员的信息 - 冻结积分、将要获得的积分
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_point($this->member['member_id']);
            }
        }
        // 判断是否开启预存款
        $_mdl_payment_cfgs = app::get('ectools')->model('payment_cfgs');
        $_payment_info = $_mdl_payment_cfgs->getPaymentInfo('deposit');
        if($_payment_info['app_staus'] == app::get('ectools')->_('开启'))
            $this->pagedata['deposit_status'] = 'true';
        $this->pagedata['member'] = $this->member;
        $this->pagedata['total_order'] = $order_total;
        $this->pagedata['aNum']=$aInfo['advance']['total'];$this->set_tmpl('member');
        $obj_member = &$this->app->model('member_goods');
        $aData_fav = $obj_member->get_favorite($this->app->member_id,$this->member['member_lv']);
        $this->pagedata['favorite'] = $aData_fav['data'];
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $rule = kernel::single('b2c_member_solution');
        $this->pagedata['wel'] = $rule->get_all_to_array($this->member['member_lv']);
        $this->pagedata['res_url'] = $this->app->res_url;

        $this->pagedata['fav_num'] = count($aData_fav['data']);
        //echo '<pre>';print_r($this->pagedata);exit;
        //优惠券的数量
        $oCoupon = kernel::single('b2c_coupon_mem');
        $aCoupon = $oCoupon->get_list_m($this->member['member_id']);
        $this->pagedata['coupon_num'] = count($aCoupon);
        //待评价的商品
        $oRder_items = &$this->app->model('order_items');
        $omember_comments = &$this->app->model('member_comments');
        $evaluate_filter['member_id'] = $this->member['member_id'];
        $evaluate_filter['status'] = 'finish';
        $evaluate_filter['comments_count'] = 0;
        $orders = $oRder->getList('order_id,createtime,comments_count',$evaluate_filter);
       
        $day_1 = app::get('b2c')->getConf('site.comment_original_time');
        $day_2 = app::get('b2c')->getConf('site.comment_additional_time');
        $day_1 = intval($day_1)?intval($day_1):30;
        $day_2 = intval($day_2)?intval($day_2):90;
        $order_ids = array();
        foreach($orders as &$v){
            if(intval($v['comments_count']) > 1 || intval($v['createtime']) < strtotime("-{$day_2} day")) continue;
            if(intval($v['comments_count']) == 0 && intval($v['createtime']) < strtotime("-{$day_1} day")) continue;
            if(intval($v['comments_count']) == 0 && intval($v['createtime']) >= strtotime("-{$day_1} day")){
                $order_ids[] = $v['order_id'];
            }
        }
        $good = array();
        if(!empty($order_ids))
        $good = $oRder_items->getList('order_id,goods_id,name',array('order_id|in'=>$order_ids));
       
        foreach($good as $k=>&$v){
             $orders1 = $oRder->getList('createtime',array('order_id'=>$v['order_id']));
             $ogoods1 = $oGoods->getList('image_default_id,comments_count',array('goods_id'=>$v['goods_id']));
             $v['createtime'] = $orders1[0]['createtime'];
             $v['image_default_id'] = $ogoods1[0]['image_default_id'];
             $v['comments_count'] = $ogoods1[0]['comments_count'];
             $is_comment= $omember_comments->getList('comment_id',array('type_id'=>$v['goods_id'],'order_id'=>$v['order_id']));
             if(count($is_comment)>0){
                 $v['is_comment'] = true;
             }else{
                 $v['is_comment'] = false;
             }
        }
        $this->pagedata['discuss_num'] = count($order_ids);
        $this->pagedata['good'] = $good;
        //已买到的宝贝
        $buy_filter['member_id'] = $this->member['member_id'];
        $buy_filter['status|noequal'] = 'dead';
        $buy_filter['comments_count'] = 0;
        $buy_orders = $oRder->getList('*',$buy_filter);
        foreach($buy_orders as &$v){
              $buy_order_ids[] = $v['order_id'];
        }
        $buy_good = $oRder_items->getList('order_id,goods_id',array('order_id|in'=>$buy_order_ids),0,-1,'item_id desc');
        foreach($buy_good as $k=>&$v){
             $orders2 = $oRder->getList('ship_status,status,confirm,pay_status,comments_count,createtime',array('order_id'=>$v['order_id']));
             $ogoods2 = $oGoods->getList('image_default_id',array('goods_id'=>$v['goods_id']));
             $v['ship_status'] = $orders2[0]['ship_status'];
             $v['status'] = $orders2[0]['status'];
             $v['pay_status'] = $orders2[0]['pay_status'];
            
            if(intval($orders2[0]['comments_count']) > 1 || intval($orders2[0]['createtime']) < strtotime("-{$day_2} day")){
                $v['comments_count'] = -1;
            }else if(intval($orders2[0]['comments_count']) == 0 && intval($orders2[0]['createtime']) < strtotime("-{$day_1} day")){
                $v['comments_count'] = -1;
            }else{
                $v['comments_count'] = $orders2[0]['comments_count'];
            }
             
             
             $v['confirm'] = $orders2[0]['confirm'];
             $v['image_default_id'] = $ogoods2[0]['image_default_id'];
        }
        $this->pagedata['buy_good'] = $buy_good;
        //待确认订单的数目
        $confirm_filter['member_id'] = $this->member['member_id'];
        $confirm_filter['confirm'] = 'N';
        $confirm_filter['pay_status'] = '1';
        $confirm_filter['ship_status'] = '1';
        $confirm_filter['status'] = 'active';
        $confirm_orders = $oRder->getList('order_id',$confirm_filter);
        $this->pagedata['confirm_num'] = count($confirm_orders);
        //降价商品的数量
        $Mgoods = $this->app->model('member_goods');
        $goods_price = $Mgoods->getList('gnotify_id',array('type'=>'fav','is_change'=>'down','member_id'=>$this->app->member_id));
        $this->pagedata['goods_price_down_num'] = count($goods_price);
        //促销商品
        $pmt_goods = $Mgoods->getList('goods_id',array('type'=>'fav','member_id'=>$this->app->member_id));
        foreach($pmt_goods as $v){
            $p_goods[] = $v['goods_id'];
        }

        $pmt_good = $oGoods->getList('goods_id',array('act_type|noequal'=>'normal','goods_id|in'=>$p_goods));
        $this->pagedata['pmt_good_num'] = count($pmt_good);
        //判断手机，邮箱，支付密码
        $is_pass = $oMem->getList('mobile,email',array('member_id'=>$this->app->member_id));
        $is_pass_num = 0;
        if($is_pass[0]['mobile']){
            $is_pass_num++;
            $is_mobile = 1;
        }else{
            $is_mobile = 0;
        }
        if($is_pass[0]['email']){
            $is_pass_num++;
            $is_email = 1;
        }else{
            $is_email = 0;
        }
        //获取提醒信息
        $mem_msg = $this->app->model('member_comments');
        $sql = " SELECT * FROM `sdb_b2c_member_comments` WHERE  `to_id`='".$this->member['member_id']."' AND `for_comment_id`='0' AND `object_type`='msg'  AND `has_sent`='true' AND `inbox`='true' AND `mem_read_status`='false' AND `display`='true'";
        $msg_arr =  $mem_msg->db->select($sql);
        $remind_info_count = count($msg_arr);
        $this->pagedata['remind_info_count'] = $remind_info_count;

        $this->pagedata['is_mobile'] = $is_mobile;
        $this->pagedata['is_email'] = $is_email;
        $this->pagedata['is_pass_num']= $is_pass_num;

        $site_get_policy_method = $this->app->getConf('site.get_policy.method');
        $this->pagedata['site_point_usage'] = $site_get_policy_method != '1' ? 'true' : 'false';
        $this->output();
    }

    function del_fav($nGid=null,$delAll=false){
        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,'goods',$nGid,$page)){
            $this->splash('failed', 'back', app::get('b2c')->_('删除错误！'));
            }

        $this->redirect(array('app'=>'b2c','ctl'=>'site_member','act'=>'favorite','args'=>array($page)));
    }

    function ajax_del_fav($object_type='goods'){
        if(!$this->app->member_id){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index'));
        }

        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$_POST['gid'],$maxPage)){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_('删除失败！').'",_:null}';
        }else{
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

            $current_page = $_POST['current'];
            header('Content-Type:text/jcmd; charset=utf-8');

            if ($current_page > $maxPage){
                $current_page = $maxPage;
                $reload_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'favorite','args'=>array($current_page)));
                echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null,data:"",reload:"'.$reload_url.'"}';exit;
        }

            $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$current_page);
            $aProduct = $aData['data'];

            $oImage = app::get('image')->model('image');
            $imageDefault = app::get('image')->getConf('image.set');
            foreach($aProduct as $k=>$v) {
                if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
    }
        }
            $this->pagedata['favorite'] = $aProduct;
            $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
            $str_html = $this->fetch('site/member/favorite_items.html');
            echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
        }
    }

    function ajax_fav()
    {
        $object_type = $_POST['type'];
        $nGid = $_POST['gid'];
        $act_type = $_POST['act_type'];
        if($act_type == 'del'){
            if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$nGid)){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('删除失败！').'",_:null}';
            }else{
                $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null}';
            }
        }
        else{
            if (!kernel::single('b2c_member_fav')->add_fav($this->app->member_id,$object_type,$nGid)){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('添加失败！').'",_:null}';
            }else{
                $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.app::get('b2c')->_('添加成功！').'",_:null}';
            }
        }


    }

    //收件箱
    function inbox($nPage=1) {
        $this->get_msg_num();
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true'));
        $aData['data'] = $row;
        #print_r($row);
        $aData['total'] = count($row);
        $count = count($row);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true'),$aPage['start'],$this->pagesize);

        $params['page'] = $aPage['maxPage'];
        $this->pagedata['message'] = $params['data'];
        $this->pagedata['total_msg'] = $aData['total'];
        $this->pagination($nPage,$params['page'],'inbox');
        $this->output();
    }

    //草稿箱
    function outbox($nPage=1) {
        $this->get_msg_num();
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id));
        $aData['data'] = $row;
        $aData['total'] = count($row);
        $count = count($row);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $oMsg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagedata['message'] = $params['data'];
        $this->pagedata['total_msg'] = $aData['total'];
        $this->pagination($nPage,$params['page'],'outbox');
        $this->output();
    }

    //已发送
    function track($nPage=1) {
        $this->get_msg_num();
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('author_id' => $this->app->member_id,'has_sent' => 'true','track' => 'true'));
        $aData['data'] = $row;
        $aData['total'] = count($row);
        $count = count($row);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $oMsg->getList('*',array('author_id' => $this->app->member_id,'has_sent' => 'true','track' => 'true'),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagedata['message'] = $params['data'];
        $this->pagedata['total_msg'] = $aData['total'];
        $this->pagination($nPage,$params['page'],'track');
        $this->output();
    }

    function view_msg($nMsgId){
        $objMsg = kernel::single('b2c_message_msg');
        $aMsg = $objMsg->getList('comment',array('comment_id' => $nMsgId,'for_comment_id' => 'all','to_id'=>$this->app->member_id));
        if($aMsg[0]&&($aMsg[0]['author_id']!=$this->member['member_id']&&$aMsg[0]['to_id']!=$this->member['member_id'])){
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('b2c')->_('对不起，您没有权限查看这条信息！');exit;
        }
        $objMsg->setReaded($nMsgId);
        $objAjax = kernel::single('b2c_view_ajax');
        echo $objAjax->get_html(htmlspecialchars_decode($aMsg[0]['comment']),'b2c_ctl_site_member','view_msg');
        exit;

    }

    function viewMsg($nMsgId){
        $objMsg = kernel::single('b2c_message_msg');
        $objMsg->type = 'msg';

        $nMsgId = kernel::database()->quote($nMsgId);
        $filter = array(
            'filter_sql'=>'(`comment_id`='.$nMsgId.' AND `for_comment_id`="all" AND `to_id`="'.$this->app->member_id.'") OR (`comment_id`='.$nMsgId.' AND `for_comment_id`="all" AND `author_id`="'.$this->app->member_id.'")'
        );

        $aMsg = $objMsg->getList('comment',$filter);
        if($aMsg[0]&&($aMsg[0]['author_id']!=$this->member['member_id']&&$aMsg[0]['to_id']!=$this->member['member_id'])){
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('b2c')->_('对不起，您没有权限查看这条信息！');exit;
        }
        echo htmlspecialchars_decode($aMsg[0]['comment']);
        exit;

    }

    function del_in_box_msg(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'inbox'));
        if(!empty($_POST['delete']))
        {
            $objMsg = kernel::single('b2c_message_msg');
            if($objMsg->check_msg($_POST['delete'],$this->member['member_id']))
            {
                if($objMsg->delete_msg($_POST['delete'],'inbox'))
                $this->splash('success',$url,app::get('b2c')->_('删除成功！'),'','',true);
                else $this->splash('failed',$url,app::get('b2c')->_('删除失败！'),'','',true);
            }
            else
            {
                $this->splash('failed',$url,app::get('b2c')->_('删除失败: 参数提交错误！！'),'','',true);
            }

        }
        else
        {
              $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
        }
    }

    function del_track_msg() {
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'track'));
        if(!empty($_POST['deltrack'])){
            $objMsg = kernel::single('b2c_message_msg');
            if($objMsg->check_msg($_POST['deltrack'],$this->member['member_id']))
            {
                if($objMsg->delete_msg($_POST['deltrack'],'track'))
                $this->splash('success',$url,app::get('b2c')->_('删除成功！'),'','',true);
                else $this->splash('failed',$url,app::get('b2c')->_('删除失败！'),'','',true);
            }
            else
            {
                $this->splash('failed',$url,app::get('b2c')->_('删除失败: 参数提交错误！！'),'','',true);
            }

        }
        else
        {
            $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
        }
    }

    function del_out_box_msg() {
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'outbox'));
        if(!empty($_POST['deloutbox']))
        {
            $objMsg = kernel::single('b2c_message_msg');
            if($objMsg->check_msg($_POST['deloutbox'],$this->member['member_id']))
            {
                $objMsg->delete(array('object_type' => 'msg','comment_id' =>$_POST['deloutbox']));
                $this->splash('success',$url,app::get('b2c')->_('删除成功！'));
            }
            else
            {
                 $this->splash('failed',$url,app::get('b2c')->_('删除失败: 参数提交错误！！'),'','',true);
            }
        }
        else
        {
            $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
        }
    }

    function send($nMsgId=false,$type='') {
        $this->get_msg_num();
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($type == 'reply'){
                $objMsg->setReaded($nMsgId);
                $init['to_uname'] = $init['author'];
                $init['subject'] = "Re:".$init['title'];
                $init['comment'] = '';
                $this->pagedata['is_reply'] = true;
            }
            else{
                $init['subject'] = $init['title'];
            }
            $this->pagedata['init'] = $init;
            $this->pagedata['comment_id'] = $nMsgId;
        }

        $this->output();
    }

    function ajax_send($nMsgId=false,$type='') {
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($type == 'reply'){
                $objMsg->setReaded($nMsgId);
                $init['to_uname'] = $init['author'];
                $init['subject'] = "Re:".$init['title'];
                $init['comment'] = '';
                $this->pagedata['is_reply'] = true;
            }
            else{
                $init['subject'] = $init['title'];
            }
            $this->pagedata['init'] = $init;
            $this->pagedata['comment_id'] = $nMsgId;
        }

        echo $this->fetch('site/member/ajax_send.html');
        exit;
    }

     function ajax_message($nMsgId=false, $status='send') { //给管理员发信件
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            $this->pagedata['init'] = $init;
            $this->pagedata['msg_id'] = $nMsgId;
        }
        if($status === 'reply'){
            $this->pagedata['reply'] = 1;
        }
         echo $this->fetch('site/member/ajax_message.html');
        exit;
    }

    function message($nMsgId=false, $status='send') { //给管理员发信件
        $this->get_msg_num();
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($init['author_id'] == $this->app->member_id)
            {
                $this->pagedata['init'] = $init;
                $this->pagedata['msg_id'] = $nMsgId;
            }
        }
        if($status === 'reply'){
            $this->pagedata['reply'] = 1;
        }
        $this->output();
    }

    function msgtoadmin(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'message'));
        $_POST['msg_to'] = 0;
        if($_POST['subject'] && $_POST['comment']) {
            $objMessage = kernel::single('b2c_message_msg');
            $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
            $_POST['member_id'] = $this->app->member_id;
            $_POST['uname'] = $this->member[uname];
            $_POST['to_type'] = 'admin';
            $_POST['contact'] = $this->member['email'];
            $_POST['ip'] = $_SERVER["REMOTE_ADDR"];
            $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
            $_POST['subject'] = strip_tags($_POST['subject']);
            $_POST['comment'] = strip_tags($_POST['comment']);
            if( $objMessage->send($_POST) ) {
            if($_POST['has_sent'] == 'false'){
                $this->splash('success',$url,app::get('b2c')->_('保存到草稿箱成功！'),'','',true);
            }else{
                $this->splash('success',$url,app::get('b2c')->_('发送成功！'),'','',true);
            }
            } else {
                $this->splash('failed',$url,app::get('b2c')->_('发送失败！！'),'','',true);
            }
        }
        else {
            $this->splash('failed',$url,app::get('b2c')->_('必填项不能为空！！'),'','',true);
        }
    }

    function send_msg(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'inbox'));
        if($_POST['msg_to'] && $_POST['subject'] && $_POST['comment']) {
            $obj_member = &$this->app->model('members');
            if($to_id = $obj_member->get_id_by_uname($_POST['msg_to'])){
                $objMessage = kernel::single('b2c_message_msg');
                $_POST['member_id'] = $this->app->member_id;
                $_POST['uname'] = $this->member[uname];
                $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
                $_POST['to_id'] = $to_id;
                if($_POST['comment_id']){
                    //$data['comment_id'] = $_POST['comment_id'];
                    $_POST['comment_id'] = '';//防止用户修改comment_id
                }
                $_POST['subject'] = strip_tags($_POST['subject']);
                $_POST['comment'] = strip_tags($_POST['comment']);
                if( $objMessage->send($_POST) ) {
                    if($_POST['has_sent'] == 'false'){
                         $this->splash('success','back',app::get('b2c')->_('保存到草稿箱成功！！'));
                    }else{
                         $this->splash('success','back',app::get('b2c')->_('发送成功！！'));
                    }
                 } else {
                     $this->splash('failed','back',app::get('b2c')->_('发送失败！！'));
                 }
            } else {
                $this->splash('failed','back',app::get('b2c')->_('找不到你填写的用户！！'));
            }
        } else {
               $this->splash('failed','back',app::get('b2c')->_('必填项不能为空！！'));

        }
    }

    function security($type = ''){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('修改密码'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $obj_member = &$this->app->model('members');
        $this->pagedata['mem'] = $obj_member->dump($this->app->member_id);
        $this->pagedata['type'] = $type;
        $this->output();
    }

    function save_security(){
       $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'security'));
        $obj_member = &$this->app->model('members');
        $result = $obj_member->save_security($this->app->member_id,$_POST,$msg);
        if($result){
            $this->splash('success',$url,$msg,'','',true);
        }
        else{
            $this->splash('failed',$url,$msg,'','',true);
        }
    }

    function save_security_issue(){
       $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'security'));
        $obj_member = &$this->app->model('members');
       if($obj_member->save_security($this->app->member_id,$_POST,$msg)){
           $this->splash('success',$url,$msg,'','',true);
       }
       else{
           $this->splash('failed',$url,$msg,'','',true);
       }
    }

    function receiver(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('收货地址'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $objMem = &$this->app->model('members');
        $this->pagedata['receiver'] = $objMem->getMemberAddr($this->app->member_id);//print_r($this->pagedata['receiver']);
        $this->pagedata['is_allow'] = (count($this->pagedata['receiver'])<6 ? 1 : 0);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->output();
    }

    function edit_addr(){
        $addrId = $_POST['addrid'];
        if(!$addrId)
        {
            echo  app::get('b2c')->_("参数错误");exit;
        }
        $obj_member = &$this->app->model('members');
        if($obj_member->check_addr($addrId,$this->member['member_id']))
        {
            if($aRet = $obj_member->getAddrById($addrId))
            {
                $aRet['defOpt'] = array('0'=>app::get('b2c')->_('否'), '1'=>app::get('b2c')->_('是'));
                 $this->pagedata = $aRet;
            }else
            {
                $this->_response->set_http_response_code(404);
                $this->_response->set_body(app::get('b2c')->_('修改的收货地址不存在！'));
                exit;
            }
            $this->pagedata['addrid'] = $addrId;
            $this->page('site/member/edit_addr.html',true);
        }
        else
        {
            echo  app::get('b2c')->_("参数错误");exit;
        }
    }


    //添加收货地址
    function add_receiver(){
        $obj_member = &$this->app->model('members');
        if($obj_member->isAllowAddr($this->app->member_id)){
            $this->output();
        }else{
            echo app::get('b2c')->_('不能新增收货地址');
        }
    }

    function insert_rec(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = &$this->app->model('members');
        if(!$obj_member->isAllowAddr($this->app->member_id)){
             $this->splash('failed','',app::get('b2c')->_('不能新增收货地址'),'','',true);
        }
        $aData = $this->check_input($_POST);
        if($obj_member->insertRec($aData,$this->app->member_id,$message)){
             $this->splash('success',$url,$message,'','',true);
            }
        else{
            $this->splash('failed','',$message,'','',true);
        }

    }

    //设置和取消默认地址，$disabled 2为设置默认1为取消默认
    function set_default($addrId=null,$disabled=1){
        if(!$addrId) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'));
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = &$this->app->model('members');
        $member_id = $this->app->member_id;
        if($obj_member->check_addr($addrId,$this->member['member_id']))
        {
            if($obj_member->set_to_def($addrId,$member_id,$message,$disabled))
            {
                $this->splash('success',$url,$message,'','',true);
            }
            else
            {
                $this->splash('failed',$url,$message,'','',true);
            }
        }
        else
        {
            $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        }
    }

    //修改收货地址
    function modify_receiver($addrId=null){
        if(!$addrId)
        {
            echo  app::get('b2c')->_("参数错误");exit;
        }
        $obj_member = &$this->app->model('members');
        if($obj_member->check_addr($addrId,$this->member['member_id']))
        {
            if($aRet = $obj_member->getAddrById($addrId))
            {
                $aRet['defOpt'] = array('0'=>app::get('b2c')->_('否'), '1'=>app::get('b2c')->_('是'));
                 $this->pagedata = $aRet;
            }else
            {
                $this->_response->set_http_response_code(404);
                $this->_response->set_body(app::get('b2c')->_('修改的收货地址不存在！'));
                exit;
            }
            $this->output();
        }
        else
        {
            echo  app::get('b2c')->_("参数错误");exit;
        }
    }

    function save_rec(){
        $back_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = &$this->app->model('members');
        if($obj_member->check_addr($_POST['addr_id'],$this->member['member_id']))
        {
            $aData = $this->check_input($_POST);
            if($obj_member->save_rec($aData,$this->app->member_id,$message)){
                $this->splash('success',$back_url,app::get('b2c')->_('修改成功'),'','',true);
                //echo json_encode(array('status'=>'success', 'msg'=>app::get('b2c')->_('操作成功')));exit;
            }
            else{
                $this->splash('failed',$back_url,$message,'','',true);
                //echo json_encode(array('status'=>'failed', 'msg'=>$message));exit;
            }
        }
        else
        {
            $this->splash('failed',$back_url,app::get('b2c')->_('操作失败'),'','',true);
            //echo json_encode(array('status'=>'failed','msg'=>app::get('b2c')->_('操作失败')));exit;
        }
    }

    //删除收货地址
    function del_rec($addrId=null){
        if(!$addrId) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'));
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = &$this->app->model('members');
        if($obj_member->check_addr($addrId,$this->member['member_id']))
        {
            if($obj_member->del_rec($addrId,$message,$this->member['member_id']))
            {
                $this->splash('success',$url,$message,'','',true);
            }
            else
            {
                $this->splash('failed',$url,$message,'','',true);
            }
        }
        else
        {
            $this->splash('failed', 'back', app::get('b2c')->_('操作失败'),'','',true);
        }


    }

    function score(){
        $this->output();
    }

    function exchange($cpnsId=null) {
        //积分设置的用途
        $site_point_usage = app::get('b2c')->getConf('site.point_usage');
        if($site_point_usage != '1'){
            $this->splash('failed',$url,app::get('b2c')->_('兑换失败,原因:积分只用于抵扣，不能兑换...'),'','',true);
        }
        if(!$cpnsId) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'couponExchange'));
        $oExchangeCoupon = kernel::single('b2c_coupon_mem');
        $memberId = intval($this->app->member_id);//会员id号
        if ($memberId){
            if ($oExchangeCoupon->exchange($cpnsId,$memberId,$this->member['point'],$params)){
                $cpns_point = $params['cpns_point'];
                $member_point = $this->app->model('member_point');
                if($member_point->change_point($this->member['member_id'],-$cpns_point,$msg,'exchange_coupon',2,$memberId,$memberId,'exchange')){
                    $this->splash('success',$url,app::get('b2c')->_('兑换成功'),'','',true);
                }
                else{
                    $oExchangeCoupon->exchange_delete($params);
                    $this->splash('failed',$url,$msg,'','',true);
                }

            }
            }
        else{
            $this->splash('failed',$url,app::get('b2c')->_('没有登录'),'','',true);
        }
        $this->splash('failed',$url,app::get('b2c')->_('兑换失败,原因:积分不足/兑换购物券无效...'),'','',true);
     }

    function download_ddvanceLog(){
        $charset = kernel::single('base_charset');
        $obj_member = &$this->app->model('member_advance');
        $aData = $obj_member->get_list_bymemId($this->app->member_id);
        header('Pragma: no-cache, no-store');
        header("Expires: Wed, 26 Feb 1997 08:21:57 GMT");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=advance_".date("Ymd").".csv");
        $out = app::get('b2c')->_("事件,存入金额,支出金额,当前余额,时间\n");
        foreach($aData as $v){
            $out .= $v['message'].",".$v['import_money'].",".$v['explode_money'].",".$v['member_advance'].",".date("Y-m-d H:i",$v['mtime'])."\n";
        }
        echo $charset->utf2local($out,'zh');
        exit;
    }

    /**
     * 添加留言
     * @params string order_id
     * @params string message type
     */
    public function add_order_msg( $order_id , $msgType = 0 ){
        $objOrder = $this->app->model('orders');
        $aOrder = $objOrder->dump($order_id );

        $timeHours = array();
        for($i=0;$i<24;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeHours[$v] = $v;
        }
        $timeMins = array();
        for($i=0;$i<60;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeMins[$v] = $v;
        }
        $this->pagedata['orderId'] = $order_id;
        $this->pagedata['msgType'] = $msgType;
        $this->pagedata['timeHours'] = $timeHours;
        $this->pagedata['timeMins'] = $timeMins;

        $this->output();
    }

    /**
     * 订单留言提交
     * @params null
     * @return null
     */
    public function toadd_order_msg()
    {
        $this->begin();

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $_POST['to_type'] = 'admin';
        $_POST['author_id'] = $this->member['member_id'];
        $_POST['author'] = $this->member['uname'];
        $is_save = true;
        $obj_order_message = kernel::single("b2c_order_message");
        if ($obj_order_message->create($_POST))
            $this->end(true,app::get('b2c')->_('留言成功!'));
        else
            $this->end(false,app::get('b2c')->_('留言失败!'));
    }

    function point_history($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的积分'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $member = $this->app->model('members');
        $member_point = $this->app->model('member_point');
        $obj_gift_link = kernel::service('b2c.exchange_gift');
        if ($obj_gift_link)
        {
            $this->pagedata['exchange_gift_link'] = $obj_gift_link->gen_exchange_link();
        }
        // 额外的会员的信息 - 冻结积分、将要获得的积分
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_point($this->member['member_id']);
            }
        }
        $data = $member->dump($this->app->member_id,'*',array('score/event'=>array('*')));
        $count = count($data['score']['event']);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $member_point->getList('*',array('member_id' => $this->member['member_id']),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'point_history');
        $this->pagedata['total'] = $data['score']['total'];
        $this->pagedata['historys'] = $params['data'];
        $this->output();
    }

    /*
        过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
    */
    function check_input($data){
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    function arrContentReplace($array){
        if (is_array($array)){
            foreach($array as $key=>$v){
                $array[$key] =     $this->arrContentReplace($array[$key]);
            }
        }
        else{
            $array = strip_tags($array);
        }
        return $array;
    }

    function set_read($comment_id=null,$object_type='ask'){
        if(!$comment_id) return ;
        $comment = kernel::single('b2c_message_disask');
        $comment->type = $object_type;
        $reply_data = $comment->getList('comment_id',array('for_comment_id' => $comment_id));
        foreach($reply_data as $v){
            $comment->setReaded($v['comment_id']);
        }

    }

    function get_msg_num(){
        $this->pagedata['controller'] = "comment";
        $msg = kernel::single('b2c_message_msg');
        if($this->member['member_id']){
            $row = $msg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
            $this->pagedata['inbox_num'] = count($row)?count($row):0;
            $row = $msg->getList('*',array('author_id' => $this->app->member_id,'has_sent' => 'true','track' => 'true'));
            $this->pagedata['track_num'] = count($row)?count($row):0;
             $row = $msg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id));
            $this->pagedata['outbox_num'] = count($row)?count($row):0;
            unset($msg);
        }
        else{
            return null;
        }
    }

    function comment($nPage=1){
        /*
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('评论与咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $comment = kernel::single('b2c_message_disask');
        $goods = $this->app->model('goods');
        $objPoint = $this->app->model('comment_goods_point');
        $aData = $comment->get_member_disask($this->app->member_id,$nPage,'discuss');
        $comment=array();
        $imageDefault = app::get('image')->getConf('image.set');
        $oImage = app::get('image')->model('image');
        foreach((array)$aData['data'] as $k => $v){
                $goods_data = $goods->getList('name,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$v['type_id']));
                if(!$goods_data) continue;
                if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['image_default_id']))){
                    $goods_data[0]['image_default_id'] = $imageDefault['S']['default_image'];
                }
                if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['thumbnail_pic']))) {
                    $goods_data[0]['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
                $v['goods_point'] = $objPoint->get_single_point($v['type_id']);
                $v['name'] = $goods_data[0]['name'];
                $v['thumbnail_pic'] = $goods_data[0]['thumbnail_pic'];
                $v['udfimg'] = $goods_data[0]['udfimg'];
                $v['image_default_id'] = $goods_data[0]['image_default_id'];
                $comment[] = $v;
        }
        $this->pagedata['commentList'] = $comment;
        $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagination($nPage,$aData['page'],'comment');
        $this->output();*/
        
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('评论与咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $objDisask = kernel::single('business_message_disask');
        $objGoods = app::get('business')->model('goods');
        $objPoint = app::get('business')->model('comment_goods_point');
        $aData = $objDisask->get_member_disask($this->app->member_id,$nPage,'discuss');
        $comment=array();
        $imageDefault = app::get('image')->getConf('image.set');
        $oImage = app::get('image')->model('image');
        foreach((array)$aData['data'] as $k => $v){
            $goods_data = $objGoods->getList('name,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$v['type_id']));
            if(!$goods_data) continue;
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['image_default_id']))){
                $goods_data[0]['image_default_id'] = $imageDefault['S']['default_image'];
            }
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['thumbnail_pic']))) {
                $goods_data[0]['thumbnail_pic'] = $imageDefault['S']['default_image'];
            }
            $v['goods_point'] = $objPoint->get_single_point($v['type_id']);
            $v['name'] = $goods_data[0]['name'];
            $v['thumbnail_pic'] = $goods_data[0]['thumbnail_pic'];
            $v['udfimg'] = $goods_data[0]['udfimg'];
            $v['image_default_id'] = $goods_data[0]['image_default_id'];
            $comment[] = $v;
        }
        $this->pagedata['commentList'] = $comment;
        $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagination($nPage,$aData['page'],'comment','','b2c',$ctl='site_member');
        $this->output();
    }

    function ask($nPage=1){

		$this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        // $this->pagedata['controller'] = "comment";
        $comment = kernel::single('b2c_message_disask');
        $goods = $this->app->model('goods');
        $objPoint = $this->app->model('comment_goods_point');
        $aData = $comment->get_member_disask($this->app->member_id,$nPage,'ask');
        $comment=array();
        $imageDefault = app::get('image')->getConf('image.set');
        $oImage = app::get('image')->model('image');
        foreach($aData['data'] as $k => $v){
            if(!$v['type_id']){$comment[] = $v;continue;} 
                $goods_data = $goods->getList('name,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$v['type_id']));
                if(!$goods_data) continue;
                if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['image_default_id']))){
                    $goods_data[0]['image_default_id'] = $imageDefault['S']['default_image'];
                }
                if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['thumbnail_pic']))) {
                    $goods_data[0]['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
                $v['goods_point'] = $objPoint->get_single_point($v['type_id']);
                $v['name'] = $goods_data[0]['name'];
                $v['thumbnail_pic'] = $goods_data[0]['thumbnail_pic'];
                $v['udfimg'] = $goods_data[0]['udfimg'];
                $v['image_default_id'] = $goods_data[0]['image_default_id'];
                $comment[] = $v;
        }
        $this->pagedata['commentList'] = $comment;
        //echo '<pre>';print_r($comment);exit;
        $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagination($nPage,$aData['page'],'ask');
        $this->output();
    }
    function del_view_history(){
        header('Content-Type:text/jcmd; charset=utf-8');
        $obj_obj_view_history = &$this->app->model('goods_view_history');
        if(!$obj_obj_view_history->del_history($this->app->member_id,$_POST['gid'])){
           echo '{error:"'.app::get('b2c')->_('删除失败！').'",_:null}';
        }
        echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null}';
    }
    function view_history($npage=1){
         $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('浏览历史'),'link'=>'#');
         $GLOBALS['runtime']['path'] = $this->path;
         $obj_obj_view_history = &$this->app->model('goods_view_history');
         $aData = $obj_obj_view_history->get_view_history($this->app->member_id,$this->member['member_lv'],$npage);
         $imageDefault = app::get('image')->getConf('image.set');
        $aProduct = $aData['data'];
        $oGoods = app::get('b2c')->model('goods');
        foreach($aProduct as &$value){

            $goods = $oGoods->getList('bn',array('goods_id'=>$value['goods_id']));
            $value['bn'] = $goods[0]['bn'];

            $mgoods = $obj_obj_view_history->getList('last_modify',array('goods_id'=>$value['goods_id'],'member_id'=>$this->app->member_id));
            $value['last_modify'] = $mgoods[0]['last_modify'];
        } 
        $oImage = app::get('image')->model('image');
        foreach($aProduct as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
            }

            if(!$oImage->getList("image_id",array('image_id'=>$v['thumbnail_pic']))) {
                $aProduct[$k]['thumbnail_pic'] = $imageDefault['S']['default_image'];
            }
        }
        $this->pagedata['view_history'] = $aProduct;
        $arr_args = array();
        $this->pagination($aData['curentPage'],$aData['totalpage'],'view_history',$arr_args);
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['current_page'] = $nPage;
        /** 接触收藏的页面地址 **/
        $this->pagedata['fav_ajax_del_goods_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'del_view_history'));

         $this->output();
         
    }
    function del_store_view_history(){
        header('Content-Type:text/jcmd; charset=utf-8');
        $obj_store_history=app::get('business')->model('store_view_history');
        if(!$obj_store_history->del_history($this->app->member_id,$_POST['sid'])){
           echo '{error:"'.app::get('b2c')->_('删除失败！').'",_:null}';
        }
        echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null}';
    }
    function store_view_history($npage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('浏览历史'),'link'=>'#');
         $GLOBALS['runtime']['path'] = $this->path;
         $obj_store_history=app::get('business')->model('store_view_history');
         $aData=$obj_store_history->get_view_history($this->app->member_id,$npage);
         $imageDefault = app::get('image')->getConf('image.set');
        $aStore = $aData['data'];
        $oImage = app::get('image')->model('image');
        foreach($aStore as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                $aStore[$k]['image_default_id'] = $imageDefault['S']['default_image'];
            }
        }
        $this->pagedata['view_history'] = $aStore;
        $this->pagination($aData['currentpage'],$aData['totalpage'],'store_view_history');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['current_page'] = $nPage;
        $this->output();
    }
     ##缺货登记
    function notify($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('到货通知'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $oMem = &$this->app->model('member_goods');
        $aData = $oMem->get_gnotify($this->app->member_id,$this->member['member_lv'],$nPage);
        $this->pagedata['notify'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'notify');
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['setting'] = $setting;
        $this->pagedata['member_id'] = $this->app->member_id;
        $this->output();
    }

    ##删除缺货登记
    function del_notify($pid=null,$member_id=null){
        if(!$pid || !$member_id) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'notify'));
        $member_goods= $this->app->model('member_goods');
        if($member_goods->getList('gnotify_id',array('product_id' => $pid,'member_id' => $this->member['member_id'])))
        {
            if($member_goods->delete(array('product_id'=>$pid,'member_id'=>$this->member['member_id']))){
                $this->splash('success',$url,app::get('b2c')->_('删除成功'),'','',true);
            }
            else{
                $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
            }
        }
        else
        {
            $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
        }

    }

    function coupon($nPage=1) {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的优惠券'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        //$oCoupon = kernel::single('b2c_coupon_mem');
        //$aData = $oCoupon->get_list_m($this->member['member_id']);

        //  增加会员中心优惠券分页
        $pagelimit=10;
        $mobj_coupon = app :: get('b2c') -> model('member_coupon');
        $filter = array('member_id'=>$this->member['member_id']);
        $filter['disabled'] = 'false';
        $filter['memc_isvalid'] = 'true';
        $aData=$mobj_coupon->_get_list('*',$filter,$pagelimit*($nPage-1),$pagelimit);
        $count = $mobj_coupon->count($filter);

        if ($aData) {
            foreach ($aData as $k => $item) {
                if ($item['coupons_info']['cpns_status']==1) {
                    $member_lvs = explode(',',$item['time']['member_lv_ids']);
                    if (in_array($this->member['member_lv'],(array)$member_lvs)) {
                        $curTime = time();
                        if ($curTime>=$item['time']['from_time'] && $curTime<$item['time']['to_time']) {
                            if ($item['memc_used_times']<$this->app->getConf('coupon.mc.use_times')){
                                if ($item['coupons_info']['cpns_status']){
                                     if($item['memc_isactive']=='false'){
                                       $aData[$k]['memc_status'] = app::get('b2c')->_('未激活');
                                    }else{
                                        $aData[$k]['memc_status'] = app::get('b2c')->_('可使用');
                                    }
                                }else{
                                    $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券已作废');
                                }
                            }else{
                                $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券次数已用完');
                            }
                        }else{
                            $aData[$k]['memc_status'] = app::get('b2c')->_('还未开始或已过期');
                        }
                    }else{
                        $aData[$k]['memc_status'] = app::get('b2c')->_('本级别不准使用');
                    }
                }else{
                    $aData[$k]['memc_status'] = app::get('b2c')->_('此种优惠券已取消');
                }
            }
        }
        $this->pagedata['mc_use_times'] = $this->app->getConf('coupon.mc.use_times');
        $this->pagedata['coupons'] = $aData;

        // 增加会员中心优惠券分页
        $this->pagedata['pager'] = array(
            'current'=>$nPage,
            'total'=>ceil($count/$pagelimit) ,
            'link' =>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member','act'=>'coupon','args'=>array(($tmp = time())))),
            'token'=>$tmp,
         );

        $this->output();
    }

    function couponExchange($page=1) {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('积分兑换优惠卷'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $pageLimit = 10;
        $oExchangeCoupon = kernel::single('b2c_coupon_mem');
        $filter = array('ifvalid'=>1);
        $site_point_usage = $this->app->getConf('site.point_usage');
        $this->pagedata['site_point_usage'] = ($site_get_policy_method != '1' && $site_point_usage == '1') ? 'true' : 'false';
        if ($aExchange = $oExchangeCoupon->get_list()) {
            $this->pagedata['couponList'] = $aExchange;
        }
        $this->output();
    }

    function buy($page=1){
        $list_listnum = $this->pagesize;
        $order = $this->app->model('orders');
        $order_items = $this->app->model('order_items');
        $goods = $this->app->model('goods');
        $member_comment = $this->app->model('member_comments');
        $oImage = app::get('image')->model('image');
        if($page == 1 || !$_SESSION['order_goods_data']){
            $row = $order->getList('order_id,createtime',array('member_id' => $this->app->member_id,'createtime|than' => time()-30*24*3600,'pay_status' => 1,'status'=>'finish'));
            $falg = array();
            foreach($row as $val){
            $data = $order_items->getList('goods_id',array('order_id' => $val['order_id']));
                foreach($data as $v){
                    if(!in_array($v['goods_id'],$falg)){
                        $result = current($goods->getList('name,goods_id,thumbnail_pic,udfimg,marketable,view_count,view_w_count,buy_count,buy_w_count,image_default_id,comments_count',array('goods_id'=>$v['goods_id'])));
                        $result['buytime'] = $val['createtime'];
                        if($row = $member_comment->getList('comment_id',array('for_comment_id' => 0,'object_type' => 'discuss','type_id' => $v['goods_id'],'author_id' => $this->app->member_id)))
                        $result['is_discuss'] = 'true';
                        else
                        $result['is_discuss'] = 'false';
                        if(!$oImage->getList("image_id",array('image_id'=>$result['image_default_id']))){
                            $result['image_default_id'] = '';
                        }
                        if(!$oImage->getList("image_id",array('image_id'=>$result['thumbnail_pic']))) {
                            $result['thumbnail_pic'] = '';
                        }
                        // 添加订单号 --start
                        $result['order_id'] = $val['order_id'];
                        //-- end
                        $aData[] = $result;
                    }
                    $falg[] = $v['goods_id'];
                }
            }
            $_SESSION[$this->app->member_id]['order_goods_data'] = $aData;
        }
        $total = count($_SESSION[$this->app->member_id]['order_goods_data'])/$list_listnum;
        $count = count($_SESSION[$this->app->member_id]['order_goods_data']);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start;
        $this->pagination($page,$total,'buy');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['aData'] = array_slice((array)$_SESSION[$this->app->member_id]['order_goods_data'],$start,$list_listnum);
        $this->pagedata['switch_discuss'] = $this->app->getConf('comment.switch.discuss');
        $this->output();
    }

     /**
    * 订单取消
    */
    public function order_cancel($order_id,$act='1'){

        $this->begin(array('app' => 'b2c','ctl' => 'site_member', 'act'=>'orders'));
        $order_obj = &$this->app->model('orders');
        $order=$order_obj->getList('*',array('order_id'=>$order_id,'member_id'=>$this->app->member_id));
        //判断是否可以做取消处理
        if(isset($order)&&!empty($order)){
            //活动订单，未支付，未发货的可以取消
           if($order[0]['status']=='active'&&($order[0]['pay_status']=='0' || $order[0]['pay_status']=='1')&&$order[0]['ship_status']=='0'){
                $rel=$order_obj->update(array('status'=> 'dead'), array('order_id' =>$order_id));
                if($rel){
                     $this->end(true, app::get('b2c')->_('订单取消成功'));
                }else{
                     $this->end(false, app::get('b2c')->_('订单取消失败'));
                }
           }else{
               //ERROR
                $this->end(false, app::get('b2c')->_('该订单不能取消'));
           }
        }else{
            //ERROR
             $this->end(false, app::get('b2c')->_('没有找到该订单'));
        }
    }

     /**
    * 订单的搜素
    * @params order_id：订单号,goods_name：商品名称,goods_bn：商品编号
    * @return array
    */
    private function search_order($order_id,$goods_name,$goods_bn){
        //防止SQL注入
        $order_id = mysql_real_escape_string($order_id);
        $goods_name = mysql_real_escape_string($goods_name);
        $goods_bn = mysql_real_escape_string($goods_bn);

        $sdb = kernel::database()->prefix;
        $strsql="select distinct order_id from ".$sdb."b2c_orders where member_id='".$this->app->member_id."' and order_id in ";

        $strsql.="(select item.order_id from ".$sdb."b2c_order_items as item inner join ".$sdb."b2c_goods goods on item.goods_id=goods.goods_id where 1=1 ";

        if($order_id != ''){
            $strsql.="and item.order_id like '%".$order_id."%'";
        }
        
        if($goods_bn != ''){
            $strsql.="and  goods.bn like '%".$goods_bn."%'";
        }
        
        if($goods_name != ''){
           $strsql.="and goods.name like '%".$goods_name."%' ";
        }
        
        $strsql.=")";
		
        $arr_order_id= $order = &$this->app->model('orders')->db->select($strsql);
		
        return $arr_order_id;
    }

    /**
    * 动态获取选择的时间 
    * @return array
    */
    private function get_select_date(){

       $year = date('Y',time());
       $options = array();
       
       $options['all'] = "全部时间";
       $options['3th'] = "三个月内";
       $options['6th'] = "半年内";
       $options[$year] = "今年内";
       $options['1'] = "1年以前";

       return $options;
    }


    /**
    *根据时间筛选订单
    * @return array
    */
    private function get_search_order_ids($type='',$time=''){

         //解析时间
        $year = date('Y',time());
        $sdb = kernel::database()->prefix;
		
        $time_sql = "";
        $str_sql;
        //三个月内
        if($time == '3th'){
            $time_sql = " createtime<".time()." AND createtime>".strtotime("-3 month");
        //半年内
        }else if($time == '6th'){
            $time_sql = " createtime<".time()." AND createtime>".strtotime("-6 month");
        //今年
        }else if($time == $year){
            $time_sql = " createtime<".time()." AND createtime>".mktime(0,0,0,1,1,$year);
        //一年前
        }else if($time == '1'){
            $time_sql = " createtime<".mktime(0,0,0,12,31,$year-1);
        }else {
            $time_sql = " 1=1 ";
        }

		//type
		$type_sql='';
		if($type == 'nopayed'){
			$type_sql=" pay_status='0' and status='active' ";//待付款
		}else if($type == 'ship'){
			$type_sql=" pay_status='1' and ship_status='0' ";//待发货
		}else if($type == 'shiped'){
			$type_sql=" pay_status='1' and ship_status='1' and status='active'";//待收货
		}else if($type == 'comment'){
			$type_sql="status='finish' and comments_count=0 ";//未评论
		}else if($type == 'finish'){
			$type_sql=" status='finish' ";//已完成
		}else if($type == 'confirm'){
			$type_sql=" pay_status='1' and ship_status='1' and status='active' and confirm='N' ";//待确认
		}else if($type == 'dead'){
			$type_sql=" status='dead' ";//作废
		}else{
			$type_sql=' 1=1 ';
		}

		
        $str_sql = "SELECT order_id FROM ".$sdb."b2c_orders WHERE member_id=".$this->app->member_id;
        
        $str_sql.=" AND ". $time_sql.' AND '.$type_sql;
		
        return $str_sql;

    }

   /**
     * 
     * @method :active_mycoupons
     * @description :激活我的优惠券
     * @author :PanF 
     * @data :2013-5-13
     */
    function active_mycoupons() {
        $coupons = $_POST['code'];

        $item = array();
        $url = $this -> gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'coupon'));
        $member_id = $this -> member['member_id'];
        $coupons = mysql_real_escape_string($coupons);
        $oCoupon = kernel :: single('b2c_coupon_mem'); 
        $aData = $oCoupon -> get_list_m($member_id, 'false'); 
        // 获取似否有该优惠券号
        foreach($aData as $temp) {
            if ($temp['memc_code'] == trim($coupons)) {
                $item = $temp;
                break;
            } 
        } 

        if (!isset($item) || empty($item)) {
            $mdl_coupons = app :: get('b2c') -> model('coupons');  
            $prefix = $mdl_coupons -> getPrefixFromCouponCode(trim($coupons));
            $arr_coupons_info = $mdl_coupons -> getCouponByPrefix($prefix,-1);  
            if (empty($arr_coupons_info)) { 
                // $this->splash('failed',$url,app::get('b2c')->_('您输入的优惠券编号不存在！'));
                $result['status'] = 'failed';
                $result['msg'] = app :: get('b2c') -> _('您输入的优惠券编号不存在！');
                echo json_encode($result);
                exit;
            } else {
                //  解决前缀重复不能校验  start
                //$arr_coupons_info = $arr_coupons_info[0];
                //$flg_valid = $mdl_coupons -> validCheckNum($arr_coupons_info, trim($coupons), $prefix);
                foreach($arr_coupons_info as $couons_info){
                    $flg_valid = $mdl_coupons -> validCheckNum($couons_info, trim($coupons), $prefix);
                    if($flg_valid){
                        $arr_coupons_info = $couons_info;
                        break;
                    }
                }
                //  解决前缀重复不能校验  end

                if ($flg_valid == false) {
                    // $this->splash('failed',$url,app::get('b2c')->_('您输入的优惠券编号不存在！'));
                    $result['status'] = 'failed';
                    $result['msg'] = app :: get('b2c') -> _('您输入的优惠券编号不存在！');
                    echo json_encode($result);
                    exit;
                } else {
                    if (empty($arr_coupons_info['rule_id'])) {
                        // $this->splash('failed',$url,app::get('b2c')->_('本优惠券已作废，不能激活！'));
                        $result['status'] = 'failed';
                        $result['msg'] = app :: get('b2c') -> _('本优惠券已作废，不能激活！');
                        echo json_encode($result);
                        exit;
                    } else {
                        $arr_rule_info = $this -> app -> model('sales_rule_order') -> dump($arr_coupons_info['rule_id'], 'from_time,to_time,member_lv_ids');
                        $curTime = time();
                        if ($curTime < $arr_rule_info['from_time']) {
                            // $this->splash('failed',$url,app::get('b2c')->_('本优惠券活动尚未开始，不能激活！'));
                            $result['status'] = 'failed';
                            $result['msg'] = app :: get('b2c') -> _('本优惠券活动尚未开始，不能激活！');
                            echo json_encode($result);
                            exit;
                        } else if ($curTime > $arr_rule_info['to_time']) {
                            // $this->splash('failed',$url,app::get('b2c')->_('本优惠券已过期，不能激活！'));
                            $result['status'] = 'failed';
                            $result['msg'] = app :: get('b2c') -> _('本优惠券已过期，不能激活！');
                            echo json_encode($result);
                            exit;
                        } else {
                            $aSave = array('memc_code' => trim($coupons),
                                'cpns_id' => $arr_coupons_info['cpns_id'],
                                'member_id' => $member_id,
                                'memc_gen_time' => time(),
                                'expiretime' => '0',
                                'memc_isactive' => 'true',
                                );
                            $arr = $this -> app -> model('member_coupon') -> save($aSave);
                            if ($arr) {
                                // $this->splash('success',$url,app::get('b2c')->_('激活成功！'));
                                $result['status'] = 'success';
                                $result['msg'] = app :: get('b2c') -> _('激活成功！');
                                echo json_encode($result);
                                exit;
                            } else {
                                // $this->splash('failed',$url,app::get('b2c')->_('激活失败！'));
                                $result['status'] = 'failed';
                                $result['msg'] = app :: get('b2c') -> _('激活失败！');
                                echo json_encode($result);
                                exit;
                            } 
                        } 
                    } 
                } 
            } 
        } 

        if ($item['coupons_info']['cpns_status'] == 1) {
            $member_lvs = explode(',', $item['time']['member_lv_ids']);
            if (in_array($this -> member['member_lv'], (array)$member_lvs)) {
                $curTime = time();
                if ($curTime >= $item['time']['from_time'] && $curTime < $item['time']['to_time']) {
                    if ($item['memc_used_times'] < $this -> app -> getConf('coupon.mc.use_times')) {
                        if ($item['coupons_info']['cpns_status']) {
                            // 加入是否激活判断
                            if ($item['memc_isactive'] == 'false') {
                                // 激活
                                $arr = $this -> app -> model('member_coupon') -> update(array('memc_isactive' => 'true'), array('member_id' => $member_id, 'memc_code' => $item['memc_code']));
                                if ($arr) {
                                    // $this->splash('success',$url,app::get('b2c')->_('激活成功！'));
                                    $result['status'] = 'success';
                                    $result['msg'] = app :: get('b2c') -> _('激活成功！');
                                    echo json_encode($result);
                                    exit;
                                } else {
                                    // $this->splash('failed',$url,app::get('b2c')->_('激活失败！'));
                                    $result['status'] = 'failed';
                                    $result['msg'] = app :: get('b2c') -> _('激活失败！');
                                    echo json_encode($result);
                                    exit;
                                } 
                            } else {
                                // $this->splash('failed',$url,app::get('b2c')->_('该优惠券已激活，不能重复激活'));
                                $result['status'] = 'failed';
                                $result['msg'] = app :: get('b2c') -> _('该优惠券已激活，不能重复激活');
                                echo json_encode($result);
                                exit;
                            } 
                            // 加入是否激活判断
                        } else {
                            // $this->splash('failed',$url,app::get('b2c')->_('本优惠券已作废'));
                            $result['status'] = 'failed';
                            $result['msg'] = app :: get('b2c') -> _('本优惠券已作废');
                            echo json_encode($result);
                            exit;
                        } 
                    } else {
                        // $this->splash('failed',$url,app::get('b2c')->_('本优惠券次数已用完'));
                        $result['status'] = 'failed';
                        $result['msg'] = app :: get('b2c') -> _('本优惠券次数已用完');
                        echo json_encode($result);
                        exit;
                    } 
                } else {
                    // $this->splash('failed',$url,app::get('b2c')->_('还未开始或已过期'));
                    $result['status'] = 'failed';
                    $result['msg'] = app :: get('b2c') -> _('还未开始或已过期');
                    echo json_encode($result);
                    exit;
                } 
            } else {
                // $this->splash('failed',$url,app::get('b2c')->_('本级别不准使用'));
                $result['status'] = 'failed';
                $result['msg'] = app :: get('b2c') -> _('本级别不准使用');
                echo json_encode($result);
                exit;
            } 
        } else {
            // $this->splash('failed',$url,app::get('b2c')->_('此种优惠券已取消'));
            $result['status'] = 'failed';
            $result['msg'] = app :: get('b2c') -> _('此种优惠券已取消');
            echo json_encode($result);
            exit;
        } 
    } 
    
	//日用百货
	function merchandise(){
	  $this->set_tmpl_file('index-merchandise.html');
      $this->output();
	}

    public function dofinish($order_id){
        $this->pagedata['order_id'] = $order_id;
        $obj_order = app::get('business')->model('orders');
        $time = $obj_order->dump($order_id,'*');

        $this->pagedata['time'] = ($time['confirm_time'])*1000;
        $this->pagedata['now_time'] = time()*1000;
        $this->pagedata['base_url'] = kernel::base_url();
        $this->pagedata['path'] = app::get('site')->res_full_url;

        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, '*', $subsdf);
        $objMath = kernel::single("ectools_math");

        if($sdf_order['member_id']){
            $member = &$this->app->model('members');
            $aMember = $member->dump($sdf_order['member_id'], 'email');
            $sdf_order['receiver']['email'] = $aMember['contact']['email'];
        }

        // 处理收货人地区
        $arr_consignee_area = array();
        $arr_consignee_regions = array();
        if (strpos($sdf_order['consignee']['area'], ':') !== false)
        {
            $arr_consignee_area = explode(':', $sdf_order['consignee']['area']);
            if ($arr_consignee_area[1])
            {
                if (strpos($arr_consignee_area[1], '/') !== false)
                {
                    $arr_consignee_regions = explode('/', $arr_consignee_area[1]);
                }
            }

            $sdf_order['consignee']['area'] = (is_array($arr_consignee_regions) && $arr_consignee_regions) ? $arr_consignee_regions[0] . $arr_consignee_regions[1] . $arr_consignee_regions[2] : $sdf_order['consignee']['area'];
        }

        // 订单的相关信息的修改
        $obj_other_info = kernel::servicelist('b2c.order_other_infomation');
        if ($obj_other_info)
        {
            foreach ($obj_other_info as $obj)
            {
                $this->pagedata['discount_html'] = $obj->gen_point_discount($sdf_order);
            }
        }
        $sdf_order['isNeedAddress'] = true;
        $sdf_order['isNeedDelivery'] = true;
        if (isset($sdf_order['order_kind']) && $sdf_order['order_kind'] == '3rdparty') {
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if ($processor->goodsKindDetail() == $sdf_order['order_kind_detail'] && $processor->isCustom('order_delivery')) {
                    $sdf_order['isNeedAddress'] = $processor->isNeedAddress();
                    $sdf_order['isNeedDelivery'] = $processor->isNeedDelivery();
                    break;
                }
            }
        }
        
        $this->pagedata['order'] = $sdf_order;

        $order_items = array();
        $gift_items = array();
        $this->get_order_detail_item($sdf_order,'member_order_detail');
        $this->pagedata['order'] = $sdf_order;

        /** 去掉商品优惠 **/
        if ($this->pagedata['order']['order_pmt'])
        {
            foreach ($this->pagedata['order']['order_pmt'] as $key=>$arr_pmt)
            {
                if ($arr_pmt['pmt_type'] == 'goods')
                {
                    unset($this->pagedata['order']['order_pmt'][$key]);
                }
            }
        }
        /** end **/

        // 得到订单留言.
        $oMsg = &kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');

        $this->pagedata['ordermsg'] = $arrOrderMsg;
        $this->pagedata['res_url'] = $this->app->res_url;

        // 生成订单日志明细
        //$oLogs =&$this->app->model('order_log');
        //$arr_order_logs = $oLogs->getList('*', array('rel_id' => $order_id));
        $arr_order_logs = $objOrder->getOrderLogList($order_id);

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        $this->pagedata['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($this->pagedata['order']['payinfo']['pay_app_id']);
        
        //物流跟踪安装并且开启
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        $logisticst_service = kernel::service('b2c_change_orderloglist');
        if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
            $this->pagedata['services']['logisticstack'] = $logisticst_service;
        }
        
        $this->pagedata['orderlogs'] = $arr_order_logs['data'];


        $this->output('b2c');
    }

    function emails(){
        $emailObj = &$this->app->model('member_email');
        $member_email = $emailObj->getList('email_type',array('member_id'=>$this->app->member_id,'status'=>'1'));
        foreach($member_email as $k=>$v){
            $this->pagedata['email'][$v['email_type']] = true;
        }
        
        $messenger = &$this->app->model('member_messenger');
        $action = $messenger->actions();
        foreach($action as $act=>$info){
            $list = $messenger->getSenders($act);
            foreach($list as $msg){
                $this->pagedata['call'][$act][$msg] = true;
            }
        }

        $this->output('b2c');
    }

    function saveEmail(){
        $this->begin(array('app' => 'b2c','ctl' => 'site_member', 'act'=>'emails'));
        $emailObj = &$this->app->model('member_email');
        $member_email = $emailObj->getList('email_type',array('member_id'=>$this->app->member_id,'status'=>'1'));
        $emailType = array();
        foreach($member_email as $k=>$v){
            $emailType[$v['email_type']] = $v['email_type'];
        }
        if(isset($_POST['email'])){
            $memberEmail = array();
            foreach($_POST['email'] as $k=>$v){
                $memberEmail[] = $k;
                if(!in_array($k,$emailType)){
                    $data = array(
                            'member_id'=>$this->app->member_id,
                            'email_type'=>$k,
                            'status'=>'1'
                        );
                    if(!$emailObj->insert($data)){
                        $this->end(false, app::get('b2c')->_('保存失败'));
                    }
                }
            }
            foreach($emailType as $k=>$v){
                if(in_array($v,$memberEmail)){
                    unset($emailType[$v]);
                }
            }

        }
        if(!empty($emailType)){
            foreach($emailType as $k=>$v){
                if(!$emailObj->delete(array('member_id'=>$this->app->member_id,'email_type'=>$v,'status'=>'1'))){
                    $this->end(false, app::get('b2c')->_('保存失败'));
                }
            }
        }
        $this->end(true, app::get('b2c')->_('保存成功'));
    }

    /**
     * 订单取消
     * @params string order id
     * @return null
     */
    public function docancel()
    {
        /*$form = $_POST['from']?$_POST['from']:'seller';
        if($from == 'seller'){
            $this->begin(array('app' =>'business','ctl'=>'site_member','act' =>'seller_order'));
        }else{
            $this->begin(array('app' =>'b2c','ctl'=>'site_member','act' =>'orders'));
        }*/
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($_POST['order_id'],'',$message))
        {
           echo json_encode($message);
           exit;
        }
        
        $sdf['order_id'] = $_POST['order_id'];
        $sdf['op_id'] = $this->app->member_id;
        //获取用户名
        $obj_account = app::get('pam')->model('account');
        $login_name = $obj_account->dump($this->app->member_id,'login_name');
        $sdf['opname'] = $login_name['login_name'];
        $b2c_order_cancel = kernel::single("b2c_order_cancel");
        if ($b2c_order_cancel->generate($sdf, $this, $message))
        {
            //ajx crm
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $req_arr['order_id']=$_POST['order_id'];
            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
          
            $order_id = $_POST['order_id'];
            $orderObj = app::get('b2c')->model('orders');
            $orderItemObj = app::get('b2c')->model('order_items');
            $order_info = $orderObj->dump(array('order_id'=>$order_id),'act_id,order_type,itemnum');
            switch($order_info['order_type']){
                case 'group':
                    $buyMod = app::get('groupbuy')->model('memberbuy');
                    $applyObj = app::get('groupbuy')->model('groupapply');
                    $apply = $applyObj->dump(array('id'=>$order_info['act_id']),'aid,gid,remainnums,nums');
                    if($apply){
                      $buyMod->update(array('effective'=>'false'),array('order_id'=>$order_id));
                    }
                    break;
                case 'spike':
                    $buyMod = app::get('spike')->model('memberbuy');
                    $applyObj = app::get('spike')->model('spikeapply');
                    $apply = $applyObj->dump(array('id'=>$order_info['act_id']),'aid,gid,remainnums,nums');
                    if($apply){
                      $buyMod->update(array('effective'=>'false'),array('order_id'=>$order_id));
                    }
                    break;
                case 'score':
                    $buyMod = app::get('scorebuy')->model('memberbuy');
                    $applyObj = app::get('scorebuy')->model('scoreapply');
                    $apply = $applyObj->dump(array('id'=>$order_info['act_id']),'aid,gid,remainnums,nums');
                    if($apply){
                      $buyMod->update(array('effective'=>'false'),array('order_id'=>$order_id));
                    }
                    break;
                case 'timedbuy':
                    $buyMod = app::get('timedbuy')->model('memberbuy');
                    $businessMod = app::get('timedbuy')->model('businessactivity');
                    $buys = $buyMod->getList('*',array('order_id'=>$order_id));
                    if($buys){
                      $business = $businessMod->getList('*',array('gid'=>$buys[0]['gid'],'aid'=>$buys[0]['aid']));
                      $buyMod->update(array('disable'=>'true'),array('order_id'=>$order_id));
                      if($business[0]['nums']){
                          $arr['remainnums'] = intval($business[0]['remainnums'])+intval($buys[0]['nums']);
                          $businessMod->update($arr,array('id'=>$business[0]['id']));
                      }
                    }
                    break;
            }
            
            //end
            echo json_encode('订单取消成功！');
        }
        else
        {
            echo json_encode('订单取消失败！');
        }
    }

    /**
     * 订单完成
     * @params string oder id
     * @return boolean 成功与否
     */
    public function gofinish()
    {
        $this->begin($this->gen_url(array('app' =>'b2c','ctl'=>'site_member','act' =>'orders')));
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_finish($_POST['order_id'],'',$message))
        {
            $this->end(false, $message);
        }

        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        
        $sdf['order_id'] = $_POST['order_id'];
        $arrMember = $this->get_current_member();
        $sdf['op_id'] = $arrMember['member_id'];
        $sdf['opname'] = $arrMember['uname'];
        $sdf['confirm_time'] = time();
        
        $b2c_order_finish = kernel::single("b2c_order_finish");

        $system_money_decimals = $this->app->getConf('system.money.decimals');
        $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');

        if ($b2c_order_finish->generate($sdf, $this, $message))
        {
            //生成结算单
            $obj_order = &$this->app->model('orders');
            $money = $obj_order->getRow('payed,pmt_order,cost_freight,is_protect,cost_protect,cost_payment,member_id,ship_status,score_u,score_g,discount_value',array('order_id'=>$_POST['order_id']));
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

            $refunds = app::get('ectools')->model('refunds');
            unset($sdf['inContent']);
            
            $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');
            $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

            $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);

            $time = time();
            $sdf['pay_app_id'] = $sdf['payment'];
            $sdf['member_id'] = $sdf_order['store_id'] ? $sdf_order['store_id'] : 0;
            $sdf['currency'] = $sdf_order['currency'];
            $sdf['paycost'] = 0;
            //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
            $sdf['t_begin'] = $time;
            $sdf['t_payed'] = $time;
            $sdf['t_confirm'] = $time;
            $sdf['pay_object'] = 'order';
            
            $return_product_obj = app::get('aftersales')->model('return_product');
            $returns = $return_product_obj->getList('amount',array('order_id'=>$sdf['order_id'],'refund_type|in'=>array('3','4'),'status'=>'3'));
            if($returns[0]['amount']){
                //部分退款的确认收货
                if($money['is_protect']){
                    $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect']-$returns[0]['amount'];
                }else{
                    $cost_freight = $money['cost_freight']+$money['cost_payment']-$returns[0]['amount'];
                }
                if($money['discount_value'] > 0){
                    $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight+$money['discount_value'];
                }else{
                    $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight;
                }
                $obj_items = $this->app->model('order_items');
                $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                //退款金额小于运费
                if($cost_freight >= 0){
                    $profit = 0;
                    foreach($items as $k=>$v){
                        $obj_cat = $this->app->model('goods_cat');
                        $obj_goods = $this->app->model('goods');
                        $cat_id = $obj_goods->dump($v['goods_id'],'cat_id');
                        if(app::get('b2c')->getConf('member.isprofit') == 'true'){
                            $profit_point = $obj_cat->dump($cat_id['category']['cat_id'],'profit_point');
                            if(is_null($profit_point['profit_point'])){
                                $parent_id = $obj_cat->dump($cat_id['category']['cat_id'],'parent_id');
                                $profit_point = $obj_cat->dump($parent_id['parent_id'],'profit_point');
                            }
                        }else{
                            $profit_point['profit_point'] = 0;
                        }
                        if($total_money>0){
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                        }else{
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                        }
                    }
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    $profit = $profit + $cost_freight*($freight_pro/100);
                }else{
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    if($money['discount_value'] > 0){
                        $total_money = ($money['payed']+($money['discount_value']))*($freight_pro/100);
                    }else{
                        $total_money = ($money['payed'])*($freight_pro/100);
                    }
                }
                //计算系统价格 
                $math = kernel::single("ectools_math");
                $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                if($money['discount_value'] > 0){
                    $sdf['money'] = ($money['payed']+($money['discount_value']))-$profit;
                }else{
                    $sdf['money'] = ($money['payed'])-$profit;
                }

                if($money['score_g'] > 0){
                    $sdf['money'] = $sdf['money']-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                }
                
                //end
                $sdf['return_score'];

                $refunds = app::get('ectools')->model('refunds');
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                
                $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['op_id'] = $money['member_id'];
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '2';
                $obj_ys = app::get('business')->model('storemanger');
                $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                $sdf['account'] = $ys['company_name'];
                $sdf['profit'] = $profit;
                
                if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                     $this->end(false, $message);
                }
                $obj_refunds = kernel::single("ectools_refund");
                $rs_seller = $obj_refunds->generate($sdf, $this, $msg);

                // 增加经验值
                $obj_member = $this->app->model('members');
                $obj_member->change_exp($money['member_id'], floor($total_money));
            }elseif($money['ship_status'] == '3'){
                //部分退货的确认收货
                $obj_items = $this->app->model('order_items');
                $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                
                $payed = 0;
                foreach($items as $k=>$v){
                    $payed = $payed+$v['price']*$v['sendnum'];
                }
                $payed = $payed - $money['pmt_order'];
                //剩余可打金额
                $return_product_obj = app::get('aftersales')->model('return_product');
                $amount = $return_product_obj->getRow('amount',array('order_id'=>$sdf['order_id'],'status'=>'6'));
                if($money['discount_value'] > 0){
                    $money_useful = ($money['payed'])+($money['discount_value']);
                }else{
                    $money_useful = ($money['payed']);
                }
                //剩余杂费
                $cost_freight = $money_useful - $payed;

                $total_money = $payed+$money['pmt_order'];

                $profit = 0;
                foreach($items as $k=>$v){
                    $obj_cat = $this->app->model('goods_cat');
                    $obj_goods = $this->app->model('goods');
                    $cat_id = $obj_goods->dump($v['goods_id'],'cat_id');
                    if(app::get('b2c')->getConf('member.isprofit') == 'true'){
                        $profit_point = $obj_cat->dump($cat_id['category']['cat_id'],'profit_point');
                        if(is_null($profit_point['profit_point'])){
                            $parent_id = $obj_cat->dump($cat_id['category']['cat_id'],'parent_id');
                            $profit_point = $obj_cat->dump($parent_id['parent_id'],'profit_point');
                        }
                    }else{
                        $profit_point['profit_point'] = 0;
                    }
                    if($total_money>0){
                        $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                    }else{
                        $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                    }
                }
                $freight_pro = app::get('b2c')->getConf('member.profit');
                $profit = $profit + $cost_freight*($freight_pro/100);

                //计算系统价格 
                $math = kernel::single("ectools_math");
                $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                if($money['score_g'] > 0){
                    $sdf['money'] = $money_useful-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                }else{
                    $sdf['money'] = $money_useful-$profit;
                }
                //end

                $sdf['return_score'];

                $refunds = app::get('ectools')->model('refunds');
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                
                $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['op_id'] = $money['member_id'];
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '2';

                $obj_ys = app::get('business')->model('storemanger');
                $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                $sdf['account'] = $ys['company_name'];
                $sdf['profit'] = $profit;

                if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                     $this->end(false, $message);
                }

                $obj_refunds = kernel::single("ectools_refund");
                $rs_seller = $obj_refunds->generate($sdf, $this, $msg);

                // 增加经验值
                $obj_member = $this->app->model('members');
                $obj_member->change_exp($money['member_id'], floor($total_money));
            }else{
                //进行提成计算（正常流程）
                if($money['is_protect']){
                    $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect'];
                }else{
                    $cost_freight = $money['cost_freight']+$money['cost_payment'];
                }
                if($money['discount_value'] > 0){
                    $total_money = $money['payed']+$money['pmt_order']-$cost_freight+($money['discount_value']);
                }else{
                    $total_money = $money['payed']+$money['pmt_order']-$cost_freight;
                }
                $obj_items = $this->app->model('order_items');
                $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));

                $profit = 0;
                foreach($items as $k=>$v){
                    $obj_cat = $this->app->model('goods_cat');
                    $obj_goods = $this->app->model('goods');
                    $cat_id = $obj_goods->dump($v['goods_id'],'cat_id');
                    if(app::get('b2c')->getConf('member.isprofit') == 'true'){
                        $profit_point = $obj_cat->dump($cat_id['category']['cat_id'],'profit_point');
                        if(is_null($profit_point['profit_point'])){
                            $parent_id = $obj_cat->dump($cat_id['category']['cat_id'],'parent_id');
                            $profit_point = $obj_cat->dump($parent_id['parent_id'],'profit_point');
                        }
                    }else{
                        $profit_point['profit_point'] = 0;
                    }
                    if($total_money>0){
                        $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                    }else{
                        $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                    }
                }
                $freight_pro = app::get('b2c')->getConf('member.profit');
                $profit = $profit + $cost_freight*($freight_pro/100);

                //计算系统价格 
                $math = kernel::single("ectools_math");
                $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                if($money['discount_value'] > 0 && $money['score_g'] > 0){
                    $sdf['money'] = $money['payed']+($money['discount_value'])-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                }elseif($money['discount_value'] > 0 && $money['score_g'] == 0){
                    $sdf['money'] = $money['payed']+($money['discount_value'])-$profit;
                }elseif($money['discount_value'] == 0 && $money['score_g'] > 0){
                    $sdf['money'] = $money['payed']-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                }else{
                    $sdf['money'] = $money['payed']-$profit; 
                }
                //end

                $sdf['return_score'];

                $refunds = app::get('ectools')->model('refunds');
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                
                $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['op_id'] = $money['member_id'];
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '2';
                $obj_ys = app::get('business')->model('storemanger');
                $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                $sdf['account'] = $ys['company_name'];
                $sdf['profit'] = $profit;

                if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                     $this->end(false, $message);
                }

                $obj_refunds = kernel::single("ectools_refund");
                $rs_seller = $obj_refunds->generate($sdf, $this, $msg);

                // 增加经验值
                $obj_member = $this->app->model('members');
                $obj_member->change_exp($money['member_id'], floor($total_money));
            }
            
            //$this->updateRank($sdf['order_id']); 
            //将款项打给卖家
            if($rs_seller){
                $refund = app::get('ectools')->model('refunds');
                $refund_data = $refund->dump($refund_id,'*');
                $bill = app::get('ectools')->model('order_bills');
                $rel_order_id = $bill->dump(array('bill_id'=>$refund_id),'rel_id');
                if($refund_data['refund_type'] == '2' && $refund_data['status'] == 'ready'){
                    if($refund_data['pay_app_id'] == 'ysepay'){
                        //判断时候需要调用银盛代付接口
                        if($refund_data['cur_money'] == 0){
                            $result['0'] = "true";
                        }else{
                            foreach( kernel::servicelist('ysepay_tools') as $services ) {
                                if ( is_object($services)) {
                                    if ( method_exists($services, 'amount_transfer') ) {

                                        $sz_payer = unserialize(app::get('ectools')->getConf('ysepay_payment_plugin_ysepay'));
                                        $payer['payerName'] = urlencode($sz_payer['setting']['src_name']);
                                        $payer['payerUserCode'] = $sz_payer['setting']['member_id'];
                                        $src = $sz_payer['setting']['member_id'];

                                        $payee['payeeName'] = urlencode($ys['company_name']);
                                        $payee['payeeUserCode'] = $ys['ysusercode'];
                                        //转账信息
                                        $amount = $refund_data['cur_money'];//转账金额
                                        $out_order_id=$rel_order_id['rel_id'];//代付单号，唯一

                                        $result = $services->amount_transfer($src,$payer,$payee,$amount,$out_order_id,$rel_order_id['rel_id']);
                                    }
                                }
                            }
                        }
                        if($result['0'] == "true"){
                            $obj_refunds = kernel::single("ectools_refund");
                            $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>'2'));
               
                            if ($ref_rs)
                            {
                                $this->end(true, '确认收货成功！');

                            }else{
                                $this->end(true, '确认收货成功！');
                            }
                        }else{
                            $refund->update(array('memo'=>$result['1']),array('refund_id'=>$refund_id));
                            $this->end(true, '确认收货成功！'.$result['1']);
                        }
                    }else{
                        $obj_refunds = kernel::single("ectools_refund");
                        $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>'2'));
           
                        if ($ref_rs)
                        {
                            $this->end(true, '确认收货成功！');

                        }else{
                            $this->end(false, '确认收货成功！更新结算单状态失败！');
                        }
                    }
                }else{
                    $this->end(false, '结算单类型错误！');
                }
            }
            
        }
        else
        {
            $this->end(false, app::get('b2c')->_('确认收货失败！'));
        }
    }
    

    public function updateRank($order_id=0){
        if(!$order_id) return true;
        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('obj_id',array('order_items'=>array('goods_id,nums',array(':goods'=>array('goods_id,count_stat'))))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, 'order_id', $subsdf);
        if(!$sdf_order['order_id']) return true;
        $objGoods = &$this->app->model('goods');
        $weekMark = 'buy';
        $item = 'buy_count';
        foreach((array)$sdf_order['order_objects'] as $objects){
            if(!$objects['obj_id']) continue;
            foreach((array)$objects['order_items'] as $items){
                if(!isset($items['goods']['goods_id']) || $items['goods']['goods_id'] != $items['goods_id']) continue;
                $count_stat = unserialize($items['goods']['count_stat']);
                $dayNum = $objGoods->day(time());
                $dayBegin = floor(mktime(0,0,0,date('m'),01,date('Y'))/86400);
                $dayEnd = $dayBegin + date('t', mktime(0,0,0,date('m'),date('d'),date('Y')));
                $weekNum = $num = intval($items['quantity']);
                if(isset($count_stat[$weekMark])){
                    foreach($count_stat[$weekMark] as $day => $countNum){
                        if($dayNum > $day+9) unset($count_stat[$weekMark][$day]);
                        if($dayNum < $day+8) $weekNum += $countNum;
                    }
                }
                $count_stat[$weekMark][$dayNum] += $num;
                $sqlCol = '';
                $monthMark = 'mbuy'; 
                $monthNum = $num;
                if(isset($count_stat[$monthMark])){
                    foreach($count_stat[$monthMark] as $day => $countNum){
                        //if($dayBegin>$day || $dayEnd<$day) unset($count_stat[$monthMark][$day]);
                        //else $monthNum += $countNum;
                        if($dayNum > $day+32) unset($count_stat[$monthMark][$day]);
                        if($dayNum < $day+30) $monthNum += $countNum;
                    }
                }
                $count_stat[$monthMark][$dayNum] += $num;
                $sqlCol .= ','.$weekMark.'_m_count='.intval($monthNum);
                $objStore = app::get('business')->model('storemanger');
                $sql =" update sdb_business_storemanger as s inner join ".
                  " (select sum(buy_m_count)+".intval($num)." as _count,store_id  from sdb_b2c_goods where store_id in (select store_id from sdb_b2c_goods where goods_id=".intval($items['goods_id']).") group by store_id) as c on s.store_id=c.store_id ".
                  " set s.buy_m_count=c._count ";
                $objGoods->db->exec($sql);
                $sqlCol .= ','.$weekMark.'_w_count='.intval($weekNum).', count_stat=\''.serialize($count_stat).'\'';
                $objGoods->db->exec("UPDATE sdb_b2c_goods SET ".$item." = ".$item."+".intval($num).$sqlCol." WHERE goods_id =".intval($items['goods_id']));
            }
        }
        $orderData = $objGoods->db->selectrow('SELECT o.member_id, m.login_name,o.ship_email FROM sdb_b2c_orders o LEFT JOIN sdb_pam_account m ON o.member_id = m.account_id WHERE o.order_id = '.$objGoods->db->quote($order_id));
        $orderItem = $objGoods->db->select('SELECT i.price, p.goods_id, i.product_id, p.name,p.spec_info, i.nums FROM sdb_b2c_order_items i LEFT JOIN sdb_b2c_products p ON p.product_id = i.product_id WHERE i.order_id = '.$objGoods->db->quote($order_id));
        foreach( $orderItem as $iKey => $iValue ){
            $sql = 'INSERT INTO sdb_b2c_sell_logs (member_id,name,price,goods_id,product_id,product_name,spec_info,number,createtime) VALUES ( "'.($orderData['member_id']?$orderData['member_id']:0).'", "'.($orderData['login_name']?$orderData['login_name']:$orderData['ship_email']).'", "'.$iValue['price'].'", "'.$iValue['goods_id'].'", "'.$iValue['product_id'].'", "'.htmlspecialchars($iValue['name']).'", "'.$iValue['spec_info'].'" , "'.$iValue['nums'].'", "'.time().'" )';
            $objGoods->db->exec($sql);
        }
    }
   

    public function extend_finish_apl($order_id){
        $obj_orders=app::get('b2c')->model('orders');
        $obj_members=app::get('b2c')->model('members');
        $obj_storemanger=app::get('business')->model('storemanger');
        $order_info = $obj_orders->getRow('store_id,member_id',array('order_id'=>$order_id));
        $menber_id = $obj_storemanger->getRow('account_id',array('store_id'=>$order_info['store_id']));
        $uname = $obj_members->getRow('name',array('member_id'=>$order_info['member_id']));
        //echo "<pre>";print_r($order_info);exit;
        $data['uname'] = 'demo';
        $data['order_id'] = $order_id;
        $id = $menber_id['account_id'];        
        $obj_orders->fireEvent('extend',$data,$id);
        $rs = $obj_orders->update(array('is_extend'=>'1'),array('order_id'=>$order_id));
      
        if($this->app->getConf('webcall.ordernotice.enabled') == 'true'){
            $webcall_service = kernel::service('api.b2c.webcall');
            if($webcall_service && method_exists($webcall_service, 'orderNotice')){
                $result = $webcall_service->orderNotice($order_id,2);
            }
        }
       
        $this->splash('success', $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'orders')),'申请成功，请等待卖家操作！');
    }

    /**
     * 合并付款
     * @params string order id
     * @params boolean 支付方式的选择
     */
    public function all_orderPayments($selecttype=false,$all_orders='',$from_orders='')
    {
        $total_amount = 0;
        if($from_orders != ''){
            $tatalOrders = explode('|',base64_decode($from_orders));
            $orders = implode(',',$tatalOrders);
            $this->pagedata['all_orders'] = $orders;
        }elseif($selecttype){
            $selecttype = 1;
            $this->pagedata['all_orders'] = $all_orders;
            $tatalOrders = explode(',',$all_orders);
        }else{
            $selecttype = 0;
            $orders = implode(',',$_POST['order']);
            $this->pagedata['all_orders'] = $orders;
            $tatalOrders = $_POST['order'];
        }
        
        $this->pagedata['order']['selecttype'] = $selecttype;
        foreach($tatalOrders as $key1=>$order_id){
            $objOrder = &$this->app->model('orders');
            $sdf = $objOrder->dump($order_id);
            $objMath = kernel::single("ectools_math");
            if(!$sdf){
                exit;
            }
            $sdf['total'] = $sdf['cur_amount'];
            $sdf['cur_amount'] = $objMath->number_minus(array($sdf['cur_amount'], $sdf['payed']));
            $sdf['total_amount'] = $objMath->number_div(array($sdf['cur_amount'], $sdf['cur_rate']));

            $this->pagedata['orders'][$key1]['order'] = $sdf;
            // 货到付款不能进入此页面
            if ($sdf['payinfo']['pay_app_id'] == '-1')
            {
                $this->begin(array('app' => 'b2c','ctl' => 'site_member', 'act'=>'orderdetail', 'arg0'=>$order_id));
                $this->end(false, app::get('b2c')->_('配送方式只支持货到付款'));
            }

            $opayment = app::get('ectools')->model('payment_cfgs');
            $this->pagedata['payments'] = $opayment->getListByCode($sdf['currency']);

            $system_money_decimals = $this->app->getConf('system.money.decimals');
            $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');
            foreach ($this->pagedata['payments'] as $key=>&$arrPayments)
            {
                if (!$sdf['member_id'])
                {
                    if (trim($arrPayments['app_id']) == 'deposit')
                    {
                        unset($this->pagedata['payments'][$key]);
                        continue;
                    }
                }

                if ($arrPayments['app_id'] == $this->pagedata['orders'][$key1]['order']['payinfo']['pay_app_id'])
                {
                    $arrPayments['cur_money'] = $objMath->formatNumber($this->pagedata['orders'][$key1]['order']['cur_amount'], $system_money_decimals, $system_money_operation_carryset);
                    $arrPayments['total_amount'] = $objMath->formatNumber($this->pagedata['orders'][$key1]['order']['total_amount'], $system_money_decimals, $system_money_operation_carryset);
                }
                else
                {
                    $arrPayments['cur_money'] = $this->pagedata['orders'][$key1]['order']['cur_amount'];
                    $cur_discount = $objMath->number_multiple(array($sdf['discount'], $this->pagedata['orders'][$key1]['order']['cur_rate']));
                    if ($this->pagedata['orders'][$key1]['order']['payinfo']['cost_payment'] > 0)
                    {
                        if ($this->pagedata['orders'][$key1]['order']['cur_amount'] > 0)
                            $cost_payments_rate = $objMath->number_div(array($arrPayments['cur_money'], $objMath->number_plus(array($this->pagedata['orders'][$key1]['order']['cur_amount'], $this->pagedata['orders'][$key1]['order']['payed']))));
                        else
                            $cost_payments_rate = 0;
                        $cost_payment = $objMath->number_multiple(array($objMath->number_multiple(array($this->pagedata['orders'][$key1]['order']['payinfo']['cost_payment'], $this->pagedata['orders'][$key1]['order']['cur_rate'])), $cost_payments_rate));
                        $arrPayments['cur_money'] = $objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                        $arrPayments['cur_money'] = $objMath->number_minus(array($arrPayments['cur_money'], $cost_payment));
                        $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']))));
                        $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                    }
                    else
                    {
                        $arrPayments['cur_money'] = $objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                        $cost_payment = $objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']));
                        $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $cost_payment));
                        $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                    }

                    $arrPayments['total_amount'] = $objMath->formatNumber($objMath->number_div(array($arrPayments['cur_money'], $this->pagedata['orders'][$key1]['order']['cur_rate'])), $system_money_decimals, $system_money_operation_carryset);
                    $arrPayments['cur_money'] = $objMath->formatNumber($arrPayments['cur_money'], $system_money_decimals, $system_money_operation_carryset);
                }
            }

            $objCur = app::get('ectools')->model('currency');
            $aCur = $objCur->getFormat($this->pagedata['orders'][$key1]['order']['currency']);
            $this->pagedata['orders'][$key1]['order']['cur_def'] = $aCur['sign'];

            $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result'));
            $this->pagedata['res_url'] = $this->app->res_url;
            $this->pagedata['form_action'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'all_dopayment','arg0'=>'order'));
            $obj_order_payment_html = kernel::servicelist('b2c.order.pay_html');
            $app_id = 'b2c';
            if ($obj_order_payment_html)
            {
                foreach ($obj_order_payment_html as $obj)
                {
                    $obj->gen_data($this, $app_id);
                }
            }

            if ($sdf['cur_amount'] == '0')
            {
                // 模拟支付流程
                $objPay = kernel::single("ectools_pay");
                $sdffds = array(
                    'payment_id' => $objPay->get_payment_id(),
                    'order_id' => $sdf['order_id'],
                    'rel_id' => $sdf['order_id'],
                    'op_id' => $sdf['member_id'],
                    'pay_app_id' => $sdf['payinfo']['pay_app_id'],
                    'currency' => $sdf['currency'],
                    'payinfo' => array(
                        'cost_payment' => $sdf['payinfo']['cost_payment'],
                    ),
                    'pay_object' => 'order',
                    'member_id' => $sdf['member_id'],
                    'op_name' => $this->user->user_data['account']['login_name'],
                    'status' => 'ready',
                    'cur_money' => $sdf['cur_amount'],
                    'money' => $sdf['total_amount'],
                );
                $is_payed = $objPay->gopay($sdffds, $msg);
                if (!$is_payed){
                    $msg = app::get('b2c')->_('订单自动支付失败！');
                    $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')));
                }

                $obj_pay_lists = kernel::servicelist("order.pay_finish");
                $is_payed = false;
                foreach ($obj_pay_lists as $order_pay_service_object)
                {
                    $is_payed = $order_pay_service_object->order_pay_finish($sdffds, 'succ', 'font',$msg);
                }
            }

            $total_amount = $total_amount + $this->pagedata['orders'][$key1]['order']['total_amount'];
        }
        //print_r($total_amount);exit;
        //begin 获取银行信息
		$bankInfo = kernel::single('b2c_banks_info')->getBank();
		$this->pagedata['bankinfo'] = $bankInfo;
		//	echo '<pre>';print_r($this->pagedata);exit;
        $this->pagedata['total_amount'] = $total_amount;
        $this->pagedata['payments_nums'] = count($this->pagedata['payments']);
        if($from_orders){
            //$this->set_tmpl('order_index');
            $this->page('site/member/all_orderPayments.html',false,'b2c');
        }elseif($selecttype == '1'){
            $this->page('site/member/all_orderPayments.html',false,'b2c');
        }else{
            $str_html = $this->fetch('site/member/all_orderPayments.html','b2c');
            echo '{success:"'.app::get('b2c')->_($obj.'成功！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
        }
    }

    public function check_payments(){
        $tatalOrders = explode(',',$_POST['order_id']);
        $obj_order = app::get('b2c')->model('orders');
        $tag = '1';
        foreach($tatalOrders as $key=>$val){
            $pay_id = $obj_order->getRow('payment,pay_status',array('order_id'=>$val));
            if($key == '0'){
                $payment = $pay_id['payment'];
            }
            if($payment != $pay_id['payment'] || $pay_id['pay_status'] != '0'){
                $tag = '0';
            }
        }
        echo $tag;
    }

    public function js_function_do_finish(){
        //进入页面是需要调用订单操作脚本
        kernel::single('b2c_orderautojob')->order_do_finish($_POST['order_id']);
       
    }

    public function js_function_do_refund_cancel(){
        //进入页面是需要调用订单操作脚本
        kernel::single('b2c_orderautojob')->order_do_refund_cancel($_POST['return_id']);
        
    }

    public function js_function_order_do_refund_atuo_cancel(){
        //进入页面是需要调用订单操作脚本
        kernel::single('b2c_orderautojob')->order_do_refund_atuo_cancel($_POST['return_id'],$_POST['order_id']);
        
    }

    //发送发货提醒
    public function remind(){
        if($_POST['order_id']){
           
            if($this->app->getConf('webcall.ordernotice.enabled') == 'true'){
                $webcall_service = kernel::service('api.b2c.webcall');
                if($webcall_service && method_exists($webcall_service, 'orderNotice')){
                    $result = $webcall_service->orderNotice($_POST['order_id'],1);
                }
            }
            //调用订单通知接口 end
            echo json_encode("提醒成功！");
        }else{
            echo json_encode("提醒失败！");
        }
    }

    function my_entity($nPage=1){
        $obj_entity_goods = $this->app->model('entity_goods');
        $obj_items = $this->app->model('goods_entity_items');
        $entityGoods = $obj_entity_goods->getList('*',array('member_id'=>$this->app->member_id));
        $count = count($entityGoods);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $obj_entity_goods->getList('*',array('member_id' => $this->app->member_id),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'my_entity');
        $obj_goods = $this->app->model('goods');
        foreach($params['data'] as $key=>$val){
            $info = $obj_items->dump(array('items_id'=>$val['item_id']),'card_id,card_psw,goods_id');
            $params['data'][$key]['goods_name'] = $obj_goods->dump($info['goods_id'],'name');
            $params['data'][$key]['card'] = $info;
        }
        $this->pagedata['entityGoods'] = $params['data'];
        //echo "<pre>";print_r($entityGoods);exit;
        $this->output('b2c');
    }
    
    public function my_point($nPage=1)
	{
		$this->path[] = array('title'=>app::get('business')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('我的积分'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
		
        $member = app::get('b2c')->model('members');
        $member_point = app::get('business')->model('member_point');
        $orders = app::get('b2c')->model('orders');
		
		
		$data = $member->dump($this->app->member_id,'*',array('score/event'=>array('*')));
        $count = count($member_point->get_all_list('*',array('member_id' => $this->member['member_id'])));
        // 扩展的积分信息
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_detail_point($data);
            }
        }
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $member_point->get_all_list('*',array('member_id' => $this->member['member_id']),$aPage['start'],$this->pagesize);

        foreach($params['data'] as &$v){
            if($v['change_point'] >= 0){
                $v['score_u'] = 0;
                $v['score_g'] = $v['change_point'];
            }else{
                $v['score_u'] = abs($v['change_point']);
                $v['score_g'] = 0;
            }
        
        }

        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'my_point','','b2c','site_member');
        $this->pagedata['total'] = $data['score']['total'];
        $this->pagedata['historys'] = $params['data'];
        $this->output('b2c');
	}

    function active_coupons($store_id,$cpnsId){
        $nums = 1;

        if( $nums != 1 ) {
            $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('领取数量出错！'));
        }

        $mCoupon =  app::get('b2c')->model('coupons');
        $cpnsnum = $mCoupon->getList('cpns_gen_quantity', array('cpns_id' => $cpnsId), 0, -1);
        $storemanger_model = app::get('business') -> model('storemanger');
        $gradeinfo =  $storemanger_model->getgradebyid($store_id);

        if($gradeinfo['coupons_num']){
            if( ($cpnsnum[0]['cpns_gen_quantity']+ $nums) >  $gradeinfo['coupons_num'] ) {
                $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('发行数量已经超过可允许发行最大数量,请修改数量后再试！'));
            }
        }

      

        if ($list = $mCoupon->downloadCoupon($cpnsId,$nums)) {
            $coupons = $list['0'];

            $item = array();
            $url = $this -> gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'coupon'));
            $member_id = $this -> member['member_id'];
            $coupons = mysql_real_escape_string($coupons);
            $oCoupon = kernel :: single('b2c_coupon_mem'); 
            $aData = $oCoupon -> get_list_m($member_id, 'false'); 
            // 获取似否有该优惠券号
            foreach($aData as $temp) {
                if ($temp['memc_code'] == trim($coupons)) {
                    $item = $temp;
                    break;
                } 
            } 
            if (!isset($item) || empty($item)) {
                $mdl_coupons = app :: get('b2c') -> model('coupons');  
                $prefix = $mdl_coupons -> getPrefixFromCouponCode(trim($coupons));
                $arr_coupons_info = $mdl_coupons -> getCouponByPrefix($prefix,-1);  
                $member_cpn = app::get('b2c')->model('member_coupon');
                $user_cp_num = $member_cpn->count(array('member_id'=>$this->app->member_id,'cpns_id'=>$cpnsId));
                if (empty($arr_coupons_info)) { 
                    $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('优惠券编号不存在！'));
                } else {
                    //  解决前缀重复不能校验  start
                    //$arr_coupons_info = $arr_coupons_info[0];
                    //$flg_valid = $mdl_coupons -> validCheckNum($arr_coupons_info, trim($coupons), $prefix);
                    foreach($arr_coupons_info as $couons_info){
                        $flg_valid = $mdl_coupons -> validCheckNum($couons_info, trim($coupons), $prefix);
                        if($flg_valid){
                            $arr_coupons_info = $couons_info;
                            break;
                        }
                    }
                    //  解决前缀重复不能校验  end

                    if ($flg_valid == false) {
                        $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('您输入的优惠券编号不存在！'));
                    } else {
                        if (empty($arr_coupons_info['rule_id'])) {
                            $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('本优惠券已作废，不能领取！'));
                        } else {
                            $arr_rule_info = $this -> app -> model('sales_rule_order') -> dump($arr_coupons_info['rule_id'], 'from_time,to_time,member_lv_ids');
                            $curTime = time();
                            if ($curTime < $arr_rule_info['from_time']) {
                                $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('本优惠券活动尚未开始，不能领取！'));
                            } else if ($curTime > $arr_rule_info['to_time']) {
                                $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('本优惠券已过期，不能领取！'));
                            } else if ($arr_coupons_info['num_online_limit'] <= $user_cp_num) {
                                $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),'本优惠券最多领取'.$arr_coupons_info['num_online_limit'].'张！');
                            } else if ($arr_coupons_info['num_online'] <= $arr_coupons_info['online_num']) {
                                $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),'本优惠券已发放结束！');
                            } else {
                                $aSave = array('memc_code' => trim($coupons),
                                    'cpns_id' => $arr_coupons_info['cpns_id'],
                                    'member_id' => $member_id,
                                    'memc_gen_time' => time(),
                                    'expiretime' => '0',
                                    'memc_isactive' => 'true',
                                    );
                                $arr = $this -> app -> model('member_coupon') -> save($aSave);
                                if ($arr) {
                                    $this -> app -> model('member_coupon')->db->query("update sdb_b2c_coupons set online_num= online_num+1 where cpns_id =".$cpnsId );
                                    $this->splash('success',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('领取成功！'));
                                } else {
                                    $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('领取失败！'));
                                } 
                            } 
                        } 
                    } 
                } 
            } 

            if ($item['coupons_info']['cpns_status'] == 1) {
                $member_lvs = explode(',', $item['time']['member_lv_ids']);
                if (in_array($this -> member['member_lv'], (array)$member_lvs)) {
                    $curTime = time();
                    if ($curTime >= $item['time']['from_time'] && $curTime < $item['time']['to_time']) {
                        if ($item['memc_used_times'] < $this -> app -> getConf('coupon.mc.use_times')) {
                            if ($item['coupons_info']['cpns_status']) {
                                // 加入是否激活判断
                                if ($item['memc_isactive'] == 'false') {
                                    // 激活
                                    $arr = $this -> app -> model('member_coupon') -> update(array('memc_isactive' => 'true'), array('member_id' => $member_id, 'memc_code' => $item['memc_code']));
                                    if ($arr) {
                                        $this->splash('success',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('领取成功！'));
                                    } else {
                                        $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('领取失败！'));
                                    } 
                                } else {
                                    $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('该优惠券已激活，不能重复领取'));
                                } 
                                // 加入是否激活判断
                            } else {
                                $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('本优惠券已作废'));
                            } 
                        } else {
                            $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('本优惠券次数已用完'));
                        } 
                    } else {
                        $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('还未开始或已过期'));
                    } 
                } else {
                    $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('本级别不准使用'));
                } 
            } else {
                $this->splash('failed',$this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),app::get('b2c')->_('此种优惠券已取消'));
            } 
            $this->splash('success', $this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),'领取成功！');
        }else{
            $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_shop', 'act' => 'view', 'arg0' =>$store_id)),'当前优惠券未发布/时间未到,暂时不能领取！');  
        }

    }
}