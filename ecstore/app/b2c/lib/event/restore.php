<?php


class b2c_event_restore{

    function __construct($app){
        $this->app = $app;
    }

    function restoreEvent(){
        base_kvstore::instance('b2c_goods')->store('goods_cat.data',false);
        base_kvstore::instance('b2c_goods')->store('goods_virtualcat.data',false);
        base_kvstore::instance('b2c_goods')->store('goods_virtualcat.all.data',false);

        $objB2c = app::get('b2c');

        $objvircat = $objB2c->model('goods_virtual_cat');
        $vircat_id = $objvircat->getList('virtual_cat_id',array());
        if(is_array($vircat_id)){
            foreach($vircat_id as $vk=>$val){
                $url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_gallery','args'=>array(null,null,0,'','',$val['virtual_cat_id']) ) );
              // $objvircat->update(array('url'=>$url),array('virtual_cat_id'=>$val['virtual_cat_id']));
                $objvircat->db->exec('UPDATE sdb_b2c_goods_virtual_cat SET url = \''.$url.'\' WHERE virtual_cat_id = '.$val['virtual_cat_id'] );
            }
        }
    }


}
