const { REACT_APP_BASE_URL, REACT_APP_S3_URL } = process.env
//Datepicker
export const DATE_FORMAT = 'dd/MM/yyyy';

export const ADMIN_FOLDER_NAME='adminapi/';
export const ADMIN_AUTH_KEY='admin_id_token';

export const baseURL = REACT_APP_BASE_URL;
export const ALLOW_COMMUNICATION_DASHBOARD = 1;
export const ALLOW_DEAL = 1;

export const deviceType = "3";
export const deviceID = "";
export const leagueId = "";
export const sportsId = "7";
export const CURRENCY = "₹";
export const EXPORT_REPORT_LIMIT = 10000;
export const successCode = 200;
export const INTERNAL_SERVER = 500;
export const AUTHENTICATE_REQUIRE_CODE = 401;
export const sessionExpireCode = 401;
export const ITEMS_PERPAGE = 50;
export const ITEMS_PERPAGE_LG = 100;
export const CURRENT_PAGE = 1;
export const sessionKey = "";
export const SYSTEM_ERROR = "System generated an error please try again later.";


export var Locale = "";
export class Language {
    static update(data) {
        Locale = data;
    }
}

//Imnages folder dir path
export const  S3 = REACT_APP_S3_URL;
export const  UPLOAD = 'upload/';
export const  BANNER = 'upload/banner/';
export const  APPBANNER = 'upload/app_banner/';
export const  FLAG = 'upload/flag/';
export const  JERSY = 'upload/jersey/';

export const  PAN = 'upload/pan/';
export const  THUMB = 'upload/profile/thumb/';
export const  BANK = 'upload/bank_document/';
export const  NOIMAGE = 'assets/img/no_image.png';

//Help text here 
export const PROMO_CODE_HELP = "Please enter min 3 and max 100 alphanumeric character for promocode";
export const DISCOUNT_HELP = "Discount to be given to user on entry fee";
export const BENEFIT_CAP_HELP = "Maximum amount value of the discount";
export const PER_USER_ALLOWED_HELP = "This will define that how many times single user can use the same promo code";


export const GET_ALL_SPORTS = "adminapi/common/get_all_sport";
export const GET_SPORT_LEAGUES = "adminapi/common/get_sport_leagues";

//login 
export const DO_LOGIN = "affiliate/auth/dologin";


/***
 * AFFILIATE API 
 */
 export const GET_AFFILIATE = "affiliate/admin/affiliate/get_affiliates";
 export const CREATE_AFFILIATE = "affiliate/admin/affiliate/add_affiliate";
 export const GET_CAMPAIGN_DETAIL = "affiliate/admin/affiliate/get_campaign_details";
 export const UPDATE_CAMPAIGN = "affiliate/admin/affiliate/update_campaign";
 export const TRACK_URL = "affiliate/admin/affiliate/track_single_url";
 export const TRACK_URL_DETAIL = "affiliate/admin/affiliate/get_single_user_details";
 export const EXPOERT_PDF = "affiliate/admin/affiliate/export_affiliates";
 export const CREATE_CAMPAIGN = "affiliate/admin/affiliate/create_campaign";
 export const UPDATE_AFFILIATE = "affiliate/admin/affiliate/update_affiliate";



 /****
  * USER LOGIN API
  */
  export const USER_AFFLILLIATE_PROFILE = "affiliate/profile/get_profile";
  export const USER_CAMPAIGN_LIST = "affiliate/profile/get_aff_campaign_detail";
  export const TRACK_SINGLE_URL = "affiliate/profile/track_single_url";
  export const TRACK_SINGLE_URL_DETAIL = "affiliate/profile/get_single_user_details";
