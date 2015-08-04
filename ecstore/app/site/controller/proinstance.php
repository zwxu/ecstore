<?php

class site_ctl_proinstance extends site_controller 
{
    
    public function index() 
    {
        $this->pagedata['file'] = 'site_proinstance:'.$this->_request->get_param(0);
        $this->page('proinstance.html', true);
    }//End Function
    
	public function get_css()
	{
		$params = $this->_request->get_params(true);		
		$theme = $params[0];
		$tmpl = base64_decode($params[1]);		
		$this->set_theme($theme);        
        $this->pagedata['store_id']=$params[2];
        $GLOBALS['runtime']['store_id']=$params[2];        
        $this->tmpl_cachekey('store_id',$params[2]);
         $this->_compiler()->set_compile_helper('compile_widgets',kernel::single('business_view_compiler'));
		$content = $this->display_tmpl($tmpl,true);
		
		$style = '';
		$__widgets_css = array();
		preg_match_all('/<\s*style.*?>(.*?)<\s*\/\s*style\s*>/is', $content, $matchs);
		if(isset($matchs[0][0]) && !empty($matchs[0][0])){
			$__widgets_css = array_merge($__widgets_css,$matchs[1]);
        }		
		$style .= implode("\r\n", array_unique($__widgets_css));	
		
		$this->_response->set_body($style);
		$this->_response->set_header('Content-type','text/css');
	}//End Function 

}//End Class