import React, { Component } from "react";
import { Row, Col, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button } from 'reactstrap';
import * as NC from '../../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { DFST_getTournamentFixtures, DFSTR_SAVE_TOUR_FIXTURES } from '../../../helper/WSCalling';
import WSManager from "../../../helper/WSManager";
import HF, { _Map } from "../../../helper/HelperFunction";

export default class DFSTourFixtureModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Posting: false,
            fixtureList: [],
            selFixList: [],
            isMore: false
        }
    }

    componentDidMount() {
        if (this.props) {
            this.getFixtureList(this.props.data)
        }
    }

    getFixtureList = (listItem) => {
        this.setState({ Posting: true })
        let params = {
            tournament_id: listItem.tournament_id
        }
        DFST_getTournamentFixtures(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                let tmpList = []
                _Map(Response.data, (fix, indx) => {
                    if (fix.is_added == '1') {
                        tmpList.push(fix.season_id)
                    }
                })
                this.setState({
                    fixtureList: Response.data,
                    Posting: false,
                    selFixList: tmpList
                }, () => {
                    this.setState({
                        isMore: this.state.fixtureList.length == this.state.selFixList.length ? false : true
                    })
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    saveTourFixture = () => {
        this.setState({ Posting: true })
        let params = {
            tournament_id: this.props.data.tournament_id,
            season_ids: this.state.selFixList
        }
        DFSTR_SAVE_TOUR_FIXTURES(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, "success", 3000);
                this.props.hide()
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    onSelect = (e, item, idx) => {
        let tmp = this.state.fixtureList
        let tmpSelList = this.state.selFixList
        tmp[idx].is_added = item.is_added == '1' ? '0' : '1'
        tmp[idx].new_added = item.is_added == '1' ? true : false
   
        if (item.is_added == '0') {
            let index = tmpSelList.indexOf(item.season_id);
            if (index > -1) {
                tmpSelList.splice(index, 1);
            }
        }
        else {
            tmpSelList.push(item.season_id)
        }
      
        this.setState({
            fixtureList: tmp,
            isMore: true
        })
    }


    render() {
        let { show, hide, data } = this.props
        let { Posting, fixtureList, selFixList, isMore } = this.state
        let UnPubFix = parseInt(fixtureList.length || 0) - parseInt(selFixList.length || 0)
        let { int_version } = HF.getMasterData()
        return (
            <Modal
                isOpen={show}
                className="match-msg-modal fix-sel-mdl"
                toggle={hide}
            >
                <ModalHeader className="">
                    {int_version == "1" ? "Add Game" : "Add Fixture"}
                </ModalHeader>
                <ModalBody>
                    <h2>You can add more {int_version == "1" ? "games" : "fixtures"} to this tournament. <br /> {selFixList.length || 0} Published | {UnPubFix || 0} Not Published</h2>
                    <div className="list-view">
                        <div className="sel-fix-lbl">{int_version == "1" ? 'Select Games' : 'Select Fixtures'} </div>
                   
                        {
                            !Posting && fixtureList && fixtureList.length > 0 &&
                            <ul className="list-wrap">
                                {
                                    _Map(fixtureList, (match, idx) => {
                                        return (
                                            <li className={`list-item`}>
                                                <Input
                                                    disabled={match.is_added == '1' && match.new_added != true}
                                                    className="select-all-in"
                                                    type="checkbox"
                                                    onChange={(e) => this.onSelect(e, match, idx)}
                                                    checked={match.is_added == '1' ? true : false}
                                                />
                                                {
                                                    match.is_tour_game == 1 ?
                                                    <div className="team-abr">{match.tournament_name}</div>
                                                    :
                                                    <div className="team-abr">{match.home} vs {match.away}</div>
                                                }
                                                <div className="date-time">
                                                    {WSManager.getUtcToLocalFormat(match.season_scheduled_date, 'D-MMM, hh:mm A')}
                                                </div>
                                                {match.is_added == '1' && match.new_added != true ? <div className="overlay"></div> : ''}
                                            </li>
                                        )
                                    })
                                }
                            </ul>
                        }
                    </div>
                </ModalBody>
                <ModalFooter className="border-0 justify-content-center">
                    <Button
                        disabled={!isMore}
                        onClick={() => this.saveTourFixture()}
                        className="btn-secondary-outline"
                    >Save</Button>
                    <Button
                        onClick={() => this.props.hide()}
                        className="btn-secondary-outline"
                    >Cancel</Button>
                </ModalFooter>
            </Modal>
        )
    }
}