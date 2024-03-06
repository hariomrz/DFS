<?php 

/**
* @SWG\Swagger(
*     schemes={"http"},
*     host="localhost/inclusify",
*     basePath="/admin_api",
*     @SWG\Info(
*         version="1.0.0",
*         title="Inclusify API",
*         description="This is a Inclusify API api server."
*     )
* )
*/




/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Login\Index", 
* required={"Username","Password"},
* @SWG\Property(property="Username", type="string", description="User name", example="9827298272"),
* @SWG\Property(property="Password", type="string", description="Password", example="Vtech#2012"),
* )
*/



/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Login\Logout", 
* required={""}
* ) 
*/


/**
* @SWG\Post(path="/login/index",
*   tags={"Login"},
*   summary="This api is used to login.",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Login\Index")
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
    *     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Login\Logout")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/