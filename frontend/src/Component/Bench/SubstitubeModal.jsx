import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';

export default class SubstitubeModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    findSubData=(data,subFor)=>{
        let obj = {};
        if(subFor == 'in'){
            obj = data.filter((obj) => { return obj.sub_in == 1 });
        }
        else{
            obj = data.filter((obj) => { return obj.status == 1 });
        }
        return obj[0];
    }

    render() {

        const { showM, hideM, data } = this.props;
        let subInObj = this.findSubData(data,'in')
        let subOutObj = this.findSubData(data,'inout')
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={showM}
                        onHide={hideM}
                        dialogClassName="custom-modal thank-you-modal substitute-modal header-circular-modal "
                        className="center-modal confirm-new"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header closeButton >
                            <div className="modal-img-wrap">
                                <div className="wrap">
                                    <i className="icon-bench-substitute"></i>
                                </div>
                            </div>
                            <div className='h2'> {AL.SUBSTITUTE} </div>
                            <div className='sub-head'> {AL.SUBSTITUTE_TEXT} </div>
                        </Modal.Header>

                        <Modal.Body>
                            <div className="subs-head">
                                <div className="subs-head-in left">
                                    <i className="icon-arrow-down"></i>
                                    {AL.SUB_OUT}
                                </div>
                                <div className="subs-head-in right">
                                    <i className="icon-arrow-up"></i>
                                    {AL.SUB_IN}
                                </div>
                            </div>
                            <div className="subs-value">
                                <div className="subs-head-in left">
                                    <img src={Utilities.playerJersyURL(subOutObj.jersey)} alt="" />
                                    <span className="player-name">{subOutObj.full_name}</span>
                                    <span className="player-abbr">{subOutObj.team_abbr}</span>
                                </div>
                                <div className="subs-head-in right">
                                    <img src={Utilities.playerJersyURL(subInObj.jersey)} alt="" />
                                    <span className="player-name">{subInObj.full_name}</span>
                                    <span className="player-abbr">{subInObj.team_abbr}</span>
                                </div>
                            </div>
                            <a className='btn btn-primary btn-rounded' onClick={hideM}>{AL.OKAY}</a>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}