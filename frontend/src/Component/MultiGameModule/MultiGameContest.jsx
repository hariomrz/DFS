import React,{lazy, Suspense} from 'react';
import { ProgressBar } from 'react-bootstrap';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import CountdownTimer from '../../views/CountDownTimer';
import { MyContext } from '../../InitialSetup/MyProvider';
import ContestDetailModal from '../../Modals/ContestDetail';
import {createBrowserHistory} from 'history';
import {Utilities} from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import {Sports} from "../../JsonFiles";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import {getPublicContestDetailMultiGame } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../../Component/CustomComponent';
import * as Constants from "../../helper/Constants";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

var globalThis = null;

export default class MultiGameContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            contestData: '',
            showContestDetail: false,
            FixtureData: '',
            referredCodeForSignup: '',
        }
    }
    UNSAFE_componentWillMount() {
        Utilities.setScreenName('sharedcontest')
        WSManager.setShareContestJoin(true);
        WSManager.setPickedGameType(Constants.GameType.MultiGame);
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
                let contest_unique_id = this.props.match.params.contest_unique_id;
                this.props.history.replace("/"+ Sports.url[sportsId]+"/contest/"+contest_unique_id);
                return;
            }
        }
    }

    checkForUserRefferal() {
        if (parsed.referral != "") {
            WSManager.setReferralCode(parsed.referral)
        }
    }

    getPublicContest(data) {
        let param = {
            "contest_unique_id": data.contest_unique_id

        }
        getPublicContestDetailMultiGame(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {

                this.setState({
                    contestData: responseJson.data
                })
                if (responseJson.data.total_user_joined == responseJson.data.size) {
                    Utilities.showToast(AppLabels.Entry_for_the_contest, 3000);
                }
            }
        })     

        
    }


    componentDidMount() {
        globalThis = this;
        const matchParam = this.props.match.params
        this.getPublicContest(matchParam)
    }

    ContestDetailShow = (data) => {
        this.setState({
            FixtureData: data,
            showContestDetail: true,
        });
    }

    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }

    onSubmitBtnClick = (data) => {
        WSManager.clearLineup();
        let urlData = data;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = ''
        if(urlData.home){
            lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
        }
        else{
            let pathurl = Utilities.replaceAll(urlData.collection_name,' ','_');
            lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
        }
        
        if (WSManager.loggedIn()) {
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: data,from:'share_contest' ,isFrom:'shareContest',resetIndex: 1, current_sport: Constants.AppSelectedSport } })
        }
        else {
            this.props.history.push({
                pathname: '/signup', state: {
                    joinContest: true,
                    lineupPath: lineupPath.toLowerCase(),
                    FixturedContest: this.state.FixtureData,
                    LobyyData: data
                }
            })
        }

    }


    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }

    FixtureListFunction = (item) =>{
        return (
            <div className="collection-list">
                <div className="display-table">
                    <div className="display-table-cell text-center v-mid w20">
                        <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="team-img" />
                    </div>
                    <div className="display-table-cell text-center v-mid w-lobby-40">
                        <div className="team-block">
                            <span className="team-name text-uppercase">{item.home}</span>
                            <span className="verses">{AppLabels.VS}</span>
                            <span className="team-name text-uppercase">{item.away}</span>
                        </div>
                        <div className="match-timing">
                            {
                                Utilities.showCountDown(item) ?
                                    <div className="countdown time-line">
                                        {item.game_starts_in && <CountdownTimer deadlineTimeStamp={item.game_starts_in} currentDateTimeStamp={item.today} />}
                                    </div> :
                                    <span> <MomentDateComponent data={{date:item.season_scheduled_date,format:"D MMM - hh:mm A "}} /></span>
                            }
                        </div>
                    </div>
                    <div className="display-table-cell text-center v-mid w20">
                        <img src={Utilities.teamFlagURL(item.away_flag)} alt="" className="team-img" />
                    </div>
                </div>
            </div>
        );
    }

    render() {
        globalThis = this;
        const {
            contestData,
            showContestDetail,
            FixtureData,
        } = this.state;

        const HeaderOption = {
            
            
            
            back: false,
            filter: false,
            
            title: AppLabels.Contest,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true
        }

        var settings = {
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: false,
            centerPadding: '100px 0 5px',
            initialSlide: 0,
            className: "center",
            centerMode: true,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1,
                    }
                },
                {
                    breakpoint: 414,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '60px 0 10px',
                    }
                },
                {
                    breakpoint: 320,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '20px 0 10px',
                        afterChange: '',
                    }
                }
            ]
        };

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container header-margin web-container-fixed share-contest-wrapper share-contest-wrapper-ML" + (Constants.SELECTED_GAMET == Constants.GameType.MultiGame  && contestData && contestData.match_list&&contestData.match_list.length > 1 ? ' share-collection-wrapper' : ' ')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <meta name="keywords" content={MetaData.sharedcontest.keywords} />

                            <title>{MetaData.sharedcontest.title}</title>
                            <meta name="description" content={contestData ? contestData.collection_name+" | "+contestData.contest_name : MetaData.sharedcontest.description} />                            
                            <meta property="og:title" content={contestData ? contestData.contest_name : MetaData.sharedcontest.title}></meta>
                            <link rel="canonical" href={window.location.href} />
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                            <div className="contest-card contest-card-wrapper">
                                <div className="contest-card-header ">
                                    <ul className={Constants.SELECTED_GAMET== Constants.GameType.MultiGame  ? "fixture-list-content" : ""}>
                                        {Constants.SELECTED_GAMET!=Constants.GameType.MultiGame  &&
                                            <React.Fragment>
                                                <li className="team-left-side">
                                                    <div className="team-content-img">
                                                        <img src={contestData.home_flag ? Utilities.teamFlagURL(contestData.home_flag) : ""} alt="" />
                                                    </div>
                                                    <span className="team-name">{contestData.home}</span> 
                                                </li>
                                                <li className="progress-middle">
                                                    <div className="team-content pb10 public-contest">
                                                        <p>{contestData.league_name}</p>
                                                        {
                                                            Utilities.showCountDown(contestData) ?

                                                                <div className="share-contest-countdown">
                                                                    {contestData.game_starts_in && <CountdownTimer deadlineTimeStamp={contestData.game_starts_in} />}
                                                                </div> :
                    
                                                                <span className="share-contest-time-date"> 
                                                                    <MomentDateComponent data={{date:contestData.season_scheduled_date,format:"D MMM - hh:mm A "}} /> 
                                                                </span>

                                                        }
                                                    </div>
                                                </li>
                                                <li className="team-right-side">
                                                    <span className="team-name">{contestData.away}</span>
                                                    <div className="team-content-img">
                                                        <img src={contestData.away_flag ? Utilities.teamFlagURL(contestData.away_flag) : ""} alt="" />
                                                    </div>
                                                </li>
                                            </React.Fragment>
                                        }
                                        { Constants.SELECTED_GAMET==Constants.GameType.MultiGame  && contestData &&contestData.match_list&& contestData.match_list.length == 1 &&
                                            <React.Fragment>
                                                <li className="team-left-side">
                                                    <div className="team-content-img">
                                                        <img src={contestData.match_list ? Utilities.teamFlagURL(contestData.match_list[0].home_flag) : ""} alt="" />
                                                    </div>
                                                    <span className="team-name">{contestData.match_list[0].home}</span> 
                                                </li>
                                                <li className="progress-middle">
                                                    <div className="team-content pb10 public-contest">
                                                        <p>{contestData.match_list[0].league_name}</p>
                                                        {
                                                            Utilities.showCountDown(contestData) && contestData.today ?

                                                                <div className="share-contest-countdown">
                                                                    {contestData.game_starts_in && <CountdownTimer deadlineTimeStamp={contestData.game_starts_in} currentDateTimeStamp={contestData.today} />}
                                                                </div> :
                    
                                                                <span className="share-contest-time-date"> 
                                                                {contestData.match_list&&contestData.match_list[0].league_name}
                                                                </span>

                                                        }
                                                    </div>
                                                </li>
                                                <li className="team-right-side">
                                                    <span className="team-name">{contestData.match_list[0].away}</span>
                                                    <div className="team-content-img">
                                                        <img src={contestData.match_list ? Utilities.teamFlagURL(contestData.match_list[0].away_flag) : ""} alt="" />
                                                    </div>
                                                </li>
                                            </React.Fragment>
                                        }
                                        {Constants.SELECTED_GAMET == Constants.GameType.MultiGame  && contestData && contestData.match_list&& contestData.match_list.length > 1 &&
                                            <li className="progress-middle progress-middle-fullwidth ">
                                                <div className="team-content pb10">
                                                    <p>{contestData.collection_name}</p>
                                                    <div className="collection-match-info">
                                                        {contestData.match_list.length} {AppLabels.MATCHES}
                                                        <span className="circle-divider"></span>
                                                        {
                                                            Utilities.showCountDown(contestData) && contestData.today ?

                                                                <div className="share-contest-countdown">
                                                                    {contestData.game_starts_in && <CountdownTimer deadlineTimeStamp={contestData.game_starts_in} currentDateTimeStamp={contestData.today} />}
                                                                </div> :
                    
                                                                <span className="share-contest-time-date"> 
                                                                {contestData.match_list&&contestData.match_list[0].league_name}

                                                                </span>

                                                        }
                                                    </div>
                                                </div>
                                                <div className="collection-body">
                                                    <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                                                        {contestData.match_list && contestData.match_list.map((item, index) => {
                                                                return (
                                                                    <React.Fragment>
                                                                        <div className="collection-list-slider">
                                                                            {this.FixtureListFunction(item)}
                                                                        </div>
                                                                    </React.Fragment>
                                                                );
                                                            })
                                                        }                                                        
                                                    </ReactSlickSlider></Suspense>
                                                </div>
                                            </li>
                                        }
                                    </ul>
                                </div>


                                <div className="contest-list contest-card-body" >
                                    <div className="contest-list-header">
                                        <div className="contest-heading">
                                           
                                            {contestData.multiple_lineup > 0 &&

                                                <span className="featured-icon" onClick={(e)=>e.stopPropagation()}>m</span>
                                            }
                                            {
                                                contestData.guaranteed_prize == 2 && parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size) &&
                                                <span className="featured-icon" onClick={(e)=>e.stopPropagation()}>g</span>

                                            }
                                            {
                                                contestData.is_confirmed == 1 && 
                                                parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size) &&
                                                <span className="featured-icon" onClick={(e)=>e.stopPropagation()}>c</span>
                                            }
                                            {/* -----assured code here----*/}
                                            <h3 className="win-type">
                                                <span >
                                                    <span className="text-uppercase " >
                                                        {AppLabels.WIN}
                                                                    </span>
                                                    {(contestData.prize_type == 0) &&
                                                        <span><i className="icon-bonus"></i> {contestData.prize_pool == "0" ? AppLabels.PRACTICE : contestData.prize_pool}</span>
                                                    }

                                                    {(contestData.prize_type == 1) &&
                                                        <span>
                                                            <span className="currency-span">{Utilities.getMasterData().currency_code}</span>
                                                            {contestData.prize_pool == "0" ? AppLabels.PRACTICE : contestData.prize_pool}</span>
                                                    }


                                                    {contestData.prize_type == 2 &&
                                                        <span>
                                                            <img src={Images.COINS} alt="" className="beans-img" />
                                                            {contestData.prize_pool == "0" ? AppLabels.PRACTICE : contestData.prize_pool}
                                                        </span>
                                                    }
                                                </span>
                                               

                                            </h3>
                                            {
                                                contestData.max_bonus_allowed != '0' &&
                                                <div className="text-small-italic">
                                                    {contestData.max_bonus_allowed}{'% '}{AppLabels.BONUS}
                                                </div>
                                            }
                                        </div>
                                        <div className="display-table">
                                            <div className="progress-bar-default display-table-cell v-mid" >
                                                <ProgressBar now={globalThis.ShowProgressBar(contestData.total_user_joined, contestData.minimum_size)} className={parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size) ? ' ' : 'danger-area'} />
                                                <div className="progress-bar-value" >
                                                    <span className="user-joined">{contestData.total_user_joined}</span><span className="total-entries"> / {contestData.size} {AppLabels.ENTRIES}</span>
                                                    <span className="min-entries">{AppLabels.MIN} {contestData.minimum_size}</span>
                                                </div>
                                            </div>
                                            <div className="display-table-cell v-mid position-relative entry-criteria pl15" >
                                                {parseInt(contestData.total_user_joined) < parseInt(contestData.size) && <button onClick={() => this.ContestDetailShow(contestData)} 
                                                className="white-base btnStyle btn-rounded btn btn-primary ">
                                                    {contestData.entry_fee > 0 ? 
                                                      
                                                        <React.Fragment>
                                                            {
                                                                contestData.currency_type == 2 ?
                                                                    <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                                    :
                                                                    <span>
                                                                        {Utilities.getMasterData().currency_code}
                                                                    </span>
                                                            }
                                                            {Utilities.numberWithCommas(contestData.entry_fee)}
                                                        </React.Fragment>
                                                        : AppLabels.FREE
                                                    }


                                                    {/* {
                                                            (contestData.prize_type == 0 || contestData.prize_type == 1 || contestData.prize_type == 2) &&
                                                            <React.Fragment>
                                                                <span> <i className="icon-bonus"></i> </span>{contestData.entry_fee}
                                                            </React.Fragment>
                                                    }

                                                    {(contestData.prize_type == 1) &&
                                                        <React.Fragment> 
                                                            <span className="currency-span">{Utilities.getMasterData().currency_code}</span>
                                                            {contestData.entry_fee}
                                                        </React.Fragment>
                                                    }


                                                    {contestData.prize_type == 2 &&
                                                        <React.Fragment> 
                                                            <img src={Images.COINS} alt="" className="beans-img" />
                                                            {contestData.entry_fee}
                                                        </React.Fragment>
                                                    } */}
                                                </button>}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button className="btn-block btn-primary bottom btn btn-default" onClick={() => this.props.history.push('/lobby')}>{AppLabels.GO_TO_LOBBY}</button>
                            {showContestDetail &&
                                <ContestDetailModal showPCError={true} LobyyData={contestData} IsContestDetailShow={showContestDetail} onJoinBtnClick={this.onSubmitBtnClick} IsContestDetailHide={this.ContestDetailHide} OpenContestDetailFor={FixtureData} {...this.props}  />
                            }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}