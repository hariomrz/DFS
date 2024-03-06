import React from 'react';
import { Modal, FormGroup, Row, Col } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import FloatingLabel from 'floating-label-react';
import { inputStyleLeft, darkInputStyleLeft } from '../helper/input-style';
import { Utilities } from '../Utilities/Utilities';
import { validateFundPromo } from '../WSHelper/WSCallings';
import * as WSC from "../WSHelper/WSConstants";
import {DARK_THEME_ENABLE} from "../helper/Constants";

export default class ApplyPromoCode extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            mPromoCode: '',
            showError: false,
            isLoading: false
        };
    }

    applyPromo() {
        if (this.state.mPromoCode && this.state.mPromoCode.trim() != '') {
            if (!this.state.isLoading) {
                this.setState({
                    isLoading: true
                })
                let param = {
                    "amount": this.props.mAmount,
                    "promo_code": this.state.mPromoCode
                }

                validateFundPromo(param).then((responseJson) => {
                    this.setState({
                        isLoading: false
                    })
                    if (responseJson.response_code == WSC.successCode) {
                        this.props.onApplyPromoCode(responseJson.data)
                        if (responseJson.data.cash_type == 0) {
                            this.setState({
                                promocodeDiscountAmt: responseJson.data.discount
                            })
                            this.props.onApplyPromoCode(responseJson.data)
                        } else if (responseJson.data.cash_type == 1) {
                            this.setState({
                                promocodeDiscountAmt: responseJson.data.discount
                            })
                        }
                        this.setState({
                            promoCodeData: responseJson.data,
                            discountPercent: responseJson.data.discount,
                            benefitCap: responseJson.data.benefit_cap,
                            isDisabled: true,
                            showError: false
                        })
                    } else {
                        this.setState({
                            showError: true,
                            promoCodeErrorMsg: responseJson.message
                        })
                    }
                })
            }
        }
        else {
            Utilities.showToast(AppLabels.ENTER_PROMO_CODE, 2000)
        }
    }

    handleChange = (e) => {
        this.setState({ mPromoCode: e.target.value })
    }

    removePromoText=()=>{
        this.setState({mPromoCode: null,showError: false},
            ()=>{
                this.setState({mPromoCode: ''})
            }
        )
    }

    render() {

        const { IsPromoCodeShow, IsPromoCodeHide } = this.props;
        const { mPromoCode } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={IsPromoCodeShow}
                        onHide={IsPromoCodeHide}
                        dialogClassName="custom-modal edit-input-modal"
                        className="center-modal"
                    >
                        <Modal.Header>
                            <div className="icon-section">
                                <i className="icon-promocode"></i>
                            </div>
                            <h2>{AppLabels.PROMO_CODE}</h2>
                        </Modal.Header>
                        <Modal.Body>
                            <div className="edit-input-form">
                                <Row>
                                    <Col xs={12} className={"input-label-spacing" + (this.state.showError ? ' show-error-msg' : '')}>
                                        <FormGroup
                                            className={'input-label-center input-transparent '}
                                            controlId="formBasicText">
                                            {mPromoCode !== null &&
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                    id='panno'
                                                    name='panno'                                            
                                                    placeholder={AppLabels.ENTER_PROMO_CODE}
                                                    type='text'
                                                    onChange={this.handleChange}
                                                    value={mPromoCode}
                                                />
                                            }
                                        </FormGroup>
                                        <span className="bordered-span"></span>
                                        {mPromoCode != ''  &&
                                            <i className="icon-cross-circular remove-applied-code" onClick={()=>this.removePromoText()}></i>
                                        }
                                        {this.state.showError &&
                                            <div className="error-text text-left">{AppLabels.INVALID_PROMOCODE}</div>
                                        }
                                    </Col>
                                </Row>
                                <a onClick={() => this.applyPromo()} href className="button button-primary button-block btm-fixed">{AppLabels.APPLY}</a>
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}