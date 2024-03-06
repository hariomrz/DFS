import React, { Component } from "react";
import { Row, Col, Input, Button, Tooltip } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _, { indexOf } from 'lodash';
import Select from 'react-select';
import Images from '../../components/images';
import Loader from "../../components/Loader";
import HF from '../../helper/HelperFunction';
import SelectDropdown from "../../components/SelectDropdown";
export default class HubPage extends Component {
    constructor(props) {
        super(props)
        this.toggle = this.toggle.bind(this);

        this.state = {
            ModuleSetting: "1",
            LanguageType: 'en',
            // tooltipOpen1: false,
            // tooltipOpen2: false,

            languageOptions: HF.getLanguageData() ? HF.getLanguageData() : [],
            ConfigureList: [],
            toggPosting: false,
            BannerData: [],
            BannerIcon: [],
            loaders:false
        }
    }
    componentDidMount() {
        this.getConfiguration()
        this.getBannerData()
    }
    toggle(from) {
        this.setState({
            [from]: !this.state[from]
        });
    }
    getBannerData() {
        WSManager.Rest(NC.baseURL + NC.GET_HUB_ICON_BANNER, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let BannerImg = []
                let BannerIcon = []
                if (!_.isEmpty(responseJson.data)) {
                    _.map(responseJson.data, (item, idx) => {
                        if (item.key_name === 'allow_hub_banner') {
                            BannerImg = responseJson.data[idx]
                        }
                        if (item.key_name === 'allow_hub_icon') {
                            BannerIcon = responseJson.data[idx]
                        }
                    })
                }
                this.setState({
                    BannerData: BannerImg,
                    BannerIcon: BannerIcon,
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    handleLangChange = (value) => {
        if (!_.isNull(value)) {
            this.setState({ LanguageType: value.value }, () => {
                this.getConfiguration()
            })
        }
    }

    getConfiguration() {
        let params = {
            "language": this.state.LanguageType
        }

        WSManager.Rest(NC.baseURL + NC.GET_SPORTS_HUB_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {

                _.map(Response.data, (item, idx) => {
                    Response.data[idx].ImagePosting = true
                    Response.data[idx].formValid = true
                })

                this.setState({
                    ConfigureList: Response.data,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    updateConfiguration(up_item, idx) {
        let { LanguageType } = this.state
        let msg = ''
        let flag = false
        if (_.isEmpty(up_item[LanguageType + '_title'])) {
            msg = 'Title can not be empty'
            flag = true
        }

        if (flag) {
            notify.show(msg, 'error', 5000)
            return false
        }

        let params = {
            "language": LanguageType,
            "title": up_item[LanguageType + '_title'],
            // "body": up_item[LanguageType + '_desc'],
            "image": up_item.image,
            "game_key": up_item.game_key
        }

        WSManager.Rest(NC.baseURL + NC.UPDATE_SPORTS_HUB, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                let ConfigureList = this.state.ConfigureList;
                ConfigureList[idx]['formValid'] = true;
                this.setState({
                    ConfigureList
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleInputChange = (e, idx) => {
        let name = e.target.name;
        let value = e.target.value;
        let ConfigureList = this.state.ConfigureList;
        ConfigureList[idx][name] = value;
        ConfigureList[idx]['formValid'] = false;
        this.setState({
            ConfigureList
        });
    }

    handleModuleChange = () => {
        let { BannerData } = this.state
        BannerData['key_value'] = BannerData['key_value'] == "1" ? "0" : "1";
        this.setState({
            BannerData
        }, this.updateBannerStatus)
    }

    updateBannerStatus = (item) => {
        this.setState({ toggPosting: true })
        let params = {
            "key_name": "allow_hub_banner",
            "status": this.state.BannerData['key_value']
        }
        WSManager.Rest(NC.baseURL + NC.TOGGLE_BANNER_IMAGE_STATUS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000)
                this.setState({ toggPosting: false })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    revertOrigional = (item, type) => {
        this.setState({ ImagePosting: false })
        let params = {
            "type": type,
            "key_name": type == '2' ? item.key_name : item.game_key,
            // "game_key": item.game_key ? item.game_key : ""
        }
        WSManager.Rest(NC.baseURL + NC.REVERT_TO_ORIGNAL_HUB, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000)
                if (type == '2') {
                    this.getBannerData()
                }
                if (type == '1') {
                    this.getConfiguration()
                }
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    moveToUp =(item, itemIdx, move)=>{
        this.setState({ loaders: true })
        console.log(this.state.ConfigureList, 'this.state.ConfigureList');

        // return false;
        // let List = Object.assign({}, this.state.ConfigureList)
        let List = [...this.state.ConfigureList]

        let CItemDO = this.state.ConfigureList[itemIdx].display_order
        let PItemDO = this.state.ConfigureList[itemIdx - 1].display_order

        List[itemIdx]['display_order'] = PItemDO
        List[itemIdx - 1]['display_order'] = CItemDO

        // List[itemIdx]['display_order'] = PItemDO
        // List[itemIdx - 1]['display_order'] = CItemDO

        // console.log(List,'ListListListList')

        let NewArray = []

        _.map(List,(i,indx)=>{
            NewArray.push({ 'display_order': i.display_order, 'sports_hub_id': i.sports_hub_id })
        })
        // console.log(NewArray, 'NewArray'); return false;
        // let tmpParam = this.objectMaker(tmpArray, KA)
        let tmpParam = NewArray

        WSManager.Rest(NC.baseURL + NC.UPDATE_SPORTSHUB_HUB_ORDER, tmpParam).then((responseJson) => {


            if (responseJson.response_code === NC.successCode) {
                this.setState({ loaders: false })

                notify.show(responseJson.message, "success", 3000)
                this.getConfiguration()
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })










        // let keyArray = Object.getOwnPropertyNames(List) // will get you array of all keys inside ConfigureList Object


        // let KIdx = keyArray.indexOf(itemIdx) // index of current key
        // let PIdx = keyArray[KIdx - 1]

        // let PItem = List[PIdx] // previous object item

        // List[PIdx] = item
        // List[itemIdx] = PItem

        // let KA = Object.getOwnPropertyNames(List)
        // let tmpArray = []
        // KA.map((data) => {
        //     tmpArray.push(List[data].sports_hub_id)
        // })

        // let tmpParam = this.objectMaker(tmpArray, KA)

        // WSManager.Rest(NC.baseURL + NC.UPDATE_SPORTSHUB_HUB_ORDER, tmpParam).then((responseJson) => {


        //     if (responseJson.response_code === NC.successCode) {
        //         this.setState({ loaders: false })

        //         notify.show(responseJson.message, "success", 3000)
        //         this.getConfiguration()
        //     }
        // }).catch((error) => {
        //     notify.show(NC.SYSTEM_ERROR, "error", 3000);
        // })

    }


    moveTodown = (item, itemIdx, move) => {


        this.setState({ loaders: true })
        console.log(this.state.ConfigureList, 'this.state.ConfigureList');

        // return false;
        // let List = Object.assign({}, this.state.ConfigureList)
        let List = [...this.state.ConfigureList]

        let CItemDO = this.state.ConfigureList[itemIdx].display_order
        let PItemDO = this.state.ConfigureList[itemIdx + 1].display_order

        List[itemIdx]['display_order'] = PItemDO
        List[itemIdx + 1]['display_order'] = CItemDO

        // List[itemIdx]['display_order'] = PItemDO
        // List[itemIdx - 1]['display_order'] = CItemDO

        // console.log(List,'ListListListList')

        let NewArray = []

        _.map(List, (i, indx) => {
            NewArray.push({ 'display_order': i.display_order, 'sports_hub_id': i.sports_hub_id })
        })
        // console.log(NewArray, 'NewArray'); return false;
        // let tmpParam = this.objectMaker(tmpArray, KA)
        let tmpParam = NewArray

        WSManager.Rest(NC.baseURL + NC.UPDATE_SPORTSHUB_HUB_ORDER, tmpParam).then((responseJson) => {


            if (responseJson.response_code === NC.successCode) {
                this.setState({ loaders: false })

                notify.show(responseJson.message, "success", 3000)
                this.getConfiguration()
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
        // this.setState({ loaders: true })
        // console.log(this.state.ConfigureList, 'this.state.ConfigureList');

        // // return false;
        // let List =  [...this.state.ConfigureList] // Object.assign({}, this.state.ConfigureList)

        // // console.log(List,'list');


        // let keyArray = Object.getOwnPropertyNames(List) // will get you array of all keys inside ConfigureList Object

        // // console.log(keyArray,'keyArray')

        // let KIdx = keyArray.indexOf(itemIdx) // index of current key
        // let PIdx = keyArray[KIdx + 1]

        // let PItem = List[PIdx] // previous object item

        // List[PIdx] = item
        // List[itemIdx] = PItem

        // let KA = Object.getOwnPropertyNames(List)

        // // console.log(KA,'KA');return false
        // let tmpArray = []
        // KA.map((data) => {
        //     // console.log(List[data],'List[data]'); return false;
        //     tmpArray.push(List[data].sports_hub_id)
        // })

        // let tmpParam = this.objectMaker(tmpArray, KA)
        // // console.log('KA', tmpParam); return false

        // WSManager.Rest(NC.baseURL + NC.UPDATE_SPORTSHUB_HUB_ORDER, tmpParam).then((responseJson) => {


        //     if (responseJson.response_code === NC.successCode) {
        //         this.setState({ loaders: false })

        //         notify.show(responseJson.message, "success", 3000)
        //         this.getConfiguration()
        //     }
        // }).catch((error) => {
        //     notify.show(NC.SYSTEM_ERROR, "error", 3000);
        // })

    }

    objectMaker(keys, values) {
        const object = {};

        if (keys.length !== values.length)
            return false;

        keys.forEach((key, i) => object[key] = values[i])

        return object
    }



  
    onChangeGTypeImg = (event, idx, key_name) => {

        let { ConfigureList } = this.state

        let tempConImg = ConfigureList
        tempConImg[idx].ImagePosting = false

        this.setState({ ConfigureList: tempConImg })

        const file = event.target.files[0];
        if (!file) {
            return;
        }
        let imgType = 0;
        // if (key_name === 'allow_dfs')
        //     imgType = 1

        var data = new FormData();
        data.append("name", file);
        data.append("type", imgType);
        data.append("game_key", key_name ? key_name : '');
        WSManager.multipartPost(NC.baseURL + NC.HUB_IMAGE_DO_UPLOAD, data)
            .then(responseJson => {
              
                if (responseJson.response_code === NC.successCode) {
                    tempConImg[idx].image = responseJson.data.image_name
                    tempConImg[idx].formValid = false
                    this.setState({
                        ConfigureList: tempConImg,
                    })
                    notify.show(responseJson.message, "success", 3000)
                }
                tempConImg[idx].ImagePosting = true
                this.setState({
                    ConfigureList: tempConImg,
                })
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    onChangeImage = (event, img_for) => {
        this.setState({
            NewBgImage: URL.createObjectURL(event.target.files[0]),
            fileUplode: event.target.files[0].name
        });

        const file = event.target.files[0];
        if (!file) {
            return;
        }

        var data = new FormData();
        data.append("name", file);
        data.append("type", img_for);
        WSManager.multipartPost(NC.baseURL + NC.HUB_IMAGE_DO_UPLOAD, data)

            .then(responseJson => {
                notify.show(responseJson.message, "success", 3000)
                this.getBannerData()
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    render() {
        const { loaders, languageOptions, LanguageType, ConfigureList, BannerData, BannerIcon } = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: languageOptions,
            place_holder: "Select Language",
            selected_value: LanguageType,
            modalCallback: this.handleLangChange
        }
        return (
             loaders == false ?
         
            <div className="hub-page">
              

                <h2 className="h2-cls">Hub Page</h2>
                <Row className="hp-lang">
                    <Col md={3}>
                        <label htmlFor="language">Language</label>
                        <SelectDropdown SelectProps={Select_Props} />
                    </Col>
                    <Col md={9}></Col>
                </Row>
                <Row>
                    <Col md={6}>
                        <div className="hp-bg-box mt-30">
                            <Row>
                                <Col md={12} className='d-f'>
                                    {/* <label htmlFor="SportsHubButton">Sports Hub Button</label>
                                    <div className='info-banner-2 ml10'>
                                        <i className="icon-info" id="TooltipExample"></i>
                                        <Tooltip placement="top" isOpen={this.state.tooltipOpen1} target="TooltipExample" toggle={() => { (this.toggle('tooltipOpen1')) }}>
                                            Displyed in the centre of the footer in all pages as a button
                                        </Tooltip>
                                    </div> */}
                                </Col>
                                <Col md={12}>
                                <label className="title-mrgin" htmlFor="SportsHubButton">Sports Hub Button</label>
                                    {/* <div className='info-banner-2 ml10'>
                                        <i className="icon-info" id="TooltipExample"></i>
                                        <Tooltip placement="top" isOpen={this.state.tooltipOpen1} target="TooltipExample" toggle={() => { (this.toggle('tooltipOpen1')) }}>
                                            Displyed in the centre of the footer in all pages as a button
                                        </Tooltip>
                                    </div> */}
                                    <div className="hp-up-img-btn uplod-image img-new-upload">
                                        <div className="hp-up-img">
                                            <span className="hp-up-img-txt">
                                            <img className="def-addphoto" src={Images.IMAGE_GALLARY} alt="" />
                                                Upload Image
                                                [150*150]
                                            </span>
                                            <Input
                                                type="file"
                                                name='PageHubImg'
                                                className="ph-img"
                                                onChange={(e) => this.onChangeImage(e, 2)}
                                            />
                                            
                                             {
                                                (!_.isEmpty(BannerIcon.image)) &&
                                                <div
                                                    className="hp-revert-div-txt revert-text"
                                                    onClick={() => this.revertOrigional(BannerIcon, 2)}
                                                >
                                                    Revert to orignal
                                                </div>
                                            }
                                          
                                        </div>
                                        
                                       
                                        {/* <div className="hp-size-txt">Size - 150*150</div> */}
                                        <div className="box_one">
                                            <div>
                                                <figure className="hp-img-box image-new-box">
                                                {
                                                    (!_.isEmpty(BannerIcon.image)) ?
                                                        <img
                                                            className="img-cover"
                                                            src={(BannerIcon.image) ? NC.S3 + NC.SETTING_IMG_PATH + BannerIcon.image : Images.no_image}
                                                            alt=""
                                                        />
                                                        :
                                                        (_.isEmpty(BannerIcon.image)) ?
                                                            ''
                                                            :
                                                            <Loader />
                                                }
                                                
                                                </figure>
                                            
                                            </div>
                                           
                                        </div>
                                    </div>
                                    
                                    {/* <figure className="hp-img-box">
                                        {
                                            (!_.isEmpty(BannerIcon.image)) ?
                                                <img
                                                    className="img-cover"
                                                    src={(BannerIcon.image) ? NC.S3 + NC.SETTING_IMG_PATH + BannerIcon.image : Images.no_image}
                                                    alt=""
                                                />
                                                :
                                                (_.isEmpty(BannerIcon.image)) ?
                                                    ''
                                                    :
                                                    <Loader />
                                        }
                                    </figure> */}

                                </Col>
                            </Row>
                        </div>
                    </Col>



                    <Col md={6}>
                    <div className="hp-bg-box mt-30">
                            <Row>
                                <Col md={12}>
                                    {/* <div className="hp-bann-title float-left">
                                        Banner
                                    </div> */}
                                    {/* <div className="activate-module float-right">
                                        {
                                            <label className="global-switch">
                                                <input
                                                    type="checkbox"
                                                    checked={BannerData.key_value == "1" ? false : true}
                                                    onChange={this.handleModuleChange}
                                                />
                                                <span className="switch-slide round">
                                                    <span className={`switch-on ${BannerData.key_value == "1" ? 'active' : ''}`}>ON</span>
                                                    <span className={`switch-off ${BannerData.key_value == "0" ? 'active' : ''}`}>OFF</span>
                                                </span>
                                            </label>
                                        }
                                    </div> */}
                                </Col>
                            </Row>
                            {
                                // BannerData.key_value === "1" &&
                                <Row>
                                    {/* <Col md={2} className='d-f'>
                                        <label htmlFor="SportsHubButton">Banner Image</label>
                                        <div className='info-banner-2 ml10'>
                                            <i className="icon-info" id="TooltipExample2"></i>
                                            <Tooltip placement="top" isOpen={this.state.tooltipOpen2} target="TooltipExample2" toggle={() => { (this.toggle('tooltipOpen2')) }}>
                                                Diplayed the banner images on the sports hub page
                                            </Tooltip>
                                        </div>
                                    </Col> */}
                                    <Col md={12}>
                                    <div className="hp-bann-title inline-bnner">
                                       <div> Banner </div> 
                                       <div>
                                                    <div className="activate-module postion-btn">
                                                        {
                                                            <label className="global-switch">
                                                                <input
                                                                    type="checkbox"
                                                                    checked={BannerData.key_value == "1" ? false : true}
                                                                    onChange={this.handleModuleChange}
                                                                />
                                                                <span className="switch-slide round">
                                                                    <span className={`switch-on ${BannerData.key_value == "1" ? 'active' : ''}`}>ON</span>
                                                                    <span className={`switch-off ${BannerData.key_value == "0" ? 'active' : ''}`}>OFF</span>
                                                                </span>
                                                            </label>
                                                        }
                                                    </div>
                                       </div>
                                    </div>
                                        <div className="hp-up-img-btn img-new-upload">
                                                    <div className={BannerData.key_value === "1" ? 'hp-up-img' :"hp-up-img disable_image"}>
                                           
                                                <span className="hp-up-img-txt">
                                                <img className="def-addphoto" src={Images.IMAGE_GALLARY} alt="" />
                                                    Upload Image [1024*375]
                                                </span>
                                                    <Input
                                                        type="file"
                                                            disabled={BannerData.key_value === "1" ? '' : 'disabled'}
                                                        name='PageHubImg'
                                                        className="ph-img"
                                                        onChange={(e) => this.onChangeImage(e, 3)}
                                                    />
                                                {
                                                BannerData.key_value === "1" &&
                                                (!_.isEmpty(BannerData.image)) &&
                                                <div
                                                    className="hp-revert-div-txt revert-text"
                                                    onClick={() => this.revertOrigional(BannerData, 2)}
                                                >
                                                    Revert to orignal
                                                </div>
                                                }
                                              
                                            </div>
                                            
                                    
                                        {/* <div className="hp-size-txt">Size - 1024*375</div> */}
                                        <div className="box_one">
                                            <figure className="hp-img-box width-change">
                                                {
                                                    (!_.isEmpty(BannerData.image)) ?
                                                        <img
                                                            className="img-cover"
                                                            src={(BannerData.image) ? NC.S3 + NC.SETTING_IMG_PATH + BannerData.image : Images.no_image}
                                                            alt=""
                                                        />
                                                        :
                                                        (_.isEmpty(BannerData.image)) ?
                                                            ''
                                                            :
                                                            <Loader />
                                                }
                                            </figure>
                                        </div>

                                      
                                        </div>

                                    </Col>
                                </Row>
                            }
                        </div>
                        </Col>
                </Row>
                {/* <Row>
                    <Col md={12}>
                        <div className="hp-bg-box mt-30">
                            <Row>
                                <Col md={12}>
                                    <div className="hp-bann-title float-left">
                                        Banner
                                    </div>
                                    <div className="activate-module float-right">
                                        {
                                            <label className="global-switch">
                                                <input
                                                    type="checkbox"
                                                    checked={BannerData.key_value == "1" ? false : true}
                                                    onChange={this.handleModuleChange}
                                                />
                                                <span className="switch-slide round">
                                                    <span className={`switch-on ${BannerData.key_value == "1" ? 'active' : ''}`}>ON</span>
                                                    <span className={`switch-off ${BannerData.key_value == "0" ? 'active' : ''}`}>OFF</span>
                                                </span>
                                            </label>
                                        }
                                    </div>
                                </Col>
                            </Row>
                            {
                                BannerData.key_value === "1" &&
                                <Row>
                                    <Col md={2} className='d-f'>
                                        <label htmlFor="SportsHubButton">Banner Image</label>
                                        <div className='info-banner-2 ml10'>
                                            <i className="icon-info" id="TooltipExample2"></i>
                                            <Tooltip placement="top" isOpen={this.state.tooltipOpen2} target="TooltipExample2" toggle={() => { (this.toggle('tooltipOpen2')) }}>
                                                Diplayed the banner images on the sports hub page
                                            </Tooltip>
                                        </div>
                                    </Col>
                                    <Col md={10}>
                                        <div className="hp-up-img-btn">
                                            <div className="hp-up-img">
                                                <span className="hp-up-img-txt">
                                                    Upload Image
                                                </span>
                                                <Input
                                                    type="file"
                                                    name='PageHubImg'
                                                    className="ph-img"
                                                    onChange={(e) => this.onChangeImage(e, 3)}
                                                />
                                            </div>
                                            {
                                                (!_.isEmpty(BannerData.image)) &&
                                                <div
                                                    className="hp-revert-div-txt"
                                                    onClick={() => this.revertOrigional(BannerData, 2)}
                                                >
                                                    Revert to orignal
                                                </div>
                                            }
                                        </div>
                                        <div className="hp-size-txt">Size - 1024*375</div>
                                        <figure className="hp-img-box">
                                            {
                                                (!_.isEmpty(BannerData.image)) ?
                                                    <img
                                                        className="img-cover"
                                                        src={(BannerData.image) ? NC.S3 + NC.SETTING_IMG_PATH + BannerData.image : Images.no_image}
                                                        alt=""
                                                    />
                                                    :
                                                    (_.isEmpty(BannerData.image)) ?
                                                        ''
                                                        :
                                                        <Loader />
                                            }
                                        </figure>

                                    </Col>
                                </Row>
                            }
                        </div>
                    </Col>
                </Row> */}
                {
                    _.map(ConfigureList, (item, idx) => {
                        // console.log(idx, 'idx')
                        let titleName = ""
                        if (!_.isUndefined(item.game_key)) {
                            let rmAllpw = item.game_key.replace('allow_', ' ')
                            titleName = rmAllpw.replace(/_/g, ' ').toUpperCase()
                        }
                        return (
                            <div key={idx} className="hp-dy-banners">
                                {/* <Row>
                                    <Col md={12}>
                                        <div className="hp-dy-title float-left">
                                            {titleName}
                                        </div>
                                    </Col>
                                </Row> */}
                                <Row className="mb-5">
                                    <Col md={12}>
                                       
                                    <div className="hp-dy-title float-left">
                                                        {titleName}
                                                    </div>
                                        <div className="hub-title">
                                                   
                                                  
                                                    <div className="title-arrngr">
                                                    <label className="hp-dy-label" htmlFor="language">Title</label>
                                                        <Input
                                                            maxLength={30}
                                                            type="text"
                                                            placeholder="Daily Fantasy Image"
                                                            name={LanguageType + '_title'}
                                                            value={item[LanguageType + '_title']}
                                                            onChange={(e) => this.handleInputChange(e, idx)}
                                                        />
                                                    </div>
                                                
                                        
                                            
                                    
                                            {/* <div className="hub-title"> */}
                                                
                                                    {/* <Col md={3} className='d-f'>
                                                        <label className="hp-dy-label" htmlFor="SportsHubButton">Image</label>
                                                        <div className='info-banner-2 ml10'>
                                                            <i className="icon-info" id={"TooltipExample3" + idx}></i>
                                                            <Tooltip placement="top" isOpen={this.state['tooltip' + idx]} target={"TooltipExample3" + idx} toggle={() => { (this.toggle('tooltip' + idx)) }}>
                                                                {
                                                                    titleName == ' DFS' ? "Displayed as a Game Card for DFS Game type" :
                                                                        titleName == ' PICKEM' ? "Displayed as a Game Card for Pick'em Game type" :
                                                                            titleName == ' EQUITY' ? "Displayed as a Game Card for Stock Equity Game type" :
                                                                                titleName == ' STOCK PREDICT' ? "Displayed as a Game Card for Pick'em Game type" :
                                                                                    titleName == ' FREE2PLAY' ? "Displayed as a Game Card for Free2Play Game type" :
                                                                                        titleName == ' MULTIGAME' ? "Displayed as a Game Card for Multigame Game type" :
                                                                                            titleName == ' LIVE FANTASY' ? "Displayed as a Game Card for Live Fantasy Game type" :
                                                                                                titleName == ' OPEN PREDICTOR' ? "Displayed as a Game Card for Open Predictor Game type" :
                                                                                                    titleName == ' FIXED OPEN PREDICTOR' ? "Displayed as a Game Card for Fixed Open Predictor Game type" :
                                                                                                        titleName == ' PREDICTION' ? "Displayed as a Game Card for Predict and Win Game type" :
                                                                                                            'We will update soon '

                                                                }

                                                            </Tooltip>
                                                        </div>

                                                    </Col> */}
                                                    <div className="hp-up-img-btn">
                                                            <div className="hp-up-img">
                                                                <span className="hp-up-img-txt">
                                                                <img className="def-addphoto" src={Images.IMAGE_GALLARY} alt="" />
                                                                    Upload Image 
                                                            
                                                                      [670x576
                                                                        {/* {
                                                                            item.game_key === 'allow_dfs' ?
                                                                                '1100x560'
                                                                                :
                                                                                '630x630'
                                                                        }  */}
                                                                        ] 
                                                       
                                                                </span>
                                                                <Input
                                                                    accept="image/*"
                                                                    type="file"
                                                                    name='PageHubImg'
                                                                    className="ph-img"
                                                                    onChange={(e) => this.onChangeGTypeImg(e, idx, item.game_key)}
                                                                />
                                                            </div>
                                                            {/* {
                                                                (!_.isEmpty(item.image)) &&
                                                                <div
                                                                    className="hp-revert-div-txt"
                                                                    onClick={() => this.revertOrigional(item, 1)}
                                                                >
                                                                    Revert to orignal
                                                                </div>
                                                            } */}
                                                        </div>
                                                    <div className="imge-des">
                                                        {/* <div className="hp-up-img-btn">
                                                            <div className="hp-up-img">
                                                                <span className="hp-up-img-txt">
                                                                    Upload Image 
                                                            
                                                                        {
                                                                            item.game_key === 'allow_dfs' ?
                                                                                'Size - 1100x560'
                                                                                :
                                                                                'Size - 630x630'
                                                                        }
                                                       
                                                                </span>
                                                                <Input
                                                                    accept="image/*"
                                                                    type="file"
                                                                    name='PageHubImg'
                                                                    className="ph-img"
                                                                    onChange={(e) => this.onChangeGTypeImg(e, idx, item.game_key)}
                                                                />
                                                            </div>
                                                        
                                                        </div> */}
                                                        
                                                        <div>
                                                            <figure className="hp-ban-img-box">
                                                                {
                                                                    (!_.isEmpty(item.image) && item.ImagePosting) ?
                                                                        <img
                                                                            className="img-cover"
                                                                            src={(item.image) ? NC.S3 + NC.SETTING_IMG_PATH + item.image : Images.no_image}
                                                                            alt=""
                                                                        />
                                                                        :
                                                                        (_.isEmpty(item.image) && item.ImagePosting) ?
                                                                            ''
                                                                            :
                                                                            <Loader />
                                                                }
                                                            </figure>
                                                        </div>
                                                    </div>
                                                
                                            {/* </div> */}
                                            {ConfigureList.length > 2 &&
                                            <div className="arrow-hub-btn">
                                               
                                                {idx != Object.keys(ConfigureList)[Object.keys(ConfigureList).length - 1] &&
                                                    < span className="icon-circle-down" onClick={() => this.moveTodown(item, idx, 'move_down')} > </span>
                                                     }
                                                { idx != 0 &&
                                                    <span onClick={() => this.moveToUp(item, idx ,'move_up')} className="icon-circle-up"></span>
                                                 }
                                              
                                            </div>
                                            }
                                            <div className="submit-hub-btn">
                                            <Button
                                                disabled={item.formValid}
                                                className="btn-secondary-outline float-right"
                                                onClick={() => this.updateConfiguration(item, idx)}
                                            >Save
                                            </Button>
                                        </div>
                                        </div>
                                        
                                    </Col>
                                </Row>
                                {/* <Row>
                                    <Col md={12}>
                                        <Button
                                            disabled={item.formValid}
                                            className="btn-secondary-outline float-right"
                                            onClick={() => this.updateConfiguration(item, idx)}
                                        >Save
                                        </Button>
                                    </Col>
                                </Row> */}
                            </div>
                        )
                    })
                }
                </div> : <Loader />
            
  
        )
    }
}