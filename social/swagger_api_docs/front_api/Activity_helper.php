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
* definition="Inclusify\Api\Activity_Helper\set_activity_title", 
* required={"ActivityGUID", "Title"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* @SWG\Property(property="Title", type="string", description="Activity Title", example="title"),
* ) 

*/


/**
    * @SWG\Post(path="/activity_helper/set_activity_title",
    *   tags={"Activity Helper"},
    *   summary="Set activity title",
    *   description="",
    *   operationId="set_activity_title",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="This api is used to set the activity title",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\set_activity_title")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\delete_activity_title", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* ) 

*/


/**
    * @SWG\Post(path="/activity_helper/delete_activity_title",
    *   tags={"Activity Helper"},
    *   summary="Delete activity title",
    *   description="",
    *   operationId="delete_activity_title",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="This api is used to delete the activity title",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\delete_activity_title")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


 /**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\top_contributor", 
* required={},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="TagID", type="integer", description="Tag ID", example="10"),
* ) 

*/


/**
    * @SWG\Post(path="/activity_helper/top_contributor",
    *   tags={"Top Contributor"},
    *   summary="Get Top Contributor List",
    *   description="",
    *   operationId="top_contributor",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="This api is used to delete the activity title",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\top_contributor")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\similar_activity", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* ) 

*/


/**
    * @SWG\Post(path="/activity_helper/similar_activity",
    *   tags={"Similar Activity"},
    *   summary="This api used to get similar post",
    *   description="This api used to get similar post",
    *   operationId="similar_activity",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="This api used to get similar post",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\similar_activity")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\admin_tool", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* ) 

*/


/**
    * @SWG\Post(path="/activity_helper/admin_tool",
    *   tags={"Activity Admin Tool"},
    *   summary="This api used to get admin tool setting for particular post",
    *   description="This api used to get admin tool setting for particular post",
    *   operationId="admin_tool",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="LoginSessionKey",
    *     in="header",
    *     description="The Login Session Key of logged in user.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="APPVERSION",
    *     in="header",
    *     description="API Version, Current value is v3.",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="This api used to get admin tool setting for particular post",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\admin_tool")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\set_promotion_status", 
* required={"ActivityGUID","IsPromoted"},
* @SWG\Property(property="ActivityGUID", type="integer", description="ActivityGUID", example="3sadas-saf-sa-fssafsa"),
* @SWG\Property(property="IsPromoted", type="integer", description="IsPromoted", example="1"),
* ) 

*/



/**
    * @SWG\Post(path="/activity_helper/set_promotion_status",
    *   tags={"Promote/Un Promote Activity"},
    *   summary="This api is used to set the promotion status of an activity.",
    *   description="",
    *   operationId="set_promotion_status",
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
    *     description="This api is used to set the promotion status of an activity. Possible values for status( 0, 1)",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\set_promotion_status")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\update_activity_newsfeed_status", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* ) 
*/



/**
    * @SWG\Post(path="/activity_helper/update_activity_newsfeed_status",
    *   tags={"Show/Hide from Newsfeed"},
    *   summary="This api is used to hide/show this activity on newsfeed.",
    *   description="",
    *   operationId="update_activity_newsfeed_status",
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
    *     description="This api is used to hide/show this activity on newsfeed",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\update_activity_newsfeed_status")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\move_to_city_news", 
* required={"ActivityGUID","IsCityNews"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* @SWG\Property(property="IsCityNews", type="integer", description="Is City News, 0 - No, 1 - Yes", example="1"),
* @SWG\Property(property="IsShowOnNewsFeed", type="integer", description="Is Show On NewsFeed, 0 - Yes, 1 - No", example="1"),
* ) 
*/



/**
    * @SWG\Post(path="/activity_helper/move_to_city_news",
    *   tags={"Move/Remove to City News"},
    *   summary="This api is used to Move to this activity in city news.",
    *   description="",
    *   operationId="move_to_city_news",
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
    *     description="This api is used to Move to this activity in city news",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\move_to_city_news")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\remove_from_city_news", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* ) 
*/



/**
    * @SWG\Post(path="/activity_helper/remove_from_city_news",
    *   tags={"Move/Remove to City News"},
    *   summary="This api is used to remove this activity from city news.",
    *   description="",
    *   operationId="remove_from_city_news",
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
    *     description="This api is used to remove this activity from city news",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\remove_from_city_news")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\pin_to_top", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* ) 
*/



/**
    * @SWG\Post(path="/activity_helper/pin_to_top",
    *   tags={"Pin To Top"},
    *   summary="This api is used to pin activity on top.",
    *   description="",
    *   operationId="pin_to_top",
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
    *     description="This api is used to pin activity on top",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\pin_to_top")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\remove_pin_to_top", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* ) 
*/



/**
    * @SWG\Post(path="/activity_helper/remove_pin_to_top",
    *   tags={"Pin To Top"},
    *   summary="This api is used to unpin activity from top.",
    *   description="",
    *   operationId="remove_pin_to_top",
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
    *     description="This api is used to unpin activity from top",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\remove_pin_to_top")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\related_to_indore", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* @SWG\Property(property="IsRelated", type="integer", description="IsRelated, It may be 0 - No, 1 - Yes", example="1"),
* ) 
*/



/**
    * @SWG\Post(path="/activity_helper/related_to_indore",
    *   tags={"Related to Indore"},
    *   summary="This api is used to update related to indore flag for an activity.",
    *   description="",
    *   operationId="related_to_indore",
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
    *     description="This api is used to update related to indore flag for an activity",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\related_to_indore")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\idea_for_better_indore", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f"),
* @SWG\Property(property="IsIdea", type="integer", description="IsIdea, It may be 0 - No, 1 - Yes", example="1"),
* ) 
*/



/**
    * @SWG\Post(path="/activity_helper/idea_for_better_indore",
    *   tags={"Idea for Better Indore"},
    *   summary="This api is used to update idea for better indore flag for an activity.",
    *   description="",
    *   operationId="idea_for_better_indore",
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
    *     description="This api is used to update idea for better indore flag for an activity",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\idea_for_better_indore")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
* @SWG\Definition(
* definition="Inclusify\Api\Activity_Helper\bump_up", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="0ff31ff2-13b8-ef08-00f3-47c73d0d5e7f")
* ) 
*/



/**
    * @SWG\Post(path="/activity_helper/bump_up",
    *   tags={"Bump up post"},
    *   summary="This api is used to Bump up particular post.",
    *   description="",
    *   operationId="bump_up",
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
    *     description="This api is used to Bump up particular post",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity_Helper\bump_up")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */