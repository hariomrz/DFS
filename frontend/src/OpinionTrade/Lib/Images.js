import app_config from "../../InitialSetup/AppConfig";
import { Utilities } from 'Utilities/Utilities';

// const {Utils} = Helper
// const {getImageBaseUrl} = Utils

const getImageBaseUrl=(image) =>{
    let IMAGE_BASE_URL = Utilities.getS3URL(image);
    return process.env.NODE_ENV === 'production' ? IMAGE_BASE_URL : require('../../assets/img/' + image);
}

const Images = {
    S3_BUCKET_IMG_PATH: app_config.s3.BUCKET + "assets/img/",
    LEADERBOARD_ICON: getImageBaseUrl('leaderboard-ic.png'),
    BANNER_1: getImageBaseUrl('banner-1.png'),
    BANNER_2: getImageBaseUrl('banner-2.png'),
    IC_BADGE: getImageBaseUrl('ic-badge.png'),
    IC_RUPEE: getImageBaseUrl('ic-rupee.png'),
    BG_EMPTY: getImageBaseUrl('bg-empty.png'),
    IC_TRADE: getImageBaseUrl('trade.svg'),
    IC_MATCH_TRADE: getImageBaseUrl('matc-trade.svg'),
    IC_UNMATCH_TRADE: getImageBaseUrl('unmatched-trade.svg'),
    IC_COIN: getImageBaseUrl('ic-coin.png'),
    COIN_IMG: getImageBaseUrl('coin-img.png'),
    DEFAULT_AVATAR: getImageBaseUrl('default_avatar.svg'),
    IC_SHARE: getImageBaseUrl('share.svg'),
    IC_PINS: getImageBaseUrl('pins.svg'),
    ICON_TRADE: getImageBaseUrl('icon_trade.svg'),
    IC_OPTION: getImageBaseUrl('ic_option.svg'),
    IC_OPTION_DOWN: getImageBaseUrl('ic_option_down.svg'),
    
    
    

    
}
export default Images;