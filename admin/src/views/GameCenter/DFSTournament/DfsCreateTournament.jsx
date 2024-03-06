import React, { Component, Fragment } from "react";
import { Button, Row, Col, Tooltip, FormGroup, Input, InputGroup } from 'reactstrap';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import Select from 'react-select';
import LS from 'local-storage';
import _ from 'lodash';
import Images from '../../../components/images';
import moment from 'moment';
import { notify } from 'react-notify-toast';
import SelectDate from "../../../components/SelectDate";
import { PT_SPONSOR_MSG, PT_MSG_UPLOADLOGO, PT_START_G_END, PT_START_G_CUR, DFST_BONUS_ALLOW } from "../../../helper/Message";
import HF, { _times, _isEmpty, _isUndefined, _Map, _remove, _isNull } from "../../../helper/HelperFunction";
import { DFST_CreateTournment, DFSTR_PUBLISHED_FIXTURES, DFST_getTournamentMasterData, DFST_getTournamentEditData, DFST_updateTournament, DFST_removeTournamentLogo, DFST_removeTournamentBanner } from "../../../helper/WSCalling";
import { MomentDateComponent } from "../../../components/CustomComponent";
import { Base64 } from 'js-base64';
import queryString from 'query-string';
import SelectDropdown from "../../../components/SelectDropdown";
class DfsCreateTournament extends Component {

    constructor(props) {
        super(props);
        let DefaultPrizeSetType = {
            Daily_SetPrize: true,
            Daily_newrow: [{ min: "1", max: "1", prize_type: "1", amount: "" }],
        }
        this.state = {
            selected_league: "",
            leagueList: [],
            leagueListM: [],
            fixtureList: [],
            fixtureFilter: [{ label: "All", id: 1 }, { label: "Selected", value: 2 }, { label: "Unselected", value: 3 }],
            fixtureFilterSelected: { label: "All", value: 1 },
            fixtureMainList: [],
            tournamentName: '',
            activeTab: 1,
            posting: false,
            keyword: '',
            selectAll: false,
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            uplogottShow: false,
            sponsorttShow: false,
            SetSponsor: '0',
            payout_data: [],
            prize_profit: { min_total: 0, max_total: 0, min_gross_profit: 0, max_gross_profit: 0, min_net_profit: 0, max_net_profit: 0 },
            PrizeSetType: DefaultPrizeSetType,
            MerchandiseList: [],
            PrizeTypeOpt: [],
            TotalRealDistri: 0,
            TotalFixture: 0,
            Dfst_Id: 0,
            editMode: false,
            subPosting: false,
            BannerArr: [],
            CurrencyType: '1',
            currencyOptions: [],
            UpLogoPosting: false,
            ContestType: false,
            NumberFixture : false,
            NumberOfFixtures: '0',
            FixtureType :'0',
            hideCol:true,
            TeamFixture : '1',
            Teamcheked : '',
            chekedOne : true,
            chekedTwo: false,
            FixtureTypedis :'0',
            TodayDate: new Date(),
            EndDate : null
        };
    }

    componentDidMount() {
        if (HF.allowDFSTournament() != '1') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getTournamentMasterData();
        let values = queryString.parse(this.props.location.search)
        if (!_isUndefined(values.pid)) {
            this.setState({
                Dfst_Id: !_isUndefined(values.pid) ? Base64.decode(values.pid) : '0'
            }, () => {
                this.getEditData();
                this.GetAllLeagueList();
            })
        } else {
            this.GetAllLeagueList();
        }
    }

    
    ContestTypeToggle = () => {
        this.setState({
            ContestType: !this.state.ContestType
        });
      }

      NumberFixtureToggle = () => {
        this.setState({
            NumberFixture: !this.state.NumberFixture
        });
      }

    //   handlePrizeInPercentage = (e, tindex) => {
    //     if (e) {
    //       let value = e.target.value;
    //       let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    //       contestTemplate[tindex] = value;
    
    //       //Start Logic for on change contest type
    //       if (tindex === 'prize_pool_type' && value === '1' && contestTemplate.minimum_size && contestTemplate.size && contestTemplate.entry_fee) {
    //         let prize_pool = (contestTemplate.minimum_size * contestTemplate.entry_fee);
    //         prize_pool = prize_pool.toFixed(0);
    //         contestTemplate['prize_pool'] = prize_pool;
    //       }
    //       if (tindex === 'prize_pool_type' && value === '2') {
    //         contestTemplate['prize_value_type'] = '0';
    //       }
    //       //End Logic for on change contest type
    
    //       this.setState({
    //         contestTemplate: contestTemplate
    //       }, function () {
    //         this.initPayoutData();
    //       })
    //     }
    //   }
    

    editData = (data) => {
        let temDisData = this.state.PrizeSetType
        temDisData['Daily_SetPrize'] = parseInt(data.is_prize) ? true : false


        temDisData['Daily_newrow'] = data.prize_detail ? data.prize_detail : ''
        this.getTotRealDistribution(this.state.PrizeSetType)
        this.setState({
            editMode: true,
            selected_league: data.league_id ? data.league_id : '',
            tournamentName: data.name ? data.name : '',

            PrizeSetType: temDisData,
            fixtureList: data.fixtures ? data.fixtures : [],
            fixtureMainList: data.fixtures ? data.fixtures : [],
            TotalFixture: data.fixtures ? data.fixtures.length : '',
            BannerArr: data.sponsor_banners ? data.sponsor_banners : [],
            SetSponsor: (data.sponsor_banners && data.sponsor_banners.length > 0) ? '1' : '0',
            UploadLogoName: (data.image) ? data.image : '',
            CurrencyType: (data.currency_type) ? data.currency_type : '1',
        })
    }

    RadioonChange=(e,name)=>{   
        
        console.log(e.target.value);

        if(name == 'team_from_fixture'){
            this.setState({
                TeamFixture :e.target.value,
                // FixtureType :e.target.value
            })
        }
        if(name == 'top_team'){
            this.setState({
                FixtureType :e.target.value
            })
       }
        if(name == 'top_team' &&  e.target.value == '1'){
            this.setState({
                FixtureTypedis: '1',              
            })

        }else{
            this.setState({
                FixtureTypedis: '0',
              
            })

        }     


        if(name == 'top_team' && e.target.value == '0'){
            this.setState({
                hideCol : true,
                NumberOfFixtures : '0'
            })
        }

        if(name == 'top_team' && e.target.value == '1'){
            this.setState({
                hideCol : false,
                NumberOfFixtures : '1'
            })
        }
        // console.log(e.target.value,'console.log()console.log()')
    }
    // RadiofixtureChange=(e)=>{     
    //     this.setState({
    //         TeamFixture :e.target.value,
            
       

    //     })


       
    // }
    
    getEditData = () => {
        this.setState({ posting: true })
        let params = {
            "tournament_id": this.state.Dfst_Id
        }

        DFST_getTournamentEditData(params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let data = responseJson.data.result ? responseJson.data.result : []
                this.editData(data)
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        }).catch((e) => {
            this.setState({ posting: false })
        })
    }

    checkValidation = () => {
        let {FixtureType,NumberOfFixtures, selected_league, tournamentName } = this.state

        if (isNaN(NumberOfFixtures)) {
            notify.show("Please enter number only", "error", 3000);
            return false
        }      
 
        if (selected_league == '') {
            notify.show("Please select league.", "error", 3000);
            return false;
        }

        else if (tournamentName == '') {
            notify.show("Please enter tournament name.", "error", 3000);
            return false;
        }
        else if (tournamentName.length < 6) {
            notify.show("Tournament name length should be greater than or equal to 5.", "error", 3000);
            return false;
        }
        else if (tournamentName.length > 50) {
            notify.show("Tournament name length should be less than or equal to 50.", "error", 3000);
            return false;
        }
       
        else {
            return true;
        }
    }

    checkDistribution = () => {
        let { PrizeSetType } = this.state
        let pType = PrizeSetType['Daily_SetPrize']
        let apiCall = true
        if (pType) {
            if (_.isEmpty(PrizeSetType['Daily_newrow'])) {
                notify.show("Please enter prizes", 'error', 5000)
                apiCall = false
            }

            _.map(PrizeSetType['Daily_newrow'], (rowcheck, idx) => {
                let rowNum = parseInt(idx) + parseInt(1)
                if (rowcheck.min === "") {
                    notify.show("Please enter the minimum rank for row " + rowNum, 'error', 5000)
                    apiCall = false
                }
                if (parseInt(rowcheck.min) > parseInt(rowcheck.max)) {
                    notify.show("Maximum rank should be greater or equal for row " + rowNum, 'error', 5000)
                    apiCall = false
                }
                else if (rowcheck.max === "") {
                    notify.show("Please enter the maximum rank for row " + rowNum, 'error', 5000)
                    apiCall = false
                }
                else if (rowcheck.prize_type === "") {
                    notify.show("Please enter the prize type for row " + rowNum, 'error', 5000)
                    apiCall = false
                }
                else if (rowcheck.prize_type != '3' && (isNaN(rowcheck.amount) || rowcheck.amount == "")) {
                    notify.show("Please enter the amount for row " + rowNum, 'error', 5000)
                    apiCall = false
                }
                else if (rowcheck.amount === "") {
                    notify.show("Please enter the distribution for row " + rowNum, 'error', 5000)
                    apiCall = false
                }
            })
        }
        return apiCall
    }

    CreateTournament = () => {
       
        let {EndDate,FixtureType, TeamFixture ,NumberOfFixtures, selected_league, tournamentName, selected_sport, SetSponsor, UploadLogoName, PrizeSetType, BannerArr } = this.state

        if (!this.checkValidation()) {
            return false
        }

        if (!this.checkDistribution()) {
            return false
        }

        if (SetSponsor == "1" && _isEmpty(BannerArr)) {
            notify.show("Please upload minimum 1 and maximum 5 banner", 'error', 5000)
            return false
        }

        

         if(FixtureType == '1'){

            NumberOfFixtures = NumberOfFixtures;
            TeamFixture =  '1'

         }else{
            NumberOfFixtures = '0';
            TeamFixture = TeamFixture
         }

        //  console.log(FixtureType); return false;
         

         if(FixtureType == '1'){
            // console.log(NumberOfFixtures);return false;
            if(NumberOfFixtures < 1 || NumberOfFixtures >= 99 ) {
                // console.log(NumberOfFixtures)
                notify.show("please select minimum one and max 99 fixture", 'error', 5000)
                return false
            }
        }

        if(EndDate == null){          
            notify.show("Tournament End Date is required", 'error', 5000)
            return false         
        }     

        this.setState({ subPosting: true })

        let params = {
            "sports_id": selected_sport,
            "league_id": selected_league,
            "name": tournamentName,
            "image": UploadLogoName,
            "season_ids": this.getSelectedLeague(),
            "prize_detail": PrizeSetType.Daily_newrow,
            "banner_images": SetSponsor ? BannerArr : [],
            "no_of_fixture":NumberOfFixtures,
            "is_top_team": TeamFixture,
            "end_date": moment.utc(EndDate).format("YYYY-MM-DD HH:mm:ss")
        }   

        // console.log(params); return false;

        DFST_CreateTournment(params).then((responseJson) => {

            // console.log(responseJson); return false
            this.setState({ subPosting: false })
            if (responseJson.response_code === NC.successCode) {
                this.props.history.push('/game_center/DFS?pctab=3&tab=2')
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }else{
                notify.show(responseJson.message, "error", 5000);
            }

        }).catch((e) => {
            // notify.show(responseJson.message, "error", 5000);
            this.setState({
                posting: false
            })
        })
    }

    updateTournament = () => {
        let { Dfst_Id, BannerArr, UploadLogoName, tournamentName } = this.state

        let params = {
            "tournament_id": Dfst_Id,
            "sponsor_banners": BannerArr,
            "image": UploadLogoName,
            "season_game_uids": this.getSelectedLeague().join(),
            "name": tournamentName,
        }
        this.setState({ subPosting: true })
        DFST_updateTournament(params).then((responseJson) => {
            this.setState({ subPosting: false })
            if (responseJson.response_code === NC.successCode) {
                this.props.history.push('/game_center/tournament-detail/' + Dfst_Id + '/' + 1)
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }

        }).catch((e) => {
            this.setState({
                posting: false
            })
        })
    }

    getTournamentMasterData = () => {
        this.setState({ posting: true })
        let params = {}
        DFST_getTournamentMasterData(params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;

                let mArr = (responseJson.merchandise_list) ? responseJson.merchandise_list : [];
                let tempArr = [];

                let entArr = (responseJson.currency_type) ? responseJson.currency_type : [];

                mArr.map(function (lObj) {
                    tempArr.push({ value: lObj.merchandise_id, label: lObj.name + '(' + lObj.price + ')' });
                });

                let prizeTypeTmp = [];
                responseJson.prize_type.map(function (lObj, lKey) {
                    prizeTypeTmp.push(lObj);
                });

                this.setState({
                    MerchandiseList: tempArr,
                    PrizeTypeOpt: prizeTypeTmp,
                    currencyOptions: entArr,
                })

            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            this.setState({ posting: false })
        }).catch((e) => {
            this.setState({
                posting: false
            })
        })
    }

    GetAllLeagueList = () => {
        this.setState({ posting: true })
        let params = {
            "sports_id": this.state.selected_sport
        }

        if (this.state.Dfst_Id > 0) {
            params.edit = 1;
        }
        WSManager.Rest(NC.baseURL + NC.DFSTR_SPORT_LEAGUES, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;
                this.setState({ posting: false }, () => {
                    this.createLeagueList(responseJson);
                })
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            this.setState({
                posting: false
            })
        }).catch((e) => {
            this.setState({
                posting: false
            })
        })
    }

    getLeagueSeasion = () => {
        this.setState({ posting: true })
        let { selected_league } = this.state
        let params = {
            "league_id": selected_league,
        }
        DFSTR_PUBLISHED_FIXTURES(params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = (!_isEmpty(responseJson.data) ? responseJson.data : []);
                this.setState({
                    posting: false,
                    fixtureList: responseJson,
                    TotalFixture: responseJson ? responseJson.length : 0,
                    fixtureMainList: responseJson
                }, () => {

                })
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            this.setState({
                posting: false
            })
        }).catch((e) => {
            this.setState({
                posting: false
            })
        })
    }

    createLeagueList = (list) => {
        let leagueArr = list;
        let tempArr = [];
        if (!_isUndefined(leagueArr) && !_isEmpty(leagueArr)) {
            leagueArr.map(function (lObj, lKey) {
                tempArr.push({ value: lObj.league_id, label: lObj.league_name });
            });
            this.setState({ leagueListM: list, leagueList: tempArr });
        }
    }

    handleFieldVal = (e) => {
        if (e) {
            let name = e.target.name
            let value = e.target.value

            this.setState({ [name]: value })
        }
    }

    handleLeague = (value, dropName) => {
        if (value) {
            if (dropName == "selected_league") {
                this.setState({
                    selected_league: value.value,
                    fixtureList: [],
                    fixtureMainList: [],
                    selectAll: false
                }, () => {
                    this.getLeagueSeasion();
                });
            }
        }
    }

    handleFilter = (value) => {
        if (value) {
            let filteredList = [];
            let tempList = this.state.fixtureMainList;
            this.setState({ fixtureFilterSelected: value }, function () {
                if (value.value == 2) {
                    filteredList = tempList.filter(function (item) {
                        return item.is_selected
                    });
                } else if (value.value == 3) {
                    filteredList = tempList.filter(function (item) {
                        return !item.is_selected
                    });
                } else {
                    filteredList = tempList;
                }

                this.setState({ fixtureList: filteredList, keyword: '' })

            });


        }
    }

    selectAll() {
        _.map(this.state.fixtureList, (item, index) => {
            item.is_selected = this.state.selectAll;
        })

        this.setState({
            fixtureList: this.state.fixtureList
        })

    }

    handleChkVal = (e) => {
        if (e) {
            let value = e.target.checked;
            this.setState({
                selectAll: value,
            }, function () {
                this.selectAll();
            })
        }
    }

    search() {
        let { keyword, fixtureMainList, fixtureFilterSelected } = this.state
        if (keyword.length >= 1) {
            var fixtureLists = fixtureMainList.filter((item) => {
                let condition = (item.home.toLowerCase().includes(keyword.toLowerCase()) || item.away.toLowerCase().includes(keyword.toLowerCase()));

                if (fixtureFilterSelected.value == 2) {
                    return (item.is_selected && condition);
                }
                else if (fixtureFilterSelected.value == 3) {
                    return (!item.is_selected && condition);
                } else {
                    return condition;
                }
            });
            this.setState({ fixtureList: fixtureLists })

        } else {
            this.setState({ fixtureList: fixtureMainList })
        }
    }

    toggle = () => {
        this.setState({ uplogottShow: !this.state.uplogottShow })
    }

    onChangeImage = (event) => {
        this.setState({ UpLogoPosting: true });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("file_name", file);
        data.append("type", "dfstlogo");
        WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        UploadLogoName: Response.data.image_name,
                    })
                }
                this.setState({ UpLogoPosting: false })
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    removeFile = () => {
        let { Dfst_Id, UploadLogoName } = this.state
        let params = {
            "tournament_id": Dfst_Id,
            "image": UploadLogoName
        }
        if (_isEmpty(Dfst_Id)) {
            this.setState({
                UpLogoPosting: true,
                UploadLogoName: '',
            });
        } else {
            DFST_removeTournamentLogo(params).then((responseJson) => {
                if (responseJson.response_code === NC.successCode) {
                    notify.show(responseJson.message, "success", 3000);
                    this.setState({
                        UpLogoPosting: true,
                        UploadLogoName: '',
                    });
                } else if (responseJson.response_code == NC.sessionExpireCode) {
                    WSManager.logout();
                    this.props.history.push('/login');
                }
            }).catch((e) => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            })
        }
    }

    onUploadBanner = (event) => {
        let { BannerArr } = this.state
        if (!_isEmpty(BannerArr) && BannerArr.length >= 5) {
            notify.show("You can add upto 5 banner", "error", 3000);
            return
        }
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        this.setState({ UpLogoPosting: true })
        data.append("file_name", file);
        data.append("type", "dfstournament");
        let tempBannerArr = !_isUndefined(BannerArr) ? BannerArr : []
        WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    let newImg = Response.data.image_name ? Response.data.image_name : ''
                    tempBannerArr.push(newImg)
                    this.setState({ BannerArr: tempBannerArr })
                }
                this.setState({ UpLogoPosting: false })
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }



    removeBannerImg = (img_name) => {
        let tempBanAr = this.state.BannerArr
        _remove(tempBanAr, function (item) {
            return item == img_name
        })
        this.setState({ BannerArr: tempBanAr });
    }

    removeBanner = (img_name) => {
        let { Dfst_Id } = this.state
        let params = {
            "tournament_id": Dfst_Id,
            "image": img_name
        }
        if (!_isEmpty(Dfst_Id) && !_isUndefined(img_name)) {
            DFST_removeTournamentBanner(params).then((responseJson) => {
                if (responseJson.response_code === NC.successCode) {
                    notify.show(responseJson.message, "success", 3000);
                    this.removeBannerImg(img_name)
                } else if (responseJson.response_code == NC.sessionExpireCode) {
                    WSManager.logout();
                    this.props.history.push('/login');
                }
            }).catch((e) => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            })
        } else {
            this.removeBannerImg(img_name)
        }
    }

    sponsorToggle = () => {
        this.setState({ sponsorttShow: !this.state.sponsorttShow })
    }

    handleSponsor = (e) => {
        if (e) {
            let temp_spo = this.state.SetSponsor == '0' ? '1' : '0';
            this.setState({
                SetSponsor: temp_spo
            })
        }
    }

    renderDistributionTable = (CallType) => {
        let { PrizeSetType, PrizeTypeOpt, MerchandiseList, TotalRealDistri, editMode } = this.state
        return (
            <div className="fixed-set-prize">

                <div className='prize-dist-table'>
                    <div className="prize-head clearfix">
                        <div className="head-title">Rank</div>
                        <div className="head-title">Prize Type</div>
                        <div className="head-title">Distribution</div>
                    </div>
                    {
                        _.map(PrizeSetType[CallType + '_newrow'], (newrow, idx) => {
                            return (
                                <div key={idx} className="prize-add-body clearfix">
                                    <div className="rank-add clearfix float-left">
                                        <div className="rank-input">
                                            <Input
                                                type="number"
                                                disabled
                                                value={newrow.min} />
                                        </div>
                                        <div className="rank-seprator">-</div>
                                        <div className="rank-input">
                                            <Input
                                                disabled={editMode}
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
                                            disabled={editMode}
                                            searchable={false}
                                            clearable={false}
                                            placeholder="Type"
                                            className="pd-select"
                                            name={'prize_type'}
                                            value={newrow.prize_type}
                                            options={PrizeTypeOpt}
                                            onChange={(e) => this.handlePrizeType(e, CallType, idx, 'prize_type')}
                                        />
                                    </div>
                                    <div className="fx-distribution clearfix float-left">
                                        {/* {
                                            newrow.prize_type != 3 && */}
                                        <Input
                                            disabled={editMode}
                                            type={newrow.prize_type == 3 ? "text" : "number"}
                                            // value='Iphone'
                                            maxLength={newrow.prize_type == '3' ? '' : 5}
                                            name='value'
                                            value={newrow.amount}
                                            onChange={(e) => this.handlePrizeDis(e, CallType, idx, 'amount', newrow)}
                                        />
                                        {/* // }

                                        // {
                                        //     newrow.prize_type == 3 &&
                                        //     <div className="prize-type clearfix float-left">
                                        //         <Select
                                        //             disabled={editMode}
                                        //             searchable={false}
                                        //             clearable={false}
                                        //             name="value"
                                        //             placeholder="Merchandise"
                                        //             className="pd-select"
                                        //             value={newrow.amount}
                                        //             options={MerchandiseList}
                                        //             onChange={(e) => this.handlePrizeType(e, CallType, idx, 'amount')}
                                        //         />
                                        //     </div>
                                        // } */}

                                        {
                                            <div className="cancel-container">
                                                <span className="dis-each">(Each)</span>

                                                {(!editMode && idx > 0) && <i className="icon-cross icon-style" onClick={() => this.removeRow(CallType, idx)}></i>
                                                }

                                            </div>
                                        }
                                    </div>
                                </div>
                            )
                        })
                    }
                    <div className="add-btn-footer">
                        <div className="dfs-btn-wdt">
                            <div
                                className="add-prize-btn"
                                onClick={() => editMode ? null : this.addPrizeRow(CallType)}>
                                Add Prize <img src={Images.ADDBTN} style={{ maxWidth: 21, position: 'relative', top: '-2px' }} />
                                {/* <i className="icon-plus icon-style ml-2"></i> */}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }

    handleSetPrize = (e, CallType, CallFrom) => {
        let { PrizeSetType } = this.state
        if (e) {
            let name = e.target.name;

            PrizeSetType[CallType + '_SetPrize'] = (name == 'selectSetPrize' && PrizeSetType[CallType + '_SetPrize']) ? false : true
            if (!PrizeSetType[CallType + '_SetPrize']) {
                PrizeSetType[CallType + '_newrow'] = []
            } else {
                PrizeSetType[CallType + '_newrow'] = [{ min: "1", max: "1", prize_type: "1", amount: "" }]
            }

            this.setState({
                PrizeSetType: PrizeSetType
            })
        }
    }

    addPrizeRow = (CallType) => {
        let { PrizeSetType } = this.state
        let newrow_arr = PrizeSetType[CallType + '_newrow'] != null ? PrizeSetType[CallType + '_newrow'] : []
        let minString = !_.isEmpty(newrow_arr) ? (parseInt(newrow_arr[newrow_arr.length - 1].max) + parseInt(1)).toString() : '1'
        let newrow = {
            'min': minString ? minString : '1',
            'max': !isNaN(minString) ? minString : "",
            'prize_type': "1",
            'amount': "",
        }

        newrow_arr.push(newrow)
        PrizeSetType[CallType + '_newrow'] = (newrow_arr)
        this.setState({
            PrizeSetType: PrizeSetType
        });
    }

    removeRow = (CallType, idx) => {
        let { PrizeSetType } = this.state
        PrizeSetType[CallType + '_newrow'].splice(idx, 1)
        this.setState({
            PrizeSetType: PrizeSetType
        }, () => {
            this.getTotRealDistribution(this.state.PrizeSetType)
        });
    }

    SetPrizeToggle = (CallType, key) => {
        let { PrizeSetType } = this.state
        PrizeSetType[CallType + key] = !PrizeSetType[CallType + key]; this.setState({ PrizeSetType: PrizeSetType })
    }

    handlePrizeType = (e, CallType, indx, key) => {
        let { PrizeSetType } = this.state
        if (e) {
            PrizeSetType[CallType + '_newrow'][indx][key] = e.value
            if (key === 'prize_type') {
                PrizeSetType[CallType + '_newrow'][indx]['amount'] = ''
            }
            this.setState({ PrizeSetType: PrizeSetType })
        }
    }

    getTotRealDistribution = (pay_list) => {
        let TotReal = 0
        if (!_isEmpty(pay_list) && !_isUndefined(!_isEmpty(pay_list.Daily_newrow))) {
            pay_list.Daily_newrow.map((pData) => {
                if (pData.prize_type == '1' && !_isEmpty(pData.amount) && !_isEmpty(pData.max) && !_isEmpty(pData.min)) {
                    TotReal += !_.isUndefined(pData.amount) ? (parseInt(pData.amount) * ((parseInt(pData.max) - parseInt(pData.min)) + 1)) : 0
                }
            });
            this.setState({ TotalRealDistri: TotReal })
        }
    }

    handlePrizeDis = (e, CallType, indx, key, item) => {
        let { PrizeSetType } = this.state
        let name = e.target.name;
        let value = e.target.value;
        if (key == 'amount' && item.prize_type == 2) {
            value = parseInt(value)
        }

        if (isNaN(value) && name === 'max') {
            notify.show("Please enter number only", "error", 3000);
            return false
        }
        var totalRow = PrizeSetType[CallType + '_newrow'].length;

        PrizeSetType[CallType + '_newrow'][indx][key] = value.toString()

        if (key === 'max') {
            PrizeSetType[CallType + '_newrow'][indx]['amount'] = ''
        }
        if (totalRow > (indx + 1) && name === 'max') {
            PrizeSetType[CallType + '_newrow'][indx + 1]['min'] = (parseInt(value) + parseInt(1)).toString()
            for (var i = indx + 1; i < totalRow; i++) {
                if (i != indx + 1) { PrizeSetType[CallType + '_newrow'][i]['min'] = '' }
                PrizeSetType[CallType + '_newrow'][i]['max'] = ''
                PrizeSetType[CallType + '_newrow'][i]['amount'] = ''
            }
        }
        this.setState({ PrizeSetType: PrizeSetType }, () => {
            // this.getTotRealDistribution(this.state.PrizeSetType)
        })
    }

    handleCurrChange = (value) => {
        this.setState({ CurrencyType: value.value }, () => {
        })
    }

    handleDate = (date, dateType) => {
        this.validateDate(date)
        this.setState({
          Validedate:  this.validateDate(date)
        })
        this.setState({ [dateType]: date }, () => {
            let time = this.state.TimeOption
            _Map(time, (item, idx) => {
                let disable = this.getTimeDisable(item.value)
                time[idx]['disabled'] = disable
            })
            this.setState({ TimeOption: time })

        })
    }
  validateDate=(date)=>{
    let TodayDate = new Date()
    TodayDate.setMinutes(TodayDate.getMinutes() + 5);
      if (date <= TodayDate) {
        notify.show('Please select time more than 5 min from current time', "error", 5000)
        return false;
      }
      else return true
  } 

  setDateExtend(){
    var d;
    d = new Date();
    d.setMinutes(d.getMinutes() + 5);
    this.setState({
        CreatedDate: d
    },()=>{})
  }

    render() {
        let {EndDate,TodayDate,FixtureTypedis,chekedTwo,chekedOne,Teamcheked,hideCol,FixtureType,NumberOfFixtures ,leagueList, selectAll, tournamentName, UploadLogoName, uplogottShow, sponsorttShow, SetSponsor, TotalFixture, selected_league, editMode, subPosting, BannerArr, fixtureFilterSelected, fixtureFilter, UpLogoPosting ,TeamFixture} = this.state
        let { int_version } = HF.getMasterData()
        console.log(typeof(FixtureType))

        const sameDateProp = { 
            show_time_select: true, 
            time_format: "HH:mm",
            time_intervals: 5,
            time_caption: "time",
            date_format: 'dd/MM/yyyy h:mm aa',
            handleCallbackFn: this.handleDate,
            class_name: 'Select-control inPut icon-calender',
            year_dropdown: true,
            month_dropdown: true,
            className: ''
          }
        
            const DateProps = {
            ...sameDateProp,
            min_date: new Date(TodayDate),
            max_date: null,
            sel_date: EndDate,
            date_key: 'EndDate',
            place_holder: 'Select Date',
            // className: 'icon-calender Ccalender'
            }
        return (
            <div className="dfs-create-tournament dfs-new-tour">
                <Row>
                    <Col md={12}>
                        <h2 className="h2-cls float-left">
                            {editMode ? 'Update ' : 'Create '} Tournament
                        </h2>
                        <div
                            onClick={() => {
                                this.props.history.goBack()
                            }}
                            className="float-right back-to-fixtures mt-1">
                            <img src={Images.RIGHTARROW} />
                            {'< '}Back
                        </div>
                    </Col>
                </Row>
                <div className="dfs-white-box">
                    <Row>
                        <Col md={4}>
                            <div className="">
                                <label className="dfs-label" >League</label>
                                <Select
                                    disabled={editMode}
                                    className={`dfs-sel-league ${editMode ? ' f-disable' : ''}`}
                                    id="selected_league"
                                    name="selected_league"
                                    placeholder="Select League"
                                    value={selected_league}
                                    options={leagueList}
                                    onChange={(e) => this.handleLeague(e, 'selected_league')}
                                />
                            </div>
                        </Col>
                        <Col md={4}>
                            <div className="">
                                <label className="dfs-label">Enter Tournament Name</label>
                                <Input
                                    maxLength="50"
                                    className={"required"}
                                    id="tournamentName"
                                    name="tournamentName"
                                    value={tournamentName}
                                    onChange={(e) => this.handleFieldVal(e)}
                                />
                            </div>
                        </Col>



                        <Col md={4} className="">
                            <div className="dfs-uplogo-box mr-t">
                                <label className="dfs-label uplogo-btn float-none">
                                    Upload Logo
                                    <i className="ml-2 icon-info-border cursor-pointer" id='uplogott'>
                                        <Tooltip
                                            placement="right"
                                            isOpen={uplogottShow}
                                            target='uplogott'
                                            toggle={this.toggle}>
                                            {PT_MSG_UPLOADLOGO}
                                        </Tooltip>
                                    </i>

                                </label>
                                <div className="dfs-image-box changes_box">
                                    {!_.isEmpty(UploadLogoName) ?
                                        <Fragment>
                                            <img className="img-cover" src={NC.S3 + NC.DFST_LOGO + UploadLogoName} />
                                            <div
                                                onClick={this.removeFile}
                                                className="dfs-remove-img">
                                                Remove image
                                            </div>
                                        </Fragment>
                                        :
                                        <Fragment>
                                            <Input
                                                type="file"
                                                name='UploadLogoName'
                                                id="UploadLogoName"
                                                className="dfs-up-btn"
                                                onChange={this.onChangeImage}
                                            />
                                            <div className="dfs-upload">
                                                Upload Image
                                                <div className="dfs-banner-sz">
                                                    Size 470 * 200
                                                </div>
                                            </div>
                                        </Fragment>
                                    }
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <Row className = "mt-50 mb-50">

                    <Col sm={4}  >                   
                      <div className="form-group gray-form-group ">
                      <label className="dfs-label">Contest Type <i className="icon-info" id="ContestTypeTooltip">
                <Tooltip style={{ textAlign: 'left',padding: 10}} placement="top" isOpen={this.state.ContestType} target="ContestTypeTooltip" toggle={this.ContestTypeToggle}>
                <h6>All Fixtures</h6> <p>All the teams from the applicable contests will be considered 
                    for every match. The leaderboard ranking will be based on the sum of fantasy points
                     of all the teams.</p>
                    <h6>Top n Fixtures </h6> <p>Only top performing team from all the applicable contests will be considered for each match. 
                    The leaderboard ranking will be based on this team’s fantasy points</p>
              </Tooltip>
              </i></label>
                        <div className="input-box radio-input-box p-0 pt-2">
                          <ul className="coupons-option-list">
                            <li className="coupons-option-item">
                              <div className="custom-radio">
                                <input
                                  type="radio"
                                  className="custom-control-input"
                                  id="is_auto"
                                  name="FixtureType"
                                  defaultChecked={true}
                                  onChange={(e)=>this.RadioonChange(e,'top_team')}
                                  value="0"
                                //   checked={contestTemplate.prize_pool_type === '1'}
                                  />
                                <label className="custom-control-label" htmlFor="is_auto">
                                  <span className="input-text">All Fixtures</span>
                                </label>
                              </div>
                            </li>
                            <li className="coupons-option-item">
                            {/* onChange={(e) => this.handlePrizeInPercentage(e, 'prize_pool_type')}  */}
                              <div className="custom-radio">
                                <input
                                  type="radio"
                                  className="custom-control-input"
                                  id="is_fixed_value"
                                  name="FixtureType"
                                  value="1"                                  
                                onChange={(e)=>this.RadioonChange(e,'top_team')}                              
                                  />
                                <label className="custom-control-label" htmlFor="is_fixed_value">
                                  <span className="input-text">Top n Fixtures</span>
                                </label>
                              </div>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </Col>                  

                    <Col md={4}>
                            <div className={FixtureType == '0' ? "text-low-color" : ""}>
                                <label className="dfs-label">Number of Fixture to Consider <i className="icon-info" id="NumberFixtureTooltip"> <Tooltip style={{ textAlign: 'left',paddingLeft: 10}} placement="top" isOpen={this.state.NumberFixture} target="NumberFixtureTooltip" toggle={this.NumberFixtureToggle}>
                                The leaderboard ranking will be based on the fantasy points earned in the top ‘n’ fixtures in this tournament
                                </Tooltip></i></label>
                                <Input
                                    maxLength="2"
                                    className={"required"}
                                    id="NumberOfFixtures"
                                    name="NumberOfFixtures"
                                    disabled={hideCol}
                                    value= {NumberOfFixtures}                                    
                                    onChange={(e) => this.handleFieldVal(e)}
                                />
                            </div>
                    </Col>

                    </Row>


                    <Row className="mb-50">
                        <Col sm={4} className="">
                       
                        <div className="form-group gray-form-group">
                        <label className="dfs-label">Teams from Fixtures </label>
                            <div className="input-box radio-input-box p-0 pt-2">
                            <ul className="coupons-option-list">

                            <li className="coupons-option-item">
                                <div className="custom-radio">
                                    <input
                                    type="radio"
                                    className="custom-control-input"
                                    id="is_autos"
                                    name="team_type"
                                    value="1"
                                     //defaultChecked= {FixtureType === '1'}
                                    //defaultChecked= {true}
                                    checked = {FixtureType == '1' ? true : (TeamFixture == 1 ? true : false)}   
                                    onChange={(e)=>this.RadioonChange(e,'team_from_fixture')}  
                                                               
                                    />
                                    
                                    <label className="custom-control-label" htmlFor="is_autos">
                                    <span className="input-text">Top team from contest per fixture</span>
                                    </label>
                                </div>
                                </li>
                               
                                <li className="coupons-option-item">
                              
                                <div className="custom-radio">
                                    <input
                                    type="radio"
                                    className="custom-control-input"
                                    id="is_fixed_values"
                                    name="team_type"
                                    value="0"
                                    //defaultChecked = {FixtureType === '0' ? true : false}
                                     checked = {FixtureType == '1' ? false : (TeamFixture == 0 ? true : false)}                                  
                                   disabled={FixtureTypedis == '1'}                                                              
                                    onChange={(e)=>this.RadioonChange(e,'team_from_fixture')}
                                   
                                    
                                    />
                                    <label className="custom-control-label" htmlFor="is_fixed_values">
                                    <span  className={FixtureType == '1' ? "text-low-color input-text" : "input-text"}>All team from contest per fixture</span>
                                    </label>
                                </div>
                                </li>

                             
                            </ul>
                            </div>
                        </div>
                        </Col>  

                        <Col sm={4} className="">
                            
                        <div className='addFixturesOptionsItem'>
                      <div className='inputFields inPutBg'>

                        <label className="filter-label" htmlFor="CandleDetails">Tournaments Ends On</label>
                        <>
                          <SelectDate DateProps={DateProps} />
                          <i className='icon-calender Ccalender trntcalender'></i>
                       </>
                        
                        
                      </div>  
                    </div>
                            
                        </Col>                



                        </Row>



                    {this.getSelectedLeague().length != 0 ?
                        <label className="fixture-label">
                            {int_version == "1" ? "Select Games" : "Select Fixture"}
                            <span className="dfs-sel-fx">
                                {' (' + this.getSelectedLeague().length + '/' + TotalFixture +( int_version =="1" ? " Game selected)" : " Fixture selected)")}
                            </span>
                        </label>
                        : <label className="fixture-label">{int_version == "1" ? "Select Games" : "Select Fixture"}</label>
                    }
                    <div className="dfs-fixture-view">
                        <div className="fixture-view-header">
                            <div className="select-all-parent">
                                <input type="checkbox"
                                    defaultChecked={selectAll}
                                    checked={selectAll}
                                    onChange={(e) => this.handleChkVal(e)}
                                />
                                <label className="dfs-select-all">Select All</label>
                            </div>
                            <div className="right-item">
                                <Select
                                    className="dfs-filter-dopdown"
                                    id="selected_league"
                                    name="selected_league"
                                    placeholder="All"
                                    value={fixtureFilterSelected}
                                    options={fixtureFilter}
                                    onChange={(e) => this.handleFilter(e, 'fixtureFilter')}
                                />
                                <FormGroup className="float-right">
                                    <InputGroup className="dfs-search-wrapper">
                                        <i className="icon-search" onClick={() => {
                                            this.search();
                                        }}></i>
                                        <Input
                                            type="text"
                                            id="keyword"
                                            name="keyword"
                                            value={this.state.keyword}
                                            onChange={(e) => this.handleFieldVal(e)}
                                            onKeyPress={event => {
                                                if (event.key === 'Enter') {
                                                    this.search()
                                                }
                                            }}
                                            placeholder="Search by name" />
                                    </InputGroup>
                                </FormGroup>
                            </div>
                        </div>
                        <div className="line" />
                        <div>
                            <Row>{
                                _Map(this.state.fixtureList, (item, idx) => {
                                    return (
                                        <Col md={4} key={idx}>
                                            <div
                                                className="dfst common-fixture"
                                                onClick={() => {
                                                    item.is_selected = !item.is_selected;
                                                    let isAllSelected = this.isAllSelected();
                                                    this.setState({ selectAll: isAllSelected }, () => {
                                                        this.setState({ fixtureList: this.state.fixtureList })
                                                    })
                                                }}
                                            >
                                                <div className="bg-card">
                                                    {
                                                        item.is_selected &&
                                                        <div className="right-selection">
                                                            <img src={Images.tick} className="rght-img" />
                                                        </div>
                                                    }
                                                    <div>
                                                        {
                                                            item.is_tour_game != 1 &&
                                                            <>
                                                                <img className="com-fixture-flag float-left" src={item.home_flag ? NC.S3 + NC.FLAG + item.home_flag : Images.no_image} />
                                                                <img className="com-fixture-flag float-right" src={item.away_flag ? NC.S3 + NC.FLAG + item.away_flag : Images.no_image} />
                                                            </>
                                                        }
                                                        <div className="com-fixture-container">
                                                            {
                                                                item.is_tour_game != 1 ?
                                                                    <div className="com-fixture-name">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                                    :
                                                                    <div className="com-fixture-name">{item.tournament_name}</div>
                                                            }
                                                            <div className="com-fixture-title">
                                                                {
                                                                    <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} />
                                                                }
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </Col>
                                    )
                                })
                            }
                            </Row>
                        </div>

                    </div>
                    <Row className="mt-30">
                        <Col md="12">
                            {this.renderDistributionTable('Daily')}
                        </Col>
                    </Row>
                </div>
                <div className="dfs-sponsor-box dfs-white-box">
                    <Row>
                        <Col md={3}>
                            <div className="">
                                <label className="dfs-label">
                                    Sponsored
                                    <i className="ml-2 icon-info-border cursor-pointer"
                                        id='sponsortt'>
                                        <Tooltip
                                            placement="right"
                                            isOpen={sponsorttShow}
                                            target='sponsortt'
                                            toggle={this.sponsorToggle}>
                                            {PT_SPONSOR_MSG}
                                        </Tooltip>
                                    </i>
                                </label>
                                <div className="common-cus-checkbox">
                                    <label className="com-chekbox-container">
                                        <span className="opt-text">Enable Sponser</span>
                                        <input
                                            type="checkbox"
                                            name="SetSponsor"
                                            checked={SetSponsor == '1' ? true : false}
                                            onChange={(e) => this.handleSponsor(e)}
                                        />
                                        <span className="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                            </div>
                        </Col>
                        {
                            (SetSponsor == '1') &&
                            <Col md={9}>
                                <div className="dfs-uplogo-box">
                                    <div className="dfs-banner-box">
                                        <Fragment>
                                            <div className="dfs-banner-sz clearfix">
                                                <div className="dfs-btn-txt">
                                                    <div className="dfs-upload">
                                                        {
                                                            !UpLogoPosting &&
                                                            <Input
                                                                type="file"
                                                                name='UploadBanner'
                                                                id="UploadBanner"
                                                                className="dfs-up-btn"
                                                                onChange={(e) => this.onUploadBanner(e)}
                                                            />
                                                        }
                                                        Upload Banner
                                                    </div>
                                                    <div className="dfs-banner-sz">
                                                        Size 1300 X 240
                                                    </div>
                                                </div>
                                                <div className="dfs-banner-text">
                                                    You can add upto 5 banner
                                                </div>
                                            </div>
                                        </Fragment>
                                    </div>
                                </div>
                            </Col>
                        }
                    </Row>
                    {
                        (SetSponsor == '1') &&
                        <Row>
                            {
                                _Map(BannerArr, (item, idx) => {
                                    return (
                                        <Col md={4} key={idx}>
                                            <div className="dfs-banner-img">
                                                <i
                                                    onClick={() => this.removeBanner(item)}
                                                    className="icon-close"></i>
                                                <img src={NC.S3 + NC.DFST_SPONSOR + item} className="img-cover" />
                                            </div>
                                        </Col>
                                    )
                                })
                            }
                        </Row>
                    }
                </div>
                <Row>
                    <Col md={12}>
                        <div className="dfs-btn-sub">
                            <Button
                                disabled={subPosting}
                                onClick={() => editMode ? this.updateTournament() : this.CreateTournament()}
                                className='btn-secondary-outline'>
                                Submit
                            </Button>
                        </div>
                    </Col>
                </Row>
            </div>

        )
    }

    getFormatedDate = (date) => {
        date = WSManager.getUtcToLocal(date);
        return moment(date).format('LLLL');
    }
    getSelectedLeague() {
        let season_id = [];
        _.map(this.state.fixtureList, (item, index) => {
            if (item.is_selected)
                season_id.push(item.season_id);
        })
        return season_id;
    }
    isAllSelected() {
        let isAllSelected = true;
        for (let i = 0; i < this.state.fixtureList.length; i++) {
            if (!this.state.fixtureList[i].is_selected) {
                isAllSelected = false;
                break;
            }
        }
        return isAllSelected;
    }
}
export default DfsCreateTournament;
