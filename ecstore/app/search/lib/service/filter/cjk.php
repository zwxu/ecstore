<?php

class search_service_filter_cjk implements search_interface_filter 
{

    public function normalize($input) 
    {
        $search = array(",", "/", "\\", ".", ";", ":", "\"", "!", 
                        "~", "`", "^", "(", ")", "?", "-", "\t", "\n", "'", 
                        "<", ">", "\r", "\r\n", "$", "&", "%", "#", 
                        "@", "+", "=", "{", "}", "[", "]", "：", "）", "（", 
                        "．", "。", "，", "！", "；", "“", "”", "‘", "’", "［", "］", 
                        "、", "—", "　", "《", "》", "－", "…", "【", "】",
        );
        return str_replace($search, ' ', $input);
    }//End Function
}//End Class