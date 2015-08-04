<?php
 

class b2c_ctl_site_tools extends b2c_frontpage{

    function __construct($app) {
        parent::__construct($app);

        $this->app = $app;
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'get_goods_spec') );
    }

    function selRegion()
    {
        $arrGet = $this->_request->get_get();
        $path = $arrGet['path'];
        $depth = $arrGet['depth'];

        //header('Content-type: text/html;charset=utf8');
        $local = kernel::single('ectools_regions_select');
        $ret = $local->get_area_select(app::get('ectools'),$path,array('depth'=>$depth));
        if($ret){
            echo '&nbsp;-&nbsp;'.$ret;exit;
        }else{
            echo '';exit;
        }
    }

    function history(){
        $this->path[] = array('title'=>app::get('b2c')->_('历史记录'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_tools', 'act'=>'history','full'=>1)));
        $GLOBALS['runtime']['path'] = $this->path;
        $this->title= app::get('b2c')->_('浏览过的商品');
        $this->page('site/tools/history.html');
    }


    function products(){
        $objGoods  = &$this->app->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $filter = array();
        foreach(explode(',',$_POST['goods']) as $gid){
            $filter['goods_id'][] = $gid;
         }

        $aProduct = $objGoods->getList('*,find_in_set(goods_id,"'.utils::addslashes_array($_POST['goods']).'") as rank',$filter,0,-1,array('rank','asc'));
        $aData = $this->get_current_member();
        if(!$aData['member_id']){
            $this->pagedata['login'] = 'nologin';
        }
        $view = $this->app->getConf('gallery.default_view');
        if($view=='index') $view='list';

        if(is_array($aProduct) && count($aProduct) > 0){
            $objProduct = $this->app->model('products');
            if($this->app->getConf('site.show_mark_price')=='true'){
                $setting['mktprice'] = $this->app->getConf('site.show_mark_price');
                if(isset($aProduct)){
                    foreach($aProduct as $pk=>$pv){
                        if(empty($aProduct[$pk]['mktprice']))
                        $aProduct[$pk]['mktprice'] = $objProduct->getRealMkt($pv['price']);
                    }
                }
            }else{
                $setting['mktprice'] = 0;
            }
            $setting['saveprice'] = $this->app->getConf('site.save_price');
            $setting['buytarget'] = $this->app->getConf('site.buy.target');
            $this->pagedata['setting'] = $setting;
            //spec_desc
            $siteMember = $this->get_current_member();
            $this->site_member_lv_id = $siteMember['member_lv'];
            $oGoodsLv = &$this->app->model('goods_lv_price');
            $oMlv = &$this->app->model('member_lv');
            $mlv = $oMlv->db_dump( $this->site_member_lv_id,'dis_count' );

            foreach ($aProduct as $key=>&$val) {
                $temp = $objProduct->getList('product_id, spec_info, price, freez, store,   marketable, goods_id',array('goods_id'=>$val['goods_id'],'marketable'=>'true'));
                $aProduct[$key]['spec_desc'] = unserialize($val['spec_desc']);
                if( $this->site_member_lv_id ){
                    $tmpGoods = array();
                    foreach( $oGoodsLv->getList( 'product_id,price',array('goods_id'=>$val['goods_id'],'level_id'=>$this->site_member_lv_id ) ) as $k => $v ){
                        $tmpGoods[$v['product_id']] = $v['price'];
                    }
                    foreach( $temp as &$tv ){
                        $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$tv['price'] ));
                    }
                    $val['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$val['price'] ));
                }
                $promotion_price = kernel::single('b2c_goods_promotion_price')->process($val);
                if($promotion_price){
                    if($promotion_price['price']) {
                        $val['timebuyprice'] = $promotion_price['price'];
                    }
                    else {
                        $val['timebuyprice'] = $val['price'];
                    }
                    $val['show_button'] = $promotion_price['show_button'];
                    $val['timebuy_over'] = $promotion_price['timebuy_over'];
                }
                $val['spec_desc_info'] = $temp;
                $aProduct[$key]['product_id'] = $temp[0]['product_id'];
                if(empty($val['image_default_id']))
                $aProduct[$key]['image_default_id'] = $imageDefault['S']['default_image'];

            }
            $this->pagedata['products'] = &$aProduct;
        }

        $this->page('site/gallery/type/'.$view.'.html',true);
    }
    
    public function count_digist()
    {
        if ($_POST['data'])
        {
            header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
            header('Progma: no-cache');
        
            $arr_data = json_decode($_POST['data'], 1);
            $obj_math = kernel::single('ectools_math');
            try{
                echo $obj_math->$_POST['_method']($arr_data);exit;
            }catch(Exception $e)
            {
                echo $e->message();exit;
            }
        }
    }
	
	public function send_orders()
	{
		if (!$_POST['order_id']){
			echo '{failed:"'.app::get('b2c')->_('发送订单号不存在！').'",msg:"'.app::get('b2c')->_('发送订单号不存在！').'"}';exit;
		}
		
		$order_id = $_POST['order_id'];
		$objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf = $objOrder->dump($order_id, '*', $subsdf);
		
		/** 开始发送 **/
		if (!$sdf){
			echo '{failed:"'.app::get('b2c')->_('发送订单不存在！').'",msg:"'.app::get('b2c')->_('发送订单不存在！').'"}';exit;
		}
		
		$queue = kernel::single('b2c_queue');
		$cursor_id = 1;
		$queue->send_orders($cursor_id,$sdf);
		
		echo '{success:"'.app::get('b2c')->_('成功！').'",msg:"'.app::get('b2c')->_('成功！').'"}';exit;
	}
	
	public function send_payments()
	{
		if (!$_POST['payment_id']){
			echo '{failed:"'.app::get('b2c')->_('发送支付号不存在！').'",msg:"'.app::get('b2c')->_('发送支付号不存在！').'"}';exit;
		}
		
		$app_ectools = app::get('ectools');
        $oPayment = $app_ectools->model('payments');
        $subsdf = array('orders'=>array('*'));
        $sdf_payment = $oPayment->dump($_POST['payment_id'], '*', $subsdf);
		
		/** 开始发送 **/
		if (!$sdf_payment){
			echo '{failed:"'.app::get('b2c')->_('发送支付单不存在！').'",msg:"'.app::get('b2c')->_('发送支付单不存在！').'"}';exit;
		}
		
		$queue = kernel::single('b2c_queue');
		$cursor_id = 1;
		$queue->send_payments($cursor_id,$sdf_payment);
		
		echo '{success:"'.app::get('b2c')->_('成功！').'",msg:"'.app::get('b2c')->_('成功！').'"}';exit;
	}
}
