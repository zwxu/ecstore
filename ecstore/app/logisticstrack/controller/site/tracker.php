<?php


class logisticstrack_ctl_site_tracker extends site_controller{

    public function __construct($app) {
        parent::__construct($app);
    }
    
    public function pull($deliveryid) {
        if ( !$deliveryid ) {
            $this->pagedata['logi_error'] = app::get('logisticstrack')->_('缺少发货单号');
            return false;
        }
        header("cache-control: no-store, no-cache, must-revalidate");
        header('Content-Type: text/html; charset=UTF-8');
        if ( logisticstrack_puller::pull_logi($deliveryid, $data) ) {
            $this->pagedata['logi'] = $data['data'];
            $this->pagedata['logi_source'] = $data['source'];
        } else {
            $this->pagedata['logi_error'] = $data['msg'];
        }
        $this->display('site/logistic_detail.html');
    }
}