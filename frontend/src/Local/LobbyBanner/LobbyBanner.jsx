import React,{lazy, Suspense} from 'react';
// import './LobbyBanner.scss';
import { Utilities } from 'Utilities/Utilities';
import Images from 'components/images';
import { Helper } from 'Local';
import ReactSlickSlider from '../../Component/CustomComponent/ReactSlickSlider';
import { withRedux } from 'ReduxLib';
import Auth from "Local/Helper/Auth/Auth";

const { Utils, _, Trans } = Helper
const LobbyBanner = ({t, ...props}) => {
  const {BannerList} = props;
  var settings = {
    touchThreshold: 10,
    infinite: true,
    slidesToScroll: 1,
    slidesToShow: 1,
    variableWidth: false,
    initialSlide: 0,
    dots: false,
    autoplay: true,
    autoplaySpeed: 5000,
    arrows: false,
    centerMode: BannerList.length == 1 ? false : true,
    responsive: [
      {
        breakpoint: 500,
        settings: {
          className: "center",
          centerPadding: "20px",
        }

      },
      {
        breakpoint: 360,
        settings: {
          className: "center",
          centerPadding: "15px",
        }

      }
    ]
  };
 
const redirectLink = (item) => {
    const targetUrl = item.target_url; 

    if (Auth.getAuth() && targetUrl) {
        window.open(targetUrl, '_blank');
    } 
  }
 
  return <div>
     <Suspense fallback={<div />} >
        <ReactSlickSlider settings={settings}>
                {
                    BannerList.map((item, index) => {
                        let bannerType = item.banner_type_id;
                        let currenyType = item.currency_type;
                        return (
                            <div className="banner-container" key={index}>
                                {
                                    (bannerType == '1' || bannerType == '6' || bannerType == '4')
                                        ?
                                        <div className='banner-item'>
                                            <img alt='' onClick={() => redirectLink(item)} 
                                            src={Utilities.getBannerURL(item.image)} />
                                        </div>
                                        :
                                        <div className='banner-item refer-banner-item'>
                                            {
                                                bannerType == '2' && <img alt='' className='banner-logo' src={Images.REFER_BANNER_IMG_SM} />
                                            }
                                            {
                                                bannerType == '3' && <img alt='' className='banner-logo' src={Images.BANNER_ADD_FUND} />
                                            }
                                            <div onClick={() => redirectLink(item)} 
                                              className='info-container'>
                                                {
                                                    bannerType != '2' && bannerType != '3' &&
                                                    <div className='title-style'>{item.name}</div>
                                                }
                                                <div className='message-style'>
                                                    {bannerType == '2' ? t('Refer a friend & get') + ' ' : bannerType == '3' ? ' ' + t('Deposit Bnr & Earn') + ' ' : ''}
                                                    <span className='highlighted-text'>{currenyType == 'INR' ? (Utilities.getMasterData().currency_code) : (currenyType == 'Bonus' ? <i className="icon-bonus bonus-ic" /> : currenyType == 'Coin' ? <img className="coin-img" src={Images.IC_COIN} alt="" /> : '')}
                                                        {Utilities.numberWithCommas(item.amount)}</span>
                                                    {bannerType == '2' ? ' ' + t("on your friend’s signup.") : bannerType == '3' ? ' ' + t("on your first cash contest.") : ''}
                                                </div>
                                            </div>
                                        </div>
                                }
                            </div>
                        );
                    })
                }
            </ReactSlickSlider></Suspense>
  </div>;
};

export default withRedux(LobbyBanner);
