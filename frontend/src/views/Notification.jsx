import React from 'react';
import { Media } from 'react-bootstrap';
import { MyContext } from '../InitialSetup/MyProvider';
import { Utilities, _Map, _times } from '../Utilities/Utilities';
import { Helmet } from "react-helmet";
import { setValue, NOTIFICATION_DATA,DARK_THEME_ENABLE, GameType } from '../helper/Constants';
import { getNotification } from '../WSHelper/WSCallings';
import InfiniteScroll from 'react-infinite-scroll-component';
import ls from 'local-storage';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../components/CustomHeader';
import MetaData from "../helper/MetaData";
import Images from '../components/images';
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { NoDataView, MomentDateComponent } from '../Component/CustomComponent';
import WSManager from '../WSHelper/WSManager';
import { XPNotification } from '../Component/XPModule';
var globalThis = null;

const NotificationList = ({ context, notificationItem, iconList }) => {
    let isWinning = notificationItem.notification_type == 600 || notificationItem.notification_type == 3 || notificationItem.notification_type == 183 || notificationItem.notification_type == 223 || notificationItem.notification_type == 241 || notificationItem.notification_type== 254 || notificationItem.notification_type== 472 || notificationItem.notification_type== 554 || notificationItem.notification_type== 651;
    let cardNotify = notificationItem.notification_type == 600 ||notificationItem.notification_type == 181 || notificationItem.notification_type == 251 || notificationItem.notification_type == 252 || notificationItem.notification_type == 550;
    let Data = JSON.parse(notificationItem.content);
    let UnReadNoti = false //notificationItem.notification_status == 1;
    let addClass = (notificationItem.notification_type == 600 || notificationItem.notification_type == 181 || notificationItem.notification_type == 550) ? ' congrats-notify' : notificationItem.notification_type == 251 ? ' refund-notify' : notificationItem.notification_type == 252 ? ' blnt-notify' : '';
    return <div className={"list-card" + (notificationItem.notification_status == 1 ? ' unread': '') + (isWinning ? ' winning' : '') + (cardNotify ? ' card-notification' : '') + (addClass) + (UnReadNoti ? ' un-read-notify' : '')} onClick={() => globalThis.gotoDetails(notificationItem,Data)}>
        {
            (notificationItem.notification_type == 181 || notificationItem.notification_type == 600 || notificationItem.notification_type == 251 || notificationItem.notification_type == 252 || notificationItem.notification_type == 550) ?
                <React.Fragment>
                    <Media>
                        <Media.Left style={{background:notificationItem.notification_type == 600 ? '#ffffff':''}} align="middle">
                            {
                             <i style={{color:notificationItem.notification_type == 600 ? ' #F4B553' :''}} className={(iconList.hasOwnProperty(notificationItem.notification_type) ? iconList[notificationItem.notification_type] : 'icon-mail')}></i>

                            }
                        </Media.Left>
                        <Media.Body>
                            <Media.Heading>{notificationItem.subject}</Media.Heading>
                            <p dangerouslySetInnerHTML={{ __html: globalThis.renderTagMessage(notificationItem) || '--' }}></p>
                            <Media className="btm-info">
                                <div className="notification-timing">
                                    <MomentDateComponent data={{ date: notificationItem.added_date, format: "D MMM - hh:mm A " }} />
                                </div>
                                {
                                    notificationItem.notification_type != 550 && notificationItem.notification_type != 600 &&
                                    <div className="notify-won">
                                        <div className="label">{AppLabels.YOU_WON}</div>
                                        <div className="value">
                                            <img src={Images.IC_COIN} alt="" />
                                            <span>
                                                {Data.amount || 0}

                                            </span>
                                            {/* {
                                            Data.amount ?
                                            Data.amount : 0
                                        } */}
                                        </div>
                                    </div>
                                }
                            </Media>
                        </Media.Body>
                    </Media>
                </React.Fragment>
                :
                <React.Fragment>
                    {
                        iconList[notificationItem.notification_type] == 'icon-ruppee' ?
                            <span className='notification-type-currency'>{Utilities.getMasterData().currency_code}</span> :
                            <i className={(iconList.hasOwnProperty(notificationItem.notification_type) ? iconList[notificationItem.notification_type] : 'icon-mail')}></i>
                    }

                    <p dangerouslySetInnerHTML={{ __html: globalThis.renderTagMessage(notificationItem) || '--' }}></p>
                    <p className="notification-timing">
                        <MomentDateComponent data={{ date: notificationItem.added_date, format: "D MMM - hh:mm A " }} />
                    </p>
                </React.Fragment>
        }
    </div>
}

const Shimmer = () => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div className="shimmer-container">
                <div className="image">
                    <Skeleton width={30} height={30} />
                </div>

                <div className="shimmer-list">
                    <Skeleton height={8} />
                    <Skeleton height={6} width={'50%'} />
                    <div className="pt-md-1">
                        <Skeleton height={4} width={'30%'} />
                    </div>
                </div>
            </div>            
        </SkeletonTheme>
    )
}

var hasMore = false;
export default class Notification extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoading: false,
            notificationList: [],
            isLoadMoreLoaderShow: false,
            limit: 20,
            sort_field: "added_date",
            sort_order: "DESC",
            page_no: 1,
            iconByNotificationType: {
                '1': 'icon-my-contests',
                '2': 'icon-trophy-cancelled',
                '3': 'icon-trophy',
                '4': 'icon-ruppee',
                '5': 'icon-mail',
                '6': 'icon-ruppee',
                '7': 'icon-ruppee',
                '8': 'icon-my-contests',
                '9': 'icon-ruppee',
                '20': 'icon-trophy',
                '25': 'icon-ruppee',
                '26': 'icon-ruppee',
                '27': 'icon-ruppee',
                '28': 'icon-ruppee',
                '33': 'icon-ruppee',
                '34': 'icon-ruppee',
                '35': 'icon-ruppee',
                '36': 'icon-ruppee',
                '37': 'icon-ruppee',
                '125': 'icon-trophy-cancelled',
                '138': 'icon-coins-bal-ic',
                '139': 'icon-bonus',
                '140': 'icon-ruppee',
                '141': 'icon-coins-bal-ic',
                '183': 'icon-trophy',
                '223': 'icon-trophy',
                '241': 'icon-trophy',
                '181': 'icon-gift',
                '252': 'icon-thumb',
                '251': 'icon-info-solid',
                '411': 'icon-coins-bal-ic',
                '412': 'icon-ruppee',
                '413': 'icon-bonus',
                '414': 'icon-trophy',
                '472': 'icon-trophy',
                '550': 'icon-gift',
                '437': 'icon-sub-success',
                '438': 'icon-unsubcribed',
                '439': 'icon-renewable',
                '600': 'icon-flash',

            }
        }
    }

    componentDidMount() {
        Utilities.setScreenName('notification')
        
        Utilities.handleAppBackManage('notification')
        let obj = NOTIFICATION_DATA;
        obj['count'] = 0;
        setValue.setNotificationCount(obj);
        this.getUserNotifications();
        globalThis = this;
    }

    goToLobby = () => {
        this.props.history.push({ pathname: '/' })
    }

    getUserNotifications() {

        let param = {
            "page_no": this.state.page_no,
            "page_size": this.state.limit,
        }

        this.setState({ isLoading: true })
        getNotification(param).then((responseJson) => {
            this.setState({ isLoading: false })

            if (responseJson.response_code == WSC.successCode) {
                if (typeof responseJson.data != "undefined") {
                    hasMore = responseJson.data.length === this.state.limit;
                    let mergeList = [];
                    if (this.state.page_no > 1) {
                        mergeList = [...this.state.notificationList, ...responseJson.data]
                    }
                    else {
                        mergeList = responseJson.data;
                    }


                    this.setState({ notificationList: mergeList, page_no: this.state.page_no + 1 })
                }
            }
        })
    }

    replacePrizeMsg = (msg, data) => {
        if (data.prize_type == 3) {
            msg = msg.replace("{{amount}}", '<span class="highlighted-text">' + (data.name || data.amount) + '</span>');
        }
        else {
            let showIcon = data.prize_type;
            if (showIcon == 0) {
                msg = msg.replace("{{amount}}", '<i class="icon-bonus"></i><span class="highlighted-text">' + data.amount + '</span>');
            }
            if (showIcon == 1) {
                msg = msg.replace("{{amount}}", '<span>' + Utilities.getMasterData().currency_code + '</span><span class="highlighted-text">' + data.amount + '</span>');
            }
            if (showIcon == 2) {
                msg = msg.replace("{{amount}}", '<span class="highlighted-text">' + data.amount + ' coins </span>');
            }
        }
        return msg
    }

    renderTagMessage = rowData => {
        var msg = rowData.message || '';
        const _content = rowData.content ? JSON.parse(rowData.content) : {}
        var content = JSON.parse(rowData.content);
        if (rowData.notification_type === 3 || rowData.notification_type === 254 && content.contest_data && content.contest_data.length > 0) {
            content = content.contest_data[0];
        }
        if (rowData.notification_type === 264 || rowData.notification_type === 265 || rowData.notification_type === 569 || rowData.notification_type === 570) {
            msg = this.replacePrizeMsg(msg, content)
        }
        
        msg = Utilities.replaceNotifAll(msg, "{{currency}}", Utilities.getMasterData().currency_code);
        msg = Utilities.replaceNotifAll(msg, "{{currency_type}}", _content.currency_type == 2 ? `${AppLabels.coins} ` : Utilities.getMasterData().currency_code);
        _Map(Object.keys(content), (key, idx) => {
            if (key == 'date') {
                let startDate = Utilities.getUtcToLocal(content.date);
                let date = Utilities.getFormatedDateTime(startDate, "DD - MMMM");
                msg = msg.replace("{{date}}", '<span>' + date + '</span>');
            }
            else {
                msg = msg.replace("{{" + key + "}}", '<span class="highlighted-text">' + content[key] + '</span>');
            }
        });
        if (msg.includes('#learn_more#') && rowData.notification_type === 550) {
            msg = msg.replace("#learn_more#", '<a class="brand-clr">' + AppLabels.LEARN_MORE_SM + '</a>');
        }
        if (msg.includes('{{level_number}}') && content.custom_data) {
            let customData = JSON.parse(content.custom_data);
            let contest_name = customData.custom_data[0].level_number;
            msg = msg.replace("{{level_number}}", '<a class="brand-clr">' + contest_name + '</a>');
        }
        if (msg.includes('{{bonus_cash}}') && rowData.notification_type === 141) {
            msg = msg.replace("{{bonus_cash}}", '<span class="highlighted-text">' + AppLabels.BONUS_CASH + '</span>');
        }
        if (msg.includes('{{real_cash}}') && rowData.notification_type === 141) {
            msg = msg.replace("{{real_cash}}", '<span class="highlighted-text">' + AppLabels.REAL_CASH + '</span>');
        }
        if (msg.includes('{{gift_voucher}}') && rowData.notification_type === 141) {
            msg = msg.replace("{{gift_voucher}}", '<span class="highlighted-text">' + AppLabels.GIFT_VOUCHER + '</span>');
        }
        if (msg.includes('day_number') && content.custom_data) {
            let customData = JSON.parse(content.custom_data);
            msg = msg.replace("{{day_number}}", '<span className="highlighted-text">' + customData.day_number + '</span>');
        }
        if (msg.includes('contest_name') && content.custom_data) {
            let customData = JSON.parse(content.custom_data);
            let contest_name = customData.custom_data[0].contest_name;
             msg = msg.replace("{{contest_name}}", '<span className="highlighted-text">' + contest_name + '</span>');
        }
        if (msg.includes('amount') && content.custom_data && content.custom_data.length > 0) {
            let customData = content.custom_data[0];
            msg = this.replacePrizeMsg(msg, customData)
            // if (customData.prize_type == 3) {
            //     msg = msg.replace("{{amount}}", '<span class="highlighted-text">' + customData.name + '</span>');
            // }
            // else {
            //     let showIcon = customData.prize_type;
            //     if (showIcon == 0) {
            //         msg = msg.replace("{{amount}}", '<i class="icon-bonus"></i><span class="highlighted-text">' + customData.amount + '</span>');
            //     }
            //     if (showIcon == 1) {
            //         msg = msg.replace("{{amount}}", '<span>' + Utilities.getMasterData().currency_code + '</span><span class="highlighted-text">' + customData.amount + '</span>');
            //     }
            //     if (showIcon == 2) {
            //         msg = msg.replace("{{amount}}", '<span class="highlighted-text">' + customData.amount + ' coins </span>');
            //     }
            // }
        }
        if (msg.includes('start_date') && content.start_date) {
            let startDate = Utilities.getUtcToLocal(content.start_date);
            let date = Utilities.getFormatedDateTime(startDate, "D MMM YYYY");
            msg = msg.replace("{{start_date}}", '<span>' + date + '</span>');
        }
        if (msg.includes('contest_name') && content.custom_data) {
            let customData = JSON.parse(content.custom_data);
            let contest_name = customData.custom_data[0].contest_name;
             msg = msg.replace("{{contest_name}}", '<span className="highlighted-text">' + contest_name + '</span>');
        }
        if(msg.includes('{{p_to_id}}')){
            msg = msg.replace('{{p_to_id}}',AppLabels.replace_PANTOID(AppLabels.PAN))
        }
        if (msg.includes('{{coins}}') && msg.includes('{{coin_balance}}') && content.custom_data) {
            let customData = content.custom_data;
            msg = msg.replace("{{coin_balance}}", '<span className="highlighted-text">' + customData.coin_bal + '</span>');
            msg = msg.replace("{{coins}}", '<span className="highlighted-text">' + customData.amount + '</span>');
            msg = msg.replace("{{coin_icon}}", '');
        }
        if(msg.includes('{{a_to_id}}')){
            msg = msg.replace('{{a_to_id}}',AppLabels.replace_PANTOID(AppLabels.AADHAR_TEXT))
        }
        return msg;
    };

    replacePrizeMsg = (msg, data) => {
        if (data.prize_type == 3) {
            msg = msg.replace("{{amount}}", '<span class="highlighted-text">' + (data.name || data.amount) + '</span>');
        }
        else {
            let showIcon = data.prize_type;
            if (showIcon == 0) {
                msg = msg.replace("{{amount}}", '<i class="icon-bonus"></i><span class="highlighted-text">' + data.amount + '</span>');
            }
            if (showIcon == 1) {
                msg = msg.replace("{{amount}}", '<span>' + Utilities.getMasterData().currency_code + '</span><span class="highlighted-text">' + data.amount + '</span>');
            }
            if (showIcon == 2) {
                msg = msg.replace("{{amount}}", '<span class="highlighted-text">' + data.amount + ' coins </span>');
            }
        }
        return msg
    }

    gotoDetails(NotificationData,contentData) {
        if (!NotificationData.notification_type) {
            return true;
        }

        let myContestTypes = [1, 3, 20, 22, 23, 24, 261, 260, 223, 241,254,250,472,436];
        let walletTypes = [4, 7, 9, 25, 26, 27, 28, 33, 34, 35, 36, 37, 53, 54, 55, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85];
        let transaction = [138, 6, 139, 140, 141,151, 142, 143, 144, 145, 146, 147, 148, 149, 150,151, 50, 51, 52, 56, 57, 58, 59, 60, 71, 72, 73, 411, 412, 413, 86, 87, 92, 93, 224, 220, 184, 174, 251,183,425, 550,589];
        let prediction = [222, 221, 176, 240, 175];
        let stockF = [/*552,*/ 553, 554];
        let pickem = [252, 181]; 
        let xpPoint = [550]; 
        let pickfantasy = [645,647]; 
        let propsFantasy = [654, 655];

        if(propsFantasy.indexOf(NotificationData.notification_type) > -1){
            let content = JSON.parse(NotificationData.content)
            WSManager.setPickedGameType(GameType.PropsFantasy)
            if (content.sports_id) {
                ls.set('selectedSports', content.sports_id);
                setValue.setAppSelectedSport(content.sports_id);
            }
            if (NotificationData.notification_type == 654) {
                this.props.history.push('/my-contests?contest=upcoming');
            }
            if (NotificationData.notification_type == 655) { 
                this.props.history.push('/my-contests?contest=completed');
            }
        }

        if(stockF.indexOf(NotificationData.notification_type) > -1){
            if(contentData.stock_type == '2'){
                WSManager.setPickedGameType(GameType.StockFantasyEquity)
            }
            else if(contentData.stock_type == '1'){
                WSManager.setPickedGameType(GameType.StockFantasy)
            }
            this.props.history.push({ pathname: '/my-contests', state: { from: NotificationData.notification_type === 554 ? 'notification' : '' }});
        }
        if (myContestTypes.indexOf(NotificationData.notification_type) > -1) {
            WSManager.setPickedGameType(GameType.DFS)
            console.log('NotificationData.content',NotificationData.content)
            if (NotificationData.content.sports_id) {
                ls.set('selectedSports', NotificationData.content.sports_id);
                setValue.setAppSelectedSport(NotificationData.content.sports_id);
            }
            if (NotificationData.notification_type == 1 || NotificationData.notification_type == 260) {
                var collection_master_id = contentData && contentData.collection_master_id ? contentData.collection_master_id : ''
                this.props.history.push({ pathname: '/my-contests', state: { from: 'notificationupcomin',collectionMasterId:collection_master_id } });

            }

            else if(Utilities.getMasterData().dfs_multi != 1 && NotificationData.notification_type == 3 || NotificationData.notification_type == 472 || NotificationData.notification_type == 436){

                if(contentData.season_game_count > 1){
                    WSManager.setPickedGameType(GameType.MultiGame)
                }
                var collection_master_id = contentData && contentData.collection_master_id ? contentData.collection_master_id : contentData.match_data &&  contentData.match_data.collection_master_id ? contentData.match_data.collection_master_id : contentData.contest_data && contentData.contest_data.collection_master_id ? contentData.contest_data.collection_master_id : ''
                this.props.history.push({ pathname: '/my-contests', state: { from: 'notification',collectionMasterId:collection_master_id } });

            }
            else {
                if (NotificationData.notification_type == 223 || NotificationData.notification_type == 241) {
                    WSManager.setPickedGameType(GameType.Pred)
                }
                this.props.history.push({ pathname: '/my-contests', state: { from: 'notification' } });
            }
        }
        if (pickem.indexOf(NotificationData.notification_type) > -1) {
            WSManager.setPickedGameType(GameType.Pickem)
            if (NotificationData.notification_type == 250) {
                //this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl() + "#pickem")

            }
            else {
                this.props.history.push({ pathname: '/my-contests', state: { from: 'notification' } });

            }

        }
        // if (pickfantasy.indexOf(NotificationData.notification_type) > -1) {
        //     WSManager.setPickedGameType(GameType.PickFantasy)
        //     this.props.history.push({ pathname: '/my-contests', state: { from: 'notification' } });

        // }

        if (walletTypes.indexOf(NotificationData.notification_type) > -1) {
            this.props.history.push({ pathname: '/my-wallet' });
        }
        if (prediction.indexOf(NotificationData.notification_type) > -1) {
            WSManager.setPickedGameType(GameType.Pred)
            var season_game_uid = contentData && contentData.season_game_uid ? contentData.season_game_uid : ''
            WSManager.setPredictionId(season_game_uid)
            this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl() + "#prediction")
        }
        if (transaction.indexOf(NotificationData.notification_type) > -1) {
            this.props.history.push({ pathname: '/transactions', state: { from: 'notification' } });
        }
        if (xpPoint.indexOf(NotificationData.notification_type) > -1) {
            this.props.history.push({ pathname: '/experience-points', state: { from: 'notification' } });
        }
    }


    fetchMoreData = () => {
        this.getUserNotifications();
    }



    render() {

        const {
            iconByNotificationType
        } = this.state;

        const HeaderOption = {
            back: true,
            menu: true,
            title: AppLabels.NOTIFICATIONS,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container notification-container transparent-header web-container-fixed">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.notification.title}</title>
                            <meta name="description" content={MetaData.notification.description} />
                            <meta name="keywords" content={MetaData.notification.keywords}></meta>
                        </Helmet>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        <div className="webcontainer-inner">
                            {/* <div className="sticky-blue-header"></div> */}
                            {
                                this.state.notificationList.length > 0 &&
                                <InfiniteScroll
                                    dataLength={this.state.notificationList.length}
                                    next={this.fetchMoreData.bind(this)}
                                    hasMore={hasMore}
                                    scrollableTarget='notification-scroll-list'
                                >
                                    {

                                        <div id="notification-scroll-list" className="notification-list">
                                            {
                                                this.state.notificationList.length > 0 && this.state.notificationList.map((item, index) => {
                                                    return (
                                                        <div>
                                                            {/* <XPNotification/> */}
                                                            <NotificationList context={this} notificationItem={item} iconList={iconByNotificationType} notificationKey={index} key={"notificationkey-" + index} />
                                                        </div>
                                                        
                                                    )
                                                })
                                            }
                                        </div>

                                    }

                                </InfiniteScroll>

                            }
                            {
                                this.state.notificationList.length == 0 && !this.state.isLoading &&
                                <NoDataView
                                    BG_IMAGE={Images.no_data_bg_image}
                                    CENTER_IMAGE={Images.no_notification}
                                    MESSAGE_1={AppLabels.DONT_HAVE_ANY + AppLabels.NOTIFICATION_YET}
                                    MESSAGE_2=''
                                    BUTTON_TEXT={AppLabels.GO_BACK_TO_LOBBY}
                                    onClick={this.goToLobby}
                                />
                            }

                            {
                                this.state.notificationList.length == 0 && this.state.isLoading && <div className="shimmer-list-container">
                                    {
                                        _times(15, (index) => {
                                            return (
                                                <Shimmer key={"shimerkey-" + index} />
                                            )
                                        })
                                    }
                                </div>
                            }
                        </div>

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}