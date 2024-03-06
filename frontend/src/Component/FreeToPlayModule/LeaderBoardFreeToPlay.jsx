import React, { Fragment } from 'react';
import { Row, Col, Alert } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map, _isUndefined } from '../../Utilities/Utilities';
import { getMiniLeagueLeaderBoard, getUserMiniLeagueLeaderBoard } from '../../WSHelper/WSCallings';
import { NoDataView } from '../../Component/CustomComponent';
import { AppSelectedSport,DARK_THEME_ENABLE } from '../../helper/Constants';
import Images from '../../components/images';
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';

import * as AppLabels from "../../helper/AppLabels";
import * as NC from "../../WSHelper/WSConstants";
import LeagueDetails from './LeagueDetails';
import LBAnimation from '../../Component/FantasyRefLeaderboard/LeaderboardAnimation';

/**
  * @description This is the header of other user rank list.
  * @return UI components
  * @param context This is the instance of this component
*/
const ListHeader = ({ context }) => {
    return (
        <div className="ranking-list user-list-header mini-leage-leaderbord" style={Object.keys(context.state.userRankList).length === 0 ? { marginTop: 0 } : {}}>
            <div className="display-table-cell text-center">
                <div className="list-header-text list-heder-mini-league">{AppLabels.RANK}</div>
            </div>
            <div className="display-table-cell pl-1">
                <div className="list-header-text list-heder-mini-league left pl6">{AppLabels.NAME}</div>
            </div>
            <div className="display-table-cell">
                <div className="list-header-text list-heder-mini-league text-right mr10">{AppLabels.POINTS}</div>
            </div>
        </div>
    )
}

const Shimmer = () => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div className="ranking-list shimmer margin-2p">
                <div className="display-table-cell text-center">
                    <div className="rank">--</div>
                    <div className="rank-heading">{AppLabels.RANK}</div>
                </div>
                <div className="display-table-cell pl-1 pointer-cursor">
                    <figure className="user-img shimmer">
                        <Skeleton circle={true} width={40} height={40} />
                    </figure>
                    <div className="user-name-container shimmer">
                        <Skeleton width={'80%'} height={8} />
                        <Skeleton width={'40%'} height={5} />
                    </div>
                </div>
                <div className="display-table-cell">
                    <div className="points">--</div>
                </div>
            </div>
        </SkeletonTheme>
    )
}

export default class LeaderBoardFreeToPlay extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoaderShow: false,
            isLoadMoreLoaderShow: false,
            hasMore: true,
            leaderboardList: [],
            userRankList: '',
            ShimmerList: [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            status: '',
            contestItem: '',
            contestId: '',
            LobyyData: !_isUndefined(props.location.state) ? props.location.state.LobyyData : [],
            MiniLeagueSponser: !_isUndefined(props.location.state) ? props.location.state.MiniLeagueSponser : '',
            MiniLeagueListItem: !_isUndefined(props.location.state) ? props.location.state.MiniLeagueListItem : '',
            rootItem: '',
            mfileURL: '',
            downloadFail: false,
            isExpanded: false,
            isExpandedWithDelay: false,
            pageNo: 1,
            userData: '',
            showContestDetail: false,
            AllLineUPData: {},
            SelectedLineup: '',
            MiniLeagueData:'',

            showFieldV: false,
            isRefresh: false,
            isMiniLeaguePrize: true
        }
        this.headerRef = React.createRef();
    }


    componentDidMount() {
        if (this.props.location.state) {
            this.getLeaderboard();
            this.getUserRank()
            if (this.headerRef) {
                this.headerRef.GetHeaderProps('', {}, {}, this.state.rootItem);
            }
        }
    }

    onLoadMore() {
        if (!this.state.isLoaderShow && this.state.hasMore) {
            this.setState({ hasMore: false })
            this.getLeaderboard()
        }
    }

    /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
    handleRefresh = () => {
        if (!this.state.isLoaderShow) {
            this.setState({ hasMore: false, pageNo: 1, isRefresh: true }, () => {
                this.hideFieldV();
                this.getLeaderboard();
                this.getUserRank()
            })
        }
    }

    getUserRank() {
        let param = {
            "sports_id": AppSelectedSport,
            "mini_league_uid": this.state.MiniLeagueSponser.mini_league_uid,
        }
        getUserMiniLeagueLeaderBoard(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == NC.successCode) {
                this.setState({
                    userRankList: responseJson.data
                });
            }
        })

    }

    getLeaderboard() {
        let param = {
            "sports_id": AppSelectedSport,
            "mini_league_uid": this.state.MiniLeagueSponser.mini_league_uid,
            "page_size": "20",
            "page_no": this.state.pageNo
        }

        this.setState({ isLoaderShow: true })
        getMiniLeagueLeaderBoard(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            setTimeout(() => {
                this.setState({
                    isRefresh: false
                })
            }, 2000);
            if (responseJson && responseJson.response_code == NC.successCode) {
                this.setState({
                    leaderboardList: this.state.pageNo == 1 ? responseJson.data : [...this.state.leaderboardList, ...responseJson.data],
                    hasMore: responseJson.data.length === 20,
                    pageNo: this.state.pageNo + 1
                });
            }
        })
    }

    openPrizesForLeague = () => {
        this.props.history.push({
            pathname: '/all-prizes/' + this.state.MiniLeagueSponser.mini_league_uid + "/" + true, state: {
                LobyyData: this.state.LobyyData,
                MiniLeagueSponser: this.state.MiniLeagueSponser,
                isMiniLeaguePrize: this.state.isMiniLeaguePrize,
                MiniLeagueData: this.state.MiniLeagueSponser,
            }
        })


    }

    sendMessageToApp(action) {
        if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify(action));
        }
    }

    /**
     * @description This function is used to open player lineup page with formatted URL data
     * @param teamItem Team item
     * @see FieldView
    */
    openLineup = (teamItem, isYou) => {
        this.setState({
            SelectedLineup: teamItem.mini_league_leaderboard_id,
            userData: teamItem
        }, () => {
            
            let statusLeague = this.state.MiniLeagueListItem.status == 0 && (this.state.MiniLeagueListItem.game_starts_in * 1000 > Date.now()) ? '' : this.state.MiniLeagueListItem.status == 2 || this.state.MiniLeagueListItem.status == 3 ? 2 : 1

            this.props.history.push({
                pathname: '/user-league-points', state: {
                    FixturedContest: this.state.FixtureData,
                    LobyyData: this.state.LobyyData,
                    MiniLeagueSponser: this.state.MiniLeagueSponser,
                    userData: this.state.userData,
                    status: statusLeague,
                    isYou: isYou

                }
            })
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
    goToLobby = () => {
        this.props.history.push('/lobby')
    }
    
    ContestDetailShow = (item) => {
        this.setState({
          showContestDetail: true,
          MiniLeagueData:item,
      });
  }
   /**
     * @description method to hide contest detail model
     */
    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }
    render() {
        let statusLeague = this.state.MiniLeagueListItem.status == 0 && (this.state.MiniLeagueListItem.game_starts_in * 1000 > Date.now()) ? '' : this.state.MiniLeagueListItem.status == 2 || this.state.MiniLeagueListItem.status == 3 ? 2 : 1


        const HeaderOption = {
            back: true,
            fixture: false,
            statusLeaderBoard: statusLeague,
            screentitle: this.state.MiniLeagueSponser.mini_league_name,
            leagueDate: this.state.MiniLeagueSponser,
            hideShadow: true,
            leaderboard: true,
            minileague:true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        const { MiniLeagueSponser,showContestDetail } = this.state
        let sponserImage = MiniLeagueSponser.sponsor_logo && MiniLeagueSponser.sponsor_logo != null ? MiniLeagueSponser.sponsor_logo : 0
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container">

                        <CustomHeader
                            ref={(ref) => this.headerRef = ref}
                            HeaderOption={HeaderOption}
                            {...this.props} />


                        <Row>

                            <Col sm={12}>
                                <div className="leaderboard-wrapper">
                                    {
                                        (this.state.userRankList && this.state.leaderboardList.length == 0 && this.state.isLoaderShow) ?
                                            this.state.ShimmerList.map((item, index) => {
                                                return (
                                                    <Shimmer key={index} />
                                                )
                                            })
                                            :
                                            (this.state.userRankList && this.state.leaderboardList.length == 0 && !this.state.isLoaderShow) ?
                                                // <NoDataView
                                                //     BG_IMAGE={Images.no_data_bg_image}
                                                //     CENTER_IMAGE={Images.teams_ic}
                                                //     MESSAGE_1={AppLabels.NO_DATA_AVAILABLE}
                                                //     MESSAGE_2={''}
                                                //     BUTTON_TEXT={AppLabels.GO_BACK_TO_LOBBY}
                                                //     onClick={this.goToLobby.bind(this)}
                                                // />
                                                <div className="leaderbrd-ani-wrapper">
                                                    <LBAnimation />
                                                </div>
                                                :


                                                <div>
                                                    {this.state.rootItem.scoring_alert && this.state.rootItem.scoring_alert != '0' &&
                                                        <Alert variant="warning" className="alert-warning msg-alert-container border-radius-0">
                                                            <div className="msg-alert-wrapper">
                                                                <span className=""><i className="icon-megaphone"></i></span>
                                                                <span>{AppLabels.CUSTOM_SCORING_MSG}</span>
                                                            </div>
                                                        </Alert>
                                                    }

                                                    <div className="leaderboard-header header-free_to_play">
                                                        <div className="leaderboard-rank ">
                                                            <img style={{ marginLeft: '12px' }} src={Images.HALL_OF_FAME_SMALL_ICON} />

                                                            <div className="text_hall_of_fame">
                                                                {AppLabels.SPONSORED_BY}
                                                            </div>
                                                            {
                                                            window.ReactNativeWebView ?
                                                                <a
                                                                    href
                                                                    onClick={(event) => Utilities.callNativeRedirection(Utilities.getValidSponserURL(MiniLeagueSponser.sponsor_link, event))}>
                                                                    <img className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage)} />
                                                                </a>

                                                                :
                                                                <a
                                                                    href={Utilities.getValidSponserURL(MiniLeagueSponser.sponsor_link)}
                                                                    onClick={(event) => event.stopPropagation()}
                                                                    target='_blank'>
                                                                    <img className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage)} />
                                                                </a>

                                                        }
                                                        </div>

                                                        {

                                                            this.state.userRankList &&
                                                            <Fragment>

                                                                <div className={"leaderboard-header " + ("collpased-header-list-hide you-rank")} style={Object.keys(this.state.userRankList).length === 0 ? { height: 0, overflow: 'auto' } : { height: 55 }}>
                                                                    <div>
                                                                        {
                                                                            Object.keys(this.state.userRankList).length === 0 ? '' :
                                                                                <div key={this.state.userRankList.mini_league_leaderboard_id} onClick={() => this.openLineup(this.state.userRankList, true)} className={"ranking-list pointer-cursor my-ranking-list" + (this.state.SelectedLineup == this.state.userRankList.mini_league_leaderboard_id ? ' sel-active' : '')}>
                                                                                    <div className="display-table-cell text-center">
                                                                                        <div className="rank you-rank">{this.state.userRankList.game_rank}</div>
                                                                                    </div>
                                                                                    <div className={"display-table-cell pl-1 pt3 pb3" + (this.state.isExpandedWithDelay ? " " : '')}>
                                                                                        <div className= {"user-name-container"+(this.state.userRankList.prize_data && this.state.userRankList.prize_data != null && this.state.userRankList.prize_data.length > 0 ? '':' mt6')}>
                                                                                            {
                                                                                                
                                                                                                this.state.userRankList &&
                                                                                                <div className="user-name user-name-mini-league-leaderboard">{AppLabels.You}</div>
                                                                                            }
                                                                                            <div className={"user-team-name" + (!this.state.isExpandedWithDelay ? ' ' : '')}>


                                                                                                <span className="won-amount">

                                                                                                    {
                                                                                                        this.state.userRankList.prize_data && this.state.userRankList.prize_data != null && this.state.userRankList.prize_data.length > 0 &&

                                                                                                        _Map(this.state.userRankList.prize_data, (prizeItem, idx) => {

                                                                                                            return (

                                                                                                                (prizeItem.prize_type == 0) ?
                                                                                                                    <span className="contest-prizes"  >
                                                                                                                        {<i style={{ display: 'inlineBlock'}} className="icon-bonus"></i>}
                                                                                                                        {this.state.userRankList.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}
                                                                                                                    </span>
                                                                                                                    :
                                                                                                                    (prizeItem.prize_type == 1) ?
                                                                                                                        <span className="contest-prizes"  >

                                                                                                                            {<span style={{ display: 'inlineBlock'}}>{Utilities.getMasterData().currency_code}</span>}
                                                                                                                            {this.state.userRankList.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}
                                                                                                                        </span>
                                                                                                                        :
                                                                                                                        (prizeItem.prize_type == 2) ?
                                                                                                                            <span className="contest-prizes">
                                                                                                                                {<span style={{ display: 'inlineBlock' }}>
                                                                                                                                    <img style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />
                                                                                                                                    {this.state.userRankList.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}</span>}

                                                                                                                            </span>
                                                                                                                            :
                                                                                                                            (prizeItem.prize_type == 3) ?
                                                                                                                                <span className="contest-prizes" >
                                                                                                                                    {<span style={{ display: 'inlineBlock' }}>{this.state.userRankList.prize_data.length === idx + 1 ? prizeItem.name : prizeItem.name + "/"}</span>}

                                                                                                                                </span> : ''



                                                                                                            )


                                                                                                        })
                                                                                                    }
                                                                                                    {this.state.userRankList.prize_data && this.state.userRankList.prize_data != null && this.state.userRankList.prize_data.length > 0 ? <React.Fragment>

                                                                                                        <span style={{ color: '#5DBE7D' }} className="won"> {AppLabels.WON}  </span>{this.state.userRankList.team_name ? this.state.userRankList.team_name : ''}
                                                                                                    </React.Fragment> : " " + this.state.userRankList.team_name ? this.state.userRankList.team_name : ''
                                                                                                    }
                                                                                                </span>

                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="display-table-cell">
                                                                                        <div className="points">{this.state.userRankList.total_score}</div>
                                                                                    </div>
                                                                                    <div className='space' />

                                                                                </div>




                                                                        }
                                                                    </div>
                                                                </div>
                                                                
                                                            </Fragment>
                                                        }
                                                        <ListHeader context={this} />
                                                    </div>
                                                    <div className={'p-height ' + (Object.keys(this.state.userRankList).length === 0 ? ' user-rank-unavilable' : ' user-rank-available')} id='scrollableTarget'>
                                                        <InfiniteScroll
                                                            dataLength={this.state.leaderboardList.length}
                                                            next={() => this.onLoadMore()}
                                                            hasMore={!this.state.isLoaderShow && this.state.hasMore}
                                                            scrollableTarget={'scrollableTarget'}
                                                            loader={
                                                                this.state.isLoadMoreLoaderShow &&
                                                                <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                                            }>
                                                            <div className="leaderboard-listing">

                                                                {_Map(this.state.leaderboardList, (item, idx) => {

                                                                    return (
                                                                        <div key={item.mini_league_leaderboard_id} onClick={() => item.user_id == this.state.userRankList.user_id ? this.openLineup(item, true) : this.openLineup(item, false)} className={"ranking-list pointer-cursor " + (this.state.SelectedLineup == item.mini_league_leaderboard_id ? ' sel-active' : '')}>
                                                                            <div className="display-table-cell text-center">
                                                                                <div className="rank">{item.game_rank}</div>

                                                                            </div>
                                                                            <div className="display-table-cell pl-1">

                                                                                <div className= {"user-name-container" +(item.prize_data && item.prize_data != null && item.prize_data.length > 0 ? '':' mt6')}>
                                                                                    <div className="user-name user-name-mini-league-leaderboard">{item.user_name}</div>
                                                                                    <div className="user-team-name">



                                                                                        <span className="won-amount">
                                                                                            {
                                                                                                item.prize_data && item.prize_data != null && item.prize_data.length > 0 &&

                                                                                                _Map(item.prize_data, (prizeItem, idx) => {

                                                                                                    return (

                                                                                                        (prizeItem.prize_type == 0) ?
                                                                                                            <span className="contest-prizes" >
                                                                                                                {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                                                                                                                {item.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}
                                                                                                            </span>
                                                                                                            :
                                                                                                            (prizeItem.prize_type == 1) ?
                                                                                                                <span className="contest-prizes" >

                                                                                                                    {<span style={{ display: 'inlineBlock'}}>{Utilities.getMasterData().currency_code}</span>}
                                                                                                                    {item.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}
                                                                                                                </span>
                                                                                                                :
                                                                                                                (prizeItem.prize_type == 2) ?
                                                                                                                    <span className="contest-prizes" >
                                                                                                                        {<span style={{ display: 'inlineBlock' }}>
                                                                                                                            <img style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />
                                                                                                                            {item.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}</span>}

                                                                                                                    </span>
                                                                                                                    :
                                                                                                                    (prizeItem.prize_type == 3) ?
                                                                                                                        <span className="contest-prizes">
                                                                                                                            {<span style={{ display: 'inlineBlock' }}>{item.prize_data.length === idx + 1 ? prizeItem.name : prizeItem.name + "/"}</span>}

                                                                                                                        </span> : ''



                                                                                                    )


                                                                                                })


                                                                                            }


                                                                                            {item.prize_data && item.prize_data != null && item.prize_data.length > 0 ? <React.Fragment>

                                                                                                <span style={{ color: '#5DBE7D' }} className="won"> {AppLabels.WON}  </span>{item.team_name ? item.team_name : ''}
                                                                                            </React.Fragment> : " " + item.team_name ? item.team_name : ''
                                                                                            }

                                                                                        </span>

                                                                                    </div>
                                                                                </div>

                                                                            </div>
                                                                            <div className="display-table-cell">
                                                                                <div className="points">{item.total_score}</div>
                                                                            </div>
                                                                        </div>
                                                                    )
                                                                })}

                                                            </div>

                                                        </InfiniteScroll>
                                                    </div>


                                                </div>
                                    }
                                    
                                </div>
                            </Col>
                        </Row>

                        {
                            (this.state.userRankList.length > 0 || this.state.leaderboardList.length > 0) &&
                            <div className="bottom-download-container">

                                <div onClick={() => this.openPrizesForLeague()} >
                                    <span className="download-text text-uppercase"> {AppLabels.VIEW_ALL_PRIZES}</span>
                                </div>


                            </div>
                        }
                         {
                            showContestDetail &&
                            <LeagueDetails
                               {...this.props}
                                IsContestDetailShow={showContestDetail}
                                IsContestDetailHide={this.ContestDetailHide}
                                LobyyData={this.state.LobyyData}
                                MiniLeagueData={this.state.MiniLeagueSponser} />
                    }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
