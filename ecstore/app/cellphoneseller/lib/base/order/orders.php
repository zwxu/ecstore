<?php

class cellphoneseller_base_order_orders extends cellphoneseller_cellphoneseller{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    /**
    * 订单列表
    * @params
    * @return array
    */ 
    function order_list_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }
        
        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $order = app::get('business')->model('orders');

        $order_id = trim($params['order_id']);
        $goods_name = trim($params['goods_name']);
        $goods_bn = trim($params['goods_bn']);

        if(!$params['size']){
            $params['size'] = "CL";
        }

        $sql = $this->get_search_order_ids($params['type'],$params['time'],$store_id);
        $arrayorser = $order->db->select($sql);

        $search_order=$this->search_order($order_id,$goods_name,$goods_bn,$store_id);

        foreach($arrayorser as $key=>$value){
			foreach($search_order as $k=>$v){
				if($value['order_id']==$v['order_id']){
					$arr[]=$value;
				}
			}
		}

		$arrayorser=$arr;

        if(empty($arrayorser)){
            $this->send(false,null,app::get('business')->_('没有找到相应的订单！'));
		}else{
            if(!isset($params['nPage'])){
                $params['nPage'] = 1;
            }
			$aData = $order->fetchByShop($member_id,$store_id,$params['nPage']-1,$params['order_status'],$params['arr_order'],$arrayorser,$params['pagelimit']);
		}

        //测试
        if(empty($aData['data'])){
            $this->send(false,null,app::get('business')->_('没有找到相应的订单！'));
        }else{
            $mdl_member = app::get('pam')->model('account');
            $mdl_goods = app::get('b2c')->model('goods');
            foreach($aData['data'] as $k=>$v){
                $data[$k]['order_id'] = $v['order_id'];
                $data[$k]['pay_status'] = $v['pay_status'];
                $data[$k]['ship_status'] = $v['ship_status'];
                $data[$k]['status'] = $v['status'];
                $data[$k]['total_amount'] = $v['total_amount'];
                $data[$k]['member_id'] = $v['member_id'];
                $member = $mdl_member->dump($v['member_id'],'login_name');
                $data[$k]['member_name'] = $member['login_name'];
                $i = 0;
                foreach($v['order_objects'] as $k1=>$v1){
                    $data[$k]['order_objects'][$i]['obj_id'] = $v1['obj_id'];
                    $data[$k]['order_objects'][$i]['goods_id'] = $v1['goods_id'];
                    $data[$k]['order_objects'][$i]['name'] = $v1['name'];
                    $data[$k]['order_objects'][$i]['price'] = $v1['price'];
                    $data[$k]['order_objects'][$i]['quantity'] = $v1['quantity'];
                    $image = $mdl_goods->dump($v1['goods_id'],'image_default_id');
                    $data[$k]['order_objects'][$i]['image_id'] =$image['image_default_id'];
                    $data[$k]['order_objects'][$i]['image'] = $this->get_img_url($image['image_default_id'],$params['size']);
                    $i++;
                }
            }
            $result['data'] = $data;
            $result['pager'] = $aData['pager'];
            $this->send(true,$result,'查询成功');
        }
        exit;

    }

    /**
    *根据时间筛选订单 
    * @return array
    */
    private function get_search_order_ids($type='',$time='',$store_id){

         //解析时间
        $year = date('Y',time());
        $sdb = kernel::database()->prefix;

        $time_sql = "";
        $str_sql;
        //三个月内
        if($time == '3th'){
            $time_sql = " createtime<".time()." AND createtime>".strtotime("-3 month");
        //一天内
        }else if($time == '3day'){
            $time_sql = " createtime<".time()." AND createtime>".strtotime("-3 day");
        //三天内
        }
        else if($time == '1day'){
            $time_sql = " createtime<".time()." AND createtime>".strtotime("today");
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
			$type_sql=" pay_status='0' and status='active' ";
		}else if($type == 'ship'){
			$type_sql=" pay_status='1' and ship_status='0' ";
		}else if($type == 'finish'){
			$type_sql=" status='finish' ";
		}else if($type == 'dead'){
			$type_sql=" status='dead' ";
		}else if($type == 'shiped'){
			$type_sql=" pay_status='1' and ship_status='1' and status='active'";//待收货
		}else if($type == 'refunded'){
			$type_sql=" refund_status <> '0' and refund_status <> '2' and refund_status <> '4'";
		}else{
			$type_sql=' 1=1 ';
		}


        $str_sql = "SELECT order_id FROM ".$sdb."b2c_orders WHERE store_id=".$store_id;

        $str_sql.=" AND ". $time_sql.' AND '.$type_sql;


        return $str_sql;

    }

    /**
    * 订单的搜素
    * @params order_id：订单号,goods_name：商品名称,goods_bn：商品编号
    * @return array
    */
    private function search_order($order_id,$goods_name,$goods_bn,$store_id){
        //防止SQL注入
        $order_id = mysql_real_escape_string($order_id);
        $goods_name = mysql_real_escape_string($goods_name);
        $goods_bn = mysql_real_escape_string($goods_bn);

        $sdb = kernel::database()->prefix;
        $strsql="select distinct order_id from ".$sdb."b2c_orders where store_id='".$store_id."' and order_id in ";

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

        $arr_order_id= $order = app::get('business')->model('orders')->db->select($strsql);

        return $arr_order_id;
    }



    /**
    * 订单明细
    * @params
    * @return array
    */ 
    function order_detail_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }
        $order_id = $params['order_id'];
        if (!isset($order_id) || !$order_id)
        {
            $this->send(false,null,app::get('business')->_('订单编号不能为空！'));
        }
        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        if(!$params['size']){
            $params['size'] = "CL";
        }

        $objOrder = app::get('b2c')->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, '*', $subsdf);
        $objMath = kernel::single("ectools_math");

        if(!$sdf_order||$store_id!=$sdf_order['store_id']){
            $this->send(false,null,app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
        }
        //邮件
        if($sdf_order['member_id']){
            $member = app::get('b2c')->model('members');
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

        // 得到订单留言.
        $oMsg = &kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');
        $sdf_order['ordermsg'] = $arrOrderMsg;

        // 生成订单日志明细
        $sdf_order['arr_order_logs'] = $objOrder->getOrderLogList($order_id);

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        $sdf_order['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        //物流跟踪安装并且开启
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        $logisticst_service = kernel::service('b2c_change_orderloglist');
        if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
            $sdf_order['services']['logisticstack'] = $logisticst_service;
        }
        if($sdf_order){
            $mdl_member = app::get('pam')->model('account');
            $mdl_goods = app::get('b2c')->model('goods');
            $order_info['order_id'] = $sdf_order['order_id'];
            $order_info['total_amount'] = $sdf_order['total_amount'];
            $order_info['cost_shipping'] = $sdf_order['shipping']['cost_shipping'];
            $order_info['member_id'] = $sdf_order['member_id'];
            $member = $mdl_member->dump($order_info['member_id'],'login_name');
            $order_info['member_name'] = $member['login_name'];
            $order_info['pay_status'] = $sdf_order['pay_status'];
            $order_info['ship_status'] = $sdf_order['ship_status'];
            $order_info['status'] = $sdf_order['status'];
            $order_info['memo'] = $sdf_order['memo'];
            $order_info['consignee'] = $sdf_order['consignee'];
            $order_info['createtime'] = $sdf_order['createtime'];
            $i = 0;
            foreach($sdf_order['order_objects'] as $k=>$v){
                $order_info['order_objects'][$i]['obj_id'] = $v['obj_id'];
                $order_info['order_objects'][$i]['goods_id'] = $v['goods_id'];
                $order_info['order_objects'][$i]['name'] = $v['name'];
                $order_info['order_objects'][$i]['price'] = $v['price'];
                $order_info['order_objects'][$i]['quantity'] = $v['quantity'];
                $order_info['order_objects'][$i]['bn'] = $v['bn'];
                $order_info['order_objects'][$i]['store'] = $v['order_items'][$k]['products']['store'];
                $image = $mdl_goods->dump($v['goods_id'],'image_default_id');
                $order_info['order_objects'][$i]['image'] = $this->get_img_url($image['image_default_id'],$params['size']);
                $i++;
            }
            

            $this->send(true,$order_info,'查询成功');
        }else{
            $this->send(false,null,app::get('business')->_('查询失败'));
        }
    }



    /**
    * 订单运费修改
    * @params
    * @return array
    */ 
    function order_costfreight_update(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'order_id'=>'订单号'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $order_id = $params['order_id'];
        $cost_freight_change = $params['cost_freight'];

        if (!isset($order_id) || !$order_id)
        {
            $this->send(false,null,app::get('business')->_('订单编号不能为空！'));
        }
        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $cost_freight = app::get('business')->model('orders')->dump(array('order_id' => $order_id),'cost_freight,total_amount,currency,store_id');

        if(!$cost_freight||$store_id!=$cost_freight['store_id']){
            $this->send(false,null,app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
        }

        if(!is_numeric($cost_freight_change)){
            $this->send(false,null,app::get('business')->_('请填写数字'));
        }elseif($cost_freight_change < 0){
            $this->send(false,null,app::get('business')->_('修改运费不能为负数'));
        }else{
            $data['total_amount'] = $cost_freight['total_amount'] + ($cost_freight_change - $cost_freight['shipping']['cost_shipping']);
            $data['cost_freight'] = $cost_freight_change;

            //计算转换后的价钱
            $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
            $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');
            $data['final_amount'] = app::get('ectools')->model("currency")->changer_odr($data['total_amount'], $cost_freight['currency'], true, false, $system_money_decimals, $system_money_operation_carryset);
            //end
            if($this->check_order($order_id)){
                $arr = app::get('b2c')->model('orders')->update($data,array('order_id' => $order_id));
                $objorder_log = app::get('b2c')->model('order_log');

                $log_text = '修改运费为'.$cost_freight_change.'元！（手机端修改）';

                $opid = $member['member_id'];
                $opname = $member['uname'];

                $sdf_order_log = array(
                    'rel_id' => $order_id,
                    'op_id' => $opid,
                    'op_name' => $opname,
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'change_price',
                    'result' => 'SUCCESS',
                    'log_text' => $log_text,
                );
                $log_id = $objorder_log->save($sdf_order_log);
                $this->send(true,$cost_freight_change,'修改成功');
            }else{
                $this->send(false,null,app::get('business')->_('信息错误，无法修改'));
            }
        }

    }

    private function check_order($order_id){
        $order_info = app::get('b2c')->model('orders')->dump(array('order_id' => $order_id),'pay_status,ship_status,status');
        if($order_info['pay_status'] != 0 || $order_info['ship_status'] != 0 || $order_info['status'] != 'active'){
            return false;
        }else{
            return true;
        }
    }


    /**
    * 订单取消
    * @params
    * @return array
    */ 
    function order_cancel(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'order_id'=>'订单ID'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $order_id = $params['order_id'];

        if (!isset($order_id) || !$order_id)
        {
            $this->send(false,null,app::get('business')->_('订单编号不能为空！'));
        }
        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $order_info = app::get('business')->model('orders')->dump(array('order_id' => $order_id),'store_id');

        if(!$order_info||$store_id!=$order_info['store_id']){
            $this->send(false,null,app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
        }

        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($order_id,'',$message))
        {
           $this->send(false,null,app::get('b2c')->_('订单无法取消！'));
        }

        $sdf['order_id'] = $order_id;
        $sdf['op_id'] = $member['member_id'];
        $sdf['opname'] = $member['uname'];

        $b2c_order_cancel = kernel::single("b2c_order_cancel");
        $controller   = kernel::single('b2c_ctl_site_order');

        if ($b2c_order_cancel->generate($sdf, $controller, $message))
        {
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

            $this->send(true,1,'订单取消成功');
        }
        else
        {
            $this->send(false,null,'订单取消失败');
        }

    }


    /**
    * 退货单列表
    * @params
    * @return array
    */ 
    function aftersale_reship_list_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $clos = "return_id,order_id,add_time,status,member_id,is_intervene";
        $filter = array();

        if( $params["status"] != "" ){
            $filter["status"] = $params["status"];
        }

        if( $params["order_id"] != "" ){
            $filter["order_id"] = $params["order_id"];
        }

        $filter["refund_type"] = '2';
        $filter["store_id"] = $store_id;

        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->send(false,null,'售后服务应用不存在！');
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->send(false,null,'售后服务信息没有取到！');
        }

        if(!$arr_settings['is_open_return_product']){
            $this->send(false,null,'售后服务信息没有开启！');
        }
        if(!isset($params['nPage'])){
            $params['nPage'] = 1;
        }

        if(!isset($params['pagelimit'])){
            $params['pagelimit'] = 10;
        }

        $aData = $obj_return_policy->get_return_product_list($clos, $filter, $params['nPage'],$params['pagelimit']);

        $obj_account = app::get('pam')->model('account');
        $order_obj = app::get('b2c')->model('orders');
        //添加用户名
        foreach($aData['data'] as $key=>$val){
            $uname = $obj_account->getRow('login_name',array('account_id'=>$val['member_id']));
            $aData['data'][$key]['uname'] = $uname['login_name'];
            $status = $order_obj->dump(array('order_id'=>$val['order_id']),'refund_status');
            $aData['data'][$key]['refund_status'] = $status['refund_status'];
        }

        $aData['total'] = ceil($aData['total']/$params['pagelimit']);

        $this->send(true,$aData,'查询成功');

    }

    /**
    * 退款列表
    * @params
    * @return array
    */ 
    function aftersale_refund_list_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $clos = "return_id,order_id,add_time,status,member_id,is_intervene";
        $filter = array();

        if( $params["status"] != "" ){
            $filter["status"] = $params["status"];
        }

        if( $params["order_id"] != "" ){
            $filter["order_id"] = $params["order_id"];
        }

        $filter["refund_type|in"] = array('1','3','4');
        $filter["store_id"] = $store_id;

        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->send(false,null,'售后服务应用不存在！');
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->send(false,null,'售后服务信息没有取到！');
        }

        if(!$arr_settings['is_open_return_product']){
            $this->send(false,null,'售后服务信息没有开启！');
        }
        if(!isset($params['nPage'])){
            $params['nPage'] = 1;
        }
        if(!isset($params['pagelimit'])){
            $params['pagelimit'] = 10;
        }
        $aData = $obj_return_policy->get_return_product_list($clos, $filter, $params['nPage'],$params['pagelimit']);

        $obj_account = app::get('pam')->model('account');
        $order_obj = app::get('b2c')->model('orders');
        //添加用户名
        foreach($aData['data'] as $key=>$val){
            $uname = $obj_account->getRow('login_name',array('account_id'=>$val['member_id']));
            $aData['data'][$key]['uname'] = $uname['login_name'];
            $status = $order_obj->dump(array('order_id'=>$val['order_id']),'refund_status');
            $aData['data'][$key]['refund_status'] = $status['refund_status'];
        }
        $aData['total'] = ceil($aData['total']/$params['pagelimit']);
        $this->send(true,$aData,'查询成功');

    }

    /**
    * 退款退货详情
    * @params
    * @return array
    */ 
    function aftersale_return_detail_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'return_id'=>'退款ID',
        );
        $this->check_params($must_params);
        
        $return_id = $params['return_id'];

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        if(!$params['size']){
            $params['size'] = "CL";
        }

        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->send(false,null,'售后服务应用不存在！');
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->send(false,null,'售后服务信息没有取到！');
        }

        if(!$arr_settings['is_open_return_product']){
            $this->send(false,null,'售后服务信息没有开启！');
        }

        $return_item =  $obj_return_policy->get_return_product_by_return_id($return_id);

        $obj_account = app::get('pam')->model('account');
        $uname = $obj_account->getRow('login_name',array('account_id'=>$return_item['member_id']));
        $return_item['uname'] = $uname['login_name'];

        //添加商品信息
        $obj_goods = app::get('b2c')->model('goods');
        foreach($return_item['product_data'] as $key=>$val){
            $goods_info = $obj_goods->dump(array('bn'=>$val['bn']),'image_default_id,goods_id,price');
            $return_item['product_data'][$key]['goods_id'] = $goods_info['goods_id'];
            $return_item['product_data'][$key]['price'] = $goods_info['price'];
            $return_item['product_data'][$key]['image'] = $this->get_img_url($goods_info['image_default_id'],$params['size']);
        }
        
        //添加退货地址显示
        $obj_address = app::get('business')->model('dlyaddress');
        $address = $obj_address->getList('*',array('da_id'=>$return_item['refund_address']));
        $ads = explode(':',$address['0']['region']);
        $address['0']['region'] = $ads[1];
        $return_item['refund_address'] = $address['0'];

        //添加退款日志
        $obj_return_p = app::get('aftersales')->model('return_product');
        $order_id = $obj_return_p->dump(array('return_id'=>$return_id));
        $obj_return_log = app::get('aftersales')->model('return_log');
        $log_info = $obj_return_log->getList('*',array('order_id'=>$order_id['order_id']),-1,-1,'alttime DESC');
        $return_item['log_info'] = $log_info;
        if($return_item){
            $this->send(true,$return_item,'查询成功');
        }else{
            $this->send(false,null,'查询失败');
        }

    }

    /**
    * 评论列表
    * @params
    * @return array
    */ 
    function discuss_list_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
        );
        $this->check_params($must_params);
        
        $return_id = $params['return_id'];
        $goods_id = $params['goods_id'];

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        if(!$params['size']){
            $params['size'] = "CL";
        }

        $objDisask = kernel::single('business_message_disask');
        $objGoods = app::get('business')->model('goods');
        $objPoint = app::get('business')->model('comment_goods_point');

        $data['_all_point'] = $objPoint->get_business_point($store_id);

        if($goods_id){
            $filter['type_id']=$goods_id;
        }

        $aData = $objDisask->get_business_disask_phone($store_id,$params['nPage'],'discuss',$filter,$params['pagelimit']);
        $comment=array();
        $imageDefault = app::get('image')->getConf('image.set');
        $oImage = app::get('image')->model('image');
        foreach((array)$aData['data'] as $k => $v){
            $goods_data = $objGoods->getList('name,price,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$v['type_id']));
            if(!$goods_data) continue;
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['image_default_id']))){
                $goods_data[0]['image_default_id'] = $imageDefault['S']['default_image'];
            }
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['thumbnail_pic']))) {
                $goods_data[0]['thumbnail_pic'] = $imageDefault['S']['default_image'];
            }
            $v['goods_point'] = $objPoint->get_single_point($v['type_id']);
            $v['name'] = $goods_data[0]['name'];
            $v['price'] = $goods_data[0]['price'];
            $v['thumbnail_pic'] = $goods_data[0]['thumbnail_pic'];
            $v['udfimg'] = $goods_data[0]['udfimg'];
            $v['image_default_id'] = $goods_data[0]['image_default_id'];
            $v['image'] = $this->get_img_url($goods_data[0]['image_default_id'],$params['size']);
            $comment[] = $v;
        }
        $data['commentList'] = $comment;
        $data['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $data['defaultImage'] = $imageDefault['S']['default_image'];
        $data['total'] = $aData['page'];
        $this->send(true,$data,'查询成功');
    }


    /**
    * 评论列表
    * @params
    * @return array
    */ 
    function discuss_detail_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        if(!$params['size']){
            $params['size'] = "CL";
        }

        $objDisask = kernel::single('business_message_disask');
        $objGoods = app::get('business')->model('goods');
        $objPoint = app::get('business')->model('comment_goods_point');
        $filter['comment_id']=$params['comment_id'];

        $aData = $objDisask->get_business_disask_phone($store_id,$params['nPage'],'discuss',$filter,$params['pagelimit']);

        $comment=array();
        $imageDefault = app::get('image')->getConf('image.set');
        $oImage = app::get('image')->model('image');
        foreach((array)$aData['data'] as $k => $v){
            $goods_data = $objGoods->getList('name,price,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$v['type_id']));
            if(!$goods_data) continue;
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['image_default_id']))){
                $goods_data[0]['image_default_id'] = $imageDefault['S']['default_image'];
            }
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['thumbnail_pic']))) {
                $goods_data[0]['thumbnail_pic'] = $imageDefault['S']['default_image'];
            }
            $v['goods_point'] = $objPoint->get_single_point($v['type_id']);
            $v['name'] = $goods_data[0]['name'];
            $v['price'] = $goods_data[0]['price'];
            $v['thumbnail_pic'] = $goods_data[0]['thumbnail_pic'];
            $v['udfimg'] = $goods_data[0]['udfimg'];
            $v['image_default_id'] = $goods_data[0]['image_default_id'];
            $v['image'] = $this->get_img_url($goods_data[0]['image_default_id'],$params['size']);
            $comment[] = $v;
        }
        $data['commentList'] = $comment;
        $data['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $data['defaultImage'] = $imageDefault['S']['default_image'];
        $data['total'] = $aData['page'];
                
        $this->send(true,$data,'查询成功');
    }

    /**
    * 评论回复
    * @params
    * @return array
    */
    function discuss_reply_add(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'comment_id'=>'评论id',
            'reply_content'=>'解释内容'
        );
        $this->check_params($must_params);
        
        $comment_id = $params['comment_id'];
        $comment = $params['reply_content'];

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        if($comment_id&&$comment){
            $member_comments = kernel::single('business_message_disask');
            $row = $member_comments->dump($comment_id);
            $author_id = $row['author_id'];
            unset($row['goods_point']);
            if(app::get('b2c')->getConf('comment.display.discuss') == 'reply'){
                $aData = $row;
                $aData['display'] = 'true';
                $goods_point = app::get('b2c')->model('comment_goods_point');
                $goods_point->set_status($comment_id,'true');
                $_is_add_point = app::get('b2c')->getConf('member_point');
                if($_is_add_point && $author_id){
                    $obj_member_point = app::get('b2c')->model('member_point');
                    $obj_member_point->change_point($author_id,$_is_add_point,$_msg,'comment_discuss',2,$row['type_id'],$author_id,'comment');
                }
                $member_comments->save($aData);
            }

            $sdf['store_id'] = $store_id;
            $sdf['comments_type'] = '0';
            $sdf['comment_id']= '';
            $sdf['for_comment_id'] = $comment_id;
            $sdf['object_type'] = "discuss";
            $sdf['to_id'] = $author_id;
            $sdf['author_id'] = $member['member_id'];
            $sdf['author'] = $member['uname'];
            $sdf['title'] = '';
            $sdf['contact'] = '';
            $sdf['display'] = 'true';
            $sdf['time'] = time();
            $sdf['comment'] = $comment;
            if($member_comments->send($sdf,'discuss')){
                $comments = app::get('b2c')->model('member_comments');
                $data['member_id'] = $author_id;
                $comments->fireEvent('discussreply',$data,$author_id);
                $params['items'] = $member_comments->get_reply($comment_id);
                $this->send(true,1,'评论成功');
            }else{
                $this->send(false,null,'评论失败');
            }
        }
        else{
            $this->send(false,null,'评论失败');
        }
    }

    /**
    * 订单个数统计
    * @params
    * @return array
    */ 
    function order_count_get(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $order = app::get('business')->model('orders');
        $data['daycount'] = $order->db->select('select count(order_id) from '.$order->table_name(1).' where store_id='.$store_id.' AND createtime<'.time().' AND createtime>'.strtotime("today"));
        $data['daycount'] = $data['daycount'][0]['count(order_id)'];

        $data['3daycount'] = $order->db->select('select count(order_id) from '.$order->table_name(1).' where store_id='.$store_id.' AND createtime<'.time().' AND createtime>'.strtotime("-3 day")); 
        $data['3daycount'] = $data['3daycount'][0]['count(order_id)'];

        $data['nopayed'] = $order->db->select('select count(order_id) from '.$order->table_name(1).' where store_id='.$store_id.' AND pay_status="0" and status="active"');
        $data['nopayed'] = $data['nopayed'][0]['count(order_id)'];

        $data['noshiped'] = $order->db->select('select count(order_id) from '.$order->table_name(1).' where store_id='.$store_id.' AND pay_status="1" and ship_status="0"');
        $data['noshiped'] = $data['noshiped'][0]['count(order_id)'];

        $data['shiped'] = $order->db->select('select count(order_id) from '.$order->table_name(1).' where store_id='.$store_id.' AND pay_status="1" and ship_status="1" and status="active"');
        $data['shiped'] = $data['shiped'][0]['count(order_id)'];

        $data['finish'] = $order->db->select('select count(order_id) from '.$order->table_name(1).' where store_id='.$store_id.' AND status="finish"');
        $data['finish'] = $data['finish'][0]['count(order_id)'];

        $data['cancel'] = $order->db->select('select count(order_id) from '.$order->table_name(1).' where store_id='.$store_id.' AND status="dead"');
        $data['cancel'] = $data['cancel'][0]['count(order_id)'];

        $this->send(true,$data,'查询成功');
    }

    /**
    * 订单发货
    * @params
    * @return array
    */ 
    function order_delivery(){
        $params = $this->params;
        $this->check($params);
        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'order_id'=>'订单号',
            'send'=>'发货数量',
            'delivery'=>'发货',
            'deladdress'=>'发货id',
            'logi_id'=>'快递id',
            'logi_no'=>'快递号'
        );
        $this->check_params($must_params); 

        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('business')->_('您不是卖家'));
        }

        $sto= kernel::single("business_memberstore",$member_id);
        $sto->process($member_id);
        $store_id = $sto->storeinfo['store_id'];

        $params['send'] = json_decode($params['send'],true);
        
        $obj_order = app::get('b2c')->model('orders');

        $ship_info = $obj_order->dump(array('order_id'=>$params['order_id']),'ship_name,ship_tel,ship_mobile,ship_zip,ship_area,ship_addr');
        
        $params['ship_name'] = $ship_info['consignee']['name'];
        $params['ship_tel'] = $ship_info['consignee']['telephone'];
        $params['ship_mobile'] = $ship_info['consignee']['mobile'];
        $params['ship_area'] = $ship_info['consignee']['area'];
        $params['ship_addr'] = $ship_info['consignee']['addr'];
        $params['ship_zip'] = $ship_info['consignee']['zip'];

        $rp = app::get('aftersales')->model('return_product');
        $return_id = $rp->getRow('*',array('order_id'=>$params['order_id'],'refund_type'=>'1'));
        if($return_id){
            
            $obj_return_policy = kernel::single('aftersales_data_return_policy');

            $sdf = array(
                'return_id' => $return_id['return_id'],
                'status' => '5',
            );
            
            $this->pagedata['return_status'] = $obj_return_policy->change_status($sdf);        
            if ($this->pagedata['return_status'])
                $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
            
            $obj_aftersales = kernel::servicelist("api.aftersales.request");
            foreach ($obj_aftersales as $obj_request)
            {
                $obj_request->send_update_request($sdf);
            }

            //修改订单状态
            $refund_status = array('refund_status'=>'2');
            $rs = $obj_order->update($refund_status,array('order_id'=>$params['order_id']));
        }
        
        if(!$order_id) $order_id = $params['order_id'];
        else $params['order_id'] = $order_id;
        
        $sdf = $params;

        $sdf['opid'] = $member['member_id'];
        $sdf['opname'] = $member['uname'];
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_delivery($sdf['order_id'],$sdf,$message))
        {
            $this->send(false,null,'发货失败');
        }
       
        // 处理支付单据.
        $objB2c_delivery = b2c_order_delivery::getInstance(app::get('b2c'), app::get('b2c')->model('delivery'));
        $controller   = kernel::single('b2c_ctl_site_order');
        if ($objB2c_delivery->generate($sdf, $controller, $message))
        {            
            //ajx crm
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $req_arr['order_id']=$sdf['order_id'];
            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
            $obj_corp = app::get('b2c')->model('dlycorp');
            $code = $obj_corp->dump(array('corp_id'=>$sdf['logi_id']));
            if($code['corp_code'] == 'BAM'){
                $data['confirm_time'] = time()+(app::get('b2c')->getConf('member.to_finish_BAM'))*86400;
            }else{
                $data['confirm_time'] = time()+(app::get('b2c')->getConf('member.to_finish'))*86400;
            }
            $data['del_address'] = $sdf['deladdress'];
            $arr = app::get('business')->model('orders')->update($data,array('order_id' => $sdf['order_id']));
            //$arr = $this->app->model('orders')->dump(array('order_id' => $sdf['order_id']));
            $this->send(true,1,'发货成功');
        }
        else
        {
            $this->send(false,null,'发货失败');
        }
    }

}
