
import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import ls from 'local-storage';

export default class RGIModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {

        };
    }

    goToSelfExclusion = () => {
        ls.set('RGMshow', 1)
        ls.set('showModalSequence', '')
        window.location.replace('/self-exclusion')
    }

    render() {

        const { showM, hideM } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={showM}
                        // onHide={hideM}
                        dialogClassName="custom-modal responsible-gaming-into"
                        className="center-modal"
                    >
                        <Modal.Header >
                            {/* <div className="modal-img-wrap">
                           <img src={Images.RGM_IMG} alt=""/>
                        </div> */}
                            <i className="icon-close" onClick={() => this.props.hideM()}></i>
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    <div className='inner-container-responsible'>
                                        <div className='inner-view-responsible'>
                                            <i className='icon-respo-game' />
                                        </div>
                                    </div>
                                    <h2>{AL.PLAYING_LIMIT}</h2>
                                    <div className='responsible-gaming-container'>
                                    <div className='responsible-gaming-view'>
                                    <p>{AL.RESPONSIBLE_GAMING_INTO_TEXT1}</p>
                                    <p>{AL.RESPONSIBLE_GAMING_INTO_TEXT2}</p>
                                    </div>
                                    </div>
                                    {/* <div className="text-right">
                                        <a href onClick={() => this.goToSelfExclusion()}>{AL.LEARN_MORE}</a>
                                    </div> */}
                                    <div className='responsible-button'>
                                    <button className="btn "
                                         onClick={() => this.goToSelfExclusion()}>{AL.SET_AMOUNT}
                                    </button>
                                    </div>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}