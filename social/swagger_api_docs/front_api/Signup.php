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
* definition="Inclusify\Api\Signup\Index", 
* required={""},
* @SWG\Property(property="FullName", type="string", description="User name", example="Suresh Patidar"),
* @SWG\Property(property="DeviceType", type="string", description="Device Type: IPhone or AndroidPhone", example="AndroidPhone"),
* @SWG\Property(property="AppVersion", type="string", description="App Version", example="1.0"),
* @SWG\Property(property="Mobile", type="string", description="Mobile Number", example="9827298272"),
* @SWG\Property(property="Password", type="string", description="Password", example="Vtech#2012"),
* @SWG\Property(property="IsDevice", type="integer", description="Is Mobile App or web: 1 for App, 0 for web", example="1"),
* @SWG\Property(property="DeviceID", type="string", description="if IsDevice value is 1 then it is required field"), 
* @SWG\Property(property="HouseNumber", type="string", description="House Number", example="")
* ) 
*/


/**
* @SWG\Post(path="/signup/index",
*   tags={"Signup"},
*   summary="This api is used to signup user.",
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
*     name="LocalityID",
*     in="header",
*     description="The Locality ID.",
*     required=true,
*     type="integer"
*   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Signup\Index")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Signup\check_mobile_exist", 
* required={"Mobile"},
* @SWG\Property(property="Mobile", type="string", description="Mobile Number", example="9827298272")
* ) 
*/

/**
* @SWG\Post(path="/signup/check_mobile_exist",
*   tags={"Signup"},
*   summary="This api is used to check given mobile number exist or not.",
*   description="",
*   operationId="check_mobile_exist",
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
*     name="LocalityID",
*     in="header",
*     description="The Locality ID.",
*     required=true,
*     type="integer"
*   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Signup\check_mobile_exist")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Definition(
* definition="Inclusify\Api\Signup\add_analytics", 
* required={""},
* @SWG\Property(property="Latitude", type="string", description="latitude", example="22.22"),
* @SWG\Property(property="Longitude", type="string", description="longitude", example="24.32"),
* @SWG\Property(property="SessionID", type="string", description="session id, which is return by server ", example="asfsd-dg-dfhgf-jdf"),
* @SWG\Property(property="IPAddress", type="string", description="User IP address", example="192.168.0.12"),
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
* @SWG\Post(path="/signup/add_analytics",
*   tags={"Signup"},
*   summary="This api is used to add analytics.",
*   description="",
*   operationId="add_analytics",
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
*     name="LocalityID",
*     in="header",
*     description="The Locality ID.",
*     required=true,
*     type="integer"
*   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Signup\add_analytics")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/