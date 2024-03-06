import React, { Component, lazy, Suspense } from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { _times, _Map, Utilities, _isUndefined, _isEmpty, parseURLDate } from '../../Utilities/Utilities';
import * as WSC from "../../WSHelper/WSConstants";
import { getUserMatchStatus, getContestLeaderboardLF,getFixtureDetailLF,getNextOverDetails } from '../../WSHelper/WSCallings';
import * as AL from "../../helper/AppLabels";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { AppSelectedSport, DARK_THEME_ENABLE,SELECTED_GAMET } from '../../helper/Constants';
import Images from '../../components/images';
import LFContestLeaderborad from './LFContestLeaderboard';
import CustomLoader from "../../helper/CustomLoader";
import { data } from "jquery";
import ls from 'local-storage';

const ReactSlickSlider = lazy(() => import('../../Component/CustomComponent/ReactSlickSlider'));
const LFWaitingModal = lazy(() => import('./LFWaitingScreenModal'));

export default class LivefantasyOverResult extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            showWS: false,
            LobyyData: !_isUndefined(props.location.state) ? props.location.state.LobyyData : [],
            ballDetailList: !_isUndefined(props.location.state) ? props.location.state.ballDetailList : [],
            next_over:!_isUndefined(props.location.state) ? props.location.state.nextOver : {},
            userMatchStats: [],
            collection_id: this.props.match.params.collection_master_id,
            isLoadingLeaderboard: false,

        }
    }

    componentWillMount() {
    }

    FixtureDetail = async () => {
        //  if (this.state.LobyyData.home) {
        let param = {
            "sports_id": AppSelectedSport,
            "collection_id": this.state.collection_id,
        }
        getFixtureDetailLF(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ LobyyData: responseJson.data }, () => {

                })
            }
        })

    }
    getOverDetails = async ()=>{
        let param = {
            "collection_id": this.state.collection_id,
        }
        getNextOverDetails(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ next_over: responseJson.data }, () => {

                })
            }
        })
    }
    hideLeaderboard = () => {
        this.setState({ showLeaderBrd: false }, () => {

        })
    }
    showLeaderboard = () => {
        this.setState({ showLeaderBrd: true }, () => {

        })
    }
    openLeaderboard = (data) => {
        this.setState({ contestdata: data }, () => {
            this.getContestLeaderboardCall(data)

        })




    }
    getContestLeaderboardCall = async (data) => {
        this.setState({ isLoadingLeaderboard: true })

        let param = {
            "contest_id": data.contest_id,
            "type": 1,
            "pageNo": 1,
            "page_size": 100
        }
        getContestLeaderboardLF(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                // this.setState({ matchPlayerList: responseJson.data })
                this.setState({
                    leaderBoardData: responseJson.data,
                    showLeaderBrd: true,
                    isLoadingLeaderboard: false

                })
            }
            else {
                this.setState({ isLoadingLeaderboard: false })

            }
        })

    }

    componentDidMount() {
        ls.set("isULF", false)
        console.log('LobyyData',JSON.stringify(this.state.LobyyData))
        if(this.state.LobyyData== undefined || _isEmpty(this.state.LobyyData) ){
            this.FixtureDetail()
        }
        if(this.state.next_over == undefined || _isEmpty(this.state.next_over)){
            this.getOverDetails()
        }
        
        this.getUserMatchStatus()
    }
    getUserMatchStatus = async () => {
        //  if (this.state.LobyyData.home) {
        let param = {
            "collection_id": this.state.collection_id ? this.state.collection_id : this.state.LobyyData.collection_id,
        }
        getUserMatchStatus(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ userMatchStats: responseJson.data,
                     ballDetailList: responseJson.data.over_ball }, () => {
                    //this.parseHistoryStateData();

                })
            }
        })

    }

    hideLeaderboard = () => {
        this.setState({
            showLeaderBrd: false
        })
    }
    openWSModal = () => {
        this.setState({
            showWS: true
        })
    }

    hideWSModal = () => {
        this.setState({
            showWS: false
        })
    }
    getPrizeAmount = (prize_data, status) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span style={{ color: status == 1 ? '#999999' : '', display: "inline-block" }} className={"contest-prizes"}>
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>


                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ?
                            <div style={{ color: status == 1 ? '#999999' : '' }} className="contest-listing-prizes" ><i style={{ marginLeft: status == 1 ? 4 : '' }} className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ?
                                <div style={{ display: "flex", flexDirection: "row", justifyContent: "start", marginLeft: 18, marginTop: -13 }}>
                                    <img style={{ height: 15, width: 15, marginLeft: status == 1 ? 4 : '' }} className="img-coin" alt='' src={Images.IC_COIN} />
                                    <span>{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                </div>
                                : AL.PRIZES
                }
            </React.Fragment>
        )


    }
    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
            var amount = 0;
            if (lObj.max_value) {
                amount = parseFloat(lObj.max_value);
            } else {
                amount = parseFloat(lObj.amount);
            }
            if (lObj.prize_type == 3) {
                prizeAmount['is_tie_breaker'] = 1;
            }
            if (lObj.prize_type == 0) {
                prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
            } else if (lObj.prize_type == 2) {
                prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
            } else {
                prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
            }
        })
        return prizeAmount;
    }

    gotoCompletd = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'lobby-completed' } });

    }
    gotoLobby = () => {
        this.props.history.push({ pathname: '/lobby' });

    }
    renderPrize = (item, prizeItem, idx) => {
        return (
            prizeItem != 'undefined' && prizeItem && prizeItem.prize_type &&
                (prizeItem.prize_type == 0) ?
                <span style={{ fontSize: 15 }} key={idx} className="contest-prizes p-0">
                    <div style={{ display: "flex", flexDirection: "row", justifyContent: "center" }}>
                        <span style={{ fontSize: 15, marginTop: 3, color: '#5DBE7D', display: 'inlineBlock' }}>
                            {<i style={{ display: 'inlineBlock', color: '#5DBE7D' }} className="icon-bonus"></i>}

                        </span>
                        <span style={{ fontSize: 15, marginTop: 4, color: '#5DBE7D', display: 'inlineBlock' }}>
                            {Utilities.kFormatter(Number(parseFloat(prizeItem.amount || 0).toFixed(2)))}{item.length === idx + 1 ? '' : ''}

                        </span>
                    </div>
                </span>
                :
                (prizeItem.prize_type == 1) ?
                    <span style={{ display: 'inline-flex' }} key={idx} className="contest-prizes p-0">
                        {
                            <span style={{ fontSize: 15, marginTop: 4, display: 'inlineBlock', color: '#5DBE7D' }}>
                                {Utilities.getMasterData().currency_code}</span>
                        }
                        {
                            <span style={{ fontSize: 15, marginTop: 3, color: '#5DBE7D', display: 'inlineBlock' }}>
                                {Utilities.kFormatter(Number(parseFloat(prizeItem.amount || 0).toFixed(2)))}{item.length === idx + 1 ? '' : ''}
                            </span>
                        }

                    </span>
                    :
                    (prizeItem.prize_type == 2) ?
                        <span style={{ fontSize: 15 }} key={idx} className="contest-prizes p-0">
                            {
                                <span style={{ display: 'inlineBlock', color: '#5DBE7D', fontSize: 15 }}>
                                    <img alt='' style={{ marginRight: '2px', marginBottom: '1px' }} src={Images.IC_COIN} width="14px" height="14px" />
                                    {Utilities.kFormatter(prizeItem.amount)}{item.length === idx + 1 ? '' : ''}
                                </span>
                            }
                        </span>
                        :
                        (prizeItem.prize_type == 3) ?
                            <span key={idx} className="contest-prizes p-0">
                                <span className="merch-prize-sec" style={{ display: 'inlineBlock', fontSize: 15, color: '#5DBE7D', marginTop: 3 }}>
                                    {prizeItem.name}{item.length === idx + 1 ? '' : ''}
                                </span>
                            </span>
                            : '--'
        )
    }
    goToNextOver =(nextOver)=>{
        let data = this.state.LobyyData
        data['collection_master_id'] = nextOver.collection_id;
        nextOver['over'] = nextOver.overs;
        let dateformaturl = parseURLDate(data.season_scheduled_date);
        this.setState({ LobyyData: data })
        let contestListingPath = "/" + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing-lf/' + nextOver.collection_id + '/'  + data.home + "-vs-" + data.away + "-" + dateformaturl ;
        let CLPath = contestListingPath.toLowerCase()+ "?sgmty=" +  btoa(SELECTED_GAMET)
        this.props.history.push({ pathname: CLPath, state: {fromOverResult:true, LobyyData: data, lineupPath: CLPath ,OverData:nextOver} })

    }

    isTrophyShow =(item)=>{
        let updateMatchRank = item;
        let pd_length = updateMatchRank.prize_detail && updateMatchRank.prize_detail.length > 0 ? updateMatchRank.prize_detail.length : false;
        let isWinner = pd_length  ? parseInt(updateMatchRank.prize_detail[pd_length - 1].max) >= parseInt(item.game_rank) : false;
        return isWinner;
    }
    render() {
        const { ballDetailList, showWS, userMatchStats, isLoadingLeaderboard, showLeaderBrd,next_over ,LobyyData} = this.state;
        let contesList = userMatchStats.contest_list ? userMatchStats.contest_list : []
        let contestLength = !_isEmpty(contesList) ? contesList.length : 0
        let titile = LobyyData &&  LobyyData.collection_name ? (LobyyData.collection_name + " " + AL.OVER + " " + LobyyData.overs) :  userMatchStats ? (userMatchStats.collection_name + " " + AL.OVER + " " + userMatchStats.overs) : ''
        const HeaderOption = {
            back: true,
            goBackLobby:true,
            title: titile,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
        }
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: ballDetailList && ballDetailList.length,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: false,
            autoplaySpeed: 5000,
            centerMode: false,
            centerPadding: "13px",
            beforeChange: this.BeforeChange,
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }

                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }

                }
            ]
        };
        let updatedLiveMatchList = {
            
            home_flag: LobyyData.home_flag,
            away_flag: LobyyData.away_flag,


        }


        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container transparent-header web-container-fixed live-fantasy-over-result">
                       <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.LIVEOVER.title}</title>
                            <meta name="description" content={MetaData.LIVEOVER.description} />
                            <meta name="keywords" content={MetaData.LIVEOVER.keywords}></meta>
                        </Helmet>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        {
                            (isLoadingLeaderboard) && <CustomLoader />
                        }
                        <div className="lf-center-header">
                            <div className="lbl">{AL.OVER} {" "} {userMatchStats.overs}</div>
                            <div className="ball-sec">
                                <div className="lft-label-sec">
                                    <div className="vrl"></div>
                                    <div className="lbl">{AL.PTS}</div>
                                </div>
                                {
                                    ballDetailList && ballDetailList.length > 0 &&
                                    <>
                                        {

                                            <Suspense fallback={<div />} ><ReactSlickSlider ref={slider => (this.slider = slider)} settings={settings}>
                                                {
                                                    ballDetailList.map((item, idx) => {
                                                        return (

                                                            <div className={`ball-wrap `}>
                                                                {
                                                                    (item.predict_id != '' && item.result != 0) || item.result > 0 ?
                                                                        <div>
                                                                            <span className={`ball ${item.is_correct == 1 && item.result > 0 ? " success " : item.is_correct == 2 && item.result > 0 ? " danger " : ''}`}>{item.btext != undefined && item.btext != '' ? item.btext : item.score}</span>
                                                                            <span className={`${item.is_correct == 1 && item.result > 0 ? ' success' : ' '}`}>{(item.is_correct == 2 && item.result > 0) ? '--' : item.points && item.points != '' && Utilities.getExactValue(parseFloat(item.points))}</span>
                                                                        </div>

                                                                        :
                                                                        <i className={`icon-game-ball icon-ball-status ${item.active && item.active == 1 ? ' active' : item.predict_id == '' && item.market_id != 0 ? " " : ''}`}></i>



                                                                }
                                                            </div>
                                                        )
                                                    })
                                                }
                                            </ReactSlickSlider></Suspense>
                                        }
                                    </>
                                }
                            </div>
                            <div className="ttl-pts-detail">
                                {AL.TOTAL}{" "}{AL.PTS}
                                <span className="val">{userMatchStats.total_score}</span>
                            </div>
                        </div>
                      
                        <div className={"result-block" + (userMatchStats.prize_status == 1 ? ' p-dis' : '')}>
                            <div className="head">
                                <img style={{ marginRight: 5 }} src={Images.OVER_RESULT}></img>
                                {AL.OVER} {" "} {this.state.LobyyData.overs} {" "} {AL.COMPLETED}
                            </div>
                            {
                                userMatchStats.prize_status == 0 && <div className='prize-not-ditributed'>{AL.PRIZE_DISTRIBUTED_MESSAGE}</div>

                            }
                            <div className="result-dtl-blk">
                                <div className="head">{contestLength} {AL.CONTEST_JOINED}</div>

                                {
                                    contesList && contesList.map((item, idx) => {
                                         let prize_data = item.prize_data != null && !_isEmpty(item.prize_data) ?  JSON.parse(item.prize_data) :[]
                                         //let prize_detail = item.prize_detail != null && !_isEmpty(item.prize_detail) ?  JSON.parse(item.prize_detail) :[]


                                        return (
                                            <div onClick={() => this.openLeaderboard(item)} className="cont-blk">
                                                <div className="etry-dtl">
                                                    {AL.ENTRY}
                                                    <React.Fragment>
                                                        {
                                                            item.currency_type == 2 ?
                                                                <img style={{ height: 15, width: 15, marginLeft: 4 }} className="img-coin" alt='' src={Images.IC_COIN} />
                                                                :
                                                                <div className='curren'>
                                                                    {Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(item.entry_fee)}
                                                                </div>
                                                        }
                                                        {item.currency_type != 1 && Utilities.numberWithCommas(item.entry_fee)}

                                                    </React.Fragment>
                                                    {/* <span> {AL.WIN} {this.getPrizeAmount(item,1)}</span> */}
                                                </div>
                                                <div className="rgt-sec">
                                                    <div className="tbl">
                                                        <div className="rnk-sc">
                                                            {item.game_rank}
                                                            <span>{AL.RANK}</span>
                                                        </div>
                                                        <div className="won-sc">
                                                            <div style={{display:'flex',justifyContent:'center'}}>
                                                            {item.status == 3 && item.is_winner == 1 && prize_data != null && !_isEmpty(prize_data) &&
                                                                _Map(prize_data, (obj, index) => {
                                                                    return this.renderPrize(item, obj, index)
                                                                })
                                                               
                                                            }
                                                            </div>
                                                           
                                                            {
                                                              item.status != 3 &&
                                                              <div>
                                                                  {this.isTrophyShow(item) ? <i className='icon-trophy icon-color'></i> : '--'}
                                                                  {/* {
                                                                       _Map(item.prize_detail, (obj, index) => {
                                                                        return this.renderPrize(item, obj, index)
                                                                    })
                                                                  } */}
                                                              </div>
                                                             

                                                            }
                                                            <span>Won</span>


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        )
                                    })
                                }
                                



                            </div>
                            <div style={{ cursor: 'pointer' }} onClick={() => this.gotoCompletd()} className="my-contest-link">{AL.VIEW} {' '} {AL.MY_CONTEST}</div>
                            <div style={{ cursor: 'pointer',paddingBottom:80 }} className="text-center ">
                                <a href onClick={this.gotoLobby} className="btn btn-rounded">{AL.JOIN_MORE} {' '} {AL.Contest}</a>
                            </div>
                            

                        </div>
                        

                        {
                            !_isEmpty(next_over) &&
                            <div onClick={()=>this.goToNextOver(next_over)} className='bottom-live-over-conatiner'>
                            <div className='conatiner-live-match'>
                                <div className='flag-text-container'>
                                    <img className='flag-home' src={updatedLiveMatchList.home_flag ? Utilities.teamFlagURL(updatedLiveMatchList.home_flag) : Images.NODATA} alt="" />
                                    <img className='flag-away' src={updatedLiveMatchList.away_flag ? Utilities.teamFlagURL(updatedLiveMatchList.away_flag) : Images.NODATA} alt="" />
                                    <div className='go-to-the-game-cente'>
                                        {AL.PLAY + " " + userMatchStats.collection_name + " for"}
                                        <span> {AL, AL.OVER + " " + next_over.overs} </span>
                                        {/* <div style={{display:'inline'}}> {userMatchStats.collection_name + "for next over"} </div> */}


                                    </div>

                                </div>
                                <div className='live-text-arrow'>
                                    {/* <span className="live-indicator"></span> */}
                                    <div className='live'>{AL.PLAY} {"NOW"} <i className="icon-arrow-right"></i>

                                    </div>
                                    

                                </div>

                            </div>
                        </div>
                        }

                        

                        {
                            showWS &&
                            <LFWaitingModal show={showWS} hide={this.hideWSModal} />
                        }
                        {
                            this.state.showLeaderBrd &&
                            <LFContestLeaderborad
                                updateMatchRank={this.state.contestdata}
                                leaderBoardData={this.state.leaderBoardData}
                                MShow={showLeaderBrd}
                                MHide={this.hideLeaderboard}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }

    Shimmer = (index) => {
        return (
            <SkeletonTheme key={index} color={DARK_THEME_ENABLE ? "#030409" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
                <div className="shimmer-list xp-point-list">
                    <div className="shimmer-container">
                        <div>
                            <div className="shimmer-inner shimmer-image">
                                <Skeleton width={36} height={45} />
                                <Skeleton width={40} height={10} />
                            </div>
                        </div>
                        <div className="shimmer-inner">
                            <Skeleton width={24} height={4} />
                            <Skeleton width={24} height={4} />
                        </div>
                    </div>
                </div>
            </SkeletonTheme>
        )
    }
}