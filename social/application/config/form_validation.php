<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$config = array(
    'api/contest/create' => array(
        array(
            'field' => 'Title',
            'label' => 'title',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Description',
            'label' => 'description',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Image',
            'label' => 'image',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Heading',
            'label' => 'heading',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'NoOfSeats',
            'label' => 'no of seats',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'StartDate',
            'label' => 'start date',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EndDate',
            'label' => 'end date',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'WinnerAnnouncementDate',
            'label' => 'winner announcement date',
            'rules' => 'trim|required'
        )
    ),
    'api/contest/get_participant_list' => array(
        array(
            'field' => 'ActivityID',
            'label' => 'activity id',
            'rules' => 'trim|required'
        )
    ),
    'api/contest/mark_participant_as_winner' => array(
        array(
            'field' => 'ContestID',
            'label' => 'contest id',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Participants[]',
            'label' => 'participants',
            'rules' => 'trim|required'
        )
    ),
    'api/contest/add_participant' => array(
        array(
            'field' => 'ActivityID',
            'label' => 'activity id',
            'rules' => 'trim|required'
        )
    ),
    'api/contest/delete_participant' => array(
        array(
            'field' => 'ContestID',
            'label' => 'contest id',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ParticipantID',
            'label' => 'participant id',
            'rules' => 'trim|required'
        )
    ),
    'api/contest/delete_contest' => array(
        array(
            'field' => 'ContestID',
            'label' => 'contest id',
            'rules' => 'trim|required'
        )
    ),
    'api/login' => array(
        array(
            'field' => 'Mobile',
            'label' => 'mobile',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Password',
            'label' => 'password',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'DeviceType',
            'label' => 'device type',
            'rules' => 'trim|required|callback_validate_DeviceType'
        )
    ),
    'api/group/newGroupListing' => array(
        array(
            'field' => 'GroupType',
            'label' => 'group type',
            'rules' => 'trim|required'
        )
    ),
    'api/group/set_member_permission' => array(
        array(
            'field' => 'GroupID',
            'label' => 'group id',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'module id',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleEntityID',
            'label' => 'module entity id',
            'rules' => 'trim|required'
        ) 
    ),
    'api/send_native_invitations' => array(
        array(
            'field' => 'UserSocialId[]',
            'label' => 'email',
            'rules' => 'trim|required|valid_email'
        ),
        array(
            'field' => 'Message',
            'label' => 'message',
            'rules' => 'trim|required'
        )
    ),
    'api/users/previous_profile_pictures' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'module id',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'module entity guid',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
    ),    
    'api/users/remove_follow' => array(
        array(
            'field' => 'UserGUID',
            'label' => 'user guid',
            'rules' => 'required'
        )
    ),
    'admin_api/users/create_account' => array(
        array(
            'field' => 'FirstName',
            'label' => 'firstname',
            'rules' => 'trim|max_length[50]'
        ),
        array(
            'field' => 'LastName',
            'label' => 'lastname',
            'rules' => 'trim|max_length[50]'
        )
    ),
    'api/native_signup' => array(
        array(
            'field' => 'FullName',
            'label' => 'name',
            'rules' => "trim|required|max_length[50]|regex_match[/^[a-zA-Z.@' ]+$/]"
        ),
        array(
            'field' => 'Mobile',
            'label' => 'mobile',
            'rules' => 'trim|required|numeric|min_length[10]|max_length[10]|callback_is_unique_value[' . USERS . '.PhoneNumber.StatusID.PhoneNumber]'
        ),
        array(
            'field' => 'Password',
            'label' => 'password',
            'rules' => 'trim|required|min_length[6]|max_length[15]'
        )
    ),
    'api/socail_signup' => array(
        array(
            'field' => 'FirstName',
            'label' => 'firstname',
            'rules' => 'trim|required|max_length[50]'
        ),
/*        array(
            'field' => 'LastName',
            'label' => 'lastname',
            'rules' => 'trim|required|max_length[50]'
        ),*/
        array(
            'field' => 'DeviceType',
            'label' => 'devicetype',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'SocialType',
            'label' => 'source type',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'UserSocialID',
            'label' => 'username',
            'rules' => 'trim|required'
        )
    ),
    'api/users/update_profile_web' => array(
        array(
            'field' => 'FirstName',
            'label' => 'firstname',
            'rules' => "trim|required|max_length[50]|regex_match[/^[a-zA-Z' ]+$/]"
        ),
        array(
            'field' => 'LastName',
            'label' => 'lastname',
            'rules' => "trim|required|max_length[50]|regex_match[/^[a-zA-Z' ]+$/]"
        )
    ),
    'api/users/update_profile' => array(
        array(
            'field' => 'FullName',
            'label' => 'name',
            'rules' => "trim|required|max_length[50]|regex_match[/^[a-zA-Z.@' ]+$/]"
        ),
        array(
            'field' => 'Address',
            'label' => 'address',
            'rules' => "trim|max_length[200]"
        ),
        array(
            'field' => 'Occupation',
            'label' => 'occupation',
            'rules' => "trim|max_length[100]"
        )
    ),
    'api/changepassword/set' => array(
        array(
            'field' => 'PasswordNew',
            'label' => 'new password',
            'rules' => 'trim|required|min_length[6]|max_length[15]'
        )
    ),
    'api/group/create' => array(
        array(
            'field' => 'GroupName',
            'label' => 'Group Name',
            'rules' => 'trim|required|min_length[2]|max_length[100]|callback_check_all_num'
        ),
        array(
            'field' => 'CategoryIds[]',
            'label' => 'Category',
            'rules' => 'required'
        ),
        array(
            'field' => 'GroupDescription',
            'label' => 'Group Description',
            'rules' => 'trim|required|min_length[2]|max_length[400]'
        )
        ,
        array(
            'field' => 'IsPublic',
            'label' => 'Group type',
            'rules' => 'required'
        )
    ),
    'api/group/update' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'Group GUID',
            'rules' => 'trim|required|validate_guid[1]'
        ),
        array(
            'field' => 'GroupName',
            'label' => 'Group Name',
            'rules' => 'trim|required|min_length[2]|max_length[100]|callback_check_all_num'
        ),
        array(
            'field' => 'CategoryIds[]',
            'label' => 'Category',
            'rules' => 'required'
        ),
        array(
            'field' => 'GroupDescription',
            'label' => 'Group Description',
            'rules' => 'trim|required|min_length[2]|max_length[400]'
        )
        ,
        array(
            'field' => 'IsPublic',
            'label' => 'Group type',
            'rules' => 'required'
        )
    ),
    'api/group/update_informal' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'Group GUID',
            'rules' => 'trim|required|validate_guid[1]'
        ),
    ),
    'api/group/groupidrequired' => array(
        array(
            'field' => 'GroupID',
            'label' => 'group ID',
            'rules' => 'trim|required'
        )
    ),
    'api/group/removeMembersGroup' => array(
        array(
            'field' => 'DeleteMemberID',
            'label' => 'member ID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'GroupID',
            'label' => 'group ID',
            'rules' => 'trim|required'
        )
    ),
    'api/group/addMemberToGroup' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'UsersGUID[]',
            'label' => 'UsersGUID',
            'rules' => 'required'
        ),
        array(
            'field' => 'AddForceFully',
            'label' => 'Add ForceFully',
            'rules' => 'required'
        )
    ),
    'api/group/joinPublicGroup' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        )
    ),
    'api/group/groupAcceptDenyRequest' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'UserGUID',
            'label' => 'UserGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'StatusID',
            'label' => 'StatusID',
            'rules' => 'trim|required'
        )
    ),
    'api/group/groupDropOutAction' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
    ),
    'api/group/toggle_user_role' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        ),
        array(
            'field' => 'RoleAction',
            'label' => 'RoleAction',
            'rules' => 'trim|required'
        )
    ),
    'api/group/groupMembers' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Type',
            'label' => 'Type',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'Offset',
            'label' => 'Offset',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'Limit',
            'label' => 'Limit',
            'rules' => 'trim|required'
        )
    ),
    'api/group/groupListing' => array(
        array(
            'field' => 'ListingType',
            'label' => 'ListingType',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Offset',
            'label' => 'Offset',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'Limit',
            'label' => 'Limit',
            'rules' => 'trim|required'
        )
    ),
    'api/group/can_post_on_wall' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        )
        ,
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
        ,
        array(
            'field' => 'CanPostOnWall',
            'label' => 'CanPostOnWall',
            'rules' => 'trim|required'
        )
    ),
    'api/toggleLike' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/getAllComments' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/removeActivity' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/restoreActivity' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/addComment' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'activity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/deleteComment' => array(
        array(
            'field' => 'CommentGUID',
            'label' => 'Comment GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/privacyChange' => array(
        array(
            'field' => 'ActivityGUID',
            'label' => 'activity GUID',
            'rules' => 'trim|required'
        ), array(
            'field' => 'Visibility',
            'label' => 'privacy status',
            'rules' => 'trim|required'
        )
    ),
    'api/users/attach_social_account' => array(
        array(
            'field' => 'SocialType',
            'label' => 'social type',
            'rules' => 'trim|required'
        ), array(
            'field' => 'SocialID',
            'label' => 'social ID',
            'rules' => 'trim|required'
        ), array(
            'field' => 'profileUrl',
            'label' => 'profile Url',
            'rules' => 'trim|required'
        )
    ),
    'api/users/detach_social_account' => array(
        array(
            'field' => 'SocialType',
            'label' => 'social type',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/getLikeDetails' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/sharePost' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'module ID',
            'rules' => 'trim|required|numeric'
        )
    ),
    'api/toggle_favourite' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        )
    ),
    'api/subscribe' => array(
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/subscribe/unsubscribe' => array(
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/toggle_sticky' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/commentStatus' => array(        
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        )
    ),
    'admin_api/activity/createWallPost' => array(
        array(
            'field' => 'Visibility',
            'label' => 'visibility',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Commentable',
            'label' => 'commentable',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'PostContent',
            'label' => 'post content',
            'rules' => 'trim'
        ),
        array(
            'field' => 'PostTitle',
            'label' => 'post title',
            'rules' => 'trim|max_length[140]'
        )
    ),
    'api/activity/createWallPost' => array(
        array(
            'field' => 'Visibility',
            'label' => 'visibility',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Commentable',
            'label' => 'commentable',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'PostContent',
            'label' => 'post content',
            'rules' => 'trim'
        ),
        array(
            'field' => 'PostTitle',
            'label' => 'post title',
            'rules' => 'trim'
        ),
        
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'numeric'
        ),
        
    ),
    'api/activity' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity guid',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'module id',
            'rules' => 'trim|required|numeric'
        )
    ),
    'api/public_feed' => array(
        array(
            'field' => 'ActivityGUID',
            'label' => 'activity guid',
            'rules' => 'trim|required'
        )
    ),
    'api/subscribe/toggle_subscribe' => array(
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityGUID',
            'label' => 'entity guid',
            'rules' => 'trim|required'
        )
    ),
    'api/flag' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'FlagReason',
            'label' => 'flag reason',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/blockUser' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'module id',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'module entity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/activity/approveFlagActivity' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        )
    )
    ,
    'api/category/get_categories' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'required|numeric'
        )
    ),
    'api/category/updatecategories' => array(
        array(
            'field' => 'EntityID',
            'label' => 'EntityID',
            'rules' => 'required|numeric'
        ),
        array(
            'field' => 'CategoryIDS',
            'label' => 'CategoryIDS',
            'rules' => 'required'
        )
    ),
    'api/event/saveEvent' => array(
        array(
            'field' => 'Title',
            'label' => 'Event Titile',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'StartDate',
            'label' => 'Event Start Date',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'StartTime',
            'label' => 'Event Start Time',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'EndDate',
            'label' => 'Event End Date',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'EndTime',
            'label' => 'Event End Time',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'Venue',
            'label' => 'Venue',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'Location',
            'label' => 'Event Location',
            'rules' => 'required'
        )
        ,
        array(
            'field' => 'CategoryID',
            'label' => 'Category',
            'rules' => 'required'
        )
    ),
    'api/events/add' => array(
        array(
            'field' => 'Title',
            'label' => 'Event Titile',
            'rules' => 'trim|required|min_length[2]|max_length[100]'
        ),
        array(
            'field' => 'CategoryID',
            'label' => 'Category',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'StartDate',
            'label' => 'Event Start Date',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'StartTime',
            'label' => 'Event Start Time',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EndDate',
            'label' => 'Event End Date',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EndTime',
            'label' => 'Event End Time',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'URL',
            'label' => 'URL',
            'rules' => 'trim|valid_url'
        ),
        array(
            'field' => 'Summary',
            'label' => 'Event Summary',
            'rules' => 'trim|required|min_length[2]|max_length[250]'
        ),
        array(
            'field' => 'Description',
            'label' => 'Event Description',
            'rules' => 'trim|required|min_length[2]|max_length[400]'
        ),        
        array(
            'field' => 'Venue',
            'label' => 'Venue',
            'rules' => 'trim|required'
        ),
        /*array(
            'field' => 'Location[]',
            'label' => 'Event Location',
            'rules' => 'callback_is_validate_location'
        ),*/
        array(
            'field' => 'CategoryID',
            'label' => 'Category',
            'rules' => 'required'
        )
    ),
    'api/events/edit' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'event guid',
            'rules' => 'trim|required|min_length[2]|max_length[100]'
        ),
        array(
            'field' => 'Title',
            'label' => 'Event Titile',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'CategoryID',
            'label' => 'Category',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Summary',
            'label' => 'Event Summary',
            'rules' => 'trim|required|min_length[2]|max_length[250]'
        ),
        array(
            'field' => 'Description',
            'label' => 'Event Description',
            'rules' => 'trim|required|min_length[2]|max_length[400]'
        ),
        array(
            'field' => 'StartDate',
            'label' => 'Event Start Date',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'StartTime',
            'label' => 'Event Start Time',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EndDate',
            'label' => 'Event End Date',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EndTime',
            'label' => 'Event End Time',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'URL',
            'label' => 'URL',
            'rules' => 'trim|valid_url'
        ),
        array(
            'field' => 'Venue',
            'label' => 'Venue',
            'rules' => 'trim|required'
        ),
        /*array(
            'field' => 'Location[]',
            'label' => 'Event Location',
            'rules' => 'required'
        ),*/
        array(
            'field' => 'CategoryID',
            'label' => 'Category',
            'rules' => 'required'
        )
    ),
    'api/event/details' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'Event GUID',
            'rules' => 'required|validate_guid[14]'
        )
    ),
    'api/events/event_owner_detail' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'Event GUID',
            'rules' => 'required|validate_guid[14]'
        )
    ),
    'api/events/event_user_detail' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'Event GUID',
            'rules' => 'required|validate_guid[14]'
        )
    ),
    'api/events/event_attende_list' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'Event GUID',
            'rules' => 'required|validate_guid[14]'
        )
    ),
    'api/events/get_recent_invites' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'Event GUID',
            'rules' => 'required|validate_guid[14]'
        )
    ),
    'api/events/get_similar_event' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'Event GUID',
            'rules' => 'required|validate_guid[14]'
        )
    ),
    'api/event/editEvent' => array(
        array(
            'field' => 'Location',
            'label' => 'Event Location',
            'rules' => 'required'
        )
    ),
    'api/event/invite_users' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'Event GUID',
            'rules' => 'required|validate_guid[14]'
        ),
        array(
            'field' => 'UsersGUID',
            'label' => 'UsersGUID',
            'rules' => 'required'
        )
    ),
    'api/event/invitemanageevent' => array(
        array(
            'field' => 'Users[]',
            'label' => 'Users',
            'rules' => 'required'
        ),
        array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        )
    ),
    'api/event/update_presence' => array(
        array(
            'field' => 'TargetPresence',
            'label' => 'TargetPresence',
            'rules' => 'required'
        ),
        array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        )
    ),
    'api/event/addedeleteventmedia' => array(
        array(
            'field' => 'TargetModule',
            'label' => 'TargetModule',
            'rules' => 'required'
        ),
        array(
            'field' => 'MediaGUID',
            'label' => 'MediaGUID',
            'rules' => 'required|validate_guid[21]'
        ),
        array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        )
    ),
    'api/event/toggle_user_role' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        ),
        array(
            'field' => 'RoleAction',
            'label' => 'RoleAction',
            'rules' => 'trim|required'
        )
    ),
    'api/event/can_post_on_wall' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        )
        ,
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
        ,
        array(
            'field' => 'CanPostOnWall',
            'label' => 'CanPostOnWall',
            'rules' => 'trim|required'
        )
    ),
    'api/event/leave' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'UserGUID',
            'label' => 'UserGUID',
            'rules' => 'trim|required'
        )
    ),
    'api/uploadimage/updateProfilePicture' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        )
        ,
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        ),
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'required'
        ),
        array(
            'field' => 'ImageName',
            'label' => 'Image Name',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ImageData',
            'label' => 'Image data',
            'rules' => 'trim|required'
        )
    ),
    'admin_api/uploadimage/updateProfilePicture' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'required'
        ),
        array(
            'field' => 'ImageName',
            'label' => 'Image Name',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ImageData',
            'label' => 'Image data',
            'rules' => 'trim|required'
        )
    ),
    'api/uploadimage/removeProfileCover' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        )
        ,
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
    ),
    'api/page/create' => array(
        array(
            'field' => 'Title',
            'label' => 'Page Title',
            'rules' => 'trim|required|min_length[2]|max_length[200]'
        ),
        array(
            'field' => 'Description',
            'label' => 'Page Description',
            'rules' => 'trim|required|min_length[2]|max_length[1000]'
        ),
        array(
            'field' => 'PageType',
            'label' => 'Page Type',
            'rules' => 'required|numeric|callback_check_page_type_valid'
        ),
        array(
            'field' => 'PageURL',
            'label' => 'Page URL',
            'rules' => 'trim|required|min_length[2]|max_length[200]'
        ),
        array(
            'field' => 'StatusID',
            'label' => 'Status ID',
            'rules' => 'numeric|callback_check_status_id'
        ),
        array(
            'field' => 'WebsiteURL',
            'label' => 'Website URL',
            'rules' => 'callback_check_website_url'
        ),
        array(
            'field' => 'CategoryIds[]',
            'label' => 'Category Ids',
            'rules' => 'required'
        ),
        array(
            'field' => 'Phone',
            'label' => 'Phone',
            'rules' => 'trim|numeric|callback_valid_phone_number'
        )
    ),
    'api/page/update' => array(
        array(
            'field' => 'PageGUID',
            'label' => 'Page GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Title',
            'label' => 'Page Title',
            'rules' => 'trim|required|min_length[2]|max_length[200]'
        ),
        array(
            'field' => 'Description',
            'label' => 'Page Description',
            'rules' => 'trim|required|min_length[2]|max_length[1000]'
        ),
        array(
            'field' => 'PageType',
            'label' => 'Page Type',
            'rules' => 'required|numeric|callback_check_page_type_valid'
        ),
        array(
            'field' => 'PageURL',
            'label' => 'Page URL',
            'rules' => 'trim|required|min_length[2]|max_length[200]'
        ),
        array(
            'field' => 'StatusID',
            'label' => 'Status ID',
            'rules' => 'numeric|callback_check_status_id'
        ),
        array(
            'field' => 'WebsiteURL',
            'label' => 'Website URL',
            'rules' => 'callback_check_website_url'
        ),
        array(
            'field' => 'CategoryIds[]',
            'label' => 'Category Ids',
            'rules' => 'required'
        ),
        array(
            'field' => 'Phone',
            'label' => 'Phone',
            'rules' => 'trim|numeric|callback_valid_phone_number'
        )
    ),
    'api/page/listing' => array(
        array(
            'field' => 'Offset',
            'label' => 'Offset',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'Limit',
            'label' => 'Limit',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ListingType',
            'label' => 'ListingType',
            'rules' => 'trim|required|alpha'
        )
    ),
    'api/page/details' => array(
        array(
            'field' => 'PageGUID',
            'label' => 'PageGUID',
            'rules' => 'trim|required'
        )
    ),
    'api/page/followers' => array(
        array(
            'field' => 'PageGUID',
            'label' => 'PageGUID',
            'rules' => 'required'
        )
    ),
    'api/page/suggestions' => array(
        array(
            'field' => 'Offset',
            'label' => 'Offset',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'Limit',
            'label' => 'Limit',
            'rules' => 'trim|required|numeric'
        )
    ),
    'api/page/delete' => array(
        array(
            'field' => 'PageGUID',
            'label' => 'PageGUID',
            'rules' => 'trim|required|callback_check_page_owner'
        )
    ),
    'api/page/can_post_on_wall' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        ),
        array(
            'field' => 'CanPostOnWall',
            'label' => 'CanPostOnWall',
            'rules' => 'trim|required'
        )
    ),
    'api/page/toggle_user_role' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        )
        ,
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        ),
        array(
            'field' => 'RoleAction',
            'label' => 'RoleAction',
            'rules' => 'trim|required'
        )
    ),
    'api/page/remove_users' => array(
        array(
            'field' => 'UserID',
            'label' => 'UserID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required'
        )
    ),
    'api/log' => array(
        array(
            'field' => 'EntityType',
            'label' => 'Entity Type',
            'rules' => 'required'
        ),
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'required'
        )
    ),
    'api/log/user_list' => array(
        array(
            'field' => 'EntityType',
            'label' => 'Entity Type',
            'rules' => 'required'
        ),
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'required'
        )
    ),
    'api/ignore' => array(
        array(
            'field' => 'EntityType',
            'label' => 'Entity ID',
            'rules' => 'required'
        ),
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'required'
        )
    ),
    'api/smtp_setting' => array(
        array(
            'field' => 'Name',
            'label' => 'name',
            'rules' => 'trim|required|max_length[100]'
        ),
        array(
            'field' => 'FromName',
            'label' => 'fromname',
            'rules' => 'trim|required|max_length[100]'
        ),
        array(
            'field' => 'ServerName',
            'label' => 'serverName',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'UserName',
            'label' => 'username',
            'rules' => 'trim|required|max_length[100]'
        ),
        array(
            'field' => 'SPortNo',
            'label' => 'portno',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'FromEmail',
            'label' => 'email',
            'rules' => 'trim|required|valid_email'
        ),
        array(
            'field' => 'Password',
            'label' => 'password',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ReplyTo',
            'label' => 'replyemail',
            'rules' => 'trim|required'
        )
    ),
    'api/ip_setting' => array(
        array(
            'field' => 'IpAddress',
            'label' => 'ip address',
            'rules' => 'trim|required'
        )
    ),
    'api/configuration' => array(
        array(
            'field' => 'ConfigValue',
            'label' => 'current value',
            'rules' => 'trim|required'
        )
    ),
    'api/cretae_roles' => array(
        array(
            'field' => 'RoleName',
            'label' => 'rolename',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'RoleStatus',
            'label' => 'role status',
            'rules' => 'trim|required'
        )
    ),
    'api/validate_role_user' => array(
        array(
            'field' => 'FirstName',
            'label' => 'firstname',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'LastName',
            'label' => 'lastname',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Username',
            'label' => 'username',
            'rules' => 'trim|required|alpha_numeric|max_length[50]'
        ),
        array(
            'field' => 'Email',
            'label' => 'email',
            'rules' => 'trim|required|valid_email'
        )
    ),
    'api/validate_analytic_tools_data' => array(
        array(
            'field' => 'AnalyticProviderID',
            'label' => 'analytic provider',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'AnalyticsCode',
            'label' => 'analytics code',
            'rules' => 'trim|required'
        )
    ),
    'api/validate_communication' => array(
        array(
            'field' => 'subject',
            'label' => 'subject',
            'rules' => 'trim|required|max_length[100]'
        ),
        array(
            'field' => 'message',
            'label' => 'message',
            'rules' => 'trim|required'
        )
    ),
    'api/validate_support_request' => array(
        array(
            'field' => 'Title',
            'label' => 'title',
            'rules' => 'trim|required|max_length[100]'
        ),
        array(
            'field' => 'Description',
            'label' => 'description',
            'rules' => 'trim|required'
        )
    ),
    'api/validate_email_update' => array(
        array(
            'field' => 'Email',
            'label' => 'email',
            'rules' => 'trim|required|valid_email|callback_is_unique_value[' . USERS . '.Email.StatusID.Email]'
        )
    ),
    'api/search/group' => array(
        array(
            'field' => 'ListingType',
            'label' => 'listing type',
            'rules' => 'trim|required'
        )
    ),
    'api/search/photo' => array(
        array(
            'field' => 'SearchKeyword',
            'label' => 'keyword',
            'rules' => 'trim|required'
        )
    ),
    'api/search/video' => array(
        array(
            'field' => 'SearchKeyword',
            'label' => 'keyword',
            'rules' => 'trim|required'
        )
    ),
    'api/search/event' => array(
        array(
            'field' => 'SearchKeyword',
            'label' => 'search keyword',
            'rules' => 'trim|required'
        )
    ),
    //Album related rules block start
    'api/album/set_privacy' => array(
        array(
            'field' => 'AlbumGUID',
            'label' => 'lang:album_guid',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Privacy',
            'label' => 'privacy',
            'rules' => 'trim|required'
        )
    ),
    'api/album/add' => array(
        array(
            'field' => 'AlbumName',
            'label' => 'lang:album',
            //'rules' => 'trim|required|callback_is_unique_album_name',
            'rules' => 'trim|required|callback_is_default_album_name',
        ),
        array(
            'field' => 'Description',
            'label' => 'lang:description',
            'rules' => 'trim|required'
        ),
        /* array(
          'field' => 'Description',
          'label' => 'lang:description',
          'rules' => 'trim|required'
          ), */
        array(
            'field' => 'Visibility',
            'label' => 'lang:visibility',
            'rules' => 'trim|required|integer'
        ),
        array(
            'field' => 'AlbumType',
            'label' => 'lang:album_type',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'lang:ModuleEntityGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'lang:ModuleID',
            'rules' => 'trim|required'
        ),
    ),
    'api/album/edit' => array(
        array(
            'field' => 'AlbumGUID',
            'label' => 'lang:album_guid',
            'rules' => 'trim|required|callback_is_exist_album_guid'
        ),
        array(
            'field' => 'AlbumName',
            'label' => 'lang:album',
            'rules' => 'trim|required|callback_is_unique_album_name'
        ),
        array(
            'field' => 'Description',
            'label' => 'lang:description',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Description',
            'label' => 'lang:description',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Visibility',
            'label' => 'lang:visibility',
            'rules' => 'trim|required|integer'
        ),
        array(
            'field' => 'AlbumType',
            'label' => 'lang:album_type',
            'rules' => 'trim|required'
        ),
    ),
    'api/album/add_media' => array(
        array(
            'field' => 'AlbumGUID',
            'label' => 'lang:album_guid',
            'rules' => 'trim|required|callback_is_exist_album_guid'
        ),
    ),
    'api/album/delete' => array(
        array(
            'field' => 'AlbumGUID',
            'label' => 'lang:album_guid',
            'rules' => 'trim|required|callback_is_exist_album_guid'
        ),
    ),
    'api/album/update_media_caption' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'lang:media_guid',
            'rules' => 'trim|required|callback_is_exist_media_guid'
        ),
        array(
            'field' => 'Caption',
            'label' => 'lang:caption',
            'rules' => 'trim|required'
        ),
    ),
    'api/album/delete_media' => array(
        array(
            'field' => 'AlbumGUID',
            'label' => 'lang:album_guid',
            'rules' => 'trim|required|callback_is_exist_album_guid'
        ),
        array(
            'field' => 'Media',
            'label' => 'lang:media',
            'rules' => 'required'
        ),
    ),
    'api/album/media_details' => array(
        array(
            'field' => 'AlbumGUID',
            'label' => 'lang:album_guid',
            'rules' => 'trim|required|callback_is_exist_album_guid'
        ),
        array(
            'field' => 'MediaGUID',
            'label' => 'lang:media_guid',
            'rules' => 'trim|required|callback_is_exist_media_guid'
        ),
    ),
    'api/album/set_cover_media' => array(
        array(
            'field' => 'AlbumGUID',
            'label' => 'lang:album_guid',
            'rules' => 'trim|required|callback_is_exist_album_guid'
        ),
        array(
            'field' => 'MediaGUID',
            'label' => 'lang:media_guid',
            'rules' => 'trim|required|callback_is_exist_media_guid'
        ),
    ),
    'api/album/details' => array(
        array(
            'field' => 'AlbumGUID',
            'label' => 'lang:album_guid',
            'rules' => 'trim|required|callback_is_exist_album_guid'
        ),
    ),
    'api/album/list' => array(
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'lang:module_guid',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'lang:module_id',
            'rules' => 'trim|required'
        )
    ),
    'api/album/check_name' => array(
        array(
            'field' => 'AlbumName',
            'label' => 'lang:album',
            'rules' => 'trim|required|callback_is_unique_album_name',
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'lang:module_entity_guid',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'lang:module_id',
            'rules' => 'trim|required',
        ),
    ),
    //Album related rules block ends
    'api/friends/addFriend' => array(
        array(
            'field' => 'FriendGUID',
            'label' => 'friend guid',
            'rules' => 'trim|required'
        )
    ),
    'api/friends/deleteFriend' => array(
        array(
            'field' => 'FriendGUID',
            'label' => 'friend guid',
            'rules' => 'trim|required'
        )
    )
    ,
    'api/friends/rejectFriend' => array(
        array(
            'field' => 'FriendGUID',
            'label' => 'friend guid',
            'rules' => 'trim|required'
        )
    )
    ,
    'api/friends/denyFriend' => array(
        array(
            'field' => 'FriendGUID',
            'label' => 'friend guid',
            'rules' => 'trim|required'
        )
    )
    ,
    'api/friends/acceptFriend' => array(
        array(
            'field' => 'FriendGUID',
            'label' => 'friend guid',
            'rules' => 'trim|required'
        )
    ),
    'api/signup/email_activation' => array(
        array(
            'field' => 'ActivationCode',
            'label' => 'Activation Code',
            'rules' => 'trim|required'
        )
    ),
    'api/signup/ResendActivationLink' => array(
        array(
            'field' => 'UserGUID',
            'label' => 'user guid',
            'rules' => 'trim|required|validate_guid[3]'
        )
    ),
    'api/rating/add' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        )
        ,
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        ),
        array(
            'field' => 'RateValue',
            'label' => 'rate value',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Title',
            'label' => 'title',
            'rules' => 'trim|required|max_length[60]'
        ),
        array(
            'field' => 'Description',
            'label' => 'description',
            'rules' => 'trim|required|max_length[500]'
        )
    ),
    'api/rating/edit' => array(
        array(
            'field' => 'RatingGUID',
            'label' => 'rating guid',
            'rules' => 'trim|required|validate_guid[23]'
        ),
        array(
            'field' => 'RateValue',
            'label' => 'rate value',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Title',
            'label' => 'title',
            'rules' => 'trim|required|max_length[60]'
        ),
        array(
            'field' => 'Description',
            'label' => 'description',
            'rules' => 'trim|required|max_length[500]'
        )
    ),
    'api/rating/parameter' => array(
        array(
            'field' => 'CategoryID',
            'label' => 'category id',
            'rules' => 'trim|required'
        )
    ),
    'api/rating/list' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'module id',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'module entity guid',
            'rules' => 'trim|required'
        )
    ),
    'api/rating/vote' => array(
        array(
            'field' => 'Vote',
            'label' => 'vote',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityGUID',
            'label' => 'entity guid',
            'rules' => 'trim|required|validate_guid[23]'
        ),
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        )
    ),
    'api/rating/details' => array(
        array(
            'field' => 'RatingGUID',
            'label' => 'rating guid',
            'rules' => 'trim|required|validate_guid[23]'
        )
    ),
    'api/rating/overall' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'module id',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'module entity guid',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
    ),
    'api/rating/star_count' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'module id',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'module entity guid',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
    ),
    'api/rating/parameter_summary' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'module id',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'module entity guid',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
    ),
    'api/rating/entitylist' => array(
        array(
            'field' => 'ModuleID',
            'label' => 'module id',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'module entity guid',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
    ),
    'api/rating/delete' => array(
        array(
            'field' => 'RatingGUID',
            'label' => 'rating guid',
            'rules' => 'trim|required'
        )
    ),
    'api/media/add_comment' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'trim|required'
        )
    ),
    'api/media/toggle_like' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'trim|required'
        )
    ),
    'api/media/flag' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'FlagReason',
            'label' => 'flag reason',
            'rules' => 'trim|required'
        )
    ),
    'api/media/comments' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'trim|required'
        )
    ),
    'api/media/like_details' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'trim|required'
        )
    ),
    'api/media/toggle_subscribe' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'trim|required'
        )
    ),
    'api/media/delete' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'trim|required'
        )
    ),
    'api/media/privacy' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Visibility',
            'label' => 'visibility',
            'rules' => 'trim|required'
        )
    ),
    'api/media/share_details' => array(
        array(
            'field' => 'MediaGUID',
            'label' => 'media guid',
            'rules' => 'trim|required'
        )
    ),
    'api/share_post_by_email' => array(
        array(
            'field' => 'emails[]',
            'label' => 'email',
            'rules' => 'required'
        ),
        array(
            'field' => 'link',
            'label' => 'link',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'message',
            'label' => 'message',
            'rules' => 'trim|required'
        )
    ),
    'api/team/delete_pages' => array(
        array(
            'field' => 'GroupGUIDS',
            'label' => 'GroupGUIDS',
            'rules' => 'required'
        ),
        array(
            'field' => 'ActionType',
            'label' => 'ActionType',
            'rules' => 'trim|required'
        )
    ),
    'api/team/delete_page' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'required|callback_validate_groupguid'
        ),
        array(
            'field' => 'ActionType',
            'label' => 'ActionType',
            'rules' => 'trim|required'
        )
    ),
    'api/team/search_group_user' => array(
        array(
            'field' => 'GroupOwnerGUID',
            'label' => 'GroupOwnerGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'SearchKeyword',
            'label' => 'SearchKeyword',
            'rules' => 'trim|required'
        )
    ),
    'api/blog/create' => array(
        array(
            'field' => 'Title',
            'label' => 'Title',
            'rules' => 'required'
        ),
        array(
            'field' => 'Description',
            'label' => 'Description',
            'rules' => 'required'
        )
    ),
     'api/announcement/add' => array(
        array(
            'field' => 'Description',
            'label' => 'Description',
            'rules' => 'required'
        )
    ),
    'api/blog/edit' => array(
        array(
            'field' => 'Title',
            'label' => 'Title',
            'rules' => 'required'
        ),
        array(
            'field' => 'Description',
            'label' => 'Description',
            'rules' => 'required'
        ),
        array(
            'field' => 'BlogGUID',
            'label' => 'BlogGUID',
            'rules' => 'required'
        )
    ),
    'api/blog/detail' => array(
        array(
            'field' => 'BlogGUID',
            'label' => 'BlogGUID',
            'rules' => 'required'
        )
    ),
    'api/blog/delete' => array(
        array(
            'field' => 'BlogGUID',
            'label' => 'BlogGUID',
            'rules' => 'required'
        )
    ),
    'api/add_category' => array(
        array(
            'field' => 'Name',
            'label' => 'Category Name',
            'rules' => 'trim|required|max_length[200]'
        ),
        array(
            'field' => 'PhoneNumber',
            'label' => 'Phone Number',
            'rules' => 'trim|numeric'
        )
    ),
    'api/edit_category' => array(
        array(
            'field' => 'Name',
            'label' => 'Category Name',
            'rules' => 'trim|required|max_length[200]'
        ),
        array(
            'field' => 'CategoryID',
            'label' => 'Category ID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Description',
            'label' => 'Description',
            'rules' => 'trim|max_length[500]'
        )
    ),
    'api/privacy/save' => array(
        array(
            'field' => 'Privacy',
            'label' => 'Privacy',
            'rules' => 'trim|required'
        )
    ),
    'api/users/save_interest' => array(
        array(
            'field' => 'CategoryIDs[]',
            'label' => 'CategoryIDs',
            'rules' => 'required'
        )
    ),
    'api/reminder/add' => array(
        array(
            'field' => 'ActivityGUID',
            'label' => 'activity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ReminderDateTime',
            'label' => 'reminder date time',
            //'rules' => 'trim|required|validate_date[Y-m-d H:i:s]'
            'rules' => 'trim|required|validate_date[Y-m-d g:i:s A]'
        )
    ),
    'api/reminder/edit' => array(
        array(
            'field' => 'ReminderGUID',
            'label' => 'reminder GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ReminderDateTime',
            'label' => 'reminder date time',
            'rules' => 'trim|required|validate_date[Y-m-d g:i:s A]'
        )
    ),
    'api/reminder/details' => array(
        array(
            'field' => 'ReminderGUID',
            'label' => 'reminder GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/reminder/delete' => array(
        array(
            'field' => 'ReminderGUID',
            'label' => 'reminder GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/reminder/change_status' => array(
        array(
            'field' => 'ReminderGUID',
            'label' => 'reminder GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Status',
            'label' => 'reminder status',
            'rules' => 'trim|required'
        )
    ),
       'api/users/add_introduction' => array(
        array(
            'field' => 'Introduction',
            'label' => 'Introduction',
            'rules' => 'trim|max_length[200]'
        ) 
    ),
    'api/activity/toggle_archive' => array(
        array(
            'field' => 'ActivityGUID',
            'label' => 'activity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/privacy/save_news_feed_setting' => array(
        array(
            'field' => 'news_feed_setting[]',
            'label' => 'news feed setting',
            'rules' => 'required'
        )
    ),
    'api/group/add_member_forcefully' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'Group GUID',
            'rules' => 'required'
        ),
        array(
            'field' => 'UsersGUID[]',
            'label' => 'UsersGUID',
            'rules' => 'required'
        )
    ),
    'admin_api/skills/remove_category' => array(
        array(
            'field' => 'CategoryIDs[]',
            'label' => 'Category ID',
            'rules' => 'required'
        ),
    ),
    'admin_api/skills/merge_skills' => array(
        array(
            'field' => 'Name',
            'label' => 'Skill Name ',
            'rules' => 'required'
        ),
        array(
            'field' => 'SkillIDs',
            'label' => 'Merge Skill ',
            'rules' => 'required'
        ),
    ),
    'admin_api/skills/save' => array(
        array(
            'field' => 'Name',
            'label' => 'Skill Name ',
            'rules' => 'required|min_length[1]|max_length[50]|callback_validate_SkillName'
        ),
    ),
    'admin_api/skills/remove' => array(
        array(
            'field' => 'SkillIDs[]',
            'label' => 'Skill ID ',
            'rules' => 'required'
        ),
    ),
    'admin_api/skills/skill_profile_count' => array(
        array(
            'field' => 'SkillID',
            'label' => 'Skill ID ',
            'rules' => 'required'
        ),
    ),
    'admin_api/skills/category_profile_count' => array(
        array(
            'field' => 'CategoryID',
            'label' => 'Category ID ',
            'rules' => 'required'
        ),
    ),
    'admin_api/skills/get_single_skill' => array(
        array(
            'field' => 'SkillID',
            'label' => 'Skill ID ',
            'rules' => 'required'
        ),
    ),
    'api/polls/add' => array(
        array(
            'field' => 'Description',
            'label' => 'Description',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Options[]',
            'label' => 'Options',
            'rules' => 'required|callback_validate_poll_options'
        ),
        array(
            'field' => 'Visibility',
            'label' => 'visibility',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Commentable',
            'label' => 'commentable',
            'rules' => 'trim|required'
        )
    ),
    'api/polls/add_vote' => array(
        array(
            'field' => 'OptionGUID',
            'label' => 'OptionGUID',
            'rules' => 'required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required'
        )
    ),
    'api/polls/get_entity_list' => array(
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required'
        )
    ),
    'api/polls/edit_vote' => array(
        array(
            'field' => 'PollGUID',
            'label' => 'PollGUID',
            'rules' => 'required|required'
        )
    ),
    'api/polls/about_to_close' => array(
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required'
        )
    ),
    'api/polls/invite_friends_and_groups' => array(
        array(
            'field' => 'InviteMembers',
            'label' => 'InviteMembers',
            'rules' => 'required'
        ),
        array(
            'field' => 'PollGUID',
            'label' => 'PollGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'InvitedByModuleEntityID',
            'label' => 'InvitedByModuleEntityID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'InvitedByModuleID',
            'label' => 'InvitedByModuleID',
            'rules' => 'trim|required'
        )
    ),
    
    'admin_api/flags/list' => array(
        array(
            'field' => 'EntityType',
            'label' => 'Entity Type',
            'rules' => 'trim|required|max_length[30]'
        ),
        array(
            'field' => 'SearchString',
            'label' => 'Search String',
            //'rules' => 'trim|required|max_length[30]'
            'rules' => 'trim|max_length[30]'
        ),
        array(
            'field' => 'PageNo',
            'label' => 'lang:page_no',
            'rules' => 'trim|integer'
        ),
        array(
            'field' => 'PageSize',
            'label' => 'lang:page_size',
            'rules' => 'trim|integer'
        ),
    ),
    'admin_api/flag/remove' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        ),
    ),
    'admin_api/flag/entityflags' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        ),
    ),
    'api/create_sticky' => array(        
        array(
            'field' => 'ActivityGUID',
            'label' => 'ActivityGUID',
            'rules' => 'trim|required|validate_guid[0]'
        ),
        array(
            'field' => 'StickyType',
            'label' => 'StickyType',
            'rules' => 'trim|required'
        ),
    ),    
    'api/remove_sticky' => array(        
        array(
            'field' => 'ActivityGUID',
            'label' => 'ActivityGUID',
            'rules' => 'trim|required|validate_guid[0]'
        ),
        array(
            'field' => 'StickyType',
            'label' => 'StickyType',
            'rules' => 'trim|required'
        ),
    ),
    'api/users/update_profile_new' => array(
        array(
            'field' => 'FirstName',
            'label' => 'First Name',
            'rules' => 'trim|required|max_length[50]'
        ),
        array(
            'field' => 'TimeZoneID',
            'label' => 'TimeZoneID',
            'rules' => 'numeric'
        ),
        array(
            'field' => 'Gender',
            'label' => 'Gender',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'OfficialEmail',
            'label' => 'Official Email',
            'rules' => 'trim|valid_email|max_length[50]'
        ),
        array(
            'field' => 'ExtensionNumber',
            'label' => 'Extension Number',
            'rules' => 'trim|numeric|max_length[5]'
        ),
        array(
            'field' => 'MobileNumber',
            'label' => 'Mobile Number',
            'rules' => 'trim|numeric|max_length[12]'
        ),
        array(
            'field' => 'EmergencyContactNumber',
            'label' => 'Emergency Contact Number',
            'rules' => 'trim|numeric|max_length[12]'
        ),
        array(
            'field' => 'PersonalEmail',
            'label' => 'Personal Email',
            'rules' => 'trim|valid_email'
        ),
        array(
            'field' => 'AboutMe',
            'label' => 'About Me',
            'rules' => 'trim|max_length[500]'
        ),
        array(
            'field' => 'PermanentAddress',
            'label' => 'Permanent Address',
            'rules' => 'trim|max_length[500]'
        ),
        array(
            'field' => 'PresentAddress',
            'label' => 'Present Address',
            'rules' => 'trim|max_length[500]'
        ),
        array(
            'field' => 'PAN',
            'label' => 'PAN',
            'rules' => 'trim|alpha_numeric|max_length[10]'
        ),
        array(
            'field' => 'LandlineOffice',
            'label' => 'Landline Office',
            'rules' => 'trim|max_length[15]'
        ),
        array(
            'field' => 'HomeLandline',
            'label' => 'Home Landline',
            'rules' => 'trim|max_length[15]'
        )
    ),
    'api/request_question_answer_for_activity' => array(        
        array(
            'field' => 'ActivityGUID',
            'label' => 'ActivityGUID',
            'rules' => 'trim|required'
        )/*,
        array(
            'field' => 'Note',
            'label' => 'Note',
            'rules' => 'trim|required|min_length[2]|max_length[140]'
        )*/
    ),'api/mark_best_answer' => array(
        array(
            'field' => 'CommentGUID',
            'label' => 'comment GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ActivityGUID',
            'label' => 'activity GUID',
            'rules' => 'trim|required'
        )
    ),'api/get_requested_answer_users' => array(
        array(
            'field' => 'ActivityGUID',
            'label' => 'activity GUID',
            'rules' => 'trim|required'
        )
    ),'api/get_activity_friend_list' => array(
        array(
            'field' => 'ActivityGUID',
            'label' => 'activity GUID',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/newGroupListing' => array(
        array(
            'field' => 'GroupType',
            'label' => 'group type',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/create' => array(
        array(
            'field' => 'GroupName',
            'label' => 'Group Name',
            'rules' => 'trim|required|min_length[2]|max_length[200]|callback_check_all_num'
        ),
        array(
            'field' => 'CategoryIds[]',
            'label' => 'Category',
            'rules' => 'required'
        ),
        array(
            'field' => 'GroupDescription',
            'label' => 'Group Description',
            'rules' => 'trim|required|min_length[2]|max_length[140]'
        )
        ,
        array(
            'field' => 'IsPublic',
            'label' => 'Group type',
            'rules' => 'required'
        )
    ),
    'api/discussion/update' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'Group GUID',
            'rules' => 'trim|required|validate_guid[1]'
        ),
        array(
            'field' => 'GroupName',
            'label' => 'Group Name',
            'rules' => 'trim|required|min_length[2]|max_length[200]|callback_check_all_num'
        ),
        array(
            'field' => 'CategoryIds[]',
            'label' => 'Category',
            'rules' => 'required'
        ),
        array(
            'field' => 'GroupDescription',
            'label' => 'Group Description',
            'rules' => 'trim|required|min_length[2]|max_length[140]'
        )
        ,
        array(
            'field' => 'IsPublic',
            'label' => 'Group type',
            'rules' => 'required'
        )
    ),
    'api/discussion/update_informal' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'Group GUID',
            'rules' => 'trim|required|validate_guid[1]'
        ),
    ),
    'api/discussion/groupidrequired' => array(
        array(
            'field' => 'GroupID',
            'label' => 'group ID',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/removeMembersGroup' => array(
        array(
            'field' => 'DeleteMemberID',
            'label' => 'member ID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'GroupID',
            'label' => 'group ID',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/addMemberToGroup' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'UsersGUID[]',
            'label' => 'UsersGUID',
            'rules' => 'required'
        ),
        array(
            'field' => 'AddForceFully',
            'label' => 'Add ForceFully',
            'rules' => 'required'
        )
    ),
    'api/discussion/groupAcceptDenyRequest' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'UserGUID',
            'label' => 'UserGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'StatusID',
            'label' => 'StatusID',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/groupDropOutAction' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
    ),
    'api/discussion/groupDelete' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ActionType',
            'label' => 'ActionType',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/toggle_user_role' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        ),
        array(
            'field' => 'RoleAction',
            'label' => 'RoleAction',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/groupMembers' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'GroupGUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Type',
            'label' => 'Type',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'Offset',
            'label' => 'Offset',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'Limit',
            'label' => 'Limit',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/groupListing' => array(
        array(
            'field' => 'ListingType',
            'label' => 'ListingType',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Offset',
            'label' => 'Offset',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'Limit',
            'label' => 'Limit',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/groupSuggestions' => array(
        array(
            'field' => 'Offset',
            'label' => 'Offset',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'Limit',
            'label' => 'Limit',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/can_post_on_wall' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'EntityGUID',
            'rules' => 'trim|required'
        )
        ,
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|numeric'
        )
        ,
        array(
            'field' => 'ModuleEntityGUID',
            'label' => 'ModuleEntityGUID',
            'rules' => 'trim|required|validate_guid[ModuleID]'
        )
        ,
        array(
            'field' => 'CanPostOnWall',
            'label' => 'CanPostOnWall',
            'rules' => 'trim|required'
        )
    ),
    'api/discussion/add_member_forcefully' => array(
        array(
            'field' => 'GroupGUID',
            'label' => 'Group GUID',
            'rules' => 'required'
        ),
        array(
            'field' => 'UsersGUID[]',
            'label' => 'UsersGUID',
            'rules' => 'required'
        )
    ),
    'api/activity/getRequestDetails' => array(
        array(
            'field' => 'EntityGUID',
            'label' => 'entity GUID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EntityType',
            'label' => 'entity type',
            'rules' => 'trim|required'
        )
    ),
    'api/group/similar_discussion' => array(
        array(
            'field' => 'EntityID',
            'label' => 'EntityID',
            'rules' => 'required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'required'
        )
    ),
    'api/group/group_member_suggestion' => array(
        array(
            'field' => 'ModuleEntityID',
            'label' => 'ModuleEntityID',
            'rules' => 'required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'required'
        )
    ),
    'api/group/gruop_setting' => array(
        array(
            'field' => 'ModuleEntityID',
            'label' => 'ModuleEntityID',
            'rules' => 'required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'required'
        )
    ),
    'api/group/get_post_content_types' => array(
        array(
            'field' => 'ModuleEntityID',
            'label' => 'ModuleEntityID',
            'rules' => 'required'
        ),
        array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'required'
        )
    ),
    'api/group/save_default_permisson' => array(
        array(
            'field' => 'GroupID',
            'label' => 'GroupID',
            'rules' => 'required'
        )
    ),
    
    'api/admin/activity/get_activity_entities' => array(
        array(
            'field' => 'UserID',
            'label' => 'UserID',
            'rules' => 'required'
        )
    ),
    
    'api/admin/rules/configuration' => array(
        array(
            'field' => 'NoOfFrndConfVal',
            'label' => 'NoOfFrndConfVal',
            'rules' => 'trim|required'
        ),
        
        array(
            'field' => 'NoOfPostConfVal',
            'label' => 'NoOfPostConfVal',
            'rules' => 'trim|required'
        )
    ),
    'admin_api/advertise/saveBanner' => array(
        array(
            'field' => 'BlogTitle',
            'label' => 'Title',
            'rules' => 'trim|required|max_length[50]'
        ),
        array(
            'field' => 'BlogUniqueID',
            'label' => 'Module name',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Advertiser',
            'label' => 'Advertiser',
            'rules' => 'trim|required|max_length[100]'
        ),
        /* array(
          'field' => 'URL',
          'label' => 'URL',
          'rules' => 'trim|required'
          ),
          array(
          'field' => 'Duration',
          'label' => 'Duration',
          'rules' => 'trim|required'
          ), */
        array(
            'field' => 'StartDate',
            'label' => 'Start date',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'EndDate',
            'label' => 'End date',
            'rules' => 'trim|required'
        ),
    ),
    'admin_api/users/suspend' => array(
        array(
            'field' => 'UserID',
            'label' => 'user id',
            'rules' => 'trim|required|numeric'
        ),
        array(
            'field' => 'AccountSuspendTill',
            'label' => 'Account suspend till',
            'rules' => 'trim'
        )
    ),
    'admin_api/users/update_network_details' => array(
        array(
            'field' => 'Admin_Facebook_profile_URL',
            'label' => 'Facebook Profile URL',
            'rules' => 'trim|callback_validate_url'
        ),
        array(
            'field' => 'NoOfFriendsFB',
            'label' => 'No Of Facebook Friends',
            'rules' => 'trim|numeric'
        ),
        array(
            'field' => 'NoOfFollowersFB',
            'label' => 'No Of Facebook Followers',
            'rules' => 'trim|numeric'
        ),
        array(
            'field' => 'Admin_Linkedin_profile_URL',
            'label' => 'LinkedIn Profile URL',
            'rules' => 'trim|callback_validate_url'
        ),
        array(
            'field' => 'NoOfConnectionsIn',
            'label' => 'No Of LinkedIn Connections',
            'rules' => 'trim|numeric'
        ),
        array(
            'field' => 'Admin_Twitter_profile_URL',
            'label' => 'Twitter Profile URL',
            'rules' => 'trim|callback_validate_url'
        ),
        array(
            'field' => 'NoOfFollowersTw',
            'label' => 'No Of twitter followers',
            'rules' => 'trim|numeric'
        )
    ),
    'api/watchlist/toggle_watchlist' => array(
        array(
            'field' => 'ActivityGUID',
            'label' => 'Activity GUID',
            'rules' => 'trim|required|validate_guid[0]'
        )
    ),
    'api/toggle_mydesk_task' => array(
        array(
            'field' => 'ActivityGUID',
            'label' => 'Activity GUID',
            'rules' => 'trim|required|validate_guid[0]'
        ),
        array(
            'field' => 'TaskStatus',
            'label' => 'Task Status',
            'rules' => 'trim|required'

        ),
    ),
    'api/share_post_by_email' => array(
        array(
            'field' => 'link',
            'label' => 'Link',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'message',
            'label' => 'Message',
            'rules' => 'trim|required'

        ),
    ),
    'api/share_event_by_email' => array(
        array(
            'field' => 'link',
            'label' => 'Link',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'message',
            'label' => 'Message',
            'rules' => 'trim|required'

        ),
    ),
    'admin_api/announcementpopup/save' => array(
        array(
            'field' => 'PopupTitle',
            'label' => 'Popup Title',
            'rules' => 'trim|required|max_length[200]'
        ),        
        array(
            'field' => 'PopupContent',
            'label' => 'Popup Content',
            'rules' => 'trim|required'
        )
    ), 
    'admin_api/announcementpopup/change_status' => array(
        array(
            'field' => 'AnnouncementPopupID',
            'label' => 'AnnouncementPopupID',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'Status',
            'label' => 'Status',
            'rules' => 'trim|required'
        )
    ),
    'api/event/delete_event' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required'
        ),
        array(
            'field' => 'ActionType',
            'label' => 'ActionType',
            'rules' => 'trim|required'
        )
    ),
    'api/event/feature_event' => array(
        array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required'
        ),
        array(
            'field' => 'IsFeatured',
            'label' => 'IsFeatured',
            'rules' => 'required|integer'
        ),
        array(
            'field' => 'ActionType',
            'label' => 'ActionType',
            'rules' => 'trim|required'
        )
    ),
);
