import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';

export default class QuizModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }


    /**
     * WELCOME POPUP 
     */

    _welComePopupUI = () => {
        return <div className='welcome-holder'>
            <div className='inner-holder-2'>
                {/* <div className='temp-holder'>
                    <div className='quiz-icon-holder'>
                        <img src={Images.QUIZ_ICON} />
                    </div>
                </div> */}

                {/* <div className='temp-holder mt25'>
                    <img src={Images.QUIZ_MG} className='big-img' />
                </div> */}
                <div className='daily-quiz-titile'>
                    {AL.DAILY_QUIZ}
                </div>
                <div className='sub-text'>
                    <div className='sub-text-inner'>
                   <div className='display-quiz-flex'> <i className='icon-flash-ic'/> <span className='sub-text-p'> {AL.CLAIM_DAILY_QUIZ_REWARDS}</span></div>
                   <div className='display-quiz-flex mt20'> <i className='icon-flash-ic'/><span className='sub-text-p'>{AL.GET_LIMITED_TIME_TO_ANS}</span></div>
                   <div className='display-quiz-flex mt20'> <i className='icon-flash-ic'/> <span className='sub-text-p '>{AL.EARN_FOR_EVERY_CRT_ANS}</span></div>

                    </div>
                </div>
                <div className='lets-play' onClick={this.props.Action}>
                    <div className="button button-primary-white padding-more isbtn">{AL.LETS_PLAY}!</div>
                </div>
            </div>
        </div>
    }

    render() {

        const { isShow, isHide } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isShow}
                        onHide={isHide}
                        dialogClassName="custom-bg-modal custom-modal header-circular-modal overflow-hidden quiz-modal-custom nw-hcmodal"
                        className="center-modal custom-bg-modal-dialog"
                        // className="modal-full-screen quiz-modal-custom"
                    >
                        <a href className="close-header" onClick={isHide}><i className="icon-close"></i></a>
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap with-img">
                                    <img src={Images.QUIZ_ICON_IMG} alt="" /> 
                                </div>
                            </div>
                            {/* Hey! */}
                            <div className='temp-holder '>
                                <img src={Images.QUIZ_IMG} className='big-img big-img-view' />
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            {
                                this._welComePopupUI()
                            }
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        );
    }
}