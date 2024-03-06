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
* definition="Inclusify\Api\Contact\index", 
* required={"Name","Mobile","Message"},
* @SWG\Property(property="Name", type="string", description="User name", example="Suresh Patidar"),
* @SWG\Property(property="Mobile", type="string", description="Mobile Number", example="9827298272"),
* @SWG\Property(property="Message", type="string", description="Message", example="MahaLaxmi Nagar"),
* )
*/

/**
    * @SWG\Post(path="/contact/index",
    *   tags={"Activity Section"},
    *   summary="Used to submit contact us form",
    *   description="",
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
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Contact\index")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */