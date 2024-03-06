import React, {lazy, Suspense} from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../InitialSetup/MyProvider';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import ls from 'local-storage';
import { getReferralMasterData } from '../WSHelper/WSCallings';
const ReferralSysComponent =  lazy(()=>import('../Component/CustomComponent/ReferralSysComponent'));
export default class ReferralSystem extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            showCheckbox: false,
            masterData: '',
            selfBonus: 0,
            selfReal: 0,
            slefCoins: 0,
            userBonus: 0,
            userReal: 0,
            userCoin: 0,
            valueFivethRef: 0,
            valueTenRef: 0,
            valueFifRef: 0,
            valueFriendDeposit: [],
            profileDetail: WSManager.getProfile(),
            showCheck: false
        };
    }


    UNSAFE_componentWillMount() {
        this.callRFMasterDataApi();
    }

    callRFMasterDataApi() {
        let param = {}
        getReferralMasterData(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let temp = responseJson.data;
                this.setState({
                    masterData: temp,
                    valueFivethRef: temp[17],
                    valueTenRef: temp[18],
                    valueFifRef: temp[19],
                    valueFriendDeposit: temp[12],
                }, () => {
                    this.setState({
                        selfReal: parseInt(temp[0].real_amount),
                        selfBonus: parseInt(temp[0].bonus_amount),
                        slefCoins: parseInt(temp[0].coin_amount),
                        userReal: parseInt(temp[0].user_real),
                        userBonus: parseInt(temp[0].user_bonus),
                        userCoin: parseInt(temp[0].user_coin),

                    })
                })
            }
        })

    }

    openEditRefCode = (e) => {
        this.props.IsModalHide(this.state.showCheckbox);
    }

    dontShowAgain=()=>{
        this.setState({
            showCheckbox: !this.state.showCheckbox
        })
    }


    render() {
        const { IsModalShow, IsModalHide } = this.props;
        let showCheck = ls.get("isShowPopup") || this.state.showCheck;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={IsModalShow}
                        onHide={IsModalHide}
                        dialogClassName="banner-modal-refer modal-full-screen"
                    >
                        <Modal.Body>
                        <Suspense fallback={<div />} ><ReferralSysComponent isModal={true} showCheck = {showCheck} dontShowAgain={this.dontShowAgain} {...this.state} goBack={()=>{}} openEditRefCode={this.openEditRefCode} /></Suspense>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}