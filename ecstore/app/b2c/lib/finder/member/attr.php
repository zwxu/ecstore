<?php

 
class b2c_finder_member_attr{    
    function __construct(&$app){
        $this->app=$app;
        $this->ui = new base_component_ui($this);
    }    
    var $detail_basic = '会员注册项';
    function detail_basic($attr_id){
        $attr_model = $this->app->model('member_attr');
        if($_POST){
            $_POST['attr_id'] = $attr_id;
            $attr_model->save($_POST);
        }
        $data = $attr_model->dump($attr_id); 
        $html .= $this->ui->form_start();
        foreach($data as $k=>$val){
            $col = $attr_model->schema['columns'][$k];
            if($col['type'] == 'bool'){
                $input['type'] = 'bool';
        }
        $input['value'] = $val;
        $input['name'] = $k;
        $input['title'] =$col ['label'];
        $html .= $this->ui->form_input($input);
        unset($input);
        }
        $html .= $this->ui->form_end();
        return $html;
    }  
}
