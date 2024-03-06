import React from 'react';
import {  Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map } from "Utilities/Utilities";
import * as AppLabels from "helper/AppLabels";
import { MyContext } from 'InitialSetup/MyProvider';
import {Sports} from "JsonFiles";
import { AppSelectedSport } from 'helper/Constants';
import { getRulePageData } from 'WSHelper/WSCallings';
import { Images } from 'OpinionTrade/Lib';

export default class OpinionTradeSucess extends React.Component {
  
    render() {

        const { MShow,MHide,isDetails} = this.props;
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
                                <div className="wrap-icon">
                                    <img style={{marginTop:'-3px',marginLeft:'-3px'}} alt='' src={Images.ICON_TRADE}/> 
                                </div>
                            </div>
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                            <div className="webcontainer-inner mt-0">  
                            <div className="page-body rules-scoring-body p-0">
                                    <React.Fragment>
                                       <div className="pick-fantasy-rules-view">
                                            <div className='txt-trade-order'>Trade Order Placed!</div>
                                            <div className='txt-trade-desc'>Check question details to view order status</div>
                                            <div onClick={()=>MHide(true)} className='btn-trade'>
                                                <div className='txt-btn-trade'>{isDetails?"Done":"Go to Question Details"}</div>
                                            </div>
                                       </div>
                                    </React.Fragment>
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