import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { NavLink } from "react-router-dom";

export default class RookieContestHTP extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            answerShow: false
        };

    }

    answerShow = () => {
        const currentState = this.state.answerShow;
        this.setState({ answerShow: !currentState });
    }

    render() {

        const { mShow, mHide } = this.props;
        const { answerShow } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        bsSize="large"
                        dialogClassName="sec-inn-htp-modal rookie-contest-htp-view"
                        className=""
                    >
                        <Modal.Body>
                            <div className="header-sec">
                                <i onClick={mHide} className="icon-close" />
                                <img className='rookie-contest-img' alt=''
                                    src={Images.ROOKIE_CONTEST_IMG} />
                            </div>
                            <div className="exclusive-contest-view">
                                <div className='exclusive-text'>
                                    {AL.EXCLUSIVE_CONTEST_TEXT}
                                </div>
                                <img src={Images.ROOKIE_LINE_IMG} alt="" />
                            </div>
                            <div className='rookie-bg-img'>
                                <img alt="" src={Images.ROOKIE_LINE_BG} />
                            </div>
                            <div className="rookie-winner-view">
                                <img src={Images.ROOKIE_ARROW_VIEW} alt="" />
                                <img className='rookie-winner-img' src={Images.ROOKIE_POSITION_VIEW} alt="" />
                            </div>
                            <div className="rookie-text-view">
                                <div className="rookie-help-text">{AL.ROOKIE_HELPS_YOU_TEXT}</div>
                                <div className="rookie-compete-text">{AL.WHAT_NEW_MSG}</div>
                            </div>
                            <div className="rookie-game-img">
                                <img className='' alt="" src={Images.ROOKIE_CONTEST_VIEW} />
                            </div>
                            <div className="play-now-button">
                                <div className="button-view" onClick={mHide}>{AL.PLAY_NOW}</div>
                            </div>
                            <div className="frequently-questions-view">
                                {AL.FREQUENTLY_ASK_QUESTION}
                            </div>
                            <div className="rookie-question-view">
                                <div className="rookie-question">{AL.WHAT_IS_ROOKIE}</div>
                                <i className={answerShow ? "icon-remove" : "icon-plus-ic"} onClick={() => this.answerShow()} />
                            </div>
                            {answerShow && <div className="answer-container-rookie">
                                {AL.ROOKIE_ANSWER}
                            </div>}

                            <div className="tearms-condtions-view">
                                <NavLink exact to="/terms-condition">
                                    {AL.TERMS_CONDITION}
                                </NavLink>
                            </div>


                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        );
    }
}