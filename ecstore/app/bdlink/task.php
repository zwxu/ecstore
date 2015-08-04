<?php


class bdlink_task 
{
    function post_install()
    {
        kernel::log('Initial bdlink');
        kernel::single('base_initial', 'bdlink')->init();
    }//End Function
}//End Class
