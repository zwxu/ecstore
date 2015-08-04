<?php

class goodsapi_shopex_member_level_price_list extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->mebmer_price_model = app::get('b2c')->model('goods_lv_price');
    }

    //批量获取会员等级价格列表接口
    function shopex_member_level_price_list(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        /** 获取条件 **/
        $filter = array();
        if(isset($params['bns']) && !empty($params['bns'])){
            foreach(explode(',',$params['bns']) as $goods_bn){
                $goods_id = $this->getGoodsIdByGoodsBn($goods_bn);
                $goods_row[$goods_id] = $goods_bn;
                $filter['goods_id|in'][] = $goods_id ;
            }
        }
        /** end **/

        $params['page_no'] = $params['page_no'] ? $params['page_no'] : '1';
        $params['page_size'] = $params['page_size'] ? $params['page_size'] : '100';
        $page_no = intval($params['page_no']) - 1;
        $page_size = intval($params['page_size']);
        $page_offset = $page_no * $page_size;

        //获取会员等级价格列表
        $goods_lv_price = $this->mebmer_price_model->getList('*',$filter,$page_offset,$page_size);
        $member_level_prices= array();
        if( $goods_lv_price ){
            foreach($goods_lv_price as $level_k=>$level_v){
                //商品编号
                if( $goods_row ){
                    $bn = $goods_row[$level_v['goods_id']];
                }else{
                    $obj_goods = app::get('b2c')->model('goods')->dump(array('goods_id'=>$level_v['goods_id']),'bn');
                    $bn = $obj_goods['bn'];
                }

                //会员名称
                $member_lv_name = app::get('b2c')->model('member_lv')->dump(array('member_lv_id'=>$level_v['level_id']),'name');
                //货品编号
                $member_lv_product = app::get('b2c')->model('products')->dump(array('product_id'=>$level_v['product_id']),'bn');
                $member_level_prices[$level_k] = array(
                    'member_lv_name' =>$member_lv_name['name'],
                    'price' => intval($level_v['price']),
                    'bn'    => $bn,
                    'bn_code' =>$member_lv_product['bn'],
                    'last_modify' =>time()
                );
            }
        }
        $data['item_total'] = count($member_level_prices);
        $data = $member_level_prices;
        $this->send_success($data);
    }//end api

}

