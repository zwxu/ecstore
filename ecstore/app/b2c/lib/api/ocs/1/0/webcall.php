<?php
class b2c_api_ocs_1_0_webcall{
  
    public function __construct($app){
        $this->app = $app;
        $this->net = &kernel::single('base_httpclient');
    }
    
    /*
    * @method : get_visit_goods
    * @description : 获取用户访问的商品信息
    * @params :
    *       $data : 传递的参数(访问url)
    * @return : array
    * @author : 
    * @date : 
    */
    public function get_visit_goods(&$data, &$obj){
        if($data['url']){
            $arr_url = pathinfo($data['url']);
            if(!strpos($arr_url['basename'], '.html')){
                $obj->send_user_error(app::get('b2c')->_('参数错误！'), array());
            }else{
                if(!strpos($arr_url['basename'], '-')){
                    $obj->send_user_error(app::get('b2c')->_('缺失参数！'), array());
                }else{
                    $arr_params = explode('-', $arr_url['basename']);
                    foreach($arr_params as $key => $value){
                        if($value === ''){
                            unset($arr_params[$key]);
                        }
                    }

                    if($arr_params[0] != 'product'){
                        $obj->send_user_error(app::get('b2c')->_('参数错误！'), array());
                    }else{
                        if(!isset($arr_params[1]) || !$arr_params[1]){
                            $obj->send_user_error(app::get('b2c')->_('缺失参数！'), array());
                        }

                        //if(isset($arr_params[2]) && !$arr_params[2]){
                        //    $obj->send_user_error(app::get('b2c')->_('缺失参数！'), array());
                        //}

                        $objGoods = &$this->app->model('goods');
                        $objImage = kernel::single('base_storager');
                        $goods_arr = array();

                        //商品基本信息 goods表获取
                        $goods = $objGoods->getList('name,price,image_default_id', array('goods_id' => $arr_params[1]));

                        if($goods[0] && $goods[0]['image_default_id']){
                            $image = $objImage->image_path($goods[0]['image_default_id'], 's');
                            $goods[0]['s_image_url'] = $image;//商品小图路径
                        }

                        //由商品获取货品及相关信息
//                        $list2dump = kernel::single("b2c_goods_list2dump");
//                        $aGoods = $list2dump->get_goods($goods[0]);
//
//                        if(!$aGoods || $aGoods === false || !$aGoods['product']){
//                            $obj->send_user_error(app::get('b2c')->_('无效商品，可能是商品未上架！'), array());
//                        }

                        //获取参加活动的商品的价格
                        /*if(isset($arr_params[2])){
                            if($arr_params[2] == 'time'){
                                //限时购活动的商品信息
                                $timedbuy = $this->getTimedBuy($arr_params[1]);
                                $goods[0]['price'] = $timedbuy['price'];
                            }
                        }*/
                        $timedbuy = $this->getTimedBuy($arr_params[1]);
                        if($timedbuy)
                        $goods[0]['preferential_price'] = $timedbuy['price'];

//                        $goods_arr['products'] = $aGoods['product'];
//                        $temp_arr = array('product_id', 'goods_id', 'name', 'mktprice', 'lv_price', 'price');
//
//                        //获取需要的货品信息
//                        if(!empty($goods_arr['products'])){
//                            foreach($goods_arr['products'] as $pkey => $pvalue){
//                                $pvk = array_keys($pvalue);
//
//                                foreach($pvk as $k => $v){
//                                    if(!in_array($v, $temp_arr)){
//                                        unset($goods_arr['products'][$pkey][$v]);
//                                    }
//                                }
//                            }
//                        }

//                        $goods_arr['images'] = $aGoods['images'];//商品对应的图片

                        $goods_arr['small_image_url'] = $goods[0]['s_image_url'];
                        $goods_arr['goods_name'] = $goods[0]['name'];
                        $goods_arr['price'] = $goods[0]['price'];
                        $goods_arr['preferential_price'] = $goods[0]['preferential_price'];
                        return (array)$goods_arr;
                    }
                }
            }
        }else{
            $obj->send_user_error(app::get('b2c')->_('缺失参数！'), array());
        }
    }
    
    /*
    * @method : getTimedBuy
    * @description : 获取限时购活动的商品信息
    * @params :
    *       $gid : 商品id
    * @return : array
    * @author : 
    * @date : 
    */
    public function getTimedBuy($gid){
        $oTimeGoods = app::get('timedbuy')->model('businessactivity');

        $timeGoods = $oTimeGoods->getList('*', array('gid' => $gid));
        $timeGoods = $timeGoods[0];

        return $timeGoods;
    }
    
    /*
    * @method : getLatestOrder
    * @description : 获取会员最近一笔订单信息
    * @params :
    *       $data : 传递的参数信息(member_id:会员id, start_time:起始时间(无实际作用，可不传))
    * @return : array
    * @author : 
    * @date : 
    */
    public function getLatestOrder(&$data, &$obj){
        if(!$data['member_id']){
            $obj->send_user_error(app::get('b2c')->_('缺失参数！'), array());
        }else{
            $filter['member_id'] = intval($data['member_id']);
        }

        if(isset($data['start_time']) && $data['start_time']){
            $filter['createtime|bthan'] = strtotime($data['start_time']);
        }
        
        if(!$data['main_account']){
            $obj->send_user_error(app::get('b2c')->_('缺失参数！'), array());
        }else{
            $objMember = &$this->app->model('members');
            $store = $objMember->db->selectrow('select store_id from sdb_business_storemanger as s join sdb_b2c_members as m on s.account_id=m.member_id where m.im_webcall=\''.$data['main_account'].'\'');
            $filter['store_id'] = $store['store_id']?intval($store['store_id']):0;
        }

        $objOrder = &$this->app->model('orders');
        $objOrderItems = &$this->app->model('order_items');
        $objMember = &app::get('pam')->model('account');

        //获取订单信息
        $orders = $objOrder->getList('order_id,total_amount,member_id,status,itemnum', $filter, 0, -1, 'createtime DESC');
        $order = $orders[0];

        if($order){
            if($order['status'] == 'active'){
                $order['status'] = app::get('b2c')->_('活动订单');
            }elseif($order['status'] == 'finish'){
                $order['status'] = app::get('b2c')->_('已完成订单');
            }else{
                $order['status'] = app::get('b2c')->_('已作废订单');
            }

            //获取订单明细信息
            $order_items = $objOrderItems->getList('name,price,amount,nums', array('order_id' => $order['order_id']));
            $order['items'] = $order_items;

            //获取会员信息
            $member = $objMember->dump(array('account_id' => $order['member_id']), 'login_name');
            $order['user_name'] = $member['login_name'];
        }
        return (array)$order;
    }
    
    /*
    * @method : orderNotice
    * @description : 订单通知(订单创建成功后，通知客服)
    * @params :
    *       $aConfig : 传递的参数信息(包含：订单编号(n),订单创建时间(t),订单状态(s),订单内容(c))
    * @return : int(调用结果状态)
    * @author : 
    * @date : 
    */
    public function orderNotice($order_id,$type = 0){
        $header = "Content-type: text/xml;";
        $host = defined('WEBCALL_HOST')?WEBCALL_HOST:'';
        define('WEBCALL_ORDERNOTICE_URL',$host.'/orderNotice.aspx');
        if(defined('WEBCALL_ORDERNOTICE_URL')){
            $url = WEBCALL_ORDERNOTICE_URL;

            if(!empty($order_id)){
                $objOrder = &$this->app->model('orders');
                $orderInfo = $objOrder->dump(array('order_id' => $order_id), 'createtime,status,total_amount,store_id');
                $webcall = $objOrder->db->select("select im_webcall from sdb_b2c_members as m join sdb_business_storemanger as s on m.member_id=s.account_id and s.store_id={$orderInfo['store_id']}");
                $webcall = $webcall[0]['im_webcall'];

                if($orderInfo){
                    if($orderInfo['status'] == 'active'){
                        $orderInfo['status'] = '活动订单';
                    }
        //            elseif($orderInfo['status'] == 'finish'){
        //                $orderInfo['status'] = '已完成订单';
        //            }else{
        //                $orderInfo['status'] = '已作废订单';
        //            }

                    $objItems = &$this->app->model('order_items');
                    $orderItems = $objItems->getList('name,price,nums', array('order_id' => $order_id));

                    if($orderItems){
                        $itemsInfo = '';
                        foreach($orderItems as $item_key => $item_value){
                            $itemsInfo .= $item_value['name'] . ':' . '单价(' . $item_value['price'] . ')，数量(' . $item_value['nums'] . ')；';
                        }
                        $itemsInfo .= '总计：' . $orderInfo['total_amount'] . '元。';

                        $aConfig['s'] = $orderInfo['status'];
                        $aConfig['t'] = date('Y-m-d H:i:s', $orderInfo['createtime']);
                        $aConfig['n'] = $order_id;
                        $aConfig['c'] = $itemsInfo;
                        $aConfig['l'] = kernel::base_url(true).app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderdetail','arg'=>$order_id));
                        $aConfig['w'] = $type;
                        $aConfig['u'] = $webcall;

                        $aPost = array();
                        foreach($aConfig as $key => $value){
                            $aPost[] = $key . "=" . urlencode($value);
                            //$aPost[$key] = urlencode($value);
                        }
                        $sUrl = $url . "?" . join("&", $aPost);
                        $ch = curl_init();
                        //设置选项，包括URL
                        curl_setopt($ch, CURLOPT_URL, $sUrl);
                        curl_setopt($ch, CURLOPT_HEADER, $header);
                        //执行
                        $return = curl_exec($ch);
                        //释放curl句柄
                        curl_close($ch);
                        // error_log(print_r($return,true),3,'e:/1.txt');
                        return $return;
                        $result = $this->net->get($sUrl, $header);//get方式请求接口
                        return $result;
                    }
                }
            }
        }
    }
    
    public function getClientStoreInfo(&$data, &$obj){
        if(!$data['main_account'] || !$data['member_id']){
            $obj->send_user_error(app::get('b2c')->_('缺失参数！'), array());
        }

        $objMember = &$this->app->model('members');
        $store_info = $objMember->db->selectrow('select s.store_id,s.store_name,s.image,s.area,s.approved_time,s.apply_time from sdb_business_storemanger as s join sdb_b2c_members as m on s.account_id=m.member_id where m.im_webcall=\''.$data['main_account'].'\'');
        $filter['store_id'] = $store_info['store_id']?intval($store_info['store_id']):0;
        $store_info['image'] = kernel::single('base_storager')->image_path($store_info['image'],'m');
        $store_info['url'] = kernel::base_url(true).app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_shop','act'=>'view','arg'=>$filter['store_id']));
        unset($store_info['store_id']);
        $store_info['area'] = explode(':',$store_info['area']);
        $store_info['area'] = $store_info['area'][1];
        $store_info['area'] = explode('/',$store_info['area']);
        $store_info['area'] = $store_info['area'][0];
        $objComment = app::get('business')->model('comment_stores_point');
        $point = $objComment->getStoreInfo($filter['store_id']);
        foreach($point['store_point'] as $item){
            $desc = '';
            if($item['avg_percent'] > 0)
                $desc = "低于";
            elseif($item['avg_percent'] < 0)
                $desc = "高于";
            else
                $desc = "持平";
            $store_info['point'][] = array('lable'=>$item['name'],'avg'=>$item['avg_point'],'desc'=>$desc,'percent'=>abs($item['avg_percent']).'%');
        }
        $store_info['store_title'] = $store_info['store_name'].((isset($data['shopman'])&&!$data['shopman'])?'的掌柜':'的伙计');
        $store_info['register_time'] = date('Y-m-d',$store_info['approved_time']);
        $store_info['store_create'] = date('Y-m-d',$store_info['apply_time']);
        
        $imageDefault = app::get('image')->getConf('image.set');
        $goods_info = $objMember->db->select("select g.goods_id,g.name,g.image_default_id,price,g.udfimg,g.thumbnail_pic from sdb_b2c_goods_view_history as h join sdb_b2c_goods as g on h.goods_id=g.goods_id and g.goods_type='normal' and g.marketable='true' where h.member_id=".intval($data['member_id'])." order by h.last_modify desc limit 0,1");
        $store_info['view_goods'] = array();
        foreach((array)$goods_info as $item){
            $timedbuy = $this->getTimedBuy($item['goods_id']);
            $store_info['view_goods'][] = array(
                'name' => $item['name'],
                'url' => kernel::base_url(true).app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg'=>$item['goods_id'])),
                'image' => kernel::single('base_storager')->image_path((($item['udfimg']=='true')?($item['thumbnail_pic']?$item['thumbnail_pic']:$imageDefault['S']['default_image']):($item['image_default_id']?$item['image_default_id']:$imageDefault['S']['default_image'])),'m'),
                'price' => $item['price'],
                'preferential_price' => ($timedbuy?$timedbuy['price']:''),
            );
        }
        return (array)$store_info;
    }
}