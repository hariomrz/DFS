const { 
    REACT_APP_S3_URL,
    REACT_APP_S3_PREFIX,
    REACT_APP_BASE_URL,
    REACT_APP_USER_BASE_URL,
    REACT_APP_FANTASY_BASE_URL,
    REACT_APP_STOCK_BASE_URL,
    REACT_APP_NODE_URL,
    REACT_APP_PROPS_BASE_URL,
    REACT_APP_FB_APP_ID,
    REACT_APP_GOOGLE_CLIENT_ID,
    REACT_APP_OPINION_TRADE_BASE_URL,
    REACT_APP_BUCKET_STATIC_DATA_ALLOWED
} = process.env;

const getModuleUrl = (url) => {
    return url ? url + '/': REACT_APP_USER_BASE_URL + '/'
}

const prod = {
    s3: {
        BUCKET: REACT_APP_S3_URL,
        S3_FOLDER: {
            UPLOAD: 'upload/',
            SETTING: 'upload/setting/',
            BANNER: 'upload/banner/',
            APPBANNER: 'upload/app_banner/',
            FLAG: 'upload/flag/',
            JERSY: 'upload/jersey/',
            PAN: 'upload/pan/',
            THUMB: 'upload/profile/thumb/',
            BANK: 'upload/bank_document/',
            REWARDS: 'upload/rewards/',
            S3ASSETS: 'assets/img/',
            BADGES:'upload/badges/',
            MERCHANDISE:'upload/merchandise/',
            SPONSER:'upload/sponsor/',
            CATEGORY: 'upload/category/',
            OPENPRED: 'upload/open_predictor/',
            CMS: 'upload/cms_images/',
            OPENPREDFPP: 'upload/fixed_open_predictor/sponsor/',
            PICKEM_TOUR_LOGO: 'upload/pickem_tr_logo/',
            PICKEM_TOUR_SPONSOR: 'upload/pickem_tr_sponsor/',
            PICKEM_TEAM_FLAG: 'pickem/upload/pt_team_flag/',
            DFS_TOUR_LOGO: 'upload/dfs_tr_logo/',
            DFS_TOUR_SPONSOR: 'upload/dfs_tr_sponsor/',
            STOCK_LOGO: 'upload/stock/',
            BOOSTER_LOGO:'upload/booster/',
            H2H:'upload/h2h/',
            DFSTOUR:'upload/dfstournament/',
            PICKEMTOUR:'upload/pickem/',
            WHATSNEW :'upload/whatsnew/',
            PICKS :'upload/picks/',
            GETQUIZIMG :'upload/quiz/',
            PAYMENTSIMG : 'upload/paymentgetway/',
        },
        BUCKET_DATA_PREFIX: REACT_APP_S3_PREFIX,
        BUCKET_STATIC_DATA_ALLOWED: REACT_APP_BUCKET_STATIC_DATA_ALLOWED == '1',
    },
    apiGateway: {
        URL: REACT_APP_BASE_URL + '/',
        USER_URL: REACT_APP_USER_BASE_URL + '/',
        FANTASY_URL: REACT_APP_FANTASY_BASE_URL + '/',
        nodeURL: REACT_APP_NODE_URL + ":4000",
        STOCK_URL: getModuleUrl(REACT_APP_STOCK_BASE_URL),
        PROPS_URL: getModuleUrl(REACT_APP_PROPS_BASE_URL),
        PROPS_URL: getModuleUrl(REACT_APP_PROPS_BASE_URL),
        OPINION_TRADE_URL: getModuleUrl(REACT_APP_OPINION_TRADE_BASE_URL),
    },
    cognito: {
        FB_APP_ID: REACT_APP_FB_APP_ID,
        GOOGLE_CLIENT_ID: REACT_APP_GOOGLE_CLIENT_ID,
        GOOGLE_PROFILE_ID: '',
    }
};

export default {
    ...prod
};