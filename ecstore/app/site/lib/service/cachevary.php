<?php


class site_service_cachevary 
{
    public function get_varys() 
    {
        $varys['SEPARATOR'] = trim(app::get('site')->getConf('base.site_params_separator'));
        $varys['URI_EXPENEDE_NAME'] = (app::get('site')->getConf('base.enable_site_uri_expanded') == 'true') ? '.' . app::get('site')->getConf('base.site_uri_expanded_name') : '';
        return $varys;
    }//End Function

}//End Class