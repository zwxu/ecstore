<?php
 

class  b2c_ctl_admin_analysis extends desktop_controller
{

    public function index(){
        $html = kernel::single('b2c_analysis_index')->set_service('b2c_analysis_shopsale')->set_extra_view(array('ectools'=>'analysis/index_view.html'))->set_params($_POST)->fetch();
        $this->pagedata['_PAGE_CONTENT'] = $html;
        $this->page();
    }

    public function sale(){
        kernel::single('b2c_analysis_sale')->set_params($_POST)->display();
    }

    public function advance(){
        kernel::single('b2c_analysis_advance')->set_params($_POST)->display();
    }

    public function shopsale(){
        kernel::single('b2c_analysis_shopsale')->set_params($_POST)->display();
        //kernel::single('b2c_analysis_shopsale')->set_extra_view(array('ectools'=>'analysis/shopsale.html'))->set_params($_POST)->display();
    }

    public function productsale(){
        kernel::single('b2c_analysis_productsale')->set_params($_POST)->display();
    }

    public function member(){
        kernel::single('b2c_analysis_member')->set_params($_POST)->display();
    }

}