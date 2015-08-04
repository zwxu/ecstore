<?php

class search_instance_analyzer_filter_cjk extends search_abstract_analysis_analyzer_filter
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