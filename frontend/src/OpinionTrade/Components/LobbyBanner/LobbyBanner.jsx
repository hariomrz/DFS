import React,{useState, Suspense, useEffect} from 'react';
import { Utilities } from 'Utilities/Utilities';
import Images from 'components/images';
import { Helper } from 'Local';
import ReactSlickSlider from './ReactSlickSlider';
import * as WSC from "WSHelper/WSConstants";
import WSManager from 'WSHelper/WSManager';
import { useParams } from 'react-router-dom';
import { LobbyBannerSlider } from 'Component/CustomComponent';

const { Utils, _, Trans } = Helper

const LobbyBanner = (props) => {
    const [bannerList, setBannerList] = useState([]);
    const S3_URL_PREFIX = WSC.S3_BUCKET_PATH + "appstatic/" + WSC.BUCKET_DATA_PREFIX;
    const AppLANG = WSManager ? ('_' + WSManager.getAppLang()) : '';
    const { sports_id } = useParams()
    
    const API = {
        LOBBY_BANNER_LIST: S3_URL_PREFIX + "lobby_banner_list_" + sports_id + AppLANG + ".json"
    }
    
    

const parseBannerData = (bdata) => {
    let refData = "";
    let temp = [];
    _.map(bdata, (item, idx) => {
      if (
        item.game_type_id == 0 ||
        WSManager.getPickedGameTypeID() == item.game_type_id
      ) {
        if (item.banner_type_id == 2) {
          refData = item;
        }
        if (item.banner_type_id == 1) {
          let dateObj = Utils.getUtcToLocal(item.schedule_date);
          if (Utilities.minuteDiffValue({ date: dateObj }) < 0) {
            temp.push(item);
          }
        } else {
          temp.push(item);
        }
      }
    });
    setBannerList(temp);
  };
useEffect(()=>{
    let params = {"sports_id":sports_id}
    WSManager.RestS3ApiCall(API.LOBBY_BANNER_LIST, params).then(({...res }) => {
        parseBannerData(res);
      });
},[])

  return <>
    
        <div className={bannerList.length > 0 ? 'banner-v animation' : 'banner-v'}>
            {
                bannerList.length > 0 && <LobbyBannerSlider BannerList={bannerList}  />
            }
        </div>
  </>
};

export default LobbyBanner;
