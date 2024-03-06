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
* definition="Inclusify\Api\Quiz\index",
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="Filter", type="integer", description="Filter quiz list, 0 - Upcoming, 1 - Completed, 2 - All", example="0"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Quiz\short_link",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="To get particular quiz short url", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Quiz\details",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="To get particular quiz details", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Quiz\get_predictions",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="To get particular quiz details", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Quiz\make_prediction",
* required={"QuestionGUID","OptionGUID"},
* @SWG\Property(property="QuestionGUID", type="string", description="Question GUID", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="OptionGUID", type="string", description="Option GUID", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Quiz\get_unread_prediction_count",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="To get unread prediction count", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Quiz\toggle_follow",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="To follow/unfollow quiz", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Quiz\leaderboard",
* required={"QuizGUID"},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* @SWG\Property(property="QuizGUID", type="string", description="To get particular quiz details", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\Api\Quiz\user_predicted_prediction",
* required={"QuizGUID","UserGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="Quiz GUID", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="UserGUID", type="string", description="User GUID", example="e3218947d111-6703-aefb-dbc8-1ebb78a8"),
* )
*/


/**
* @SWG\Post(path="/quiz/index",
*   tags={"Quiz"},
*   summary="This api is used to get quiz list.",
*   description="This api is used to get quiz list",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Quiz\index")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/details",
*   tags={"Quiz"},
*   summary="This api is used to quiz details.",
*   description="This api is used to quiz details.",
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
*     name="Loginsessionkey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Quiz\details")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/short_link",
*   tags={"Quiz"},
*   summary="This api is used to get quiz url.",
*   description="This api is used to get quiz url.",
*   operationId="short_link",
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
*     required=false,
*     type="string"
*   ),
*   @SWG\Parameter(
*     name="body",
*     in="body",
*     description="",
*     required=true,
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Quiz\short_link")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/get_predictions",
*   tags={"Prediction"},
*   summary="This api is used to get predictions.",
*   description="This api is used to get predictions.",
*   operationId="get_predictions",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Quiz\get_predictions")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/make_prediction",
*   tags={"Prediction"},
*   summary="This api is used to make prediction.",
*   description="This api is used to make prediction.",
*   operationId="make_prediction",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Quiz\make_prediction")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/get_unread_prediction_count",
*   tags={"Prediction"},
*   summary="This api is used to get unread prediction count.",
*   description="This api is used to get unread prediction count.",
*   operationId="get_unread_prediction_count",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Quiz\get_unread_prediction_count")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/toggle_follow",
*   tags={"Follow/Unfollow"},
*   summary="This api is used to follow/unfollow quiz.",
*   description="This api is used to follow/unfollow quiz.",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Quiz\toggle_follow")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/


/**
* @SWG\Post(path="/quiz/leaderboard",
*   tags={"Leaderboard"},
*   summary="This api is used to quiz leaderboard.",
*   description="This api is used to quiz leaderboard.",
*   operationId="leaderboard",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Quiz\leaderboard")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/user_predicted_prediction",
*   tags={"User Predicted Prediction"},
*   summary="This api is used to get user predicted prediction.",
*   description="This api is used to get user predicted prediction.",
*   operationId="user_predicted_prediction",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\Api\Quiz\user_predicted_prediction")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/