<?php


 class spike_view_input{

     function input_goods($params){
        $return_url = $params['return_url']?$params['return_url']:'shopamdin?app=desktop&ctl=editor&act=object_rows';
        $callback = $params['callback']?$params['callback']:'';
        $init = $params['data']['init']?$params['data']['init']:'';
        $params['breakpoint'] = isset($params['breakpoint'])?$params['breakpoint']:20;

        $object = $params['object'];
        if(strpos($params['object'],'@')!==false){
            list($object,$app_id) = explode('@',$params['object']);
            $params['object'] = $object;
        }elseif($params['app']){
            $app_id = $params['app'];
        }else{
            $app_id = $this->app->app_id;
        }

        $app = app::get($app_id);        
        $o = $app->model($object);
        $render = new base_render(app::get('spike'));
        $ui = new base_component_ui($app);


        $dbschema = $o->get_schema();

        $params['app_id'] = $app_id;

        if(isset($params['filter'])){
            if(!is_array($params['filter'])){
                parse_str($params['filter'],$params['filter']);
            }
        }

        $params['domid'] = substr(md5(uniqid()),0,6);

        $key = $params['key']?$params['key']:$dbschema['idColumn'];
        $textcol = $params['textcol']?$params['textcol']:$dbschema['textColumn'];
        
        
        //显示列 可以多列显示 不完全修改 。。。。。。。 
        $textcol = explode(',',$textcol);
        $_textcol = $textcol;
        $textcol = $textcol[0];


        $tmp_filter = $params['filter']?$params['filter']:null;
        $count = $o->count($tmp_filter);
        if($count<=$params['breakpoint']&&!$params['multiple']&&$params['select']!='checkbox'){
            if(strpos($textcol,'@')===false){
                $list = $o->getList($key.','.$textcol,$tmp_filter);
                if(!$list[0]) $type=array();
                foreach($list as $row){
                    $label = $row[$textcol];
                    if(!$label&&method_exists($o,'title_modifier')){
                        $label = $o->title_modifier($row[$key]);
                    }
                    $type[$row[$key]] = $label;
                }
                
            }else{
                list($name,$table,$app_id) = explode('@',$textcol);
                $app = $app_id?app::get($app_id):$app;
                $mdl = $app->model($table);
                $list = $o->getList($key,$tmp_filter);
                foreach($list as $row){
                    $tmp_row = $mdl->getList($name,array($mdl->idColumn=>$row[$key]),0,1);
                    $label = $tmp_row[0][$name];
                    if(!$label&&method_exists($o,'title_modifier')){
                        $label = $o->title_modifier($row[$key]);
                    }
                    $type[$row[$key]] = $label;
                }

            }
            $tmp_params['name'] = $params['name'];
            $tmp_params['value'] = $params['value'];
            $tmp_params['type'] = $type;
            if($callback)
                $tmp_params['onchange'] = $callback.'(this)';
            $str_filter = $ui->input($tmp_params);
            unset($tmp_params);
            return $str_filter;

        }

        $params['idcol'] = $keycol['keycol'] = $key;
        $params['textcol'] = implode(',',$_textcol);
        
        $params['_textcol'] = $_textcol;
        if($params['value']){
            if(strpos($params['view'],':')!==false){
                list($view_app,$view) = explode(':',$params['view']);
                $params['view_app'] = $view_app;
                $params['view'] = $view;
            }
            if(is_string($params['value'])){
                $params['value'] = explode(',',$params['value']);
            }
            $params['items'] = &$o->getList('*',array($key=>$params['value']),0,-1);
            
            //过滤不存在的值
            //某些数据被添加后 可能原表数据已删除，但此处value中还存在。
            $_params_items_row_key = array();
            foreach( $params['items'] as $_params_items_row ) {
                $_params_items_row_key[] = $_params_items_row[$key];
            }
            $params['value'] = implode(',',$_params_items_row_key);
        }
        if($params['value'] && $params['select'] != 'checkbox'){
            $string = $params['items'][0][$textcol];
        }else{
            $string = $params['emptytext']?$params['emptytext']:app::get('desktop')->_('请选择...');
        }
        
        $str_app = $params['app'];
        unset($params['app']);

        if($params['data']){
            $_params = (array)$params['data'];
            unset($params['data']);
            $params = array_merge($params,$_params);
        }

        if($params['select']=='checkbox'){
            if($params['default_id'] ) $params['domid'] = $params['default_id'];
            $params['type'] = 'checkbox';
        }else{
            $id = "handle_".$params['domid'];
            $params['type'] = 'radio';
            $getdata = '&singleselect=radio';
        }
        if(is_array($params['items'])){
            foreach($params['items'] as $key=>$item){
                $items[$key] = $item[$params['idcol']];
            }
        }
        $params['return_url'] = urlencode($return_url);
        $vars = $params;
        $vars['items'] = $items;
        
        $object = utils::http_build_query($vars);
        
        $url = 'shopadmin?app=desktop&act=alertpages&goto='.urlencode('index.php?app=desktop&ctl=editor&act=finder_common&sign=site&app_id='.$app_id.'&'.$object.$getdata).'&sign=site';
        
        $render->pagedata['string'] = $string;
        $render->pagedata['url'] = $url;
        $render->pagedata['app'] = 'app='.$str_app;
        $render->pagedata['return_url'] = $return_url;
        $render->pagedata['id'] = $id;
        $render->pagedata['params'] = $params;
        $render->pagedata['object'] = $object;
        $render->pagedata['callback'] = $callback;
        $render->pagedata['init'] = $init;
        return $render->fetch('finder/input_radio.html');
     }
 }