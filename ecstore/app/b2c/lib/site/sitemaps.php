<?php

class b2c_site_sitemaps {
    
    public function __construct( $app ) {
        $this->app = $app;
    }
    
    /*
     *
     * return array
     * array(
     *  array(
     *      'url' => '...........'
     *      ),
     *  array(
     *      'url' => '...........'
     *      )
     * )
     */
    
    public function get_arr_maps() {
        $this->router = app::get('site')->router();
        $tmp = array();
        
        
        
        //货品
        $this->_get( 'goods','site_product','index','goods_id',$tmp );
        
        //品牌
        $this->_get( 'brand','site_brand','index','brand_id',$tmp );
        
        //分类
        $this->_get( 'goods_cat','site_gallery','index','cat_id',$tmp );
        return $tmp;
    }
    
    private function _get( $model,$ctl,$act,$index,&$tmp ) {
        $offset = 0;
        $limit  = 100;
        while(true) {
            $arr = $this->app->model($model)->getList( '*', array(), $offset, $limit );
            foreach( (array)$arr as $row ) {
                $tmp[] = array(
                        'url' => $this->router->gen_url(array('app'=>'b2c', 'ctl'=>$ctl, 'act'=>$act, 'arg0'=>$row[$index], 'full'=>true) ),
                    );
            }
            $offset += $limit;
            if( $limit>count($arr) ) break;
        }
    }
    
}