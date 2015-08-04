<?php

 

class gift_task 
{
    function post_install()
    {
        kernel::log('Initial gift');
        kernel::single('base_initial', 'gift')->init();
    }//End Function
}//End Class
