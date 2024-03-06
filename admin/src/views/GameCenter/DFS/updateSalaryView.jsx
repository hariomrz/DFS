import React, { Component, Fragment } from 'react'
import { Row, Col, Table, Input } from 'reactstrap'
import _ from 'lodash'
import Select from 'react-select';
import { notify } from 'react-notify-toast';
import HF from '../../../helper/HelperFunction';
class UpdateSalaryView extends Component {
    constructor(props) {
        super(props)
        this.state = {
            All_Postion: this.props.All_Postion,
            NewPublishedList: this.props.NewPublishedList,
            UnPublishedList: this.props.UnPublishedList,
            PublishedList: this.props.PublishedList,
            Roster_Data: this.props.Roster_Data,
            activeTab: this.props.activeTab
        }
    }
    componentWillReceiveProps(nextProps) {
        if (nextProps.NewPublishedList != this.props.NewPublishedList) {
            this.setState({
                All_Postion: nextProps.All_Postion,
                NewPublishedList: nextProps.NewPublishedList,
                UnPublishedList: nextProps.UnPublishedList,
                PublishedList: nextProps.PublishedList,
                Roster_Data: nextProps.Roster_Data,
                activeTab: nextProps.activeTab,
            })
        }
    }

    handleDisNameChange = (e, item) => {
        if (e != null) {
            this.props.updateDisNameList(item, e.target.value);
        }
    }

    handleChange = (e, item) => {
        if (e != null) {
            this.props.updatePositionList(item, e.value);
        }
    }

    isPositionAllSelected = (List, position) => {
        let { NewPublishedList } = this.state;
        let isAllSelected = true;
        _.map(NewPublishedList, (item) => {
            if (item.is_selected != '1') {
                isAllSelected = false
            }
        })
        return isAllSelected
    }

    updateRosterListOnInputChange = (upValue, changePlId, UpdateKey) => {
        let tempRosList = this.state.FinalRosterList
        _.map(tempRosList, (RosterList, RosterListIdx) => {
            if (RosterList.player_id == changePlId) {
                if (UpdateKey == "salary")
                    tempRosList[RosterListIdx].salary = upValue
                else
                    tempRosList[RosterListIdx].position = upValue
            }
        })
        this.setState({ FinalRosterList: tempRosList })
        //End code for update roster lisht on change input
    }

    handleInputChange = (e, item) => {
        if (e != null) {
            //Start check validation
            let tarValue = e.target.value

            if (tarValue.includes(".")) {
                var spNumber = tarValue.split('.');
                if (spNumber[1].length > 2) {
                    this.props.updateSalaryList(item, '');
                    notify.show(item.full_name + 'Salary should be of 2 decimal values', "error", 5000);
                } else {
                    this.props.updateSalaryList(item, e.target.value);
                }
            } else {
                this.props.updateSalaryList(item, e.target.value);
            }
            //End check validation;
        }
    }

    handleSelectPlayer = (item, type, ListArray) => {
        let { NewPublishedList, Roster_Data } = this.state

        let selectedV = item.is_selected == "1" ? "0" : "1"
        let removeKey = false
        if (type == "un_published" && Roster_Data.is_published == "0" && selectedV == "0") {
            removeKey = true
        }
        this.props.updateSelectList(item, selectedV, ListArray, type, removeKey);
    }

    selectAllPlayer = () => {
        let { All_Postion, activeTab } = this.state;
        let tmpAllP = All_Postion;
        let isSelect = ''
        _.map(tmpAllP, (item, idx) => {
            if (item.value == activeTab) {
                isSelect = item.isAllSelected == "1" ? "0" : "1"
                tmpAllP[idx]['isAllSelected'] = isSelect;
            }
        })
        this.setState({ All_Postion: tmpAllP }, () => {
            this.props.updateAllSelectList(isSelect);
        })
    }


    handleAllSelect = (selectAllNewPlayer, selectFrom) => {
        let { listPropData } = this.state
        if (selectFrom != null) {
            let TempListData = listPropData

            let TempSelArr = this.props.FinalRosterList

            var arrayData = TempListData[selectFrom];

            let TempRosterList = {}
            let selectVAr = (selectAllNewPlayer == "0") ? "1" : "0"
            _.map(arrayData, (item, idx) => {
                item.is_published = selectVAr

                TempRosterList = {
                    "full_name": item.full_name,
                    "player_team_id": item.player_team_id,
                    "player_id": item.player_id,
                    "team_league_id": item.team_league_id,
                    "salary": item.salary,
                    "position": item.position,
                    "is_published": "1",
                }

                let isExist = false
                _.remove(TempSelArr, function (RemoveItem, idx) {
                    if (RemoveItem.player_team_id == item.player_team_id) {
                        isExist = true
                        return RemoveItem.player_team_id == item.player_team_id
                    }
                })

                if (!isExist)
                    TempSelArr.push(TempRosterList)
            })
            this.setState({
                listPropData: TempListData,
                selectAllNewPlayer: selectVAr,

                FinalRosterList: TempSelArr

            }, () => {
                this.props.saveUpdatePlayers(this.state.FinalRosterList)
            })
        }
    }

    onChangeReturnNull = () => {
        return null
    }

    renderListComponent = (ListArray, type) => {
        let { All_Postion, activeTab, Roster_Data } = this.state
        return (
            <Fragment>
                {
                    Roster_Data.is_tour_game == 1 ?
                    <Row>
                    <Col sm="12">
                        <Table className="players-table players-table-new-motor">
                            <thead>
                                <tr>
                                    {
                                        (type == "is_published" && Roster_Data.is_published == "1") ? <></> :
                                        <th rowSpan="2" className="pl-4">
                                        {
                                            type == "new_published" &&
                                            <Fragment>
                                                Add in Team
                                                <br />
                                                <label
                                                    className="select-all"
                                                    onChange={() => this.selectAllPlayer()}
                                                >
                                                    <Input
                                                        className="select-all-in"
                                                        type="checkbox"
                                                        onChange={this.onChangeReturnNull}
                                                        checked={this.isPositionAllSelected(All_Postion, activeTab)}
                                                    />
                                                    <span className="ml-4">Select All</span>
                                                </label>
                                            </Fragment>
                                        }
                                    </th>
                                    }
                                   
                                    <th style={{paddingLeft : "10px"}}>Player Name</th>
                                    <th>Display Name</th>
                                    <th className="pl-20">Team Name</th>
                                    {/* <th>Car</th> */}
                                    <th>Salary</th>
                                </tr>
                            </thead>
                            <tbody>
                                {

                                    _.map(ListArray, (item, idx) => {
                                        return (
                                            <tr
                                                className={(type == "is_published" && item.is_published == "1" && Roster_Data.is_published == "1") ? "already-published" : ((item.is_selected == "1") ? "active" : "")}


                                                key={idx}>
                                                    {
                                                        (type == "is_published" && item.is_published == "1" && Roster_Data.is_published == "1") ?
                                                        <></> :
                                                        <td className="pt-4">
                                                        <Input
                                                            disabled={HF.getMasterData().allow_network_fantasy == '1' ? true : false}
                                                            className="select-single ml-4"
                                                            type="checkbox"
                                                            checked={item.is_selected == "1"}
                                                            onChange={(e) => this.handleSelectPlayer(item, type, ListArray)}
                                                        />
                                                    </td>
                                                  }
                                               
                                                <td className="pt-4" style={{paddingLeft : "10px"}}>{item.full_name}</td>
                                                <td className="pt-4">
                                                    <Input
                                                        className="salary-input w-100"
                                                        type="text"
                                                        value={item.display_name}
                                                        onChange={e => this.handleDisNameChange(e, item)}
                                                    />
                                                </td>
                                                <td className="pt-4 pl-20">{item.team_name}</td>
                                                {/* <td>
                                                    <Select
                                                        disabled={!_.isUndefined(HF.getMasterData().pl_version) && HF.getMasterData().pl_version == 'v2' ? true : false}
                                                        isSearchable={false}
                                                        class="form-control"
                                                        options={All_Postion}
                                                        value={item.new_position || item.position}
                                                        onChange={e => this.handleChange(e, item)}
                                                    />
                                                </td> */}
                                                
                                                <td>
                                                            <Input
                                                                disabled={!_.isUndefined(HF.getMasterData().pl_version) && HF.getMasterData().pl_version == 'v2' ? true : false}
                                                                min="0" max="20" maxLength="2"
                                                                value={item.new_salary || item.salary}
                                                                className="salary-input"
                                                                style={!_.isUndefined(HF.getMasterData().pl_version) && HF.getMasterData().pl_version == 'v2' ? { boxShadow: "none",border:"none"} : {}}
                                                                type="number"
                                                                onChange={e => this.handleInputChange(e, item)}
                                                            />
                                                        </td>
                                            </tr>
                                        )
                                    })

                                }
                            </tbody>
                        </Table>
                    </Col>
                </Row>
                        :
                        <Row>
                            <Col sm="12">
                                <Table className="players-table">
                                    <thead>
                                        <tr>
                                            <th rowSpan="2" className="pl-4">
                                                {
                                                    type == "new_published" &&
                                                    <Fragment>
                                                        Add in Team
                                                        <br />
                                                        <label
                                                            className="select-all"
                                                            onChange={() => this.selectAllPlayer()}
                                                        >
                                                            <Input
                                                                className="select-all-in"
                                                                type="checkbox"
                                                                onChange={this.onChangeReturnNull}
                                                                checked={this.isPositionAllSelected(All_Postion, activeTab)}
                                                            />
                                                            <span className="ml-4">Select All</span>
                                                        </label>
                                                    </Fragment>
                                                }
                                            </th>
                                            <th>Player Name</th>
                                            <th>Display Name</th>
                                            <th className="pl-20">Team Name</th>
                                            <th>Position</th>
                                            <th>Salary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {

                                            _.map(ListArray, (item, idx) => {
                                                return (
                                                    <tr
                                                        className={(type == "is_published" && item.is_published == "1" && Roster_Data.is_published == "1") ? "already-published" : ((item.is_selected == "1") ? "active" : "")}


                                                        key={idx}>
                                                        <td className="pt-4">
                                                            <Input
                                                                disabled={HF.getMasterData().allow_network_fantasy == '1' ? true : false}
                                                                className="select-single ml-4"
                                                                type="checkbox"
                                                                checked={item.is_selected == "1"}
                                                                onChange={(e) => this.handleSelectPlayer(item, type, ListArray)}
                                                            />
                                                        </td>
                                                        <td className="pt-4">{item.full_name}</td>
                                                        <td className="pt-4">
                                                            <Input
                                                                className="salary-input w-100"
                                                                type="text"
                                                                value={item.display_name}
                                                                onChange={e => this.handleDisNameChange(e, item)}
                                                            />
                                                        </td>
                                                        <td className="pt-4 pl-20">{item.team_name}</td>
                                                        <td>
                                                            <Select
                                                                disabled={!_.isUndefined(HF.getMasterData().pl_version) && HF.getMasterData().pl_version == 'v2' ? true : false}
                                                                isSearchable={false}
                                                                class="form-control"
                                                                options={All_Postion}
                                                                value={item.new_position || item.position}
                                                                onChange={e => this.handleChange(e, item)}
                                                            />
                                                        </td>
                                                        <td>
                                                            <Input
                                                                disabled={!_.isUndefined(HF.getMasterData().pl_version) && HF.getMasterData().pl_version == 'v2' ? true : false}
                                                                min="0" max="20" maxLength="2"
                                                                value={item.new_salary || item.salary}
                                                                className="salary-input"
                                                                type="number"
                                                                onChange={e => this.handleInputChange(e, item)}
                                                            />
                                                        </td>
                                                    </tr>
                                                )
                                            })

                                        }
                                    </tbody>
                                </Table>
                            </Col>
                        </Row>
                }


            </Fragment>
        )
    }

    render() {
        let { NewPublishedList, PublishedList, UnPublishedList, Roster_Data } = this.state;
        return (
            <Fragment>
                {
                    NewPublishedList.length > 0 &&
                    <Fragment>
                        <Row>
                            <Col md={12}>
                                <div className="published-players m-style">
                                    New added player(s) from feed
                                </div>
                            </Col>
                        </Row>
                        {this.renderListComponent(NewPublishedList, "new_published")}
                    </Fragment>
                }
                {
                    PublishedList.length > 0 &&
                    <Fragment>
                        <Row>
                            <Col md={12}>
                            {Roster_Data.is_tour_game == "0" &&
                                <div className="published-players m-style">
                                    {
                                        Roster_Data.is_published == "0"
                                            ?
                                            "Dream 11 Verified Players"
                                            :
                                            "Published Players"

                                    }
                                </div>
    }
                            </Col>
                        </Row>
                        {this.renderListComponent(PublishedList, "is_published")}
                    </Fragment>
                }
                {
                    UnPublishedList.length > 0 &&
                    <Fragment>
                        <Row>
                            <Col md={12}>
                                {Roster_Data.is_tour_game == "0" &&
                                    <div className="published-players m-style">
                                        {
                                            Roster_Data.is_published == "0"
                                                ?
                                                "Feed Verified Players"
                                                :
                                                "Unpublished Players"

                                        }
                                    </div>
                                }

                            </Col>
                        </Row>
                        {this.renderListComponent(UnPublishedList, "un_published")}
                    </Fragment>
                }
            </Fragment>
        )
    }
}
export default UpdateSalaryView