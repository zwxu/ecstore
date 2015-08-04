<?php



class b2c_mdl_goods extends dbeav_model{
    var $has_tag = true;
    var $defaultOrder = array('d_order',' DESC',',goods_id',' DESC');
    var $has_many = array(
        'product' => 'products:contrast',
        'rate' => 'goods_rate:replace:goods_id^goods_1',
        'keywords'=>'goods_keywords:replace',
        'images' => 'image_attach@image:contrast:goods_id^target_id',
        'tag'=>'tag_rel@desktop:replace:goods_id^rel_id',
        'dlytypes'=>'goods_dly@b2c:replace:goods_id^goods_id',
    );
    var $has_one = array(

    );
    var $subSdf = array(
            'default' => array(

                'keywords'=>array('*'),
                'product'=>array(
                    '*',array(
                        'price/member_lv_price'=>array('*')
                    )
                ),
                ':goods_type'=>array(
                    '*'
                ),
                ':goods_cat'=>array(
                    '*'
                ),
/*                'tag'=>array(
                    '*',array(
                        ':tag'=>array('*')
                    )
                ),*/
                'images'=>array(
                    '*',array(
                        ':image'=>array('*')
                    )
                ),
                'dlytypes'=>array('*','manual'=>'normal'),
            ),
            'delete' => array(

                'keywords'=>array('*'),
                'product'=>array(
                    '*',array(
                        'price/member_lv_price'=>array('*')
                    )
                ),
                'images'=>array(
                    '*'
                 ),
                 'dlytypes'=>array('*','manual'=>'normal'),
            )
        );

    function __construct($app){
        parent::__construct($app);
        //使用meta系统进行存储
        $this->use_meta();
    }

    public function goods_meta_register() {
        //2013-9-30 xuz 添加是否推荐，是否新品两个字段
        $columns = array(0 => array(
                'is_tui' => array(
                    'type' => 'varchar(4)',
                    'required' => false,
                    'label' => __(' 是否推荐'),
                    'width' => 110,
                    'editable' => false,
                )), 1 => array(
                'is_new' => array(
                    'type' => 'varchar(4)',
                    'required' => false,
                    'label' => __(' 是否新品'),
                    'width' => 110,
                    'editable' => false,
                )),
        );
        $result = array();
        foreach ($columns as $column) {
            $this->meta_register($column);
        }
        //return $result;
    }

    var $ioSchema = array(
        'csv' => array(
            'bn:商品编号'=> 'bn',
            'ibn:规格货号' => array('bn','product'),
            'col:分类' => 'category/cat_id',
            'col:品牌' => 'brand/brand_id',
            'keywords:商品关键字' => 'keywords',
            'col:市场价' => array('price/mktprice/price','product'),
            'col:成本价' => array('price/cost/price','product'),
            'col:销售价' => array('price/price/price','product'),
            'col:缩略图' => 'thumbnail_pic',
            'col:图片文件' => '',
            'col:商品名称' => 'name',
            'col:上架' => 'status',
            'col:规格' => 'spec',
            'col:商品简介' => 'brief',
            'col:详细介绍' => 'description',
            'col:重量' => array('weight','product'),
            'col:单位' => 'unit',
            'col:库存' => array( 'store','product' )
        )
    );

    function io_title( $filter,$ioType='csv' ){
//        if( $this->ioTitle['csv'][$filter['type_id']] )
//            return $this->ioTitle['csv'][$filter['type_id']];
        $title = array();
        switch( $ioType ){
            case 'csv':
            default:
                $oGtype = $this->app->model('goods_type');
                if( $this->csvExportGtype[$filter['type_id']] )
                    $gType = $this->csvExportGtype[$filter['type_id']];
                else
                    $gType = $oGtype->dump($filter['type_id'],'*');
                $this->oSchema['csv'][$filter['type_id']] = array(
                    '*:'.$gType['name']=>'type/name',
                    app::get('b2c')->_('bn:商品编号') => 'bn',
                    app::get('b2c')->_('ibn:规格货号') => array('bn','product'),
                    app::get('b2c')->_('col:分类') => 'category/cat_name',
                    app::get('b2c')->_('col:品牌') => 'brand/brand_name',
                    app::get('b2c')->_('keywords:商品关键字') => 'keywords',
                    app::get('b2c')->_('col:市场价') => array('price/mktprice/price','product'),
                    app::get('b2c')->_('col:成本价') => array('price/cost/price','product'),
                    app::get('b2c')->_('col:销售价') => array('price/price/price','product'),
                    app::get('b2c')->_('col:缩略图') => 'thumbnail_pic',
                    app::get('b2c')->_('col:图片文件') => '',
                    app::get('b2c')->_('col:商品名称') => 'name',
                    app::get('b2c')->_('col:上架') => 'status',
                    app::get('b2c')->_('col:规格') => 'spec',
                    app::get('b2c')->_('col:库存') => 'store'
                );
                $oMlv = $this->app->model('member_lv');
                foreach( $oMlv->getList() as $mlv ){
                    $this->oSchema['csv'][$filter['type_id']]['price:'.$mlv['name']] = 'price/member_lv_price/'.$mlv['member_lv_id'].'/price';
                }
                $this->oSchema['csv'][$filter['type_id']] = array_merge(
                    $this->oSchema['csv'][$filter['type_id']],
                    array(
                        app::get('b2c')->_('col:商品简介') => 'brief',
                        app::get('b2c')->_('col:详细介绍') => 'description',
                        app::get('b2c')->_('col:重量') => 'weight',
                        app::get('b2c')->_('col:单位') => 'unit',
                    )
                );
                foreach( (array)$gType['props'] as $propsK => $props ){
                    $this->oSchema['csv'][$filter['type_id']]['props:'.$props['name']] = 'props/p_'.$propsK;
                }
                foreach( (array)$gType['params'] as $paramGroup => $paramItem ){
                    foreach( (array)$paramItem as $paramName => $paramValue ){
                        $this->oSchema['csv'][$filter['type_id']]['params:'.$paramGroup.'->'.$paramName] = 'params/'.$paramGroup.'/'.$paramName;
                    }
                }
                break;
        }
        $this->ioTitle['csv'][$filter['type_id']] = array_keys($this->oSchema['csv'][$filter['type_id']]);
        return $this->ioTitle['csv'][$filter['type_id']];
    }

    //function fetchkv( $goods_id ){
    //    if( base_kvstore::instance('b2c_goods')->fetch('b2c_goods_'.implode('_',$goods_id),$contents) === false )
    //        return false;
    //    return $contents;
    //}
    function dump($filter,$field = '*',$subSdf = null){
        $dumpData = &parent::dump($filter,$field,$subSdf);
        $oSpec = &$this->app->model('specification');
        if( $dumpData['spec_desc'] && is_array( $dumpData['spec_desc'] ) ){
            foreach( $dumpData['spec_desc'] as $specId => $spec ){
                $dumpData['spec'][$specId] = $oSpec->dump($specId,'*');
                foreach( $spec as $pSpecId => $specValue ){
                    $dumpData['spec'][$specId]['option'][$pSpecId] = array_merge( array('private_spec_value_id'=>$pSpecId), $specValue );
                }
            }
        }

        unset($dumpData['spec_desc']);
        if( $dumpData['product'] ){
            $aProduct = current( $dumpData['product']);
            if( isset( $aProduct['price']['price']['current_price'] ) )
                $dumpData['current_price'] = $aProduct['price']['price']['current_price'];
        }else{
            if( $dumpData['price'] )
                $dumpData['current_price'] = $dumpData['price'];
        }
        return $dumpData;
    }

    function _filter($filter,$tbase=''){
        //如果filter条件是直接可以只用的则在条件中增加 str_where参数,直接返回
        if(isset($filter['str_where']) && $filter['str_where']){
            return $filter['str_where'];
        }
        foreach(kernel::servicelist('b2c_mdl_goods.filter') as $k=>$obj_filter){
            if(method_exists($obj_filter,'extend_filter')){
                $obj_filter->extend_filter($filter);
            }
        }
        if($this->use_meta){
            foreach(array_keys((array)$filter) as $col){
                if(in_array(strval($col),$this->metaColumn)){
                    $meta_filter[$col] = $filter[$col];
                    unset($filter[$col]);
                    $obj_meta = new dbeav_meta($this->table_name(true),$col);
                    $meta_filter_ret .= $obj_meta->filter($meta_filter);
                }
            }
        }
        $b2c_goods_filter = kernel::single('b2c_goods_filter');
        $b2c_goods_filter_ret = $b2c_goods_filter->goods_filter($filter, $this);
        if($this->use_meta){
            return $b2c_goods_filter_ret.$meta_filter_ret;
        }
        return $b2c_goods_filter_ret;
    }

    function wFilter($words){
        $replace = array(",","+");
        $enStr=preg_replace("/[^chr(128)-chr(256)]+/is"," ",$words);
        $otherStr=preg_replace("/[chr(128)-chr(256)]+/is"," ",$words);
        $words=$enStr.' '.$otherStr;
        $return=str_replace($replace,' ',$words);
        $word=preg_split('/\s+/s',trim($return));
        $GLOBALS['search_array']=$word;
        foreach($word as $k=>$v){
            if($v){
                $goodsId = array();
                foreach($this->getGoodsIdByKeyword(array($v)) as $idv)
                    $goodsId[] = $idv['goods_id'];
                foreach( $this->db->select('SELECT goods_id FROM sdb_b2c_products WHERE bn = \''.trim(addslashes($v)).'\' ') as $pidv)
                    $goodsId[] = $pidv['goods_id'];
                $sql[]='(`sdb_b2c_goods`.name LIKE \'%'.$word[$k].'%\' or `sdb_b2c_goods`.bn like \''.$word[$k].'%\' '.( $goodsId?' or `sdb_b2c_goods`.goods_id IN ('.implode(',',$goodsId).') ':'' ).')';
            }
        }

        return '('.implode('or',$sql).')';
    }
    function getGoodsIdByKeyword($keywords , $searchType = 'tequal'){
        foreach( $keywords as $k => &$v ){
            $v = addslashes($v);
        }
        $where = '';
        if($keywords&&!is_array($keywords))  $keywords = array($keywords);
        switch( $searchType ){
            case 'has':
                $where = ' keyword LIKE "%'.implode( '%" AND keyword LIKE "%' ,$keywords ).'%" ';
                //like
                break;
            case 'nohas':
                $where = ' keyword NOT LIKE "%'.implode( '%" AND keyword NOT LIKE "%' ,$keywords ).'%" ';
                // not like
                break;
            case 'tequal':
            default:
                $where = ' keyword in ( "'.implode('","',$keywords).'" ) ';
                break;
        }
        return $this->db->select('SELECT goods_id FROM sdb_b2c_goods_keywords WHERE '.$where);
    }
    function save(&$goods,$mustUpdate = null){
        if( !$goods['bn'] ) $goods['bn'] = strtoupper(uniqid('g'));
        if( array_key_exists( 'spec',$goods ) ){
            if( $goods['spec'] )
                foreach( $goods['spec'] as $gSpecId => $gSpecOption ){
                    $goods['spec_desc'][$gSpecId] = $gSpecOption['option'];
                }
            else
                $goods['spec_desc'] = null;
        }
        $goodsStatus = false;
        $store = 0;
        is_array($goods['product']) or $goods['product'] = array();
        $bnList = array();
        foreach( $goods['product'] as $pk => $pv ){
            if( $goods['goods_type'] ) //product add goods_type default normal
                $goods['product'][$pk]['goods_type'] = $goods['goods_type'];


            if( !$pv['bn'] ) $goods['product'][$pk]['bn'] = strtoupper(uniqid('p', true));
            if( array_key_exists( $goods['product'][$pk]['bn'],$bnList ) ){
                return null;
            }
            $bnList[$goods['product'][$pk]['bn']] = 1;
            $goods['product'][$pk]['name'] = $goods['name'];
            if( $pv['status'] != 'false' ) $goodsStatus = true;
            if( $pv['store'] === null || $pv['store'] === '' ){
                $store = null;
            }else{
				if ($store !== null)
                $store += $pv['store'];
            }
            
            $p_memo .= ",货品(".$goods['product'][$pk]['bn'].")库存为({$pv['store']})销售价为({$pv['price']['price']['price']})市场价为({$pv['price']['mktprice']['price']})";
        }
        if($goods['product']) {
            $goods['store'] = $store;
            if( !$goodsStatus && !$goods['status'])
                $goods['status'] = 'false';
        }
        else {
            unset($goods['product']);
        }
        unset($goods['spec']);

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录添加商品日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'inlogs')){
                if(empty($goods['goods_id'])){
                    $memo = '添加新商品,名称为 "'.$goods['name'].'"'.$p_memo;
                    $obj_operatorlogs->inlogs($memo, '添加商品', 'goods');
                }
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录添加商品日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录编辑商品日志-start@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.b2c_mdl_goods')){
            $addorrestore_goods_flag = false;
            if(empty($goods['goods_id'])){//添加商品则为空
                $addorrestore_goods_flag = true;
            }else{//回收站恢复商品时判断
                $isindb = $this->getList('goods_id',array('goods_id'=>$goods['goods_id']));
                if(!$isindb['0']['goods_id']){
                    $addorrestore_goods_flag = true;
                }
            }
            if(method_exists($obj_operatorlogs,'logGoodsStart')){
                if(isset($addorrestore_goods_flag) && !$addorrestore_goods_flag){
                    $obj_operatorlogs->logGoodsStart($goods['goods_id']);
                }
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录编辑商品日志-end@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        $rs = parent::save($goods,$mustUpdate);

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录编辑商品日志-start@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.b2c_mdl_goods')){
            if(method_exists($obj_operatorlogs,'logGoodsEnd')){
                if(isset($addorrestore_goods_flag) && !$addorrestore_goods_flag){
                    $obj_operatorlogs->logGoodsEnd($goods);
                }
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录编辑商品日志-end@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		/** 保存数据到kvstore **/
		//if ($rs){
		//	$this->storekv_product_info($goods['goods_id'],$goods);
		//}
        //商品下架则相应的货品也要下架 @lujy--start-
        if($rs && ($goods['status'] == 'false')){
            $objpro = $this->app->model('products');
            $objpro->pro_unmarketable($goods['goods_id'],$goods['status']);
        }
        //--end-
       if($goods['product']) {
            $this->createSpecIndex($goods);
       }
        if( $goods['goods_id'] )
            $this->createKvStore( array( 'goods_id'=> $goods['goods_id']) );
        return $rs;
    }

    function createKvStore( $filter ){
        $sdf = $this->dump( $filter,'*','default' );
        ksort( $filter );
        base_kvstore::instance('b2c_goods')->store('b2c_goods_'.implode('_',$filter),$sdf);
        return true;
    }

    function createSpecIndex($goods){
        $oSpecIndex = &$this->app->model('goods_spec_index');
        $oSpecIndex->delete( array('goods_id'=>$goods['goods_id']) );
        foreach( $goods['product'] as $pro ){
            if( $pro['spec_desc'] ){
                foreach( $pro['spec_desc']['spec_value_id'] as $specId => $specValueId ){
                    $data = array(
                        'type_id' => $goods['type']['type_id'],
                        'spec_id' => $specId,
                        'spec_value_id' => $specValueId,
                        'goods_id' => $goods['goods_id'],
                        'product_id' => $pro['product_id'],
                    );
                    $oSpecIndex->save($data);
                }
            }
        }
    }

    function delete($filter){
        $rs = parent::delete($filter);
        if( $rs ){
            $oSpecIndex = &$this->app->model('goods_spec_index');
            $oSpecIndex->delete( $filter );
        }
        base_kvstore::instance('b2c_goods')->delete('b2c_goods_'.implode('_',$filter));
        return $rs;
    }

    /**
     * @params string goods_id
     * @params string product_id
     * @params string num
     */
    public function unfreez($goods_id, $product_id, $num){
        $objGoods = &$this->app->model('goods');
        $nostore = $objGoods->dump($goods_id, 'nostore_sell');
        $oPro = &$this->app->model('products');
        $sdf_pdt = $oPro->dump($product_id, 'freez,store');
        $objMath = kernel::single('ectools_math');

        if(is_null($sdf_pdt['freez']) || $sdf_pdt['freez'] === ''){
            if (is_null($sdf_pdt['store']) || $sdf_pdt['store'] === '')
                return true;

            $sdf_pdt['freez'] = 0;
        }elseif($num < $sdf_pdt['freez']){
            $sdf_pdt['freez'] = $objMath->number_minus(array($sdf_pdt['freez'], $num));
        }elseif($num >= $sdf_pdt['freez']){
            $sdf_pdt['freez'] = 0;
        }
        $sdf_pdt['product_id'] = $product_id;
        $sdf_pdt['last_modify'] = time();

        return $oPro->save($sdf_pdt);
    }


    function diff($gid){
        $oGtype = &$this->app->model('goods_type');
        $oGtypeprops = &$this->app->model('goods_type_props');
        $oGtypeValue = &$this->app->model('goods_type_props_value');
        if(!$gid) return array();
        foreach($gid as $t=>$v){
                $gid[$t]=intval($v);
        }

        $params = $this->getList('*',array('goods_id'=>$gid));
        foreach ($params as &$val) {
                $temp = $this->dump($val['goods_id'],'goods_id',array(
                    'product'=>array(
                        'product_id, spec_info, price, freez, store, goods_id',
                        array('price/member_lv_price'=>array('*'))
                    )
                )
                );
                $val['spec_desc_info'] = $temp['product'];
                if(is_array($temp['product']))
                    $tempPro = current( $temp['product'] );
                $val['current_price'] = $tempPro['price']['price']['current_price'];
        }

        $params2 = $this->db->select('select * from sdb_b2c_goods as A Left Join sdb_b2c_goods_type as B ON A.type_id = B.type_id where A.goods_id in ('.implode(',',$gid).')');
        foreach($params2 as $i=>$p){
            $props = $oGtypeprops->getList('props_id,goods_p,name',array('type_id'=>$p['type_id']));
            if(is_array($props)){
                foreach($props as $pk=>$pv){
                    if(isset($pv['goods_p'])){
                           $name = $oGtypeValue->dump($p['p_'.$pv['goods_p']],'name');
                           $p_map[app::get('b2c')->_('基本属性')][$pv['name']][$p['goods_id']] = $name['name'];
                    }
                }
            }
        }
        foreach($params as $i=>$p){
            if(is_string($params[$i]['params']))
                $params[$i]['params']=unserialize($params[$i]['params']);
            if(is_string($params2[$i]['params']))
                $params2[$i]['params']=unserialize($params2[$i]['params']);
            $params[$i]['pdt_desc']=$params[$i]['pdt_desc'];
     /*       foreach($params[$i]['params'] as $group=>$items){
                    foreach($items as $p_name=>$v){
                        if(isset($params2[$i]['params'][$group][$p_name])){
                             $p_map[$group][$p_name][$p['goods_id']] = $v;
                        }
                    }

            }*/

        }
        return array('params'=>$p_map,'length'=>floor(80/count($gid)),'colp'=>count($gid)+1,'goods'=>$params,'cols'=>count($params)+1,'width'=>floor(100/(count($params)+1)).'%');
    }

    /**
     * 冻结产品的库存
     * @params string goods_id
     * @params string product_id
     * @params string num
     */
    public function freez($goods_id, $product_id, $num)
    {
        $objGoods = &$this->app->model('goods');
        $nostore = $objGoods->dump($goods_id, 'nostore_sell');
        if($nostore['nostore_sell'])   return true;
        $oPro = &$this->app->model('products');
        $sdf_pdt = $oPro->dump($product_id, 'freez,store');
        $objMath = kernel::single('ectools_math');

        if(is_null($sdf_pdt['freez']) || $sdf_pdt['freez'] === ''){
            if (is_null($sdf_pdt['store']) || $sdf_pdt['store'] === '')
                return true;

            $sdf_pdt['freez'] = 0;
            $sdf_pdt['freez'] = $objMath->number_plus(array($sdf_pdt['freez'], $num));
            //$sdf_pdt['freez'] += $num;
            if ($sdf_pdt['freez'] > $sdf_pdt['store'])
                return false;
        }elseif($objMath->number_plus(array($sdf_pdt['freez'], $num)) > $sdf_pdt['store'] ){
            //$sdf_pdt['freez'] = $sdf_pdt['store'];
            return false;
        }else{
            $sdf_pdt['freez'] = $objMath->number_plus(array($sdf_pdt['freez'], $num));
            //$sdf_pdt['freez'] += $num;
        }

        $sdf_pdt['product_id'] = $product_id;
        $sdf_pdt['last_modify'] = time();

        return $oPro->save($sdf_pdt);
    }

    /**
     * 检查货品是否可以冻结库存
     * @param string goods_id
     * @param string product_id
     * @param string num
     * @return boolean true or false
     */
    public function check_freez($goods_id, $product_id, $num)
    {
        $objGoods = &$this->app->model('goods');
        $nostore = $objGoods->dump($goods_id, 'nostore_sell,store,store_freeze');
        // 支持无库存销售
        if($nostore['nostore_sell'])   return true;
        $oPro = &$this->app->model('products');
        $sdf_pdt = $oPro->dump($product_id, 'freez,store');
        $objMath = kernel::single('ectools_math');

        if(is_null($sdf_pdt['freez']) || $sdf_pdt['freez'] === ''){
            // 无限库存
            if (is_null($sdf_pdt['store']) || $sdf_pdt['store'] === '')
                return true;

            $sdf_pdt['freez'] = 0;
            $sdf_pdt['freez'] = $objMath->number_plus(array($sdf_pdt['freez'], $num));
            if ($sdf_pdt['freez'] > $sdf_pdt['store'])
                return false;
        }elseif($objMath->number_plus(array($sdf_pdt['freez'], $num)) > $sdf_pdt['store'] ){
            return false;
        }else{
            $sdf_pdt['freez'] = $objMath->number_plus(array($sdf_pdt['freez'], $num));
        }

        //增加总库存判断
        if(empty($nostore['store_freeze'])){
            $nostore['store_freeze'] = 0;
        }
        if(($nostore['store'] - $num - $nostore['store_freeze']) <0 ){
            return false;
        }
        //增加总库存判断
        return true;
    }

    function orderBy($id=null){
        $order=array(//更新单一维度排序时如果排序相同则按照评分排序。
           1=>array('label'=>app::get('b2c')->_('默认')),
           2=>array('label'=>app::get('b2c')->_('按发布时间 新->旧'),'sql'=>'last_modify desc,avg_point desc'),
           3=>array('label'=>app::get('b2c')->_('按发布时间 旧->新'),'sql'=>'last_modify ,avg_point desc'),
           4=> array('label'=>app::get('b2c')->_('按价格 从高到低'),'sql'=>'price desc,avg_point desc'),
           5=>array('label'=>app::get('b2c')->_('按价格 从低到高'),'sql'=>'price,avg_point desc'),
           6=>array('label'=>app::get('b2c')->_('访问周次数'),'sql'=>'view_w_count desc,avg_point desc'),
           7=> array('label'=>app::get('b2c')->_('总访问次数'),'sql'=>'view_count desc,avg_point desc'),
           8=>array('label'=>app::get('b2c')->_('周购买次数'),'sql'=>'buy_w_count desc,avg_point desc'),
           9=> array('label'=>app::get('b2c')->_('总购买次数'),'sql'=>'buy_count desc,avg_point desc'),
           10=> array('label'=>app::get('b2c')->_('评论次数'),'sql'=>'comments_count desc,avg_point desc'),
           11=> array('label'=>app::get('b2c')->_('月购买次数'),'sql'=>'buy_m_count desc,avg_point desc'),
        );
        if($this->app->getConf('gallery.deliver.time')=='false'){
            unset($order[2]);
            unset($order[3]);
        }
        if($this->app->getConf('gallery.comment.time')=='false'){
            unset($order[10]);
        }
        if($id){
            return $order[$id];
        }else{
            return $order;
        }
    }

    function prepared_import_csv_row($row,$title,&$goodsTmpl,&$mark,&$newObjFlag,&$msg){
        if( substr($row[0],0,1) == '*' ){
            $mark = 'title';
            $newObjFlag = true;

            $oGType = &$this->app->model('goods_type');
            $goodsTmpl['gtype'] = $oGType->dump(array('name'=>ltrim($row[0],'*:')),'*',array(
            'brand' => array('*'),
            'spec' => array('*'),
            'props'=>array('*',array('props_value'=>array('*',null, array( 0,-1,'order_by ASC' ))) )) );
            if( !$goodsTmpl['gtype'] ){
                $msg = array('error'=>app::get('b2c')->_('商品类型:').ltrim( $row[0],'*:' ).app::get('b2c')->_(' 不存在'));
                return false;
            }

            if( $goodsTmpl['gtype']['props'] ){
                foreach( $goodsTmpl['gtype']['props'] as $propsk => $props ){
                    $this->ioSchema['csv']['props:'.$props['name']] = 'props/p_'.$propsk.'/value';
                    foreach( $props['options'] as $p => $v ){
                        $goodsTmpl['props_hash'][$props['name']][$v] = $p;
                    }
                }
            }

            if( $goodsTmpl['gtype']['params'] ){
                foreach( (array)$goodsTmpl['gtype']['params'] as $paramGroup => $paramItem ){
                    foreach( (array)$paramItem as $paramName => $paramValue ){
                        $this->ioSchema['csv']['params:'.$paramGroup.'->'.$paramName] = 'params/'.$paramGroup.'/'.$paramName;
                    }
                }
            }
            $oMlv = &$this->app->model('member_lv');
            foreach( $oMlv->getList('member_lv_id,name','',0,-1) as $mlv ){
                $this->ioSchema['csv']['price:'.$mlv['name']] = array('price/member_lv_price/'.$mlv['member_lv_id'].'/price','product');
            }


            return array_flip($row);
        }else{
            $mark = 'contents';
            if( $row[$title[app::get('b2c')->_('ibn:规格货号')]] ){
                if( $this->io->proBn && array_key_exists( $row[$title[app::get('b2c')->_('ibn:规格货号')]] , $this->io->proBn ) ){
                    $msg = array( 'error'=>app::get('b2c')->_('规格货号:').$row[$title[app::get('b2c')->_('ibn:规格货号')]].app::get('b2c')->_(' 文件中有重复') );
                    return false;
                }
                $this->io->proBn[$row[$title[app::get('b2c')->_('ibn:规格货号')]]] = null;
            }


            if( !$row[$title[app::get('b2c')->_('ibn:规格货号')]] || in_array($row[$title[app::get('b2c')->_('col:规格')]],array('','-')) ){
                $newObjFlag = true;
            }


            return $row;
        }
    }

    function ioSchema2sdf($data,$title,$csvSchema,$key = null){
        $rs = array();
        $subSdf = array();
        foreach( $csvSchema as $schema => $sdf ){
            $sdf = (array)$sdf;
            if( ( !$key && !$sdf[1] ) || ( $key && $sdf[1] == $key ) ){
                eval('$rs["'.implode('"]["',explode('/',$sdf[0])).'"] = $data[$title[$schema]];');
                unset($data[$title[$schema]]);
            /*}else if( ){
                eval('$rs["'.implode('"]["',explode('/',$sdf[0])).'"] = $data[$title[$schema]];');
                unset($data[$title[$schema]]);*/
            }else{
                $subSdf[$sdf[1]] = $sdf[1];
            }
        }
        if(!$key){
            foreach( $subSdf as $k ){
                foreach( $data[$k] as $v ){
                    $rs[$k][] = $this->ioSchema2sdf($v,$title,$csvSchema,$k);
                }
            }
        }
        foreach( $data as $orderk => $orderv ){
            if( substr($orderk,0,4 ) == 'col:' ){
                $rs[ltrim($orderk,'col:')] = $orderv;
            }
        }
        return $rs;

    }

    function checkProductBn($bn, $gid=0){
        if(empty($bn)){
            return false;
        }
		$gid = intval($gid);
		$bn = $this->db->quote($bn);
        if($gid){
            $sql = 'SELECT count(*) AS num FROM sdb_b2c_products WHERE bn = '.$bn.' AND goods_id != '.$gid;
            $Gsql = 'SELECT count(*) AS num FROM sdb_b2c_goods WHERE bn = '.$bn.' AND goods_id != '.$gid;
        }else{
            $sql = 'SELECT count(*) AS num FROM sdb_b2c_products WHERE bn = '.$bn;
            $Gsql = 'SELECT count(*) AS num FROM sdb_b2c_goods WHERE bn = '.$bn;
        }
        $aTmp = $this->db->select($sql);
        $GaTmp = $this->db->select($Gsql);
        return $aTmp[0]['num']+$GaTmp[0]['num'];
    }

    function prepared_import_csv_obj($data,&$mark,$goodsTmpl,&$msg = ''){
        if( !$data['contents'] )return null;
        $mark = 'contents';
        $gData = &$data['contents'];
        $gTitle = $data['title'];
        $rs = array();
        //id
        if( $this->io->goodsBn && array_key_exists( $gData[0][$gTitle[app::get('b2c')->_('bn:商品编号')]] , $this->io->goodsBn ) ){
            $msg = array( 'error'=>app::get('b2c')->_('商品编号:').$gData[0][$gTitle[app::get('b2c')->_('bn:商品编号')]].app::get('b2c')->_(' 文件中有重复') );
            return false;
        }

        $goodsId = $this->dump(array('bn'=>$gData[0][$gTitle[app::get('b2c')->_('bn:商品编号')]]),'goods_id');
        if( $goodsId['goods_id'] )
            $gData[0]['col:goods_id'] = $goodsId['goods_id'];

        $gData[0][$gTitle[app::get('b2c')->_('col:上架')]] = (in_array( trim( $gData[0][$gTitle[app::get('b2c')->_('col:上架')]] ), array('Y','TRUE') )?'true':'false');

        foreach( $gTitle as $colk => $colv ){
            if( substr( $colk, 0,6 ) == 'props:' ){
                if( !$this->ioSchema['csv'][$colk] )
                    $msg['warning'][] = app::get('b2c')->_('属性：').ltrim($colk,'props:').app::get('b2c')->_('不存在');
                else{
                    if( $goodsTmpl['props_hash'][ltrim($colk,'props:')] && $gData[0][$gTitle[$colk]] && !array_key_exists( $gData[0][$gTitle[$colk]], $goodsTmpl['props_hash'][ltrim($colk,'props:')] ) )
                        $msg['warning'][] = app::get('b2c')->_('属性值：').$gData[0][$gTitle[$colk]].app::get('b2c')->_('不存在');
                    if( $goodsTmpl['props_hash'][ltrim($colk,'props:')] )
                    $gData[0][$gTitle[$colk]] = $goodsTmpl['props_hash'][ltrim($colk,'props:')][$gData[0][$gTitle[$colk]]];
                }
            }
            if( (substr( $colk,0,6 ) == 'price:' || in_array( $colk , array(app::get('b2c')->_('col:市场价'),app::get('b2c')->_('col:成本价'),app::get('b2c')->_('col:销售价')) ) ) && $gData[0][$gTitle[$colk]] !== 0 && !$gData[0][$gTitle[$colk]] ){
                unset($gData[0][$gTitle[$colk]]);
            }
        }

        //分类
        $catPath = array();
        $oCat = &$this->app->model('goods_cat');
        $catId = 0;
        foreach( explode( '->',$gData[0][$gTitle[app::get('b2c')->_('col:分类')]] ) as $catName ){
            $aCatId = $oCat->dump(array('cat_name'=>$catName,'parent_id'=>$catId),'cat_id');
            if( $aCatId )
                $catId = $aCatId['cat_id'];
            else
                $catId = 0;
        }
        $catId = $oCat->dump($catId,'cat_id');
        if( $gData[0][$gTitle[app::get('b2c')->_('col:分类')]] && !$catId['cat_id'] )
            $msg['warning'][] = app::get('b2c')->_('分类：').$gData[0][$gTitle[app::get('b2c')->_('col:分类')]].app::get('b2c')->_('不存在');
        $gData[0][$gTitle[app::get('b2c')->_('col:分类')]] = intval( $catId['cat_id']);

        //品牌
        $oBrand = &$this->app->model('brand');
        if( !$gData[0][$gTitle[app::get('b2c')->_('col:品牌')]] ){
            $brandId = array('brand_id'=>0);
        }else{
            $brandId = $oBrand->dump(array('brand_name'=>$gData[0][$gTitle[app::get('b2c')->_('col:品牌')]]),'brand_id');
            if( !$brandId['brand_id'] && $gData[0][$gTitle[app::get('b2c')->_('col:品牌')]] )
                $msg['warning'][] = app::get('b2c')->_('品牌：').$gData[0][$gTitle[app::get('b2c')->_('col:品牌')]].app::get('b2c')->_('不存在');
        }
        $gData[0][$gTitle[app::get('b2c')->_('col:品牌')]] = intval( $brandId['brand_id'] );

        //货品 处理return值
        $rs = $gData[0];
        $oPro = &$this->app->model('products');
        $spec = array();
        if( count( $gData ) == 1 ){
            unset($rs[$gTitle[app::get('b2c')->_('col:规格')]] );
            if( !$gData[0][$gTitle[app::get('b2c')->_('ibn:规格货号')]] )
                $gData[0][$gTitle[app::get('b2c')->_('ibn:规格货号')]] = $gData[0][$gTitle[app::get('b2c')->_('bn:商品编号')]];
            $proId = $oPro->dump( array('bn'=>$gData[0][$gTitle[app::get('b2c')->_('ibn:规格货号')]] ),'product_id,goods_id' );

            if( ( !$rs['col:goods_id'] && $proId['product_id'] ) || ( $rs['col:goods_id'] && $rs['col:goods_id'] != $proId['goods_id'] ) ){
                $msg = array( 'error'=>app::get('b2c')->_('规格货号:').$gData[0][$gTitle[app::get('b2c')->_('bn:商品编号')]].app::get('b2c')->_(' 已存在' ));
                return false;
            }

            $rs['product'][0] = $gData[0];
            if( $proId['product_id'] )
                $rs['product'][0]['col:product_id'] = $proId['product_id'];
        }else{

            reset($gData);
            $oSpec = &$this->app->model('specification');
            foreach( explode('|',$gData[0][$gTitle[app::get('b2c')->_('col:规格')]] ) as $speck => $specName ){
                $spec[$speck] = array(
                    'spec_name' => $specName,
                    'option' => array(),
                );
            }

            while( ( $aPro = next($gData) ) ){
                $aProk = key( $gData );
                $proId = $oPro->dump( array('bn'=>$aPro[$gTitle[app::get('b2c')->_('ibn:规格货号')]]),'product_id,goods_id' );

                if( ( !$rs['col:goods_id'] && $proId['product_id'] ) || ( $rs['col:goods_id'] && $rs['col:goods_id'] != $proId['goods_id'] ) ){
                    $msg = array( 'error'=>app::get('b2c')->_('规格货号:').$aPro[$gTitle[app::get('b2c')->_('ibn:规格货号')]].app::get('b2c')->_(' 已存在' ));
                    return false;
                }
                $aPro['col:product_id'] = $proId['product_id'];
                $rs['product'][$aProk] = $aPro;
                foreach( explode('|',$aPro[$gTitle[app::get('b2c')->_('col:规格')]]) as $specvk => $specv ){
                    $spec[$specvk]['option'][$specv] = $specv;
                }
//                $gData[$aProk]['']
            }
            foreach($spec as $sk => $aSpec){
                $specIdList = $oSpec->getSpecIdByAll($aSpec);
                foreach( $specIdList as $sv ){
                    if( array_key_exists($sv['spec_id'],(array)$goodsTmpl['gtype']['spec'] ) ){
                        $spec[$sk]['spec_id'] = $sv['spec_id'];
                    }
                }
                if( !$spec[$sk]['spec_id'] )
                    $spec[$sk]['spec_id'] = $specIdList[0]['spec_id'];
                if( !$spec[$sk]['spec_id'] ){
                    $msg = array('error'=>app::get('b2c')->_('规格：').$aSpec['spec_name'].app::get('b2c')->_('出现错误 请检查') );
                    return false;
                }
                $spec[$sk]['option'] = $oSpec->getSpecValuesByAll($spec[$sk]);
            }
            $pItem = 0;

            foreach( $rs['product'] as $prok => $prov ){

                if( !($pItem++) )$rs['product'][$prok]['col:default'] = 1;
                $proSpec = explode('|',$prov[$gTitle[app::get('b2c')->_('col:规格')]]);
                $rs['product'][$prok]['col:spec_info'] = implode(',',$proSpec);

                foreach( $proSpec as $aProSpeck => $aProSpec ){
//                    foreach( $spec as $aaSpec ){
                    $rs['product'][$prok]['col:spec_desc']['spec_value'][$spec[$aProSpeck]['spec_id']] = $spec[$aProSpeck]['option'][$aProSpec]['spec_value'];
                    $rs['product'][$prok]['col:spec_desc']['spec_private_value_id'][$spec[$aProSpeck]['spec_id']] = $spec[$aProSpeck]['option'][$aProSpec]['private_spec_value_id'];
                    $rs['product'][$prok]['col:spec_desc']['spec_value_id'][$spec[$aProSpeck]['spec_id']] = $spec[$aProSpeck]['option'][$aProSpec]['spec_value_id'];
//                    }
                }
            }

            unset( $rs[$gTitle[app::get('b2c')->_('col:规格')]] );
            foreach( $spec as $sk => $sv ){
                foreach( $sv['option'] as $psk => $psv ){
                    $rs[$gTitle[app::get('b2c')->_('col:规格')]][$sv['spec_id']]['option'][$psv['private_spec_value_id']] = $psv;
                }
            }
       }

        $return =  $this->ioSchema2sdf( $rs,$gTitle, $this->ioSchema['csv'] );
        if( $gData[0][$gTitle[app::get('b2c')->_('col:缩略图')]] ){
            $oImage = &app::get('image')->model('image');

            $image = explode('@',$gData[0][$gTitle[app::get('b2c')->_('col:缩略图')]] );
            if( count($image) == 2 ){
                $imageId = $image[0];
                $image = $image[1];
				$return['udfimg'] = 'true';
            }else{
                $imageId = null;
                $image = $image[0];
            }
            if( substr($image,0,4 ) == 'http' ){
                $imageName = null;
            }else{
                $imageName = null;
                $image = ROOT_DIR.'/'.$image;
            }
            if( $imageId && !$oImage->dump($imageId) )
                $imageId = null;
            $imageId = $oImage->store($image,$imageId,null,$imageName);

            $return['thumbnail_pic'] = $imageId;
        }

        if( $gData[0][$gTitle[app::get('b2c')->_('col:图片文件')]] ){
            $oImage = &app::get('image')->model('image');
            $i = 0;
            foreach( explode( '#', $gData[0][$gTitle[app::get('b2c')->_('col:图片文件')]] ) as $image ){
                $image = explode('@',$image);
                if( count($image) == 2 ){
                    $imageId = $image[0];
                    $image = $image[1];
                }else{
                    $imageId = null;
                    $image = $image[0];
                }
                if( substr($image,0,4 ) == 'http' ){
                    $imageName = null;
                }else{
                    $imageName = null;
                    $image = ROOT_DIR.'/'.$image;
                }
                if( $imageId && !$oImage->dump($imageId) )
                    $imageId = null;
                $imageId = $oImage->store($image,$imageId,null,$imageName);

                //商品批量上传图片大中小的处理
                $oImage->rebuild($imageId,array('L','M','S'));

                $return['images'][] = array(
                    'target_type'=>'goods',
                    'image_id'=>$imageId
                );
                if( $i++ == 0 ){
                    $return['image_default_id'] = $imageId;
                }

            }
        }

        if( trim( $gData[0][$gTitle[app::get('b2c')->_('keywords:商品关键字')]] ) ){
            $return['keywords'] = array();
            foreach( explode( '|', $gData[0][$gTitle[app::get('b2c')->_('keywords:商品关键字')]] ) as $kwk => $kwv ){
                $return['keywords'][] = array(
                    'keyword' => $kwv,
                    'res_type' => 'goods'
                );
            }
        }

        foreach( $rs['product'] as $prok => $prov ){
            if($prov[$gTitle[app::get('b2c')->_('col:上架')]] == 'N'){
                $return['product'][$prok-1]['status'] = 'false';
            }
            if($prov[$gTitle[app::get('b2c')->_('col:上架')]] == 'Y'){
                $return['product'][$prok-1]['status'] = 'true';
            }
        }
        foreach( $return['product'] as $pk => $pv ){
            $return['product'][$pk]['name'] = $return['name'];
            foreach( $pv['price']['member_lv_price'] as $lvk => $lvv ){
                if( $lvv['price'] === null || $lvv['price'] === '' ){
                    unset( $return['product'][$pk]['price']['member_lv_price'][$lvk] );
                    continue;
                }
                $return['product'][$pk]['price']['member_lv_price'][$lvk]['level_id'] = $lvk;
            }
        }

        $return['type']['type_id'] = intval( $goodsTmpl['gtype']['type_id'] );

        $this->io->goodsBn[$return['bn']] = null;

        return $return;
    }

    function getProducts($gid, $pid=0){
        $sqlWhere = '';
        if($pid > 0) $sqlWhere = ' AND A.product_id = '.$pid;
        $sql = "SELECT A.*,B.image_default_id FROM sdb_b2c_products AS A LEFT JOIN sdb_b2c_goods AS B ON A.goods_id=B.goods_id WHERE A.goods_id=".intval($gid).$sqlWhere;
        return $this->db->select($sql);
    }

    function getGoodsIdByBn( $bn , $searchType = 'has') {
		$like_bn = $this->db->quote('%'.$bn.'%');
		$left_like_bn = $this->db->quote('%'.$bn);
		$right_like_bn = $this->db->quote($bn.'%');
		$bn = $this->db->quote($bn);
        switch($searchType){
            case'nohas':
                $goodsId = $this->db->select('SELECT g.goods_id FROM sdb_b2c_goods g LEFT JOIN sdb_b2c_products p ON g.goods_id = p.goods_id WHERE g.bn NOT LIKE '.$like_bn.' OR p.bn NOT LIKE '.$like_bn);
                break;
            case'tequal':
                $goodsId = $this->db->select('SELECT g.goods_id FROM sdb_b2c_goods g LEFT JOIN sdb_b2c_products p ON g.goods_id = p.goods_id WHERE g.bn in( "'.$bn.'") OR p.bn in( "'.$bn.'")');
                break;
            case'has':
            default:
                $goodsId = $this->db->select('SELECT g.goods_id FROM sdb_b2c_goods g LEFT JOIN sdb_b2c_products p ON g.goods_id = p.goods_id WHERE g.bn LIKE '.$like_bn.' OR p.bn LIKE '.$like_bn);
                break;
            case'head':
                $goodsId = $this->db->select('SELECT g.goods_id FROM sdb_b2c_goods g LEFT JOIN sdb_b2c_products p ON g.goods_id = p.goods_id WHERE g.bn LIKE '.$right_like_bn.' OR p.bn LIKE '.$right_like_bn);
                break;
            case'foot':
                $goodsId = $this->db->select('SELECT g.goods_id FROM sdb_b2c_goods g LEFT JOIN sdb_b2c_products p ON g.goods_id = p.goods_id WHERE g.bn LIKE '.$left_like_bn.' OR p.bn LIKE '.$left_like_bn);
                break;
                }

        $rs = array();
        foreach( $goodsId as $key=>$val) {
            if(!in_array($val['goods_id'],$rs)){
                $rs[] = $val['goods_id'];
            }
        }
        return $rs;
     }

    function getPath($gid,$method=null){
        $gids['goods_id'] = $gid;
        $list_row = $this->getList("cat_id,name",array('goods_id'=>$gid));
        $row = $list_row[0];
        $goods = &$this->app->Model('goods_cat');
        $ret = $goods->getPath($row['cat_id'],$method);
        $ret[] = array('type'=>'goods','title'=>$row['name'],'link'=>'#');
        return $ret;
    }

    function fgetlist_csv(&$data,$filter,$offset){

		/** 适当放大内存到512M **/
		if ($offset == 0){
			@ini_set('memory_limit','512M');
		}
		/** end **/
        $subSdf = array(
            'keywords' => array('*'),
            'product'=>array(
                '*',array('price/member_lv_price'=>array('*'))
            ),
            'images'=>array('*',array(':image'=>array('*'))),
            ':brand'=>array('*')
            //':goods_type'=>array('*')
        );
        $limit = 40;
		$is_none_data = false;
        if( $filter['_gType'] ){
            $title = array();
            if(!$data['title'])$data['title'] = array();
            $data['title'][''.$filter['_gType']] = '"'.implode('","', $this->io_title(array('type_id'=>$filter['_gType'])) ).'"';
			$is_none_data = true;
        }
        $oGtype = &$this->app->model('goods_type');
        if($is_none_data || !$goodsList = $this->getList('goods_id',$filter,$offset*$limit,$limit))return false;
		/** 清空原有数据 ，防止数据过大，超过分配的内存 **/
		//$data['content'] = array();
		/** end **/
        foreach( $goodsList as $aFilter ){
            $aGoods = $this->dump( $aFilter['goods_id'],'*',$subSdf );
            if(empty($aGoods['thumbnail_pic']))  $aGoods['thumbnail_pic'] = $aGoods['image_default_id'];                        //为了防止少缩略图导后产生BUG

/*            if( $aGoods['udfimg'] == 'true' )
                $aGoods['thumbnail_pic'] = '';*/
            if( !$aGoods )continue;
            if( !$this->csvExportGtype[$aGoods['type']['type_id']] ){
                $this->csvExportGtype[$aGoods['type']['type_id']] = $oGtype->dump($aGoods['type']['type_id'],'*');
                //$data['title'][$aGoods['type']['type_id']];
                $data['title'][$aGoods['type']['type_id']] = '"'.implode('","',$this->io_title($aGoods['type'])).'"';
            }
            if( $aGoods['keywords'] ){
                $goodsKeywords = array();
                foreach( $aGoods['keywords'] as $kwk => $kwv ){
                    $goodsKeywords[] = $kwv['keyword'];
                }
                $aGoods['keywords'] = implode('|',$goodsKeywords);
            }
            $csvData = $this->sdf2csv($aGoods);
            $data['content'][$aGoods['type']['type_id']] = array_merge((array)$data['content'][$aGoods['type']['type_id']],(array)$csvData);
        }
        return true;

    }


    function export_csv($data){
        $output = array();
        if(is_array($data['title'])){
            foreach( $data['title'] as $k => $val ){
                $output[] = $val."\n".implode("\n",(array)$data['content'][$k]);
            }
        }
        return implode("\n",$output);
    }

    function getLinkList($goods_id){
        return $this->db->select('SELECT r.*, goods_id, bn, name FROM sdb_b2c_goods_rate r, sdb_b2c_goods
                WHERE ((goods_2 = goods_id AND goods_1='.intval($goods_id)
                .') OR (goods_1 = goods_id AND goods_2 = '.intval($goods_id)
                .' AND manual=\'both\')) AND rate > 99');
    }

    function sdf2csv( $sdfdata ){
        $rs = array();
//        $sdf = $this->_column();
//        $product = $sdfdata['product'];
        //        unset($sdfdata['product']);
        $conTmp = array();
        $sdfdata['description'] = str_replace( '"','""', $sdfdata['description'] );
        foreach( $this->io_title( $sdfdata['type'] ) as $titleCol ){
            $conTmp[$titleCol] = '';
        }
        $gcontent = $conTmp;

    //    $this->oSchema['csv'][$sdfdata['type']['type_id']][app::get('b2c')->_('col:市场价')] = 'mktprice';
    //    $this->oSchema['csv'][$sdfdata['type']['type_id']][app::get('b2c')->_('col:成本价')] = 'cost';
    //    $this->oSchema['csv'][$sdfdata['type']['type_id']][app::get('b2c')->_('col:销售价')] = 'price';
        $sdfdata['type']['name'] = $this->csvExportGtype[$sdfdata['type']['type_id']]['name'];
        foreach( $this->oSchema['csv'][$sdfdata['type']['type_id']] as $title => $sdfpath ){
            if( !is_array($sdfpath) ){
                $tSdfCol = utils::apath($sdfdata,explode('/',$sdfpath));
                $gcontent[$title] = (is_array($tSdfCol)?$tSdfCol:$tSdfCol);
            }else{
                $gcontent[$title] = '';
            }
            if( substr($title,0,6) == 'props:' ){
                if( !$gcontent && $gcontent[$title]['value'] !== 0 ){
                    $gcontent[$title] = '';
                }else{
                    $k = explode('_',$sdfpath);
                    $k = $k[1];
                    if( $this->csvExportGtype[$sdfdata['type']['type_id']]['props'][$k]['options'] ){
                        $gcontent[$title] = $this->csvExportGtype[$sdfdata['type']['type_id']]['props'][$k]['options'][$gcontent[$title]['value']];
                    }else{
                        $gcontent[$title] = $gcontent[$title]['value'];
                    }
                }
            }
        }
       $cat = array();
        $oCat = &$this->app->model('goods_cat');
        $tcat = $oCat->dump($sdfdata['category']['cat_id'],'cat_path');
        $catPath = array();
        foreach( explode(',',$tcat['cat_path']) as $catv ){
            if( $catv )$catPath[] = $catv;
        }
        if( $sdfdata['category']['cat_id'] )
            $catPath[] = $sdfdata['category']['cat_id'];
        if( $catPath ){
           foreach( $oCat->getList('cat_name',array('cat_id'=>$catPath)) as $acat ){
               if( $acat ) $cat[] = $acat['cat_name'];
            }
            $gcontent[app::get('b2c')->_('col:分类')] = implode('->',$cat);
        }else{
            $gcontent[app::get('b2c')->_('col:分类')] = '';
        }
        $gcontent[app::get('b2c')->_('col:上架')] = $gcontent[app::get('b2c')->_('col:上架')] == 'true'?'Y':'N';
        if( $sdfdata['images'] ){
            $oImage = &app::get('image')->model('image');
            foreach( $sdfdata['images'] as $aImage ){
                $imageData = $oImage->dump($aImage['image_id'],'url');
                $gcontent[app::get('b2c')->_('col:图片文件')][] = $aImage['image_id'].'@'.$imageData['url'];
            }
            $gcontent[app::get('b2c')->_('col:图片文件')] = implode('#',$gcontent[app::get('b2c')->_('col:图片文件')]);
        }
        if( $sdfdata['udfimg'] == 'true' && $sdfdata['thumbnail_pic'] && substr($sdfdata['thumbnail_pic'],0,4 ) != 'http' ){
            $oImage = &app::get('image')->model('image');
            $imageData = $oImage->dump($sdfdata['thumbnail_pic'],'url');
            $gcontent[app::get('b2c')->_('col:缩略图')] = $sdfdata['thumbnail_pic'].'@'.$imageData['url'];

        }
        $this->oSchema['csv'][$sdfdata['type']['type_id']][app::get('b2c')->_('col:市场价')] = array('price/mktprice/price','product');
        $this->oSchema['csv'][$sdfdata['type']['type_id']][app::get('b2c')->_('col:成本价')] = array('price/cost/price','product');
        $this->oSchema['csv'][$sdfdata['type']['type_id']][app::get('b2c')->_('col:销售价')] = array('price/price/price','product');

        if( !$sdfdata['spec'] ){
            $product = current( (array)$sdfdata['product'] );
            foreach( $this->oSchema['csv'][$sdfdata['type']['type_id']] as $title => $sdfpath ){
                if( is_array($sdfpath) && $sdfpath[1] == 'product' ){
                    $tSdfCol = utils::apath($product,explode('/',$sdfpath[0]));
                    $gcontent[$title] = (is_array($tSdfCol))?$tSdfCol:$tSdfCol;
                }
            }
            $gcontent[app::get('b2c')->_('col:规格')] = '-';
            $rs[0] = '"'.implode('","',$gcontent).'"';
        }else{
            $spec = array();
            foreach( $sdfdata['spec'] as $aSpec ){
                $spec[] = $aSpec['spec_name'];
            }
            $gcontent[app::get('b2c')->_('col:规格')] = implode('|',$spec);

            $oSpec = &$this->app->model('spec_values');

            $rs[0] = '"'.implode('","',$gcontent).'"';
            foreach( $sdfdata['product'] as $row => $aSdfdata ){
                $content = $conTmp;
                foreach( $this->oSchema['csv'][$sdfdata['type']['type_id']] as $title => $sdfpath ){
                    $content[$title] = utils::apath($aSdfdata,explode('/',(!is_array($sdfpath)?$sdfpath:$sdfpath[0])));
                }
                $specValue = array();
                foreach( $oSpec->getList('spec_value',array('spec_value_id'=>$aSdfdata['spec_desc']['spec_value_id']) ) as $aSpecValue ){
                    $specValue[] = $aSpecValue['spec_value'];
                }
                $content[app::get('b2c')->_('col:规格')] = implode('|',$specValue);
                $content[app::get('b2c')->_('bn:商品编号')] = $gcontent[app::get('b2c')->_('bn:商品编号')];
                $content[app::get('b2c')->_('col:上架')] = $content[app::get('b2c')->_('col:上架')] == 'true'?'Y':'N';
                $content['*:'.$sdfdata['type']['name']] = $sdfdata['type']['name'];
                $rs[$row] = '"'.implode('","',$content).'"';
            }
        }
        return $rs;
    }
    function searchOptions(){
        $arr = parent::searchOptions();
        $arr = array_merge($arr,array(
                'bn'=>app::get('b2c')->_('货号'),
                'keyword'=>app::get('b2c')->_('商品关键字'),
               // 'barcode'=>app::get('b2c')->_('条码'),
            ));
        foreach(kernel::servicelist('b2c_mdl_goods.extends_cols') as $k=>$obj_cols){
            if(method_exists($obj_cols,'get_extends_cols')){
                $obj_cols->get_extends_cols($arr);
            }
        }
        return $arr;
    }

    function pre_restore(&$data,$restore_type='add'){
        if( $restore_type == 'add' ){
            if( $this->checkProductBn( $data['bn'] ) ){
                $data['bn'] = '';
            }
            foreach( $data['product'] as $k => $p ){
                if( $this->checkProductBn( $p['bn'] ) ){
                    $data['product'][$k]['bn'] = '';
                }
            }

        }
        if( $restore_type == 'none' ){
            if( $this->checkProductBn( $data['bn'] ) ){
                return false;
            }
            foreach( $data['product'] as $k => $p ){
                if( $this->checkProductBn( $p['bn'] ) ){
                    return false;
                }
            }

        }
        $data['need_delete'] = true;
        return true;
    }

    function setEnabled($finderResult,$status){
        if($finderResult['goods_id'][0] == '_ALL_')  unset($finderResult);
        $data['marketable'] = $status;
        $objProducts = &$this->app->model('products');
        $goods_id = $this->getList('goods_id',$finderResult);
        foreach($goods_id as $pk => $pv){
           $result['goods_id'][] = $pv['goods_id'];
        }
        if($status == 'true')
            $objProducts->update($data,$result);
        $rs_flag = $this->update($data,$result);

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($rs_flag){
            if($obj_operatorlogs = kernel::service('operatorlog')){
                if(method_exists($obj_operatorlogs,'inlogs')){
                    $m_tmp = array('true'=>'上架','false'=>'下架');
                    if(!isset($finderResult)){
                        $memo = $m_tmp[$status].'所有商品';
                    }else{
                        if(count($finderResult['goods_id'])>100){
                            $memo = '批量'.$m_tmp[$status].'商品 ID('.implode(',',$finderResult['goods_id']).')';
                        }else{
                            $goods_bn = $this->getList('bn',$finderResult);
                            $v2tmp='';
                            foreach($goods_bn as $v2){
                                $v2tmp .=$v2['bn'].',';
                            }
                            $memo = '批量'.$m_tmp[$status].'商品编号('.rtrim($v2tmp,',').')';
                        }
                    }
                    $obj_operatorlogs->inlogs($memo, '', 'goods');
                }
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        return $rs_flag;
    }

    function setRecommend($finderResult, $stype, $status) {
        if ($finderResult['goods_id'][0] == '_ALL_')
            unset($finderResult);
        $data[$stype] = $status;
        $table_name = $this->table_name(true);
        $db = &kernel::database();
        $meta_info = $this->dump($finderResult['goods_id'], $stype); //若未注册这两个拓展字段，则注册

        if (!is_array($meta_info) && !$meta_info) {

            $this->goods_meta_register();
        }
        $goods_id = $this->getList('goods_id', $finderResult);

        foreach ($goods_id as $pk => $pv) {
            $result['goods_id'][] = $pv['goods_id'];
        }
        $rowResult = $db->selectrow("SELECT mr.mr_id as mmr_id, mv.mr_id FROM sdb_dbeav_meta_register mr LEFT JOIN sdb_dbeav_meta_value_varchar mv ON mr.mr_id=mv.mr_id WHERE mr.tbl_name='" . $table_name . "' AND mr.col_name='" . $stype . "'");

        if ($rowResult['mmr_id'] && !$rowResult['mr_id']) {

            $rs_flag = $db->exec("insert into sdb_dbeav_meta_value_varchar (mr_id, pk, value) values({$rowResult['mmr_id']}, {$result['goods_id'][0]},'{$data[$stype]}')");
        } else {

            $rs_flag = $this->update($data, $result);
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy copy ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if ($rs_flag) {
            if ($obj_operatorlogs = kernel::service('operatorlog')) {
                if (method_exists($obj_operatorlogs, 'inlogs')) {
                    $m_tmp = array('true' => '设置', 'false' => '取消');
                    $m_stype = array('is_tui' => '热门推荐', 'is_new' => '新品上架');
                    if (!isset($finderResult)) {
                        $memo = $m_tmp[$status] . $m_stype[$stype] . '所有商品';
                    } else {
                        if (count($finderResult['goods_id']) > 100) {
                            $memo = '批量' . $m_tmp[$status] . $m_stype[$stype] . '商品 ID(' . implode(',', $finderResult['goods_id']) . ')';
                        } else {
                            $goods_bn = $this->getList('bn', $finderResult);
                            $v2tmp = '';
                            foreach ($goods_bn as $v2) {
                                $v2tmp .=$v2['bn'] . ',';
                            }
                            $memo = '批量' . $m_tmp[$status] . $m_stype[$stype] . '商品编号(' . rtrim($v2tmp, ',') . ')';
                        }
                    }
                    $obj_operatorlogs->inlogs($memo, '', 'goods');
                }
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        return $rs_flag;
    }

    function getBatchEditInfo($filter){
        $r = $this->db->selectrow('select count( goods_id ) as count from sdb_b2c_goods where '.$this->_filter($filter));
        return $r;
    }

    function getGoodsIdByFilter($filter){
        $sql = 'SELECT goods_id FROM sdb_b2c_goods WHERE '.$this->_filter($filter);
        if($filter['goods_id'] == '_ALL_')
             $sql = 'SELECT goods_id FROM sdb_b2c_goods';
        $goodsList = $this->db->select($sql);
        $func=create_function('$r','return$r["goods_id"];');
        return array_map($func,$goodsList);
    }

    function countGoods($filter=null){
        $row = $this->db->select('SELECT count(*) as _count FROM sdb_b2c_goods WHERE '.$this->_filter($filter));
        return intval($row[0]['_count']);
    }

    function updateRank($gid, $item, $num=1){
        $weekMark = false;
        switch($item){
            case "discuss":
            $item = "comments_count";
            break;
//            $weekMark = 'view';
//            break;
            case "buy_count":
            $weekMark = 'buy';
            break;
            case "ask":
            $item = "rank_count";
            break;
        }
        
        if($weekMark){
            $aGstat = $this->dump(array($gid),'count_stat');
            $count_stat = unserialize($aGstat['count_stat']);
            $dayNum = $this->day(time());
            $weekNum = $num;
            if(isset($count_stat[$weekMark])){
                foreach($count_stat[$weekMark] as $day => $countNum){
                    if($dayNum > $day+30) unset($count_stat[$weekMark][$day]);
                    if($dayNum < $day+8) $weekNum += $countNum;
                }
            }
            $count_stat[$weekMark][$dayNum] += $num;
            
           
            $sqlCol = '';
            $monthMark = 'mbuy'; 
            $monthNum = $num;
            if(isset($count_stat[$monthMark])){
                foreach($count_stat[$monthMark] as $day => $countNum){
                    if($dayNum > $day+35) unset($count_stat[$monthMark][$day]);
                    if($dayNum < $day+30) $monthNum += $countNum;
                }
            }
            $count_stat[$monthMark][$dayNum] += $num;
            $sqlCol .= ','.$weekMark.'_m_count='.$weekMark.'_m_count+'.intval($monthNum);
            $objStore = app::get('business')->model('storemanger');
            $sql =" update sdb_business_storemanger as s inner join ".
              " (select sum(buy_m_count+0) as _count,store_id  from sdb_b2c_goods where store_id in (select store_id from sdb_b2c_goods where goods_id=".intval($gid).") group by store_id) as c on s.store_id=c.store_id ".
              " set s.buy_m_count=c._count ";
            $this->db->exec($sql);
            
            $sqlCol .= ','.$weekMark.'_w_count='.$weekMark.'_w_count+'.intval($weekNum).', count_stat=\''.serialize($count_stat).'\'';
        }
        return $this->db->exec("UPDATE sdb_b2c_goods SET ".$item." = ".$item."+".intval($num).$sqlCol." WHERE goods_id =".intval($gid));//last_modify不做更新
    }

    function day($time=null){
        if(!isset($GLOBALS['_day'][$time])){
            return $GLOBALS['_day'][$time] = floor($time/86400);
        }else{
            return $GLOBALS['_day'][$time];
        }
    }

    function pre_recycle($rows){
        $obj_check_order = kernel::single('b2c_order_checkorder');
        $objProduct = &$this->app->model('products');

        if(is_array($rows)){
            foreach($rows as $key => $val){
                $product_id = $objProduct->getList('product_id',array('goods_id'=>$val['goods_id']));
                foreach($product_id as $pkey =>$pval){
                   $orderStatus = $obj_check_order->check_order_product(array('goods_id'=>$val['goods_id'],'product_id'=>$pval['product_id']));
                    if(!$orderStatus){
                        $this->recycle_msg = '该商品有订单未处理';
                        return false;
                    }
                }
                foreach( kernel::servicelist("b2c_allow_delete_goods") as $object ) {
                    if( !method_exists($object,'is_delete') ) continue;
                    if( !$object->is_delete($val['goods_id']) ) {
                        $this->recycle_msg = $object->error_msg;
                        return false;
                    }
                }
            }

        }
        return true;
    }

    function countBrandGoods($filter=null,$brand=null){
    
        if(is_array($brand)&&count($brand)>0){
            foreach($brand as $bk => $bv){
                //$brand_id[] = 'brand_id = '.intval($bv['brand_id']);
                $brand_id[] =intval($bv['brand_id']);
            }
            //$sql='SELECT count(goods_id) as _count,brand_id FROM `'.$this->table_name(1).'` WHERE '.$this->_filter($filter).' AND  ('.implode(' OR ',$brand_id).') GROUP BY  brand_id order by brand_id asc';
             $sql='SELECT count(goods_id) as _count,brand_id FROM `'.$this->table_name(1).'` WHERE '.$this->_filter($filter).' AND  brand_id in('.implode(',',$brand_id).') GROUP BY  brand_id order by brand_id asc';
            $row = $this->db->select($sql);
            //print_r($sql);
            return $row;
        }else{
            return null;
        }
    }

    function sdf_to_plain($data,$appends=false){
        foreach($this->_columns() as $k=>$v){
            $map[$k] = $v['sdfpath']?$v['sdfpath']:$k;
        }
        if($appends){
            $map = array_merge($map,(array)$appends);
        }

        $return = array();
        foreach($map as $k=>$v){
            $ret = utils::apath($data,explode('/',$v));
            if( $ret !== false ){
                $return[$k] = $ret;
            }
        }

        if(is_array($data['product']) && $data['product']){
            foreach($data['product'] as $key=>$val){
                $price[] = $val['price']['price']['price'];
            }
            $minPrice = min($price);
            $return['price'] = $minPrice;
        }

        return $return;
    }

    function getCartAdjunct($params,$fromGoodsId ){
        $objGoods = &$this->app->model('goods');
        $objProducts = &$this->app->model('products');
        $adjunct = $this->dump(array('goods_id'=>$fromGoodsId),'adjunct');
        $setting = unserialize($adjunct['adjunct']);
        if(is_array($setting)){
            foreach($setting as $sk=>$sv){
                if(is_array($sv['items']['product_id'])){
                    $adj['product_id'] = $sv['items']['product_id'];
                    $padjunct = $adj['product_id'];
                    unset($adj);
                }else{
                    $rows = $sv;
                    if($rows['type'] == 'goods'){
                       $arr = $rows['items'];
                    }else{
                        parse_str($rows['items'].'&dis_goods[]='.$fromGoodsId, $arr);
                    }
                    if(isset($arr['type_id'])){
                        $gId = $objGoods->getList('goods_id',$arr,0,-1);
                        if(is_array($gId)){
                            foreach($gId as $gv){
                                $gfilter['goods_id'][] = $gv['goods_id'];
                            }
                            if(empty($gfilter))
                            $gfilter['goods_id'] = '-1';
                        }
                    }else{
                        $gfilter = $arr;
                    }
                    if($aAdj = $objProducts->getList('product_id',$gfilter,0,-1)){
                        if(is_array($aAdj)){
                            foreach($aAdj as $ad =>$av){
                                $adj['product_id'][] = $av['product_id'];
                            }
                        }
                        $padjunct = $adj['product_id'];

                    }
                    unset($aAdj);
                    unset($adj);
                 }

                 $adjGroup[$sk]['product_id'] = $padjunct;
                 $adjGroup[$sk]['setting'] = $sv;
                 unset($padjunct);
                 unset($sv);
            }
            return $adjGroup;
        }else{
            return array();
        }

    }

    function checkPriceWeight($data){
        if(is_array($data)){
            foreach($data as $key=>&$val){
                if(!empty($val['price']['price']['price']) && !is_numeric($val['price']['price']['price'])){
                     return false;
                }
                if(!empty($val['price']['cost']['price']) && !is_numeric($val['price']['cost']['price'])){
                     return false;
                }
                if(!empty($val['price']['mktprice']['price']) && !is_numeric($val['price']['mktprice']['price'])){
                     return false;
                }
                if(!empty($val['weight']) && !is_numeric($val['weight'])){
                     return false;
                }
            }
        }
        return true;
    }

    function checkStore($data){
        if(is_array($data)){
            foreach($data as $key=>&$val){
                if((!empty($val['store']) && !is_numeric($val['store'])) || $val['store'] < 0){
                     return false;
                }
            }
        }
        return true;
    }

    function getLinkListNums($goods_id){
        /*
        $res =  $this->db->selectrow('SELECT count(goods_id) AS num FROM sdb_b2c_goods_rate r, sdb_b2c_goods
                WHERE ((goods_2 = goods_id AND goods_1='.intval($goods_id)
                .') OR (goods_1 = goods_id AND goods_2 = '.intval($goods_id)
                .' AND manual=\'both\')) AND rate > 99');
        */

        /* 上面的写法在实际应用当中有很严重的效率问题, 改成如下 */

        $goods_id = intval($goods_id);
	$sql = "SELECT COUNT(goods_id) AS num FROM (
                      SELECT goods_id FROM sdb_b2c_goods_rate r, sdb_b2c_goods  WHERE rate > 99 AND manual='both' AND  (goods_1=goods_id and goods_2='{$goods_id}' and marketable='true')
              UNION
                    SELECT goods_id FROM sdb_b2c_goods_rate r, sdb_b2c_goods WHERE rate >99  AND (goods_2=goods_id and goods_1='{$goods_id}'  and marketable='true')
              ) tmp ";
        $res = $this->db->selectrow($sql);
        return intval($res['num']);
    }

	///**
	// * 商品改造信息从kvstore里面获取的方法
	// * @param int goods id
	// * @return mixed
	// */
	//public function getkv_goods_info($goods_id){
	//	if (base_kvstore::instance('_ec_optimize')->fetch('goods_info_'.$goods_id,$value)){
	//		return $value;
	//	}

	//	return array();
	//}

	///**
	// * 获取商品价格
	// * @param int goods id
	// * @return mixed
	// */
	//public function getkv_goods_price($goods_id){
	//	if (base_kvstore::instance('_ec_optimize')->fetch('goods_price_'.$goods_id,$value)){
	//		return $value;
	//	}

	//	return array();
	//}

	///**
	// * 获取商品价格库存
	// * @param int goods id
	// * @return mixed
	// */
	//public function getkv_goods_store($goods_id){
	//	if (base_kvstore::instance('_ec_optimize')->fetch('goods_store_'.$goods_id,$value)){
	//		return $value;
	//	}

	//	return array();
	//}

	///**
	// * 商品改造信息从kvstore里面存储的方法
	// * @param int goods id
	// * @param mixed
	// */
	//public function storekv_product_info($goods_id,$data=array()){
	//	$goods = array();

	//	$goods['goods_id'] = $goods_id;
	//	$goods['bn'] = $data['bn'];
	//	$goods['name'] = $data['name'];
	//	/** 获取商品类型相关的信息 **/
	//	if (isset($data['type']['type_id'])&&$data['type']['type_id']){
	//		$type_id = $data['type']['type_id'];
	//		$obj_goods_type = $this->app->model('goods_type');
	//		$obj_goods_type_props = $this->app->model('goods_type_props');
	//		$obj_goods_type_props_value = $this->app->model('goods_type_props_value');

	//		$arr_type = $obj_goods_type->getList("*",array('type_id'=>$type_id));
	//		$goods['type'] = array(
	//			'type_id'=>$type_id,
	//			'name'=>$arr_type[0]['name'],
	//			'floatstore'=>$arr_type[0]['floatstore'],
	//			'setting'=>$arr_type[0]['setting'],
	//			'params'=>$arr_type[0]['params'],
	//			'disabled'=>$arr_type[0]['disabled'],
	//		);
	//		$arr_type_props = $obj_goods_type_props->getList('*',array('type_id'=>$type_id),0,-1,'ordernum ASC');
	//		foreach ($arr_type_props as $k=>$props){
	//			$goods['type']['props'][$k+1] = array(
	//				'props_id'=>$props['props_id'],
	//				'type_id'=>$props['type_id'],
	//				'type'=>$props['type'],
	//				'show'=>$props['show'],
	//				'name'=>$props['name'],
	//				'alias'=>$props['alias'],
	//				'goods_p'=>$props['goods_p'],
	//				'ordernum'=>$props['ordernum'],
	//				'lastmodify'=>$props['lastmodify'],
	//			);
	//			$arr_type_props_values = $obj_goods_type_props_value->getList('props_value_id,name,alias',array('props_id'=>$props['props_id']),0,-1,'order_by ASC');
	//			foreach ($arr_type_props_values as $props_value){
	//				$goods['type']['props'][$k+1]['options'][$props_value['props_value_id']] = $props_value['name'];
	//				$goods['type']['props'][$k+1]['optionAlias'][$props_value['props_value_id']] = $props_value['alias'];
	//				$goods['type']['props'][$k+1]['optionIds'][$props_value['props_value_id']] = $props_value['props_value_id'];
	//			}
	//		}
	//	}
	//	$goods['category'] = $data['category'];

	//	/** 商品品牌 **/
	//	if (isset($data['brand']['brand_id'])&&$data['brand']['brand_id']){
	//		$obj_brand = $this->app->model('brand');
	//		$arr_brand = $obj_brand->getList('brand_id,brand_name',array('brand_id'=>$data['brand']['brand_id']));
	//		$goods['brand'] = array(
	//			'brand_id'=>$data['brand']['brand_id'],
	//			'brand_name'=>$arr_brand[0]['brand_name']
	//		);
	//	}

	//	$goods['status'] = $data['status'];
	//	//$goods['store'] = $data['store'];- 通过货品相加得到
	//	$goods['gain_score'] = $data['gain_score'];
	//	$goods['unit'] = $data['unit'];
	//	$goods['brief'] = $data['brief'];
	//	$goods['image_default_id'] = $data['image_default_id'];
	//	$goods['udfimg'] = $data['udfimg'];
	//	$goods['thumbnail_pic'] = $data['thumbnail_pic'];
	//	$goods['package_scale'] = $data['package_scale'];
	//	$goods['package_unit'] = $data['package_unit'];
	//	$goods['score_setting'] = 'number';
	//	$goods['nostore_sell'] = $data['nostore_sell'];
	//	$goods['disabled'] = $data['disabled'];
	//	$goods['props'] = $data['props'];
	//	$goods['adjunct'] = $data['adjunct'];
	//	$goods['seo_info'] = $data['seo_info'];
	//	$goods['weight'] = $data['weight'];

	//	/** 商品规格相关信息 **/
	//	$store = array();
	//	$price = array();
	//	if ($data['product']){
	//		$obj_goods = $this->app->model('goods');
	//		$arr_goods = $obj_goods->getList('spec_desc',array('goods_id'=>$goods_id));
	//		$obj_goods_lv_price = $this->app->model('goods_lv_price');
	//		$obj_member_lv = $this->app->model('member_lv');

	//		$price = array(
	//			'price'=>$arr_goods[0]['price'],
	//			'cost'=>$arr_goods[0]['cost'],
	//			'mktprice'=>$arr_goods[0]['mktprice'],
	//		);

	//		/** 货品信息 **/
	//		$obj_product = $this->app->model('products');
	//		$arr_product = $obj_product->getList('*',array('goods_id'=>$goods_id));
	//		$has_spec = false;
	//		foreach ($arr_product as $k=>$product){
	//			if ($k == 0 && $product['spec_desc']) $has_spec = true;
	//			$goods['product'][$product['product_id']] = array(
	//				'product_id'=>$product['product_id'],
	//				'goods_id'=>$product['goods_id'],
	//				'bn'=>$product['bn'],
	//				'spec_desc'=>$product['spec_desc'],
	//			);
	//			$store['store'] += intval($product['store'])-intval($product['freez']);
	//			$store['product'][$product['product_id']] = intval($product['store'])-intval($product['freez']);

	//			$price['product'][$product['product_id']]['price']['price']['price'] = $product['price'];
	//			$price['product'][$product['product_id']]['price']['cost']['price'] = $product['cost'];
	//			$price['product'][$product['product_id']]['price']['mktprice']['price'] = $product['mktprice'];

	//			/** 判断是否有设置的会员价格 **/
	//			$arr_goods_lv_prices = $obj_goods_lv_price->getList('*',array('goods_id'=>$goods_id,'product_id'=>$product['product_id']));
	//			if (!$arr_goods_lv_prices){
	//				$arr_member_lv = $obj_member_lv->getList('member_lv_id,name,dis_count');
	//				foreach ((array)$arr_member_lv as $lv){
	//					$price['product'][$product['product_id']]['price']['member_lv_price'][intval($lv['member_lv_id'])] = array(
	//						'level_id'=>$lv['member_lv_id'],
	//						'title'=>$lv['name'],
	//						'price'=>$lv['dis_count'],
	//						'custom'=>'false',
	//					);
	//				}
	//			}else{
	//				foreach ((array)$arr_goods_lv_prices as $arr_goods_lv_price){
	//					$arr_member_lv = $obj_member_lv->getList('member_lv_id,name,dis_count',array('member_lv_id'=>$arr_goods_lv_price['level_id']));
	//					$price['product'][$product['product_id']]['price']['member_lv_price'][$arr_goods_lv_price['level_id']] = array(
	//						'level_id'=>$arr_goods_lv_price['level_id'],
	//						'title'=>$arr_member_lv[0]['name'],
	//						'price'=>$arr_goods_lv_price['price'],
	//						'custom'=>'true',
	//					);
	//				}
	//			}
	//		}

	//		if ($has_spec){
	//			$obj_goods_spec_index = $this->app->model('goods_spec_index');
	//			$obj_goods_specification = $this->app->model('specification');
	//			$obj_spec_value = $this->app->model('spec_values');
	//			$arr_spec_indexs = $obj_goods_spec_index->getList('*',array('goods_id'=>$product['goods_id']));
	//			$spec_id = 0;
	//			foreach ((array)$arr_spec_indexs as $k=>$spec_index){
	//				if ($spec_id == $spec_index['spec_id']) continue;

	//				$spec_id = $spec_index['spec_id'];
	//				$arr = $obj_goods_specification->getList('*',array('spec_id'=>$spec_index['spec_id']));

	//				/** 找private_spec_value_id这个值 **/
	//				$index = array_search($spec_index['spec_value_id'], $goods['product'][$spec_index['product_id']]['spec_desc']['spec_value_id']);
	//				$arr_spec_value = $obj_spec_value->getList('*',array('spec_value_id'=>$spec_index['spec_value_id']));
	//				$goods['spec'][$k] = array(
	//					'spec_id'=>$spec_index['spec_id'],
	//					'spec_name'=>$arr[0]['spec_name'],
	//					'spec_show_type'=>$arr[0]['spec_show_type'],
	//					'spec_type'=>$arr[0]['spec_type'],
	//					'spec_memo'=>$arr[0]['spec_memo'],
	//					'p_order'=>$arr[0]['p_order'],
	//					'disabled'=>$arr[0]['disabled'],
	//					'alias'=>$arr[0]['alias'],
	//					'option'=>$arr_goods[0]['spec_desc'],
	//				);
	//			}
	//		}
	//	}

	//	$goods['images'] = $data['images'];

	//	/** 存储库存 **/
	//	base_kvstore::instance('_ec_optimize')->store('goods_store_'.$goods_id,$store);
	//	/** 存储商品 **/
	//	base_kvstore::instance('_ec_optimize')->store('goods_info_'.$goods_id,$goods);
	//	/** 存储商品价格 **/
	//	base_kvstore::instance('_ec_optimize')->store('goods_price_'.$goods_id,$price);

	//	return true;
	//}
    
    function getStoreInfo($store_id){
        $store_id = intval($store_id);
        $objStore = app::get('business')->model('storemanger');
        $store_info = $objStore->dump($store_id,'*','default');
        if($store_info['store_id']){
            $sql = " select t.*,p.avg_point,p.avg_percent from sdb_b2c_comment_goods_type as t ".
                "left join sdb_business_comment_stores_point as p on t.type_id=p.type_id and p.store_id=".intval($store_info['store_id']);
        }else{
            $sql = " select t.* from sdb_b2c_comment_goods_type as t ";
        }
        $store_info['store_point'] = $this->db->select($sql);
        return $store_info;
    }
    
    function getCity(){
        if($_SERVER['HTTP_CDN_SRC_IP']){
            $realIp = $_SERVER['HTTP_CDN_SRC_IP'];
        }elseif($_SERVER['HTTP_X_FORWARDED_FOR']){
            $client_ips = preg_split('/[,\s]+/', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $realIp = $client_ips[0];
        }elseif($_SERVER['HTTP_CLIENT_IP']){
            $realIp = $_SERVER['HTTP_CLIENT_IP'];
        }else{
            $realIp = $_SERVER['REMOTE_ADDR'];
        }
        $custom_ip = sprintf("%u", ip2long($realIp));
        $aIPs = $this->db->select("select city from sdb_business_ipdata where start<={$custom_ip} and end>={$custom_ip}");
        return $aIPs[0]['city'];
    }
    
    function getDlytype($aGoods,$area_id){
        $store_id = intval($aGoods['store_id']);
        /*$dt_id = $aGoods['dt_id'];
        if($dt_id) $dt_id = explode(',',$dt_id);
        if(count($dt_id) > 0) $dt_id = array_filter($dt_id);
        //$dt_id = array(1,2,5);
        if(!dt_id) return;*/
        $objIP = app::get('business')->model('ipdata');
        $objRegions = app::get('ectools')->model('regions');
        $objDlytype = app::get('business')->model('dlytype');
        if(!$area_id){
            if($_SERVER['HTTP_CDN_SRC_IP']){
                $realIp = $_SERVER['HTTP_CDN_SRC_IP'];
            }elseif($_SERVER['HTTP_X_FORWARDED_FOR']){
                $client_ips = preg_split('/[,\s]+/', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $realIp = $client_ips[0];
            }elseif($_SERVER['HTTP_CLIENT_IP']){
                $realIp = $_SERVER['HTTP_CLIENT_IP'];
            }else{
                $realIp = $_SERVER['REMOTE_ADDR'];
            }
            $custom_ip = sprintf("%u", ip2long($realIp));
            //$custom_ip = 974359552;
            //$aIPs = $objIP->getList('city', array('start|sthan'=>$custom_ip,'end|bthan'=>$custom_ip));
            $aIPs = $this->db->select("select city from sdb_business_ipdata where start<={$custom_ip} and end>={$custom_ip}");

            if(!$aIPs[0]['city']) return;
            $aRegion = $objRegions->getList('region_path,region_id',array('local_name|head'=>$aIPs[0]['city']));
            if(!$aRegion[0]['region_id']) return;
            $area_id = $aRegion[0]['region_id'];
        }
        $goods_id = intval($aGoods['goods_id']);
        $dly_id = array();
        if(!empty($goods_id)) foreach((array)app::get('b2c')->model('goods_dly')->getList('dly_id',array('goods_id'=>$goods_id,'manual'=>'normal'),0,-1) as $item){
            $dly_id[] = $item['dly_id'];
        }
        if(!empty($dly_id) && !empty($store_id))
        $dlytype = $objDlytype->getList('*',array('store_id'=>$store_id,'dt_id|in'=>$dly_id,'dt_status'=>'1'),0,-1,'ordernum ASC');
        else
        $dlytype = $objDlytype->getList('*',array('store_id'=>$store_id,'dt_status'=>'1'),0,-1,'ordernum ASC');
        if ($dlytype && is_array($dlytype))
        {
            $setting_0 = $setting_1 = array();
            foreach ($dlytype as $key=>$value)
            {
                if ($value['setting']==1)
                {
                    //统一费用
                    $setting_1[$value['ordernum'].'.'.$value['dt_id']] = $value;
                }
                else
                {
                    if ($value['def_area_fee'] == 'true')
                    {
                        $setting_0[$value['ordernum'].'.'.$value['dt_id']] = $value;
                    }
                    
                    $area_fee_conf = unserialize($value['area_fee_conf']);
                    if ($area_fee_conf && is_array($area_fee_conf))
                    {
                        foreach ($area_fee_conf as $k=>$v)
                        {
                            $areas = explode(',',$v['areaGroupId']);
                            
                            // 再次解析字符
                            foreach ($areas as &$strArea)
                            {
                                if (strpos($strArea, '|') !== false)
                                {
                                    $strArea = substr($strArea, 0, strpos($strArea, '|'));
                                     // 取当前area id对应的最上级的区域id
                                    $objRegions = app::get('ectools')->model('regions');
                                    $arrRegion = $objRegions->dump($area_id);
                                    while ($row = $objRegions->getRegionByParentId($arrRegion['p_region_id']))
                                    {
                                        $arrRegion = $row;
                                        $tmp_area_id = $row['region_id'];
                                        if ($tmp_area_id == $strArea)
                                        {
                                            $area_id = $tmp_area_id;
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            if(in_array($area_id,$areas)){//如果地区在其中，优先使用地区设置的配送费用，及公式
                                $value['firstprice'] = $v['firstprice'];
                                $value['continueprice'] = $v['continueprice'];
                                //if($v['dt_useexp']==1){
                                $value['dt_expressions'] = $v['dt_expressions'];
                                //}
                                $setting_0[$value['ordernum'].'.'.$value['dt_id']] = $value;
                                break;
                            }
                        }
                    }
                }
            }
            
            $all_dly_types = array_merge($setting_1,$setting_0);
            ksort($all_dly_types);
            
            foreach ($all_dly_types as $rows)
            {
                if ($rows['is_threshold'])
                {
                    if ($rows['threshold'])
                    {
                        $rows['threshold'] = unserialize(stripslashes($rows['threshold']));
                        if (isset($rows['threshold']) && $rows['threshold'])
                        {
                            foreach ($rows['threshold'] as $res)
                            {
                                if ($res['area'][1] > 0)
                                {
                                    if ($cost_item >= $res['area'][0] && $cost_item < $res['area'][1])
                                    {
                                        $rows['firstprice'] = $res['first_price'];
                                        $rows['continueprice'] = $res['continue_price'];
                                    }
                                }
                                else
                                {
                                    if ($cost_item >= $res['area'][0])
                                    {
                                        $rows['firstprice'] = $res['first_price'];
                                        $rows['continueprice'] = $res['continue_price'];
                                    }
                                }
                            }
                        }
                    }
                }
                $rows['money'] = @utils::cal_fee($rows['dt_expressions'], intval($aGoods['weight']), 1, $rows['firstprice'], $rows['continueprice'], $rows['firstprice']);
                
                $shipping['dlytype'][] = $rows;
            }
            $shipping['area'] = $area_id;
            $objRegions = app::get('ectools')->model('regions');
            $arrRegion = $objRegions->dump($area_id);
            $shipping['localname'] = $arrRegion['local_name'];
            $pos = strpos($arrRegion['region_path'], ',', 1)-1;
            $shipping['parent'] = ($pos)?substr($arrRegion['region_path'],1,$pos):substr($arrRegion['region_path'],1);
            return $shipping;
        }else{
            return;
        }
    }
    
    function setToFree($finderResult,$status){
        if($finderResult['goods_id'][0] == '_ALL_')  unset($finderResult);
        $data['freight_bear'] = $status;
        $goods_id = $this->getList('goods_id',$finderResult);
        foreach($goods_id as $pk => $pv){
           $result['goods_id'][] = $pv['goods_id'];
        }
        $rs_flag = $this->update($data,$result);

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($rs_flag){
            if($obj_operatorlogs = kernel::service('operatorlog')){
                if(method_exists($obj_operatorlogs,'inlogs')){
                    $m_tmp = array('business'=>'商家承担','member'=>'买家承担');
                    if(!isset($finderResult)){
                        $memo = $m_tmp[$status].'所有商品';
                    }else{
                        if(count($finderResult['goods_id'])>100){
                            $memo = '批量'.$m_tmp[$status].'商品 ID('.implode(',',$finderResult['goods_id']).')运费';
                        }else{
                            $goods_bn = $this->getList('bn',$finderResult);
                            $v2tmp='';
                            foreach($goods_bn as $v2){
                                $v2tmp .=$v2['bn'].',';
                            }
                            $memo = '批量'.$m_tmp[$status].'商品编号('.rtrim($v2tmp,',').')运费';
                        }
                    }
                    $obj_operatorlogs->inlogs($memo, '', 'goods');
                }
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        return $rs_flag;
    }
    
    // 列表页搜索专用
   private function _getSearchGoodsCount($filter=array(),$str_where=''){
        $sql=array();
        $sql[]="SELECT";
        $sql[]="COUNT(`sdb_b2c_goods`.goods_id) as gcount";
        //商品30天销售量	销售量越高，排名越前	10%
        $sql[]=",MAX(`sdb_b2c_goods`.buy_m_count)as maxBuyMonthCount";
        $sql[]=",MIN(`sdb_b2c_goods`.buy_m_count)as minBuyMonthCount";
        $sql[]=",MAX(`sdb_b2c_goods`.buy_m_count)/100 as subBuyMonthCount";
        //商品浏览量	浏览量越高，排名越前	5%
        $sql[]=",MAX(`sdb_b2c_goods`.view_count)as maxViewCount";
        $sql[]=",MIN(`sdb_b2c_goods`.view_count)as minViewCount";
        $sql[]=",MAX(`sdb_b2c_goods`.view_count)/100 as subViewCount";
        //商品购买转化率	转化率越高，排名越前	10%
        //商品购买转化率=总购买量/总浏览量
        $sql[]=",MAX(if(`sdb_b2c_goods`.view_count=0,0,`sdb_b2c_goods`.buy_count/`sdb_b2c_goods`.view_count))as maxBuyPercent";
        $sql[]=",MIN(if(`sdb_b2c_goods`.view_count=0,0,`sdb_b2c_goods`.buy_count/`sdb_b2c_goods`.view_count))as minBuyPercent";
        $sql[]=",MAX(if(`sdb_b2c_goods`.view_count=0,0,`sdb_b2c_goods`.buy_count/`sdb_b2c_goods`.view_count))/100 as subBuyPercent";
        //商品收藏量	收藏越多，排名越前	5%
        $sql[]=",MAX(`sdb_b2c_goods`.fav_count)as maxFavCount";
        $sql[]=",MIN(`sdb_b2c_goods`.fav_count)as minFavCount";
        $sql[]=",MAX(`sdb_b2c_goods`.fav_count)/100 as subFavCount";
        //商品价格	价格越低，排名越前	2%
        $sql[]=",MAX(`sdb_b2c_goods`.price) as maxPrice";
        $sql[]=",MIN(`sdb_b2c_goods`.price) as minPrice";
        $sql[]=",MAX(`sdb_b2c_goods`.price)/100 as subPrice";

        //橱窗推荐	有推荐，排名靠前	5%
        //有橱窗推荐就是5分。没有0分。
        
        //商品更新时间	更新时间越近，排名越前	2%
        $sql[]=",MAX(`sdb_b2c_goods`.last_modify)as maxLastModify";
        $sql[]=",MIN(`sdb_b2c_goods`.last_modify)as minLastModify";
        $sql[]=",MAX(`sdb_b2c_goods`.last_modify)/100 as subLastModify";
        //店铺等级	等级越高，排名越前	5%        
        $sql[]=",MAX(s.experience)as maxStoreLevel";
        $sql[]=",MIN(s.experience)as minStoreLevel";
        $sql[]=",MAX(IFNULL(s.experience,0))/100 as subStoreLevel";
        //店铺6个月“宝贝鱼描述相符”评分	评分越高，排名越前	4%
        $sql[]=",MAX(p.storePoint)as maxStorePoint";
        $sql[]=",MIN(p.storePoint)as minStorePoint";
        $sql[]=",MAX(IFNULL(p.storePoint,0))/100 as subStorePoint";
        //店铺6个月“卖家服务态度”评分	评分越高，排名越前	3%
        $sql[]=",MAX(p.servicePoint)as maxServicePoint";
        $sql[]=",MIN(p.servicePoint)as minServicePoint";
        $sql[]=",MAX(IFNULL(p.servicePoint,0))/100 as subServicePoint";
        //店铺6个月“卖家发货速度”评分	评分越高，排名越前	3%
        $sql[]=",MAX(p.deliveryPoint)as maxDeliveryPoint";
        $sql[]=",MIN(p.deliveryPoint)as minDeliveryPoint";
        $sql[]=",MAX(IFNULL(p.deliveryPoint,0))/100 as subDeliveryPoint";
        //店铺的投诉率	投诉率越低，排名越前	4%
        $sql[]=",0 as maxRateOfComplaints";
        $sql[]=",0 as minRateOfComplaints";
        $sql[]=",0 as subRateOfComplaints";
        //店铺的退款率	退款率越低，排名越前	4%
        $sql[]=",0 as maxRefundsPercent";
        $sql[]=",0 as minRefundsPercent";
        $sql[]=",0 as subRefundsPercent";
        //店铺的退款速度	退款速度越快，排名越前	3%
        $sql[]=",0 as maxRefundsSpeed";
        $sql[]=",0 as minRefundsSpeed";
        $sql[]=",0 as subRefundsSpeed";
        //店铺的处罚数	处罚数越低，排名越前	3%
        $sql[]=",0 as maxPenaltyCount";
        $sql[]=",0 as minPenaltyCount";
        $sql[]=",0 as subPenaltyCount";

        //客服在线	客服在线，排名优先	2%
        
        $sql[]=" from sdb_b2c_goods";
        $sql[]=" left join sdb_business_storemanger as s on `sdb_b2c_goods`.store_id=s.store_id";
        $sql[]=" left join (";
        //店铺评分
        $sql[]="select store_id";
        $sql[]=",sum(if(type_id=1,avg_point,0)) as storePoint";
        $sql[]=", sum(if(type_id=2,avg_point,0)) as servicePoint";
        $sql[]=",sum(if(type_id=3,avg_point,0)) as deliveryPoint";
        $sql[]="from sdb_business_comment_stores_point";
        $sql[]="group by store_id";
        $sql[]=") as p  on p.store_id=`sdb_b2c_goods`.store_id";
        $sql[]=" where ".$this->_extend_filter($filter);
        $sql[]=" and s.limit_news_value>0";
        $sql[]=" and `sdb_b2c_goods`.goods_order_down>0";
        $sql[]=" and s.limit_storedown='0'";
        $sql[]=" and s.limit_store='0'";
        $ssql=implode(' ',$sql);
        //print_r($ssql);
        $rows=$this->db->select($ssql);
        return $rows[0];
    }
    function _extend_filter($filter){
        $str_where=array();
        if(isset($filter['store_name']) && !empty($filter['store_name'])){
            $store_name=$filter['store_name'];
            $arr_name=explode(' ',$store_name);
            $str_where[]=" (s.store_name like'%".implode("%' OR  s.store_name like '%",$arr_name)."%')";
            unset($filter['name']);
            unset($filter['store_name']);
        }
        if (isset($filter['loc']) && !empty($filter['loc'])) {
            if (is_string($filter['loc'])) {
                $str_where[]= " ((s.area  LIKE '%" . implode("%') or (s.area LIKE '%" , explode(',', $filter['loc'])) . "%'))";
            } else {
                $str_where[] = " ((s.area  LIKE '%" . implode("%') or (s.area LIKE '%" , $filter['loc']) . "%'))";
            } 
            unset($filter['loc']);
        }
        $where=$this->_filter($filter);
        if(!empty($str_where)){
            $where.=' and '.implode(' and ',$str_where);
        }
        return $where;
    }
    function getSearchGoods($cols='*',$filter=array(),$offset=0,$limit=-1,$orderType=null,$_cat=array(),&$count=0){
        $total=$this->_getSearchGoodsCount($filter);
        if(empty($total)){
           $count=0;
           return array();
        }
        $count=$total['gcount'];
        $sql=array();
        $sql[]="SELECT";
        if($cols=='*'){
           $sql[]="`sdb_b2c_goods`.*";
        }else{
           $sql[]=$cols;
        }
        $sql[]=" ,s.store_name";
        $sql[]=" ,((0";
        //商品30天销售量	销售量越高，排名越前	10%
        if($total['maxBuyMonthCount']!=$total['minBuyMonthCount']&&!empty($total['subBuyMonthCount'])){
            $sql[]=" +(`sdb_b2c_goods`.buy_m_count/".floatval($total['subBuyMonthCount']).")*0.1";
        }
        //商品浏览量	浏览量越高，排名越前	5%
        if($total['maxViewCount']!=$total['minViewCount']&&!empty($total['subViewCount'])){
            $sql[]=" +(`sdb_b2c_goods`.view_count/".floatval($total['subViewCount']).")*0.05";
        }
        //商品购买转化率	转化率越高，排名越前	10%
        //商品购买转化率=总购买量/总浏览量
        if($total['maxBuyPercent']!=$total['minBuyPercent']&&!empty($total['subBuyPercent'])){
            $sql[]=" +((if(`sdb_b2c_goods`.view_count=0,0,`sdb_b2c_goods`.buy_count/`sdb_b2c_goods`.view_count))/".floatval($total['subBuyPercent']).")*0.1";
        }
        //商品收藏量	收藏越多，排名越前	5%
        if($total['maxFavCount']!=$total['minFavCount']&&!empty($total['subFavCount'])){
            $sql[]=" +(`sdb_b2c_goods`.fav_count/".floatval($total['subFavCount']).")*0.05";
        }
        //商品价格	价格越低，排名越前	2%
        if($total['maxPrice']!=$total['minPrice']&&!empty($total['subPrice'])){
            $sql[]=" + (100 - IFNULL(pp.p_price,`sdb_b2c_goods`.price)/".floatval($total['subPrice']).")*0.02";
        }
        //橱窗推荐	有推荐，排名靠前	5%
        //有橱窗推荐就是5分。没有0分。
        $sql[]=" + 5";
        //商品更新时间	更新时间越近，排名越前	2%
        if($total['maxLastModify']!=$total['minLastModify']&&!empty($total['subLastModify'])){        
            $sql[]=" + (`sdb_b2c_goods`.last_modify/".floatval($total['subLastModify']).")*0.02";
        }
        //店铺等级	等级越高，排名越前	5%
        $sql[]=" + 5 ";
        //商品更新时间	更新时间越近，排名越前	2%
        if($total['maxStoreLevel']!=$total['minStoreLevel']&&!empty($total['subStoreLevel'])){        
            $sql[]=" + (s.experience/".floatval($total['subStoreLevel']).")*0.05";
        }
        //店铺6个月“宝贝鱼描述相符”评分	评分越高，排名越前	4%       
        $sql[]=" + (IFNULL(p.storePoint,0)/0.05)*0.04";
        //店铺6个月“卖家服务态度”评分	评分越高，排名越前	3%
        $sql[]=" + (IFNULL(p.servicePoint,0)/0.05)*0.03";
        //店铺6个月“卖家发货速度”评分	评分越高，排名越前	3%
        $sql[]=" + (IFNULL(p.deliveryPoint,0)/0.05)*0.03";
        //店铺的投诉率	投诉率越低，排名越前	4%
        $sql[]=" + 4";
        //店铺的退款率	退款率越低，排名越前	4%
        $sql[]=" + 4";
        //店铺的退款速度	退款速度越快，排名越前	3%
        $sql[]=" + 3";
        //店铺的处罚数	处罚数越低，排名越前	3%
        $sql[]=" + 3";
        //客服在线	客服在线，排名优先	2%
        $sql[]=" + 2";
        // 标题关键词相关性	宝贝标题/属性/标题里含有搜索词的关联度高排名前	15%
        $sql[]=" + 15";
        //类目相关性	与搜索关键词比较相关的类目对应的排名越前	15%
        if(!empty($_cat)){
          $sql[]=" + IF(`sdb_b2c_goods`.cat_id in(".implode(',',$_cat)."),15,0)";
        }
        //$sql[]=")*s.limit_news_value/100)*(`sdb_b2c_goods`.goods_order_down/100) as dorder";
        //$sql[]=",(`sdb_b2c_goods`.buy_m_count*s.limit_news_value/100)*(`sdb_b2c_goods`.goods_order_down/100) as dmcount";
        //$sql[]=",(IFNULL(pp.p_price,`sdb_b2c_goods`.price)*s.limit_news_value/100)*(`sdb_b2c_goods`.goods_order_down/100) as dprice";
        $sql[]=")) as dorder";
        //$sql[]=",(`sdb_b2c_goods`.buy_m_count*s.limit_news_value/100)*(`sdb_b2c_goods`.goods_order_down/100) as dmcount";
        //$sql[]=",(IFNULL(pp.p_price,`sdb_b2c_goods`.price)*s.limit_news_value/100)*(`sdb_b2c_goods`.goods_order_down/100) as dprice";
        $sql[]=",(s.limit_news_value * `sdb_b2c_goods`.goods_order_down) as orderWeight";


        $sql[]=" from `sdb_b2c_goods`";
        $sql[]=" left join sdb_business_storemanger as s on `sdb_b2c_goods`.store_id=s.store_id";
        $sql[]=" left join sdb_business_goods_promotion_price as pp on `sdb_b2c_goods`.goods_id=pp.goods_id";
        $sql[]=" left join (";
        //店铺评分
        $sql[]="select store_id";
        $sql[]=",sum(if(type_id=1,avg_point,0)) as storePoint";
        $sql[]=", sum(if(type_id=2,avg_point,0)) as servicePoint";
        $sql[]=",sum(if(type_id=3,avg_point,0)) as deliveryPoint";
        $sql[]="from sdb_business_comment_stores_point ";
        $sql[]="group by store_id";
        $sql[]=") as p  on p.store_id=`sdb_b2c_goods`.store_id";
        $sql[]=" where ".$this->_extend_filter($filter);
        $sql[]=" and s.limit_news_value>0";
        $sql[]=" and `sdb_b2c_goods`.goods_order_down>0";
        $sql[]=" and s.limit_storedown='0'";
        $sql[]=" and s.limit_store='0'";
        if(!empty($orderType)){
         /*if(strpos($orderType,'buy_m_count')!==false){
            $orderType=str_replace('buy_m_count','dmcount',$orderType);
         }
         if(strpos($orderType,'price')!==false){
            $orderType=str_replace('price','dprice',$orderType);
         }*/
         $sql[]=" ORDER BY orderWeight DESC, ".$orderType;
        }
        $ssql=implode(' ',$sql);
        //echo '<pre>';print_r($sql);echo $ssql;echo '</pre>';
        $rows=$this->db->selectLimit($ssql,$limit,$offset);
        
        return $rows;
    }
    function getList_1($cols='*',$filter=array(),$offset=0,$limit=-1){
        $sql=array();
        $sql[]="SELECT";
        if($cols=='*'){
           $sql[]="`sdb_b2c_goods`.*";
        }else{
           $sql[]=$cols;
        }
        $sql[]=" ,s.store_name";
        $sql[]=" from `sdb_b2c_goods`";
        $sql[]=" left join sdb_business_storemanger as s on `sdb_b2c_goods`.store_id=s.store_id";
        $sql[]=" left join sdb_business_goods_promotion_price as pp on `sdb_b2c_goods`.goods_id=pp.goods_id";
        $sql[]=" where ".$this->_filter($filter);
        $ssql=implode(' ',$sql);
        $rows=$this->db->selectLimit($ssql,$limit,$offset);
        return $rows;
    }
}
