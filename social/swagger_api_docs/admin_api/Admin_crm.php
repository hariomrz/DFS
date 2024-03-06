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
* definition="Inclusify\AdminApi\AdminCrm\GetUsers", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="PageSize", example="20"),
* @SWG\Property(property="OrderByField", type="string", description="OrderByField", example="U.UserID"),
* @SWG\Property(property="OrderBy", type="string", description="OrderBy", example="DESC"),
* @SWG\Property(property="CityID", type="integer", description="CityID", example="0"),
* @SWG\Property(property="AgeGroupID", type="integer", description="AgeGroupID", example="0"),
* @SWG\Property(property="Gender", type="integer", description="Gender", example="0"),
* @SWG\Property(property="SearchKey", type="string", description="SearchKey", example=""),
* @SWG\Property(property="TagUserType", type="string", description="TagUserType", example={}),
* @SWG\Property(property="TagUserSearchType", type="integer", description="TagUserSearchType", example="0"),
* @SWG\Property(property="TagTagType", type="string", description="TagTagType", example={}),
* @SWG\Property(property="TagTagSearchType", type="integer", description="TagTagSearchType", example="0"),
* @SWG\Property(property="Download", type="integer", description="Download", example="0")
* )
*/






/**
    * @SWG\Post(path="/admin_crm/get_users",
    *   tags={"Admin Crm users Section"},
    *   summary="This api is used to get list of crm users.",
    *   description="",
    *   operationId="get_users",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="List of crm users.",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\AdminCrm\GetUsers")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */
