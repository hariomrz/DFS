
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
* definition="Inclusify\AdminApi\Users\user_search", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page no", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page size", example="10"),
* @SWG\Property(property="SearchKeyword", type="string", description="Search Keyword", example="su"),
* ) 
*/



/**
    * @SWG\Post(path="/users/user_search",
    *   tags={"User Search"},
    *   summary="This api is used to search user",
    *   description="This api is used to search user",
    *   operationId="user_search",
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
    *     description="The Admin Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="This api is used to search user.",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Users\user_search")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

