<?php

 
class rpc extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testRequest(){
        
        //插入一条服务器消息
//        $server = array(
//                'node_id'=>'5',
//                'node_url'=>kernel::base_url(),
//                'node_name'=>'localhost',
//                'node_api'=>'index.php/api',
//                'sitekey'=>md5(123456),
//            );
//        app::get('base')->model('network')->replace($server,array('node_id'=>5));


        $params = array(
            'fields'=>'brand_id,brand_name',
            'shopurl'=>'http://shop1.test.shopex.cn:20210/api.php',
        );

        $rst = app::get('base')->matrix()->set_callback('dev_sandbox','show',array(1,2,3,'aa'=>time()))
            ->set_timeout(10)
            ->call('store.shopbrands.list.get',$params);
    }
}
