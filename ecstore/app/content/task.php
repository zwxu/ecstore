<?php


class content_task 
{
    function post_install()
    {
        kernel::log('Initial content');
        kernel::single('base_initial', 'content')->init();
    }//End Function
}//End Class
