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
* definition="Inclusify\Api\Announcement\index", 
* required={}
* ) 
*/

/**
    * @SWG\Post(path="/announcement/index",
    *   tags={"Announcement"},
    *   summary="announcement details",
    *   description="",
    *   operationId="index",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
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
    *     description="This api is used to get announcement details",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Announcement\index")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Announcement\ignore", 
* required={"BlogGUID"},
* @SWG\Property(property="BlogGUID", type="string", description="Announcement GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* ) 

*/


/**
    * @SWG\Post(path="/announcement/ignore",
    *   tags={"Announcement"},
    *   summary="Ignore announcement",
    *   description="",
    *   operationId="ignore",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
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
    *     description="This api is used to ignore announcement",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Announcement\ignore")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */