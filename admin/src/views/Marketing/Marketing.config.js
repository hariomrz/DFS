// export const GET_FILTER_RESULT_TEST = "adminapi/communication_dashboard/user_segmentation/get_filter_result_test/false";
export const GET_FILTER_RESULT_TEST = "adminapi/communication_dashboard/user_segmentation/get_filter_result_test";
export const GET_DEPOSIT_PROMOCODES = "adminapi/communication_dashboard/user_segmentation/get_deposit_promocodes";
export const GET_SEGEMENTATION_TEMPLATE_LIST = "adminapi/communication_dashboard/user_segmentation/get_segementation_template_list";
export const GET_RECENT_COMMUNICATION_LIST = "adminapi/communication_dashboard/user_segmentation/get_recent_communication_list";

export const NOTIFY_BY_SELECTION = "adminapi/communication_dashboard/user_segmentation/notify_by_selection";
export const NOTIFY_BY_SELECTION_COUNT = "adminapi/communication_dashboard/user_segmentation/notify_by_selection_counts";
export const RECENT_COMMUNICATION = "adminapi/communication_dashboard/user_segmentation/resent_communication";
export const GET_RECENT_COMMUNICATION_DETAIL = "adminapi/communication_dashboard/user_segmentation/get_recent_communication_detail";
export const GET_CD_BALANCE = "adminapi/communication_dashboard/user_segmentation/get_cd_balance";
export const ADD_NOTIFICATION_ENTITY = "adminapi/communication_dashboard/user_segmentation/add_notify_entity";
export const EXPORT_FILTER_DATA = "adminapi/communication_dashboard/user_segmentation/export_filter_data";
export const GET_LIVE_UPCOMING_MATCHS = "adminapi/communication_dashboard/new_campaign/get_live_upcoming_fixtures";
export const RENDER_EMAIL_BODY = "adminapi/communication_dashboard/new_campaign/render_cd_body";

export const GET_CITY_NAMES = "adminapi/communication_dashboard/user_segmentation/get_city_names";
export const GET_PREFERENCE_LIST = "adminapi/communication_dashboard/user_segmentation/get_preference_list";
export const UPDATE_PREFERENCE_LIST = "adminapi/communication_dashboard/user_segmentation/update_preference_list";
export const CREATE_USER_BASE_LIST = "adminapi/communication_dashboard/user_segmentation/create_user_base_list";
export const DELETE_USER_BASE_LIST = "adminapi/communication_dashboard/user_segmentation/delete_user_base_list";
export const GET_SINGLE_USER_BASE_LIST = "adminapi/communication_dashboard/user_segmentation/get_single_user_base_list";
export const CREATE_NEW_CATEGORY = "adminapi/communication_dashboard/user_segmentation/create_new_category";
export const GET_USER_COUNT = "adminapi/communication_dashboard/user_segmentation/get_user_count";
export const GET_USER_BASE_LIST = "adminapi/communication_dashboard/user_segmentation/get_user_base_list";
export const GET_CUSTOME_TEMPLATE = "adminapi/communication_dashboard/user_segmentation/get_custome_template";
export const GET_TEMPLATE_CATEGORY = "adminapi/communication_dashboard/user_segmentation/get_template_category";
export const CREATE_NEW_TEMPLATE = "adminapi/communication_dashboard/user_segmentation/create_new_template";
export const UPDATE_USER_BASE_LIST = "adminapi/communication_dashboard/user_segmentation/update_user_base_list";
export const GET_DELAYED_FIXTURES = "adminapi/communication_dashboard/user_segmentation/get_delayed_fixtures";
export const CD_GET_DEALS_LIST = "adminapi/communication_dashboard/new_campaign/get_deals_list";
export const GET_SCHEDULED_DATA = "adminapi/communication_dashboard/user_segmentation/get_scheduled_data";

export const UTM_SOURCE_SMS = 'SMS';
export const UTM_SOURCE_NOTIFICATION = 'noti';
export const UTM_MEDIUM = 'VtechAdmin';
export const userBaseType = {
  1: {
    all_user: 1,
    text: 'All User'
  },
  2: {
    login: 1,
    text: 'Login'
  },
  3: {
    signup: 1,
    text: 'Signup'
  },
  4: {
    fixture_participation: 1,
    text: 'By Fixture Participation'
  }
}

export const userBases = {
  1: {
    all_user: 1
  },
  2: {
    login: 1
  },
  3: {
    signup: 1
  },
  4: {
    fixture_participation: 1
  }
}

export const sourceByTemplate = {
  121: 'contest_id',
  120: 'promo_code_id',
  124: 'season_game_uid',
  131: 'season_game_uid',
  132: 'season_game_uid'
}

export const fixtureChannelMap = {
  email_fixture_model: 'email',
  message_fixture_model: 'message',
  notification_fixture_model: 'notification'
}

//Lobby, Wallet, My Profile, My contests, Refer a Friend.
export const notification_landing_pages = [{
    label: 'Lobby',
    value: 1
  },
  {
    label: 'Wallet',
    value: 2
  },
  {
    label: 'My Profile',
    value: 3
  },
  {
    label: 'My contests',
    value: 4
  },
  {
    label: 'Refer a Friend',
    value: 5
  },
  {
    label: 'Contest Listing',
    value: 6
  },
  {
    label: 'Add Fund',
    value: 7
  },
]

export const GenderOptions = [
  {id: '1',name: 'Male'},
  {id: '2',name: 'Female'},
  {id: '3',name: 'Other'},
];

export const extend = (obj, src) => {
  for (var key in src) {
    if (src.hasOwnProperty(key)) obj[key] = src[key];
  }
  return obj;
}
