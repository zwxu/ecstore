<?php
 

class b2c_ctl_site_brand extends b2c_frontpage{

    var $seoTag=array('shopname','brand');

    function __construct($app){
        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        $this->shopname = $shopname;
        $this->set_tmpl('brandlist');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('品牌页').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('品牌页').'_'.$shopname;
            $this->description = app::get('b2c')->_('品牌页').'_'.$shopname;
        }

    }

    public function showList($page=1){

        $pageLimit = 24;
        $oGoods=&$this->app->model('brand');
        $result=$oGoods->getList('*', '',($page-1)*$pageLimit,$pageLimit,'ordernum desc');
        $brandCount = $oGoods->count();

        $oSearch = &$this->app->model('search');
        
        $this->path[] = array('title'=>app::get('b2c')->_('品牌专区'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_brand', 'act'=>'showlist','full'=>1)));
        $GLOBALS['runtime']['path'] = $this->path;
        
        $title=$title['title']?$title['title']:app::get('b2c')->_('品牌');
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($brandCount/$pageLimit),
            'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_brand', 'act'=>'showList','full'=>1,'args'=>array(($tmp = time())))),
            'token'=>$tmp
            );

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['data'] = $result;
        $this->setSeo('site_brand','showList',$this->prepareListSeoData($this->pagedata));
        $this->page('site/brand/showList.html');
    }

    public function index($brand_id, $page=1,$orderBy=1,$view='') {

        $oGoods=&$this->app->model('brand');
        $this->path[] = array('title'=>app::get('b2c')->_('品牌专区'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_brand', 'act'=>'showlist','full'=>1)));
        $argu=array("brand_id","brand_name","brand_url","brand_desc","brand_logo","brand_setting");
        $argu=implode(",",$argu);
        $result = $oGoods->getList($argu,array('brand_id'=>$brand_id));
        $result = $result[0];

        $this->set_tmpl('brand');
        if( $result['brand_setting']['brand_template'] )
            $this->set_tmpl_file($result['brand_setting']['brand_template']);
        $this->pagedata['data'] = $result;

        if(empty($view))
            $view = $this->app->getConf('gallery.default_view')?$this->app->getConf('gallery.default_view'):'list';
        if($view == 'index') $view = 'list';
        $views = array(
                    app::get('b2c')->_('列表')=>'list',
                    app::get('b2c')->_('大图')=>'grid',
                    app::get('b2c')->_('文字')=>'text',
                );
        foreach($views as $key=>$val){
            $this->pagedata['views'][$key] = array($brand_id,$page,$orderBy,$val);
        }

        $this->pagedata['curView'] = $view;
        $this->pagedata['brandview'] = '/site/gallery/type/'.$view.'.html';
        $this->path[] = array('title'=>$result['brand_name'],'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $objGoods  = &$this->app->model('goods');
        $filter = array();
        if($mlv = $_COOKIE['MLV']){
            $filter['mlevel'] = $mlv;
        }
        if(!$this->check_login()){
            $this->pagedata['login'] = 'nologin';
        }
        $filter['brand_id'] = $brand_id;
        $filter['marketable'] = 'true';

        $pageLimit = 20;
        $start = ($page-1)*$pageLimit;
        $this->pagedata['args'] = array($brand_id,$page,$orderBy,$view);
        $this->pagedata['orderBy'] = $objGoods->orderBy();
        if(!isset($this->pagedata['orderBy'][$orderBy])){
            $this->_response->set_http_response_code(404);
        }else{
            $orderby = $this->pagedata['orderBy'][$orderBy]['sql'];
        }

        $aProduct  = $objGoods->getList('*',$filter,$start,$pageLimit,$orderby);
        $count = count($aProduct);
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

            foreach ($aProduct as &$val) {
                $temp = $objProduct->getList('product_id, spec_info, price, freez, store, goods_id',array('goods_id'=>$val['goods_id']));
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
                if(!empty($promotion_price['price'])){
                    $val['price'] = $promotion_price['price'];
                    $val['show_button'] = $promotion_price['show_button'];
                    $val['timebuy_over'] = $promotion_price['timebuy_over'];
                }
                $val['spec_desc_info'] = $temp;
            }
            $this->pagedata['products'] = &$aProduct;
        }

        $productCount = $objGoods->count($filter);
        $this->pagedata['count'] = $productCount;

        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($productCount/$pageLimit),
            'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_brand','full'=>1,'act'=>'index','args'=>array($brand_id,$tmp=time()))),
            'token'=>$tmp);


        if(is_array($aProduct) && count($aProduct) > 0){
            $setting['mktprice'] = $this->app->getConf('site.market_price');
            $setting['saveprice'] = $this->app->getConf('site.save_price');
            $setting['buytarget'] = $this->app->getConf('site.buy.target');
            $this->pagedata['setting'] = $setting;
            $this->pagedata['products'] = $aProduct;
        }

        $imageDefault = app::get('image')->getConf('image.set');

        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['searchtotal'] = $count;
        $oSearch = &$this->app->model('search');
        $this->pagedata['link'] = $this->gen_url('gallery',$this->app->getConf('gallery.default_view'),array('',$oSearch->encode(array('brand_id'=>array($brand_id)))));
        $seo_info = @$oGoods->dump($brand_id,'seo_info');
        if(!isset($seo_info['seo_info'])){
            $oGoods->brand_meta_register();
        }
        if(!empty($seo_info['seo_info']['seo_title']) || !empty($seo_info['seo_info']['seo_keywords']) || !empty($seo_info['seo_info']['seo_description'])){
            $this->title = $seo_info['seo_info']['seo_title'];
            $this->keywords = $seo_info['seo_info']['seo_keywords'];
            $this->description = $seo_info['seo_info']['seo_description'];
        }else{
            $this->setSeo('site_brand','index',$this->prepareSeoData($this->pagedata));
        }
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        $this->pagedata['gallery_display'] = $this->app->getConf('gallery.display.grid.colnum');
        if($count < $this->pagedata['gallery_display']){
            $this->pagedata['gwidth'] = $count * (100/$this->pagedata['gallery_display']);
        }else{
            $this->pagedata['gwidth'] = 100;
        }
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'get_goods_spec') );
        $this->page('site/brand/index.html');

    }

    function prepareSeoData($data){
        $intro = $this->get_brand_intro($data);
        return array(
            'shop_name'=>$this->shopname,
            'brand_name'=>$data['data']['brand_name'],
            'brand_url'=>$data['data']['brand_url'],
            'brand_intro'=>$intro,
            'goods_amount'=>$data['count']
        );
    }

    function prepareListSeoData($data){
    	if(is_array($data['data'])){
    	    foreach($data['data'] as $dk=>$dv){
    	    	if($dk == 0){
                    $brand_name = $dv['brand_name'];
    	    	}else{
    	    	    $brand_name .= ','.$dv['brand_name'];
    	    	}
    	    }
    	}
        return array(
            'shop_name'=>$this->shopname,
            'brand_name'=>$brand_name,
        );
    }

    private function get_brand(&$result,$list=0){
        if($list){
            foreach($result['data'] as $k => $v)
                $brandName[]=$v['brand_name'];
            return implode(",",$brandName);
        }else{
            return $result['data']['brand_name'];
        }
    }

    private function get_goods_amount(&$result,$list=0){
        return $result['count'];
    }

    private function get_brand_intro(&$result,$list=0){
        $brand_desc=preg_split('/(<[^<>]+>)/',$result['data']['brand_desc'],-1);
        if(is_string($brand_desc)){
            if ( $brand_desc && strlen($brand_desc)>50)
                $brand_desc=substr($brand_desc,0,50);
        }
        return $brand_desc;
    }

    private function get_brand_kw(&$result,$list=0){
        $brand = $this->app->model('goods/brand');
        $row=$brand->instance($result['data']['brand_id'],'brand_keywords');
        return $row['brand_keywords'];
    }
}
