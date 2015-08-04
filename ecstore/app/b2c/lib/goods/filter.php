<?php



class b2c_goods_filter extends dbeav_filter{
    var $name = 'B2C商品筛选器';
    function goods_filter(&$filter, &$object){

        $filter = utils::addslashes_array($filter);
        $ObjProducts = $object->app->model('products');
        $where = array();
        if( isset( $filter['marketable'] ) ){
            if( $filter['marketable'] === 'true' )
                $filter['marketable'] = 'true';
            if( $filter['marketable'] === 'false' )
                $filter['marketable'] = 'false';
        }
       
        if($filter['store_id']=='_ANY_'||$filter['store_id'][0]=='_ANY_'){
            unset($filter['store_id']);
        }

        if($filter['type_id'] == '_ANY_')
            unset($filter['type_id']);
        // 此导致商品高级筛选（销售价介于）

        if(is_numeric($filter['price']) && isset($filter['_price_search'])){
            $filter['price|'.$filter['_price_search']] = $filter['price'];
            if($filter['price_from'] && $filter['price_to']){
                $filter['price|'.$filter['_price_search']]=array($filter['price_from'],$filter['price_to']);
            }
            unset($filter['price']);
            unset($filter['_price_search']);
        }
    

        if($filter['cat_id'][0] == "_ANY_" || $filter['cat_id'] == "_ANY_")
            unset($filter['cat_id']);


        if($filter['cat_id'] || $filter['cat_id'] === 0){
            if(!is_array($filter['cat_id'])){
                $filter['cat_id']=array($filter['cat_id']);
            }else{
            	foreach($filter['cat_id'] as $vCat_id){
	                if($vCat_id !== '_ANY_' && $vCat_id !== ''){
	                    $aCat_id[] = intval($vCat_id);
	                }
                }
                $filter['cat_id']=$aCat_id;
            }
            
            if(!isset($object->__show_goods)){
                $object->__show_goods = $object->app->getConf('system.category.showgoods');
            }
            if($object->__show_goods){
                if(count($filter['cat_id'])>0)
                    $where[] = 'cat_id in ('.implode($filter['cat_id'],' , ').')';
            }else{
                if($filter['cat_id']){
                    $oCat = $object->app->model('goods_cat');
                    $fcat_id = $filter['cat_id'];
                    $aCat = $oCat->getList('cat_path,cat_id',array('cat_id'=>$fcat_id));
                }

                $pathplus='';
                if(count($aCat)){
                    foreach($aCat as $v){
                        $pathplus.=' cat_path LIKE \''
                                .($v['cat_path']).$v['cat_id'].',%\' OR';
                    }
                }
                if($aCat){
                    foreach($object->db->select('SELECT cat_id FROM sdb_b2c_goods_cat WHERE '.$pathplus.' cat_id in ('.implode($filter['cat_id'],' , ').')') as $rows){
                        $aCatid[] = $rows['cat_id'];
                    }
                }else{
                    unset($aCatid);
                }
/*                if(in_array('0', $filter['cat_id'])){
                    $aCatid[] = 0;
                }*/
                if(!is_null($aCatid)){
                    $where[] = 'cat_id IN ('.implode(',', $aCatid).')';
                }else if($filter['cat_id'] && $filter['cat_id'][0]){                    
                    $where[] = 'cat_id IN ('.implode(',', $filter['cat_id']).')';
                }

            }
            $filter['cat_id'] = null;
        } 
        
        if(isset($filter['area']) && $filter['area']){
            $where[] = 'goods_id < '.$filter['area'][0]. ' and goods_id >'.$filter['area'][1];
            //$where[] = 'and goods_id < 1000';
            unset($filter['area']);
        }
        if($filter['type_id']=="_ANY_" || empty($filter['type_id'][0])){
            unset($filter['type_id']);
        }
        if(is_array($filter['tag'])){
            foreach($filter['tag'] as $tk=>$tv){
                if($tv == '_ANY_')
                    unset($filter['tag'][$tk]);
            }
        }

        if(isset($filter['brand_id']) && $filter['brand_id']){
            if(is_array($filter['brand_id'])){
                foreach($filter['brand_id'] as $brand_id){
                    if($brand_id!='_ANY_'){
                        $aBrand[] = intval($brand_id);
                    }
                }
                if(count($aBrand)>0){
                    $where[] = 'brand_id IN('.implode(',', $aBrand).')';
                }
            }elseif($filter['brand_id'] > 0){
                $where[] = 'brand_id = '.$filter['brand_id'];
            }
            unset($filter['brand_id']);
        }
        if(isset($filter['goods_id']) && $filter['goods_id']){
            if(is_array($filter['goods_id'])){
                if( $filter['goods_id'][0] != '_ALL_' ){
                    foreach($filter['goods_id'] as $goods_id){
                        if($goods_id!='_ANY_'){
                            $goods[] = intval($goods_id);
                        }
                    }
                }
            }else{
                $goods[] = intval($filter['goods_id']);
            }
        }
        unset($filter['goods_id']);

		/** 下面查询通过商品主键组合查询条件 - left join 相应的表 **/
        if(isset($filter['keyword']) && $filter['keyword']) {
            $filter['keywords'] = array($filter['keyword']);
        }
        unset($filter['keyword']);

        if(isset($filter['keywords']) && $filter['keywords'] && !in_array('_ANY_',$filter['keywords'])) {
            $keywordsList = $object->getGoodsIdByKeyword($filter['keywords'],$filter['_keyword_search']);
            $keywordsGoods = array();
            foreach($keywordsList as $keyword)
                $keywordsGoods[] = intval($keyword['goods_id']);
            if(!empty($keywordsGoods) && !empty($goods)){
                $keywordsGoods = array_intersect($keywordsGoods, $goods);
                if(empty($keywordsGoods))
                    $goods = array('-1');
                else
                    $goods = $keywordsGoods;
            }else{
                if(!empty($keywordsGoods)){
                    $goods = $keywordsGoods;
                }else{
                    $goods = array('-1');
                }
            }
        }
        unset($filter['keywords']);

        if(isset($filter['bn']) && $filter['bn']){
            $sBn = '';
            if(is_array($filter['bn'])){
                $sBn = trim($filter['bn'][0]);
            }else{
                $sBn = trim($filter['bn']);
            }
            $bnGoodsId = $object->getGoodsIdByBn($sBn,$filter['_bn_search']);

            if(!empty($bnGoodsId) && !empty($goods)){
                $bnGoodsId = array_intersect($bnGoodsId, $goods);
                if(empty($bnGoodsId))
                    $goods = array('-1');
                else
                    $goods = $bnGoodsId;
            }else{
                if(!empty($bnGoodsId)){
                    $goods = $bnGoodsId;
                }else{
                    $goods = array('-1');
                }
            }
            unset( $filter['bn'] );
        }
        if(isset($filter['barcode']) && $filter['barcode']){
			$goods_id = $ObjProducts->getList('goods_id',array('barcode'=>$filter['barcode']));
            //$goods_id = $ObjProducts->dump(array('barcode'=>$filter['barcode']),'goods_id');
            if(isset($goods_id[0]['goods_id'])){
                $filter['goods_id'] = $goods_id[0]['goods_id'];
            }else{
                $filter['goods_id'] = 0;
            }
            unset( $filter['barcode'] );
        }
        $filter = (array)$filter;
        foreach($filter as $k=>$v){
            if(substr($k,0,2)=='p_'){
            	if(strpos($k,'|')!==false){
            		unset($filter[$k]);
            	    list($k,$type) = explode('|',$k);
                    $_str = $this->_inner_getFilterType($type,$v,false);
                    if( strpos($_str,'{field}')!==false )
                        $where[] = str_replace('{field}',$tPre.$k,$_str);
                    else
                        $where[] = $tPre.$k.$_str;
                    $_str = null;
            	}else{
	                $ac = array();
	                if(is_array($v)){
	                    foreach($v as $m){
	                        if($m!=='_ANY_' && $m!==''){
	                            $ac[] = $tPre.$k.'=\''.$m.'\'';
	                        }
	                    }
	                    if(count($ac)>0){
	                        $where[] = '('.implode($ac,' or ').')';
	                    }
	                }elseif(isset($v) && $v!='' && $v!='_ANY_'){
	                    $where[] = $tPre.$k.'=\''.$v.'\'';
	                }
            	}
                unset($filter[$k]);
            }
            else if( substr($k,0,2) == 's_' ){
                $sSpecId = array();
                if( is_array( $v ) ){
                    foreach( $v as $n ){
                        if( $n !== '_ANY_' && $n != false ){
                            $sSpecId[] = $n;
                        }
                    }
                    unset($filter[$k]);
                }

                if( count( $sSpecId )>0 ){
                    $sql = 'SELECT goods_id FROM sdb_b2c_goods_spec_index WHERE spec_value_id IN ( '.implode( ',',$sSpecId ).' )';
                    $sGoodsId = $object->db->select($sql);
                    $sgid = array();
                    foreach( $sGoodsId as $si )
                        $sgid[] = $si['goods_id'];
                    if(!empty($goods))
                        $sgid = array_intersect( $sgid , $goods);
                    if(!empty($sgid)){
                        $goods = $sgid;
                    }else{
                        $goods = array(-1);
                    }
                }

            }elseif($k=='filter_sql'){
                $where[] = str_replace('{table}',$tPre,stripslashes($v));
                unset($filter[$k]);
            }

        }

        if(isset($goods) && count($goods)>0){
            $where[] = ' `sdb_b2c_goods`.goods_id IN ('.implode(',', $goods).')';
        }


        if(isset($filter['price']) && is_array($filter['price'])){
            if($filter['price'][0]==0 || $filter['price'][0]){
                //价格区间在0到0.1之间的商品无法搜索。huoxh 2013-07-08
                //$where[] = 'price >= '.intval($filter['price'][0]);
                $where[] = 'price >= '.floatval($filter['price'][0]);
            }

            if($filter['price'][1]=='0' || $filter['price'][1]){
                //价格区间在0到0.1之间的商品无法搜索。huoxh 2013-07-08
                //$where[] = 'price <= '.intval($filter['price'][1]);
                $where[] = 'price <= '.floatval($filter['price'][1]);
            }
            if(!is_numeric($filter['price'][0])||!is_numeric($filter['price'][1])){
                unset($filter['price']);
            }
            /*
            if($filter['price'][0] && $filter['price'][1]){
                $where[] = 'price >= '.min($filter['price']).' AND price <= '.max($filter['price']);
            }*/
            unset($filter['price']);
        }else if($filter['priceto']){
            if(empty($filter['pricefrom'])) $filter['pricefrom'] = 0;

            $where[] = 'price >= '.$filter['pricefrom'].' AND price <= '.$filter['priceto'];
            unset($filter['pricefrom']);
            unset($filter['priceto']);
        }else if(!is_numeric($filter['price'])){
            unset($filter['price']);
        }else{
            unset($filter['pricefrom']);
            unset($filter['priceto']);
        }
        if(isset($filter['cost'])){
            if(!is_numeric($filter['cost'])){
                unset($filter['cost']);
            }
        }
        if(isset($filter['mktprice'])){
            if(!is_numeric($filter['mktprice'])){
                unset($filter['mktprice']);
            }
        }
        if(is_numeric($filter['store']) && $filter['_store_search']){
             $filter['store|'.$filter['_store_search']] = $filter['store'];
             if($filter['store_from'] && $filter['store_to']){
                 $filter['store|'.$filter['_store_search']] = array($filter['store_from'],$filter['store_to']);
             }
             unset($filter['store'],$filter['_store_search']);
        }

        if(isset($filter['store']) && !is_numeric($filter['store'])){
                unset($filter['store']);
        }

        if(isset($filter['gkey']) && trim($filter['gkey'])){
            $filter['name'] = trim($filter['gkey']);
        }
        if($filter['searchname']){
           $filter['name'][]=$filter['searchname'];
        }

        if(isset($filter['name']) && $filter['name']){
            if(is_array($filter['name'])){
                $filter['name']=implode('+',$filter['name']);
                if($filter['name']){
                    $filter['name']=str_replace('%xia%','_',$filter['name']);
                    $filter['name']=preg_replace('/[\'|\"]/','+',$filter['name']);
                    $GLOBALS['search']=$filter['name'];
                    $filter['name'] = urldecode($filter['name']);
                    $where[]=$object->wFilter($filter['name']);
                }
            }else{ //后台搜索
                $GLOBALS['search']=$filter['name'];
                $where[] = 'name LIKE \'%'.trim(mysql_real_escape_string($filter['name'])).'%\'';
            }
            $filter['name'] = null;
        }

        if( isset($filter['spec_desc']) ){
            if( $filter['spec_desc'] === 'true' ){
                $where[] = '(spec_desc IS NOT NULL && spec_desc != \'\' && spec_desc != \'a:0:{}\')';
            }
            if( $filter['spec_desc'] === 'false' ){
                $where[] = '(spec_desc IS NULL || spec_desc = \'\' || spec_desc = \'a:0:{}\')';
            }
            unset($filter['spec_desc']);
        }
        if(!$filter['goods_type'])
        $filter['goods_type'] = 'normal';

		foreach($filter as $k=>$v){
		    if(!isset($v)) unset($filter[$k]);
		}
        return parent::dbeav_filter_parser($filter,null,$where,$object);
        }


    function _inner_getFilterType($type,$var,$force=true){
        if(!$this->use_like && !is_array($var) && $force){
            $type = 'nequal';
        }
        $FilterArray= array('than'=>' > '.$var,
                            'lthan'=>' < '.$var,
                            'nequal'=>' = \''.$var.'\'',
                            'noequal'=>' <> \''.$var.'\'',
                            'tequal'=>' = \''.$var.'\'',
                            'sthan'=>' <= '.$var,
                            'bthan'=>' >= '.$var,
                            'has'=>' like \'%'.$var.'%\'',
                            'head'=>' like \''.$var.'%\'',
                            'foot'=>' like \'%'.$var.'\'',
                            'nohas'=>' not like \'%'.$var.'%\'',
                            'between'=>' {field}>='.$var[0].' and '.' {field}<'.$var[1],
                            'in' =>" in ('".implode("','",(array)$var)."') ",
                            );
        return $FilterArray[$type];

    }
}
