<?php

 
class desktop_application_menu extends base_application_prototype_xml {

    var $xml='desktop.xml';
    var $xsd='desktop_content';
    var $path = 'workground';

    function current(){
        $this->current = $this->iterator()->current();
        $this->current['action'] = $this->current['action']?$this->current['action']:'index';
        $this->key = $this->target_app->app_id.'_ctl_'.$this->current['controller'];
        return $this;
    }

    function row($fag,$key){
        if($this->current['menugroup'][$fag]['menu'][$key]['params']){
            $a_tmp = explode('|', $this->current['menugroup'][$fag]['menu'][$key]['params']);
            foreach((array)$a_tmp as $k => $v){
                $a = explode(':',$v);
                 if(strpos($a[0],'p[')!==false){
                    $a = explode(':',$v);
                    eval('$url_params'.str_replace('p','[p]',$a[0]).' = $a[1];');
                 }else{
                    $url_params[$a[0]] = $a[1];
                 }
            }
            $addon['url_params'] = $url_params;
            unset($url_params);
        }
        $row = array(
                    'menu_type' => $this->content_typename(),
                    'app_id'=>$this->target_app->app_id,
                    'workground' => $this->current['id'],
                    'content_name' => $this->key(),
                    'menu_group' => $this->current['menugroup'][$fag]['name'],
                    'menu_order' => $this->current['menugroup'][$fag]['menu'][$key]['order'],
                    'addon' => serialize($addon),
                    'target'=>$this->current['menugroup'][$fag]['menu'][$key]['target']?$this->current['menugroup'][$fag]['menu'][$key]['target']:'',
                );
        $app_id = $this->current['menugroup'][$fag]['menu'][$key]['app']?$this->current['menugroup'][$fag]['menu'][$key]['app']:$this->target_app->app_id;
        $this->current['menugroup'][$fag]['menu'][$key]['action'] = $this->current['menugroup'][$fag]['menu'][$key]['action']?$this->current['menugroup'][$fag]['menu'][$key]['action']:'index';
        $row['menu_path'] = "app={$app_id}&ctl={$this->current['menugroup'][$fag]['menu'][$key]['controller']}&act={$this->current['menugroup'][$fag]['menu'][$key]['action']}";
        $row['menu_title'] =  $this->current['menugroup'][$fag]['menu'][$key]['value'];
        $row['permission'] = $this->current['menugroup'][$fag]['menu'][$key]['permission'];
        $row['display'] = $this->current['menugroup'][$fag]['menu'][$key]['display']?$this->current['menugroup'][$fag]['menu'][$key]['display']:"true";
        return $row;
    }
    function menu_row($fag){
        if($this->current['menu'][$fag]['params']){
            $a_tmp = explode('|', $this->current['menu'][$fag]['params']);
            foreach((array)$a_tmp as $k => $v){
                $a = explode(':',$v);
                $url_params[$a[0]] = $a[1];
            }
            $addon['url_params'] = $url_params;
            unset($url_params);
        }
        $row = array(
                    'menu_type' => $this->content_typename(),
                    'app_id'=>$this->target_app->app_id,
                    'workground' => $this->current['id'],
                    'content_name' => $this->key(),
                    'menu_group' => '',
                    'menu_order' => $this->current['menu'][$fag]['order'],
                    'addon' => serialize($addon),
                    'target'=>$this->current['menu'][$fag]['target']?$this->current['menu'][$fag]['target']:'',
                );
               # $row = parent::row();
        $app_id = $this->current['menu'][$fag]['app']?$this->current['menu'][$fag]['app']:$this->target_app->app_id;
        $this->current['menu'][$fag]['action'] = $this->current['menu'][$fag]['action']?$this->current['menu'][$fag]['action']:'index';
        $row['menu_path'] = "app={$app_id}&ctl={$this->current['menu'][$fag]['controller']}&act={$this->current['menu'][$fag]['action']}";
        $row['menu_title'] =  $this->current['menu'][$fag]['value'];
        $row['permission'] = $this->current['menu'][$fag]['permission'];
        $row['display'] = $this->current['menu'][$fag]['display'];
        return $row;
    }
 
    function install(){
         foreach($this->current['menugroup'] as $fag=>$val){
                 foreach($val['menu'] as $key=>$data){
                     #print_r($data);exit;
                     kernel::log('Installing '.$this->content_typename().' '.$this->key());
                    app::get('desktop')->model('menus')->insert($this->row($fag,$key));
                 }
             }
             if($this->current['menu']){
              foreach($this->current['menu'] as $fag=>$val){

                         kernel::log('Installing '.$this->content_typename().' '.$this->key());
                        app::get('desktop')->model('menus')->insert($this->menu_row($fag));
                     
                 }
             }
                         
    }
    
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('desktop')->model('menus')->delete(array(
            'app_id'=>$app_id,'menu_type' => $this->content_typename()));
    }

}
