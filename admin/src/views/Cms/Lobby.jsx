import React, { Component, Fragment } from "react";
import { Row, Col, Input, Button,Tooltip } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import Select from 'react-select';
import Images from '../../components/images';
import Loader from "../../components/Loader";
import RLDD from 'react-list-drag-and-drop/lib/RLDD';
import HF from '../../helper/HelperFunction';
import SelectDropdown from "../../components/SelectDropdown";
export default class HubPage extends Component {
    constructor(props) {
        super(props)
        this.toggle = this.toggle.bind(this);

        this.state = {
            LanguageType: 'en',
            languageOptions: HF.getLanguageData() ? HF.getLanguageData() : [],
            ConfigureList: [],
            SportsList: [],
            sportPosting: true
        }
    }
    componentDidMount() {
        this.getSportsDName()
        this.getConfiguration()
    }

    getSportsDName() {
        let { LanguageType } = this.state
        let params = {
            "language": LanguageType
        }
        WSManager.Rest(NC.baseURL + NC.GET_SPORTS_DISPLAY_NAME, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let ResponseData = responseJson.data
                let TempSport = []
                let TempSportDict = {}
                _.map(ResponseData, (sportList, idx) => {
                    TempSportDict = {
                        "id": parseInt(sportList.sports_id),
                        "title": sportList.display_name,
                        "display_name": sportList[LanguageType + '_display_name']
                    }
                    TempSport.push(TempSportDict)
                })
                this.setState({
                    SportsList: TempSport
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    toggle(from) {
        this.setState({
            [from]: !this.state[from]
        });
    }

    handleLangChange = (value) => {
        if (!_.isNull(value)) {
            this.setState({ LanguageType: value.value }, () => {
                this.getConfiguration()
                this.getSportsDName()
            })
        }
    }

    getConfiguration() {
        let params = {
            "language": this.state.LanguageType
        }

        WSManager.Rest(NC.baseURL + NC.GET_BANNER_IMAGE_DATA, params).then(Response => {
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

    updateConfiguration() {
        this.setState({ sportPosting: true })
        let { LanguageType, SportsList } = this.state
        let fUpArr = []
        let fDict = {}

        _.map(SportsList, (item, idx) => {
            fDict = {
                "order": idx + 1,
                "sports_id": item.id,
                "display_name": item.title,
                [LanguageType + '_display_name']: item.display_name
            }
            fUpArr.push(fDict)
        })

        WSManager.Rest(NC.baseURL + NC.UPDATE_SPORTS_DISPLAY_NAME, fUpArr).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.setState({ sportPosting: false })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleModuleChange = (key_value, idx, key_name) => {
        let { ConfigureList } = this.state
        ConfigureList[idx]['key_value'] = key_value == "1" ? "0" : "1";
        this.setState({
            ConfigureList
        }, () => {
            this.updateBannerStatus(idx, key_name)
        })
    }

    updateBannerStatus = (idx, key_name) => {
        this.setState({ toggPosting: true })
        let params = {
            "key_name": key_name,
            "status": this.state.ConfigureList[idx]['key_value']
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

    revertOrigional = (item, idx) => {
        this.setState({ ImagePosting: false })
        let params = {
            "image": item.image,
            "key_name": item.key_name
        }
        WSManager.Rest(NC.baseURL + NC.SETT_REMOVE_BANNER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000)

                let tempConImg = this.state.ConfigureList
                tempConImg[idx].image = responseJson.data.image_name
                tempConImg[idx].formValid = false
                this.setState({
                    ConfigureList: tempConImg,
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
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

        var data = new FormData();
        data.append("userfile", file);
        data.append("key_name", key_name);

        WSManager.multipartPost(NC.baseURL + NC.SETT_BANNER_UPLOAD, data)
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

    handleInputChange = (e, idx) => {
        let value = e.target.value;
        let SportsList = this.state.SportsList;
        SportsList[idx]['display_name'] = value;
        this.setState({
            SportsList,
            sportPosting: false
        });
    }

    handleRLDDChange = (reorderedItems: Array<Item>) => {
        this.setState({ SportsList: reorderedItems, sportPosting: false });
    };

    render() {
        const { languageOptions, LanguageType, SportsList, ConfigureList, sportPosting } = this.state
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
            <div className="hub-page lobby-page">
                <h2 className="h2-cls">Lobby</h2>
                <Row className="hp-lang">
                    <Col md={3}>
                        <label htmlFor="language">Language</label>
                        <SelectDropdown SelectProps={Select_Props} />
                    </Col>
                    <Col md={9}></Col>
                </Row>
                <div className="hp-dy-banners">
                    <Row>
                        <Col md={12}>
                            <div className="hp-dy-title float-left">
                                Sports name update
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="sn-item mb-3">
                                <Row>
                                    <Col md={9}>
                                        <Row>
                                            <Col md={2}>Position</Col>
                                            <Col md={3}>Sports Name</Col>
                                            <Col md={4}>Display Name</Col>
                                        </Row>
                                    </Col>
                                    <Col md={3}></Col>
                                </Row>
                            </div>
                            <div className="sn-list handle">
                                <Row>
                                    <Col md={9}>
                                        {
                                            _.map(SportsList, (item, idx) => {
                                                return (
                                                    <Row className="mb-2" key={idx}>
                                                        <Col md={2}>
                                                            <span className="ml-2">
                                                                {idx + 1}
                                                            </span>
                                                        </Col>
                                                        <Col md={3}>
                                                            <span>
                                                                {item.title}
                                                            </span>
                                                        </Col>
                                                        <Col md={4}>
                                                            <Input
                                                                type="text"
                                                                placeholder="Cricket"
                                                                name={item.display_name}
                                                                value={item.display_name}
                                                                onChange={(e) => this.handleInputChange(e, idx)}
                                                            />
                                                        </Col>
                                                    </Row>
                                                )
                                            })
                                        }
                                    </Col>
                                    <Col md={3} className="ml-xxxl">
                                        <RLDD
                                            items={SportsList}
                                            itemRenderer={(item, idx) => {
                                                return (
                                                    <div className="sw-po-icon icon-switch-pos"></div>
                                                );
                                            }}
                                            onChange={this.handleRLDDChange}
                                        />
                                    </Col>
                                </Row>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="float-right">
                                <Button
                                    disabled={sportPosting}
                                    className="btn-secondary-outline"
                                    onClick={() => this.updateConfiguration()}
                                >Save
                                </Button>
                            </div>
                        </Col>
                    </Row>
                </div>
                {
                    _.map(ConfigureList, (item, idx) => {
                        let titleName = ""
                        if (!_.isUndefined(item.key_name)) {
                            let rmAllpw = item.key_name.replace('_bnr', ' ')
                            titleName = rmAllpw.replace(/_/g, ' ').toUpperCase()
                        }
                        return (
                            <div key={idx} className="hp-dy-banners">
                                <Row>
                                    <Col md={12}>
                                        <div className="hp-dy-title float-left">
                                            {titleName}

                                        </div>
                                        <div className="activate-module float-right">
                                            <label className="global-switch">
                                                <input
                                                    type="checkbox"
                                                    checked={item.key_value == "1" ? false : true}
                                                    onChange={() => this.handleModuleChange(item.key_value, idx, item.key_name)}
                                                />
                                                <span className="switch-slide round">
                                                    <span className={`switch-on ${item.key_value == "1" ? 'active' : ''}`}>ON</span>
                                                    <span className={`switch-off ${item.key_value == "0" ? 'active' : ''}`}>OFF</span>
                                                </span>
                                            </label>
                                        </div>
                                    </Col>
                                </Row>
                                {
                                    item.key_value === "1" &&
                                    <Fragment>
                                        <Row>
                                            <Col md={2} className='d-f'>
                                                <label className="hp-dy-label" htmlFor="SportsHubButton">Banner Image</label>
                                                <div className='info-banner-2 ml10'>
                                                    <i className="icon-info" id={"TooltipExample3" + idx}></i>
                                                    <Tooltip placement="top" isOpen={this.state['tooltip' + idx]} target={"TooltipExample3" + idx} toggle={() => { (this.toggle('tooltip' + idx)) }}>
                                                        {
                                                            titleName == 'ALLOW DFS ' ? "Displayed underneath the prediction cards" :
                                                                titleName == 'ALLOW PRIZE ' ? "Displayed in the Predict and Win lobby page in between the prediction questions." :
                                                                    titleName == 'ALLOW SPORTS PREDICTION ' ? "Displayed on a lobby underneath the fixture cards" :
                                                                     'We will update soon '

                                                        }

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
                                                            accept="image/*"
                                                            type="file"
                                                            name='PageHubImg'
                                                            className="ph-img"
                                                            onChange={(e) => this.onChangeGTypeImg(e, idx, item.key_name)}
                                                        />
                                                    </div>
                                                    {(!_.isEmpty(item.image)) && <div
                                                        className="hp-revert-div-txt"
                                                        onClick={() => this.revertOrigional(item, idx)}
                                                    >
                                                        Revert to orignal
                                                    </div>}
                                                </div>
                                                <div className="hp-size-txt">Size - 1024*375</div>
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
                                            </Col>
                                        </Row>
                                    </Fragment>
                                }
                            </div>
                        )
                    })
                }
            </div>
        )
    }
}
