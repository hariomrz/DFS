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
* definition="Inclusify\AdminApi\Quiz\add",
* required={"SponsorGUID", "Title", "StartDate", "EndDate", "MaximumQuestion", "MaximumPost"},
* @SWG\Property(property="SponsorGUID", type="string", description="Sponsor guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="SponsorAbout", type="string", description="Sponsor about", example="Details about sponsor"),
* @SWG\Property(property="Title", type="string", description="Quiz Title", example="Potato was introduced to Europe by?"),
* @SWG\Property(property="Description", type="string", description="Quiz Description", example="Potato was introduced to Europe by?"),
* @SWG\Property(property="StartDate", type="string", description="Quiz Start Date", example="2021-03-23 12:30:00"),
* @SWG\Property(property="EndDate", type="string", description="Quiz Expiry Date", example="2021-03-28 12:30:00"),
* @SWG\Property(property="MaximumPost", type="integer", description="Maximum allowed post", example="5"),
* @SWG\Property(property="MaximumQuestion", type="integer", description="Maximum allowed question", example="5"),
* @SWG\Property(property="LogoID", type="integer", description="Logo ID", example="5"),
* @SWG\Property(property="PreviewID", type="integer", description="Preview ID", example="5"),
* @SWG\Property(property="BannerID", type="integer", description="Banner ID", example="5"),
* @SWG\Property(property="AboutImageID", type="integer", description="About Image ID", example="5"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\update",
* required={"QuizGUID", "Title", "StartDate", "EndDate", "MaximumQuestion", "MaximumPost"},
* @SWG\Property(property="QuizGUID", type="string", description="Quiz guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="SponsorAbout", type="string", description="Sponsor about", example="Details about sponsor"),
* @SWG\Property(property="Title", type="string", description="Quiz Title", example="Potato was introduced to Europe by?"),
* @SWG\Property(property="Description", type="string", description="Quiz Description", example="Potato was introduced to Europe by?"),
* @SWG\Property(property="StartDate", type="string", description="Quiz Start Date", example="2021-03-23 12:30:00"),
* @SWG\Property(property="EndDate", type="string", description="Quiz Expiry Date", example="2021-03-28 12:30:00"),
* @SWG\Property(property="MaximumPost", type="integer", description="Maximum allowed post", example="5"),
* @SWG\Property(property="MaximumQuestion", type="integer", description="Maximum allowed question", example="5"),
* @SWG\Property(property="LogoID", type="integer", description="Logo ID", example="5"),
* @SWG\Property(property="PreviewID", type="integer", description="Preview ID", example="5"),
* @SWG\Property(property="BannerID", type="integer", description="Banner ID", example="5"),
* @SWG\Property(property="AboutImageID", type="integer", description="About Image ID", example="5"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\add_rules",
* required={"QuizGUID","Rules"},
* @SWG\Property(property="QuizGUID", type="string", description="Quiz guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="Rules", type="array", description="Array of quiz rules",@SWG\Items(
*     type="object",
*     @SWG\Property(property="Title", type="string",example="Title"),
*     @SWG\Property(property="Description", type="string",example="Description"),
*     )
*   ),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\set_prizes",
* required={"QuizGUID","DistributionDetail", "AllowPrize"},
* @SWG\Property(property="QuizGUID", type="string", description="Quiz guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="AllowPrize", type="integer", description="Allow Prize 0 - No, 1 - Yes", example="1"),
* @SWG\Property(property="DistributionDetail", type="array", description="Prize distribution detail",@SWG\Items(
*     type="object",
*     @SWG\Property(property="min", type="integer",example="1"),
*     @SWG\Property(property="max", type="integer",example="1"),
*     @SWG\Property(property="amount", type="string",example="DSLR Camera"),
*     @SWG\Property(property="prize_type", type="integer",example="1", description="1-Real Cash, 2-Voucher, 3- Certificate"),
*     )
*   ),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\delete",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="Quiz guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\upload_image",
* required={"ImageData"},
* @SWG\Property(property="ImageData", type="string", description="image raw data", example="d111e3218947-aefb-dbc8-6703-1ebb78a8")
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\index",
* required={""},
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\details",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="To get particular quiz details", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\announce_winner",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="Quiz GUID", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\add_prediction",
* required={"QuizGUID", "Title", "EndDate", "Options"},
* @SWG\Property(property="QuizGUID", type="string", description="Quiz guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="Title", type="string", description="Question Title", example="Potato was introduced to Europe by?"),
* @SWG\Property(property="Description", type="string", description="Question Description", example="Potato was introduced to Europe by"),
* @SWG\Property(property="EndDate", type="string", description="Question Expiry Date", example="2021-03-08 12:30:00"),
* @SWG\Property(property="Options", type="array", description="Options, array of question options",@SWG\Items(
*     type="object",
*     @SWG\Property(property="text", type="string",example="php")
*     )
*   ),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\update_prediction",
* required={"QuestionGUID", "Title", "EndDate", "Options"},
* @SWG\Property(property="QuestionGUID", type="string", description="Question guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="Title", type="string", description="Question Title", example="Potato was introduced to Europe by?"),
* @SWG\Property(property="Description", type="string", description="Question Description", example="Potato was introduced to Europe by"),
* @SWG\Property(property="EndDate", type="string", description="Question Expiry Date", example="2021-03-08 12:30:00"),
* @SWG\Property(property="Options", type="array", description="Options, array of question options",@SWG\Items(
*     type="object",
*     @SWG\Property(property="text", type="string",example="php")
*     )
*   ),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\get_predictions",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="To get particular quiz details", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\submit_prediction_answer",
* required={"QuestionGUID", "OptionGUID"},
* @SWG\Property(property="QuestionGUID", type="string", description="Question guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="OptionGUID", type="string", description="Option guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="ProofDescription", type="string", description="Proof description", example="Description for correct answer"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\get_predictions",
* required={"QuizGUID"},
* @SWG\Property(property="QuizGUID", type="string", description="To get particular quiz details", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\delete_prediction",
* required={"QuestionGUID"},
* @SWG\Property(property="QuestionGUID", type="string", description="Question guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* )
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Quiz\get_prediction_participants",
* required={"QuestionGUID"},
* @SWG\Property(property="QuestionGUID", type="string", description="Question guid", example="d111e3218947-aefb-dbc8-6703-1ebb78a8"),
* @SWG\Property(property="PageNo", type="integer", description="Page Number", example="1"),
* @SWG\Property(property="PageSize", type="integer", description="Page Size", example="10"),
* )
*/

/**
* @SWG\Post(path="/quiz/add",
*   tags={"Quiz"},
*   summary="This api is used to add quiz.",
*   description="This api is used to quiz.",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\add")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/update",
*   tags={"Quiz"},
*   summary="This api is used to update quiz.",
*   description="This api is used to update quiz.",
*   operationId="update",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\update")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/add_rules",
*   tags={"Quiz"},
*   summary="This api is used to add quiz rules.",
*   description="This api is used to add quiz rules.",
*   operationId="add_rules",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\add_rules")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/set_prizes",
*   tags={"Quiz"},
*   summary="This api is used to set prizes for quiz.",
*   description="This api is used to set prizes for quiz.",
*   operationId="set_prizes",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\set_prizes")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/delete",
*   tags={"Quiz"},
*   summary="This api is used to delete quiz.",
*   description="This api is used to delete quiz.",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\delete")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/upload_image",
*   tags={"Quiz"},
*   summary="This api is used to upload image.",
*   description="This api is used to upload image.",
*   operationId="upload_image",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\upload_image")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\index")
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\details")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/announce_winner",
*   tags={"Quiz"},
*   summary="This api is used to announce quiz winner.",
*   description="This api is used to announce quiz winner.",
*   operationId="announce_winner",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\announce_winner")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/add_prediction",
*   tags={"Prediction"},
*   summary="This api is used to add prediction for particular quiz.",
*   description="This api is used to add prediction for particular quiz.",
*   operationId="add_prediction",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\add_prediction")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/update_prediction",
*   tags={"Prediction"},
*   summary="This api is used to update prediction.",
*   description="This api is used to update prediction.",
*   operationId="update_prediction",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\update_prediction")
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\get_predictions")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/submit_prediction_answer",
*   tags={"Prediction"},
*   summary="This api is used to submit prediction answer.",
*   description="This api is used to submit prediction answer.",
*   operationId="submit_prediction_answer",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\submit_prediction_answer")
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\get_predictions")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/delete_prediction",
*   tags={"Prediction"},
*   summary="This api is used to delete prediction.",
*   description="This api is used to delete prediction.",
*   operationId="delete_prediction",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\delete_prediction")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/

/**
* @SWG\Post(path="/quiz/get_prediction_participants",
*   tags={"Prediction"},
*   summary="This api is used to get prediction participants.",
*   description="This api is used to get prediction participants.",
*   operationId="get_prediction_participants",
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
*     name="AdminLoginSessionKey",
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
*     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Quiz\get_prediction_participants")
*   ),
*   @SWG\Response(response=200, description="success message with data array"),
*   @SWG\Response(response=500, description="Error Message")
* )
*/