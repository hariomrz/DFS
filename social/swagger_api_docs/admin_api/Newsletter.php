
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
* definition="Inclusify\AdminApi\Newsletter\AddNewsletterSubscriber", 
* required={""},
* @SWG\Property(property="Email", type="String", description="Email", example="subscriber1@milinator.com"),
* @SWG\Property(property="Name", type="String", description="Name", example="Subscriber User"),
* @SWG\Property(property="Gender", type="integer", description="Gender", example="0"),
* @SWG\Property(property="DOB", type="String", description="DOB", example="1990/09/22"),
* @SWG\Property(property="UserID", type="String", description="UserID", example="15"),
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Newsletter\CreateNewsletterGroup", 
* required={""},
* @SWG\Property(property="Name", type="String", description="Name", example="Website Active Users"),
* @SWG\Property(property="Description", type="String", description="Description", example="Website Active Users"),
* @SWG\Property(property="NewsLetterGroupID", type="Integer", description="NewsLetterGroupID", example="15"),
* @SWG\Property(property="NewsLetterSubscriberID", type="String", description="NewsLetterSubscriberID", example={}),
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Newsletter\UnsubscribeNewsletter", 
* required={""},
* @SWG\Property(property="NewsLetterSubscriberGUID", type="String", description="NewsLetterSubscriberGUID", example="294357af-de88-4896-85bc-8057ba7325c3"),
* ) 
*/

/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Newsletter\RemoveSubcribersFromGroup", 
* required={""},
* @SWG\Property(property="NewsLetterGroupID", type="String", description="NewsLetterGroupID", example="15"),
@SWG\Property(property="NewsLetterSubscriberID", type="String", description="NewsLetterSubscriberID", example={}),
* ) 
*/


/**
    * @SWG\Post(path="/newsletter/add_newsletter_subscriber",
    *   tags={"Admin User Section"},
    *   summary="This api is used to add newsletter subscriber",
    *   description="",
    *   operationId="add_newsletter_subscriber",
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
    *     description="Add newsletter subscriber.",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Newsletter\AddNewsletterSubscriber")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */
/**
    * @SWG\Post(path="/newsletter/create_newsletter_group",
    *   tags={"Admin User Section"},
    *   summary="This api is used to create/update newsletter group(list)",
    *   description="",
    *   operationId="create_newsletter_group",
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
    *     description="Create/Update newsletter group(list).",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Newsletter\CreateNewsletterGroup")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/newsletter/unsubscribe_newsletter",
    *   tags={"Admin User Section"},
    *   summary="This api is used to unsubscribe newsletter",
    *   description="",
    *   operationId="unsubscribe_newsletter",
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
    *     description="Unscriber newsletter.",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Newsletter\UnsubscribeNewsletter")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */

/**
    * @SWG\Post(path="/newsletter/remove_subscribers_from_group",
    *   tags={"Admin User Section"},
    *   summary="This api is used to remove subscriber from newsletter group",
    *   description="",
    *   operationId="remove_subscribers_from_group",
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
    *     description="Remove subscriber from newsletter group.",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Newsletter\RemoveSubcribersFromGroup")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */






/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Newsletter\demo", 
* required={""},
* @SWG\Property(property="Email", type="String", description="Email", example="subscriber1@milinator.com"),
* @SWG\Property(property="Name", type="String", description="Name", example="Subscriber User"),
* @SWG\Property(property="Gender", type="integer", description="Gender", example="0"),
* @SWG\Property(property="DOB", type="String", description="DOB", example="1990/09/22"),
* @SWG\Property(property="UserID", type="String", description="UserID", example="15"),
* ) 
*/


/**
    * @SWG\Post(path="/newsletter/demo",
    *   tags={"Admin demo"},
    *   summary="This api is used to remove subscriber from newsletter group",
    *   description="",
    *   operationId="demo",
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
    *     description="Remove subscriber from newsletter group.",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Newsletter\demo")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */







/**
* @SWG\Definition(
* definition="Inclusify\AdminApi\Newsletter\update_mailchimp_subscriber_id", 
* required={""},
* @SWG\Property(property="Email", type="String", description="Email", example="subscriber1@milinator.com")
* ) 
*/

/**
    * @SWG\Post(path="/newsletter/update_mailchimp_subscriber_id",
    *   tags={"Admin update_mailchimp_subscriber_id"},
    *   summary="This api is used to remove subscriber from newsletter group",
    *   description="",
    *   operationId="update_mailchimp_subscriber_id",
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
    *     description="Remove subscriber from newsletter group.",
    *     required=true,
    *     @SWG\Schema(ref="#/definitions/Inclusify\AdminApi\Newsletter\update_mailchimp_subscriber_id")
    *   ),
    *   @SWG\Response(response=200, description="success message with data array"),
    *   @SWG\Response(response=500, description="Error Message")
    * )
    */