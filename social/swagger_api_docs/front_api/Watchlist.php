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
* definition="Inclusify\Api\Watchlist\Toggle_watchlist", 
* required={""},
* @SWG\Property(property="ActivityGUID", type="string", description="ActivityGUID of task", example=""),
* ) 

*/

/**
    * @SWG\Post(path="/watchlist/toggle_watchlist",
    *   tags={"watchlist toggle"},
    *   summary="Activity API",
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
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Watchlist\Toggle_watchlist")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */



