import React, { lazy, Suspense } from 'react';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import Skeleton from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { NoDataView,MomentDateComponent } from '../CustomComponent';
import { stockCompareTeams, getStockLobbySetting } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { DARK_THEME_ENABLE ,StockSetting,setValue} from '../../helper/Constants';
import * as WSC from "../../WSHelper/WSConstants";
import { Utilities, _filter, _Map } from '../../Utilities/Utilities';
import StockTeamPreview from "./StockTeamPreview";
import StockScoreCalculation from './StockScoreCalculation';
const ReactSelectDD = lazy(() => import('../CustomComponent/ReactSelectDD'));
const StockPlayerCard = lazy(() => import('./StockPlayerCard'));

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

export default class StockTeamComparison extends React.Component {
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
            StockSettingValue: [],
            showPlayerCard: false
        }
    }

    componentDidMount=()=>{
        if(StockSetting.length > 0){
            this.setState({
                StockSettingValue: StockSetting
            })
        }
        else{
            getStockLobbySetting().then((responseJson) => {
                setValue.setStockSettings(responseJson.data);
                this.setState({ StockSettingValue: responseJson.data })
            })
        }
    }

    componentWillMount() {
        this.setLocationStatedata();
    }

    setLocationStatedata = () => {
        if (this.props && this.props.location && this.props.location.state) {
            const { userRankList, oppData, youData, status, selectedContest, rootItem } = this.props.location.state;
            this.setState({
                userRankList: userRankList,
                oppData: oppData,
                youData: youData,
                // youDataScore: youData.total_score,
                status: status || 2,
                selectedContest: selectedContest,
                rootItem: rootItem
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
        stockCompareTeams(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let data = responseJson.data;
                this.setState({
                    youLineupData: data.you,
                    otherLineupData: data.oponent,
                    youDataScore: data.you.team_info ? data.you.team_info.total_score : 0,
                    oppDataScore: data.oponent.team_info ? data.oponent.team_info.total_score : 0
                }, () => {
                    this.CVCPlayer()
                    this.commonPlayer(data.you, data.oponent, true)
                    this.commonPlayer(data.oponent, data.you, false)
                    this.setState({
                        isLoading: false
                    })
                })
            }
        })
    }

    CVCPlayer = () => {
        const { youLineupData, otherLineupData } = this.state;
        let youCVCtotalScore = 0;
        let oppCVCtotalScore = 0;
        let UTeamCVC = _filter(youLineupData.lineup, (obj) => {
            return obj.player_role && (obj.player_role == 1)
        });
        let UTeamVC = _filter(youLineupData.lineup, (obj) => {
            return obj.player_role && (obj.player_role == 2)
        });
        UTeamCVC = UTeamCVC.concat(UTeamVC)
        for (var obj of UTeamCVC) {
            youCVCtotalScore = parseFloat(youCVCtotalScore) + parseFloat(obj.score || 0);
            youCVCtotalScore = parseFloat(youCVCtotalScore).toFixed(2)
        }
        let OppTeamCVC = _filter(otherLineupData.lineup, (obj) => {
            return obj.player_role && (obj.player_role == 1)
        });
        let OppTeamVC = _filter(otherLineupData.lineup, (obj) => {
            return obj.player_role && (obj.player_role == 2)
        });
        OppTeamCVC = OppTeamCVC.concat(OppTeamVC)
        for (var obj of OppTeamCVC) {
            oppCVCtotalScore = parseFloat(oppCVCtotalScore) + parseFloat(obj.score || 0)
            oppCVCtotalScore = parseFloat(oppCVCtotalScore).toFixed(2)
        }
        this.setState({
            UTeamCVC: UTeamCVC,
            OppTeamCVC: OppTeamCVC,
            youCVCtotalScore: youCVCtotalScore,
            oppCVCtotalScore: oppCVCtotalScore
        })
    }

    commonPlayer = (you, opp, isYou) => {
        let tempCList = [];
        let totalScore = 0;
        for (var youData of you.lineup) {
            for (var otherData of opp.lineup) {
                if (youData.player_role != 1 && youData.player_role != 2 && otherData.player_role != 1 && otherData.player_role != 2 && youData.stock_id == otherData.stock_id && youData.type == otherData.type) {
                    totalScore = parseFloat(parseFloat(youData.score || 0) + parseFloat(totalScore || 0)).toFixed(2);
                    tempCList.push(youData)
                }
            }
        }
        this.setState({
            commonScore: totalScore
        })
        this.AllPlayerList(you, tempCList, isYou)
    }

    AllPlayerList = (you, tempCList, isYou) => {
        let tempPList = [];
        if (tempCList.length > 0) {
            for (let youData of you.lineup) {
                for (var obj of tempCList) {
                    if (youData.player_role != 1 && youData.player_role != 2 && obj.player_role != 1 && obj.player_role != 2 && (youData.stock_id != obj.stock_id || youData.type != obj.type)) {
                        if (!tempPList.includes(youData) && !tempCList.includes(youData)) {
                            tempPList.push(youData)
                        }
                    }
                }
            }
        }
        else {
            for (let youData of you.lineup) {
                if (youData.player_role != 1 && youData.player_role != 2) {
                    if (!tempPList.includes(youData) && !tempCList.includes(youData)) {
                        tempPList.push(youData)
                    }
                }
            }
        }
        if (isYou) {
            this.setState({
                youCLD: tempCList,
                youAllLD: tempPList,
            })
        }
        else {
            this.setState({
                oppCLD: tempCList,
                oppAllLD: tempPList
            })
        }
    }

    /**
     * @description This function is used to open player lineup page with formatted URL data
     * @param teamItem Team item
     * @see FieldView
    */
    openLineup = (teamItem, data) => {
        this.setState({
            SelectedLineup: teamItem.lineup_master_contest_id,
            UserName: teamItem.user_name || '',
            lineupData: data
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
                                {this.state.status == 1 ? AL.STOCK_OPP_CAP_VC_LEAD : AL.STOCK_OPP_CAP_VC_WON} <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
                            </>
                            :
                            <>
                                {this.state.status == 1 ? AL.STOCK_OPP_CAP_LEAD : AL.STOCK_OPP_CAP_WON} <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
                            </>
                        }
                        </>
                        :
                        <>
                        {
                            isVCEnable ?
                                <>
                                {this.state.status == 1 ? AL.STOCK_YOUR_CAP_VC__LEAD : AL.STOCK_YOUR_CAP_VC__WON} <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
                                </>
                            :
                                <>
                                {this.state.status == 1 ? AL.STOCK_YOUR_CAP_LEAD : AL.STOCK_YOUR_CAP_WON} <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
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

    render() {
        let catID = this.state.rootItem ? (this.state.rootItem.category_id || '') : '';
        const HeaderOption = {
            back: true,
            // fixture: false,
            hideShadow: true,
            status: this.state.status,
            title: '',//AL.COMPARE_TEAMS,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            screentitle: (this.state.rootItem && this.state.rootItem.collection_name && this.state.rootItem.collection_name != '' ? this.state.rootItem.collection_name : catID.toString() === "1" ? AL.DAILY : catID.toString() === "2" ? AL.WEEKLY : AL.MONTHLY) + ' ' + AL.STOCK_FANTASY,
            minileague:true,
            leagueDate: {
                scheduled_date: this.state.rootItem.scheduled_date || this.state.rootItem.season_scheduled_date || '',
                end_date: this.state.rootItem.end_date || '', //catID.toString() === "1" ? '' : rootItem.end_date,
                game_starts_in: this.state.rootItem.game_starts_in || '',
                catID: catID
            },
            showleagueTime: true
        }

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
            StockSettingValue,
            showPlayerCard,
            playerDetails,
            status
        } = this.state;
        let youTotSPF = parseFloat(youDataScore || 0)
        let oppTotSPF = parseFloat(oppDataScore || 0)
        let OppLeads = oppTotSPF > youTotSPF ? true : false;
        let youScoreLen = youDataScore.length;
        let oppScoreLen = oppDataScore.length;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed team-comparison stk-team-comp">
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
                                    {AL.POINTS_UPDATED_AT} <MomentDateComponent data={{ date: rootItem.score_updated_date, format: "hh:mm a" }} />
                                </div>
                            }
                            <div className="team-comparison-header">
                                <div className="team-names-wrap">
                                    <div className="team-info-sec">
                                        <div className="team-name opp-team-name">{oppData.user_name}</div>
                                        <div className="post-wrap-inner">#{oppData.game_rank} | {oppData.team_short_name ? oppData.team_short_name : oppData.team_name}</div>
                                    </div>
                                    <div className="team-info-sec">
                                        <div style={{ width: '100%', maxWidth: '140px' }} className={"team-points-sec" + ((oppScoreLen > 5 || youScoreLen > 5) ? ' team-points-sec-xsm' : (oppScoreLen > 3 || youScoreLen > 3) ? ' team-points-sec-sm' : '')}>
                                            {/* <span
                                                onClick={() => this.setState({ selectedLineup: (otherLineupData.team_info || '').lineup_master_id, clickTeamScore: oppData.total_score, showScoreV: true })} */}
                                            <span
                                               onClick={() => this.openLineup(oppData, otherLineupData)} 
                                                style={{ width: '50%', textDecorationLine: 'underline', cursor: 'pointer' }}>{oppDataScore}</span>
                                            <img src={Images.ZIG_ZAG_LINE} alt="" />
                                            <span
                                                onClick={() => this.openLineup(youData, youLineupData)} style={{ width: '50%', textDecorationLine: 'underline', cursor: 'pointer' }}>{youDataScore}</span> 
                                            {/* <span
                                                onClick={() => this.setState({ selectedLineup: (youLineupData.team_info || '').lineup_master_id, clickTeamScore: youDataScore, showScoreV: true })}
                                                style={{ width: '50%', textDecorationLine: 'underline', cursor: 'pointer' }}>{youDataScore}</span> */}
                                        </div>
                                        <div className="post-wrap-inner">{AL.TOTAL_POINTS}</div>
                                    </div>
                                    <div className="team-info-sec">
                                        <div className="team-name you-team-name">{AL.You}
                                        </div>
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
                                                    placeholder={'#' + youData.game_rank + " | " + youData.team_name}
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
                                </div>
                                <div style={{ width: 'fit-content', padding: '4px 15px 2px', maxWidth: '300px' }} className="circular-main-wrap" onClick={() => this.openLineup(oppData, otherLineupData)}>
                                    <span style={{ textDecoration: 'none' }}>
                                        <img style={{ width: 22, objectFit: 'contain', marginRight: '6px', position: 'relative', top: '-1px' }} src={DARK_THEME_ENABLE ? Images.search_light : Images.search_dark} alt='' /> {AL.SEE} <span style={{ textDecoration: 'underline' }}>{oppData.user_name}</span> {AL.TEAM_PREVIEW.replace(AL.Team, AL.PORTFOLIO)}
                                    </span>
                                </div>
                            </div>
                            <div className="team-comparison-body">
                                {
                                    OppLeads ?
                                        <div className="score-card-sec">
                                            {status == 1 ? AL.OPP_LEAD_BY : AL.OPP_WON_BY} <span>{parseFloat(oppTotSPF - youTotSPF).toFixed(2)} {AL.PTS1}</span>
                                        </div>
                                        :
                                        <div className="score-card-sec">
                                            {status == 1 ? AL.YOU_LEAD_BY : AL.YOU_WON_BY} <span>{parseFloat(youTotSPF - oppTotSPF).toFixed(2)} {AL.PTS1}</span>
                                        </div>
                                }
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
                                                <div className="comparison-sec">
                                                    <div className="lead-sec">
                                                        {this.showCVCLeadText(oppCVCtotalScore, youCVCtotalScore)}
                                                    </div>
                                                    <div className="players-selected">
                                                        <div className="players-selected-col">
                                                            {
                                                                OppTeamCVC && OppTeamCVC.length > 0 &&
                                                                _Map(OppTeamCVC, (item, idx) => {
                                                                    return (
                                                                        <div className="opp-player-sec" key={'oppcvc' + idx}>
                                                                            <span style={{ padding: '5px' }} className="img-wrap" onClick={(e)=>this.PlayerCardShow(e, item)}><img style={{ top: 0, transform: 'none', objectFit: 'contain', width: '100%', height: '100%' }} src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" /></span>
                                                                            <span className="c-vc-sec">{item.player_role == 1 ? 'c' : item.player_role == 2 ? 's' : ''}</span>
                                                                            <div className="player-nm" onClick={(e)=>this.PlayerCardShow(e, item)}><span>{item.stock_name}</span> <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} /></div>
                                                                            <div className="team-pos">{item.type == 1 ? AL.BUY : AL.SELL}</div>
                                                                            <div className={"score-diff-inner-sec stk" + ((item.score.includes('-') || item.score <= 0) ? ' danger-text' : '')}>
                                                                                <span>{item.score}</span>
                                                                            </div>
                                                                        </div>
                                                                    )
                                                                })
                                                            }
                                                        </div>
                                                        <div className="players-selected-col">
                                                            {
                                                                UTeamCVC && UTeamCVC.length > 0 &&
                                                                _Map(UTeamCVC, (item, idx) => {
                                                                    return (
                                                                        <div className="you-player-sec" key={'ucvc' + idx}>
                                                                            <span style={{ padding: '5px' }} className="img-wrap" onClick={(e)=>this.PlayerCardShow(e, item)}><img style={{ top: 0, transform: 'none', objectFit: 'contain', width: '100%', height: '100%' }} src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" /></span>
                                                                            <span className="c-vc-sec">{item.player_role == 1 ? 'c' : item.player_role == 2 ? 's' : ''}</span>
                                                                            <div className="player-nm" onClick={(e)=>this.PlayerCardShow(e, item)}><span>{item.stock_name}</span> <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} /></div>
                                                                            <div className="team-pos">{item.type == 1 ? AL.BUY : AL.SELL}</div>
                                                                            <div className={"score-diff-inner-sec stk" + ((item.score.includes('-') || item.score <= 0) ? ' danger-text' : '')}><span>{item.score}</span></div>
                                                                        </div>
                                                                    )
                                                                })
                                                            }
                                                        </div>
                                                    </div>
                                                </div>
                                                {
                                                    oppAllLD && oppAllLD.length > 0 && youAllLD && youAllLD.length > 0 &&
                                                    <div className={"comparison-sec" + (youCLD && youCLD.length == 0 && oppCLD && oppCLD.length == 0 ? ' no-bor-bot' : '')}>
                                                        <div className="lead-sec">{AL.ALL_STOCKS}</div>
                                                        <div className="players-selected">
                                                            <div className="players-selected-col">
                                                                {
                                                                    oppAllLD && oppAllLD.length > 0 &&
                                                                    _Map(oppAllLD, (item, idx) => {
                                                                        return (
                                                                            <div className="opp-player-sec" key={'oppall' + idx}>
                                                                                <span style={{ padding: '5px' }} className="img-wrap" onClick={(e)=>this.PlayerCardShow(e, item)}><img style={{ top: 0, transform: 'none', objectFit: 'contain', width: '100%', height: '100%' }} src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" /></span>
                                                                                <div className="player-nm" onClick={(e)=>this.PlayerCardShow(e, item)}><span>{item.stock_name}</span> <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} /></div>
                                                                                <div className="team-pos">{item.type == 1 ? AL.BUY : AL.SELL}</div>
                                                                                <div className={"score-diff-inner-sec stk"+ ((item.score.includes('-') || item.score <= 0) ? ' danger-text' : '')}><span>{item.score}</span></div>
                                                                            </div>
                                                                        )
                                                                    })
                                                                }
                                                            </div>
                                                            <div className="players-selected-col">
                                                                {
                                                                    youAllLD && youAllLD.length > 0 &&
                                                                    _Map(youAllLD, (item, idx) => {
                                                                        return (
                                                                            <div className="you-player-sec" key={'all' + idx}>
                                                                                <span style={{ padding: '5px' }} className="img-wrap" onClick={(e)=>this.PlayerCardShow(e, item)}><img style={{ top: 0, transform: 'none', objectFit: 'contain', width: '100%', height: '100%' }} src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" /></span>
                                                                                <div className="player-nm" onClick={(e)=>this.PlayerCardShow(e, item)}><span>{item.stock_name}</span> <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} /></div>
                                                                                <div className="team-pos">{item.type == 1 ? AL.BUY : AL.SELL}</div>
                                                                                <div className={"score-diff-inner-sec stk" + ((item.score.includes('-') || item.score <= 0) ? ' danger-text' : '')}><span>{item.score}</span></div>
                                                                            </div>
                                                                        )
                                                                    })
                                                                }
                                                            </div>
                                                        </div>
                                                    </div>
                                                }
                                                {
                                                    youCLD && youCLD.length > 0 && oppCLD && oppCLD.length > 0 &&
                                                    <div className={"comparison-sec common-com-sec no-bor-bot" + (oppAllLD && oppAllLD.length == 0 && youAllLD && youAllLD.length == 0 ? ' no-bor-bot' : '')}>
                                                        <div className="lead-sec">
                                                            {AL.COMMON_STOCKS}
                                                            <span> {parseFloat(commonScore || 0).toFixed(2)} {AL.PTS}</span>
                                                        </div>
                                                        <div className="players-selected">
                                                            <div className="players-selected-col">
                                                                {
                                                                    oppCLD && oppCLD.length > 0 &&
                                                                    _Map(oppCLD, (item, idx) => {
                                                                        return (
                                                                            <div className="opp-player-sec" key={'oppcld' + idx}>
                                                                                <span style={{ padding: '5px' }} className="img-wrap" onClick={(e)=>this.PlayerCardShow(e, item)}><img style={{ top: 0, transform: 'none', objectFit: 'contain', width: '100%', height: '100%' }} src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" /></span>
                                                                                <div className="player-nm" onClick={(e)=>this.PlayerCardShow(e, item)}><span>{item.stock_name}</span> <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} /></div>
                                                                                <div className="team-pos">{item.type == 1 ? AL.BUY : AL.SELL}</div>
                                                                                <div className={"score-diff-inner-sec" + ((item.score.includes('-') || item.score <= 0) ? ' danger-text' : '')}><span>{item.score}</span></div>
                                                                            </div>
                                                                        )
                                                                    })
                                                                }
                                                            </div>
                                                            <div className="players-selected-col">
                                                                {
                                                                    youCLD && youCLD.length > 0 &&
                                                                    _Map(youCLD, (item, idx) => {
                                                                        return (
                                                                            <div className="you-player-sec" key={'ucld' + idx}>
                                                                                <span style={{ padding: '5px' }} className="img-wrap" onClick={(e)=>this.PlayerCardShow(e, item)}><img style={{ top: 0, transform: 'none', objectFit: 'contain', width: '100%', height: '100%' }} src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" /></span>
                                                                                <div className="player-nm" onClick={(e)=>this.PlayerCardShow(e, item)}><span>{item.stock_name}</span> <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} /></div>
                                                                                <div className="team-pos">{item.type == 1 ? AL.BUY : AL.SELL}</div>
                                                                            </div>
                                                                        )
                                                                    })
                                                                }
                                                            </div>
                                                        </div>
                                                    </div>
                                                }
                                            </>
                                }


                            </div>
                        </div>
                        {
                            this.state.showFieldV && <StockTeamPreview total_score={lineupData ? (lineupData.team_info.total_score || 0) : 0} status={this.state.status} userName={this.state.UserName} isFrom={'point'} CollectionData={this.state.rootItem}
                                openTeam={lineupData ? lineupData.lineup : ''} isViewAllShown={this.state.showFieldV} onViewAllHide={() => this.setState({ showFieldV: false })}  StockSettingValue={this.state.StockSettingValue}  />
                        }
                        {
                            this.state.showScoreV && <StockScoreCalculation total_score={this.state.clickTeamScore} selectedLineup={this.state.selectedLineup} CollectionData={this.state.rootItem} isViewAllShown={this.state.showScoreV} onViewAllHide={() => this.setState({ showScoreV: false })} />
                        }
                        {
                            showPlayerCard &&
                            <Suspense fallback={<div />} >
                                <StockPlayerCard
                                    mShow={showPlayerCard}
                                    mHide={this.PlayerCardHide}
                                    isFrom={'stockitem'}
                                    // isPreview={isPreview}
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