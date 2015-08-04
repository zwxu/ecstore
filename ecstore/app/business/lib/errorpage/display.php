<?php 

class business_errorpage_display
{
    
    
    
    /**
     * 
     * @params errormsg 默认错误信息
     * @params key 唯一 
     **/
    public function init_conf() {
        $return = array(
                    array(
                        'title' => '店铺异常',
                        'key' => 'errorpage.closeStore',
                        'errormsg' => '<div style="height:200px;line-height:50px;text-align:center;">无效店铺！<br>店铺审核未通过或者已经关店</div>',
            			'desc' => '店铺异常',
                        )
                  );
        return $return;
    }
}