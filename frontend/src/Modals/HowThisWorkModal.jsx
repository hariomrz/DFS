import React from 'react';
import { Modal} from 'react-bootstrap';
import Images from '../components/images';
import { MyContext } from '../InitialSetup/MyProvider';
import * as AL from "../helper/AppLabels";
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import { getReferralMasterData } from '../WSHelper/WSCallings';
import { Utilities } from '../Utilities/Utilities';

export default class HowThisWorkModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            posting: false,
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

    componentDidMount() {
        if(this.props.masterData){
            this.setApiData(this.props.masterData)
        }
        else{
            this.callRFMasterDataApi();
        }
    }

    referMore = () => {
        if (!this.props.isFromRefer) {
            this.props.history.push({ pathname: "/refer-friend" , state:{ fromHTWM: true} });
        }
        this.props.mHide();


    }

    callRFMasterDataApi() {
        let param = {}
        getReferralMasterData(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let temp = responseJson.data;
                this.setApiData(temp)
            }
        })

    }

    setApiData=(temp)=>{
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

    render() {

        const { mShow, mHide, isFromRefer } = this.props;
        const { selfBonus, selfReal,slefCoins,userReal,userBonus,userCoin,valueFivethRef,valueTenRef,valueFifRef,valueFriendDeposit,masterData} = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-bg-modal custom-modal header-circular-modal affliate overflow-hidden hiw-mdl nw-hcmodal"
                        className="center-modal custom-bg-modal-dialog"
                        style={{overflow: "auto"}}
                    >
                        <a href className="close-header" onClick={() => mHide()} ><i className="icon-close"></i></a>
                        <Modal.Header style={{ paddingTop: 55 }} >
                            <div className="modal-img-wrap">
                                {/* <div className="wrap with-img">
                                    <img src={Images.REFER_TOP} alt="" />
                                </div> */}
                                <div className="wrap with-img wrap-new-img">
                                <img alt="" src={Images.TROPHY_CUP_IMG}/>
                                </div>
                            </div>
                            {/* {AL.HOW_THIS_WORKS} */}
                            {/* <div className="sub-heading">You caught us!</div> */}
                        </Modal.Header>

                        <Modal.Body>
                            <div className="ht-works-view">
                                <img alt="" src={Images.HTP_LOUD_IMG}/>
                            </div>
                            <div className="how-this-work-container">
                                 {AL.HOW_THIS_WORKS}
                            </div>
                            <div 
                            // style={{ marginBottom: 12 }}
                            className="text-cont afflicat-font afflicat-font-new">
                                {AL.HTW_TEXT1}
                                <span>
                                    {selfBonus >= slefCoins && selfBonus >= selfReal ? <i className="icon-bonus  margin-postion" /> :
                                        selfReal >= slefCoins && selfReal >= selfBonus ? <i className="  refer-s-rupee-h margin-postion" >{Utilities.getMasterData().currency_code}</i> :
                                            slefCoins >= selfReal && slefCoins >= selfBonus ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> :
                                                ''
                                    }
                                    <>
                                        {selfBonus >= selfReal && selfBonus >= slefCoins ? selfBonus :
                                            selfReal >= selfBonus && selfReal >= slefCoins ? selfReal :
                                                slefCoins >= selfBonus && slefCoins >= selfReal ? slefCoins : ''} </>
                                </span> 
                                {AL.HTW_TEXT2} 
                                {userBonus >= userCoin && userBonus >= userReal ? <i className="icon-bonus line-h-14 margin-postion" /> :
                                                userReal >= userCoin && userReal >= userBonus ? <i className="  refer-s-rupee-h margin-postion">{Utilities.getMasterData().currency_code}</i> :
                                                    userCoin >= userReal && userCoin >= userBonus ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> :
                                                        ''
                                            }
                                            <span>
                                                {userBonus >= userReal && userBonus >= userCoin ? userBonus :
                                                    userReal >= userBonus && userReal >= userCoin ? userReal :
                                                        userCoin >= userBonus && userCoin >= userReal ? userCoin : ''} </span>
                                {AL.HTW_TEXT3}
                            </div>
                            <div 
                            // style={{ marginBottom: 18 , marginTop:20}}
                             className="text-cont afflicat-font afflicat-font-new">
                                {AL.HTW_TEXT4} {valueFriendDeposit != null && valueFriendDeposit != undefined ? valueFriendDeposit.real_amount : 0}% {AL.HTW_TEXT5} (<span>{AL.UPTO} <i className=" refer-s-rupee-h">{Utilities.getMasterData().currency_code}</i>{valueFriendDeposit != undefined && valueFriendDeposit != null ? valueFriendDeposit.max_earning_amount : 0}</span>)
                            </div>
                            <div className='refer-friend-popup refer-friend-popup-new'>
                                {/* <div className='loyalty-cash'>{AL.HTW_TEXT6}</div> */}
                                {/* <div className='inner'> */}
                                    <div className='refer-labels'>
                                    <i className="icon-flash-ic"/>
                                        {AL.HTW_TEXT7} 
                                        <span>
                                            {valueFivethRef.bonus_amount >= valueFivethRef.real_amount && valueFivethRef.bonus_amount >= valueFivethRef.coin_amount ? <><i className="icon-bonus icon-bonus-new "></i> </> :
                                                valueFivethRef.real_amount >= valueFivethRef.bonus_amount && valueFivethRef.real_amount >= valueFivethRef.coin_amount ? <><i className="icon-bonus-new ">{Utilities.getMasterData().currency_code}</i> </> :
                                                    valueFivethRef.coin_amount >= valueFivethRef.bonus_amount && valueFivethRef.coin_amount >= valueFivethRef.real_amount ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> : ''}
                                            <>{valueFivethRef.bonus_amount >= valueFivethRef.real_amount && valueFivethRef.bonus_amount >= valueFivethRef.coin_amount ? valueFivethRef.bonus_amount :
                                                valueFivethRef.real_amount >= valueFivethRef.bonus_amount && valueFivethRef.real_amount >= valueFivethRef.coin_amount ? valueFivethRef.real_amount :
                                                    valueFivethRef.coin_amount}</>
                                      
                                        </span>
                                    </div>
                                    {/* <div className='seprator'></div> */}

                                {/* </div> */}
                                {/* <div className='inner'> */}
                                    <div className='refer-labels'>
                                    <i className="icon-flash-ic"/>
                                        {AL.HTW_TEXT8} 
                                        <span>
                                            {valueTenRef.bonus_amount >= valueTenRef.real_amount && valueTenRef.bonus_amount >= valueTenRef.coin_amount ? <><i className="icon-bonus  line-h-14"></i></> :
                                                valueTenRef.real_amount >= valueTenRef.bonus_amount && valueTenRef.real_amount >= valueTenRef.coin_amount ? <> <span className="icon-bonus-new " style={{lineHeight:"11px"}}>{Utilities.getMasterData().currency_code}</span> </> :
                                                    valueTenRef.coin_amount >= valueTenRef.bonus_amount && valueTenRef.coin_amount >= valueTenRef.real_amount ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> : ''}
                                            <>{valueTenRef.bonus_amount >= valueTenRef.real_amount && valueTenRef.bonus_amount >= valueTenRef.coin_amount ? valueTenRef.bonus_amount :
                                                valueTenRef.real_amount >= valueTenRef.bonus_amount && valueTenRef.real_amount >= valueTenRef.coin_amount ? valueTenRef.real_amount :
                                                    valueTenRef.coin_amount}</>
                                             
                                        </span>
                                    {/* </div> */}
                                    {/* <div className='seprator'></div> */}
                                </div>
                                {/* <div className='inner'> */}
                                    <div className='refer-labels'>
                                        <i className="icon-flash-ic"/>
                                        {AL.HTW_TEXT9} 
                                        <span>
                                            {valueFifRef.bonus_amount >= valueFifRef.real_amount && valueFifRef.bonus_amount >= valueFifRef.coin_amount ? <><i className="icon-bonus  line-h-14"></i></> :
                                                valueFifRef.real_amount >= valueFifRef.bonus_amount && valueFifRef.real_amount >= valueFifRef.coin_amount ? <> <span className="icon-bonus-new " style={{lineHeight:"11px"}}>{Utilities.getMasterData().currency_code}</span> </> :
                                                    valueFifRef.coin_amount >= valueFifRef.bonus_amount && valueFifRef.coin_amount >= valueFifRef.real_amount ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> : ''}
                                            <>{valueFifRef.bonus_amount >= valueFifRef.real_amount && valueFifRef.bonus_amount >= valueFifRef.coin_amount ? valueFifRef.bonus_amount :
                                                valueFifRef.real_amount >= valueFifRef.bonus_amount && valueFifRef.real_amount >= valueFifRef.coin_amount ? valueFifRef.real_amount :
                                                    valueFifRef.coin_amount}</>
                                            
                                        </span>
                                    {/* </div> */}

                                </div>
                            </div>
                            <div style={{ margin: "20px 20px 30px "  }} onClick={() => this.referMore()} className={'join-now'}>
                                {/* {AL.REFER_NOW} */}
                                {AL.LETS_PLAY}!
                                </div>

                            {/* <div className="MBtmImgSec">
                                <img src={Images.REFER_BOTTOM} alt="" />
                            </div> */}
                        </Modal.Body>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}