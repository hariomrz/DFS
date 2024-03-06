import React, { Component, Fragment } from "react";
import { Row, Col, Button, Input, Table } from 'reactstrap';
import _ from 'lodash';
import * as NC from '../../helper/NetworkingConstants';
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import LS from 'local-storage';
import HF, { _isEmpty } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import Images from "../../components/images";
import { getFixtureApplyBooster, saveFixtureBooster } from "../../helper/WSCalling";
import { MODULE_NOT_ENABLE, BOOSTER_ER } from "../../helper/Message";
import { MomentDateComponent } from "../../components/CustomComponent";
class ApplyBooster extends Component {
    constructor(props) {
        super(props)
        this.state = {
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            // league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,
            // season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
            collection_master_id: (this.props.match.params.collection_master_id) ? this.props.match.params.collection_master_id : '',
            FromFixture: this.props.match.params.fromfixture,
            BoosterList: [],
            ApplyArr: [],
            btnPosting: true,
            BackTab: (this.props.match.params.tab) ? this.props.match.params.tab : 2,
            AllBstrApply: false,
            fxPosting: true,
            fixtureDetail: [],
            season_id: (this.props.match.params.season_id) ? this.props.match.params.season_id : '',
        }

    }

    componentDidMount() {
        if (HF.allowBooster() != '1' || !(HF.allowBoosterInSports(this.state.selected_sport))) {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }

        this.GetFixtureDetail();
        this.getBooster()
    }

    GetFixtureDetail = () => {
        let param = {
            // "league_id": this.state.league_id,
            // "sports_id": this.state.selected_sport,
            // "season_id": this.state.season_id,
            "collection_master_id": this.state.collection_master_id,
        }
        this.setState({ posting: true });

        WSManager.Rest(NC.baseURL + NC.DFS_GET_FIXTURE_DETAILS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    fixtureDetail: responseJson.data
                });
            }
            this.setState({ fxPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getBooster = () => {
        this.setState({ ListPosting: true })
        let params = {
            // "season_id": this.state.season_id,
            // "league_id": this.state.league_id,
            // "sports_id": this.state.selected_sport,
            "collection_master_id": this.state.collection_master_id,
        }
        getFixtureApplyBooster(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                this.setState({
                    BoosterList: ApiResponse.data ? ApiResponse.data : [],
                    ListPosting: false,
                    Total: ApiResponse.data ? ApiResponse.data.length : 0
                }, () => {
                    if (this.state.BoosterList.some(e => e.is_applied == '0')) {
                        this.setState({ AllBstrApply: true })
                    }
                })
            } else {
                this.setState({ ListPosting: false, Total: 0 })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    changeStatusToggle = (idx) => {
        let tempList = this.state.BoosterList
        let sendArr = this.state.ApplyArr

        if (tempList[idx]['is_applied'] == '1' && !tempList[idx]['disable']) {
            notify.show(BOOSTER_ER, "error", 3000)
            return false
        }

        tempList[idx]['is_applied'] = tempList[idx]['is_applied'] == '1' ? '0' : '1'
        tempList[idx]['disable'] = true

        if (tempList[idx]['is_applied'] == '1') {
            sendArr.push(tempList[idx].booster_id)
        } else {
            const index = sendArr.indexOf(tempList[idx].booster_id);
            if (index > -1) {
                sendArr.splice(index, 1);
            }
        }
        this.setState({
            BoosterList: tempList,
            btnPosting: _isEmpty(sendArr) ? true : false,
            ApplyArr: sendArr
        });
    }

    updateBooster = () => {
        let { ApplyArr, season_id, league_id, BackTab, FromFixture,collection_master_id } = this.state
        let param = {
            "collection_master_id": collection_master_id,
            // "league_id": league_id,
            "booster": ApplyArr
        }

        this.setState({ btnPosting: true })
        saveFixtureBooster(param).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                notify.show(ApiResponse.message, "success", 3000)
                if (FromFixture === '1') {
                    this.props.history.push('/game_center/DFS?tab=' + BackTab)
                } else {
                    this.props.history.push({ pathname: '/contest/fixturecontest/' + collection_master_id + '/' + season_id + '/' + BackTab })
                }
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    skip = () => {
        let { season_id, league_id, BackTab ,collection_master_id} = this.state
        this.props.history.push({ pathname: '/contest/fixturecontest/' + collection_master_id + '/' + season_id + '/' + BackTab })
    }

    render() {
        let { fixtureDetail, BoosterList, Total, ListPosting, btnPosting, fxPosting, AllBstrApply, BackTab, FromFixture } = this.state
        let { int_version } = HF.getMasterData()
        return (
            <div className="apply-booster">
                {
                    (FromFixture === '1') &&
                    <label className="back-to-fixtures float-right" onClick={() => this.props.history.push('/game_center/DFS?tab=' + BackTab)}> {'<'} {int_version == "1" ? "Back to Games" : "Back to Fixtures"}</label>
                }
                <Row className="mt-30">
                    <div className="flip-animation common-fixture p-0">
                        <div className="bg-card">
                            {
                                fxPosting ?
                                    <Loader hide />
                                    :
                                    <div className="dfs-mn-hgt">
                                        <img className="com-fixture-flag float-left xcardimg" src={NC.S3 + NC.FLAG + (fixtureDetail.home_flag ? fixtureDetail.home_flag : fixtureDetail.match[0].home_flag)}></img>
                                        <img className="com-fixture-flag float-right xcardimg" src={NC.S3 + NC.FLAG + (fixtureDetail.away_flag ? fixtureDetail.away_flag : fixtureDetail.match[0].away_flag)}></img>
                                        <div className="com-fixture-container">
                                            <div className="com-fixture-name xlivcardh3">{(fixtureDetail.home) ? fixtureDetail.home : fixtureDetail.match[0].home} VS {(fixtureDetail.away) ? fixtureDetail.away : fixtureDetail.match[0].away}</div>
                                            <div className="com-fixture-time xlivcardh6">
                                                {/* <MomentDateComponent data={{ date: fixtureDetail.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                                {HF.getFormatedDateTime(fixtureDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                                            </div>
                                            <div className="com-fixture-title xlivcardh6">{fixtureDetail.league_name}</div>
                                        </div>
                                    </div>
                            }
                        </div>
                    </div>
                </Row>
                <div className="booster animate-left">
                    <Row>
                        <Col md={12}>
                            <h2 className="h2-cls">Boosters</h2>
                            {(AllBstrApply && BackTab == '2') && <div className="en-booster">Enable Boosters</div>}
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table className="mb-0">
                                <thead>
                                    <tr>
                                        <th>Scoring Parameters</th>
                                        <th>Icon</th>
                                        <th>Position</th>
                                        <th>Display Name</th>
                                        <th>Points</th>
                                        {(BackTab == '2') && <th>Enable/Disable</th>}
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _.map(BoosterList, (item, idx) => {
                                            return (
                                                // item.is_applied == "1" &&
                                                ((BackTab == '2') || (BackTab != '2' && item.is_applied == "1")) &&
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>{item.name}</td>
                                                        <td>
                                                            <div className="b-icon">
                                                                <img
                                                                    src={item.image_name ? NC.S3 + NC.BOOSTER + item.image_name : Images.no_image}
                                                                    className="img-cover" alt=""
                                                                />
                                                            </div>
                                                        </td>
                                                        <td>{item.position}</td>
                                                        <td>{item.name}</td>
                                                        <td>{item.points}</td>
                                                        {
                                                            (BackTab == '2') &&
                                                            <td>
                                                                <div className="activate-module">
                                                                    <label className="global-switch">
                                                                        <input
                                                                            type="checkbox"
                                                                            checked={item.is_applied == "0" ? false : true}
                                                                            onChange={() => this.changeStatusToggle(idx)}
                                                                        />
                                                                        <span className="switch-slide round">
                                                                            <span className={`switch-on ${item.is_applied == "0" ? 'active' : ''}`}></span>
                                                                            <span className={`switch-off ${item.is_applied == "1" ? 'active' : ''}`}></span>
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </td>
                                                        }
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan="8">
                                                    {(Total == 0 && !ListPosting) ?
                                                        <div className="no-records">
                                                            {NC.NO_RECORDS}</div>
                                                        :
                                                        <Loader />
                                                    }
                                                </td>
                                            </tr>
                                        </tbody>
                                }
                            </Table>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="submit-box">
                                {
                                    (AllBstrApply && BackTab == '2') &&
                                    <Button
                                        disabled={btnPosting}
                                        className="btn-secondary-outline"
                                        onClick={() => this.updateBooster()}
                                    >
                                        Submit
                                    </Button>
                                }
                                {
                                    (FromFixture === '0' && BackTab == '2') && <a
                                        className="skip"
                                        onClick={() => this.skip()}>
                                        Skip
                                    </a>
                                }
                            </div>
                        </Col>
                    </Row>
                </div>
            </div>
        )
    }
}
export default ApplyBooster

