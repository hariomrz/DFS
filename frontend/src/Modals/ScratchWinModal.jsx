import React from 'react';
import { Modal } from 'react-bootstrap';
import { getRandomScratchCard, claimScratchCard } from "../WSHelper/WSCallings";
import { Utilities } from '../Utilities/Utilities';
import Images from '../components/images';
import ScratchCard from '../Component/CustomComponent/ScratchCard';
import Particles from '../Component/CustomComponent/Particles';
import WSManager from '../WSHelper/WSManager';
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import CustomLoader from '../helper/CustomLoader';

class ScratchWinModal extends React.Component {
    static id = 1;
    constructor(props) {
        super(props);
        this.state = {
            ActiveScratch: WSManager.getActiveScratch(),
            scratchStart: false,
            scratchComplete: false,
            particles: [],
            SCData: '',
            encodeData: '',
            isLoading: true
        };
    }

    componentDidMount() {
        let param = {
            contest_id: this.state.ActiveScratch.contest_id
        }
        getRandomScratchCard(param).then((response) => {
            this.setState({
                isLoading: false
            });
            if (response.data) {
                let decodeStr = atob(response.data);
                let sepAry = decodeStr.split('_')
                this.setState({
                    encodeData: response.data,
                    SCData: {
                        prize_type: sepAry.length > 0 ? sepAry[0] : '',
                        user_id: sepAry.length > 1 ? sepAry[1] : '',
                        scratch_card_id: sepAry.length > 2 ? sepAry[2] : '',
                        amount: sepAry.length > 3 ? sepAry[3] : '',
                    }
                });
            } else {
                WSManager.setActiveScratch({})
                this.props.hideModal()
            }
        })
    }

    clean(id) {
        this.setState({
            particles: this.state.particles.filter(_id => _id !== id)
        });
    }

    handleOnClick = () => {
        const id = ScratchWinModal.id;
        ScratchWinModal.id++;

        this.setState({
            particles: [...this.state.particles, id]
        });
        setTimeout(() => {
            this.clean(id);
        }, 5000);
    }

    onCompleteScratch = () => {
        this.setState({
            scratchComplete: true
        })
        let param = {
            contest_id: this.state.ActiveScratch.contest_id,
            scratch_card_id: this.state.SCData.scratch_card_id,
            prize_data: this.state.encodeData
        }
        if (this.state.SCData.amount > 0) {
            this.handleOnClick()
            claimScratchCard(param).then((response) => {
                WSManager.setActiveScratch({})
            })
        } else {
            WSManager.setActiveScratch({})
        }
    }

    hideModal = (scratchComplete) => {
        if (scratchComplete) {
            this.props.hideModal()
        }
    }

    render() {

        const { showModal } = this.props;
        const { scratchComplete, scratchStart, particles, SCData, encodeData, isLoading } = this.state;
        const settings = {
            width: 315,
            height: 350,
            image: Images.SCRATHC_COVER,
            brush: Images.BRUSH,
            finishPercent: 50,
            onStart: () => this.setState({ scratchStart: true }),
            onComplete: this.onCompleteScratch
        };
        return (
            (!encodeData || isLoading) ?
                <CustomLoader />
                :
                <Modal
                    show={showModal}
                    onHide={() => this.hideModal(scratchComplete)}
                    className="center-modal scratch-win-m particles"
                >
                    <Modal.Body>
                        <div className="scratch-view">
                            <ScratchCard {...settings} >
                                <div className="result-inner">
                                    <img src={Images.CELE} alt='' className="img_bg" />
                                    <div className="container">
                                        <img src={Images.TROPHY} alt='' className="trophy_img_bg" />
                                        {
                                            SCData.amount > 0 ? <div>
                                                <span className="won-msg">
                                                    {AppLabels.YOU_HAVE_WON}
                                                </span>
                                                <span className="won-amount">

                                                    {
                                                        SCData.prize_type == 1 ? Utilities.getMasterData().currency_code : SCData.prize_type == 0 ? <i className="icon-bonus" /> : <img className="coin-img" src={Images.IC_COIN} alt="" />
                                                    } {SCData.amount}
                                                </span>
                                            </div>
                                                :
                                                <span className="won-msg">
                                                    {AppLabels.BETTER_LUCK}
                                                </span>
                                        }
                                    </div>
                                </div>
                            </ScratchCard>
                        </div>
                        <div className="win-upto">
                            {!scratchStart && <div className="msg">{AppLabels.SWIPE_BACK}</div>}
                            <img alt='' src={Images.SCRATCH_SHAPE} />
                            {!scratchComplete && <div className="win-title">{AppLabels.SURPRISE_WAY}</div>}
                            {scratchComplete && SCData.amount <= 0 && <div className="win-from">{AppLabels.JOIN_MORE_SCRATCH}</div>}
                            {scratchComplete && SCData.amount > 0 && <div className="win-from">{AppLabels.EXP_PAYMENT_IN}</div>}
                            <div className="win-from">{AppLabels.FROM} {WSC.AppName} {AppLabels.Team}</div>
                        </div>
                    </Modal.Body>
                    {particles.map(id => (
                        <Particles key={id} count={Math.floor(window.innerWidth / 5)} />
                    ))}
                </Modal>
        );
    }
}
export default ScratchWinModal;