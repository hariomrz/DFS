import React, { useState } from 'react'
import Images from '../../components/images'
import { becomeAffilateUser } from '../../WSHelper/WSCallings';
import WSManager from '../../WSHelper/WSManager';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';
import ThankuAffiliateModal from './ThankuAffiliateModal';
import { useHistory } from 'react-router-dom/cjs/react-router-dom.min';

const AffiliateRequest = (props) => {
  const history = useHistory()
   const [domainName, setDomainName] = useState('')
   const [referCount, setReferCount] = useState('')
   const [error, setError] = useState(false);
   const [showThankyou, setShowThankyou] = useState(false)
   const validationRegex = /^(?:(?:(?:[a-zA-z\-]+)\:\/{1,3})?(?:[a-zA-Z0-9])(?:[a-zA-Z0-9\-\.]){1,61}(?:\.[a-zA-Z]{2,})+|\[(?:(?:(?:[a-fA-F0-9]){1,4})(?::(?:[a-fA-F0-9]){1,4}){7}|::1|::)\]|(?:(?:[0-9]{1,3})(?:\.[0-9]{1,3}){3}))(?:\:[0-9]{1,5})?$/;
   const is_affiliate = WSManager.getProfile().is_affiliate
   
   
   const isValidURL = (input) => {
   const validationRegex = /^(?:(?:(?:[a-zA-z\-]+)\:\/{1,3})?(?:[a-zA-Z0-9])(?:[a-zA-Z0-9\-\.]){1,61}(?:\.[a-zA-Z]{2,})+|\[(?:(?:(?:[a-fA-F0-9]){1,4})(?::(?:[a-fA-F0-9]){1,4}){7}|::1|::)\]|(?:(?:[0-9]{1,3})(?:\.[0-9]{1,3}){3}))(?:\:[0-9]{1,5})?$/;
    return validationRegex.test(input);
  };
  
   const validateDomainName = (domainName) => {
    return validationRegex.test(domainName) ? null : 'Invalid URL';
  };

  const validateReferCount = (referCount) => {
    return referCount ? null : 'Refer count must be at least 1';
  };

    const submitAffiliate=(e)=>{
        e.preventDefault()
        const domainError = validateDomainName(domainName);
        const referError = validateReferCount(referCount);
        if (domainError || referError) {
            setError(true); 
            return false;
        }
        if(is_affiliate != 2){
            let param = {
                user_id: WSManager.getProfile().user_id,
                web_url: domainName,
                refer: referCount
            }
            becomeAffilateUser(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    Utilities.showToast(responseJson.message, 5000);
                    showThanku()
                    let lsProfile = WSManager.getProfile();
                    lsProfile['is_affiliate'] = '2'
                    WSManager.setProfile(lsProfile);
                    setReferCount('')
                    setDomainName('')
                    setError(false)
                }
            })
        } else{
            Utilities.showToast('Request already sent', 5000);
        }
    }

   const showThanku = () => {
      setShowThankyou(true)
    }

  const hideThanku = () => {
      setShowThankyou(false)
      history.push('/more')
    }
    
    const goToEarnMoreScr = () => {
      history.push('/earn-coins')
    }

    const goToMore=()=>{
        history.push('/more')
    }

    const handleKeyPress = (e) => {
      if (e.key === 'e' || e.key === 'E') {
        e.preventDefault();
      }
    }

     const handleInputChange = (e) => {
    const value = e.target.value;
    if (/^\d*$/.test(value) && Number(value) >= 0 && Number(value) <= 99999) {
      setReferCount(value);
    }
  };

  
  return (
    <div className='web-container affiliate-request'>
        <div className='main-wrap-aff-rqst'>
            <i className='icon-left-arrow' onClick={goToMore}/>
        <h1 className='aff-request-wrap'>{AL.AFFILIATE_REQUEST}</h1>
        <div className='bdy-t-wrap-afflt-request'>
         <div className='aff-img'>
            <img  src={Images.BULB} alt="Affiliate Image"/>
         </div>
         <p className='aff-p'>{AL.AFFILIATE_PARA}</p>
        </div>
        <form onSubmit={submitAffiliate}>
          <div className='b-main-aff-rqst'>
            <label className='txt-aff'>{AL.WEB_PROMOTION}</label>
            <input type='text' className='input-box' placeholder='Website URL' value={domainName} onChange={(e)=> setDomainName(e.target.value)}/>
            {error && !isValidURL(domainName) && <div className='err-msg'>{AL.INVALID_URL}</div>}
            <label className='txt-aff'>{AL.AFF_USER_COUNT_TXT}</label>

            <input type='number' 
            className='input-box' 
            placeholder='Number' 
            value={referCount} 
            onChange={handleInputChange}
            onKeyDown={handleKeyPress}
            />

            {error && !referCount && <div className='err-msg'>{AL.INPUT_VALID}</div>}
          </div>
          <button className={`btn-sbmit ${domainName === '' || referCount === ''? 'disabled':''}`} disabled={domainName === '' || referCount === ''} type='submit'>{AL.SUBMIT}</button>
          </form>
        </div>
                {
                showThankyou && <ThankuAffiliateModal {...props} preData={{
                    mShow: showThanku,
                    mHide: hideThanku,
                    goToEarnMoreScr: goToEarnMoreScr
                }} />
            }
        </div>
  )
}

export default AffiliateRequest