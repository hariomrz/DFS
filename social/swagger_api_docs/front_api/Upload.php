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
* definition="Inclusify\Api\Upload_image\index", 
* required={""},
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Upload_video\index", 
* required={""},
* ) 
*/

/**
    * @SWG\Post(path="/upload_image",
    *   tags={"Upload Section"},
    *   summary="Upload API",
    *   description="",
    *   operationId="index",
    *   produces={"application/json"},
    *   consumes={"multipart/form-data"},
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
    *     name="qqfile",
    *     type="file",
    *     in="formData",
    *     description="Please select media",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Upload_image\index")
    *   ),
    *   @SWG\Parameter(
    *     name="Type",
    *     type="text",
    *     in="formData",
    *     description="it may be: profile or wall or comments or messages or album or gallery",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Upload_image\index")
    *   ),
    *   @SWG\Parameter(
    *     name="ModuleID",
    *     type="text",
    *     in="formData",
    *     description="it is entity module id 3 for User",
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Upload_image\index")
    *   ),
    *   @SWG\Parameter(
    *     name="ModuleEntityGUID",
    *     type="text",
    *     in="formData",
    *     description="it is entity guid",
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Upload_image\index")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/upload_video",
    *   tags={"Upload Section"},
    *   summary="Upload Video API",
    *   description="",
    *   operationId="index",
    *   produces={"application/json"},
    *   consumes={"multipart/form-data"},
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
    *     name="qqfile",
    *     type="file",
    *     in="formData",
    *     description="Please select media",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Upload_video\index")
    *   ),
    *   @SWG\Parameter(
    *     name="Type",
    *     type="text",
    *     in="formData",
    *     description="it may be: wall",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Upload_video\index")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */
