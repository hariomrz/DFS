import React, { Component, Fragment } from 'react';
import { Row, Col, Button, Input } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import SelectDate from "../../components/SelectDate";
var current = new Date();
class CreateCampaign extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ToDate:  new Date(current.getTime() + 86400000).toISOString().split('T')[0],
            posting: false,
            FromDate:  new Date(current.getTime() + 86400000).toISOString().split('T')[0],
            url: '',
            source: '',
            medium: '',
            campaignName: '',
            signup: '',
            deposit_per: '',
            deposit_cap: '',
            game_cap: '',
            game_per: '',
            isUpdate: false
        }
    }

    componentDidMount = () => {
        console.log("======",this.props.location.item)
        if (this.props.location.item) {
            let propsData = this.props.location.item;
            this.setState({
                isUpdate: true,
                FromDate: new Date(propsData.expiry_date),
                url: propsData.url,
                source: propsData.source,
                medium: propsData.medium,
                campaignName: propsData.name,
                signup: JSON.parse(propsData.commission).signup,
                deposit_per: JSON.parse(propsData.commission).deposit_per,
                deposit_cap: JSON.parse(propsData.commission).deposit_cap,
                // game_cap: JSON.parse(propsData.commission).game_cap,
                game_per: JSON.parse(propsData.commission).game_per,
            })
        } else {
            this.setState({
                isUpdate: false
            })
        }
    }

    /***
  * HANDLE DATE CHANGE EVENT 
  */

    handleDate = (e, date_key) => {
        this.setState({
            [date_key]: e.toISOString().split('T')[0]
        })
    }

    /**
* HANDLE MOBILE INPUT VALIDATION 
*/

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value
        if (name == 'signup' || name == 'deposit_cap' || name == 'deposit_per' || name == 'game_per' || name == 'game_cap') {
            if (value < 0) {
                return;
            }
            if (name == 'deposit_per' || name == 'game_per') {
                if (value > 100) {
                    return;
                }
            }
        }

        this.setState({ [name]: value })
    }

    /****
     * VALIDATE FORM
     */
    _validateForm = () => {
        if (this.state.posting) {
            return;
        }
        if (this.state.url == undefined || this.state.url == '') {
            notify.show('Please enter url', "error", 3000)
            return;
        }
        if (!WSManager.isValidUrl(this.state.url)) {
            notify.show('Please enter valid url', "error", 3000)
            return;
        }
        if (this.state.FromDate == undefined || this.state.FromDate == '') {
            notify.show('Please select date', "error", 3000)
            return;
        }
        if (this.state.source == undefined || this.state.source == '') {
            notify.show('Please enter source', "error", 3000)
            return;
        }
        if (this.state.medium == undefined || this.state.medium == '') {
            notify.show('Please enter medium', "error", 3000)
            return;
        }
        if (this.state.campaignName == undefined || this.state.campaignName == '') {
            notify.show('Please enter campaign name', "error", 3000)
            return;
        }
        if (this.state.signup == undefined || this.state.signup == '') {
            notify.show('Please enter signup fixed amount', "error", 3000)
            return;
        }
        if (this.state.game_per == undefined || this.state.game_per == '') {
            notify.show('Please enter contest joined ', "error", 3000)
            return;
        }
        if (this.state.deposit_per == undefined || this.state.deposit_per == '') {
            notify.show('Please enter deposit in %', "error", 3000)
            return;
        }
        if (this.state.deposit_cap == undefined || this.state.deposit_cap == '') {
            notify.show('Please enter deposit capping', "error", 3000)
            return;
        }
        this.setState({ posting: true })
        this._createCampaignApi();
    }

    /****
     * CREATE CAMPAIHN API INTEGRTION 
     */

    _createCampaignApi = () => {
        this.setState({ posting: true })
        let params = {
            "name": this.state.campaignName,
            "source": this.state.source,
            "medium": this.state.medium,
            "url": this.state.url,
            "expiry_date":this.state.FromDate,
            "commission": {
                "signup": this.state.signup,
                "deposit_per": this.state.deposit_per,
                "deposit_cap": this.state.deposit_cap,
                "game_per": this.state.game_per,
                // "game_cap": this.state.game_cap,
            }
        }
        var isURL = NC.baseURL + NC.CREATE_CAMPAIGN;
        if (this.state.isUpdate) {
            params['status'] = this.props.location.item.status == '4' ? 1 : '';
            params['campaign_id'] = this.props.location.item.campaign_id;
            isURL = NC.baseURL + NC.UPDATE_CAMPAIGN;
        } else {
            params['affiliate_id'] = this.props.match.params.affiliate_id;
        }

        WSManager.Rest(isURL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({ posting: false })
                this.props.history.goBack();
            } else {
                notify.show(ResponseJson.message, "error", 3000)
            }
            this.setState({ posting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
            this.setState({ posting: false })

        })
    }



    render() {
        const { campaignName, url, signup, medium, source, deposit_cap, deposit_per, game_cap, game_per } = this.state;
        const FromDateProps = {
            min_date: new Date(current.getTime() + 86400000).toISOString().split('T')[0],
            // max_date: new Date(),
            sel_date: new Date(this.state.FromDate),
            date_key: 'FromDate',
            place_holder: 'From Date',
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'form-control mr-3',
            year_dropdown: true,
            month_dropdown: true,
        }
        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">{this.state.isUpdate ? 'Update Campaign Link' : 'Create Campaign Link'}</h1>
                        </Col>
                    </Row>
                    <div className='promocode-add-view'>
                        <Row>
                            <Col md={12}>
                                <span className='header-t'>Campaign Info</span>
                            </Col>
                        </Row>
                        <Row className="m-t-20">
                            <Col md={12}>
                                <Row>
                                    <Col md={8}>
                                        <label>Website Url</label>
                                        <Input
                                            type="text"
                                            name='url'
                                            placeholder="URL"
                                            value={url}
                                            className='custome-control'
                                            onChange={this.handleInputChange}
                                            disabled={this.state.isUpdate}
                                        />
                                    </Col>
                                    <Col md={4}>
                                        <div className="float-left">
                                            <label className="filter-label">Expiry Date</label>
                                            <SelectDate DateProps={FromDateProps} />
                                        </div>

                                    </Col>
                                </Row>
                            </Col>
                        </Row>

                        <Row className="m-t-20">
                            <Col md={4}>
                                <label>Source</label>
                                <Input
                                    type="text"
                                    name='source'
                                    placeholder="Source"
                                    value={source}
                                    className='custome-control'
                                    onChange={this.handleInputChange}
                                    disabled={this.state.isUpdate}

                                />
                            </Col>
                            <Col md={4}>
                                <label>Medium</label>
                                <Input
                                    type="text"
                                    name='medium'
                                    placeholder="Medium"
                                    value={medium}
                                    className='custome-control'
                                    onChange={this.handleInputChange}
                                    disabled={this.state.isUpdate}

                                />
                            </Col>
                            <Col md={4}>
                                <label>Campaign Name</label>
                                <Input
                                    type="text"
                                    name='campaignName'
                                    placeholder="Campaign Name"
                                    value={campaignName}
                                    className='custome-control'
                                    onChange={this.handleInputChange}
                                    disabled={this.state.isUpdate}

                                />
                            </Col>
                        </Row>
                        <Row className='m-t-20'>
                            <Col md={12}>
                                <span className='header-t'>Commission Info</span>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <span className='-note'>Note: If “Deposit %” is 0 then whatever amount entered in the capping field would be treated as a fixed commission amount</span>
                            </Col>
                        </Row>

                        <Row className="m-t-20">
                            <Col md={4}>
                                <label>Signup (Fixed Amount)</label>
                                <Input
                                    type="number"
                                    name='signup'
                                    placeholder="Enter Amount"
                                    value={signup}
                                    className='custome-control'
                                    onChange={this.handleInputChange}
                                />
                            </Col>
                            <Col md={4}>
                                <label>Contest Joined (Fixed Amount)</label>
                                <Input
                                    type="number"
                                    name='game_per'
                                    placeholder="Enter Amount"
                                    value={game_per}
                                    className='custome-control'
                                    onChange={this.handleInputChange}
                                />
                            </Col>
                        </Row>
                        <Row className="m-t-20">
                            <Col md={4}>
                                <label>Deposit in %</label>
                                <Input
                                    type="number"
                                    name='deposit_per'
                                    placeholder="Enter 0 to make it fixed"
                                    value={deposit_per}
                                    className='custome-control'
                                    onChange={this.handleInputChange}
                                />
                            </Col>
                            <Col md={4}>
                                <label>Capping (Upto)</label>
                                <Input
                                    type="number"
                                    name='deposit_cap'
                                    placeholder="Capping (Upto)"
                                    value={deposit_cap}
                                    className='custome-control'
                                    onChange={this.handleInputChange}
                                />
                            </Col>
                        </Row>

                        {/* <Row className="m-t-20">
                            <Col md={4}>
                                <label>Contest Played in %</label>
                                <Input
                                    type="number"
                                    name='game_per'
                                    placeholder="Contest Played in %"
                                    value={game_per}
                                    className='custome-control'
                                    onChange={this.handleInputChange}
                                />
                            </Col>
                            <Col md={4}>
                                <label>Capping (Upto)</label>
                                <Input
                                    type="number"
                                    name='game_cap'
                                    placeholder="Capping (Upto)"
                                    value={game_cap}
                                    className='custome-control'
                                    onChange={this.handleInputChange}
                                />
                            </Col>
                        </Row> */}

                        <Row className="m-t-20">
                            <Col md={12}>
                                <Button className="btn-secondary mr-3" onClick={() => { this._validateForm() }}>{this.state.isUpdate ? 'Update' : 'Save'}</Button>
                                <Button className="btn-secondary-outline" onClick={() => { this.props.history.goBack() }}>Cancel</Button>
                            </Col>
                        </Row>
                    </div>
                </div>
            </Fragment>
        );
    }
}

export default CreateCampaign;
