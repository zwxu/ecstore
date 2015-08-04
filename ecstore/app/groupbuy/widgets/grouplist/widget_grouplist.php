<?php
function widget_grouplist(&$setting,&$smarty){
    $goods_ids = explode(',',$setting['linkid']);
    $info = b2c_widgets::load('Goods')->getGoodsList(array('goodsId'=>$goods_ids));
    $goods_info = json_decode($setting['goods'],true);
    $cat_arr = array();
    foreach($goods_info as $k=>$v){
        $sql = "select gd.name as g_name,c.cat_name,a.start_time,a.end_time,g.id,g.gid,g.cat_id,g.aid,g.last_price,g.nums,g.remainnums,g.personlimit,gd.price as g_price  from sdb_groupbuy_groupapply as g".
        " join sdb_b2c_goods_cat as c on g.cat_id=c.cat_id and g.status='2'".
        " join sdb_groupbuy_activity as a on g.aid=a.act_id".
        " join sdb_b2c_goods as gd on g.gid=gd.goods_id".
        " where g.gid={$v['id']} and g.status='2' and a.act_open='true'";
        $rs = app::get('groupbuy')->model('activity')->db->select($sql);
        if($rs && !empty($rs)){
            $result[$k] = $rs[0];
            $result[$k]['title'] = $v['nice'];
            $result[$k]['pic'] = $v['pic'];
            $result[$k]['discount'] = round($result[$k]['last_price']/$result[$k]['g_price']*10,2);
            $result[$k]['image'] = $info['goodsRows'][$rs[0]['gid']]['goodsPicM'];
            $result[$k]['args'] = array($rs[0]['gid'],'','',$rs[0]['id']);
            $cat_arr[$rs[0]['cat_id']][] = array('name'=>$rs[0]['cat_name'],'gid'=>$rs[0]['gid']);
        }
    }
    $cat = array();
    $allCatNum = 0;
    foreach($cat_arr as $k=>$v){
        $cat[$k]['num'] = count($v);
        $cat[$k]['name'] = $v[0]['name'];
        $allCatNum += $cat[$k]['num'];
    }
    $data = array();
    $data['activity'] = $result;
    $data['nowtime'] = time();
    $data['cat'] = $cat;
    $data['allCatNum'] = $allCatNum;

    return $data;
}
?>
