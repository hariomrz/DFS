<?php
    switch (ENVIRONMENT)
    {
        case 'production':
            define('SUBSCRIPTION_KEY','ffckr5u7ut5t256rnubwsf6u');
            define('ACCESS_LEVEL','p');
        break;
        default:
            define('SUBSCRIPTION_KEY','yerzt87kta8bpdqkj2urz379');
            define('ACCESS_LEVEL','t');
        break;
    }   
    


function two_factor_SMS($From,$To,$Msg,$sms_type=1)
{
    $YourAPIKey=SMS_GATEWAY_AUTH_KEY;
   
    $api_url = "/ADDON_SERVICES/SEND/TSMS";

    //sms type 1=transactional
    //sms type 2=promotional
    switch ($sms_type) {
        case 1:
            $api_url = "/ADDON_SERVICES/SEND/TSMS";
            break;
        case 2:
            $api_url = "/ADDON_SERVICES/SEND/PSMS";
            break;
        
        default:
            # code...
            break;
    }

    ### DO NOT Change anything below this line

    $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

    $url = TWO_FACTOR_SMS_API_ENDPOINT."$YourAPIKey".$api_url; 

    $ch = curl_init(); 
    curl_setopt($ch,CURLOPT_URL,$url); 
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); 
    curl_setopt($ch,CURLOPT_POSTFIELDS,"From=$From&To=$To&Msg=$Msg"); 
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    $output = curl_exec($ch); 
    curl_close($ch);

    return $output;
}

    





function check_key_exist($str='',$array=array())
{
    if(isset($array[$str])){
        return $array[$str];
    }else{
        return array();
    }
}



function xml2array($contents, $get_attributes = 1, $priority = 'attribute')
{
	if (!$contents) return array();

	if (!function_exists('xml_parser_create')) {
		//print "'xml_parser_create()' function not found!";
		return array();
	}

	//Get the XML parser of PHP - PHP must have this module for the parser to work
	$parser = xml_parser_create('');
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);

	if (!$xml_values) return; //Hmm...

	//Initializations
	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();

	$current = & $xml_array; //Refference

	//Go through the tags.
	$repeated_tag_index = array(); //Multiple tags with same name will be turned into an array
	foreach ($xml_values as $data) {
		unset($attributes, $value); //Remove existing values, or there will be trouble

		//This command will extract these variables into the foreach scope
		// tag(string), type(string), level(int), attributes(array).
		extract($data); //We could use the array by itself, but this cooler.

		$result = array();
		$attributes_data = array();

		if (isset($value)) {
			if ($priority == 'tag') $result = $value;
			else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
		}

		//Set the attributes too.
		if (isset($attributes) and $get_attributes) {
			foreach ($attributes as $attr => $val) {
				if ($priority == 'tag') $attributes_data[$attr] = $val;
				else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
			}
		}

		//See tag status and do the needed.
		if ($type == "open") { //The starting of the tag '<tag>'
			$parent[$level - 1] = & $current;
			if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
				$current[$tag] = $result;
				if ($attributes_data) $current[$tag . '_attr'] = $attributes_data;
				$repeated_tag_index[$tag . '_' . $level] = 1;

				$current = & $current[$tag];

			} else { //There was another element with the same tag name

				if (isset($current[$tag][0])) { //If there is a 0th element it is already an array
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					$repeated_tag_index[$tag . '_' . $level]++;
				} else { //This section will make the value an array if multiple tags with the same name appear together
					$current[$tag] = array($current[$tag], $result); //This will combine the existing item and the new item together to make an array
					$repeated_tag_index[$tag . '_' . $level] = 2;

					if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
						$current[$tag]['0_attr'] = $current[$tag . '_attr'];
						unset($current[$tag . '_attr']);
					}

				}
				$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
				$current = & $current[$tag][$last_item_index];
			}

		} elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
			//See if the key is already taken.
			if (!isset($current[$tag])) { //New Key
				$current[$tag] = $result;
				$repeated_tag_index[$tag . '_' . $level] = 1;
				if ($priority == 'tag' and $attributes_data) $current[$tag . '_attr'] = $attributes_data;

			} else { //If taken, put all things inside a list(array)
				if (isset($current[$tag][0]) and is_array($current[$tag])) { //If it is already an array...

					// ...push the new element into that array.
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

					if ($priority == 'tag' and $get_attributes and $attributes_data) {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag . '_' . $level]++;

				} else { //If it is not an array...
					$current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $get_attributes) {
						if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well

							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset($current[$tag . '_attr']);
						}

						if ($attributes_data) {
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
					}
					$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
				}
			}

		} elseif ($type == 'close') { //End of tag '</tag>'
			$current = & $parent[$level - 1];
		}
	}

	return ($xml_array);
}




/*This function for get api data through curl if file_get_contents have create some issue */

function get_api_data($url)
{
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);
	if (empty($buffer)){
		return  "Nothing returned from url.<p>";
	}else{
		return  $buffer;
	}
}


//Time Interval for game closed for when we will proccess prize distribution

function game_interval($sports_id)
{
	switch ($sports_id)
	{
		case '1':
			$interval = 8;
		break;
		case '4':
			$interval = 8;
		break;
		case '5':
			$interval = 8;
		break;
		  // In case of Cricket (OneDay)
		case '7_1':
			$interval = 15;
		break;
            // In case of Cricket (Test)
		case '7_2':
			$interval = 144;    // 24*6
		break;
            // In case of Cricket (T20)
		case '7_3':
			$interval = 8;
		break;
		case '8':
			$interval = 6;
		break;
		case '10':
                $interval = 1;
                break; 
		default:
			$interval = 6;
	}    
	
	return $interval;
}

	function game_format($format)
	{

		switch ($format) 
		{
			case CRICKET_ONE_DAY:
				$str = CRICKET_ONE_DAY_TEXT;
			break;
			case CRICKET_TEST:
				$str = CRICKET_TEST_TEXT;
			break;
			case CRICKET_T20:
				$str = CRICKET_T20_TEXT;
			break;
			case CRICKET_T10:
				$str = CRICKET_T10_TEXT;
			break;
			default:
		
				$str = CRICKET_ONE_DAY_TEXT;
			break;
		}

		return $str;
	}

function get_team_abbr_brasileiro_serie_a($team_name)
{
	$team_array = array("AME"=>"América-MG","FlU"=>"Fluminense", "MIN"=>"Atlético Mineiro", "SFC"=>"Santos", "BOT"=>"Botafogo", 
		"SPA"=>"Sao Paulo", "COR"=>"Corinthians", "GRE"=>"Gremio", "COR"=>"Coritiba", "CRU"=>"Cruzeiro", "FIG"=>"Figueirense", "PON"=>"Ponte Preta",
		 "FLA"=>"Flamengo", "REC"=>"Sport Recife", "INT"=>"Internacional", "CHA"=>"Chapecoense", "PAL"=>"Palmeiras", "ATP"=>"Atlético Paranaense",
		 "SCR"=>"Santa Cruz FC", "VIT"=>"Vitória");
	$abbr = array_search($team_name, $team_array);
	if($abbr) return strtoupper($abbr); 
	$word_count = str_word_count($team_name);
	if ($word_count > 2) {
		$te = explode(' ', $team_name);
		$team_abbr = substr($te[0], 0, 1) . substr($te[1], 0, 1);
	} else {
		$te = explode(' ', $team_name);
		if (strlen($te[0]) == 2) {
			$team_abbr = substr($te[0], 0, 2) . substr($te[1], 0, 1);
		} else {
			$team_abbr = substr(strtok($te[0], " "), 0, 3);
		}
	}
	return strtoupper($team_abbr);
}
function get_team_abbr_copa_do_brazil($team_name)
{
	$team_array = array("COR"=>"Corinthians","NAR"=>"Nautico-RR","CRU"=>"Cruzeiro","FLU"=>"Fluminense","GOI"=>"Goiás",
						"INL"=>"Internacional","JUV"=>"Juventude","YPI"=>"Ypiranga","ATG"=>"Atlético Goianiense",
						"CA"=>"Campinense","ASA"=>"ASA","AME"=>"América-MG","BRA"=>"Bragantino","PON"=>"Ponte Preta",
						"POR"=>"Portuguesa","BOT"=>"Botafogo","FLA"=>"Flamengo","VAS"=>"Vasco da Gama","LIN"=>"Linense",
						"LAJ"=>"Lajeadense","Fig"=>"Figueirense","For"=>"Fortaleza", "GRE"=>"Gremio","Par"=>"Paraná","SAM"=>"Sampaio Correa",
						"COR"=>"Coruripe","CHA"=>"Chapecoense","CDR"=>"Clube do Remo","CUI"=>"Cuiaba","NFC"=>"Nacional FC","Independente AC"=>"IAC",
						"Operario"=>"OFEC","Joinville"=>"JOI","Caldense"=>"AAC","Tombense"=>"TOM","Juazeirense"=>"JUA","Vitória da Conquista"=>"VDC",
						"Londrina"=>"LEC","SCR"=>"Santa Cruz FC","CON"=>"Confiança","PAR"=>"Parnahyba","AFE"=>"Ferroviária","RBB"=>"Red Bull Brasil",
						"SMF"=>"Santos do Macapa","BFC"=>"Botafogo-PB","BRA"=>"Brasilia","RBR"=>"Rio Branco","SCG"=>"Genus","RIV"=>"River-PI","GUA"=>"Guarany",
						"GLO"=>"Globo","OPE"=>"Operario","INT"=>"Inter de Lages","IMP"=>"Imperatriz","Comercial-MS"=>"COM","Aparecidense"=>"APA",
						"Rio Branco-ES"=>"RBR","Estanciano"=>"EST","Brasil-RS"=>"BRA","Galvez"=>"GAL","Ivinhema"=>"IVI","Parauapebas"=>"PAR",
						"TOC"=>"Tocantinopolis","DBO"=>"Dom Bosco","Ame"=>"América-RN","NAU"=>"Náutico","REC"=>"Sport Recife","COR"=>"Coritiba",
						"ATP"=>"Atlético Paranaense","SFC"=>"Santos","SPA"=>"Sao Paulo","PAL"=>"Palmeiras","RES"=>"Resende","VIT"=>"Vitória","ABC"=>"ABC",
						"AVA"=>"Avaí","BAH"=>"Bahia","CEA"=>"Ceará","CRB"=>"Clube de Regatas Brasil","CRI"=>"Criciúma","Ga"=>"Gama"
						);
	$abbr = array_search($team_name, $team_array);
	if($abbr) return strtoupper($abbr); 

	$word_count = str_word_count($team_name);
	if ($word_count > 2) {
		$te = explode(' ', $team_name);
		$team_abbr = substr($te[0], 0, 1) . substr($te[1], 0, 1);
	} else {
		$te = explode(' ', $team_name);
		if (strlen($te[0]) == 2) {
			$team_abbr = substr($te[0], 0, 2) . substr($te[1], 0, 1);
		} else {
			$team_abbr = substr(strtok($te[0], " "), 0, 3);
		}
	}	
	return strtoupper($team_abbr);
}

function add_flag($arr)
{
if(!empty($arr['flag']))
{
$arr['flag_url'] = get_image(0,$arr['flag']); 
}
else
{
$arr['flag_url'] = get_image(0,''); 
}	
return $arr;
}

function add_jersey($arr)
{
if(!empty($arr['jersey']))
{
$arr['jersey_url'] = get_image(1,$arr['jersey']); 
}
else
{
$arr['jersey_url'] = get_image(1,''); 
}	
return $arr;
}

function add_player_image($arr)
{
if(!empty($arr['player_photo']))
{
$arr['player_photo_url'] = get_image(3,$arr['player_photo']); 
}
else
{
$arr['player_photo_url'] = get_image(3,''); 
}	
return $arr;
}

function add_league_logo($arr)
{
if(!empty($arr['image']))
{
$arr['image_url'] = get_image(2,$arr['image']); 
}
else
{
$arr['image_url'] = get_image(2,''); 
}	
return $arr;
}









