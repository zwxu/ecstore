<?php

 
class business_task{
     
    /**
     * listener array
     */
    private $arr_listener = array(
        'b2c:orders:create' =>'b2c:stats_listener:get_orderinfo',
        'b2c:orders:payed'=>'b2c:stats_listener:get_payinfo',
        'b2c:orders:shipping'=>'b2c:stats_listener:get_deliveryinfo',
        'b2c:orders:returned'=>'b2c:stats_listener:get_deliveryinfo',
        'b2c:member_account:register'=>'b2c:stats_listener:get_memberinfo',
        'b2c:member_account:login'=>'b2c:stats_listener:get_logmember',
        'b2c:member_advance:changeadvance'=>'b2c:stats_listener:get_money',
    );
    
    /**
     * modifiers array
     */
    private $arr_lister_keys = array(
        'b2c:orders:create' =>'ORDERINFO',
        'b2c:orders:payed'=>'PAYINFO',
        'b2c:orders:shipping'=>'SHIPINFO',
        'b2c:orders:returned'=>'SHIPINFO',
        'b2c:member_account:register'=>'MEMBERINFO',
        'b2c:member_account:login'=>'LOGININFO',
        'b2c:member_advance:changeadvance'=>'DESPOITINFO',
    );
    
    function install_options(){
        return array();
    }

    function post_install($options)
    {
        /*//添加订单确认收货时间
        $obj_orders = app::get('b2c')->model('orders');
        $col = array(
           'confirm_time'=>array (
              'type' => 'time',
              'required' => false,
              'label' => app::get('b2c')->_('确认时间'),
              'width' => 110,
              'editable' => false,
            ),
        );
        $obj_orders->meta_register($col);
        //修改添加表的字段
        $obj_meta_value_int = app::get('dbeav')->model('meta_value_int');
        $sql = "ALTER TABLE ".$obj_meta_value_int->table." MODIFY COLUMN `pk` bigint(20) default '0',MODIFY COLUMN `value` bigint(20) default '0';";
        $obj_meta_value_int->db->exec($sql);

        $obj_meta_value_decimal = app::get('dbeav')->model('meta_value_decimal');
        $sql = "ALTER TABLE ".$obj_meta_value_decimal->table." MODIFY COLUMN `pk` bigint(20) default '0';";
        $obj_meta_value_decimal->db->exec($sql);

        //订单添加商铺字段
        $col = array(
           'store_id'=>array(
              'type'=>'table:storemanger@business',
              'required' => true,
              'label' => app::get('b2c')->_('店铺'),
              'width' => 110,
              'editable' => false,
            ),
        );
        $obj_orders->meta_register($col);
        $obj_goods = app::get('b2c')->model('goods');
        $col = array(
            'store_id'=>array(
              'type'=>'table:storemanger@business',
              'required' => false,
              'label' => app::get('b2c')->_('店铺'),
              'width' => 110,
              'editable' => false,
            ),
        );
        $obj_goods->meta_register($col);
        $col = array(
            'goods_state' =>array (
              'type' => array(
                  'new' => app::get('b2c')->_('全新'),
                  'used' => app::get('b2c')->_('二手'),
              ),
              'required' => true,
              'default' => 'new',
              'label' => app::get('b2c')->_('商品状态'),
              'width' => 110,
              'editable' => false,
            ),
        );
        $obj_goods->meta_register($col);
        $col = array(
            'dt_id'=>array(
              'type'=>'table:dlytype@b2c',
              'label' => app::get('b2c')->_('运费模板'),
              'width' => 110,
              'editable' => false,
            ),
        );
        $obj_goods->meta_register($col);
        $obj_dlytype = app::get('b2c')->model('dlytype');
        $col = array(
            'store_id'=>array(
              'type'=>'table:storemanger@business',
              'required' => false,
              'label' => app::get('b2c')->_('店铺'),
              'width' => 110,
              'editable' => false,
            ),
        );
        $obj_dlytype->meta_register($col);
        $obj_meta_value_varchar = app::get('dbeav')->model('meta_value_varchar');
        $sql = "ALTER TABLE ".$obj_meta_value_varchar->table." MODIFY COLUMN `pk` bigint(20) default '0';";
        $obj_meta_value_varchar->db->exec($sql);

        //退款单添加结算单类型
        $obj_refunds = app::get('ectools')->model('refunds');
        $col = array(
              'cart'=>array (
              'type' => 
                  array(
                    '1' => app::get('aftersales')->_('退款单'),
                    '2' => app::get('aftersales')->_('结算单'),
                  ),
              'required' => false,
              'label' => app::get('b2c')->_('确认时间'),
              'width' => 110,
              'editable' => false,
            ),
        );
        $obj_refunds->meta_register($col);


        $obj_member_comments = app::get('b2c')->model('member_comments');
        $col = array(
            'store_id'=>array(
              'type'=>'table:storemanger@business',
              'required' => false,
              'label' => app::get('b2c')->_('店铺'),
              'width' => 110,
              'editable' => false,
            ),
        );
        $obj_member_comments->meta_register($col);*/

        $col = array(
           'confirm_time'=>array (
              'type' => 'time',
              'required' => false,
              'label' => app::get('b2c')->_('确认时间'),
              'width' => 110,
              'editable' => false,
            ),
            'store_id'=>array(
              'type'=>'table:storemanger@business',
              'required' => true,
              'default' => 0,
              'label' => app::get('b2c')->_('店铺名称'),
              'width' => 110,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
            ),
            'refund_status' => 
            array (
              'type' => 
              array (
                0 => '未申请退款',
                1 => '退款申请中,等待卖家审核',
                2 => '卖家拒绝退款',
                3 => '卖家同意退款,等待买家退货',
                4 => '卖家已退款',
                5 => '买家已退货,等待卖家确认收货',
              ),
              'default' => '0',
              'required' => true,
              'label' => '退款状态',
              'width' => 75,
              'editable' => false,
              'filtertype' => 'yes',
              'filterdefault' => true,
              'in_list' => true,
              'default_in_list' => true,
            ),
        );
        $index = array(
            'idx_store_id' => 
            array (
              'columns' => 
              array (
                0 => 'store_id',
              ),
            ),
        );
        $this->schema_install($col,'b2c','orders',$index);

        $col = array(
              'refund_type'=>array (
              'type' => 
                  array(
                    '1' => app::get('aftersales')->_('退款单'),
                    '2' => app::get('aftersales')->_('结算单'),
                  ),
              'required' => false,
              'label' => app::get('b2c')->_('单据种类'),
              'width' => 110,
              'editable' => false,
            ),
        );
        $this->schema_install($col,'ectools','refunds');
        
     
        $col = array(
            'store_id'=>array(
              'type'=>'table:storemanger@business',
              'required' => false,
              'label' => app::get('b2c')->_('店铺名称'),
              'width' => 110,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
            ),
            'goods_state' =>array (
              'type' => array(
                  'new' => app::get('b2c')->_('全新'),
                  'used' => app::get('b2c')->_('二手'),
              ),
              'required' => true,
              'default' => 'new',
              'label' => app::get('b2c')->_('是否全新'),
              'width' => 110,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
            ),
            'freight_bear' =>array (
              'type' => array(
                  'business' => app::get('b2c')->_('商家'),
                  'member' => app::get('b2c')->_('会员'),
              ),
              'required' => true,
              'default' => 'member',
              'label' => app::get('b2c')->_('运费承担'),
              'width' => 110,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
            ),
        );
        $index = array(
            'idx_store_id' => 
            array (
              'columns' => 
              array (
                0 => 'store_id',
              ),
            ),
        );
        $this->schema_install($col,'b2c','goods',$index);

        $col = array(
            'store_id'=>array(
              'type'=>'table:storemanger@business',
              'required' => false,
              'label' => app::get('b2c')->_('店铺名称'),
              'width' => 110,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
            ),
        );
        $index = array(
            'idx_store_id' => 
            array (
              'columns' => 
              array (
                0 => 'store_id',
              ),
            ),
        );
        $this->schema_install($col,'b2c','dlytype',$index);
        
        $col = array(
            'comments_count'=>array (
              'type' => 'int unsigned',
              'default' => 0,
              'required' => true,
              'label' => app::get('b2c')->_('评论次数'),
              'editable' => false,
            ),
        );
        $this->schema_install($col,'b2c','orders');
        
        $col = array(
            'comments_type'=>array (
              'type' => array(
                0 => '解释',
                1 => '评论',
                2 => '回复',
                3 => '追加',
              ),
              'default' => '0',
              'required' => true,
              'label' => app::get('b2c')->_('评论类型'),
              'editable' => false,
            ),
        );
        $this->schema_install($col,'b2c','member_comments');
        
         $col = array(
            'store_id'=>array(
              'type'=>'table:storemanger@business',
              'required' => false,
              'label' => app::get('b2c')->_('店铺名称'),
              'width' => 110,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
            ),
        );
        $index = array(
            'idx_store_id' => 
            array (
              'columns' => 
              array (
                0 => 'store_id',
              ),
            ),
        );
        $this->schema_install($col,'b2c','member_comments',$index);
        //--end
    }
    
    function post_uninstall(){
        /*$obj_orders = app::get('b2c')->model('orders');
        $obj_orders->meta_meta('confirm_time');
        $obj_orders->meta_meta('store_id');
        $obj_meta_value_int = app::get('dbeav')->model('meta_value_int');
        $sql = "ALTER TABLE ".$obj_meta_value_int->table." MODIFY COLUMN `pk` mediumint(8) default '0',MODIFY COLUMN `value` mediumint(8) default '0';";
        $obj_meta_value_int->db->exec($sql);
        $obj_meta_value_decimal = app::get('dbeav')->model('meta_value_decimal');
        $sql = "ALTER TABLE ".$obj_meta_value_decimal->table." MODIFY COLUMN `pk` mediumint(8) default '0';";
        $obj_meta_value_decimal->db->exec($sql);
        $obj_meta_value_varchar = app::get('dbeav')->model('meta_value_varchar');
        $sql = "ALTER TABLE ".$obj_meta_value_varchar->table." MODIFY COLUMN `pk` mediumint(8) default '0';";
        $obj_meta_value_varchar->db->exec($sql);
        $obj_goods = app::get('b2c')->model('goods');
        $obj_goods->meta_meta('store_id');
        $obj_refunds = app::get('ectools')->model('refunds');
        $obj_refunds->meta_meta('cart');*/
        $this->schema_uninstall(array('confirm_time','store_id'),'b2c','orders',array('store_id'));
        $this->schema_uninstall(array('refund_type'),'ectools','refunds');
       
        $this->schema_uninstall(array('store_id','goods_state','freight_bear'),'b2c','goods',array('store_id'));
        $this->schema_uninstall(array('store_id'),'b2c','dlytype',array('store_id'));
        $this->schema_uninstall(array('comments_count'),'b2c','orders');
        $this->schema_uninstall(array('comments_type'),'b2c','member_comments');
       
        $this->schema_uninstall(array('store_id'),'b2c','member_comments',array('store_id'));
        //--end
    }

   /**
	* xml文件的更新操作
	* @param object $app app对象实例
	*/
	private function xml_update($app)
	{
		if (!$app) return;		
		
		$detector = kernel::single('b2c_application_apiv');
		foreach($detector->detect($app) as $name=>$item){
			$item->install();
		}
		
	}

    private function schema_install($col = array(),$app_id,$table_name,$index = array()){
        if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php')){
            $file_path = CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php';
        }else{
            $file_path = ROOT_DIR.'/app/'.$app_id.'/dbschema/'.$table_name.'.php';
        }
        include($file_path);
        if(defined('CUSTOM_CORE_DIR') && is_dir(CUSTOM_CORE_DIR.'/'.$app_id)){
            if(!is_dir(CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema')){
                mkdir(CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema', 0700);
                $filename=CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php';
                $fp=fopen("$filename", "w+"); //打开文件指针，创建文件
                if ( !is_writable($filename) ){
                      die("文件:" .$filename. "不可写，请检查！");
                }
                fclose($fp);  //关闭指针
            }
            $file_path = CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php';
        }
        $db[$table_name]['columns'] = array_merge($db[$table_name]['columns'],$col);
        if($index){
            if($db[$table_name]['index']){
                $db[$table_name]['index'] = array_merge($db[$table_name]['index'],$index);
            }else{
                $db[$table_name]['index'] = $index;
            }
        }
        $schema = "\$db['".$table_name."']=".var_export($db[$table_name],true);
        $schema = "<?php \r\n ".$schema.";";
        file_put_contents($file_path,$schema);        
    }

    private function schema_uninstall($col_names = array(),$app_id,$table_name,$index_names = array()){
        if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php')){
            $file_path = CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php';
        }else{
            $file_path = CORE_DIR.'/app/'.$app_id.'/dbschema/'.$table_name.'.php';
        }
        include($file_path);
        foreach($col_names as $col_name){
            if(array_key_exists($col_name,$db[$table_name]['columns'])){
                unset($db[$table_name]['columns'][$col_name]);
            }
        }
        foreach($index_names as $index_name){
            if(array_key_exists($index_name,$db[$table_name]['index'])){
                unset($db[$table_name]['index'][$index_name]);
            }
        }
        if(empty($db[$table_name]['index'])){
            unset($db[$table_name]['index']);
        }
        $schema = "\$db['".$table_name."']=".var_export($db[$table_name],true);
        $schema = "<?php \r\n ".$schema.";";
        file_put_contents($file_path,$schema);        
    }

}

