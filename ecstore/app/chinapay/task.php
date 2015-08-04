<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class chinapay_task  
{		
	/**
	 * before install
	 * @param null
	 * @return null
	 */
    public function post_install() 
    {		
		kernel::log('Initial chinapay');
        kernel::single('base_initial', 'chinapay')->init();
    }//End Function
	
	public function post_uninstall()
	{
		
    }//End Function
}//End Class
