import React from 'react';
import { Modal, Button, Label } from 'react-bootstrap';
import Images from '../../components/images';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as AppLabels from "../../helper/AppLabels";

export default class HowToPlayModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            show: false,
        };
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
                                    <div>
                                        <div>
                                            <Label className='how-to_play_label'>{AppLabels.MORE_ABOUT_FREE_TO_PLAY} </Label>
                                        </div>
                                    </div>
                                    <div style={{ marginTop: '15%', paddingBottom:'15%' }}>
                                        <div className='parallelogram-outer-white'>
                                            <div className='parallelogram-outer-full-white'>
                                                <div className='plan-bg'>
                                                    <Label className='text-blue'>{AppLabels.PARTICIPATE_FOR_FREE}</Label><br></br>
                                                </div>
                                                <div style={{ marginTop: '12px' }} className='plan-bg'>
                                                    <Label className='how-to-play-detail'>{AppLabels.CREATE_YOUR_TEAM_AND_JOIN}</Label><br></br>
                                                </div>
                                            </div>
                                            <img src={Images.IC1} className='ic1'></img>
                                        </div>
                                        <div className='parallelogram-outer-r-white'>
                                            <img src={Images.IC2} className='ic2'></img>
                                            <div className='parallelogram-outer-r-full-white'>

                                                <div className='parallelogram-outer-full-white'>
                                                    <div className='plan-bg-r'>
                                                        <Label className='text-blue-right'>{AppLabels.BE_PART_OF_HALL_OF_FAME}</Label><br></br>
                                                    </div>
                                                    <div style={{ marginTop: '12px' }} className='plan-bg-r'>
                                                        <Label className='create-your-team-and'>{AppLabels.JOIN_ALL_MATCHES}</Label><br></br>
                                                        {/* <Label className='create-your-team-and'>totally free </Label> */}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className='parallelogram-outer-white'>
                                            <img src={Images.IC3} className='ic3'></img>
                                            <div className='parallelogram-outer-full-white'>
                                                <div className='plan-bg'>
                                                    <Label className='text-blue'>{AppLabels.WIN_EXCITING_PRIZES_FREE_TO_PLAY}</Label><br></br>
                                                </div>
                                                <div style={{ marginTop: '20px' }} className='plan-bg'>
                                                    <Label className='how-to-play-detail'>{AppLabels.PRIZES_WILL_BE_FOR_CONTEST}</Label><br></br>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <Button onClick={() => IsCollectionInfoHide()} className=" btn-primary button-got-it"> {AppLabels.GOT_IT}</Button>

                            </Modal.Body>
                        </Modal>

                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}