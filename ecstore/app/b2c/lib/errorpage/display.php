<?php

class b2c_errorpage_display
{
    function init_conf(){

        $return = array(
                    array(
                        'title' => '搜索为空页',
                        'key' => 'errorpage.search',
                        'errormsg' => '<h1 class="error" style="">非常抱歉，没有找到相关商品</h1>
        <p style="margin:15px 1em;"><strong>建议：</strong><br />适当缩短您的关键词或更改关键词后重新搜索，如：将 “索尼手机X1” 改为 “索尼+X1”</p>',
            			'desc' => '搜索为空页',
                        ),
        );
        return $return;
    }
 




}
?>