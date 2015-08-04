<?php

 

 /**
 * CMS系统的前台路由
 */
class content_service_router
{
	/**
	* @var bool 
	*/
    private $_enable = false;
	/**
	* 构造方法,为变量赋值
	*/
    function __construct() 
    {
        $this->_enable = (app::get('content')->getConf('base.use_node_path_url') == 'true') ? true : false;
    }//End Function
	
	/**
	* 获取 use_node_path_url值
	*/
    function enable() 
    {
        return $this->_enable;
    }//End Function
	
	/**
	* 获取前台控制器对应的地址
	* @param array $params 控制器，方法，参数 等
	* @return string  返回访问地址
	*/
    public function gen_url($params = array()) 
    {
        $full = ($params['full']) ? 'true' : 'false';
        $real = ($params['real']) ? 'true' : 'false';
        $act = ($params['act']) ? $params['act'] : 'index';
        $args_keys = array_keys($params['args']);
        $first_arg = $params['args'][$args_keys[0]];
        if($params['ctl'] == 'site_article' && $first_arg > 0){
            switch($act){
                case 'index':
                    $article_indexs = kernel::single('content_article_detail')->get_index($first_arg, true);
                    $node_id = $article_indexs['node_id'];
                    break;
                case 'l':
                case 'i':
                    $node_id = $first_arg;
                    break;
                default:
                    $node_id = 0;
            }
            if($node_id > 0){
                $prefix = kernel::single('content_article_node')->get_node_path_url($node_id, true);
                
                if( 'i'==$act && 'CONTENT_NULL' == $prefix){
                    return "javascript:void(0)";
                }
            }
        }
        if($params['act']=='index' && (count($params['args'])==0 || is_numeric($first_arg))){
            //此情况可省略act 这个太恶心了 EDwin
        }else{
            array_unshift($params['args'], $params['act']);
        }
        if(isset($prefix)) array_unshift($params['args'], $prefix);
        $urlmap = kernel::single('site_router')->get_urlmap();
        $part = $urlmap[$params['app'].':'.$params['ctl']];
        array_unshift($params['args'], $part);
        $base_url = app::get('site')->base_url($full);
        $url =  implode(kernel::single('site_router')->get_separator(), $params['args']) . kernel::single('site_router')->get_uri_expended_name($part);
        return $base_url . (($params['real']=='true') ? $url : kernel::single('site_router')->parse_route_static_genurl($url));
    }//End Function

    public function dispatch($query, $allow_name, $separator) 
    {
        $args = explode($separator, $query);
        if(isset($args)){
            $ctl_flag = array_shift($args);
            $urlmap = kernel::single('site_router')->get_urlmap();
            $urlmap = array_flip($urlmap);
            if($urlmap[$ctl_flag] == 'content:site_article'){
                array_shift($args);
            }
            array_unshift($args, $ctl_flag);
            $query = @join(kernel::single('site_router')->get_separator(), $args);
        }
        kernel::single('site_router')->default_dispatch($query, $allow_name, $separator);
    }//End Function

}//End Class
