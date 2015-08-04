<?php

 /**
 * 购买了还购买量前台挂件请求显示的控制器类
 */
class recommended_ctl_site_show extends site_controller {
	/**
	* 构造方法
	* @param object $app 当前APP实例
	*/
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
	/**
	* 前台显示
	*/
    public function index() {
        if ( strpos( $_POST['url'], '/product-' ) ) {
		    $url = explode( '/product-', $_POST['url'] );
			$productIdUrl = explode( '.html', $url['1'] );
            $productId = $productIdUrl[0];
		}
		elseif ( strpos( $_POST['url'], '/cart' ) ) {
			if ( $_COOKIE["S"]["CART_COUNT"] ) {
		        $cart = app::get('b2c')->model('cart_objects');
                $mdl_cart = app::get('b2c')->model('cart');
                $member = kernel::single( 'b2c_frontpage' )->get_current_member();
                if(count($member) > 0){
                    $objIdent = $cart->getList( 'obj_ident', null, 0, 1, 'time DESC' );                     
                }else{
                    $getobj = $mdl_cart->get_objects('');
                    $objIdent = $getobj['object']['goods'];
                }
			    $identArr = explode( '_', $objIdent[0]["obj_ident"] );
				$productId = $identArr[1];
			}
		}
        $setting['num'] = $_POST['num'];
		$setting['pic_h'] = $_POST['pic_h'];
		$setting['pic_w'] = $_POST['pic_w'];
		$setting['price'] = $_POST['price'];
		$setting['maxlength'] = $_POST['maxlength'];

		if ( $productId ) {
			$filter  = array( 'primary_goods_id'=>$productId );
			$orderby = array( 'last_modified', 'DESC' );
			$limit   = $setting['num'];
			$cols    = 'secondary_goods_id';

			// get other goods id from goods_period
			$goods = app::get( 'recommended' )->model( 'goods_period' );
			$re = $goods->getList( $cols, $filter, 0, -1, $orderby );
			$goods_arr = array();
			$temp = array();
			foreach ( $re as $k=>$v ){
				$goods_arr[] = $v['secondary_goods_id'];
			}
			$arr_ids = array_unique($goods_arr);
			$arr_ids = array_values($arr_ids);

			if ( count( $arr_ids ) > $limit ) {
				$arr_ids = array_slice( $arr_ids, 0, $limit );
			}
            $imageDefault = app::get('image')->getConf('image.set');
			$goods_filter = implode(',',$arr_ids);
			$goods_cols    = 'goods_id, name, price, image_default_id,thumbnail_pic,udfimg';
			$recommend_goods = app::get( 'b2c' )->model( 'goods' );
			$result['goods'] = $recommend_goods->db->select( "SELECT ".$goods_cols." FROM ".$recommend_goods->table_name(1)." WHERE goods_id IN (".$goods_filter.") ORDER BY find_in_set(goods_id,'".$goods_filter."')" );
			$result['defaultImage'] = $imageDefault['S']['default_image'];
			$result['setting'] = $setting;

            if ( count($result['goods']) ) {
                $this->pagedata['data'] = $result;
                $tpl = "site/" . $_POST['tpl'] . ".html";
                $this->page( $tpl, true );
            }

		}// End of if
    }// End of index
}