<?php

 

class site_ctl_sitemaps extends site_controller{
    
    
    
    
    public function index() {
    	$this->_response->set_header('Cache-Control', 'no-store');
        $_index = $this->_request->get_param(0);
        $o_sitemaps = base_kvstore::instance('site_sitemaps');
        $o_sitemaps->fetch( $_index, $arr );
        if( empty( $arr ) ) {
            $this->_response->set_http_response_code(404);
        }else{
            $this->pagedata['sitemaps'] = $arr;
            $this->pagedata['base_url'] = $this->app->res_url;
            $this->_response->set_header('Content-type', ' application/xml');
            $this->page('sitemaps/index.xml', true);
        }
    }

    function catalog(){
    
        $o_sitemaps = base_kvstore::instance('site_sitemaps');
        $o_sitemaps->fetch( 'count', $count_sitemaps );
        $catalog = array();
        
        for( $i=1; $i<=$count_sitemaps; $i++ ) {
            $url = $this->gen_url( array('app'=>'site', 'ctl'=>'sitemaps', 'act'=>'index', 'arg0'=>$i, 'full'=>true ) );
            $catalog[]['url'] = $url;
        }
        $this->pagedata['base_url'] = $this->app->res_url;
        $this->pagedata['catalog'] = $catalog;
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->_response->set_header('Content-type', ' application/xml');
        $this->page('sitemaps/catalog.xml', true);
    }

}
