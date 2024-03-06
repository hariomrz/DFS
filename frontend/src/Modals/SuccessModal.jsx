import React from 'react';
import {  Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';

export default class SuccessModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            
        };
    }

    handleClick=()=>{
        this.props.HideSuccessModal();
    }

    render() {

        const { IsSuccessModalShow} = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={IsSuccessModalShow}
                        dialogClassName="custom-modal thank-you-modal link-send-success-modal" 
                        className="center-modal"
                    >
                        <Modal.Header closeButton>
                            <div className="header-modalbg">
                                <i className="icon-tick-circular primary-icon"></i>
                            </div>
                        </Modal.Header>
                        <div>
                            <Modal.Body>
                                <div className="thank-you-body">
                                    <h4 className="text-uppercase text-bold">{AppLabels.SUCCESS}</h4>
                                    <p>{AppLabels.EMAIL_SEND_SUCCESS_MESSAGE}</p>
                                    {/* <p>Please check your email to reset password.</p> */}
                                </div>
                            </Modal.Body>
                            <Modal.Footer className='custom-modal-footer overflow'>
                                    <a 
                                        href
                                        onClick={()=>this.handleClick()}
                                        className="btn-single"
                                        // onClick={() => this.props.history.push('/password')}
                                    >
                                        <span>{AppLabels.OK}</span>
                                    </a>
                            </Modal.Footer>
                        </div>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}