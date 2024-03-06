import React from 'react';
import { Modal} from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import ls from 'local-storage';
import Images from "../components/images";
import {DARK_THEME_ENABLE} from "../helper/Constants";
import { Utilities } from '../Utilities/Utilities';


export default class BonusCaseModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            mUserProfile: ls.get('profile'),
            isShow: true,
            isHide: false,
            isBonus: '',
            valueOfPointsRef: '',
            valueOfPointsWRef: '',
        };
    }

    onSubmit = (e) => {
        this.setState({
            isShow: false,
            isHide: true,
        }, () => {
            this.props.SkipStep();
        })
    }

    UNSAFE_componentWillMount = (e) => {
        if (this.props.data.isSkip == 0 || this.props.data.isSkip == 1) {
            let bonusValueRef = this.props.data.refData;
            let valueToShowRef;
            let valueToShowRefW;
            let toShowValueWRef;
            let toShowValueRef;

            
            if (parseInt(bonusValueRef.bonus_amount) >= parseInt(bonusValueRef.coins) && parseInt(bonusValueRef.bonus_amount) >= parseInt(bonusValueRef.real_amount)) {
                valueToShowRef = bonusValueRef.bonus_amount;
                toShowValueRef = 0;
            }
            
            if (parseInt(bonusValueRef.real_amount)>= parseInt(bonusValueRef.coins) && parseInt(bonusValueRef.real_amount)>= parseInt(bonusValueRef.bonus_amount)) {
                valueToShowRef = bonusValueRef.real_amount;
                toShowValueRef = 2;
            }

            if (parseInt(bonusValueRef.bonus_amount) <= parseInt(bonusValueRef.coins) && parseInt(bonusValueRef.real_amount) <= parseInt(bonusValueRef.coins) ) {
                valueToShowRef = bonusValueRef.coins;
                toShowValueRef = 1;
            }
  
            let bonusValueWithoutRef = this.props.data.withoutRefData;

            if (parseInt(bonusValueWithoutRef.bonus_amount) >= parseInt(bonusValueWithoutRef.coins) && parseInt(bonusValueWithoutRef.bonus_amount) >= parseInt(bonusValueWithoutRef.real_amount)) {
                valueToShowRefW = bonusValueWithoutRef.bonus_amount;
                toShowValueWRef = 0;
            }
            if (parseInt(bonusValueWithoutRef.real_amount) >= parseInt(bonusValueWithoutRef.coins )&& parseInt(bonusValueWithoutRef.real_amount) >= parseInt(bonusValueWithoutRef.bonus_amount)) {
                valueToShowRefW = bonusValueWithoutRef.real_amount;
                toShowValueWRef = 2;
            }
            if (parseInt(bonusValueWithoutRef.coins) >= parseInt(bonusValueWithoutRef.bonus_amount )&& parseInt(bonusValueWithoutRef.coins) >= parseInt(bonusValueWithoutRef.real_amount)) {
                valueToShowRefW = bonusValueWithoutRef.coins;
                toShowValueWRef = 1;
            }
            this.setState({
                isBonusValue: this.props.data,
                isBonus: this.props.data.isSkip == 1 ? valueToShowRef : valueToShowRefW,
                valueOfPointsRef: this.props.data.isSkip == 1 ? toShowValueRef : toShowValueWRef,
            })
        }else
        {
            this.setState({
                isBonusValue: this.props.data,
                isBonus: this.props.data.value,
                valueOfPointsRef: this.props.data.type,
            })
        }

    }

    render() {

        return (
            <Modal
                show={this.state.isShow}
                onHide={this.state.isHide}
                dialogClassName="custom-modal edit-input-modal edit-input-modal-lg"
                className="center-modal refer-friend "
            >
                <Modal.Header className='j-c-c d-f '>
                    <div className='pad-btm'>
                        <div className='round-area-sm'>
                            <img alt='' src={ DARK_THEME_ENABLE ? Images.DT_ADD_CASE : Images.ADD_CASE} />
                        </div>
                    </div>

                </Modal.Header>
                <Modal.Body className='pfix-modal'>
                    <div className='w-h100'>
                        <div className='inner-div-area'>
                            <span className='congratalutions-label label-black'>{AppLabels.CONGRATULATIONS}</span><br></br>
                            <div className='text-align-c'>
                                <div className='inner-div-message'>
                                    <span className='label-16'>{AppLabels.YOU_GOT_}</span>&nbsp;
                                    <span className='congratalutions-label-blue'>
                                        {
                                            this.state.valueOfPointsRef == 0 &&
                                            <i className="icon-bonus f-s-14"></i>
                                        }
                                        {
                                            this.state.valueOfPointsRef == 2 &&
                                            Utilities.getMasterData().currency_code
                                        }
                                    </span>
                                    {this.state.valueOfPointsRef == 1 ? 
                                        <img alt='' src={Images.IC_COIN} className='icon-height-is'/> : ''} <span className='mt1 congratalutions-label-blue-regular'>&nbsp;
                                        {this.state.isBonus} 
                                        {this.state.valueOfPointsRef == 2 ? AppLabels.REAL_CASH : this.state.valueOfPointsRef == 0 ? AppLabels.BONUS_CASH : AppLabels.COINS}
                                    </span>
                                </div>
                                {
                                    this.state.isBonusValue.isSkip == 0 ? <span className='label-16'>{AppLabels.ON_SIGN_UP}</span> : this.state.isBonusValue.isSkip == 1 ? <span className='label-16'>{AppLabels.BONUS_CASH_ON_SIGNINGUP_REFER_CODE}</span> : <span className='label-16'>{AppLabels.BONUS_CASH_ON_SETTING_REF_CODE} </span>
                                }

                            </div>
                        </div>
                        <div className='big-btn' onClick={() => { this.onSubmit() }}>
                            <span className='okay-btn' >{AppLabels.OK}</span>
                        </div>
                    </div>
                </Modal.Body>
            </Modal>
        );
    }
}





