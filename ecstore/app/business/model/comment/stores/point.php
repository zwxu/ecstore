<?php
class business_mdl_comment_stores_point extends dbeav_model{
    function __construct(&$app){
        parent::__construct($app);
        $this->use_meta();
    }
    
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
    
    function exec_auto(){
        $sql = " select o.store_id,o.type_id,sum(o.point+0)/count(o.order_id) as avg_point,ifnull(s.store_region,c.region) as store_region ".
            " from sdb_business_comment_orders_point as o ".
            " inner join (select a.order_id from sdb_b2c_orders a where createtime>".strtotime("-6 month")." and ifnull(a.member_id,0)>0 and ifnull(a.store_id,0)>0 and a.status='finish' and 3 > (select count(*) from sdb_b2c_orders where member_id=a.member_id and store_id=a.store_id and MONTH(from_unixtime(createtime)) = MONTH(from_unixtime(a.createtime)) and createtime<a.createtime ) order by a.order_id) as b on o.order_id=b.order_id ".
            " left join sdb_business_storemanger as s on o.store_id=s.store_id and s.store_region like ',%,' ".
            " left join (select concat(',',group_concat(convert(cat_id,char) separator  ','),',') as region from sdb_b2c_goods_cat where parent_id = 0 and disabled='false') as c on 1=1 ".
            " where o.disabled='false'group by o.store_id,o.type_id ";
        $exec_data = $this->db->select($sql);
        $data = array();
        foreach($exec_data as $key => $value){
            $temp = explode(',', $value['store_region']);
            if(count($temp)) $temp = array_filter($temp);
            $min = 5.0;
            foreach($temp as $rows){
                $point = floatval($data[$rows][$value['type_id']]['point'])+floatval($value['avg_point']);
                $num = floatval($data[$rows][$value['type_id']]['num'])+1.0;
                $avg = floatval($point / $num);
                if(!floatval($data[$rows][$value['type_id']]['min'])){
                    $min = min($min, floatval($value['avg_point']));
                }else{
                    $min = min(floatval($data[$rows][$value['type_id']]['min']), floatval($value['avg_point']));
                }
                $data[$rows][$value['type_id']] = array('point'=>$point,'num'=>$num,'avg'=>$avg,'min'=>$min);
            }
        }
        foreach($exec_data as $key => $value){
            $temp = explode(',', $value['store_region']);
            if(count($temp)) $temp = array_filter($temp);
            $count = count($temp);
            $avg_point = 0.0;
            $min_point = 0.0;
            foreach($temp as $rows){
                $avg_point += floatval($data[$rows][$value['type_id']]['avg']);
                $min_point += floatval($data[$rows][$value['type_id']]['min']);
            }
            if($count){
                $avg_point = floatval($avg_point / $count);
                $min_point = floatval($min_point / $count);
            }
            if(!$avg_point || !$min_point){
                $exec_data[$key]['avg_percent'] = 0;
            }else{
                $numerator = $avg_point - floatval($value['avg_point']);
                $denominator = $avg_point - $min_point;
                if(!$denominator){
                    $exec_data[$key]['avg_percent'] = 0;
                }else{
                    $exec_data[$key]['avg_percent'] = floor(($numerator/$denominator)*10000)/10000*100;
                }
            }
        }
        
        $sql = " select store_id,type_id from sdb_business_comment_stores_point ";
        $hava_data = array();
        foreach($this->db->select($sql) as $rows){
            $hava_data[$rows['store_id']][] = $rows['type_id'];
        }
        
        $insert_data = array();
        $time = time();
        foreach($exec_data as $rows){
            if(array_key_exists($rows['store_id'], $hava_data) && in_array($rows['type_id'],$hava_data[$rows['store_id']])){
                $sql = ' update sdb_business_comment_stores_point '.
                    ' set avg_point='.floatval($rows['avg_point']).',store_region=\''.$rows['store_region'].'\',avg_percent='.floatval($rows['avg_percent']).',last_modify=\''.$time.'\''.
                    ' where store_id='.intval($rows['store_id']).' and type_id='.intval($rows['type_id']);
                $this->db->exec($sql);
            }else{
                $insert_data[] = '('.intval($rows['store_id']).','.intval($rows['type_id']).','.floatval($rows['avg_point']).',\''.$rows['store_region'].'\''.','.floatval($rows['avg_percent']).',\''.$time.'\')';
            }
        }
        if(count($insert_data)){
            $this->db->exec('insert into sdb_business_comment_stores_point (store_id,type_id,avg_point,store_region,avg_percent,last_modify) values '.implode(',',$insert_data));
        }
    }
    
    function exec_point(){
        $day_1 = app::get('b2c')->getConf('site.comment_original_time');
        $day_1 = intval($day_1)?intval($day_1):30;
        $sql  = " select o.store_id,o.member_id,5 as point,t.type_id,o.order_id 
                  from sdb_b2c_orders as o 
                  left join sdb_b2c_comment_goods_type as t on 1=1 
                  left join sdb_business_comment_orders_point as p on p.order_id = o.order_id and p.type_id = t.type_id 
                  where (o.comments_count+0)=0 and o.member_id>0 and o.status='finish' 
                  and DATE_ADD(from_unixtime(o.createtime), INTERVAL {$day_1} DAY) < now() and p.type_id is null ";
        $exec_data = $this->db->select($sql);
        $insert_data = array();
        foreach($exec_data as $rows){
            $insert_data[] = '('.intval($rows['store_id']).','.intval($rows['member_id']).','.intval($rows['point']).','.intval($rows['type_id']).',\''.$rows['order_id'].'\',\'false\')';
        }
        if(count($insert_data)){
            $this->db->exec('insert into sdb_business_comment_orders_point (store_id,member_id,point,type_id,order_id,disabled) values '.implode(',',$insert_data));
        }
    }
}