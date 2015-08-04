<?php

class business_ctl_site_member extends b2c_ctl_site_member
{
    public $verify = true;

    public function __construct(&$app)
    {

        $this->app_current = $app;
        $this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
        $shopname = $this->app_b2c->getConf('system.shopname');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('卖家中心').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('卖家中心_').'_'.$shopname;
            $this->description = app::get('b2c')->_('卖家中心_').'_'.$shopname;
        }
        $this->cur_view = 'member';
        /*
        $storemanger_model = &$this -> app_current -> model('storemanger');
        $data = $storemanger_model -> getList('*', array('account_id' => $this->app_b2c->member_id), 0, -1);
        if (!$data) {
            // 不是店长。
            $objBGoods = &$this->app_current->model('goods');
            $aRegion = $objBGoods->getRegions($this->app_b2c->member_id);
        }else{
            // 是店长。
            foreach($data as $rows){
                $aRegion[$rows['store_id']] = $rows['store_region'];
            }
        }
        if(count($aRegion)>0){
            foreach($aRegion as $key => $value){
                $this->region_id = $value?$value:0;
                $this->store_id = $key?$key:0;
                break;
            }
        }else{
            $this->region_id = 0;
            $this->store_id = 0;
        }*/

        $sto= kernel::single("business_memberstore",$this->app_b2c->member_id);
        $sto->process($this->app_b2c->member_id);
        $data = $sto->storeinfo;

     
        if($data['seller']=='seller' && $this->app_b2c->member_id){
            $this->verify = false;
        }else {
             $this->verify = true;
        }

        $this->store = $data;
        if($sto->isshoper == 'true'){
            $this->region_id = array_keys($data['store_region']);
            $this->store_brand = $data['store_brand'];
            $this->store_id = $data['store_id']?intval($data['store_id']):0;
            $this->issue_type = $data['issue_type']?intval($data['issue_type']):0;

        }elseif($sto->isshopmember == 'true'){
            $this->region_id = array_keys($data[0]['store_region']);
            $this->store_brand = $data[0]['store_brand'];
            $this->store_id = $data[0]['store_id']?intval($data[0]['store_id']):0;
            $this->issue_type = $data[0]['issue_type']?intval($data[0]['issue_type']):0;
        }else{

            if($this->verify){
                if($data['seller']=='seller'){
                    $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_storeapply', 'act' => 'index', 'full' => 1)), app::get('b2c')->_('您还未入驻商城，请先入驻！'));
                }else {
                    $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_storeapply', 'act' => 'index', 'full' => 1)), app::get('b2c')->_('您登录的账号不是企业用户，请先注册企业用户！'));
                }
            }
        }


        //检查店铺状态
        $this->checkroles();

        if(!$this->region_id){
            $this->region_id = array(0);
        }
        if(!is_array($this->store_brand) || !$this->store_brand){
            $this->store_brand = array();
        }
         
        $GLOBALS['runtime']['nocache']=microtime();
        if(!$this->store_id){
            $this->store_id = 0;
        }else {
            $this -> pagedata['store_id'] = $this->store_id;
        }

        if(!$this->issue_type){
            $this->issue_type = 0;
        }/*
        if($this->issue_type == 0){
            foreach($this->app_current->model('goods')->get_subcat_list(0) as $rows){
                $region_ids[] = $rows['cat_id'];
            }
            if($region_ids) $this->region_id = $region_ids;
        }*/
        if($sto->isshoper == 'true'){
            $this->grade_id = $data['store_grade']?$data['store_grade']:0;
        }elseif($sto->isshopmember == 'true'){
            $this->grade_id = $data[0]['store_grade']?$data[0]['store_grade']:0;
        }
        $this->pagedata['current_url'] = app::get('business')->res_url;
    }

    function getPCCat($cat_id){
        $objBGoods = &$this->app_current->model('goods');
        $cat_id = $cat_id?intval($cat_id):0;
        $aCatId = $objBGoods->getCats($this->region_id);
        if(!in_array($cat_id, $aCatId['cat_id'])){
            $cat_id = $this->region_id[0];
        }
        $parent_id = $this->region_id;
        $cat_list = $aCatId['cat_id'];
        return array('cat'=>$cat_id, 'parent'=>$parent_id, 'catlist'=>$cat_list);
    }

    function _editor($cat_id,$type_id){
        $this->pagedata['img_size'] = app::get('b2c')->getConf('site.big_pic_width').'*'.app::get('b2c')->getConf('site.big_pic_height');
        $this->pagedata['store_id'] = $this->store_id;
        $this->pagedata['region_id'] = $this->region_id;
        $memberLevel = &$this->app_b2c->model('member_lv');
        $this->pagedata['mLevels'] = $memberLevel->getList('member_lv_id,dis_count');
        $oTag = &app::get('desktop')->model('tag');
        $this->pagedata['tagList'] = $oTag->getList('*',array('tag_mode'=>'normal','tag_type'=>'goods'),0,-1);
        $oTrel = &app::get('desktop')->model('tag_rel');
        $this->pagedata['tag'] =  $oTrel->getList('tag_id',array('rel_id'=>$this->goods_id));
        $this->pagedata['image_dir'] = &app::get('image')->res_url;
        $this->pagedata['storeplace'] = $this->app_b2c->getConf('storeplace.display.switch');
        $this->pagedata['site_min_order'] = $this->app_b2c->getConf('site.min_order');
        $this->pagedata['spec_default_pic'] = $this->app_b2c->getConf('spec.default.pic');
        $this->pagedata['point_setting'] = $this->app_b2c->getConf('point.get_policy');
        $this->pagedata['goodsbn_display_switch'] = ($this->app_b2c->getConf('goodsbn.display.switch') == 'true');
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_tools','act'=>'alertpages'));
        $urlgoto=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_tools','act'=>'image_broswer'));
        $url='"'.$url.'?dd="+Date.now()+"&goto='.urlencode($urlgoto).'"';
        //$this->pagedata['spec_image_request_url'] = "&quot;index.php?app=desktop&act=alertpages&goto=".urlencode("index.php?app=image&ctl=admin_manage&act=image_broswer")."&quot;";
        $this->pagedata['spec_image_request_url'] = $url;
        $this->pagedata['goodslink_filter'] = array('goods_id|noequal'=>$this->goods_id,'marketable'=>'true','store_id'=>$this->store_id);
        $this->pagedata['gdlytype_filter'] = array('store_id'=>$this->store_id,'dt_status'=>'1','disabled'=>'false');
        $this->pagedata['catlink_filter'] = array('store_id'=>$this->store_id);
        $goods = array();
        $objBGoods = &$this->app_current->model('goods');
        foreach($objBGoods->getList('goods_id',array('goods_id|noequal'=>$this->goods_id,'marketable'=>'true','store_id'=>$this->store_id)) as $items){
            $goods[] = $items['goods_id'];
        }
        if(empty($goods))$goods = array(-1);
        //过滤下架商品,过滤商品本身（配件）@lujy
        $this->pagedata['adjgoods_filter'] = array('goods_id|in'=>$goods);
        $tmp = $this->getPCCat($cat_id);
        $cat_id = $tmp['cat'];
        $parent = $tmp['parent'];
        $cat_list = $tmp['catlist'];
        $this->pagedata['goods']['category']['cat_id'] = $cat_id;
        $objCat = &$this->app_b2c->model('goods_cat');
        $this->pagedata['cats'] = $objCat->getMapTree($parent,'');
        $typeinfo = $objCat->getList('type_id', array('cat_id|in'=>$cat_list));
        $type_ids = array();
        foreach($typeinfo as $items){
            $type_ids[] = $items['type_id'];
        }
        $this->pagedata['gtype'][1] = array('type_id' => 1, 'name' => '通用商品类型');
        if(empty($type_ids)){
            return;
        }
        $objGtype = &$this->app_b2c->model('goods_type');
        foreach($objGtype->getList('*',array('type_id|in'=>$type_ids),0,-1) as $rows){
            $this->pagedata['gtype'][$rows['type_id']] = array('type_id' => $rows['type_id'], 'name' => $rows['name']);
        }
        $prototype = $objGtype->dump($type_id,'*',array('brand'=>array('*',array(':brand'=>array('brand_id,brand_name')))));
        /*if( $type_id == 1 ){
            $this->pagedata['brandList'] = array();
        }else if($prototype['setting']['use_brand']){
            if($this->issue_type == 1 || $this->issue_type == 3){
                $this->pagedata['brandList'] = $this->store_brand;
            }elseif(!empty($prototype['brand'])){
                $oBBrand = $this->app_current->model('brand');
                $aBBrand = array();
                foreach((array)$oBBrand->getList('brand_id', array('store_id'=>$this->store_id,'status'=>'1','type'=>'1')) as $rows){
                    $aBBrand[] = $rows['brand_id'];
                }

                foreach( $prototype['brand'] as $typeBrand ){
                    //$typeBrand['brand']['brand_id'] = 's_'.$typeBrand['brand']['brand_id'];
                    if(in_array($typeBrand['brand']['brand_id'], $aBBrand)){
                    $this->pagedata['brandList'][] = $typeBrand['brand'];
                    }
                }
            }
        }*/
        $oBBrand = $this->app_current->model('brand');
        $aBBrand = array();
        foreach((array)$oBBrand->getList('brand_id', array('store_id'=>$this->store_id,'status'=>'1'),0,-1) as $rows){
            $aBBrand[] = $rows['brand_id'];
        }
        $this->pagedata['brandList'] = $this->app->model('brand')->getList('*',array('brand_id'=>$aBBrand),0,-1);
        /*$objBBrand = &$this->app_current->model('brand');
        $custom_brand = $objBBrand->getList('');*/
        $this->pagedata['goods']['type']['type_id'] = $type_id;
        if($this->pagedata['goods']['spec']){ // || $prototype['spec']
            $prototype['setting']['use_spec'] = 1;
            if(!$this->pagedata['goods']['products']){
                $this->pagedata['goods']['products'] = array(1);
            }
        }
        $this->pagedata['goods']['type'] = $prototype;

        if($type_id != '1'){
            $goods_type_spec = $objGtype->getSpec($type_id);
            if($goods_type_spec){
                $this->pagedata['spec'] = true;
                $this->pagedata['type_id'] = $type_id;
                $this->pagedata['params_spec'] = json_encode($goods_type_spec);
            }
        }
        if(empty($this->pagedata['params_spec'])){
            $this->pagedata['params_spec'] = json_encode(array());
        }
    }

    public function goods_add()
    {
        $oStoregrade = $this->app_current->model('storegrade');
        $aGrade = $oStoregrade->getList('goods_num', array('grade_id'=>$this->grade_id));
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'goods_onsell'));
        if(!$aGrade) $this->splash('failed',$url , app::get('b2c')->_('由于某种原因，您不能执行该操作！'),'','',false);
        $oGoods = &$this->app_current->model('goods');
        $count = $oGoods->count(array('store_id'=>$this->store_id));
        if(intval($aGrade[0]['goods_num']) && $count >= intval($aGrade[0]['goods_num'])) $this->splash('failed',$url , app::get('b2c')->_('您已有最大'.$count.'件商品，不能再添加！'),'','',false);
        $oDlytype = app::get('b2c')->model('dlytype');
        $count = $oDlytype->count(array('store_id'=>$this->store_id,'dt_status'=>'1'));
        if(!$count) $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlytype')) , app::get('b2c')->_('您的店铺还没有创建或未启用运费模板！'),'','',false);
        $count = $oDlytype->db->select("select da_id from sdb_business_dlyaddress where store_id='{$this->store_id}' and (consign='true' or refund='true')");
        if(!count($count)) $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlyaddress')) , app::get('b2c')->_('您的店铺还没有创建发货地址或收货地址！'),'','',false);

        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('发布宝贝'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $cat_id = $this->issue_type?$this->region_id[0]:0;
        $store_id = $this->store_id;
        if($cat_id){
            $objCat = &$this->app_b2c->model('goods_cat');
            $aCat = $objCat->getList('type_id', array('cat_id'=>$cat_id));
            $type_id = ($aCat[0]['type_id']?$aCat[0]['type_id']:1);
        }else{
            $type_id = 1;
        }
        $this->pagedata['goods']['category']['cat_id'] = $cat_id;
        $this->pagedata['cat']['type_id'] = $type_id;
        $this->pagedata['goods']['type']['type_id'] = $type_id;
        $this->_editor($cat_id, $type_id);
        if(!count($this->pagedata['brandList'])) $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_brand','act'=>'return_brand')) , app::get('b2c')->_('您的店铺还没有商品品牌！'),'','',false);
        //header("Cache-Control:no-store");
        $this->pagedata['IMAGE_MAX_SIZE'] = IMAGE_MAX_SIZE;
        $this->pagedata['point_mim_get_value'] = $this->app_b2c->getConf('site.point_mim_get_value')*100;//运营商设置的兑换积分的最低比例
        $this->pagedata['point_max_get_value'] = $this->app_b2c->getConf('site.point_max_get_value')*100;//运营商设置的兑换积分的最高比例
        $this->pagedata['img_size'] = app::get('b2c')->getConf('site.big_pic_width').'*'.app::get('b2c')->getConf('site.big_pic_height');
        $this->output('business');
    }

    public function goods_edit($goods_id,$goods_switch="onsell")
    {
        $oDlytype = app::get('b2c')->model('dlytype');
        $count = $oDlytype->count(array('store_id'=>$this->store_id,'dt_status'=>'1'));
        if(!$count) $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlytype')) , app::get('b2c')->_('您的店铺还没有创建或未启用运费模板！'),'','',false);
        $count = $oDlytype->db->select("select da_id from sdb_business_dlyaddress where store_id='{$this->store_id}' and (consign='true' or refund='true')");
        if(!count($count)) $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlyaddress')) , app::get('b2c')->_('您的店铺还没有创建发货地址或收货地址！'),'','',false);

        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('维护宝贝'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'get_related_product','arg'=>$goods_id));
        $oGoods = &$this->app_b2c->model('goods');
        $this->pagedata['goods_switch'] = $goods_switch;
        $goods = $oGoods->dump(array('goods_id'=>$goods_id,'store_id'=>$this->store_id),'*','default');
        if(empty($goods)){
            $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'goods_'.$goods_switch)) , app::get('b2c')->_('此商品不是您的商品，不能编辑'),'','',false);
        }
        if ($goods['goods_kind'] == '3rdparty') {
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if (($processor->goodsKindDetail() == $goods['goods_kind_detail']) && $processor->isCustom('goods')) {
                    $processor->goodsEditPage($goods, $this);
                    return;
                }
            }
        }
        if(empty($goods['act_type']) || $goods['act_type'] != 'normal'){
            $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'goods_'.$goods_switch)) , app::get('b2c')->_('商品在活动中，不能编辑！'),'','',false);
        }
        if(!$goods) $goods_id = '';
        ksort($goods['images']);
        $this->goods_id = $goods_id;
        $this->_editor($goods['category']['cat_id'], $goods['type']['type_id']);
        if(!count($this->pagedata['brandList'])) $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_brand','act'=>'return_brand')) , app::get('b2c')->_('您的店铺还没有商品品牌！'),'','',false);
        if(is_numeric($goods['store'])) $goods['store'] = (float)$goods['store'];
        if(is_array($goods['product'])){
            foreach($goods['product'] as $k=>$v){
                $goods['product'][$k]['store'] = $v['store']!==null ? (float)$v['store'] : '';
            }
        }
        $this->pagedata['productkey'] = serialize(array_keys($goods['product']));
        $this->pagedata['goods'] = $goods;
        if(isset($goods['spec']) && !empty($goods['spec'])){
            $this->pagedata['spec'] = true;
            $this->pagedata['params_spec'] = json_encode($goods['spec']);
            $this->pagedata['goods_id'] = $goods_id;
        }else{
            $this->pagedata['spec'] = false;
        }
        $this->pagedata['app_dir'] = app::get('b2c')->app_dir;
        if(!is_array($goods['adjunct']))
            $this->pagedata['goods']['adjunct'] = unserialize($goods['adjunct']);
        else
            $this->pagedata['goods']['adjunct'] = $goods['adjunct'];
        foreach($oGoods->getLinkList($goods_id) as $rows){
            if($rows['goods_1'] == $goods_id){
                $aLinkList[] = $rows['goods_2'];
                $linkType[$rows['goods_2']] = array('manual'=>$rows['manual']);
            }else{
                $aLinkList[] = $rows['goods_1'];
                $linkType[$rows['goods_1']] = array('manual'=>$rows['manual']);
            }
        }
        $aDly = array();
        foreach((array)app::get('b2c')->model('goods_dly')->getList('dly_id',array('goods_id'=>$goods_id,'manual'=>'normal'),0,-1) as $items){
            $aDly[] = $items['dly_id'];
        }
        $this->pagedata['goods']['gdlytype'] = (array)$aDly;

        $oUrl = kernel::single('site_route_app');
        $goods_url = app::get('site')->router()->gen_url( array( 'app'=>'b2c','real'=>1,'ctl'=>'site_product','args'=>array($goods_id) ) );
        $goods_url = substr( $goods_url , strlen( app::get('site')->base_url() ) );
        $url = $oUrl->fetch_static( array( 'static'=>$goods_url ) );
        $this->pagedata['goods_static'] = $url['static'];
        $this->pagedata['goods']['product_num'] = count($this->pagedata['goods']['product']);
        $this->pagedata['goods']['glink']['items'] = $aLinkList;
        $this->pagedata['goods']['glink']['moreinfo'] = $linkType;
        $this->pagedata['goods']['goods_setting'] = $goods['goods_setting'];
        $this->pagedata['IMAGE_MAX_SIZE'] = IMAGE_MAX_SIZE;
        $objBGoodsCat = $this->app_current->model('goods_cat_conn');
        foreach($objBGoodsCat->getList('cat_id',array('goods_id'=>$goods_id)) as $rows){
            $this->pagedata['goods']['customcat'][] = $rows['cat_id'];
        }
        //$this->pagedata['related_return_url'] = 'index.php?app=b2c&ctl=admin_goods_editor&act=get_related_product&p[0]='.$goods_id;
        $this->pagedata['point_mim_get_value'] = $this->app_b2c->getConf('site.point_mim_get_value')*100;//运营商设置的兑换积分的最低比例
        $this->pagedata['point_max_get_value'] = $this->app_b2c->getConf('site.point_max_get_value')*100;//运营商设置的兑换积分的最高比例
        $this->pagedata['img_size'] = app::get('b2c')->getConf('site.big_pic_width').'*'.app::get('b2c')->getConf('site.big_pic_height');
        $this->output('business');
    }

    public function selAlbumsImg(){
        $this->pagedata['selImgs'] = explode(',',$_POST['selImgs']);
        $this->pagedata['img'] = $_POST['img'];
        $new_pics = array();
        $obj = kernel::single('base_storager');
        if(isset($_POST['goods']['images'])){
            foreach((array)$_POST['goods']['images'] as $items){
                $new_pics[$items] = $obj->image_path($items,'s');
            }
        }
        if(empty($this->pagedata['img'])) $this->pagedata['img'] = array();
         $this->pagedata['img'] = array_merge($this->pagedata['img'], $new_pics);
        $this->display('site/goods/spec_selalbumsimg.html','business');
    }

    public function set_nospec_index(){
        $goods_id = $_POST['goods']['goods_id'];
        $this->goods_id = $goods_id;
        $oGoods = &$this->app_b2c->model('goods');

        $this->_editor($_POST['goods']['category']['cat_id'], $_POST['goods']['type']['type_id']);
        $goods = $oGoods->dump($goods_id,'*','default');
        if(is_array($goods['product'])){
            foreach($goods['product'] as $k=>$v){
                $goods['product'][$k]['store'] = $v['store']!==null ? (float)$v['store'] : '';
                $this->pagedata['goods']['product'][0] = $goods['product'][$k];
                break;
            }
        }
        $this->pagedata['spec'] = false;
        $str_html = $this->fetch('site/goods/nospec.html','business');
        echo '{success:"'.app::get('b2c')->_($obj.'成功！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
    }

    public function set_spec_index(){
        /*
        if(!$this->has_permission('editgoods')){//没有编辑权限则没有编辑货品权限
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('desktop')->_("您无权操作");exit;
        }*/
        $this->pagedata['goods_info'] = $_GET['goods_info'];
        $goods_id = $_POST['goods']['goods_id'];
        if($_POST['goods']['images']){
            foreach($_POST['goods']['images'] as $items){
                $this->pagedata['goods']['images'][] = array('image_id'=>$items);
            }
        }else{
            $oImage = app::get('image')->model('image_attach');
            $image_arr = $oImage->getList('image_id',array('target_id'=>$goods_id,'target_type'=>'goods'));
            $image_arr_tmp = array();
            foreach($image_arr as $k=>$v){
                $image_arr_tmp[] = $v;
            }
            $this->pagedata['goods']['images'] = $image_arr_tmp;
        }

        $oGoods = &$this->app_b2c->model('goods');
        $this->goods_id = $goods_id;
        $this->_editor($_POST['goods']['category']['cat_id'], $_POST['goods']['type']['type_id']);
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_tools','act'=>'alertpages'));
        $urlgoto=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_tools','act'=>'image_broswer'));
        $url='"'.$url.'?dd="+Date.now()+"&goto='.urlencode($urlgoto).'"';
        //$this->pagedata['spec_image_request_url'] = "&quot;index.php?app=desktop&act=alertpages&goto=".urlencode("index.php?app=image&ctl=admin_manage&act=image_broswer")."&quot;";
        $this->pagedata['spec_image_request_url'] = $url;
        $this->pagedata['goods_id'] = $goods_id;
        $this->display('site/goods/spec.html','business',false);
    }

    public function set_spec($typeId)
    {
        $goods_id = $_POST['goods_id'];
        $spec_goods_images = app::get('image')->model('image_attach')->getList('image_id',array('target_id'=>$goods_id));
        if(is_string($_POST['spec'])){
            $_POST['spec'] = json_decode($_POST['spec'],1);
        }
        $has_spec = false;
        $oGoods = &$this->app_b2c->model('goods');
        $goods = $oGoods->dump($goods_id,'cat_id,goods_id,type_id,spec_desc');
        if(isset($goods['spec']) && !empty($goods['spec'])){
            $has_spec = true;
        }
        if( $_POST['spec'] && count($_POST['spec'])){
            $aReturn = $this->_set_spec($_POST['spec'],$has_spec);
        }else{
            $aReturn = $this->_set_type_spec($typeId);
        }
        echo json_encode($aReturn);
    }

    public function _set_type_spec($typeId){
        $oGtype = &$this->app_b2c->model('goods_type');
        $spec = (array)$oGtype->dump($typeId,'type_id',array(
                'spec'=>array('spec_id',
                    array(
                        'spec:specification'=>array('*',
                            array(
                                'spec_value' =>array('*')
                            )
                        )
                    )
                )
            )
        );

        $aSpec = array();
        foreach($spec['spec'] as $k1=>$v1){
            $spec_values = array();
            foreach($v1['spec']['spec_value'] as $k2=>$v2){
                if($v1['spec']['spec_type'] == "image"){
                    $v2['color'] = $v2['spec_value'];
                    $v2['view'] = base_storager::image_path($v2['spec_image'],'s');
                    $spec_values[] = $v2;
                }
                else{
                    $spec_values[] = $v2;
                }
            }


            $aSpec[$v1['spec_id']]['spec_id'] = $v1['spec']['spec_id'];
            $aSpec[$v1['spec_id']]['text'] = $v1['spec']['spec_name'];
            $aSpec[$v1['spec_id']]['spec_type'] = $v1['spec']['spec_type'];
            $aSpec[$v1['spec_id']]['tp'] = 'tp'.$v1['spec_id'];
            $aSpec[$v1['spec_id']]['index'] = $v1['spec_id'];
            $aSpec[$v1['spec_id']]['value'] = $spec_values;
            $spec['spec'][$k1] = $v1;
        }
        sort($aSpec);
        return array('all_use_spec'=>array(), 'spec_info'=>array(), 'spec'=>$aSpec,'selectedSpec'=>array(),'products'=>array());
    }

    public function _set_spec($spec,$has_spec=true){
        $oSpec = &$this->app_b2c->model('specification');
        $subSdf = array(
            'spec_value' =>array('*')
        );
        $specdata = $specinfo = $_POST['spec'];
        $default_spec_image = $this->app_b2c->getConf('spec.default.pic');
        $tmp_spec_goods_imgsrc = array();
        foreach($specinfo as $k=>$v){
            foreach($v['option'] as $k2=>$v2){
                $v['option'][$k2]['spec_goods_imagesrc'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$default_spec_image);
                $tmp_spec_image_url[$v2['spec_value_id']] = $v['option'][$k2]['spec_goods_imagesrc'];
                $tmp_spec_image[$v2['spec_value_id']] = ($v2['spec_image']&&$v2['spec_image']!='null')?$v2['spec_image']:$default_spec_image;
                $tmp_spec_value[$v2['spec_value_id']] = $v2['spec_value'];
                $tmp_spec_goods_img[$v2['private_spec_value_id']] = $v2['spec_goods_images']&&$v2['spec_goods_images']!='null'?$v2['spec_goods_images']:'';
                $aSpecTmp = explode(",", $tmp_spec_goods_img[$v2['private_spec_value_id']]);
                if($aSpecTmp){
                    foreach($aSpecTmp as $ks=>$vs){
                        $tmp_spec_goods_imgsrc[$v2['private_spec_value_id']][] = base_storager::image_path($vs&&$vs!='null'?$vs:$default_spec_image);
                    }
                }
                else{
                    $tmp_spec_goods_imgsrc[$v2['private_spec_value_id']][] = base_storager::image_path($default_spec_image);
                }
            }
            sort($v['option']);
            $specinfo[$k] = $v;
        }
        $specifications = $oSpec->batch_dump( array('spec_id'=>array_keys($spec)), '*' , $subSdf, 0 ,-1 );
        $aSpec = array();
        $selectedSpec = array();
        $i = 0;

        foreach($specifications as $k1=>$v1){
            $spec_values = array();
            $j = 0;
            foreach($v1['spec_value'] as $k2=>$v2){
                if($v1['spec_type'] == "image"){
                    $v2['color'] = $v2['spec_value'];
                    $v2['view'] = base_storager::image_path($v2['spec_image'],'s');
                }

                foreach($spec[$v1['spec_id']]['option'] as $k3=>$v3){
                    if($v3['spec_value_id'] == $v2['spec_value_id']){
                        $v2['private_spec_value_id'] = $v3['private_spec_value_id'];
                        $tmp_specvalue = array(
                            'spec_type'=>$v1['spec_type'],
                            'spec_id'=>$v1['spec_id'],
                            'spec_name'=>$v1['spec_name'],
                            'trSpec'=>'tp'.$v1['spec_id'],
                            'trPoint'=>'tp'.$v1['spec_id'].$j,
                            'spec_value_id'=>$v3['spec_value_id'],
                            'private_spec_value_id'=>$v3['private_spec_value_id'],
                            //'spec_value'=>$v2['spec_value'],
                            'spec_value'=>$tmp_spec_value[$v3['spec_value_id']],
                            'spec_goods_images'=>explode(",",$tmp_spec_goods_img[$v3['private_spec_value_id']]),
                            'spec_goods_images_url'=>$tmp_spec_goods_imgsrc[$v3['private_spec_value_id']],
                            'spec_image_url'=>$tmp_spec_image_url[$v3['spec_value_id']]
                        );
                        if($v1['spec_type'] == "image"){
                            //$tmp_specvalue['spec_image'] = $v2['spec_image'];
                            $tmp_specvalue['spec_image'] = $tmp_spec_image[$v3['spec_value_id']];
                            $tmp_specvalue['has_img']['spec_image_url'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$default_spec_image,'s');
                        }else{
                            unset($tmp_specvalue['spec_image_url']);
                        }
                        $selectedSpec[]['specValue'] = $tmp_specvalue;
                    }
                }
                $spec_values[] = $v2;
                $j++;
            }

            $aSpec[$v1['spec_id']]['spec_id'] = $v1['spec_id'];
            $aSpec[$v1['spec_id']]['text'] = $v1['spec_name'];
            $aSpec[$v1['spec_id']]['spec_type'] = $v1['spec_type'];
            $aSpec[$v1['spec_id']]['tp'] = 'tp'.$v1['spec_id'];
            $aSpec[$v1['spec_id']]['index'] = $i;
            $aSpec[$v1['spec_id']]['value'] = $spec_values;
            $spec[$k1] = $v1;
            $i++;
        }


        //if(isset($_POST['goods_id']) && $_POST['goods_id']){
        if($has_spec)
        $products = $this->getProducts($_POST['goods_id'], $_POST['product'],$specdata);

        //}
        sort($aSpec);

        return array('all_use_spec'=>$this->get_all_spec($all_use_spec,$_POST['spec']),'spec_info'=>$specinfo, 'spec'=>$aSpec,'selectedSpec'=>$selectedSpec,'product'=>$products);
    }

    public function getProducts($gid=0, $pdata=array(), $specdata=array()){
        if($gid){
            $defalut_spec_image = $this->app_b2c->getConf('spec.default.pic');
            $oGoods = app::get('b2c')->model('goods');
            $goodsinfo = $oGoods->dump($gid,'goods_id,spec_desc',
                            array('product'=>array('product_id,bn,price,cost,mktprice,store,store_place,weight,marketable,spec_desc',
                            array('price/member_lv_price'=>array('*'))
                        )));

            $products = $goodsinfo['product'];
            $spec_desc = $goodsinfo['spec_desc'];
        }
        else{
            $products = $pdata;
        }

        $returnValue = array();

        $goods_lv_price = $this->app_b2c->model('goods_lv_price')->getList('level_id,price,product_id',array('goods_id'=>$gid));
        if($goods_lv_price) {
            foreach($goods_lv_price as $k=>$v) {
                $goods_lv_price[$v['product_id']][$v['level_id']] = $v;
                unset($goods_lv_price[$k]);
            }
        }

        if(!$gid){
            foreach($products as $k=>$v){
                $mLevelPrice = array();
                if(!$gid){
                    foreach($v['price']['member_lv_price'] as $k2=>$v2) {
                        $products[$k]['price']['member_lv_price'][$k2] = array('price'=>$v2,'level_id'=>$k2, 'product_id'=>$v['product_id']);

                    }
                }
            }
        }

        foreach($products as $k=>$v){
            $mLevelPrice = array();
            foreach($v['price']['member_lv_price'] as $k2=>$v2) {
                $mLevelPrice[$k2]['member_lv_id'] = $v2['level_id'];
                if($gid){
                    if(isset($goods_lv_price[$v['product_id']]) && isset($goods_lv_price[$v['product_id']][$v2['level_id']])){
                        $mLevelPrice[$k2]['ml_price'] = $goods_lv_price[$v['product_id']][$v2['level_id']]['price'];
                    }
                }
                else{
                    $mLevelPrice[$k2]['ml_price'] = $v2['price'];
                }

            }
            sort($mLevelPrice);
            $returnValue[$v['product_id']] = array(
                'product_id'=>$v['product_id'],
                'bn'=>$v['bn'],
                'store'=>$v['store'],
                'price'=>$v['price']['price']['price'],
                'cost'=>$v['price']['cost']['price'],
                'mktprice'=>isset($v['price']['mktprice']['price'])?$v['price']['mktprice']['price']:$v['mktprice'],
                'mlv_price'=>$v['price']['member_lv_price'],
                'weight'=>$v['weight'],
                'store_place'=>$v['store_place'],
                'marketable'=>$v['status'],
                'mLevelPrice'=>$mLevelPrice,
            );

            $spec_info = isset($goodsinfo['spec'])?$goodsinfo['spec']:$specdata;
            $spec_desc = $v['spec_desc'];
            $spec = array();
            foreach($spec_desc['spec_value'] as $k2=>$v2){
                $spec[$k2]['spec_id'] = $k2;
                $spec[$k2]['spec_value'] = $v2;
                $spec[$k2]['spec_name'] = $spec_info[$k2]['spec_name'];
            }
            foreach($spec_desc['spec_private_value_id'] as $k2=>$v2){
                $spec_image = $spec_info[$k2]['option'][$v2]['spec_image'];

                $spec[$k2]['spec_private_value_id'] = $v2;
                $spec[$k2]['spec_image'] = $spec_image;
                $spec[$k2]['spec_image_url'] = base_storager::image_path($spec_image&&$spec_image!='null'?$spec_image:$defalut_spec_image);
                $spec[$k2]['spec_self_image_url'] = base_storager::image_path($spec_image&&$spec_image!='null'?$spec_image:$defalut_spec_image);
            }
            foreach($spec_desc['spec_value_id'] as $k2=>$v2){
                $spec[$k2]['spec_value_id'] = $v2;
            }
            sort($spec);
            $returnValue[$v['product_id']]['spec_desc'] = $spec;
        }
        sort($returnValue);
        return $returnValue;
    }

    public function addSpecValue(){
        $_POST = utils::stripslashes_array($_POST);

        $this->pagedata['spec_default_pic'] = $this->app_b2c->getConf('spec.default.pic');

        $aTmp = explode(",",$_POST['specGoodsImages']);
        $goods_spec_images = array();
        $goods_spec_images_url = array();
        foreach($aTmp as $k=>$v){
            $goods_spec_images[] = $v;
            $goods_spec_images_url[] = base_storager::image_path($v&&$v!=''?$v:$this->pagedata['spec_default_pic']);
        }

        $specValue = array(
            'spec_type' => $_POST['specType'],
            'spec_id' => $_POST['specId'],
            'spec_name' => $_POST['specName'],
            'trSpec' => $_POST['trSpec'],
            'trPoint' => $_POST['trPoint'],
            'spec_value_id' => $_POST['specValueId'],
            'spec_value' => $_POST['spec_value'],
            'private_spec_value_id'=>time().$_POST['specValueId'],
            'spec_goods_images'=>$goods_spec_images,
            'spec_goods_images_url'=>$goods_spec_images_url
        );

        if($_POST['specType'] == "image") {
            $specValue['spec_image'] = $_POST['specImage'];
            $specValue['has_img']['spec_image_url'] = base_storager::image_path(isset($_POST['specImage'])&&$_POST['specImage']!=''?$_POST['specImage']:$this->pagedata['spec_default_pic']);
            $specValue['spec_image_url'] = $specValue['has_img']['spec_image_url'];
        }

        $specinfo = $_POST['spec'];
        $specinfo[$specValue['spec_id']]['option'][$specValue['private_spec_value_id']] = array(
            'private_spec_value_id'=>$specValue['private_spec_value_id'],
            'spec_value'=>$specValue['spec_value'],
            'spec_value_id'=>$specValue['spec_value_id'],
            'spec_image'=>$specValue['spec_image'],
            'spec_goods_image'=>$specValue['spec_goods_image']
        );

        foreach($specinfo as $k=>$v){
            $res['spec_desc'][] = array(
                'spec_name'=>$v['spec_name'],
                'spec_id'=>$v['spec_id'],
                'spec_value'=>'',
                'spec_private_value_id'=>'',
                'spec_value_id'=>'',
                'spec_image'=>'',
                'spec_image_url'=>base_storager::image_path($defalut_spec_image),
            );
            foreach($v['option'] as $k2=>$v2){
                $v['option'][$k2]['spec_goods_imagesrc'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$defalut_spec_image);
            }
            sort($v['option']);
            $specinfo[$k] = $v;
        }
        echo json_encode (array('spec'=>$specinfo, 'specValue'=>$specValue,'spec_default_pic'=>base_storager::image_path($this->pagedata['spec_default_pic'])));
    }

    private function get_all_spec($all_use_spec, $spec) {
        if( empty( $spec ) ){
            $res = array();
            foreach( $all_use_spec as $pk => $pv ){
                foreach( $pv as $pvk => $pvv ){
                    $res[$pk][$pvv['spec_id']] =  $pvv['private_spec_value_id'];
                }
            }
            return $res;
        }
        $firstSpec = array_shift( $spec );

        $rs = array();
        foreach( $firstSpec['option'] as $sitem ){
            foreach( (array)$all_use_spec as $pitem ){
                $apitem = $pitem ;
                array_push( $apitem , array('spec_id'=>$firstSpec['spec_id']) + $sitem );
                $rs[] = $apitem;
            }
            if( empty($all_use_spec) )
                $rs[] = array( array_merge( array('spec_id'=>$firstSpec['spec_id']) , $sitem) );
        }
        return $this->get_all_spec( $rs, $spec);
    }

    function doAddSpec(){
        $oImage = app::get('image')->model('image');
        $defalut_spec_image = $this->app_b2c->getConf('spec.default.pic');
        $this->pagedata['goods']['spec'] = $_POST['spec'];
        foreach($this->pagedata['goods']['spec'] as $k=>$v){
            foreach($v['option'] as $k2=>$v2){
                if(!$v2['spec_value'] && $v2['spec_value'] !== "0") {
                    echo json_encode(array("msg"=>app::get('b2c')->_('新增规格项未填写规格值，请填写')));
                    exit;
                }
                $v['option'][$k2]['spec_goods_imagesrc'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$defalut_spec_image);
            }
            sort($v['option']);
            $this->pagedata['goods']['spec'][$k] = $v;
        }
        if( $_GET['create'] == 'true' ){
            $spec_names = array();
            foreach($_POST['spec'] as $v){
                $spec_names[$v['spec_id']] = $v['spec_name'];
            }
            $pro = $this->_doCreatePro( $pro, $_POST['spec'], $spec_names, $all_spec);
            $this->pagedata['fromType'] = 'create';
            foreach($pro as $k=>$v){
                //$pro[$k]['spec_desc']['a']['k'] = array_keys($v['spec_desc']['spec_value']);
            }

            $memberLevel = &$this->app_b2c->model('member_lv');
            $mLevels = $memberLevel->getList('member_lv_id');
            foreach($pro as $k=>$v) {
                $pro[$k]['mLevelPrice'] = $mLevels;
                $pro[$k]['marketable'] = 'true'; //默认上架商品
            }
            $this->pagedata['goods']['product'] = $pro;
        }
        $aReturn = $this->_set_spec( $_POST['spec'] );
        $this->pagedata['spec_tmpl'] = $this->pagedata['spec'];
        //$this->pagedata['needUpValue'] = json_encode($_POST['needUpValue']);
//        $this->pagedata['spec_default_pic'] = $this->app_b2c->getConf('spec.default.pic');
        $this->pagedata['app_dir'] = app::get('b2c')->app_dir;
        $data = array('goods'=>array('all_use_spec'=>$all_spec, 'spec'=>$this->pagedata['goods']['spec'],'products'=>$this->pagedata['goods']['product']));
        echo json_encode(array('goods'=>array('all_use_spec'=>$all_spec, 'spec'=>$this->pagedata['goods']['spec'],'products'=>$this->pagedata['goods']['product'])));
        $this->pagedata['spec_default_pic'] = $this->app_b2c->getConf('spec.default.pic');

        //$this->display('admin/goods/detail/spec/spec.html');
    }

    public function _doCreatePro( $pro, $spec, $spec_names, &$all_spec=array() ){
        if( empty( $spec ) ){
            $defalut_spec_image = $this->app_b2c->getConf('spec.default.pic');
            $res = array();
            foreach( $pro as $pk => $pv ){
                $res['new_'.$pk]['product_id'] = 'new_'.$pk;
                foreach( $pv as $pvk => $pvv ){

                    $res['new_'.$pk]['spec_desc'][] = array(
                        'spec_value'=>$pvv['spec_value'],
                        'spec_id'=>$pvv['spec_id'],
                        'spec_private_value_id'=>$pvv['private_spec_value_id'],
                        'spec_value_id'=>$pvv['spec_value_id'],
                        'spec_name'=>$spec_names[$pvv['spec_id']],
                        'spec_image'=>$pvv['spec_image'],
                        'spec_image_url'=>base_storager::image_path($pvv['spec_image']&&$pvv['spec_image']!='null'?$pvv['spec_image']:$defalut_spec_image),
                    );
                    $all_spec[$pk][$pvv['spec_id']] =  $pvv['private_spec_value_id'];

                    //$res['new_'.$pk]['spec_desc']['spec_type']['s'][] = array('id'=>$pvv['spec_id'],'value'=>$spec_names[$pvv['spec_id']]);
                }
            }
            return $res;
        }
        $firstSpec = array_shift( $spec );

        $rs = array();
        foreach( $firstSpec['option'] as $sitem ){
            foreach( (array)$pro as $pitem ){
                $apitem = $pitem ;
                array_push( $apitem , array('spec_id'=>$firstSpec['spec_id']) + $sitem );
                $rs[] = $apitem;
            }
            if( empty($pro) )
                $rs[] = array( array_merge( array('spec_id'=>$firstSpec['spec_id']) , $sitem) );
        }

       return $this->_doCreatePro( $rs, $spec, $spec_names, $all_spec);
    }

    public function addProduct(){
        $product_id = 'new_'.$_GET['product_id'];
        $specinfo = $_POST['spec'];
        $defalut_spec_image = $this->app_b2c->getConf('spec.default.pic');
        $memberLevel = &$this->app_b2c->model('member_lv');
        $mLevels = $memberLevel->getList('member_lv_id');

        $res['product_id'] = $product_id;
        $res['marketable'] = 'true';
        $res['mLevelPrice'] = $mLevels;

        foreach($specinfo as $k=>$v){
            foreach($v['option'] as $ks=>$vs) {
                if(!$vs['spec_value'] && $vs['spec_value'] !== "0") {
                    echo json_encode(array("msg"=>app::get('b2c')->_('新增规格项未填写规格值，请填写')));
                    exit;
                }
            }
            $res['spec_desc'][] = array(
                'spec_name'=>$v['spec_name'],
                'spec_id'=>$v['spec_id'],
                'spec_value'=>'',
                'spec_private_value_id'=>'',
                'spec_value_id'=>'',
                'spec_image'=>'',
                'spec_image_url'=>base_storager::image_path($defalut_spec_image),
                'marketable'=>true,
            );
            foreach($v['option'] as $k2=>$v2){
                $v['option'][$k2]['spec_goods_imagesrc'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$defalut_spec_image);
            }
            sort($v['option']);
            $specinfo[$k] = $v;
        }

        echo json_encode(array('product'=>$res,'spec'=>$specinfo,'all_use_spec'=>$this->get_all_spec($all_use_spec, $_POST['spec'])));
    }

    public function set_mprice(){
        /*@lujy--会员价权限
        if(!$this->has_permission('editmemberlevelprice')){
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('b2c')->_("您无权操作会员价");exit;
        }*/
        $memberLevel = &$this->app_b2c->model('member_lv');
        foreach($memberLevel->getList('member_lv_id,name,dis_count,name') as $level){
            $level['dis_count'] = ($level['dis_count']>0 ? $level['dis_count'] : 1);
            $level['price'] = $_POST['level'][$level['member_lv_id']];
            $this->pagedata['mPrice'][$level['member_lv_id']] = $level;
        }
        $this->display('site/goods/level_price.html', 'business');
    }

    public function addGrp(){
        $this->pagedata['goods_id'] = $_GET['goods_id'];
        $this->pagedata['aOptions'] = array('goods'=>app::get('b2c')->_('选择几件商品作为配件'), 'filter'=>app::get('b2c')->_('选择一组商品搜索结果作为配件'));
        $this->display('site/goods/adj_info.html','business');
    }

    public function doAddGrp($goodsid){
        $this->pagedata['adjunct'] =array('name'=>$_POST['name'],'type'=>$_POST['type']);
        $this->pagedata['key'] = time();
        $objBGoods = &$this->app_current->model('goods');
        $goods = array();
        foreach($objBGoods->getList('goods_id',array('goods_id|noequal'=>$goodsid,'marketable'=>'true','store_id'=>$this->store_id)) as $items){
            $goods[] = $items['goods_id'];
        }
        if(empty($goods))$goods = array(-1);
        //过滤下架商品,过滤商品本身（配件）@lujy
        $this->pagedata['adjgoods_filter'] = array('goods_id|in'=>$goods);
        $this->pagedata['store_id'] = $this->store_id;
        $this->pagedata['region_id'] = $this->region_id;
        $this->display('site/goods/adj_row.html','business');
    }

    public function goods_onsell($orderBy = 0, $page = 1)
    {
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('出售中的宝贝'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['goods_switch'] = "onsell";
        $this->pagedata = array_merge_recursive($this->goods_info($_POST['content'], 'onsell', $orderBy, $page), $this->pagedata);
        $this->output('business');
    }

    public function goods_instock($orderBy = 0, $page = 1)
    {
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('仓库中的宝贝'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['goods_switch'] = "instock";
        $this->pagedata = array_merge_recursive($this->goods_info($_POST['content'], 'instock', $orderBy, $page), $this->pagedata);
        $this->output('business');
    }

    public function goods_alert($orderBy = 0, $page = 1)
    {
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('预警中的宝贝'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['goods_switch'] = "alert";
        $obj_strman = app::get('business')->model('storemanger');
        $this->pagedata['alert_num'] = $obj_strman->getList('alert_num',array('store_id'=>$this->store_id));
        $this->pagedata['alert_num'] = ($this->pagedata['alert_num'][0])?intval($this->pagedata['alert_num'][0]['alert_num']):'';
        $this->pagedata['system_num'] = $this->app->getConf('system.product.alert.num');
        $this->pagedata = array_merge_recursive($this->goods_info($_POST['content'], 'alert', $orderBy, $page), $this->pagedata);
        $this->output('business');
    }

    public function goods_violate($orderBy = 0, $page = 1)
    {
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('违规下架的宝贝'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['controller'] = 'goods_instock';
        $this->pagedata['goods_switch'] = "violate";
        $this->pagedata = array_merge_recursive($this->goods_info($_POST['content'], 'violate', $orderBy, $page), $this->pagedata);
        $this->output('business');
    }

    public function goods_delete($switch="onsell", $mdl_name="b2c_mdl_goods")
    {
        $act = 'goods_onsell';
        $filter = array('disabled'=>'false','goods_type'=>'normal','store_id'=>$this->store_id);
        switch($switch){
            case 'onsell':
              $act = 'goods_onsell';
              $filter['marketable'] = 'true';
              break;
            case 'instock':
              $act = 'goods_instock';
              $filter['marketable'] = 'false';
              break;
            case 'alert':
              $alert_num = $this->app->getConf('system.product.alert.num');
              $obj_strman = app::get('business')->model('storemanger');
              $alert_num = $obj_strman->getList('alert_num', array('store_id'=>$this->store_id));
              $alert_num = intval($alert_num[0]['alert_num']);
              $filter['marketable'] = 'true';
              $filter['store|sthan'] = $alert_num;
              $act = 'goods_alert';
              break;
            case 'violate':
              $act = 'goods_violate';
              $filter['marketable_allow'] = 'false';
              break;
        }
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>$act));

        if(empty($_POST)){
            echo '{error:"'.app::get('b2c')->_('没有可以删除的数据').'",_:null}';exit;
        }

        //if(isset($_POST['operate']) && $_POST['operate'] == 'true'){
        //}else
        if(count($_POST['item']) > 0){
            $filter['goods_id|in'] = $_POST['item'];
        }else{
            echo '{error:"'.app::get('b2c')->_('没有可以删除的数据').'",_:null}';exit;
        }

        $servicelog = kernel::service('operatorlog.' . $mdl_name);
        if(method_exists($servicelog, 'logDelInfoStart')){
            $servicelog->logDelInfoStart($filter);
        }
        list($app_id,$table) = explode('_mdl_',$mdl_name);
        $o = app::get($app_id)->model($table);
        $dbschema = $o->get_schema();
        $pkey = $dbschema['idColumn'];
        $rows = $o->getList('*',$filter,0,-1);
        $error_info = array();
        if($table == 'goods')
        foreach((array)$rows as $items){
            if(empty($items['act_type']) || $items['act_type'] != 'normal'){
                $error_info[] = $items['bn'];
            }
        }
        if(!empty($error_info)){
            echo '{error:"商品编号为 '.implode('、', $error_info).' 在活动中，不允许删除",_:null}';exit;
        }
        if(method_exists($o, 'pre_recycle')){
            if(!$o->pre_recycle($rows)){
                echo '{error:"'.$o->recycle_msg.'",_:null}';exit;
            }
        }
        foreach($rows as $k=>$v){
            $pkey_value = $v[$pkey];
            $o->delete(array($pkey=>$pkey_value));
            if (isset($v['goods_kind']) && $v['goods_kind'] == '3rdparty') {
                foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                    if (($processor->goodsKindDetail() == $v['goods_kind_detail']) && $processor->isCustom('goods_delete')) {
                        $processor->goodsDelete($pkey_value);
                    }
                }
            }
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员删除操作日志，删除成功信息记录@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if(method_exists($servicelog, 'logDelInfoEnd')){
            $servicelog->logDelInfoEnd($del_flag=true);
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员删除操作日志，删除成功信息记录@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        $this->pagedata = array_merge_recursive($this->goods_info($_POST['content'], $switch, 0, 1), $this->pagedata);
        $str_html = $this->fetch('site/member/goods_list.html','business');
        echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
    }

    function goods_enabled($switch="onsell", $mdl_name="b2c_mdl_goods")
    {
        $act = 'goods_onsell';
        $obj = '下架';
        $enabled = 'true';
        $filter = array('disabled'=>'false','goods_type'=>'normal','store_id'=>$this->store_id);
        switch($switch){
            case 'onsell':
              $act = 'goods_onsell';
              $obj = '下架';
              $enabled = 'false';
              $filter['marketable'] = 'true';
              break;
            case 'instock':
              $act = 'goods_instock';
              $obj = '上架';
              $enabled = 'true';
              $filter['marketable'] = 'false';
              break;
        }
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>$act));

        if(empty($_POST)){
            echo '{error:"'.app::get('b2c')->_('没有可以'.$obj.'的数据').'",_:null}';exit;
        }

        //if(isset($_POST['operate']) && $_POST['operate'] == 'true'){
        //}else
        if(count($_POST['item']) > 0){
            $filter['goods_id|in'] = $_POST['item'];
        }else{
            echo '{error:"'.app::get('b2c')->_('没有可以'.$obj.'的数据').'",_:null}';exit;
        }

        list($app_id,$table) = explode('_mdl_',$mdl_name);
        $o = app::get($app_id)->model($table);
        $dbschema = $o->get_schema();
        $pkey = $dbschema['idColumn'];
        $rows = $o->getList('*',$filter,0,-1);
        $error_info = array();
        if($table == 'goods')
        foreach((array)$rows as $items){
            if(empty($items['act_type']) || $items['act_type'] != 'normal'){
                $error_info[] = $items['bn'];
            }
        }
        if(!empty($error_info)){
            echo '{error:"商品编号为 '.implode('、', $error_info).' 在活动中，不允许'.$obj.'",_:null}';exit;
        }
        foreach($rows as $k=>$v){
            if($switch == 'instock' && $v['marketable_allow'] != 'true'){
                $error_info[] = $v['bn'];
                continue;
            }
            if($switch == 'instock' && $this->store['limit_goods'] == 1){
                $error_info[] = $v['bn'];
                continue;
            }
            if($switch == 'instock' && intval($v['store']) < 1 ){
                $error_info[] = $v['bn'];
                continue;
            }
            $pkey_value = $v[$pkey];
            $o->setEnabled(array($pkey=>$pkey_value), $enabled);
        }
        $this->pagedata = array_merge_recursive($this->goods_info($_POST['content'], $switch, 0, 1), $this->pagedata);
        $str_html = $this->fetch('site/member/goods_list.html','business');
        if($error_info){
            echo '{error:"商品编号为 '.implode('、', $error_info).' 的不允许上架",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
        }else
        echo '{success:"'.app::get('b2c')->_($obj.'成功！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
    }

    function goods_recommend($mdl_name = "b2c_mdl_goods") {
        $act = 'goods_onsell';
        $goodstype = $_GET['goodstype'];
        $status = $_GET['status'];
        $filter = array('disabled' => 'false', 'goods_type' => 'normal', 'store_id' => $this->store_id);
        //var_dump($goodstype, $status);
        //die();
        if ($goodstype == 'recommend') {
            $tings = '热门推荐';
            $mtype = 'is_tui';
        } else {
            $tings = '新品上架';
            $mtype = 'is_new';
        }

        if ($status == '1') {
            $obj = '设置';
            $st = 'true';
        } else {
            $obj = '取消';
            $st = 'false';
        }

        $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => $act));

        if (empty($_POST)) {
            echo '{error:"' . app::get('b2c')->_('没有可以' . $obj . $tings . '的数据') . '",_:null}';
            exit;
        }

        if (count($_POST['item']) > 0) {
            $filter['goods_id|in'] = $_POST['item'];
        } else {
            echo '{error:"' . app::get('b2c')->_('没有可以' . $obj . $tings . '的数据') . '",_:null}';
            exit;
        }
        //$filter['is_tui'] = 'true';
        list($app_id, $table) = explode('_mdl_', $mdl_name);
        $o = app::get($app_id)->model($table);
        $dbschema = $o->get_schema();
        $pkey = $dbschema['idColumn'];
        $rows = $o->getList('*', $filter, 0, -1);
        //var_dump($rows, $rows[0]['is_tui']);
        //die();
        $error_info = array();
        //var_dump($filter, $rows);
        //die();
        foreach ($rows as $k => $v) {

            if (intval($status) > 0 && $v[$mtype] == 'true') {
                $error_info[] = $v['bn'].'(已设置)';
                continue;
            }
            if (intval($status) == 0 && $v[$mtype] == 'false') {
                $error_info[] = $v['bn'].'(已取消设置)';
                continue;
            }
            if (intval($v['store']) < 1) {
                $error_info[] = $v['bn']."(库存不足)";
                continue;
            }
            $pkey_value = $v[$pkey];
            $o->setRecommend(array($pkey => $pkey_value), $mtype, $st);
        }
        $this->pagedata = array_merge_recursive($this->goods_info($_POST['content'], 'onsell', 0, 1), $this->pagedata);
        $str_html = $this->fetch('site/member/goods_list.html', 'business');
        if ($error_info) {
            echo '{error:"商品编号为 ' . implode('、', $error_info) . ' 的不允许' . $obj . $tings . '",_:null,data:"' .addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))). '",reload:null}';
            exit;
        } else {
            echo '{success:"' . app::get('b2c')->_($obj . $tings . '成功！') . '",_:null,data:"' .addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))). '",reload:null}';
            exit;
        }
    }

    function goods_tofree($switch = "onsell", $mdl_name = "b2c_mdl_goods") {
        $filter = array('disabled'=>'false','goods_type'=>'normal','store_id'=>$this->store_id);
        switch($switch){
            case 'onsell':
              $act = 'goods_onsell';
              $filter['marketable'] = 'true';
              break;
            case 'instock':
              $act = 'goods_instock';
              $filter['marketable'] = 'false';
              break;
        }
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>$act));
        if(empty($_POST)){
            echo '{error:"'.app::get('b2c')->_('参数错误').'",_:null}';exit;
        }
        //if(isset($_POST['operate']) && $_POST['operate'] == 'true'){
        //}else
        if(count($_POST['item']) > 0){
            $filter['goods_id|in'] = $_POST['item'];
        }else{
            echo '{error:"'.app::get('b2c')->_('参数错误').'",_:null}';exit;
        }

        list($app_id,$table) = explode('_mdl_',$mdl_name);
        $o = app::get($app_id)->model($table);
        $dbschema = $o->get_schema();
        $pkey = $dbschema['idColumn'];
        $rows = $o->getList('*',$filter,0,-1);
        $error_info = array();
        if($table == 'goods')
        foreach((array)$rows as $items){
            if(empty($items['act_type']) || $items['act_type'] != 'normal'){
                $error_info[] = $items['bn'];
            }
        }
        if(!empty($error_info)){
            echo '{error:"商品编号为 '.implode('、', $error_info).' 在活动中，不允许操作免运费",_:null}';exit;
        }
        foreach($rows as $k=>$v){
            $pkey_value = $v[$pkey];
            $o->setToFree(array($pkey=>$pkey_value), 'business');
        }
        $this->pagedata = array_merge_recursive($this->goods_info($_POST['content'], $switch, 0, 1), $this->pagedata);
        $str_html = $this->fetch('site/member/goods_list.html','business');
        echo '{success:"'.app::get('b2c')->_('免运费成功！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
    }

    function goods_tohave($switch="onsell",$mdl_name="b2c_mdl_goods")
    {
        $filter = array('disabled'=>'false','goods_type'=>'normal','store_id'=>$this->store_id);
        switch($switch){
            case 'onsell':
              $act = 'goods_onsell';
              $filter['marketable'] = 'true';
              break;
            case 'instock':
              $act = 'goods_instock';
              $filter['marketable'] = 'false';
              break;
        }
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>$act));
        if(empty($_POST)){
            echo '{error:"'.app::get('b2c')->_('参数错误').'",_:null}';exit;
        }
        //if(isset($_POST['operate']) && $_POST['operate'] == 'true'){
        //}else
        if(count($_POST['item']) > 0){
            $filter['goods_id|in'] = $_POST['item'];
        }else{
            echo '{error:"'.app::get('b2c')->_('参数错误').'",_:null}';exit;
        }

        list($app_id,$table) = explode('_mdl_',$mdl_name);
        $o = app::get($app_id)->model($table);
        $dbschema = $o->get_schema();
        $pkey = $dbschema['idColumn'];
        $rows = $o->getList('*',$filter,0,-1);
        $error_info = array();
        if($table == 'goods')
        foreach((array)$rows as $items){
            if(empty($items['act_type']) || $items['act_type'] != 'normal'){
                $error_info[] = $items['bn'];
            }
        }
        if(!empty($error_info)){
            echo '{error:"商品编号为 '.implode('、', $error_info).' 在活动中，不允许操作有运费",_:null}';exit;
        }
        foreach($rows as $k=>$v){
            $pkey_value = $v[$pkey];
            $o->setToFree(array($pkey=>$pkey_value), 'member');
        }
        $this->pagedata = array_merge_recursive($this->goods_info($_POST['content'], $switch, 0, 1), $this->pagedata);
        $str_html = $this->fetch('site/member/goods_list.html','business');
        echo '{success:"'.app::get('b2c')->_('有运费成功！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
    }

    function goods_search($switch="onsell", $mdl_name="b2c_mdl_goods"){
        $act = 'goods_onsell';
        switch($switch){
            case 'onsell':
              $act = 'goods_onsell';
              break;
            case 'instock':
              $act = 'goods_instock';
              break;
            case 'alert':
              $act = 'goods_alert';
              break;
            case 'violate':
              $act = 'goods_violate';
        }
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>$act));
        $orderBy = $_GET['orderby']?$_GET['orderby']:0;
        $page = $_GET['page']?$_GET['page']:1;
        if($orderBy){
            $temp = explode(',', $orderBy);
            $orderBy = 'buy_count '.$temp[0].',uptime '.$temp[1];
        }
        $data = $this->goods_info($_POST['content'], $switch, $orderBy, $page);
        $this->pagedata = $data;
        $str_html = $this->fetch('site/member/goods_list.html','business');
        echo '{success:"'.app::get('b2c')->_($obj.'成功！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
    }

    function goods_info($filter, $switch, $orderBy = 0, $page = 1){
        $objGoods = &$this->app_current->model('goods');
        if(empty($filter['name'])) unset($filter['name']);
        if(empty($filter['bn'])) unset($filter['bn']);
        if(empty($filter['cat_id'])) unset($filter['cat_id']);
        if(!empty($filter['price_from'])){
            $filter['price|bthan'] = intval($filter['price_from']);
        }
        unset($filter['price_from']);
        if(!empty($filter['price_to'])){
            $filter['price|lthan'] = intval($filter['price_to']);
        }
        unset($filter['price_to']);
        if(!empty($filter['bcount_from'])){
            $filter['buy_count|bthan'] = intval($filter['bcount_from']);
        }
        unset($filter['bcount_from']);
        if(!empty($filter['bcount_to'])){
            $filter['buy_count|lthan'] = intval($filter['bcount_to']);
        }
        unset($filter['bcount_to']);
        $filter['disabled'] = 'false';
        $filter['goods_type'] = 'normal';
        $filter['store_id'] = $this->store_id;
        if(!empty($filter['custom_cat_id'])){
            $objBGoodsCat = &$this->app_current->model('goods_cat_conn');
            $gid_list = array();
            foreach($objBGoodsCat->getList('goods_id', array('cat_id'=>intval($filter['custom_cat_id']))) as $rows){
                $gid_list[] = $rows['goods_id'];
            }
            $filter['goods_id|in'] = $gid_list;
        }
        $return = array();
        $orderBy = $orderBy?$orderBy:'buy_count desc,uptime desc';
        foreach((array)explode(',' , $orderBy) as $item){
            preg_match("/^(\S+)\s+(\S+)/i",$item, $matches);
            $matches[2] = strtolower($matches[2]);
            $matches[2] = ($matches[2] == 'desc' || $matches[2] == 'asc')?$matches[2]:'desc';
            if($matches[1] && $matches[1]=='buy_count'){
                $return['buy_count_orderby'] = $matches[2];
            }else if($matches[1] && $matches[1]=='uptime'){
                $return['uptime_orderby'] = $matches[2];
            }
        }
        switch($switch){
            case 'onsell':
            $filter['marketable'] = 'true';
            $act = 'goods_onsell';
            break;
            case 'instock':
            $filter['marketable'] = 'false';
            $act = 'goods_instock';
            break;
            case 'alert':
            $alert_num = $this->app->getConf('system.product.alert.num');
            $obj_strman = app::get('business')->model('storemanger');
            $alert_num = $obj_strman->getList('alert_num', array('store_id'=>$this->store_id));
            $alert_num = intval($alert_num[0]['alert_num']);
            $filter['marketable'] = 'true';
            $filter['store|sthan'] = $alert_num;
            $act = 'goods_alert';
            break;
            case 'violate':
            $filter['marketable_allow'] = 'false';
            $act = 'goods_violate';
            break;
        }
        $return['switch'] = $switch;
        $pageLimit = $this->app_b2c->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);
        $aGoods = $objGoods->getList('is_tui,is_new,goods_id,name,bn,store,price,buy_count,uptime,thumbnail_pic,image_default_id,store_id', $filter, $pageLimit*($page-1), $pageLimit, $orderBy);
        $iCount = $objGoods->count($filter);
        $return['gall'] = array();
        foreach((array)$objGoods->getList('goods_id',$filter,0,-1) as $item){
            $return['gall'][] = $item['goods_id'];
        }
        $return['gall'] = json_encode($return['gall']);
        $return['pager'] = array(
            'current'=>$page,
            'total'=>ceil($iCount/$pageLimit),
            'link'=>  $this->gen_url(array('app'=>'business', 'ctl'=>'site_member','full'=>1,'act'=>$act,'args'=>array( $orderBy, ($tmp=time())))),
            'token'=>$tmp);
        if($page != 1 && $page > $return['pager']['total']){
            $this->_response->set_http_response_code(404);
        }
        $imageDefault = app::get('image')->getConf('image.set');
        $return['image_set'] = $imageDefault;
        $return['defaultImage'] = $imageDefault['S']['default_image'];
        $return['pdtPic']=array('width'=>100,'heigth'=>100);
        $goods_ids = array();
        foreach((array)$aGoods as $items){
            $goods_ids[] = $items['goods_id'];
        }
        if(!empty($goods_ids)){
            $goods_store = array();
            $sql = "select goods_id,sum(store) as store,sum(freez) as freez from sdb_b2c_products where goods_id in (".implode(',',$goods_ids).") group by goods_id";
            foreach((array)$objGoods->db->select($sql) as $items){
                $goods_store[$items['goods_id']] = $items;
            }
            foreach((array)$aGoods as $key => $value){
                $aGoods[$key]['store'] = 0;
                $aGoods[$key]['freez'] = 0;
                if(isset($goods_store[$value['goods_id']])){
                    $aGoods[$key]['store'] = $goods_store[$value['goods_id']]['store'];
                    $aGoods[$key]['freez'] = $goods_store[$value['goods_id']]['freez'];
                }
            }
        }
        $return['goods'] = $aGoods;
        $return['shop'] = $filter['store_id'];
        //$return['current_url'] = app::get('business')->res_url;

        //$objCat = &$this->app_b2c->model('goods_cat');
        $objBGoods = &$this->app_current->model('goods');
        $catList =$objBGoods->get_cat_list($this->region_id);
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>app::get('b2c')->_('----无----'),'step'=>1);
        if(empty($catList)){
            $catList = $aCatNull;
        }else{
            $catList = array_merge($aCatNull, $catList);
        }
        $return['catList'] = $catList;
        $customcatList =$objBGoods->get_custom_cat_list($this->store_id);
        if(empty($customcatList)){
            $customcatList = $aCatNull;
        }else{
            $customcatList = array_merge($aCatNull, $customcatList);
        }
        $return['customcatList'] = $customcatList;
        return $return;
    }

    function goods_apply($goods_id){
        $obj_apply = app::get('b2c')->model('goods_marketable_application');
        $content['apply'] = $obj_apply->getList('*',array('goods_id'=>intval($goods_id)),0,-1,'apply_id asc');
        if($content['apply'])
        foreach((array)$content['apply'] as $key => $value){
            switch(intval($value['status'])){
                case 0:
                $content['apply'][$key]['status'] = '待审核';
                break;
                case 1:
                $content['apply'][$key]['status'] = '审核通过';
                break;
                case 2:
                $content['apply'][$key]['status'] = '审核不通过';
                break;
            }
        }
        $this->pagedata['apply_info'] = $content;
        $this->pagedata['goods_id'] = intval($goods_id);
        $this->pagedata['allow_apply'] = true;
        if($obj_apply->count(array('goods_id'=>intval($goods_id),'status'=>'0'))>0){
            $this->pagedata['allow_apply'] = false;
        }
        $this->display('site/member/goods_apply.html', 'business');
    }

    function toApply(){
        if(!$_POST['gid']){
            echo 0;exit;
        }
        $objGoods = $this->app_b2c->model('goods');
        $goods = $objGoods->getList('goods_id',array('goods_id'=>intval($_POST['gid']),'store_id'=>$this->store_id,'marketable'=>'false','marketable_allow'=>'false'));
        $goods_id = $goods[0]['goods_id'];
        if(!$goods_id){
            echo 0;exit;
        }
        $obj_apply = app::get('b2c')->model('goods_marketable_application');
        $date = time();
        $apply_data = array();
        $apply_data['goods_id'] = $goods_id;
        $apply_data['status'] = '0';
        $apply_data['content'] = $_POST['content'];
        $apply_data['apply_time'] = $date;
        $apply_data['apply_user'] = $this->app_b2c->member_id;
        $apply_data['last_modify'] = $date;
        $obj_apply->insert($apply_data);
        echo 1;exit;
    }

    function change_alert_num(){
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'goods_alert'));
        if(isset($_POST['alert_num']) && !empty($_POST['alert_num'])){
            $obj_strman = app::get('business')->model('storemanger');
            $data = array();
            $data['alert_num'] = intval($_POST['alert_num']);
            $flag = $obj_strman->update($data,array('store_id'=>$this->store_id));
        }
        $this->splash('success',$url , app::get('b2c')->_('操作成功'),'','',true);
    }

    function goods_update()
    {
        $goods = $this->_prepareGoodsData($_POST);
        $this->pagedata['goods'] = $goods;
        $this->pagedata['show'] = $_GET['show'];
        $this->_editor($goods['category']['cat_id'], $goods['type']['type_id']);
        if($_POST['is_entity'] == '1'){
            $this->pagedata['spec'] = false;
            $this->pagedata['is_entity'] = '1';
        }
        $this->display('site/goods/ginfo.html','business',false);
    }

    public function _prepareGoodsData( &$data ){
        $objGoods = $this->app_b2c->model('goods');
        $objPro = $this->app_b2c->model('products');
        $objGtype = $this->app_b2c->model('goods_type');
        $lastGoodsId = $objGoods->getlist('goods_id',array(),0,1,'goods_id desc');
        $lastGoodsId = $lastGoodsId[0]['goods_id'];
        $data['goods']['store_id'] = $this->store_id;
        $goods = $data['goods'];
        if( !$goods['category']['cat_id']) $goods['category']['cat_id'] = $this->region_id;
        if(is_numeric($goods['type']['type_id'])){
            $floatstore = $objGtype->getlist('floatstore',array('type_id'=>$goods['type']['type_id']));
            if(!$floatstore[0]['floatstore']){
                foreach((array)$goods['product'] as $key=>$val){
                    if( $val['store'] )
                        $goods['product'][$key]['store']= intval($val['store']);
                }
            }
        }


        $goods['adjunct'] = $data['adjunct'];
        if(is_array($goods['images']))
            $goods['image_default_id'] = $data['image_default'];
        else
            $goods['image_default_id'] = null;
        if( $data['keywords'] ){
            foreach( explode( '|', $data['keywords']) as $keyword ){
                $goods['keywords'][] = array(
                    'keyword' => $keyword,
                    'res_type' => 'goods'
                );
            }
        }
        if( $_POST['spec'] ){
            $spec_info = array();
            if($_POST['specall'] && is_array($_POST['specall'])){
                foreach((array)$_POST['specall'] as $items){
                    $spec_info[$items] = array();
                }
            }
            if($spec_info){
                foreach((array)$_POST['spec'] as $key => $value){
                    $spec_info[$key] = $value;
                }
                $goods['spec'] = $spec_info;
            }else
            $goods['spec'] = $_POST['spec'];
        }else{
            $goods['spec'] = null;
        }
        if( $goods['params'] ){
            $goodsParams = array();
            foreach( $goods['params'] as $gpk => $gpv ){
                $goodsParams[$data['goodsParams']['group'][$gpk]][$data['goodsParams']['item'][$gpk]] = $gpv;
            }
            $goods['params'] = $goodsParams;
        }
        //处理配件

        if( !$goods['min_buy'] )unset( $goods['min_buy'] );
        if( !$goods['brand']['brand_id'] ) $goods['brand']['brand_id'] = null;
        $images = array();
        foreach( (array)$goods['images'] as $imageId ){
            $images[] = array(
                'target_type'=>'goods',
                'image_id'=>$imageId,
                );
        }
        $goods['images'] = $images;
        unset($images);
        if(isset($goods['adjunct']['name'])){
           foreach($goods['adjunct']['name'] as $key => $name){
                $aItem = array();
                $aItem['name'] = $name;
                $aItem['type'] = $goods['adjunct']['type'][$key];
                $aItem['min_num'] = $goods['adjunct']['min_num'][$key];
                $aItem['max_num'] = $goods['adjunct']['max_num'][$key];
                $aItem['set_price'] = $goods['adjunct']['set_price'][$key];
                $aItem['price'] = $goods['adjunct']['price'][$key];
                if($aItem['type'] == 'goods'){
                    $aItem['items']['product_id'] = $goods['adjunct']['items'][$key];
                }else{
                    $temp = array();
                    $a = explode('&', $goods['adjunct']['items'][$key]);
                    $i = 0;
                    while ($i < count($a)) {
                        $b = split('=', $a[$i]);
                        $temp[] = htmlspecialchars(urldecode($b[0])).'='.htmlspecialchars(urldecode($b[1]));
                        $i++;
                    }
                    $aItem['items'] = implode('&',$temp);//.'&dis_goods[]='.$aData['goods_id']
                }
                if($aItem['set_price']  == 'discount' && $aItem['price']>1){
                    $this->splash('failed','' , app::get('b2c')->_('配件折扣不能大于1'),'','',true);
                }
                $aAdj[] = $aItem;
            }
        }
        $goods['adjunct'] = $aAdj;
        $goods['product'][key((array)$goods['product'])]['default'] = '1';

        foreach( $goods['product'] as $prok => $pro ){
            if($goods['unit'])
                $goods['product'][$prok]['unit'] = $goods['unit'];
            if( !$pro['product_id'] || substr( $pro['product_id'],0,4 ) == 'new_' )
                unset( $goods['product'][$prok]['product_id'] );
            if( $pro['status'] != 'true' ){
                $goods['product'][$prok]['status'] = 'false';
            }else{
                $upgoods = true;
            }
            $mprice = array();
            if( $pro['weight'] === '' )
                $goods['product'][$prok]['weight'] = '0';
            if( $pro['store'] === '' )
                $goods['product'][$prok]['store'] = null;
            foreach( (array)$pro['price']['member_lv_price'] as $mLvId => $mLvPrice )
                if( $mLvPrice )
                    $mprice[] = array( 'level_id'=>$mLvId,'price'=>$mLvPrice );
            $goods['product'][$prok]['price']['member_lv_price'] = $mprice;
            foreach( array('cost','price') as $pCol ){
                if( !$pro['price'][$pCol]['price'] && $pro['price'][$pCol]['price'] !== 0 ){
                    $goods['product'][$prok]['price'][$pCol]['price'] = '0';
                }
            }
            if( $pro['price']['mktprice']['price'] == '' )
                $goods['product'][$prok]['price']['mktprice']['price'] = $objPro->getRealMkt($pro['price']['price']['price']);
            $goods['product'][$prok]['price']['mktprice']['price'] = trim($goods['product'][$prok]['price']['mktprice']['price']);
            $goods['product'][$prok]['price']['cost']['price'] = trim($goods['product'][$prok]['price']['cost']['price']);
            $goods['product'][$prok]['price']['price']['price'] = trim($goods['product'][$prok]['price']['price']['price']);

            if(is_array($goods['product'][$prok]['spec_desc']['spec_value'])){
                foreach((array)$goods['product'][$prok]['spec_desc']['spec_value'] as $sk => $sv){
                    if(empty($sv)){
                        $this->splash('failed','' , app::get('b2c')->_('商品规格值不能为空'),'','',true);
                    }
                    if($goods['spec'][$sk]['spec_id'] != $sk){
                        continue;
                    }
                    $temp_spvi = $goods['product'][$prok]['spec_desc']['spec_private_value_id'][$sk];
                    if($goods['spec'][$sk]['option'][$temp_spvi]['private_spec_value_id'] != $temp_spvi){
                        continue;
                    }
                    $temp_svi = $goods['product'][$prok]['spec_desc']['spec_value_id'][$sk];
                    if($goods['spec'][$sk]['option'][$temp_spvi]['spec_value_id'] != $temp_svi){
                        continue;
                    }
                    $goods['product'][$prok]['spec_desc']['spec_value'][$sk] = $goods['spec'][$sk]['option'][$temp_spvi]['spec_value'];
                }
            }
        }

        if(is_array($data['linkid'])){
            foreach($data['linkid'] as $k => $id){
                if(!empty($goods['goods_id']))
                    $lastId = $goods['goods_id'];
                else
                    $lastId = intval($lastGoodsId)+1;
                //
                $aLink[] = array('goods_1' => $lastId, 'goods_2' => $id, 'manual' => $data['linktype'][$id], 'rate' => 100);
            }
            $goods['rate'] = $aLink;
        }
        $goods['rate'] = $aLink;

        if(is_array($data['gdlytype'])){
            $adlytype = ','.implode(',',$data['gdlytype']).',';
        }
        //$goods['dt_id'] = $adlytype;

        if( !$goods['tag'] ) $goods['tag'] = array();
        if( !$goods['adjunct'] ) $goods['adjunct'] = array();
        if( !$goods['rate'] ) $goods['rate'] = array();
        if( $goods['gain_score'] === '' ) $goods['gain_score'] = null;
        if( empty($goods['package_scale']) ) $goods['package_scale'] = '1';
        if( empty($goods['package_unit']) ) $goods['package_unit'] = '';
        if( $goods['props'] ){
            foreach( $goods['props'] as $pk => $pv ){
                if( substr($pk,2) <= 20 && $pv['value'] === '' )
                    $goods['props'][$pk]['value'] = null;
            }
        }
        $tmpProduct = $goods['product'];
        foreach( $tmpProduct as $k=>$v ) {
            if(!$k && $k!==0){
                unset($tmpProduct[$k]);
            }
        }

        if(empty($upgoods) && $tmpProduct){
            $goods['status'] = 'false';
        }
        /*
        if(isset($goods['goods_id'])){
            $temp = $objGoods->getList('goods_id',array('marketable_allow'=>'true','goods_id'=>$goods['goods_id']));
            if(!$temp) $goods['status'] = 'false';
        }*/
        return $goods;
    }

    function toAdd(){
        set_time_limit(0);
        $oStoregrade = $this->app_current->model('storegrade');
        $aGrade = $oStoregrade->getList('goods_num', array('grade_id'=>$this->grade_id));
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>(($_POST['goods_switch']&&$_POST['goods_switch']=='instock')?'goods_instock':'goods_onsell')));
        if(!$aGrade) $this->splash('failed',$url , app::get('b2c')->_('由于某种原因，您不能执行该操作！'),'','',true);
        $oGoods = &$this->app_current->model('goods');
        $filter = array('store_id'=>$this->store_id);
        if($_POST['goods']['goods_id']){
            $filter['goods_id|noequal'] = $_POST['goods']['goods_id'];
        }
        $count = $oGoods->count($filter);
        if(intval($aGrade[0]['goods_num']) && $count >= intval($aGrade[0]['goods_num'])) $this->splash('failed',$url , app::get('b2c')->_('您已有最大'.$count.'件商品，不能再添加！'),'','',true);

        $url = '';
        $oGoods = &$this->app_b2c->model('goods');
        if (!isset($_POST['goods']['category']['cat_id']) || empty($_POST['goods']['category']['cat_id'])){
            $this->splash('failed',$url , app::get('b2c')->_('商品分类不能为空'),'','',true);
        }
        if (!isset($_POST['goods']['brand']['brand_id']) || empty($_POST['goods']['brand']['brand_id'])){
            $this->splash('failed',$url , app::get('b2c')->_('商品品牌不能为空'),'','',true);
        }
        if (isset($_POST['goods']['description'])&& (empty($_POST['goods']['description']) ||$_POST['goods']['description'] == '&nbsp;')){
            $this->splash('failed',$url , app::get('b2c')->_('详细介绍内容不能为空'),'','',true);
        }
        if (isset($_POST['goods']['brief'])&&$_POST['goods']['brief']&&strlen($_POST['goods']['brief'])>210){
            $this->splash('failed',$url , app::get('b2c')->_('简短的商品介绍,请不要超过70个字！'),'','',true);
        }
        if(isset($_POST['spec_load'])){
            $this->splash('failed',$url , app::get('b2c')->_('规格未加载完毕'),'','',true);
        }
        if(isset($_POST['specall']) && !empty($_POST['specall']) || isset($_POST['spec']) && $_POST['spec']){
            if(isset($_POST['reproduct']) && !empty($_POST['reproduct'])){
                $this->splash('failed',$url , $_POST['reproduct'],'','',true);
            }
            $_POST['goods']['product'] = !empty($_POST['goods']['product'])?json_decode($_POST['goods']['product'],true):array();
        }
        if(isset($_POST['specall']) && !empty($_POST['specall'])){
            if(is_array($_POST['goods']['product'])){
                foreach($_POST['goods']['product'] as $pk=>$pv){
                    if(count($pv['spec_desc']['spec_value_id']) < count($_POST['specall'])){
                         $this->splash('failed',$url , app::get('b2c')->_('未选定全部规格'),'','',true);
                    }
                }
            }else{
                $this->splash('failed',$url , app::get('b2c')->_('未选定全部规格'),'','',true);
            }
        }elseif(isset($_POST['spec']) && $_POST['spec']){
            if(is_array($_POST['goods']['product'])){
                foreach($_POST['goods']['product'] as $pk=>$pv){
                    if(count($pv['spec_desc']['spec_value_id']) < count(unserialize($_POST['goods']['spec']))){
                         $this->splash('failed',$url , app::get('b2c')->_('未选定全部规格'),'','',true);
                    }
                }
            }else{
                $this->splash('failed',$url , app::get('b2c')->_('未选定全部规格'),'','',true);
            }
        }

        if($_POST['adjunct']['min_num'][0] > $_POST['adjunct']['max_num'][0]){
            $this->splash('failed',$url , app::get('b2c')->_('配件最小购买量大于最大购买量'),'','',true);
        }
        if(!$oGoods->checkPriceWeight($_POST['goods']['product'])){
            $this->splash('failed',$url , app::get('b2c')->_('商品价格或重量格式错误'),'','',true);
        }
        if(!$oGoods->checkStore($_POST['goods']['product'])){
            $this->splash('failed',$url , app::get('b2c')->_('库存格式错误'),'','',true);
        }

        $customhtml=$_POST['goods']['description'];
        $valite=kernel::single('business_url')->is_valid_html($customhtml);
        $img_valite=kernel::single('business_img_url')->is_valid_html($customhtml);
        // $valite = $valite && $img_valite;

        if(!$valite){
            $this->splash('failed',$url , app::get('b2c')->_('详细介绍中存在非法的图片或文字链接'),'','',true);
        }
        if(!$img_valite){
            $this->splash('failed',$url , app::get('b2c')->_('详细介绍中存在非法的图片引用地址'),'','',true);
        }

        /*$customhtml=preg_replace("/<script[^>]*>([\s\S]*?)<\/script>/i","",$customhtml);
        $burl=kernel::single('business_url');
        $customhtml=$burl->replace_html($customhtml);//非本地地址过滤
        $style=kernel::single('business_theme_widget_style');
        $customhtml=$style->prefix($customhtml,substr(md5($customhtml),0,6));//css过滤
        $_POST['goods']['description'] = $customhtml;*/

        $goods = $this->_prepareGoodsData($_POST);
        if( $goods['udfimg'] == 'true' && !$goods['thumbnail_pic'] ){
            $goods['udfimg'] = 'false';
        }

        if(is_string($_POST['productkey'])){
            $productkey = unserialize($_POST['productkey']);
            if(is_array($_POST['goods']['product'])){
                foreach($_POST['goods']['product'] as $pk => $pv){
                    $newpk[] = $pv['product_id'];
                }
            }
            if(is_array($newpk) && is_array($productkey)){
                $diff = array_diff($productkey,$newpk);
            }
            if(count($diff) > 0){
                if(!$this->pre_recycle_spec($_POST['goods']['goods_id'],$diff)){
                    $this->splash('failed',$url , app::get('b2c')->_('有的规格订单未处理'),'','',true);
                }
            }
        }

        if( count( $goods['product'] ) == 0 ){
            //$this->end(false,'货品未添加');
            exit;
        }
        if( strlen($goods['brief']) > 255 ){
            $this->splash('failed',$url , app::get('b2c')->_('商品介绍请不要超过70个汉字'),'','',true);
        }

        if( !$goods['name'] )
            $this->splash('failed',$url , app::get('b2c')->_('商品名称不能为空'),'','',true);
        if( $goods['bn']  ){
            if( $oGoods->checkProductBn($goods['bn'], $goods['goods_id']) ){
                $this->splash('failed',$url , app::get('b2c')->_('您所填写的商品编号已被使用，请检查！'),'','',true);
            }
        }

        foreach($goods['product'] as $k => $p){
            if(!$k && $k !== 0) {
                unset($goods['product'][$k]);
                continue;
            }
            if($goods['status'] != 'false' && intval($p['store']) == 0) $this->splash('failed',$url , app::get('b2c')->_('上架商品库存必须大于0'),'','',true);
            if(floatval($p['price']['price']['price']) < 0.1) $this->splash('failed',$url , app::get('b2c')->_('商品销售价必须大于等于0.1'),'','',true);
            if(floatval($p['price']['mktprice']['price']) < 0.1) $this->splash('failed',$url , app::get('b2c')->_('商品市场价必须大于等于0.1'),'','',true);
            if (is_null( $p['store'] )){$goods['product'][$k]['freez'] = null;$goods['product'][$k]['store'] = null;}
            if(empty($p['bn'])) continue;
            if($oGoods->checkProductBn($p['bn'], $goods['goods_id']) ){
                $this->splash('failed',$url , app::get('b2c')->_('您所填写的货号已被使用，请检查！'),'','',true);
            }
        }
        if(!$goods['product']) {
            unset($goods['product']);
            unset($goods['spec']);
        }

        $oUrl = kernel::single('site_route_app');

        $arr_remove_image = array();
        if( $_POST['goods']['images'] ){
            $oImage_attach = app::get('image')->model('image_attach');
            $arr_image_attach = $oImage_attach->getList('*',array('target_id'=>$goods['goods_id'],'target_type'=>'goods'));
            foreach ((array)$arr_image_attach as $_arr_image_attach){
                if (!in_array($_arr_image_attach['image_id'],$_POST['goods']['images'])){
                    $arr_remove_image[] = $_arr_image_attach['image_id'];
                }
            }
        }
        $goods['category']['cat_id'] = is_array($goods['category']['cat_id'])?0:$goods['category']['cat_id'];
        if ( !$oGoods->save($goods) ){
            $this->splash('failed',$url , app::get('b2c')->_('您所填写的货号重复，请检查！'),'','',true);
        }else{
            if( $goods['images'] ){
                $oImage = &app::get('business')->model('image');
                if ($arr_remove_image){
                    foreach($arr_remove_image as $_arr_remove_image)
                        $test = $oImage->delete_image($_arr_remove_image,'goods',$this->store_id);
                }
                foreach($goods['images'] as $k=>$v){
                    $test = $oImage->rebuild($v['image_id'],array('S','M','L'),true,$this->store_id,0);
                }
            }

            if( $_POST['goods_static'] ){
                $url = $oUrl->fetch_static( array( 'static'=>$_POST['goods_static'] ) );
                $goods_url = app::get('site')->router()->gen_url( array( 'app'=>'b2c','real'=>1,'ctl'=>'site_product','args'=>array($goods['goods_id']) ) );
                $goods_url = substr( $goods_url , strlen( app::get('site')->base_url() ) );
                $goods_url_info = $oUrl->fetch_static( array( 'static'=>$goods_url ) );
                if(empty($goods_url_info['url'])){
                    $goods_url_info['url'] = $goods_url;
                }
                $goods_url_info['static'] = $_POST['goods_static'];
                $goods_url_info['enable'] = 'true';
                if( $url['url'] && $url['url'] != $goods_url_info['url'] ){
                    $this->splash('failed',$url , app::get('b2c')->_('您填写的自定义链接已存在'),'','',true);
                }
                $oUrl->store_static( $goods_url_info );
           }else{
                $goods_url = app::get('site')->router()->gen_url( array( 'app'=>'b2c','real'=>1,'ctl'=>'site_product','args'=>array($goods['goods_id']) ) );
                $goods_url = substr( $goods_url , strlen( app::get('site')->base_url() ) );
                $oUrl->delete_static( array( 'static'=>$goods_url ) );
           }

            $data_gdlytype = array_values(array_filter($_POST['gdlytype']));
            $objGoodsDly = app::get('b2c')->model('goods_dly');
            if(is_array($data_gdlytype) && !empty($data_gdlytype)){
                $data_insert = array();
                $data_delete = array();
                $count_new = count($data_gdlytype);
                foreach((array)$objGoodsDly->getList('dly_id',array('goods_id'=>$goods['goods_id'],'manual'=>'normal'),0,-1) as $key => $rows){
                    if($key < $count_new){
                        $sql = ' update sdb_b2c_goods_dly '.
                            ' set dly_id='.intval($data_gdlytype[$key]).
                            ' where goods_id='.intval($goods['goods_id']).' and dly_id='.intval($rows['dly_id']).' and manual=\'normal\'';
                        $objGoodsDly->db->exec($sql);
                        unset($data_gdlytype[$key]);
                    }else{
                        $data_delete[] = intval($rows['dly_id']);
                    }
                }
                foreach((array)$data_gdlytype as $item){
                    if(!empty($item))$data_insert[] = '('.intval($goods['goods_id']).','.intval($item).',\'normal\')';
                }
                if(count($data_delete)){
                    $objGoodsDly->db->exec('delete from sdb_b2c_goods_dly where goods_id ='.intval($goods['goods_id']).' and dly_id in ('.implode(',',$data_delete).') and manual=\'normal\'');
                }
                if(count($data_insert)){
                    $objGoodsDly->db->exec('insert into sdb_b2c_goods_dly (goods_id,dly_id,manual) values '.implode(',',$data_insert));
                }
            }else{
                $objGoodsDly->db->exec('delete from sdb_b2c_goods_dly where goods_id ='.intval($goods['goods_id']).' and manual=\'normal\'');
            }
        }

        $_POST['goods'] = $goods;
        $goodsServiceList = kernel::servicelist("goods.action.save");
        foreach( $goodsServiceList as $aGoodsService ){
            if(!$aGoodsService->save( $_POST, $error_msg )){
                $this->end(false, $error_msg);
            }
        }

        if(app::get('base')->getConf('server.search_server.search_goods') == 'search_goods'){
            $obj = search_core::segment();
            if(search_core::instance('search_goods')->status($msg)){
                $luceneIndex = search_core::instance('search_goods')->link();
            }else{
                $luceneIndex = search_core::instance('search_goods')->create();
            }
            $luceneIndex = search_core::instance('search_goods')->update($goods);
        }

        $objBGoods = $this->app_current->model('goods');
        $objBGoodsCat = $this->app_current->model('goods_cat_conn');
        if(isset($_POST['customcatid']) && is_array($_POST['customcatid'])){

            $data = array();
            foreach($_POST['customcatid'] as $rows){
                $data[] = array('goods_id'=>$_POST['goods']['goods_id'],'cat_id'=>$rows);
            }
            if(count($data)>0){
                $objBGoodsCat->delete(array('goods_id'=>$_POST['goods']['goods_id']));
                $objBGoods->set_custom_cat($data);
            }
        }

        //$url = $this->gen_url(array('app'=>'business','ctl'=>"site_member",'act'=>"goods_onsell"));
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>(($_POST['goods_switch']&&$_POST['goods_switch']=='instock')?'goods_instock':'goods_onsell')));
        $this->splash('success',$url , app::get('b2c')->_('操作成功'),'','',true);
        //$this->end(true,app::get('b2c')->_('操作成功'),null,array('goods_id'=>$goods['goods_id'] ) );
    }

    function pre_recycle_spec($goods_id,$args){                                    //删除规格前的一些验证包括订单赠品
        $obj_check_order = kernel::single('b2c_order_checkorder');
        $objProduct = &$this->app->model('products');
        if(is_array($args)){
            foreach($args as $key => $val){
                $orderStatus = $obj_check_order->check_order_product(array('goods_id'=>$goods_id,'product_id'=>$val));
                if(!$orderStatus){
                    return false;
                }
                foreach( kernel::servicelist("b2c_allow_delete_goods") as $object ) {
                    if( !method_exists($object,'is_delete') ) continue;
                    if( !$object->is_delete($goods_id,$val) ) return false;
                }
            }

        }
        return true;
    }

    function get_subcat_list($cat_id)
    {
        $objCat = &$this->app_b2c->model('goods_cat');
        $row = $objCat->dump($cat_id);
        $path_id = explode(',',$row['cat_path']);
        array_shift($path_id);
        array_pop($path_id);
        $path_id[] = $cat_id;
        $cat_path = array();
        if($path_id){
            $filter = array('cat_id'=>$path_id);
            $cat_path = $objCat->getList('*',$filter);
        }
        $list = $objCat->get_subcat_list($cat_id);
        foreach($list as $key=>&$val){
            if($val['child_count'] > 0){
                $val['isParent'] = 'isParent';
            }else{
                //unset($list['isParent']);
            }
        }

        $count = $objCat->get_subcat_count($cat_id);
        $this->pagedata['cats'] = json_encode($list);
        if(is_array($cat_path)){
            foreach($cat_path as $ck=>$cv){
                $catPath[] = $cv['cat_id'];
            }
        }
        echo $this->pagedata['cats'];
    }

    function get_subcat($cat_id)
    {
        $data = $this->getPCCat($cat_id);
        $cat_id = $data['cat'];
        $parent_id = $data['parent'];
        $objBGoods = &$this->app_current->model('goods');

        $objCat = &$this->app_b2c->model('goods_cat');
        $row = $objCat->dump($cat_id);
        $path_id = explode(',',$row['cat_path']);
        array_shift($path_id);
        array_pop($path_id);
        $path_id[] = $cat_id;
        $cat_path = array();
        if($path_id){
            $filter = array('cat_id'=>$path_id);
            $cat_path = $objCat->getList('*',$filter,0,-1,'cat_path');
        }
        $list = $objBGoods->get_subcat_list($parent_id);
        foreach($list as $key=>&$val){
            if($val['child_count'] > 0){
                $val['isParent'] = 'isParent';
            }else{
                //unset($list['isParent']);
            }
        }
        $this->pagedata['url'] = $this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'get_subcat_list','full'=>1));
        $count = $objCat->get_subcat_count($cat_id);
        //$list[] = array('cat_id' => 0, 'cat_name' => '分类不限');
        $this->pagedata['cats'] = json_encode($list);
        if(is_array($cat_path)){
            foreach($cat_path as $ck=>$cv){
                $catPath[] = $cv['cat_id'];
            }
        }
        $this->pagedata['catPath'] = implode(',',$catPath);
        $this->page('site/goods/cat_list.html', true, 'business');
    }

    function image_del()
    {
        $image_id = $_GET['image_id'];
        $oimage = app::get('image')->model('image');
        if($oimage->delete(array('image_id'=>$image_id))){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.app::get('image')->_('删除成功').'"}';
        }
    }
    function view_gimage($image_id)
    {
        $this->pagedata['image_id'] = $image_id;
        $this->page('site/goods/gimages_view.html', true, 'business');
    }

    function dlycorp()
    {
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('默认物流公司'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $objCorp = $this->app_current->model('dlycorp');
        $self = array();
        foreach($objCorp->getList('*', array('store_id'=>$this->store_id)) as $items){
            $self[] = $items['corp_id'];
        }
        $objCorp = $this->app_b2c->model('dlycorp');
        $all_corp = $objCorp->getList('*', array('disabled'=>'false'));
        foreach($all_corp as &$item){
            if(count($self)>0 && in_array($item['corp_id'], $self)){
                $item['default'] = true;
            }else{
                $item['default'] = false;
            }
        }
        $this->pagedata['corp'] = $all_corp;
        $this->output('business');
    }

    function toAddCorp(){
        $data = array();
        if(isset($_POST['corp']) && count($_POST['corp'])>0){
            foreach($_POST['corp'] as $items){
                $data[] = array('corp_id'=>intval($items),'store_id'=>$this->store_id);
            }
        }else{
            $this->splash('failed','' , app::get('b2c')->_('没有可以保存的值'),'','',true);
        }

        $objCorp = $this->app_current->model('dlycorp');
        $objCorp->delete(array('store_id' => $this->store_id, 'corp_id|noequal'=>0));
        if ( !$objCorp->save($data) ){
            $this->splash('failed','' , app::get('b2c')->_('保存失败！'),'','',true);
        }else{
            $this->splash('success','' , app::get('b2c')->_('保存成功！'),'','',true);
        }
    }

    function dlytype($orderBy = 0, $page = 1)
    {
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('运费模板'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $this->pagedata['controller'] = 'dlycorp';
        $oDlyType = &$this->app_current->model('dlytype');
        $pageLimit = $this->app_b2c->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);
        $filter = array();
        $filter['disabled'] = 'false';
        $filter['store_id'] = $this->store_id;
        $dt_info = $oDlyType->getList('dt_id,dt_name,dt_status,protect', $filter, $pageLimit*($page-1), $pageLimit, $orderBy);
        $iCount = $oDlyType->count($filter);
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($iCount/$pageLimit),
            'link'=>  $this->gen_url(array('app'=>'business', 'ctl'=>'site_member','full'=>1,'act'=>'dlytype','args'=>array( $orderBy, ($tmp=time())))),
            'token'=>$tmp);
        if($page != 1 && $page > $this->pagedata['pager']['total']){
            $this->_response->set_http_response_code(404);
        }

        foreach($dt_info as &$items){
            $items['dt_status'] = ($items['dt_status'] == '1')?'启用':'不启用';
            $items['protect'] = ($items['protect'] == 'true')?'启用':'不启用';
        }
        $this->pagedata['dt_info'] = $dt_info;
        $pickup = &$this->app_current->model('dlycorp')->count(array('store_id' => $this->store_id, 'corp_id'=>0));
        $this->pagedata['pickup'] = $pickup;
        $this->output('business');
    }

    function dlytype_new(){
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('新增运费模板'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $this->pagedata['controller'] = 'dlycorp';
        $oDlyCorp = &$this->app_current->model('dlycorp');
        $dlycorp = $oDlyCorp->getdlycorp($this->store_id);
        $this->pagedata['weightunit'] = $this->_weightunit();
        $this->pagedata['config']=array(
            'firstunit' => '1000',
            'continueunit'=>'1000'
        );

        $this->pagedata['clist'] = $dlycorp;
        $this->pagedata['is_delivery_discount_close'] = $this->app_b2c->getConf('is_delivery_discount_close');
        $this->pagedata['arr_is_threshold_value'] = array(
            '0'=>app::get('b2c')->_('不启用'),
            '1'=>app::get('b2c')->_('启用'),
        );
        $this->output('business');
    }

    function dlytype_edit($dt_id){
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('编辑运费模板'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $this->pagedata['controller'] = 'dlycorp';
        $oDlyCorp = &$this->app_current->model('dlycorp');
        $oDlyType = &$this->app_current->model('dlytype');
        $dlycorp = $oDlyCorp->getdlycorp($this->store_id);

        $dt_info = $oDlyType->dump($dt_id);
        $dt_info['area_fee_conf'] = unserialize($dt_info['area_fee_conf']);
        $dt_info['protect_rate'] = $dt_info['protect_rate']*100;
        $tmp_threshold = array();
        if ($dt_info['is_threshold'])
        {
            if ($dt_info['threshold'])
            {
                $dt_info['threshold'] = unserialize(stripslashes($dt_info['threshold']));
                if (isset($dt_info['threshold']) && $dt_info['threshold'])
                {
                    array_shift($dt_info['threshold']);
                    foreach ($dt_info['threshold'] as $res)
                    {
                        $tmp_threshold[] = array(
                            'area'=>$res['area'][0],
                            'first_price'=>$res['first_price'],
                            'continue_price'=>$res['continue_price'],
                        );
                    }
                }
            }
        }
        $dt_info['threshold'] = $tmp_threshold;

        $this->pagedata['dt_info'] = $dt_info;
        $this->pagedata['clist'] = $dlycorp;
        $this->pagedata['weightunit'] = $this->_weightunit();
        $this->pagedata['is_delivery_discount_close'] = $this->app_b2c->getConf('is_delivery_discount_close');
        $this->pagedata['arr_is_threshold_value'] = array(
            '0'=>app::get('b2c')->_('不启用'),
            '1'=>app::get('b2c')->_('启用'),
        );

        /**
         * 扩展配送方式的信息
         */
        if ($obj_dlytype_extension = kernel::service('b2c_dlytype_fixed_time'))
        {
          $this->pagedata['extends_html'] = $obj_dlytype_extension->get_html($dt_info);
        }
        $this->output('business');
    }

    function dlytype_delete($dt_id, $mdl_name="business_mdl_dlytype")
    {
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlytype'));
        if(empty($dt_id)){
            $this->splash('failed',$url , app::get('b2c')->_('没有可以删除的数据'),'','',false);
        }

        $filter['dt_id'] = $dt_id;

        $servicelog = kernel::service('operatorlog.' . $mdl_name);
        if(method_exists($servicelog, 'logDelInfoStart')){
            $servicelog->logDelInfoStart($filter);
        }
        list($app_id,$table) = explode('_mdl_',$mdl_name);
        $o = app::get($app_id)->model($table);
        $dbschema = $o->get_schema();
        $pkey = $dbschema['idColumn'];
        $rows = $o->getList('*',$filter,0,-1);
        if(method_exists($o, 'pre_recycle')){
            if(!$o->pre_recycle($rows)){
                $this->splash('failed',$url , app::get('b2c')->_('$o->recycle_msg'),'','',false);
            }
        }
        foreach($rows as $k=>$v){
            $pkey_value = $v[$pkey];
            $o->delete(array($pkey=>$pkey_value));
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员删除操作日志，删除成功信息记录@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if(method_exists($servicelog, 'logDelInfoEnd')){
            $servicelog->logDelInfoEnd($del_flag=true);
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员删除操作日志，删除成功信息记录@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        $this->splash('success',$url , app::get('b2c')->_('删除成功'),'','',false);
    }

    function toAdddlytype(){
        $oObj = &$this->app_current->model('dlytype');
        // Make the checkbox default value.
        if (!isset($_POST['protect']))
            $_POST['protect'] = '0';
        if (!isset($_POST['def_area_fee']))
            $_POST['def_area_fee'] = '0';
        //if ($_POST['has_cod'] == '0')
            $_POST['has_cod'] = 'false';
        //else
        //    $_POST['has_cod'] = 'true';
        if (!$_POST['firstprice'])
            $_POST['firstprice'] = '0';
        if (!$_POST['continueprice'])
            $_POST['continueprice'] = '0';
        if (!$_POST['dt_useexp'])
            $_POST['dt_useexp'] = '0';
        if (!$_POST['ordernum'])
            $_POST['ordernum'] = '50';
        $_POST['store_id'] = $this->store_id;
        $is_saved = $oObj->save($_POST);
        if ($obj_dlytype_extension = kernel::service('b2c_dlytype_fixed_time')){
            $is_saved = $obj_dlytype_extension->save_dlytype($_POST);
        }
        $url = $this->gen_url(array('app'=>'business','ctl'=>"site_member",'act'=>"dlytype"));
        if (!$is_saved){
            $this->splash('failed',$url , app::get('b2c')->_('运费模板保存失败！'),'','',true);
        }else{
            $this->splash('success',$url , app::get('b2c')->_('运费模板保存成功！'),'','',true);
        }

    }
    
    function dlytype_pickup() {
        $objCorp = &$this->app_current->model('dlycorp');
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlytype'));
        $pickup = $objCorp->count(array('store_id' => $this->store_id, 'corp_id'=>0));
        if ($pickup) {
            $objCorp->delete(array('store_id' => $this->store_id, 'corp_id'=>0));
            $this->splash('success',$url , app::get('b2c')->_('停用成功！'),'','',false);
        } else {
            $objAddress = &$this->app_current->model('dlyaddress');
            $row = $objAddress->count(array('store_id'=>$this->store_id, 'pickup'=>'true'));
            if (!$row) {
                $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlyaddress'));
                $this->splash('failed',$url , app::get('b2c')->_('请先设定自提地址！'),'','',false);
            }
            
            $data = array();
            $data []= array('store_id' => $this->store_id, 'corp_id'=>0);
            if ($objCorp->save($data)) {
                $this->splash('success',$url , app::get('b2c')->_('启用成功！'),'','',false);
            }else{
                $this->splash('failed',$url , app::get('b2c')->_('启用失败！'),'','',false);
            }
        }
    }

    function _weightunit(){
        return array(
            "500"=>app::get('b2c')->_("500克"),
            "1000"=>app::get('b2c')->_("1公斤"),
            "1200"=>app::get('b2c')->_("1.2公斤"),
            "2000"=>app::get('b2c')->_("2公斤"),
            "5000"=>app::get('b2c')->_("5公斤"),
            "10000"=>app::get('b2c')->_("10公斤"),
            "20000"=>app::get('b2c')->_("20公斤"),
            "50000"=>app::get('b2c')->_("50公斤")
        );
    }

    function checkExp(){
        $oObj = &$this->app_b2c->model('dlytype');
        $this->pagedata['expressions'] = $_GET['expvalue'];
        $this->display('site/member/check_exp.html','business');
    }

    function dlyaddress($orderBy=1,$da_id=0){
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('地址库'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $this->pagedata['controller'] = 'dlycorp';
        $oObj = &$this->app_current->model('dlyaddress');
        $pageLimit = $this->app_b2c->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);$pageLimit= 3;
        $filter = array();
        $filter['store_id'] = $this->store_id;
        $filter['disabled'] = 'false';
        /*
        $da_info = $oObj->getList('*', $filter, $pageLimit*($page-1), $pageLimit, $orderBy);
        $iCount = $oObj->count($filter);
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($iCount/$pageLimit),
            'link'=>  $this->gen_url(array('app'=>'business', 'ctl'=>'site_member','full'=>1,'act'=>'dlyaddress','args'=>array( $orderBy, ($tmp=time())))),
            'token'=>$tmp);
        if($page != 1 && $page > $this->pagedata['pager']['total']){
            $this->_response->set_http_response_code(404);
        }*/
        $da_info = $oObj->getList('*', $filter, 0, -1, $orderBy);
        $single = array();
        $this->pagedata['orderBy'] = $orderBy;
        foreach($da_info as &$items){
            if($da_id && $da_id == $items['da_id']){
                $single = $items;
            }
            if($items['consign'] == 'true') $items['consign'] = 1;
            else $items['consign'] = 0;
            if($items['refund'] == 'true') $items['refund'] = 1;
            else $items['refund'] = 0;
            if($items['pickup'] == 'true') $items['pickup'] = 1;
            else $items['pickup'] = 0;
            list($a,$adr,$b)=explode(':',$items['region']);
            $items['region'] = $adr;
        }
        $this->pagedata['dlyaddress'] = $single;
        $this->pagedata['da_list'] = $da_info;
        $this->output('business');
    }

    function toAdddlyaddress(){
        $url = $this->gen_url(array('app'=>'business','ctl'=>"site_member",'act'=>"dlyaddress"));
        $data = $_POST;
        $oObj = $this->app_current->model('dlyaddress');
        if(empty($data['da_id'])){
            unset($data['da_id']);
        }
        $count = $oObj->count(array('store_id'=>$this->store_id,'consign'=>'true'));
        if(intval($count) > 0){
            if(!isset($data['da_id']))$data['consign'] = 'false';
        }else{
            $data['consign'] = 'true';
        }
        $count = $oObj->count(array('store_id'=>$this->store_id,'refund'=>'true'));
        if(intval($count) > 0){
            if(!isset($data['da_id']))$data['refund'] = 'false';
        }else{
            $data['refund'] = 'true';
        }
        if(empty($data['phone']) && empty($data['mobile'])){
            $this->splash('failed','' , app::get('b2c')->_('联系电话和联系手机必须填写一项！'),'','',true);
        }
        $data['store_id'] = $this->store_id;

        if(!$oObj->save($data)){
            $this->splash('failed',$url , app::get('b2c')->_('地址信息保存失败！'),'','',true);
        }else{
            $this->splash('success',$url , app::get('b2c')->_('地址信息保存成功！'),'','',true);
        }
    }

    function dlyaddress_update_consign($da_id){
        $oObj = &$this->app_current->model('dlyaddress');
        $da_ids = array();
        foreach($oObj->getList('da_id', array('store_id'=>$this->store_id)) as $rows){
            $da_ids[] = $rows['da_id'];
        }
        if(count($da_ids) > 0)
        $oObj->db->exec("update sdb_business_dlyaddress set consign='false' where da_id in ('".implode("','",$da_ids)."')");
        $oObj->db->exec("update sdb_business_dlyaddress set consign='true' where da_id = ".intval($da_id));
        //error_log($da_id,3,'e:/1.txt');
    }

    function dlyaddress_update_refund($da_id){
        $oObj = &$this->app_current->model('dlyaddress');
        $da_ids = array();
        foreach($oObj->getList('da_id', array('store_id'=>$this->store_id)) as $rows){
            $da_ids[] = $rows['da_id'];
        }
        if(count($da_ids) > 0)
        $oObj->db->exec("update sdb_business_dlyaddress set refund='false' where da_id in ('".implode("','",$da_ids)."')");
        $oObj->db->exec("update sdb_business_dlyaddress set refund='true' where da_id = ".intval($da_id));
    }

    function dlyaddress_update_pickup($da_id){
        $oObj = &$this->app_current->model('dlyaddress');
        $row = $oObj->getList('pickup', array('da_id'=>$da_id));
        if (count($row) && $row[0]['pickup'] == 'true') {
            $oObj->db->exec("update sdb_business_dlyaddress set pickup='false' where da_id = ".intval($da_id));
        } else {
            $oObj->db->exec("update sdb_business_dlyaddress set pickup='true' where da_id = ".intval($da_id));
        }
    }

    function dlyaddress_delete($da_id, $mdl_name="business_mdl_dlyaddress")
    {
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlyaddress'));
        if(empty($da_id)){
            $this->splash('failed',$url , app::get('b2c')->_('没有可以删除的数据'),'','',false);
        }

        $filter['da_id'] = $da_id;

        $servicelog = kernel::service('operatorlog.' . $mdl_name);
        if(method_exists($servicelog, 'logDelInfoStart')){
            $servicelog->logDelInfoStart($filter);
        }
        list($app_id,$table) = explode('_mdl_',$mdl_name);
        $o = app::get($app_id)->model($table);
        $dbschema = $o->get_schema();
        $pkey = $dbschema['idColumn'];
        $rows = $o->getList('*',$filter,0,-1);
        if(method_exists($o, 'pre_recycle')){
            if(!$o->pre_recycle($rows)){
                $this->splash('failed',$url , app::get('b2c')->_('$o->recycle_msg'),'','',false);
            }
        }
        foreach($rows as $k=>$v){
            $pkey_value = $v[$pkey];
            $o->delete(array($pkey=>$pkey_value));
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员删除操作日志，删除成功信息记录@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if(method_exists($servicelog, 'logDelInfoEnd')){
            $servicelog->logDelInfoEnd($del_flag=true);
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员删除操作日志，删除成功信息记录@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        $this->splash('success',$url , app::get('b2c')->_('删除成功'),'','',false);
    }

    public function get_goods_info()
    {
        $data = $_POST['data'];
        $arr = app::get('b2c')->model('goods')->getList( '*',array('goods_id'=>$data[0]) );
        reset( $arr );
        $arr = current($arr);
        echo json_encode( array('name'=>$arr['name'],'price'=>$arr['price'],'store'=>(INT)$arr['store'],'goods_id'=>$arr['goods_id'],'image'=>$arr['image_default_id'], 'brief'=>$arr['brief']) );
    }

    function object_rows(){
        if($_POST['data']){
            if($_POST['app_id'])
                $app = app::get($_POST['app_id']);
            else
                $app = $this->app_current;
            $obj = $app->model($_POST['object']);
            $schema = $obj->get_schema();
            $textColumn = $_POST['textcol']?$_POST['textcol']:$schema['textColumn'];
            $textColumn = explode(',',$textColumn);
            $_textcol = $textColumn;
            $textColumn = $textColumn[0];

            $keycol = $_POST['key']?$_POST['key']:$schema['idColumn'];

            //统一做掉了。
            $all_filter = !empty($obj->__all_filter) ? $obj->__all_filter : array();
            $filter = !empty($_POST['filter']) ? $_POST['filter'] : $all_filter;
            $arr_filter = array();
            if( $_POST['data'][0]==='_ALL_' ) {
                if (isset($filter['advance'])&&$filter['advance']){
                    $arr_filters = explode(',',$filter['advance']);
                    foreach ($arr_filters as $obj_filter){
                        $arr = explode('=',$obj_filter);
                        $arr_filter[$arr[0]] = $arr[1];
                    }
                    unset($filter['advance']);
                }
            }else{
                $arr_filter = array_merge($filter,array($keycol=>$_POST['data']));
            }

            $items = $obj->getList('*', $arr_filter);
            $name = $items[0][$textColumn];
            if($_POST['type']=='radio'){
                if(strpos($textColumn,'@')!==false){
                    list($field,$table,$app_) = explode('@',$textColumn);
                    if($app_){
                        $app = app::get($app_);
                    }
                    $mdl = $app->model($table);
                    $schema = $mdl->get_schema();
                    $row = $mdl->getList('*',array($schema['idColumn']=>$items[0][$keycol]));
                    $name = $row[0][$field];

                }
                echo json_encode(array('id'=>$items[0][$keycol],'name'=>$name));
                exit;
            }

            $this->pagedata['_input'] = array('items'=>$items,
                                                'idcol' => $schema['idColumn'],
                                                'keycol' => $keycol,
                                                'textcol' => $textColumn,
                                                '_textcol' => $_textcol,
                                                'name'=>$_POST['name']
                                                );
            $this->pagedata['_input']['view_app'] = 'desktop';
            $this->pagedata['_input']['view'] = $_POST['view'];
            if($_POST['view_app']){
                $this->pagedata['_input']['view_app'] =  $_POST['view_app'];
            }

            if(strpos($_POST['view'],':')!==false){
                list($view_app,$view) = explode(':',$_POST['view']);
                $this->pagedata['_input']['view_app'] = $view_app;
                $this->pagedata['_input']['view'] = $view;

            }

            $this->display('site/tools/input-row.html','business');
        }
    }

    public function get_related_product($current_goods_id){
        $filter = array();
        //$current_goods_id = $_GET['p'][0];
        if (!$current_goods_id || !$_POST['data'] || !$_POST['filter']) return '';
        $obj_goods = $this->app_b2c->model('goods');

        $filter = $_POST['filter'];
        if (isset($_POST['data'][0])&&$_POST['data'][0]=='_ALL_'){
            if (isset($filter['advance'])&&$filter['advance']){
                $arr_filters = explode(',',$filter['advance']);
                foreach ($arr_filters as $obj_filter){
                    $arr = explode('=',$obj_filter);
                    $filter[$arr[0]] = $arr[1];
                }
                unset($filter['advance']);
            }
        }else{
            $filter = array_merge($filter,array($obj_goods->idColumn=>$_POST['data']));
        }
        $arr_goods = $obj_goods->getList('goods_id,name',$filter);
        if (!$arr_goods) return '';
        /** 查找相关商品 **/
        $render = kernel::single('base_render');
        $render->pagedata['goods_items'] = $arr_goods;
        $obj_goods_rate = $this->app_b2c->model('goods_rate');
        $arr_goods_rates = $obj_goods_rate->db->select("SELECT `goods_2`,`manual` FROM ".$obj_goods_rate->table_name(1)." WHERE `goods_1` IN (".$current_goods_id.")");
        if ($arr_goods_rates){
            $arr_goods_rates_ids = (array)array_map('current',(array)$arr_goods_rates);
            foreach ((array)$arr_goods as $k=>$_new_goods_id){
                if (($key=array_search($_new_goods_id['goods_id'], $arr_goods_rates_ids)) !== false){
                    $render->pagedata['goods_items'][$k]['manual'] = $arr_goods_rates[$key]['manual'];
                }
            }
        }

        $render->pagedata['desktop_res_url'] = app::get('desktop')->res_url;
        header('Content-Type:text/html; charset=utf-8');
        //echo $render->fetch('site/goods/ajax_rel_items.html','business');exit;
        $this->page('site/goods/ajax_rel_items.html', true, 'business');
    }

    function getMember(){
        if($this->app_b2c->member_id){
            echo json_encode(array('status'=>'true'));
        }else{
            echo json_encode(array('status'=>'false'));
        }
    }

    function showfilter($type_id){
        $store_id = $this->store_id;
        $region_id = $this->region_id;
        $objBGoods = &app::get('business')->model('goods');
        $obj_cat = &app::get('b2c')->model('goods_cat');
        $aCatId = $objBGoods->getCats($region_id);
        $catinfo = $obj_cat->getList('type_id', array('cat_id|in'=>$aCatId['cat_id']));
        $type_ids = array();
        foreach($catinfo as $items){
            $type_ids[] = $items['type_id'];
        }

        $goods_filter = kernel::single('business_member_goodsfilter');
        $return = $goods_filter->member_goodsfilter($type_id,$aCatId['cat_id'],$type_ids,app::get('b2c'));
        $this->pagedata['filter'] = $return;
        if($this->issue_type == 1 || $this->issue_type == 3){
            $render->pagedata['filter']['brands'] = $this->store_brand;
        }
        $this->pagedata['filter_interzone'] = $_POST;
        $this->pagedata['view'] = $_POST['view'];
        $this->display('admin/goods/filter_addon.html');
    }

    /**
     * Member order list datasource
     * @params int equal to 1
     * @return null
     */
    public function seller_order($type='',$order_id='',$goods_name='',$goods_bn='',$time='',$pay_status='',$nPage=1)
    {
        //进入页面是需要调用订单操作脚本
        $sto= kernel::single("business_memberstore",$this->app_b2c->member_id);
        $sto->process($this->app_b2c->member_id);
        $store_id = $sto->storeinfo['store_id'];
        //kernel::single('b2c_orderautojob')->order_auto_operation('',$store_id);
      
        $this->path[] = array('title'=>app::get('business')->_('卖家中心'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('我的订单'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $order = app::get('business')->model('orders');

        $order_id = trim($order_id);
        $goods_name = trim($goods_name);
        $goods_bn = trim($goods_bn);
		$sql = $this->get_search_order_ids($type,$time);
		$arrayorser = $order->db->select($sql);

		$search_order=$this->search_order($order_id,$goods_name,$goods_bn);
		$arr;
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
		}else{
			$aData = $order->fetchByShop($this->app->member_id,$store_id,$nPage-1,'','',$arrayorser);
		}


        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $imageDefault = app::get('image')->getConf('image.set');

        $obj_product = app::get('aftersales')->model('return_product');
        foreach($aData['data'] as $k=>$v) {
            $returns = $obj_product->getList('*',array('order_id'=>$v['order_id']));
            if($v['refund_status'] == '5' && $v['refund_status'] != '8' && $v['refund_status'] != '9'){
                $r = $obj_product->getRow('return_id',array('order_id'=>$v['order_id'],'refund_type'=>'2','status'=>'12'));
                $aData['data'][$k]['return_id'] = $r['return_id'];
            }
            if($v['refund_status'] == '8'){
                $r = $obj_product->getRow('return_id',array('order_id'=>$v['order_id'],'is_intervene'=>'2'));
                $aData['data'][$k]['return_id'] = $r['return_id'];
            }
            if($v['refund_status'] == '11'){
                $r = $obj_product->getRow('return_id',array('order_id'=>$v['order_id'],'status'=>'15'));
                $aData['data'][$k]['return_id'] = $r['return_id'];
            }
            foreach($returns as $k3=>$v3) {
                if($v3['refund_type'] == '1' && $v3['status'] == '1'){
                    $aData['data'][$k]['is_cancel'] = '1';
                }
                if($v3['refund_type'] == '2' && $v3['status'] == '1'){
                    $aData['data'][$k]['is_return'] = '1';
                }
                if($v3['refund_type'] == '3' && $v3['status'] == '1'){
                    $aData['data'][$k]['is_returns'] = '1';
                }
                if($v3['refund_type'] == '4' && $v3['status'] == '1'){
                    $aData['data'][$k]['no_need_returns'] = '1';
                }
            }
            foreach($v['goods_items'] as $k2=>$v2) {
                if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aData['data'][$k]['goods_items'][$k2]['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
            }

            //获取买家/卖家
            $obj_members = app::get('pam')->model('account');
            $buy_name = $obj_members->getRow('login_name',array('account_id'=>$v['member_id']));
            $aData['data'][$k]['buy_name'] = $buy_name['login_name'];

            $obj_strman = app::get('business')->model('storemanger');
            $seller_id = $obj_strman->getRow('account_id,store_idcardname',array('store_id'=>$v['store_id']));
            $seller_name = $obj_members->getRow('login_name',array('account_id'=>$seller_id['account_id']));
            $aData['data'][$k]['seller_name'] = $seller_name['login_name'];
            $aData['data'][$k]['seller_real_name'] = $seller_id['store_idcardname'];
            
            foreach(kernel::servicelist('business.member_orders') as $service){
                if(is_object($service)){
                    if(method_exists($service,'get_seller_orders_html')){
                        $aData['data'][$k]['html'] .= $service->get_seller_orders_html($v);
                    }
                    if(method_exists($service,'get_orders_status_html')){
                        $aData['data'][$k]['status_html'] .= $service->get_orders_status_html($v, 'seller');
                    }
                }
            }
        }
        //echo '<pre>';print_r($aData['data']);exit;
		$this->pagedata['msg']=$msg;
        $this->pagedata['orders'] = $aData['data'];

        //下拉框数据 --start
        $this->pagedata['select']['time']['options'] = $this->get_select_date();
        $this->pagedata['select']['time']['value'] = $time;
        //下拉框数据 --end

		//获取传过来的参数
        $this->pagedata['type'] =$type;
		$this->pagedata['order_id'] = $order_id;
		$this->pagedata['goods_name'] = $goods_name;
		$this->pagedata['goods_bn'] = $goods_bn;
		$this->pagedata['time'] = $time;

        $arr_args = array($type,$order_id,$goods_name,$goods_bn,$time,$pay_status);
        $this->pagination($nPage,$aData['pager']['total'],'seller_order',$arr_args,$app_id='business');
        $this->pagedata['res_url'] = $this->app_b2c->res_url;

        $this->output('business');
    }

    /**
    * 订单的搜素
    * @params order_id：订单号,goods_name：商品名称,goods_bn：商品编号
    * @return array
    */
    private function search_order($order_id,$goods_name,$goods_bn){
        //防止SQL注入
        $order_id = mysql_real_escape_string($order_id);
        $goods_name = mysql_real_escape_string($goods_name);
        $goods_bn = mysql_real_escape_string($goods_bn);

		$sto= kernel::single("business_memberstore",$this->app_b2c->member_id);
        $sto->process($this->app_b2c->member_id);
        $store_id = $sto->storeinfo['store_id'];

        $sdb = kernel::database()->prefix;
        $strsql="select distinct order_id from ".$sdb."b2c_orders where store_id='".$store_id."' and order_id in ";

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

        $arr_order_id= $order = &$this->app->model('orders')->db->select($strsql);

        return $arr_order_id;
    }

    /**
    * 动态获取选择的时间
    * @return array
    */
    private function get_select_date(){

       $year = date('Y',time());
       $options = array();

       $options['all'] = "全部时间";
       $options['3th'] = "三个月内";
       $options['6th'] = "半年内";
       $options[$year] = "今年内";
       $options['1'] = "1年以前";

       return $options;
    }


    /**
    *根据时间筛选订单
    * @return array
    */
    private function get_search_order_ids($type='',$time=''){

         //解析时间
        $year = date('Y',time());
        $sdb = kernel::database()->prefix;

		$sto= kernel::single("business_memberstore",$this->app_b2c->member_id);
        $sto->process($this->app_b2c->member_id);
        $store_id = $sto->storeinfo['store_id'];

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
			$type_sql=" pay_status='0' and status='active' ";
		}else if($type == 'ship'){
			$type_sql=" pay_status='1' and ship_status='0' ";
		}else if($type == 'finish'){
			$type_sql=" status='finish' ";
		}else if($type == 'dead'){
			$type_sql=" status='dead' ";
		}else if($type == 'shiped'){
			$type_sql=" pay_status='1' and ship_status='1' and status='active'";//待收货
		}else if($type == 'refunded'){
			$type_sql=" refund_status <> '0' and refund_status <> '2' and refund_status <> '4'";
		}else{
			$type_sql=' 1=1 ';
		}


        $str_sql = "SELECT order_id FROM ".$sdb."b2c_orders WHERE store_id=".$store_id;

        $str_sql.=" AND ". $time_sql.' AND '.$type_sql;


        return $str_sql;

    }


    function index() {
        $this->path[] = array('title'=>app::get('business')->_('店铺管理'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->title=app::get('business')->_('店铺管理'). '_' .app :: get('b2c')-> getConf('system.shopname');
        $GLOBALS['runtime']['path'] = $this->path;
        $oMem = &$this->app->model('members');
        $oRder = &$this->app->model('orders');
        $oMem_lv = $this->app->model('member_lv');
        $this->pagedata['switch_lv'] = $oMem_lv->get_member_lv_switch($this->member['member_lv']);
        $order = $oRder->getList('*',array('member_id' => $this->app->member_id));
        $order_total = count($order);
        $aInfo = $oMem->dump($this->app->member_id);
        $order = &$this->app->model('orders');
        $aData = $order->fetchByMember($this->app->member_id,$nPage-1);
        $this->get_order_details($aData, 'member_latest_orders');

        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];
        #获取咨询评论回复
        $obj_mem_msg = kernel::single('b2c_message_disask');
        $this->member['unreadmsg'] = $obj_mem_msg->calc_unread_disask($this->member['member_id']);

        $obj_product = app::get('aftersales')->model('return_product');
        foreach($aData['data'] as $k=>$v) {
            $returns = $obj_product->getList('*',array('order_id'=>$v['order_id']));
            foreach($returns as $k3=>$v3) {
                if($v3['refund_type'] == '1' && $v3['status'] == '1'){
                    $aData['data'][$k]['is_cancel'] = '1';
                }
                if($v3['refund_type'] == '2' && $v3['status'] == '1'){
                    $aData['data'][$k]['is_return'] = '1';
                }
                if($v3['refund_type'] == '3' && $v3['status'] == '1'){
                    $aData['data'][$k]['is_returns'] = '1';
                }
                if($v3['refund_type'] == '4' && $v3['status'] == '1'){
                    $aData['data'][$k]['no_need_returns'] = '1';
                }
            }
            foreach($v['goods_items'] as $k2=>$v2) {
                if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aData['data'][$k]['goods_items'][$k2]['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
            }
        }

        $this->pagedata['orders'] = $aData['data'];
        $this->pagedata['pager'] = $aData['pager'];
        // 额外的会员的信息 - 冻结积分、将要获得的积分
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_point($this->member['member_id']);
            }
        }
        // 判断是否开启预存款
        $_mdl_payment_cfgs = app::get('ectools')->model('payment_cfgs');
        $_payment_info = $_mdl_payment_cfgs->getPaymentInfo('deposit');
        if($_payment_info['app_staus'] == app::get('ectools')->_('开启'))
        $this->pagedata['deposit_status'] = 'true';

          //获取店铺信息
        $sto= kernel::single("business_memberstore",$this->member['member_id']);
        $sto->process($this->member['member_id']);
        if($sto->isshopmember=='true'){
            $this->member['storeinfo'] = $sto->storeinfo[0];
        } else {
            $this->member['storeinfo'] = $sto->storeinfo;
        }
        $this->member['isshoper']= $sto->isshoper;
        $this->member['isshopmember']= $sto->isshopmember;


        //print_r($sto->storeinfo);exit;

        $this->pagedata['member'] = $this->member;
        $this->pagedata['total_order'] = $order_total;
        $this->pagedata['aNum']=$aInfo['advance']['total'];$this->set_tmpl('member');
        $obj_member = &$this->app->model('member_goods');
        $aData_fav = $obj_member->get_favorite($this->app->member_id,$this->member['member_lv']);
        $this->pagedata['favorite'] = $aData_fav['data'];
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $rule = kernel::single('b2c_member_solution');
        $this->pagedata['wel'] = $rule->get_all_to_array($this->member['member_lv']);
        $this->pagedata['res_url'] = $this->app->res_url;//echo '<pre>';print_r($sto->storeinfo['store_id']);exit;
        //获取店铺的动态评分
        $objComment = app::get('business')->model('comment_stores_point');
        if($sto->storeinfo['store_id']){
        $store_info = $objComment->getStoreInfo($sto->storeinfo['store_id']);
        $this->pagedata['store_info'] = $store_info;
        }

        if(!$this->store_id){
            $this->store_id=-1;

        }
        //官方信息
        $content = app::get('content')->model('article_indexs');
        $contents = $content->getList('*',array('node_id'=>11,'ifpub'=>'true'),0,7);
        $this->pagedata['contents'] = $contents;
        //促销活动
		  $mdl_package = app::get('package')->model('activity');
		  $mdl_timedbuy = app::get('timedbuy')->model('activity');
		  $mdl_spike = app::get('spike')->model('activity');
	      $mdl_groupbuy = app::get('groupbuy')->model('activity');
	      $mdl_scorebuy = app::get('scorebuy')->model('activity');
        //捆绑活动
        $store_region_package = $this->region_id;

        $package_activityInfo = $mdl_package->getList('*',array('act_open'=>'true'));
        $now = time();
        foreach($package_activityInfo as $k=>$v){
            if($now > $v['end_time'] || empty($v['business_type'])){
                unset($package_activityInfo[$k]);
                continue;
            }
            $businee_type =array_filter(explode(',',$v['business_type']));
            $ret = array_intersect($businee_type,$store_region_package);
            if(!$ret){
                unset($package_activityInfo[$k]);
                continue;
            }
        }
        foreach($package_activityInfo as $key=>$value){
            $package_activityInfo[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
       //限时抢购
	    $member_id = $this->member['member_id'];
        $sto= kernel::single("business_memberstore",$this->member['member_id']);        
        $store_cat = $sto->storeinfo['issue_type'];
        $store_grade = $sto->storeinfo['store_grade'];
        $storemember = app::get('business')->model('storemember');
        $storemanger = app::get('business')->model('storemanger');
        $smemberInfo = $storemember->getList('store_id',array('member_id'=>$member_id));
        if($smemberInfo){
            $shopInfo = $storemanger->getList('store_region',array('store_id'=>$smemberInfo[0]['store_id']));
        }else{
            $shopInfo = $storemanger->getList('store_region',array('account_id'=>$member_id));
        }
        if($shopInfo){
            $store_region = $shopInfo[0]['store_region'];
			$store_region = array_filter(explode(',',$store_region));
        }
		
        $timedbuy_activityInfo = $mdl_timedbuy->getList('*',array('act_open'=>'true'));
		$now = time();
		foreach($timedbuy_activityInfo as $k=>$v){
			if($now > $v['end_time']){
				unset($timedbuy_activityInfo[$k]);
			}
		}
		foreach($timedbuy_activityInfo as $k=>$v){
			if($store_region){
				$businee_type = array();
				$businee_type =array_filter(explode(',',$v['business_type']));
				$ret = array_intersect($businee_type,$store_region);
				if(!$ret){
					unset($timedbuy_activityInfo[$k]);
				}
			}

            $timedbuy_act_store_cat = explode(',',$v['store_type']);
            if(!in_array($store_cat,$timedbuy_act_store_cat)){
                unset($timedbuy_activityInfo[$k]);
            }

            $timedbuy_act_store_grade = array_filter(explode(',',$v['store_lv']));
            if(!in_array($store_grade,$timedbuy_act_store_grade)){
                unset($timedbuy_activityInfo[$k]);
            }
		}
        foreach($timedbuy_activityInfo as $key=>$value){
            $timedbuy_activityInfo[$key]['start_time'] = date('Y-m-d',$value['start_time']);
            $timedbuy_activityInfo[$key]['end_time'] = date('Y-m-d',$value['end_time']);
        }
		//团购
        $nowTime = time();
        $filter = array(
                    'act_open'=>'true',
                    'apply_start_time|sthan'=>$nowTime,
                    'apply_end_time|bthan'=>$nowTime
                );
        $groupbuy_activityInfo = $mdl_groupbuy->getList('*',$filter);
        $now = time();
        foreach($groupbuy_activityInfo as $k=>$v){
            if($now > $v['end_time']){
                unset($groupbuy_activityInfo[$k]);
            }
        }

        foreach($groupbuy_activityInfo as $k=>$v){
            if($store_region){
                $businee_type = array();
                $businee_type =array_filter(explode(',',$v['business_type']));
                $ret = array_intersect($businee_type,$store_region);
                if(!$ret){
                    unset($groupbuy_activityInfo[$k]);
                }
            }
            
            $groupbuy_act_store_cat = explode(',',$v['store_type']);
            if(!in_array($store_cat,$groupbuy_act_store_cat)){
                unset($groupbuy_activityInfo[$k]);
            }

            $groupbuy_act_store_grade = array_filter(explode(',',$v['store_lv']));
            if(!in_array($store_grade,$groupbuy_act_store_grade)){
                unset($groupbuy_activityInfo[$k]);
            }
        }
        foreach($groupbuy_activityInfo as $key=>$value){
            $groupbuy_activityInfo[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
		//积分换购
        $scorebuy_activityInfo = $mdl_scorebuy->getList('*',$filter);
        $now = time();
        foreach($scorebuy_activityInfo as $k=>$v){
            if($now > $v['end_time']){
                unset($scorebuy_activityInfo[$k]);
            }
        }
        foreach($scorebuy_activityInfo as $k=>$v){
            if($store_region){
                $businee_type = array();
                $businee_type =array_filter(explode(',',$v['business_type']));
                $ret = array_intersect($businee_type,$store_region);
                if(!$ret){
                    unset($scorebuy_activityInfo[$k]);
                }
            }
        }
        foreach($scorebuy_activityInfo as $key=>$value){
            $scorebuy_activityInfo[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }
     //秒杀活动
	    $spike_activityInfo = $mdl_spike->getList('*',$filter);
        $now = time();
        foreach($spike_activityInfo as $k=>$v){
            if($now > $v['end_time']){
                unset($spike_activityInfo[$k]);
            }
        }
        foreach($spike_activityInfo as $k=>$v){
            if($store_region){
                $businee_type = array();
                $businee_type =array_filter(explode(',',$v['business_type']));
                $ret = array_intersect($businee_type,$store_region);
                if(!$ret){
                    unset($spike_activityInfo[$k]);
                }
            }

            $act_store_cat = explode(',',$v['store_type']);
            if(!in_array($store_cat,$act_store_cat)){
                unset($spike_activityInfo[$k]);
            }

            $act_store_grade = array_filter(explode(',',$v['store_lv']));
            if(!in_array($store_grade,$act_store_grade)){
                unset($spike_activityInfo[$k]);
            }
        }
        foreach($spike_activityInfo as $key=>$value){
            $spike_activityInfo[$key]['start_time'] = date('Y-m-d',$value['start_time']);
        }

		  $activityinfo=array_merge($package_activityInfo,$timedbuy_activityInfo,$spike_activityInfo,$groupbuy_activityInfo,$scorebuy_activityInfo);

		  $this->pagedata['activityinfo']=$activityinfo;
		
 
        //等待上架的宝贝

        $objGoods = app::get('business')->model('goods');
        $goods =  $objGoods->getList('goods_id',array('marketable'=>'false','store_id'=>$this->store_id));
        $this->pagedata['wait_marketable_num'] = count($goods);
        //出售中的宝贝
        $onsell_goods = $objGoods->getList('goods_id',array('marketable'=>'true','store_id'=>$this->store_id));
        $this->pagedata['onsell_goods_num'] = count($onsell_goods);
        //需要优化的宝贝
        $alert_num = $this->app->getConf('system.product.alert.num');
        $obj_strman = app::get('business')->model('storemanger');
        $alert_num = $obj_strman->getList('alert_num', array('store_id'=>$this->store_id));
        $alert_num = intval($alert_num[0]['alert_num']);
        $this->pagedata['alert_goods_num'] = $objGoods->count(array('marketable'=>'true','store_id'=>$this->store_id,'store|sthan'=>$alert_num));
        // 待评论的宝贝
        $goods = $objGoods->db->selectrow("select count(i.item_id) as _count from sdb_b2c_order_items as i join sdb_b2c_orders as o on i.order_id=o.order_id and o.store_id=".$this->store_id." and ifnull(o.comments_count,0)=0 ");

        //print_r($goods);
        $this->pagedata['ncomment_goods_num'] = $goods['_count'];
        //退款中的订单
        $refundeds = $oRder->getList('order_id',array('refund_status|notin'=>array('0','2','4'),'store_id'=>$this->store_id));
        $this->pagedata['refunded_order_num'] = count($refundeds);
        //待发货的订单
        $ships = $oRder->getList('order_id',array('pay_status'=>'1','ship_status'=>'0','store_id'=>$this->store_id));

        $this->pagedata['ship_order_num'] = count($ships);

       
        //违规记录
        $objViolation = &app::get('business')->model('storeviolation');
        //一般违规累计扣分
        $this->pagedata['storeviolation']['total0'] = $objViolation ->getscore($this->store_id,'0');
        //严重违规累计扣分
        $this->pagedata['storeviolation']['total1'] = $objViolation ->getscore($this->store_id,'1');

        //评价
        $time= time()- 30*24*60*60;
        $this->pagedata['lasttime'] =date ('Y年m月',$time);
        $firstday = date('Y-m-01', $time);
        $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));



        //删除的评价
        $filter =array('item_type'=>'member_comments','app_key'=>'b2c','drop_time|than'=>$time);
        $objrecycle =  &app::get('desktop')->model('recycle');

        //test
        $sid =$this->store_id;
        //$sid='3';

        $strF="s:".strlen($sid).":\"".$sid."\";%'";
        //test
        $filter['filter_sql']="item_sdf LIKE '%\"store_id\";".$strF;

        $recycle = $objrecycle->getList('*',$filter);
        //最近30天
        $this->pagedata['member_comments_num1']=$recycle?count($recycle):0;

        //上个月
        $xfilter =array('item_type'=>'member_comments','app_key'=>'b2c','drop_time|between'=>array(strtotime($firstday),strtotime($lastday)));
        $xfilter['filter_sql']= $filter['filter_sql'];
        $lastrecycle = $objrecycle->getList('*',$xfilter);
        $this->pagedata['member_comments_num2']=$lastrecycle?count($lastrecycle):0;

        //评价总数
        $objcomment =  &app::get('b2c')->model('member_comments');
        $mfilter =array('object_type'=>'discuss','time|than'=>$time,'store_id'=>$this->store_id);
        $comments = $objrecycle->getList('*',$mfilter);
        $comments =$comments?count($comments):0;
         //最近30天
        $this->pagedata['member_comments_count1']=strval($comments)+strval($this->pagedata['member_comments_num1']);

        //上个月
        $xmfilter =array('object_type'=>'discuss','time|between'=>array(strtotime($firstday),strtotime($lastday)),'store_id'=>$this->store_id);
        $lastcomments = $objrecycle->getList('*',$xmfilter);
        $lastcomments = $lastcomments?count($lastcomments):0;
        $this->pagedata['member_comments_count2']=strval($lastcomments)+ strval($this->pagedata['member_comments_num2']);


        //原始中差评
        $arycomments = $objcomment->getcommentsbystoreid($this->store_id,4,$time);
        $arycomments =$arycomments?count($arycomments):0;
        $this->pagedata['comments_count1']=strval($arycomments)+strval( $this->pagedata['member_comments_num1']);

        $xarycomments = $objcomment->getcommentsbystoreid($this->store_id,4,strtotime($firstday),strtotime($lastday));
        $xarycomments =$xarycomments?count($xarycomments):0;
        $this->pagedata['comments_count2']=strval($xarycomments)+strval($this->pagedata['member_comments_num2']);


        //近期服务情况
        $objorder =  &app::get('b2c')->model('orders');

        //----------------------------------退款

        //30天
        //退款订单：
        $orderfilter =array('pay_status'=>array('4','5'),'status|noequal'=>'dead','createtime|than'=>$time,'store_id'=>$this->store_id);
        $orders=$objorder->getList('order_id',$orderfilter);
        $this->pagedata['refunds_count1']=$orders?count($orders):0;

        //退款天数
        $aryrefunds = $objorder ->getrefundsbystoreid($this->store_id,$time);
        $this->pagedata['refunds_days1']=$aryrefunds[0]['days']?$aryrefunds[0]['days']:0;

        //纠纷退款
        $iaryrefunds = $objorder ->getxrefundsbystoreid($this->store_id,$time,null);
        $this->pagedata['refunds_icount1']=$iaryrefunds?count($iaryrefunds):0;


        //上个月
        //上个月退款订单：
        $lastorderfilter =array('pay_status'=>array('4','5'),'status|noequal'=>'dead','createtime|between'=>array(strtotime($firstday),strtotime($lastday)),'store_id'=>$this->store_id);
        $lastorders=$objorder->getList('order_id',$lastorderfilter);
        $this->pagedata['refunds_count2']=$lastorders?count($lastorders):0;

        $xaryrefunds = $objorder ->getrefundsbystoreid($this->store_id,strtotime($firstday),strtotime($lastday));
        $this->pagedata['refunds_days2']=$xaryrefunds[0]['days']?$xaryrefunds[0]['days']:0;

        //上个月纠纷退款
        $xiaryrefunds = $objorder ->getxrefundsbystoreid($this->store_id,strtotime($firstday),strtotime($lastday));
        $this->pagedata['refunds_icount2']=$xiaryrefunds?count($xiaryrefunds):0;

        //退款率

        //30天所有订单
        $ofilter = array('status|noequal'=>'dead','createtime|than'=>$time,'store_id'=>$this->store_id);
        $objorders = $objorder->getList('order_id',$ofilter);
        $countorder=$objorders?count($objorders):0;
        if( $countorder>0){
            //退款率
            $this->pagedata['refunds_lv1']=(round(strval($this->pagedata['refunds_count1'])/ $countorder,0)) * 100;

            //纠纷退款率
            $this->pagedata['refunds_ilv1']=(round(strval($this->pagedata['refunds_icount1'])/strval($this->pagedata['refunds_count1']),0)) * 100;
        } else {
            $this->pagedata['refunds_lv1']= 0;
            $this->pagedata['refunds_ilv1']= 0;
        }

        //上个月所有订单
        $xofilter =array('status|noequal'=>'dead','createtime|between'=>array(strtotime($firstday),strtotime($lastday)),'store_id'=>$this->store_id);
        $xobjorders = $objorder->getList('*',$xofilter);
        $xcountorder=$xobjorders?count($xobjorders):0;
        if($xcountorder>0){
            //退款率
            $this->pagedata['refunds_lv2']=(round(strval($this->pagedata['refunds_count2'])/$xcountorder,0)) * 100;
             //纠纷退款率
             $this->pagedata['refunds_ilv2']=(round(strval($this->pagedata['refunds_icount2'])/strval($this->pagedata['refunds_count2']),0)) * 100;
        } else {
            $this->pagedata['refunds_lv2']= 0;
            $this->pagedata['refunds_ilv2']= 0;
        }

        //-------------------------------投诉
        $objcomplain =  &app::get('complain')->model('complain');
        $cfilter=array('store_id'=>$this->store_id,'createtime|than'=>$time,'status'=>'success');
        $arycomplains = $objcomplain->getList('*',$cfilter);
        $this->pagedata['complains_count1']=$arycomplains?count($arycomplains):0;

        $xcfilter =array('store_id'=>$this->store_id,'createtime|between'=>array(strtotime($firstday),strtotime($lastday)));
        $xarycomplains = $objcomplain->getList('*',$xcfilter);
        $this->pagedata['complains_count2']=$xarycomplains?count($xarycomplains):0;



        //-------------------------------获取同行店铺ID
        $objstoremanger =  &app::get('business')->model('storemanger');
        $regionary= $objstoremanger->getcounteridbystoreid($this->store_id);

        //30天
        $counter_refunds = $objorder ->getcounterxrefundsbystoreid(implode($regionary,','),$time);
        $this->pagedata['counter_refunds_count1']=$counter_refunds?count($counter_refunds):0;
        $daycounter_refunds = $objorder ->getcounterrefundsbystoreid(implode($regionary,','),$time);
        $this->pagedata['counter_refunds_days1']=$daycounter_refunds[0]['days']>0?$daycounter_refunds[0]['days']:0;

        //上个月
        $xcounter_refunds = $objorder ->getcounterxrefundsbystoreid(implode($regionary,','),strtotime($firstday),strtotime($lastday));
        $this->pagedata['counter_refunds_count2']=$xcounter_refunds?count($xcounter_refunds):0;
        $dayxcounter_refunds = $objorder ->getcounterrefundsbystoreid(implode($regionary,','),strtotime($firstday),strtotime($lastday));
        $this->pagedata['counter_refunds_days2']=$dayxcounter_refunds[0]['days']>0?$dayxcounter_refunds[0]['days']:0;

        

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        $this->pagedata['_PAGE_'] = 'index.html';
        $this->output();
    }


    protected function output($app_id = 'business') {
        $this -> pagedata['member'] = $this -> member;


        $this -> pagedata['cpmenu'] = $this -> get_cpmenu();
        $this -> pagedata['top_menu'] = $this -> get_headmenu();

        $this -> pagedata['current'] = $this -> action;



        if ($this -> pagedata['_PAGE_']) {
            $this -> pagedata['_PAGE_'] = 'site/'.$this->cur_view.'/' . $this -> pagedata['_PAGE_'];
        } else {
            $this -> pagedata['_PAGE_'] = 'site/'.$this->cur_view.'/' . $this -> action_view;
        }


        foreach(kernel :: servicelist('member_index') as $service) {
            if (is_object($service)) {
                if (method_exists($service, 'get_member_html')) {
                    $aData[] = $service -> get_member_html();
                }
            }
        }

        $this -> pagedata['app_id'] = $app_id;
        $this -> pagedata['_MAIN_'] = 'site/member/main.html';
        $this -> pagedata['get_member_html'] = $aData;
        $member_goods = $this -> app_b2c -> model('member_goods');

        $this -> pagedata['sto_goods_num'] = $member_goods -> get_goods($this -> member['member_id']);
        // 获取待付款订单数
        $orders = $this -> app_b2c -> model('orders');
        $un_pay_orders = $orders -> getList('order_id', array('member_id' => $this -> member['member_id'], 'pay_status' => 0, 'status' => 'active'));
        $this -> member['un_pay_orders'] = count($un_pay_orders);
        // 获取回复信息
        $mem_msg = $this -> app_b2c -> model('member_comments');
        $object_type = array('msg', 'discuss', 'ask');
        $aData = $mem_msg -> getList('*', array('to_id' => $this -> member['member_id'], 'for_comment_id' => 'all', 'object_type' => $object_type, 'has_sent' => 'true', 'inbox' => 'true', 'mem_read_status' => 'false', 'display' => 'true'));
        unset($mem_msg);
        $this -> member['un_readmsg'] = count($aData);
        $this -> pagedata['member'] = $this -> member;

        $this -> set_tmpl('member');
        $this -> page('site/member/main.html', false, $app_id);
    }


     private function get_cpmenu(){
        // 判断是否开启预存款
        $mdl_payment_cfgs = app::get('ectools')->model('payment_cfgs');
        $payment_info = $mdl_payment_cfgs->getPaymentInfo('deposit');
        $arr_blance = array();
        $arr_recharge_blance = array();
        $arr_point_history = array();
        $arr_point_coupon_exchange = array();
        $this->pagedata['point_usaged'] = "false";

        if ($payment_info['app_staus'] == app::get('ectools')->_('开启'))
        {
            $arr_blance = array('label'=>app::get('b2c')->_('我的预存款'),'app'=>'b2c','ctl'=>'site_member','link'=>'balance');
            $arr_recharge_blance = array('label'=>app::get('b2c')->_('预存款充值'),'app'=>'b2c','ctl'=>'site_member','link'=>'deposit');
        }

        $site_get_policy_method = $this->app->getConf('site.get_policy.method');
        if ($site_get_policy_method != '1')
        {
            $arr_point_history = array('label'=>app::get('b2c')->_('我的积分'),'app'=>'b2c','ctl'=>'site_member','link'=>'my_point');
            $arr_point_coupon_exchange = array('label'=>app::get('b2c')->_('积分兑换优惠券'),'app'=>'b2c','ctl'=>'site_member','link'=>'couponExchange');
            $this->pagedata['point_usaged'] = "true";
        }



        $arr_bases = array(
            array('label'=>app::get('b2c')->_('我是买家'),
            'mid'=>0,
            'items'=>array(
                        array('label'=>app::get('b2c')->_('我的订单'),'app'=>'b2c','ctl'=>'site_member','link'=>'orders'),
                        $arr_point_history,
                        $arr_point_coupon_exchange,
                        array('label'=>app::get('b2c')->_('我的优惠券'),'app'=>'b2c','ctl'=>'site_member','link'=>'coupon'),
                        $arr_blance,
                        $arr_recharge_blance,
			            array('label'=>app::get('b2c')->_('到货通知'),'app'=>'b2c','ctl'=>'site_member','link'=>'notify'),
			            array('label'=>app::get('b2c')->_('我的咨询'),'app'=>'b2c','ctl'=>'site_member','link'=>'ask'),
			            array('label'=>app::get('b2c')->_('我的评论'),'app'=>'business','ctl'=>'site_comment','link'=>'selfdiscuss'),
			            array('label'=>app::get('b2c')->_('最近购买的商品'),'app'=>'b2c','ctl'=>'site_member','link'=>'buy'),
			            array('label'=>app::get('b2c')->_('个人信息'),'app'=>'b2c','ctl'=>'site_member','link'=>'setting'),
                        array('label'=>app::get('b2c')->_('修改密码'),'app'=>'b2c','ctl'=>'site_member','link'=>'security'),
                        array('label'=>app::get('b2c')->_('收货地址'),'app'=>'b2c','ctl'=>'site_member','link'=>'receiver'),

            )
        ),
        );


        $obj_menu_extends = kernel::servicelist('business.member_menu_extends');
        if ($obj_menu_extends)
        {
            foreach ($obj_menu_extends as $obj)
            {
                if (method_exists($obj, 'get_extends_menu'))
                    $obj->get_extends_menu($arr_bases, array('0'=>'business', '1'=>'site_member', '2'=>'index'));
            }
        }
       
        $obj_member = app :: get('b2c') -> model('members');
        $omember = $obj_member -> get_current_member();
        $oMsg = kernel::single('b2c_message_msg');
        $no_read = $oMsg->getList('*',array('to_id' => $omember['member_id'],'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $no_read = count($no_read);
        if($arr_bases){
            foreach($arr_bases as &$v){
                foreach($v['items'] as &$v1){
                    if($v1['link']=='store_msg'){
                        $v1['label'] = app::get('b2c')->_('站内信').'('.$no_read.')';
                    }
                }
            }
        }
        //--end
        return $arr_bases;
    }



    private function get_headmenu() {
        /**
         * 会员中心的头部连接
         */
        $arr_main_top = array('member_center' => array('label' => app :: get('b2c') -> _('会员首页'),
                'app' => 'b2c',
                'ctl' => 'site_member',
                'link' => 'index',
                'args' => array(),
                ),
            'logout' => array('label' => app :: get('b2c') -> _('退出'),
                'app' => 'b2c',
                'ctl' => 'site_passport',
                'link' => 'logout',
                'args' => array(),
                ),
            'orders_nopayed' => array('label' => app :: get('b2c') -> _('待付款订单'),
                'app' => 'b2c',
                'ctl' => 'site_member',
                'link' => 'orders',
                'args' => array('nopayed'),
                ),
            'member_notify' => array('label' => app :: get('b2c') -> _('到货通知'),
                'app' => 'b2c',
                'ctl' => 'site_member',
                'link' => 'notify',
                'args' => array(),
                ),
            'member_comment' => array('label' => app :: get('b2c') -> _('到货通知'),
                'app' => 'b2c',
                'ctl' => 'site_member',
                'link' => 'comment',
                'args' => array(),
                ),
            );

        $obj_menu_extends = kernel :: servicelist('b2c.member_menu_extends');
        if ($obj_menu_extends) {
            foreach ($obj_menu_extends as $obj) {
                if (method_exists($obj, 'get_extends_top_menu'))
                    $obj -> get_extends_top_menu($arr_main_top, array('0' => 'b2c', '1' => 'site_member', '2' => 'index'));
            }
        }
        return $arr_main_top;
    }

    /**
     * Generate the order detail
     * @params string order_id
     * @return null
     */
    public function orderdetail($order_id=0)
    {
        if (!isset($order_id) || !$order_id)
        {
            $this->begin(array('app' => 'b2c','ctl' => 'site_member', 'act'=>'index'));
            $this->end(false, app::get('b2c')->_('订单编号不能为空！'));
        }

        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, '*', $subsdf);
        $objMath = kernel::single("ectools_math");

        $sto= kernel::single("business_memberstore",$this->app->member_id);
        $sto->process($this->app_b2c->member_id);

        if(!$sdf_order||$sto->storeinfo['store_id']!=$sdf_order['store_id']){
            $this->_response->set_http_response_code(404);
            $this->_response->set_body(app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
            return;
        }
        if($sdf_order['member_id']){
            $member = &$this->app->model('members');
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

        // 订单的相关信息的修改
        $obj_other_info = kernel::servicelist('b2c.order_other_infomation');
        if ($obj_other_info)
        {
            foreach ($obj_other_info as $obj)
            {
                $this->pagedata['discount_html'] = $obj->gen_point_discount($sdf_order);
            }
        }
        foreach(kernel::servicelist('business.member_orders') as $service){
            if(is_object($service)){
                if(method_exists($service,'get_orders_status_html')){
                    $sdf_order['status_html'] .= $service->get_orders_status_html($sdf_order, 'sellerdetail');
                }
            }
        }
        $sdf_order['isNeedAddress'] = true;
        $sdf_order['isNeedDelivery'] = true;
        if (isset($sdf_order['order_kind']) && $sdf_order['order_kind'] == '3rdparty') {
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if ($processor->goodsKindDetail() == $sdf_order['order_kind_detail'] && $processor->isCustom('order_delivery')) {
                    $sdf_order['isNeedAddress'] = $processor->isNeedAddress();
                    $sdf_order['isNeedDelivery'] = $processor->isNeedDelivery();
                    break;
                }
            }
        }
        $this->pagedata['order'] = $sdf_order;

        $order_items = array();
        $gift_items = array();
        $this->get_order_detail_item($sdf_order,'member_order_detail');
        $this->pagedata['order'] = $sdf_order;

        /** 去掉商品优惠 **/
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
        /** end **/

        // 得到订单留言.
        $oMsg = &kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');

        $this->pagedata['ordermsg'] = $arrOrderMsg;
        $this->pagedata['res_url'] = $this->app->res_url;

        // 生成订单日志明细
        //$oLogs =&$this->app->model('order_log');
        //$arr_order_logs = $oLogs->getList('*', array('rel_id' => $order_id));
        $arr_order_logs = $objOrder->getOrderLogList($order_id);

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        $this->pagedata['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($this->pagedata['order']['payinfo']['pay_app_id']);

        //物流跟踪安装并且开启
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        $logisticst_service = kernel::service('b2c_change_orderloglist');
        if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
            $this->pagedata['services']['logisticstack'] = $logisticst_service;
        }

        $this->pagedata['orderlogs'] = $arr_order_logs['data'];
        // 添加html埋点
        foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'fetchHtml') ) {
                    $services->fetchHtml($this,$order_id,'site/invoice_detail.html');
                }
            }
        }
        $this->output('business');
    }

    public function seller_returns_reship($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('卖家中心'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('售后服务列表'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $clos = "return_id,order_id,add_time,status,member_id,is_intervene";
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

        $filter["refund_type"] = '2';
        //添加过滤条件
        $sto= kernel::single("business_memberstore",$this->app->member_id);
        $sto->process($this->app_b2c->member_id);
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
        $obj_account = app::get('pam')->model('account');
        $order_obj = app::get('b2c')->model('orders');
        //添加用户名
        foreach($aData['data'] as $key=>$val){
            $uname = $obj_account->getRow('login_name',array('account_id'=>$val['member_id']));
            $aData['data'][$key]['uname'] = $uname['login_name'];
            $status = $order_obj->dump(array('order_id'=>$val['order_id']),'refund_status');
            $aData['data'][$key]['refund_status'] = $status['refund_status'];
        }
        if (isset($aData['data']) && $aData['data'])
            $this->pagedata['return_list'] = $aData['data'];

        $arrPager = $this->get_start($nPage, $aData['total']);
        $this->pagination($nPage, $arrPager['maxPage'], 'seller_returns_reship', '', 'business', 'site_member');

        $this->output('business');
    }

    public function seller_returns_refund($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('卖家中心'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('售后服务列表'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $clos = "return_id,order_id,add_time,status,member_id,is_intervene";
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

        $filter["refund_type|in"] = array('1','3','4');

        //添加过滤条件
        $sto= kernel::single("business_memberstore",$this->app->member_id);
        $sto->process($this->app_b2c->member_id);
        $filter["store_id"] = $sto->storeinfo['store_id'];
		$this->begin($this->gen_url(array('app' => 'business', 'ctl' => 'site_member')));
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
        $this->pagination($nPage, $arrPager['maxPage'], 'seller_returns_refund', '', 'business', 'site_member');

        $this->output('business');
    }

    function checkroles(){

        $strArgs= explode('_',get_class($this));
        if(count($strArgs)>3)
        {
           $app=$strArgs[0];
           for ($i = 2; $i < count($strArgs); $i++) {
                    $ctl.=$strArgs[$i].'_';
           }

           $ctl=substr($ctl, 0,strlen($ctl)-1);

           $action=   $this->_request->get_act_name();

        }


        //非菜单项方法跳过
        $objstoreroles = &app :: get('business') -> model('storeroles');

        $permission = "app=" . $app . "&ctl=" . $ctl . "&act=" . $action;

        $treedata =$objstoreroles -> get_cpmenu();

        foreach($treedata as $item) {
             foreach($item['items'] as $k => $t) {
                 $menus[]="app=" . $t['app'] . "&ctl=" . $t['ctl'] . "&act=" . $t['link'] ;
             }
        }

        //非菜单项不做检查
        if(!in_array($permission,$menus)){
            return;
        }

        $sto= kernel::single("business_memberstore",$this->app_b2c->member_id);
        $sto->process($this->app_b2c->member_id);
        $data = $sto->storeinfo;


        if($sto->isshoper == 'true'){

          $arrayexit=array('business','storeinfo','editstore','store','storeapplystep1',
                           'storeapplystep2','storeapplystep3','storeapplystep4','storeapplyend',
                           'storeapplyredirect','earnestdeposit','setting','security','checkbrandstore','emailcheck');

          if(in_array($action,$arrayexit)){
             return;
          }

          switch($data['approved']){

                case '0':
                    $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您的店铺正在审核中，请稍后再试！'));
                    break;

                case '2':
                    $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您的店铺未通过审核中，请与管理中心联系！'));
                    break;

            }

            if($data['status']=='0'){
               $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您的店铺未开启，请与管理中心联系！'));
            }

            if($data['last_time'] && $data['last_time']<date()){
               $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您的店铺已过期，请与管理中心联系！'));
            }

            //店铺违规处理
            //限制发布商品 limit_goods
            if($data['limit_goods']=='1' && ( $action=='goods_add' || $action=='goods_import')){
              $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)), app::get('b2c')->_('您的店铺因违规被处理，请与管理中心联系！'));
            }

            //下架所有商品 limit_goodsdown

            //商品降权 limit_news

            //店铺屏蔽 limit_store
            if($data['limit_store']=='1' &&  $action=='store_msg' ){
              $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)), app::get('b2c')->_('您的店铺因违规被处理，请与管理中心联系！'));
            }

            //关闭店铺
            if($data['limit_storedown']=='1'){
              $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)), app::get('b2c')->_('您的店铺因违规被关闭，请与管理中心联系！'));
            }

            //限制参加营销活动
             if($data['limit_sales']=='1' && ($action=='' ||$action=='')){
              $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)), app::get('b2c')->_('您的店铺因违规被关闭，请与管理中心联系！'));
            }

            //扣除违约金 limit_earnest




        }elseif($sto->isshopmember == 'true'){
            $roles_id =$data[0]['roles_id'];
            $workgrounds =$data[0]['workground'];
            $workground = unserialize($workgrounds);
            $permission = "app=" . $app . "&ctl=" . $ctl . "&act=" . $action;

            $arrayexit=array('namecheck','insert_rec','modify_stroemember','modify_storeroles','save_storeroles',
                             'del_storeroles','del_stroemember','role_namecheck','addcoupon','del_storecoupon','edit_storecoupon',
                             'download_storecoupon','modify_stroeroles','save_roles',
                             'dlyaddress','toAdddlyaddress','toAddCorp','dlytype','dlytype_edit','dlytype_delete','toAdddlytype');

            if (in_array($permission, $workground) or  $permission =='app=business&ctl=site_member&act=index' or in_array($action,$arrayexit)) {

                return;

            } else { //print_r($permission);exit;
                 $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您没有相应的权限，请与店主联系！'));
            }

        }else{

            if($ctl=='site_store' ){
                $arrayexit=array('storeapplystep1','storeapplystep2','storeapplystep3',
                              'storeapplystep4','getMember','idcardCheck','idcardcheck','setting','security','checkbrandstore','emailcheck');

                if(in_array($action,$arrayexit)){
                    return;
                }

            } else if($ctl=='site_member') {
                 $arrayexit=array('object_rows','getMember','idcardCheck','idcardcheck','setting','security','checkbrandstore','emailcheck');

                if(in_array($action,$arrayexit)){
                    return;
                }

            }

           if(($ctl=='site_member' &&  $action='index' ) or ($ctl=='site_store' &&  $action='storemember')) {
               $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_storeapply', 'act' => 'index', 'full' => 1)), app::get('b2c')->_('您还未入驻商城，请先提交申请！'));
           }
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
        $this->pagedata['refund_type'] = $refund_type;
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
        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($this->pagedata['return_products'][0]['return_id']);

        $this->pagedata['now_time'] = time()*1000;
        $this->pagedata['time'] = ($this->pagedata['return_products'][0]['add_time']+(app::get('b2c')->getConf('member.to_agree'))*86400)*1000;

        //添加退货地址显示
        $obj_address = app::get('business')->model('dlyaddress');
        $address = $obj_address->getList('*',array('da_id'=>$this->pagedata['return_item']['refund_address']));
        $ads = explode(':',$address['0']['region']);
        $address['0']['region'] = $ads[1];
        $this->pagedata['address'] = $address['0'];
        //添加确认收到退货按钮
        $sto= kernel::single("business_memberstore",$this->member['member_id']);
        $sto->process($this->member['member_id']);
        $store_id = $sto->storeinfo['store_id'];
        if($store_id == $this->pagedata['return_item']['store_id'] && $this->pagedata['return_item']['status'] == '已退货'){
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

        //添加退款日志
        $obj_return_log = app::get('aftersales')->model('return_log');
        $log_info = $obj_return_log->getList('*',array('order_id'=>$order_id),-1,-1,'alttime DESC');
        //echo "<pre>";print_r($log_info);exit;
        $this->pagedata['log_info'] = $log_info;
        //echo '<pre>';print_r($this->pagedata['return_products']);exit;
        $this->output('business');
    }

    public function js_function_do_agree(){
        //进入页面是需要调用订单操作脚本
        kernel::single('b2c_orderautojob')->order_do_agree($_POST['return_id'],$_POST['order_id']);
        
    }

    public function js_function_do_agrees(){
        //进入页面是需要调用订单操作脚本
        kernel::single('b2c_orderautojob')->order_do_agrees($_POST['return_id'],$_POST['order_id']);
       
    }

    public function js_function_do_refund_pass(){
        //进入页面是需要调用订单操作脚本
        kernel::single('b2c_orderautojob')->order_do_refund_pass($_POST['return_id'],$_POST['order_id']);
     
    }

    public function js_function_do_refund_agrees(){
        //进入页面是需要调用订单操作脚本
        kernel::single('b2c_orderautojob')->order_do_refund_agrees($_POST['return_id'],$_POST['order_id']);
       
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
        $this->pagedata['now_time'] = time()*1000;
        $this->pagedata['time'] = ($return_products[0]['add_time']+(app::get('b2c')->getConf('member.to_agree'))*86400)*1000;
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
        $sto->process($this->member['member_id']);
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

        $this->output('business');
    }

    public function return_details($return_id)
    {
        $this->begin($this->gen_url(array('app' => 'business', 'ctl' => 'site_member')));
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
        $sto->process($this->member['member_id']);
        $store_id = $sto->storeinfo['store_id'];

        $obj_orders = app::get('b2c')->model('orders');
        $obj_return_p = app::get('aftersales')->model('return_product');
        $order_id = $obj_return_p->dump(array('return_id'=>$this->pagedata['return_item']['return_id']));
        $order_info = $obj_orders->dump(array('order_id'=>$order_id['order_id']));

        if($store_id == $this->pagedata['return_item']['store_id'] && $this->pagedata['return_item']['status'] == '已退货' && $this->pagedata['return_item']['refund_type'] == '2' && $order_info['refund_status'] == '5'){
            $this->pagedata['is_shop'] = true;
        }else{
            $this->pagedata['is_shop'] = false;
        }

        $this->pagedata['return_id'] = $return_id;
        if( !($this->pagedata['return_item']) )
        {
           $this->begin($this->gen_url(array('app' =>'business','ctl'=>'site_member','act'=>'seller_order')));
           $this->end(false, $this->app->_("售后服务申请单不存在！"));
        }
        $this->pagedata['refund_type'] = $this->pagedata['return_item']['refund_type'];
        $this->pagedata['now_time'] = time()*1000;
        $this->pagedata['time'] = $this->pagedata['return_item']['close_time']*1000;

        $this->pagedata['now_time_do_return'] = time()*1000;
        $this->pagedata['time_do_return'] = ($this->pagedata['return_item']['add_time']+(app::get('b2c')->getConf('member.to_agree'))*86400)*1000;

        //添加退款日志
        $obj_return_log = app::get('aftersales')->model('return_log');
        $log_info = $obj_return_log->getList('*',array('order_id'=>$order_id['order_id']),-1,-1,'alttime DESC');
        //echo "<pre>";print_r($log_info);exit;
        $this->pagedata['log_info'] = $log_info;

        $this->output('business');
    }


     function setting(){
        $this->path[] = array('title'=>app::get('b2c')->_('卖家中心'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('个人信息'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $member_model = &$this->app_b2c->model('members');
        $mem = $member_model->dump($this->app->member_id);
        $cur_model = app::get('ectools')->model('currency');
        $cur = $cur_model->curAll();
        foreach((array)$cur as $item){
           $options[$item['cur_code']] = $item['cur_name'];
        }


        $cur['options'] = $options;
        $cur['value'] = $mem['currency'];
        $this->pagedata['currency'] = $cur;
        $mem_schema = $member_model->_columns();
        $attr =array();
            foreach($this->app_b2c->model('member_attr')->getList() as $item){
            if($item['attr_show'] == "true") $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item){
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath){
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1){
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                    foreach($a_temp  as $value){
                        $name .= '['.$value.']';
                    }
                }
            }else{
                $name = $item['attr_column'];
            }
            if($item['attr_group'] == 'defalut'){
             switch($attr[$key]['attr_column']){
                    case 'area':
                    $attr[$key]['attr_value'] = $mem['contact']['area'];
                    break;
                     case 'birthday':
                    $attr[$key]['attr_value'] = $mem['profile']['birthday'];
                    break;
                    case 'name':
                    $attr[$key]['attr_value'] = $mem['contact']['name'];
                    break;
					
					case 'nickname':
                    $attr[$key]['attr_value'] = $mem['nickname'];
					break;
					case 'idcard':
                    $attr[$key]['attr_value'] = $mem['idcard'];
					break;
					
                    case 'mobile':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['mobile'];
                    break;
                    case 'tel':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['telephone'];
                    break;
                    case 'zip':
                    $attr[$key]['attr_value'] = $mem['contact']['zipcode'];
                    break;
                    case 'addr':
                    $attr[$key]['attr_value'] = $mem['contact']['addr'];
                    break;
                    case 'sex':
                    $attr[$key]['attr_value'] = $mem['profile']['gender'];
                    break;
                    case 'pw_answer':
                    $attr[$key]['attr_value'] = $mem['account']['pw_answer'];
                    break;
                    case 'pw_question':
                    $attr[$key]['attr_value'] = $mem['account']['pw_question'];
                    break;
                   }
           }
          if($item['attr_group'] == 'contact'||$item['attr_group'] == 'input'||$item['attr_group'] == 'select'){
              $attr[$key]['attr_value'] = $mem['contact'][$attr[$key]['attr_column']];
              if($item['attr_sdfpath'] == ""){
              $attr[$key]['attr_value'] = $mem[$attr[$key]['attr_column']];
              if($attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_value'] = unserialize($mem[$attr[$key]['attr_column']]);
              }
          }
          }

          $attr[$key]['attr_column'] = $name;
          if($attr[$key]['attr_column']=="birthday"){
              $attr[$key]['attr_column'] = "profile[birthday]";
          }

          if($attr[$key]['attr_type'] =="select" ||$attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
          }

        }
        $this->pagedata['attr'] = $attr;
        $this->pagedata['email'] = $mem['contact']['email'];
		$this->pagedata['mobile'] = $mem['contact']['phone']['mobile'];

        //print_r($this->pagedata);exit;
        $this->output();
    }

     /*
        过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
    */
    function check_input($data){
        $aData = $this->arrContentReplace($data);
        return $aData;
    }


    function save_setting(){
        $url = $this->gen_url(array('app'=>'business','ctl'=>"site_member",'act'=>"setting"));
        $member_model = &$this->app_b2c->model('members');
        foreach($_POST as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $_POST[$aTmp[1]] = serialize($val);
            }
        }

        $_POST = $this->check_input($_POST);
        
        $aMem = $member_model->dump($this->app->member_id,'*',array(':account@pam'=>array('*')));
        $use_pass_data['login_name'] = $aMem['pam_account']['login_name'];
        $use_pass_data['createtime'] = $aMem['pam_account']['createtime'];
        if((empty($_POST['contact']['passwd']))||(pam_encrypt::get_encrypted_password(trim($_POST['contact']['passwd']),pam_account::get_account_type($this->app->app_id),$use_pass_data) != $aMem['pam_account']['login_password'])){
            $this->splash('failed', '', app::get('b2c')->_('密码错误，请输入正确的密码'),'','',true);
        } else {
            unset($_POST['contact']['passwd']);
        }

        if($_POST['contact']['email']&&$member_model->is_exists_email($_POST['contact']['email'],$this->app->member_id)){
            $this->splash('failed',$url , app::get('b2c')->_('邮箱已经存在'),'','',true);
        }

        if($_POST['contact']['phone']['mobile'] && !preg_match('/^1[3458][0-9]{9}$/',$_POST['contact']['phone']['mobile'])){
            $this->splash('failed',$url , app::get('b2c')->_('手机输入格式不正确'),'','',true);
        }

        //--防止恶意修改
        $arr_colunm = array('contact','profile','pam_account','currency');
        $attr = $this->app->model('member_attr')->getList('attr_column');
        foreach($attr as $attr_colunm){
            $colunm = $attr_colunm['attr_column'];
            $arr_colunm[] = $colunm;
        }
        foreach($_POST as $post_key=>$post_value){
            if( !in_array($post_key,$arr_colunm) ){
                unset($_POST[$post_key]);
            }
        }
        //---end

        $_POST['member_id'] = $this->app->member_id;

		//同步到ucenter yindingsheng
		if( $member_object = kernel::service("uc_user_edit")) {
			if(!$member_object->uc_user_edit($_POST)){
				$this->splash('failed',$url , app::get('b2c')->_('提交失败'),'','',true);
			}
		}
		//同步到ucenter yindingsheng

        if($member_model->save($_POST)){

            //增加会员同步 2012-05-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->modifyActive($_POST['member_id']);
            }

            $this->splash('success', $url , app::get('b2c')->_('提交成功'),'','',true);
        }else{
            $this->splash('failed',$url , app::get('b2c')->_('提交失败'),'','',true);
        }
    }

     function security($type = ''){
        $this->path[] = array('title'=>app::get('b2c')->_('卖家中心'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('修改密码'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $obj_member = &$this->app->model('members');
        $this->pagedata['mem'] = $obj_member->dump($this->app->member_id);
        $this->pagedata['type'] = $type;
        $this->output();
    }

    function save_security(){
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'security'));
        $obj_member = &$this->app_b2c->model('members');
        $result = $obj_member->save_security($this->app->member_id,$_POST,$msg);

        if($result){
            $this->splash('success',$url,$msg,'','',true);
        }
        else{
            $this->splash('failed',$url,$msg,'','',true);
        }
    }

    function busydiscuss($nPage=1, $filter=null){
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('来自买家的评论'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        if(isset($filter['change'])){
            $this->pagedata['myfilter'] = $filter['change'];
            unset($filter['change']);
        }

        $objDisask = kernel::single('business_message_disask');
        $objGoods = $this->app_current->model('goods');
        $objPoint = $this->app_current->model('comment_goods_point');

        $this->pagedata['_all_point'] = $objPoint->get_business_point($this->store_id);

        $aData = $objDisask->get_business_disask($this->store_id,$nPage,'discuss',$filter);
        $comment=array();
        $imageDefault = app::get('image')->getConf('image.set');
        $oImage = app::get('image')->model('image');
        foreach((array)$aData['data'] as $k => $v){
            $goods_data = $objGoods->getList('name,price,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$v['type_id']));
            if(!$goods_data) continue;
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['image_default_id']))){
                $goods_data[0]['image_default_id'] = $imageDefault['S']['default_image'];
            }
            if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['thumbnail_pic']))) {
                $goods_data[0]['thumbnail_pic'] = $imageDefault['S']['default_image'];
            }
            $v['goods_point'] = $objPoint->get_single_point($v['type_id']);
            $v['name'] = $goods_data[0]['name'];
            $v['price'] = $goods_data[0]['price'];
            $v['thumbnail_pic'] = $goods_data[0]['thumbnail_pic'];
            $v['udfimg'] = $goods_data[0]['udfimg'];
            $v['image_default_id'] = $goods_data[0]['image_default_id'];
            $comment[] = $v;
        }
        $this->pagedata['commentList'] = $comment;
        $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagination($nPage,$aData['page'],'busydiscuss','','business',$ctl='site_member');
        $this->pagedata['_PAGE_'] = 'bdiscuss.html';
        $this->output('business');
    }

    function comment_filter(){
        $filter = array();
        switch($_GET['arg0']){
            case '0':
            $filter['change'] = $_GET['arg0'];
            $filter['comment'] = '';
            break;
            case '1':
            $filter['change'] = $_GET['arg0'];
            $filter['comment|noequal'] = '';
            break;
            case '2':
            $filter['change'] = $_GET['arg0'];
            $filter['comments_type'] = '3';
            break;
            default:
            break;
        }
        if($_GET['arg1'] == 'discuss') $this->busydiscuss(1, $filter);
    }

    function return_refuse(){
        $rp = app::get('aftersales')->model('return_product');
        $returns = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
        $this->pagedata['money'] = $returns['amount'];
        $this->pagedata['return_id'] = $_POST['return_id'];
        $this->output('business');
    }
    function goods_import($nPage){
        $obj_tpl=kernel::single('business_goods_import_tpl');
        $_POST['store_id']=$this->store_id;
        $aData=$obj_tpl->get_list($_POST,$this,$nPage);
        $this->pagedata['tpl_list']=$aData['data'];
        $this->pagination($nPage,$aData['page'],'goods_import_ajax','','business',$ctl='site_member');
        $this->output('business');
    }
    function goods_import_ajax($nPage){
        $obj_tpl=kernel::single('business_goods_import_tpl');
        $_POST['store_id']=$this->store_id;
        $aData=$obj_tpl->get_list($_POST,$this,$nPage);
        $this->pagedata['tpl_list']=$aData['data'];
        $this->pagination($nPage,$aData['page'],'goods_import_ajax','','business',$ctl='site_member');
        $this->page('site/member/import/list.html', true, 'business');
    }
    function goods_export(){
         $obj_cat = &app::get('b2c')->model('goods_cat');
        $pcat=$obj_cat->getList('cat_id,cat_name,parent_id,child_count',array('cat_id'=>$this->region_id));
        $pid=array();
        foreach($pcat as $v){
            $pid[]=$v['cat_id'];
        }
        $ccat=$obj_cat->getList('cat_id,cat_name,parent_id,child_count',array('parent_id'=>$pid));
        $ccat=utils::array_change_key($ccat,'parent_id', 1);
        foreach($pcat as $key=>$v){
            $pcat[$key]['child']=$ccat[$v['cat_id']];
        }
        $this->pagedata['pcat']=$pcat;
        $this->page('site/member/goods_export.html', true, 'business');
    }
    function goods_import_cat(){
        header('Content-Type:text/html; charset=utf-8');
        $pid=$_POST['cat_id'];
        if(empty($pid)){
          echo '';exit;
        }
        $obj_cat = &app::get('b2c')->model('goods_cat');
        $pcat=$obj_cat->getList('cat_id,cat_name,parent_id,child_count',array('parent_id'=>$pid));

        $this->pagedata['pcat']=$pcat;
        $this->page('site/member/import/cat.html', true, 'business');
    }

    function seller_update($return_id){
        $rp = app::get('aftersales')->model('return_product');
        $objOrder = app::get('b2c')->model('orders');
        $obj_return_policy = kernel::single('aftersales_data_return_policy');
        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($return_id);

        //添加退款日志
        $obj_return_log = app::get('aftersales')->model('return_log');
        $log_info = $obj_return_log->getList('*',array('order_id'=>$this->pagedata['return_item']['order_id']),-1,-1,'alttime DESC');
        //echo "<pre>";print_r($log_info);exit;
        $this->pagedata['log_info'] = $log_info;
        $this->output('business');
    }

    private function fileext($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

    function goods_export_detail(){
        header('Content-Type:text/html; charset=utf-8');
        $tpl_id=$_POST['tpl_id'];
        if(empty($tpl_id)){
          echo '';exit;
        }
        $mdl_tpl=app::get('business')->model('goods_import_tpl');
        $tpl=$mdl_tpl->dump($tpl_id);
        $result=$mdl_tpl->get_type_info($tpl['cat_id']);
        $this->pagedata['tpl']=$tpl;
        $this->pagedata['result']=$result['result'];
        $this->page('site/member/import/detail.html', true, 'business');
    }
    function goods_import_csv(){
        header('Content-Type:text/html; charset=utf-8');
        $tpl_id=$_POST['tpl_id'];
        if(empty($tpl_id)){
          echo '';exit;
        }
        $mdl_tpl=app::get('business')->model('goods_import_tpl');
        $tpl=$mdl_tpl->dump($tpl_id);
        $this->pagedata['tpl']=$tpl;
        $this->page('site/member/import/csv.html', true, 'business');
    }
    function goods_import_type(){
        header('Content-Type:text/html; charset=utf-8');
        $cat_id=$_POST['cat_id'];
        if(empty($cat_id)){
          echo '';exit;
        }
        $mdl_tpl=app::get('business')->model('goods_import_tpl');
        $result=$mdl_tpl->get_type_info($cat_id);
        $this->pagedata['type_id']=$result['gtype']['type_id'];
        $this->pagedata['result']=$result['result'];
        $this->page('site/member/import/type.html', true, 'business');
    }
    function goods_import_data(){
        $contents = array();
        $tmpFileHandle = fopen( $_FILES['csv']['tmp_name'],"r" );

        $ioType=kernel::single('desktop_io_type_csv');
        $ioType->fgethandle($tmpFileHandle,$contents);
        fclose($tmpFileHandle);

        if(!$contents[1]){
            echo '文件中无商品信息！';exit;
        }
        $csvtitle=$contents[0];
        $csvtitle='"'.implode('","', $csvtitle ).'"';

        $iotitle=app::get('business')->model('goods')->io_title(array('type_id'=>$_POST['type_id']));
        $iotitle='"'.implode('","', $iotitle ).'"';


        //验证导入文件结构和期望文件结构是否一致。
        if($iotitle!=$csvtitle){
           echo '<h3>导入CSV文件列和要求不符：</h3>';
           echo '<span style="padding-left:30px;display:block;padding-top:10px;"><p>要求：'.$iotitle.'</p><br>';
           echo '<p>文件：'.$csvtitle.'</p></span>';exit;
        }
        $mdl_tpl=app::get('business')->model('goods_import_tpl');

        //正确的数据。
        $sdfContents=array();
        //错误的数据行。
        $errorContents=array();
        $msgList=$mdl_tpl->turn_to_sdf($contents,$sdfContents,$errorContents,$_POST['type_id'],$_POST['cat_id']);
        //echo '<pre>';print_r($msgList);echo '</pre>';exit;
        //取得商品数据并验证合法性。
        if($msgList!==true){
            $this->pagedata['result']=$msgList;
            $this->page('site/member/import/result.html', true, 'business');
        }else{
            //store_id
            $db = kernel::database();
            $transaction= $db->beginTransaction();
            $mdl_goods=app::get('b2c')->model('goods');
            $result=false;
            $errsdf=array();
            foreach($sdfContents as $key=>$aData){
                $aData['store_id']=$this->store_id;
                $result=$mdl_goods->save($aData);
                if(!$result){
                   $errsdf=$aData;
                    break;
                }
            }
            if($result){
                $db->commit($transaction);
                echo '<em style="color:#0000FF;">导入成功。</em>';
            }else{
                $db->rollback();
                echo '<em style="color:#FF0000;">商品编号【'.$errsdf['bn'].'】导入失败，请检查对应信息。</em>';
            }
            exit;
        }
    }
    function goods_export_csv($type){
        $type_id=$_POST['type_id'];
        $cat_id=$_POST['cat_id'];
        $cat_name=$_POST['cat_name'];
        $mdl_tpl=app::get('business')->model('goods_import_tpl');
        $aData=$mdl_tpl->dump(array('cat_id'=>$cat_id,'store_id'=>$this->store_id),'*');
        if(empty($aData)){
           $aData['tpl_id']=$mdl_tpl->gen_id();
           $aData['vcode']='0001';
        }else{
           $aData['vcode']=str_pad((intval($aData['vcode'])+1), 4, "0", STR_PAD_LEFT);
        }
        $aData['store_id']=$this->store_id;
        $aData['cat_id']=$cat_id;
        $aData['type_id']=$type_id;
        $aData['cat_name']=$cat_name;
        $aData['createtime']=time();
        $aData['last_modified']=time();

        //$aData['vcode']=substr(md5(microtime()),0,4);
        $mdl_tpl->save($aData);
        header("Content-Type: text/csv");
        $filename = "goods_".$aData['tpl_id'].'_'.$aData['vcode'].".csv";
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);

        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox$/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $iotitle=app::get('business')->model('goods')->io_title(array('type_id'=>$type_id));
        $iotitle='"'.implode('","', $iotitle ).'"';
        //echo $iotitle; 导出的csv在office中打开是乱码。
        if(function_exists('iconv')){
            echo mb_convert_encoding($iotitle, 'GBK', 'UTF-8');
        }else{
            echo kernel::single('base_charset')->utf2local( $iotitle );
        }
    }

    function seller_intereven(){
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'seller_order'));
        $intereven_image = '';

        if ( $_FILES['file']['size'] > 314572800 )
        {
			$com_url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'seller_order'));
            $this->end(false, app::get('business')->_("上传文件不能超过300M"), $com_url);
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif","jpeg","rar","zip");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'seller_order'));
                $this->end(false, app::get('business')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
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
        if($intereven_image == ''){
            $intereven_image = $image_id;
        }else{
            $intereven_image = $intereven_image.','.$image_id;
        }
        //添加两张维权图片
        if ( $_FILES['file1']['size'] > 5242880 )
        {
            $com_url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'seller_order'));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file1']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file1']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'seller_order'));
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
        if($intereven_image == ''){
            $intereven_image = $image_id1;
        }else{
            $intereven_image = $intereven_image.','.$image_id1;
        }

        if ( $_FILES['file2']['size'] > 5242880 )
        {
            $com_url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'seller_order'));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过5M"), $com_url);
        }

        if ( $_FILES['file2']['name'] != "" )
        {
            $type=array("png","jpg","gif");

            if(!in_array(strtolower($this->fileext($_FILES['file2']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'seller_order'));
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
        if($intereven_image == ''){
            $intereven_image = $image_id2;
        }else{
            $intereven_image = $intereven_image.','.$image_id2;
        }
       

        $rp = app::get('aftersales')->model('return_product');
        $data = array('intereven_image'=>$intereven_image,'intereven_comment'=>$_POST['content'],'is_intervene'=>3);
        $result = $rp->update($data,array('return_id'=>$_POST['return_id']));
        //添加退款日志
        if ($this->app->member_id)
        {
            $obj_members = app::get('b2c')->model('members');
            $arrPams = $obj_members->dump($this->app->member_id, '*', array(':account@pam' => array('*')));
        }
        $log_text = "卖家上传举证，举证留言：".$_POST['content'];
        $result = "SUCCESS";
        $returnLog = app::get('aftersales')->model("return_log");
        $sdf_return_log = array(
            'order_id' => $_POST['order_id'],
            'return_id' => $_POST['return_id'],
            'op_id' => $this->app->member_id,
            'op_name' => (!$this->app->member_id) ? app::get('b2c')->_('买家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'behavior' => 'seller_update',
            'result' => $result,
            'role' => 'seller',
            'log_text' => $log_text,
            'image_file'=>$intereven_image,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = app::get('b2c')->model("order_log");

        $sdf_order_log = array(
            'rel_id' => $_POST['order_id'],
            'op_id' => $this->app->member_id,
            'op_name' => (!$this->app->member_id) ? app::get('b2c')->_('买家') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => $result,
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);

        $obj_order = app::get('b2c')->model('orders');
        $obj_order->update(array('refund_status'=>'9'),array('order_id'=>$_POST['order_id']));

        if($result){
            $this->splash('success', $url , app::get('b2c')->_('举证成功'));
        }else{
            $this->splash('failed',$url , app::get('b2c')->_('举证失败'));
        }

    }

    function goods_type_select(){
        $this->output('business');
    }

    function goods_add_go(){
        if($_POST['goods_kind'] == '3rdparty'){
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if (($processor->goodsKindDetail() == $goods['goods_kind_detail']) && $processor->isCustom()) {
                    $processor->goodsAddPage($_POST);
                    return;
                }
            }
        }
        if($_POST['goods_kind'] == 'entity'){
            $this->redirect(array('app'=>'business', ctl=>'site_member','act'=>'goods_add_entity'));
        }else{
            $this->redirect(array('app'=>'business', ctl=>'site_member','act'=>'goods_add'));
        }
    }

    function goods_add_entity(){
        $oStoregrade = $this->app_current->model('storegrade');
        $aGrade = $oStoregrade->getList('goods_num', array('grade_id'=>$this->grade_id));
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'goods_onsell'));
        if(!$aGrade) $this->splash('failed',$url , app::get('b2c')->_('由于某种原因，您不能执行该操作！'),'','',false);
        $oGoods = &$this->app_current->model('goods');
        $count = $oGoods->count(array('store_id'=>$this->store_id));
        if(intval($aGrade[0]['goods_num']) && $count >= intval($aGrade[0]['goods_num'])) $this->splash('failed',$url , app::get('b2c')->_('您已有最大'.$count.'件商品，不能再添加！'),'','',false);
        $oDlytype = app::get('b2c')->model('dlytype');
        $count = $oDlytype->count(array('store_id'=>$this->store_id,'dt_status'=>'1'));
        if(!$count) $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlytype')) , app::get('b2c')->_('您的店铺还没有创建或未启用运费模板！'),'','',false);
        $count = $oDlytype->db->select("select da_id from sdb_business_dlyaddress where store_id='{$this->store_id}' and (consign='true' or refund='true')");
        if(!count($count)) $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'dlyaddress')) , app::get('b2c')->_('您的店铺还没有创建发货地址或收货地址！'),'','',false);

        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this->path[] = array('title'=>app::get('b2c')->_('发布宝贝'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['current_url'] = app::get('business')->res_url;
        $cat_id = $this->issue_type?$this->region_id[0]:0;
        $store_id = $this->store_id;
        if($cat_id){
            $objCat = &$this->app_b2c->model('goods_cat');
            $aCat = $objCat->getList('type_id', array('cat_id'=>$cat_id));
            $type_id = ($aCat[0]['type_id']?$aCat[0]['type_id']:1);
        }else{
            $type_id = 1;
        }
        $this->pagedata['goods']['category']['cat_id'] = $cat_id;
        $this->pagedata['cat']['type_id'] = $type_id;
        $this->pagedata['goods']['type']['type_id'] = $type_id;
        $this->_editor($cat_id, $type_id);
        if(!count($this->pagedata['brandList'])) $this->splash('failed',app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_brand','act'=>'return_brand')) , app::get('b2c')->_('您的店铺还没有商品品牌！'),'','',false);
        //header("Cache-Control:no-store");
        $this->pagedata['IMAGE_MAX_SIZE'] = IMAGE_MAX_SIZE;
        $this->pagedata['point_mim_get_value'] = $this->app_b2c->getConf('site.point_mim_get_value')*100;//运营商设置的兑换积分的最低比例
        $this->pagedata['point_max_get_value'] = $this->app_b2c->getConf('site.point_max_get_value')*100;//运营商设置的兑换积分的最高比例
        $this->pagedata['img_size'] = app::get('b2c')->getConf('site.big_pic_width').'*'.app::get('b2c')->getConf('site.big_pic_height');
        $this->output('business');
    }

    function toAdd_entity(){
        $customhtml=$_POST['goods']['description'];
        $valite=kernel::single('business_url')->is_valid_html($customhtml);
        $img_valite=kernel::single('business_img_url')->is_valid_html($customhtml);
        // $valite = $valite && $img_valite;

        if(!$valite){
            $this->splash('failed',$url , app::get('b2c')->_('详细介绍中存在非法的图片或文字链接'),'','',true);
        }
        if(!$img_valite){
            $this->splash('failed',$url , app::get('b2c')->_('详细介绍中存在非法的图片引用地址'),'','',true);
        }
        /*$customhtml=preg_replace("/<script[^>]*>([\s\S]*?)<\/script>/i","",$customhtml);
        $burl=kernel::single('business_url');
        $customhtml=$burl->replace_html($customhtml);//非本地地址过滤
        $style=kernel::single('business_theme_widget_style');
        $customhtml=$style->prefix($customhtml,substr(md5($customhtml),0,6));//css过滤
        $_POST['goods']['description'] = $customhtml;*/
        //计算库存
        if($_POST['goods']['create'] == 'member'){
            $_POST['goods']['product']['0']['store'] = count($_POST['goods']['product']['0']['card']);
        }
        $oStoregrade = $this->app_current->model('storegrade');
        $aGrade = $oStoregrade->getList('goods_num', array('grade_id'=>$this->grade_id));
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>(($_POST['goods_switch']&&$_POST['goods_switch']=='instock')?'goods_instock':'goods_onsell')));
        if(!$aGrade) $this->splash('failed',$url , app::get('b2c')->_('由于某种原因，您不能执行该操作！'),'','',true);
        $oGoods = &$this->app_current->model('goods');
        $filter = array('store_id'=>$this->store_id);
        if($_POST['goods']['goods_id']){
            $filter['goods_id|noequal'] = $_POST['goods']['goods_id'];
        }
        $count = $oGoods->count($filter);
        if(intval($aGrade[0]['goods_num']) && $count >= intval($aGrade[0]['goods_num'])) $this->splash('failed',$url , app::get('b2c')->_('您已有最大'.$count.'件商品，不能再添加！'),'','',true);

        $url = '';
        $oGoods = &$this->app_b2c->model('goods');
        if (!isset($_POST['goods']['category']['cat_id']) || empty($_POST['goods']['category']['cat_id'])){
            $this->splash('failed',$url , app::get('b2c')->_('商品分类不能为空'),'','',true);
        }
        if (!isset($_POST['goods']['brand']['brand_id']) || empty($_POST['goods']['brand']['brand_id'])){
            $this->splash('failed',$url , app::get('b2c')->_('商品品牌不能为空'),'','',true);
        }
        if (isset($_POST['goods']['description'])&&$_POST['goods']['description'] == '&nbsp;'){
            $this->splash('failed',$url , app::get('b2c')->_('详细介绍内容不能为空'),'','',true);
        }
        if (isset($_POST['goods']['brief'])&&$_POST['goods']['brief']&&strlen($_POST['goods']['brief'])>210){
            $this->splash('failed',$url , app::get('b2c')->_('简短的商品介绍,请不要超过70个字！'),'','',true);
        }
        if(isset($_POST['spec_load'])){
            $this->splash('failed',$url , app::get('b2c')->_('规格未加载完毕'),'','',true);
        }

        if(isset($_POST['specall']) && !empty($_POST['specall'])){
            if(is_array($_POST['goods']['product'])){
                foreach($_POST['goods']['product'] as $pk=>$pv){
                    if(count($pv['spec_desc']['spec_value_id']) < count($_POST['specall'])){
                         $this->splash('failed',$url , app::get('b2c')->_('未选定全部规格'),'','',true);
                    }
                }
            }else{
                $this->splash('failed',$url , app::get('b2c')->_('未选定全部规格'),'','',true);
            }
        }elseif(isset($_POST['spec']) && $_POST['spec']){
            if(is_array($_POST['goods']['product'])){
                foreach($_POST['goods']['product'] as $pk=>$pv){
                    if(count($pv['spec_desc']['spec_value_id']) < count(unserialize($_POST['goods']['spec']))){
                         $this->splash('failed',$url , app::get('b2c')->_('未选定全部规格'),'','',true);
                    }
                }
            }else{
                $this->splash('failed',$url , app::get('b2c')->_('未选定全部规格'),'','',true);
            }
        }

        if($_POST['adjunct']['min_num'][0] > $_POST['adjunct']['max_num'][0]){
            $this->splash('failed',$url , app::get('b2c')->_('配件最小购买量大于最大购买量'),'','',true);
        }
        if(!$oGoods->checkPriceWeight($_POST['goods']['product'])){
            $this->splash('failed',$url , app::get('b2c')->_('商品价格或重量格式错误'),'','',true);
        }
        if(!$oGoods->checkStore($_POST['goods']['product'])){
            $this->splash('failed',$url , app::get('b2c')->_('库存格式错误'),'','',true);
        }

        $goods = $this->_prepareGoodsData($_POST);
        if( $goods['udfimg'] == 'true' && !$goods['thumbnail_pic'] ){
            $goods['udfimg'] = 'false';
        }

        if(is_string($_POST['productkey'])){
            $productkey = unserialize($_POST['productkey']);
            if(is_array($_POST['goods']['product'])){
                foreach($_POST['goods']['product'] as $pk => $pv){
                    $newpk[] = $pv['product_id'];
                }
            }
            if(is_array($newpk) && is_array($productkey)){
                $diff = array_diff($productkey,$newpk);
            }
            if(count($diff) > 0){
                if(!$this->pre_recycle_spec($_POST['goods']['goods_id'],$diff)){
                    $this->splash('failed',$url , app::get('b2c')->_('有的规格订单未处理'),'','',true);
                }
            }
        }

        if( count( $goods['product'] ) == 0 ){
            //$this->end(false,'货品未添加');
            exit;
        }
        if( strlen($goods['brief']) > 255 ){
            $this->splash('failed',$url , app::get('b2c')->_('商品介绍请不要超过70个汉字'),'','',true);
        }

        if( !$goods['name'] )
            $this->splash('failed',$url , app::get('b2c')->_('商品名称不能为空'),'','',true);
        if( $goods['bn']  ){
            if( $oGoods->checkProductBn($goods['bn'], $goods['goods_id']) ){
                $this->splash('failed',$url , app::get('b2c')->_('您所填写的商品编号已被使用，请检查！'),'','',true);
            }
        }

        foreach($goods['product'] as $k => $p){
            if(!$k && $k !== 0) {
                unset($goods['product'][$k]);
                continue;
            }
            if($goods['status'] != 'false' && intval($p['store']) == 0) $this->splash('failed',$url , app::get('b2c')->_('上架商品库存必须大于0'),'','',true);
            if (is_null( $p['store'] )){$goods['product'][$k]['freez'] = null;$goods['product'][$k]['store'] = null;}
            if(empty($p['bn'])) continue;
            if($oGoods->checkProductBn($p['bn'], $goods['goods_id']) ){
                $this->splash('failed',$url , app::get('b2c')->_('您所填写的货号已被使用，请检查！'),'','',true);
            }
        }

        //判断是否有过个规格
        if(count($goods['product']) > 1){
            $this->splash('failed',$url , app::get('b2c')->_('虚拟商品不可以有多个规格'),'','',true);
        }

        if(!$goods['product']) {
            unset($goods['product']);
            unset($goods['spec']);
        }

        $oUrl = kernel::single('site_route_app');

        $arr_remove_image = array();
        if( $_POST['goods']['images'] ){
            $oImage_attach = app::get('image')->model('image_attach');
            $arr_image_attach = $oImage_attach->getList('*',array('target_id'=>$goods['goods_id'],'target_type'=>'goods'));
            foreach ((array)$arr_image_attach as $_arr_image_attach){
                if (!in_array($_arr_image_attach['image_id'],$_POST['goods']['images'])){
                    $arr_remove_image[] = $_arr_image_attach['image_id'];
                }
            }
        }
        $goods['category']['cat_id'] = is_array($goods['category']['cat_id'])?0:$goods['category']['cat_id'];
        if ( !$oGoods->save($goods) ){
            $this->splash('failed',$url , app::get('b2c')->_('您所填写的货号重复，请检查！'),'','',true);
        }else{
            if( $goods['images'] ){
                $oImage = &app::get('business')->model('image');
                if ($arr_remove_image){
                    foreach($arr_remove_image as $_arr_remove_image)
                        $test = $oImage->delete_image($_arr_remove_image,'goods',$this->store_id);
                }
                foreach($goods['images'] as $k=>$v){
                    $test = $oImage->rebuild($v['image_id'],array('S','M','L'),true,$this->store_id,0);
                }
            }

            if( $_POST['goods_static'] ){
                $url = $oUrl->fetch_static( array( 'static'=>$_POST['goods_static'] ) );
                $goods_url = app::get('site')->router()->gen_url( array( 'app'=>'b2c','real'=>1,'ctl'=>'site_product','args'=>array($goods['goods_id']) ) );
                $goods_url = substr( $goods_url , strlen( app::get('site')->base_url() ) );
                $goods_url_info = $oUrl->fetch_static( array( 'static'=>$goods_url ) );
                if(empty($goods_url_info['url'])){
                    $goods_url_info['url'] = $goods_url;
                }
                $goods_url_info['static'] = $_POST['goods_static'];
                $goods_url_info['enable'] = 'true';
                if( $url['url'] && $url['url'] != $goods_url_info['url'] ){
                    $this->splash('failed',$url , app::get('b2c')->_('您填写的自定义链接已存在'),'','',true);
                }
                $oUrl->store_static( $goods_url_info );
           }else{
                $goods_url = app::get('site')->router()->gen_url( array( 'app'=>'b2c','real'=>1,'ctl'=>'site_product','args'=>array($goods['goods_id']) ) );
                $goods_url = substr( $goods_url , strlen( app::get('site')->base_url() ) );
                $oUrl->delete_static( array( 'static'=>$goods_url ) );
           }

        }

        $_POST['goods'] = $goods;
        $goodsServiceList = kernel::servicelist("goods.action.save");
        foreach( $goodsServiceList as $aGoodsService ){
            if(!$aGoodsService->save( $_POST, $error_msg )){
                $this->end(false, $error_msg);
            }
        }

        //添加虚拟商品 
        $obj_entity = app::get('b2c')->model('goods_entity_items');
        if($_POST['goods']['create'] == 'member'){
            //手动创建
            if(isset($_POST['goods']['product']['0']['card']) && $_POST['goods']['product']['0']['card'] != ''){
                foreach($_POST['goods']['product']['0']['card'] as $key=>$val){
                    $data = array('goods_id'=>$_POST['goods']['goods_id'],'product_id'=>$_POST['goods']['product']['0']['product_id'],'card_id'=>$val['card_id'],'card_psw'=>$val['card_psw'],'store_id'=>$this->store_id);
                    $obj_entity->save($data);
                }
            }else{
                $this->splash('failed',$url , app::get('b2c')->_('请输入至少一组卡号'),'','',true);
            }
        }else{
            //自动生成
            if(isset($_POST['goods']['product']['0']['store'])){
                $key = $obj_entity->generate_key();
                $random = rand(1000,2000);
                for($i=1;$i<=$_POST['goods']['product']['0']['store'];$i++){
                    $card_id = $obj_entity->_makeCouponCode($_POST['goods']['product']['0']['store']+$i,$random,$key);
                    $card_psw = $obj_entity->_makeCouponCode($_POST['goods']['product']['0']['store']+$i,$random,$key);
                    $data = array('goods_id'=>$_POST['goods']['goods_id'],'product_id'=>$_POST['goods']['product']['0']['product_id'],'card_id'=>$card_id,'card_psw'=>$card_psw,'store_id'=>$this->store_id,'key'=>$key,'random'=>$random);
                    $obj_entity->save($data);
                }
            }else{
                $this->splash('failed',$url , app::get('b2c')->_('请填写商品库存'),'','',true);
            }
        }
        //end

        if(app::get('base')->getConf('server.search_server.search_goods') == 'search_goods'){
            $obj = search_core::segment();
            if(search_core::instance('search_goods')->status($msg)){
                $luceneIndex = search_core::instance('search_goods')->link();
            }else{
                $luceneIndex = search_core::instance('search_goods')->create();
            }
            $luceneIndex = search_core::instance('search_goods')->update($goods);
        }

        $objBGoods = $this->app_current->model('goods');
        $objBGoodsCat = $this->app_current->model('goods_cat_conn');
        if(isset($_POST['customcatid']) && is_array($_POST['customcatid'])){

            $data = array();
            foreach($_POST['customcatid'] as $rows){
                $data[] = array('goods_id'=>$_POST['goods']['goods_id'],'cat_id'=>$rows);
            }
            if(count($data)>0){
                $objBGoodsCat->delete(array('goods_id'=>$_POST['goods']['goods_id']));
                $objBGoods->set_custom_cat($data);
            }
        }

        //$url = $this->gen_url(array('app'=>'business','ctl'=>"site_member",'act'=>"goods_onsell"));
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>(($_POST['goods_switch']&&$_POST['goods_switch']=='instock')?'goods_instock':'goods_onsell')));
        $this->splash('success',$url , app::get('b2c')->_('操作成功'),'','',true);
        //$this->end(true,app::get('b2c')->_('操作成功'),null,array('goods_id'=>$goods['goods_id'] ) );
    }

    function my_entity($nPage=1){
        $obj_entity_goods = $this->app->model('entity_goods');
        $obj_items = $this->app->model('goods_entity_items');
        $entityGoods = $obj_items->getList('*',array('store_id'=>$this->store_id));
        $count = count($entityGoods);
        $aPage = $this->get_start($nPage,$count);
        $info = $obj_items->getList('*',array('store_id' => $this->store_id),$aPage['start'],$this->pagesize);
        $obj_goods = $this->app->model('goods');
        foreach($info as $key=>$val){
            $info[$key]['goods_name'] = $obj_goods->dump($val['goods_id'],'name');
        }
        $params['data'] = $info;
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'my_entity','', 'business', 'site_member');
        $this->pagedata['entityGoods'] = $params['data'];
        //echo "<pre>";print_r($entityGoods);exit;
        $this->output('business');
    }

    function upload_money($return_id){
        $this->pagedata['return_id'] = $return_id;
        $this->output('business');
    }

    function do_upload(){
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'seller_order'));
        if($_POST['return_money_id']){
            $obj_order = app::get('b2c')->model('orders');
            $rp = app::get('aftersales')->model('return_product');
            
            $refunds = app::get('ectools')->model('refunds');
            //处理图片
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
            }else{
                $this->splash('failed',$url,'请上传退款图片截图！');
            }
            $aData['image_upload'] = $image_id;
            $aData['return_money_id'] = $_POST['return_money_id'];
            $aData['is_return_money'] = '2';
            $aData['status'] = '16';

            $result = $rp->update($aData,array('return_id'=>$_POST['return_id']));

            $order_id = $rp->dump(array('return_id'=>$_POST['return_id']),'order_id');
            $refund_status = array('refund_status'=>'12','score_u'=>$score_u);
            $rs = $obj_order->update($refund_status,array('order_id'=>$order_id['order_id']));
            $rs = $rp->update($aData,array('return_id'=>$_POST['return_id']));
            
            //添加退款日志
            if ($this->app->member_id)
            {
                $obj_members = app::get('b2c')->model('members');
                $arrPams = $obj_members->dump($this->app->member_id, '*', array(':account@pam' => array('*')));
            }
            $log_text = "卖家上传打款凭证,流水单号：".$_POST['return_money_id'];
            $result = "SUCCESS";
            $returnLog = app::get('aftersales')->model("return_log");
            $sdf_return_log = array(
                'order_id' => $order_id['order_id'],
                'return_id' => $_POST['return_id'],
                'op_id' => $this->app->member_id,
                'op_name' => (!$this->app->member_id) ? app::get('b2c')->_('买家') : $arrPams['pam_account']['login_name'],
                'alttime' => time(),
                'behavior' => 'seller_update',
                'result' => $result,
                'role' => 'seller',
                'log_text' => $log_text,
                'image_file'=>$image_id,
            );

            $log_id = $returnLog->save($sdf_return_log);

            $objOrderLog = app::get('b2c')->model("order_log");

            $sdf_order_log = array(
                'rel_id' => $order_id['order_id'],
                'op_id' => $this->app->member_id,
                'op_name' => (!$this->app->member_id) ? app::get('b2c')->_('买家') : $arrPams['pam_account']['login_name'],
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'refunds',
                'result' => $result,
                'log_text' => $log_text,
            );
            $log_id = $objOrderLog->save($sdf_order_log);

            $this->splash('success',$url,'上传凭证成功！');
        }else{
            $this->splash('failed',$url,'请填写流水单号！');
        }
    }

    function copylink(){
        $this->pagedata['htmldata'] = 'http://'.$_SERVER['HTTP_HOST'].kernel::base_url().'/member-active_coupons-'.$this->store_id.'-'.$_GET['cid'].'.html';
        
        $this->page('site/member/cplink.html',true,'business');
    }
}

