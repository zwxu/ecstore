<?php
class business_mdl_dlytype extends b2c_mdl_dlytype{
    function __construct($app) {
        parent::__construct(app::get('b2c'));
        $this->use_meta();
    }
}