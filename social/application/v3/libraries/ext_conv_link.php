<?php

/*
					This class will extract the hyperlinks from a given text and then make it shorten using 
					http://bit.ly URL shortner service.
					
					Make sure you already have a bit.ly login and API key.
					
					Bit.ly can return JSON format as well. Here we are requesting for xml format.
					
					By: Muhammad Arfeen


*/



class ext_conv_link {
	
		// a constructor
	
		function __construct($params){
			$this->_bitlyLogin 	= $params['bitlyLogin'];
			$this->_bitlyAPIKey = $params['bitlyAPIKey'];
			
		}
	
		// extracting links and converting
	
		function ExtractAndConvert($text=""){
			
			$hyperlinksArray = array();
			
			preg_match_all('(((f|ht){1}(tp://|tps://))[-a-zA-Z0-9@:%_+.~#?&//=]+)',$text,$hyperlinksArray);
			
				for($i=0;$i<count($hyperlinksArray[0]);$i++){
					
						$ShortLink = $this->_GetSortenLinkViaAPI(urlencode($hyperlinksArray[0][$i]));		
						$text = str_replace($hyperlinksArray[0][$i],"$ShortLink",$text);
		
				}
				
				return $text;
			
		}
				
		// url shortning via bit.ly API
		
		function _GetSortenLinkViaAPI($URL){
		
						$BitlyXML = file_get_contents("http://api.bit.ly/shorten?version=2.0.1&longUrl=$URL&login=".$this->_bitlyLogin."&apiKey=".$this->_bitlyAPIKey."&format=xml");
						
						$XMLtoArray = $this->_ConvXMLtoArray($BitlyXML);
	
								if(isset($XMLtoArray['bitly']['results']['nodeKeyVal']['shortUrl']['_value_']))
 										return $XMLtoArray['bitly']['results']['nodeKeyVal']['shortUrl']['_value_'];
	
					}
			
		
		// bit.ly xml conversion into array
		
		function _ConvXMLtoArray($contents, $get_attributes=1) { 
    if(!$contents) return array(); 

    if(!function_exists('xml_parser_create')) { 
        
        return array(); 
    } 
    
    $parser = xml_parser_create(); 
    xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 ); 
    xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 ); 
    xml_parse_into_struct( $parser, $contents, $xml_values ); 
    xml_parser_free( $parser ); 

    if(!$xml_values) return;

    
    $xml_array = array(); 
    $parents = array(); 
    $opened_tags = array(); 
    $arr = array(); 

    $current = &$xml_array; 

    
    foreach($xml_values as $data) { 
        unset($attributes,$value);

        
        
        extract($data);

        $result = ''; 
        if($get_attributes) {
            $result = array(); 
            if(isset($value)) $result['_value_'] = $value; 

             
            if(isset($attributes)) { 
                foreach($attributes as $attr => $val) { 
                    if($get_attributes == 1) $result['_attr_'][$attr] = $val;  
                    
                } 
            } 
        } elseif(isset($value)) { 
            $result = $value; 
        } 

        
        if($type == "open") {
            $parent[$level-1] = &$current; 

            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { 
                $current[$tag] = $result; 
                $current = &$current[$tag]; 

            } else { 
                if(isset($current[$tag][0])) { 
                    array_push($current[$tag], $result); 
                } else { 
                    $current[$tag] = array($current[$tag],$result); 
                } 
                $last = count($current[$tag]) - 1; 
                $current = &$current[$tag][$last]; 
            } 

        } elseif($type == "complete") { 
            
            if(!isset($current[$tag])) { 
                $current[$tag] = $result; 

            } else { 
                if((is_array($current[$tag]) and $get_attributes == 0)
                        or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes == 1)) { 
                    array_push($current[$tag],$result); 
                } else { 
                    $current[$tag] = array($current[$tag],$result); 
                } 
            } 

        } elseif($type == 'close') { 
            $current = &$parent[$level-1]; 
        } 
    } 

    return($xml_array); 
}

// end of the class

};

