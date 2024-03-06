import React from 'react';
import {  Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';

export default class UnableJoinContest extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            
        };
    }

    handleClick=()=>{
        this.props.HideSuccessModal();
    }

    goToSelfExclusion=()=>{
        window.location.replace('/self-exclusion')
    }

    render() {

        const { showM,hideM } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                    show={showM}
                    // onHide={hideM}
                    dialogClassName="custom-modal header-circular-modal overflow-hidden UJContest-modal"
                    className="center-modal"
                //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                >
                    <Modal.Header >
                        <div className="modal-img-wrap">
                            <div className="wrap">
                                <i className="icon-warning"></i>   
                            </div>
                        </div>
                    </Modal.Header>

                    <Modal.Body className="static-page">
                        <div className="webcontainer-inner mt-0">  
                            <h2>{AL.UNABLE_ENTER_CONTEST}</h2>
                            <p>{AL.UNABLE_ENTER_CONTEST_DESC}</p>
                        </div>
                        <div className="text-center">
                            <a href className="btn btn-rounded btn-primary" onClick={()=>this.props.hideM()}>fghgg</a>
                        </div>
                        <div className="text-center">
                            <a href className="anchor-text" onClick={()=>this.goToSelfExclusion()}>{AL.SEE_MY_NEW_LIMIT}</a>
                        </div>
                    </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}