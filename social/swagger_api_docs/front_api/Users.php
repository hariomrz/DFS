<?php 

/**
* @SWG\Swagger(
*     schemes={"http"},
*     host="localhost/inclusify",
*     basePath="/api",
*     @SWG\Info(
*         version="1.0.0",
*         title="Inclusify API",
*         description="This is a Inclusify API api server."
*     )
* )
*/




/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\change_status", 
* required={""},
* @SWG\Property(property="UserID", type="integer", description="UserID", example="2"),
* @SWG\Property(property="Status", type="integer", description="Status", example="4"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\make_admin", 
* required={""},
* @SWG\Property(property="UserID", type="integer", description="UserID", example="2")
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\remove_admin", 
* required={""},
* @SWG\Property(property="UserID", type="integer", description="UserID", example="2")
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Recovery_password\forgot_password", 
* required={""},
* @SWG\Property(property="Type", type="string", description="Type may be Email or Mobile", example="Mobile"),
* @SWG\Property(property="Value", type="string", description="Value", example="9827298272")
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Recovery_password\validate_forgot_password_token", 
* required={""},
* @SWG\Property(property="OTP", type="string", description="Forgot password token", example="982729")
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Recovery_password\set_password", 
* required={""},
* @SWG\Property(property="OTP", type="string", description="Forgot password token", example="982729"),
* @SWG\Property(property="Password", type="string", description="New password", example="98272956"),
* @SWG\Property(property="Type", type="string", description="Mobile or Email", example="Mobile")
* )

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Change_password\index", 
* required={""},
* @SWG\Property(property="PasswordNew", type="string", description="New password", example="9827294567"),
* @SWG\Property(property="Password", type="string", description="Old password", example="98272956")
* )

*/

/** 
* @SWG\Definition(
* definition="Inclusify\Api\Users\remove_profile_picture", 
* required={""},
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\change_locality", 
* required={"LocalityID"},
* @SWG\Property(property="LocalityID", type="integer", description="New Locality", example=2)
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\get_association_user", 
* required={""},
* @SWG\Property(property="OrderBy", type="string", description="Name or Activity", example="Name"),
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\get_vip_user", 
* required={""},
* @SWG\Property(property="OrderBy", type="string", description="Name or Activity", example="Name"),
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\toggle_block_user", 
* required={"UserGUID"},
* @SWG\Property(property="UserGUID", type="string", description="User GUID", example="9d6937c6-5350-ee50-0670-2e3d9d84ab51"),
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\blocked_user_list", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page size", example="20"),
* @SWG\Property(property="SearchKeyword", type="string", description="Search Keyword", example="su"),
* ) 
*/

/** 
* @SWG\Definition(
* definition="Inclusify\Api\Users\list", 
* required={""},
* @SWG\Property(property="Type", type="string", description="values:NewsFeedTagging", example="NewsFeedTagging"),
* @SWG\Property(property="ModuleID", type="string", description="Group=1,Page=18,user=3", example="3"),
* @SWG\Property(property="ModuleEntityID", type="string", description="UID", example="9d6937c6-5350-ee50-0670-2e3d9d84ab51"),
* @SWG\Property(property="SearchKey", type="string", description="search string", example="test"),
* @SWG\Property(property="WID", type="integer", description="Ward ID", example="1"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\get_preferred_category", 
* required={""},
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\save_preferred_categories", 
* required={"CategoryIDs"},
* @SWG\Property(property="CategoryIDs", type="array", description="Array of Category Ids", @SWG\Items(type="integer",example=1)),
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\profession_list", 
* required={""},
* ) 
*/

/**
* @SWG\Post(path="/users/list",
*   tags={"User List for @Tagging"},
*   summary="This api is used to getting list of users for tagging.",
*   description="",
*   operationId="list",
*   produces={"application/json"},
*   consumes={"application/json"},
*   @SWG\Parameter(
*     name="APPVERSION",
*     in="header",
*     description="API Version, Current value is v3.",
*     required=false,
*     type="string"
*   ),
*   @SWG\Parameter(
*     name="LoginSessionKey",
*     in="header",
*     description="The Login Session Key of logged in user.",
*     required=true,
*     type="string"
*   ),
 * @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\list")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/users/change_status",
*   tags={"User"},
*   summary="This api is used to update user status.",
*   description="",
*   operationId="change_status",
*   produces={"application/json"},
*   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
*   @SWG\Parameter(
*     name="LoginSessionKey",
*     in="header",
*     description="The Login Session Key of logged in user.",
*     required=true,
*     type="string"
*   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description="Status value may be 3 (for delete), 4 (for block).",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\change_status")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/users/make_admin",
*   tags={"User"},
*   summary="This api is used to make any user as sub admin.",
*   description="",
*   operationId="make_admin",
*   produces={"application/json"},
*   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
*   @SWG\Parameter(
*     name="LoginSessionKey",
*     in="header",
*     description="The Login Session Key of logged in user.",
*     required=true,
*     type="string"
*   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\make_admin")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/users/remove_admin",
*   tags={"User"},
*   summary="This api is used to remove admin rights for user.",
*   description="",
*   operationId="remove_admin",
*   produces={"application/json"},
*   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
*   @SWG\Parameter(
*     name="LoginSessionKey",
*     in="header",
*     description="The Login Session Key of logged in user.",
*     required=true,
*     type="string"
*   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\remove_admin")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/recovery_password/forgot_password",
*   tags={"User"},
*   summary="This api is used forgot password..",
*   description="",
*   operationId="forgot_password",
*   produces={"application/json"},
*   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Recovery_password\forgot_password")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/recovery_password/validate_forgot_password_token",
*   tags={"User"},
*   summary="This api is used validate forgot password token.",
*   description="",
*   operationId="validate_forgot_password_token",
*   produces={"application/json"},
*   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Recovery_password\validate_forgot_password_token")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/recovery_password/set_password",
*   tags={"User"},
*   summary="This api is used to set new password based on forgot password token.",
*   description="",
*   operationId="set_password",
*   produces={"application/json"},
*   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Recovery_password\set_password")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/change_password/index",
*   tags={"User"},
*   summary="This api is used to change user password.",
*   description="",
*   operationId="change_password",
*   produces={"application/json"},
*   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Change_password\index")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/users/remove_profile_picture",
*   tags={"User"},
*   summary="This api is used to remove profile picture.",
*   description="",
*   operationId="remove_profile_picture",
*   produces={"application/json"},
*   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
*   @SWG\Parameter(
*     name="LoginSessionKey",
*     in="header",
*     description="The Login Session Key of logged in user.",
*     required=true,
*     type="string"
*   ),
 * @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\remove_profile_picture")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/users/change_locality",
*   tags={"Change Locality"},
*   summary="This api is used to change user locality.",
*   description="",
*   operationId="change_locality",
*   produces={"application/json"},
*   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\change_locality")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
*/

/**
    * @SWG\Post(path="/users/get_association_user",
    *   tags={"Association User"},
    *   summary="Association user list",
    *   description="",
    *   operationId="get_association_user",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description=".",
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\get_association_user")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/users/get_vip_user",
    *   tags={"VIP User"},
    *   summary="VIP user list",
    *   description="",
    *   operationId="get_vip_user",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description=".",
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\get_vip_user")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/users/toggle_block_user",
    *   tags={"Block User"},
    *   summary="Used to block/unblock user",
    *   description="",
    *   operationId="toggle_block_user",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description=".",
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\toggle_block_user")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/users/blocked_user_list",
    *   tags={"Block User"},
    *   summary="Used to get block user list",
    *   description="",
    *   operationId="blocked_user_list",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description=".",
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\blocked_user_list")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/users/get_preferred_category",
    *   tags={"Preferred Category"},
    *   summary="Used to get user preferred category list",
    *   description="",
    *   operationId="get_preferred_category",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description=".",
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\get_preferred_category")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/users/save_preferred_categories",
    *   tags={"Preferred Category"},
    *   summary="Used to save user preferred categories",
    *   description="",
    *   operationId="save_preferred_categories",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description=".",
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\save_preferred_categories")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


    /**
    * @SWG\Post(path="/users/profession_list",
    *   tags={"Profession List"},
    *   summary="Used to get user profession list",
    *   description="",
    *   operationId="profession_list",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=false,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description=".",
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\profession_list")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */