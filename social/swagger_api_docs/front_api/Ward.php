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
* definition="Inclusify\Api\Ward\list", 
* required={""}
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Ward\get_trending_ward_list", 
* required={""}
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Ward\get_featured_user", 
* required={"WID"},
* @SWG\Property(property="WID", type="integer", description="Ward ID", example="3"),
* @SWG\Property(property="OrderBy", type="string", description="Name or Activity", example="Name"),
* ) 

*/

/**
    * @SWG\Post(path="/ward/list",
    *   tags={"Ward"},
    *   summary="Ward List",
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
    *     name="body",
    *     in="body",
    *     description=".",
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Ward\list")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/ward/get_trending_ward_list",
    *   tags={"Ward"},
    *   summary="Trending ward list",
    *   description="",
    *   operationId="get_trending_ward_list",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Ward\get_trending_ward_list")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
    * @SWG\Post(path="/ward/get_featured_user",
    *   tags={"Ward Featured User"},
    *   summary="Featured user list",
    *   description="",
    *   operationId="get_featured_user",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Ward\get_featured_user")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */