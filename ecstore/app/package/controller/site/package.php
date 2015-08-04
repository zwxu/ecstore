<?php
class package_ctl_site_package extends package_frontpage{
    function index(){
        //$this->_response->set_header('Cache-Control', 'no-store');
        $_getParams = $this->_request->get_params();
        $id = $_getParams[0];
        if(!$id){
            $this->_response->clean_all_headers()->set_http_response_code('404')->send_headers();
            exit('捆绑参数错误！');
            exit;
        }

        $objAttend = $this->app->model('attendactivity');
        $objActivity = $this->app->model('activity');
        $sell_log = $this->app->model('sell_log');
        
        $arr = $objAttend->dump($id);
        $this->path = array();
        $this->path[] = array('title'=>$arr['name'],'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        
        if(!$arr || $arr === false){
            $this->_response->clean_all_headers()->set_http_response_code('404')->send_headers();
            $this->splash('failed','back' , app::get('b2c')->_('无效捆绑！'));
            //echo '无效捆绑！';
            exit;
        }
        if($arr['aid']){
            $act_info = $objActivity->getList('*',array('act_id'=>$arr['aid']));
            $act_info = $act_info[0];
        }
        if(!$act_info || $act_info === false){
            $this->_response->clean_all_headers()->set_http_response_code('404')->send_headers();
            //echo '无效活动！';
            $this->splash('failed','back' , app::get('b2c')->_('无效活动！'));
            exit;
        }
        //当前时间
        $time = time();
        if( ($act_info['start_time'] && $act_info['start_time']>$time) || ($act_info['end_time'] && $act_info['end_time']<=$time) ) {
            $this->_response->clean_all_headers()->set_http_response_code('404')->send_headers();
            $this->pagedata['outtime'] = 1;
            //echo '该捆绑商品不在活动时间范围之内！';
            //$this->splash('failed','back' , app::get('b2c')->_('该捆绑商品不在活动时间范围之内！'));
            //exit;
        }

        $objGoods = kernel::single("package_site_goods");
        $goods_info = $objGoods->get_goods_info(array_filter(explode(',',$arr['gid'])));
        $objGoods = kernel::single('b2c_goods_model'); 
        foreach($goods_info as $k=>&$v){
            if(is_array($v['spec']) && !empty($v['spec'])){
                foreach($v['spec'] as $spec_k=>&$spec_v){
                    $aGoods = $objGoods->getGoods($v['goods_id']);
                    $spec_v['product2spec']=$aGoods['product2spec'];
                    $spec_v['spec2product']=$aGoods['spec2product'];
                }
            }
        }
       
        $quantity = $sell_log->db->selectrow('SELECT count(quantity) as q from sdb_package_sell_log where giftpackage_id='.$id);
        $this->pagedata['quantity'] = $quantity['q'];
        $this->pagedata['store'] = $arr['store'];
        $this->pagedata['goods'] = $goods_info;
        $this->pagedata['package'] = $arr;


        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_default_id'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = app::get('b2c')->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;

        //检查买家是否是店家
        $checkSeller = kernel::service('business_check_goods_isMy');
        if($checkSeller){
            if($checkSeller->check_isSeller($msg)){
                $this->pagedata['isSeller'] = 'true';
            }else{
                $this->pagedata['isSeller'] = 'false';
            }
        }

        $this->set_tmpl('package');
        $this->page('site/index/index.html');
    }
    
    public function add_to_cart() {
        $this->app = app::get('b2c');
        $siteMember = $this->get_current_member();
        $this->app = app::get('package');
        $sto= kernel::single("business_memberstore",$siteMember['member_id']);
        if($sto->storeinfo){
            $status = false;
            $msg = '只能会员购买！';
        }
        $arr = $this->get_data();
        if( !$arr['products'] && !$arr['id'] ) { //登陆成功后跳转
            $status = false;
            $msg = '参数错误！';
        } elseif(($return=kernel::single('package_cart_object_package')->add_object( array('package'=>$arr) ))===true) {
            unset($return);
            $status = true;
            $msg = app::get('package')->_('加入购物车成功！');
            if( $arr['checkout'] ) {
                $url = array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'checkout');
            } else {
                $url = array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'index');
            }
        } else {
            $status = false;
            $msg = $return ? $return : app::get('package')->_('参数错误！');
            $url = array('app'=>'package', 'ctl'=>'site_package', 'act'=>'index','arg0'=>$arr['id']);
        }
        if( !$status ) { //加入购物车失败
            if($_POST['mini_cart']){
                echo json_encode( array('error'=>$msg) );exit;
            }
        } else {
            if($_POST['mini_cart']){
                $arr = app::get('b2c')->model("cart")->get_objects();
                $temp = $arr['_cookie'];
                $this->pagedata['cartCount']      = $temp['CART_COUNT'];
                $this->pagedata['cartNumber']     = $temp['CART_NUMBER'];
                $this->pagedata['cartTotalPrice'] = $temp['CART_TOTAL_PRICE'];
                $this->page('site/cart/mini_cart.html', true,'b2c');return;
            }
        }
        $this->begin( $url );
        $this->end($status, $msg);
    }
    
    private function get_data() {
        $arr = $this->_request->get_params(true);
        $return['num'] = 1;
        $return['products'] = $arr['goods'];
        $return['id'] = $arr['id'];
        $return['checkout'] = $arr['checkout'];
        
        $this->o_b2c_products = app::get('b2c')->model('products');
        foreach( (array)$return['products'] as $key => $row ) {
            if( !$row['product_id'] || $row['product_id']=='null' ) {
                $arr_product_info = $this->o_b2c_products->getList( 'product_id',array('goods_id'=>$row['goods_id']) );
                if( !$arr_product_info || !is_array($arr_product_info) || count($arr_product_info)>1 ) return false;
                reset( $arr_product_info );
                $arr_product_info = current( $arr_product_info );
                $return['products'][$key]['product_id'] = $arr_product_info['product_id'];
            }
        }

        return $return;
    }
    
    public function remove_cart_to_disabled() {
        kernel::single('base_session')->start();
        $_obj_type  = $this->_request->get_param(0);
        $_obj_ident  = $this->_request->get_param(1);
        $_product_id = (int)$this->_request->get_param(2);
        $_SESSION['cart_objects_disabled_item'][$_obj_type][$_obj_ident][$_product_id] = 'true';
        $this->_response->set_http_response_code(404);return;
    }

    public function gallery($orderBy=1,$page=1){
        $page = ($page > 1) ? intval($page) : 1;
        $pageLimit = app::get('b2c')->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);
      
        $orderByArr = $this->orderBy(intval($orderBy));		
        $orderby = $orderByArr['sql'];

        $obj_activity = $this->app->model('activity');
        $obj_attend = $this->app->model('attendactivity');

        $aActivity = $obj_activity->getList('act_id', array('start_time|sthan'=>time(),'end_time|than'=>time(),'act_open'=>'true'), 0, -1);
        $packageInfo = array();
        $count = 0;
        if($aActivity){
            $sfilter['status'] = '2';
            foreach((array)$aActivity as $item){
                $sfilter['aid'][] = $item['act_id'];
            }
            $packageInfo = $obj_attend->getList('*',$sfilter,$pageLimit*($page-1),$pageLimit,$orderby);
            $count = $obj_attend->count($sfilter);
        }

        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($count/$pageLimit),
            'link'=> $this->gen_url(array('app'=>'package', 'ctl'=>'site_package','act'=>'gallery','args'=>array($orderBy,$tmp=time()))),
            'token'=>$tmp);
        $this->pagedata['orderBy'] = $orderBy;
        $imageDefault = app::get('image')->getConf('image.set');
        foreach($packageInfo as $k=>$v){
            if(empty($v['image'])){
                $image_id = $imageDefault['M']['default_image'];
            }else{
                $image_id = $v['image'];
            }
            $pic = base_storager::image_path($image_id,'m');
            $packageInfo[$k]['pic'] = $pic;
            $packageInfo[$k]['url'] = $this->gen_url(array('app'=>'package', 'ctl'=>'site_package','act'=>'index','args'=>array($v['id'])));
            $packageInfo[$k]['pic_width'] = $imageDefault['M']['width'];
            $packageInfo[$k]['pic_height'] = $imageDefault['M']['height'];
        }
        $this->pagedata['pageNums'] = $page;
        $this->pagedata['list'] = $packageInfo;
        $this->set_tmpl('package');
        $this->page('site/index/gallery_list.html');
    }

    public function orderBy($id){
        $order = array(
            1 => array('label' => app::get('package')->_('默认')),
            2 => array('label' => app::get('package')->_('价格 从高到低'), 'sql' => 'amount desc'),
            3 => array('label' => app::get('package')->_('价格 从低到高'), 'sql' => 'amount'),
            4 => array('label' => app::get('package')->_('发布时间 新->旧'),'sql'=> 'last_midifity desc'),
            5 => array('label' => app::get('package')->_('发布时间 旧->新'),'sql'=> 'last_midifity'),
        );
        if($id){
            return $order[$id];
        }else{
            return $order;
        }
    }
}
