import React, { lazy, Suspense } from 'react';
import { PanelGroup, Panel } from "react-bootstrap";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Skeleton from 'react-loading-skeleton';
import { NoDataView } from '../../Component/CustomComponent';
import Images from '../../components/images';
import { Utilities, _filter, _Map, _isUndefined, _isEmpty } from '../../Utilities/Utilities';
const ReactSelectDD = lazy(() => import('../../Component/CustomComponent/ReactSelectDD'));

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

export default class ScoreCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            activeKey: '1',
            fixtureData: '',
            fixtureDetail: '',
            scoreCardData: '',
            isLoaderShow: false,
            ShimmerList: [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
        }
        this.handleSelect = this.handleSelect.bind(this);
    }
    componentDidMount() {
    }

    handleSelect(activeKey) {
        this.setState({ activeKey });
    }

    showScoreData = (SData, isFor) => {
        return (
            <>
                {
                    SData && SData.length > 0 && isFor == 'FOW' &&
                    <table>
                        <thead>
                            <tr>
                                <th>{AL.FALL_OF_WICKETS}</th>
                                <th>{AL.SCORE}</th>
                                <th>{AL.OVER}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {
                                _Map(SData, (item, idx) => {
                                    return (
                                        <tr>
                                            <td>{item.name}</td>
                                            <td>{item.score_at_dismissal}/{item.number}</td>
                                            <td>{item.overs_at_dismissal}</td>
                                        </tr>
                                    )
                                })
                            }
                        </tbody>
                    </table>
                }
                {
                    SData && Object.keys(SData).length > 0 && isFor != 'FOW' &&
                    <table>
                        {
                            isFor == 'batting' ?
                                <>
                                    <thead>
                                        <tr>
                                            <th>{AL.BATSMEN}</th>
                                            <th>R</th>
                                            <th>B</th>
                                            <th>4s</th>
                                            <th>6s</th>
                                            <th>S/R</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {
                                            _Map(SData, (item, idx) => {
                                                return (
                                                    <tr>
                                                        <td>
                                                            {item.player_name}
                                                            {/* <span>{item.position}</span> */}
                                                            <span className='out-of-string-text'>{item.out_string}</span>

                                                        </td>
                                                        <td>
                                                            {item.batting_runs}
                                                        </td>
                                                        <td>
                                                            {item.batting_balls_faced}
                                                        </td>
                                                        <td>
                                                            {item.batting_fours}
                                                        </td>
                                                        <td>
                                                            {item.batting_sixes}
                                                        </td>
                                                        <td>
                                                            {item.batting_strike_rate}
                                                        </td>
                                                    </tr>
                                                )
                                            })
                                        }
                                    </tbody>
                                </>
                                :
                                isFor == 'bowling' &&
                                <>
                                    <thead>
                                        <tr>
                                            <th>{AL.BOWLER}</th>
                                            <th>O</th>
                                            <th>M</th>
                                            <th>R</th>
                                            <th>W</th>
                                            <th>E/R</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {
                                            _Map(SData, (item, idx) => {
                                                return (
                                                    <tr>
                                                        <td>
                                                            {item.player_name}
                                                            {/* <span>{item.position}</span> */}
                                                        </td>
                                                        <td>
                                                            {item.bowling_overs}
                                                        </td>
                                                        <td>
                                                            {item.bowling_maiden_overs}
                                                        </td>
                                                        <td>
                                                            {item.bowling_runs_given}
                                                        </td>
                                                        <td>
                                                            {item.bowling_wickets}
                                                        </td>
                                                        <td>
                                                            {item.bowling_economy_rate}
                                                        </td>
                                                    </tr>
                                                )
                                            })
                                        }
                                    </tbody>
                                </>
                        }
                    </table>
                }
            </>
        )
    }

    render() {

        const {
            scoreCardData,
            fixtureDetail,
            // isLoaderShow
        } = this.props;
        let TeamScoreData = fixtureDetail.score_data;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="scorecard-wrap">
                        <div className="table-scoreboard">
                            {
                                fixtureDetail && TeamScoreData &&
                                <>

                                    {
                                        _Map(scoreCardData, (scoreArry, idx) => {
                                            let tmScore = TeamScoreData[idx]
                                            let isHS = !_isUndefined(scoreArry[fixtureDetail.home_uid]) && !_isUndefined(scoreArry[fixtureDetail.home_uid].batting) ? true : false
                                            let isAS = !_isUndefined(scoreArry[fixtureDetail.away_uid]) && !_isUndefined(scoreArry[fixtureDetail.away_uid].batting) ? true : false
                                            return (
                                                <>
                                                    {
                                                        _Map(_isEmpty(fixtureDetail.team_batting_order) ? 
                                                            [{ team_uid: fixtureDetail.home_uid }, { team_uid: fixtureDetail.away_uid }] : 
                                                            fixtureDetail.team_batting_order, (item, i) => {
                                                            return (
                                                                <CommonTeam key={i} {...{ 
                                                                    scoreArry, 
                                                                    tmScore, 
                                                                    fixtureDetail, 
                                                                    showScoreData: this.showScoreData,
                                                                    team_uid: item.team_uid, 
                                                                    }}
                                                                    defaultExpanded={i == 0}
                                                                />
                                                            )
                                                        })
                                                    }
                                                    {
                                                        !isAS && !isHS &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            CENTER_IMAGE={Images.teams_ic}
                                                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                            MESSAGE_2={''}
                                                        />
                                                    }
                                                </>
                                            )
                                        }
                                        )
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
const CommonTeam = ({ scoreArry, tmScore, fixtureDetail, showScoreData, team_uid, defaultExpanded }) => {
    const team_uid_f = team_uid == fixtureDetail.away_uid ? fixtureDetail.away_uid : fixtureDetail.home_uid;
    const team_uid_s = team_uid == fixtureDetail.home_uid ? fixtureDetail.away_uid : fixtureDetail.home_uid;

    const prefix = fixtureDetail.away_uid == team_uid_f ? 'away' : 'home'
    return (
        <Panel id={`collapsible-panel-example-${team_uid_f}`} defaultExpanded={defaultExpanded}>
            <Panel.Heading>
                <Panel.Title toggle>
                    <div className="team-score">
                        <span>({tmScore[prefix + '_overs']})</span> {tmScore[prefix + '_team_score']}/{tmScore[prefix + '_wickets']}
                        <i className="icon-arrow-down"></i>
                        <i className="icon-arrow-up"></i>
                    </div>
                    <div className="team-nm">{fixtureDetail[prefix]}</div>
                </Panel.Title>
            </Panel.Heading>
            <Panel.Collapse>
                <Panel.Body>
                    <>
                        {
                            !_isUndefined(scoreArry[team_uid_f]) && !_isUndefined(scoreArry[team_uid_f].batting) && showScoreData(scoreArry[team_uid_f].batting, 'batting')
                        }
                    </>
                    <>
                        {!_isUndefined(scoreArry[team_uid_s]) && !_isUndefined(scoreArry[team_uid_s].bowling) && showScoreData(scoreArry[team_uid_s].bowling, 'bowling')}
                    </>
                    <>
                        {!_isUndefined(scoreArry[team_uid_f]) && !_isUndefined(scoreArry[team_uid_f].fall_of_wickets) && showScoreData(scoreArry[team_uid_f].fall_of_wickets, 'FOW')}
                    </>
                </Panel.Body>
            </Panel.Collapse>
        </Panel>
    )
}