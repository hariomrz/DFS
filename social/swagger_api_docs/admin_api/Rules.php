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
* definition="Inclusify\AdminApi\Rules\GetWelcomeQuestions", 
* required={""},
* @SWG\Property(property="ActivityRuleID", type="integer", description="Rule ID", example="1"),
* ) 
*/

/**
 
 * @SWG\Definition(
* definition="Inclusify\AdminApi\Rules\RuleQuestion", 
* required={""},
* @SWG\Property(property="ActivityRuleID", type="integer", description="Rule ID", example="1"),
* @SWG\Property(property="QuestionActivityID", type="integer", description="QuestionActivityID", example={}),
* ) 
*/

/**
    * @SWG\Post(path="/rules/rule_question",
    *   tags={"Admin Rule Section"},
    *   summary="This api is used to Add Welcome Question for users on Frontend",
    *   description="",
    *   operationId="rule_question",
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
    *     description="Add Welcome Question for users on Frontend.",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Rules\RuleQuestion")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/rules/get_welcome_questions",
    *   tags={"Admin Rule Section"},
    *   summary="This api is used to get already added Welcome Questions for users in admin",
    *   description="",
    *   operationId="rule_question",
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
    *     description="Add Welcome Question for users on Frontend.",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Rules\GetWelcomeQuestions")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */




