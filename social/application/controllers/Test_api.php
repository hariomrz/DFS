<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Test_api extends CI_Controller {

    function curlUsingPost($action_for = "") {
        //$base = base_url() . 'index.php/admin_api/';
        $base = base_url() . 'api/';
        $action_for;
        $url = $base . $action_for;
        //$base = 'http://103.15.66.183:81/ReusableMobileCMS/api/getAllCategories';
        if ($action_for == 'signup') {
            $url = $base . 'signup/index';
            $data = array(
                'UserName' => "amithinduja16",
                'Email' => "amit.hinduja164@gmail.com",
                'Password' => "123456",
                "UserTypeID" => "",
                "SocialType" => "Web",
                "UserSocialID" => "1234567890",
                "IPAddress" => "",
                "Latitude" => "",
                "Longitude" => "",
                "FirstName" => "Amit",
                "LastName" => "Hinduja",
                "Token" => "",
                "Picture" => "",
                "DeviceType" => "Native",
            );
        } else if ($action_for == 'login') {
            $data = array(
                'Username' => "test@mailinator.com",
                'Password' => "123456",
                "SocialType" => "Web",
                "DeviceType" => "Native",
                "Resolution" => "sdgas",
            );
        } else if ($action_for == 'activity') {
            $data = array(
                'LoginSessionKey' => "40664e08c15f7f5f05138b538b0e7f32",
                //'LoginSessionKey' => "33d3bcf5c8acce4ae217d17b58285e72",
                'PageNo' => "1",
                'PageSize' => "100"
            );
        } else if ($action_for == 'groups') {
            $url = $base . 'group/' . $action_for;
            $data = array(
                'LoginSessionKey' => "84e34e499ac6323882bd41f2ca0c0ecf",
                'PageType' => 'dashboard',
                'Begin' => '1',
                'End' => '2',
                'SearchKey' => 'by all',
                'Status' => '', //can be 0 (Closed), 1 (Open)
                'SortBy' => '', //can be 0 (Closed), 1 (Open)
                'OrderBy' => '',
                'GroupID' => '',
                'Filter' => 'Owner'//Can be Owner (Gets all groups owned by me, sorts as per status and sortby if given else returns all public and private groups owned by the user), OnlyPublic (no matter the input of Status and SortBy this will only return public groups of which I am not a part of) and empty string (Returns all groups I am a part of, does not return groups I own, can even filter as per Status and SortBy inputs)
                    //'PageNo' => "1",
                    //'PageSize' => "10"
            );
            //{"PageNo":"1","PageType":"dashboard","Begin":1,"End":2,"SearchKey":"","Status":"","SortBy":"","OrderBy":"","GroupID":"","LoginSessionKey":"8b87f051e2f68de296d07c26b7950d0e"}
        } else if ($action_for == 'deleteGroups') {
            $url = $base . 'group/' . $action_for;
            $data = array(
                'LoginSessionKey' => "84e34e499ac6323882bd41f2ca0c0ecf",
                'GroupID' => '1',
            );
            //{"PageNo":"1","PageType":"dashboard","Begin":1,"End":2,"SearchKey":"","Status":"","SortBy":"","OrderBy":"","GroupID":"","LoginSessionKey":"8b87f051e2f68de296d07c26b7950d0e"}
        } 
        
        else if ($action_for == 'add') {
            $url = $base . 'album/' . $action_for;
            $data = array(
                'AlbumGUID'     => "",
                'AlbumName'     => "youtube videoswwssss not exi66666ssstgg",
                'UserID'        => "81",
                'ModuleID'      => 3,
                'ModuleEntityGUID'=> "c105ed45c381",
                'Description'   => "description",
                'Visibility'    => 1,
                'AlbumType'     => 'PHOTO',
                'MediaID'       => 0,
                'CreatedDate'   => get_current_date('%Y-%m-%d %H:%i:%s'),
                'ModifiedDate'  => get_current_date('%Y-%m-%d %H:%i:%s'),
                'MediaCount'    => 2,
                'IsEditable'    => 1,
                'StatusID'      => 2,
                "Commentable"   => 1,
                'Media'         => array(
                                        array(
                                            "MediaGUID"=>"",
                                            "Caption"=>"youtube1",
                                             "Location"=>array( 
                                                "City" => "Indore",
                                                "Country" => "India",
                                                "CountryCode" => "IN",
                                                "FormattedAddress" => "New Palasia, Old Palasia, Indore, Madhya Pradesh, India",
                                                "Latitude" => 22.7272694,
                                                "Longitude" => 75.88389440000003,
                                                "PostalCode" => "",
                                                "Route" => "",
                                                "State" => "Madhya Pradesh",
                                                "StateCode" => "MP",
                                                "UniqueID" => "dca9c68463ba1ae78c3d5cfcef4aadcf496c015b"
                                                ),
                                            "MediaType"=>"YOUTUBE",
                                            "Url"=> "https://www.youtube.com/watch?v=kclXuc_J50Y",
                                            "Commentable" =>1
                                        ),
                                        array(
                                            "MediaGUID"=>"",
                                            "Caption"=>"youtube2",
                                            "Location"=>array( 
                                                "City" => "Indore",
                                                "Country" => "India",
                                                "CountryCode" => "IN",
                                                "FormattedAddress" => "New Palasia, Old Palasia, Indore, Madhya Pradesh, India",
                                                "Latitude" => 22.7272694,
                                                "Longitude" => 75.88389440000003,
                                                "PostalCode" => "",
                                                "Route" => "",
                                                "State" => "Madhya Pradesh",
                                                "StateCode" => "MP",
                                                "UniqueID" => "dca9c68463ba1ae78c3d5cfcef4aadcf496c015b"
                                                ),
                                            "MediaType"=>"YOUTUBE",
                                            "Url"=> "https://www.youtube.com/watch?v=kclXuc_J50Y",
                                            "Commentable" =>1
                                        )
                                    ),
                 "Location"=>array( 
                    "City" => "Indore",
                    "Country" => "India",
                    "CountryCode" => "IN",
                    "FormattedAddress" => "New Palasia, Old Palasia, Indore, Madhya Pradesh, India",
                    "Latitude" => 22.7272694,
                    "Longitude" => 75.88389440000003,
                    "PostalCode" => "",
                    "Route" => "",
                    "State" => "Madhya Pradesh",
                    "StateCode" => "MP",
                    "UniqueID" => "dca9c68463ba1ae78c3d5cfcef4aadcf496c015b"
                    ),
                'LoginSessionKey' => 'd5acdf92-16c3-1765-5931-c105ed45c381',
            );
        }
        else if ($action_for == 'edit') {
            $url = $base . 'album/' . $action_for;
            $data = array(
                'AlbumGUID'     => "90e56416-5265-e7a6-5ee5-926f6a35ad0c",
                'AlbumName'     => "edit works hehe",
                'UserID'        => "81",
                'ModuleID'      => 3,
                'ModuleEntityGUID'=> "c105ed45c381",
                'Description'   => "description",
                'Visibility'    => 1,
                'AlbumType'     => 'PHOTO',
                'MediaID'       => 0,
                'CreatedDate'   => get_current_date('%Y-%m-%d %H:%i:%s'),
                'ModifiedDate'  => get_current_date('%Y-%m-%d %H:%i:%s'),
                'MediaCount'    => 2,
                'IsEditable'    => 1,
                'StatusID'      => 2,
                "Commentable"   => 1,
                'Media'         => array(
                                        array(
                                            "MediaGUID"=>"",
                                            "Caption"=>"youtube1",
                                            "Location"=>array( 
                                                "City" => "Indore",
                                                "Country" => "India",
                                                "CountryCode" => "IN",
                                                "FormattedAddress" => "New Palasia, Old Palasia, Indore, Madhya Pradesh, India",
                                                "Latitude" => 22.7272694,
                                                "Longitude" => 75.88389440000003,
                                                "PostalCode" => "",
                                                "Route" => "",
                                                "State" => "Madhya Pradesh",
                                                "StateCode" => "MP",
                                                "UniqueID" => "dca9c68463ba1ae78c3d5cfcef4aadcf496c015b"
                                            ),
                                            "MediaType"=>"YOUTUBE",
                                            "Url"=> "https://www.youtube.com/watch?v=kclXuc_J50Y",
                                            "Commentable" =>1
                                        ),
                                        array(
                                            "MediaGUID"=>"9279b649-e396-3bde-779a-866510975b96",
                                            "Caption"=>"other media",
                                            "Location"=>array( 
                                                "City" => "Indore",
                                                "Country" => "India",
                                                "CountryCode" => "IN",
                                                "FormattedAddress" => "New Palasia, Old Palasia, Indore, Madhya Pradesh, India",
                                                "Latitude" => 22.7272694,
                                                "Longitude" => 75.88389440000003,
                                                "PostalCode" => "",
                                                "Route" => "",
                                                "State" => "Madhya Pradesh",
                                                "StateCode" => "MP",
                                                "UniqueID" => "dca9c68463ba1ae78c3d5cfcef4aadcf496c015b"
                                            ),
                                            "MediaType"=>"PHOTO",
                                            "Url"=> "",
                                            "Commentable" =>1
                                        )
                                    ),
                'LoginSessionKey' => 'd5acdf92-16c3-1765-5931-c105ed45c381',
            );
        }
        else if ($action_for == 'add_media') {
            $url = $base . 'album/' . $action_for;
            $data = array(
                'AlbumGUID'     => "90e56416-5265-e7a6-5ee5-926f6a35ad0c",
                'Media'         => array(
                                        array(
                                            "MediaGUID"=>"",
                                            "Caption"=>"youtube1",
                                            "Location"=>array( 
                                                "City" => "Indore",
                                                "Country" => "India",
                                                "CountryCode" => "IN",
                                                "FormattedAddress" => "New Palasia, Old Palasia, Indore, Madhya Pradesh, India",
                                                "Latitude" => 22.7272694,
                                                "Longitude" => 75.88389440000003,
                                                "PostalCode" => "",
                                                "Route" => "",
                                                "State" => "Madhya Pradesh",
                                                "StateCode" => "MP",
                                                "UniqueID" => "dca9c68463ba1ae78c3d5cfcef4aadcf496c015b"
                                            ),
                                            "MediaType"=>"YOUTUBE",
                                            "Url"=> "https://www.youtube.com/watch?v=kclXuc_J50Y",
                                            "Commentable" =>1
                                        )
                                    ),
                'LoginSessionKey' => 'd5acdf92-16c3-1765-5931-c105ed45c381',
            );
        }
        else if ($action_for == 'list') {
            $url = $base . 'album/' . $action_for;
            $data = array(
                'LoginSessionKey' => 'd5acdf92-16c3-1765-5931-c105ed45c381',
                "ModuleID"        => 3,
                "ModuleEntityGUID"=> "6350be1c-553f-5465-19ab-9c68c1744695",
                "PageNo"          => 1,
                "PageSize"        => 9,
                "SortBy"          => "AlbumName",
                "OrderBy"         => "DESC"
            );
        }
        else if ($action_for == 'details') {
            $url = $base . 'album/' . $action_for;
            $data = array(
                'LoginSessionKey'  => 'd5acdf92-16c3-1765-5931-c105ed45c381',
                "AlbumGUID"        => 'eb9af517-91ce-e72e-fcb1-f8543ca79826',
            );
        }
        else if ($action_for == 'list_media') {
            $url = $base . 'album/' . $action_for;
            $data = array(
                'LoginSessionKey'  => 'd5acdf92-16c3-1765-5931-c105ed45c381',
                "AlbumGUID"        => 'eb9af517-91ce-e72e-fcb1-f8543ca79826',
            );
        }
        else if ($action_for == 'update_media_caption') {
            $url = $base . 'album/' . $action_for;
            $data = array(
                'LoginSessionKey'  => 'd5acdf92-16c3-1765-5931-c105ed45c381',
                "MediaGUID"        => '7caf0f74-c457-e0f6-9321-6dc716856f06',
                "AlbumGUID"        => 'eb9af517-91ce-e72e-fcb1-f8543ca79826',
                "Caption"        => 'Youtube1 dfdfdfdfdfdfdfdfdfdf',
                
            );
        }
        else if ($action_for == 'set_cover_media') {
            $url = $base . 'album/' . $action_for;
            $data = array(
                'LoginSessionKey'  => 'd5acdf92-16c3-1765-5931-c105ed45c381',
                "MediaGUID"        => '7caf0f74-c457-e0f6-9321-6dc716856f06',
                "AlbumGUID"        => 'eb9af517-91ce-e72e-fcb1-f8543ca79826',
            
            );
        }

        else {
            $url = 'http://localhost/CommonSocialNetwork/samplejson.txt';
            $url = 'http://103.15.66.183:81/ReusableComponents/api2.php';
            $data = array('serviceName' => 'getAllCategories', 'type' => 'iOS,Android');
            //$data = array('serviceName' => 'getAllProjects','CategoryID' => 230, 'Keyword' => 'Email', 'type' => 'iOS,Android');
        }

        //echo $url;


        $fields_string = json_encode($data);
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        $headers = array('Accept: application/json', 'Content-Type: application/json');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); # timeout after 10 seconds, you can increase it
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);  # Set curl to return the data instead of printing it to the browser.
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)"); # Some server may refuse your request if you dont pass user agent
        $result = curl_exec($ch);
        curl_close($ch);
        //print_r(json_decode(utf8_encode(preg_replace("/[\r\n]+/", " ",$result))));
        print_r(json_decode($result, true));
        die;
    }
    
    /*
    function updateUserPrivacySettings() {
        $this->load->model(array('login_model'));
        $userData = $this->login_model->fetchData(USERS,array(),array('UserID'));
        foreach ($userData as $key => $value) {
            
            $this->db->insert_batch(PRIVACYSETTINGS,array(
                array('user_id'=>$value['UserID'],'key'=>'saves','value'=>0),
                array('user_id'=>$value['UserID'],'key'=>'comments','value'=>0),
                array('user_id'=>$value['UserID'],'key'=>'wardrobe','value'=>0),
                array('user_id'=>$value['UserID'],'key'=>'style_notes','value'=>0)
            ));
        }
        echo 'updated';die;
    }
    
    function addItemCategory(){
        $itemCat = array(
            'Women'=>array(
                'Clothing'=>array(
                    'Activewear',
                    'Bridal',
                    'Denim',
                    'Dresses',
                    'Intimates',
                    'Jackets',
                    'Jewelry',
                    'Maternity',
                    'Outerwear',
                    'Pants',
                    'Petite Clothing',
                    'Plus Sizes',
                    'Shorts',
                    'Skirts',
                    'Suits',
                    'Sweaters',
                    'Swimwear',
                    'Tops',
                    'T-shirts',
                    'Sweats & Hoodies'
                ),
                'Accessories'=>array(
                    'Earrings',
                    'Eyewear',
                    'Jewelry',
                    'Watches',
                    'Fragrances'
                ),
                'Handbags'=>array(
                    'Backpacks',
                    'Clutches',
                    'Evening',
                    'Hobos',
                    'Satchels',
                    'Shoulder',
                    'Duffel & Totes',
                    'Wallets'
                ),
                'Shoes'=>array(
                    'Athletic',
                    'Boots',
                    'Flats',
                    'Mules & Clogs',
                    'Platforms',
                    'Pumps',
                    'Sandals',
                    'Sneakers',
                    'Wedges',
                    'Watches'
                )
            ),
            'Men'=>array(
                'Clothing'=>array(
                    'Blazers',
                    'Boots',
                    'Denim',
                    'Hats',
                    'Hoodies',
                    'Jackets',
                    'Pants',
                    'Polos',
                    'Shirts',
                    'Shorts',
                    'Sneakers',
                    'Suits',
                    'Sweaters',
                    'Swimwear',
                    'T-shirts',
                    'Tanktops',
                    'Underwear',
                    'Vests'
                ),
                'Shoes'=>array(
                    'Athletic',
                    'Boots',
                    'Sandals',
                    'Slip-ons & Loafers',
                    'Sneakers'
                ),
                'Accessories'=>array(
                    'Bags',
                    'Belts',
                    'Eyewear',
                    'Fragrances',
                    'Jewelry',
                    'Scarves',
                    'Wallets',
                    'Watches'
                )
                 
            ),
            'Kids'=>array(
                'Boys'=>array(),
                'Girls'=>array()
            )
        );
        $insertArray = array();
        $this->load->model(array('login_model'));
        foreach ($itemCat as $zeroLevelCatName => $levelZero) {
           
            $levelZeroID = $this->login_model->insertData(ITEM_MASTER_CATEGORY,
                    array('ItemMasterCategoryParentID'=>0,'ItemMasterCategoryLevel'=>0,'Name'=>$zeroLevelCatName,'ItemMasterCategoryGuID'=>uniqid())
                    );
            
           
            foreach ($levelZero as $firstLevelCatName => $levelFirst){
                $levelFirstID = $this->login_model->insertData(ITEM_MASTER_CATEGORY,
                       array('ItemMasterCategoryParentID'=>$levelZeroID,'ItemMasterCategoryLevel'=>1,'Name'=>$firstLevelCatName,'ItemMasterCategoryGuID'=>uniqid())
                       );
                $insertArray = array();
                foreach($levelFirst as $key => $secondLevelCatName){
                    $insertArray[] = array('ItemMasterCategoryParentID'=>$levelFirstID,'ItemMasterCategoryLevel'=>2,'Name'=>$secondLevelCatName,'ItemMasterCategoryGuID'=>uniqid());
                }
                
                if(!empty($insertArray)){
                    $this->db->insert_batch(ITEM_MASTER_CATEGORY,$insertArray);
                }
                
                
            }
           
           
        }
        
        //print_r($itemCat);
        die('done');
        
        
        
    }
    
    function uploadBrand(){
        $array = explode("\n", file_get_contents('/var/www/CommonSocialNetwork/upload/brandMaster.txt'));
        $insertArray = array();
        foreach ($array as $key=>$val){
            if($val!=''){
                $insertArray[] = array('BrandName'=>$val);
            }
        }
        
        if(!empty($insertArray)){
            $this->db->insert_batch(BRAND_MASTER,$insertArray);
        }
        
        echo 'done';die;
    }*/
    
    

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
