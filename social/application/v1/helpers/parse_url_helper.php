<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * [get_url_source Get source details based on given url]
 * @param  [string] $url [url]
 * @return [string]      [source details]
 */
if ( ! function_exists('get_url_source')) 
{    
    function get_url_source($url)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $a = curl_exec($ch); // $a will contain all headers
        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL
        return $url; // Voila
    }
}

/**
 * [get_domain Get domin based on given url]
 * @param  [string] $url [url]
 * @return [string]      [domain name]
 */
if ( ! function_exists('get_domain')) 
{ 
    function get_domain($url)
    {
      $pieces = parse_url($url);
      $domain = isset($pieces['host']) ? $pieces['host'] : '';
      if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
      }
      return false;
    }
}

/**
 * [get_youtube_id_from_url Get youtube id from given url]
 * @param  [string] $url [url]
 * @return [string]      [youtube id]
 */
if ( ! function_exists('get_youtube_id_from_url')) 
{ 
    function get_youtube_id_from_url($url) 
    {
         $pattern = 
                "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/"
                ;
        $result = preg_match($pattern, $url, $matches);
        if ($result) 
        {
            return $matches[1];
        }
        return false;
    }
}

/**
 * [get_vimeo_thumb Get vimeo thumb from given url]
 * @param  [string] $videoLink  [videoLink]
 * @return [string]             [vimeo thumb]
 */
if ( ! function_exists('get_vimeo_thumb')) 
{ 
    function get_vimeo_thumb($videoLink)
    {
        $videoId = '';
        if (preg_match("/https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/", $videoLink, $id)) 
        {
            $videoId = $id[3];
            if(!empty($videoId))
            {
                $xml = simplexml_load_file("http://vimeo.com/api/v2/video/".$videoId.".xml");
                $videoDetail = json_decode(json_encode((array)$xml));
                if(!empty($videoDetail))
                {
                    return  get_url_source($videoDetail->video->thumbnail_medium);
                }
                else
                {
                    return false;   
                }
            }
        }
    }
}

/**
 * [check_url_values check the value of given url]
 * @param  [string] $value  [value]
 * @return [string]         [url]
 */
if ( ! function_exists('check_url_values')) 
{
    function check_url_values($value)
    {
        $value = trim($value);
        if (get_magic_quotes_gpc())
        {
                $value = stripslashes($value);
        }
        $value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
        $value = strip_tags($value);
        $value = htmlspecialchars($value);
        return $value;
    }
}

if ( ! function_exists('extract_url_tags(')) 
{
    function extract_url_tags( $html, $tag, $selfclosing = null, $return_the_entire_tag = false, $charset = 'ISO-8859-1' )
    {
        if ( is_array($tag) )
        {
            $tag = implode('|', $tag);
        }

        //If the user didn't specify if $tag is a self-closing tag we try to auto-detect it
        //by checking against a list of known self-closing tags.
        $selfclosing_tags = array( 'area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta', 'col', 'param' );
        if ( is_null($selfclosing) )
        {
            $selfclosing = in_array( $tag, $selfclosing_tags );
        }

        //The regexp is different for normal and self-closing tags because I can't figure out
        //how to make a sufficiently robust unified one.
        if ( $selfclosing )
        {
            $tag_pattern =
            '@<(?P<tag>'.$tag.')  # <tag
            (?P<attributes>\s[^>]+)?  # attributes, if any
            \s*/?>                    # /> or just >, being lenient here
            @xsi';
        } 
        else 
        {
            $tag_pattern =
            '@<(?P<tag>'.$tag.')  # <tag
            (?P<attributes>\s[^>]+)?  # attributes, if any
            \s*>                    # >
            (?P<contents>.*?)            # tag contents
            </(?P=tag)>                # the closing </tag>
            @xsi';
        }

        $attribute_pattern =
        '@
        (?P<name>\w+)                            # attribute name
        \s*=\s*
        (
        (?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)    # a quoted value
        |                            # or
        (?P<value_unquoted>[^\s"\']+?)(?:\s+|$)            # an unquoted value (terminated by whitespace or EOF)
        )
        @xsi';

        //Find all tags
        if ( !preg_match_all($tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) )
        {
            //Return an empty array if we didn't find anything
            return array();
        }

        $tags = array(); 
        $a = 0;
        foreach ($matches as $match)
        {
            $a++;
            //Parse tag attributes, if any
            $attributes = array();
            if ( !empty($match['attributes'][0]) )
            {
                if ( preg_match_all( $attribute_pattern, $match['attributes'][0], $attribute_data, PREG_SET_ORDER ) )
                {
                    //Turn the attribute data into a name->value array
                    foreach($attribute_data as $attr)
                    {
                        if( !empty($attr['value_quoted']) )
                        {
                            $value = $attr['value_quoted'];
                        } 
                        else if( !empty($attr['value_unquoted']) )
                        {
                            $value = $attr['value_unquoted'];
                        } 
                        else 
                        {
                            $value = '';
                        }

                        //Passing the value through html_entity_decode is handy when you want
                        //to extract link URLs or something like that. You might want to remove
                        //or modify this call if it doesn't fit your situation.
                        $value = html_entity_decode( $value, ENT_QUOTES, $charset );

                        $attributes[$attr['name']] = $value;
                    }
                }
            }

            $tag = array(
                'tag_name' => $match['tag'][0],
                'offset' => $match[0][1],
                'contents' => !empty($match['contents'])?$match['contents'][0]:'', //empty for self-closing tags
                'attributes' => $attributes,
            );

            if ( $return_the_entire_tag )
            {
                $tag['full_tag'] = $match[0][0];
            }

            $tags[] = $tag;  
        }
        return $tags;
    }
}

function is_404($url) {
    $handle = curl_init($url);
    curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

    /* Get the HTML or whatever is linked in $url. */
    $response = curl_exec($handle);

    /* Check for 404 (file not found). */
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    /* If the document has loaded successfully without any redirection or error */
    if ($httpCode >= 200 && $httpCode < 300) {
        return false;
    } else {
        return true;
    }
}