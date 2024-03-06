import React, { Component, Fragment } from "react";
import { Row, Col, Input, Tooltip, Button } from 'reactstrap';
import Select from 'react-select';
import Images from '../../components/images';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import HF, { _Map, _isEmpty, _isNull, _isUndefined, _remove, _find, _cloneDeep } from "../../helper/HelperFunction";
import { MODULE_NOT_ENABLE } from "../../helper/Message";
import SelectDropdown from "../../components/SelectDropdown";
import { LB_geTMasterData, LB_getLiveUpcomingLeagues } from '../../helper/WSCalling';
import LS from 'local-storage';
import _ from "lodash";

const PrizeTypeOpt = [
    { label: 'Bonus Cash', value: '0' },
    { label: 'Real Cash', value: '1' },
    { label: 'Coins', value: '2' },
    { label: 'Merchandise', value: '3' },
]
const PrizeTypeOpt1 = [
    { label: 'Coins', value: '2' },
]
class SetprizeLeaderboard extends Component {
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
            PrizeSetType: DefaultPrizeSetType,
            SelectedCate: '',
            CateOption: [],
            DurationOption: [],
            DurationData: [],
            LeagueOption: [],
            SelectedLeague: '',
            prize_id: (this.props.match.params.prize_id) ? this.props.match.params.prize_id : 0,
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            SelectedLeagueName:'',
            prizeDetails:{}


        }
    }

    componentDidMount(){
        // if (HF.allowRefLeaderboard() != '1') {
        //     notify.show(MODULE_NOT_ENABLE, 'error', 5000)
        //     this.props.history.push('/dashboard')
        // }
       
        this.setCateOption()
        this.geLbMasterData()
        if (this.state.prize_id == 0) {
            this.addPrizeRow("Daily")
        }
        else {
            this.getPrizeDetails()

        }


        this.geLeagues()
    }
    
    setCateOption = () => {
        let lbCategory = HF.getLeaderboardData() ? HF.getLeaderboardData() : [];
        let spCate = []
        _Map(lbCategory, function (CFormat) {
            if(HF.allowDFS() == "0" && CFormat.category_id == 2) {
            } else {
                spCate.push({
                    value: CFormat.category_id,
                    label: CFormat.name
                });
            }
        })
        this.setState({ CateOption: spCate })
    }

    setDuraOption = () => {var dArr = [];
        let cat_id = this.state.SelectedCate
        _Map(this.state.DurationData, function (itm, idx) {
            if (itm.category_id == cat_id){
                for (var number in itm.type) {                    
                    dArr.push({
                        value: number,
                        label: itm.type[number]
                    });
                }
            }
        })
        this.setState({ DurationOption: dArr })
    }

    geLbMasterData = () => {
        LB_geTMasterData({}).then((ApiResponse) => {
              if (ApiResponse.response_code === NC.successCode) {
                let data = ApiResponse.data.leaderboard ? ApiResponse.data.leaderboard : []
                this.setState({ DurationData: data })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    getPrizeDetails = () => {
        let param = {
            "prize_id": this.state.prize_id,
         
        }
        let { PrizeSetType } = this.state        
        WSManager.Rest(NC.baseURL + NC.LB_GET_PRIZE_DETAIL, param).then(Response => {
            if (Response.response_code == NC.successCode) {
                let ResponseData = Response.data
                PrizeSetType[ResponseData.name + '_SetPrize'] = ResponseData.allow_prize != "0" ? true : false
                PrizeSetType[ResponseData.name + '_UnsetPrize'] = ResponseData.allow_prize == "0" ? true : false
                PrizeSetType[ResponseData.name + '_SponSetPrize'] = ResponseData.allow_sponsor == "1" ? true : false
                PrizeSetType[ResponseData.name + '_newrow'] = ResponseData.prize_detail               
                this.setState({ PrizeSetType: PrizeSetType,prizeDetails:ResponseData,
                    SelectedCate:ResponseData.category_id,
                    SelectedDuration:ResponseData.type,
                    SelectedLeague:ResponseData.reference_id,
                },()=>{
                    if(ResponseData.type == '4'){
                        this.setState({SelectedLeagueName:ResponseData.name})
                    }
                    setTimeout(() => {
                        this.setDuraOption()
                    }, 500);

                })
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
        let minString = !_isEmpty(newrow_arr) ? (parseInt(newrow_arr[newrow_arr.length - 1].max) + parseInt(1)).toString() : '1'
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
        let prizType = PrizeSetType[CallType + '_newrow'][indx].prize_type;
        const onlyNumber = /^[0-9\b]+$/;
        
        if (value == '' && name === 'max') {
            PrizeSetType[CallType + '_newrow'][indx][key] = ''
            this.setState({ PrizeSetType: PrizeSetType })
            return false ;
        }
        else if (!onlyNumber.test(value) && name === 'max') {
            notify.show("Please enter integer number only", "error", 3000);
            return false
        }
        else if( prizType != 3 && isNaN(value)){
            notify.show("Please enter number only", "error", 3000);
            return false 
        }
        else if( prizType != 3 && value == '0'){
            notify.show("Please enter value greater then zero", "error", 3000);
            return false 
        }
        else if( prizType == 2 && value == ''){
            PrizeSetType[CallType + '_newrow'][indx][key] = ''
            this.setState({ PrizeSetType: PrizeSetType })
            return false 
        }
        else if( prizType == 2 && !onlyNumber.test(value)){
            notify.show("Please enter integer vlaue for coin", "error", 3000);
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
            PrizeSetType[CallType + '_newrow'][indx]['amount'] = ''

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
        let { PrizeSetType } = this.state
        return(
            <Fragment>                 
                <div className="prize-duration">Set Prizes
                    <i className="icon-info-border ml-2 font-16" id={CallType + '_PrizeTT'}>
                        <Tooltip placement="right" isOpen={PrizeSetType[CallType + '_PrizeToolTip']} target={CallType + '_PrizeTT'} toggle={() => this.SetPrizeToggle(CallType, '_PrizeToolTip')}>Set prizes fo {CallType.toLowerCase()} distribution</Tooltip>
                    </i>
                </div>
                <div className="select-prize-op">
                {/* <div className="common-cus-checkbox">
                    <label className="com-chekbox-container">
                        <span className="opt-text">Yes</span>
                         <input
                            type="checkbox"
                            name="selectSetPrize"

                            defaultChecked={PrizeSetType[CallType + '_SetPrize']}
                            checked={PrizeSetType[CallType + '_SetPrize']}

                            onChange={(e) => this.handleSetPrize(e, CallType, 'SetPrize')}
                        />
                        <span className="com-chekbox-checkmark"></span>
                    </label>
                    <label className="com-chekbox-container">
                        <span className="opt-text">No</span>
                        <input
                            name="selectUnsetPrize"
                            type="checkbox"
                            defaultChecked={PrizeSetType[CallType + '_UnsetPrize']}
                            checked={PrizeSetType[CallType + '_UnsetPrize']}
                            onChange={(e) => this.handleSetPrize(e, CallType, 'SetPrize')}
                        />
                        <span className="com-chekbox-checkmark"></span>
                    </label>
                </div>                */}
                </div>               
                {(
                    <div className={`setp-prize-dist-table ${CallType == "Monthly" ? 'ml-0 mr-2' : CallType == "Weekly" ? 'ml-0' : ''}`}>
                <div className="prize-head clearfix">
                    <div className="head-title text-left ml-4">Rank</div>
                    <div className="head-title">Prize Type</div>
                    <div className="head-title">Distribution</div>
                </div>
                
                {
                    _Map(PrizeSetType[CallType + '_newrow'],(newrow, idx)=>{
                        return(
                            <div key={idx} className={`prize-add-body clearfix ${idx === 0 ? 'border-0' : ''}`}>
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
                                        options={HF.allowCoin() != '1' ? PrizeTypeOpt : [...PrizeTypeOpt]}
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
                                        <span className="dis-each">Each</span>
                                        {idx >= 0 && <i className="icon-cross icon-style" onClick={() => this.removeRow(CallType, idx)}></i>}
                                    </div>
                                </div>
                            </div>
                        )
                    })
                }

                <div className="text-center">
                    <div className="add-prize-btn" onClick={()=>this.addPrizeRow(CallType)}>
                        Add Prize 
                        <i className="icon-plus icon-style ml-2"></i>
                    </div>
                </div>
            </div>)}
            {/* <Row>
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
                                    <label className="com-chekbox-container">
                                        <span className="opt-text">Yes</span>
                                        <input
                                            name="selectSetPrize"
                                            type="checkbox"

                                            checked={PrizeSetType[CallType + '_SponSetPrize']}

                                            onChange={(e) => this.handleSetPrize(e, CallType, 'Sponsor')}
                                        />
                                        <span className="com-chekbox-checkmark"></span>
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
                        <div className="">
                            <div className="select-image-box float-left">
                                <div className="dashed-box">
                                            {!_isEmpty(PrizeSetType[CallType + '_sponsorImage']) ?
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
            </Row> */}
            <Row className="setp-updatebtn-box mt-5">
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
            if (_isEmpty(PrizeSetType[CallType + '_newrow']))
            {
                notify.show("Please enter prizes for " + CallTypeLower, 'error', 5000)
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
            "category_id": this.state.SelectedCate,
            "type": this.state.SelectedDuration,
            "allow_prize": "1",
            "prize_detail": PrizeSetType[CallType + '_newrow']

        }
        if (this.state.SelectedDuration == '4') {
            params['reference_id'] = this.state.SelectedLeague
            params['league_name'] = this.state.SelectedLeagueName
        }
        if (this.state.prize_id != 0) {
            params['prize_id'] = this.state.prize_id
        }
        for( var i =0;i < params.prize_detail.length;i++){
           let  rowcheck = params.prize_detail[i];
           if (rowcheck.min == "")
           {
               notify.show("Please enter the minimum rank ", 'error', 5000)
               apiCall = false
               PrizeSetType[CallType + '_disable'] = false
               break;

           } 
           if (parseInt(rowcheck.min) > parseInt(rowcheck.max))
           {                    
               notify.show("Maximum rank should be greater or equal to min", 'error', 5000)
               apiCall = false
               PrizeSetType[CallType + '_disable'] = false
               break;
           } 
           else if (rowcheck.max == "")
           {
               notify.show("Please enter the maximum rank ", 'error', 5000)
               apiCall = false
               PrizeSetType[CallType + '_disable'] = false
               break;
            } 
           else if (rowcheck.prize_type == "")
           {
               notify.show("Please enter the prize type ", 'error', 5000)
               apiCall = false
               PrizeSetType[CallType + '_disable'] = false
               break;
            } 
           else if (rowcheck.prize_type != '3' && isNaN(rowcheck.amount))
           {
               notify.show("Please enter the amount ", 'error', 5000)
               apiCall = false
               PrizeSetType[CallType + '_disable'] = false
               break;

           } 
           else if (rowcheck.amount == ""){
               notify.show("Please enter the distribution ", 'error', 5000)
               apiCall = false
               PrizeSetType[CallType + '_disable'] = false
               break;

           }
           else{
               apiCall = true
           }  

        }
        // _Map(params.prize_detail, (rowcheck, idx)=>{
        //     let rowNum = parseInt(idx) + parseInt(1)
        //     if (rowcheck.min === "")
        //     {
        //         notify.show("Please enter the minimum rank ", 'error', 5000)
        //         apiCall = false
        //         PrizeSetType[CallType + '_disable'] = false
        //         return;

        //     } 
        //     if (parseInt(rowcheck.min) > parseInt(rowcheck.max))
        //     {                    
        //         notify.show("Maximum rank should be greater or equal to min", 'error', 5000)
        //         apiCall = false
        //         PrizeSetType[CallType + '_disable'] = false
        //         return;
        //     } 
        //     else if (rowcheck.max === "")
        //     {
        //         notify.show("Please enter the maximum rank ", 'error', 5000)
        //         apiCall = false
        //         PrizeSetType[CallType + '_disable'] = false
        //         return;
        //      } 
        //     else if (rowcheck.prize_type === "")
        //     {
        //         notify.show("Please enter the prize type ", 'error', 5000)
        //         apiCall = false
        //         PrizeSetType[CallType + '_disable'] = false
        //         return;
        //      } 
        //     else if (rowcheck.prize_type != '3' && isNaN(rowcheck.amount))
        //     {
        //         notify.show("Please enter the amount ", 'error', 5000)
        //         apiCall = false
        //         PrizeSetType[CallType + '_disable'] = false
        //         return;

        //     } 
        //     else if (rowcheck.amount == ""){
        //         notify.show("Please enter the distribution ", 'error', 5000)
        //         apiCall = false
        //         PrizeSetType[CallType + '_disable'] = false
        //         return;

        //     }
        //     else{
        //         apiCall = true
        //     }  
        // })

        if(apiCall){
            WSManager.Rest(NC.baseURL + NC.LB_SAVE_PRIZES_POST, params).then(Response => {
                if (Response.response_code == NC.successCode) {
                    notify.show(Response.message, 'success', 5000)
                    PrizeSetType[CallType + '_disable'] = false
                    this.setState({ PrizeSetType: PrizeSetType })
                    this.props.history.goBack()
                } else {
                    PrizeSetType[CallType + '_disable'] = false
                    this.setState({ PrizeSetType: PrizeSetType })
                }
            }).catch(error => {
                PrizeSetType[CallType + '_disable'] = false
                this.setState({ PrizeSetType: PrizeSetType })
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            })
        }

        
    }

    handleCatChange = (value, name) => {
        let { SelectedCate, SelectedDuration, SelectedLeague } = this.state

        if (name === 'SelectedCate'){
            this.setState({ SelectedDuration: '', SelectedLeague: '' })
        } 
    
        this.setState({ [name]: value.value }, () => {
           if (name === 'SelectedCate') {
                this.setDuraOption()
            }
            if (name === 'SelectedLeague') {
                this.setState({ SelectedLeagueName: value.label })

            }
            if (name === 'SelectedDuration') {
                //this.geLeagues()
            }
        })
    }   

    geLeagues = () => {

        LB_getLiveUpcomingLeagues().then((ApiResponse) => {
              if (ApiResponse.response_code === NC.successCode) {

                let data = ApiResponse.data.result ? ApiResponse.data.result : []
                let lgArr = []

                _Map(data, function (CFormat) {
                    lgArr.push({
                        value: CFormat.league_id,
                        label: CFormat.league_name
                    });
                })

                this.setState({ LeagueOption: lgArr },()=>{

                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }
    
    render() {
        let { CateOption, SelectedCate, DurationOption, SelectedDuration, LeagueOption, SelectedLeague ,prizeDetails,prize_id} = this.state
        const Comm_Props = {
            is_disabled: prize_id == '0' ? false :true,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            place_holder: "Select",
        }
        const Select_Cat_Props = {
            ...Comm_Props,
            sel_options: CateOption,
            selected_value: SelectedCate,
            select_name: 'SelectedCate',
            modalCallback: (e, name)=>this.handleCatChange(e, name)
        }

        const Select_Dura_Props = {
            ...Comm_Props,
            sel_options: DurationOption,
            selected_value: SelectedDuration,
            select_name: 'SelectedDuration',
            modalCallback: (e, name) => this.handleCatChange(e, name)
        }

        const Select_League_Props = {
            ...Comm_Props,
            sel_options: LeagueOption,
            selected_value: SelectedLeague,
            select_name: 'SelectedLeague',
            modalCallback: (e, name) => this.handleCatChange(e, name)
        }

        return (
            <div className="xfixed-set-prize set-p-lrdbrd">
                <div className="bg-white mt-4">
                    <div className="sp-filters">
                        <Row style={{cursor: this.state.prize_id != 0 ? 'none' :''}}>
                            <Col md={4}>
                                <label htmlFor="">Leaderboard</label>
                                <SelectDropdown SelectProps={Select_Cat_Props} />
                            </Col>
                            <Col md={4}>
                                <label htmlFor="">Duration</label>
                                <SelectDropdown SelectProps={Select_Dura_Props} />
                            </Col>
                            {
                                SelectedDuration == '4' &&
                                <Col md={4}>
                                    <label htmlFor="">Select League</label>
                                    <SelectDropdown SelectProps={Select_League_Props} />
                                </Col>
                            }
                        </Row>
                    </div>
                    {
                        <Row>
                            <Col md={9} className="fx-border-right animate-left">
                                {this.renderDistributionTable(prizeDetails.name ? prizeDetails.name: "Monthly" )}
                            </Col>
                        </Row>
                    }
                </div>
            </div>
        )
    }
}
export default SetprizeLeaderboard
