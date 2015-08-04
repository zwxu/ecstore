<?php

 

class b2c_mdl_goods_lv_price extends dbeav_model{

    function dump($filter,$field = '*',$subSdf = null){
        $rs = parent::dump($filter,$field,$subSdf);
        $oMlv = &$this->app->model('member_lv');
        $memLv = $oMlv->dump( $filter['level_id'] );
        $price =  $this->db->selectrow('SELECT price FROM sdb_b2c_products WHERE product_id = '.intval($filter['product_id']));
       $price = $price['price'];
        if($rs){
            $rs['title'] = $memLv['name'];
            $rs['custom'] = 'true';
        }else{
            $rs = array(
                'level_id' => $filter['level_id'],
                'price' => ($memLv['dis_count']>0?$memLv['dis_count'] * $price:$price),
                'title' => $memLv['name'],
                'custom' => 'false'
            );
        }
        return $rs;
    }

    function save(&$data,$mustUpdate = null){
        if( $data['custom'] == 'false' )
            return true;
        parent::save($data,$mustUpdate);
    }
    
}
