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
* definition="Inclusify\AdminApi\Dashboard\GetUnverifiedEntities", 
* required={""},
* @SWG\Property(property="search", type="string", description="Search Keyword Field", example=""),
* @SWG\Property(property="entityType", type="string", description="Entity Type Field (ALL, USERS, GROUPS, EVENTS, PAGES)", example="ALL"),
* @SWG\Property(property="page_size", type="integer", description="Pagination record per page limit", example="10"),
* @SWG\Property(property="page_no", type="integer", description="Pagination page number", example="1")
* )
*
* @SWG\Definition(
* definition="Inclusify\AdminApi\Dashboard\GetUnverifiedEntity", 
* required={""},
* @SWG\Property(property="ModuleID", type="string", description="ModuleID", example="3"),
* @SWG\Property(property="ModuleEntityID", type="string", description="ModuleEntityID", example="2"),
* )
* @SWG\Definition(
* definition="Inclusify\AdminApi\Dashboard\UpdateEntity", 
* required={""},
* @SWG\Property(property="ModuleID", type="integer", description="Entity ModuleID To which need to change verify status( 1(Grpup), 14(Event) , 18(Pages), 3(Users) )", example="3"),
* @SWG\Property(property="ModuleEntityID", type="integer", description="Entity ModuleEntityID", example="1"),
* @SWG\Property(property="EntityColumn", type="integer", description="EntityColumn", example=""),
* @SWG\Property(property="EntityColumnVal", type="integer", description="EntityColumnVal", example="1"),
* @SWG\Property(property="UserID", type="integer", description="UserID", example="0"),
* ) 

* @SWG\Definition(
* definition="Inclusify\AdminApi\Dashboard\SaveNote", 
* required={""},
* @SWG\Property(property="ModuleID", type="integer", description="Entity ModuleID", example="3"),
* @SWG\Property(property="ModuleEntityID", type="integer", description="Entity ModuleEntityID", example="1"),
* @SWG\Property(property="Description", type="string", description="Entity Note", example="Testing Note"),
* ) 
 
* @SWG\Definition(
* definition="Inclusify\AdminApi\Dashboard\DeleteNote", 
* required={""},
* @SWG\Property(property="NoteID", type="integer", description="Entity NoteID", example="3"),
* ) 

* @SWG\Definition(
* definition="Inclusify\AdminApi\Dashboard\GetNoteList", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page no", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page size", example="10"),
* @SWG\Property(property="ModuleID", type="integer", description="ModuleID", example="0"),
* @SWG\Property(property="ModuleEntityID", type="integer", description="ModuleEntityID", example="0"),
* ) 
 * 

* @SWG\Definition(
* definition="Inclusify\AdminApi\Dashboard\SendMessage", 
* required={""},
* @SWG\Property(property="ModuleID", type="integer", description="Module Id for group, user, event or page", example="1"),
* @SWG\Property(property="ModuleEntityID", type="integer", description="module entity id", example="1"),
* @SWG\Property(property="Replyable", type="string", description="message Replyable", example="1"),
* @SWG\Property(property="Body", type="string", description="message body", example="Testing message"),
* @SWG\Property(property="Media", type="string", description="message media", example={}),
* @SWG\Property(property="Subject", type="string", description="message subject", example="test subject"),
* ) 
 * 
 
 
 * @SWG\Definition(
* definition="Inclusify\AdminApi\Dashboard\GetActivities", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page no", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page size", example="10"),


* @SWG\Property(property="PostType", type="integer", description="PostType", example="0"),
* @SWG\Property(property="ActivityFilterType", type="integer", description="ActivityFilterType", example="1"),
* @SWG\Property(property="IsMediaExists", type="integer", description="IsMediaExists", example="2"),
* @SWG\Property(property="StartDate", type="string", description="StartDate", example=""),
* @SWG\Property(property="EndDate", type="string", description="EndDate", example=""),
* @SWG\Property(property="UserID", type="integer", description="UserID", example="0"),
* @SWG\Property(property="SearchKey", type="string", description="SearchKey", example=""),
* @SWG\Property(property="Tags", type="string", description="Tags", example={}),
* @SWG\Property(property="CityID", type="integer", description="CityID", example="0"),
* @SWG\Property(property="AgeGroupID", type="integer", description="AgeGroupID", example="0"),
* @SWG\Property(property="Gender", type="integer", description="Gender", example="0"),
* @SWG\Property(property="TagType", type="integer", description="TagType", example="0"),
* @SWG\Property(property="FeedSortBy", type="integer", description="FeedSortBy", example="2"),
* @SWG\Property(property="City", type="string", description="City", example=""),
* @SWG\Property(property="State", type="string", description="State", example=""),
* @SWG\Property(property="Country", type="string", description="Country", example=""),
* @SWG\Property(property="CountryCode", type="string", description="CountryCode", example=""),
* @SWG\Property(property="StateCode", type="string", description="StateCode", example=""),
* @SWG\Property(property="GET_ENTITY_TYPE", type="string", description="GET_ENTITY_TYPE", example="ALL"),
* ) 
 
  
  
  
  
  
  
  
* @SWG\Definition(
* definition="Inclusify\AdminApi\Dashboard\GetUserPostDetails", 
* required={""},
* @SWG\Property(property="UserID", type="integer", description="UserID", example="1"),
* @SWG\Property(property="ActivityID", type="integer", description="ActivityID", example="9763"),
* ) 
 *  

*/






/**
    * @SWG\Post(path="/dashboard/get_unverified_entities",
    *   tags={"Admin Dashboard Section"},
    *   summary="This api is used to get list of unverified entities.",
    *   description="",
    *   operationId="get_unverified_entities",
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
    *     description="List unverified entities. The field entityType can have these values (ALL, USERS, GROUPS, EVENTS, PAGES)",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Dashboard\GetUnverifiedEntities")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/dashboard/get_unverified_entity",
    *   tags={"Admin Dashboard Section"},
    *   summary="This api is used to get unverified entity.",
    *   description="",
    *   operationId="get_unverified_entity",
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
    *     description="Get unverified entity. The field ModuleID can have these values (1, 3, 14, 18)",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Dashboard\GetUnverifiedEntity")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/dashboard/update_entity",
    *   tags={"Admin Dashboard Section"},
    *   summary="This api is used to change column value of entities (Users, Groups) to verified",
    *   description="",
    *   operationId="update_entity",
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
    *     description="Update column value of entities. The Module Id field can have these values ( 1(Grpup), 14(Event) , 18(Pages), 3(Users), 20 (POSTCOMMENTS) ), 19(ACTIVITY). 
                       EntityColumn can be (Verified, StatusID).
                       You need to send UserID param in case of activity delete. UserID will be, activity is deleted for.   ",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Dashboard\UpdateEntity")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */



 /**
    * @SWG\Post(path="/dashboard/save_note",
    *   tags={"Admin Dashboard Section"},
    *   summary="This api is used to save note for entities",
    *   description="",
    *   operationId="save_note",
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
    *     description="save note for entities. The Module Id field can have these values ( 1(Grpup), 14(Event) , 18(Pages), 3(Users) )",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Dashboard\SaveNote")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */



 /**
    * @SWG\Post(path="/dashboard/delete_note",
    *   tags={"Admin Dashboard Section"},
    *   summary="This api is used to delete note for entities",
    *   description="",
    *   operationId="delete_note",
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
    *     description="delete note for entities",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Dashboard\DeleteNote")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
    * @SWG\Post(path="/dashboard/get_note_list",
    *   tags={"Admin Dashboard Section"},
    *   summary="This api is used to list notes for entities",
    *   description="",
    *   operationId="get_note_list",
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
    *     description="list note for entities",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Dashboard\GetNoteList")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
    * @SWG\Post(path="/dashboard/send_message",
    *   tags={"Admin Dashboard Section"},
    *   summary="This api is used to send messages to different entities ( USER, To Group Admins, To Event Admins, To Page Admins )",
    *   description="",
    *   operationId="send_message",
    *   produces={"application/json"},
    *   consumes={"application/json"},
    *   @SWG\Parameter(
    *     name="AdminLoginSessionKey",
    *     in="header",
    *     description="The Admin Login Session Key of logged in user. ",
    *     required=true,
    *     type="string"
    *   ),
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="send message to different entities.  The Module Id field can have these values ( 1(Grpup), 14(Event) , 18(Pages), 3(Users) )",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Dashboard\SendMessage")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
    * @SWG\Post(path="/dashboard/get_activities",
    *   tags={"Admin Dashboard Section"},
    *   summary="This api is used to get logged unverified activities for different entitites ( Activity, Comment, Reply, Share Post )",
    *   description="",
    *   operationId="get_activities",
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
    *     description="Get List of activities for different modules.
 
   ************* Description for output parameters *****************
    Group IsPublic Possible values ( 1 = public , 0= private , 2 = secrate  )
    
    *****     Description for Input parameters  **********
    Gender Possible values( 1 Male, 2 Female., 3 Other ) 
    AgeGroupID Possible Values ( 1 => 0 - 13, 2 => 13 - 18 , 3 => 18 - 25,  4 => 25 - 30,  5 => 30 - 35,    6 => 35 - 40,    7 => 40 - 45,   8 => 45 - 50,   9 => Above 50)
    ActivityFilterType Possible values (3, 7, 10, 11)
    PostType possible values ( 1 - Discussion, 2 - Q & A, 3- Polls, 4 - Knowledge Base, 5 - Tasks & Lists, 6 - Ideas, 7 - Announcements  )
    IsMediaExist possible values ( 2 for all posts with or without media, 1 only posts having medias )
    TagType possible values ( 1- Normal Tag, 2- Hash Tag, 3- Activity Mood, 4- Activity Classification, 5- User/Reader Tag, 6- User Profession, 7- Brand ).   
    FeedSortBy possible Values ( 2,3,5 => For latest first, 'popular', 'General', 'Question', 'UnAnswered' )
    * ",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Dashboard\GetActivities")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
    * @SWG\Post(path="/dashboard/get_user_post_details",
    *   tags={"Admin Dashboard Section"},
    *   summary="This api is used to get User and Post details",
    *   description="",
    *   operationId="get_user_post_details",
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
    *     description="This api is used to get User and Post details",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Dashboard\GetUserPostDetails")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */
