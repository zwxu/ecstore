<?php


class tags_task 
{
    public function post_install()
    {
    	kernel::log('Register tag meta');
    	$obj_tags = app::get( 'desktop' )->model( 'tag' );
    	$col = array(
    	    'params' => array(
    	        'type' => 'serialize',
    	        'editable' => false,
    	    ),
    	);
    	$obj_tags->meta_register( $col );
        kernel::log('Initial tags');
        kernel::single('base_initial', 'tags')->init();
       
    }
    
    function post_uninstall(){
    	kernel::log('drop tag meta');
    	$obj_tags = app::get( 'desktop' )->model( 'tag' );
    	$obj_tags->meta_meta( 'params' );
    }
}