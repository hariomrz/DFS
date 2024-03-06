import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import ls from 'local-storage';
import * as WSC from "../../WSHelper/WSConstants";
import WSManager from '../../WSHelper/WSManager';
import { Utilities } from '../../Utilities/Utilities';
import { socketConnect } from 'socket.io-react';
var globalThis = null;


class LFWaitingModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }
    componentWillUnmount() {  
        const { socket } = this.props
        socket.off('updateMatchOverStatus')   
    }

    componentDidMount() {
        const { socket } = this.props
        ls.set("isULF", true)
        globalThis = this;

        this._isMounted = true;
        let userId = ls.get('profile').user_id;
        let collection_id = this.props.collection_id ? this.props.collection_id : this.props.OverData.collection_id
        console.log("collection_id :-", collection_id + " userid:- " + userId)

            console.log('isConnected', socket.connected);
            if (WSManager.loggedIn()) {
                socket.emit('JoinMatchLF', { collection_id: collection_id, user_id: userId });
                socket.on('updateMatchOverStatus', (obj) => {
                    console.log("updateMatchOverStatus", JSON.stringify(obj))
                    if (obj != undefined) {
                        globalThis.gotoLiveMatchOver(obj)

                    }
                })

                socket.on('disconnect', function () {
                    let interval = null
                    let isConnected = null
                    socket.off('updateMatchOverStatus')
                    interval = setInterval(() => {
                        if (isConnected) {
                            clearInterval(interval);
                            interval = null;
                            socket.emit('JoinMatchLF', { collection_id: collection_id, user_id: userId });
                            socket.on('updateMatchOverStatus', (obj) => {
                                console.log("updateMatchOverStatus", JSON.stringify(obj))
                                if (obj != undefined) {
                                    globalThis.gotoLiveMatchOver(obj)

                                }
                            })
                            return;
                        }
                        isConnected = socket.connected;
                        socket.connect();
                    }, 500)
                });


            }
    }
    gotoLiveMatchOver = (obj)=>{
        if(obj.status == 1){
            this.props.hide(obj.status)
        }
  

    }
    componentWillMount() {
        const { socket } = this.props
        this._isMounted = false;
        socket.off('updateMatchOverStatus')
    }

    render() {

        const { show, hide,OverData,LobyyData } = this.props;
        let match_over = OverData.over ? OverData.over : OverData.overs ? OverData.overs : LobyyData.overs ;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={show}
                        dialogClassName="custom-modal waiting-screen"
                        className="center-modal"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header >
                            <div className="over-dtl">
                                <a href className="mdl-back">
                                    <i onClick={()=>hide()} className="icon-left-arrow"></i>
                                </a>
                                {AL.OVER} {match_over} 
                                {/* <span>{AL.UP_NXT_OVR} 2 >></span> */}
                            </div>
                        </Modal.Header>

                        <Modal.Body>
                            <div className="ws-body">
                                <p className="grab-text">{AL.GRAP_TEXT}</p>
                               
                                <div  className="img-sc">
                                    <img src={Images.LF_WAITING_IMG} alt="" />
                                </div>
                                <div className="tips-sc">
                                    <img src={Images.BULB} alt="" />
                                    <h5>{AL.TIPS}</h5>
                                    <p>{AL.TIPS_TEXT}</p>
                                </div>
                                <div className="rules-sc">
                                    <h5>{AL.RULES} -</h5>
                                    <div className="lbl">{AL.RULES_DESC_LF.replace('10', Utilities.getMasterData().lf_predict_time)}</div>
                                    <ul>
                                        <li>- {AL.LF_WAITING_SC_TXT1}</li>
                                        <li>- {AL.LF_WAITING_SC_TXT2}</li>
                                        <li>- {AL.LF_WAITING_SC_TXT1}</li>
                                        <li>- {AL.LF_WAITING_SC_TXT2}</li>
                                    </ul>
                                </div>
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}
export default socketConnect(LFWaitingModal)