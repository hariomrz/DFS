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
* definition="Inclusify\Api\Messages\search_user", 
* required={""},
* @SWG\Property(property="SearchKeyword", type="string", description="search key word", example="su"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Messages\compose", 
* required={"Recipients"},
* @SWG\Property(property="Body", type="string", description="message", example="Hi"),
* @SWG\Property(property="Media", type="array", description="Media, array of post Media",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="Caption", type="string",example="php"),
*     )
*   ),
* @SWG\Property(property="Recipients", type="array", description="Recipients, array of Recipients",@SWG\Items(
*     type="object",
*     @SWG\Property(property="UserGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     )
*   ),
* @SWG\Property(property="Links", type="array", description="Links, array of message Links",@SWG\Items(
*     type="object",
*     @SWG\Property(property="URL", type="string",example="http://www.google.com/"),
*     @SWG\Property(property="Title", type="string",example="Google"),
*     @SWG\Property(property="MetaDescription", type="string",example="Google"),
*     @SWG\Property(property="ImageURL", type="string",example="uploads/messages/1530699280MfrhTS8J.jpg"),
*     @SWG\Property(property="IsCrawledURL", type="integer",example="0")
*     )
*   ),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Messages\inbox", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Messages\change_thread_status", 
* required={"ThreadGUID"},
* @SWG\Property(property="ThreadGUID", type="string", description="Thread GUID", example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
* @SWG\Property(property="Status", type="string", description="Thread read/un read status, it's value UN_READ or READ or DELETED", example="UN_READ"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Messages\details", 
* required={"ThreadGUID"},
* @SWG\Property(property="ThreadGUID", type="string", description="Thread GUID", example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Messages\change_unseen_to_seen", 
* required={},
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Messages\reply", 
* required={"ThreadGUID"},
* @SWG\Property(property="ThreadGUID", type="string", description="Thread GUID", example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
* @SWG\Property(property="Body", type="string", description="message", example="Hi"),
* @SWG\Property(property="Media", type="array", description="Media, array of post Media",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="Caption", type="string",example="php"),
*     )
*   ),
* @SWG\Property(property="Links", type="array", description="Links, array of message Links",@SWG\Items(
*     type="object",
*     @SWG\Property(property="URL", type="string",example="http://www.google.com/"),
*     @SWG\Property(property="Title", type="string",example="Google"),
*     @SWG\Property(property="MetaDescription", type="string",example="Google"),
*     @SWG\Property(property="ImageURL", type="string",example="uploads/messages/1530699280MfrhTS8J.jpg"),
*     @SWG\Property(property="IsCrawledURL", type="integer",example="0")
*     )
*   ),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Messages\delete", 
* required={"MessageGUID"},
* @SWG\Property(property="MessageGUID", type="string", description="Thread GUID", example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Messages\get_thread_guid", 
* required={"MessageGUID"},
* @SWG\Property(property="UserGUID", type="string", description="User GUID", example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
* )
*/

/**
    * @SWG\Post(path="/messages/search_user",
    *   tags={"Message"},
    *   summary="This api is used to search user.",
    *   description="",
    *   operationId="search_user",
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
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Messages\search_user")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/messages/compose",
    *   tags={"Message"},
    *   summary="This api is used to send message to other user.",
    *   description="",
    *   operationId="compose",
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
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Messages\compose")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */    

/**
    * @SWG\Post(path="/messages/inbox",
    *   tags={"Message"},
    *   summary="This api is used to get user inbox.",
    *   description="",
    *   operationId="inbox",
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
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Messages\inbox")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/messages/change_thread_status",
    *   tags={"Message"},
    *   summary="This api is used to update thread status.",
    *   description="",
    *   operationId="change_thread_status",
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
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Messages\change_thread_status")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */  

 /**
    * @SWG\Post(path="/messages/details",
    *   tags={"Message"},
    *   summary="This api is used to get message details.",
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
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Messages\details")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */   

    /**
    * @SWG\Post(path="/messages/reply",
    *   tags={"Message"},
    *   summary="This api is used to reply on message.",
    *   description="",
    *   operationId="reply",
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
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Messages\reply")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */   


    /**
    * @SWG\Post(path="/messages/delete",
    *   tags={"Message"},
    *   summary="This api is used to delete message.",
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
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Messages\delete")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */ 

    /**
    * @SWG\Post(path="/messages/get_thread_guid",
    *   tags={"Message"},
    *   summary="This api is used to get existing thread guid between logged in user & requested user.",
    *   description="",
    *   operationId="get_thread_guid",
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
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Messages\get_thread_guid")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */ 

    /**
    * @SWG\Post(path="/messages/change_unseen_to_seen",
    *   tags={"Message"},
    *   summary="This api is used to mark all thread as seen.",
    *   description="",
    *   operationId="change_unseen_to_seen",
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
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Messages\change_unseen_to_seen")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */ 