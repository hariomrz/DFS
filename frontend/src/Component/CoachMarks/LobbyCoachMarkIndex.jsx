import React from 'react';
import { GameType, SELECTED_GAMET, CMStatus } from '../../helper/Constants';
import ls from 'local-storage';
import LobbyCoachMarkModal from "./CoachMarkLobby";
import MGLobbyCoachMarkModal from "./CoachMarkMGLobby";
import PickemCoachMark from "./CoachmarkPickem";
import PredictionCoachMark from "./CoachmarkPrediction";
import CoachMarkStcLobbyEqModal from "./CoachMarkStcLobbyEq";
import WSManager from "../../WSHelper/WSManager";
import CoachMarkSPLobby from './CoachMarkSPLobby';

export default class LobbyCoachMarkIndex extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            showDFSCM: false,
            showPRDCM: false,
            showMGLCM: false,
            showPCM: false,
            showSECM: false,
            showSPCM: false
        }
    }

    
    componentWillMount() {
        if(CMStatus == 2){
            if(WSManager.loggedIn()){
                this.setState({
                    showDFSCM: ls.get('coachmark-dfs') != 1,
                    showPRDCM: ls.get('coachmark-pred') != 1,
                    showMGLCM: ls.get('MGLCM') != 1,
                    showPCM: ls.get('pickem-coachmark') != 1,
                    showSECM: ls.get('stkeq-coachmark') != 1,
                    showSPCM: ls.get('sp-coachmark') != 1
                })
            }
        }
        else if(CMStatus == 1){
            this.setState({
                showDFSCM: ls.get('coachmark-dfs') != 1,
                showPRDCM: ls.get('coachmark-pred') != 1,
                showMGLCM: ls.get('MGLCM') != 1,
                showPCM: ls.get('pickem-coachmark') != 1,
                showSECM: ls.get('stkeq-coachmark') != 1,
                showSPCM:  ls.get('sp-coachmark') != 1
            })
        }
    }
    

    render() {
        return (
            <React.Fragment>
                {SELECTED_GAMET == GameType.DFS && this.state.showDFSCM && <LobbyCoachMarkModal {...this.props} cmData={{
                    mHide: () => {
                        this.setState({
                            showDFSCM: false
                        })
                    },
                    mShow: this.state.showDFSCM
                }} />}
                {SELECTED_GAMET == GameType.Pred && this.state.showPRDCM &&
                    <PredictionCoachMark cmData={{
                        mHide: () => {
                            this.setState({
                                showPRDCM: false
                            })
                        },
                        mShow: this.state.showPRDCM,
                    }} />
                }               
                {SELECTED_GAMET == GameType.MultiGame && this.state.showMGLCM &&
                    <MGLobbyCoachMarkModal cmData={{
                        mHide: () => {
                            this.setState({
                                showMGLCM: false
                            })
                        },
                        mShow: this.state.showMGLCM,
                    }} />
                }                
                {SELECTED_GAMET == GameType.Pickem && this.state.showPCM &&
                    <PickemCoachMark cmData={{
                        mHide: () => {
                            this.setState({
                                showPCM: false
                            })
                        },
                        mShow: this.state.showPCM,
                    }} />
                }
                {SELECTED_GAMET == GameType.StockFantasyEquity && this.state.showSECM &&
                    <CoachMarkStcLobbyEqModal cmData={{
                        mHide: () => {
                            this.setState({
                                showSECM: false
                            })
                        },
                        mShow: this.state.showSECM,
                    }} />
                }
                {SELECTED_GAMET == GameType.StockPredict && this.state.showSPCM &&
                    <CoachMarkSPLobby  cmData={{
                        mHide: () => {
                            this.setState({
                                showSPCM: false
                            })
                        },
                        mShow: this.state.showSPCM,
                    }} />
                }
               
            </React.Fragment>
        )
    }
}