<?php
/**
 * @author chris.zhang
 *
 */
class b2c_widgets_goods extends b2c_widgets_public {
    protected $_filter = array(
        'goodsId'   => 'goods_id',      //商品ID
        'priceFrom' => 'pricefrom',     //价格区间（低）
        'priceTo'   => 'priceto',       //价格区间（高）
        'typeId'    => 'type_id',       //商品类型ID
        'catId'     => 'cat_id',        //商品分类ID
        'brandId'   => 'brand_id',     //商品品牌ID
    );
    //商品返回数据格式
    protected $_outData = array(
        'goodsId'           => 'goods_id',      //商品ID
        'goodsName'         => 'name',          //商品名称
        'goodsCategory'     => 'cat_name',      //商品分类
        'goodsPicL'         => 'l_pic',         //商品大图
        'goodsPicM'         => 'm_pic',         //商品中图
        'goodsPicS'         => 's_pic',         //商品小图
        'goodsMarketPrice'  => 'mktprice',      //商品市场价
        'goodsSalePrice'    => 'price',         //商品销售价
        'goodsMemberPrice'  => 'memprice',      //商品会员价
        'goodsDiscount'     => 'dis_count',     //商品折扣
        'goodsIntro'        => 'brief',         //商品简介
        'goodsLink'         => '_link_',        //商品链接
    );

    //商品排序条件返回数据格式
    protected $_orderByData = array(
        'label'     => 'label',     //排序显示名称
        'condition' => 'sql',       //排序的条件
    );

    protected $default_cols = 'goods_id,name,price,mktprice,cost,bn,marketable,marketable_allow,type_id,cat_id,brand_id,p_order,d_order,score,score_setting,image_default_id,udfimg,rank_count,comments_count,view_count,view_w_count,view_m_count,buy_count,buy_w_count,buy_m_count,store_id,fav_count';

    /**
     * 根据条件获取商品信息
     * @param array $filter （所有条件都可选）
     *  array(
            'goodsId' => array(1,2)/1,  //商品ID
            'priceFrom' => 5,           //价格区间（低）
            'priceTo' => 100,           //价格区间（高）
            'typeId' => 2,              //商品类型ID
            'catId' => array(1,2)/1,    //商品分类ID
            'brandId' => array(1,2)/1,  //商品品牌ID
            'goodsOrderBy' => 1,        //商品排序
            'goodsNum' => 12,           //总商品数（最多20）
        ),
     */
    public function getGoodsList($filter, $field){
        if ( isset( $_COOKIE['MLV'] ) ) {
            $member_level = $_COOKIE['MLV'];
            (array)$arr = $this->app->model('member_lv')->getList('dis_count', array('member_lv_id' => $member_level));
            $dis_count = $arr[0]['dis_count'];
        }

        $objLvprice     = &$this->app->model('goods_lv_price');
        $goods          = &$this->app->model('goods');
        $objProduct     = &$this->app->model('products');
        $config         = $this->app->getConf('site.save_price');
        //$imageDefault   = app::get('image')->getConf('image.set');
        $search         = &$this->app->model('search');


        $orderBy    = $filter['goodsOrderBy'] ? $filter['goodsOrderBy'] : 0;
        $order      = $filter['goodsOrderBy'] ? $this->_getOrderBy($filter['goodsOrderBy'],false) : null;
        $limit      = (intval($filter['goodsNum'])>0) ? intval($filter['goodsNum']) : 6;
        //$limit      = ($limit > 20) ? 20 : $limit;
        $result     = array();

        unset($filter['goodsOrderBy']);
        unset($filter['goodsNum']);

        unset($filter['goodsOrderBy']);
        unset($filter['goodsNum']);

        $_filter = $this->_getFilter($filter);

        $result['goodsMoreLink']= &kernel::router()->gen_url(
            array(
                'app' => 'b2c',
                'ctl'=>'site_gallery',
                'act'=>'index',
                'args' => array(implode(",",(array)$filter['catId']),$search->encode($filter),($orderBy))
            )
        );

        if(empty($field)){
            $field = $this->default_cols ? $this->default_cols : '*';
        }

        $goodsList = $goods->getList($field,$_filter,0,$limit,$order['sql']);
        if(!empty($_filter['goods_id'])){
            $goods_temp = array();
            foreach($_filter['goods_id'] as $k=>$v){
                foreach($goodsList as $row){
                    if($v == $row['goods_id']){
                        $goods_temp[$k] = $row;
                    }
                }
            }
            unset($goodsList);
            $goodsList = $goods_temp;
            unset($goods_temp);
        }
        if( is_array( $goodsList ) && $goodsList)
        foreach($goodsList as $pk => $pv){
            if(empty($pv['mktprice']) ||$pv['mktprice'] == '0'){
                $pv['mktprice'] = $objProduct->getRealMkt($pv['price']);
            }
            $cat = $goods->db->selectrow("SELECT cat_name FROM sdb_b2c_goods_cat WHERE cat_id = ".intval($pv['cat_id']));
            $pv['cat_name'] = $cat['cat_name'];
            // add for member price
            $lv_price = $objLvprice->getList('price',array('goods_id'=>$pv['goods_id'],'level_id'=>$member_level));
            if ( isset( $dis_count ) ) {
                if(count($lv_price) > 0){
                    $lv_price = end($lv_price);
                    $pv['memprice'] = $lv_price['price'];
                }else{
                    $pv['memprice'] = $pv['price'] * $dis_count;
                }
                if(intval($pv['price']) != 0){
                    $pv['dis_count'] = (1 - $pv['memprice'] / $pv['price']) * 100;
                }else{
                    $pv['dis_count'] = 0;
                }
                
            }else{
                $pv['dis_count'] = 0;
                $pv['memprice'] = false;
            }
            $imageUrl = $this->get_image_url($pv['image_default_id']);
            $pv['l_pic'] = $imageUrl['url_lager'];
            $pv['m_pic'] = $imageUrl['url_middle'];
            $pv['s_pic'] = $imageUrl['url_small'];
            $pv['_link_'] = $this->getGoodsLink($pv['goods_id']);
            $result['goodsRows'][$pv['goods_id']] = $this->_getOutData($pv);
        }

        return $result;
    }

    /**
     *获取某商品链接
     *@param int $goodsId
     */
    public function getGoodsLink($goodsId){
        return $this->get_link(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','args'=>array($goodsId)));
    }

    /**
     * 获取商品的排序条件
     * @param int $id（可选）
     */
    public function getGoodsOrderBy($id=null){
        return $this->_getOrderBy($id);
    }

    protected function _getFilter($filter){
        foreach ($this->_filter as $_k => $_v){
            if (isset($filter[$_k])) $filter[$_v] = $filter[$_k];
            unset($filter[$_k]);
        }

        $filter = array_merge(array('marketable'=>"true",'disabled'=>"false",'goods_type'=>"normal"),$filter);
        if($GLOBALS['runtime']['member_lv']){
            $filter['mlevel'] = $GLOBALS['runtime']['member_lv'];
        }

        if($filter['props']){
            foreach($filter['props'] as $k=>$v){
                if($v!='_ANY_'){
                    $filter['p_'.$k]=$v;
                }
            }
        }

        $filter['price'][0]=$filter['pricefrom'];
        $filter['price'][1]=$filter['priceto'];
        $filter['name'][0]=$filter['searchname'];

        return $filter;

    }

    private function _getOrderBy($id=null,$output=true){
        $o      = &$this->app->model('goods');
        $output = $this->get_bool($output);
        $data   = $o->orderBy( $id );
        $_data  = array();

        if (!$data) return array();
        if ($output==true){
            if ($id){
                $data['sql'] = $id;
                $_data = $this->_getOutData($data, $this->_orderByData);
            }else {
                foreach ($data as $key => $row){
                    $row['sql'] = $key;
                    $_data[] = $this->_getOutData($row, $this->_orderByData);
                }
            }
            return $_data;
        }
        return $data;
    }

}
