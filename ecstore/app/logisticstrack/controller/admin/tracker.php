<?php


class logisticstrack_ctl_admin_tracker extends desktop_controller{

    public function __construct($app) {
        parent::__construct($app);
    }
    
    public function pull($deliveryid) {
        header("cache-control: no-store, no-cache, must-revalidate");
        header('Expires: Fri, 16 Dec 2000 10:38:27 GMT');
        header('Content-Type: text/html; charset=UTF-8');
        
        if ( logisticstrack_puller::pull_logi($deliveryid, $data) ) {
            $this->pagedata['logi'] = $data['data'];
            $this->pagedata['logi_source'] = $data['source'];
        } else {
            $this->pagedata['logi_error'] = $data['msg'];
        }
        $this->display('admin/logistic_detail.html');
    }
}