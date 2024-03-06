import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';

export default class H2HOpponentDetailModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {};

    }

    componentDidMount() {

    }
    calculateWinPercentage =()=>{
        const {opponentData } = this.props;
        let winP = 0;
        if(opponentData.total != '0'){
            let win = parseFloat(opponentData.win)*100;
            winP = win / parseFloat(opponentData.total);
        }
        console.log("winP",Math.round(winP))

       
        return Math.round(winP) || 0
    }


    render() {

        const { MShow,MHide,opponentData } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={MShow}
                        onHide={MHide}
                        dialogClassName="custom-modal opponents-modal header-circular-modal overflow-hidden"
                        className="center-modal"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header >
                            {/* <i onClick={()=> MHide()} className="icon-close"></i>    */}
                            <div className='profile-conatiner'>
                                <div className='bg-opp-image'>
                                    <img src={opponentData && opponentData.image ? Utilities.getThumbURL(opponentData.image) : Images.USER_OPP} className='image-opp'></img>

                                </div>
                                <div className='name-opp'>{opponentData.user_name} </div>

                            </div>

                        </Modal.Header>

                        <Modal.Body >
                            <React.Fragment>
                                <div className="webcontainer-inner">
                                    <div className="challange-played">
                                        <i className="icon-badge-icon icon-vs-ic"></i>
                                        <div className="right-container">
                                            <div className="value-played">{opponentData.total}</div>
                                            <div className="value-label">{AppLabels.H2H_CHALLANGE_PLAYED}</div>

                                        </div>

                                    </div>
                                    <div className="challange-played">
                                        <i className="icon-badge-icon icon-trophy2-ic"></i>
                                        <div className="right-container">
                                            <div className="value-played">{opponentData.win}</div>
                                            <div className="value-label">{AppLabels.H2H_CHALLANGE_WON}</div>

                                        </div>

                                    </div>
                                    <div className="challange-played">
                                        <i className="icon-badge-icon icon-badge"></i>
                                        <div className="right-container">
                                            <div className="value-played">{this.calculateWinPercentage()}</div>
                                            <div className="value-label">{AppLabels.WINNING_PERCENTAGE}</div>

                                        </div>

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