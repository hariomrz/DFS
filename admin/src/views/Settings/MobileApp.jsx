import React, { Component, Fragment } from "react";
import { Row, Col, Input, Button, Tooltip } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import Loader from '../../components/Loader';
const { REACT_APP_BASE_URL} = process.env
export default class MobileApp extends Component {
    constructor(props) {
        super(props)
        this.toggle = this.toggle.bind(this);

        this.state = {
            fileUplode: '',
            tooltipOpen: false,

            fileName: '',
            BgImage: '',
            ImagePosting: true,
             validURL: false,
              target_url: '',
              Url: '',
              qrimage:'appqr.png',
              isloadimage:false
        }
    }
    componentDidMount() {
        this.getBgImage()
    }
    toggle() {
        this.setState({
            tooltipOpen: !this.state.tooltipOpen
        });
    }
    onChangeImage = (event) => {
        this.setState({
            NewBgImage: URL.createObjectURL(event.target.files[0]),
            fileUplode: event.target.files[0].name
        });

        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);

        if ((file.size / 1024000) > 4) {
            notify.show('File size must be less than 4 mb.', "error", 5000);
        } else {
            WSManager.multipartPost(NC.baseURL + NC.FRONT_BG_UPLOAD, data)
                .then(responseJson => {
                    document.getElementById("banner_image").value = "";
                    notify.show("Image uploaded successfully", "success", 3000)
                }).catch(error => {
                    notify.show(NC.SYSTEM_ERROR, "error", 3000);
                });
        }
    }

    getBgImage = () => {
        this.setState({ ImagePosting: false })
        WSManager.Rest(NC.baseURL + NC.GET_FRONT_BG_IMAGE, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    NewBgImage: responseJson.data.image_url,
                    ImagePosting: true
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    GenerateQr = () => {
        this.setState({isloadimage:true,qrimage:''});

        console.log(this.state.validURL,'this.state.validURL');

        if(this.state.validURL == true){
             notify.show('please insert valid url', "error", 3000);
        }

        const {validURL,Url} = this.state


        if (Url == "") {
            var newUrl = REACT_APP_BASE_URL + 'app'
        }else{
           var newUrl = Url 
        }

        let params = {
            "url": newUrl          
        }

        // this.setState({ ImagePosting: false })
        WSManager.Rest(NC.baseURL + NC.QR_GENERATE, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({qrimage:responseJson.data.image_name,isloadimage:false});
                notify.show(responseJson.message, "success", 3000)
                setTimeout(() => {
                    window.location.reload();
                }, 2000)
                 
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

      handleNameChange = e => {       
        const name = e.target.name;
        const value = e.target.value;
        // console.log(value,'fhfhfhf')
        this.setState({ [name]: value,Url:value })
         //this.setState({qrimage:''});
        if (name == "target_url" && !value.match(/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/)) {
           
            this.setState({
                validURL: true                
            }, function () {
               
            })
        } else {
           
            this.setState({
            
                validURL: false
            }, function () {
               
            })

        }

    }

    render() {
        const { ImagePosting, isloadimage,qrimage } = this.state
        return (
            <Fragment>
                <div className="mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Mobile App QR</h1>
                        </Col>
                    </Row>
                    <div className="animated fadeIn new-banner">
                        <Col md={12} className="input-row">
                            <Row>
                                <Col md={6}>
                                    <Row>
                                    <Col md={6} className='d'>
                                        <div>
                                        <Input
                                            type="text"
                                            name='target_url'
                                            placeholder="Target Url"
                                            onChange={this.handleNameChange}
                                            // value={target_url}
                                        />
                                        </div>
                                        
                                        <p> {this.state.validURL &&
                                                    <span className="error-text">Please upload valid URL</span>
                                            }</p>                                   
                                    </Col>
                                    <Col md={6} className='d'>
                                        <Button className="btn-secondary mr-3" onClick={() => this.GenerateQr()}>Generate QR</Button>
                                    </Col>
                                    <Col md={9}>   
                                                                    
                                        <div className="set-qr-size">
                                            {console.log(qrimage,'qrimageqrimageqrimageqrimage')}
                                            {!isloadimage ?
                                                <img className="img-cover" src={NC.S3 +"upload/"+qrimage } alt="new "/>
                                                :
                                                
                                            <Loader />
                                            }
                                        </div>
                                    </Col>
                                    </Row>
                                </Col>
                                <Col md ={6}>
                                    <div className="qr-text">
                                        <h3>Important</h3>
                                        <p>This link will redirect the user to download the application from the mentioned URL.
                                        </p>
                                        <p>
                                            Do not add any URL if the app is available on play store and app store both. The QR will be auto generated in this case.
                                        </p>
                                    </div>
                                </Col>
                            </Row>
                        </Col>
                        {/* <Col md={12} className="banner-action">
                            <Button className="btn-secondary mr-3" onClick={() => this.GenerateQr()}>Reset BG Image</Button>
                        </Col> */}
                    </div>
                </div>
            </Fragment>
        )
    }
}