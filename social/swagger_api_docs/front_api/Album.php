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
* definition="Inclusify\Api\Album\add", 
* required={"AlbumName"},
* @SWG\Property(property="AlbumName", type="string", description="Name of Album", example="Bhopu"),
* @SWG\Property(property="Description", type="string", description="Description of Album", example="Bhopu"),
* @SWG\Property(property="Media", type="array", description="Media, array of post Media",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="Location", type="string",example="Mumbai, Maharashtra, India"),
*     @SWG\Property(property="isCoverPic", type="integer",example="1"),
*     @SWG\Property(property="Description", type="string",example="Bhopu"),
*     )
*   ),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\edit", 
* required={"AlbumGUID", "AlbumName"},
* @SWG\Property(property="AlbumGUID", type="string", description="GUID of Album", example="321ss-25asA-fd42w-872hj"),
* @SWG\Property(property="AlbumName", type="string", description="Name of Album", example="Bhopu"),
* @SWG\Property(property="Description", type="string", description="Description of Album", example="Bhopu"),
* @SWG\Property(property="Media", type="array", description="Media, array of post Media",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="Location", type="string",example="Mumbai, Maharashtra, India"),
*     @SWG\Property(property="isCoverPic", type="integer",example="1"),
*     @SWG\Property(property="Description", type="string",example="Bhopu"),
*     )
*   ),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\list", 
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="PageSize", example="10"),
* @SWG\Property(property="SortBy", type="integer", description="Sort By, it may be 1 for recent / 2 for popular", example="1"),
* @SWG\Property(property="OrderBy", type="string", description="Order By, it may be ASC or DESC", example="DESC"),
* @SWG\Property(property="IsFeatured", type="integer", description="1 - Get only Featured Album, 0 - All", example="0"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\delete", 
* required={"AlbumGUID"},
* @SWG\Property(property="AlbumGUID", type="string", description="GUID of Album", example="321ss-25asA-fd42w-872hj"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\mark_as_feature", 
* required={"AlbumGUID"},
* @SWG\Property(property="AlbumGUID", type="string", description="GUID of Album", example="321ss-25asA-fd42w-872hj"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\remove_as_feature", 
* required={"AlbumGUID"},
* @SWG\Property(property="AlbumGUID", type="string", description="GUID of Album", example="321ss-25asA-fd42w-872hj"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\set_privacy", 
* required={"AlbumGUID"},
* @SWG\Property(property="Visibility", type="integer", description="Album Visibility: 1 - All, 2 - Only Admin, 3 - None", example="1"),
* @SWG\Property(property="AlbumGUID", type="string", description="GUID of Album", example="321ss-25asA-fd42w-872hj"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\add_media", 
* required={"AlbumGUID"},
* @SWG\Property(property="AlbumGUID", type="string", description="guid of Album", example="321ss-25asA-fd42w-872hj"),
* @SWG\Property(property="Media", type="array", description="Media, array of post Media",@SWG\Items(
*     type="object",
*     @SWG\Property(property="MediaGUID", type="string",example="f7d1cdef-ef64-8f5e-3898-043eb546437d"),
*     @SWG\Property(property="Location", type="string",example="Indore, MP India"),
*     @SWG\Property(property="Description", type="string",example="Bhopu"),
*     )
*   ),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\list_media", 
* required={"AlbumGUID"},
* @SWG\Property(property="AlbumGUID", type="string", description="guid of Album", example="321ss-25asA-fd42w-872hj"),
* @SWG\Property(property="PageNo", type="integer", description="PageNo", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="PageSize", example="10"),
* @SWG\Property(property="SortBy", type="integer", description="Sort By, it may be 1 for recent / 2 for popular", example="1"),
* @SWG\Property(property="OrderBy", type="string", description="Order By, it may be ASC or DESC", example="DESC"),
* @SWG\Property(property="StartDate", type="string", description="Start Date, show media which are created on & after this date", example="2020-01-01"),
* @SWG\Property(property="EndDate", type="string", description="End Date, show media which are created on & before this date", example="2020-10-14"),
* @SWG\Property(property="Verified", type="integer", description="Show verified media - 1, non verified media = 0, all media - 2", example="2"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\set_cover_media", 
* required={"AlbumGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="GUID of Media", example="a775681c-9419-159b-57f7-7770f6e58e18"),
* @SWG\Property(property="AlbumGUID", type="string", description="GUID of Album", example="321ss-25asA-fd42w-872hj"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\update_media_location", 
* required={"MediaGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="GUID of Media", example="a775681c-9419-159b-57f7-7770f6e58e18"),
* @SWG\Property(property="Location", type="string", description="Location of Media", example="Indore, MP India"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\delete_media", 
* required={"AlbumGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="GUID of Media", example="a775681c-9419-159b-57f7-7770f6e58e18"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\change_media_album", 
* required={"MediaGUID", "AlbumGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="GUID of Media", example="a775681c-9419-159b-57f7-7770f6e58e18"),
* @SWG\Property(property="AlbumGUID", type="string", description="GUID of Album", example="321ss-25asA-fd42w-872hj"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\toggle_verify", 
* required={"MediaGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="GUID of Media", example="a775681c-9419-159b-57f7-7770f6e58e18"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Album\send_notification", 
* required={"MediaGUID"},
* @SWG\Property(property="MediaGUID", type="string", description="GUID of Media", example="a775681c-9419-159b-57f7-7770f6e58e18"),
* )
*/

/**
* @SWG\Post(path="/album/add",
*   tags={"Album"},
*   summary="This api is used to create album.",
*   description="",
*   operationId="add",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\add")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/edit",
*   tags={"Album"},
*   summary="This api is used to update album.",
*   description="",
*   operationId="edit",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\edit")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/album/list",
*   tags={"Album"},
*   summary="This api is used to get album list.",
*   description="",
*   operationId="list",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\list")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/delete",
*   tags={"Album"},
*   summary="This api is used to delete album.",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\delete")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/mark_as_feature",
*   tags={"Album"},
*   summary="This api is used to mark an album as featured.",
*   description="",
*   operationId="mark_as_feature",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\mark_as_feature")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/remove_as_feature",
*   tags={"Album"},
*   summary="This api is used to remove an album from featured.",
*   description="",
*   operationId="remove_as_feature",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\remove_as_feature")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/set_privacy",
*   tags={"Album"},
*   summary="This api is used to update album privacy.",
*   description="",
*   operationId="set_privacy",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\set_privacy")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/add_media",
*   tags={"Album"},
*   summary="This api is used to add album media.",
*   description="",
*   operationId="add_media",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\add_media")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/list_media",
*   tags={"Album"},
*   summary="This api is used to get album media.",
*   description="",
*   operationId="list_media",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\list_media")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/set_cover_media",
*   tags={"Album"},
*   summary="This api is used to set cover media for album.",
*   description="",
*   operationId="set_cover_media",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\set_cover_media")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/update_media_location",
*   tags={"Album"},
*   summary="This api is used to update media location.",
*   description="",
*   operationId="update_media_location",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\update_media_location")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/delete_media",
*   tags={"Album"},
*   summary="This api is used to delete album media.",
*   description="",
*   operationId="delete_media",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\delete_media")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/change_media_album",
*   tags={"Album"},
*   summary="This api is used to change media album.",
*   description="",
*   operationId="change_media_album",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\change_media_album")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/toggle_verify",
*   tags={"Album"},
*   summary="This api is used to verify / unverify media.",
*   description="",
*   operationId="toggle_verify",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\toggle_verify")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/album/send_notification",
*   tags={"Album"},
*   summary="This api is used to send media push notification.",
*   description="",
*   operationId="send_notification",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Album\send_notification")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/