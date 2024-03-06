import React, { lazy, Suspense } from 'react';
import { Row,Col } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import Skeleton from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { NoDataView,MomentDateComponent } from '../CustomComponent';
import { getSPCompareTeams } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { DARK_THEME_ENABLE ,StockSetting,setValue} from '../../helper/Constants';
import * as WSC from "../../WSHelper/WSConstants";
import { Utilities, _filter, _Map } from '../../Utilities/Utilities';
import StockTeamPreview from "../StockFantasy/StockTeamPreview";
import SPScoreCalc from './SPScoreCalculation';
const ReactSelectDD = lazy(() => import('../CustomComponent/ReactSelectDD'));
const StockPlayerCard = lazy(() => import('../StockFantasy/StockPlayerCard'));

const Shimmer = () => {
    return (
        <div className="ranking-list shimmer margin-2p">
            <div className="display-table-cell pointer-cursor">
                <figure className="user-img shimmer">
                    <Skeleton circle={true} width={40} height={40} />
                </figure>
                <div className="user-name-container shimmer">
                    <Skeleton width={'80%'} height={8} />
                    <Skeleton width={'40%'} height={5} />
                </div>
            </div>
            <div className="display-table-cell pointer-cursor">
                <figure className="user-img shimmer">
                    <Skeleton circle={true} width={40} height={40} />
                </figure>
                <div className="user-name-container shimmer">
                    <Skeleton width={'80%'} height={8} />
                    <Skeleton width={'40%'} height={5} />
                </div>
            </div>
        </div>
    )
}

export default class SPTeamComp extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            userRankList: [],
            oppData: [],
            youData: [],
            youDataScore: 0,
            oppDataScore: 0,
            selectedTeam: '',
            youLineupData: '',
            otherLineupData: '',
            youCLD: [],
            oppCLD: [],
            youAllLD: [],
            oppAllLD: [],
            UTeamCVC: [],
            OppTeamCVC: [],
            lineupData: [],
            oppCVCtotalScore: 0,
            youCVCtotalScore: 0,
            commonScore: 0,
            teamOptions: [],
            SelectedLineup: '',
            ShimmerList: [1, 2, 3, 4, 5],
            isLoading: false,
            isCMounted: false,
            status: 2,
            showPlayerCard: false,


            youTeamdata: [],
            oppTeamData: []
        }
    }

    componentDidMount=()=>{
        // if(StockSetting.length > 0){
        //     this.setState({
        //         StockSettingValue: StockSetting
        //     })
        // }
        // else{
        //     getStockLobbySetting().then((responseJson) => {
        //         setValue.setStockSettings(responseJson.data);
        //         this.setState({ StockSettingValue: responseJson.data })
        //     })
        // }
    }

    componentWillMount() {
        this.setLocationStatedata();
    }

    setLocationStatedata = () => {
        if (this.props && this.props.location && this.props.location.state) {
            const { userRankList, oppData, youData, status, selectedContest, rootItem, contestId } = this.props.location.state;
            this.setState({
                userRankList: userRankList,
                oppData: oppData,
                youData: youData,
                // youDataScore: youData.total_score,
                status: status || 2,
                selectedContest: selectedContest,
                rootItem: rootItem,
                contestId: contestId
            }, () => {
                this.getLineupComaprisonData(this.state.youData, this.state.oppData);
                let tmpArry = []
                for (var obj of this.state.userRankList) {
                    tmpArry.push({
                        "value": obj.lineup_master_contest_id,
                        "lineup_master_contest_id": obj.lineup_master_contest_id,
                        "label": '#' + obj.game_rank + ' | ' + obj.team_name,
                        "total_score": obj.total_score
                    })
                }
                this.setState({
                    teamOptions: tmpArry,
                    isCMounted: true
                })
            })
        }
    }

    getLineupComaprisonData = (YOU, OPP) => {
        this.setState({
            isLoading: true
        })
        let param = {
            "u_lineup_master_contest_id": YOU.lineup_master_contest_id,
            "o_lineup_master_contest_id": OPP.lineup_master_contest_id,
        }
        getSPCompareTeams(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let data = responseJson.data;
                this.setState({
                    youLineupData: data.you.lineup,
                    otherLineupData: data.oponent.lineup,
                    youTeamdata: data.you.team_info ? data.you.team_info : [],
                    oppTeamData: data.oponent.team_info ? data.oponent.team_info : []
                }, () => {
                    this.setState({
                        isLoading: false
                    })
                })
            }
        })
    }

    /**
     * @description This function is used to open player lineup page with formatted URL data
     * @param teamItem Team item
     * @see FieldView
    */
    openLineup = (teamItem, data) => {
        this.setState({
            SelectedLineup: teamItem.lineup_master_id, //lineup_master_contest_id
            UserName: teamItem.user_name || '',
            lineupData: teamItem
        }, () => {
            this.showFieldV()
        })
    }

    showFieldV = () => {
        this.setState({
            showFieldV: true
        });
    }
    hideFieldV = () => {
        this.setState({
            showFieldV: false,
            SelectedLineup: ''
        });
    }
    handleTeamChange = (selectedOption) => {
        this.setState({
            selectedTeam: selectedOption,
            youDataScore: selectedOption.total_score
        }, () => {
            this.getLineupComaprisonData(selectedOption, this.state.oppData);
        })
    }

    goBack() {
        this.props.history.goBack();
    }

    showCVCLeadText = (oppScore, youScore) => {
        let OS = parseFloat(oppScore || 0);
        let YS = parseFloat(youScore || 0);
        let showscore = OS > YS ? (OS - YS) : (YS - OS);
        let isVCEnable = this.state.StockSettingValue && this.state.StockSettingValue.vc_point > 0 ? true : false;
        return (
            <>
                {
                    OS > YS ?
                        <>
                        {
                            isVCEnable ?
                            <>
                                {AL.STOCK_OPP_CAP_VC_LEAD} <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
                            </>
                            :
                            <>
                                {AL.STOCK_OPP_CAP_LEAD} <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
                            </>
                        }
                        </>
                        :
                        <>
                        {
                            isVCEnable ?
                                <>
                                {AL.STOCK_YOUR_CAP_VC__LEAD} <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
                                </>
                            :
                                <>
                                {AL.STOCK_YOUR_CAP_LEAD} <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
                                </>
                            }
                        </>
                }
            </>
        )
    }

    PlayerCardShow = (e, item) => {
        e.stopPropagation();
        this.setState({
            playerDetails: item,
            showPlayerCard: true
        });
    }

    PlayerCardHide = () => {
        this.setState({
            showPlayerCard: false,
            playerDetails: {}
        });
    }   

    showPer=(val,total)=>{
        let per = total > 0 ? (val / total) * 100 : 0;
        per = per.toString();
        per = per.slice(0, (per.indexOf("."))+3);
        Number(per);
        return "(" + Math.abs(per) + "%)"
    }

    // showCalcPSum=(value,PRole)=>{
    //     if(PRole == 1){
    //         value = parseFloat(value) * 2
    //     }
    //     else if(PRole == 2){
    //         value = parseFloat(value) * 1.5
    //     }
    //     else{
    //         value = parseFloat(value)
    //     }
    //     return parseFloat(value).toFixed(2)
    // }

    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
            <React.Fragment>
                {AL.WIN} {" "}
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span>
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span><img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
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
    render() {
        const {
            selectedTeam,
            teamOptions,
            youLineupData,
            otherLineupData,
            oppData,
            youData,
            oppCLD,
            oppAllLD,
            youAllLD,
            youCLD,
            UTeamCVC,
            OppTeamCVC,
            oppCVCtotalScore,
            youCVCtotalScore,
            commonScore,
            lineupData,
            isLoading,
            isCMounted,
            youDataScore,
            oppDataScore,
            rootItem,
            showPlayerCard,
            playerDetails,
            youTeamdata,
            oppTeamData,
            contestId,
            status
        } = this.state;
        let youTotSPF = parseFloat(youDataScore || 0)
        let oppTotSPF = parseFloat(oppDataScore || 0)
        let OppLeads = oppTotSPF > youTotSPF ? true : false;
        let youScoreLen = youDataScore.length;
        let oppScoreLen = oppDataScore.length;

        const HeaderOption = {
            back: true,
            hideShadow: true,
            status: status,
            title: rootItem && rootItem.contest_title ? rootItem.contest_title : this.getPrizeAmount(rootItem) ,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            screenDatetitle:rootItem,
            isBid:true,
            // teamName: 
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed team-comparison stk-team-comp stk-eqt-team-comp sp-team-comp">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.leaderboard.title}</title>
                            <meta name="description" content={MetaData.leaderboard.description} />
                            <meta name="keywords" content={MetaData.leaderboard.keywords}></meta>
                        </Helmet>
                        <CustomHeader
                            ref={(ref) => this.headerRef = ref}
                            HeaderOption={HeaderOption}
                            {...this.props} />
                        <div className="team-comparison-wrapper">
                            {
                                rootItem.score_updated_date &&
                                <div className="last-pts-updated">
                                    {AL.UPDATED_AT} <MomentDateComponent data={{ date: rootItem.score_updated_date, format: "hh:mm a" }} />
                                </div>
                            }
                            {
                                !isLoading && youTeamdata != '' && oppTeamData != '' &&
                                <div className="team-comparison-header team-comp-header-new">
                                    <div className="team-names-wrap">
                                        <Row>
                                            <Col sm={6} xs={6}>
                                                <div className="team-info-sec">
                                                    <div className="user-img-sec">
                                                        <img src={youTeamdata.image !== '' ? Utilities.getThumbURL(youTeamdata.image) : Images.DEFAULT_AVATAR} alt="" />
                                                    </div>
                                                    <div className="team-name you-team-name" onClick={() => this.setState({ selectedLineup: (youTeamdata || '').lineup_master_id, showScoreV: true })}>{AL.You}
                                                    </div>
                                                    {/* <div className="team-name you-team-name" onClick={() => this.openLineup(youTeamdata, youLineupData)}>{AL.You}
                                                    </div> */}
                                                    <div className={"post-wrap-inner you-post-wrap-inner" + (teamOptions.length == 1 ? ' no-dropdown' : '')}>
                                                        {
                                                            teamOptions.length > 1 &&
                                                            <i className="icon-arrow-down"></i>
                                                        }
                                                        {
                                                            isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                                onChange={this.handleTeamChange}
                                                                options={teamOptions}
                                                                className="basic-select-field"
                                                                classNamePrefix="select"
                                                                value={selectedTeam}
                                                                placeholder={'#' + youTeamdata.game_rank + " | " + youTeamdata.team_name}
                                                                isSearchable={false}
                                                                theme={(theme) => ({
                                                                    ...theme,
                                                                    borderRadius: 0,
                                                                    colors: {
                                                                        ...theme.colors,
                                                                        primary25: '#fff',
                                                                        primary: '#999',
                                                                    },
                                                                })}
                                                            /></Suspense>
                                                        }
                                                    </div>
                                                </div>
                                            </Col>
                                            <Col sm={6} xs={6}>
                                                <div className="team-info-sec opp-bg">
                                                    <div className="user-img-sec">
                                                        <img src={oppTeamData.image !== '' ? Utilities.getThumbURL(oppTeamData.image) : Images.DEFAULT_AVATAR} alt="" />
                                                    </div>
                                                    <div className="team-name opp-team-name" onClick={() => this.setState({ selectedLineup: (oppTeamData || '').lineup_master_id, showScoreV: true })} >{oppTeamData.user_name}</div>
                                                    
                                                    <div className="post-wrap-inner">#{oppTeamData.game_rank} | {oppTeamData.team_short_name ? oppTeamData.team_short_name : oppTeamData.team_name}</div>
                                                </div>                                            
                                            </Col>
                                        </Row>
                                        <div className="team-info-sec" style={{display: 'none'}}>
                                            <div style={{ width: '100%', maxWidth: '140px' }} className={"team-points-sec" + ((oppScoreLen > 5 || youScoreLen > 5) ? ' team-points-sec-xsm' : (oppScoreLen > 3 || youScoreLen > 3) ? ' team-points-sec-sm' : '')}>
                                                
                                                <span
                                                onClick={() => this.openLineup(oppData, otherLineupData)} 
                                                    style={{ width: '50%', textDecorationLine: 'underline', cursor: 'pointer' }}>{oppDataScore}</span>
                                                <img src={Images.ZIG_ZAG_LINE} alt="" />
                                                <span
                                                    onClick={() => this.openLineup(youData, youLineupData)} style={{ width: '50%', textDecorationLine: 'underline', cursor: 'pointer' }}>{youDataScore}</span> 
                                            </div>
                                            <div className="post-wrap-inner">{AL.TOTAL_POINTS}</div>
                                        </div>
                                    
                                    </div>
                                    <div style={{display:'none', width: 'fit-content', padding: '4px 15px 2px', maxWidth: '300px' }} className="circular-main-wrap" onClick={() => this.openLineup(oppData, otherLineupData)}>
                                        <span style={{ textDecoration: 'none' }}>
                                            <img style={{ width: 22, objectFit: 'contain', marginRight: '6px', position: 'relative', top: '-1px' }} src={DARK_THEME_ENABLE ? Images.search_light : Images.search_dark} alt='' /> {AL.SEE} <span style={{ textDecoration: 'underline' }}>{oppData.user_name}</span> {AL.TEAM_PREVIEW.replace(AL.Team, AL.PORTFOLIO)}
                                        </span>
                                    </div>
                                </div>
                            }
                            <div className="team-comparison-body">
                                {
                                    (isLoading && youLineupData == '' && otherLineupData == '') ?
                                        this.state.ShimmerList.map((item, index) => {
                                            return (
                                                <Shimmer key={index} />
                                            )
                                        })
                                        :
                                        (!isLoading && youLineupData == '' && otherLineupData == '') ?
                                            <NoDataView
                                                BG_IMAGE={Images.no_data_bg_image}
                                                CENTER_IMAGE={Images.teams_ic}
                                                MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                MESSAGE_2={''}
                                                BUTTON_TEXT={AL.GO_TO_MY_CONTEST}
                                                onClick={this.goBack.bind(this)}
                                            />
                                            :
                                            <>
                                                <div className="new-TC">
                                                    <Row>
                                                        <Col sm={6} xs={6}>
                                                            <div className="player-dtl-list">
                                                                {
                                                                    youLineupData && youLineupData.length > 0 &&
                                                                    _Map(youLineupData, (item, idx) => {
                                                                        return (
                                                                            <div className="you-player-sec" key={'all' + idx}>
                                                                                <span style={{ padding: '5px' }} className="img-wrap" onClick={(e)=>this.PlayerCardShow(e, item)}>
                                                                                    <img style={{ top: 0, transform: 'none', objectFit: 'contain', width: '100%', height: '100%' }} src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                                                                                    {
                                                                                        (item.player_role == 1 || item.player_role == 2) &&
                                                                                        <span>{item.player_role == 1 ? 'A' : 'B'}</span>
                                                                                    }
                                                                                </span>
                                                                                <div className="player-nm" onClick={(e)=>this.PlayerCardShow(e, item)}>
                                                                                    <span>{item.stock_name}</span> 
                                                                                </div>
                                                                                <div className="stk-prc-dtl">
                                                                                    {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(item.open_price).toFixed(2))} -  
                                                                                    {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(item.close_price).toFixed(2))} 
                                                                                </div> 
                                                                                <div className="stk-prc-dtl">
                                                                                    {AL.PREDICTED} : {Utilities.numberWithCommas(parseFloat(item.user_price).toFixed(2))}
                                                                                </div>
                                                                                <div className="stk-prc-dtl">
                                                                                    {AL.ACCURACY} : {parseFloat(item.accuracy_percent).toFixed(2)}%
                                                                                </div>
                                                                            </div>
                                                                        )
                                                                    })
                                                                }
                                                            </div>
                                                        </Col>
                                                        <Col sm={6} xs={6}>
                                                            <div className="player-dtl-list left-player-dtl-list">
                                                                {
                                                                    otherLineupData && otherLineupData.length > 0 &&
                                                                    _Map(otherLineupData, (item, idx) => {
                                                                        return (
                                                                            <div className="opp-player-sec" key={'oppall' + idx}>
                                                                                <span style={{ padding: '5px' }} className="img-wrap" onClick={(e)=>this.PlayerCardShow(e, item)}>
                                                                                    <img style={{ top: 0, transform: 'none', objectFit: 'contain', width: '100%', height: '100%' }} src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                                                                                    {
                                                                                        (item.player_role == 1 || item.player_role == 2) &&
                                                                                        <span>{item.player_role == 1 ? 'A' : 'B'}</span>
                                                                                    }
                                                                                </span>
                                                                                <div className="player-nm" onClick={(e)=>this.PlayerCardShow(e, item)}>
                                                                                    <span>{item.stock_name}</span> 
                                                                                </div>
                                                                                <div className="stk-prc-dtl">
                                                                                    {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(item.open_price).toFixed(2))} -  
                                                                                    {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(item.close_price).toFixed(2))} 
                                                                                </div> 
                                                                                <div className="stk-prc-dtl">
                                                                                    {AL.PREDICTED} : {Utilities.numberWithCommas(parseFloat(item.user_price).toFixed(2))}
                                                                                </div>
                                                                                <div className="stk-prc-dtl">
                                                                                    {AL.ACCURACY} : {parseFloat(item.accuracy_percent).toFixed(2)}%
                                                                                </div>
                                                                            </div>
                                                                        )
                                                                    })
                                                                }
                                                            </div>
                                                        </Col>
                                                    </Row>
                                                </div>
                                            </>
                                }


                            </div>
                        </div>
                        {
                            this.state.showFieldV && <StockTeamPreview total_score={lineupData ? (lineupData.total_score || 0) : 0} status={this.state.status} userName={this.state.UserName} isFrom={'point'} CollectionData={this.state.rootItem}
                                openTeam={lineupData ? lineupData.lineup : ''} isViewAllShown={this.state.showFieldV} onViewAllHide={() => this.setState({ showFieldV: false })}  StockSettingValue={this.state.StockSettingValue}  />
                        }
                        {
                            this.state.showScoreV && <SPScoreCalc total_score={this.state.clickTeamScore} selectedLineup={this.state.selectedLineup} CollectionData={this.state.rootItem} isShow={this.state.showScoreV} isHide={() => this.setState({ showScoreV: false })} status={this.state.status} contestId={contestId} />
                        }
                        {
                            showPlayerCard &&
                            <Suspense fallback={<div />} >
                                <StockPlayerCard
                                    mShow={showPlayerCard}
                                    mHide={this.PlayerCardHide}
                                    isFrom={'stockitem'}
                                    isPreview={true}
                                    isFCap={true}
                                    playerData={playerDetails}
                                    buySellAction={this.buySellAction}
                                    addToWatchList={this.addToWatchList} />
                            </Suspense>
        
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}