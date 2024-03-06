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
* definition="Inclusify\Api\Tag\get_trending_tags", 
* required={""},
* @SWG\Property(property="WID", type="integer", description="Ward ID", example="3"),
* @SWG\Property(property="ADT", type="integer", description="Get most used tag other  than top tag, 0 - No, 1 - Yes", example="0"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\get_entity_tags", 
* required={""},
* @SWG\Property(property="SearchKeyword", type="string", description="Tag search keyword", example=""),
* @SWG\Property(property="TagType", type="string", description="it may be ACTIVITY, USER, PROFESSION", example="ACTIVITY"),
* @SWG\Property(property="EntityType", type="string", description="it may be ACTIVITY, USER", example="ACTIVITY"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\save", 
* required={""},
* @SWG\Property(property="EntityGUID", type="string", description="EntityGUID, it may be ACTIVITY GUID, USER ID", example="SDASD-asdfas-safads-fsas"),
* @SWG\Property(property="EntityType", type="string", description="Entity Type, it may be ACTIVITY, USER", example="USER"),
* @SWG\Property(property="TagType", type="string", description="Tage Type, it may be ACTIVITY, USER, PROFESSION", example="USER"),
* @SWG\Property(property="TagsList", type="string", description="Tage List To be added", example={{"Name" : "Test", "TagID" : "1"}}),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\delete_entity_tag", 
* required={""},
* @SWG\Property(property="EntityGUID", type="string", description="EntityGUID, it may be ACTIVITY GUID, USER ID", example="4esd-asdfas-safads-fsas"),
* @SWG\Property(property="EntityType", type="string", description="Entity Type, it may be ACTIVITY, USER", example="USER"),
* @SWG\Property(property="TagsIDs", type="string", description="Tag ids to be deleted", example={}),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Search\tag", 
* required={""},
* @SWG\Property(property="SearchKeyword", type="string", description="search keyword", example="ph"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\save_tag_category", 
* required={"Name"},
* @SWG\Property(property="Name", type="string", description="Tag category name", example="Water"),
* @SWG\Property(property="TagsList", type="array", description="Tags, array of tags",@SWG\Items(
*     type="object",
*     @SWG\Property(property="Name", type="string",example="water"),
*     @SWG\Property(property="TagID", type="integer",example="1")
*     )
*   )
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\delete_tag_category", 
* required={"TagCategoryID"},
* @SWG\Property(property="TagCategoryID", type="integer", description="Tag category id", example="1")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\get_tag_categories", 
* required={""}
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\change_category_tag_order", 
* required={""},
* @SWG\Property(property="OrderData", type="array", description="Display order data of tag category",@SWG\Items(
*     type="object",
*     @SWG\Property(property="TagCategoryID", type="integer",example="1"),
*     @SWG\Property(property="DisplayOrder", type="integer",example="2")
*     )
*   )
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\top_contribution_tags", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* ) 

*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\toggle_follow",
* required={"TagID"},
* @SWG\Property(property="TagID", type="integer", description="To follow/unfollow tag", example="2"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\toggle_mute",
* required={"TagID"},
* @SWG\Property(property="TagID", type="integer", description="To mute/unmute tag", example="2"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\browse_topic", 
* required={""}
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Tag\details",
* required={"TagID"},
* @SWG\Property(property="TagID", type="integer", description="To get tag details", example="2"),
* )
*/

/**
    * @SWG\Post(path="/tag/get_trending_tags",
    *   tags={"Ward Treanding Tags"},
    *   summary="This api is used to get trending tags for ward.",
    *   description="",
    *   operationId="get_trending_tags",
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
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\get_trending_tags")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Get(path="/tag/get_entity_tags",
    *   tags={"Tag Auto Suggestion"},
    *   summary="This api is used to get tag suggestion.",
    *   description="",
    *   operationId="get_entity_tags",
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
    *       name="SearchKeyword",
    *       in="query",
    *       required=false,
    *       type="string",
    *       description="Search keyword",
    *   ),
    *   @SWG\Parameter(
    *       name="TagType",
    *       in="query",
    *       required=false,
    *       type="string",
    *       description="TagType may be (ACTIVITY, USER, PROFESSION)",
    *   ),
    *   @SWG\Parameter(
    *       name="EntityType",
    *       in="query",
    *       required=false,
    *       type="string",
    *       description="EntityType may be (ACTIVITY, USER)",
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */   
    
    /**
    * @SWG\Post(path="/tag/save",
    *   tags={"Save Entity Tags"},
    *   summary="This api is used to save tag.",
    *   description="",
    *   operationId="save",
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
    *     description="EntityType may be (ACTIVITY, USER), TagType may be (ACTIVITY, USER, PROFESSION)",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\save")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/tag/delete_entity_tag",
    *   tags={"Delete Entity Tag"},
    *   summary="This api is used to delete_entity_tag_post Used to delete entity tag.",
    *   description="",
    *   operationId="delete_entity_tag",
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
    *     description="delete tag. EntityType may be (ACTIVITY, USER)",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\delete_entity_tag")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

  /**
    * @SWG\Post(path="/search/tag",
    *   tags={"Search Tags"},
    *   summary="This api is used to search tags.",
    *   description="",
    *   operationId="tag",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Search\tag")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


    

/**
    * @SWG\Post(path="/tag/save_tag_category",
    *   tags={"Tags Category Section"},
    *   summary="This api is used to save tag category.",
    *   description="",
    *   operationId="save_tag_category",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\save_tag_category")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/tag/delete_tag_category",
    *   tags={"Tags Category Section"},
    *   summary="This api is used to delete tag category.",
    *   description="",
    *   operationId="delete_tag_category",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\delete_tag_category")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/tag/get_tag_categories",
    *   tags={"Tags Category Section"},
    *   summary="This api is used to get tag categories.",
    *   description="",
    *   operationId="get_tag_categories",
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
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\get_tag_categories")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/tag/change_category_tag_order",
    *   tags={"Tags Category Section"},
    *   summary="This api is used to change display order of tag categories.",
    *   description="",
    *   operationId="change_category_tag_order",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\get_tag_categories")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


    /**
    * @SWG\Post(path="/tag/top_contribution_tags",
    *   tags={"Top Contribution Tags"},
    *   summary="This api is used to get top contribution tag.",
    *   description="",
    *   operationId="top_contribution_tags",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\top_contribution_tags")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
* @SWG\Post(path="/tag/toggle_follow",
*   tags={"Follow/Unfollow"},
*   summary="This api is used to follow/unfollow tag.",
*   description="This api is used to follow/unfollow tag.",
*   operationId="toggle_follow",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\toggle_follow")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

    /**
* @SWG\Post(path="/tag/toggle_mute",
*   tags={"Mute/Unmute"},
*   summary="This api is used to mute/unmute tag.",
*   description="This api is used to mute/unmute tag.",
*   operationId="toggle_mute",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\toggle_mute")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

    /**
    * @SWG\Post(path="/tag/browse_topic",
    *   tags={"Browse Topic"},
    *   summary="This api is used to get tags for browse topic.",
    *   description="",
    *   operationId="browse_topic",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\browse_topic")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/tag/details",
    *   tags={"Tag Details"},
    *   summary="This api is used to get tag details.",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Tag\details")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */
