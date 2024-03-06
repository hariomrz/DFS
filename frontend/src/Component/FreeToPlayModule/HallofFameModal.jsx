import React from 'react';
import { Modal, Button, Label } from 'react-bootstrap';
import Images from '../../components/images';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as AppLabels from "../../helper/AppLabels";

export default class HallofFameModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            show: false,
        };
    }


    showLeageueDetail= (status,data, event) => {
         event.stopPropagation();
        this.props.IsCollectionInfoHide(status,data)
    }

    render() {
        const { IsCollectionInfoShow, IsCollectionInfoHide } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={IsCollectionInfoShow} onHide={() => IsCollectionInfoHide()} bsSize="large" dialogClassName="how-to-play-modal" className="center-modal">
                            <Modal.Header closeButton>
                            </Modal.Header>
                            <Modal.Body>

                                <div >
                                    <img  onClick={(event) => event.stopPropagation()} src={Images.BG_HALL_OF_FAME} className='modal-celebrate'></img>
                                    <div className="text-center">
                                        <div className="hall-of-fame-info" >
                                            <img style={{marginTop:'-15px'}} src={Images.HALL_OF_FAME_BIG_ICON} ></img>
                                            <div className="hall-of-fame-title">
                                                {AppLabels.WHAT_IS_HALL_OF_FAME} </div>
                                            <div className="hall-of-fame-detail">
                                                {AppLabels.HALL_OF_FAME_JOIN_CONTEST} </div>
                                            <div className="hall-of-sub-fame-detail">
                                                {AppLabels.LEAGUE_END} </div>
                                            <div className='level-open-right'>
                                                <img src={Images.RIGHT_SHAPE} className='bg-shape-open-right'></img>
                                                <div className='parallelogram-outer inner-d'>

                                                </div>
                                                <div className='pos-abs right-side'>
                                                    <Label style={{ color: '#333333', marginLeft: "5px" }}>{AppLabels.PLAY_THE} {this.props.item.mini_league_name} </Label>
                                                    <img hspace="15" src={Images.HOF1}></img>
                                                </div>
                                            </div>
                                            <div className='level-open-down'>
                                                <img src={Images.LEFT_SHAPE} className='bg-shape-open-left do-margin'></img>
                                                <div className='parallelogram-outer-r inner-d'>
                                                </div>
                                                <div className='pos-abs-center left-side'>
                                                    <img hspace="15" src={Images.HOF2}></img>
                                                    <Label style={{ color: '#333333', marginRight: "35px" }}>{AppLabels.HALL_OF_FAME_GATHER_POINTS} </Label>

                                                </div>
                                            </div>
                                            <div className='level-open-right-m'>
                                                <img src={Images.RIGHT_SHAPE} className='bg-shape-open-right do-margin'></img>
                                                <div className='parallelogram-outer inner-d'>

                                                </div>
                                                <div className='pos-abs right-side'>
                                                    <Label style={{ color: '#333333', marginLeft: "35px" }}>{AppLabels.HALL_OF_FAME_WIN_PRIZES} </Label>
                                                    <img hspace="15" src={Images.HOF3}></img>
                                                </div>

                                            </div>
                                        </div>

                                        <div className="league_sheduled_btn text-center mt50" onClick={(event) =>this.showLeageueDetail(true,this.props.item,event) }>
                                            <div className="button button-primary-rounded padding-more height-b">
                                                {AppLabels.HALL_OF_FAME_VIEW_SCHEDULED}</div>
                                        </div>
                                    </div>
                                </div>

                            </Modal.Body>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}