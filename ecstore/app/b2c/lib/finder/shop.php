<?php

 

class b2c_finder_shop {
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    var $column_editbutton = '操作';
    public function column_editbutton($row)
    {
        $callback_url = urlencode(kernel::openapi_url('openapi.b2c.callback.shoprelation','callback', array('shop_id'=>$row['shop_id'])));
        $api_url = kernel::base_url(1).kernel::url_prefix().'/api';
        $obj_user = kernel::single('desktop_user');
        $user_id = $obj_user->user_data['user_id'];
        $user_name = $obj_user->user_data['name'];
        $api_v = $this->app->getConf("api.local.version");
        $str_operation = "";
        if ($row['status'] == 'unbind')
        {
            $str_operation = '<a href="index.php?app=b2c&ctl=admin_shoprelation&act=showEdit&p[0]=' . $row['shop_id'] . '" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
            
            if ($str_operation)
                $str_operation .= '&nbsp;<a href="javascript:void(0);" onclick="new Request({evalScripts:true,url:\'index.php?ctl=shoprelation&act=index&p[0]=apply&p[1]=' . $this->app->app_id . '&p[2]=' . $callback_url . '&p[3]=' . $api_url .'&p[4]=' . $user_id . '&p[5]=' . $user_name . '&p[6]=' . $api_v . '\'}).get();">'.app::get('b2c')->_('申请绑定').'</a>';
            else
              $str_operation .= '<a href="javascript:void(0);" onclick="new Request({evalScripts:true,url:\'index.php?ctl=shoprelation&act=index&p[0]=apply&p[1]=' . $this->app->app_id . '&p[2]=' . $callback_url . '&p[3]=' . $api_url .'&p[4]=' . $user_id . '&p[5]=' . $user_name . '&p[6]=' . $api_v . '\'}).get();">'.app::get('b2c')->_('申请绑定').'</a>';
        }
        else
        {
            $str_operation = '';
            
            if ($str_operation)
                $str_operation .= '&nbsp;<a href="javascript:void(0);" onclick="new Request({evalScripts:true,url:\'index.php?ctl=shoprelation&act=index&p[0]=accept&p[1]=' . $this->app->app_id . '&p[2]=' . $callback_url . '&p[3]=' . $api_url .'&p[4]=' . $user_id . '&p[5]=' . $user_name . '&p[6]=' . $api_v . '\'}).get();">'.app::get('b2c')->_('解除绑定').'</a>';
            else
                $str_operation .= '<a href="javascript:void(0);" onclick="new Request({evalScripts:true,url:\'index.php?ctl=shoprelation&act=index&p[0]=accept&p[1]=' . $this->app->app_id . '&p[2]=' . $callback_url . '&p[3]=' . $api_url .'&p[4]=' . $user_id . '&p[5]=' . $user_name . '&p[6]=' . $api_v . '\'}).get();">'.app::get('b2c')->_('解除绑定').'</a>';
        }

        return $str_operation;
    }
}