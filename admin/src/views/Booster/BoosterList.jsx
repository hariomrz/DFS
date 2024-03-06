import React, { Component } from "react";
import { Row, Col, Table, Button, Input } from "reactstrap";
import _ from 'lodash';
import { getBoosterList, getPositionList, saveBooster } from "../../helper/WSCalling";
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import HF, { _isEmpty } from "../../helper/HelperFunction";
import { MODULE_NOT_ENABLE, B_DISPLAY_NAME_RNG, B_DISPLAY_NAME_ALPHA, B_POINTS_DEC, B_POINTS_RNG } from "../../helper/Message";
import Images from "../../components/images";
import LS from 'local-storage';
import WSManager from "../../helper/WSManager";
import Select from 'react-select';
class BoosterList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            BoosterList: [],
            ListPosting: false,
            Total: 0,
            SelectedSport: LS.get('selected_sport') ? LS.get('selected_sport') : NC.sportsId,
            btnPosting: true,
        }
    }

    componentDidMount = () => {
        if (HF.allowBooster() != '1' || !(HF.allowBoosterInSports(this.state.SelectedSport))) {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getBooster()
        this.positionList()
    }

    positionList = () => {
        this.setState({ ListPosting: true })
        let params = { "sports_id": this.state.SelectedSport }
        getPositionList(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                let plist = []
                _.map(ApiResponse.data, (itm) => {
                    plist.push({
                        label: itm.position,
                        value: itm.position_id,
                    })
                })
                this.setState({ PositionList: plist })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getBooster = () => {
        this.setState({ ListPosting: true })
        let params = { "sports_id": this.state.SelectedSport }
        getBoosterList(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                this.setState({
                    BoosterList: ApiResponse.data ? ApiResponse.data : [],
                    ListPosting: false,
                    Total: ApiResponse.data ? ApiResponse.data.length : 0
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleChange = (value, idx) => {
        let blist = this.state.BoosterList
        blist[idx]['position_id'] = value.value
        this.setState({ SelectedSport: blist, btnPosting: false })
    }

    changeStatusToggle = (idx) => {
        let tempList = this.state.BoosterList
        tempList[idx]['status'] = tempList[idx]['status'] == '1' ? '0' : '1'
        this.setState({ BoosterList: tempList, btnPosting: false });
    }

    validate = (name, value, idx) => {
        let blist = this.state.BoosterList
        if (name == 'display_name' && (value.length < 1 || value.length > 15)) {
            notify.show(B_DISPLAY_NAME_RNG, "error", 3000)
            value = '';
        }
        else if (name == 'display_name' && HF.checkAlphabets(value)) {
            notify.show(B_DISPLAY_NAME_ALPHA, "error", 3000)
            value = blist[idx][name];
        }
        else if (name == 'points' && (value < 1 || value > 50)) {
            notify.show(B_POINTS_RNG, "error", 3000)
            value = '';
        }
        else if (name == 'points' && HF.isFloat(value)) {
            notify.show(B_POINTS_DEC, "error", 3000)
            if ((value < 1 || value > 50)) {
                value = ''
            } else {
                value = HF.decimalValidate(value, 3);
            }
        }
        return value
    }

    handleInputChange = (e, idx) => {
        if (e) {
            let blist = this.state.BoosterList
            let name = e.target.name;
            let value = e.target.value;

            let val = this.validate(name, value, idx)
            blist[idx][name] = val

            this.setState({ BoosterList: blist, btnPosting: false })
        }
    }

    onChangeImage = (event, idx) => {
        let blist = this.state.BoosterList
        blist[idx]['imgPosting'] = true
        blist[idx]['image_name'] = ''
        this.setState({ BoosterList: blist });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);

        WSManager.multipartPost(NC.baseURL + NC.BSTR_DO_UPLOAD, data)
            .then(ApiResponse => {
                // if (ApiResponse.response_code == NC.successCode) {
                    blist[idx]['image_name'] = ApiResponse.data.file_name
                    blist[idx]['imgPosting'] = false
                    this.setState({ BoosterList: blist, btnPosting : false });
                // } else {
                //     notify.show(NC.SYSTEM_ERROR, "error", 3000);
                // }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    updateBooster = () => {
        let blist = this.state.BoosterList
        let flag = true
        let msg = ''
        _.map(blist, (itm) => {
            if (_isEmpty(itm.display_name)) {
                msg = 'Please enter display name for scoring parameters "' + itm.name + '"'
                flag = false
            }
            else if (_isEmpty(itm.points)) {
                msg = 'Please enter points for scoring parameters "' + itm.name + '"'
                flag = false
            }
        })
        if(!flag)
        {
            notify.show(msg, "error", 3000)
            return false
        }

        this.setState({ btnPosting: true })
        saveBooster(blist).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                notify.show(ApiResponse.message, "success", 3000)
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    render() {
        let { BoosterList, Total, ListPosting, PositionList, btnPosting } = this.state
        return (
            <React.Fragment>
                <div className="booster animate-left">
                    <Row>
                        <Col md={12}>
                            <h2 className="h2-cls">Booster Configuration</h2>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table className="mb-0">
                                <thead>
                                    <tr>
                                        <th>Scoring Parameters</th>
                                        <th>Icon</th>
                                        <th>Position</th>
                                        <th>Name</th>
                                        <th>Points</th>
                                        <th>Enable/Disable</th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _.map(BoosterList, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>{item.name}</td>
                                                        <td className="w-100-px">
                                                            <div className="bstr-upload">                                                                    
                                                                <div className="b-icon">
                                                                    {
                                                                        (item.imgPosting && _isEmpty(item.image_name)) ?
                                                                            <Loader hide />
                                                                            :
                                                                            <img
                                                                                src={item.image_name ? NC.S3 + NC.BOOSTER + item.image_name : Images.no_image}
                                                                                className="img-cover" alt=""
                                                                            />
                                                                    }
                                                                </div>
                                                                <div className="upload-btn">
                                                                    <Input
                                                                        type="file"
                                                                        name='banner_image'
                                                                        id="banner_image"
                                                                        onChange={(e) => this.onChangeImage(e, idx)}
                                                                    />
                                                                    <i className="icon-upload"></i>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <Select
                                                                searchable={false}
                                                                clearable={false}
                                                                className='b-pos-select'
                                                                options={PositionList}
                                                                value={item.position_id}
                                                                placeholder='Select'
                                                                onChange={e => this.handleChange(e, idx)}
                                                            />
                                                        </td>
                                                        <td className="padd-input">
                                                            <Input
                                                                maxLength={15}
                                                                type="text"
                                                                className="form-control"
                                                                name="display_name"
                                                                value={item.display_name}
                                                                onChange={(e) => this.handleInputChange(e, idx)}
                                                            />
                                                        </td>
                                                        <td className="padd-input">
                                                            <Input
                                                                type="number"
                                                                className="form-control"
                                                                name="points"
                                                                value={item.points}
                                                                onChange={(e) => this.handleInputChange(e, idx)}
                                                            />
                                                        </td>
                                                        <td>
                                                            <div className="activate-module">
                                                                <label className="global-switch">
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={item.status == "0" ? false : true}
                                                                        onChange={() => this.changeStatusToggle(idx)}
                                                                    />
                                                                    <span className="switch-slide round">
                                                                        <span className={`switch-on ${item.status == "0" ? 'active' : ''}`}></span>
                                                                        <span className={`switch-off ${item.status == "1" ? 'active' : ''}`}></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan="8">
                                                    {(Total == 0 && !ListPosting) ?
                                                        <div className="no-records">
                                                            {NC.NO_RECORDS}</div>
                                                        :
                                                        <Loader />
                                                    }
                                                </td>
                                            </tr>
                                        </tbody>
                                }
                            </Table>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="b-update-box">
                                <Button
                                    disabled={btnPosting}
                                    className="btn-secondary-outline"
                                    onClick={() => this.updateBooster()}
                                >
                                    Update
                                </Button>
                            </div>
                        </Col>
                    </Row>
                </div>
            </React.Fragment>
        )
    }
}
export default BoosterList
