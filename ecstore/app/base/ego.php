<?php
/*** desktop finder ***/


/**
 * 
 * finder id
 * 
 * @param string $find_id
 * @return string
 */
function ecos_desktop_finder_find_id($find_id = '')
{
	if($find_id){
		return $find_id;
	}else{
		return substr(md5($_SERVER['QUERY_STRING']),5,6);
	}
}

/**
 * 
 * 获取请求参数
 * @param array $params
 * @return array
 */
function ecos_desktop_finder_get_args($params = array())
{
	$extends = array();
	foreach( $params as $key => $val ) {
		if( $key!='app' && $key!='act' && $key!='ctl' && $key!='_finder' )
			$extends[$key] = $val;
		if( $key=='_finder' ) break;
	}
	return $extends;
}

/**
 * 
 * 生成GET
 * 
 * @param string $find_id
 * @return array
 */
function ecos_desktop_finder_make_get($find_id = '')
{
	$_GET['ctl'] = $_GET['ctl']?$_GET['ctl']:'default';
    $_GET['act'] = $_GET['act']?$_GET['act']:'index';
    $_GET['_finder']['finder_id'] = $find_id;
    if($_GET['action'])unset($_GET['action']);
    return $_GET;
}

/**
 * 
 * 按model全名获取app名及model名
 * @param sting $model_name
 * @return array array(app_name , model_name);
 */
function ecos_desktop_finder_split_model($model_name)
{
	$return = array();
	if($p=strpos($model_name,'_mdl_')){
		$return[] = substr($model_name,0,$p);
		$return[] = substr($model_name,$p+5);
	}else{
		trigger_error('finder only accept full model name: '.$full_object_name, E_USER_ERROR);
	}
	return $return;
}

/**
 * 
 * 获取列
 * @param string $cols
 * @param array $func_columns
 * @param array $default_in_list
 * @param array $all_cols
 * @return array 
 */
function ecos_desktop_finder_get_columns($cols , $func_columns , $default_in_list , $all_cols)
{
	if($cols){
		return explode(',',$cols);
	}else{
		if($func_columns){
			foreach($func_columns as $key=>$func_column){
				$col_keys[] = $key;
			}
		}
		$columns = array_merge((array)$col_keys,(array)$default_in_list);
		foreach($all_cols as $key=>$value){
			if(in_array($key,$columns)){
				$return[] = $key;
			}
		}
		return $return;
	}
}
/**
 * 
 * 获取所有column
 * @param array $in_list
 * @param array $func_columns
 * @param array $dbschema_columns
 * @return array
 */
function ecos_desktop_finder_all_columns($in_list , $func_columns , $dbschema_columns)
{
	$columns = array();
	foreach((array)$in_list as $key){
		$columns[$key] = &$dbschema_columns[$key];
	}
	$return = array_merge((array)$func_columns,(array)$columns);
	foreach($return as $k=>$r){
		if(!$r['order']){
			$return[$k]['order'] = 100;
			
		}
		$orders[] = $return[$k]['order'];
	}
	array_multisort($orders,SORT_ASC,$return);
	return $return;
}

function ecos_desktop_finder_builder_prototype_get_view_modifier($views, $finder_aliasname, &$views_temp=array())
{
	foreach((array)$views as $k=>$view){
		if(!isset($view['finder'])){
			$views_temp[$k] = $view;
		}elseif(isset($view['finder'])){
			if(is_array($view['finder'])){
				if(in_array($finder_aliasname,$view['finder'])){
					$views_temp[$k] = $view;
				}
			}elseif($finder_aliasname==$view['finder']){
				$views_temp[$k] = $view;
			}
			
		}
	}
}

/**
 * 获取finder gen_url的array
 * @param array 扩展参数数组
 * @param array url的控制器数组
 * @return null
 */
function ecos_desktop_finder_builder_prototype_get_view_url_array($extends, &$_url_array=array())
{
	if( $extends && is_array($extends) ) {
		foreach( $extends as $_key => $_val ) {
			if( array_key_exists($_key,$_url_array) ) continue;
			$_url_array[$_key] = $_val;
		}
	}
}

/**
 * 生成详细页面里面的操作按钮的顺序
 * @param array detail pages
 * @retrun null
 */
function ecos_desktop_finder_builder_detail_gen_detail_page(&$detail_pages=array())
{
	if (isset($detail_pages))
        {
            foreach ((array)$detail_pages as $k=>$detail_func)
            {
                $str_detail_order = 'detail_' . $detail_func[1] . '_order';
                if (isset($detail_func[0]->$str_detail_order) && $detail_func[0]->$str_detail_order)
                {
                    switch ($detail_func[0]->$str_detail_order)
                    {
                        case COLUMN_IN_HEAD:
                            $tmp = $detail_pages[$k];
                            unset($detail_pages[$k]);
                            $detail_pages = array_reverse($detail_pages);
                            $detail_pages[$k] = $tmp;
                            $detail_pages = array_reverse($detail_pages);
                            break;
                        case COLUMN_IN_TAIL:
                            $tmp = $detail_pages[$k];
                            unset($detail_pages[$k]);
                            $detail_pages[$k] = $tmp;
                            break;
                    }
                }
            }
        }
}

/**
 * finder view 下面的main
 * 生成 filter
 * @param finder设置的视图
 * @param array get参数
 * @param boolean 是否使用统计标签
 * @param int 计数
 * @param array 中间过滤参数，其他方法里面会使用
 * @param array 过滤后需要处理用到的filter
 * @param array 过滤后的get filter
 */
function ecos_desktop_finder_builder_view_main_gen_filter($_view=array(),$_arr_get=array(),$_use_view_tab,&$tab_view_count=0,&$__view_filter=array(),&$view_filter=array(),&$get_filter=array())
{
	if(count($_view) && $_use_view_tab){
		$tab_view_count = 0;
		foreach((array)$_view as $view){
			if($view['addon'])
				$tab_view_count += $view['addon'];
		}
		$view_filter = (array)$_view[$_arr_get['view']]['filter'];
	}
	$__view_filter = $view_filter;

	if($_arr_get['filter']){
		$get_filter = (array)$_arr_get['filter'];
		if(!is_array($_arr_get['filter'])){
			if(isset($_arr_get['filter']) && $_arr_get['filter']=(array)unserialize(urldecode($_arr_get['filter']))){
				$get_filter = (array)$_arr_get['filter'];
			}
		}
	}
}

function ecos_desktop_finder_builder_view_script_gen_finderoptions($data,$is_display_packet,$__options,&$finderOptions=array())
{
	if ($finderOptions['packet']){
		foreach ($data as $arr){
			if ($arr['addon']){
				$is_display_packet = 'true';
				break;
			}
			else
				$is_display_packet = 'false';
		}
	}
	if ($is_display_packet == 'true')
		$finderOptions['packet'] = true;
	else
		$finderOptions['packet'] = false;
	/** end **/
	if($__options){
		$finderOptions = array_merge($finderOptions,$__options);
	}
	
	$finderOptions = json_encode($finderOptions);
}

function ecos_desktop_finder_builder_view_actions_make_actions($finder_name,&$actions,&$show_actions=array(),&$other_actions=array())
{
	if (isset($actions) && $actions)
	{
		foreach($actions as $key=>$item){

		//  if(!$item['label']){continue;}
			
			if($item['href']){$item['href'] = $item['href'].'&_finder[finder_id]='.$finder_name.'&finder_id='.$finder_name;
			}else{
			   $item['href'] ="javascript:void(0);";
			}
			if($item['submit']){$item['submit'] = $item['submit'].'&finder_id='.$finder_name;}
		
			$show_actions[] = $item;
			unset($actions[$key]);
			if($i++==$max_action-1){
				break;
			}
		}
		$other_actions = $actions;
	}
}

function ecos_desktop_finder_builder_view_create_view_column_sql($allCols,$col,$dbschema,&$sql=array())
{
	if(isset($allCols[$col]['sql'])){
		$sql[] = $allCols[$col]['sql'].' as '.$col;
	}elseif($col=='_tag_'){
		$sql[] = $dbschema['idColumn'].' as _tag_';
	}else{
		$sql[] = '`'.$col.'`';
	}
}


/*** site > widget ***/


function ecos_site_lib_theme_widget_save_all($widgets_id,$widgets,$match,&$return,&$slots)
{
	if(!$widgets_id){
		return false;
	}else{
		$return[$_SESSION['_tmp_wg_insert'][$match[1]]['_domid']] = $widgets_id;
		unset($_SESSION['_tmp_wg_insert'][$match[1]]);
		$slots[$widgets['core_file']][]=$widgets_id;
        return true;
	}
}

function ecos_site_lib_theme_widget_widgets_config_empty($name,&$data,$app)
{	
	$data['crun'] = 'widget_cfg_' . $name;
	$data['cfg'] = $data['dir'] . '/widget_cfg_' . $name . '.php';
	$data['run'] = 'widget_' . $name;
	$data['func'] = $data['dir'] . '/' . $data['run'] . '.php';
	$data['flag'] = 'app_' . $app;
}

function ecos_site_lib_theme_widget_widgets_config_theme($name,&$data,$theme)
{
	$data['crun'] = 'theme_widget_cfg_' . $name;
	$data['cfg'] = $data['dir'] . '/theme_widget_cfg_' . $name . '.php';
	$data['run'] = 'theme_widget_' . $name;
	$data['func'] = $data['dir'] . '/' . $data['run'] . '.php';
	$data['flag'] = 'theme_' . $theme;
}

function ecos_site_lib_theme_widget_widgets_get_libs_notype($info,$val,&$widgetsLib=array())
{
	if($info['catalog']){
		if(!$widgetsLib['list'][$info['catalog']]){
			$widgetsLib['list'][$info['catalog']]=$info['catalog'];
		}
	}
	if($info['usual']=='1'){
		$widgetsLib['usual'][]=array('sort'=>$info['order'],'description'=>$info['description'],'name'=>$val['name'], 'app'=>$val['app'],'theme'=>$val['theme'],'label'=>$info['name']);
	}
}

function ecos_site_lib_theme_widget_widgets_get_libs_type($info,$type,$val,&$widgetsLib=array())
{
	if($info['catalog']==$type){
		$order[]=$info['order']?$info['order']:0;
		$widgetsLib['list'][] = array('sort'=>$info['order'],'description'=>$info['description'],'name'=>$val['name'], 'app'=>$val['app'],'theme'=>$val['theme'],'label'=>$info['name']);
	}
	/*
	if($info['usual']=='1'){
		$widgetsLib['usual'][]=array('sort'=>$info['order'],'description'=>$info['description'],'name'=>$file,'label'=>$info['name']);
	}
	*/
}

function ecos_site_lib_theme_widget_prefix_content(&$content, $widgets_dir)
{
	$pattern = array(
		'/(\'|\")(images\/)/is',
		'/((?:background|src|href)\s*=\s*["|\'])(?:\.\/|\.\.\/)?(images\/.*?["|\'])/is',
		'/((?:background|background-image):\s*?url\()(?:\.\/|\.\.\/)?(images\/)/is',
	);
	$replacement = array(
		"\$1" . $widgets_dir .'/' . "\$2",
		"\$1" . $widgets_dir .'/' . "\$2",
		"\$1" . $widgets_dir .'/' . "\$2",
	);
	$content = preg_replace($pattern, $replacement, $content);
}

function ecos_site_lib_theme_widget_editor(&$widgets,&$values,$setting,$widgets_dir,&$return)
{
	//kxgsy163  Ä¬ÈÏÅäÖÃÎ´·ÅÈë$values±äÁ¿
	is_array($values) or $values=array();
	$values = array_merge($setting, $values);
	
	if(!empty($setting['template'])){
		$return['tpls'][$file]=$setting['template'];////////
	}else{
		if($widgets=='html'){
			$widgets='usercustom';
			if(!$values['usercustom']) $values['usercustom']= $values['html'];
		}
		if ($handle = opendir($widgets_dir)) {
			while (false !== ($file = readdir($handle))) {
				if(substr($file,0,1)!='_' && strtolower(substr($file,-5))=='.html' && file_exists($widgets_dir.'/'.$file)){
					$return['tpls'][$file]=$file;
				}
			}
			closedir($handle);
		}else{
			return false;
		}
	}
}



/*** site > router ***/
