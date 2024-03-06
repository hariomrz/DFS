import React, { Component } from 'react';
import { Helmet } from "react-helmet";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import { MyContext } from '../../views/Dashboard';
import { Utilities, _Map } from '../../Utilities/Utilities';
import { getRewardList, redeemRewards } from '../../WSHelper/WSCallings';
import CustomHeader from '../../components/CustomHeader';
import WSManager from "../../WSHelper/WSManager";
import MD from "../../helper/MetaData";
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import { ConfrimRedeem } from '.';

var globalRCLIST = []

class RedeemCoins extends Component {
    constructor(props) {
        super(props)
        this.state = {
            RCLIST: [],
            ISLOAD: false,
            userCoinB: (WSManager.getBalance().point_balance || 0),
            isApiCalling: false,
            userCoinBalnc: (WSManager.getBalance().point_balance || 0),
            showCP: false,
            confirmRedeem : {},
            redeemImgValue: {}
        }
    }

    componentDidMount() {
        if (globalRCLIST.length > 0) {
            this.setState({
                RCLIST: globalRCLIST.sort((a, b) => parseInt(a.redeem_coins) - parseInt(b.redeem_coins))
            })
        } else {
            this.callApiRedeemList();
        }
    }

    callApiRedeemList = () => {
        let param = {}
        this.setState({ ISLOAD: true })
        getRewardList(param).then((responseJson) => {
            this.setState({ ISLOAD: false })
            if (responseJson.response_code === WSC.successCode) {
                let listdata = responseJson.data.rewards || [];
                this.setState({
                    RCLIST: listdata.sort((a, b) => parseInt(a.redeem_coins) - parseInt(b.redeem_coins))
                })
                globalRCLIST = listdata.sort((a, b) => parseInt(a.redeem_coins) - parseInt(b.redeem_coins))
            }
        })
    }

    btnAction = (value) => {
        if (!this.state.isApiCalling) {
            let preBal = parseInt(this.state.userCoinB);
            let updatedBal = preBal - parseInt(value.redeem_coins);
            let param = {
                coin_reward_id: value.coin_reward_id['$oid']
            }
            this.setState({
                isApiCalling: true
            })
            redeemRewards(param).then((responseJson) => {
                this.setState({
                    isApiCalling: false
                })
                if (responseJson.response_code === WSC.successCode) {
                    CustomHeader.updateCoinBalance(updatedBal);
                    this.setState({ userCoinB: updatedBal });
                    CustomHeader.showRSuccess(value);
                    let bal = WSManager.getBalance();
                    bal["point_balance"] = updatedBal;
                    WSManager.setBalance(bal);
                }
            })
        }
    }
    openConfirmModal = (item) =>{
        // this.callApiRedeemList()
        this.setState({
            showCP: true,
            confirmRedeem : item,
            userCoinBalnc : (WSManager.getBalance().point_balance || 0)
        })
    }
    hideCP = (updatedBal) => {
        this.setState({
            showCP: false,
            confirmRedeem : {},
        },()=>{
            console.log("first",updatedBal)
            if(updatedBal > 0){
            let bal = WSManager.getBalance();
            bal["point_balance"] = updatedBal;
            WSManager.setBalance(bal);
            this.setState({userCoinBalnc : (WSManager.getBalance().point_balance || 0) })
            }
        } )
    }

    renderListItem = (item, idx) => {
        let imgValue = item.type === "3" ? Utilities.getRewardsURL(item.image) : (item.type === "1" ? Images.REDEEM_BONUS_IMG : Images.REDEEM_CASH_IMG)
        return (
            <li key={idx} className={"border " + (parseInt(item.redeem_coins || '0') > parseInt(this.state.userCoinB) ? 'disabled' : '')} style={{backgroundImage: 'url(' + imgValue + ')', backgroundRepeat: 'no-repeat', backgroundSize: 'cover', backgroundPosition: 'top center'}}>
                <div className="rdm-txt-sec">
                    <div className="lock-overlay"></div>
                    <div className="lock-icon">
                        <img src={Images.REDEEM_LOCK_IMG} alt="" />
                    </div>
                    {/* <div className="text-c">
                        <div className={"detail-c" + (item.type === "3" ? '' : ' not-gift')}>
                            {
                                item.type != "3" ?
                                    <React.Fragment>
                                        <p className="list-t"> {item.type === "1" ? <i className="icon-bonus" /> : (Utilities.getMasterData().currency_code)}{item.value}</p>
                                        <p className="list-d">{item.type === "3" ? item.detail : (item.type === "2" ? AL.REAL_CASH : AL.BONUS_CASH)}</p>
                                    </React.Fragment>
                                    :
                                    <p className="list-t">{item.detail}</p>
                            }
                        </div>
                    </div> */}
                    <div className="rdm-txt">
                        {
                            item.type === "1" ?
                            <>{'Redeem ' + item.value + ' bonus cash'}</> :
                            item.type === "3" ?
                            <>{item.detail}</> :
                            <>{'Redeem ' +  (Utilities.getMasterData().currency_code) + item.value + ' real money'}</>
                        }
                    </div>
                    <div className='position-relative'>
                    <a href className="list-btn" 
                    onClick={() => this.openConfirmModal(item)}
                    // onClick={() => this.btnAction(item)} 
                    ><img className="coin-img" src={parseInt(item.redeem_coins || '0') > parseInt(this.state.userCoinB) ? Images.IC_COIN_GRAY : Images.IC_COIN} alt="" />{item.redeem_coins}</a>
                    </div>
                </div>
            </li>
        )
    }
    // renderListItem = (item, idx) => {
    //     let imgValue = item.type === "3" ? Utilities.getRewardsURL(item.image) : ''
    //     return (
    //         <li key={idx} className={"border " + (parseInt(item.redeem_coins || '0') > parseInt(this.state.userCoinB) ? 'disabled' : '')} style={{backgroundImage: 'url(' + imgValue + ') no-repeat'}}>
    //             <div className="lock-icon"><i className="icon-lock-ic" /></div>
    //             <div className="text-c">
    //                 {
    //                     item.type === "3" && <div className="img-c">
    //                         <img src={Utilities.getRewardsURL(item.image)} alt="" />
    //                     </div>
    //                 }
    //                 <div className={"detail-c" + (item.type === "3" ? '' : ' not-gift')}>
    //                     {
    //                         item.type != "3" ?
    //                             <React.Fragment>
    //                                 <p className="list-t"> {item.type === "1" ? <i className="icon-bonus" /> : (Utilities.getMasterData().currency_code)}{item.value}</p>
    //                                 <p className="list-d">{item.type === "3" ? item.detail : (item.type === "2" ? AL.REAL_CASH : AL.BONUS_CASH)}</p>
    //                             </React.Fragment>
    //                             :
    //                             <p className="list-t">{item.detail}</p>
    //                     }
    //                 </div>
    //             </div>
    //             <div>
    //                 <p className="list-d text-center m-b-xs">{AL.REDEEM_W}</p>
    //                 <a href className="list-btn" onClick={() => this.btnAction(item)} ><img className="coin-img" src={parseInt(item.redeem_coins || '0') > parseInt(this.state.userCoinB) ? Images.IC_COIN_GRAY : Images.IC_COIN} alt="" />{item.redeem_coins}</a>
    //             </div>
    //         </li>
    //     )
    // }

    Shimmer = (index) => {
        return (
            <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
                <div key={index} className="contest-list border shimmer-border">
                    <div className="shimmer-container">
                        <div className="shimmer-image">
                            <Skeleton width={70} height={46} />
                        </div>
                        <div className="shimmer-top-view">
                            <div className="shimmer-line m-l w-75">
                                <Skeleton height={9} width={'75%'} />
                                <Skeleton height={6} width={'80%'} />
                            </div>
                        </div>
                        <div className="m-0">
                            <Skeleton height={6} width={'100%'} />
                            <div className="shimmer-button">
                                <Skeleton height={30} width={85} />
                            </div>
                        </div>

                    </div>
                </div>
            </SkeletonTheme>
        )
    }

    render() {
        const { RCLIST, ISLOAD, showCP,confirmRedeem} = this.state;

        const HeaderOption = {
            back: true,
            notification: false,
            title: AL.REWARDS,
            hideShadow: true,
            earnCoin: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container redeem-coins-cont">
                        <Helmet titleTemplate={`${MD.template} | %s`}>
                            <title>{MD.RWRDS.title}</title>
                            <meta name="description" content={MD.RWRDS.description} />
                            <meta name="keywords" content={MD.RWRDS.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="top-primary">
                            <span className="ttl-amt"><img className="coin-img" src={Images.IC_COIN} /> {Utilities.numberWithCommas(Utilities.kFormatter(this.state.userCoinBalnc))}</span>
                            <div className='ttl-txt'>{AL.TOTAL} {AL.coins}</div> 
                        </div>
                        <ul className="list-type">
                            {
                                _Map(RCLIST, (item, idx) => {
                                    return this.renderListItem(item, idx)
                                })
                            }
                            {
                                RCLIST.length === 0 && ISLOAD &&
                                [1, 1, 1, 1, 1, 1].map((item, index) => {
                                    return this.Shimmer(index)
                                })
                            }
                        </ul>
                        {
                            showCP && <ConfrimRedeem 
                            preData={{
                                mShow: showCP,
                                cpData: confirmRedeem,
                                mHide: this.hideCP
                            }} />
                        }
                    </div>
                )
                }
            </MyContext.Consumer>
        )
    }
}

export default RedeemCoins;