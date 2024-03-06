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
* definition="Inclusify\Api\Activity_Hide\index", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* @SWG\Property(property="Status", type="integer", description="hide status, 1 - Hide, 2 - Unhide", example="1"),
* ) 

*/


/**
    * @SWG\Post(path="/activity_hide/index",
    *   tags={"Activity Hide"},
    *   summary="Used to hide activity from user news feed",
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
    *     description="This api is used to set the activity title",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Hide\index")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

