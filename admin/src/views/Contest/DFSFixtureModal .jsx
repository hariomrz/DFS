import React, { Component } from "react";
import { Row, Col, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { DFST_getTourFixtures, DFST_SAVE_CONTEST_TOURNAMENT } from '../../helper/WSCalling';
import WSManager from "../../helper/WSManager";
import HF, { _Map } from "../../helper/HelperFunction";

export default class DFSFixtureModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Posting: false,
            fixtureList: [],
            selFixList: [],
            isMore: false,
            contestId : this.props.ContestData.contest_id
        }
    }

    componentDidMount() {
        console.log(this.props,'jjjjkj')
        if (this.props) {
            this.getFixtureList(this.props.data)
        }
        // console.log(this.props,'sssss')this.props
    }

    getFixtureList = (listItem) => {

        // console.log(listItem); 
        this.setState({ Posting: true })
        let params = {
            collection_master_id: listItem
        }
        DFST_getTourFixtures(params).then(Response => {
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

    onSelect = (e, item, idx) => {
        // console.log(item,'nilesh');
        let tmp = this.state.fixtureList
        let tmpSelList = this.state.selFixList
        tmp[idx].is_added = item.is_added == '1' ? '0' : '1'
        tmp[idx].new_added = item.is_added == '1' ? true : false
   
        if (item.contest_id > '0') {
            // console.log('kkkk')
            let index = tmpSelList.indexOf(item.tournament_id);

            console.log(index, 'ttttt')
            if (index > -1) {
                tmpSelList.splice(index, 1);
            }
        }
        else {
            tmpSelList.push(item.tournament_id)
        }
      
        this.setState({
            fixtureList: tmp,
            isMore: false
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

                    {
                    // console.log(fixtureList)
                    
                    
                    }
                     <h2>This contest is avilable for the following tournaments </h2> 
                    {/* <p style={{fontSize:"20px",textAlign:"center"}}> Tournaments </p> */}
                    {/* <p  style={{fontSize:"15px",textAlign:"center"}}> This contest is avilable for the following tournaments </p> */}
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
                                                    disabled={match.contest_id > '0'}
                                                    className="select-all-in"
                                                    type="checkbox"
                                                    onChange={(e) => this.onSelect(e, match, idx)}
                                                    // checked={match.contest_id > '0' ? true : false}
                                                />
                                                {
                                                    <div className="team-abr">{match.name}</div>
                                                    
                                                }
                                               
                                                {match.contest_id > '0' ? <div className="overlay"></div> : ''}
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
                        onClick={() => this.props.saveFn(this.props.collection_master_id,this.state.selFixList,this.props.ContestData.contest_id)}
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