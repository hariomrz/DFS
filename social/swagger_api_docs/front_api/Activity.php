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
* definition="Inclusify\Api\Activity\index", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="EntityGUID", type="string", description="Entity GUID, it may be User/Quiz GUID", example="1ebb78a8-aefb-dbc8-6703-d111e3218947"),
* @SWG\Property(property="ModuleID", type="integer", description="Entity Module ID, 3 - User, 47 - Quiz", example="3"),
* @SWG\Property(property="FeedSortBy", type="integer", description="Activity Sort Order, 1 for Recent Updated, 2 for Recent Post, 3 for popular, 4 for bookmark", example="1"),
* @SWG\Property(property="AllActivity", type="integer", description="used to fetch feed or wall, 0 for wall, 1 for newsfeed", example="1"),
* @SWG\Property(property="ActivityGUID", type="string", description="To get particular activity details", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="SearchKey", type="string", description="Search Keyword", example="abc"),
* @SWG\Property(property="IsMediaExists", type="integer", description="Used to show only media(1) or only text(0), or both(2)", example="2"),
* @SWG\Property(property="FeedUser", type="array", description="Used to show only particular user feed", @SWG\Items(type="string",example="aae78fc2-898c-a185-2525-937ac2c5d7b3")),
* @SWG\Property(property="StartDate", type="string", description="Start Date, show feed which are created on & after this date", example="2017-12-01"),
* @SWG\Property(property="EndDate", type="string", description="End Date, show feed which are created on & before this date", example="2018-04-07"),
* @SWG\Property(property="ActivityFilterType", type="string", description="Different type of filter like 0- for all, 11 - for Feature, 1 for user specific bookmark, 12 for all user bookmark", example="0"),
* @SWG\Property(property="AsOwner", type="integer", description="It is used in case of page wall, to filter all the post which are created as a Page, 0 -for all, 1 -for page created post  ", example="0"),
* @SWG\Property(property="Mentions", type="array", description="Mentions, Used to filter only those post, in which given user/group are tagged",@SWG\Items(
*     type="object",
*     @SWG\Property(property="ModuleEntityGUID", type="string",example="eae972b2-cc27-81bd-183d-1b25e3256338"),
*     @SWG\Property(property="ModuleID", type="integer",example="3"),
*     )
*   ), 
* @SWG\Property(property="ViewEntityTags", type="integer", description="Used to get activity tag or not, 1 -for Yes, 0 -for No", example="1"),
* @SWG\Property(property="PostType", type="array", description="Post Type: 0 -All Post, 1 -Discussion, 2 -Q&A, 4 -Article, 7 -Announcement, 8 -Visual, 9 -Contest", @SWG\Items(type="integer",example=1)),
* @SWG\Property(property="Tags", type="array", description="Filter feed based on some existing post tags", @SWG\Items(type="integer",example=1)),
* @SWG\Property(property="TagID", type="integer", description="Tag ID", example="1"),
* @SWG\Property(property="WardIds", type="array", description="Array of Ward Ids", @SWG\Items(type="integer",example=1)),
* @SWG\Property(property="TagCategories", type="array", description="Array of Tag Category Ids", @SWG\Items(type="integer",example=1))
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\createWallPost", 
* required={"Visibility","PostContent"},
* @SWG\Property(property="ModuleEntityGUID", type="string", description="Module Entity GUID, it may be User/Quiz GUID", example="1ebb78a8-aefb-dbc8-6703-d111e3218947"),
* @SWG\Property(property="ModuleID", type="integer", description="Entity Module ID, 3 - User, 47 - Quiz", example="3"),
* @SWG\Property(property="PostType", type="integer", description="Post Type: 1 -Discussion, 2 -Q&A, 4 -Article, 7 -Announcement, 8 -Visual, 9 -Contest", example="2"),
* @SWG\Property(property="Visibility", type="integer", description="Post privacy, 1 for Everyone, 3 Friend, 3 Only me", example="1"),
* @SWG\Property(property="Commentable", type="integer", description="Comment on/Off, 1 -On, 0 -Off", example="1"),
* @SWG\Property(property="PostTitle", type="string", description="Post Title", example="abc"),
* @SWG\Property(property="PostContent", type="string", description="Post Content", example="abc"),
* @SWG\Property(property="AllActivity", type="integer", description="Created from wall/newsfeed, 0 for wall, 1 for newsfeed", example="1"),
* @SWG\Property(property="NotifyAll", type="integer", description="Used to notify all member if post made on group wall, 1 -Notify all, 0 -Not Notify", example="0"),
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID, in case of edit", example=""),
* @SWG\Property(property="Summary", type="string", description="Post Summary, it is used in case of article", example=""),
* @SWG\Property(property="PostAsModuleID", type="integer", description="Post Creator module ID, it may be 3(User), Page(18)", example="3"),
* @SWG\Property(property="PostAsModuleEntityGUID", type="string", description="Post Creator GUID", example="1ebb78a8-aefb-dbc8-6703-d111e3218947"),
* @SWG\Property(property="EntityTags", type="array", description="EntityTags, array of post tags",@SWG\Items(
*     type="object",
*     @SWG\Property(property="Name", type="string",example="php")
*     )
*   ),
* @SWG\Property(property="Media", type="array", description="Media, array of post Media",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="MediaType", type="string",example="PHOTO"),
*     @SWG\Property(property="Caption", type="string",example="php"),
*     )
*   ), 
* @SWG\Property(property="Files", type="array", description="Files, array of post Files",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="MediaType", type="string",example="Documents"),
*     @SWG\Property(property="Caption", type="string",example="php")
*     )
*   ), 
* @SWG\Property(property="Links", type="array", description="Links, array of post Links",@SWG\Items(
*     type="object",
*     @SWG\Property(property="URL", type="string",example="http://www.google.com/"),
*     @SWG\Property(property="Title", type="string",example="Google"),
*     @SWG\Property(property="MetaDescription", type="string",example="Google"),
*     @SWG\Property(property="ImageURL", type="string",example="uploads/wall/1530699280MfrhTS8J.jpg"),
*     @SWG\Property(property="IsCrawledURL", type="integer",example="0")
*     )
*   ),  
* @SWG\Property(property="Status", type="integer", description="Post Status, is it draft post or not, 10 -For draft post, 2 -For active post", example="2"),
* @SWG\Property(property="PollDescription", type="string", description="Poll Description", example="You Like"),
* @SWG\Property(property="PollOptions", type="array", description="Poll Options, array of poll options, only two option allowed",@SWG\Items(
*     type="object",
*     @SWG\Property(property="OptionDescription", type="string",example="Kachori"),
*     @SWG\Property(property="OptionDescription", type="string",example="Samosa")
*     )
*   ),
* @SWG\Property(property="WardIds", type="array", description="Array of Ward Ids", @SWG\Items(type="integer",example=1)),
* @SWG\Property(property="DeleteLink", type="integer", description="0 or 1, if 1 then delete existing link preview", example="1"),
* @SWG\Property(property="IsCityNews", type="integer", description="City news or not 0 - No, 1 - Yes", example="0"),
* @SWG\Property(property="IsShowOnNewsFeed", type="integer", description="Show it on news feed or not, 0 - Yes, 1 - No", example="0")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\toggleLike", 
* required={""},
* @SWG\Property(property="EntityGUID", type="string", description="Entity GUID, which is being like/unlike", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="EntityType", type="string", description="EntityType, it may be ACTIVITY, ALBUM, COMMENT", example="ACTIVITY"),
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\getLikeDetails", 
* required={"EntityGUID","EntityType"},
* @SWG\Property(property="EntityGUID", type="string", description="Entity GUID, which is being like/unlike", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="EntityType", type="string", description="EntityType, it may be ACTIVITY, ALBUM, COMMENT", example="ACTIVITY"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10") 
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\removeActivity", 
* required={"EntityGUID"},
* @SWG\Property(property="EntityGUID", type="string", description="Entity GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="Reason", type="string", description="Delete Reason", example="not valid content")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\get_deleted_activity", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Entity GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="CommentGUID", type="string", description="Comment GUID", example="c274032-fe2e0-b3cc-4f63-b9e6a8960bd7")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\seen_list", 
* required={"EntityGUID","EntityType"},
* @SWG\Property(property="EntityGUID", type="string", description="Entity GUID, which is being viewed", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="EntityType", type="string", description="EntityType, it is Activity", example="Activity"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10") 
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\addComment", 
* required={"EntityGUID"},
* @SWG\Property(property="EntityGUID", type="string", description="Entity GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="EntityType", type="string", description="EntityType, it may be ACTIVITY, MEDIA", example="ACTIVITY"),
* @SWG\Property(property="Comment", type="string", description="Comment text", example="Testing"),
* @SWG\Property(property="ParentCommentGUID", type="string", description="Parent Comment GUID, used in case of reply", example="fe2e0c27-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="DeleteLink", type="integer", description="0 or 1, if 1 then delete existing link preview", example="1"), 
* @SWG\Property(property="Links", type="array", description="Links, array of post Links",@SWG\Items(
*     type="object",
*     @SWG\Property(property="URL", type="string",example="http://www.google.com/"),
*     @SWG\Property(property="Title", type="string",example="Google"),
*     @SWG\Property(property="MetaDescription", type="string",example="Google"),
*     @SWG\Property(property="ImageURL", type="string",example="uploads/wall/1530699280MfrhTS8J.jpg"),
*     @SWG\Property(property="IsCrawledURL", type="integer",example="0")
*     )
*   ),
* @SWG\Property(property="Media", type="array", description="Media, array of post Media",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="MediaType", type="string",example="PHOTO"),
*     @SWG\Property(property="Caption", type="string",example="php"),
*     )
*   )
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\deleteComment", 
* required={"CommentGUID"},
* @SWG\Property(property="CommentGUID", type="string", description="Comment GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="Reason", type="string", description="Delete Reason", example="not valid content")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\set_featured_post", 
* required={"ActivityGUID","ModuleID","ModuleEntityID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="ModuleEntityID", type="integer", description="Module Entity ID, it may be User/Group/Page/Event ID", example="1"),
* @SWG\Property(property="ModuleID", type="integer", description="Entity Module ID", example="3"), 
* ) 
*/


/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\getAllComments", 
* required={"EntityGUID"},
* @SWG\Property(property="EntityGUID", type="string", description="Entity GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="EntityType", type="string", description="EntityType, it may be Activity", example="Activity"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="Filter", type="string", description="it may be, Recent, Popular, Network", example="Network")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\activity_media", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\visibility", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Log\index", 
* required={"EntityGUID"},
* @SWG\Property(property="EntityGUID", type="string", description="Entity GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a"),
* @SWG\Property(property="EntityType", type="string", description="Entity Type", example="Activity")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\short_link", 
* required={"ActivityGUID"},
* @SWG\Property(property="ActivityGUID", type="string", description="Activity GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\city_news", 
* required={},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="WardIds", type="array", description="Array of Ward Ids", @SWG\Items(type="integer",example=1)),
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\get_unread_city_news_count", 
* required={}
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\story_user", 
* required={},
* @SWG\Property(property="WID", type="integer", description="Ward id", example="2")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\user_story", 
* required={"UserGUID"},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="UserGUID", type="string", description="User GUID", example="c27fe2e0-4032-4f63-b3cc-8960bd7b9e6a")
* ) 
*/


/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\public_feed", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="ModuleEntityGUID", type="string", description="Module Entity GUID, it may be User/Quiz GUID", example="1ebb78a8-aefb-dbc8-6703-d111e3218947"),
* @SWG\Property(property="ModuleID", type="integer", description="Entity Module ID, 3 - User, 47 - Quiz", example="3"),
* @SWG\Property(property="FeedSortBy", type="integer", description="Activity Sort Order, 1 for Recent Updated, 2 for Recent Post, 3 for popular, 4 for bookmark", example="1"),
* @SWG\Property(property="ActivityGUID", type="string", description="To get particular activity details", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="ViewEntityTags", type="integer", description="Used to get activity tag or not, 1 -for Yes, 0 -for No", example="1"),
* @SWG\Property(property="PostType", type="array", description="Post Type: 0 -All Post, 1 -Discussion, 2 -Q&A, 4 -Article, 7 -Announcement, 8 -Visual, 9 -Contest", @SWG\Items(type="integer",example=1)),
* @SWG\Property(property="TagID", type="integer", description="Tag ID", example="1"),
* @SWG\Property(property="TagCategoryID", type="integer", description="Tag Category ID", example="1")
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Activity\idea_for_better_indore", 
* required={},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"), 
* @SWG\Property(property="ViewEntityTags", type="integer", description="Used to get activity tag or not, 1 -for Yes, 0 -for No", example="1"),
* ) 
*/

/**
    * @SWG\Post(path="/activity/index",
    *   tags={"Activity Section"},
    *   summary="Activity API",
    *   description="",
    *   operationId="index",
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
    *     name="LocalityID",
    *     in="header",
    *     description="The Locality ID.",
    *     required=true,
    *     type="integer"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\index")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/createWallPost",
    *   tags={"Activity Section"},
    *   summary="Create/Edit Activity API",
    *   description="Used to create new post",
    *   operationId="createWallPost",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\createWallPost")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/toggleLike",
    *   tags={"Activity Section"},
    *   summary="Used to  like / unlike an entity",
    *   description="",
    *   operationId="toggleLike",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\toggleLike")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/getLikeDetails",
    *   tags={"Activity Section"},
    *   summary="Used to get list of user's who liked this post",
    *   description="",
    *   operationId="getLikeDetails",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\getLikeDetails")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/removeActivity",
    *   tags={"Activity Section"},
    *   summary="Used to delete post",
    *   description="",
    *   operationId="removeActivity",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\removeActivity")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

 /**
    * @SWG\Post(path="/activity/get_deleted_activity",
    *   tags={"Activity Section"},
    *   summary="Used to get deleted post",
    *   description="",
    *   operationId="get_deleted_activity",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\get_deleted_activity")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
    * @SWG\Post(path="/activity/seen_list",
    *   tags={"Activity Section"},
    *   summary="Used to get list of user's who viewed this post",
    *   description="",
    *   operationId="seen_list",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\seen_list")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/addComment",
    *   tags={"Activity Section"},
    *   summary="Used to add comment on an entity",
    *   description="",
    *   operationId="addComment",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\addComment")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/getAllComments",
    *   tags={"Activity Section"},
    *   summary="Used to get comment list for an entity",
    *   description="",
    *   operationId="getAllComments",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\getAllComments")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/deleteComment",
    *   tags={"Activity Section"},
    *   summary="Used to delete comment",
    *   description="",
    *   operationId="deleteComment",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\deleteComment")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/set_featured_post",
    *   tags={"Activity Section"},
    *   summary="Used to mark an post as featured/Unfeatured post",
    *   description="",
    *   operationId="set_featured_post",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\set_featured_post")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */



/**
    * @SWG\Post(path="/activity/activity_media",
    *   tags={"Activity Section"},
    *   summary="Used to get activity media list",
    *   description="",
    *   operationId="activity_media",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\activity_media")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/short_link",
    *   tags={"Activity Section"},
    *   summary="Used to get activity short link",
    *   description="",
    *   operationId="short_link",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\short_link")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/visibility",
    *   tags={"Activity Visibility"},
    *   summary="Used to get activity visibility WARD list",
    *   description="",
    *   operationId="visibility",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\visibility")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/log",
    *   tags={"Activity View"},
    *   summary="Used to save view log",
    *   description="",
    *   operationId="log",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Log\index")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/activity/city_news",
    *   tags={"City News"},
    *   summary="Used to get city news list",
    *   description="",
    *   operationId="city_news",
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
    *     required=false,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\city_news")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


    /**
    * @SWG\Post(path="/activity/get_unread_city_news_count",
    *   tags={"City News"},
    *   summary="Used to get city news unread count",
    *   description="",
    *   operationId="city_news",
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
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\get_unread_city_news_count")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/activity/story_user",
    *   tags={"Story"},
    *   summary="Used to get story user list with story count",
    *   description="",
    *   operationId="story_user",
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
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\story_user")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

    /**
    * @SWG\Post(path="/activity/user_story",
    *   tags={"Story"},
    *   summary="Used to get user stories",
    *   description="",
    *   operationId="user_story",
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
    *     required=false,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\user_story")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


    /**
    * @SWG\Post(path="/activity/public_feed",
    *   tags={"Public Activity"},
    *   summary="Public Activity API",
    *   description="",
    *   operationId="public_feed",
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
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\public_feed")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


    /**
     * @SWG\Post(path="/activity/idea_for_better_indore",
     *   tags={"Idea For Better Indore"},
     *   summary="Used to get activity which are marked as Idea For Better Indore",
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
    *     description="",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Activity\idea_for_better_indore")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */