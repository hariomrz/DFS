import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';

export default class LeaderboardModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    gotIT = () => {
        this.props.mHide()
        if (window.location.pathname !== '/leaderboard') {
            this.props.history.push('/global-leaderboard?type=2')
        }
    }

    render() {

        const { mShow, mHide } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal leaderboard-modal"
                        className="modal-full-screen"
                    >
                        <Modal.Body>
                            <a href className="modal-close" onClick={mHide}>
                                <i className="icon-close"></i>
                            </a>
                            <div className="main-heading mt-lg-5">{AL.WHAT_IS_LEADERBOARD}</div>
                            <div className="main-sub-heading">{AL.LB_TEXT1}</div>
                            {/* <div className="img-sec">
                                <img src={Images.RFLB1} alt="" />
                            </div>
                            <div className="heading">{AL.REFERRAL}</div>
                            <div className="sub-heading">{AL.LB_TEXT2}</div> */}
                            <div className="img-sec full-w">
                                <img src={Images.RFLB2} alt="" />
                            </div>
                            <div className="heading">{AL.FANTASY_POINTS}</div>
                            <div className="sub-heading">{AL.LB_TEXT3}</div>
                            <div className="btn-sec">
                                <a onClick={this.gotIT} href className="btn btn-rounded btn-primary">{AL.GOT_IT}</a>
                            </div>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        );
    }
}