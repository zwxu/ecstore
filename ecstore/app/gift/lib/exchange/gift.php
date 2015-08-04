<?php

 

class gift_exchange_gift
{
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * 得到兑换赠品的链接
     * @param null
     * @return string html
     */
    public function gen_exchange_link()
    {
        $render = $this->app->render();
        return $render->fetch('site/member/exchange_gift.html');
    }
}