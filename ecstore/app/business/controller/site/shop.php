<?php


class business_ctl_site_shop extends b2c_frontpage{

    function __construct($app){
        parent::__construct($app);
    }

     /**
      * _set_tmpl_file 设置模版文件
      * @author 曹辰吟
      * @param [int] $store_id  店铺ID
      * @param [string] $tmpl_type 模版文件类型
      */
    private function _set_tmpl_file($store_id,$tmpl_type){
        //获取模版文件
        $theme_tmpl =  $this->_get_theme_tmpl($store_id,$tmpl_type);
        //为控制器指定模版文件
        $this->set_tmpl_file($theme_tmpl['tmpl_path']);
        //注册widgets标签解析方法
        $this->_compiler()->set_compile_helper('compile_widgets',kernel::single('business_view_compiler'));
        //传入店铺ID
        $this->pagedata['store_id'] = $store_id;
    }

    /**
     * _get_theme_tmpl 获取模版文件
     * @author 曹辰吟
     * @param [int] $store_id  店铺ID
     * @param [string] $tmpl_type 模版文件类型
     * @return [array]  模版文件
     */
    private function _get_theme_tmpl($store_id,$tmpl_type){
        $store = app::get('business')->model('storemanger')->getRow('theme_id',array('store_id'=>$store_id));
        $theme = array();
        if ($store['theme_id']) {
            switch ($tmpl_type) {
                case 'gallery':
                    $theme = app::get('business')->model('theme')->getRow('gallery_tmpl_id AS theme_tmpl_id ',array('theme_id'=>$store['theme_id']));
                    break;
                default:
                    $theme = app::get('business')->model('theme')->getRow('shop_tmpl_id AS theme_tmpl_id',array('theme_id'=>$store['theme_id']));
                    break;
            }
            $theme_tmpl = app::get('site')->model('themes_tmpl')->getRow('*',array('id'=>$theme['theme_tmpl_id']));
        } else {
            $current_theme = kernel::single('site_theme_base')->get_default();
            $defaultIndexFile = kernel::single('site_theme_tmpl')->get_default($tmpl_type, $current_theme); 
            $theme_tmpl = app::get('site')->model('themes_tmpl')->getRow('*',array('theme'=>$current_theme, 'tmpl_path'=>$defaultIndexFile));
        }
        return $theme_tmpl;
    }
    /**
     * view 店铺首页
     * @author 曹辰吟
     * @param  [int] $store_id 店铺ID
     * @return [null]
     */
    public function view($store_id) {
        $GLOBALS['runtime']['store_id']=$store_id;
        $GLOBALS['runtime']['nocache']=microtime();
        
        //update by Huoxh 2014-05-14 店铺异常则显示异常页面。
        $aStore=app::get('business')->model('storemanger')->getRow('*',array('store_id'=>$store_id));
        if(!$aStore || $aStore === false ||$aStore['status']=='0'|| (!!$aStore['last_time'] && $aStore['last_time']<time())){
            $info = kernel::single('site_errorpage_list')->getList('errorpage.closeStore');
            $this->pagedata['errorpage'] = $info['errormsg'];
            $this->page("site/store/closeStore.html", false,'business');
            return;
        }
        $this->pagedata['store'] = $aStore;
       
        $this->_set_tmpl_file($store_id,'shop');
        
        
        //$oMem = app::get('b2c')->model('members');
        //$aInfo = app::get('business')->model('storemanger')->getList('area,account_id',array('store_id'=>$store_id));
        //end 
        $siteMember = kernel::single('b2c_frontpage')->get_current_member();
        $this->pagedata['member_info']=$siteMember;
        if($siteMember['member_id']){
            app::get('business')->model('store_view_history')->add_history($siteMember['member_id'],$store_id);
        }

        //设置模板缓存
        $this->tmpl_cachekey('store_id',$store_id);

       
        $this->title = $this->pagedata['store']['store_name'];
        //$this->keywords = app::get('b2c')->_('商品分类_').'_'.$shopname;
        //$this->description = app::get('b2c')->_('商品分类_').'_'.$shopname;
        $this->page();
    }

    /**
     * gallery 商品列表页
     * @author 曹辰吟
     * @param  [int] $store_id 店铺ID
     * @return [null]
     */
    public function gallery($store_id,$urlFilter=null,$viewType='grid',$page=1,$orderType=0){
        $store_id = intval($store_id);
        
        $GLOBALS['runtime']['store_id']=$store_id;
        $GLOBALS['runtime']['nocache']=microtime();

        //设置模板缓存
        $this->tmpl_cachekey('store_id',$store_id);
        

        $this->pagedata['member_info']=kernel::single('b2c_frontpage')->get_current_member();
        $this->_set_tmpl_file($store_id,'gallery');
        $aStore = app::get('business')->model('storemanger')->getRow('*',array('store_id'=>$store_id,'approved'=>'1'));
        //upate by Huoxh 2014-05-14 店铺异常则显示异常页面。
        //if(!$aStore || $aStore === false ||  (!!$aStore['last_time'] && $aStore['last_time']<time())){
        if(!$aStore || $aStore === false || $aStore['status']=='0'|| (!!$aStore['last_time'] && $aStore['last_time']<time())){
            //$this->_response->clean_all_headers()->set_http_response_code('404')->send_headers();
            //echo '无效店铺！<br>可能是店铺审核未通过';
            //exit;
            $info = kernel::single('site_errorpage_list')->getList('errorpage.closeStore');
            $this->pagedata['errorpage'] = $info['errormsg'];
            $this->page("site/store/closeStore.html", false,'business');
            return;
        }
        //end
        
        $urlFilter = htmlspecialchars(urldecode($urlFilter));
        $urlFilter .= "_i,{$store_id}";
        if($_GET['searchRange']){
            $urlFilter = "_i,{$store_id}";
            $this->pagedata['searchRange'] = 1;
        }
        
        if($_GET['keyword']){
            $urlFilter .= "_n,".htmlspecialchars($_GET['keyword']);
        }
        
        if($_GET['price1'] && $_GET['price2']){
            $urlFilter .= "_p,".serialize(array(0 => floatval($_GET['price1']),1 => floatval($_GET['price2'])));
        }elseif($_GET['price1']){
            $urlFilter .= "_p,".serialize(array(0 => floatval($_GET['price1'])));
        }elseif($_GET['price2']){
            $urlFilter .= "_p,".serialize(array(1 => floatval($_GET['price2'])));
        }
        $this->pagedata['keyword'] = $_GET['keyword'];
        $this->pagedata['price1'] = $_GET['price1'];
        $this->pagedata['price2'] = $_GET['price2'];
        
        $path =array();
        $cat=array();
        $searchtools = &app::get('b2c')->model('search');              
        $propargs = $searchtools->decode($urlFilter,$path,$cat);
        
        if(isset($propargs['price'][0])){
            $propargs['price'][0] = unserialize($propargs['price'][0]);
            if(!is_array($propargs['price'][0])) unset($propargs['price']);
        }
        
        if(isset($propargs['price'][0])){
            $temp = $propargs['price'][0];unset($propargs['price']);
            $urlFilter = $searchtools->encode($propargs)."_p,".serialize($temp);
            foreach((array)$temp as $key => $value){
                if($key == 0){
                    $propargs["price|bthan"] = floatval($value);
                }
                if($key == 1){
                    $propargs["price|sthan"] = floatval($value);
                }
            }
        }else{
            $urlFilter = $searchtools->encode($propargs);
        }

        $objGoods = &$this->app->model('goods');
        if($propargs['cat_id']){
           $cat_id=implode(",",$propargs['cat_id']);
           $cat_current = $objGoods->db->selectrow("select a.cat_name,a.parent_id,a.custom_cat_id AS cat_id,a.cat_path from sdb_business_goods_cat a WHERE a.store_id=".intval($store_id)." and a.custom_cat_id=".intval($propargs['cat_id'][0]));
           $cats = array();
           if($cat_current){
              $cat_parent = array_filter(explode(',', $cat_current['cat_path']));
              $cat_list = $objGoods->db->select("select a.cat_name,a.parent_id,a.custom_cat_id AS cat_id,a.cat_path from sdb_business_goods_cat a WHERE a.store_id=".intval($store_id)." and (a.custom_cat_id in ('".implode('\',\'', $cat_parent)."') or a.parent_id=".intval($cat_current['cat_id']).") ");
              
              foreach((array)$cat_list as $items){
                  if($items['parent_id'] == $cat_current['cat_id']) $cats['children'][] = array('cat_id'=>$items['cat_id'],'cat_name'=>$items['cat_name']);
                  else $cats['parent'][] = array('cat_id'=>$items['cat_id'],'cat_name'=>$items['cat_name']);
              }
              $cats['current'] = $cat_current;
           }
            $this->pagedata['discuss_cat'] = $cats;
            unset($propargs['cat_id']);
        }
        if(!!$cat_id){
            $cat_id=explode(",",$cat_id);
            foreach($cat_id as $k=>$v){
                if($v) $cat_id[$k]=intval($v);
            }
            $this->id = implode(",",$cat_id);
        }else{
            $cat_id = array('');
            $this->id = '';
        }
        
        $business_goods = $this->app->model('goods_cat_conn')->getList('goods_id',array('cat_id'=>$cat_id));
        foreach($business_goods as $rows){
            $propargs['goods_id'][] = $rows['goods_id'];
        }
        if($cat_id && empty($propargs['goods_id'])){
            $propargs['goods_id'] = -1;
        }
        
        $goods_props = array();
        foreach($path as $p){
            if(is_numeric($p['type'])){
                $goods_props[] = implode(',', $p['data']);
            }
        }
        
        $filter = array_merge(array('marketable'=>'true','goods_type'=>'normal'),$propargs);
        if( ($cat_id[0] === '' || $cat_id[0] === null ) && !isset( $cat_id[1] ) )
            unset($filter['goods_id']);
        if( ($filter['brand_id'][0] ==='' || $filter['brand_id'][0] === null) && !isset( $filter['brand_id'][1] ))
            unset($filter['brand_id']);
        if( ($filter['store_id'][0] ==='' || $filter['store_id'][0] === null) && !isset( $filter['store_id'][1] ))
            unset($filter['store_id']);
        
        $page = ($page > 1) ? intval($page) : 1;
        $pageLimit = app::get('b2c')->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);
        $args = array($store_id,urlencode($urlFilter),$viewType);
        $this->pagedata['filter'] = urlencode($urlFilter);
        
        $productCat = &app::get('b2c')->model('goods_cat');
        $tmp_filter['str_where'] = $objGoods->_filter($filter);
        $this->pagedata['orderType'] = $orderType;
        $this->pagedata['viewType'] = $viewType;
        $orderBy = $this->orderBy();
        $orderType = ($orderType == 0 || $orderType == 1 )?2:$orderType;
        if(isset($orderBy[$orderType])){
            $orderby = $orderBy[$orderType]['sql'];
        }
        $aProduct = $objGoods->getList('*',$tmp_filter,$pageLimit*($page-1),$pageLimit,$orderby);
        $count = $objGoods->count($tmp_filter);
        
        if(!$aStore['store_region']){
            $aStore['store_region'] = explode(',', $aStore['store_region']);
            if(!count($aStore['store_region'])){
                unset($aStore['store_region']);
                foreach($this->app->model('goods')->get_subcat_list(0) as $rows){
                    $aStore['store_region'][] = $rows['cat_id'];
                }
            }
        }
        $this->pagedata['store'] = $aStore;
        
        $aCatId = $objGoods->getCats($aStore['store_region']);
        $cat_list = $aCatId['cat_id'];
        $typeinfo = $productCat->getList('type_id', array('cat_id'=>$cat_list));
        $aTypeId = array();
        foreach($typeinfo as $items){
            $aTypeId[] = $items['type_id'];
        }
        
        $has_filter = array();
        $goods_count = array();
        $goods_ids = array();
        $cols = '';
        for($i=1; $i<51; $i++){
            $cols .= ",p_{$i}";
        }
        foreach($objGoods->getList('type_id,goods_id'.$cols,$tmp_filter,0,-1) as $items){
            for($i=1; $i<51; $i++){
                if($items['type_id']>0 && $items["p_{$i}"]>0)
                $goods_count[$items['type_id']][$i][$items["p_{$i}"]] += 1;
            }
            $goods_ids[] = $items['goods_id'];
        }
        $sql = " select count(g.goods_id) as _count,u.brand_id,b.brand_name from sdb_business_brand as u join sdb_b2c_brand as b on u.brand_id=b.brand_id join sdb_b2c_goods as g on b.brand_id=g.brand_id ";
        if(!!$goods_ids){
            $sql .= " and g.goods_id in (".implode(',', $goods_ids).") ";
        }else{
            $sql .= " and 1=0 ";
        }
        $sql .= " and g.marketable='true' and g.goods_type='normal' where 1=1 ";
        if(!!$filter['brand_id'] && is_array($filter['brand_id'])){
            //$sql .= " and u.brand_id not in (".implode(',', $filter['brand_id']).") ";
        }
        $sql .= " and u.store_id='{$store_id}' and u.status='1' and u.type='1' group by u.brand_id order by b.ordernum,b.brand_id desc";
        $business_brand = $objGoods->db->select($sql);
        foreach((array)$business_brand as $key => $value){
            if(!!$filter['brand_id'] && is_array($filter['brand_id']) && in_array($value['brand_id'],$filter['brand_id'])){
                $has_filter[] = array('name'=>'品牌', 'value'=>$value['brand_name'], 'filter'=>str_replace("b,{$value['brand_id']}","",$urlFilter));
                unset($business_brand[$key]);
            }
        }
        
        $business_props = array();
        if(!!$aTypeId && count($aTypeId) > 0){
            $sql = " select p.type_id,p.goods_p,p.props_id,p.name,v.props_value_id,v.name as pv_name from sdb_b2c_goods_type_props as p join sdb_b2c_goods_type_props_value as v on p.props_id=v.props_id ";
            if(!!$goods_props && count($goods_props) > 0){
                //$sql .= " and v.props_value_id not in (".implode(',',$goods_props).") ";
            }
            $sql .= "where p.type_id in (".implode(',',$aTypeId).") and p.type='select' and p.show='on' group by p.props_id,v.props_value_id order by p.ordernum, p.props_id, v.props_value_id ";
            $business_type = $objGoods->db->select($sql);
            if(!!$business_type)
            foreach($business_type as $key => $items){
                if(!!$goods_props && count($goods_props) > 0 && in_array($items['props_value_id'],$goods_props)){
                    $has_filter[] = array('name'=>$items['name'], 'value'=>$items['pv_name'], 'filter'=>str_replace("{$items['goods_p']},{$items['props_value_id']}","",$urlFilter));
                    unset($business_type[$key]);
                    continue;
                }
                $_count = $goods_count[$items['type_id']][$items['goods_p']][$items['props_value_id']];
                if(!$_count || $_count<0) continue;
                $business_props[$items['props_id']]['name'] = $items['name'];
                $business_props[$items['props_id']]['props'][$items['props_value_id']] = array('name'=>$items['pv_name'],'goods_p'=>$items['goods_p'],'_count'=>$_count);
            }
        }
        
        $this->pagedata['pdtPic']=array('width'=>100,'heigth'=>100);
        $this->pagedata['property_select'] = $this->app->getConf('site.property.select');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['M']['default_image'];
        $this->pagedata['brand'] = $business_brand;
        $this->pagedata['proparg'] = $business_props;
        $this->pagedata['has_filter'] = $has_filter;
        $this->pagedata['gallery_display'] = $this->app->getConf('gallery.display.grid.colnum');
        if($count < $this->pagedata['gallery_display']){
            $this->pagedata['gwidth'] = $count * (100/$this->pagedata['gallery_display']);
        }else{
            $this->pagedata['gwidth'] = 100;
        }
        $this->pagedata['products'] = &$aProduct;
        $this->pagedata['count'] = $count; 
        if(app::get('b2c')->getConf('system.seo.noindex_catalog'))
            $this->header .= '<meta name="robots" content="noindex,noarchive,follow" />';

        //对商品进行预处理
        $this->pagedata['mask_webslice'] = app::get('b2c')->getConf('system.ui.webslice')?' hslice':null;
        $this->pagedata['_PDT_LST_TPL'] = 'site/gallery/'.(($viewType == 'grid'||$viewType == 'list')?$viewType:'grid').'.html';
        $this->_plugins['function']['selector'] = array(&$this,'_selector');

        //分页开始，前台将调用gimage标签 显示分页。
        for($i=1;$i<ceil($count/$pageLimit)+1;$i++){
            $total_page[] = $i;
        }
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($count/$pageLimit),
            'page'=>$total_page,
            'link'=> app::get('site')->router()->gen_url(array('app'=>'business', 'ctl'=>'site_shop','full'=>1,'act'=>'gallery','args'=>array_merge($args,array($tmp=time(),$orderType)))),
            'token'=>$tmp);

        //如果当前页大于总页数。则报错。
        if($page != 1 && $page > $this->pagedata['pager']['total']){
            $this->_response->set_http_response_code(404);
        }
        //如果未搜索到则显示报错页面。
        if(!$count){
            $this->pagedata['emtpy_info'] = kernel::single('site_errorpage_get')->getConf('errorpage.search');
        }
        
        $oMem = app::get('b2c')->model('members');
        $aInfo = app::get('business')->model('storemanger')->getList('area,account_id',array('store_id'=>$store_id));
        $siteMember = kernel::single('b2c_frontpage')->get_current_member();

        $this->title = $this->pagedata['store']['store_name'];
        $this->page('site/gallery/index.html', false,'business');
    }
    
    function orderBy($id=null){
        $order=array(
           1=> array('label'=>app::get('b2c')->_('默认')),
           2=> array('label'=>app::get('b2c')->_('销量 高->低'),'sql'=>'buy_count desc'),
           3=> array('label'=>app::get('b2c')->_('销量 低->高'),'sql'=>'buy_count'),
           4=> array('label'=>app::get('b2c')->_('新品 新->旧'),'sql'=>'last_modify desc'),
           5=> array('label'=>app::get('b2c')->_('新品 旧->新'),'sql'=>'last_modify'),
           6=> array('label'=>app::get('b2c')->_('价格 高->低'),'sql'=>'price desc'),
           7=> array('label'=>app::get('b2c')->_('价格 低->高'),'sql'=>'price'),
           8=> array('label'=>app::get('b2c')->_('收藏 高->低'),'sql'=>'fav_count desc'),
           9=> array('label'=>app::get('b2c')->_('收藏 低->高'),'sql'=>'fav_count'),
           10=>array('label'=>app::get('b2c')->_('人气 高->低'),'sql'=>'view_count desc'),
           11=>array('label'=>app::get('b2c')->_('人气 低->高'),'sql'=>'view_count'),
        );
        if(app::get('b2c')->getConf('gallery.deliver.time')=='false'){
            unset($order[4]);
            unset($order[5]);
        }
        if($id){
            return $order[$id];
        }else{
            return $order;
        }
    }
    
    function searchPage($store_id,$urlFilter=null,$viewType='grid',$page=1,$orderType=0){
        $viewType = ($_GET['viewType'] && $_GET['viewType'] == 'grid' || $_GET['viewType'] == 'list')?$_GET['viewType']:'grid';
        $page = intval($_GET['pageNum'])?intval($_GET['pageNum']):1;
        $orderType = intval($_GET['orderType'])?intval($_GET['orderType']):0;
        return $this->gallery($store_id,$urlFilter,$viewType,$page,$orderType);
    }
    
    /**
     * compile_id 生成缓存ID
     * @author 曹辰吟
     * @param  [string] $path 模版文件
     * @return [string] 缓存ID
     */
    function compile_id($path){
        $store_id = $this->store['$store_id'];
        ksort($this->_tpl_key_prefix);
        //讲店铺ID加入缓存ID的生成
        return md5($store_id.$path.serialize($this->_tpl_key_prefix));
    }
}