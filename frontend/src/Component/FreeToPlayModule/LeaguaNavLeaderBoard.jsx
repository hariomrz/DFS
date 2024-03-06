import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { _isUndefined } from '../../Utilities/Utilities';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { getMiniLeagueByStatus } from "../../WSHelper/WSCallings";
import * as Constants from "../../helper/Constants";
import { NoDataView } from '../CustomComponent';
import * as AppLabels from "../../helper/AppLabels";
import LBAnimation from '../../Component/FantasyRefLeaderboard/LeaderboardAnimation';

class LeaguaNavLeaderBoard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            LobyyData: !_isUndefined(props.location.state) ? props.location.state.LobyyData : [],
            isFromLobby: !_isUndefined(props.location.state) ? props.location.state.isFromLobby : true,
            MiniLeagueList: [],
            MiniLeagueData: '',
            showBtmBtn: '',
            oldScrollOffset: 0,
            scrollStatus: '',

            showContestDetail: false,
            HeaderOption: {
                back: true,
                title: AL.F2P_LEAGUES,
                hideShadow: false
            }
        };
        this._timeout = null;
        this.checkScrollStatus = this.checkScrollStatus.bind(this);
    }

    checkScrollStatus() {
        if (this._timeout) { 
            clearTimeout(this._timeout);
        }
        this._timeout = setTimeout(() => {
            this._timeout = null;
            this.setState({
                scrollStatus: 'scroll stopped',
                showBtmBtn: ''
            });
        }, 700);
        if (this.state.scrollStatus !== 'scrolling') {
            this.setState({
                scrollStatus: 'scrolling'
            });
        }
    }
    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        this.checkScrollStatus();
        this.setState({
            soff: scrollOffset
        })
        if (this.state.oldScrollOffset < scrollOffset) {
            this.setState({
                showBtmBtn: 'hideBottomBtn',
                oldScrollOffset: scrollOffset
            })
        } else {
            this.setState({
                showBtmBtn: '',
                oldScrollOffset: scrollOffset
            })
        }
    }

    UNSAFE_componentWillMount = () => {
        this.getMiniLeagueByStatusApi();
        window.addEventListener('scroll', this.onScrollList);


    }
    componentWillUnmount() {
        window.removeEventListener('scroll', this.onScrollList);
    }
    ContestDetailShow = (item) => {
        this.setState({
            showContestDetail: true,
            MiniLeagueData: item,
        });
    }
    openLeaderBoardLeague = (item) => {

        this.props.history.push({
            pathname: '/mini-league-leader-board', state: {
                LobyyData: this.state.LobyyData,
                MiniLeagueSponser: item,
                MiniLeagueListItem: item
            }
        })
    }
    /**
     * @description method to hide contest detail model
     */
    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }

    getMiniLeagueByStatusApi = async () => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            "status": "live",
            "page_no": "1",
            "page_size": "500"
        }
        delete param.limit;
        var api_response_data = await getMiniLeagueByStatus(param);
        if (api_response_data) {
            this.setState({ MiniLeagueList: api_response_data })
        }




    }
    render() {

        const HeaderOption = {
            back: true,
            isFromLobby: this.state.isFromLobby,
            title: AL.F2P_LEAGUES,
            share: true
        }
        const { LobyyData, MiniLeagueList, showContestDetail } = this.state;
        return (
            <MyContext.Provider >
                    <div className="prediction-wrap-v padding-leagues prediction-part-v open-predict-leaderboard is-leaderboard">
                            {
                                MiniLeagueList.data && MiniLeagueList.data.map((item, index) => {
                                    return (
                                        <div className="league-list-all">
                                            <div className="sort-contest-wrapper mt15">
                                                <div className="league-section no-border" onClick={() => this.openLeaderBoardLeague(item)}>
                                                    <div className="league-name">
                                                        {item.mini_league_name}

                                                    </div>
                                                    {
                                                        item.join_count > 0 &&
                                                        <div className="pull-right verity-count">
                                                            {AL.JOINED_CAP}
                                                        </div>
                                                    }


                                                    <p> {item.total_complete + " / " + item.season_count + " Matches"}</p>
                                                </div>

                                            </div>
                                        </div>
                                    );
                                })
                            }
                            {
                                MiniLeagueList.data && MiniLeagueList.data.length == 0 &&
                                // <NoDataView
                                //     BG_IMAGE={Images.no_data_bg_image}
                                //     CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                //     MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                //     MESSAGE_2={AppLabels.NO_DATA_VIEW_MESSAGE_ALL_LEAGUE}
                                //     onClick_2={this.joinContest}
                                // />
                                <div className="leaderbrd-ani-wrapper">
                                    <LBAnimation />
                                </div>
                            }
                             <div className={"roster-footer mb80 pl15 pr15 " + this.state.showBtmBtn}>
                            <div className="btn-wrap">
                                <button onClick={() => this.props.history.push({
                                    pathname: '/completed-leagues',
                                    state: { LobyyData: LobyyData, }
                                })} className="btn btn-primary btn-block btm-fix-btn completed-league-preview">{AL.COMPLETED_LEAGUE}</button>
                            </div>
                        </div>

                       
                    </div>
                
            </MyContext.Provider>



        )
    }
}

export default LeaguaNavLeaderBoard;
