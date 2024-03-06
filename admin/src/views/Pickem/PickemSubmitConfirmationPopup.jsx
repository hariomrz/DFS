import React, { Component } from "react";
import { notify } from "react-notify-toast";
import { Row, Col, Modal, ModalBody, ModalFooter, Button } from 'reactstrap';
import { _Map } from "../../helper/HelperFunction";
import * as NC from "../../helper/NetworkingConstants";
import { submitQaPickem } from "../../helper/WSCalling";

export default class PickemSubmitConfirmationPopup extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Posting: false,
        }
    }


    submitPickem = () => {
        const { liveList,tournDetails } = this.props;
        let tmpArray = []
        if(tournDetails.is_score_predict == 1 ){
            _Map(liveList, (item, idx) => {
                if (item.home_score && item.home_score != '' && item.away_score && item.away_score != '') {
                    tmpArray.push({
                        "season_id": item.season_id, "team_id": item.home_score > item.away_score ? item.home_id : item.away_id, "is_score_predict": tournDetails.is_score_predict, "away_score": item.away_score, "home_score": item.home_score
                    })
                }
            })         
        }else{
            _Map(liveList, (item, idx) => {
                if (item.user_sel_team_id && item.user_sel_team_id != '') {
                    tmpArray.push({
                        "season_id": item.season_id, "team_id": item.user_sel_team_id, "is_score_predict": tournDetails.is_score_predict,
                    })
                }
            })
        }
        let param = { "season_data": tmpArray }
        console.log('Param submitPickem', param)
        submitQaPickem(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    data: responseJson.data
                })
                
                this.props.closePickemSubmitModal();
                this.props.pickemList('1');
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })

    }



    render() {
        let { submitPickemConfirmPop } = this.props;
        return (
            <Modal
                isOpen={submitPickemConfirmPop}
                className="cancel-tour-modal"
            >
                <ModalBody>
                    <Row>
                        <Col md={12} className="cancel-wrap">
                            <p className='cancel-heading mb-0'>Are you sure you want to declare this result?</p>
                            <p className='cancel-heading-undo mb-0'>(You cannot undo this action)</p>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer mt-5">
                    <Button className="btn-secondary-outline"
                        onClick={() => this.props.closePickemSubmitModal()}>
                        No
                    </Button>
                    <Button
                        disabled={this.state.cancel_reason == ''}
                        onClick={() => this.submitPickem()}
                        className={`btn-secondary-outline yes-wd-cls`}>
                        Yes
                    </Button>
                </ModalFooter>
            </Modal>
        )
    }
}
