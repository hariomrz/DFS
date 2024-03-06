import React, {lazy, Suspense} from 'react';
import * as AL from "../../helper/AppLabels";
import { CommonLabels } from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import Skeleton from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { NoDataView } from '../../Component/CustomComponent';
import { getLineupWithTeamCompare , getLineupMasterData} from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { AppSelectedSport,DARK_THEME_ENABLE, SELECTED_GAMET } from '../../helper/Constants';
import * as WSC from "../../WSHelper/WSConstants";
import { Utilities, _filter, _Map, _isEmpty } from '../../Utilities/Utilities';
import FieldView from "./../FieldView";
import { SportsIDs } from '../../JsonFiles';
const ReactSelectDD = lazy(()=>import('../../Component/CustomComponent/ReactSelectDD'));

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

export default class TeamComparison extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            userRankList: [],
            oppData: [],
            youData: [],
            youDataScore: 0,
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
            oppBooster:{},
            youBooster:{},
            boosterExpand:false,
            youBench: [],
            oppBench: [],
            rootItem: '',
            c_vc: {}
        }
    }
    componentDidMount() {
        this.getLineupComaprisonData(this.state.youData, this.state.oppData);
        let tmpArry = []
        for (var obj of this.state.userRankList) {
            tmpArry.push({
                "value" : obj.lineup_master_contest_id,
                "lineup_master_contest_id" : obj.lineup_master_contest_id,
                "label" : '#' + obj.game_rank + ' | ' + obj.team_name,
                "total_score": obj.total_score
            })
        }
        this.setState({
            teamOptions: tmpArry,
            isCMounted: true
        })
    }

    componentWillMount() {
        Utilities.setScreenName('leaderboard')
        this.setLocationStatedata();       
    }    

    setLocationStatedata=()=>{
        if(this.props && this.props.location && this.props.location.state){
            const {userRankList,oppData,youData,status, selectedContest,rootItem} = this.props.location.state;
            this.setState({
                userRankList: userRankList,
                oppData: oppData,
                youData: youData,
                youDataScore: youData.total_score,
                status: status || 2,
                selectedContest: selectedContest,
                rootItem: rootItem,
            })
        }
    }

    getLineupComaprisonData = (YOU, OPP) => {
        const { rootItem } = this.state
        this.setState({
            isLoading: true
        })
        let param = {
            "u_lmc_id": YOU.lineup_master_contest_id,
            "o_lmc_id": OPP.lineup_master_contest_id,
            "sports_id": AppSelectedSport,
            "collection_master_id": rootItem.collection_master_id,
        }
        const dataModified = (obj, position) => {
            const { lineup, bench, booster, ..._obj } = obj;
            let _position = {}
            _Map(position, item => {
                _position[item.position] = item.position_display_name
                return item
            })
            return {
                all_position: position,
                pos_list: _position,
                bench: bench,
                lineup: lineup,
                team_info: {..._obj, booster, is_tour_game: AppSelectedSport == SportsIDs.MOTORSPORTS || AppSelectedSport == SportsIDs.tennis ? 1 : 0}
            }
        }

        getLineupWithTeamCompare(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let data = responseJson.data;
                let you = dataModified(data.you, data.position)
                let oponent = dataModified(data.oponent, data.position)
                this.setState({
                    c_vc: data.c_vc,
                    youLineupData: you,
                    otherLineupData: oponent,

                    oppBooster:oponent.team_info,
                    youBooster:you.team_info,
                    youBench: you.bench || [],
                    oppBench: oponent.bench || []
                }, () => {
                    this.CVCPlayer()
                    this.commonPlayer(you, oponent, true)
                    this.commonPlayer(oponent, you, false)
                    this.setState({
                        isLoading: false
                    })
                })
            }
        })
    }

    // commonPlayerYouTeam = (you, opp) => {
    //     this.commonPlayer(you, opp, true)
    // }

    // commonPlayerOppTeam = (you, opp) => {
    //     this.commonPlayer(you, opp, false)
    // }

    CVCPlayer = () => {
        const { youLineupData, otherLineupData } = this.state;
        let youCVCtotalScore = 0;
        let oppCVCtotalScore = 0;
        let UTeamCVC = _filter(youLineupData.lineup, (obj) => {
            return obj.captain && (obj.captain == 1)
        });
        let UTeamVC = _filter(youLineupData.lineup, (obj) => {
            return obj.captain && (obj.captain == 2)
        });
        UTeamCVC = UTeamCVC.concat(UTeamVC)
        for (var obj of UTeamCVC) {
            youCVCtotalScore = parseFloat(youCVCtotalScore) + parseFloat(obj.score || 0);
            youCVCtotalScore = parseFloat(youCVCtotalScore).toFixed(2)
        }
        let OppTeamCVC = _filter(otherLineupData.lineup, (obj) => {
            return obj.captain && (obj.captain == 1)
        });
        let OppTeamVC = _filter(otherLineupData.lineup, (obj) => {
            return obj.captain && (obj.captain == 2)
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
                if (youData.captain != 1 && youData.captain != 2 && otherData.captain != 1 && otherData.captain != 2 && youData.player_id == otherData.player_id) {
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
        if(tempCList.length > 0){
            for (var youData of you.lineup) {
                for (var obj of tempCList) {
                    if (youData.captain != 1 && youData.captain != 2 && obj.captain != 1 && obj.captain != 2 && youData.player_id != obj.player_id) {
                        if(!tempPList.includes(youData) && !tempCList.includes(youData)){
                            tempPList.push(youData)
                        }
                    }
                }
            }
        }
        else{
            for (var youData of you.lineup) {
                if (youData.captain != 1 && youData.captain != 2) {
                    if(!tempPList.includes(youData) && !tempCList.includes(youData)){
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
            selectedTeam: selectedOption ,
            youDataScore: selectedOption.total_score 
        },()=>{
            this.getLineupComaprisonData(selectedOption, this.state.oppData);
        })
    }

    goBack() {
        this.props.history.goBack();
    }

    showCVCLeadText=(oppScore,youScore, is_tour_game = false)=>{
        const {c_vc} = this.state; 
        let OS = parseFloat(oppScore || 0);
        let YS = parseFloat(youScore || 0);
        let showscore = OS > YS ? (OS - YS) : (YS - OS)
        



        let caption_text = AL.YOU_CVC_LEAD_BY.replace(" & VC", "")
        let viceCaption_text = AL.YOU_CVC_LEAD_BY.replace("C & ", "")

        let caption_text_opp = AL.OPP_CVC_LEAD_BY.replace(" & VC", "")
        let viceCaption_text_opp = AL.OPP_CVC_LEAD_BY.replace("C & ", "")
       
        return(
            <>
                {
                    OS > YS ?
                        <>
                            {is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS ? CommonLabels.OPP_T_LEAD_BY : (Number(c_vc.c_point) > 0 && Number(c_vc.vc_point) > 0 ) ?  AL.YOU_CVC_LEAD_BY : Number(c_vc.c_point) > 0 ? caption_text_opp : Number(c_vc.vc_point) > 0 ? viceCaption_text_opp : '' } <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
                        </>
                        :
                        <>
                            {is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS ? CommonLabels.YOU_T_LEAD_BY : (Number(c_vc.c_point) > 0 && Number(c_vc.vc_point) > 0 ) ?  AL.YOU_CVC_LEAD_BY : Number(c_vc.c_point) > 0 ? caption_text : Number(c_vc.vc_point) > 0 ? viceCaption_text : '' } <span>{parseFloat(showscore || 0).toFixed(2)} {AL.PTS1}</span>
                        </>
                }
            </>
        )
    }
    showBooster = () => {
        if (!this.state.boosterExpand) {
            this.setState({boosterExpand: true})
        }
        else{
            this.setState({boosterExpand: false})
  
        }
    }


    




    render() {
        const HeaderOption = {
            back: true,
            fixture: false,
            hideShadow: true,
            status: this.state.status,
            title: AL.COMPARE_TEAMS,
            isPrimary: DARK_THEME_ENABLE ? false : true,
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
            selectedContest,
            boosterExpand,
            youBench,
            oppBench,
            c_vc
        } = this.state;
        let youTotSPF = parseFloat(youDataScore || 0)
        let oppTotSPF = parseFloat(oppData.total_score || 0)
        let OppLeads = oppTotSPF > youTotSPF ? true : false;
        let youScoreLen = youDataScore.length;
        let oppScoreLen = oppData.total_score.length;
        let boosteritem= this.state.youBooster && this.state.youBooster.booster ? this.state.youBooster.booster:[]
        let boosteritemOponent= this.state.oppBooster && this.state.oppBooster.booster ? this.state.oppBooster.booster:[]
        let is_tour_game = AppSelectedSport == SportsIDs.MOTORSPORTS || AppSelectedSport == SportsIDs.tennis
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed team-comparison">
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
                            <div className="team-comparison-header">
                                <div className="team-names-wrap">
                                    <div className="team-info-sec">
                                        <div className="team-name opp-team-name">{oppData.user_name}</div>
                                        <div className="post-wrap-inner">#{oppData.game_rank} | {oppData.team_name}</div>
                                    </div>
                                    <div className="team-info-sec">
                                        <div className={"team-points-sec" + ((oppScoreLen > 5 || youScoreLen > 5) ? ' team-points-sec-xsm' : (oppScoreLen > 3 || youScoreLen > 3) ? ' team-points-sec-sm' : '')}>
                                            <span>{oppData.total_score}</span>
                                            <img src={Images.ZIG_ZAG_LINE} alt="" />
                                            <span>{youDataScore}</span>
                                        </div>
                                        <div className="post-wrap-inner">{AL.TOTAL_POINTS}</div>
                                    </div>
                                    <div className="team-info-sec">
                                        <div className="team-name you-team-name">YOU
                                        </div>
                                        <div className={"post-wrap-inner you-post-wrap-inner" + (teamOptions.length == 1 ? ' no-dropdown' :'')}>
                                                {
                                                    teamOptions.length > 1 &&
                                                    <i className="icon-arrow-down"></i>
                                                }
                                                    {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
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
                                                        /></Suspense>}
                                        </div>
                                    </div>
                                </div>
                                <div className="circular-main-wrap" onClick={() => this.openLineup(oppData, otherLineupData)}>
                                    <span>
                                        <i className="icon-ground"></i> {AL.SEE} <span>{oppData.user_name}</span> {AL.ON_FIELDVIEW}
                                    </span> 
                                </div>
                            </div>
                            <div className="team-comparison-body">
                                {
                                    OppLeads ?
                                        <div className="score-card-sec">
                                            {AL.OPP_LEAD_BY} <span>{parseFloat(oppTotSPF - youTotSPF).toFixed(2)} {AL.PTS1}</span>
                                        </div>
                                        :
                                        <div className="score-card-sec">
                                            {AL.YOU_LEAD_BY} <span>{parseFloat(youTotSPF - oppTotSPF).toFixed(2)} {AL.PTS1}</span>
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
                                                {
                                                    (Number(c_vc.c_point) > 0 || Number(c_vc.vc_point) > 0) &&
                                                    <div className="comparison-sec">
                                                        <div className="lead-sec">
                                                            {this.showCVCLeadText(oppCVCtotalScore,youCVCtotalScore, is_tour_game)}                                                       
                                                        </div>
                                                        <div className="players-selected">
                                                            <div className="players-selected-col">
                                                                {
                                                                    OppTeamCVC && OppTeamCVC.length > 0 &&
                                                                    _Map(OppTeamCVC, (item, idx) => {
                                                                        return (
                                                                            <div className="opp-player-sec" key={'oppcvc' + idx}>
                                                                                <span className="img-wrap"><img src={Utilities.playerJersyURL(item.jersey)} alt="" /></span>
                                                                                <span className="c-vc-sec">{item.captain == 1 ? (is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS ? 'T': 'c') : 'vc'}</span>
                                                                                <div className="player-nm">{item.full_name}</div>
                                                                                <div className="team-pos">{item.team_abbr || item.team_abbreviation}-{item.position_name || item.position}</div>
                                                                                <div className="score-diff-inner-sec"><span>{item.score}</span></div>
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
                                                                                <span className="img-wrap"><img src={Utilities.playerJersyURL(item.jersey)} alt="" /></span>
                                                                                <span className="c-vc-sec">{item.captain == 1 ? (is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS ? 'T': 'c') : 'vc'}</span>
                                                                                <div className="player-nm">{item.full_name}</div>
                                                                                <div className="team-pos">{item.team_abbr || item.team_abbreviation}-{item.position_name || item.position}</div>
                                                                                <div className="score-diff-inner-sec"><span>{item.score}</span></div>
                                                                            </div>
                                                                        )
                                                                    })
                                                                }
                                                            </div>
                                                        </div>
                                                    </div>
                                                }

                                                {
                                                    oppAllLD && oppAllLD.length > 0 && youAllLD && youAllLD.length > 0 &&
                                                    <div className={"comparison-sec" + (youCLD && youCLD.length == 0 && oppCLD && oppCLD.length == 0 ? ' no-bor-bot' : '')}>
                                                        <div className="lead-sec">{AL.ALL_PLAYERS}</div>
                                                        <div className="players-selected">
                                                            <div className="players-selected-col">
                                                                {
                                                                    oppAllLD && oppAllLD.length > 0 &&
                                                                    _Map(oppAllLD, (item, idx) => {
                                                                        return (
                                                                            <div className="opp-player-sec" key={'oppall' + idx}>
                                                                                <span className="img-wrap"><img src={Utilities.playerJersyURL(item.jersey)} alt="" /></span>
                                                                                <div className="player-nm">{item.full_name}</div>
                                                                                <div className="team-pos">{item.team_abbr || item.team_abbreviation}-{item.position_name || item.position}</div>
                                                                                <div className="score-diff-inner-sec"><span>{item.score}</span></div>
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
                                                                                <span className="img-wrap"><img src={Utilities.playerJersyURL(item.jersey)} alt="" /></span>
                                                                                <div className="player-nm">{item.full_name}</div>
                                                                                <div className="team-pos">{item.team_abbr || item.team_abbreviation}-{item.position_name || item.position}</div>
                                                                                <div className="score-diff-inner-sec"><span>{item.score}</span></div>
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
                                                            {AL.COMMON_PLAYERS} 
                                                            <span> {parseFloat(commonScore || 0).toFixed(2)} {AL.PTS}</span>
                                                        </div>
                                                        <div className="players-selected">
                                                            <div className="players-selected-col">
                                                                {
                                                                    oppCLD && oppCLD.length > 0 &&
                                                                    _Map(oppCLD, (item, idx) => {
                                                                        return (
                                                                            <div className="opp-player-sec" key={'oppcld' + idx}>
                                                                                <span className="img-wrap"><img src={Utilities.playerJersyURL(item.jersey)} alt="" /></span>
                                                                                <div className="player-nm">{item.full_name}</div>
                                                                                <div className="team-pos">{item.team_abbr || item.team_abbreviation}-{item.position_name || item.position}</div>
                                                                                <div className="score-diff-inner-sec"><span>{item.score}</span></div>
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
                                                                                <span className="img-wrap"><img src={Utilities.playerJersyURL(item.jersey)} alt="" /></span>
                                                                                <div className="player-nm">{item.full_name}</div>
                                                                                <div className="team-pos">{item.team_abbr || item.team_abbreviation}-{item.position_name || item.position}</div>
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
                                   (!_isEmpty(boosteritem) || !_isEmpty(boosteritemOponent)) &&
                                    <div className={'footer-layout' + (boosterExpand ? ' expand-height' : '')}>
                                        <div className={'footer-booster-strip'}>
                                            <div onClick={() => this.showBooster()} className={'circualr-top-bar' + (boosterExpand ? ' expand-bottom-margin' : '')}>
                                                <img style={{ height: 8, width: 20 }} src={Images.BOOSTER_DASH} alt=''></img>

                                            </div>
                                        </div>
                                        <div onClick={() => this.showBooster()} className="applied-boosters">
                                            <div className="text"> {"Applied Booster"}</div>
                                        </div>
                                        {
                                            boosterExpand &&
                                            <div className='seprator'></div>

                                        }
                                {
                                    boosterExpand &&
                                    <div className="players-selected">
                                        <div className="players-selected-col">
                                            {
                                                <div className="opp-player-sec">
                                                    <span className="img-wrap"><img src={boosteritemOponent != '' && boosteritemOponent.image_name && boosteritemOponent.image_name != undefined ? Utilities.getBoosterLogo(boosteritemOponent.image_name) : Images.BOOSTER_STRAIGHT} alt="" /></span>
                                                    <div className="player-nm">{boosteritemOponent != '' && boosteritemOponent.name && boosteritemOponent.name != undefined ? boosteritemOponent.name : 'NA'}</div>
                                                    <div className="team-pos">{boosteritemOponent != '' && boosteritemOponent.position && boosteritemOponent.position != undefined ? boosteritemOponent.position : 'NA'}</div>
                                                    <div className="score-diff-inner-sec"><span>{boosteritemOponent != '' && boosteritemOponent.score && boosteritemOponent.score != undefined ? parseFloat(boosteritemOponent.score).toFixed(1) : 'NA'}</span></div>
                                                </div>
                                            }
                                        </div>
                                        <div className="players-selected-col">
                                            {
                                                <div className="you-player-sec">
                                                    <span className="img-wrap"><img src={boosteritem != '' && boosteritem.image_name && boosteritem.image_name != undefined ? Utilities.getBoosterLogo(boosteritem.image_name) : Images.BOOSTER_STRAIGHT} alt="" /></span>
                                                    <div className="player-nm">{boosteritem != '' && boosteritem.name && boosteritem.name != undefined ? boosteritem.name : 'NA'}</div>
                                                    <div className="team-pos">{boosteritem != '' && boosteritem.position && boosteritem.position != undefined ? boosteritem.position : 'NA'}</div>
                                                    <div className="score-diff-inner-sec"><span>{boosteritem != '' && boosteritem.score && boosteritem.score != undefined ? parseFloat(boosteritem.score).toFixed(1) : 'NA'}</span></div>


                                                </div>
                                            }
                                        </div>
                                    </div>
                                }

                                    </div>

                                }
                        {
                             !_isEmpty(lineupData) && this.state.SelectedLineup &&
                            <FieldView
                                SelectedLineup={lineupData ? lineupData.lineup : ''}
                                MasterData={lineupData || ''}
                                isFrom={'rank-view'}
                                isFromTC={true}
                                team_name={oppData.team_name ? oppData.team_name : ''}
                                showFieldV={this.state.showFieldV}
                                userName={this.state.UserName}
                                hideFieldV={this.hideFieldV.bind(this)}
                                isFromTeamComp={true}
                                league_id={selectedContest.league_id}
                                benchPlayer={this.state.oppBench}
                                isReverseF= {selectedContest.is_reverse == 1 || false}
                                isSecIn={selectedContest.is_2nd_inning == 1 || false}
                                lData={this.state.rootItem}
                                fixtureData={this.state.rootItem}
                                // isFromCompare= {'isFromCompare'}
                                updateTeamDetails={new Date().valueOf()}
                                allPosition={lineupData.pos_list}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}