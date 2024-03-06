import React from 'react';
import { Modal, Button, FormGroup, Row, Col } from 'react-bootstrap';
import { MyContext } from "../../InitialSetup/MyProvider";
import { _Map ,Utilities} from '../../Utilities/Utilities';
import * as AppLabels from "../../helper/AppLabels";

export default class SkipConfirmationModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {

        }
    }

    render() {
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                    show={this.props.isShow}
                    onHide={this.props.isHide}
                    dialogClassName="custom-modal skip-confirmation"
                    className="center-modal"
                    >
                        <Modal.Header >
                                <span className='join-title'>{AppLabels.JOIN_CONTEST}</span>
                                <div className='msg'>
                                    <div>{AppLabels.JOIN_SKIP_MSG1}</div>
                                    <div>{AppLabels.JOIN_SKIP_MSG2}</div>
                                </div>
                                <div onClick={()=>this.props.onJoinClick()} className='join-now-btn'><span> {AppLabels.CONTEST_JOIN_NOW}</span></div>
                                <div onClick={()=>this.props.onJoinLaterClick()} className='join-later'><span>{AppLabels.JOIN_LATER}</span></div>
                        </Modal.Header>
                       
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}