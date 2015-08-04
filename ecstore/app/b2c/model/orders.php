<?php

 

class b2c_mdl_orders extends dbeav_model{
    var $has_tag = true;
    var $defaultOrder = array('createtime','DESC');
    var $has_many = array(
        'order_objects'=>'order_objects',
        'order_pmt'=>'order_pmt'
    );
    
    public $arr_print_type = array(
        'ORDER_PRINT_CART' => 1,
        'ORDER_PRINT_SHEET' => 2,
        'ORDER_PRINT_MERGE' => 4,
        'ORDER_PRINT_DLY' => 8,
    );
    
    /**
     * 得到唯一的订单编号
     * @params null
     * @return string 订单编号
     */
    public function gen_id()
    {
        $i = rand(0,999999);
        do{
            if(999999==$i){
                $i=0;
            }
            $i++;
            $order_id = date('YmdH').str_pad($i,6,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('SELECT order_id from sdb_b2c_orders where order_id ='.$order_id);
        }while($row);
        return $order_id;
    }
    
    /**
     * 重载订单标准数据
     * @params array - standard data format
     * @params boolean 是否必须强制保存
     */
    public function save(&$sdf,$mustUpdate = null)
    {
        $info_object = kernel::service('sensitive_information');
        if(is_object($info_object)) $info_object->opinfo($sdf,'b2c_mdl_orders',__FUNCTION__);
        $is_save = parent::save($sdf, $mustUpdate);
        return $is_save;
    }
    
    /**
     * 通过会员的编号得到orders标准数据格式
     * @params string member id
     * @params string page number
     * @params array order status
     * @return array sdf 数据
     */
    public function fetchByMember($member_id, $nPage, $order_status=array(),$arr_order=null,$arrayorser=null)
    {
        #$limit = $this->app->getConf("selllog.display.listnum");
        if (!$limit) 
            $limit = 10;
        $limitStart = $nPage * $limit;
        if (!$order_status)
            $filter = array('member_id' => $member_id);
        else
        {
            $filter = array(
                'member_id' => $member_id,
            );
            
            if (isset($order_status['pay_status']))
                $filter['pay_status'] = $order_status['pay_status'];
            if (isset($order_status['ship_status']))
                $filter['ship_status'] = $order_status['ship_status'];
            if (isset($order_status['status']))
                $filter['status'] = $order_status['status'];
        }
        
        //根据订单状态搜索订单 --start 
        if(isset($arr_order)&&!empty($arr_order)){

           foreach($arr_order as $key=>$v){
               $filter[$key] = $v;
           }

        }//--end
        
        //根据搜索条件搜索订单 --start 
        if(isset($arrayorser)&&!empty($arrayorser)){
            $temp=array();
            foreach($arrayorser as $item){
                $temp[]=$item['order_id'];
            }
            $filter['order_id|in']=$temp; 
        }//--end
        $sdf_orders = $this->getList('*', $filter, $limitStart, $limit, 'createtime DESC');
        
        // 生成分页组建
        $countRd = $this->count($filter);
        $total = ceil($countRd/$limit);
        $current = $nPage;
        $token = '';
        $arrPager = array(
            'current' => $current,
            'total' => $total,
            'token' => $token,
        );
        
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        foreach ($sdf_orders as &$arr_order)
        {
            $arr_order = $this->dump($arr_order['order_id'], '*', $subsdf);
        }
        $arrdata['data'] = $sdf_orders;
        $arrdata['pager'] = $arrPager;
        
        return $arrdata;
    }
    
    /**
     * 返回订单字段的对照表
     * @params string 状态
     * @params string key value
     */
    public function trasform_status($type='status', $val)
    {
        switch($type){
            case 'status':
                $tmpArr = array(
                            'active' => app::get('b2c')->_('活动'),
                            'finish' => app::get('b2c')->_('完成'),
                            'dead' => app::get('b2c')->_('死单'),
                );
                return $tmpArr[$val];
            break;
            case 'pay_status':
                $tmpArr = array(
                            0 => app::get('b2c')->_('未付款'),
                            1 => app::get('b2c')->_('已付款'),
                            2 => app::get('b2c')->_('付款至担保方'),
                            3 => app::get('b2c')->_('部分付款'),
                            4 => app::get('b2c')->_('部分退款'),
                            5 => app::get('b2c')->_('已退款'),
                );
                return $tmpArr[$val];
            break;
            case 'ship_status':
                $tmpArr = array(
                            0 => app::get('b2c')->_('未发货'),
                            1 => app::get('b2c')->_('已发货'),
                            2 => app::get('b2c')->_('部分发货'),
                            3 => app::get('b2c')->_('部分退货'),
                            4 => app::get('b2c')->_('已退货'),
                );
                return $tmpArr[$val];
            break;
        }
    }
    
    /**
     * 取到最新的order
     * @params int 最新显示的数量
     * @return array 数据结果
     */
    public function getLastestOrder($number)
    {
        return $this->getList('*', array(), 0, $number, 'createtime DESC');
    }
    
    /**
     *  得到货品信息
     * @param $orderid
     * @param $goodsbn
     * @return array 货品信息数组
     */
    public function getProductInfo($orderid, $goodsbn){
        $aOrder = parent::dump($orderid, 'member_id');
        $mdl_goods = $this->app->model('goods');
        $mdl_products = $this->app->model('products');
        $subsdf = array('product'=>array('*'));
        $filter = array('bn'=>$goodsbn,'marketable'=>'true');
        $goods = $mdl_goods->getList('*',$filter);
        $goods = $goods[0]; 
        $aProduct = $mdl_products->getList('*',array('goods_id'=>$goods['goods_id'],'bn'=>$goodsbn));
        $aProduct = $aProduct[0];
       if(!$aProduct['product_id']) return 'none';

        if($goods['nostore_sell']=='0' && !is_null($aProduct['store']) && $aProduct['store']-intval($aProduct['freez']) < 1){
            return 'understock';
        }
        if($aOrder['member_id']){
            $oMember = $this->app->model('members');
            $aMember = $oMember->dump($aOrder['member_id'], 'member_lv_id');
            $mdl_goods_lv_price = $this->app->model('goods_lv_price');
            $filter = array('product_id'=>$aProduct['product_id'],'level_id'=>intval($aMember['member_lv_id']));
            $mPrice = $mPrice[0];
            if(!$mPrice['mprice']){
                $mPrice['mprice'] = $aProduct['price'];
            }
        }else{
            $oLevel = $this->app->model('member_lv');
            $aLevel = $oLevel->getList('dis_count', array('default_lv'=>1));
            $mPrice['mprice'] = $aProduct['price'] * ($aLevel[0]['dis_count'] ? $aLevel[0]['dis_count'] : 1);
        }
        $except_obj_id = array();
        $obj_order_object = $this->app->model('order_objects');
        $arr_order_object = $obj_order_object->getList('obj_id,obj_type', array('order_id'=>$orderid));
        if ($arr_order_object)
        {
            foreach ($arr_order_object as $str_order_object)
            {
                if ($str_order_object['obj_type'] != 'goods' && $str_order_object['obj_type'] != 'gift')
                {
                    $except_obj_id[] = $str_order_object['obj_id'];
                }
            }
        }
        $mdl_order_items = $this->app->model('order_items');
        $order_items = $mdl_order_items->getList('*',array('order_id'=>$orderid,'product_id'=>$aProduct['product_id'],'item_type'=>'product','obj_id|notin'=>$except_obj_id));
        if($order_items){
            return 'exist';
        }

        return array_merge($aProduct, $mPrice);
    }
    
    /**
     * smarty 修改订单备注的显示
     * @param array 出入的设置参数
     * @return string remark、
     */
    public function get_order_remark_display($remark='')
    {   
        $arr_remark = unserialize(trim($remark));
        $arr_mark = array();
        if ($arr_remark)
        {
            foreach ($arr_remark as $remark_info)
            {
                if (is_int($remark_info['add_time']))
                    $arr_mark[] = "Marked by ".$remark_info['op_name'].", " . $remark_info['mark_text'] . ", " . date('Y-m-d H:i:s', $remark_info['add_time']);
                else
                    $arr_mark[] = "Marked by ".$remark_info['op_name'].", " . $remark_info['mark_text'] . ", " . $remark_info['add_time'];
            }
        }
        
        return $arr_mark;
    }
    
    /**
     * 得到特定订单的所有日志
     * @params string order id
     * @params int page num
     * @params int page limit
     * @return array log list
     */
    public function getOrderLogList($order_id, $page=0, $limit=-1)
    {
        $obj_orderloglist = kernel::service('b2c_change_orderloglist');
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        if(!is_object($obj_orderloglist) || $logisticst == 'false')
        {
            $objlog = $this->app->model('order_log');
            $arrlogs = array();
            $arr_returns = array();
            
            if ($limit < 0)
            {
                $arrlogs = $objlog->getList('*', array('rel_id' => $order_id));
            }
            
            $limitStart = $page * $limit;
            
            $arrlogs_all = $objlog->getList('*', array('rel_id' => $order_id));
            $arrlogs = $objlog->getList('*', array('rel_id' => $order_id), $limitStart, $limit);
            if ($arrlogs)
            {
                foreach ($arrlogs as &$logitems)
                {
                    switch ($logitems['behavior'])
                    {
                        case 'creates':
                            $logitems['behavior'] = app::get('b2c')->_("创建");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }                           
                            }
                            break;
                        case 'updates':
                            $logitems['behavior'] = app::get('b2c')->_("修改");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }                           
                            }
                            break;
                        case 'payments':
                            $logitems['behavior'] = app::get('b2c')->_("支付");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2]);
                                }                           
                            }
                            break;
                        case 'refunds':
                            $logitems['behavior'] = app::get('b2c')->_("退款");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }                           
                            }
                            break;
                        case 'delivery':
                            $logitems['behavior'] = app::get('b2c')->_("发货");
                            /** 处理日志中的语言包问题 **/
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {                       
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2],$arr_log['data'][3],$arr_log_text['data'][4],$arr_log['data'][5]);
                                }
                            }
                            break;
                        case 'reship':
                            $logitems['behavior'] = app::get('b2c')->_("退货");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2],$arr_log['data'][3],$arr_log_text['data'][4],$arr_log['data'][5]);
                                }                           
                            }
                            break;
                        case 'finish':
                            $logitems['behavior'] =  app::get('b2c')->_("完成");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }                           
                            }
                            break;
                        case 'cancel':
                            $logitems['behavior'] = app::get('b2c')->_("作废");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }                           
                            }
                            break;
                        case 'change_price':
                            $logitems['behavior'] = app::get('b2c')->_("修改价格");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }                           
                            }
                            break;
                        case 'extend_time':
                            $logitems['behavior'] = app::get('b2c')->_("延长收货时间");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }                           
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            
            $arr_returns['page'] = count($arrlogs_all);
            $arr_returns['data'] = $arrlogs;
            
            return $arr_returns;
        }
        else
        {
            return $obj_orderloglist->getOrderLogList($order_id, $page, $limit);
        }
        
    }
    
    /** 
     * 重写getList方法
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_list = parent::getList($cols,$filter,$offset,$limit,$orderType);
        $obj_extends_order_service = kernel::servicelist('b2c_order_extends_actions');
        if ($obj_extends_order_service)
        {
            foreach ($obj_extends_order_service as $obj)
                $obj->extend_list($arr_list);
        }
        $info_object = kernel::service('sensitive_information');
        if(is_object($info_object)) $info_object->opinfo($arr_list,'b2c_mdl_orders',__FUNCTION__);
        return $arr_list;
    }
    
    /**
     * 订单的邮件触发器
     * @params string 订单处理动作
     * @params array 订单数据
     * @params int member id
     */
    public function fireEvent($action , &$object, $member_id=0)
    {
         $trigger = &$this->app->model('trigger');
         
         return $trigger->object_fire_event($action, $object, $member_id, $this);
    }
    
    /**
     * filter字段显示修改
     * @params string 字段的值
     * @return string 修改后的字段的值
     */
    public function modifier_payment($row)
    {
        if ($row == '-1')
        {
            // 货到付款
            return app::get('b2c')->_('货到付款');
        }
        
        $obj_paymentmethod = app::get('ectools')->model('payment_cfgs');
        $arr_data = $obj_paymentmethod->getPaymentInfo($row);
        
        return $arr_data['app_name'] ? $arr_data['app_name'] : $row;
    }
    
    public function modifier_member_id($row)
    {
        if ($row === 0 || $row == '0'){
            return app::get('b2c')->_('非会员顾客');
        }    
        else{
            $obj_member = app::get('pam')->model('account');
            $sdf = $obj_member->dump($row);
            return $sdf['login_name'];
        }
    }
    
    public function modifier_final_amount($row)
    {
        $currency = app::get('ectools')->model('currency');
        $filter = array('order_id' => $this->pkvalue);
        $tmp = $this->getList('currency', $filter);        
        $arr_cur = $currency->getcur($tmp[0]['currency']);
        $row = $currency->changer_odr($row,$tmp[0]['currency'],false,true,$this->app->getConf('system.money.decimals'),$this->app->getConf('system.money.operation.carryset'));
        
        return $row;
    }
    
    public function modifier_mark_text($row)
    {
        $arr_mark = $this->get_order_remark_display($row);
        $mark_text = "";
        if ($arr_mark)
        {
            foreach ($arr_mark as $str_mark)
            {
                $mark_text .= $str_mark . ", ";
            }
        }
        if ($mark_text)
            $mark_text = substr($mark_text, 0, strlen($mark_text)-2);
        
        return $mark_text;
    }
    
    /**
     * 生成销售记录
     * @param array 发货数据
     * @return boolean 成功与否
     */
    public function addSellLog($data)
    {
        return true; 
        /**
         * 取到用户信息
         */
        $orderData = $this->db->selectrow('SELECT o.member_id, m.login_name,o.ship_email FROM sdb_b2c_orders o LEFT JOIN sdb_pam_account m ON o.member_id = m.account_id WHERE o.order_id = '.$this->db->quote($data['order_id']));
        
        /**
         * 取到订单明细信息
         */
        $orderItem = $this->db->select('SELECT i.price, p.goods_id, i.product_id, p.name,p.spec_info, i.nums FROM sdb_b2c_order_items i LEFT JOIN sdb_b2c_products p ON p.product_id = i.product_id WHERE i.order_id = '.$this->db->quote($data['order_id']));
        
        foreach( $orderItem as $iKey => $iValue ){
            $sql = 'INSERT INTO sdb_b2c_sell_logs (member_id,name,price,goods_id,product_id,product_name,spec_info,number,createtime) VALUES ( "'.($orderData['member_id']?$orderData['member_id']:0).'", "'.($orderData['login_name']?$orderData['login_name']:$orderData['ship_email']).'", "'.$iValue['price'].'", "'.$iValue['goods_id'].'", "'.$iValue['product_id'].'", "'.htmlspecialchars($iValue['name']).'", "'.$iValue['spec_info'].'" , "'.$iValue['nums'].'", "'.time().'" )';
            $this->db->exec($sql);
        }
    }
    
    function _filter($filter,$tableAlias=null,$baseWhere=null){
        if (isset($filter) && $filter && is_array($filter) && array_key_exists('member_login_name', $filter))
        {
            $obj_pam_account = app::get('pam')->model('account');
            $pam_filter = array(
                'login_name|has'=>$filter['member_login_name'],
            );
            $row_pam = $obj_pam_account->getList('*',$pam_filter);
            $arr_member_id = array();
            if ($row_pam)
            {
                foreach ($row_pam as $str_pam)
                {
                    $arr_member_id[] = $str_pam['account_id'];
                }
                $filter['member_id|in'] = $arr_member_id;
            }
            else
            {
                if ($filter['member_login_name'] == app::get('b2c')->_('非会员顾客'))
                    $filter['member_id'] = 0;
            }
            unset($filter['member_login_name']);
        }        

        foreach(kernel::servicelist('b2c_mdl_orders.filter') as $k=>$obj_filter){
            if(method_exists($obj_filter,'extend_filter')){
                $obj_filter->extend_filter($filter);
            }
        }
        $info_object = kernel::service('sensitive_information');
        if(is_object($info_object)) $info_object->opinfo($filter,'b2c_mdl_orders',__FUNCTION__);
        $filter = parent::_filter($filter);
        return $filter;
    }
    
    /**
     * 重写搜索的下拉选项方法
     * @param null
     * @return null
     */
    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                $columns[$k] = $v['label'];
            }
        }
        /** 添加用户名搜索 **/
        $columns['member_login_name'] = app::get('b2c')->_('会员用户名');
        /** end **/

        /** 添加店铺名称搜索 **/
        $columns['storemanger_store_name'] = app::get('b2c')->_('店铺名称');
        /** end **/
        
        /** 添加额外的搜索列 **/
        $arr_extends_options = array();
        foreach (kernel::servicelist('b2c.order.searchOptions.addExtends') as $object)
        {
            if (!isset($object) || !is_object($object)) continue;
            if (method_exists($object, 'get_order'))
                $index = $object->get_order();
            else
                $index = 10;
                
            $arr_extends_options[$index] = $object;
        }
        if ($arr_extends_options)
        {
            ksort($arr_extends_options);
            foreach ($arr_extends_options as $obj)
            {
                $obj->get_extends_cols($columns);
            }
        }
        /** end **/
        
        return $columns;
    }
    
    /**
     * 订单删除之后做的事情
     * @param array post
     * @return boolean
     */
    public function suf_recycle($filter=array())
    {
        if (!$filter)
            $filter = $_GET['p'][0];
            
        $is_update = true;
        $obj_suf_recycles = kernel::servicelist('b2c.order.after_delete');
        if ($obj_suf_recycles)
        {
            foreach ($obj_suf_recycles as $obj_suf)
            {
                $is_update = $obj_suf->dorecycle($filter);
            }
        }
        
        return $is_update;
    }
    
    /**
     * 订单恢复之后做的事情
     * @param array post
     * @return boolean
     */
    public function suf_restore($filter=array())
    {
        $is_update = true;
        $obj_suf_restores = kernel::servicelist('b2c.order.after_restore');
        if ($obj_suf_restores)
        {
            foreach ($obj_suf_restores as $obj_suf)
            {
                $is_update = $obj_suf->dorestore($filter);
            }
        }
        
        return $is_update;
    }
    //订单备注图标2011-11-30
    public function modifier_mark_type($row){
        $res_dir = app::get('b2c')->res_url;
        $row = '<img width="20" height="20" src="'.$res_dir.'/remark_icons/'.$row.'.gif">';
        return $row;
     }
     
     /**
      *订单发送至OCS成功后,释放冻结库存，减去真实库存
      *@param order_id
      *@return boolean
    */
    public function unfreez_order($order_id){
        $order = $this->app->model('orders');
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
         $arrFreez = $obj_checkorder->checkOrderFreez('send_ocs', $order_id);

         $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
         $sdf_order = $order->dump($order_id, 'order_id,status,pay_status,ship_status', $subsdf);

        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type){
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }
        foreach($sdf_order['order_objects'] as $k => $v){
            if ($v['obj_type'] != 'goods' && $v['obj_type'] != 'gift'){

                    foreach ( kernel::servicelist('b2c.order_store_extends') as $object ) {
                        if ( $object->get_goods_type()!=$v['obj_type'] ) continue;
                        $obj_extends_store = $object;
                        if ($obj_extends_store){
                            $obj_extends_store->store_change($v, 'send_ocs','send_ocs');
                        }
                    }
                    continue;
            }

            foreach ($v['order_items'] as $arrItem){

                 $arr_params = array(
                            'goods_id' => $arrItem['products']['goods_id'],
                            'product_id' => $arrItem['products']['product_id'],
                            'number' => $arrItem['quantity'],
                            'quantity'=>$arrItem['quantity'],
                        );

                if ($arrItem['item_type'] == 'product'){
                    $arrItem['item_type'] = 'goods';
                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
                    if ($arrFreez['store']){
                        $str_service_goods_type_obj->minus_store($arr_params);
                    }
                   if ($arrFreez['unfreez']){
                        $str_service_goods_type_obj->unfreezeGoods($arr_params);
                   }
                }else{
                    if (isset($arr_service_goods_type_obj[$arrItem['item_type']]) &&      $arr_service_goods_type_obj[$arrItem['item_type']]){
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
                        if ($arrFreez['store']){
                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
                                $str_service_goods_type_obj->minus_store($arr_params);
                            }
                        if ($arrFreez['unfreez']){
                            $str_service_goods_type_obj->unfreezeGoods($arr_params);
                          }

                    }
                }

            }
        }
        return true;
    }

    /**
     * 重写订单导出方法
     * @param array $data
     * @param array $filter
     * @param int $offset
     * @param int $exportType
     */
    public function fgetlist_csv( &$data,$filter,$offset,$exportType =1 ){
        $limit = 100;
        $cols = $this->_columns();
        if(!$data['title']){
            $this->title = array();
            foreach( $this->getTitle($cols) as $titlek => $aTitle ){
                $this->title[$titlek] = $aTitle;
            }
            // service for add title when export
            foreach( kernel::servicelist('export_add_title') as $services ) {
                if ( is_object($services) ) {
                    if ( method_exists($services, 'addTitle') ) {
                        $this->title = $services->addTitle($this->title);
                    }
                }
            }
            $data['title'] = '"'.implode('","',$this->title).'"';
        }

        if(!$list = $this->getList(implode(',',array_keys($cols)),$filter,$offset*$limit,$limit))return false;
        
        // $data['contents'] = array();
        foreach( $list as $line => $row ){
            // service for add data when export
            foreach( kernel::servicelist('export_add_data') as $services ) {
                if ( is_object($services) ) {
                    if ( method_exists($services, 'addData') ) {
                        $row = $services->addData($row);
                    }
                }
            }
            $rowVal = array();
            foreach( $row as $col => $val ){
                
                if( in_array( $cols[$col]['type'],array('time','last_modify') ) && $val ){
                   $val = date('Y-m-d H:i',$val)."\t";
                }
                if ($cols[$col]['type'] == 'longtext'){
                    if (strpos($val, "\n") !== false){
                        $val = str_replace("\n", " ", $val);
                    }
                }

                if(strlen($val) > 8 && eregi("^[0-9]+$",$val)){
                    $val .= "\r";
                }
                
                if( strpos( (string)$cols[$col]['type'], 'table:')===0 ){
                    $subobj = explode( '@',substr($cols[$col]['type'],6) );
                    if( !$subobj[1] )
                        $subobj[1] = $this->app->app_id;
                    $subobj = &app::get($subobj[1])->model( $subobj[0] );
                    $subVal = $subobj->dump( array( $subobj->schema['idColumn']=> $val ),$subobj->schema['textColumn'] );
                    $val = $subVal[$subobj->schema['textColumn']]?$subVal[$subobj->schema['textColumn']]:$val;
                }

                if( array_key_exists( $col, $this->title ) )
                    $rowVal[] = addslashes(  (is_array($cols[$col]['type'])?$cols[$col]['type'][$val]:$val ) );
            }

            $data['contents'][] = '"'.implode('","',$rowVal).'"';
        }
        return true;

    }
    function getTitle(&$cols){
        $title = array();
        $title_lang = array(
            'score_u' => '使用积分',
            'score_g' => '获得积分',
            'pmt_order' => '使用优惠券',
        );
        foreach( $cols as $col => $val ){
            //if( !$val['deny_export'] )
            if(!empty($title_lang[$col])) {
                $title[$col] = $title_lang[$col].'('.$col.')';
            } else {
                $title[$col] = $val['label'].'('.$col.')';
            }
        }

        return $title;
    }


     function  getrefundsbystoreid($store_id,$start,$end=null) {

        $sql = "SELECT ROUND(AVG((sdb_ectools_refunds.t_payed - IFNULL(sdb_aftersales_return_product.add_time ,0))/(60*60*24)),0) AS days  FROM sdb_b2c_orders 
                        LEFT JOIN sdb_ectools_order_bills  ON sdb_b2c_orders.order_id = sdb_ectools_order_bills.rel_id
                        LEFT JOIN sdb_ectools_refunds ON sdb_ectools_order_bills.bill_id = sdb_ectools_refunds.refund_id
                        LEFT JOIN sdb_aftersales_return_product ON sdb_aftersales_return_product.order_id=sdb_b2c_orders.order_id
                        WHERE sdb_ectools_order_bills.bill_type='refunds' AND sdb_ectools_order_bills.pay_object='order'
                        AND  sdb_ectools_refunds.refund_type='1'
                        AND  sdb_b2c_orders.store_id = '{$store_id}'   AND sdb_b2c_orders.`status` <>'dead'
                        AND   sdb_b2c_orders.createtime > {$start} ";
        if($end){
             $sql .="  AND  sdb_b2c_orders.createtime < {$end} ";

        }

       //$sql .=' GROUP BY sdb_b2c_orders.order_id';


       $row = $this->db->select($sql);

       return $row;


    }

     function  getxrefundsbystoreid($store_id,$start,$end=null) {

        $sql = "SELECT  sdb_b2c_orders.order_id  FROM sdb_b2c_orders 
                        LEFT JOIN sdb_ectools_order_bills  ON sdb_b2c_orders.order_id = sdb_ectools_order_bills.rel_id
                        LEFT JOIN sdb_ectools_refunds ON sdb_ectools_order_bills.bill_id = sdb_ectools_refunds.refund_id
                        LEFT JOIN sdb_aftersales_return_product ON sdb_aftersales_return_product.order_id=sdb_b2c_orders.order_id
                        WHERE sdb_ectools_order_bills.bill_type='refunds' AND sdb_ectools_order_bills.pay_object='order'
                        AND  sdb_ectools_refunds.refund_type='1'
                        AND  sdb_aftersales_return_product.is_intervene IN ('2','3','4')
                        AND  sdb_b2c_orders.store_id = '{$store_id}'  AND sdb_b2c_orders.`status` <>'dead'
                        AND  sdb_b2c_orders.createtime > {$start} ";
        if($end){
             $sql .="  AND  sdb_b2c_orders.createtime < {$end} ";

        }

       $sql .=' GROUP BY sdb_b2c_orders.order_id';

       //print_r($sql);

       $row = $this->db->select($sql);

       return $row;


    }



    function  getcounterrefundsbystoreid($store_id,$start,$end=null) {

        $sql = "SELECT ROUND(AVG((sdb_ectools_refunds.t_payed - IFNULL(sdb_aftersales_return_product.add_time ,0))/(60*60*24)),0) AS days  FROM sdb_b2c_orders 
                        LEFT JOIN sdb_ectools_order_bills  ON sdb_b2c_orders.order_id = sdb_ectools_order_bills.rel_id
                        LEFT JOIN sdb_ectools_refunds ON sdb_ectools_order_bills.bill_id = sdb_ectools_refunds.refund_id
                        LEFT JOIN sdb_aftersales_return_product ON sdb_aftersales_return_product.order_id=sdb_b2c_orders.order_id
                        WHERE sdb_ectools_order_bills.bill_type='refunds' AND sdb_ectools_order_bills.pay_object='order'
                        AND  sdb_ectools_refunds.refund_type='1'
                        AND  sdb_b2c_orders.store_id in ( {$store_id}) AND sdb_b2c_orders.`status` <>'dead'
                        AND   sdb_b2c_orders.createtime > {$start} ";
        if($end){
             $sql .="  AND  sdb_b2c_orders.createtime < {$end} ";

        }

        //$sql .=' GROUP BY sdb_b2c_orders.order_id';

     

       $row = $this->db->select($sql);

       return $row;


    }


     function  getcounterxrefundsbystoreid($store_id,$start,$end=null) {

        $sql = "SELECT sdb_b2c_orders.order_id FROM sdb_b2c_orders  FROM sdb_b2c_orders 
                        LEFT JOIN sdb_ectools_order_bills  ON sdb_b2c_orders.order_id = sdb_ectools_order_bills.rel_id
                        LEFT JOIN sdb_ectools_refunds ON sdb_ectools_order_bills.bill_id = sdb_ectools_refunds.refund_id
                        LEFT JOIN sdb_aftersales_return_product ON sdb_aftersales_return_product.order_id=sdb_b2c_orders.order_id
                        WHERE sdb_ectools_order_bills.bill_type='refunds' AND sdb_ectools_order_bills.pay_object='order'
                        AND  sdb_ectools_refunds.refund_type='1'
                        AND  sdb_b2c_orders.store_id in ( {$store_id}) AND sdb_b2c_orders.`status` <>'dead'
                        AND   sdb_b2c_orders.createtime > {$start} ";
        if($end){
             $sql .="  AND  sdb_b2c_orders.createtime < {$end} ";

        }

        $sql .=' GROUP BY sdb_b2c_orders.order_id';

     

       $row = $this->db->select($sql);

       return $row;


    }



    function  report_refunds($store_id){
          $sql = "SELECT count(DISTINCT sdb_ectools_refunds.refund_id) as ref_count,
                         count(DISTINCT sdb_b2c_orders.order_id) as order_count,
                         ROUND(count(DISTINCT sdb_ectools_refunds.refund_id)/count(DISTINCT sdb_b2c_orders.order_id),2) AS total_lv,
                         ROUND(AVG((sdb_ectools_refunds.t_payed - IFNULL(sdb_aftersales_return_product.add_time ,0))/(60*60*24)),2) AS total_days,
                         CONCAT(YEAR(FROM_UNIXTIME(sdb_b2c_orders.createtime)),'-', LPAD(month(FROM_UNIXTIME(sdb_b2c_orders.createtime)),2,'0') ) AS total_date
                        FROM sdb_b2c_orders 
                        LEFT JOIN (SELECT * FROM sdb_ectools_order_bills  WHERE sdb_ectools_order_bills.bill_type='refunds' AND sdb_ectools_order_bills.pay_object='order') AS bills
                                  ON sdb_b2c_orders.order_id = bills.rel_id
                        LEFT JOIN (SELECT * FROM sdb_ectools_refunds WHERE   sdb_ectools_refunds.refund_type='1') AS sdb_ectools_refunds ON bills.bill_id = sdb_ectools_refunds.refund_id
                        LEFT JOIN sdb_aftersales_return_product ON sdb_aftersales_return_product.order_id=sdb_b2c_orders.order_id
                        WHERE sdb_b2c_orders.store_id = '{$store_id}' AND sdb_b2c_orders.`status` <>'dead'
                        GROUP BY total_date ORDER BY   total_date DESC  ";

                       
        $row = $this->db->select($sql);

        

       return $row;

    }


    function  report_counterrefunds($store_id){
          $sql = "SELECT count(DISTINCT sdb_ectools_refunds.refund_id) as counter_count,
                         count(DISTINCT sdb_b2c_orders.order_id) as order_count,
                         ROUND(count(DISTINCT sdb_ectools_refunds.refund_id)/count(DISTINCT sdb_b2c_orders.order_id),2) AS counter_lv,
                         ROUND(AVG((sdb_ectools_refunds.t_payed - IFNULL(sdb_aftersales_return_product.add_time ,0))/(60*60*24)),2) AS counter_days,
                         CONCAT(YEAR(FROM_UNIXTIME(sdb_b2c_orders.createtime)),'-', LPAD(month(FROM_UNIXTIME(sdb_b2c_orders.createtime)),2,'0') ) AS total_date
                        FROM sdb_b2c_orders 
                        LEFT JOIN (SELECT * FROM sdb_ectools_order_bills  WHERE sdb_ectools_order_bills.bill_type='refunds' AND sdb_ectools_order_bills.pay_object='order') AS bills
                                  ON sdb_b2c_orders.order_id = bills.rel_id
                        LEFT JOIN (SELECT * FROM sdb_ectools_refunds WHERE   sdb_ectools_refunds.refund_type='1') AS sdb_ectools_refunds ON bills.bill_id = sdb_ectools_refunds.refund_id
                        LEFT JOIN sdb_aftersales_return_product ON sdb_aftersales_return_product.order_id=sdb_b2c_orders.order_id
                        WHERE sdb_b2c_orders.store_id in ( {$store_id})  AND sdb_b2c_orders.`status` <>'dead'
                        GROUP BY total_date ORDER BY   total_date DESC  ";

        $row = $this->db->select($sql);

       return $row;

    }

    
}
