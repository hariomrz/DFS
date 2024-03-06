import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import ScratchWinModal from './ScratchWinModal';
import WSManager from '../WSHelper/WSManager';
import { GameType, SELECTED_GAMET } from '../helper/Constants';
import { _isEmpty } from '../Utilities/Utilities';
import ls from 'local-storage';
import { CommonLabels } from "../helper/AppLabels";


class Thankyou extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            showModal: false,
            showScratchWin: false,
            ActiveScratch: WSManager.getActiveScratch(),
            isH2Hmessage: WSManager.getH2hMessage(),
            isProps: this.props.isProps ? this.props.isProps : ls.get('isProps')
        };
    }

    componentDidMount() {
        if (this.state.ActiveScratch && this.state.ActiveScratch.contest_id && this.state.ActiveScratch.is_scratchwin) {
            this.setState({
                showScratchWin: true
            })
        } else {
            this.setState({
                showModal: this.props.ThankyouModalShow
            })
        }
    }

    hideScratchModal = () => {
        this.setState({
            showScratchWin: false,
        })
        setTimeout(() => {
            this.setState({
                showModal: true
            })
        }, 200);
    }

    render() {

        const { goToLobbyClickEvent, seeMyContestEvent, isDFSTour, isStock, isPickemTournament, user_team_id = false } = this.props;
        const { isProps } = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <>
                        <Modal
                            show={this.props.ThankyouModalShow}
                            dialogClassName="custom-modal thank-you-modal"
                            className="center-modal"
                        >
                            <Modal.Header>
                                <div className="header-modalbg">
                                    <i className="icon-tick-circular primary-icon"></i>
                                </div>
                            </Modal.Header>
                            <div>
                                <Modal.Body>
                                    <div className="thank-you-body">
                                        <h4>{AppLabels.JOIN_SUCCESS_TITLE}</h4>
                                        <p>
                                            {
                                                SELECTED_GAMET == GameType.PickemTournament ? '' :
                                                    SELECTED_GAMET == GameType.PropsFantasy ? user_team_id ? CommonLabels.JOIN_SUCCESS_EDIT : CommonLabels.JOIN_SUCCESS :
                                                        isStock ?
                                                            AppLabels.JOIN_PORTFOLIO_SUCCESS_MESSAGE :
                                                            this.state.isH2Hmessage && !_isEmpty(this.state.isH2Hmessage) ?
                                                                AppLabels.THANKU_MESSAGE_H2H :
                                                                AppLabels.JOIN_SUCCESS_MESSAGE
                                            }
                                        </p>
                                    </div>
                                </Modal.Body>
                                <Modal.Footer className='custom-modal-footer'>
                                    <div className="btn-grops-footer">
                                        <a onClick={() => goToLobbyClickEvent(this.state, context)}>
                                            {
                                                isProps ?
                                                    <span>{AppLabels.GO_BACK_TO_LOBBY}</span>
                                                    :
                                                    <span>{AppLabels.JOIN_MORE_POPUP} <br></br> {(isDFSTour || isPickemTournament) ? AppLabels.TOURNAMENT_POPUP : AppLabels.CONTESTS_POPUP}</span>
                                            }
                                        </a>
                                        <a onClick={() => seeMyContestEvent(this.state, context)}>
                                            {
                                                isProps ?
                                                    <span>{AppLabels.SEE_MY_CONTESTS}</span>
                                                    :
                                                    <span>{this.props.from && this.props.from == 'MyContest' ? AppLabels.DISMISS : AppLabels.SEE_MY_CONTESTS}</span>
                                            }
                                        </a>
                                    </div>
                                </Modal.Footer>
                            </div>
                        </Modal>
                        {
                            this.state.showScratchWin &&
                            <ScratchWinModal showModal={this.state.showScratchWin} hideModal={this.hideScratchModal} />
                        }
                    </>
                )}
            </MyContext.Consumer>
        );
    }
}
Thankyou.defaultProps = {
    isStock: false,
    isProps: false
}
export default Thankyou;