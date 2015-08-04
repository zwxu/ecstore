<?php 

class site_errorpage_display
{
    
    
    
    /**
     * 
     * @params errormsg 默认错误信息
     * @params key 唯一 
     **/
    public function init_conf() {
        $return = array(
                    array(
                        'title' => '无法找到页面',
                        'key' => 'errorpage.404',
                        'errormsg' => '无法找到页面',
            			'desc' => '404页面',
                        ),
                    array(
                        'title' => '系统错误',
                        'key'=>'errorpage.500',
                        'errormsg' => '系统发生错误',
            			'desc' => '500页面',
                        ),
                  );
        return $return;
    }
}