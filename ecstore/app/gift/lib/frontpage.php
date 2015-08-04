<?php

 
class gift_frontpage extends site_controller{
    //todo
    function __construct(&$app){
        parent::__construct($app);     
    } 
    /**
     * 取得当前登陆会员信息
     * @return array
     */
    public function get_current_member()
    {
        return kernel::single( 'b2c_frontpage' )->get_current_member();
    }

    /**
     * 设置seo相关信息
     *
     * @param string $app app名称
     * @param string $act 控制器名称
     * @param array $args 请求参数
     * @return void
     */
    function setSeo($app,$act,$args=null){
        $seo = kernel::single('site_seo_base')->get_seo_conf($app,$act,$args);
        $this->title = $seo['seo_title'];
        $this->keywords = $seo['seo_keywords'];
        $this->content = $seo['seo_content'];
        $this->nofollow = $seo['seo_nofollow'];
        $this->noindex = $seo['seo_noindex'];
    }//End Function


    
    
}
