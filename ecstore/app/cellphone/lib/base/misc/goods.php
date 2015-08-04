<?php
  
class cellphone_base_misc_goods extends cellphone_cellphone
{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }
    
    public function get_goods(){
        $params = $this->params;
        $must_params = array(
            'goods_id'=>'商品标识',
        );
        $this->check_params($must_params);
        $params['object_type'] = $params['object_type']?$params['object_type']:'normal';
        if($params['object_type'] != 'package'){
            $this->goods_detail($params);
        }else{
            $this->activity_detail($params);
        }
    }
    
    // 获取商品信息
    public function goods_detail($params){
        $objGoods = &app::get('b2c')->model('goods');
        $gid = intval($params['goods_id']);
        if(!$gid){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
            exit;
        }else{
            $rs = $objGoods->dump(array('goods_id'=>$gid),'goods_id');
            if(!$rs || empty($rs)){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
                exit;
            }
        }
        $picSize = in_array(strtolower($params['pic_size']), array('cl', 'cs'))?strtolower($params['pic_size']):'cl';
        $aGoods = array();
        $siteMember = $this->get_current_member();
        $siteMember['member_lv'] = isset($siteMember['member_lv'])?$siteMember['member_lv']:-1;
        $cols = "store_id,goods_id,name,price,mktprice,marketable,act_type,buy_m_count,fav_count,comments_count
,avg_point,freight_bear,store,store_freeze,notify_num,score,score_setting,weight,unit,min_buy,package_scale,package_unit,package_use,nostore_sell,disabled,spec_desc,brief,adjunct";
        $aGoods_list = $objGoods->getList($cols ,array('goods_id'=>$gid, 'store_id|than'=>0, 'disabled'=>'false'));
        $aGoods['store_id'] = $aGoods_list[0]['store_id']?intval($aGoods_list[0]['store_id']):0;
        $aGoods['goods_id'] = $aGoods_list[0]['goods_id'];
        $aGoods['name'] = $aGoods_list[0]['name'];
        $aGoods['price'] = $aGoods_list[0]['price'];
        $objProduct = &app::get('b2c')->model('products');
        if( $aGoods_list[0]['mktprice'] == '' || $aGoods_list[0]['mktprice'] == null )
            $aGoods['mktprice'] = $objProduct->getRealMkt($aGoods['price']);
        else
            $aGoods['mktprice'] = $aGoods_list[0]['mktprice'];
        $aGoods['act_type'] = $aGoods_list[0]['act_type']=='package'?'normal':$aGoods_list[0]['act_type'];
        $aGoods['buy_m_count'] = $aGoods_list[0]['buy_m_count'];
        $aGoods['fav_count'] = $aGoods_list[0]['fav_count'];
        $aGoods['comments_count'] = $aGoods_list[0]['comments_count'];
        $aGoods['avg_point'] = $aGoods_list[0]['avg_point'];
        $aGoods['freight_bear'] = $aGoods_list[0]['freight_bear'];
        $aGoods['weight'] = $aGoods_list[0]['weight'];
        $aGoods['unit'] = $aGoods_list[0]['unit'];
        $aGoods['store'] = $aGoods_list[0]['store'];
        $aGoods['freez'] = 0;
        $aGoods['store_freeze'] = $aGoods_list[0]['store_freeze'];
        $aGoods['unit'] = $aGoods_list[0]['unit'];
        if($siteMember['member_id']){
            $temp = app::get('b2c')->model('member_goods')->getRow('goods_id',array('goods_id'=>$gid,'member_id'=>$siteMember['member_id'],'status'=>'ready','disabled'=>'false','type'=>'fav','object_type'=>'goods'));
            $aGoods['is_fav'] = $temp?1:0;
        }else{
            $aGoods['is_fav'] = 0;
        }
        
        if($aGoods_list[0]['score_setting'] == 'percent'){
            $point_money_value = app::get('b2c')->getConf('site.point_money_value');
            if($point_money_value == ''){
                $point_money_value = 1;
            }
            $aGoods['gain_score'] = intval($aGoods_list[0]['price'] * ($aGoods_list[0]['score']/100) * $point_money_value);
        }else{
            $aGoods['gain_score'] = $aGoods_list[0]['score'];
        }

        $objMemberLv = &app::get('b2c')->model("member_lv");
        $memLvaData = array();
        foreach((array)$objMemberLv->getList('member_lv_id,name,dis_count') as $row){
            $memLvaData[$row['member_lv_id']] = $row;
        }
        $sql = "SELECT p.product_id,p.price,p.mktprice,p.store,p.freez,p.spec_desc,l.level_id,l.price as lv_price
FROM  `sdb_b2c_products` AS p
LEFT JOIN  `sdb_b2c_goods_lv_price` AS l ON p.product_id = l.product_id
WHERE p.goods_id ={$gid}";
        $productAmp = array();
        foreach((array)$objMemberLv->db->select($sql) as $pro_v){
            $productAmp[$pro_v['product_id']]['goods_id'] = $gid;
            $productAmp[$pro_v['product_id']]['product_id'] = $pro_v['product_id'];
            $temp = unserialize($pro_v['spec_desc']);
            $productAmp[$pro_v['product_id']]['spec_desc'] = array();
            foreach($temp['spec_private_value_id'] as $key => $value){
                $productAmp[$pro_v['product_id']]['spec_desc'][] = array(
                    'spec_id'=>$key,
                    'private_spec_value_id'=>$value,
                );
            }
            $productAmp[$pro_v['product_id']]['store'] = $pro_v['store']-$pro_v['freez'];
            $productAmp[$pro_v['product_id']]['freez'] = $pro_v['freez'];
            if(array_key_exists($siteMember['member_lv'], $memLvaData)){
                $memLv_v = $memLvaData[$siteMember['member_lv']];
                $productAmp[$pro_v['product_id']]['price'] = $pro_v['level_id']?$pro_v['lv_price']:(($memLv_v['dis_count']>0?$memLv_v['dis_count'] * $pro_v['price']:$pro_v['price']));
            }else{
                $productAmp[$pro_v['product_id']]['price'] = $pro_v['price'];
            }
            $productAmp[$pro_v['product_id']]['mktprice'] = $pro_v['mktprice'];
            $aGoods['freez'] +=  intval($pro_v['freez']);
        }
        $aGoods['product'] = array_values($productAmp);
        $objImage = &app::get("image")->model("image_attach");
        $image_data = $objImage->getList("attach_id,image_id",array("target_id"=>$gid,'target_type'=>'goods'),0,-1,"attach_id asc");
        $aGoods['images'] = null;
        foreach($image_data as $row){
            $aGoods['images'][] = $this->get_img_url($row['image_id'],$picSize);
        }
        $objSpec = &app::get('b2c')->model('specification');
        if( $aGoods_list[0]['spec_desc'] && is_array( $aGoods_list[0]['spec_desc'] ) ){
            foreach( $aGoods_list[0]['spec_desc'] as $specId => $spec ){
                $aRow = $objSpec->getList("*",array('spec_id'=>$specId));
                $aGoods['spec'][$specId] = $aRow[0];
                foreach( $spec as $pSpecId => $specValue ){
                    $specValue['spec_image'] = $this->get_img_url($specValue['spec_image'],'s');
                    $aGoods['spec'][$specId]['option'][$pSpecId] = array_merge( array('private_spec_value_id'=>$pSpecId), $specValue );
                }
                $aGoods['spec'][$specId]['option'] = array_values($aGoods['spec'][$specId]['option']);
            }
        }
        $aGoods['spec'] = array_values($aGoods['spec']);
        if( $aGoods['product'] ){
            $aProduct = current( $aGoods['product']);
            if( isset( $aProduct['price'] ) )
                $aGoods['current_price'] = $aProduct['price'];
        }elseif( $aGoods['price'] ){
            $aGoods['current_price'] = $aGoods['price'];
        }
        
        if(!$aGoods || $aGoods === false || !$aGoods['product']){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
            exit;
        }
        $aInfo = app::get('business')->model('storemanger')->getList('area,account_id',array('store_id'=>$aGoods['store_id']));
        if($aInfo[0]['area'])
        list($a,$area_from,$b) = explode(':', $aInfo[0]['area']);
        else $area_from ='';
        if($area_from) $area_from = explode('/', $area_from);
        if(is_array($area_from)) $area_from = (($area_from[0] == '北京' || $area_from[0] == '天津' || $area_from[0] == '上海' || $area_from[0] == '重庆')?'':$area_from[0]).$area_from[1];
        else $area_from = '';
        $aGoods['area_from'] = $area_from;
        $aGoods['dlytype_info'] = array();
        if($aGoods['freight_bear'] == 'member'){
            $aGoods['dlytype_info'] = $this->freight_detail($gid, $aGoods['store_id'], $aGoods['weight']);
        }
        if(!$siteMember['member_id']){
            $aGoods['login_status'] = 'nologin';
        }else{
            $obj_store = app::get('business')->model('storemanger');
            $obj_smb = app::get('business')->model('storemember');
            $is_business = $obj_store->count(array('account_id'=>$siteMember['member_id']));
            if(!$is_business) $is_business = $obj_smb->count(array('member_id'=>$siteMember['member_id']));
            if($is_business > 0) $aGoods['login_status'] = 'business';
            else $aGoods['login_status'] = 'member';
        }

        $cur = app::get('ectools')->model('currency');
        $cur_info = $cur->getFormat();
        if($cur_info['cur_sign']) {
            $cur_info['sign'] = $cur_info['cur_sign'];
        }
        $ret =array(
            'decimals'=>app::get('b2c')->getConf('system.money.decimals'),
            'dec_point'=>app::get('b2c')->getConf('system.money.dec_point'),
            'thousands_sep'=>app::get('b2c')->getConf('system.money.thousands_sep'),
            'fonttend_decimal_type'=>app::get('b2c')->getConf('system.money.operation.carryset'),
            'fonttend_decimal_remain'=>app::get('b2c')->getConf('system.money.decimals'),
            'sign' => $cur_info['sign']
        );
        if(isset($cur_info['cur_default']) && $cur_info['cur_default'] === "false") {
            $ret['cur_rate'] = $cur_info['cur_rate'];
        }
        $aGoods['money_format'] = $ret;
        $aGoods['discuss_info'] = $this->discuss_detail(array('goods_id'=>$gid,'pageLimit'=>'2'));
        $objComment = app::get('business')->model('comment_stores_point');
        $store_info = $objComment->getStoreInfo($aGoods['store_id']);
        foreach((array)$store_info['store_point'] as $row){
            $aGoods['store_info'][] = array(
                'name' => $row['name'],
                'avg_point' => $row['avg_point'],
                'avg_percent' => $row['avg_percent'],
            );
        }

        if($aGoods['act_type'] && $aGoods['act_type'] != 'normal' && $aGoods['act_type'] != 'package'){
            $app_list = cellphone_misc_exec::get_actapp();
            $app_id = $aGoods['act_type']=='group'?'groupbuy':($aGoods['act_type']=='score'?'scorebuy':$aGoods['act_type']);
            if(!$app_list || !array_key_exists($app_id, $app_list)){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
                exit;
            }
            $objact = @app::get($app_id)->model($app_list[$app_id]['m1']);
            $objapply = @app::get($app_id)->model($app_list[$app_id]['m2']);
            if(!$objact || !$objapply){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
                exit;
            }
            $aGoods['act_detail'] = array();
            
            //$filter = array('act_id'=>$aApply['aid']);
            $filter = array('act_open'=>'true','start_time|sthan'=>time(),'end_time|than'=>time(),'act_open'=>'true');
            if($app_id=='timedbuy'){
                $filter['active_status|noequal'] = 'end';
            }else{
                $filter['act_status|noequal'] = '2';
            }
            $aAct = array();
            foreach($objact->getList('*',$filter) as $item){
                cellphone_misc_exec::get_change($item);
                $aAct[$item['act_id']] = $item;
            }
            $aApply = array();
            foreach($objapply->getList('*',array('gid'=>$gid,'status'=>'2')) as $item){
                cellphone_misc_exec::get_change($item);
                if($item['aid'] && array_key_exists($item['aid'],$aAct)){
                    $aApply = $item;
                    continue;
                }
            }
            if(!isset($aApply['aid']) || empty($aApply['aid'])){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
                exit;
            }
            $aGoods['act_detail']['price'] = isset($aApply['last_price'])?$aApply['last_price']:$aApply['price'];
            $aGoods['act_detail']['limit'] = $aApply['personlimit'];
            $aGoods['act_detail']['stime'] = $aAct[$aApply['aid']]['start_time'];
            $aGoods['act_detail']['etime'] = $aAct[$aApply['aid']]['end_time'];
            if(count($aGoods['product'])){
                foreach($aGoods['product'] as $pdk=>$pdv){
                    //处理货品库存与商品真实总库 取小的值
                    $aGoods['product'][$pdk]['store'] = min($pdv['store'],$aApply['remainnums']);
                }
            }
            $aGoods['current_price'] = $aGoods['act_detail']['price'];
            $aGoods['store'] = min($aApply['remainnums'],($aGoods['store']-$aGoods['freez']));
            if($app_id == 'scorebuy'){
                if($siteMember['member_lv']>0 && $aApply['isMemLv'] == '1'){
                    $memLvScoreObj = app::get('scorebuy')->model('memberlvscore');
                    $memlvs = $memLvScoreObj->getMemLvScoreByIds($aApply['aid'],$gid);
                    $aGoods['act_detail']['price'] = $memlvs[$siteMember['member_lv']]['last_price'];
                    $aGoods['act_detail']['score'] = $memlvs[$siteMember['member_lv']]['score'];
                }else{
                    $aGoods['act_detail']['price'] = $aApply['last_price'];
                    $aGoods['act_detail']['score'] = $aApply['score'];
                }
            }
        }
        $aGoods['dlytype_info'] = empty($aGoods['dlytype_info']['area'])?null:$aGoods['dlytype_info'];
        
        if($siteMember['member_id']){
            @app :: get('b2c')->model('goods_view_history')->add_history($siteMember['member_id'],$gid);
        }
        
        $this->send(true, $aGoods, app::get('b2c')->_('success'));
    }
    
    // 获取多商品信息
    public function activity_detail($params){
        $app_id = $params['object_type'];
        $app_list = cellphone_misc_exec::get_actapp();
        if(!$app_list || !array_key_exists($app_id, $app_list)){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
            exit;
        }
        $objact = @app::get($app_id)->model($app_list[$app_id]['m1']);
        $objapply = @app::get($app_id)->model($app_list[$app_id]['m2']);
        if(!$objact || !$objapply){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
            exit;
        }
        $gid = intval($params['goods_id']);
        if(!$gid){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
            exit;
        }
        $aApply = $objapply->getRow('*',array($objapply->idColumn=>$gid),0,-1);
        cellphone_misc_exec::get_change($aApply);
        $aApply['aid'] = $aApply['aid']?intval($aApply['aid']):-1;
        $aAct = $objact->getRow('*',array($objact->idColumn=>$aApply['aid']));
        cellphone_misc_exec::get_change($aAct);
        if(empty($aAct['act_id']) || empty($aApply['gid'])){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
            exit;
        }
        $goods_id = array_filter(explode(',',$aApply['gid']));
        if(empty($goods_id)){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
            exit;
        }
        $picSize = in_array(strtolower($params['pic_size']), array('cl', 'cs'))?strtolower($params['pic_size']):'cl';
        $siteMember = $this->get_current_member();
        $siteMember['member_lv'] = isset($siteMember['member_lv'])?$siteMember['member_lv']:-1;
        $objGoods = app::get('b2c')->model('goods');
        $aGoods['store_id'] = $aApply['store_id']?intval($aApply['store_id']):0;
        $aGoods['act_type'] = $app_id=='groupbuy'?'group':($app_id=='scorebuy'?'score':$app_id);
        $aGoods['goods_id'] = $aApply['act_id'];
        $aGoods['name'] = $aApply['name'];
        $aGoods['freight_bear'] = $aApply['freight_bear'];
        $aGoods['store'] = $aApply['store'];
        $aGoods['freez'] = $aApply['freez'];
        $aGoods['gain_score'] = $aApply['score'];
        $aGoods['images'][] = $this->get_img_url($aApply['image'],$picSize);
        
        $cols = "goods_id,name,price,mktprice,buy_m_count,fav_count,comments_count,avg_point,weight,spec_desc,udfimg,thumbnail_pic,image_default_id";
        $filter = array('goods_id'=>$goods_id,'store_id'=>$aGoods['store_id'],'act_type'=>$aGoods['act_type'],'marketable'=>'true','disabled'=>'false');
        $aGoods_count = $objGoods->count($filter);
        if(count($goods_id) !== $aGoods_count){
            $this->send(false, null, app::get('b2c')->_('无效商品！商品参数错误'));
            exit;
        }
        $aGoods['price'] = 0;
        $aGoods['mktprice'] = 0;
        $aGoods['weight'] = 0;
        $aGoods_list = array();
        $spec_list = array();
        $objProduct = &app::get('b2c')->model('products');
        $dlytype_info = array();
        foreach((array)$objGoods->getList($cols,$filter,0,-1) as $row){
            if( $row['mktprice'] == '' || $row['mktprice'] == null )
                $row['mktprice'] = $objProduct->getRealMkt($row['price']);
            else
                $row['mktprice'] = $row['mktprice'];
            $aGoods['price'] += $row['price'];
            $aGoods['mktprice'] += $row['mktprice'];
            $aGoods['weight'] += $row['weight'];
           
            $aGoods_list[$row['goods_id']] = array(
                'goods_id' => $row['goods_id'],
                'name' => $row['name'],
                'buy_m_count' => $row['buy_m_count'],
                'fav_count' => $row['fav_count'],
                'comments_count' => $row['comments_count'],
                'avg_point' => $row['avg_point'],
                'image' => $row['udfimg']?$this->get_img_url($row['thumbnail_pic'],$picSize):$this->get_img_url($row['image_default_id'],$picSize),
            );
            if($siteMember['member_id']){
                $temp = app::get('b2c')->model('member_goods')->getRow('goods_id',array('goods_id'=>$row['goods_id'],'member_id'=>$siteMember['member_id'],'status'=>'ready','disabled'=>'false','type'=>'fav','object_type'=>'goods'));
                $aGoods_list[$row['goods_id']]['is_fav'] = $temp?1:0;
            }else{
                $aGoods_list[$row['goods_id']]['is_fav'] = 0;
            }
            //$row['spec_desc'] = unserialize($row['spec_desc']);
            if($row['spec_desc'] && is_array($row['spec_desc'])){
                foreach($row['spec_desc'] as $specId => $spec){
                    $spec_list[$row['goods_id']][$specId] = $spec;
                }
            }
            if(empty($spec_list[$row['goods_id']])){
                $aGoods['spec'][$row['goods_id']] = array(
                    'goods_id' => $row['goods_id'],
                    'items' => null,
                );
            }
            if($aGoods['freight_bear'] == 'member'){
                $dlytype_info[] = $this->freight_detail($row['goods_id'], $aGoods['store_id'], $row['weight']);
            }
        }
        $sql = "SELECT p.goods_id,p.product_id,p.price,p.mktprice,p.store,p.freez,p.spec_desc,l.level_id,l.price as lv_price
FROM  `sdb_b2c_products` AS p
LEFT JOIN  `sdb_b2c_goods_lv_price` AS l ON p.product_id = l.product_id
WHERE p.goods_id in (".implode(',',$goods_id).")";
        $productAmp = array();
        foreach((array)$objProduct->db->select($sql) as $pro_v){
            $productAmp[$pro_v['goods_id']][$pro_v['product_id']]['product_id'] = $pro_v['product_id'];
            $temp = unserialize($pro_v['spec_desc']);
            $productAmp[$pro_v['goods_id']][$pro_v['product_id']]['spec_desc'] = array();
            foreach($temp['spec_private_value_id'] as $key => $value){
                $productAmp[$pro_v['goods_id']][$pro_v['product_id']]['spec_desc'][] = array(
                    'spec_id'=>$key,
                    'private_spec_value_id'=>$value,
                );
            }
            //$productAmp[$pro_v['goods_id']][$pro_v['product_id']]['spec_desc'] = unserialize($pro_v['spec_desc']);
            $productAmp[$pro_v['goods_id']][$pro_v['product_id']]['price'] = $pro_v['price'];
            $productAmp[$pro_v['goods_id']][$pro_v['product_id']]['mktprice'] = $pro_v['mktprice'];
            $productAmp[$pro_v['goods_id']][$pro_v['product_id']]['store'] = $pro_v['store'];
            $productAmp[$pro_v['goods_id']][$pro_v['product_id']]['freez'] = $pro_v['freez'];
            $aGoods_list[$pro_v['goods_id']]['product'] = array_values($productAmp[$pro_v['goods_id']]);
        }
        if(count($aGoods_list) !== count($productAmp)){
            $this->send(false, null, app::get('b2c')->_('无效商品！商品参数错误'));
            exit;
        }
        $aGoods['items'] = array_values($aGoods_list);
        $objSpec = &app::get('b2c')->model('specification');
        foreach((array)$spec_list as $key => $specinfo){
            $aGoods['spec'][$key]['goods_id'] = $key;
            foreach((array)$specinfo as $specId => $spec){
                $aRow = $objSpec->getList("*",array('spec_id'=>$specId));
                $aGoods['spec'][$key]['items'][$specId] = $aRow[0];
                foreach( $spec as $pSpecId => $specValue ){
                    $specValue['spec_image'] = $this->get_img_url($specValue['spec_image'],$picSize);
                    $aGoods['spec'][$key]['items'][$specId]['option'][$pSpecId] = array_merge( array('private_spec_value_id'=>$pSpecId), $specValue );
                }
                $aGoods['spec'][$key]['items'][$specId]['option'] = array_values($aGoods['spec'][$key]['items'][$specId]['option']);
            }
            $aGoods['spec'][$key]['items'] = array_values($aGoods['spec'][$key]['items']);
        }
        $aGoods['spec'] = array_values($aGoods['spec']);
        $aGoods['dlytype_info'] = array();
        foreach((array)$dlytype_info as $key => $value){
            if(!$value['area'] || empty($value['dlytype'])){
                unset($aGoods['dlytype_info']);
                break;
            }
            if(empty($aGoods['dlytype_info'])){
                $aGoods['dlytype_info'] = $value;
                continue;
            }
            if($aGoods['dlytype_info']['area'] != $value['area']){
                unset($aGoods['dlytype_info']);
                break;
            }
            $temp = array();
            foreach((array)$aGoods['dlytype_info']['dlytype'] as $item){
                $temp[$item['name']] = $item['money'];
            }
            if(empty($temp)){
                unset($aGoods['dlytype_info']);
                break;
            }
            foreach((array)$aGoods['dlytype_info']['dlytype'] as $ckey => $cvalue){
                if(!array_key_exists($cvalue['name'],$temp)){
                    unset($aGoods['dlytype_info']['dlytype'][$ckey]);
                }else{
                    $aGoods['dlytype_info']['dlytype'][$ckey]['money'] += floatval($cvalue['money']);
                }
            }
        }
        if(!isset($aGoods['dlytype_info']) || empty($aGoods['dlytype_info']['dlytype'])){
            $aGoods['dlytype_info'] = array(
                'area' => null,
                'localname' => null,
                'parent' => null,
                'dlytype' => null,
            );
        }

        $aInfo = app::get('business')->model('storemanger')->getList('area,account_id',array('store_id'=>$aGoods['store_id']));
        if($aInfo[0]['area'])
        list($a,$area_from,$b) = explode(':', $aInfo[0]['area']);
        else $area_from ='';
        if($area_from) $area_from = explode('/', $area_from);
        if(is_array($area_from)) $area_from = (($area_from[0] == '北京' || $area_from[0] == '天津' || $area_from[0] == '上海' || $area_from[0] == '重庆')?'':$area_from[0]).$area_from[1];
        else $area_from = '';
        $aGoods['area_from'] = $area_from;
        
        if(!$siteMember['member_id']){
            $aGoods['login_status'] = 'nologin';
        }else{
            $obj_store = app::get('business')->model('storemanger');
            $obj_smb = app::get('business')->model('storemember');
            $is_business = $obj_store->count(array('account_id'=>$siteMember['member_id']));
            if(!$is_business) $is_business = $obj_smb->count(array('member_id'=>$siteMember['member_id']));
            if($is_business > 0) $aGoods['login_status'] = 'business';
            else $aGoods['login_status'] = 'member';
        }

        $cur = app::get('ectools')->model('currency');
        $cur_info = $cur->getFormat();
        if($cur_info['cur_sign']) {
            $cur_info['sign'] = $cur_info['cur_sign'];
        }
        $ret =array(
            'decimals'=>app::get('b2c')->getConf('system.money.decimals'),
            'dec_point'=>app::get('b2c')->getConf('system.money.dec_point'),
            'thousands_sep'=>app::get('b2c')->getConf('system.money.thousands_sep'),
            'fonttend_decimal_type'=>app::get('b2c')->getConf('system.money.operation.carryset'),
            'fonttend_decimal_remain'=>app::get('b2c')->getConf('system.money.decimals'),
            'sign' => $cur_info['sign']
        );
        if(isset($cur_info['cur_default']) && $cur_info['cur_default'] === "false") {
            $ret['cur_rate'] = $cur_info['cur_rate'];
        }
        $aGoods['money_format'] = $ret;
        $objComment = app::get('business')->model('comment_stores_point');
        $store_info = $objComment->getStoreInfo($aGoods['store_id']);
        foreach((array)$store_info['store_point'] as $row){
            $aGoods['store_info'][] = array(
                'name' => $row['name'],
                'avg_point' => $row['avg_point'],
                'avg_percent' => $row['avg_percent'],
            );
        }

        $aGoods['act_detail'] = array();
        $aGoods['act_detail']['price'] = isset($aApply['last_price'])?$aApply['last_price']:$aApply['price'];
        $aGoods['act_detail']['limit'] = $aApply['personlimit'];
        $aGoods['act_detail']['stime'] = $aAct['start_time'];
        $aGoods['act_detail']['etime'] = $aAct['end_time'];
        $aGoods['current_price'] = $aGoods['act_detail']['price'];

        if($app_id == 'scorebuy'){
            if($siteMember['member_lv']>0 && $aApply['isMemLv'] == '1'){
                $memLvScoreObj = app::get('scorebuy')->model('memberlvscore');
                $memlvs = $memLvScoreObj->getMemLvScoreByIds($aApply['aid'],$gid);
                $aGoods['act_detail']['price'] = $memlvs[$siteMember['member_lv']]['last_price'];
                $aGoods['act_detail']['score'] = $memlvs[$siteMember['member_lv']]['score'];
            }else{
                $aGoods['act_detail']['price'] = $aApply['last_price'];
                $aGoods['act_detail']['score'] = $aApply['score'];
            }
        }
        $aGoods['dlytype_info'] = empty($aGoods['dlytype_info']['area'])?null:$aGoods['dlytype_info'];
        
        if($siteMember['member_id']){
            foreach((array)$$goods_id as $gid){
                @app :: get('b2c')->model('goods_view_history')->add_history($siteMember['member_id'],$gid);
            }
        }
        
        $this->send(true, $aGoods, app::get('b2c')->_('success'));
    }
    
    // 获取商品扩展属性
    public function get_extparams(){
        $params = $this->params;
        $must_params = array(
            'goods_id'=>'商品标识',
        );
        $this->check_params($must_params);
        $objGoods = &app::get('b2c')->model('goods');
        $gid = intval($params['goods_id']);
        if(!$gid){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
            exit;
        }else{
            $rs = $objGoods->dump(array('goods_id'=>$gid),'goods_id');
            if(!$rs || empty($rs)){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
                exit;
            }
        }
        $aGoods = array();
        $cols = "bn,brief,brand_id,type_id,cat_id,p_1,p_2,p_3,p_4,p_5,p_6,p_7,p_8,p_9,p_10,p_11,p_12,p_13,p_14,p_15,p_16,p_17,p_18,p_19,p_20,p_21,p_22,p_23,p_24,p_25,p_26,p_27,p_28,p_29,p_30,p_31,p_32,p_33,p_34,p_35,p_36,p_37,p_38,p_39,p_40,p_41,p_42,p_43,p_44,p_45,p_46,p_47,p_48,p_49,p_50";
        $aGoods_list = $objGoods->getList($cols ,array('goods_id'=>$gid, 'store_id|than'=>0, 'disabled'=>'false'));
        $objBrand = &app::get('b2c')->model('brand');
        $aGoods['brand'] = $objBrand->getRow('brand_id,brand_name',array('brand_id'=>$aGoods_list[0]['brand_id']));
        if(empty($aGoods['brand']))$aGoods['brand']=null;
        $aGoods['bn'] = $aGoods_list[0]['bn'];
        $objCat = &app::get('b2c')->model('goods_cat');
        $aGoods['category'] = $objCat->getRow('cat_id,cat_name',array('cat_id'=>$aGoods_list[0]['cat_id']));
        $aGoods['brief'] = $aGoods_list[0]['brief'];
        $goods_type = app::get("b2c")->model("goods_type");
        cachemgr::co_start();
        if(!cachemgr::get("goods_type_props_value_list2dump".$aGoods_list[0]['type_id'], $goods_type_data)){
            $goods_type_data = $goods_type->dump($aGoods_list[0]['type_id']);
            cachemgr::set("goods_type_props_value_list2dump".$aGoods_list[0]['type_id'], $goods_type_data, cachemgr::co_end());
        }
        $aGoods['ext_attr'] = array();
        if($goods_type_data && $goods_type_data['setting']['use_props']){
            $props = array();
            foreach ($aGoods_list[0] as $aGoods_k => $aGoods_v) {
                if(strpos($aGoods_k,"p_")===0)$props[$aGoods_k] = $aGoods_v;
            }
            foreach((array)$goods_type_data['props'] as $key => $row){
                $aGoods['ext_attr'][] = array(
                    'name' => $row['name'],
                    'value' => (isset($props['p_'.$key])&&isset($row['options'][$props['p_'.$key]]))?$row['options'][$props['p_'.$key]]:'',
                );
            }
        }
        $this->send(true, $aGoods, app::get('b2c')->_('success'));
    }
    
    // 获取商品详细介绍
    public function get_description(){
        $params = $this->params;
        $must_params = array(
            'goods_id'=>'商品标识',
        );
        $this->check_params($must_params);
        
        $params['object_type'] = $params['object_type']?$params['object_type']:'normal';
        if($params['object_type'] == 'normal'){
            $objGoods = &app::get('b2c')->model('goods');
            $gid = intval($params['goods_id']);
            if(!$gid){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
                exit;
            }else{
                $rs = $objGoods->dump(array('goods_id'=>$gid),'goods_id');
                if(!$rs || empty($rs)){
                    $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
                    exit;
                }
            }
            $aData = $objGoods->getRow('intro' ,array('goods_id'=>$gid, 'store_id|than'=>0, 'disabled'=>'false'));
        }else{
            $app_id = $params['object_type'];
            $app_list = cellphone_misc_exec::get_actapp();
            if(!$app_list || !array_key_exists($app_id, $app_list)){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
                exit;
            }
            $objact = @app::get($app_id)->model($app_list[$app_id]['m1']);
            $objapply = @app::get($app_id)->model($app_list[$app_id]['m2']);
            if(!$objact || !$objapply){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
                exit;
            }
            $gid = intval($params['goods_id']);
            if(!$gid){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
                exit;
            }
            $aApply = $objapply->getRow('*',array($objapply->idColumn=>$gid),0,-1);
            cellphone_misc_exec::get_change($aApply);
            $aApply['aid'] = $aApply['aid']?intval($aApply['aid']):-1;
            $aAct = $objact->getRow('*',array($objact->idColumn=>$aApply['aid']));
            cellphone_misc_exec::get_change($aAct);
            if(empty($aAct['act_id']) || empty($aApply['gid'])){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是活动已过期'));
                exit;
            }
            $aData = $aApply;
            $this->activity_detail($params);
        }
        $aData['intro'] = str_replace('href=','shref=',$aData['intro']);
        $this->send(true, $aData['intro'], app::get('b2c')->_('success'));
    }
    
    // 获取地区信息
    public function get_regions(){
        $params = $this->params;
        $objRegions = app::get('ectools')->model('regions');
        $aData = array();
        foreach((array)$objRegions->getList('region_id,local_name', array('region_grade'=>1,'disabled'=>'false'), 0, -1) as $key => $row){
            $aData[$row['region_id']]['local_name'] = $row['local_name'];
        }
        $region_info = array();
        if(isset($params['area_id']) && !empty($params['area_id'])){
            $region_special = array();
            foreach((array)$objRegions->getList('region_id,local_name,p_region_id', array('p_region_id'=>array(1,21,42,62),'disabled'=>'false')) as $row){
                $region_special[$row['p_region_id']] = $row['region_id'];
            }
            $area_id = intval($params['area_id']);
            if(isset($region_special[$area_id])) $area_id = $region_special[$area_id]['region_id'];
            $region_id = $objRegions->getList('region_path', array('region_id'=>$area_id,'disabled'=>'false'));
            $region_id = explode(',', $region_id[0]['region_path']);
            $region_id = array_filter($region_id);
            if(isset($region_id[0]) && ($region_id[0] == 1 || $region_id[0] == 21 || $region_id[0] == 42 || $region_id[0] == 62)){
                array_shift($region_id);
            }
            reset($region_id);
            if(!empty($region_id)){
                $region_data = $objRegions->getList('region_id,p_region_id,local_name', array('p_region_id'=>$region_id,'disabled'=>'false'), 0, -1,'region_grade asc,region_id asc');
                $this->region_structure($region_data,$region_info);
                foreach((array)$aData as $key => $row){
                    if(array_key_exists($key, $region_info)){
                        $aData[$key]['item'] = $region_info[$key];
                        break;
                    }
                }
            }
        }
        $this->send(true, $aData, app::get('b2c')->_('success'));
    }
    
    private function region_structure($source, &$data){
        foreach((array)$source as $row){
            if(empty($data) || array_key_exists($row['p_region_id'],$data)){
                $data[$row['p_region_id']]['items'][$row['region_id']] = array('local_name'=>$row['local_name']);
                array_shift($source);
                $this->region_structure($source, $data[$row['p_region_id']]['items']);
            }else{
                return;
            }
        }
    }
    
    // 获取运费模板
    public function get_freight(){
        $params = $this->params;
        $must_params = array(
            'goods_id'=>'商品标识',
            'store_id'=>'店铺标识',
            'area_id'=>'地区标识',
        );
        $this->check_params($must_params);
        $params['weight'] = isset($params['weight'])?floatval($params['weight']):0;
        $aData = $this->freight_detail($params['goods_id'],$params['store_id'],$params['weight'],$params['area_id']);
        $this->send(true,$aData,'sucess');
    }
    
    private function freight_detail($goods_id, $store_id, $weight, $area_id=0){
        $objGoods = app::get('b2c')->model('goods');
        $aData = array(
            'goods_id' => intval($goods_id),
            'store_id' => intval($store_id),
            'weight' => floatval($weight),
        );
        $aDt = $objGoods->getDlytype($aData, $area_id);
        $aReturn = array();
        $aReturn['area'] = $aDt['area'];
        $aReturn['localname'] = $aDt['localname'];
        $aReturn['parent'] = $aDt['parent'];
        foreach((array)$aDt['dlytype'] as $row){
            $aReturn['dlytype'][] = array(
                'name' => $row['dt_name'],
                'money' => $row['money'],
            );
        }
        return $aReturn;
    }
    
    // 获取单件商品评论信息
    public function get_discuss(){
        $params = $this->params;
        $must_params = array(
            'goods_id'=>'商品标识',
        );
        $this->check_params($must_params);
        $aData = $this->discuss_detail($params);
        $this->send(true,$aData,'sucess');
    }
    
    private function discuss_detail($params = array()){
        if(!$params['goods_id']) return array();
        $limit = $params['pageLimit']?intval($params['pageLimit']):10;
        $offset = $params['nPage']?(intval($params['nPage'])-1)*$limit:0;
        $aData = array();
        $siteMember = $this->get_current_member();
        if(!$siteMember['member_id']){
            $aData['login_status'] = 'nologin';
        }
        $objComment = app::get('b2c')->model('member_comments');
        $filter = array('type_id'=>$params['goods_id'],'object_type'=>'discuss','for_comment_id'=>0,'display'=>'true','comments_type'=>'1');
        $aData['total'] = $objComment->count($filter);
        $sql = "  select c.comment_id,c.author,c.time,c.comment,c.addon,c.author_id,m.name as author_alias 
from sdb_b2c_member_comments as c 
left join sdb_b2c_members as m on c.author_id=m.member_id 
left join sdb_b2c_order_items as i on c.order_id=i.order_id and c.type_id=i.goods_id 
where c.object_type='discuss' and c.for_comment_id=0 and c.type_id=".intval($params['goods_id'])." and c.display='true' and c.comments_type='1' 
group by  comment_id,i.goods_id order by time desc limit {$offset},{$limit}";
        $aData['data'] = array();
        $aId = array();
        foreach((array)$objComment->db->select($sql) as $val){
            $val['addon'] = unserialize($val['addon']);
            if(!empty($val['author_alias'])) $val['author'] = $val['author_alias'];
            if(isset($val['addon']['hidden_name']) && $val['addon']['hidden_name'] == 'YES' && ($val['author_id'] !=0 || $val['author_id'] !=1)){
                $val['author'] = mb_substr($val['author'], 0, 1, 'UTF-8').'****'.mb_substr($val['author'], mb_strlen($string, 'UTF-8')-1, 1, 'UTF-8');
            }
            $aId[] = $val['comment_id'];
            unset($val['author_alias'],$val['author_id'],$val['addon']);
            $aData['data'][] = $val;
            
        }
        if(!empty($aId)){
            $addition = array();
            $temp = $objComment->getList('comment_id,for_comment_id',array('for_comment_id' => $aId,'comments_type'=>'3','display' => 'true'));
            foreach((array)$temp as $rows){
                $aId[] = $rows['comment_id'];
                $addition[$rows['for_comment_id']][] = $rows['comment_id'];
            }
            $aReply = (array)kernel::single('business_message_disask')->getCommentsReply($aId, true);
            foreach((array)$aData['data'] as $key => $rows){
                foreach($aReply as $rkey => $rrows){
                    $condition1 = $rows['comment_id'] == $rrows['for_comment_id'];
                    $condition2 = !empty($addition) && isset($addition[$rows['comment_id']]) && in_array($rrows['for_comment_id'],$addition[$rows['comment_id']]);
                    if($condition1 || $condition2){
                        $aData['data'][$key]['items'][] = array(
                            'author' => ($aReply[$rkey]['comments_type']==0&&$aReply[$rkey]['author']!='管理员')?'掌柜':$aReply[$rkey]['author'],
                            'time' => $aReply[$rkey]['time'],
                            'comment' => $aReply[$rkey]['comment'],
                        );
                    }
                }
                unset($aData['data'][$key]['comment_id']);
            }
        }
        return $aData;
    }
    
    // 到货通知功能
    public function toNotify(){
        $params = $this->params;
        $must_params = array(
            'goods_id'=>'商品标识',
            'product_id'=>'货品标识',
        );
        $this->check_params($must_params);

        $siteMember = $this->get_current_member();
        $member_id = $member['member_id']?$member['member_id']:null;
        
        
        if (empty($params['email']) && empty($params['cellphone'])) {
            $this->send(false,null,app::get('b2c')->_('邮箱或手机号请至少填一项'));
            exit;
        }
        if(!empty($params['email']) && !preg_match('/\S+@\S+/',$params['email'])){
            $this->send(false,null,app::get('b2c')->_('邮箱格式错误'));
            exit;
        }
        if(!empty($params['cellphone']) && !preg_match('/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/',$params['cellphone'])){
            $this->send(false,null,app::get('b2c')->_('手机格式错误'));
            exit;
        }
        $objGoods = &app::get('b2c')->Model('goods');
        $objProducts = &app::get('b2c')->Model('products');
        $ret = $objProducts->getList('product_id',array('product_id' => $params['product_id'],'goods_id' => $params['goods_id']));
        if(!$ret){
            $this->send(false,null,app::get('b2c')->_('参数错误,无此货品'));exit;
        }
        $back_url = app::get('site')->base_url(1);
        $member_goods = app::get('b2c')->model('member_goods');
        
        $data['item'][0]['goods_id'] = $params['goods_id'];
        $data['item'][0]['product_id'] = $params['product_id'];
        $data['email'] = $params['email'] ? $params['email'] : null;
        $data['cellphone'] = $params['cellphone'] ? $params['cellphone'] : null;
        if($member_goods->check_gnotify($data)){
            $this->send(false,null,app::get('b2c')->_('不能重复登记'));exit;
        }else{
            if($member_goods->add_gnotify($member_id,$params['goods_id'],$params['product_id'],$params['email'])){
                $objGoods->db->exec("update sdb_b2c_goods set notify_num=notify_num+1 where goods_id = ".intval($params['goods_id']));
                $this->send(true,null,app::get('b2c')->_('登记成功'));exit;
            }else{
                $this->send(false,null,app::get('b2c')->_('登记失败'));exit;
            }
        }
    }
}