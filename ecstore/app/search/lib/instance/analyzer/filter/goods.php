<?php

class search_instance_analyzer_filter_goods extends search_abstract_analysis_analyzer_filter
{
    public $_defaultToken = array();
    
    public function normalize($input) 
    {
        $this->resetDefaultToken();
        preg_match_all('/\#[0-9a-zA-Z]+\@/isU', $input, $match);
        if(count($match[0])){
            foreach($match[0] AS $v){
                $start = strpos($input, $v);
                $end = $start + strlen($v);
                $newToken = new Zend_Search_Lucene_Analysis_Token(
                                             $v,
                                             $start,
                                             $end);
                $this->_defaultToken[] = $newToken;
                $input = preg_replace('/'.preg_quote($v).'/', ' ', $input, 1);
            }
        }
        return $input;
    }//End Function

    public function resetDefaultToken() 
    {
        $this->_defaultToken = array();
    }//End Function

    public function getDefaultToken() 
    {
        return ($this->_defaultToken) ? $this->_defaultToken : array();
    }//End Function
    
}//End Class