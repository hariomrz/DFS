import React, {lazy, Suspense} from 'react';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import { getReferralMasterData } from '../WSHelper/WSCallings';
const ReferralSysComponent =  lazy(()=>import('../Component/CustomComponent/ReferralSysComponent'));
export default class ReferralSystem extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
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

    componentWillUnmount() {

    }

    goBack = (e) => {
        this.props.history.goBack();
    }

    openEditRefCode = (e) => {
        if (this.state.profileDetail.is_rc_edit == 1) {
            this.goBack();
            return;
        }
        let passingData = this.state.masterData[16]
        this.props.history.push('/edit-referral-code', passingData);
    }

    render() {

        return <Suspense fallback={<div />} ><ReferralSysComponent {...this.state} goBack={this.goBack} openEditRefCode={this.openEditRefCode} /></Suspense>
    }
}