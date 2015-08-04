<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_member_order extends cellphone_cellphone{

    private static $support_corps = array(
        'EMS' => 'ems',
        'STO' => 'shentong',
        'YTO' => 'yuantong',
        'SF' => 'shunfeng',
        'YUNDA' => 'yunda',
        'APEX' => 'quanyikuaidi',
        'LBEX' => 'longbanwuliu',
        'ZJS' => 'zhaijisong',
        'TTKDEX' => 'tiantian',
        'ZTO' => 'zhongtong',
        'HTKY' => 'huitongkuaidi',
        'CNMH' => 'minghangkuaidi',
        'AIRFEX' => 'yafengsudi',
        'CNKJ' => 'kuaijiesudi',
        'DDS' => 'dsukuaidi',
        'HOAU' => 'huayuwuliu',
        'CRE' => 'zhongtiewuliu',
        'FedEx' => 'fedex',
        'UPS' => 'ups',
        'DHL' => 'dhl',
        'CYEXP' => 'changyuwuliu',
        'DBL' => 'debangwuliu',
        'POST' => 'post',
        'CCES' => 'cces',
        'DTW' => 'datianwuliu',
        'ANTO' => 'andewuliu',
    );

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }


    public function getlist(){ 
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];

        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }

         if($params['pagelimit']){
            $pagelimit=$params['pagelimit'];
        }else{
            $pagelimit=10;
        }

        if($params['nPage']){
            $nPage=$params['nPage'];
        }else{
            $nPage=1;
        }

        if($params['picSize']){
            $picSize=trim($params['picSize']);
        }else{
            $picSize='CL';
        }



        //进入页面是需要调用订单操作脚本
        //kernel::single('b2c_orderautojob')->order_auto_operation($member_id,'');
        //end by lf
        
        
        //根据订单ID查找
        if($params['order_id']){
            $order_id=trim($params['order_id']);
        }

        //根据商品名查找
        if($params['goods_name']){
            $goods_name=trim($params['goods_name']);
        }

        //根据商品编号查找
        if($params['goods_bn']){
            $goods_bn=trim($params['goods_bn']);
        }
        //根据时间查找
        if($params['time']){
            $time=trim($params['time']);
        }
        
        //订单类型  nopayed 待付款  ship 待发货   shiped 待收货  comment 未评论 finish 已完成  confirm 待确认  dead 作废
        if($params['ordertype']){
            $type=trim($params['ordertype']);
            if(!in_array($type,array('nopayed','ship','shiped','comment','finish','confirm','dead'))){
               $this->send(false,null,app::get('b2c')->_('不存在此订单类型'));
            }
        }

        $order = &app::get('b2c')->model('orders');
        $sql = $this->get_search_order_ids($type,$time,$member_id);

  
		$arrayorser = $order->db->select($sql);
		$search_order=$this->search_order($order_id,$goods_name,$goods_bn,$member_id);
  
		$arr=array();
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
            $this->send(true,null, $msg);
		}else{
			$aData = $order->fetchByMember($member_id,$nPage-1,'','',$arrayorser);
		}
        
      
        $this->get_order_details($aData,'member_orders');
        
        $oImage = app::get('image')->model('image');
        $imageDefault = app::get('image')->getConf('image.set');
        $applySObj = app::get('spike')->model('spikeapply');
        $applyGObj = app::get('groupbuy')->model('groupapply');
        $applyScoreObj = app::get('scorebuy')->model('scoreapply');
        foreach($aData['data'] as $k=>$v) {
            //获取订单支付时间 add by lf
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
                    $aData['data'][$k]['goods_items'][$k2]['product']['thumbnail_pic'] =
                    $this->get_img_url($v['image_default_id'],$picSize);

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


        //整理返回值数组
        $obj_store=&app :: get('business')->model('storemanger');
        $obj_goods=&app :: get('b2c')->model('goods');
        

        foreach($aData['data'] as $key=>&$value){
            unset($value['pay_status'],$value['ship_status'],$value['is_delivery'],
            $value['createtime'],$value['last_modified'],$value['payinfo'],
            $value['shipping'],$value['confirm'],$value['consignee'],
            $value['weight'],$value['title'],$value['itemnum'],
            $value['status'],$value['ip'],$value['cost_item'],$value['is_tax'],
            $value['cost_tax'],$value['tax_title'],$value['currency'],
            $value['cur_rate'],$value['score_u'],$value['score_g'],
            $value['discount'],$value['pmt_goods'],$value['pmt_order'],
            $value['payed'],$value['memo'],$value['disabled'],
            $value['mark_type'],$value['mark_text'],$value['extend'],
            $value['order_refer'],$value['addon'],$value['pay_time'],
            $value['need_send'], $value['confirm_time'],
            $value['comments_count'],$value['refund_status'],$value['act_id'],
            $value['order_type'],$value['is_extend'],$value['order_kind'],$value['order_kind_detail'],
            $value['need_edit'],$value['seller_real_name'],$value['buy_name']
            );

            $oStore= $obj_store->getList('store_name',array('store_id'=> $value['store_id']));
            if($oStore[0]){
                $value['store_name']=$oStore[0]['store_name'];
            }

            unset($value['goods_items']);

            $value['order_objects']=array_values($value['order_objects']);

            foreach($value['order_objects'] as $xkey=>&$xvalue){

                unset($xvalue['obj_id'],$xvalue['order_id'],
                        $xvalue['obj_type'],$xvalue['obj_alias'],$xvalue['weight'],$xvalue['price'],
                        $xvalue['goods_id'],$xvalue['bn'],$xvalue['score']
                );

                foreach($xvalue['order_items'] as $ykey=>&$yvalue){
                    unset($yvalue['item_id'],$yvalue['order_id'],
                    $yvalue['obj_id'],$yvalue['goods_id'],
                    $yvalue['type_id'],$yvalue['bn'],
                    $yvalue['sendnum'],$yvalue['addon'],
                    $yvalue['addon'],$yvalue['weight'], $yvalue['g_price'],$yvalue['score'],
                    $yvalue['cost'],
                    //$yvalue['products']['goods_id'],
                    $yvalue['products']['bn'],
                    $yvalue['products']['uptime'],
                    $yvalue['products']['last_modify'],$yvalue['products']['disabled'],
                    $yvalue['products']['status'],
                    $yvalue['products']['title'],
                    $yvalue['products']['store_place'],
                    $yvalue['products']['freez'],
                    $yvalue['products']['store'],
                    $yvalue['products']['unit'],
                    $yvalue['products']['price']['mktprice'],
                    $yvalue['products']['price']['cost'],
                    $yvalue['products']['price'],
                    $yvalue['products']['spec_desc'],
                    $yvalue['products']['weight'], $yvalue['products']['goods_type'],
                    $yvalue['products']['barcode']
                    );

                    $oGoods=$obj_goods->getList('image_default_id,udfimg,thumbnail_pic',array('goods_id'=>$yvalue['products']['goods_id']));
                    $yvalue['products']['image_default_id'] =
                        $this->get_img_url(($oGoods[0]['udfimg']=='true'?$oGoods[0]['thumbnail_pic']:$oGoods[0]['image_default_id']),$picSize);

                }

                $xvalue['order_items']=array_values($xvalue['order_items']);

            }

            //$value['goods_items']=array_values($value['goods_items']);

        }


       //print_r('<pre>');print_r($aData);print_r('</pre>');exit;

       $this->send(true,$aData, app::get('b2c')->_('订单'));
    }


     /**
    *根据时间筛选订单 add by hzy
    * @return array
    */
    private function get_search_order_ids($type='',$time='',$member_id){

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


        $str_sql = "SELECT order_id FROM ".$sdb."b2c_orders WHERE member_id=".$member_id;

        $str_sql.=" AND ". $time_sql.' AND '.$type_sql;

        return $str_sql;

    }


      /**
    * 订单的搜素
    * @params order_id：订单号,goods_name：商品名称,goods_bn：商品编号
    * @return array
    */
    private function search_order($order_id,$goods_name,$goods_bn,$member_id){
        //防止SQL注入
        $order_id = mysql_real_escape_string($order_id);
        $goods_name = mysql_real_escape_string($goods_name);
        $goods_bn = mysql_real_escape_string($goods_bn);

        $sdb = kernel::database()->prefix;
        $strsql="select distinct order_id from ".$sdb."b2c_orders where member_id='".$member_id."' and order_id in ";

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

        $arr_order_id= $order = &app::get('b2c')->model('orders')->db->select($strsql);

        return $arr_order_id;
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
            $obj_specification = &app::get('b2c')->model('specification');
            $obj_spec_values = &app::get('b2c')->model('spec_values');
            $obj_goods = &app::get('b2c')->model('goods');
            if (isset($arr_data_item['order_objects']) && $arr_data_item['order_objects'])
            {
                       
                foreach ($arr_data_item['order_objects'] as $k=>$arr_objects)
                {
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
                                $o = &app::get('b2c')->model('order_items');
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
                                $arr_data_item['goods_items'][$k]['product']['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k]['product']['quantity']);
                                $arr_data_item['goods_items'][$k]['product']['amount'] = $arr_items['amount'];
                                $arr_goods_list = $obj_goods->getList('image_default_id', array('goods_id' => $arr_items['goods_id']));
                                $arr_goods = $arr_goods_list[0];
                                if (!$arr_goods['image_default_id'])
                                {
                                    $arr_goods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k]['product']['thumbnail_pic'] =
                                             $this->get_img_url($arr_goods['image_default_id'],$picSize);


                                //团购秒杀链接add by ql 2013-7-27
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

                                    //$arr_data_item['goods_items'][$k]['product']['link_url'] = $this->gen_url(array('app'=>$appName,'ctl'=>'site_product','act'=>'index','args'=>$args));
                                }else{
                                    //$arr_data_item['goods_items'][$k]['product']['link_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$arr_items['goods_id']));
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
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['thumbnail_pic'] =
                                                                             $this->get_img_url($arrGoods['image_default_id'],$picSize);

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
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['thumbnail_pic'] =
                                            $this->get_img_url($arrGoods['image_default_id'],$picSize);

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
                                        $o = &app::get('b2c')->model('order_items');
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
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['thumbnail_pic'] =
                                        $this->get_img_url($arrGoods['image_default_id'],$picSize);

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
                                //$arr_data_item['extends_items'][] = $str_service_goods_type_obj->get_order_object($arr_objects, $arr_Goods,$tpl);
                            }
                            
                            
                        }
                        
                    }
                }
                
                
            }

        }
    }

    //根据订单ID获取订单详细
    public function detail($iss=null){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'order_id'=>'订单编号'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];

        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }

        if($params['picSize']){
            $picSize=trim($params['picSize']);
        }else{
            $picSize='CL';
        }

        $order_id=$params['order_id'];

        $objOrder = &app::get('b2c')->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, '*', $subsdf);
        $objMath = kernel::single("ectools_math");

        if(!$sdf_order||$member_id!=$sdf_order['member_id']){
           $this->send(false,null,app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
        }

        if($sdf_order['member_id']){
            $member = &app::get('b2c')->model('members');
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

        /*
        // 订单的相关信息的修改
        $obj_other_info = kernel::servicelist('b2c.order_other_infomation');
        if ($obj_other_info)
        {
            foreach ($obj_other_info as $obj)
            {
                $this->pagedata['discount_html'] = $obj->gen_point_discount($sdf_order);
            }
        }
        */

        $order_items = array();
        $gift_items = array();
        $this->get_order_detail_item($sdf_order,'member_order_detail');


        /** 去掉商品优惠 **/
        /*
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
        */
        /** end **/

         // 得到订单留言.
        $oMsg = &kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');

        $sdf_order['ordermsg']= $arrOrderMsg;

        // 生成订单日志明细
        //$oLogs =&$this->app->model('order_log');
        //$arr_order_logs = $oLogs->getList('*', array('rel_id' => $order_id));
        $arr_order_logs = $objOrder->getOrderLogList($order_id);

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        $sdf_order['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($this->pagedata['order']['payinfo']['pay_app_id']);

        //物流跟踪安装并且开启
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        $logisticst_service = kernel::service('b2c_change_orderloglist');
        if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
            $sdf_order['services']['logisticstack'] = $logisticst_service;
        }

        $sdf_order['orderlogs'] = $arr_order_logs['data'];



         //整理返回值数组
        $sdf_order['order_objects']=array_values($sdf_order['order_objects']);

        foreach($sdf_order['order_objects'] as $xkey=>&$xvalue){


            $xvalue['order_items']=array_values($xvalue['order_items']);
        }

        $sdf_order['goods_items']=array_values($sdf_order['goods_items']);


        $obj_store=&app :: get('business')->model('storemanger');
        $obj_goods=&app :: get('b2c')->model('goods');

        $oStore= $obj_store->getList('store_name',array('store_id'=>  $sdf_order['store_id']));
        if($oStore[0]){
            $sdf_order['store_name']=$oStore[0]['store_name'];
        }

        unset($sdf_order['goods_items'], $sdf_order['weight'],$sdf_order['title'],
              $sdf_order['itemnum'], $sdf_order['orderlogs'],
              $sdf_order['last_modified'],$sdf_order['createtime'],
                       $sdf_order['ip']
        );


         foreach($sdf_order['order_objects'] as $xkey=>&$xvalue){

                unset($xvalue['obj_id'],$xvalue['order_id'],
                        $xvalue['obj_type'],$xvalue['obj_alias'],$xvalue['weight'],$xvalue['price'],
                        $xvalue['goods_id'],$xvalue['bn'],$xvalue['score']
                );

                foreach($xvalue['order_items'] as $ykey=>&$yvalue){
                    unset($yvalue['item_id'],$yvalue['order_id'],
                    $yvalue['obj_id'],$yvalue['goods_id'],
                    $yvalue['type_id'],$yvalue['bn'],
                    $yvalue['sendnum'],$yvalue['addon'],
                    $yvalue['addon'],$yvalue['weight'], $yvalue['g_price'],$yvalue['score'],
                    $yvalue['cost'],
                    //$yvalue['products']['goods_id'],
                    $yvalue['products']['bn'],
                    $yvalue['products']['uptime'],
                    $yvalue['products']['last_modify'],$yvalue['products']['disabled'],
                    $yvalue['products']['status'],
                    $yvalue['products']['title'],
                    $yvalue['products']['store_place'],
                    $yvalue['products']['freez'],
                    $yvalue['products']['store'],
                    $yvalue['products']['unit'],
                    $yvalue['products']['price']['mktprice'],
                    $yvalue['products']['price']['cost'],
                    $yvalue['products']['price'],
                    $yvalue['products']['spec_desc'],
                    $yvalue['products']['weight'], $yvalue['products']['goods_type'],
                    $yvalue['products']['barcode']
                    );

                    $oGoods=$obj_goods->getList('udfimg,thumbnail_pic,image_default_id',array('goods_id'=>$yvalue['products']['goods_id']));
                    $yvalue['products']['image_default_id'] =
                        $this->get_img_url(($oGoods[0]['udfimg']=='true'?$oGoods[0]['thumbnail_pic']:$oGoods[0]['image_default_id']),$picSize);

                }

                $xvalue['order_items']=array_values($xvalue['order_items']);

        }

        //创建时间
        $sdf_order[ 'creates_time']='';
        //付款时间
        $sdf_order[ 'payments_time']='';
        //更新时间
        $sdf_order['updates_time']='';
        //退款时间
        $sdf_order['refunds_time']='';
        //发货时间
        $sdf_order['delivery_time']='';
        //换货时间
        $sdf_order['reship_time']='';
        //完成时间
        $sdf_order['finish_time']='';
        //取消时间
        $sdf_order['cancel_time']='';
        //更改价格时间
        $sdf_order['change_price_time']='';
        //延长收货时间的时间
        $sdf_order['extend_time_time']='';

        $obj_orderlog=&app::get('b2c')->model('order_log');
        $oOrderlog=$obj_orderlog->getList('*',array('rel_id'=>$sdf_order['order_id'],'bill_type'=>'order','result'=>'SUCCESS'));

        foreach($oOrderlog as $item){
            switch($item['behavior']) {
               case 'creates':
                   $sdf_order[ 'creates_time']=$item['alttime'];
                   break;
               case 'payments':
                    $sdf_order[ 'payments_time']=$item['alttime'];
                   break;
               case 'updates' :
                    $sdf_order[ 'updates_time']=$item['alttime'];
                   break;
               case 'refunds':
                    $sdf_order[ 'refunds_time']=$item['alttime'];
                   break;
               case 'delivery':
                    $sdf_order[ 'delivery_time']=$item['alttime'];
                   break;
               case 'reship' :
                    $sdf_order[ 'reship_time']=$item['alttime'];
                   break;
               case 'finish':
                    $sdf_order[ 'finish_time']=$item['alttime'];
                   break;
               case 'cancel':
                    $sdf_order[ 'cancel_time']=$item['alttime'];
                   break;
               case 'change_price':
                    $sdf_order[ 'change_price_time']=$item['alttime'];
                   break;
               case 'extend_time':
                    $sdf_order[ 'extend_time_time']=$item['alttime'];
                   break;
            }

        }


        $obj_payment=&app::get('ectools')->model('payments');

        $oPayment=$obj_payment->get_payments_by_order_id($sdf_order['order_id']);

        $sdf_order['payment_id']=$oPayment[0]['payment_id'];

        $obj_delivery= &app::get('b2c')->model('delivery');
        $obj_dlycorp= &app::get('b2c')->model('dlycorp');
        $oDelivery=$obj_delivery->getList('delivery_id,logi_id,logi_name,logi_no',array('order_id'=>$sdf_order['order_id']));
        foreach($oDelivery as $key=>&$value){
            $oDlycorp=$obj_dlycorp->getList('corp_code',array('corp_id'=>$value['logi_id']));
            if($oDlycorp[0]){
                //转换接口用物流代码
                $value['logi_code']=self::$support_corps[$oDlycorp[0]['corp_code']];
                if(empty( $value['logi_code'])){
                    $value['logi_code']=$oDlycorp[0]['corp_code'];
                }
            }
        }

        $sdf_order['shipping']['delivery_id']=$oDelivery[0]['delivery_id'];
        $sdf_order['shipping']['logi_id']=$oDelivery[0]['logi_id'];
        $sdf_order['shipping']['logi_name']=$oDelivery[0]['logi_name'];
        $sdf_order['shipping']['logi_no']=$oDelivery[0]['logi_no'];
        $sdf_order['shipping']['logi_code']=$oDelivery[0]['logi_code'];

        //print_r('<pre>');print_r($sdf_order);print_r('</pre>');exit;

        if($iss){
            return $sdf_order;
        }else{
            $this->send(true,$sdf_order,app::get('b2c')->_('订单详情'));
        }

    }




}