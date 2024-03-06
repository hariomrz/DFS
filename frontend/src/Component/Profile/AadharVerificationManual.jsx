import React from 'react';
import { Row, Col, FormGroup, Checkbox } from 'react-bootstrap';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import WSManager from "../../WSHelper/WSManager";
import { inputStyleLeft } from '../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import { Utilities, blobToFile, compressImg } from '../../Utilities/Utilities';
import {
  saveAadharDetail
} from "../../WSHelper/WSCallings";
import CustomLoader from '../../helper/CustomLoader';
import Images from '../../components/images';



var globalThis = null;
const options = {
  maxWidthOrHeight: 900
}
export default class AadharVerification extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      userProfile: WSManager.getProfile(),
      bankDocImageURL: WSManager.getProfile().user_bank_detail.ac_number ? Utilities.getPanURL(WSManager.getProfile().user_bank_detail.bank_document) : '',
      isLoading: false,
      file: '',
      showDeleteModal: false,
      refreshPage: true,
      isLoadingshow: false,
      showMessageModal: false,
      cameraPermisiionGranted: false,
      userName: '',
      aadhar_number: '',
      aadhar_name: null,
      front_image: '',
      back_image: '',
      imageUrl: '',
      imageBackUrl: '',
      ageConsent: true,
      stateConsent: true,
      confirmConsent: true,
      ActiveAadharField: false
    }
  }

  submitAadharDetail() {
    this.setState({ isLoading: true });
    let param = {
      "name": this.state.aadhar_name,
      "aadhar_number": this.state.aadhar_number,
      "front_image": this.state.imageUrl,
      "back_image": this.state.imageBackUrl,
    }
    saveAadharDetail(param).then((responseJson) => {
      this.setState({ isLoading: false });
      if (responseJson != null && responseJson != '' && responseJson.response_code == WSC.successCode) {
        Utilities.showToast(responseJson.message, 5000, Images.BANK_ICON);
        setTimeout(() => {
          this.props.history.replace({ pathname: '/my-profile' })
        }, 1000)
      }
    })
  }


  onDrop(e) {
    e.preventDefault();
    let reader = new FileReader();
    let mfile = e.target.files[0];
    reader.onloadend = () => {
      if (mfile.type && (mfile.type == 'image/png' || mfile.type == 'image/jpeg')) {
        this.setState({ bankDocImageURL: reader.result })

        this.compressImage(mfile)

      }
      else {
        Utilities.showToast(AppLabels.UPLOAD_FORMATS, 2000, Images.BANK_ICON)
      }
    }
    if (mfile) {
      reader.readAsDataURL(mfile)
    }
  }


  onDropBack(e) {
    e.preventDefault();
    let reader = new FileReader();
    let mBackfile = e.target.files[0];
    reader.onloadend = () => {
      if (mBackfile.type && (mBackfile.type == 'image/png' || mBackfile.type == 'image/jpeg')) {
        this.setState({ bankDocImageURL: reader.result })

        this.compressImageBack(mBackfile)

      }
      else {
        Utilities.showToast(AppLabels.UPLOAD_FORMATS, 2000, Images.BANK_ICON)
      }
    }
    if (mBackfile) {
      reader.readAsDataURL(mBackfile)
    }
  }


  compressImage = async (mfile, name) => {
    compressImg(mfile, options).then((compressedFile) => {
      this.setState({ profileImageFile: blobToFile(compressedFile ? compressedFile : mfile, mfile.name) }, () => {
        this.uploadImage(name)
      })
    })
  }

  compressImageBack = async (mBackfile, name) => {
    compressImg(mBackfile, options).then((compressedFile) => {
      this.setState({ profileImageFile: blobToFile(compressedFile ? compressedFile : mBackfile, mBackfile.name) }, () => {
        this.uploadImageBack(name)
      })
    })
  }

  uploadImage(name) {
    this.setState({ isLoading: true });
    var data = new FormData();
    data.append("userfile", this.state.profileImageFile);
    data.append("update_image_record", '1');
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.addEventListener("readystatechange", function () {
      if (this.readyState == 4) {
        if (!this.responseText) {
          Utilities.showToast(AppLabels.SOMETHING_ERROR, 5000, Images.PAN_ICON);
          return;
        }
        var response = JSON.parse(this.responseText);
        if (response !== '' && response.response_code == WSC.successCode) {
          globalThis.setState({
            isLoading: false,
            imageUrl: response.data.image_path,
          })
        }
        else {
          if (response.global_error && response.global_error != '') {
            Utilities.showToast(response.global_error, 5000);
          }
          else {
            var keys = Object.keys(response.error);
            if (keys.length > 0) {
              Utilities.showToast(response.global_error, 5000);
            }
          }
        }

      }
    });

    xhr.open("POST", WSC.userURL + WSC.DO_UPLOAD_AADHAR);
    xhr.setRequestHeader('Sessionkey', WSManager.getToken())
    xhr.send(data);
  }

  uploadImageBack(name) {
    this.setState({ isLoading: true });
    var databack = new FormData();
    databack.append("userfile", this.state.profileImageFile);
    databack.append("update_image_record", '1');
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.addEventListener("readystatechange", function () {
      if (this.readyState == 4) {
        if (!this.responseText) {
          Utilities.showToast(AppLabels.SOMETHING_ERROR, 5000, Images.PAN_ICON);
          return;
        }
        var response = JSON.parse(this.responseText);
        if (response !== '' && response.response_code == WSC.successCode) {
          globalThis.setState({
            isLoading: false,
            imageBackUrl: response.data.image_path,
          })
        }
        else {
          if (response.global_error && response.global_error != '') {
            Utilities.showToast(response.global_error, 5000);
          }
          else {
            var keys = Object.keys(response.error);
            if (keys.length > 0) {
              Utilities.showToast(response.global_error, 5000);
            }
          }
        }

      }
    });

    xhr.open("POST", WSC.userURL + WSC.DO_UPLOAD_AADHAR);
    xhr.setRequestHeader('Sessionkey', WSManager.getToken())
    xhr.send(databack);
  }

  handleChangeNumber = (e) => {
    const { value } = e.target
    if (value.length <= 12) {
      this.setState({
        aadhar_number: e.target.value,
      })
    }
  }
  handleKeyDown = (event) => {
    const { aadhar_number, ActiveAadharField } = this.state
    if(!ActiveAadharField) return;
    if (aadhar_number.length >= 12 && event.key !== 'Backspace') {
      event.preventDefault();
    }
  };

  componentDidMount() {
    window.addEventListener('keypress', this.handleKeyDown);
  }

  componentWillUnmount() {
    window.removeEventListener('keypress', this.handleKeyDown);
  }

  handleChangeName(e) {
    const value = e.target.value;
    const regMatch = /^[a-zA-Z-" "]*$/.test(value);
    if (e && e.target.value.length > 3 && regMatch) {
      this.setState({
        aadhar_name: value
      })
    }
    else {
      this.setState({
        aadhar_name: false
      })
    }
  }

  onChangeImage(value) {
    if (value == 'front') {
      this.setState({
        imageUrl: ''
      })
    }
    else {
      this.setState({
        imageBackUrl: ''
      })
    }
  }



  render() {
    const {
      refreshPage,
      userName,
      aadhar_number,
      aadhar_name,
      imageUrl,
      imageBackUrl,
      ageConsent,
      confirmConsent,
      stateConsent
    } = this.state;
    globalThis = this;

    const HeaderOption = {
      back: true,
      notification: false,
      hideShadow: true,
      title: AppLabels.AadharVerification,
      fromProfile: true
    }

    let bankDocDescTxt = AppLabels.UPLOAD_BANK_DOC_DESC || ''
    let banCodeMSg = AppLabels.BANK + ' ' + AppLabels.CODE;
    let bankDocDesc = Utilities.getMasterData().int_version != 1 ? bankDocDescTxt : bankDocDescTxt.replace((', ' + AppLabels.IFSC_CODE), (', ' + banCodeMSg).toLowerCase())
    return (
      <div className="verify-account">
        {this.state.isLoading &&
          <CustomLoader />
        }

        {(refreshPage && (WSManager.getProfile().aadhar_detail == "" || WSManager.getProfile().aadhar_status == "2")) ?
          (<div className="verify-wrapper aadhar-block" >
            <div className='upload-aadhar'>
              {imageUrl ?
                <div className='aadhar-img-upload-block'>
                  <img src={imageUrl} width="200" height="100" />
                  <i className='icon-close change-btn'
                    onClick={() => this.onChangeImage('front')}
                  />
                </div>
                :
                <div><input type="file" className='file-upload' onChange={this.onDrop.bind(this)} accept="image/pdf, image/jpeg, image/png" />
                  <img src={Images.PAN_ICON_PNG} alt="" className="pan-img" />
                </div>
              }
              <h5 className='aadhar-heading'>{AppLabels.UPLOAD_AADHAR_FRONT}</h5>
              <p className='aadhar-detail'>{AppLabels.MAX_SIZE_MB}</p>
              <p className='aadhar-detail'>{AppLabels.ALL_POSSIBLE_FORMATS}</p>
            </div>


            <div className='upload-aadhar'>
              {imageBackUrl ?
                <div className='aadhar-img-upload-block'>
                  <img src={imageBackUrl} width="200" height="100" />
                  <i className='icon-close change-btn'
                    onClick={() => this.onChangeImage('back')}
                  />
                </div>
                :
                <div>
                  <input type="file" className='file-upload' onChange={this.onDropBack.bind(this)} accept="image/pdf, image/jpeg, image/png" />
                  <img src={Images.PAN_ICON_PNG} alt="" className="pan-img" />
                </div>
              }
              <h5 className='aadhar-heading'>{AppLabels.UPLOAD_AADHAR_BACK}</h5>
              <p className='aadhar-detail'>{AppLabels.MAX_SIZE_MB}</p>
              <p className='aadhar-detail'>{AppLabels.ALL_POSSIBLE_FORMATS}</p>
            </div>


            <p className='consent-text'><i className='icon-info info' /><span>{AppLabels.CONSENT_TEXT}</span></p>
            <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
              <Row>
                <Col xs={12} className="input-label-spacing">
                  <FormGroup
                    className={`input-label-center input-transparent`}
                    controlId="formBasicText"
                  >
                    <FloatingLabel
                      autoComplete='off'
                      styles={inputStyleLeft}
                      id='aadharName'
                      name='aadharName'
                      placeholder={AppLabels.NAME_ON_AADHAR}
                      onChange={(e) => this.handleChangeName(e)}
                      minLength={3}
                      maxLength={40}
                    />
                    {aadhar_name == false && <label className='validate-digit'>Please enter alphabetic and (3 to 40) characters.</label>}

                  </FormGroup>
                </Col>
              </Row>
              <Row>
                <Col xs={12} className="input-label-spacing">
                  <FormGroup
                    className={`input-label-center input-transparent`}
                    controlId="formBasicText"
                  >
                    <FloatingLabel
                      autoComplete='off'
                      styles={inputStyleLeft}
                      id='aadharNumber'
                      name='aadharNumber'
                      placeholder={AppLabels.AADHAR_NUMBER}
                      type='number'
                      onChange={(e) => this.handleChangeNumber(e)}
                      value={aadhar_number}
                      onFocus={() => this.setState({
                        ActiveAadharField: true
                      })}
                      onBlur={() => this.setState({
                        ActiveAadharField: false
                      })}
                    />
                  </FormGroup>
                </Col>
              </Row>
            </div>

            <div className='filter-body hub-filter'>
              <ul className='pl-0 mt-2'>
                <FormGroup>
                  <Checkbox
                    className="custom-checkbox"
                    value=""
                    name="age"
                    id="age"
                    defaultChecked={true}
                    onClick={() => this.setState({
                      ageConsent: !ageConsent
                    })}
                  >
                    <span className='consent-text'>{AppLabels.CONSENT_TEXT_AGE}</span>
                  </Checkbox>
                </FormGroup>

                <FormGroup>
                  <Checkbox
                    className="custom-checkbox"
                    value=""
                    name="state"
                    id="state"
                    defaultChecked={true}
                    onClick={() => this.setState({
                      stateConsent: !stateConsent
                    })}
                  >
                    <span className='consent-text'>{AppLabels.CONSENT_TEXT_STATE}</span>
                  </Checkbox>
                </FormGroup>

                <FormGroup>
                  <Checkbox
                    className="custom-checkbox"
                    value=""
                    defaultChecked={true}
                    name="confirm"
                    id="confirm"
                    onClick={() => this.setState({
                      confirmConsent: !confirmConsent
                    })}
                  >
                    <span className='consent-text'>{AppLabels.CONSENT_TEXT_CONFIRMATION}</span>
                  </Checkbox>
                </FormGroup>
              </ul>
            </div>
            <div className="text-center btm-fixed-action mt-3 pt-3">
              <a id="verifyPanCard" className={(ageConsent == true && stateConsent == true && confirmConsent == true && imageUrl != '' && imageBackUrl != '' && aadhar_number.length == 12  && aadhar_name != null && aadhar_name != false)
                ? "button button-primary-rounded btn-verify" : 'disabled button button-primary-rounded btn-verify'}
                onClick={() => this.submitAadharDetail()}>
                {AppLabels.replace_PANTOID(AppLabels.SUBMIT)}
              </a>
                <div className='help-block-kyc'>
              {
                Utilities.getMasterData().adr_mode == 1 && 
                  <div className='text-with-link'>
                    <a onClick={() => this.props.toggleView()}>{AppLabels.TRY_AUTO_VERIFICATION}</a>
                  </div>
              }
                </div>
            </div>
          </div>)
          :
          (
            <div className="verify-wrapper aadhar-block">
              <div className='upload-aadhar'>
                <div className='mb-3'><img src={WSManager.getProfile().aadhar_detail.front_image} width="100" /></div>
                <div className='mt-3'><img src={WSManager.getProfile().aadhar_detail.back_image} width="100" /></div>

              </div>
              <div className='aadhar-bg'>
                <div>
                  <p className='aadhar-name-heading'>{AppLabels.NAME_ON_AADHAR}</p>
                  <p className='aadhar-name'>{WSManager.getProfile().aadhar_detail.name}</p>
                </div>
                <div>
                  <p className='aadhar-name-heading'>Aadhaar Card Number</p>
                  <p className='aadhar-name'>{WSManager.getProfile().aadhar_detail.aadhar_number}</p>
                </div>
              </div>
            </div>
          )
        }

      </div>
    )
  }
}
