import React, { Component, Fragment } from "react";
import { Row, Col, Table, Input, Button, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalHeader, ModalBody, ModalFooter, Tooltip } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import Select from 'react-select';
import Images from '../../components/images';
import HelperFunction, { _filter } from "../../helper/HelperFunction";

export default class LobbyBanner extends Component {
    constructor(props) {
        super(props)
        this.toggle = this.toggle.bind(this);
        this.state = {
            TotalBanner: 0,
            tooltipOpen: false,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            BannerType: [],
            NewBannerToggle: false,
            AddBannerPosting: false,
            target_url: '',
            banner_name: '',
            fileUplode: '',
            fileName: '',
            TempFileName: '',
            validURL: false,
            MasterScoringRules: [],
            ActionPosting: false,
            BannerOption: [],
            AllBannerOption: [],
            gameOptions: [],
            FixtureOption: [],
            selBnrId:'',
            SelFixScdDate: ''
        }
        this.onChangeImage = this.onChangeImage.bind(this);
        this.resetFile = this.resetFile.bind(this);
    }
    componentDidMount() {
        this.getLobbyBanner()
        this.getBannerType()
    }

    toggle() {
        this.setState({
            tooltipOpen: !this.state.tooltipOpen
        });
    }

    getLobbyBanner() {
        const { PERPAGE, CURRENT_PAGE } = this.state
        const param = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: "DESC",
            sort_field: "banner_id"
        }

        WSManager.Rest(NC.baseURL + NC.GET_BANNERS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    MasterScoringRules: responseJson.data,
                    TotalBanner: responseJson.data.total
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }
    getBannerType() {
        WSManager.Rest(NC.baseURL + NC.GET_BANNER_TYPE, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var BannerList = [];
                // _.map(responseJson.data.banner_type, function (item) {
                //     if(HelperFunction.allowDFS() == "0" && item.banner_type_id == 1  ){


                _.map(responseJson.data.banner_type, function (item) {
                    if(
                        HelperFunction.allowDFS() == "0" && (
                        item.banner_type_id == 1
                        || item.banner_type_id == 7
                        || item.banner_type_id == 8
                        )
                    ){
                    }
                    else{
                        BannerList.push({
                            value: item.banner_type_id,
                            label: item.banner_type
                        });
                    }
                  
                })
                var gameList =[
                    {
                        value: 0,
                        label: 'All'
                    }
                ];
                _.map(responseJson.data.game_type, function(item){
                    gameList.push({
                        value: item.sports_hub_id,
                        label: item.en_title
                    })
                })
                this.setState({
                    BannerType: responseJson.data,
                    AllBannerOption: BannerList,
                    gameOptions: gameList
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    getFixtureType() {
        let params = {
            name: "",
            target_url: "",
            banner_type_id: "1",
            collection_master_id: "",
            image: "",
            is_preview: 0,
            is_remove: 0,
            uploadbtn: 1,
            image_name: "",
            size_tip: ""
        }
        WSManager.Rest(NC.baseURL + NC.GET_ALL_UPCOMING_COLLECTIONS, params).then((responseJson) => {

            if (responseJson.response_code === NC.successCode) {
                var FixtureList = [];
                _.map(responseJson.data, function (item) {
                    FixtureList.push({
                        value: item.collection_master_id,
                        label: item.collection_name + ' ' + item.season_schedule_date,
                        scheduleDate: HelperFunction.getFormatedDateTime(item.scheduled_date),
                        // utcScheduledDate: item.scheduled_date
                    });
                })
                this.setState({
                    BannerType: responseJson.data,
                    FixtureOption: FixtureList,
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    NewBannerTogle(flag) {
        this.setState({ NewBannerToggle: flag })
    }
    handleBannerType(name, value, schlDate) {
        let BnrOpt = this.state.AllBannerOption
        this.setState({ [name]: value }, () => {
            if(name == 'SelectGameType'){
                if(value == 0){
                    BnrOpt = _filter(BnrOpt, (obj) => {
                        return obj.value != '1'
                    });
                }
                if(value == 2){
                    BnrOpt = _filter(BnrOpt, (obj) => {
                        return (obj.value != '7' && obj.value != '8')
                    });
                }
                if(value != 2 && value != 0){
                    BnrOpt = _filter(BnrOpt, (obj) => {
                        return (obj.value != '7' && obj.value != '8' && obj.value != '1')
                    });
                }
                this.setState({
                    BannerOption: BnrOpt
                })
            }
            if(name == 'SelectFixtureType'){
                this.setState({
                    SelFixScdDate: schlDate
                })
            }
            this.validateForm()
            if (value == 1)
                this.getFixtureType()
        })
    }
    handleGameType(name, value, schlDate) {
        this.setState({ [name]: value }, () => {
            if(name == 'SelectFixtureType'){
                this.setState({
                    SelFixScdDate: schlDate
                })
            }
            this.validateForm()
            if (value == 1)
                this.getFixtureType()
        })
    }
    handleNameChange = e => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value })
        if (name == "target_url" && !value.match(/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/)) {
            this.setState({
                validURL: true
            })
        } else {
            this.setState({
                validURL: false
            }, function () {
                this.validateForm()
            })

        }

    }
    validateForm() {
        const { SelectBannerType, SelectGameType, banner_name, SelectFixtureType, target_url, TempFileName } = this.state
        this.setState({ AddBannerPosting: false })

        if (SelectBannerType == 1 && (!_.isEmpty(TempFileName)) && (!_.isEmpty(SelectBannerType) && !_.isEmpty(banner_name) && !_.isEmpty(SelectFixtureType))) {
            this.setState({ AddBannerPosting: true })
        }
        else if (SelectBannerType == 4 && (!_.isEmpty(TempFileName)) && !_.isEmpty(SelectBannerType) && !_.isEmpty(banner_name) && !_.isEmpty(target_url)) {
            this.setState({ AddBannerPosting: true })
        }
        else if (!_.isEmpty(SelectBannerType) && (!_.isEmpty(TempFileName)) && !_.isEmpty(banner_name) && SelectBannerType != 1 && SelectBannerType != 4) {
            this.setState({ AddBannerPosting: true })
        }
    }

    onChangeImage = (event) => {
        this.setState({
            TempFileName: URL.createObjectURL(event.target.files[0]),
        }, function () {
            this.validateForm()
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        data.append("banner_type_id", this.state.SelectBannerType);
        WSManager.multipartPost(NC.baseURL + NC.LOBBY_IMAGE_UPLOAD, data)
            .then(responseJson => {
                this.setState({
                    fileName: responseJson.data.image_url,
                    fileUplode: responseJson.data.image_url,
                    ImageName: responseJson.data.image
                });
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile(event) {
        event.preventDefault();
        document.getElementById("banner_image").value = "";
        this.setState({
            fileName: null,
            fileUplode: null,
        }, function () {
            this.validateForm()
        });
    }

    createBanner() {
        this.setState({ AddBannerPosting: false })
        let { banner_name, target_url, SelectBannerType, SelectGameType, fileUplode, ImageName, SelectFixtureType ,SelFixScdDate} = this.state
        let param = {
            name: banner_name,
            target_url: target_url,
            banner_type_id: SelectBannerType,
            game_type_id: SelectGameType,
            collection_master_id: "",
            image: fileUplode,
            image_name: ImageName,
            is_preview: 1,
            is_remove: 1,
            uploadbtn: 0,
            size_tip: "",
            collection_master_id: SelectFixtureType,
            scheduled_date: SelectBannerType == 1 ? SelFixScdDate : ''
        }
        WSManager.Rest(NC.baseURL + NC.CREATE_BANNER, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000);
                this.setState({
                    fileName: null,
                    fileUplode: null,
                    target_url: '',
                    banner_name: '',
                    SelectFixtureType: '',
                    SelectBannerType: '',
                    SelectGameType: '',
                }, function () {
                    this.getLobbyBanner()
                    this.NewBannerTogle(false)
                });
            }
            this.setState({ AddBannerPosting: true })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    deleteToggle = (setFalg, idx, deleteId,banner_type_id) => {
        if (setFalg) {
            this.setState({
                deleteIndex: idx,
                deleteId: deleteId,
                selBnrId: banner_type_id || ''
            })
        }
        this.setState(prevState => ({
            DeleteModalOpen: !prevState.DeleteModalOpen
        }));
    }

    deleteAppBanner = () => {
        const { MasterScoringRules, deleteId, deleteIndex,selBnrId } = this.state
        this.setState({ DeleteActionPosting: true })
        
        const param = {
            banner_id: deleteId,
            banner_type_id: selBnrId
        }

        let tempBannerList = MasterScoringRules
        WSManager.Rest(NC.baseURL + NC.DELETE_LOBBY_BANNER, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _.remove(tempBannerList, function (item, idx) {
                    return idx == deleteIndex
                })
                this.deleteToggle(false, {}, {})
                notify.show("Banner deleted", "success", 5000);
                this.setState({
                    MasterScoringRules: tempBannerList,
                    DeleteActionPosting: false
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    updateBannerStatus = (idx, banner_id, status, banner_type_id) => {
        this.setState({ ActionPosting: true })
        let tempBannerList = this.state.MasterScoringRules
        const param = {
            status: status,
            banner_id: banner_id,
            banner_type_id: banner_type_id
        }
        WSManager.Rest(NC.baseURL + NC.LOBBY_UPDATE_STATUS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000);
                tempBannerList[idx].status = status
                this.setState({
                    MasterScoringRules: tempBannerList,
                    ActionPosting: false
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }


    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getLobbyBanner();
        });
    }
    render() {
        const { MasterScoringRules, NewBannerToggle, AddBannerPosting, SelectBannerType,SelectGameType, banner_name, SelectFixtureType, target_url, validURL, ActionPosting, DeleteActionPosting, BannerOption, gameOptions, FixtureOption } = this.state
        return (
            <Fragment>
                {
                    !NewBannerToggle &&
                    <div className="mt-4">
                        <Row>
                            <Col md={12}>
                                <h1 className="h1-cls">Manage Banner</h1>
                            </Col>
                        </Row>
                        <Row className="filters-box">
                            <Col md={12}>

                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.NewBannerTogle(true)}>New Banner</Button>
                                </div>
                                <div className='info-banner-2'>
                                    <i className="icon-info info-icon-banner" id="TooltipExample"></i>
                                    <Tooltip placement="bottom" isOpen={this.state.tooltipOpen} target="TooltipExample" toggle={this.toggle}>
                                        <p>
                                            Sports Hub Featured: Displayed on the top of Sports Hub page <br></br> <br></br>

                                            Sports Hub Ads: Displayed at the bottom of Sports Hub page<br></br> <br></br>

                                            All other categories: Displayed on the top in DFS Lobby page
                                        </p>
                                    </Tooltip>
                                </div>

                            </Col>
                        </Row>
                        <Row className="animated fadeIn new-banner">
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="left-th pl-4">Banner Type</th>
                                            <th>Name</th>
                                            <th>Target Url</th>
                                            <th>Image</th>
                                            <th>Sports Name</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    {
                                        _.map(MasterScoringRules, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td className="pl-4">{item.banner_type}</td>
                                                        <td>{item.name}</td>
                                                        <td>{item.target_url ? item.target_url : '--'}</td>
                                                        <td>
                                                            <figure className="lobby-banner-img">
                                                                <img src={item.image ? item.image : Images.no_image} className="img-cover" alt="" />   </figure>
                                                        </td>
                                                        <td>
                                                            {item.sports_name && item.sports_name}
                                                        </td>
                                                        <td>
                                                            {item.status == 1 ?
                                                                <i className="icon-verified active"></i>
                                                                :
                                                                <i className="icon-inactive"></i>
                                                            }
                                                        </td>
                                                        <td>
                                                            <UncontrolledDropdown>
                                                                <DropdownToggle disabled={ActionPosting} className="icon-action" />
                                                                <DropdownMenu>
                                                                    {item.status == 1
                                                                        ?
                                                                        <DropdownItem onClick={() => this.updateBannerStatus(idx, item.banner_id, 0, item.banner_type_id)}>Deactivate</DropdownItem>
                                                                        :
                                                                        <DropdownItem onClick={() => this.updateBannerStatus(idx, item.banner_id, 1, item.banner_type_id)}>Active</DropdownItem>
                                                                    }
                                                                    <DropdownItem onClick={() => { this.deleteToggle(true, idx, item.banner_id,item.banner_type_id) }}>Delete</DropdownItem>
                                                                </DropdownMenu>
                                                            </UncontrolledDropdown>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                    }
                                </Table>
                            </Col>
                        </Row>
                        <div>
                            <Modal isOpen={this.state.DeleteModalOpen} toggle={this.deleteToggle}>
                                <ModalHeader>Delete App Banner</ModalHeader>
                                <ModalBody>Are you sure to delete this App banner data?</ModalBody>
                                <ModalFooter>
                                    <Button disabled={DeleteActionPosting} color="secondary" onClick={() => this.deleteAppBanner()}>Yes</Button>{' '}
                                    <Button color="primary" onClick={this.deleteToggle}>No</Button>
                                </ModalFooter>
                            </Modal>
                        </div>
                    </div>
                }

                {
                    NewBannerToggle &&
                    <div className="mt-4">
                        <Row>
                            <Col md={12}>
                                <h1 className="h1-cls">New Banner</h1>
                            </Col>
                        </Row>
                        <div className="animated fadeIn new-banner">
                            {<Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Game Type <span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        {/* <i className="icon-Shape"></i> */}
                                        <Select
                                            name="GameType"
                                            searchable={true}
                                            clearable={false}
                                            options={gameOptions}
                                            placeholder="Select Game Type"
                                            menuIsOpen={true}
                                            value={SelectGameType}
                                            onChange={(e) => this.handleBannerType('SelectGameType', e.value)}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            }
                            {
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Banner Type <span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        {/* <i className="icon-Shape"></i> */}
                                        <Select
                                            name="BannerType"
                                            searchable={true}
                                            clearable={false}
                                            options={BannerOption}
                                            placeholder="Select Banner Type"
                                            menuIsOpen={true}
                                            value={SelectBannerType}
                                            onChange={(e) => this.handleBannerType('SelectBannerType', e.value)}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            }
                            {
                                SelectBannerType == 1 &&
                                <Col md={12} className="input-row">
                                    <Row>
                                        <Col md={3} className="b-input-label">Fixtures<span className="asterrisk">*</span></Col>
                                        <Col md={9}>
                                            {/* <i className="icon-Shape"></i> */}
                                            <Select
                                                searchable={true}
                                                clearable={false}
                                                options={FixtureOption}
                                                placeholder="Select Fixtures"
                                                menuIsOpen={true}
                                                value={SelectFixtureType}
                                                onChange={(e) => this.handleBannerType('SelectFixtureType', e.value,e.scheduleDate)}
                                            />
                                        </Col>
                                    </Row>
                                </Col>
                            }
                            {
                                (SelectBannerType == 4 || SelectBannerType == 7 || SelectBannerType == 8) &&
                                <Col md={12} className="input-row">
                                    <Row>
                                        <Col md={3} className="b-input-label">Target Url<span className="asterrisk">*</span></Col>
                                        <Col md={9}>
                                            <Input
                                                type="text"
                                                name='target_url'
                                                placeholder="Target Url"
                                                onChange={this.handleNameChange}
                                                value={target_url}
                                            />
                                            {validURL &&
                                                <span className="error-text">Please upload valid target URL</span>
                                            }
                                        </Col>
                                    </Row>
                                </Col>
                            }
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Name <span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="text"
                                            name='banner_name'
                                            placeholder="Banner Name"
                                            onChange={this.handleNameChange}
                                            value={banner_name}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            <Col md={12} className="input-row">
                                <Row>
                                   
                                    <Col md={3} className="b-input-label">Upload Image  (1300 X 240, PNG) <span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="file"
                                            name='banner_image'
                                            id="banner_image"
                                            onChange={this.onChangeImage}
                                        />
                                        {this.state.fileName && (
                                            <div className="banner-img">
                                                <Button className="btn-secondary mt-4 mb-3" onClick={this.resetFile}>Remove</Button>
                                                <img className="img-cover" src={this.state.fileName} />
                                            </div>
                                        )}
                                    </Col>
                                </Row>
                            </Col>
                            <Col md={12} className="banner-action">
                                <Button disabled={!AddBannerPosting} className="btn-secondary mr-3" onClick={() => this.createBanner()}>Save</Button>
                                <Button className="btn-secondary-outline" onClick={() => this.NewBannerTogle(false)}>Cancel</Button>
                            </Col>
                        </div>
                    </div>
                }
            </Fragment>
        )
    }
}