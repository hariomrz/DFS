import React, { Component, lazy } from 'react';
import { withRouter } from "react-router-dom";
import { socketConnect } from 'socket.io-react';
import ls from 'local-storage';
import WSManager from "../../WSHelper/WSManager";
import { getUserLiveOversLf } from "../../WSHelper/WSCallings";
import { _isEmpty } from '../../Utilities/Utilities';
import * as Constants from "../../helper/Constants";
import * as WSC from "../../WSHelper/WSConstants";
const LiveOverUniveralModal = lazy(() => import('../../Component/LiveFantasy/LiveOverUniveralModal'));
var globalThis = null;



class LiveOverSocket extends Component {
    constructor(props) {
        super(props);
        this.state = {
            userLiveOverList: [],
            showUniLf: false
        }
    }

    componentDidMount() {
        globalThis = this;
        const { socket } = this.props


        if (Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy) {
            
            if (WSManager.loggedIn() && ls.get('profile') != null) {
                let userId = ls.get('profile').user_id;
                console.log('isConnectedHeader', socket.connected);
                console.log("userid:- " + userId)
                console.log("socket",socket.id)
                socket.emit('JoinLFGame', { user_id: userId });
                socket.on('updateMatchOverLive', (obj) => {
                    console.log("updateMatchOverLive", JSON.stringify(obj))
                    if (obj != undefined) {
                        globalThis.showLiveOverPopup(obj)
                    }
                })

                socket.on('disconnect', function () {
                    let interval = null
                    let isConnected = null
                    socket.off('updateMatchOverLive')
                    interval = setInterval(() => {
                        if (isConnected) {
                            clearInterval(interval);
                            interval = null;
                            socket.emit('JoinLFGame', { user_id: userId });
                            socket.on('updateMatchOverLive', (obj) => {
                                console.log("updateMatchOverLive", JSON.stringify(obj))
                                if (obj != undefined) {
                                    globalThis.showLiveOverPopup(obj)
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
    }
    componentWillUnmount() {
        const { socket } = this.props
        if (Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy && socket) {
            socket.off('updateMatchOverLive')    
        }
        this.setState = () => {
            return;
        };
    }

    showLiveOverPopup = (obj) => {
        let isShowpopu = ls.get("isULF")
        if (!isShowpopu) {
            this.getUserLiveOvers()
        }
    }

    getUserLiveOvers = async () => {
        let param = {
            "sports_id": Constants.AppSelectedSport
        }
        getUserLiveOversLf(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ userLiveOverList: responseJson.data }, () => {
                    if (!_isEmpty(this.state.userLiveOverList)) {
                        this.openUniversalModalLF()
                    }
                })
            }
        })
    }

    openUniversalModalLF = () => {
        this.setState({
            showUniLf: true,
        });
    }

    hideUniversalModalLF = () => {
        this.setState({
            showUniLf: false,
        });
    }

    render() {
        const { userLiveOverList, showUniLf } = this.state
        return (
            showUniLf &&
            <LiveOverUniveralModal {...this.props} userLiveOverList={userLiveOverList}  MShow={showUniLf} MHide={this.hideUniversalModalLF} />
        );
    }
}

export default withRouter(socketConnect(LiveOverSocket));