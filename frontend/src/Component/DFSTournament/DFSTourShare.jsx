import React from "react";
import Images from '../../components/images';
import {Sports} from "../../JsonFiles";
import { MomentDateComponent, NoDataView } from "../CustomComponent";
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import {getPublicDFSTourDetail } from '../../WSHelper/WSCallings';
import { _times,Utilities ,_Map,_filter, _isEmpty} from '../../Utilities/Utilities';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import {createBrowserHistory} from 'history';
import { AppSelectedSport, DARK_THEME_ENABLE, GameType } from '../../helper/Constants';
import WSManager from '../../WSHelper/WSManager';
import ls from 'local-storage';
var globalThis = null;
const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

export default class DFSTourShare extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            limit: 20,
            offset: 0,
            hasMore: false,
            TourList:[],
            TourData:[],
            sports_id : AppSelectedSport,
            isListLoading: false
        }
    }

    UNSAFE_componentWillMount() {
        if(Utilities.getMasterData().a_dfst == 1){
            ls.set('isDfsTourEnable',true)
        }
        WSManager.setShareContestJoin(true);
        WSManager.setPickedGameType(GameType.DFS);
        this.checkOldUrlPattern();
        this.checkForUserRefferal();
    }

    /**
     * @description this method is used to replace old url pattern to new eg. from "/7/contest-listing" to "/cricket/contest-listing"
     */
    checkOldUrlPattern=()=> {        
        let sportsId = this.props.match.params.sportsId;
        if(!(sportsId in Sports)){
            if(sportsId in Sports.url){
                let sportsId = this.props.match.params.sportsId;
                let tournament_unique_id = this.props.match.params.tournament_unique_id;
                this.props.history.replace("/"+ Sports.url[sportsId]+"/tournament/"+tournament_unique_id);
                return;
            }
        }
    }

    checkForUserRefferal() {
        if (parsed.referral != "") {
            WSManager.setReferralCode(parsed.referral)
        }
    }

    componentDidMount() {
        globalThis = this;
        const matchParam = this.props.match.params
        this.getPublicContest(matchParam)
    } 

    getPublicContest(data) {
        this.setState({isListLoading:true})
        let param = {
            "tournament_unique_id": data.tournament_unique_id
        }
        getPublicDFSTourDetail(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    isListLoading: false,
                    TourData: responseJson.data
                })
            }
            else{
                this.goToLobby()
            }
        }) 
    } 

    goToLobby=()=>{
        this.props.history.push({ pathname: '/' });
    }

    joinTournament=(item)=>{
        let isFor = 'upcoming';
        let leaguename = item.league_name.replace(/ /g, '');
        let tournamentId = item.tournament_id;
        let leagueId = item.league_id;
        let dateformaturl = Utilities.getUtcToLocal(item.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let tourPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/tournament/' + tournamentId + "/" + leagueId + "/"  + leaguename + "/" + dateformaturl
        if (WSManager.loggedIn()) {
            this.props.history.push({ 
                pathname: tourPath.toLowerCase(), 
                state: {
                    data: item,
                    isFor: isFor || 'upcoming',
                    MerchandiseList: item.merchandise
                } 
            })
        } 
        else {

            this.props.history.push({ pathname: '/signup', state: {lineupPath:tourPath.toLowerCase(), LobyyData: item,FixturedContest: item, current_sport: AppSelectedSport,sportsId: AppSelectedSport,resetIndex: 1, joinContest: true} })
        }
    }

    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'merchandise': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
            var amount = 0;
            if (lObj.prize_type == 3) {
                amount = lObj.amount;
                if(!prizeAmount.merchandise){
                    prizeAmount['merchandise'] = amount;
                }
            }
            else{
                amount += lObj.amount ? (parseInt(lObj.amount) * ((parseInt(lObj.max) - parseInt(lObj.min)) + 1)) : 0
                if (lObj.prize_type == 0) {
                    prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
                } else if (lObj.prize_type == 2) {
                    prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
                } else {
                    prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
                }
            }
        })
        return prizeAmount;
    }

    showPrize = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data);
        let merchandiseList = this.state.TourData.merchandise;
        return (
            <React.Fragment>
                {
                    prizeAmount.merchandise ?
                        merchandiseList && merchandiseList.map((merchandise, index) => {
                            return (
                                <React.Fragment key={index}>
                                    {prizeAmount.merchandise == merchandise.merchandise_id &&
                                        <>{merchandise.name}</>
                                    }
                                </React.Fragment>
                            );
                        })
                    :
                    prizeAmount.real > 0 ?
                    <>
                        {Utilities.getMasterData().currency_code} 
                        {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                    </>
                    :
                    prizeAmount.bonus > 0 ? 
                    <><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</>
                        : 
                        prizeAmount.point > 0 ? 
                            <> <img style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</>
                            : 
                            0
                }
            </React.Fragment>
        )
    }

    isShowPrize = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data);
        let showPrizeSec = false;
        if(prizeAmount.merchandise > 0 || prizeAmount.real > 0 || prizeAmount.bonus > 0 || prizeAmount.point > 0){
            showPrizeSec = true;
        }
        return showPrizeSec
    }

    render() {
        const { 
            TourData,
            isListLoading
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed bg-white share-dfs-tour-screen">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTSHARE.title}</title>
                            <meta name="description" content={MetaData.DFSTSHARE.description} />
                            <meta name="keywords" content={MetaData.DFSTSHARE.keywords}></meta>
                        </Helmet>
                        <div className="header-modalbg">
                            {AL.TOURNAMENT_DETAIL}
                            <i onClick={()=>this.goToLobby()} className="icon-close"></i>
                        </div>
                        <div className="tour-body">
                            {
                                <>
                                {!isListLoading &&
                                    <div className="tour-detail-card">
                                        <div className="tour-img text-center">
                                            <img src={TourData.image ? Utilities.getDFSTourLogo(TourData.image) : Images.DEFAULT_DFS_TOUR_IMG} alt=""/>
                                        </div>
                                        <div className="tour-name">{TourData.name}</div>
                                        <div className="tour-time">
                                            <MomentDateComponent data={{ date: TourData.start_date, format: "D MMM " }} /> - 
                                            <MomentDateComponent data={{ date: TourData.end_date, format: "D MMM " }} />
                                            {
                                                TourData.new_fixture_count && TourData.new_fixture_count > 0 &&
                                                <>
                                                    <span className="slash">|</span>
                                                    <span className="no-of-fix">{TourData.new_fixture_count} {AL.FIXTURES}</span>
                                                </>
                                            }
                                        </div>
                                        {
                                            this.isShowPrize(TourData.prize_detail) > 0 &&
                                            <div className="tour-winnings"><span>{AL.WINNINGS}</span>
                                                {this.showPrize(TourData.prize_detail)}
                                            </div>
                                        }
                                        <div className="tour-league">{TourData.league_name}</div>
                                        <div>
                                            <a href className="btn btn-rounded" onClick={()=>this.joinTournament(TourData)}>
                                                {
                                                    TourData.entry_fee == 0 ? 
                                                    AL.JOIN_FOR_FREE 
                                                    : 
                                                    <>
                                                        {AL.JOIN_FOR}
                                                        {
                                                            TourData.currency_type == 2 ?
                                                            <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                            :
                                                            <span>
                                                                {Utilities.getMasterData().currency_code}
                                                            </span>
                                                        }
                                                        {TourData.entry_fee}
                                                    </>
                                                }
                                            </a>
                                        </div>
                                    </div>
                                
                                }
                                {
                                    isListLoading && 
                                    <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
                                        <div className="dfs-tour-share-shimmer">
                                            <div className="shimmer-card text-center">
                                                <div className="text-center">
                                                    <Skeleton circle={true} height={30} width={30} />
                                                </div>
                                                <div className="shimmer-tname">
                                                    <Skeleton height={20} width={230}  />
                                                </div>
                                                <div className="shimmer-tdate">
                                                    <Skeleton height={20} width={150}  />
                                                </div>
                                                <div className="shimmer-twin">
                                                    <Skeleton height={20} width={180}  />
                                                </div>
                                                <div className="shimmer-tleg">
                                                    <Skeleton height={20} width={150}  />
                                                </div>
                                                <div className="shimmer-tbtn">
                                                    <Skeleton height={44} width={243}  />
                                                </div>
                                            </div>
                                        </div>            
                                    </SkeletonTheme>
                                }
                            </>
                            }
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}