import React, { lazy, Suspense } from "react";

import { Modal } from 'react-bootstrap';
import {Utilities, _isEmpty, _Map} from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from "../../components/images";


export default class LiveOverUniveralModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            userLiveOverList:this.props.userLiveOverList
        };

    }

    componentDidMount() {

    }
    goToLobby = () => {
        this.props.history.push({ pathname: '/' });
    }
    gotoMyContest = () => {
        this.props.history.push({ pathname: '/my-contests' });

    }
    gotoLiveOverScreen = (item) => {
        this.props.history.push({ pathname: '/live-fantasy-center/' + item.collection_id });

    }


    render() {
        const {userLiveOverList } = this.state;

        const { MShow,MHide } = this.props;
        
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={MShow}
                        onHide={MHide}
                        dialogClassName="custom-modal universal-lf-detail-modal header-circular-modal overflow-hidden"
                        className="center-modal"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap lf-d">
                                    {/* <i className="icon-note"></i>    */}
                                    <img style={{height:50,width:50,borderRadius:50}} src={Images.LIVE_OVER_HEAD} alt=""  />

                                </div>


                              
                                
                            </div>
                            <i onClick={()=>MHide()} style={{position:'absolute',top:5,right:5,fontSize:22,cursor:'pointer'}} className='icon-cross-circular'></i>

                            
                        </Modal.Header>

                        <Modal.Body >
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    <div style={{ marginBottom: 20 }} className="uni-lf-center-header">

                                        <div className='over-live-text'>Over Live</div>
                                        <div className='the-contests-you-joi'>The contests you joined for the following over(s) just went live</div>
                                        {
                                            userLiveOverList != undefined && !_isEmpty(userLiveOverList) &&
                                            _Map(userLiveOverList,(item,idx)=>{
                                                return(
                                                    <div className='container-live-over'>
                                                         <div className='fixture-container'>
                                                         <div className='home-away-conatiner'>
                                                         <div className='home-conatiner'>
                                                         <img src={Utilities.teamFlagURL(item.home_flag)} className='home-flag'></img>
                                                         <div className='home-away'>{item.home}</div>

                                                         </div>
                                                         <div className='vs'>{AppLabels.VS}</div>
                                                         <div className='away-conatiner'>
                                                         <div className='team-away'>{item.away}</div>
                                                         <img src={Utilities.teamFlagURL(item.away_flag)} className='away-flag'></img>

                                                         </div>

                                                         </div>
                                                         <div className='inn-over-label'>Inn {item.inning} over {item.overs}</div>

                                                         </div>
                                                         <div onClick={()=>this.gotoLiveOverScreen(item)} className='rectangle'>
                                                             <span className='span-label'>{AppLabels.PLAY_NOW}</span>
                                                         </div>

                                                    </div>

                                                )
                                               
                                            })
                                        }

                                    </div>
                                    <div className='bottom-conatiner'>
                                        <span onClick={()=>this.goToLobby()} className='span-item'>{AppLabels.GO_TO_LOBBY} </span>
                                        <div className='seprator'></div>
                                        <span onClick={()=>this.gotoMyContest()} className='span-item'>{AppLabels.MY_CONTEST}</span>

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