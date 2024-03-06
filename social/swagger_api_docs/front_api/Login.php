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
* definition="Inclusify\Api\Login\Index", 
* required={""},
* @SWG\Property(property="DeviceType", type="string", description="Native", example="Native"),
* @SWG\Property(property="Mobile", type="string", description="Mobile Number", example="9827298272"),
* @SWG\Property(property="Password", type="string", description="Password", example="Vtech#2012"),
* @SWG\Property(property="IsDevice", type="integer", description="Is Mobile App or web: 1 for App, 0 for web", example="1"),
* @SWG\Property(property="DeviceID", type="string", description="if IsDevice value is 1 then it is required field"),
* @SWG\Property(property="DeviceInfo", type="array", description="Device Info, array of Device Info",@SWG\Items(
*     type="object",
*     @SWG\Property(property="manufacturer", type="string",example="LGE"),
*     @SWG\Property(property="model", type="string",example="Nexus 5X"),
*     @SWG\Property(property="deviceName", type="string",example="Nexus 5X"),
*     @SWG\Property(property="version", type="string",example="6.0.1"),
*     @SWG\Property(property="version_name", type="string",example="LOLLIPOP_MR1"),
*     )
*   )
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Login\Send_otp", 
* required={""},
* @SWG\Property(property="Mobile", type="string", description="Mobile Number", example="9827298272")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Login\Validate_otp", 
* required={""},
* @SWG\Property(property="OTP", type="string", description="OTP Number", example="982729")
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Login\Check_apk_ver", 
* required={""},
* @SWG\Property(property="current_ver", type="string", description="Current APP version", example="1.0"),
* @SWG\Property(property="device_type", type="string", description="Device Type: 1 for Android, 2 for IOS", example="1")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Login\Logout", 
* required={""}
* ) 
*/



/**
* @SWG\Definition(
* definition="Inclusify\Api\Login\Master_data", 
* required={""}
* ) 
*/




/**
* @SWG\Post(path="/login/index",
*   tags={"Login"},
*   summary="This api is used to login user.",
*   description="",
*   operationId="index",
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
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Login\Index")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/login/send_otp",
*   tags={"OTP"},
*   summary="This api is used to send OTP on mobile.",
*   description="",
*   operationId="send_otp",
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
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Login\Send_otp")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/login/validate_otp",
*   tags={"OTP"},
*   summary="This api is used to validate otp.",
*   description="",
*   operationId="validate_otp",
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
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Login\Validate_otp")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/login/check_apk_ver",
*   tags={"Check APP Version"},
*   summary="This api is used to check app version details.",
*   description="",
*   operationId="check_apk_ver",
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
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Login\Check_apk_ver")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/login/logout",
*   tags={"Logout"},
*   summary="This api is used to logout user from application.",
*   description="",
*   operationId="logout",
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
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Login\Logout")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/login/master_data",
*   tags={"Master Data"},
*   summary="This api is used to get master data.",
*   description="",
*   operationId="master_data",
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
*     description=".",
*     required=false,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Login\Master_data")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/