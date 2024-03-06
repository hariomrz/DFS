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
* definition="Inclusify\Api\Users\update_profile", 
* required={},
* @SWG\Property(property="FullName", type="string", description="User name", example="Suresh Patidar"),
* @SWG\Property(property="Address", type="string", description="Address", example="31/2 OLD PALASIA"),
* @SWG\Property(property="Occupation", type="string", description="User Occupation", example="Designer"),
* @SWG\Property(property="HouseNumber", type="string", description="House Number", example=""),
* @SWG\Property(property="AboutMe", type="string", description="About user", example=""),
* @SWG\Property(property="ProfessionID", type="integer", description="Profession ID", example="1"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Users\save_user_info", 
* required={},
* @SWG\Property(property="UserGUID", type="string", description="User GUID", example="8919a0c7-993c-0064-250f-375882f305eb"),
* @SWG\Property(property="Gender", type="integer", description="Gender value, it may be 1 - Male, 2 - Female", example="1"),
* @SWG\Property(property="IncomeLevel", type="integer", description="User Income Level, it may be 1 - Low, 2 - Medium, 3 - High", example="1"),
* @SWG\Property(property="DOB", type="string", description="User date of birth", example="2000-09-14"),
* @SWG\Property(property="IsDOBApprox", type="integer", description="Is DOB Approx, it may be 0 - No, 1 - Yes", example="1"),
* )
*/

/**
    * @SWG\Post(path="/users/update_profile",
    *   tags={"Update Profile"},
    *   summary="This api is used to update user profile details.",
    *   description="",
    *   operationId="update_profile",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\update_profile")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


    /**
    * @SWG\Post(path="/users/save_user_info",
    *   tags={"Update Profile"},
    *   summary="This api is used to update user details.",
    *   description="",
    *   operationId="save_user_info",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Users\save_user_info")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */