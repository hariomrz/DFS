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
* definition="Inclusify\Api\Events\get_event_locations", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="EntityID", example="1"),
* @SWG\Property(property="PageSize", type="string", description="Entity Type", example="10"),
* @SWG\Property(property="Filter", type="string", description="Event Type", example="AllPublicEvents"),
* @SWG\Property(property="Latitude", type="string", description="Latitue of location", example="22.7167"),
* @SWG\Property(property="Longitude", type="string", description="Longitude of location", example="75.8333"),
* @SWG\Property(property="CategoryIDs", type="array", description="Category Ids array", example={}),
* @SWG\Property(property="EndDate", type="date", description="Endate filter", example=""),
* @SWG\Property(property="StartDate", type="date", description="Startdate filter", example=""),
* @SWG\Property(property="LocationID", type="array", description="Location IDs array", example={}),
* @SWG\Property(property="SearchKeyword", type="string", description="Search keyword", example=""),
* @SWG\Property(property="OrderBy", type="string", description="", example="LastActivity"),
* @SWG\Property(property="OrderType", type="string", description="", example="DESC"),
* @SWG\Property(property="userLocationFiterOn", type="boolean", description="", example="false")
* ) 

*/

/**
    * @SWG\Post(path="/events/get_event_locations",
    *   tags={"Tags Section"},
    *   summary="Event Location List Api",
    *   description="",
    *   operationId="save",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="Get Location Event List (FilterType => 'AllPublicEvents', 'Suggested', 'MyPastEvent','AllMyEvents','EventICreated','EventIJoined','EventIInvited'), StartDate and EndDate format '2017-11-28'",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\get_event_locations")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\list", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="EntityID", example="1"),
* @SWG\Property(property="PageSize", type="string", description="Entity Type", example="10"),
* @SWG\Property(property="Filter", type="string", description="Event Type", example="AllPublicEvents"),
* @SWG\Property(property="Latitude", type="string", description="Latitue of location", example="22.7167"),
* @SWG\Property(property="Longitude", type="string", description="Longitude of location", example="75.8333"),
* @SWG\Property(property="CategoryIDs", type="array", description="Category Ids array", example={}),
* @SWG\Property(property="EndDate", type="date", description="Endate filter", example=""),
* @SWG\Property(property="StartDate", type="date", description="Startdate filter", example=""),
* @SWG\Property(property="LocationID", type="array", description="Location IDs array", example={1}),
* @SWG\Property(property="SearchKeyword", type="string", description="Search keyword", example=""),
* @SWG\Property(property="OrderBy", type="string", description="", example="LastActivity"),
* @SWG\Property(property="OrderType", type="string", description="", example="DESC"),
* @SWG\Property(property="userLocationFiterOn", type="boolean", description="", example="false")
* ) 

*/

/**
    * @SWG\Post(path="/events/list",
    *   tags={"Tags Section"},
    *   summary="Event List Api",
    *   description="",
    *   operationId="save",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="Get Event List (FilterType => 'AllPublicEvents', 'Suggested', 'MyPastEvent','AllMyEvents','EventICreated','EventIJoined','EventIInvited'), StartDate and EndDate format '2017-11-28'",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\list")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\get_event_categories", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="EntityID", example="1"),
* @SWG\Property(property="PageSize", type="string", description="Entity Type", example="10"),
* @SWG\Property(property="Filter", type="string", description="Event Type", example="AllPublicEvents"),
* @SWG\Property(property="Latitude", type="string", description="Latitue of location", example="22.7167"),
* @SWG\Property(property="Longitude", type="string", description="Longitude of location", example="75.8333"),
* @SWG\Property(property="CategoryIDs", type="array", description="Category Ids array", example={}),
* @SWG\Property(property="EndDate", type="date", description="Endate filter", example=""),
* @SWG\Property(property="StartDate", type="date", description="Startdate filter", example=""),
* @SWG\Property(property="LocationID", type="array", description="Location IDs array", example={}),
* @SWG\Property(property="SearchKeyword", type="string", description="Search keyword", example=""),
* @SWG\Property(property="OrderBy", type="string", description="", example="LastActivity"),
* @SWG\Property(property="OrderType", type="string", description="", example="DESC"),
* @SWG\Property(property="userLocationFiterOn", type="boolean", description="", example="false")
* ) 

*/

/**
    * @SWG\Post(path="/events/get_event_categories",
    *   tags={"Tags Section"},
    *   summary="Event Category List Api",
    *   description="",
    *   operationId="save",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="Get Category Event List (FilterType => 'AllPublicEvents', 'Suggested', 'MyPastEvent','AllMyEvents','EventICreated','EventIJoined','EventIInvited'), StartDate and EndDate format '2017-11-28'",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\get_event_categories")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\add", 
* required={""},
* @SWG\Property(property="Title", type="string", description="EntityID", example="test title"),
* @SWG\Property(property="CategoryID", type="integer", description="Entity Type", example="182"),
* @SWG\Property(property="StartTime", type="string", description="Event Type", example="05:20 PM"),
* @SWG\Property(property="EndTime", type="string", description="", example="09:20 PM"),
* @SWG\Property(property="URL", type="string", description="Longitude of location", example="www.google.com"),
* @SWG\Property(property="Description", type="string", description="", example="test description for event"),
* @SWG\Property(property="Venue", type="string", description="Endate filter", example="indore"),
* @SWG\Property(property="StreetAddress", type="string", description="Startdate filter", example="Indore, Madhya Pradesh, India"),
* @SWG\Property(property="Latitude", type="string", description="Location IDs array", example="22.7195687"),
* @SWG\Property(property="Longitude", type="string", description="Search keyword", example="75.85772580000003"),
* @SWG\Property(property="Privacy", type="string", description="", example="PUBLIC"),
* @SWG\Property(property="StartDate", type="date", description="", example="11/28/2017"),
* @SWG\Property(property="EndDate", type="date", description="", example="11/29/2017"),
* @SWG\Property(property="ModuleID", type="integer", description="", example=""),
* @SWG\Property(property="ModuleEntityID", type="integer", description="", example=""),
* @SWG\Property(property="Location", type="array", description="", example={
    "UniqueID": "1daa36796ce31e1776ce72634b72fc205dddda2d",
    "Latitude": 22.7195687,
    "Longitude": 75.85772580000003,
    "FormattedAddress": "Indore, Madhya Pradesh, India",
    "City": "Indore",
    "State": "Madhya Pradesh",
    "Country": "India",
    "PostalCode": "",
    "Route": "",
    "StateCode": "MP",
    "CountryCode": "IN"
  }),
* ) 

*/

/**
    * @SWG\Post(path="/events/add",
    *   tags={"Tags Section"},
    *   summary="Create Event",
    *   description="",
    *   operationId="save",
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
    *     name="body",
    *     in="body",
    *     description="Create Event",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\add")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */



/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\details", 
* required={""},
* @SWG\Property(property="EventGUID", type="string", description="EntityID", example="a669172a-48a3-b326-6d0c-10308fde3d85"),
* ) 
*/

/**
    * @SWG\Post(path="/events/details",
    *   tags={"Tags Section"},
    *   summary="Get Event Detail",
    *   description="",
    *   operationId="save",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="Get Event Detail",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\details")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\GetUsersPresence", 
* required={""},
* @SWG\Property(property="EventGUID", type="string", description="EntityID", example="a669172a-48a3-b326-6d0c-10308fde3d85"),
* ) 
*/

/**
    * @SWG\Post(path="/events/GetUsersPresence",
    *   tags={"Tags Section"},
    *   summary="Get User Event Prensece",
    *   description="",
    *   operationId="save",
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
    *     name="body",
    *     in="body",
    *     description="Get User Event Prensece",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\GetUsersPresence")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\event_user_detail", 
* required={""},
* @SWG\Property(property="EventGUID", type="string", description="EntityID", example="a669172a-48a3-b326-6d0c-10308fde3d85"),
* ) 
*/

/**
    * @SWG\Post(path="/events/event_user_detail",
    *   tags={"Tags Section"},
    *   summary="Get Event User Detail",
    *   description="",
    *   operationId="save",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="Get event user detail",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\event_user_detail")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\get_event_media", 
* required={""},
* @SWG\Property(property="ModuleEntityGUID", type="string", description="EntityID", example="a669172a-48a3-b326-6d0c-10308fde3d85"),
* @SWG\Property(property="ModuleID", type="integer", description="EntityID", example="14"),
* @SWG\Property(property="PageNo", type="integer", description="EntityID", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="EntityID", example="8"),

* ) 
*/

/**
    * @SWG\Post(path="/media/get_event_media",
    *   tags={"Tags Section"},
    *   summary="Get Event Media Detail",
    *   description="",
    *   operationId="save",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="Get event media",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\get_event_media")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\event_owner_detail", 
* required={""},
* @SWG\Property(property="EventGUID", type="string", description="EntityID", example="a669172a-48a3-b326-6d0c-10308fde3d85"),

* ) 
*/

/**
    * @SWG\Post(path="/events/event_owner_detail",
    *   tags={"Tags Section"},
    *   summary="Get Event Owner Detail",
    *   description="",
    *   operationId="save",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="Get event owner detail",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\event_owner_detail")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\event_attende_list", 
* required={""},
* @SWG\Property(property="EventGUID", type="string", description="EntityID", example="a669172a-48a3-b326-6d0c-10308fde3d85"),
* @SWG\Property(property="PageSize", type="integer", description="EntityID", example="14"),

* ) 
*/

/**
    * @SWG\Post(path="/events/event_attende_list",
    *   tags={"Tags Section"},
    *   summary="Get Event Attendees Detail",
    *   description="",
    *   operationId="save",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="Get event attendees list",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\event_attende_list")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\get_recent_invites", 
* required={""},
* @SWG\Property(property="EventGUID", type="string", description="EntityID", example="a669172a-48a3-b326-6d0c-10308fde3d85"),
* @SWG\Property(property="PageSize", type="integer", description="EntityID", example="5"),

* ) 
*/

/**
    * @SWG\Post(path="/events/get_recent_invites",
    *   tags={"Tags Section"},
    *   summary="Get Event Recent Invited User List",
    *   description="",
    *   operationId="save",
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
    *     name="body",
    *     in="body",
    *     description="Get Recent Invited user list",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\get_recent_invites")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */


/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\members", 
* required={""},
* @SWG\Property(property="EventGUID", type="string", description="EntityID", example="a669172a-48a3-b326-6d0c-10308fde3d85"),
* @SWG\Property(property="PageSize", type="integer", description="EntityID", example="12"),
* @SWG\Property(property="PageNo", type="integer", description="EntityID", example="1"),
* @SWG\Property(property="Filter", type="string", description="EntityID", example="Admin"),
* ) 
*/

/**
    * @SWG\Post(path="/events/members",
    *   tags={"Tags Section"},
    *   summary="Get Event Members List",
    *   description="",
    *   operationId="save",
    *   produces={"application/json"},
    *   consumes={"application/json"},
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
    *     description="Get Member List (Filter => 'Admin', 'Member')",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\members")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
* @SWG\Definition(
* definition="Inclusify\Api\Events\get_invitees_list", 
* required={""},
* @SWG\Property(property="EventGUID", type="string", description="EntityID", example="a669172a-48a3-b326-6d0c-10308fde3d85"),
* @SWG\Property(property="PageSize", type="integer", description="EntityID", example="12"),
* @SWG\Property(property="PageNo", type="integer", description="EntityID", example="1"),
* ) 
*/

/**
    * @SWG\Post(path="/events/get_invitees_list",
    *   tags={"Tags Section"},
    *   summary="Get Invited Members List",
    *   description="",
    *   operationId="save",
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
    *     name="body",
    *     in="body",
    *     description="Get Invited Member List (Filter => 'Admin', 'Member')",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\Api\Events\get_invitees_list")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */