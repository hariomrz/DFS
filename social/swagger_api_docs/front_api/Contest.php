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
* definition="Inclusify\Api\contest\add_participant", 
* required={""},
* @SWG\Property(property="ActivityID", type="string", description="ActivityID", example="")
* ) 
*/

/**
* @SWG\Post(path="/contest/add_participant",
*   tags={"add_participant"},
*   summary="add_participant API",
*   description="",
*   operationId="add_participant",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\contest\add_participant")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/



