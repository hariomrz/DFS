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
* definition="Inclusify\Api\Locality\index", 
* required={""},
* ) 

*/

/**
    * @SWG\Get(path="/locality/index",
    *   tags={"Locality"},
    *   summary="locality API",
    *   description="",
    *   operationId="index",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Locality\list", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="string", description="PageSize", example="10"),
* @SWG\Property(property="Keyword", type="string", description="Keyword", example="ram")
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Locality\ward_user_count", 
* required={""},
* @SWG\Property(property="WID", type="integer", description="Ward ID", example="1"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Locality\add_locality", 
* required={"Name","WID"},
* @SWG\Property(property="WID", type="integer", description="Ward ID", example="1"),
* @SWG\Property(property="Name", type="string", description="Locality Name", example="abc"),
* ) 

*/

/**
    * @SWG\Post(path="/locality/list",
    *   tags={"Locality"},
    *   summary="locality API",
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
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Locality\list")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/locality/ward_user_count",
    *   tags={"Ward User Count"},
    *   summary="Ward User Count",
    *   description="",
    *   operationId="ward_user_count",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Locality\ward_user_count")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/locality/add_locality",
    *   tags={"Add Locality"},
    *   summary="Add Suggested Locality",
    *   description="",
    *   operationId="add_locality",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Locality\add_locality")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */
