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
* definition="Inclusify\Api\Search\index",
* required={"Keyword"},
* @SWG\Property(property="Keyword", type="string", description="Search Keyword", example="bh")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Search\user",
* required={},
* @SWG\Property(property="Keyword", type="string", description="Search Keyword", example="bh"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="SkillID", type="integer", description="Skill ID", example="3"),
* @SWG\Property(property="InterestID", type="integer", description="Interest ID", example="3"),
* @SWG\Property(property="WID", type="integer", description="Ward ID", example="3")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Search\profession",
* required={},
* @SWG\Property(property="Keyword", type="string", description="Search Keyword", example="bh")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Search\profession_users",
* required={"ProfessionID"},
* @SWG\Property(property="Keyword", type="string", description="Search Keyword", example="bh"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="ProfessionID", type="integer", description="Profession ID", example="3")
* )
*/


/**
* @SWG\Post(path="/search/index",
*   tags={"Search"},
*   summary="This api is used to get search skills & interest.",
*   description="This api is used to get search skills & interest.",
*   operationId="Index",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Search\index")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/search/user",
*   tags={"Search"},
*   summary="This api is used to search user's based on skill or interest or keyword.",
*   description="This api is used to search user's based on skill or interest or keyword.",
*   operationId="user",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Search\user")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/search/profession",
*   tags={"Profession"},
*   summary="This api is used to get user list profession wise.",
*   description="This api is used to get user list profession wise.",
*   operationId="profession",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Search\profession")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/search/profession_users",
*   tags={"Profession"},
*   summary="This api is used to get user list based on profession.",
*   description="This api is used to get user list based on profession.",
*   operationId="profession_users",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Search\profession_users")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/