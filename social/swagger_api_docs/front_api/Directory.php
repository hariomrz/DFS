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
* definition="Inclusify\Api\Users\Directory", 
* required={""},
* @SWG\Property(property="WID", type="integer", description="Ward ID", example="1"),
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="string", description="PageSize", example="10"),
* @SWG\Property(property="Keyword", type="string", description="Keyword", example="ram"),
* @SWG\Property(property="OrderBy", type="string", description="Name or Recent", example="Recent"),
* @SWG\Property(property="SortBy", type="string", description="ASC or DESC", example="DESC")
* ) 

*/


/**
* @SWG\Post(path="/users/directory",
*   tags={"Tags Section"},
*   summary="This api is used to get user directory.",
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
*     name="Accept-Language",
*     in="header",
*     description="language en or hi.",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\Directory")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/




/**
* @SWG\Definition(
* definition="Inclusify\Api\Category\Utility", 
* required={"ModuleID"},
* @SWG\Property(property="ModuleID", type="integer", description="Module ID", example="45"),
* @SWG\Property(property="OrderBy", type="string", description="Name", example="Name"),
* @SWG\Property(property="SortBy", type="string", description="ASC or DESC", example="ASC")
* ) 

*/


/**
* @SWG\Post(path="/category/utility",
*   tags={"Utility Section"},
*   summary="This api is used to get utility directory.",
*   description="",
*   operationId="utility",
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
*     name="Accept-Language",
*     in="header",
*     description="language en or hi.",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Category\Utility")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/




