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
* definition="Inclusify\Api\Interest\index",
* required={""}
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Interest\update_user_interest",
* required={"InterestIDS"},
* @SWG\Property(property="InterestIDS", type="array", description="Interest ID", @SWG\Items(type="integer",example=1))
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Interest\get_user_interest",
* required={""},
* @SWG\Property(property="UserGUID", type="string", description="User GUID", example="1475e5a3-2cca-bedb-b560-74a2fd6a6757")
* )
*/


/**
* @SWG\Post(path="/interest/index",
*   tags={"Interest"},
*   summary="This api is used to get Interest list.",
*   description="This api is used to get Interest list",
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
*     name="Loginsessionkey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Interest\index")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/interest/update_user_interest",
*   tags={"Interest"},
*   summary="This api is used to insert/update user Interest.",
*   description="This api is used to insert/update user Interest.",
*   operationId="update_user_interest",
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
*     name="Loginsessionkey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Interest\update_user_interest")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/interest/get_user_interest",
*   tags={"Interest"},
*   summary="This api is used to get user interest.",
*   description="This api is used to get user interest.",
*   operationId="get_user_interest",
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
*     name="Loginsessionkey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Interest\get_user_interest")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/