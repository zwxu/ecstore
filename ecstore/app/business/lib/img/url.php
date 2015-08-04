<?php
class business_img_url
{
    var $pattern_src='/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.jpeg|\.bmp].*?))[\'|\"].*?[\/]?>/';
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->urlpattern=$this->get_pattern();
    }
    
    //是否合法的url地址。
    public function is_valid_url($url){
        return preg_match($this->urlpattern,$url)>0?true:false;
    }
    
    //替换掉非法的连接地址。
    public function replace_html($customhtml,$str='#'){
        //取得不合法的数组替换
        $search=$this->get_invalid_link($customhtml);
        if(empty($search)){
           return $customhtml;
        }
        $replace=array_fill(0, count($search), $str);
        $customhtml=htmlspecialchars_decode($customhtml);
        return str_replace($search,$replace,$customhtml);
    }
    
    //取得不合法的连接地址数组
	public function get_invalid_link($customhtml){
        $invalid=array();
        $match=$this->get_link($customhtml);
        if($this->urlpattern && $match){
            foreach($match as $v){
                if(preg_match($this->urlpattern,$v)==0){
                   $invalid[]=$v;
                }
            }
        }
        return $invalid;
    }
    
    //验证html中是否存在非本站连接。
    public function is_valid_html($customhtml){
        $invalid=$this->get_invalid_link($customhtml);
        return empty($invalid);
    }
    
    //取得本站地址验证规则。
    function get_pattern(){
        $website=$this->app->getConf('website.img_url');
        if($website){
            $website=array_map('preg_quote',$website);
            $pattern=implode('|',$website);
            return '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*\.?)?('.$pattern.')(:(\d+))?\/?/i';
        }
        return '';
        
                
    }
    //获取html的所有图片地址
    function get_link($customhtml){
        $customhtml=htmlspecialchars_decode($customhtml);
        $match=array();
        preg_match_all($this->pattern_src,$customhtml,$links);
        while(list($key,$val) = each($links[1])) {
            if(!empty($val))
                $match[] = $val;
        } 
        return $match;
    }
}