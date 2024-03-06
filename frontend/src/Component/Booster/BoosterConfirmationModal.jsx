import React from 'react';
import { Modal } from 'react-bootstrap';
import {_Map, Utilities} from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';


export default class BoosterConfirmationModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
           
        };

    }

    componentDidMount() {
        
    }
    render() {

        const { MShow,MHide,boosterList,selectedBoosterId ,confirmBooster} = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={MShow}
                        onHide={MHide}
                        dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden"
                        className="center-modal"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap">
                                    <img style={{ height: 32,width: 32}} src={Images.BOOSTER_ICON}></img>   
                                </div>
                            </div>
                            {AppLabels.BOOSTER_CONFIRMATION}
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    {
                                        boosterList && boosterList.map((item, key) => {
                                            return (
                                                item.booster_id == selectedBoosterId ?
                                                <div className="container-booster">
                                                    <div className="booster-assset">
                                                        <img src={item.image_name != '' && item.image_name != undefined ? Utilities.getBoosterLogo(item.image_name) : Images.BOOSTER_STRAIGHT} className="bitmap-copy" onClick={(e) => e.stopPropagation()} />
                                                        <div className="booster-deatils">
                                                            <div className="booster-name ">{item.name}  </div>
                                                            <div className="for-every-four-score">{AppLabels.FOR_EVERY + " " + item.name + " " + AppLabels.SCORED + AppLabels.GET + " " + parseFloat(item.points).toFixed(1) + "x " + AppLabels.POINTS_EXTRA}</div>


                                                        </div>
                                                    </div>
                                                        <div className="pos-applied-name">
                                                            <div className="applied">
                                                                <i className="icon-tick-ic"></i>
                                                                {AppLabels.APPLIED}
                                                            </div>
                                                            <div className="poition-name">{item.position}
                                                            </div>

                                                        </div>
                                                </div>
                                                :''
                                            )

                                        })
                                    }
                                    <div onClick={confirmBooster}  className="save-booster">
                                        <div className="confirm-btn">
                                            <div className="confirm-text">
                                                {AppLabels.CONFIRM}
                                            </div>
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