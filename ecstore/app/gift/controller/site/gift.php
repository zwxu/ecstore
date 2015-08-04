<?php


/**
 * ctl_cart
 *
 * @uses b2c_frontpage
 * @package
 * @version $Id: ctl.cart.php 1952 2008-04-25 10:16:07Z flaboy $
 * @author <kxgsy163@163.com>
 * @license Commercial
 */

class gift_ctl_site_gift extends gift_frontpage{


    function __construct($app){
        parent::__construct($app);
        $shopname = app::get('b2c')->getConf('system.shopname');
        $this->shopname = $shopname;
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('商品页_').$shopname;
            $this->keywords = app::get('b2c')->_('商品页_').$shopname;
            $this->description = app::get('b2c')->_('商品页_').$shopname;
        }
    }

    public function index() {
        #$this->_response->set_header('Cache-Control', 'no-store');
        $_getParams = $this->_request->get_params();
        $gid = $_getParams[0];

        $aGoods = $this->app->model('goods')->dump($gid);


        // 赠品是否有效
        if( !$aGoods || !$aGoods['gift'] ) {
            $this->begin(  );
            $this->end(false,'无效赠品！');
        }
        $gift = $aGoods['gift'];
        $arr_cat = $this->app->model('cat')->dump( $gift['cat_id'] );
        if($arr_cat['ifpub'] == 'false'){
            $gift['marketable'] = true;
        }
        $gift['cat']['cat_name'] = $arr_cat['cat_name'];
        if($gift['to_time'] < time()) {
            $gift['is_expire'] = 'true';
        }
        else {
            $gift['is_expire'] = 'false';
        }
        $this->pagedata['gift'] = $gift;
        #print_r($aGoods);exit;
        $this->get_goods_page_data( $aGoods['goods_id'],$aGoods['goods_type'],$aGoods );

        if( isset( $aGoods['gift']['member_lv_ids'] ) ) {
            $tmp_member_lv_info = $this->app->model('member_lv')->getList('*', array('member_lv_id'=>
                                                                                        ( is_array( $aGoods['gift']['member_lv_ids'] ) ? $aGoods['gift']['member_lv_ids'] : explode(',', $gift['member_lv_ids']) ),
                                                                                    ),
                                                                         0, -1, 'member_lv_id ASC');
            $this->pagedata['member_lv'] = $tmp_member_lv_info;
        }

        // 判断是否兑换赠品
        $app_b2c = app::get('b2c');
        $site_get_policy_method = $app_b2c->getConf('site.get_policy.method');
        $site_point_usage = $app_b2c->getConf('site.point_usage');
        //$this->pagedata['site_point_usage'] = ($site_get_policy_method != '1' && $site_point_usage == '1') ? 'true' : 'false';
        $this->pagedata['site_point_usage'] = $site_point_usage;
        $this->pagedata['site_get_policy_method'] = $site_get_policy_method;
        $aPath = array(
            array('link'=>$this->gen_url( array('app'=>'gift','act'=>'lists','ctl'=>'site_gift') ),'title'=>'赠品列表页'),
            array('link'=>'true','title'=>$aGoods['name']),
        );
        $GLOBALS['runtime']['path'] = $aPath;
        $this->setSeo('site_gift','index',$this->prepareSeoData($this->pagedata));
        $aGoods['setting']['buytarget'] = app::get('b2c')->getConf('site.buy.target');
        $this->pagedata['goods'] = $aGoods;
        $this->set_tmpl('gift');
        $this->page('site/product/index.html');
    }

    function prepareSeoData($data){
        return array(
            'shop_name'=>$this->shopname,
            'goods_name'=>$data['goods']['name'],
        );
    }

    /*
     * 商品信息  b2c time:2010-12-14 15:03
     * 规格等适用b2c公用
     */
    private function get_goods_page_data( $gid,$type,&$aGoods )
    {
        if( $type=='gift' ) {
            $arrGoods = $this->app->model('goods')->dump( $gid );
            $this->pagedata['product0id'] = $arrGoods['gift']['product_id'];
        }

        $this->pagedata['goodshtml']['store'] = kernel::single("b2c_goods_detail_store")->show( $gid,$arrGoods );

        if( $aGoods && is_array($aGoods['product']) ) {
            $max_store = kernel::single("gift_cart_object_gift")->get_max_store();
            $arrGoods['store'] = 0;
            foreach( $aGoods['product'] as $key => $row ) {
                if( !isset($row['store']) ) $row['store'] = $max_store;
                if( !isset($row['gift']['max_limit']) ) $row['gift']['max_limit'] = $row['store'];
                if( $row['gift'] && is_array($row['gift']) ) {
                    $p = floatval($row['gift']['max_limit']-$row['gift']['real_limit']);
                    if( $p>$row['store'] )
                        $arrGoods['store'] += $row['store'];
                    else
                        $arrGoods['store'] += $p;
                }
            }

            $arrGoods['store'] = $arrGoods['store']<=0 ? ($arrGoods['store']==0 ? 0 : $max_store) : $arrGoods['store'];

            // 详细页控制js 键值_real_store
            if( $arrGoods['store']>=$aGoods['gift']['max_buy_store'] ) $arrGoods['_real_store'] = $aGoods['gift']['max_buy_store'];
            else $arrGoods['_real_store'] = $arrGoods['store'];

            $aGoods['store'] = $arrGoods['store'];
        }


        $this->pagedata['goodshtml']['store'] = kernel::single("b2c_goods_detail_store")->show( $gid,$arrGoods );

        kernel::single('gift_site_goodsspec')->trim_spec( $arrGoods );

        if( !$arrGoods['image_default_id'] ) {
            $imageDefault = app::get('image')->getConf('image.set');
            $arrGoods['image_default_id'] = $imageDefault['M']['default_image'];
        }
        $this->pagedata['goodshtml']['goodspic'] = kernel::single("b2c_goods_detail_pic")->show( $gid,$arrGoods );

        $this->pagedata['goodshtml']['spec'] = kernel::single("b2c_goods_detail_spec")->show( $gid,$arrGoods );

        $this->pagedata['goods'] = $arrGoods;
    }
    #End Func


    public function lists() {
        #$this->_response->set_header('Cache-Control', 'no-store');
        $this->begin('index.html');
        $art_list_id = $this->_request->get_param(0);
        //$art_list_id = intval($art_list_id);
        $filter = array('marketable'=>'true');


        //每页条数
        $pageLimit = $this->app->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 10);

        //当前页
        $page = (int)$this->_request->get_param(1);

        if (!empty($art_list_id))
            $filter['cat_id'] = intval($art_list_id);


        $page or $page=1;

        $filter['goods_type'] = array('gift','normal'); //指定 类型
        $filter['to_time|than'] = time();

        $this->app->model('goods')->unuse_filter_default( false );
        $o = $this->app->model('goods');        //商品类实例

        $o_gift_ref = $this->app->model('ref');

        //当前页数据
        $arr_gift_list = $o_gift_ref->get_list_finder('*', $filter, $pageLimit*($page-1),$pageLimit,'`order` asc');
        $cat_arr = $this->app->model('cat')->getList('cat_id',array('ifpub'=>'false'));
        $cat_ids = array();
        foreach($cat_arr as $value)
        {
            $cat_ids[]=$value['cat_id'];
        }
        //总数
        $this->pagedata['searchtotal'] = $count = $this->app->model('ref')->count_finder($filter);

        if( is_array($arr_gift_list) ) {
            foreach( $arr_gift_list as $key => &$row ) {
                if(in_array($row['cat_id'],$cat_ids)){
                    unset($arr_gift_list[$key]);
                    continue;
                }
                $arr = $o->getList( '*',array('goods_id'=>$row['goods_id'],'goods_type'=>array('normal','gift')) );
                if( is_array($arr) ) {
                    reset( $arr );
                    $adsf = current( $arr );
                    $tmp = $row;
                    $row = $adsf;
                    $row['gift'] = $tmp;

                    if( isset($row['spec_desc']) && $row['spec_desc'] ) {
                        $spec_desc = current($row['spec_desc']);
                        if( $spec_desc && (count($row['spec_desc'])>1 || count($spec_desc)>1) )
                            $row['hasspec'] = 'true';
                    }
                } else {
                    unset($arr_gift_list[$key]);
                }
            }
        }
        #print_r($arr_gift_list);exit;
        //标识用于生成url
        $token = md5("page{$page}");
        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>ceil($count/$pageLimit),
                'link'=>$this->gen_url(array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'lists',  'args'=>array($art_list_id,($token=time())))),
                'token'=>$token
            );
        $this->pagedata['open'] = app::get('b2c')->getconf('site.get_policy.method');
        $this->pagedata['gift_list'] = $arr_gift_list;

        $this->pagedata['products'] = $arr_gift_list;
        $this->pagedata['is_gift'] = 'true';

        //规格商品发送请求地址
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'gift','ctl'=>'site_gift','act'=>'get_goods_spec') );

        $app = app::get('b2c');
        $setting['saveprice'] = $app->getConf('site.save_price');
        $setting['buytarget'] = $app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['M']['default_image'];
        $this->pagedata['site_point_usage'] = $app->getConf('site.point_usage');
        $aPath = array(array('link'=>'true','title'=>'赠品列表页'));
        $GLOBALS['runtime']['path'] = $aPath;
        $this->setSeo('site_gift','lists',array('shop_name'=>$this->shopname));

        $this->set_tmpl('gift');
        $this->page('site/gallery/index.html');
    }


    public function get_goods_spec() {
        #$this->_response->set_header('Cache-Control', 'no-store');
        $gid = $this->_request->get_get('gid');
        if( !$gid ) {
            echo '';
            exit;
        }
        $this->pagedata['goodshtml']['name'] = kernel::single("b2c_goods_detail_name")->show( $gid,$arrGoods );

        //规格处理 非赠品
        kernel::single('gift_site_goodsspec')->trim_spec( $arrGoods );

        if( $arrGoods['spec'] && is_array($arrGoods['spec']) )  {
            foreach( $arrGoods['spec'] as $row ) {
                $option = $row['option'];
                if( $option && is_array($option) ) {
                    foreach( $option as $img ) {
                        foreach( (array)explode(',',$img['spec_goods_images']) as $imageid )
                            $return[$imageid] = base_storager::image_path($imageid,'s');
                    }
                }
            }
        }
        $this->pagedata['spec2image'] = json_encode( $return );

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        $setting['buytarget'] = app::get('b2c')->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $arrGoods['spec2image'] = json_encode($return);
        $this->pagedata['goods'] = $arrGoods;
        $this->pagedata['goodshtml']['spec'] = kernel::single("b2c_goods_detail_spec")->show( $gid,$arrGoods );
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_default_id'] = $imageDefault['S']['default_image'];
        $this->pagedata['form_url'] = $this->gen_url( array('app'=>'b2c','act'=>'add','ctl'=>'site_cart','arg0'=>'gift') );
        $this->pagedata['goodshtml']['button'] = kernel::single('gift_goods_detail_button')->show( $gid,$arrGoods );
        $this->page( 'site/gallery/spec_dialog.html',true,'b2c' );
    }



    public function viewpic( $goodsid, $selected='def' ){
        $objGoods = &$this->app->model('goods');
        $o = &app::get('image')->model('image_attach');
        $dImg = $o->getList('*',array('target_id'=>$goodsid));
        $aGoods = $objGoods->dump_b2c( array('goods_id'=>$goodsid),'name' );

        $this->pagedata['goods_name'] = urlencode(htmlspecialchars($aGoods['name'],ENT_QUOTES));
        $this->pagedata['goods_name_show'] = $aGoods['name'];
        $this->pagedata['company_name'] = str_replace("'","&apos;",htmlspecialchars($this->app->getConf('system.shopname')));
        if(!$dImg){
            $imageDefault = app::get('image')->getConf('image.set');
            $dImg[]['image_id'] = $imageDefault['L']['image_id'];
            /*
            $selected=0;
            $id=rand(0,10);
            $dImg[$id]=array(
                'gimage_id'=>$id,
                'goods_id'=>$goodsid,
                'small'=>($this->app->getConf('site.default_small_pic')),
                'big'=>($this->app->getConf('site.default_big_pic')),
                'thumbnail'=>($this->app->getConf('site.default_thumbnail_pic'))
            );*/
        }
        $this->pagedata['image_file'] = $dImg;
        if($selected=='def'){
            $selected=current($dImg);
            $selected=$selected['target_id'];
        }
        $this->pagedata['selected'] = $selected;
        $this->page('site/product/viewpic.html',true,'b2c');

    }

    public function add_to_cart() {
        $arr = $this->get_data();
        $gift_id = $arr['gift_id'];

        if(($return=kernel::single('gift_cart_object_gift')->add( array('gift'=>$arr) ))!==true) {
            if( !is_array($return) ) {
                if( $_POST['mini_cart'] ) {
                    echo json_encode( array('error'=>'赠品不存在') );exit;
                } else {
                    $this->begin($this->gen_url(array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'lists')));
                    $this->end(false, app::get('gift')->_('赠品不存在！'), '', '', true);return;
                }
            }
        } else {
            if( $_POST['mini_cart'] ) {
                $this->app = app::get('b2c');
                $arr = $this->app->model("cart")->get_objects();
                $temp = $arr['_cookie'];

                $this->pagedata['cartCount']      = $temp['CART_COUNT'];
                $this->pagedata['cartNumber']     = $temp['CART_NUMBER'];
                $this->pagedata['cartTotalPrice'] = $temp['CART_TOTAL_PRICE'];

                $this->page('site/cart/mini_cart.html', true);
                return;
            } else {
                unset($return);
                $return['begin'] = array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'index');
                $return['end']   = array('status'=>true,  'msg'=>app::get('gift')->_('加入购物车成功！'));
            }
        }

        if( $_POST['mini_cart'] && ( $return['end']['status']==false ) ){
            echo json_encode( array('error'=>$return['end']['msg']) );exit;
            #$this->begin($this->gen_url($return['begin']));
            #$this->end($return['end']['status'], $return['end']['msg']);
            #$this->_response->set_http_response_code(404);
        } else {
            $this->begin($this->gen_url($return['begin']));
            $this->end($return['end']['status'], $return['end']['msg'], '', '', true);
        }
    }


    public function remove_cart_to_disabled() {
        kernel::single('base_session')->start();
        $_obj_type  = $this->_request->get_param(0);
        $_obj_ident  = $this->_request->get_param(1);
        $_product_id = (int)$this->_request->get_param(2);
        $_SESSION['cart_objects_disabled_item'][$_obj_type][$_obj_ident][$_product_id] = 'true';
        $this->_response->set_http_response_code(404);return;
    }


    private function get_data() {

        if( $_POST['goods'] ) {
            $arr = $_POST['goods'];

            if( !$arr['product_id'] ) {
                if( $arr['goods_id'] ) {
                    $arr_gift = $this->app->model('ref')->getList( 'product_id',array('goods_id'=>$arr['goods_id']) );
                    if( count($arr_gift)==1 ) {
                        $arr_gift = $arr_gift[0];
                        $arr['gift_id'] = (int)$arr_gift['product_id'];
                    }
                }
            } else {
                $arr['gift_id'] = $arr['product_id'];
            }
            unset( $arr['product_id'] );
        } else {
            $arr['goods_id'] = (int)$this->_request->get_param(0);
            $arr['gift_id'] = (int)$this->_request->get_param(1);
            $arr['num'] = (int)$this->_request->get_param(2);
        }

        if ( empty( $arr['gift_id'] ) || empty( $arr['goods_id'] ) ) return false;
        return $arr;
    }
}
