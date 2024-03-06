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
* definition="Inclusify\Api\Follow\index", 
* required={"UserGUID"},
* @SWG\Property(property="UserGUID", type="string", description="User GUID", example="9d6937c6-5350-ee50-0670-2e3d9d84ab51"),
* ) 

*/

/**
* @SWG\Post(path="/follow/index",
*   tags={"Follow / Un-follow User"},
*   summary="This api is used to Follow / Un-Follow user.",
*   description="",
*   operationId="index",
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
 * @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Follow\index")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Follow\following", 
* required={"UserGUID"},
* @SWG\Property(property="UserGUID", type="integer", description="User GUID", example="9d6937c6-5350-ee50-0670-2e3d9d84ab51"),
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="string", description="PageSize", example="10"),
* @SWG\Property(property="Keyword", type="string", description="Keyword", example="ram"),
* @SWG\Property(property="OrderBy", type="string", description="Name or Recent", example="Recent"),
* @SWG\Property(property="SortBy", type="string", description="ASC or DESC", example="DESC")
* ) 

*/


/**
* @SWG\Post(path="/follow/following",
*   tags={"Following"},
*   summary="This api is used to get user following user list.",
*   description="",
*   operationId="following",
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
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Follow\following")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Follow\followers", 
* required={"UserGUID"},
* @SWG\Property(property="UserGUID", type="integer", description="User GUID", example="9d6937c6-5350-ee50-0670-2e3d9d84ab51"),
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="string", description="PageSize", example="10"),
* @SWG\Property(property="Keyword", type="string", description="Keyword", example="ram"),
* @SWG\Property(property="OrderBy", type="string", description="Name or Recent", example="Recent"),
* @SWG\Property(property="SortBy", type="string", description="ASC or DESC", example="DESC")
* ) 

*/


/**
* @SWG\Post(path="/follow/followers",
*   tags={"Followers"},
*   summary="This api is used to get user followers list.",
*   description="",
*   operationId="followers",
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
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Follow\followers")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Definition(
* definition="Inclusify\Api\Follow\suggestion", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="PageSize", example="10"),
* @SWG\Property(property="Type", type="integer", description="Type of suggestion, 0 For Top Contributors, 1 For VIP, 1 For Association", example="1")
* ) 

*/


/**
* @SWG\Post(path="/follow/suggestion",
*   tags={"Follow Suggestion"},
*   summary="This api is used to get user suggestion for follow.",
*   description="",
*   operationId="suggestion",
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
*     description=".",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Follow\suggestion")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/