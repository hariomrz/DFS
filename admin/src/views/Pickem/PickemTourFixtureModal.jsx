import React, { Component } from "react";
import { Row, Col, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { DFST_getTournamentFixtures } from '../../helper/WSCalling';
import WSManager from "../../helper/WSManager";
import HF, { _Map } from "../../helper/HelperFunction";
import { getPickemSaveTournamentFixtures, getPickemGetTournamentFixtures } from "../../helper/WSCalling";

export default class PickemTourFixtureModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Posting: false,
            fixtureList: [],
            selFixList: [],
            isMore: false,
            seasonIDs: [],
            addedFixtures: '',
            newSelFix: []
        }
    }

    componentDidMount() {
        if (this.props) {
            this.getFixtureList(this.props.l_id)
        }
    }

    getFixtureList = (listItem) => {
        this.setState({ Posting: true })
        let params = {
            tournament_id: listItem
        }
        getPickemGetTournamentFixtures(params).then(Response => {
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
                    selFixList: tmpList,
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


    onSelect = (e, item, idx) => {
        // console.log(item)
        // let tmpSelList = []
        // tmpSelList.push(...this.state.seasonIDs, item.season_id)
        // this.setState({
        //     seasonIDs: tmpSelList,
        // })
        // console.log(tmpSelList)

        let tmp = this.state.fixtureList
        let tmpSelList = this.state.newSelFix
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
        // console.log('tmpSelList', tmpSelList)
        this.setState({
            fixtureList: tmp,
            isMore: true,
            newSelFix: tmpSelList
        })
    }


    saveTourFixture = () => {
        this.setState({ Posting: true })
        let params = {
            tournament_id: this.props.l_id,
            season_ids: this.state.newSelFix
        }
        // console.log('first params',params)
        getPickemSaveTournamentFixtures(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, "success", 3000);
                this.getFixtureList(this.props.l_id)

                this.props.hidePickemTourFixture()
            } else {
                notify.show(Response.message, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
        // this.props.PickemList()

    }

    render() {
        let { editFixture, hide, data } = this.props
        let { Posting, fixtureList, selFixList, isMore,newSelFix } = this.state
        let totalSelFix = parseInt(newSelFix.length || 0) + parseInt(selFixList.length || 0)
        let UnPubFix = parseInt(fixtureList.length || 0) - parseInt(totalSelFix)
        return ( 
            <Modal
                isOpen={editFixture}
                className="match-msg-modal fix-sel-mdl"
                toggle={hide}
            >
                <ModalHeader className="">
                    Add Fixture
                </ModalHeader>
                <ModalBody>
                    <h2>You can add more fixtures to this tournament. <br /> {totalSelFix || 0} Published | {UnPubFix || 0} Not Published</h2>
                    <div className="list-view">
                        <div className="sel-fix-lbl">Select Fixtures </div>
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
                                                <div className="team-abr">{match.home} vs {match.away}</div>
                                                <div className="date-time">
                                                    {/* {WSManager.getUtcToLocalFormat(match.scheduled_date, 'D-MMM, hh:mm A')} */}
                                                    {HF.getFormatedDateTime(match.scheduled_date, 'D-MMM, hh:mm A')}

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
                        onClick={() => this.props.hidePickemTourFixture()}
                        className="btn-secondary-outline"
                    >Cancel</Button>
                </ModalFooter>
            </Modal>
        )
    }
}
