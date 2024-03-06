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
* definition="Inclusify\Api\Media\like_details", 
* required={"MediaGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="MediaGUID", example="a19be4f4-c7ea-e337-deee-22902d154962"),
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="PageSize", example="10")
* )
*/

/**
* @SWG\Post(path="/Media/like_details",
*   tags={"Media details"},
*   summary="This api is used to get like list of media.",
*   description="",
*   operationId="like_details",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Media\like_details")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Media\comments", 
* required={"MediaGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="MediaGUID", example="a19be4f4-c7ea-e337-deee-22902d154962"),
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="PageSize", example="10")
* )
*/

/**
* @SWG\Post(path="/Media/comments",
*   tags={"Media activity"},
*   summary="This api is used to get comments of media.",
*   description="",
*   operationId="comments",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Media\comments")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Media\toggle_like", 
* required={"MediaGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="MediaGUID", example="a19be4f4-c7ea-e337-deee-22902d154962")
* )
*/

/**
* @SWG\Post(path="/Media/toggle_like",
*   tags={"Media activity"},
*   summary="This api is used to like/unlike media.",
*   description="",
*   operationId="toggle_like",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Media\toggle_like")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Media\add_comment", 
* required={"MediaGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="MediaGUID", example="a19be4f4-c7ea-e337-deee-22902d154962"),
* @SWG\Property(property="Comment", type="string", description="Comment", example="new comment"),
* @SWG\Property(property="Media", type="array", description="Media, array of Media",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="MediaType", type="string",example="PHOTO"),
*     @SWG\Property(property="Caption", type="string",example="nature"),
*     )
*   )
* )
*/

/**
* @SWG\Post(path="/Media/add_comment",
*   tags={"Media activity"},
*   summary="This api is used to add comment on media.",
*   description="",
*   operationId="add_comment",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Media\add_comment")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Definition(
* definition="Inclusify\Api\Media\details", 
* required={"MediaGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="MediaGUID", example="a19be4f4-c7ea-e337-deee-22902d154962"),
* )
*/

/**
* @SWG\Post(path="/Media/details",
*   tags={"Media details"},
*   summary="This api is used to get media details.",
*   description="",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Media\details")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Definition(
* definition="Inclusify\Api\Media\delete", 
* required={"MediaGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="MediaGUID", example="a19be4f4-c7ea-e337-deee-22902d154962"),
* )
*/

/**
* @SWG\Post(path="/Media/delete",
*   tags={"Delete Media"},
*   summary="This api is used to delete media.",
*   description="",
*   operationId="delete",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Media\delete")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Definition(
* definition="Inclusify\Api\Media\add_gallery", 
* required={},
* @SWG\Property(property="Media", type="array", description="Media, array of gallery Media",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="Description", type="string",example="Bhopu"),
*     )
*   ),
* )
*/

/**
* @SWG\Post(path="/Media/add_gallery",
*   tags={"User Gallery"},
*   summary="This api is used to add user gallery media.",
*   description="",
*   operationId="add_gallery",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Media\add_gallery")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Definition(
* definition="Inclusify\Api\Media\get_gallery", 
* required={},
* )
*/

/**
* @SWG\Post(path="/Media/get_gallery",
*   tags={"User Gallery"},
*   summary="This api is used to get user gallery media.",
*   description="",
*   operationId="get_gallery",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Media\get_gallery")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/