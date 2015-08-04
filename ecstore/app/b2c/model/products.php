<?php



class b2c_mdl_products extends dbeav_model{
    var $has_many = array(
        'price/member_lv_price' => 'goods_lv_price:contrast',
        );


    function __construct($app){
        parent::__construct($app);
        //使用meta系统进行存储
        $this->use_meta();
    }

    function getRealStore( $pId ){

        $data = $this->dump($pId,'store,freez');

        if( $pId === null )
            return null;
        return $data['store'] - $data['freez'];
    }

    function checkStore($pId, $quantity){
        $realQuantity = $this->getRealStore($pId);
        if(!is_null($realQuantity)){
            if($realQuantity < $quantity){

                return false;
            }
        }
        return true;

    }

    function getRealMkt($price){
        if($this->app->getConf('site.show_mark_price')=='true'){
            $math = $this->app->getConf('site.market_price');
            $rate = $this->app->getConf('site.market_rate');
            if($math == 1)
               return $price = $price*$rate;
            if($math == 2)
               return $price = $price+$rate;
        }else{
            return $price;
        }
    }

    function save(&$data,$mustUpdate = null){
        if (isset($data['spec_desc']) && $data['spec_desc'] && is_array($data['spec_desc']) && isset($data['spec_desc']['spec_value']) && $data['spec_desc']['spec_value'])
        {
            $oSpec = $this->app->model('specification');
            $tmpSpecInfo = array();
            foreach( $data['spec_desc']['spec_value'] as $spec_v_k => $spec_v_v ){
                $specname = $oSpec->dump( $spec_v_k,'spec_name' );
                $tmpSpecInfo[] = $specname['spec_name'].'：'.$spec_v_v;
            }
            $data['spec_info'] = implode('、', (array)$tmpSpecInfo);
        }
        if( $data['price']['member_lv_price'] )
            foreach( $data['price']['member_lv_price'] as $k => $v ){
                $data['price']['member_lv_price'][$k]['goods_id'] = $data['goods_id'];
            }

        return parent::save($data,$mustUpdate);
    }

    function dump($filter,$field = '*',$subSdf = null){
        $data = parent::dump($filter,$field,$subSdf);
        if( !isset($this->site_member_lv_id ) ){
            $obj_member = $this->app->model('members');
            $siteMember = $obj_member->get_current_member();
            $this->site_member_lv_id = $siteMember['member_lv'];
        }
        if (isset($data['price']) && $data['price'] && is_array($data['price']) && isset($data['price']['member_lv_price']) && $data['price']['member_lv_price'] && is_array($data['price']['member_lv_price']))
        {
            if( array_key_exists( 'member_lv_price', $data['price'] ) && array_key_exists( $this->site_member_lv_id, $data['price']['member_lv_price'] ) ){
                $data['price']['price']['current_price'] = $data['price']['member_lv_price'][$this->site_member_lv_id]['price'];
            }else{
                $data['price']['price']['current_price'] = $data['price']['price']['price'];
            }
        }
        return $data;
    }

    /**
     * 重写getlist方法
     */
    public function getList($cols='*',$filter=array(),$start=0,$limit=-1,$orderType=null){
        $arr_product = parent::getList($cols,$filter,$start,$limit,$orderType);
        $obj_extends_service = kernel::servicelist('b2c.api_goods_extend_actions');
        if ($obj_extends_service)
        {
            foreach ($obj_extends_service as $obj)
            {
                $obj->extend_get_product_list($arr_product);
            }
        }

        return $arr_product;
    }

    function _dump_depends_goods_lv_price(&$data,&$redata,$filter,$subSdfKey,$subSdfVal){
        $oMlv = &$this->app->model('member_lv');
        $memLvId = $oMlv->getList('member_lv_id','',0,-1);
        foreach( $memLvId as $aMemLvId )
            $idArray[] = array( 'level_id'=>$aMemLvId['member_lv_id'],'product_id'=>$data['product_id'] );
        $subObj = &$this->app->model('goods_lv_price');
        //$idArray = $subObj->getList( implode(',',(array)$subObj->idColumn), $filter,0,-1 );
        foreach( (array)$idArray as $aIdArray ){
            $subDump = $subObj->dump($aIdArray,$subSdfVal[0],$subSdfVal[1]);
            if( $this->has_many[$subSdfKey] ){
                switch( count($aIdArray) ){
                    case 1:
                        eval('$redata["'.implode( '"]["', explode('/',$subSdfKey) ).'"][current($aIdArray)] = $subDump;');
                        break;
                    case 2:
                        eval('$redata["'.implode( '"]["', explode('/',$subSdfKey) ).'"][current(array_diff_assoc($aIdArray,$filter))] = $subDump;');
                        break;
                    default:
                        eval('$redata["'.implode( '"]["', explode('/',$subSdfKey) ).'"][] = $subDump;');
                        break;
                }
            }else{
                eval('$redata["'.implode( '"]["', explode('/',$subSdfKey) ).'"] = $subDump;');
            }
        }
    }

    function getProductLvPrice($goodsId){
        $sql = 'SELECT goods_id, bn, spec_info, product_id, cost, price , mktprice FROM sdb_b2c_products WHERE goods_id IN ('.implode(',',$goodsId).')';

        $proList = $this->db->select($sql);

        $levelList = $this->db->select('SELECT goods_id, product_id, level_id, price AS mprice FROM sdb_b2c_goods_lv_price WHERE goods_id IN ('.implode(',',$goodsId).')');
        $returnData = array();
        $lvPrice = array();
        foreach( $levelList as $level )
            $lvPrice[$level['product_id']][$level['level_id']] = $level['mprice'] ;

        foreach( $proList as $pro )
            $returnData[$pro['goods_id']][] = array('product_id'=>$pro['product_id'],'bn'=>$pro['bn'], 'pdt_desc'=>$pro['spec_info'], 'price'=>$pro['price'], 'lv_price'=>$lvPrice[$pro['product_id']], 'cost'=>$pro['cost'],'mktprice'=>$pro['mktprice'] );


        return $returnData;
    }

    function getProductStore($goodsId){
        $sql = 'SELECT goods_id, bn, spec_info, product_id, store FROM sdb_b2c_products WHERE goods_id IN ('.implode(',',$goodsId).')';
        $proList = $this->db->select($sql);
        $returnData = array();
        foreach( $proList as $pro )
            $returnData[$pro['goods_id']][] = array( 'product_id'=>$pro['product_id'],'bn'=>$pro['bn'], 'pdt_desc'=>$pro['spec_info'], 'store'=>$pro['store'] );
        return $returnData;
    }

    function batchUpdateText( $goods_id, $updateType , $updateName , $updateValue ){ //review: 注意$this->db->quote 必要的数据
     $sql = 'UPDATE sdb_b2c_goods SET ';
        switch($updateType){
            case 'name':
                $sql .= $updateName.' = "'.$updateValue.'" WHERE goods_id in ('.implode(',',$goods_id).')';
                break;

            case 'add':
                $sql .= $updateName.' = CONCAT("'.$updateValue['front'].'",'.$updateName.',"'.$updateValue['after'].'") WHERE goods_id in ('.implode(',',$goods_id).')';
                break;

            case 'replace':
                $sql .= $updateName.' = REPLACE( '.$updateName.', "'.$updateValue['front'].'" , "'.$updateValue['after'].'" ) WHERE goods_id in ('.implode(',',$goods_id).') AND REPLACE( '.$updateName.', "'.$updateValue['front'].'" , "'.$updateValue['after'].'" ) != "" ';
                break;
        }
        $this->db->exec($sql);
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志,商品名称和商品简介@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'inlogs')){
                $arr = array('name'=>'商品名称','brief'=>'商品简介');
                if(count($goods_id)<101){
                    $goodsbn = implode(',',$goods_id);
                }else{
                    $objGoods = &$this->app->model('goods');   
                    $goods_bn = $objGoods->getList('bn',array('goods_id'=>$goods_id));
                    $v2tmp='';
                    foreach($goods_bn as $v2){
                        $v2tmp .=$v2['bn'].',';
                    }
                    $goodsbn=rtrim($v2tmp,',');
                }
                switch($updateType){
                    case 'name':
                        $memo = '批量操作商品编号(或者ID)为('.$goodsbn.')的'.$arr[$updateName].'全部修改为('.$updateValue.')';
                        break;
                    case 'add':
                        $memo = '批量操作商品编号(或者ID)为('.$goodsbn.')的'.$arr[$updateName].'增加前缀('.$updateValue['front'].')后缀('.$updateValue['after'].')';
                        break;
                    case 'replace':
                        $memo = '批量操作商品编号(或者ID)为('.$goodsbn.')的'.$arr[$updateName].'查找名称中有('.$updateValue['front'].')的替换为('.$updateValue['after'].')';
                        break;
                }
                $obj_operatorlogs->inlogs($memo, $arr[$updateName], 'goods');
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志,商品名称和商品简介@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function syncProNameByGoodsId($gids){
        $sql = 'UPDATE sdb_b2c_products p , sdb_b2c_goods g SET p.name= g.name WHERE g.goods_id = p.goods_id AND g.goods_id IN ('.(implode(',',$gids)).')';
        return $this->db->exec($sql);
    }

    function batchUpdateInt( $goods_id, $updateName, $updateValue , $tableName = '' ){
        $sql = 'UPDATE '.( $tableName?$tableName:'sdb_b2c_goods').' SET '.$updateName.' = '.$updateValue.' WHERE goods_id in ( '.implode(',', $goods_id).' )';
        $this->db->exec($sql);
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志,商品排序和分类转换@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'inlogs')){
                $arr = array('d_order'=>'商品排序','cat_id'=>'分类转换');
                if(count($goods_id)>100){
                    $goodsbn = implode(',',$goods_id);
                }else{
                    $objGoods = &$this->app->model('goods');   
                    $goods_bn = $objGoods->getList('bn',array('goods_id'=>$goods_id));
                    $v2tmp='';
                    foreach($goods_bn as $v2){
                        $v2tmp .=$v2['bn'].',';
                    }
                    $goodsbn=rtrim($v2tmp,',');
                }
                if($updateName == 'cat_id'){
                    $catName = $this->db->selectrow('SELECT cat_name FROM sdb_b2c_goods_cat WHERE cat_id = '.intval($updateValue) );
                    $upvalue = $catName['cat_name'];
                }else{
                    $upvalue = $updateValue;
                }
                $memo = '批量操作商品编号(或者ID)为('.$goodsbn.')的('.$arr[$updateName].')全部修改为('.$upvalue.')';
                $obj_operatorlogs->inlogs($memo, $arr[$updateName], 'goods');
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志,商品排序和分类转换@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function batchUpdateArray( $goods_id , $tableName, $updateName, $updateValue ){
        $addSql = array();
        foreach( $updateName as $k => $v )
            $addSql[] = $v.' = "'.$updateValue[$k].'" ';
        $sql = 'UPDATE '.$tableName.' SET '.implode(',', $addSql).' WHERE goods_id in ('.implode(',',$goods_id).') ';
        $this->db->exec($sql);
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志,商品品牌@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'inlogs')){
                if(count($goods_id)>100){
                    $goodsbn = implode(',',$goods_id);
                }else{
                    $objGoods = &$this->app->model('goods');   
                    $goods_bn = $objGoods->getList('bn',array('goods_id'=>$goods_id));
                    $v2tmp='';
                    foreach($goods_bn as $v2){
                        $v2tmp .=$v2['bn'].',';
                    }
                    $goodsbn=rtrim($v2tmp,',');
                }
                $memo = '批量操作商品编号(或者ID)为('.$goodsbn.')的(商品品牌)全部修改为('.$updateValue['1'].')';
                $obj_operatorlogs->inlogs($memo, '商品品牌', 'goods');
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志,商品品牌@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function batchUpdateByOperator( $goods_id, $tableName, $updateName , $updateValue, $operator=null , $fromName = null ){
        $sql = '';  //review: 注意$this->db->quote 必要的数据
        $updateValue = trim($updateValue);
        if( $operator == '-' ){

            $sql = 'UPDATE '.$tableName.' SET '.$updateName.' = 0 WHERE '.( strstr($tableName, ',')?' a.goods_id = b.goods_id AND a.':'' ).' goods_id IN ('.implode(',',$goods_id).') AND '.$updateName.' IS NOT NULL AND '.( $fromName?$fromName:$updateName ).'<='.floatval($updateValue);
            $this->db->exec($sql);

            $sql = 'UPDATE '.$tableName.' SET '.$updateName.' = '.( $fromName?$fromName:$updateName ).' '.$operator.' '.floatval($updateValue).' WHERE '.( strstr($tableName, ',')?' a.goods_id = b.goods_id AND a.':'' ).' goods_id IN ('.implode(',',$goods_id).') AND '.$updateName.' IS NOT NULL AND '.( $fromName?$fromName:$updateName ).'>'.floatval($updateValue);
            $this->db->exec($sql);


        }else{
            if(empty($updateValue) && $updateValue !== '0'){
                $sql = 'UPDATE '.$tableName.' SET '.$updateName.' = NULL  WHERE '.( strstr($tableName, ',')?' a.goods_id = b.goods_id AND a.':'' ).' goods_id IN ('.implode(',',$goods_id).') ';
            }else{
                $sql = 'UPDATE '.$tableName.' SET '.$updateName.' = round('.( $operator?( $fromName?$fromName:$updateName ).' '.$operator.' '.$updateValue:'"'.$updateValue.'"' ).', 3) WHERE '.( strstr($tableName, ',')?' a.goods_id = b.goods_id AND a.':'' ).' goods_id IN ('.implode(',',$goods_id).') ';
            }

            $this->db->exec($sql);
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志，统一调价，调库存，调质量@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'inlogs')){
                if($tableName=='sdb_b2c_products'){
                    $arr_name=array('mktprice'=>'市场价','price'=>'销售价','cost'=>'成本价','store'=>'库存','weight'=>'重量');
                    //$m_sql = 'SELECT mktprice,price,cost, store, weight, bn FROM sdb_b2c_products WHERE  goods_id IN ('.implode(',',$goods_id).') AND '.$updateName.' IS NOT NULL ';
                    
                    //$basicinfo = $this->db->select($m_sql);
                    $basicinfo = $this->getList('mktprice,price,cost,store,weight,bn',array('goods_id'=>$goods_id));
                    $v2tmp = '';
                    foreach($basicinfo as $key=>$val){
                        $v2tmp .='('.$val['bn'].' 改为 '.$val[$updateName].'),';
                    }
                    
                    $productsbn=rtrim($v2tmp,',');
                    //$memo = '相关商品货号('.$productsbn.')'.',批量修改为('.$basicinfo['0'][$updateName].')';
                    $memo = '商品货号'.$productsbn;
                    $operate_key = '批量统一修改商品'.$arr_name[$updateName];
                    $obj_operatorlogs->inlogs($memo, $operate_key, 'goods');
               }
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志，统一调价，调库存，调质量@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function batchUpdateStore($store){
        foreach( $store as $goods ){
            foreach( $goods as $proId => $pstore ){
            	$pstore = trim($pstore);
                if($pstore === '0'){
                    $this->db->exec('UPDATE sdb_b2c_products SET store = 0 WHERE product_id = '.intval($proId));
                }elseif(empty($pstore)){
                    $this->db->exec('UPDATE sdb_b2c_products SET store = NULL WHERE product_id = '.intval($proId));
            	}else{
                    $this->db->exec('UPDATE sdb_b2c_products SET store = '.(intval($pstore)<0?0:intval($pstore)).' WHERE product_id = '.intval($proId));
            	}

            }
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志,分别调整商品库存@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'inlogs')){
                $memo_tmps='';
                foreach( $store as $goods ){
                    $memo_tmp='';
                    foreach( $goods as $proId => $pstore ){
                        $pstore = trim($pstore);
                        $probn = $this->dump(array('product_id'=>$proId),'bn');
                        if($pstore === '0'){
                            $id_store = array('probn'=>$probn['bn'],'pstore'=>'0');
                        }elseif(empty($pstore)){
                            $id_store = array('probn'=>$probn['bn'],'pstore'=>'空');
                        }else{
                            $id_store = array('probn'=>$probn['bn'],'pstore'=>(intval($pstore)<0?0:intval($pstore)));
                        }
                        $memo_tmp .='货号：'.$id_store['probn'].',库存:'.$id_store['pstore'].'; ';
                    }
                    $memo_tmps.= $memo_tmp;
                }
                $memo = '批量修改('.$memo_tmps.')';
                $obj_operatorlogs->inlogs($memo, '批量分别调库存', 'goods');
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志,分别调整商品库存@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function synchronizationStore($goods_id){
        $storeSum1 = $this->db->select('SELECT goods_id FROM sdb_b2c_products WHERE goods_id in ('.implode(',',$goods_id).') AND store IS NULL GROUP BY goods_id');
        $nullStore = array();
        foreach( $storeSum1 as $aStore ){
            $nullStore[$aStore['goods_id']] = 1;
        }
        $storeSum = $this->db->select('SELECT goods_id, sum(store) as storesum FROM sdb_b2c_products WHERE goods_id in ('.implode(',',$goods_id).') GROUP BY goods_id');
        foreach($storeSum as $v){
            $this->db->exec('UPDATE sdb_b2c_goods SET store = '.( isset( $nullStore[$v['goods_id']] )?'null':intval($v['storesum']) ).' WHERE goods_id = '.intval($v['goods_id']));
        }
        return true;
    }

    function batchUpdatePrice($pricedata){
        foreach( $pricedata as $updateName => $data ){
            if( in_array( $updateName , array( 'price', 'cost','mktprice' ) ) ) {
                foreach( $data as $goodsId => $goodsItem ){
                    foreach( $goodsItem as $proId => $price ){
                        $this->db->exec( 'UPDATE sdb_b2c_products SET '.$updateName.' = '.floatval(trim($price)).' WHERE product_id = '.intval($proId) );
                    }
                    $minPrice = $this->db->selectrow('SELECT MIN(price) AS mprice FROM sdb_b2c_products WHERE goods_id = '.intval($goodsId) );
                    if($updateName=='price')
                    $this->db->exec( 'UPDATE sdb_b2c_goods SET '.$updateName.' = '.floatval(trim($minPrice['mprice'])).' WHERE goods_id = '.intval($goodsId) );
                    else
                    $this->db->exec( 'UPDATE sdb_b2c_goods SET '.$updateName.' = '.floatval(trim($price)).' WHERE goods_id = '.intval($goodsId) );
                }
            }else{
                foreach( $data as $goodsId => $goodsItem )
                    foreach( $goodsItem as $proId => $price ){
                        if( $price == null || $price == '' ){
                            $this->db->exec('DELETE FROM sdb_b2c_goods_lv_price WHERE product_id = '.intval($proId).' AND level_id = '.intval($updateName).' AND goods_id = '.intval($goodsId));
                            continue;
                        }
                        $datarow = $this->db->selectrow('SELECT count(*) as c FROM sdb_b2c_goods_lv_price WHERE product_id = '.intval($proId).' AND level_id = '.intval($updateName).' AND goods_id = '.intval($goodsId));
                        if($datarow['c'] > 0)
                            $this->db->exec('UPDATE sdb_b2c_goods_lv_price SET price = '.floatval(trim($price)).' WHERE product_id = '.intval($proId).' AND level_id = '.intval($updateName).' AND goods_id = '.intval($goodsId));
                        else
                            $this->db->exec('INSERT INTO sdb_b2c_goods_lv_price (product_id, level_id, goods_id, price ) VALUES ( '.intval($proId).', '.intval($updateName).', '.intval($goodsId).', '.floatval($price).' )');
                    }
            }
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志，分别调价@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'inlogs')){
                $obj_lv = $this->app->model('member_lv');
                $arr_mapname = array('price'=>'销售价','cost'=>'成本价','mktprice'=>'市场价');
                $memo='';
                foreach( $pricedata as $updateName => $data ){
                    $m1='';
                    $m2='';
                    if( in_array( $updateName , array( 'price', 'cost','mktprice' ) ) ) {
                        foreach( $data as $goodsId => $goodsItem ){
                            $memo_tmp1='';
                            foreach( $goodsItem as $proId => $price ){
                                #$probn = $this->dump(array('product_id'=>$proId),'bn');
                                $probn = $this->getRow('bn',array('product_id'=>$proId));
                                $memo_tmp1 .= '修改货号为('.$probn['bn'].')的('.$arr_mapname[$updateName].')为('.$price.');<br> ';
                            }
                            $m1.=$memo_tmp1;
                        }
                    }
                    else{
                        #$lv_name = $obj_lv->dump(array('member_lv_id'=>$updateName),'name,dis_count');
                        $lv_name = $obj_lv->getRow('name,dis_count',array('member_lv_id'=>$updateName));
                        foreach( $data as $goodsId => $goodsItem ){
                            $memo_tmp2='';
                            foreach( $goodsItem as $proId => $price ){
                                #$probn = $this->dump(array('product_id'=>$proId),'bn');
                                $probn = $this->getRow('bn',array('product_id'=>$proId));
                                if( $price == null || $price == '' ){
                                    $price= $pricedata['price'][$goodsId][$proId]*$lv_name['dis_count'];
                                    eval("\$price=$price;");
                                }
                                $memo_tmp2 .= '修改货号为('.$probn['bn'].')的('.$lv_name['name'].')价为('.$price.');<br> ';
                            }
                            $m2.=$memo_tmp2;
                        }
                    }
                    $memo .= $m1.$m2;
                }
                $obj_operatorlogs->inlogs($memo, '分别调价(只是保存当前操作后的数据)', 'goods');
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志，分别调价@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function batchUpdateMemberPriceByOperator( $goods_id, $updateLvId, $updateValue, $operator=null , $fromName = null ){
        $aallProductId = $this->db->select('SELECT product_id,goods_id FROM sdb_b2c_products WHERE goods_id IN ('.implode(',',$goods_id).')');
        $aupdateProductId = $this->db->select('SELECT product_id,goods_id FROM sdb_b2c_goods_lv_price WHERE goods_id IN ('.implode(',',$goods_id).') AND level_id = '.$updateLvId);
        $allProductId = array();
        $updateProductId = array();
        foreach( $aallProductId as $allv )
            $allProductId[$allv['product_id']] = $allv['goods_id'];
        foreach( $aupdateProductId as $alluv )
            $updateProductId[$alluv['product_id']] = $alluv['goods_id'];
        unset($aallProductId, $aupdateProductId);
        $insertProductId = array_diff_assoc( $allProductId, $updateProductId);

        if( $operator ){
            if( $updateValue ){
                if( $fromName && is_numeric($fromName) ){        //用会员价修改会员价
                    $member_lv_Row = $this->db->selectrow("SELECT dis_count FROM sdb_b2c_member_lv WHERE member_lv_id = ".$fromName);
                    foreach( $updateProductId as $upProId => $upGoodsId ){
                        $dataRow = $this->db->selectrow('SELECT price FROM sdb_b2c_goods_lv_price WHERE level_id = '.$fromName.' AND product_id = '.$upProId.' AND goods_id = '.$upGoodsId);
                        $isup_flag1 = floatval($dataRow['price']).$operator.floatval($updateValue);//修复会员价为负情况，判断修改后的价格是否小于0，是则不做修改@lujy
                        eval("\$isup_flag1=$isup_flag1;");
                        if( $isup_flag1>0){
                            $this->db->exec('UPDATE sdb_b2c_goods_lv_price SET price = '.$dataRow['price'].$operator.floatval($updateValue).' WHERE goods_id = '.$upGoodsId.' AND level_id = '.$updateLvId.' AND product_id = '.$upProId.'');
                        }
                    }
                    foreach( $insertProductId as $inProId => $inGoodsId ){
                        $dataRow = $this->db->selectrow('SELECT price FROM sdb_b2c_goods_lv_price WHERE level_id = '.$fromName.' AND product_id = '.$inProId.' AND goods_id = '.$inGoodsId);
                        if(!$dataRow)
                        {
                         $dataprice_Row = $this->db->selectrow("SELECT price AS price FROM sdb_b2c_products WHERE product_id = ".$inProId);
                         $dataRow['price'] = $dataprice_Row['price'] * floatval($member_lv_Row['dis_count']);
                        }
                        $isup_flag2 = floatval($dataRow['price']).$operator.floatval($updateValue);
                        eval("\$isup_flag1=$isup_flag2;");
                        if( $isup_flag2>0){
                            $this->db->exec('INSERT INTO sdb_b2c_goods_lv_price ( product_id, level_id, goods_id, price ) VALUES ('.$inProId.', '.$updateLvId.', '.$inGoodsId.', '.$dataRow['price'].$operator.floatval($updateValue).')');
                        }
                    }
                }else{          //用市场价、销售价、成本价修改会员价
                    foreach( $updateProductId as $upProId => $upGoodsId ){
                        $dataRow = array();
                        $upGoodsId = intval($upGoodsId);
                        if( $fromName == 'price' )
                            $dataRow = $this->db->selectrow('SELECT '.$fromName.' AS price FROM sdb_b2c_products WHERE product_id = '.$upProId);
                        else
                            $dataRow = $this->db->selectrow('SELECT '.$fromName.' AS price FROM sdb_b2c_goods WHERE goods_id = '.$upGoodsId);
                        $isup_flag3 = floatval($dataRow['price']).$operator.floatval($updateValue);
                        eval("\$isup_flag3=$isup_flag3;");
                        if( $isup_flag3>0){
                            $this->db->exec('UPDATE sdb_b2c_goods_lv_price SET price = '.$dataRow['price'].$operator.floatval($updateValue).' WHERE product_id = '.$upProId.' AND goods_id = '.$upGoodsId.' AND level_id = '.$updateLvId);
                        }
                    }
                    foreach( $insertProductId as $inProId => $inGoodsId ){
                        $inGoodsId = intval($inGoodsId);
                        $dataRow = array();
                        if( $fromName == 'price' )
                            $dataRow = $this->db->selectrow('SELECT '.$fromName.' AS price FROM sdb_b2c_products WHERE product_id = '.$inProId);
                        else
                            $dataRow = $this->db->selectrow('SELECT '.$fromName.' AS price FROM sdb_b2c_goods WHERE goods_id = '.$inGoodsId);
                        $isup_flag4 = floatval($dataRow['price']).$operator.floatval($updateValue);
                        eval("\$isup_flag4=$isup_flag4;");
                        if( $isup_flag4>0){
                            $this->db->exec('INSERT INTO sdb_b2c_goods_lv_price ( product_id, level_id, goods_id, price ) VALUES ('.$inProId.', '.$updateLvId.', '.$inGoodsId.', '.$dataRow['price'].$operator.floatval($updateValue).')');
                        }
                    }
                }
            }

        }else{
             if( $updateValue != null && $updateValue !='' ){
                foreach( $updateProductId as $upProId => $upGoodsId ){
                    $upGoodsId = intval($upGoodsId);
                    $this->db->exec( 'UPDATE sdb_b2c_goods_lv_price SET price = '.floatval($updateValue).' WHERE goods_id = '.intval($upGoodsId).' AND level_id = '.intval($updateLvId).' AND product_id = '.intval($upProId));
                }
                foreach( $insertProductId as $inProId => $inGoodsId ){
                    $this->db->exec( 'INSERT INTO sdb_b2c_goods_lv_price ( product_id, level_id, goods_id, price ) VALUES ('.intval($inProId).', '.intval($updateLvId).', '.intval($inGoodsId).', '.floatval($updateValue).')') ;
                }
             }else{
                $this->db->exec('DELETE FROM sdb_b2c_goods_lv_price WHERE goods_id IN ( '.implode(',',$goods_id).' ) AND level_id = '.intval($updateLvId));
             }
        }

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志，统一调会员价@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'inlogs')){
                $opesql = 'SELECT  sbp.goods_id,sbp.product_id,sbp.bn,sbml.name,sbglp.price
                        FROM sdb_b2c_products AS sbp, sdb_b2c_goods_lv_price AS sbglp, sdb_b2c_member_lv AS sbml
                        WHERE sbp.goods_id IN(' . implode(',',$goods_id) . ") AND sbglp.product_id = sbp.product_id AND sbglp.level_id={$updateLvId} AND sbml.member_lv_id={$updateLvId}";
                $probninfo = $this->db->select($opesql);
                $productsbn = '';
                foreach($probninfo as $key=>$val){
                    $bntmp .='('.$val['bn'].' 改为 '.$val['price'].'),';
                }
                $productsbn=rtrim($bntmp,',');
                $memo = '商品货号 '.$productsbn;
                $opoerate_key = '批量统一修改'.$probninfo['0']['name'].'价';
                $obj_operatorlogs->inlogs($memo, $opoerate_key, 'goods');
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志，统一调会员价@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        return true;
    }

    function getGoodsSellLogList($gid,$page=1,$limit=20){
        // modified by cam begin
        $today = time();
        //$purday = strtotime("-1 month");
        //$sql = "SELECT count(log_id) as _count FROM sdb_b2c_sell_logs WHERE goods_id = ".intval($gid)." and createtime>={$purday} and createtime<{$today} ORDER BY log_id DESC ";
        $sql = "SELECT count(log_id) as _count FROM sdb_b2c_sell_logs WHERE goods_id = ".intval($gid)." ORDER BY log_id DESC ";
        $data = $this->db->selectrow($sql);
        $count = $data['_count'];
        $page = $page?$page:1;
        $maxPage = ceil($count / $limit);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $limit;
        $start = $start<0 ? 0 : $start;
        //$sql = "SELECT l.*,l.name as alias FROM sdb_b2c_sell_logs as l left join sdb_b2c_members as m on l.member_id=m.member_id WHERE l.goods_id = ".intval($gid)." and l.createtime>={$purday} and l.createtime<{$today} ORDER BY l.log_id DESC limit ".$start.",".$limit;
        $sql = "SELECT l.*,l.name as alias FROM sdb_b2c_sell_logs as l left join sdb_b2c_members as m on l.member_id=m.member_id WHERE l.goods_id = ".intval($gid)."  ORDER BY l.log_id DESC limit ".$start.",".$limit;
        $result = array();
        foreach((array)$this->db->select($sql) as $val){
            if(empty($val['alias'])) $val['alias'] = $val['name'];
            $val['name'] = $val['alias'];
            //$val['alias'] = $this->replaceStartFilter($val['alias'],1);
            $val['alias'] = mb_substr($val['alias'], 0, 1, 'UTF-8').'****'.mb_substr($val['alias'], mb_strlen($string, 'UTF-8')-1, 1, 'UTF-8');
            $result['data'][] = $val;
        }
        //$result['data'] = $this->db->select($sql);
        $result['total'] = $count;
        $result['page'] = $maxPage;
        $result['current_page'] = $page;
        return $result;
       
    }
    
   
    private function replaceStartFilter($string, $start = 0, $end = 0) {
        $count = mb_strlen($string, 'UTF-8'); //此处传入编码，建议使用utf-8。此处编码要与下面mb_substr()所使用的一致
        if (!$count) {
            return $string;
        }
        if ($end == 0) {
            $end = $count-1;
        }

        $i = 0;
        $returnString = '';
        while ($i < $count) {
            $tmpString = mb_substr($string, $i, 1, 'UTF-8'); // 与mb_strlen编码一致
            if ($start <= $i && $i < $end) {
                $returnString .= '*';
            } else {
                $returnString .= $tmpString;
            }
            $i++;
        }
        return $returnString;
    }
  
    function getBatchEditInfo($filter){
        $r = $this->db->selectrow('select count( goods_id     q1) as count from sdb_b2c_goods where '.$this->_filter($filter));
        return $r;
    }

    function getGoodsSellLogNum($gid){
        $res = $this->db->selectrow('SELECT count(log_id) as totalnum FROM sdb_b2c_sell_logs WHERE goods_id = '.intval($gid));
        return intval($res['totalnum']);
    }

    /**
     * 如果商品下架则货品也要下架
     *
     * @param $goods_id 商品id
     * @param $status 商品的上下架状态
     * author @lujy
     */
    function pro_unmarketable($goods_id,$status){
        $result = array('goods_id'=>$goods_id);
        $data = array('marketable'=>$status);
        $is_update = $this->update($data,$result);

		//$this->storekv_umarkable_product($goods_id,$status);
		return $is_update;
    }



	///**
	// * 商品改造信息从kvstore里面存储的方法 - 货品需要下架判断
	// * @param int goods id
	// * @param string status
	// */
	//public function storekv_umarkable_product($goods_id,$status){
	//	base_kvstore::instance('_ec_optimize')->fetch('goods_info_'.$goods_id,$goods);

	//	/** 取到商品对应的所有规格 **/
	//	$arr_product = $this->getList('product_id',array('goods_id'=>$goods_id));
	//	foreach ((array)$arr_product as $product){
	//		$goods['product'][$product['product_id']]['status'] = $status;
	//	}

	//	base_kvstore::instance('_ec_optimize')->store('goods_info_'.$goods_id,$goods);
	//}

	/**
	 * 货品信息修改 - 保存到kvstore里面
	 * @param int goods id
	 * @param array 商品数据
	 */
	//public function storekv_product_info($goods_id,$data=array()){
	//	$product = array();

	//	$product[$data['product_id']] = array(
	//		'product_id'=>$data['product_id'],
	//		'bn'=>$data['bn'],
	//		'goods_id'=>$goods_id,
	//		'spec_desc'=>$data['spec_desc'],
	//		'status'=>$data['status'],
	//	);

	//	base_kvstore::instance('_ec_optimize')->fetch('goods_info_'.$goods_id,$goods);
	//	if (!$goods) return;
	//	if (!$goods['product']||!$goods['product'][$data['product_id']]) return;

	//	$goods['product'][$data['product_id']] = $product;
	//	/** 存储商品 **/
	//	base_kvstore::instance('_ec_optimize')->store('goods_info_'.$goods_id,$goods);

	//	/** 更新价格 **/
	//	base_kvstore::instance('_ec_optimize')->fetch('goods_price_'.$goods_id,$price);
	//	$price['product'][$data['product_id']]['price'] = $data['price'];
	//	base_kvstore::instance('_ec_optimize')->store('goods_price_'.$goods_id,$price);

	//	/** 库存保存 **/
	//	base_kvstore::instance('_ec_optimize')->fetch('goods_store_'.$goods_id,$store);
	//	$store['product'][$data['product_id']] = $data['store'];
	//	base_kvstore::instance('_ec_optimize')->store('goods_store_'.$goods_id,$store);
	//}

	/**
	 * 获取指定货品信息
	 * @param int goods id
	 * @param int product id
	 * @return array 货品数据
	 */
    //public function getkv_product_info($goods_id,$product_id){
	//	base_kvstore::instance('_ec_optimize')->fetch('goods_info_'.$goods_id,$goods);
	//	if (!$goods) return;
	//	if (!$goods['product']||!$goods['product'][$product_id]) return;
	//
	//	return $goods['product'][$product_id];
	//}
   
    function getGoodsSeeList($gid,$start=0,$limit=10){
        $objGoods = $this->app->model("goods");
        $aCat = array();
        $aStore = array();
        foreach($objGoods->getList('cat_id,store_id', array('goods_id'=>$gid)) as $rows){
            $aCat[] = $rows['cat_id'];
            $aStore[] = $rows['store_id'];
        }
        $aGoods = array();
        $agid = array();
        $filter = array('cat_id|in'=>$aCat,'store_id|in'=>$aStore,'goods_id|noequal'=>$gid,'marketable'=>'true','disabled'=>'false');
        foreach($objGoods->getList('goods_id,price,thumbnail_pic,image_default_id', $filter,$start,$limit) as $rows){
            //if($gid == $rows['goods_id']) continue;
            $aGoods[$rows['goods_id']] = $rows;
            $agid[] = $rows['goods_id'];
        }
        $data['count'] = $objGoods->count($filter);
        $objComments = $this->app->model("member_comments");
        $filter = array('type_id'=>$agid,'display'=>'true','disabled'=>'false','for_comment_id'=>0,'comments_type'=>'1');
        foreach($objComments->getList('addon,comment,author_id,author,type_id',$filter,0,20,'comment_id desc') as $rows){
            $rows['addon'] = unserialize($rows['addon']);
            $aGoods[$rows['type_id']]['comments'][] = $rows;
        }
        $data['list'] = $aGoods;
        return $data;
    }
    
   function getproductofstore($store_id,$arygoodsid=null){
         
           $sql ="SELECT sdb_b2c_products.product_id,sdb_b2c_goods.bn as goods_bn ,sdb_b2c_goods.goods_id,sdb_b2c_products.bn AS product_bn,sdb_b2c_products.store  from sdb_b2c_goods LEFT JOIN sdb_b2c_products ON  sdb_b2c_goods.goods_id= sdb_b2c_products.goods_id WHERE 
           sdb_b2c_goods.store_id={$store_id}"; 
           if($arygoodsid){
               $comma = implode(",", $arygoodsid);
               $sql .=" AND sdb_b2c_goods.goods_id in (". $comma .") ";

           }
                  
           return $this -> db -> select($sql);
   }

   function UpdateGoodsStore($goods_bn){
        $objGoods = $this->app->model("goods");
        $arygoods=$objGoods->getList('goods_id', array('bn'=>$goods_bn));

       
        if($arygoods){
            $goods_id=$arygoods[0]['goods_id'];

            $Sql="UPDATE sdb_b2c_goods 
                     SET sdb_b2c_goods.store=
                     (SELECT sum(sdb_b2c_products.store) FROM sdb_b2c_products WHERE sdb_b2c_products.goods_id={$goods_id})
                   WHERE sdb_b2c_goods.goods_id={$goods_id}";
           return $this->db->exec($Sql);

        }

        return false;

   }

   function getstoreidbyproductbn($product_bn){
        $Sql="SELECT sdb_b2c_goods.store_id from sdb_b2c_goods LEFT JOIN  sdb_b2c_products ON sdb_b2c_goods.goods_id= sdb_b2c_products.goods_id
                WHERE  sdb_b2c_products.bn='{$product_bn}'";
                 
        return $this -> db -> select($Sql);

   }

}
