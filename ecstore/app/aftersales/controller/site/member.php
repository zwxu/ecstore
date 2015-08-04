<?php


class aftersales_ctl_site_member extends b2c_ctl_site_member
{
	/**
	 * 构造方法
	 * @param object application
	 */
	public function __construct(&$app)
	{
        if(is_array($app)){
            $verify = $app['arg1'];
            $app = $app['app'];
        }else{
            $verify = true;
        }
		$this->app_current = $app;
		$this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c,$verify);
    }

	public function return_policy($app='',$ctl='',$act='')
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('申请售后服务'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->begin($this->gen_url(array('app' => $app, 'ctl' => $ctl, 'act' => $act)));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $this->pagedata['is_open_return_product'] = $arr_settings['is_open_return_product'];
        $this->pagedata['comment'] = $arr_settings['return_product_comment'];
		$this->pagedata['args'] = array($app, $ctl, $act);
        $this->output('aftersales');
    }

	public function return_list($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('售后服务列表'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $clos = "return_id,order_id,add_time,status,member_id,refund_type,is_intervene";
        $filter = array();
        $filter["member_id"] = $this->member['member_id'];
        if( $_POST["title"] != "" ){
            $filter["title"] = $_POST["title"];
        }

        if( $_POST["status"] != "" ){
            $filter["status"] = $_POST["status"];
        }

        if( $_POST["order_id"] != "" ){
            $filter["order_id"] = $_POST["order_id"];
        }

		$this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $aData = $obj_return_policy->get_return_product_list($clos, $filter, $nPage);

        $obj_account = app::get('pam')->model('account');
        //添加用户名
        foreach($aData['data'] as $key=>$val){
            $uname = $obj_account->getRow('login_name',array('account_id'=>$val['member_id']));
            $aData['data'][$key]['uname'] = $uname['login_name'];
        }
        if (isset($aData['data']) && $aData['data'])
            $this->pagedata['return_list'] = $aData['data'];

        $arrPager = $this->get_start($nPage, $aData['total']);
        $this->pagination($nPage, $arrPager['maxPage'], 'return_list', '', 'aftersales', 'site_member');

        $this->output('aftersales');
    }

    public function seller_returns($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('售后服务列表'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $clos = "return_id,order_id,add_time,status,is_intervene";
        $filter = array();
        //$filter["member_id"] = $this->member['member_id'];
        if( $_POST["title"] != "" ){
            $filter["title"] = $_POST["title"];
        }

        if( $_POST["status"] != "" ){
            $filter["status"] = $_POST["status"];
        }

        if( $_POST["order_id"] != "" ){
            $filter["order_id"] = $_POST["order_id"];
        }

        //添加过滤条件
        $sto= kernel::single("business_memberstore",$this->member['member_id']);        
        $filter["store_id"] = $sto->storeinfo['store_id'];
		$this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $aData = $obj_return_policy->get_return_product_list($clos, $filter, $nPage);
        if (isset($aData['data']) && $aData['data'])
            $this->pagedata['return_list'] = $aData['data'];

        $arrPager = $this->get_start($nPage, $aData['total']);
        $this->pagination($nPage, $arrPager['maxPage'], 'return_list', '', 'aftersales', 'site_member');

        $this->output('aftersales');
    }

	public function return_order_list($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('新增退货申请'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $obj_orders = $this->app->model('orders');
        $clos = "order_id,createtime,final_amount,currency";
        $filter = array();
        if( $_POST['order_id'] )
        {
            $filter['order_id|has'] = $_POST['order_id'];
        }
        $filter['member_id'] = $this->member['member_id'];
        $filter['pay_status'] = 1;
        $filter['ship_status'] = 1;

        $aData = $obj_orders->getList($clos, $filter, ($nPage-1)*10, 10);
        if (isset($aData) && $aData)
            $this->pagedata['orders'] = $aData;
        $total = $obj_orders->count($filter);

        $arrPager = $this->get_start($nPage, $total);
        $this->pagination($nPage, $arrPager['maxPage'], 'return_order_list', '', 'aftersales', 'site_member');

        $this->output('aftersales');
    }
    
    public function return_add_before($order_id){
        $obj_return = app::get('aftersales')->model('return_product');
        $returns = $obj_return->getRow('*',array('order_id'=>$order_id,'refund_type'=>'2','status'=>'1'));
        if($returns){
            $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'return_details','arg0'=>$returns['return_id']));
        }else{
            $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'return_add','arg0'=>$order_id));
        }
    }

	public function return_add($order_id,$page=1)
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $limit = 10;
        $objOrder = &$this->app_b2c->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        
        if($this->pagedata['order']['discount_value'] > 0){
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed']+($this->pagedata['order']['discount_value']);
            $this->pagedata['order']['payed'] = $this->pagedata['gorefund_price'];
        }else{
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed'];
        }

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id, $page, $limit);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

		$objMath = kernel::single("ectools_math");
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
			$tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
						$item['item_type'] = 'goods';

					if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
						$tmp_array = (array)$tmp_array;
						if (!$tmp_array) continue;
						
						$product_id = $tmp_array['products']['product_id'];
						if (!$order_items[$product_id]){
							$order_items[$product_id] = $tmp_array;
						}else{
							$order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
							$order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
						}
						//$order_items[$item['products']['product_id']] = $tmp_array;
					}
                }
            }
			else
			{
				if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
				{
					$tmp_array = (array)$tmp_array;
					if (!$tmp_array) continue;
					foreach ($tmp_array as $tmp){
						if (!$order_items[$tmp['product_id']]){
							$order_items[$tmp['product_id']] = $tmp;
						}else{
							$order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
							$order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
							$order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
						}
					}
				}
				//$order_items = array_merge($order_items, $tmp_array);
			}
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($order_items,($page-1)*$limit,$limit);
        $count = count($order_items);
        $arrMaxPage = $this->get_start($page, $count);
        $this->pagination($page, $arrMaxPage['maxPage'], 'return_add', array($order_id), 'aftersales', 'site_member');
        $this->pagedata['url'] = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_order_items', 'arg' => array($order_id)));
        $this->output('aftersales');
    }

	/**
	 * 截取文件名不包含扩展名
	 * @param 文件全名，包括扩展名
	 * @return string 文件不包含扩展名的名字
	 */
	private function fileext($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

	public function return_save()
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        
        if(! $_POST){
            $this->end(false, app::get('aftersales')->_("缺少必要的数据！"));
        }

        
        if($_POST['edit'] == 'edit'){
            $rp = app::get('aftersales')->model('return_product');
            $obj_order = app::get('b2c')->model('orders');
            $rp->update(array('status'=>'13'),array('return_id'=>$_POST['return_id']));
            $obj_order->update(array('refund_status'=>'2'),array('order_id'=>$_POST['order_id']));
        }

        //echo '<pre>';print_r($_POST);exit;
        
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product'])
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $objOrder = app::get('b2c')->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $order_info = $objOrder->dump($_POST['order_id'], '*', $subsdf);

        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        if($order_info['discount_value'] > 0){
            $gorefund_price = $order_info['payed']+($order_info['discount_value']);
        }else{
            $gorefund_price = $order_info['payed'];
        }

        if($_POST['amount']>$gorefund_price){
            $this->end(false, app::get('aftersales')->_("金额非法"));
        }
        //判断时候是售后
        if($order_info['status'] == 'finish'){
            if($_POST['gorefund_price']>$gorefund_price){
                $this->end(false, app::get('aftersales')->_("金额非法"));
            }
            if($_POST['amount']>$gorefund_price){
                $this->end(false, app::get('aftersales')->_("金额非法"));
            }
        }else{
            if (!$_POST['product_bn'])
            {
                if($_POST['type'] == '1'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您没有选择商品，请先选择商品！"), $com_url);
            }
        }
        $upload_file = "";
        if ( $_FILES['file']['size'] > 314572800 )
        {
            if($_POST['type'] == '1'){
			    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
            }else{
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            }
            $this->end(false, app::get('aftersales')->_("上传文件不能超过300M"), $com_url);
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif","jpeg","rar","zip");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                if($_POST['type'] == '1'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file']['name'];
            $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
        }
        //添加两张维权图片
        if ( $_FILES['file1']['size'] > 5242880 )
        {
            if($_POST['type'] == '1'){
			    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
            }else{
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            }
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file1']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file1']['name'])), $type))
            {
                $text = implode(",", $type);
                if($_POST['type'] == '1'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file1']['name'];
            $image_id1 = $mdl_img->store($_FILES['file1']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id1,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id1, $type);
        }
        $aData['image_file1'] = $image_id1;

        if ( $_FILES['file2']['size'] > 5242880 )
        {
            if($_POST['type'] == '1'){
			    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
            }else{
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            }
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file2']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file2']['name'])), $type))
            {
                $text = implode(",", $type);
                if($_POST['type'] == '1'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file2']['name'];
            $image_id2 = $mdl_img->store($_FILES['file2']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id2,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id2, $type);
        }
        $aData['image_file2'] = $image_id2;
       

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $product_data = array();
        //原系统判断金额
        $amount = 0;
        $obj_items = app::get('b2c')->model('order_items');
        if($order_info['status'] == 'active'){
            foreach ((array)$_POST['product_bn'] as $key => $val)
            {
                $price = $obj_items->getRow('price',array('order_id'=>$_POST['order_id'],'bn'=>$val));
                $amount = $amount + $price['price']*intval($_POST['product_nums'][$key]);
                if ($_POST['product_item_nums'][$key] < intval($_POST['product_nums'][$key]))
                {
                    if($_POST['type'] == '1'){
                        $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
                    }else{
                        $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                    }
                    $this->end(false, app::get('aftersales')->_("申请售后商品的数量不能大于订购数量: "), $com_url);
                }

                $item = array();
                $item['bn'] = $val;
                $item['name'] = $_POST['product_name'][$key];
                $item['num'] = intval($_POST['product_nums'][$key]);
                $product_data[] = $item;
            }

            $re_num = 0;
            foreach($product_data as $key=>$val){
                $re_num = $re_num + $val['num'];
            }
        }
        //去除订单分摊优惠
        $obj_order = app::get('b2c')->model('orders');
        $pmt_order = $obj_order->getRow('pmt_order,itemnum,cost_freight,is_protect,cost_protect,payed,score_u,discount_value,status',array('order_id'=>$_POST['order_id']));
        //$amount = $amount - $pmt_order['pmt_order']*($re_num/$pmt_order['itemnum']);
        //end
        
        $point_money_value = app::get('b2c')->getConf('site.point_deductible_value');
        if($_POST['amount'] > $pmt_order['payed']){
            $amount = $pmt_order['payed'];
            if($point_money_value != 0){
                $return_score = floor(($_POST['amount']-$pmt_order['payed'])/$point_money_value);
            }
            $score_u = $pmt_order['score_u'] - $return_score;
        }else{
            $amount = $_POST['amount'];
        }

        $aData['image_file'] = $image_id;
        $store_id = $obj_order->getRow('store_id',array('order_id'=>$_POST['order_id']));
        $sto= kernel::single("business_memberstore",$this->member['member_id']);        
        $aData['store_id'] = $store_id['store_id'];
        $aData['order_id'] = $_POST['order_id'];
        $aData['add_time'] = time();
        $aData['image_file'] = $image_id;
        //$aData['member_id'] = $this->member['member_id'];
        $aData['member_id'] = $_POST['member_id'];
        $aData['product_data'] = serialize($product_data);
        $aData['content'] = $_POST['content'];
        $aData['status'] = 1;
        $aData['amount'] = $amount;
        $aData['close_time'] = time()+86400*(app::get('b2c')->getConf('member.to_agree'));
        $aData['refund_type'] = '2';
        $aData['return_score'] = $return_score;
        $aData['comment'] = $_POST['comment'];
        //判断是否是售后申请
        if($pmt_order['status'] == 'finish'){
            $status_array = array('1' => '商品问题','2' => '七天无理由退换货','3' => '发票无效','4' => '退回多付的运费','5' => '未收到货');
            $aData['shipping_amount'] = $_POST['amount'];
            $aData['ship_cost'] = 0;
            $aData['amount_seller'] = 0;
            $aData['is_safeguard'] = '2';
            $aData['safeguard_type'] = $_POST['type'];
            $aData['comment'] = $status_array[$_POST['type']];
            if($_POST['type'] == '1' || $_POST['type'] == '2'){
                $aData['safeguard_require'] = $_POST['required_1'];
            }elseif($_POST['type'] == '3' || $_POST['type'] == '4'){
                $aData['safeguard_require'] = $_POST['required_2'];
            }else{
                $aData['safeguard_require'] = $_POST['required'];
            }
            if($aData['safeguard_require'] == '1' || $aData['safeguard_require'] == '5'){
                $aData['amount'] = $_POST['gorefund_price'];
                $aData['refund_type'] = '3';
            }elseif($aData['safeguard_require'] == '2' || $aData['safeguard_require'] == '3' || $aData['safeguard_require'] == '4'){
                $real_amount = 0;
                foreach ((array)$_POST['product_bn'] as $key => $val)
                {
                    $price = $obj_items->getRow('price',array('order_id'=>$_POST['order_id'],'bn'=>$val));
                    $amount = $amount + $price['price']*intval($_POST['product_nums'][$key]);
                    if ($_POST['product_item_nums'][$key] < intval($_POST['product_nums'][$key]))
                    {
                        if($_POST['type'] == '1'){
                            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
                        }else{
                            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                        }
                        $this->end(false, app::get('aftersales')->_("申请售后商品的数量不能大于订购数量: "), $com_url);
                    }
                    
                    $item = array();
                    $item['bn'] = $val;
                    $item['name'] = $_POST['product_name'][$key];
                    $item['num'] = intval($_POST['product_nums'][$key]);
                    $item['refund'] = $_POST['products_refund'][$key];
                    $item['price'] = $price['price'];
                    $product_data_safeguard[] = $item;
                    $real_amount = $real_amount + $item['refund'];
                } 
                $aData['product_data'] = serialize($product_data_safeguard);
                if($_POST['amount']>0){
                    $aData['amount'] = $real_amount+$_POST['amount'];
                }else{
                    $aData['amount'] = $real_amount;
                }
                $aData['refund_type'] = '2';

                if($aData['safeguard_require'] == '3' || $aData['safeguard_require'] == '4'){
                    $aData['amount'] = 0;
                }
            }elseif($aData['safeguard_require'] == '6'){
                $aData['amount'] = $_POST['gorefund_price'];
                $aData['refund_type'] = '3';
            }else{
                $aData['amount'] = 0;
            }

            if($_POST['amount'] > $pmt_order['payed']){
                $amount = $pmt_order['payed'];
                if($point_money_value != 0){
                    $return_score = floor(($_POST['amount']-$pmt_order['payed'])*$point_money_value);
                }
                $score_u = $pmt_order['score_u'] - $return_score;
            }else{
                $amount = $_POST['amount'];
            }

            //计算商家承担的金额
            if($aData['safeguard_type'] == '1' || $aData['safeguard_type'] == '2'){
                if($aData['safeguard_require'] == '1' || $aData['safeguard_require'] == '5' || $aData['safeguard_require'] == '6'){
                    $aData['seller_amount'] = $aData['amount'];
                }
                if($aData['safeguard_require'] == '2'){
                    $obj_cat = app::get('b2c')->model('goods_cat');
                    $obj_goods = app::get('b2c')->model('goods');
                    $obj_product = app::get('b2c')->model('products');
                    $seller_amount = 0;
                    //根据商品金额以及抽成比例算出商家出多少钱
                    foreach($product_data_safeguard as $key=>$val){
                        $good_id = $obj_product->dump(array('bn'=>$val['bn']),'goods_id');
                        $cat_id = $obj_goods->dump($good_id['goods_id'],'cat_id');
                        if(app::get('b2c')->getConf('member.isprofit') == 'true'){
                            $profit_point = $obj_cat->dump($cat_id['category']['cat_id'],'profit_point');
                            if(is_null($profit_point['profit_point'])){
                                $parent_id = $obj_cat->dump($cat_id['category']['cat_id'],'parent_id');
                                $profit_point = $obj_cat->dump($parent_id['parent_id'],'profit_point');
                            }
                        }else{
                            $profit_point['profit_point'] = 0;
                        }
                        $seller_amount = $seller_amount + $val['refund']*(1-$profit_point['profit_point']/100);
                    }
                    if($_POST['amount'] > 0){
                        $freight_pro = app::get('b2c')->getConf('member.profit');
                        $seller_amount = $seller_amount + ($_POST['amount'])*(1-$freight_pro/100);
                    }
                    $aData['seller_amount'] = $seller_amount;
                }
                if($aData['safeguard_require'] == '3' || $aData['safeguard_require'] == '4'){
                    $aData['seller_amount'] = 0;
                    $aData['shipping_amount'] = 0;
                }
            }elseif($aData['safeguard_type'] == '4'){
                //退邮费的情况
                $freight_pro = app::get('b2c')->getConf('member.profit');
                $aData['seller_amount'] = ($aData['amount'])*(1-$freight_pro/100);
            }else{
                //其余情况 卖家承担全部
                $aData['seller_amount'] = $aData['amount'];
            }

            //判断是否超过卖家结算所获得的金额
            $mdl_order_bill = app::get('ectools')->model('order_bills');
            $model_refunds = app::get('ectools')->model('refunds');
            $blances = $mdl_order_bill->dump(array('rel_id'=>$_POST['order_id'],'bill_type'=>'blances'),'bill_id');
            $cur_money = $model_refunds->dump(array('refund_id'=>$blances['bill_id']),'cur_money');
            if($aData['seller_amount'] > $cur_money['cur_money']){
                $aData['seller_amount'] = $cur_money['cur_money'];
            }

            if($aData['amount']>$gorefund_price){
                $this->end(false, app::get('aftersales')->_("金额非法"));
            }

        }else{
            if($re_num == $pmt_order['itemnum']){
                //全部退款时退还卖家运费
                $return_money = ($pmt_order['payed']+($pmt_order['discount_value'])) - $_POST['amount'];
                //判断剩余金额是否大于邮费
                if($return_money > $pmt_order['cost_freight']){
                    $aData['ship_cost'] = $pmt_order['cost_freight'];
                }else{
                    $aData['ship_cost'] = $return_money;
                }
                //退款以后的多余款项记录
                $amount_seller = ($pmt_order['payed']+($pmt_order['discount_value'])) - $_POST['amount'] - $pmt_order['cost_freight'];
                $freight_pro = app::get('b2c')->getConf('member.profit');
                //退款金额判断
                if($amount_seller>0){
                    $aData['amount_seller'] = $amount_seller;
                    //是否保价
                    if($pmt_order['is_protect']){
                        $aData['ship_cost'] = $aData['ship_cost'] + $pmt_order['cost_protect'];
                        //邮费抽成
                        $aData['ship_cost'] = $aData['ship_cost'];
                        $aData['amount'] = $aData['amount'] - $pmt_order['cost_protect'];
                    }else{
                        $aData['ship_cost'] = $aData['ship_cost'];
                    }
                }else{
                    $aData['amount_seller'] = 0;
                    //是否保价
                    if($pmt_order['is_protect']){
                        $aData['ship_cost'] = $aData['ship_cost'] + $pmt_order['cost_protect'];
                        //邮费抽成
                        $aData['ship_cost'] = $aData['ship_cost'];
                        $aData['amount'] = $aData['amount'] - $pmt_order['cost_protect'];
                    }else{
                        $aData['ship_cost'] = $aData['ship_cost'];
                    }
                }
                
            }else{
                $aData['ship_cost'] = 0;
                $aData['amount_seller'] = 0;
            }
        }

        if($_POST['edit'] == 'edit'){
            $aData['old_return_id'] = $_POST['return_id'];
        }

        $msg = "";
        $obj_aftersales = kernel::service("api.aftersales.request");
        if ($obj_aftersales->generate($aData, $msg))
        {
			$obj_rpc_request_service = kernel::service('b2c.rpc.send.request');
			if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
			{
				if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
					$obj_rpc_request_service->rpc_caller_request($aData,'aftersales');
			}
			else
			{
				$obj_aftersales->rpc_caller_request($aData);
			}
            //停止确认收货时间
            $confirm_time = $obj_order->getRow('confirm_time,status',array('order_id'=>$_POST['order_id']));
            if($confirm_time['confirm_time']){
                $time = $confirm_time['confirm_time'] - time();
            }else{
                $time = $confirm_time['confirm_time'];
            }
            //修改订单状态
            if($_POST['edit'] == 'edit' || $confirm_time['status'] == 'finish'){
                $refund_status = array('refund_status'=>'1');
            }else{
                $refund_status = array('refund_status'=>'1','confirm_time'=>$time);
            }
            $rs = $obj_order->update($refund_status,array('order_id'=>$_POST['order_id']));

            $this->end(true, app::get('b2c')->_('提交成功！'), $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));

        }
        else
        {
            $this->end(false, $msg, $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
        }
    }

	public function return_details($return_id)
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product'])
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($return_id);

        //添加退货地址显示
        $obj_address = app::get('business')->model('dlyaddress');
        $address = $obj_address->getList('*',array('da_id'=>$this->pagedata['return_item']['refund_address']));
        $ads = explode(':',$address['0']['region']);
        $address['0']['region'] = $ads[1];
        $this->pagedata['address'] = $address['0'];
        //添加确认收到退货按钮
        $sto= kernel::single("business_memberstore",$this->member['member_id']);        
        $store_id = $sto->storeinfo['store_id'];

        //获取订单状态
        $obj_orders = app::get('b2c')->model('orders');
        $obj_return_p = app::get('aftersales')->model('return_product');
        $order_id = $obj_return_p->dump(array('return_id'=>$this->pagedata['return_item']['return_id']));
        $order_info = $obj_orders->dump(array('order_id'=>$order_id['order_id']));
        if($this->pagedata['return_item']['status'] == '已退货' && $this->pagedata['return_item']['refund_type'] == '2' && $order_info['refund_status'] == '5'){
            $this->pagedata['is_shop'] = true;
            
        }else{
            $this->pagedata['is_shop'] = false;
        }

        $this->pagedata['return_id'] = $return_id;
        if( !($this->pagedata['return_item']) )
        {
           $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
           $this->end(false, $this->app->_("售后服务申请单不存在！"));
        }

        $this->pagedata['now_time'] = time()*1000;
        $this->pagedata['time'] = $this->pagedata['return_item']['close_time']*1000;

        $this->pagedata['now_time_do_return'] = time()*1000;
        $this->pagedata['time_do_return'] = ($this->pagedata['return_item']['add_time']+(app::get('b2c')->getConf('member.to_agree'))*86400)*1000;

        //添加退款日志
        $obj_return_log = app::get('aftersales')->model('return_log');
        $log_info = $obj_return_log->getList('*',array('order_id'=>$order_id['order_id']),-1,-1,'alttime DESC');
        //echo "<pre>";print_r($log_info);exit;
        $this->pagedata['log_info'] = $log_info;

        $this->output('aftersales');
    }

	/**
	 * 下载售后附件
	 * @param string return id
	 * @return null
	 */
	public function file_download($return_id,$image_file)
    {
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $obj_return_policy->file_download($return_id,$image_file);
    }

     /**
     * 生成退款单页面
     * @params string order id
     * @return string html
     */
    public function gorefund($order_id,$type=0)
    {
        $obj_product = app::get('aftersales')->model('return_product');
        $return_products = $obj_product->getList('*',array('order_id'=>$order_id,'refund_type|in'=>array('3','4')));
        $tag = false;
        foreach($return_products as $k=>$v){
            if($v['status'] == '1'){
                $tag = true;
                $return_id = $v['return_id'];
            }
        }
        if($tag){
            $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'return_products','arg0'=>$return_id));
        }else{
            if($type){
                $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'gorefund_mai','arg0'=>$order_id,'arg1'=>$type));
            }else{
                $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'gorefund_mai','arg0'=>$order_id));
            }
        }
    }

    public function return_products($return_id){
        $obj_product = app::get('aftersales')->model('return_product');
        $return_products = $obj_product->getRow('*',array('return_id'=>$return_id));
        //echo '<pre>';print_r($return_products);exit;
        $this->pagedata['return_products'] = $return_products;

        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product'])
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($return_products['return_id']);
        //添加退货地址显示
        $obj_address = app::get('business')->model('dlyaddress');
        $address = $obj_address->getList('*',array('da_id'=>$this->pagedata['return_item']['refund_address']));
        $ads = explode(':',$address['0']['region']);
        $address['0']['region'] = $ads[1];
        $this->pagedata['address'] = $address['0'];
        //添加确认收到退货按钮
        $sto= kernel::single("business_memberstore",$this->member['member_id']);        
        $store_id = $sto->storeinfo['store_id'];
        if($store_id == $this->pagedata['return_item']['store_id'] && $this->pagedata['return_item']['status'] == '已退货'){
            $this->pagedata['is_shop'] = true;
        }else{
            $this->pagedata['is_shop'] = false;
        }

        $this->pagedata['return_id'] = $return_products['return_id'];
        if( !($this->pagedata['return_item']) )
        {
           $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
           $this->end(false, $this->app->_("售后服务申请单不存在！"));
        }
        $this->output('aftersales');
    }

        /**
     * 生成退款单页面
     * @params string order id
     * @return string html
     */
    public function gorefund_mai($order_id,$type=0,$page=1)
    {
        if($type==3){
            $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'gorefund_mai_3','arg0'=>$order_id));
        }elseif($type==4){
            $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'gorefund_mai_4','arg0'=>$order_id));
        }
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));

        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        //$this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id, $page, $limit);
        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }
        
        $point_money_value = app::get('b2c')->getConf('site.point_money_value');

        if($this->pagedata['order']['discount_value'] > 0){
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed']+($this->pagedata['order']['discount_value']);
        }else{
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed'];
        }
        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $objMath = kernel::single("ectools_math");
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
            $tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
                        $item['item_type'] = 'goods';

                    if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
                        $tmp_array = (array)$tmp_array;
                        if (!$tmp_array) continue;
                        
                        $product_id = $tmp_array['products']['product_id'];
                        if (!$order_items[$product_id]){
                            $order_items[$product_id] = $tmp_array;
                        }else{
                            $order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
                            $order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
                        }
                        //$order_items[$item['products']['product_id']] = $tmp_array;
                    }
                }
            }
            else
            {
                if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
                {
                    $tmp_array = (array)$tmp_array;
                    if (!$tmp_array) continue;
                    foreach ($tmp_array as $tmp){
                        if (!$order_items[$tmp['product_id']]){
                            $order_items[$tmp['product_id']] = $tmp;
                        }else{
                            $order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
                            $order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
                            $order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
                        }
                    }
                }
                //$order_items = array_merge($order_items, $tmp_array);
            }
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($order_items,($page-1)*$limit,$limit);
        
        $count = count($order_items);
        //$arrMaxPage = $this->get_start($page, $count);
        //$this->pagination($page, $arrMaxPage['maxPage'], 'return_add', array($order_id), 'aftersales', 'site_member');
        $this->pagedata['url'] = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_order_items', 'arg' => array($order_id)));
        
        //echo '<pre>';print_r($this->pagedata);exit;
        $this->output('aftersales');
    }

    public function gorefund_mai_3($order_id){
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $this->pagedata['type'] = 3;
        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        //$this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id, $page, $limit);
        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }
        
        $point_money_value = app::get('b2c')->getConf('site.point_money_value');

        if($this->pagedata['order']['discount_value'] > 0){
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed']+($this->pagedata['order']['discount_value']);
        }else{
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed'];
        }
        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $objMath = kernel::single("ectools_math");
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
            $tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
                        $item['item_type'] = 'goods';

                    if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
                        $tmp_array = (array)$tmp_array;
                        if (!$tmp_array) continue;
                        
                        $product_id = $tmp_array['products']['product_id'];
                        if (!$order_items[$product_id]){
                            $order_items[$product_id] = $tmp_array;
                        }else{
                            $order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
                            $order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
                        }
                        //$order_items[$item['products']['product_id']] = $tmp_array;
                    }
                }
            }
            else
            {
                if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
                {
                    $tmp_array = (array)$tmp_array;
                    if (!$tmp_array) continue;
                    foreach ($tmp_array as $tmp){
                        if (!$order_items[$tmp['product_id']]){
                            $order_items[$tmp['product_id']] = $tmp;
                        }else{
                            $order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
                            $order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
                            $order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
                        }
                    }
                }
                //$order_items = array_merge($order_items, $tmp_array);
            }
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($order_items,($page-1)*$limit,$limit);
        
        $count = count($order_items);
        //$arrMaxPage = $this->get_start($page, $count);
        //$this->pagination($page, $arrMaxPage['maxPage'], 'return_add', array($order_id), 'aftersales', 'site_member');
        $this->pagedata['url'] = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_order_items', 'arg' => array($order_id)));
        
        //echo '<pre>';print_r($this->pagedata);exit;
        $this->output('aftersales');
    }

    public function gorefund_mai_4($order_id){
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $this->pagedata['type'] = 4;
        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        //$this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id, $page, $limit);
        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }
        
        $point_money_value = app::get('b2c')->getConf('site.point_money_value');

        if($this->pagedata['order']['discount_value'] > 0){
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed']+($this->pagedata['order']['discount_value']);
        }else{
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed'];
        }
        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $objMath = kernel::single("ectools_math");
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
            $tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
                        $item['item_type'] = 'goods';

                    if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
                        $tmp_array = (array)$tmp_array;
                        if (!$tmp_array) continue;
                        
                        $product_id = $tmp_array['products']['product_id'];
                        if (!$order_items[$product_id]){
                            $order_items[$product_id] = $tmp_array;
                        }else{
                            $order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
                            $order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
                        }
                        //$order_items[$item['products']['product_id']] = $tmp_array;
                    }
                }
            }
            else
            {
                if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
                {
                    $tmp_array = (array)$tmp_array;
                    if (!$tmp_array) continue;
                    foreach ($tmp_array as $tmp){
                        if (!$order_items[$tmp['product_id']]){
                            $order_items[$tmp['product_id']] = $tmp;
                        }else{
                            $order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
                            $order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
                            $order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
                        }
                    }
                }
                //$order_items = array_merge($order_items, $tmp_array);
            }
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($order_items,($page-1)*$limit,$limit);
        
        $count = count($order_items);
        //$arrMaxPage = $this->get_start($page, $count);
        //$this->pagination($page, $arrMaxPage['maxPage'], 'return_add', array($order_id), 'aftersales', 'site_member');
        $this->pagedata['url'] = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_order_items', 'arg' => array($order_id)));
        
        //echo '<pre>';print_r($this->pagedata);exit;
        $this->output('aftersales');
    }

	public function return_save_mai()
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        
        if(! $_POST){
            $this->end(false, app::get('aftersales')->_("缺少必要的数据！"));
        }

        
        if($_POST['edit'] == 'edit'){
            $rp = app::get('aftersales')->model('return_product');
            $obj_order = app::get('b2c')->model('orders');
            $rp->update(array('status'=>'13'),array('return_id'=>$_POST['return_id']));
            $obj_order->update(array('refund_status'=>'2'),array('order_id'=>$_POST['order_id']));
        }

        //echo '<pre>';print_r($_POST);exit;

        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product'])
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $upload_file = "";
        if ( $_FILES['file']['size'] > 5242880 )
        {
            if($_POST['type'] == '3'){
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_3', 'arg0' => $_POST['order_id']));
            }elseif($_POST['type'] == '4'){
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_4', 'arg0' => $_POST['order_id']));
            }else{
                $com_url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'orders', 'arg0' => $_POST['order_id']));
            }
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                if($_POST['type'] == '3'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_3', 'arg0' => $_POST['order_id']));
                }elseif($_POST['type'] == '4'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_4', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'orders', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file']['name'];
            $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
            $aData['image_file'] = $image_id;
        }

        //添加两张维权图片
        if ( $_FILES['file1']['size'] > 5242880 )
        {
            if($_POST['type'] == '3'){
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_3', 'arg0' => $_POST['order_id']));
            }elseif($_POST['type'] == '4'){
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_4', 'arg0' => $_POST['order_id']));
            }else{
                $com_url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'orders', 'arg0' => $_POST['order_id']));
            }
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file1']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file1']['name'])), $type))
            {
                $text = implode(",", $type);
                if($_POST['type'] == '3'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_3', 'arg0' => $_POST['order_id']));
                }elseif($_POST['type'] == '4'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_4', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'orders', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file1']['name'];
            $image_id = $mdl_img->store($_FILES['file1']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
            $aData['image_file1'] = $image_id;
        }


        if ( $_FILES['file2']['size'] > 5242880 )
        {
            if($_POST['type'] == '3'){
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_3', 'arg0' => $_POST['order_id']));
            }elseif($_POST['type'] == '4'){
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_4', 'arg0' => $_POST['order_id']));
            }else{
                $com_url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'orders', 'arg0' => $_POST['order_id']));
            }
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file2']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file2']['name'])), $type))
            {
                $text = implode(",", $type);
                if($_POST['type'] == '3'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_3', 'arg0' => $_POST['order_id']));
                }elseif($_POST['type'] == '4'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_4', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'orders', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file2']['name'];
            $image_id = $mdl_img->store($_FILES['file2']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
            $aData['image_file2'] = $image_id;
        }

       


        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $product_data = array();
        foreach ((array)$_POST['product_bn'] as $key => $val)
        {
			if ($_POST['product_item_nums'][$key] < intval($_POST['product_nums'][$key]))
			{
				if($_POST['type'] == '3'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_3', 'arg0' => $_POST['order_id']));
                }elseif($_POST['type'] == '4'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai_4', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'orders', 'arg0' => $_POST['order_id']));
                }
				$this->end(false, app::get('aftersales')->_("申请售后商品的数量不能大于订购数量: "), $com_url);
			}

            $item = array();
            $item['bn'] = $val;
            $item['name'] = $_POST['product_name'][$key];
            $item['num'] = intval($_POST['product_nums'][$key]);
            $product_data[] = $item;
        }
        $objOrder = &$this->app->model('orders');
        $store_id = $objOrder->getRow('store_id,score_u,payed',array('order_id'=>$_POST['order_id']));

        $sto= kernel::single("business_memberstore",$_POST['member_id']);
        $aData['store_id'] = $store_id['store_id'];
        $aData['order_id'] = $_POST['order_id'];
        $aData['add_time'] = time();
        //$aData['member_id'] = $this->member['member_id'];
        $aData['member_id'] = $_POST['member_id'];
        $aData['product_data'] = serialize($product_data);
        $aData['content'] = $_POST['content'];
        $aData['status'] = 1;

        $point_money_value = app::get('b2c')->getConf('site.point_deductible_value');
        if($_POST['gorefund_price'] > $store_id['payed']){
            $amount = $store_id['payed'];
            if($point_money_value != 0){
                $return_score = floor(($_POST['gorefund_price']-$store_id['payed'])/$point_money_value);
            }
            $score_u = $store_id['score_u'] - $return_score;
        }else{
            $amount = $_POST['gorefund_price'];
        }

        $aData['amount'] = $amount;
        $aData['return_score'] = $return_score;
        $aData['close_time'] = time()+86400*(app::get('b2c')->getConf('member.to_agree'));
        $aData['comment'] = $_POST['comment'];
        if($_POST['type']){
            $aData['refund_type'] = $_POST['type'];
        }
        if($_POST['edit'] == 'edit'){
            $aData['old_return_id'] = $_POST['return_id'];
        }
        $msg = "";
        $obj_aftersales = kernel::service("api.aftersales.request");
        //echo '<pre>';print_r($aData);exit;
        if ($obj_aftersales->generate($aData, $msg))
        {
			$obj_rpc_request_service = kernel::service('b2c.rpc.send.request');
			if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
			{
				if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
					$obj_rpc_request_service->rpc_caller_request($aData,'aftersales');
			}
			else
			{
				$obj_aftersales->rpc_caller_request($aData);
			}
            //停止确认收货时间
            $confirm_time = $objOrder->getRow('confirm_time,status',array('order_id'=>$_POST['order_id']));
            if($confirm_time['confirm_time']){
                $time = $confirm_time['confirm_time'] - time();
            }else{
                $time = $confirm_time['confirm_time'];
            }
            //修改订单状态
            if($_POST['edit'] == 'edit' || $confirm_time['status'] == 'finish'){
                $refund_status = array('refund_status'=>'1');
            }else{
                $refund_status = array('refund_status'=>'1','confirm_time'=>$time);
            }
            $rs = $objOrder->update($refund_status,array('order_id'=>$_POST['order_id']));

            if($rs){
                $this->end(true, app::get('b2c')->_('提交成功！'), $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
            }else{
                $this->end(false,app::get('b2c')->_('提交成功！更新订单状态失败！'), $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
            }
        }
        else
        {
            $this->end(false, $msg, $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
        }
    }

    public function do_gorefund($order_id){
        //订单详细信息
        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);
        
        // 校验订单的会员有效性.
        //$is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        $obj_product = app::get('aftersales')->model('return_product');
        //退款单详细信息
        $return_products = $obj_product->getList('*',array('order_id'=>$order_id,'refund_type'=>'1'));
        $this->pagedata['return_products'] = $return_products;
        
        //echo '<pre>';print_r($this->pagedata['return_products']);exit;
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product'])
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($return_products[0]['return_id']);
        //添加退货地址显示
        $obj_address = app::get('business')->model('dlyaddress');
        $address = $obj_address->getList('*',array('da_id'=>$this->pagedata['return_item']['refund_address']));
        $ads = explode(':',$address['0']['region']);
        $address['0']['region'] = $ads[1];
        $this->pagedata['address'] = $address['0'];
        //添加确认收到退货按钮
        $sto= kernel::single("business_memberstore",$this->member['member_id']);        
        $store_id = $sto->storeinfo['store_id'];
        if($store_id == $this->pagedata['return_item']['store_id'] && $this->pagedata['return_item']['status'] == '已退货'){
            $this->pagedata['is_shop'] = true;
        }else{
            $this->pagedata['is_shop'] = false;
        }

        $this->pagedata['return_id'] = $return_products[0]['return_id'];
        if( !($this->pagedata['return_item']) )
        {
           $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
           $this->end(false, $this->app->_("售后服务申请单不存在！"));
        }

        $this->output('aftersales');
    }

    public function warn(){
        
        $this->pagedata['return_id'] = $_POST['return_id'];
        $this->pagedata['order_id'] = $_POST['order_id'];
        $this->page('site/member/warn.html',true,'aftersales');

    }

    public function do_agree(){
        $url = $this->gen_url(array('app' =>'business','ctl'=>'site_member','act' =>'seller_order'));
        $rp = app::get('aftersales')->model('return_product'); 
        $obj_return_policy = kernel::single('aftersales_data_return_policy');
       
        $obj_product = app::get('aftersales')->model('return_product');
        $return_products = $obj_product->getList('*',array('return_id'=>$_POST['return_id']));
        
        if($return_products[0]['status'] != '1'){
            $this->splash('failed',$url,app::get('aftersales')->_('非法请求'));
        }
        $sdf = array(
            'return_id' => $_POST['return_id'],
            'status' => '3',
        );
        
        $this->pagedata['return_status'] = $obj_return_policy->change_status($sdf);        
        if ($this->pagedata['return_status'])
            $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
        
        $obj_aftersales = kernel::servicelist("api.aftersales.request");
        foreach ($obj_aftersales as $obj_request)
        {
            $obj_request->send_update_request($sdf);
        }
        //生成退款单
       
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

        $obj_order = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

        $sdf['money'] = $sdf_order['payed'];
        //$sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

        $refunds = app::get('ectools')->model('refunds');
        //$objOrder->op_id = $this->user->user_id;
        //$objOrder->op_name = $this->user->user_data['account']['name'];
        $sdf['op_id'] = $this->member['member_id'];
        $o_account = app::get('pam')->model('account');
        $uname = $o_account->dump($this->member['member_id']);
        $sdf['op_name'] = $uname['login_name'];
        //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
        unset($sdf['inContent']);
        
        $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');
        $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

        $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
            
        $time = time();
        $sdf['refund_id'] = $refund_id = $refunds->gen_id();
        $sdf['pay_app_id'] = $sdf['payment'];
        $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

        $obj_members = app::get('pam')->model('account');
        $buy_name = $obj_members->getRow('login_name',array('account_id'=>$sdf['member_id']));
        $sdf['account'] = $buy_name['login_name'];

        $sdf['currency'] = $sdf_order['currency'];
        $sdf['paycost'] = 0;
        $sdf['cur_money'] = $sdf['money'];
        //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
        $sdf['t_begin'] = $time;
        $sdf['t_payed'] = $time;
        $sdf['t_confirm'] = $time;
        $sdf['pay_object'] = 'order';
        //$sdf['op_id'] = $this->user->user_id;
        //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
        $sdf['status'] = 'ready';
        $sdf['app_name'] = $arrPaymentInfo['app_name'];
        $sdf['app_version'] = $arrPaymentInfo['app_version'];
        $sdf['refund_type'] = '1';

        $sdf['is_safeguard'] = $return_products[0]['is_safeguard'];
        if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
        {
             $this->splash('failed',$url,$message);
        }   
        $obj_refunds = kernel::single("ectools_refund");
        if ($obj_refunds->generate($sdf, $this, $msg))
        {
            //进行退款操作
            $refund = app::get('ectools')->model('refunds');
            $refund_data = $refund->dump($refund_id,'*');
            $obj_bills = app::get('ectools')->model('order_bills');
            $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
            $payment_id = $refund->get_payment($order_id['rel_id']);
            $obj_payment = app::get('ectools')->model('payments');
            $cur_money = $obj_payment->dump($payment_id['bill_id'],'*');

            //判断是否是合并付款
            if($cur_money['merge_payment_id'] != ''){
                $payment_id['bill_id'] = $cur_money['merge_payment_id'];
                $cur_money['cur_money'] = 0;
                $total = $obj_payment->getList('*',array('merge_payment_id'=>$payment_id['bill_id'],'status'=>'succ'));
                foreach($total as $key=>$val){
                    $cur_money['cur_money'] = $cur_money['cur_money'] + $val['cur_money'];
                }
            }
            //开始确认收货时间
            $confirm_time = $obj_order->getRow('confirm_time,score_g,score_u,member_id',array('order_id'=>$sdf['order_id']));
            $time = $confirm_time['confirm_time'] + time();

            $point_money_value = app::get('b2c')->getConf('site.point_money_value');

            //修改会员的冻结积分
            $point_obj = app::get('pointprofessional')->model('members');
            $reduce_score = $confirm_time['score_g'];
            $point_obj->reduce_obtained($confirm_time['member_id'],$reduce_score,$sdf['order_id']);

            //修改订单状态
            $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_g'=>0,'status'=>'finish');
            $rs = $obj_order->update($refund_status,array('order_id'=>$sdf['order_id']));

            //退还积分
            $obj_members_point = kernel::service('b2c_member_point_add');
            $obj_members_point->change_point($sdf_order['member_id'],intval($refund_data['return_score']), $msg, 'order_refund_use', 1, $sdf['order_id'],0, 'refund');

            if($rs){
                if($refund_data['pay_app_id'] != 'deposit'){
                    if($refund_data['cur_money'] == 0){
                        $obj_refunds = kernel::single("ectools_refund");
                        $ref_rs = $obj_refunds->generate_after($sdf);
                    }else{
                        $refund_data['payment_info'] = $cur_money;
                        $result = $obj_refunds->dorefund($refund_data,$this);
                        $obj_refunds->callback($refund_data,$result);
                    }
         
                    if($result == "success"){
                    //if(1){
                        
                        $obj_refunds = kernel::single("ectools_refund");
                        
                        if ($ref_rs)
                        {
                            $is_refund_finished = false;
                            $obj_refund_lists = kernel::servicelist("order.refund_finish");
                            foreach ($obj_refund_lists as $order_refund_service_object)
                            {                
                                $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                            }
                            
                            if ($is_refund_finished)
                            {
                                // 发送同步日志.
                                $order_refund_service_object->send_request($sdf);

                                //ajx crm
                                $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                                $req_arr['order_id']=$sdf['order_id'];
                                $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                                $log_text = "卖家同意退款";
                                $result_log = "SUCCESS";

                                $returnLog = app::get('aftersales')->model('return_log');
                                $sdf_return_log = array(
                                    'order_id' => $sdf['order_id'],
                                    'return_id' => $_POST['return_id'],
                                    'op_id' => $this->member['member_id'],
                                    'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                                    'alttime' => time(),
                                    'behavior' => 'agreereturn',
                                    'result' => $result_log,
                                    'role' => 'seller',
                                    'log_text' => $log_text,
                                );

                                $log_id = $returnLog->save($sdf_return_log);

                                $this->splash('success',$url,app::get('aftersales')->_('退款成功'));
                            }
                            else
                            {
                                $this->splash('success',$url,$msg);
                            }

                        }else{
                            $this->splash('success',$url,'退款成功，更新退款单失败！');
                        }
                    }else{
                        $this->splash('failed',$url,'结算失败,请等待管理员结算'.$result);
                    }
                }else{
                    $is_refund_finished = false;
                    $obj_refund_lists = kernel::servicelist("order.refund_finish");
                    foreach ($obj_refund_lists as $order_refund_service_object)
                    {                
                        $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                    }
                    
                    if ($is_refund_finished)
                    {
                        // 发送同步日志.
                        $order_refund_service_object->send_request($sdf);

                        //ajx crm
                        $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                        $req_arr['order_id']=$sdf['order_id'];
                        $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                        $log_text = "卖家同意退款";
                        $result_log = "SUCCESS";

                        $returnLog = app::get('aftersales')->model('return_log');
                        $sdf_return_log = array(
                            'order_id' => $sdf['order_id'],
                            'return_id' => $_POST['return_id'],
                            'op_id' => $this->member['member_id'],
                            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                            'alttime' => time(),
                            'behavior' => 'agreereturn',
                            'result' => $result_log,
                            'role' => 'seller',
                            'log_text' => $log_text,
                        );

                        $log_id = $returnLog->save($sdf_return_log);

                        $this->splash('success',$url,app::get('aftersales')->_('退款成功,请等待管理员结算'));
                    }
                    else
                    {
                        $this->splash('failed',$url,$msg);
                    }
                }
            }else{
                $this->splash('failed',$url,app::get('aftersales')->_('退款失败'));
            }
        }
        else
        {
            $this->splash('failed',$url,$msg);
        }

    }

    public function do_send(){
        $rp = app::get('aftersales')->model('return_product');
        $obj_return_policy = kernel::single('aftersales_data_return_policy');

        $sdf = array(
            'return_id' => $_POST['return_id'],
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

        $obj_order = app::get('b2c')->model('orders');
        $data = array('refund_status'=>'2');
        $obj_order->update($data,array('order_id'=>$_POST['order_id']));

        $this->redirect(array('app'=>'business', ctl=>'site_order','act'=>'godelivery','arg0'=>$_POST['order_id'],'arg1'=>'seller'));
    }

    public function edit_returns($return_id){
        $obj_product = app::get('aftersales')->model('return_product');
        $return_products = $obj_product->getList('*',array('return_id'=>$return_id));
        $this->pagedata['return_products'] = $return_products['0'];
        //echo '<pre>';print_r($this->pagedata['return_products']);exit;
        $this->output('aftersales');
    }

    public function return_edit_buyer(){
        //echo '<pre>';print_r($_POST);exit;
        $this->begin(array('app' =>'b2c','ctl'=>'site_member','act' =>'orders'));
        $aData = $_POST;
        $upload_file = "";
        if ( $_FILES['file']['size'] > 314572800 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过300M"), $com_url);
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif","jpeg","rar","zip");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file']['name'];
            $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
        }
        $aData['image_file'] = $image_id;
        //添加两张维权图片
        if ( $_FILES['file1']['size'] > 5242880 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file1']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file1']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file1']['name'];
            $image_id1 = $mdl_img->store($_FILES['file1']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id1,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id1, $type);
        }
        $aData['image_file1'] = $image_id1;

        if ( $_FILES['file2']['size'] > 5242880 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file2']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file2']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file2']['name'];
            $image_id2 = $mdl_img->store($_FILES['file2']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id2,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id2, $type);
        }
        $aData['image_file2'] = $image_id2;
        
        $obj_product = app::get('aftersales')->model('return_product');
        unset($aData['return_id']);
        $rs = $obj_product->update($aData,array('return_id'=>$_POST['return_id']));
        if($rs){
            $this->end(true, app::get('aftersales')->_('修改成功'));
        }else{
            $this->end(false, app::get('aftersales')->_('修改失败'));
        }

    }

    public function return_edit_seller(){
        //echo '<pre>';print_r($_POST);exit;
        $this->begin(array('app' =>'business','ctl'=>'site_member','act' =>'seller_order'));
        $aData = $_POST;
        $upload_file = "";
        if ( $_FILES['file']['size'] > 314572800 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过300M"), $com_url);
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif","jpeg","rar","zip");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file']['name'];
            $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
        }
        //添加两张维权图片
        if ( $_FILES['file1']['size'] > 5242880 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file1']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file1']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file1']['name'];
            $image_id1 = $mdl_img->store($_FILES['file1']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id1,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id1, $type);
        }
        $aData['image_file1'] = $image_id1;

        if ( $_FILES['file2']['size'] > 5242880 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file2']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file2']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file2']['name'];
            $image_id2 = $mdl_img->store($_FILES['file2']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id2,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id2, $type);
        }
        $aData['image_file2'] = $image_id2;
       
        $obj_product = app::get('aftersales')->model('return_product');
        unset($aData['return_id']);
        $aData['status'] = 11;
        $aData['close_time'] = time();
        $rs = $obj_product->update($aData,array('return_id'=>$_POST['return_id']));

        //添加退款日志
        if ($this->member['member_id'])
        {
            $obj_members = app::get('b2c')->model('members');
            $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
        }
        if(!$rs){
            $log_text = "卖家拒绝退款申请";
            $result = "FAILURE";
        }else{
            $log_text = "卖家拒绝退款申请,拒绝原因：".$_POST['seller_reason'];
            $result = "SUCCESS";
        }

        $returnLog = app::get('aftersales')->model("return_log");
        $sdf_return_log = array(
            'order_id' => $_POST['order_id'],
            'return_id' => $_POST['return_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'behavior' => 'refuseapp',
            'result' => $result,
            'role' => 'seller',
            'log_text' => $log_text,
        );

        $objOrderLog = app::get('b2c')->model("order_log");

        $sdf_order_log = array(
            'rel_id' => $_POST['order_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => $result,
            'log_text' => $log_text,
        );

        if($rs){
            $objOrder = app::get('b2c')->model('orders');
            //修改订单状态
            $refund_status = array('refund_status'=>'6');
            $rs = $objOrder->update($refund_status,array('order_id'=>$_POST['order_id']));

            $log_id = $returnLog->save($sdf_return_log);
            $log_id = $objOrderLog->save($sdf_order_log);
            $this->end(true, app::get('aftersales')->_('拒绝成功'));
        }else{
            $log_id = $returnLog->save($sdf_return_log);
            $log_id = $objOrderLog->save($sdf_order_log);
            $this->end(false, app::get('aftersales')->_('拒绝失败'));
        }

    }

    public function do_return($order_id,$refund_type=2){
        //订单详细信息
        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        // 校验订单的会员有效性.
        //$is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        $obj_product = app::get('aftersales')->model('return_product');
        //退款单详细信息
        $this->pagedata['return_products'] = $obj_product->getList('*',array('order_id'=>$order_id,'refund_type'=>$refund_type,'status'=>1));
        
        //echo '<pre>';print_r($this->pagedata['return_products']);exit;
        $this->output('aftersales');
    }

    public function select_address(){
        if($_POST['refund_type'] == 2){
            $sto= kernel::single("business_memberstore",$this->member['member_id']);  
            $obj_ads = app::get('business')->model('dlyaddress');
            $addresses = $obj_ads->getList('*',array('store_id'=>$sto->storeinfo['store_id']));
            foreach($addresses as $k=>$v){
                $sdf = explode(':',$v['region']);
                $addresses[$k]['region'] = $sdf[1];
            }
            $this->pagedata['addresses'] = $addresses;
            $this->pagedata['return_id'] = $_POST['return_id'];
            $this->display('site/member/select_address.html','aftersales');
        }else{
            $this->pagedata['return_id'] = $_POST['return_id'];
            $this->pagedata['order_id'] = $_POST['order_id'];
            if($_POST['comment'] == '虚假发货'){
                $this->pagedata['comment'] = $_POST['comment'];
            }
            $this->display('site/member/do_agree_return.html','aftersales');
            //$this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'do_agree_return','arg1'=>$_POST['return_id']));
        }
    }

    public function do_agree_return(){
        //添加赔付金
        if($_POST['comment'] == '虚假发货'){
            $order_obj = app::get('b2c')->model('orders');
            $store_obj = app::get('business')->model('storemanger');
            $info = $order_obj->dump($_POST['order_id'],'store_id,total_amount,cost_freight');
                
            $company_earnest = $store_obj->dump($info['store_id'],'earnest');
            
            $earnest_value = ($info['total_amount'] - $info['shipping']['cost_shipping'])*0.3;
            if($earnest_value > 500){
                $earnest_value = 500;
            }

            $data['earnest'] = $company_earnest['earnest']-$earnest_value;

            $obj_log = app::get('business')->model('earnest_log');
            $log_data['store_id'] = $info['store_id'];
            $log_data['earnest_value'] = (0-$earnest_value);
            $log_data['last_modify'] = time();
            $log_data['addtime'] = time();
            $log_data['expiretime'] = time();
            $log_data['source'] = '1';
            $log_data['operator'] = $this->member['member_id'];
            $log_data['remark'] = '虚假发货赔付金';
            $log_data['orders'] = $_POST['order_id'];
            if ($obj_log->insert($log_data)){
                $store_obj->update($data, array('store_id'=>$info['store_id']));
            }else{
                $msg = app::get('b2c')->_("修改失败");
            }

        }
        $url = $this->gen_url(array('app' =>'business','ctl'=>'site_member','act' =>'seller_order'));
        $rp = app::get('aftersales')->model('return_product');
        $objOrder = app::get('b2c')->model('orders');
        if($_POST['no_need_refund'] == '1'){
            $returns = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
            $obj_return_policy = kernel::single('aftersales_data_return_policy');

            $re_sdf = array(
                'return_id' => $_POST['return_id'],
                'status' => '3',
            );

            //生成退款单
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

            $obj_order = &$this->app->model('orders');
            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $sdf_order = $obj_order->dump($returns['order_id'],'*',$subsdf);

            $sdf['money'] = $returns['amount'];
            //$sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

            $refunds = app::get('ectools')->model('refunds');
            //$objOrder->op_id = $this->user->user_id;
            //$objOrder->op_name = $this->user->user_data['account']['name'];
            $sdf['op_id'] = $this->member['member_id'];
            //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
            unset($sdf['inContent']);
            
            $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');
            $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

            $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
               
            $time = time();
            $sdf['refund_id'] = $refund_id = $refunds->gen_id();
            $sdf['pay_app_id'] = $sdf['payment'];
            $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

            $obj_members = app::get('pam')->model('account');
            $buy_name = $obj_members->getRow('login_name',array('account_id'=>$sdf['member_id']));
            $sdf['account'] = $buy_name['login_name'];

            $sdf['currency'] = $sdf_order['currency'];
            $sdf['paycost'] = 0;
            $sdf['cur_money'] = $sdf['money'];
            //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
            $sdf['t_begin'] = $time;
            $sdf['t_payed'] = $time;
            $sdf['t_confirm'] = $time;
            $sdf['pay_object'] = 'order';
            $sdf['op_id'] = $this->member['member_id'];
            $o_account = app::get('pam')->model('account');
            $uname = $o_account->dump($this->member['member_id']);
            $sdf['op_name'] = $uname['login_name'];
            $sdf['status'] = 'ready';
            $sdf['app_name'] = $arrPaymentInfo['app_name'];
            $sdf['app_version'] = $arrPaymentInfo['app_version'];
            $sdf['refund_type'] = '1';
            $sdf['order_id'] = $returns['order_id'];
            $sdf['is_safeguard'] = $returns['is_safeguard'];
            if (!$obj_checkorder->check_order_refund($returns['order_id'],$sdf,$message))
            {
                 $this->splash('failed',$url,$message);
            }
            $obj_refunds = kernel::single("ectools_refund");
            
            //开始确认收货时间
            $confirm_time = $objOrder->getRow('confirm_time,status,score_u,member_id',array('order_id'=>$returns['order_id']));
            if($confirm_time['status'] == 'active'){
                $rs_buyer = $obj_refunds->generate($sdf, $this, $msg);
            }
            $time = $confirm_time['confirm_time'] + time();
            
            $refund_data = $refunds->dump($refund_id,'*');

            $score_u = $confirm_time['score_u']-$returns['return_score'];

            //修改订单状态
            if($confirm_time['status'] == 'active'){
                $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_u'=>$score_u);
            }else{
                //修改订单状态
                    if($returns['safeguard_require'] == '3' || $returns['safeguard_require'] == '4'){
                        $refund_status = array('refund_status'=>'4','score_u'=>$score_u);
                    }else{
                        $refund_status = array('refund_status'=>'11','score_u'=>$score_u);
                    }
            }
            $rs = $objOrder->update($refund_status,array('order_id'=>$returns['order_id']));

            //退还积分
            $obj_members_point = kernel::service('b2c_member_point_add');
            $obj_members_point->change_point($confirm_time['member_id'],intval($returns['return_score']), $msg, 'order_refund_use', 1, $returns['order_id'],0, 'refund');
            
            $obj_bills = app::get('ectools')->model('order_bills');
            $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
            $payment_id = $refunds->get_payment($order_id['rel_id']);
            $obj_payment = app::get('ectools')->model('payments');
            $cur_money = $obj_payment->dump($payment_id['bill_id'],'*');

            //判断是否是合并付款
            if($cur_money['merge_payment_id'] != ''){
                $payment_id['bill_id'] = $cur_money['merge_payment_id'];
                $cur_money['cur_money'] = 0;
                $total = $obj_payment->getList('*',array('merge_payment_id'=>$payment_id['bill_id'],'status'=>'succ'));
                foreach($total as $key=>$val){
                    $cur_money['cur_money'] = $cur_money['cur_money'] + $val['cur_money'];
                }
            }

            if($confirm_time['status'] == 'active'){
                if($refund_data['pay_app_id'] != 'deposit'){
                    if($refund_data['cur_money'] == 0){
                        $obj_refunds = kernel::single("ectools_refund");
                        $ref_rs = $obj_refunds->generate_after($sdf);
                    }else{
                        $refund_data['payment_info'] = $cur_money;
                        $result = $obj_refunds->dorefund($refund_data,$this);
                        $obj_refunds->callback($refund_data,$result);
                    }
         
                    if($result == "success"){
                        
                        $obj_refunds = kernel::single("ectools_refund");
                        
                        if ($ref_rs)
                        {

                            $obj_refund_lists = kernel::servicelist("order.refund_finish");
                            foreach ($obj_refund_lists as $order_refund_service_object)
                            {                
                                $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                            }
                            // 发送同步日志.
                            $order_refund_service_object->send_request($sdf);

                            //ajx crm
                            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                            $req_arr['order_id']=$sdf['order_id'];
                            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                            $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                            if ($this->pagedata['return_status'])
                                $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
                            
                            $obj_aftersales = kernel::servicelist("api.aftersales.request");
                            foreach ($obj_aftersales as $obj_request)
                            {
                                $obj_request->send_update_request($sdf);
                            }

                            //判断如果已经全部退款  则给积分（没有退还商品的情况）
                            $order_data = $objOrder->getRow('pay_status',array('order_id'=>$sdf['order_id']));
                            if($order_data['pay_status'] == '5'){
                                $obj_order->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                            }

                            //添加退款日志
                            if ($this->member['member_id'])
                            {
                                $obj_members = app::get('b2c')->model('members');
                                $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
                            }

                            $log_text = "卖家同意退款";
                            $result = "SUCCESS";

                            $returnLog = app::get('aftersales')->model("return_log");
                            $sdf_return_log = array(
                                'order_id' => $returns['order_id'],
                                'return_id' => $returns['return_id'],
                                'op_id' => $this->member['member_id'],
                                'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                                'alttime' => time(),
                                'behavior' => 'agreereturn',
                                'result' => $result,
                                'role' => 'seller',
                                'log_text' => $log_text,
                            );

                            $log_id = $returnLog->save($sdf_return_log);

                            $this->splash('success',$url,app::get('aftersales')->_('操作成功'));

                        }else{

                            $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                            if ($this->pagedata['return_status'])
                                $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];

                            $obj_refund_lists = kernel::servicelist("order.refund_finish");
                            foreach ($obj_refund_lists as $order_refund_service_object)
                            {                
                                $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                            }
                            
                            $obj_aftersales = kernel::servicelist("api.aftersales.request");
                            foreach ($obj_aftersales as $obj_request)
                            {
                                $obj_request->send_update_request($sdf);
                            }

                            //判断如果已经全部退款  则给积分（没有退还商品的情况）
                            $order_data = $objOrder->getRow('pay_status',array('order_id'=>$sdf['order_id']));
                            if($order_data['pay_status'] == '5'){
                                $obj_order->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                            }

                            //添加退款日志
                            if ($this->member['member_id'])
                            {
                                $obj_members = app::get('b2c')->model('members');
                                $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
                            }

                            $log_text = "卖家同意退款";
                            $result = "SUCCESS";

                            $returnLog = app::get('aftersales')->model("return_log");
                            $sdf_return_log = array(
                                'order_id' => $returns['order_id'],
                                'return_id' => $returns['return_id'],
                                'op_id' => $this->member['member_id'],
                                'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                                'alttime' => time(),
                                'behavior' => 'agreereturn',
                                'result' => $result,
                                'role' => 'seller',
                                'log_text' => $log_text,
                            );

                            $log_id = $returnLog->save($sdf_return_log);

                            $this->splash('success',$url,'退款成功，更新退款单失败！');
                        }
                    }else{

                        $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                        if ($this->pagedata['return_status'])
                            $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];

                        $obj_refund_lists = kernel::servicelist("order.refund_finish");
                        foreach ($obj_refund_lists as $order_refund_service_object)
                        {                
                            $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                        }
                        
                        $obj_aftersales = kernel::servicelist("api.aftersales.request");
                        foreach ($obj_aftersales as $obj_request)
                        {
                            $obj_request->send_update_request($sdf);
                        }

                        //判断如果已经全部退款  则给积分（没有退还商品的情况）
                        $order_data = $objOrder->getRow('pay_status',array('order_id'=>$sdf['order_id']));
                        if($order_data['pay_status'] == '5'){
                            $obj_order->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                        }

                        //添加退款日志
                        if ($this->member['member_id'])
                        {
                            $obj_members = app::get('b2c')->model('members');
                            $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
                        }

                        $log_text = "卖家同意退款";
                        $result = "SUCCESS";

                        $objOrderLog = app::get('b2c')->model("order_log");

                        $sdf_order_log = array(
                            'rel_id' => $returns['order_id'],
                            'op_id' => $this->member['member_id'],
                            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                            'alttime' => time(),
                            'bill_type' => 'order',
                            'behavior' => 'refunds',
                            'result' => 'SUCCESS',
                            'log_text' => $log_text,
                        );
                        $log_id = $objOrderLog->save($sdf_order_log);

                        $this->splash('success',$url,'退款成功,结算失败,请等待管理员结算'.$result['1']);
                    }
                }else{
                    $obj_refunds = kernel::single("ectools_refund");
                    $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>$sdf['refund_type']));
                    
                    $obj_refund_lists = kernel::servicelist("order.refund_finish");
                    foreach ($obj_refund_lists as $order_refund_service_object)
                    {                
                        $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                    }
                    // 发送同步日志.
                    $order_refund_service_object->send_request($sdf);

                    //ajx crm
                    $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                    $req_arr['order_id']=$sdf['order_id'];
                    $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                    $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                    if ($this->pagedata['return_status'])
                        $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
                    
                    $obj_aftersales = kernel::servicelist("api.aftersales.request");
                    foreach ($obj_aftersales as $obj_request)
                    {
                        $obj_request->send_update_request($sdf);
                    }

                    //添加退款日志
                    if ($this->member['member_id'])
                    {
                        $obj_members = app::get('b2c')->model('members');
                        $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
                    }

                    $log_text = "卖家同意退款";
                    $result = "SUCCESS";

                    $returnLog = app::get('aftersales')->model("return_log");
                    $sdf_return_log = array(
                        'order_id' => $returns['order_id'],
                        'return_id' => $returns['return_id'],
                        'op_id' => $this->member['member_id'],
                        'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                        'alttime' => time(),
                        'behavior' => 'agreereturn',
                        'result' => $result,
                        'role' => 'seller',
                        'log_text' => $log_text,
                    );

                    $log_id = $returnLog->save($sdf_return_log);

                    $this->splash('success',$url,app::get('aftersales')->_('退款成功,请等待管理员结算'));
                }
            }else{
                //申请售后流程
                /*$obj_refunds = kernel::single("ectools_refund");
                $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>$sdf['refund_type']));

                $obj_refund_lists = kernel::servicelist("order.refund_finish");
                foreach ($obj_refund_lists as $order_refund_service_object)
                {                
                    $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                }
                // 发送同步日志.
                $order_refund_service_object->send_request($sdf);*/

                //ajx crm
                $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                $req_arr['order_id']=$sdf['order_id'];
                $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');


                if($returns['safeguard_require'] == '3' || $returns['safeguard_require'] == '4'){
                    $re_sdf = array(
                        'return_id' => $_POST['return_id'],
                        'status' => '4',
                    );
                    $obj_refund_lists = kernel::servicelist("order.refund_finish");
                    foreach ($obj_refund_lists as $order_refund_service_object)
                    {                
                        $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                    }
                }else{
                    $re_sdf = array(
                        'return_id' => $_POST['return_id'],
                        'status' => '15',
                    );
                }

                $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                if ($this->pagedata['return_status'])
                    $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
                
                $obj_aftersales = kernel::servicelist("api.aftersales.request");
                foreach ($obj_aftersales as $obj_request)
                {
                    $obj_request->send_update_request($sdf);
                }

                //添加退款日志
                if ($this->member['member_id'])
                {
                    $obj_members = app::get('b2c')->model('members');
                    $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
                }

                $log_text = "卖家同意退款";
                $result = "SUCCESS";

                $returnLog = app::get('aftersales')->model("return_log");
                $sdf_return_log = array(
                    'order_id' => $returns['order_id'],
                    'return_id' => $returns['return_id'],
                    'op_id' => $this->member['member_id'],
                    'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                    'alttime' => time(),
                    'behavior' => 'agreereturn',
                    'result' => $result,
                    'role' => 'seller',
                    'log_text' => $log_text,
                );

                $log_id = $returnLog->save($sdf_return_log);

                $objOrderLog = app::get('b2c')->model("order_log");

                $sdf_order_log = array(
                    'rel_id' => $returns['order_id'],
                    'op_id' => $this->member['member_id'],
                    'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'refunds',
                    'result' => $result,
                    'log_text' => $log_text,
                );

                $log_id = $objOrderLog->save($sdf_order_log);

                $this->splash('success',$url,app::get('aftersales')->_('退款成功,请等待管理员结算'));
            }
        }else{
            $returns = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
            $obj_return_policy = kernel::single('aftersales_data_return_policy');

            $re_sdf = array(
                'return_id' => $_POST['return_id'],
                'status' => '3',
                'refund_address' => $_POST['deladdress'],
                'close_time'=> 86400*(app::get('b2c')->getConf('member.to_buyer_refund'))+time(),
            );

            //修改订单状态
            $rs = $objOrder->getRow('score_u',array('order_id'=>$returns['order_id']));
            $score_u = $rs['score_u'] - $returns['return_score'];

            $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
            if ($this->pagedata['return_status'])
                $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];

            $obj_refund_lists = kernel::servicelist("order.refund_finish");
            foreach ($obj_refund_lists as $order_refund_service_object)
            {                
                $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
            }
            
            $obj_aftersales = kernel::servicelist("api.aftersales.request");
            foreach ($obj_aftersales as $obj_request)
            {
                $obj_request->send_update_request($sdf);
            }

            //积分重新计算
            $obj_items = app::get('b2c')->model('order_items');
            $items = $obj_items->getList('score,sendnum',array('order_id'=>$returns['order_id']));
            $score = 0;
            foreach($items as $key=>$val){
                $score = $score+$val['score']*$val['sendnum'];
            }
            $data = array('score_g'=>$score,'refund_status'=>'3','score_u'=>$score_u);
            $objOrder->update($data,array('order_id'=>$returns['order_id']));
            $rp->update(array('seller_comment'=>$_POST['seller_comment']),array('return_id'=>$_POST['return_id']));

            //添加退款日志
            if ($this->member['member_id'])
            {
                $obj_members = app::get('b2c')->model('members');
                $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
            }

            $log_text = "卖家同意申请,卖家留言:".$_POST['seller_comment'];
            $result = "SUCCESS";

            $returnLog = app::get('aftersales')->model("return_log");
            $sdf_return_log = array(
                'order_id' => $returns['order_id'],
                'return_id' => $returns['return_id'],
                'op_id' => $this->member['member_id'],
                'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                'alttime' => time(),
                'behavior' => 'agreeapp',
                'result' => $result,
                'role' => 'seller',
                'log_text' => $log_text,
            );

            $log_id = $returnLog->save($sdf_return_log);

            $objOrderLog = app::get('b2c')->model("order_log");

            $sdf_order_log = array(
                'rel_id' => $returns['order_id'],
                'op_id' => $this->member['member_id'],
                'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'refunds',
                'result' => 'SUCCESS',
                'log_text' => $log_text,
            );
            $log_id = $objOrderLog->save($sdf_order_log);

            $this->splash('success',$url,app::get('aftersales')->_('操作成功'));
        }

    }

    public function do_no_return(){
        $this->pagedata['order_id'] = $_POST['order_id'];
        $this->pagedata['return_id'] = $_POST['return_id'];
        $obj_product = app::get('aftersales')->model('return_product');
        $this->pagedata['return_products'] = $obj_product->getList('*',array('return_id'=>$_POST['return_id']));
        $this->display('site/member/do_no_return.html','aftersales');
    }

    public function send_finish($return_id){
        $url = $this->gen_url(array('app' =>'business','ctl'=>'site_member','act' =>'seller_order'));
        $rp = app::get('aftersales')->model('return_product');
        $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
        $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');
        $obj_order = &$this->app->model('orders');
        if($_POST['refuse'] == '1'){
            $returns = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
            $this->pagedata['money'] = $returns['amount'];
            $this->pagedata['return_id'] = $_POST['return_id'];
            $this->output('aftersales');
        }else{
            //$url = $this->gen_url(array('app' =>'aftersales','ctl'=>'site_member','act' =>'seller_returns'));
            if($return_id){
                $returns = $rp->getRow('*',array('return_id'=>$return_id));
            }else{
                $returns = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
            }
            $obj_return_policy = kernel::single('aftersales_data_return_policy');
            if($return_id){
                $sdf = array(
                    'return_id' => $return_id,
                    'status' => '6',
                );
            }else{
                $sdf = array(
                    'return_id' => $_POST['return_id'],
                    'status' => '6',
                );
            }
            $this->pagedata['return_status'] = $obj_return_policy->change_status($sdf); 
            $obj_aftersales = kernel::servicelist("api.aftersales.request");
            foreach ($obj_aftersales as $obj_request)
            {
                $obj_request->send_update_request($sdf);
            }
            
            //判断是否是完结的订单
            $order_id = $rp->getRow('order_id,return_score',array('return_id'=>$_POST['return_id']));
            $status = $obj_order->getRow('status,score_u',array('order_id'=>$order_id['order_id']));
            if($status['status'] == 'active'){
                //生成退款单
           
                $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

                $obj_order = &$this->app->model('orders');
                $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
                $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

                $sdf['money'] = $returns['amount'];
                $sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

                $refunds = app::get('ectools')->model('refunds');
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                $sdf['op_id'] = $this->member['member_id'];
                $o_account = app::get('pam')->model('account');
                $uname = $o_account->dump($this->member['member_id']);
                $sdf['op_name'] = $uname['login_name'];
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                unset($sdf['inContent']);
                
                $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');
                $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $time = time();
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['pay_app_id'] = $sdf['payment'];
                $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

                $obj_members = app::get('pam')->model('account');
                $buy_name = $obj_members->getRow('login_name',array('account_id'=>$sdf['member_id']));
                $sdf['account'] = $buy_name['login_name'];

                $sdf['currency'] = $sdf_order['currency'];
                $sdf['paycost'] = 0;
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['t_begin'] = $time;
                $sdf['t_payed'] = $time;
                $sdf['t_confirm'] = $time;
                $sdf['pay_object'] = 'order';
                $sdf['op_id'] = $this->member['member_id'];
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '1';
                $sdf['is_safeguard'] = $returns['is_safeguard'];
                if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                     $this->splash('failed',$url,$message);
                }
                $obj_refunds = kernel::single("ectools_refund");
                $rs_buyer = $obj_refunds->generate($sdf, $this, $msg);

                $obj_bills = app::get('ectools')->model('order_bills');
                $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
                $payment_id = $refunds->get_payment($order_id['rel_id']);
                $obj_payment = app::get('ectools')->model('payments');
                $cur_money = $obj_payment->dump($payment_id['bill_id'],'*');

                //判断是否是合并付款
                if($cur_money['merge_payment_id'] != ''){
                    $payment_id['bill_id'] = $cur_money['merge_payment_id'];
                    $cur_money['cur_money'] = 0;
                    $total = $obj_payment->getList('*',array('merge_payment_id'=>$payment_id['bill_id'],'status'=>'succ'));
                    foreach($total as $key=>$val){
                        $cur_money['cur_money'] = $cur_money['cur_money'] + $val['cur_money'];
                    }
                }

                //退款
                $refund_data = $refunds->getRow('*',array('refund_id'=>$sdf['refund_id']));
                //echo "<pre>";print_r($refund_data);exit;
                if($refund_data['pay_app_id'] != 'deposit'){
                    if($refund_data['cur_money'] == 0){
                        $obj_refunds = kernel::single("ectools_refund");
                        $ref_rs = $obj_refunds->generate_after($sdf);
                    }else{
                        $refund_data['payment_info'] = $cur_money;
                        $result = $obj_refunds->dorefund($refund_data,$this);
                        $obj_refunds->callback($refund_data,$result);
                    }
                }

                $obj_refund_lists = kernel::servicelist("order.refund_finish");
                foreach ($obj_refund_lists as $order_refund_service_object)
                {                
                    $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                }

                //添加退款日志
                if ($this->member['member_id'])
                {
                    $obj_members = app::get('b2c')->model('members');
                    $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
                }

                $log_text = "卖家同意退款";
                $result = "SUCCESS";

                $returnLog = app::get('aftersales')->model("return_log");
                $sdf_return_log = array(
                    'order_id' => $returns['order_id'],
                    'return_id' => $returns['return_id'],
                    'op_id' => $this->member['member_id'],
                    'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                    'alttime' => time(),
                    'behavior' => 'agreereturn',
                    'result' => $result,
                    'role' => 'seller',
                    'log_text' => $log_text,
                );

                $log_id = $returnLog->save($sdf_return_log);

                $aUpdate['order_id'] = $returns['order_id'];
                $member_id = $obj_order->dump($returns['order_id'],'member_id');

                $obj_order->fireEvent('returned', $aUpdate, $member_id['member_id']);
                //生成运费结算单
                if($returns['ship_cost'] > 0 || $returns['amount_seller']>0){
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    
                    $math = kernel::single("ectools_math");
                    $profit = ($returns['ship_cost']+$returns['amount_seller'])*($freight_pro/100);
                    $sdf['profit'] = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);
                    $sdf['money'] = $returns['ship_cost']+$returns['amount_seller']-$sdf['profit'];

                    unset($sdf['return_score']);

                    $refunds = app::get('ectools')->model('refunds');
                    //$objOrder->op_id = $this->user->user_id;
                    //$objOrder->op_name = $this->user->user_data['account']['name'];
                    //$sdf['op_id'] = $this->user->user_id;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    
                    $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');

                    $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                        
                    $sdf['refund_id'] = $refund_ids = $refunds->gen_id();
                    $sdf['member_id'] = $sdf_order['store_id'] ? $sdf_order['store_id'] : 0;
                    $sdf['cur_money'] = $sdf['money'];
                    //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                    $sdf['op_id'] = $this->member['member_id'];
                    $o_account = app::get('pam')->model('account');
                    $uname = $o_account->dump($this->member['member_id']);
                    $sdf['op_name'] = $uname['login_name'];
                    $sdf['status'] = 'ready';
                    $sdf['app_name'] = $arrPaymentInfo['app_name'];
                    $sdf['app_version'] = $arrPaymentInfo['app_version'];
                    $sdf['refund_type'] = '2';
                    $obj_ys = app::get('business')->model('storemanger');
                    $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                    $sdf['account'] = $ys['company_name'];
                    $rs_seller = $obj_refunds->generate($sdf, $this, $msg);

                    $obj_order->update(array('status'=>'finish'),array('order_id'=>$returns['order_id']));
                    //需要结算结算单
                    
                    $refund_data = $refunds->getRow('*',array('refund_id'=>$refund_ids));
                    $bill = app::get('ectools')->model('order_bills');
                    $rel_order_id = $bill->dump(array('bill_id'=>$sdf['refund_id']),'rel_id');
                    if($refund_data['refund_type'] == '2' && $refund_data['status'] == 'ready'){
                        if($refund_data['pay_app_id'] == 'ysepay'){
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
                         }
                     }
                }

                if($result['0'] == "true"){
                    $obj_refunds = kernel::single("ectools_refund");
                    $ref_rs = $obj_refunds->generate_after($sdf);
                }else{
                    $refunds->update(array('memo'=>$result['1']),array('refund_id'=>$sdf['refund_id']));
                }
                
               
                if ($rs_buyer)
                {                             
                    if ($is_refund_finished)
                    {
                        //发送同步日志.
                        $order_refund_service_object->send_request($sdf);

                        //ajx crm
                        $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                        $req_arr['order_id']=$sdf['order_id'];
                        $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                        //积分重新计算
                        $obj_items = app::get('b2c')->model('order_items');
                        $items = $obj_items->getList('score,sendnum',array('order_id'=>$sdf['order_id']));
                        $score = 0;
                        foreach($items as $key=>$val){
                            $score = $score+$val['score']*$val['sendnum'];
                        }

                        //开始确认收货时间
                        $confirm_time = $obj_order->getRow('confirm_time,score_g,member_id,member_id,pay_status',array('order_id'=>$sdf['order_id']));
                        $time = $confirm_time['confirm_time'] + time();
                        
                        //修改订单状态
                        $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_g'=>$score);
                        $rs = $obj_order->update($refund_status,array('order_id'=>$sdf['order_id']));

                        //判断如果已经全部退款  则修改订单状态
                        if($confirm_time['pay_status'] == '5'){
                            $obj_order->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                        }

                        //修改会员的冻结积分
                        $point_obj = app::get('pointprofessional')->model('members');
                        $reduce_score = $confirm_time['score_g']-$score;
                        $point_obj->reduce_obtained($confirm_time['member_id'],$reduce_score,$sdf['order_id']);

                        //退还积分
                        $obj_members_point = kernel::service('b2c_member_point_add');
                        $obj_members_point->change_point($confirm_time['member_id'],intval($order_id['return_score']), $msg, 'order_refund_use', 1, $sdf['order_id'],0, 'refund');

                        $this->splash('success',$url,app::get('aftersales')->_('退款成功'));
                    }
                    else
                    {
                        $this->splash('failed',$url,app::get('aftersales')->_('退款成功，发送日志失败'));
                    }
                }
                else
                {
                    $this->splash('failed',$url,app::get('aftersales')->_('退款失败'));
                }
            }else{
                //售后申请流程        
                /*$is_refund_finished = false;
                $obj_refund_lists = kernel::servicelist("order.refund_finish");
                foreach ($obj_refund_lists as $order_refund_service_object)
                {                
                    $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                }

                //判断是否是完结的订单
                $order_id = $rp->getRow('order_id,return_score',array('return_id'=>$_POST['return_id']));
                $status = $obj_order->getRow('status,score_u',array('order_id'=>$order_id['order_id']));*/

                //添加退款日志
                if ($this->member['member_id'])
                {
                    $obj_members = app::get('b2c')->model('members');
                    $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
                }

                $log_text = "卖家同意退款";
                $result = "SUCCESS";

                $returnLog = app::get('aftersales')->model("return_log");
                $sdf_return_log = array(
                    'order_id' => $returns['order_id'],
                    'return_id' => $returns['return_id'],
                    'op_id' => $this->member['member_id'],
                    'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                    'alttime' => time(),
                    'behavior' => 'agreereturn',
                    'result' => $result,
                    'role' => 'seller',
                    'log_text' => $log_text,
                );

                $log_id = $returnLog->save($sdf_return_log);
                
                //if ($is_refund_finished)
                //{
                    // 发送同步日志.
                    //$order_refund_service_object->send_request($sdf);

                    //ajx crm
                    $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                    $req_arr['order_id']=$sdf['order_id'];
                    $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
                    //开始确认收货时间
                    $confirm_time = $obj_order->getRow('confirm_time',array('order_id'=>$sdf['order_id']));
                    $time = $confirm_time['confirm_time'] + time();
                    
                    //修改订单状态
                    if($returns['safeguard_require'] == '3' || $returns['safeguard_require'] == '4'){
                        $refund_status = array('refund_status'=>'4','score_u'=>$score_u);
                        $status = array('status'=>'4','close_time'=>time());
                        $obj_refund_lists = kernel::servicelist("order.refund_finish");
                        foreach ($obj_refund_lists as $order_refund_service_object)
                        {                
                            $sdf['op_id'] = $this->member['member_id'];
                            $sdf['op_name'] = (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'];
                            $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                        }
                    }else{
                        $refund_status = array('refund_status'=>'11','score_u'=>$score_u);
                        $status = array('status'=>'15','close_time'=>time());

                        $objOrderLog = app::get('b2c')->model("order_log");

                        $sdf_order_log = array(
                            'rel_id' => $returns['order_id'],
                            'op_id' => $this->member['member_id'],
                            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                            'alttime' => time(),
                            'bill_type' => 'order',
                            'behavior' => 'refunds',
                            'result' => 'SUCCESS',
                            'log_text' => $log_text,
                        );
                        $log_id = $objOrderLog->save($sdf_order_log);
                    }
                    $rs = $obj_order->update($refund_status,array('order_id'=>$sdf['order_id']));
                    $rs = $rp->update($status,array('return_id'=>$returns['return_id']));
                    $this->splash('success',$url,app::get('aftersales')->_('退款成功,等待卖家打款到平台！'));
                /*}
                else
                {
                    $this->splash('failed',$url,app::get('aftersales')->_('退款成功，发送日志失败'));
                }*/

            }
        }

    }

    public function gorefund_select($order_id){
        $obj_return = app::get('aftersales')->model('return_product');
        $returns = $obj_return->getRow('*',array('order_id'=>$order_id,'refund_type'=>'2','status'=>'1'));
        $return = $obj_return->getRow('*',array('order_id'=>$order_id,'refund_type|in'=>array('3','4'),'status'=>'1'));
        if($returns){
            $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'return_details','arg0'=>$returns['return_id']));
        }elseif($return){
            $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'return_details','arg0'=>$return['return_id']));
        }else{
            $this->pagedata['order_id'] = $order_id;
            $this->output('aftersales');
        }
        
    }

    public function swith_refund(){
        if($_POST['is_required'] == '1'){
            if($_POST['is_need_refund'] == '1'){
                $this->return_add_before($_POST['order_id']);
            }else{
                $this->gorefund($_POST['order_id'],3);
            }
        }else{
            $this->gorefund($_POST['order_id'],4);
        }
    }

    public function refund_add_buyer($order_id){

        //获取所有配送方式
        $shippings = $this->app->model('dlytype');
        $this->pagedata['shippings'] = $shippings->getList('*');
        $dlycorp = $this->app->model('dlycorp');
        $this->pagedata['corplist'] = $dlycorp->getList('*');
        $arrDlytype = $shippings->dump($this->pagedata['order']['shipping']['shipping_id']);
        $this->pagedata['order']['shipping']['corp_id'] = $arrDlytype['corp_id'];
        $objDelivery = $this->app->model('delivery');
        $arrDeliverys = $objDelivery->getList('*', array('order_id' => $order_id));

        //获取收货地址
        $returns = app::get('aftersales')->model('return_product');
        $refund_address = $returns->getRow('refund_address,return_id,close_time,seller_comment',array('order_id'=>$order_id,'status'=>'3','refund_type'=>'2'));
        $this->pagedata['return_id'] = $refund_address['return_id'];
        //$obj_order = $this->app->model('orders');
        //$store_id = $obj_order->getRow('store_id',array('order_id'=>$order_id));
        $obj_address = app::get('business')->model('dlyaddress');
        $address = $obj_address->getList('*',array('da_id'=>$refund_address['refund_address']));
        $regions = explode(':',$address[0]['region']);
        $region = $regions[1];
        $this->pagedata['refund_address'] = $region.'/'.$address[0]['address'].'，'.$address[0]['uname'].'，'.$address[0]['phone'];
        $this->pagedata['refunds'] = $refund_address['refund_address'];

        $this->pagedata['seller_comment'] = $refund_address['seller_comment'];

        $this->pagedata['time'] = ($refund_address['close_time'])*1000;
        $this->pagedata['now_time'] = time()*1000;
        $this->pagedata['order_id'] = $order_id;
        $this->output('aftersales');
    }

    public function reshipped(){
        //处理退货单
        $this->begin(array('app' =>'b2c','ctl'=>'site_member','act' =>'orders'));
        $rs_sdf['order_id'] = $_POST['order_id'];
        //配送方式
        $rs_sdf['delivery'] = "1";
        $rs_sdf['reason'] = "质量原因";
        //物流公司
        $rs_sdf['logi_id'] = $_POST['logi_id'];
        //$rs_sdf['other_name'] = "";
        //运单号
        $rs_sdf['logi_no'] = $_POST['logi_no'];
        //配送费用
        $rs_sdf['money'] = $_POST['money'];
        //是否保价
        $rs_sdf['is_protect'] = false;
        //获取退货信息
        $obj_address = app::get('business')->model('dlyaddress');
        $address = $obj_address->getList('*',array('da_id'=>$_POST['refund_address']));
        $rs_sdf['ship_name'] = $address[0]['uname'];
        $rs_sdf['ship_tel'] = $address[0]['phone'];
        $rs_sdf['ship_mobile'] = $address[0]['mobile'];
        $rs_sdf['ship_zip'] = $address[0]['zip'];
        $rs_sdf['ship_area'] = $address[0]['region'];
        $rs_sdf['ship_addr'] = $address[0]['address'];
        $rs_sdf['memo'] = $_POST['content'];
        //获取处理人
        $o_account = app::get('pam')->model('account');
        $uname = $o_account->dump($this->member['member_id']);
        $rs_sdf['op_name'] = $uname['login_name'];
        $rs_sdf['op_id'] = $this->member['member_id'];
        $rs_sdf['opname'] = $uname['login_name'];
        //处理退货物品
        $obj_order_items = app::get('b2c')->model('order_items');
        $items = $obj_order_items->getList('item_id,bn',array('order_id'=>$_POST['order_id']));
        $rp = app::get('aftersales')->model('return_product');
        $returns = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
        $item = unserialize($returns['product_data']);
        $send = array();
        foreach($item as $k1=>$v1){
            foreach($items as $k2=>$v2){
                if($v1['bn'] == $v2['bn']){
                    $send[$v2['item_id']] = $v1['num'];
                }
            }
        }
        $rs_sdf['send'] = $send;
        //处理上传图片
        if ( $_FILES['file']['size'] > 314572800 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过300M"), $com_url);
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif","jpeg","rar","zip");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file']['name'];
            $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
        }
        $rs_sdf['image_file'] = $image_id;
        //添加两张维权图片
        if ( $_FILES['file1']['size'] > 5242880 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file1']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file1']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file1']['name'];
            $image_id1 = $mdl_img->store($_FILES['file1']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id1,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id1, $type);
        }
        $rs_sdf['image_file1'] = $image_id1;

        if ( $_FILES['file2']['size'] > 5242880 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file2']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file2']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file2']['name'];
            $image_id2 = $mdl_img->store($_FILES['file2']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id2,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id2, $type);
        }
        $rs_sdf['image_file2'] = $image_id2;
        
        //echo '<pre>';print_r($rs_sdf);exit;
        $reship = app::get('b2c')->model('reship');
        $rs_sdf['reship_id'] = $reship->gen_id();
        $b2c_order_reship = b2c_order_reship::getInstance(app::get('b2c'), $reship);
        if ($b2c_order_reship->generate($rs_sdf, $this, $message))
        {
            //ajx crm 
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $req_arr['order_id']=$rs_sdf['order_id'];
            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
            //修改退款结束时间
            $close_time = time()+86400*(app::get('b2c')->getConf('member.to_agree'));
            $ref_data = array('close_time'=>$close_time,'status'=>12);
            $res = $rp->update($ref_data,array('return_id'=>$_POST['return_id']));
            
            //修改订单状态
            $obj_order = $this->app->model('orders');
            $refund_status = array('refund_status'=>'5');
            $rs = $obj_order->update($refund_status,array('order_id'=>$returns['order_id']));

            //添加退款日志
            if ($this->member['member_id'])
            {
                $obj_members = app::get('b2c')->model('members');
                $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
            }

            $log_text = "买家退货";
            $result = "SUCCESS";
            $image_file = $rs_sdf['image_file'].','.$rs_sdf['image_file1'].','.$rs_sdf['image_file2'];
            $returnLog = app::get('aftersales')->model("return_log");
            $sdf_return_log = array(
                'order_id' => $returns['order_id'],
                'return_id' => $returns['return_id'],
                'op_id' => $this->member['member_id'],
                'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
                'alttime' => time(),
                'behavior' => 'reship',
                'result' => $result,
                'role' => 'member',
                'log_text' => $log_text,
                'image_file' => $image_file,
            );

            $log_id = $returnLog->save($sdf_return_log);

            $this->end(true, app::get('aftersales')->_('操作成功'));
        }
        else
        {
            $this->end(false,$message);
        }
    }

    function refuse(){
        $rp = app::get('aftersales')->model('return_product');
        $obj_order = &$this->app->model('orders');

        $url = $this->gen_url(array('app' =>'business','ctl'=>'site_member','act' =>'seller_returns_reship'));
        //处理申请单
        $order_id = $rp->getRow('order_id',array('return_id'=>$_POST['return_id']));
        $status['status'] = '14';
        $status['seller_reason'] = $_POST['seller_reason'];
        $status['close_time'] = time();
        $retutn = $rp->update($status,array('return_id'=>$_POST['return_id']));

        
        //修改订单状态
        $refund_status = array('refund_status'=>'7');
        $rs = $obj_order->update($refund_status,array('order_id'=>$order_id['order_id']));

        //添加退款日志
        if ($this->member['member_id'])
        {
            $obj_members = app::get('b2c')->model('members');
            $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
        }

        $log_text = "卖家拒绝退款,拒绝原因".$_POST['seller_reason'];
        $result = "SUCCESS";
        $returnLog = app::get('aftersales')->model("return_log");
        $sdf_return_log = array(
            'order_id' => $order_id['order_id'],
            'return_id' => $_POST['return_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'behavior' => 'reship',
            'result' => $result,
            'role' => 'seller',
            'log_text' => $log_text,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = app::get('b2c')->model("order_log");

        $sdf_order_log = array(
            'rel_id' => $order_id['order_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);

        $this->splash('success',$url,app::get('aftersales')->_('拒绝成功'));
    }
    

    function edit_refund_app($order_id){
        $rp = app::get('aftersales')->model('return_product');

        $return_id = $rp->dump(array('order_id'=>$order_id,'status'=>14));
        $obj_return_policy = kernel::single('aftersales_data_return_policy');
        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($return_id['return_id']);

        //添加退货地址显示
        $obj_address = app::get('business')->model('dlyaddress');
        $address = $obj_address->getList('*',array('da_id'=>$this->pagedata['return_item']['refund_address']));
        $ads = explode(':',$address['0']['region']);
        $address['0']['region'] = $ads[1];
        $this->pagedata['address'] = $address['0'];

        $this->output('aftersales');

        //echo "<pre>";print_r($return_id);exit;
    }

    function edit_rp(){
        $rp = app::get('aftersales')->model('return_product');
        $obj_order = &$this->app->model('orders');

        $url = $this->gen_url(array('app' =>'aftersales','ctl'=>'site_member','act' =>'return_list'));
        //处理申请单
        $order_id = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
        if($order_id['shop_cost'] || $order_id['amount_seller']){
            $total = $order_id['shop_cost']+$order_id['amount_seller']+$order_id['amount'];
            $status['amount'] = $_POST['amount'];
            $status['shop_cost'] = $order_id['shop_cost'];
            $amount_seller = $total - $status['shop_cost'] - $status['amount'];
            $status['amount_seller'] = $amount_seller;
        }else{
            $status['amount'] = $_POST['amount'];
        }

        //如果是售后，修改卖家需要承担的金额(暂时处理方式：卖家承担的金额减去修改差额)
        if($order_id['is_safeguard'] == '2'){
            $status['seller_amount'] = $order_id['seller_amount'] - ($order_id['amount'] - $_POST['amount']);
        }
        $status['status'] = '12';
        $status['close_time'] = time()+86400*(app::get('b2c')->getConf('member.to_agree'));
        
        $retutn = $rp->update($status,array('return_id'=>$_POST['return_id']));

        
        //修改订单状态
        $refund_status = array('refund_status'=>'5');
        $rs = $obj_order->update($refund_status,array('order_id'=>$order_id['order_id']));

        //添加退款日志
        if ($this->member['member_id'])
        {
            $obj_members = app::get('b2c')->model('members');
            $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
        }

        $log_text = "买家修改退款申请,修改金额为：".$_POST['amount']."元";
        $result = "SUCCESS";
        $returnLog = app::get('aftersales')->model("return_log");
        $sdf_return_log = array(
            'order_id' => $order_id['order_id'],
            'return_id' => $_POST['return_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'behavior' => 'updates',
            'result' => $result,
            'role' => 'member',
            'log_text' => $log_text,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = app::get('b2c')->model("order_log");

        $sdf_order_log = array(
            'rel_id' => $order_id['order_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);

        $this->splash('success',$url,app::get('aftersales')->_('修改成功'));
    }

    function edit_refund($order_id){
        $rp = app::get('aftersales')->model('return_product');
        $objOrder = app::get('b2c')->model('orders');

        $return_id = $rp->dump(array('order_id'=>$order_id,'status'=>11));
        $obj_return_policy = kernel::single('aftersales_data_return_policy');
        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($return_id['return_id']);

        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);
        if($this->pagedata['order']['discount_value'] > 0){
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed']+($this->pagedata['order']['discount_value']);
        }else{
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed'];
        }
        $this->pagedata['now_time'] = time()*1000;
        $this->pagedata['time'] = ($this->pagedata['return_item']['close_time'] + 86400*(app::get('b2c')->getConf('member.to_buyer_edit')))*1000;

        $this->output('aftersales');
    }

    function return_refuse(){
        $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
        $rp = app::get('aftersales')->model('return_product');
        $obj_order = app::get('b2c')->model('orders');
        $rp->update(array('status'=>'10'),array('return_id'=>$_POST['return_id']));
        //开始确认收货时间
        $objOrder = app::get('b2c')->model('orders');
        $confirm_time = $objOrder->getRow('confirm_time',array('order_id'=>$_POST['order_id']));
        $time = $confirm_time['confirm_time'] + time();
        
        //查询订单状态
        $status = $obj_order->dump($_POST['order_id'],'status');
        if($status['status'] == 'active'){
            $obj_order->update(array('refund_status'=>'2','confirm_time'=>$time),array('order_id'=>$_POST['order_id']));
        }else{
            $obj_order->update(array('refund_status'=>'2'),array('order_id'=>$_POST['order_id']));
        }
        
        //添加退款日志
        if ($this->member['member_id'])
        {
            $obj_members = app::get('b2c')->model('members');
            $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
        }

        $log_text = "买家撤销";
        $result = "SUCCESS";
        $returnLog = app::get('aftersales')->model("return_log");
        $sdf_return_log = array(
            'order_id' => $_POST['order_id'],
            'return_id' => $_POST['return_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'behavior' => 'cancel',
            'result' => $result,
            'role' => 'member',
            'log_text' => $log_text,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = app::get('b2c')->model("order_log");

        $sdf_order_log = array(
            'rel_id' => $_POST['order_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);

        $this->end(true, app::get('b2c')->_('撤销成功！'));
    }

    function edit_refund_2($order_id){
        $rp = app::get('aftersales')->model('return_product');
        $objOrder = app::get('b2c')->model('orders');

        $return_id = $rp->dump(array('order_id'=>$order_id,'status'=>11));
        $obj_return_policy = kernel::single('aftersales_data_return_policy');
        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($return_id['return_id']);

        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $limit = 10;
        $objOrder = &$this->app_b2c->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        
        if($this->pagedata['order']['discount_value'] > 0){
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed']+($this->pagedata['order']['discount_value']);
            $this->pagedata['order']['payed'] = $this->pagedata['gorefund_price'];
        }else{
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed'];
        }

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id, $page, $limit);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

		$objMath = kernel::single("ectools_math");
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
			$tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
						$item['item_type'] = 'goods';

					if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
						$tmp_array = (array)$tmp_array;
						if (!$tmp_array) continue;
						
						$product_id = $tmp_array['products']['product_id'];
						if (!$order_items[$product_id]){
							$order_items[$product_id] = $tmp_array;
						}else{
							$order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
							$order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
						}
						//$order_items[$item['products']['product_id']] = $tmp_array;
					}
                }
            }
			else
			{
				if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
				{
					$tmp_array = (array)$tmp_array;
					if (!$tmp_array) continue;
					foreach ($tmp_array as $tmp){
						if (!$order_items[$tmp['product_id']]){
							$order_items[$tmp['product_id']] = $tmp;
						}else{
							$order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
							$order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
							$order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
						}
					}
				}
				//$order_items = array_merge($order_items, $tmp_array);
			}
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($order_items,($page-1)*$limit,$limit);
        $count = count($order_items);
        $arrMaxPage = $this->get_start($page, $count);
        $this->pagination($page, $arrMaxPage['maxPage'], 'return_add', array($order_id), 'aftersales', 'site_member');
        $this->pagedata['url'] = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_order_items', 'arg' => array($order_id)));
        if($this->pagedata['order']['status'] == 'finish'){
            $retutn_info = $rp->dump($return_id['return_id']);
            $this->pagedata['retutn_info'] = $retutn_info;
            $obj_products = app::get('b2c')->model('products');
            //需要退货的情况
            if($this->pagedata['return_item']['product_data']){
                $gorefund_price = 0;
                foreach($this->pagedata['return_item']['product_data'] as $k=>$v){
                    $price = $obj_products->dump(array('bn'=>$v['bn']),'price');
                    $gorefund_price = $gorefund_price + $price['price']['price']['price']*$v['num'];
                }
                $this->pagedata['amount_price'] = $gorefund_price;
                $this->pagedata['shipping_price'] = $this->pagedata['order']['shipping']['cost_shipping'];
                $gorefund_price = $gorefund_price+$this->pagedata['order']['shipping']['cost_shipping'];
                $this->pagedata['gorefund_price'] = $gorefund_price;
            }else{
                //不需要退货的情况
                $biggest_payed = $objOrder->dump($retutn_info['order_id'],'payed');
                $this->pagedata['biggest_payed'] = $biggest_payed['payed'];
            }
            //售后添加 售后服务类型
            $this->pagedata['type'] = array(array('id'=>'1','name'=>'商品问题'),array('id'=>'2','name'=>'七天无理由退换货'),array('id'=>'3','name'=>'发票无效'),array('id'=>'4','name'=>'退回多付的运费'),array('id'=>'5','name'=>'未收到退货'));

            //售后添加 售后要求
            $this->pagedata['require'] = array(array('id'=>'1','name'=>'不退货部分退款'),array('id'=>'2','name'=>'需要退货退款'),array('id'=>'3','name'=>'要求换货'),array('id'=>'4','name'=>'要求维修'),array('id'=>'5','name'=>'已经退货，要求退款'),array('id'=>'6','name'=>'要求退款'));

            
            $this->pagedata['_PAGE_'] = 'safeguard_edit.html';
        }
        $this->pagedata['now_time'] = time()*1000;
        $this->pagedata['time'] = ($this->pagedata['return_item']['close_time'] + 86400*(app::get('b2c')->getConf('member.to_buyer_edit')))*1000;

        $this->output('aftersales');
    }

    function s_mall_intervene(){
        $rp = app::get('aftersales')->model('return_product');
        $objOrder = app::get('b2c')->model('orders');
        $obj_return_policy = kernel::single('aftersales_data_return_policy');
        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($_POST['return_id']);      

        $this->pagedata['comment'] = array(array('id'=>'1','name'=>'空包裹，少货'),array('id'=>'2','name'=>'快递问题'),array('id'=>'3','name'=>'卖家发错货'),array('id'=>'4','name'=>'虚假发货'),array('id'=>'5','name'=>'多拍，搓牌，不想要'),array('id'=>'6','name'=>'其他'));
        $this->output('aftersales');
    }

    function intereven(){
        $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
        $rp = app::get('aftersales')->model('return_product');
        $rp->update(array('is_intervene'=>'2','intervene_reason'=>$_POST['comment'],'intervene_phone'=>$_POST['phone'],'intervene_mail'=>$_POST['mail']),array('return_id'=>$_POST['return_id']));
        $objOrder = app::get('b2c')->model('orders');
        $objOrder->update(array('refund_status'=>'8'),array('order_id'=>$_POST['order_id']));

        //添加退款日志
        if ($this->member['member_id'])
        {
            $obj_members = app::get('b2c')->model('members');
            $arrPams = $obj_members->dump($this->member['member_id'], '*', array(':account@pam' => array('*')));
        }

        $log_text = "买家要求平台介入";
        $result = "SUCCESS";
        $returnLog = app::get('aftersales')->model("return_log");
        $sdf_return_log = array(
            'order_id' => $_POST['order_id'],
            'return_id' => $_POST['return_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('买家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'behavior' => 'intereven',
            'result' => $result,
            'role' => 'member',
            'log_text' => $log_text,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = app::get('b2c')->model("order_log");

        $sdf_order_log = array(
            'rel_id' => $_POST['order_id'],
            'op_id' => $this->member['member_id'],
            'op_name' => (!$this->member['member_id']) ? app::get('b2c')->_('卖家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);

        $this->end(true, app::get('aftersales')->_('申请成功，等待卖家举证！'));
    }

    //申请维权方法
    function safeguard($order_id){
        $this->pagedata['order_id'] = $order_id;
        $this->output('aftersales');
    }

    public function swith_safeguard(){
        if($_POST['is_required'] == '1'){
            $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'safeguard_add','arg0'=>$_POST['order_id']));
        }else{
            $this->redirect(array('app'=>'aftersales', ctl=>'site_member','act'=>'add_safeguard','arg0'=>$_POST['order_id']));
        }
    }

    public function safeguard_add($order_id,$page=1)
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $limit = 10;
        $objOrder = &$this->app_b2c->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        
        if($this->pagedata['order']['discount_value'] > 0){
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed']+($this->pagedata['order']['discount_value']);
            $this->pagedata['order']['payed'] = $this->pagedata['gorefund_price'];
        }else{
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed'];
        }

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id, $page, $limit);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

		$objMath = kernel::single("ectools_math");
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
			$tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
						$item['item_type'] = 'goods';

					if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
						$tmp_array = (array)$tmp_array;
						if (!$tmp_array) continue;
						
						$product_id = $tmp_array['products']['product_id'];
						if (!$order_items[$product_id]){
							$order_items[$product_id] = $tmp_array;
						}else{
							$order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
							$order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
						}
						//$order_items[$item['products']['product_id']] = $tmp_array;
					}
                }
            }
			else
			{
				if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
				{
					$tmp_array = (array)$tmp_array;
					if (!$tmp_array) continue;
					foreach ($tmp_array as $tmp){
						if (!$order_items[$tmp['product_id']]){
							$order_items[$tmp['product_id']] = $tmp;
						}else{
							$order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
							$order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
							$order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
						}
					}
				}
				//$order_items = array_merge($order_items, $tmp_array);
			}
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($order_items,($page-1)*$limit,$limit);
        $count = count($order_items);
        $arrMaxPage = $this->get_start($page, $count);
        $this->pagination($page, $arrMaxPage['maxPage'], 'return_add', array($order_id), 'aftersales', 'site_member');
        $this->pagedata['url'] = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_order_items', 'arg' => array($order_id)));

        //售后添加 售后服务类型
        $this->pagedata['type'] = array(array('id'=>'1','name'=>'商品问题'),array('id'=>'2','name'=>'七天无理由退换货'),array('id'=>'3','name'=>'发票无效'),array('id'=>'4','name'=>'退回多付的运费'));

        //售后添加 售后要求
        $this->pagedata['require_1'] = array(array('id'=>'1','name'=>'不退货部分退款'),array('id'=>'2','name'=>'需要退货退款'),array('id'=>'3','name'=>'要求换货'),array('id'=>'4','name'=>'要求维修'),array('id'=>'5','name'=>'已经退货，要求退款'));

        $this->pagedata['require_2'] = array(array('id'=>'1','name'=>'不退货部分退款'));

        $this->output('aftersales');
    }

    function add_safeguard($order_id){
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }
        
        $point_money_value = app::get('b2c')->getConf('site.point_money_value');

        if($this->pagedata['order']['discount_value'] > 0){
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed']+($this->pagedata['order']['discount_value']);
        }else{
            $this->pagedata['gorefund_price'] = $this->pagedata['order']['payed'];
        }
        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $objMath = kernel::single("ectools_math");
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
            $tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
                        $item['item_type'] = 'goods';

                    if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
                        $tmp_array = (array)$tmp_array;
                        if (!$tmp_array) continue;
                        
                        $product_id = $tmp_array['products']['product_id'];
                        if (!$order_items[$product_id]){
                            $order_items[$product_id] = $tmp_array;
                        }else{
                            $order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
                            $order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
                        }
                        //$order_items[$item['products']['product_id']] = $tmp_array;
                    }
                }
            }
            else
            {
                if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
                {
                    $tmp_array = (array)$tmp_array;
                    if (!$tmp_array) continue;
                    foreach ($tmp_array as $tmp){
                        if (!$order_items[$tmp['product_id']]){
                            $order_items[$tmp['product_id']] = $tmp;
                        }else{
                            $order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
                            $order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
                            $order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
                        }
                    }
                }
                //$order_items = array_merge($order_items, $tmp_array);
            }
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($order_items,($page-1)*$limit,$limit);

        //售后添加 售后服务类型
        $this->pagedata['type'] = array(array('id'=>'5','name'=>'未收到货'));

        //售后添加 售后要求
        $this->pagedata['require'] = array(array('id'=>'6','name'=>'要求退款'));
        
        $this->output('aftersales');
    }

    function edit_safeguard(){
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $rp = app::get('aftersales')->model('return_product');
        $money = $rp->dump(array('return_id'=>$_POST['return_id']),'amount,member_id,product_data,safeguard_type');
        $obj_order = app::get('b2c')->model('orders');
        //echo "<pre>";print_r($_POST);exit;
        

        $aData['content'] = $_POST['content'];
        if(isset($_POST['product_item'])){
            $aData['amount'] = $_POST['goods_amount'] + $_POST['shipping_amount'];
            $aData['shipping_amount'] = $_POST['shipping_amount'];
            $product_data = unserialize($money['product_data']);
            foreach($_POST['product_item'] as $key=>$val){
                foreach($product_data as $k=>$v){
                    if($v['bn'] == $key){
                        $product_data[$k]['refund'] = $val;
                    }
                }
            }
        }else{
            if($money['safeguard_type'] == '4'){
                //退邮费的情况
                $aData['amount'] = $_POST['noship_amount'];
                $freight_pro = app::get('b2c')->getConf('member.profit');
                $seller_amount = ($_POST['noship_amount'])*(1-$freight_pro/100);
            }else{
                $aData['amount'] = $_POST['noship_amount'];
                $seller_amount = $_POST['noship_amount'];
            }
        }

        $obj_cat = app::get('b2c')->model('goods_cat');
        $obj_goods = app::get('b2c')->model('goods');
        $obj_product = app::get('b2c')->model('products');

        //根据商品金额以及抽成比例算出商家出多少钱
        if(isset($_POST['product_item'])){
            $seller_amount = 0;
            foreach($product_data as $key=>$val){
                $good_id = $obj_product->dump(array('bn'=>$val['bn']),'goods_id');
                $cat_id = $obj_goods->dump($good_id['goods_id'],'cat_id');
                if(app::get('b2c')->getConf('member.isprofit') == 'true'){
                    $profit_point = $obj_cat->dump($cat_id['category']['cat_id'],'profit_point');
                    if(is_null($profit_point['profit_point'])){
                        $parent_id = $obj_cat->dump($cat_id['category']['cat_id'],'parent_id');
                        $profit_point = $obj_cat->dump($parent_id['parent_id'],'profit_point');
                    }
                }else{
                    $profit_point['profit_point'] = 0;
                }
                $seller_amount = $seller_amount + $val['refund']*(1-$profit_point['profit_point']/100);
            }
        }
        if($aData['shipping_amount'] > 0){
            $freight_pro = app::get('b2c')->getConf('member.profit');
            $seller_amount = $seller_amount + ($aData['shipping_amount'])*(1-$freight_pro/100);
        }
        $aData['seller_amount'] = $seller_amount;

        $aData['product_data'] = serialize($product_data);
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $order_info = $obj_order->dump($_POST['order_id'], '*', $subsdf);

        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        if($order_info['discount_value'] > 0){
            $gorefund_price = $order_info['payed']+($order_info['discount_value']);
        }else{
            $gorefund_price = $order_info['payed'];
        }

        if($_POST['gorefund_price']>$gorefund_price){
            $this->end(false, app::get('aftersales')->_("金额非法"));
        }

        $upload_file = "";
        if ( $_FILES['file']['size'] > 314572800 )
        {
            if($_POST['type'] == '1'){
			    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
            }else{
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            }
            $this->end(false, app::get('aftersales')->_("上传文件不能超过300M"), $com_url);
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif","jpeg","rar","zip");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                if($_POST['type'] == '1'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file']['name'];
            $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
        }
        if($image_id != ''){
            $aData['image_id'] = $image_id;
        }
        //添加两张维权图片
        if ( $_FILES['file1']['size'] > 5242880 )
        {
            if($_POST['type'] == '1'){
			    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
            }else{
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            }
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file1']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file1']['name'])), $type))
            {
                $text = implode(",", $type);
                if($_POST['type'] == '1'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file1']['name'];
            $image_id1 = $mdl_img->store($_FILES['file1']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id1,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id1, $type);
        }
        if($image_id1 != ''){
            $aData['image_file1'] = $image_id1;
        }

        if ( $_FILES['file2']['size'] > 5242880 )
        {
            if($_POST['type'] == '1'){
			    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
            }else{
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            }
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file2']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file2']['name'])), $type))
            {
                $text = implode(",", $type);
                if($_POST['type'] == '1'){
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_mai', 'arg0' => $_POST['order_id']));
                }else{
                    $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                }
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file2']['name'];
            $image_id2 = $mdl_img->store($_FILES['file2']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id2,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id2, $type);
        }
        if($image_id2 != ''){
            $aData['image_file2'] = $image_id2;
        }
     
        $aData['status'] = '1';
        $aData['add_time'] = time();

        $res = $rp->update($aData,array('return_id'=>$_POST['return_id']));
        $obj_order->update(array('refund_status'=>'1'),array('order_id'=>$_POST['order_id']));
        if($res){
            if ($money['member_id'])
            {
                $obj_members = app::get('b2c')->model('members');
                $arrPams = $obj_members->dump($money['member_id'], '*', array(':account@pam' => array('*')));
            }
            $behavior = "updates";
            $log_text = "买家修改退款申请,修改金额从".$money['amount']."改为：".$aData['amount'].'元！';
            $result = "SUCCESS";
            $image_file = $aData['image_file'].','.$aData['image_file1'].','.$aData['image_file2'];

            $returnLog = app::get('aftersales')->model("return_log");
            $sdf_return_log = array(
                'order_id' => $_POST['order_id'],
                'return_id' => $_POST['return_id'],
                'op_id' => $money['member_id'],
                'op_name' => (!$money['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
                'alttime' => time(),
                'behavior' => $behavior,
                'result' => $result,
                'role' => 'member',
                'log_text' => $log_text,
                'image_file' => $image_file,
            );

            $log_id = $returnLog->save($sdf_return_log);

            $objOrderLog = app::get('b2c')->model("order_log");

            $sdf_order_log = array(
                'rel_id' => $_POST['order_id'],
                'op_id' => $money['member_id'],
                'op_name' => (!$money['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'refunds',
                'result' => 'SUCCESS',
                'log_text' => $log_text,
            );
            $log_id = $objOrderLog->save($sdf_order_log);

            $this->end(true, app::get('aftersales')->_('修改成功！'));
        }else{
            $this->end(false, app::get('aftersales')->_('修改失败！'));
        }
    }
}