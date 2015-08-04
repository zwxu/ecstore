<?php

class business_ctl_site_shopinfo extends b2c_frontpage {

    //var $noCache = true;
    function __construct($app) {
        //$this->set_no_cache();
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }



}

