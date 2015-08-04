<?php

 
class b2c_view_input{

    function input_category($params){
        $render = new base_render(app::get('b2c'));
        $mdl = app::get('b2c')->model('goods_cat');
        $render->pagedata['category'] = array();
        $render->pagedata['params'] = $params;
        if($params['value']){
            $row = $mdl->getList('*',array('cat_id'=>$params['value']));
            $render->pagedata['category'] = $row[0];
        }
        return $render->fetch('admin/goods/category/input_category.html');
    }
    
    
    function input_goodsfilter($params){

        $render = new base_render(app::get('b2c'));

        $obj_type = app::get('b2c')->model('goods_type');

        $input_name = $params['name'];

        parse_str($params['value'],$value);

        $params =array(
                'gtype'=>$obj_type->getList('*',null,0,-1),
                'view' => 'admin/goods/finder_filter.html',
                'params' => $params['params'],
                'json' => json_encode($data),
                'data' => $value,
                'from'=>$params['value'],
                'domid' => substr(md5(rand(0,time())),0,6),
                'name' =>$input_name
        );
        $type_id = '_ANY_';
        $params['value'] = $value;
        if($params['value']['type_id']) $type_id = $params['value']['type_id'];

        $render->pagedata['params'] = $params;

        $goods_filter = kernel::single('b2c_goods_goodsfilter');
        $return = $goods_filter->goods_goodsfilter($type_id,app::get('b2c'));
        $render->pagedata['filter'] = $return;
        $render->pagedata['type_id'] = $type_id;
        $render->pagedata['filter_items'] = array();
        foreach(kernel::servicelist('goods_filter_item') as $key=>$object){
            
            if(is_object($object)&&method_exists($object,'get_item_html')){
                $render->pagedata['filter_items'][] = $object->get_item_html();
            }
        }

        return $render->fetch('admin/goods/goods_filter.html');
    }

    public function input_corp_code($params)
    {
        $return = array();
        if (!file_exists($this->app->app_dir.'/dlycorp.txt'))
            return '';
            
        //使用文件方式 读取一定格式文件
        foreach (file($this->app->app_dir.'/dlycorp.txt') as $row)
        {
            list($key,$value,$name) = explode("\t",trim($row));
            $return[$key] = $value;
        }
        
        $checked = false;
        $str_return = '<select id="'.$params['id'].'" name="'.$params['name'].'" title="'.$params['title'].'" vtype="'.$params['vtype'].'" class="'.$params['class'].'">';
        foreach ($return as $key=>$str)
        {
            if ($params['value']==$key)
                $ckecked = true;
            $str_return .= '<option value="'.$key.'"' . (($params['value']==$key) ? ' selected="selected"' : '') . '>'.$str.'</option>';
        }
        $str_return .= '</select>&nbsp;<input type="radio" id="b2c-admin-corp-other-code" value="1" name="corp_code_other"' . ((!$ckecked && $params['value']) ? ' checked="checked"' : '' ) . ' /><input type="text" name="corp_code_copy" value="' . ((!$ckecked && $params['value']) ? $params['value'] : '' ) . '"' . ((!$ckecked && $params['value']) ? ' vtype="'.$params['vtype'].'"' : '' ) . ' class="x-input " autocomplete="off" />';
        
        return $str_return;
    }

    function input_goods_select($params){
        $return_url = $params['return_url']?$params['return_url']:'index.php?app=desktop&ctl=editor&act=object_rows'; 
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
        $render = new base_render(app::get('b2c'));
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
        unset($params['app']);

        if($params['data']){
            $_params = (array)$params['data'];
            unset($params['data']);
            $params = array_merge($params,$_params);
        }
        
        if(is_array($params['items'])){
            foreach($params['items'] as $key=>$item){
                $items[$key] = $item[$params['idcol']];
            }
        }
        $params['return_url'] = urlencode($params['return_url']);
        $vars = $params;
        $vars['items'] = $items;
        
        $object = utils::http_build_query($vars);

        if(isset($params['goods_filter'])&& $params['goods_filter']){
            $obj_filter = http_build_query($params['goods_filter']);
            $obj_filter = explode('&',$obj_filter);
            $obj_filter = implode('|',$obj_filter);
            $params['goods_filter'] = $obj_filter;
            $render->pagedata['goods_filter'] = true;
        }

        $url = 'index.php?app=b2c&ctl=admin_goods&act=finder_goods_select';

        $render->pagedata['string'] = $string;
        $render->pagedata['url'] = $url;
        $render->pagedata['return_url'] = $return_url;
        $render->pagedata['id'] = $id;
        $render->pagedata['params'] = $params;
        $render->pagedata['object'] = $object;
        $render->pagedata['callback'] = $callback;
        $render->pagedata['init'] = $init;
        $render->pagedata['value'] = $params['value'];
        // 添加类目过滤
        if(isset($params['cat_filter']['cat_id'])){
            $render->pagedata['cat_id'] = $params['cat_filter']['cat_id'];
        }
       
        /** 得到商品的数量 **/
        if ($params['value']){
            $arr_values = json_decode($params['value']);
            $render->pagedata['goods_cnt'] = count($arr_values);
        }
        return $render->fetch('admin/goods/input_radio.html');
        
    }
}
