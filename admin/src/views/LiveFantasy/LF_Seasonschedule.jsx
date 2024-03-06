import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input, Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import Pagination from "react-js-pagination";
import LS from 'local-storage';
import PromptModal from '../../components/Modals/PromptModal';
import { MOVE_LINEUP_TO_LIVE } from '../../helper/Message';
const options = [
    { value: '1', label: 'Inning 1' },
    { value: '2', label: 'Inning 2' },
]
export default class LF_Seasonschedule extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: '1',
            InningChange: "1",
            Fields: [],
            SeasonData: [],
            PublishModalOpen: false,
            EditModalOpen: false,
            itemObj: {},
            updatePosting: false,
            SeasonResult: [],
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            BackTab: (this.props.match.params.tab) ? this.props.match.params.tab : 1,
            MoveLineupOpen: false,
            MoveLineupPosting: false,
        }
    }
    componentDidMount() {
        this.getSeasonStats()
    }

    getSeasonStats = () => {
        let { InningChange, PERPAGE, CURRENT_PAGE, selected_sport } = this.state
        let { leagueid, gameid } = this.props.match.params
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: "DESC",
            sort_field: "",
            sports_id: selected_sport,
            league_id: leagueid ? leagueid : "0",
            match_inning: InningChange,
            game_unique_id: gameid ? gameid : "0"
        }
        WSManager.Rest(NC.baseURL + NC.GET_SEASON_STATS, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    Fields: ResponseJson.data.fields,
                    SeasonResult: ResponseJson.data.result,
                    SeasonData: ResponseJson.data.season_data,
                    Total: ResponseJson.data.total
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleTypeChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value.value }, this.getSeasonStats)
    }

    playPause = (matchstatus) => {
        let { gameid } = this.props.match.params
        let params = {}
        params = {
            match_status: (matchstatus == "2") ? "2" : matchstatus == "1" ? "0" : "1",
            season_game_uid: gameid ? gameid : "0"
        }

        let tempData = this.state.SeasonData
        WSManager.Rest(NC.baseURL + NC.LF_UPDATE_MATCH_STATUS, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 3000)

                tempData.match_status = (matchstatus == "2") ? "2" : matchstatus == "1" ? "0" : "1"

                if (matchstatus == "2")
                    this.publishEnsureToggle()

                this.setState({
                    SeasonData: tempData
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    reCalculate = () => {
        let { selected_sport } = this.state
        let { leagueid, gameid } = this.props.match.params
        let params = {
            sports_id: selected_sport,
            league_id: leagueid ? leagueid : "0",
            season_game_uid: gameid ? gameid : "0"
        }
        WSManager.Rest(NC.baseURL + NC.RECALCULATE_MATCH_SCORE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show("Success", "success", 3000)
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getSeasonStats();
        });
    }

    publishEnsureToggle = () => {
        this.setState({
            PublishModalOpen: !this.state.PublishModalOpen
        });
    }

    publishEnsureModal() {
        return (
            <Modal isOpen={this.state.PublishModalOpen} toggle={this.publishEnsureToggle}>
                <ModalHeader>Publish Stats</ModalHeader>
                <ModalBody className="font-16">Please recalculate point before publish it. Are you sure want to publish match ?</ModalBody>
                <ModalFooter>
                    <Button color="secondary" onClick={() => this.playPause("2")}>Yes</Button>{' '}
                    <Button color="primary" onClick={this.publishEnsureToggle}>No</Button>
                </ModalFooter>
            </Modal>
        )
    }

    editStatsToggle = (itemObj) => {
        this.setState({
            itemObj: itemObj,
            EditModalOpen: !this.state.EditModalOpen
        });
    }

    editStatsModal = () => {
        let { itemObj } = this.state
        return (
            <Modal isOpen={this.state.EditModalOpen} toggle={() => this.editStatsToggle(itemObj)} className="modal-lg lf-stats-edit-modal">
                <ModalHeader>Player Score</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr>
                                        {
                                            _.map(itemObj, (item, idx) => {
                                                return (
                                                    idx != 'player_uid' &&
                                                    <th key={idx}>
                                                        {idx.replace(/_/g, ' ').toLowerCase()}</th>
                                                )
                                            })
                                        }
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        {
                                            _.map(itemObj, (item, idx) => {
                                                return (
                                                    idx != 'player_uid' &&
                                                    (
                                                        (idx == 'display_name' || idx == 'player_name' || idx == 'position' || idx == 'team_name' || idx == 'scheduled_date') ?
                                                            <td key={idx}>
                                                                {item.replace(/_/g, ' ').toLowerCase()}
                                                            </td>

                                                            :
                                                            <td key={idx}>
                                                                <Input type="number" value={item.replace(/_/g, ' ').toLowerCase()} name={idx}
                                                                    onChange={(e) => this.handleInputChange(e)}
                                                                />
                                                            </td>
                                                    )
                                                )
                                            })
                                        }
                                    </tr>
                                </tbody>
                            </Table>
                        </Col></Row>
                </ModalBody>
                <ModalFooter>
                    <Button disabled={this.state.updatePosting} color="secondary" onClick={this.updatePlayerMatchScore}>Update</Button>{' '}
                    <Button color="primary" onClick={() => this.editStatsToggle(itemObj)}>Cancel</Button>
                </ModalFooter>
            </Modal>
        )
    }

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value
        let tempObj = this.state.itemObj
        tempObj[name] = value
        this.setState({ itemObj: tempObj })
    }

    updatePlayerMatchScore = () => {
        let { leagueid, gameid } = this.props.match.params
        let { InningChange, itemObj, selected_sport } = this.state

        for (var key in itemObj) {
            if (itemObj[key] == '') {
                notify.show(key.replace(/_/g, ' ').toLowerCase() + ' field can not be empty.', 'error', 3000)
                return false;
            }
            else {
                this.setState({
                    updatePosting: false,
                    itemObj: itemObj,
                })
            }
        }

        let params = {
            sports_id: selected_sport,
            season_game_uid: gameid ? gameid : "0",
            league_id: leagueid ? leagueid : "0",
            match_inning: InningChange,
            player_data: itemObj
        }

        WSManager.Rest(NC.baseURL + NC.UPDATE_PLAYER_MATCH_SCORE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 3000)
                this.editStatsToggle(itemObj)
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    moveLineupModal = () => {
        this.setState({
            MoveLineupOpen: !this.state.MoveLineupOpen,
        });
    }

    moveLineup = () => {
        let { SeasonData } = this.state
        this.setState({ MoveLineupPosting: true });
        let param = {
            "season_game_uid": SeasonData.season_game_uid,
            "league_id": SeasonData.league_id,
        }
        WSManager.Rest(NC.baseURL + NC.MOVE_MATCH_TO_LIVE, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {  
                this.getSeasonStats()              
                this.setState({ 
                    MoveLineupPosting: false,
                    MoveLineupOpen : false
                 })
                notify.show(responseJson.message, "success", 3000);
            }
            else{
                notify.show(responseJson.message, "error", 3000);
            }
        })
    }

    render() {
        let { BackTab, SeasonResult, SeasonData, Fields, InningChange, CURRENT_PAGE, PERPAGE, Total, MoveLineupOpen, MoveLineupPosting } = this.state
        let MoveLineupProps = {
            publishModalOpen: MoveLineupOpen,
            publishPosting: MoveLineupPosting,
            modalActionNo: this.moveLineupModal,
            modalActionYes: this.moveLineup,
            MainMessage: MOVE_LINEUP_TO_LIVE,
            SubMessage: '',
        }
        return (
            <Fragment>
                <div className="lf-season-stats">
                    {this.editStatsModal()}
                    {MoveLineupOpen && <PromptModal {...MoveLineupProps} />}
                    <Row>
                        <Col md={12} className="top-heading">
                            <h1 className="h1-cls">Season Stats</h1>
                            <label className="back-btn"
                                onClick={() => this.props.history.push('/livefantasy/fixture?tab=' + BackTab)}

                            > {'< '}Back to Fixtures</label>
                        </Col>
                    </Row>
                    <Row className="xfilter-userlist mb-4">
                        <Col md={2}>
                            <div>
                                <label className="filter-label">Select Innings</label>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    options={options}
                                    menuIsOpen={true}
                                    value={InningChange}
                                    onChange={e => this.handleTypeChange(e, 'InningChange')}
                                />
                            </div>
                        </Col>
                        <Col md={10}>
                            <ul className="stats-list">
                                {
                                    (SeasonData.status != "2") &&
                                    <Fragment>
                                    <li className="stats-items">
                                        <Button onClick={() => this.playPause(SeasonData.match_status)}>Pause</Button>
                                    </li>
                                    <li className="stats-items">
                                        <Button onClick={this.reCalculate}>Recalculate</Button>
                                    </li>
                                    </Fragment>
                                }
                                {
                                    (SeasonData.match_status == "1" && SeasonData.match_started == 1) &&
                                    <Fragment>
                                        {/* <li className="stats-items">
                                            <Button onClick={this.reCalculate}>Recalculate</Button>
                                        </li> */}
                                        <li className="stats-items">
                                            <Button onClick={() => this.playPause(SeasonData.match_status)}>Play</Button>
                                        </li>
                                        <li className="stats-items">
                                            {this.publishEnsureModal()}
                                            <Button onClick={this.publishEnsureToggle}>Publish</Button>
                                        </li>
                                    </Fragment>
                                }

                                {
                                    (SeasonData.status == "2" && SeasonData.status_overview == "4") &&
                                    <li className="stats-items">
                                        {this.publishEnsureModal()}
                                        <Button onClick={this.moveLineupModal}>Move Match To Live</Button>
                                    </li>
                                }
                            </ul>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <div className="tableFixHead">
                                <Table>
                                    <thead>
                                        <tr>
                                            {
                                                _.map(Fields, (item, idx) => {
                                                    return (
                                                        (idx > 0) &&
                                                        <th key={idx}>{item.replace(/_/g, ' ').toLowerCase()}</th>
                                                    )
                                                })
                                            }
                                        </tr>
                                    </thead>
                                    {
                                        Total > 0 ?
                                            _.map(SeasonResult, (item, sidx) => {
                                                return (<tbody key={sidx}>
                                                    <tr>
                                                        {
                                                            _.map(Fields, (fieldname, idx) => {
                                                                return (
                                                                    idx > 0 && <td key={idx}>
                                                                        {item[fieldname]}
                                                                        <div className="edit-stats-btn">
                                                                            {
                                                                                (fieldname == 'player_name' && SeasonData.match_status == "1" && SeasonData.match_started == 1) &&
                                                                                <Button onClick={() => this.editStatsToggle(item)} className="stats-btn xfloat-right">Edit</Button>
                                                                            }
                                                                        </div>
                                                                    </td>
                                                                )
                                                            })
                                                        }
                                                    </tr>
                                                </tbody>)
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan="26">
                                                        <div className="no-records">No Records Found.</div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                    }
                                </Table>
                            </div>
                        </Col>
                    </Row>
                    {Total > PERPAGE && (
                        <div className="custom-pagination lobby-paging">
                            <Pagination
                                activePage={CURRENT_PAGE}
                                itemsCountPerPage={PERPAGE}
                                totalItemsCount={Total}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>
                    )
                    }
                </div>
            </Fragment>
        )
    }
}