<?php
class notebook_gravatar{

    function get_output($item){
        return sprintf('<img style="float:left;margin-right:10px"
            src="http://www.gravatar.com/avatar/%s?s=48&r=x" />',md5($item['item_email'])
        );
    }

}
?>