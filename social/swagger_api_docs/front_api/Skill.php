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
* definition="Inclusify\Api\Skills\index",
* required={""}
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Skills\save",
* required={"Skills"},
* @SWG\Property(property="Skills", type="array", description="Skills, array of skills ID", @SWG\Items(type="integer",example=1))
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Skills\details",
* required={""},
* @SWG\Property(property="ModuleID", type="integer", description="Module ID", example="3"),
* @SWG\Property(property="ModuleEntityGUID", type="string", description="Module Entity GUID", example="a19be4f4-c7ea-e337-deee-22902d154962")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Skills\save_endorsement",
* required={"EntitySkillID"},
* @SWG\Property(property="EntitySkillID", type="integer", description="Entity Skill ID", example="3")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Skills\delete_endorsement",
* required={"EntitySkillID"},
* @SWG\Property(property="EntitySkillID", type="integer", description="Entity Skill ID", example="3")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Skills\endorsement_list",
* required={"EntitySkillID"},
* @SWG\Property(property="EntitySkillID", type="integer", description="Entity Skill ID", example="3"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10")
* )
*/

/**
* @SWG\Post(path="/skills/index",
*   tags={"Skill"},
*   summary="This api is used to get skills.",
*   description="This api is used to get skills.",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Skills\index")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/skills/save",
*   tags={"Skill"},
*   summary="This api is used to insert/update user skills.",
*   description="This api is used to insert/update user skills.",
*   operationId="save",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Skills\save")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/skills/details",
*   tags={"Skill"},
*   summary="This api is used to get user profile skills.",
*   description="This api is used to get user profile skills.",
*   operationId="details",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Skills\details")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/skills/save_endorsement",
*   tags={"Endorsement"},
*   summary="This api is used to endorse user profile skill.",
*   description="This api is used to endorse user profile skill.",
*   operationId="save_endorsement",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Skills\save_endorsement")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/skills/delete_endorsement",
*   tags={"Endorsement"},
*   summary="This api is used to remove endorsement from user profile skill.",
*   description="This api is used to remove endorsement from user profile skill.",
*   operationId="delete_endorsement",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Skills\delete_endorsement")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/skills/endorsement_list",
*   tags={"Endorsement"},
*   summary="This api used to get users list, who are endorsed another user for profile skills..",
*   description="This api used to get users list, who are endorsed another user for profile skills..",
*   operationId="endorsement_list",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Skills\endorsement_list")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/