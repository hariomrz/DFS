import React from 'react';
import { Row, Col, FormGroup } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import WSManager from "../../WSHelper/WSManager";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { inputStyleLeft } from '../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import { Utilities } from '../../Utilities/Utilities';
import Images from '../../components/images';
import { verifyCryptoDetails } from "../../WSHelper/WSCallings";
import * as CONSTANTS from '../../helper/Constants';

let error = undefined;

export default class CryptoVerifcation extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            formValid: WSManager.getProfile().user_bank_detail.bank_name ? true: false,
            wallet_address:WSManager.getProfile().user_bank_detail.upi_id ? WSManager.getProfile().user_bank_detail.upi_id : '',
            crypto_name:WSManager.getProfile().user_bank_detail.first_name ? (WSManager.getProfile().user_bank_detail.first_name) + ' ' + (WSManager.getProfile().user_bank_detail.last_name) : WSManager.getProfile().user_bank_detail.last_name ? WSManager.getProfile().user_bank_detail.last_name : '' ,
            error: 'Please enter cryto name',
            showFillterModal:false,
            selectedCValue:{key:WSManager.getProfile().user_bank_detail.bank_name ? WSManager.getProfile().user_bank_detail.bank_name : '',value:WSManager.getProfile().user_bank_detail.bank_name ? WSManager.getProfile().user_bank_detail.bank_name =='BNB.BSC'  ? CONSTANTS.crypto_cur['BNB_BSC'] :CONSTANTS.crypto_cur[WSManager.getProfile().user_bank_detail.bank_name] :AppLabels.SELECT_CUURENCY},
            cryptoData:Utilities.getMasterData().crypto_wd && Object.keys(Utilities.getMasterData().crypto_wd).length > 0 ? Object.keys(Utilities.getMasterData().crypto_wd) :[],
            isUnderVerification: WSManager.getProfile().user_bank_detail.bank_name ? true :false,
            userProfile: WSManager.getProfile(),

        }
    }
    showFillterItem=(e)=>{
        e.stopPropagation()
        if(this.state.showFillterModal || this.state.cryptoData && this.state.cryptoData.length == 1){
            this.hideFillterItem()
            return;
        }
        this.setState({
            showFillterModal: true
        })
    }
    hideFillterItem=()=>{
        this.setState({
            showFillterModal: false
        })
    }


   

    UNSAFE_componentWillMount = () => {
        Utilities.setScreenName('mywallet')

    };
   
    componentDidMount = () => {
        let keyC='';
        
        let cryptoData = this.state.cryptoData;
        if (cryptoData && cryptoData.length == 1) {
            cryptoData && cryptoData.length > 0 && cryptoData.map((key) => (
                keyC = `${key}`
            ))
            this.selectCurrency(Utilities.getMasterData().crypto_wd[keyC],keyC)
        }
    };

  

    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value },()=>{});
        // const regex = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/

        // console.log("dfssdf",regex.test(value))

        // if(value != '' && !regex.test(value)){
        //     this.setState({ [name]: value },()=>{
        //         console.log("aaa",this.state.wallet_address.length)

        //     });

        // }


    }
    validateForm() {
        this.setState({ formValid: this.isValid(false), error: error });
    }
    isValid = (notifyAllowed) => {

       console.log("sdasd",this.state.wallet_address.length)
        if (this.state.wallet_address == '') {
            if (notifyAllowed)
                Utilities.showToast('Please enter cryto name', 3000);
            error = 'Please enter cryto name';
            return false;
        }
        if (this.state.wallet_address.length < 6) {
            if (notifyAllowed)
                Utilities.showToast('Please enter cryto name', 3000);
            error = 'Please enter cryto name';
            console.log("in",this.state.wallet_address.length)

            return false;
        }
        else{
            return true;
 
        }

        error = '';
        return true;
    }
    selectCurrency =(valueC,keyC)=>{
        let data ={'key' :keyC,'value':valueC}
        this.hideFillterItem();
        this.setState({selectedCValue:data},()=>{
            console.log("selectedCValue",this.state.selectedCValue)
        })

    }
    verify=()=>{
        let param = {
            "bank_name": this.state.selectedCValue.key,
            "upi_id": this.state.wallet_address,
        }
        verifyCryptoDetails(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson !== null && responseJson !== '' && responseJson.response_code === WSC.successCode) {
                Utilities.showToast(responseJson.message)
                if(this.props.location.state.isFromProfile){
                    this.props.history.replace({ pathname: '/my-profile' })

                }
                else{
                    this.props.history.replace({ pathname: '/my-profile' })

                }


            }
        })
    }
    
    render() {
        const {
            wallet_address,
            crypto_name,
            formValid,
            showFillterModal,
            selectedCValue,
            cryptoData,
            isUnderVerification,
            userProfile
            
        } = this.state;

        const HeaderOption = {
            back: true,
            notification: false,
            hideShadow: true,
            title: AppLabels.CRYPTO_VERIFICATION,
            fromProfile: true
        }

        let isSingleItem = cryptoData && cryptoData.length == 1 ? true :false

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div onClick={(e) => this.hideFillterItem(e)} className="web-container transparent-header web-container-fixed verify-account">
                         {/* <CustomLoader /> */}

                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.mywallet.title}</title>
                            <meta name="description" content={MetaData.mywallet.description} />
                            <meta name="keywords" content={MetaData.mywallet.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        {
                            <div className={"verify-wrapper"} >
                                
                                <div className={"uploaded-info-section" + (userProfile && userProfile.is_bank_verified == '1' ? ' noneditable-section' : '')} style={{ pointerEvents: (userProfile.is_bank_verified == '1') ? 'none' : '' }}>
                                    <div>{AppLabels.CRYPTO_NAME}</div>
                                    <div onClick={(e) => this.showFillterItem(e)} className={'box-currency-type' + ( isSingleItem ? ' disbale' : '') }>
                                        <div className='first-container'>
                                            {
                                                selectedCValue.key &&  <img alt='' src={Images[selectedCValue.key == 'BNB.BSC' ? 'BNB_BSC' : selectedCValue.key]} className='image-currency'></img>
                                            }
                                           
                                            <div className='select-text'>{selectedCValue.value}</div>

                                        </div>
                                        {!isSingleItem &&    <i className='icon-arrow-down i-c'></i>}
                                     

                                    </div>
                                    {
                                        showFillterModal && 
                                        <div className='popup-conatiner'>
                                            <span className={"all-fillter-option"}>
                                                {cryptoData && cryptoData.length > 1 && cryptoData.map((key) => (
                                                    <span onClick={() => this.selectCurrency(Utilities.getMasterData().crypto_cur[`${key}`], `${key}`)} >
                                                        {Utilities.getMasterData().crypto_cur[`${key}`]}
                                                    </span>
                                                ))}
                                            </span>
                                        </div>
                                    }
                                    {/* <Row style={{marginTop:10}}>
                                        <Col xs={12} className="input-label-spacing">
                                            <FormGroup
                                                className={'input-label-center input-transparent font-14 gray-input-field '}
                                                controlId="formBasicText"
                                            >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyleLeft}
                                                    id='crypto_name'
                                                    name='crypto_name'
                                                    placeholder={AppLabels.CRYPTO_NAME}
                                                    type='text'
                                                    value={crypto_name}
                                                    onChange={this.handleChange}
                                                />
                                            </FormGroup>
                                        </Col>
                                    </Row> */}
                                    <Row style={{marginTop:10}}>
                                        <Col xs={12} className="input-label-spacing">
                                            <FormGroup
                                                className={'input-label-center input-transparent font-14 gray-input-field '}
                                                controlId="formBasicText"
                                            >
                                                <FloatingLabel
                                                    maxle
                                                    autoComplete='off'
                                                    styles={inputStyleLeft}
                                                    id='wallet_address'
                                                    name='wallet_address'
                                                    placeholder={AppLabels.WALLET_ADDRESS}
                                                    type='text'
                                                    value={wallet_address}
                                                    onChange={this.handleChange}
                                                />
                                            </FormGroup>
                                        </Col>
                                    </Row>

                                    <div className='note-box'>{AppLabels.WITHDRAW_CRYPTO_NOTE}</div>


                                </div>

                                <div className="text-center m-t-30- btm-fixed-action">

                                    <a
                                        href
                                        className={"button button-primary-rounded btn-verify" + (this.state.wallet_address == '' || this.state.wallet_address.length <6 || selectedCValue.key ==''  ? ' disabled' : ' ')}
                                        id="bankDocSubmit"
                                        onClick={() => this.verify()}
                                    >
                                        {AppLabels.VERIFY_CRYPTO_DETAILS}
                                    </a>
                                </div>
                            </div>
                        }
                       
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
