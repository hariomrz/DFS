import React, { Component, Fragment } from "react";
import { Row, Col, Input, Tooltip, Button } from 'reactstrap';
import Select from 'react-select';
import Images from '../../components/images';
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
const PrizeTypeOpt = [
    { label: 'Bonus Cash', value: '0' },
    { label: 'Real Cash', value: '1' },
    { label: 'Coins', value: '2' },
    { label: 'Merchandise', value: '3' },
]
class SetPrize extends Component {
    constructor(props) {
        super(props)
        let DefaultPrizeSetType = {
            Daily_SetPrize : false,
            Daily_SponSetPrize: false,
            Daily_SponsorDesc: '',
            Daily_SponsorLink: '',
            Daily_sponsorImageName: '',
            Daily_UnsetPrize: true,
            Daily_newrow: [],
            Daily_PrizeToolTip: false,
            Daily_SponsToolTip: false,
            Daily_disable: false,
            Monthly_SetPrize: false,
            Monthly_SponsorDesc: '',
            Monthly_SponsorLink: '',
            Monthly_PrizeToolTip: false,
            Monthly_SponsToolTip: false,
            Monthly_sponsorImageName: '',
            Monthly_UnsetPrize: true,
            Monthly_newrow: [],
            Monthly_SponSetPrize: false,
            Monthly_disable: false,
            Weekly_SetPrize: false,
            Weekly_SponSetPrize: false,
            Weekly_SponsorDesc : '',
            Weekly_SponsorLink : '',
            Weekly_sponsorImageName: '',
            Weekly_UnsetPrize: true,
            Weekly_newrow: [],
            Weekly_PrizeToolTip: false,
            Weekly_SponsToolTip: false,
            Weekly_disable: false,
        }
        this.state = {
            SetPrizeDaily: false,
            SetPrizeWeek: false,
            SetPrizeMonth: false,
            selectSetPrize: false,
            selectUnsetPrize: true,
            PrizeSetType: DefaultPrizeSetType
        }
    }

    componentDidMount(){
        this.getPredictionPrizes()
    }

    getPredictionPrizes = () => {
        let { PrizeSetType } = this.state
        
        WSManager.Rest(NC.baseURL + NC.GET_PREDICTION_PRIZES, {}).then(Response => {
            if (Response.response_code == NC.successCode) {
                let ResponseData = Response.data
                ResponseData.map((item)=>{

        PrizeSetType[item.name + '_SetPrize'] = item.allow_prize != "0" ? true : false
        PrizeSetType[item.name + '_UnsetPrize'] = item.allow_prize == "0" ? true : false
        PrizeSetType[item.name + '_SponSetPrize'] = item.allow_sponsor == "1" ? true : false
        PrizeSetType[item.name + '_newrow'] = item.prize_distribution_detail

        PrizeSetType[item.name + '_SponsorDesc'] = item.sponsor_name
        PrizeSetType[item.name + '_SponsorLink'] = item.sponsor_link
        PrizeSetType[item.name + '_sponsorImageName'] = item.sponsor_logo
        PrizeSetType[item.name + '_sponsorImage'] = item.sponsor_logo ? NC.S3 + NC.FX_SPONSOR_LOGO + item.sponsor_logo : ''
                        
                })                
                this.setState({ PrizeSetType: PrizeSetType })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }
    

    SetPrizeToggle = (CallType, key) => {
        let { PrizeSetType } = this.state
        PrizeSetType[CallType + key] = !PrizeSetType[CallType + key];  this.setState({ PrizeSetType: PrizeSetType })
    }

    handleInputChange = (e, CallType, key) => {
        let { PrizeSetType } = this.state 
        let name = e.target.name;
        let value = e.target.value;
        PrizeSetType[CallType + key] = value
        this.setState({ PrizeSetType: PrizeSetType })
    }

    addPrizeRow = (CallType) => {
        let { PrizeSetType } = this.state
        let newrow_arr = PrizeSetType[CallType + '_newrow'] != null ? PrizeSetType[CallType + '_newrow'] : []
        let minString = !_.isEmpty(newrow_arr) ? (parseInt(newrow_arr[newrow_arr.length - 1].max) + parseInt(1)).toString() : '1'
        let newrow = {
            'min': minString ? minString : '1',
            'max': '',
            'prize_type': '',
            'amount': '',
        }

        newrow_arr.push(newrow)
        PrizeSetType[CallType + '_newrow'] = (newrow_arr)
        this.setState({
            PrizeSetType: PrizeSetType
        });
    }
    
    handlePrizeDis = (e, CallType, indx, key) => {
        let { PrizeSetType } = this.state 
        let name = e.target.name;
        let value = e.target.value;

        if (isNaN(value) && name === 'max') {
            notify.show("Please enter number only", "error", 3000);
            return false
        }
        var totalRow = PrizeSetType[CallType + '_newrow'].length;
        
        PrizeSetType[CallType + '_newrow'][indx][key] = value.toString()

        
        if (totalRow > (indx + 1) && name === 'max')
        {
            PrizeSetType[CallType + '_newrow'][indx + 1]['min'] = (parseInt(value) + parseInt(1)).toString()
            for (var i = indx + 1; i < totalRow; i++) {
                if (i != indx + 1)
                {PrizeSetType[CallType + '_newrow'][i]['min'] = ''}
                PrizeSetType[CallType + '_newrow'][i]['max'] = ''                
            }
        }
        this.setState({ PrizeSetType: PrizeSetType })
    }
    
    handlePrizeType = (e, CallType, indx) => {
        let { PrizeSetType } = this.state 
        if(e)
        {
            PrizeSetType[CallType + '_newrow'][indx].prize_type = e.value
            this.setState({ PrizeSetType: PrizeSetType })
        }
    }

    handleSetPrize = (e, CallType, CallFrom) => {
        let { PrizeSetType } = this.state        
if (e) {
        let name = e.target.name;

    if (CallFrom == 'Sponsor')    {
        PrizeSetType[CallType + '_SponSetPrize'] = (PrizeSetType[CallType + '_SponSetPrize']) ? false : true        
    }else{
        PrizeSetType[CallType + '_SetPrize'] = (name == 'selectSetPrize') ? true : false
        PrizeSetType[CallType + '_UnsetPrize'] = (name == 'selectUnsetPrize') ? true : false
    }
       
        this.setState({
            PrizeSetType: PrizeSetType
        })        
}
    }

    renderDistributionTable = (CallType) => {
        let { SponsorDesc, PrizeSetType, selectSetPrize, selectUnsetPrize, sponsorImage, sponsoreTT, SetPrizeDaily } = this.state
        return(
            <Fragment>                 
                <div className="prize-duration">{CallType}</div>
                <div className="set-prizes-title">Set Prizes
                    <i className="icon-info-border ml-2" id={CallType + '_PrizeTT'}>
                        <Tooltip placement="right" isOpen={PrizeSetType[CallType + '_PrizeToolTip']} target={CallType + '_PrizeTT'} toggle={() => this.SetPrizeToggle(CallType, '_PrizeToolTip')}>Set prizes fo {CallType.toLowerCase()} distribution</Tooltip>
                    </i>
                </div>
                <div className="select-prize-op">
                <div className="common-cus-checkbox">
                    <label class="com-chekbox-container">
                        <span className="opt-text">Yes</span>
                         <input
                            type="checkbox"
                            name="selectSetPrize"

                            defaultChecked={PrizeSetType[CallType + '_SetPrize']}
                            checked={PrizeSetType[CallType + '_SetPrize']}

                            onChange={(e) => this.handleSetPrize(e, CallType, 'SetPrize')}
                        />
                        <span class="com-chekbox-checkmark"></span>
                    </label>
                    <label class="com-chekbox-container">
                        <span className="opt-text">No</span>
                        <input
                            name="selectUnsetPrize"
                            type="checkbox"

                            defaultChecked={PrizeSetType[CallType + '_UnsetPrize']}
                            checked={PrizeSetType[CallType + '_UnsetPrize']}

                            onChange={(e) => this.handleSetPrize(e, CallType, 'SetPrize')}
                        />
                        <span class="com-chekbox-checkmark"></span>
                    </label>
                </div>               
                </div>               
                {PrizeSetType[CallType + '_SetPrize'] && (
                    <div className={`prize-dist-table ${CallType == "Monthly" ? 'ml-0 mr-2' : CallType == "Weekly" ? 'ml-0' : ''}`}>
                <div className="prize-head clearfix">
                    <div className="head-title">Rank</div>
                    <div className="head-title">Prize Type</div>
                    <div className="head-title">Distribution</div>
                </div>
                
                {
                    _.map(PrizeSetType[CallType + '_newrow'],(newrow, idx)=>{
                        return(
                            <div key={idx} className="prize-add-body clearfix">
                                <div className="rank-add clearfix float-left">
                                    <div className="rank-input">
                                        <Input type="number" value='01' disabled value={newrow.min} />
                                    </div>
                                    <div className="rank-seprator">-</div>
                                    <div className="rank-input">
                                        <Input 
                                        type="text"
                                        name='max'
                                        maxLength={5}
                                        value={newrow.max} 
                                        onChange={(e) => this.handlePrizeDis(e, CallType, idx, 'max')}
                                        />
                                    </div>
                                </div>
                                <div className="prize-type clearfix float-left">
                                    <Select
                                        searchable={false}
                                        clearable={false}
                                        placeholder="Type"
                                        name={'prize_type'}
                                        value={newrow.prize_type}
                                        options={PrizeTypeOpt}
                                        onChange={(e) => this.handlePrizeType(e, CallType, idx)}
                                    />
                                </div>
                                <div className="fx-distribution clearfix float-left">
                                    <Input 
                                    type="text" 
                                    value='Iphone' 
                                        maxLength={newrow.prize_type == '3' ? '' : 5}
                                        name='value'
                                        value={newrow.amount}
                                        onChange={(e) => this.handlePrizeDis(e, CallType, idx,'amount')}
                                    />
                                    <div className="cancel-container">
                                        <span className="dis-each">(Each)</span>
                                        {idx > 0 && <i className="icon-cross icon-style" onClick={() => this.removeRow(CallType, idx)}></i>}

                                    </div>
                                </div>
                            </div>
                        )
                    })
                }               
                

                <div className="add-prize-btn" onClick={()=>this.addPrizeRow(CallType)}>
                    Add Prize 
                    <i className="icon-plus icon-style ml-2"></i>
                </div>
            </div>)}
            <Row>
                <Col md={12}>
                    <div className="op-create-category proof-box">
                            <div className="set-prizes-title">
                                <div className="sponsor-action">
                                    Sponsored
                                    <i className="icon-info-border ml-2" id={CallType + '_sponTT'}>
                                        <Tooltip placement="right" isOpen={PrizeSetType[CallType + '_SponsToolTip']} target={CallType + '_sponTT'} toggle={() => this.SetPrizeToggle(CallType, '_SponsToolTip')}> Add sponsor details for {CallType.toLowerCase()} prize distribution</Tooltip>
                                    </i>
                                </div>                                
                                <div className="select-prize-op">
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="opt-text">Yes</span>
                                        <input
                                            name="selectSetPrize"
                                            type="checkbox"

                                            checked={PrizeSetType[CallType + '_SponSetPrize']}

                                            onChange={(e) => this.handleSetPrize(e, CallType, 'Sponsor')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                </div>
                    </div>
                    {PrizeSetType[CallType + '_SponSetPrize'] && <Fragment>
                        <div className="mb-20">
                                <label htmlFor="SponsorDesc">Sponsored by</label>
                            <Input
                                type="text"
                                minLength={3}
                                maxLength={30}
                                className="question-input"
                                id={CallType + '_SponsorDesc'}
                                name={CallType + '_SponsorDesc'}
                                placeholder="Name"
                                value={PrizeSetType[CallType + '_SponsorDesc']}
                                onChange={(e) => this.handleInputChange(e, CallType, '_SponsorDesc')}
                            />
                        </div>
                        <div className="mb-20">
                                <label htmlFor="SponsorDesc">Sponsored Link</label>
                            <Input
                                type="text"
                                className="question-input"
                                name={CallType + '_SponsorLink'}
                                id={CallType + '_SponsorLink'}
                                placeholder="URL"
                                value={PrizeSetType[CallType + '_SponsorLink']}
                                onChange={(e) => this.handleInputChange(e, CallType,'_SponsorLink')}
                            />
                        </div>
                        <div className="redeem-box position-relative clearfix">
                            <div className="select-image-box float-left">
                                <div className="dashed-box">
                                            {!_.isEmpty(PrizeSetType[CallType + '_sponsorImage']) ?
                                        <Fragment>
                                            <i onClick={() => this.resetFile(CallType)} className="icon-close"></i>
                                            <img className="img-cover" src={PrizeSetType[CallType + '_sponsorImage']} />
                                        </Fragment>
                                        :
                                        <Fragment>
                                            <Input
                                                accept="image/x-png,
                                                image/jpeg,image/jpg"
                                                type="file"
                                                name={CallType + '_sponsorImage'}
                                                id="sponsorImage"
                                                onChange={(e)=>this.onChangeImage(e, CallType)}
                                            />
                                            <img className="def-addphoto" src={Images.DEF_ADDPHOTO} alt="" />
                                        </Fragment>
                                    }
                                </div>
                            </div>
                        </div>
                    </Fragment>}
                    </div>
                </Col>
            </Row>
            <Row className="updatebtn-box">
                <Col md={12}>
                    <Button 
                    className="btn-secondary-outline"
                    disabled={PrizeSetType[CallType + '_disable']}
                    onClick={() => this.savePrizes(CallType)}
                        >
                            {
                                PrizeSetType[CallType + '_disable']
                                ?
                                    <Loader hide /> 
                                :
                                    'Update'
                            }                    
                    </Button>
                </Col>
            </Row>
            </Fragment>
        )
    }

    onChangeImage = (event, CallType) => {
        let { PrizeSetType } = this.state
        PrizeSetType[CallType + '_disable'] = true
        PrizeSetType[CallType + '_sponsorImage'] = URL.createObjectURL(event.target.files[0])
        this.setState({
            PrizeSetType: PrizeSetType,
            PrfImgPosting: true
        });       

        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD_SPONSOR_IMAGE, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
        PrizeSetType[CallType + '_sponsorImageName'] = Response.data.file_name
                    PrizeSetType[CallType + '_disable'] = false
                    this.setState({
                        PrizeSetType: PrizeSetType,
                        PrfImgPosting: false,
                    });
                } else {
                    PrizeSetType[CallType + '_disable'] = false
                    this.setState({
                        PrizeSetType: PrizeSetType,
                        sponsorImage: null,
                        PrfImgPosting: false,
                    });
                }
            }).catch(error => {
                PrizeSetType[CallType + '_disable'] = false
                this.setState({ PrizeSetType: PrizeSetType })
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile = (CallType) => {
        let { PrizeSetType } = this.state
        PrizeSetType[CallType + '_sponsorImage'] = ''
        PrizeSetType[CallType + '_sponsorImageName'] = ''
        this.setState({
            PrizeSetType: PrizeSetType
        });
    }
    
    removeRow = (CallType, idx) => {
        let { PrizeSetType } = this.state
        PrizeSetType[CallType + '_newrow'].splice(idx,1)
        this.setState({
            PrizeSetType: PrizeSetType
        });
    }

    savePrizes = (CallType) => {
        let { PrizeSetType } = this.state
        PrizeSetType[CallType + '_disable'] = true
        this.setState({ PrizeSetType: PrizeSetType })
        
        let pType = PrizeSetType[CallType + '_SetPrize']
        let sponType = PrizeSetType[CallType + '_SponSetPrize']
        let apiCall = true
        let CallTypeLower = CallType.toLowerCase()
        
        if (pType)
        {
            if (_.isEmpty(PrizeSetType[CallType + '_newrow']))
            {
                notify.show("Please enter prizes for " + CallTypeLower, 'error', 5000)
                apiCall = false
            }   

            _.map(PrizeSetType[CallType + '_newrow'], (rowcheck, idx)=>{
                let rowNum = parseInt(idx) + parseInt(1)
                if (rowcheck.min === "")
                {
                    notify.show("Please enter the minimum rank for " + CallTypeLower + " row " + rowNum, 'error', 5000)
                    apiCall = false
                } 
                if (parseInt(rowcheck.min) > parseInt(rowcheck.max))
                {                    
                    notify.show("Maximum rank should be greater or equal for " + CallTypeLower + " row " + rowNum, 'error', 5000)
                    apiCall = false
                } 
                else if (rowcheck.max === "")
                {
                    notify.show("Please enter the maximum rank for " + CallTypeLower + " row " + rowNum, 'error', 5000)
                    apiCall = false
                } 
                else if (rowcheck.prize_type === "")
                {
                    notify.show("Please enter the prize type for " + CallTypeLower + " row " + rowNum, 'error', 5000)
                    apiCall = false
                } 
                else if (rowcheck.prize_type != '3' && isNaN(rowcheck.amount))
                {
                    notify.show("Please enter the amount for " + CallTypeLower + " row " + rowNum, 'error', 5000)
                    apiCall = false
                } 
                else if (rowcheck.amount === ""){
                    notify.show("Please enter the distribution for " + CallTypeLower + " row " + rowNum, 'error', 5000)
                    apiCall = false
                }  
            })
        }
        
        if (sponType){
            if (PrizeSetType[CallType +'_SponsorDesc'] === "")
            {
                notify.show("Please enter sponsored name for " + CallTypeLower , 'error', 5000)
                apiCall = false
            } 
            else if (PrizeSetType[CallType + '_sponsorImageName'] === "") {
                notify.show("Please upload sponsored logo for " + CallTypeLower, 'error', 5000)
                apiCall = false
            }
        }

        if (!apiCall)
        {
            PrizeSetType[CallType + '_disable'] = false
            this.setState({ PrizeSetType: PrizeSetType})
            return false
        }
        
        let params = {
            "prize_category": (CallType == 'Daily') ? "1" : (CallType == 'Weekly') ? "2" : "3",
            "allow_prize": pType ? "1" : "0",
            "allow_sponsor": sponType ? "1" : "0",
            "sponsor_logo": PrizeSetType[CallType + '_sponsorImageName'],
            "sponsor_link": PrizeSetType[CallType + '_SponsorLink'],
            "sponsor_name": PrizeSetType[CallType + '_SponsorDesc'],
            "prize_distribution_details": PrizeSetType[CallType + '_newrow']

        }

        WSManager.Rest(NC.baseURL + NC.FIXED_UPDATE_PRIZES, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000) 
                PrizeSetType[CallType + '_disable'] = false
                this.setState({ PrizeSetType: PrizeSetType })  
            }else{
                PrizeSetType[CallType + '_disable'] = false
                this.setState({ PrizeSetType: PrizeSetType })
            }
        }).catch(error => {
            PrizeSetType[CallType + '_disable'] = false
            this.setState({ PrizeSetType: PrizeSetType })
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    render() {
        return (
            <div className="fixed-set-prize">
                <Row className="mt-4">
                    <Col md={12}>
                        <div className="pre-heading float-left">Set Prizes</div>
                        <div onClick={() => this.props.history.push('/prize-open-predictor/dashboard')} className="go-back float-right">{'<'} Back to Dashboard</div>
                    </Col>
                </Row>
                <Row className="mt-1">
                    <Col md={12}>
                        <div className="fx-top-header">Set Prizes</div>
                    </Col>
                </Row>
                <div className="bg-white">
                    <Row>
                        <Col md={4} className="fx-border-right">
                            {this.renderDistributionTable('Daily')}
                        </Col>
                        <Col md={4} className="fx-border-right">
                            {this.renderDistributionTable('Weekly')}
                        </Col>
                        <Col md={4}>
                            {this.renderDistributionTable('Monthly')}
                        </Col>
                    </Row>
                </div>
            </div>
        )
    }
}
export default SetPrize