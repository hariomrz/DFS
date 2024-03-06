import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import * as CONSTANTS from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';
import Images from '../../components/images';
var globalThis = null;

class QrCodeCryptoModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            posting: false,
            status: this.props.preData.status ? this.props.preData.status : 0
        }
    }
    componentDidMount() {
        globalThis = this;

    }

    renderTagMessage = rowData => {
        let msg = rowData;
        let cryptoData = this.props.preData.cryptoData;

        msg = msg.replace(AL.IMPORTANT_NOTICE, '<span class="highlighted-text imortant-notice">' + AL.IMPORTANT_NOTICE + '</span>');

        msg = msg.replace(cryptoData.deposit_to_addr, '<span class="highlighted-text">' + cryptoData.deposit_to_addr + '</span>');
        msg = msg.replace(cryptoData.deposit_crypto_amt, '<span  class="highlighted-text">' + cryptoData.deposit_crypto_amt + '</span>');
        msg = msg.replace(cryptoData.deposit_crypto, '<span  class="highlighted-text">' + cryptoData.deposit_crypto + '</span>');
        return msg;

    }

    onCopyLink = (address) => {
        navigator.clipboard.writeText(address);
        Utilities.showToast(AL.WALLET_ADDRESS_HAS_BEEN_COPIED, 1000);
    }
    render() {
        const { mShow, mHide, cryptoData } = this.props.preData;
        let type = cryptoData.deposit_crypto;
        let message = AL.IMPORTANT_NOTICE + ' ' + AL.THE_WALLET_ADDRESS + ' ' + cryptoData.deposit_to_addr + " " + AL.IS_VALID_FOR_15_MINUITES_ONLY + AL.SEND + " " + cryptoData.deposit_crypto_amt + " " + cryptoData.deposit_crypto + " " + AL.WALLET_MESSAGE;
        let keyI = type == 'BNB.BSC' ? 'BNB_BSC' : type;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        bsSize="large"
                        dialogClassName="modal-full-screen"
                        className="modal-qr-crypto">
                        <Modal.Header >
                            <div className='Confirm-header'>{AL.DEPOSIT_INSTRUCTION}</div>
                            <a href onClick={() => mHide()} className="modal-close">
                                <i className="icon-close"></i>
                            </a>

                        </Modal.Header>
                        <Modal.Body className="crypto-prog">

                            <div className='main-container'>
                                <div className='upper-conatiner'>
                                    <div className='lable-value-container'>
                                        <div className='inner-c'>
                                            <div className='text-label'>{AL.YOU_SELECTED_DEPOSIT_THROUGH}</div>
                                            <div className='row-container'>
                                                <div className='value'>{" (" + cryptoData.deposit_crypto + ")"} </div>

                                            </div>

                                        </div>

                                    </div>
                                    <div className='horizontal-line'></div>
                                    <div className='lable-value-container'>
                                        <div className='inner-c'>
                                            <div className='text-label'>{AL.CRYPTO_SYMBOL}</div>
                                            <div className='row-container'>
                                                <div className='value'>{" (" + cryptoData.deposit_crypto + ")"} </div>

                                            </div>

                                        </div>
                                        <img alt='' src={Images[keyI]} className='image-right'></img>

                                    </div>
                                    <div className='horizontal-line'></div>
                                    <div className='lable-value-container'>
                                        <div className='inner-c'>
                                            <div className='text-label'>{AL.YOU_NEED_TO_DEPOSIT}</div>
                                            <div className='row-container'>
                                                <img alt='' src={Images[keyI]} className='icon-image'></img>
                                                <div className='value amount'>{cryptoData.deposit_crypto_amt} </div>
                                                <div className='type'>{cryptoData.deposit_crypto} </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>
                                <div className='upper-conatiner address-container'>
                                    <div className='lable-value-container'>
                                        <div className='inner-c'>
                                            <div className='text-label'>{AL.DEPOSIT_WALLET_ADDRESS}</div>
                                            <div className='row-container'>
                                                <div className='value'>{cryptoData.deposit_to_addr} </div>

                                            </div>

                                        </div>

                                    </div>
                                    {
                                        this.state.status == 0 && 
                                        <div className='copy-btn-container'>
                                            <div onClick={() => this.onCopyLink(cryptoData.deposit_to_addr)} className='copy-btn'>{AL.COPY}</div>
                                        </div>
                                    }


                                </div>
                                {
                                    this.state.status == 0  &&
                                    <div>
                                        <div className='or-text'>{AL.OR} {' '}{AL.SCAN_QR_CODE}</div>

                                        <div className='image-qr-code-conatiner'>
                                            <img src={cryptoData.qr_code} className='image' alt=''></img>

                                        </div>
                                    </div>


                                }
                                {
                                    this.state.status == 0  ?
                                    <div className='alert-conatiner'>
                                        <p dangerouslySetInnerHTML={{ __html: this.renderTagMessage(message, cryptoData) || '--' }}></p>

                                    </div>
                                    :
                                    <div className='upper-conatiner address-container'>
                                    <div className='lable-value-container'>
                                        <div className='inner-c'>
                                            <div className='text-label'>{AL.STATUS}</div>
                                            <div className='row-container'>
                                                <div className='value'>{this.state.status == 2 ? AL.TRANSACTION_STATUS_FAILED :this.state.status == 1 ? AL.TRANSACTION_STATUS_SUCCESS :'' } </div>

                                            </div>

                                        </div>

                                    </div>
                                    </div>

                                }
                               
                            </div>

                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default QrCodeCryptoModal;