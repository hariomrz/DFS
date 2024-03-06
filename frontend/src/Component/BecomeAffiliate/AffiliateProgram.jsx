import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities } from '../../Utilities/Utilities';
import { getAffilateUserSummary, getAffilateUserTransaction } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../CustomComponent';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import InfiniteScroll from 'react-infinite-scroll-component';
import WSManager from "../../WSHelper/WSManager";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import CustomLoader from '../../helper/CustomLoader';
import { BecomeAffiliateNew, ThankuAffiliateModal } from '../../Component/BecomeAffiliate';

class AffiliateProgram extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            profileDetail: WSManager.getProfile(),
            userAffiliateData: '',
            USERLIST: [],
            PNO: 1,
            PSIZE: 20,
            HMORE: false,
            ISLOAD: false,
            isLoading: true,
            showBG: false,
            affliateUrl: '',
            showBecomeAM: WSManager.getProfile().is_affiliate && WSManager.getProfile().is_affiliate == 0 ? true : false,
            showThanku: false,

        }
    }
    getExactValue = (pPer) => {
        let num = pPer && pPer.toString(); //If it's not already a String
        if (num && num.includes('.')) {
            num = num.slice(0, (num.indexOf(".")) + 2); //With 3 exposing the hundredths place

        }
        //Number(num); //If you need it back as a Number
        if (num != undefined) {

            return num

        }
    }

    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        if (scrollOffset > 0) {
            this.setState({
                showBG: true
            })
        }
        else {
            this.setState({
                showBG: false
            })
        }
    }
    componentDidMount() {
        window.addEventListener('scroll', this.onScrollList);

        getAffilateUserSummary().then((responseJson) => {
            setTimeout(() => {
                this.setState({
                    isLoading: false
                })
            }, 1500);
            if (responseJson.response_code == WSC.successCode) {
                let lsProfile = WSManager.getProfile();
                if (responseJson.data && responseJson.data.is_affiliate == 1) {
                    this.setState({
                        userAffiliateData: responseJson.data,
                        affliateUrl: process.env.REACT_APP_BASE_URL + '/' + 'signup?affcd=' + responseJson.data.referral_code
                    })
                    lsProfile['is_affiliate'] = 1
                    WSManager.setProfile(lsProfile);
                } else {
                    lsProfile['is_affiliate'] = responseJson.data.is_affiliate || lsProfile.is_affiliate
                    WSManager.setProfile(lsProfile);
                    if (lsProfile.is_affiliate == 2) {
                        // Utilities.showToast(AppLabels.REQ_PENDING, 3000);
                        this.showThanku()
                    } else if (lsProfile.is_affiliate == 4) {
                        // Utilities.showToast(AppLabels.REQ_CANCELED, 3000);
                        this.setState({
                            showBecomeAM: true
                        })
                    }
                    // setTimeout(() => {
                    //     this.props.history.goBack()
                    // }, 1000);
                }
            } else {
                setTimeout(() => {
                    this.props.history.goBack()
                }, 1000);
            }
        })
        this.getList()
        Utilities.setScreenName('AffiliateProgram')
    }
    onCopyLink = () => {
        this.showCopyToast(AppLabels.Link_has_been_copied);
        this.setState({ copied: true })
    }

    showCopyToast = (message) => {
        Utilities.showToast(message, 2000)
    }



    getList() {
        const { PNO, PSIZE, USERLIST, profileDetail } = this.state;
        let param = {
            "current_page": PNO,
            "items_perpage": PSIZE,
            "user_id": profileDetail.user_id
        }
        this.setState({ ISLOAD: true });
        getAffilateUserTransaction(param).then((responseJson) => {
            this.setState({ ISLOAD: false });
            if (responseJson.response_code === WSC.successCode) {
                let listTmp = responseJson.data.result || [];
                this.setState({
                    USERLIST: PNO == 1 ? listTmp : [...USERLIST, ...listTmp],
                    HMORE: listTmp.length >= PSIZE ? true : false,
                    PNO: listTmp.length >= PSIZE ? PNO + 1 : PNO
                })
            }
        })
    }

    renderBlock = (currency, label, value) => {
        return (
            <div className="data-block-wrap">
                <div className="data-count-block text-center">
                    <div className={"count"}>
                        {currency} {Utilities.numberWithCommas(value)}
                    </div>
                    <div className="count-for">{label}</div>
                </div>
            </div>
        )
    }

    renderItem = (item, idx) => {
        return (
            <li key={item.friend_id + idx} className="header-v list-item">
                <span className="user">
                    {
                        Utilities.getMasterData().allow_affiliate_commssion == 1?
                       <>{item.match}</> : <>{item.friend_name}</>
                    }
                    
                    <div className="timing">
                        <MomentDateComponent data={{ date: item.date_added, format: "DD MMM YYYY" }} />  | <span className='league-nm'>{item.league_name}</span>
                    </div>
                </span>
                <span className="amount">
                    {
                        item.friend_amount ?
                        <>
                        <span className="curr">{Utilities.getMasterData().currency_code} </span>{item.friend_amount}
                        </>
                        :
                        '--'
                    }
                </span>
                <span className="commision"><span className="curr">{Utilities.getMasterData().currency_code} </span>{item.signup_commission > 0 ? item.signup_commission : item.site_rake_comission > 0? item.site_rake_comission : item.deposit_comission}</span>
                <span>{item.signup_commission > 0 ? AppLabels.SIGNUP : item.site_rake_comission > 0 ? AppLabels.SITE_RAKE : AppLabels.AMOUNT_DEPOSITED}</span>
            </li>
        )
    }

    hideBecomeAM = (value) => {
        console.log('value', value)
        this.setState({
            showBecomeAM: false, is_affiliate: value ? value : this.state.is_affiliate
        })
        if(value && value == 2){
            this.showThanku()
        }
    }

    showThanku=()=>{
        this.setState({
            showThanku: true,
        })
        
    }
    hideThanku=()=>{
        this.setState({
            showThanku: false,
        })
        
    }

    goBack=()=>{
        this.props.history.goBack()
        this.hideThanku()
    }

    render() {
        const HeaderOption = {
            back: true,
            notification: true,
            title: AppLabels.AFFILIATE_PROGRAM,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            affiIcon: true
        }

        const { profileDetail, userAffiliateData, USERLIST, ISLOAD, HMORE, isLoading, showBG } = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container web-container-fixed wallet-wrapper affiliate-wrap wallet-new" + (showBG ? ' with-bg' : '')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.AffiliateProgram.title}</title>
                            <meta name="description" content={MetaData.AffiliateProgram.description} />
                            <meta name="keywords" content={MetaData.AffiliateProgram.keywords}></meta>
                        </Helmet>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                       
                        {
                            isLoading && <CustomLoader />
                        }
                        {!isLoading && <>
                            <div className="wallet-header header-with-circle">
                                <div className="profile-view">
                                    <img src={profileDetail.image ? Utilities.getThumbURL(profileDetail.image) : Images.DEFAULT_AVATAR} alt="" />
                                    <div className="name-view">
                                        <div className="fl-name">{profileDetail.first_name ? (profileDetail.first_name + ' ' + (profileDetail.last_name || '')) : ''}</div>
                                        <div className="u-name">{profileDetail.user_name}</div>
                                    </div>
                                </div>
                                {/* <div className="d-flex">
                                    <div>
                                        <div className="acc-bal">{Utilities.getMasterData().currency_code} {userAffiliateData.signup_commission || '0'}</div>
                                        <div className="total-bal-text">{AppLabels.BONUS_ON}</div>
                                        <div className="bal-summary">{AppLabels.SIGNUP}</div>
                                    </div>
                                    <div className="m-l-md">
                                        <div className="acc-bal">{userAffiliateData.deposit_commission || '0'}%</div>
                                        <div className="total-bal-text">{AppLabels.COMMISION_ON}</div>
                                        <div className="bal-summary">{AppLabels.DEPOSITE}</div>
                                    </div>
                                </div> */}
                                <div className='affiliate-container'>
                                    <div className='bonus-sigup-conatiner'>
                                        <div className='oval'>
                                            <div className="label-tyype">{this.getExactValue(parseFloat(userAffiliateData.signup_commission)) || '0'}</div>

                                        </div>
                                        <div className='sign-up-bonus'>
                                            {AppLabels.SIGN_UP_BONUS}
                                            {/* {Utilities.getMasterData().currency_code} */}
                                        </div>


                                    </div>
                                    <div className='commision-conatiner'>
                                        <div className='oval'>
                                            <div className="label-tyype">{this.getExactValue(parseFloat(userAffiliateData.deposit_commission)) || '0'}</div>

                                        </div>
                                        <div className='commission'>{AppLabels.COMMISSION} %</div>

                                    </div>
                                    {
                                        Utilities.getMasterData().allow_affiliate_commssion == 1 && 
                                        <div className='site-rake-conatiner'>
                                        <div className='oval'>
                                            <div className="label-tyype">{this.getExactValue(parseFloat(userAffiliateData.site_rake_commission)) || '0'}</div>
                                        </div>
                                        <div className='site-rake-bonus'>
                                            {AppLabels.SITE_RAKE_COMM} %
                                        </div>
                                    </div>
                                    }
                                  
                                </div>
                                {/* <div className="currency-circle">
                                    <i className="font-style-normal">%</i>
                                </div> */}
                            </div>
                            <div className="summary-cont">
                                <div className="bal-summary-wrap mb-3">
                                    {/* <div className="display-table-row">
                                        <div className="cash-summary-with-amt">
                                            {this.renderBlock('', AppLabels.TOTAL_SIGNUP, userAffiliateData.total_signup || '0')}
                                        </div>
                                        <div className="cash-summary-with-amt">
                                            {this.renderBlock(Utilities.getMasterData().currency_code, AppLabels.DEPOSITED_AMOUNT, userAffiliateData.deposit_amount || '0')}
                                        </div>
                                    </div> */}
                                    <div className='affliate-data-conatiner'>
                                        <div className='signup-conatiner'>
                                            <div className='oval'>
                                                <div className='prize-tyype'>{this.getExactValue(parseFloat(userAffiliateData.total_signup || '0'))} </div>

                                            </div>
                                            <div className='sign-ups'>{AppLabels.SIGNUPS} </div>


                                        </div>
                                        <div className='deposit-conatiner'>
                                            <div className='oval'>
                                                <div className='prize-tyype'>{this.getExactValue(parseFloat(userAffiliateData.deposit_amount || '0'))} </div>

                                            </div>
                                            <div className='sign-ups'>{AppLabels.DEPOSITE}{' '}{Utilities.getMasterData().currency_code} </div>

                                        </div>
                                        {
                                            userAffiliateData.site_rake_commission_total > 0?
                                            <div className='deposit-conatiner'>
                                            <div className='oval'>
                                                <div className='prize-tyype'>{Number(userAffiliateData.site_rake_commission_total)} </div>

                                            </div>
                                            <div className='sign-ups'>{AppLabels.SITE_RAKE}{' '}{Utilities.getMasterData().currency_code} </div>
                                        </div>
                                        :
                                         <div className='deposit-conatiner'>
                                         <div className='oval'>
                                             <div className='prize-tyype'>{this.getExactValue(parseFloat(userAffiliateData.commission_amount || '0'))} </div>

                                         </div>
                                         <div className='sign-ups'>{AppLabels.EARNED}{' '}{Utilities.getMasterData().currency_code} </div>
                                     </div>
                                        }
                                    </div>

                                </div>
                                <div className='share-your-subscribe'>{AppLabels.SHARE_SUB_LINK_TO_EARN_MORE}</div>
                                <div className='affliate-url'>
                                    <div className='link-af'>  {this.state.affliateUrl}
                                    </div>
                                    <CopyToClipboard onCopy={this.onCopyLink} text={this.state.affliateUrl} className='rectangle'>
                                        <div>{AppLabels.COPY}</div>
                                    </CopyToClipboard>
                                </div>
                            </div>
                            <div className="user-list">
                                <div className="detail-header">
                                    <span>{AppLabels.DETAILS}</span>
                                </div>
                                <div className="header-v">
                                    <span className="user">{AppLabels.USER}/{AppLabels.MATCH}</span>
                                    <span className="amount">{AppLabels.AFF_AMOUNT}</span>
                                    <span className="commision">{AppLabels.COMMISION}</span>
                                    <span className='type-txt'>{AppLabels.AFF_TYPE}</span>
                                </div>
                                {
                                    USERLIST.length > 0 && <InfiniteScroll
                                        dataLength={USERLIST.length}
                                        hasMore={!ISLOAD && HMORE}
                                        next={() => this.getList()}
                                    >
                                        <ul className="list-view">
                                            {
                                                USERLIST.map((item, idx) => {
                                                    return this.renderItem(item, false, idx);
                                                })
                                            }
                                        </ul>
                                    </InfiniteScroll>
                                }
                            </div>
                        </>}

                        {
                            this.state.showBecomeAM && <BecomeAffiliateNew {...this.props} preData={{
                                mShow: this.state.showBecomeAM,
                                mHide: this.hideBecomeAM
                            }} />
                        }
                        {
                            this.state.showThanku && <ThankuAffiliateModal {...this.props} preData={{
                                mShow: this.showThanku,
                                mHide: this.hideThanku,
                                goToEarnMoreScr: this.goBack,
                                showGoBack: true
                            }} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

export default AffiliateProgram;