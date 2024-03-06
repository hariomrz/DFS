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
* definition="Inclusify\Api\Comment\admin_tool", 
* required={"CommentGUID"},
* @SWG\Property(property="CommentGUID", type="string", description="Comment GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* ) 

*/


/**
    * @SWG\Post(path="/comment/admin_tool",
    *   tags={"Comment Admin Tool"},
    *   summary="This api used to get admin tool setting for particular comment",
    *   description="This api used to get admin tool setting for particular comment",
    *   operationId="admin_tool",
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
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="This api used to get admin tool setting for particular comment",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Comment\admin_tool")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
* @SWG\Definition(
* definition="Inclusify\Api\Comment\toggle_amazing", 
* required={"CommentGUID"},
* @SWG\Property(property="CommentGUID", type="string", description="Comment GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* @SWG\Property(property="IsAmazing", type="integer", description="IsAmazing, It may be 0 - No, 1 - Yes", example="1"),
* ) 
*/


/**
    * @SWG\Post(path="/comment/toggle_amazing",
    *   tags={"Amazing Comment"},
    *   summary="This api is used to update is amazing flag for an comment.",
    *   description="",
    *   operationId="toggle_amazing",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=true,
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
    *     description="This api is used to update is amazing flag for an comment",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Comment\toggle_amazing")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Comment\amazing", 
* required={},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* ) 
*/

    /**
    * @SWG\Post(path="/comment/amazing",
    *   tags={"Amazing Comment"},
    *   summary="Used to get amazing comments",
    *   description="",
    *   operationId="amazing",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=true,
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Comment\amazing")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */
