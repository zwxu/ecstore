<?php

 
class ectools_ctl_setting extends desktop_controller{

    var $require_super_op = true;

    function __construct($app){
        parent::__construct($app);
        $this->ui = new base_component_ui($this);
        $this->app = $app;
		header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->basic();
    }

    public function basic(){
        $all_settings = array(

             app::get('ectools')->_('价格精度设置')=>array(

                'site.decimal_digit.count',
                'site.decimal_type.count',
                //'site.decimal_digit.display',
                //'site.decimal_type.display',
            ),
        );
        //echo '<h5 class="head-title">系统设置</h5>';
        $this->pagedata['_PAGE_CONTENT'] = $this->_process($all_settings);
        $this->page();        
    }

    function _process($all_settings){
        $setting = new base_setting($this->app);
        $setlib = $setting->source();
        $typemap = array(
            SET_T_STR=>'text',
            SET_T_INT=>'number',
            SET_T_ENUM=>'select',
            SET_T_BOOL=>'bool',
            SET_T_TXT=>'text',
            SET_T_FILE=>'file',
            SET_T_IMAGE=>'image',
            SET_T_DIGITS=>'number',
        );

        $tabs = array_keys($all_settings);
        $html = $this->ui->form_start(array('tabs'=>$tabs, 'method'=>'POST'));
        foreach($tabs as $tab=>$tab_name){
            foreach($all_settings[$tab_name] as $set){
                $current_set = $this->app->getConf($set);
                if($_POST['set'] && array_key_exists($set,$_POST['set'])){
                    if($current_set!=$_POST['set'][$set]){
                        $current_set = $_POST['set'][$set];
                        $this->app->setConf($set,$_POST['set'][$set]);
                    }
                }
                
                $input_type = $typemap[$setlib[$set]['type']];
                
                $form_input = array(
                    'title'=>$setlib[$set]['desc'],
                    'type'=>$input_type,
                    'name'=>"set[".$set."]",
                    'tab'=>$tab,
                    'value'=>$current_set,
                    'options'=>$setlib[$set]['options'],
                );
                
                if($input_type=='image'){
                    
                   $form_input = array_merge($form_input,array(
                   
                      'width'=>$setlib[$set]['width'],
                      'height'=>$setlib[$set]['height']
                   
                   ));
                
                }

                $html.=$this->ui->form_input($form_input);
            }
        }
        return $html .= $this->ui->form_end(1, app::get('ectools')->_('保存设置'));
    }

    function licence(){
        $this->sidePanel();
        echo '<iframe width="100%" height="100%" src="'.constant('URL_VIEW_LICENCE').'" ></iframe>';
    }
}

