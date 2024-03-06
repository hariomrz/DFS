import React, { Component, Fragment } from "react";
import { Button, Row, Col, Tooltip, FormGroup, Input } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import Select from 'react-select';
import LS from 'local-storage';
import _ from 'lodash';
import Images from '../../components/images';
import moment from 'moment';
import { notify } from 'react-notify-toast';
import { PT_SPONSOR_MSG, PT_MSG_UPLOADLOGO, PT_SET_PRIZE, PT_START_G_END, PT_START_G_CUR, PT_TIE_BREAKER, PERFECT_SCORE_INFO, CONTEST_BONUS } from "../../helper/Message";
import HF, { _times, _isEmpty, _isUndefined, _Map, _remove } from "../../helper/HelperFunction";
import { getPickemFixtureList, PT_getTournamentEditData, PT_addMatchesToTournament, PT_removeTournamentLogo, PT_removeTournamentBanner, getPickemAllLeagues, getPickemTournamentList, getPickemSaveTournament, pickemGetMasterdata } from "../../helper/WSCalling";
import { MomentDateComponent } from "../../components/CustomComponent";
import { Base64 } from 'js-base64';
import queryString from 'query-string';
import InputGroup from 'react-bootstrap/InputGroup'
import SelectDate from "../../components/SelectDate";
class PTCreateTournament extends Component {

    constructor(props) {
        super(props);
        let DefaultPrizeSetType = {
            Daily_SetPrize: true,
            TieBreaker: false,
            Daily_newrow: [{ min: "1", max: "1", prize_type: "1", amount: "0" }],
        }
        let DefaultPrizeSetType1 = {
            Daily_SetPrize: true,
            TieBreaker: false,
            Daily1_newrow: [{  prize_type: "1", amount: "0", correct : "jackpot" }],
        }
        this.state = {
            selected_league: "",
            league_start_date: "",
            league_end_date: "",
            SubstitutesAllowed: "0",
            leagueList: [],
            leagueListM: [],
            fixtureList: [],
            fixtureFilter: [{ label: "All", id: 1 }, { label: "Selected", value: 2 }, { label: "Unselected", value: 3 }],
            fixtureFilterSelected: { label: "All", value: 1 },
            fixtureMainList: [],
            roster_list: [1, 2, 3],
            fixtureDetail: {},
            tournamentName: '',
            accordion: [],
            activeTab: 1,
            selectedAvtar: '',
            posting: false,
            keyword: '',
            selectAll: false,
            showAvatarPopup: false,
            dropdownOpen: new Array(19).fill(false),
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            TotalFixtures: '',

            FromDate: null,
            ToDate: null,
            TodayDate: new Date(),
            uplogottShow: false,
            sponsorttShow: false,
            SetSponsor: '0',
            payout_data: [],
            prize_profit: { min_total: 0, max_total: 0, min_gross_profit: 0, max_gross_profit: 0, min_net_profit: 0, max_net_profit: 0 },
            PrizeSetType: DefaultPrizeSetType,
            PrizeSetType1: DefaultPrizeSetType1,
            MerchandiseList: [],
            PrizeTypeOpt: [],
            TotalRealDistri: 0,
            TotalFixture: 0,
            PickemId: 0,
            editMode: false,
            subPosting: false,
            BannerArr: [],
            checkedTie: false,
            fixtureList: [],
            customOption: [
                {
                    value: 1,
                    label: "Real Money"
                },
                {
                    value: 2,
                    label: "Coin"
                },
                {
                    value: 0,
                    label: "Bonus"
                }
                // {
                //     value: 4,
                //     label: "Merchandise "
                // }
            ],
            selectedSeasonId: [],
            detailedList: [],
            perfectMoneyName: 'null',
            perfectAmount: '',
            perfectQuestion: '',
            selectAllID: [],
            setPrizeTypeValue: '',
            tieBreakerShow: false,
            perfectScoreShowToggle: false,
            handlePerfectScored: false,
            question: '',
            startRange: '',
            endRange: '',
            bonusAllowed: 0,
            setCurrencyvalue: 1,
            // prize_type_perfect_amount: '',
            currency_type_option: [],
            questionView: false,           
            EndDate: null,
            Todates: new Date(),
            checkedMatch: false
        };
    }



    componentDidMount() {
        this.getTournamentMasterData();
        this.getMasterDataApi();

        let values = queryString.parse(this.props.location.search)
        if (!_isUndefined(values.pid)) {
            this.setState({
                PickemId: !_isUndefined(values.pid) ? Base64.decode(values.pid) : '0'
            }, () => {
                this.getEditData();
                this.GetAllLeagueList();
            })
        } else {
            this.GetAllLeagueList();
        }
    }



    getMasterDataApi = () => {
        this.setState({ posting: true })
        pickemGetMasterdata().then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    PrizeTypeOpt: responseJson.data.prize_type,
                    currency_type_option: responseJson.data.currency_type
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

    editData = (data) => {
        let temDisData = this.state.PrizeSetType
        temDisData['TieBreaker'] = parseInt(data.is_tie_breaker) ? true : false
        temDisData['Daily_SetPrize'] = parseInt(data.is_prize) ? true : false


        temDisData['Daily_newrow'] = data.prize_detail ? data.prize_detail : ''
        temDisData['Daily1_newrow'] = data.prize_detail ? data.prize_detail : ''
        this.getTotRealDistribution(this.state.PrizeSetType)
        this.setState({
            editMode: true,
            selected_league: data.league_id ? data.league_id : '',
            tournamentName: data.name ? data.name : '',
            TotalFixtures: data.match_count ? data.match_count : '',
            FromDate: data.start_date ? WSManager.getUtcToLocal(data.start_date) : '',
            ToDate: data.end_date ? WSManager.getUtcToLocal(data.end_date) : '',

            PrizeSetType: temDisData,
            PrizeSetType1: temDisData,
            fixtureList: data.fixtures ? data.fixtures : [],
            fixtureMainList: data.fixtures ? data.fixtures : [],
            TotalFixture: data.fixtures ? data.fixtures.length : '',
            BannerArr: data.sponsor_banners ? data.sponsor_banners : [],
            SetSponsor: (data.sponsor_banners && data.sponsor_banners.length > 0) ? '1' : '0',
            UploadLogoName: (data.image) ? data.image : '',
        })
    }

    getEditData = () => {
        this.setState({ posting: true })
        let params = {
            "pickem_id": this.state.PickemId
        }

        PT_getTournamentEditData(params).then((responseJson) => {
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
        let { selected_league, tournamentName, entryFee, bonusAllowed, setCurrencyvalue, checkedTie, question, selectAllID, storeSeasonId, startRange, endRange, handlePerfectScored, perfectMoneyName, perfectAmount, perfectQuestion } = this.state

        // else if (_isEmpty(TotalFixtures) || (parseInt(TotalFixtures) < 1)) {
        //     notify.show("Total fixtures length should be greater than or equal to 1.", "error", 3000);
        //     return false;
        // }
        // else if (_isEmpty(TotalFixtures) || TotalFixtures.length > 3) {
        //     notify.show("Total fixtures length can not be exceed more than 3 digit.", "error", 3000);
        //     return false;
        // }
        // else if (this.getSelectedLeague().length <= 0) {
        //     notify.show("Please select atleast one fixture.", "error", 3000);
        //     return false;
        // }

        // if (bonusAllowed != "" && setCurrencyvalue != 2) {
        //     notify.show("Bonus should not be empty", "error", 3000);
        //     return false;
        // }
    }

    checkDistribution = () => {
        let { selected_league, tournamentName, prize_type_perfect_amount, entryFee, bonusAllowed, checkedTie, question, selectAllID, storeSeasonId, startRange, endRange, handlePerfectScored, perfectMoneyName, perfectAmount, PrizeSetType } = this.state;
        let pType = PrizeSetType['Daily_SetPrize']
        // return false;
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
                else if (rowcheck.prize_type != '3' && (isNaN(rowcheck.amount) || rowcheck.amount == "0")) {
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
    checkDistribution1 = () => {
        let { PrizeSetType1 } = this.state;
        let pType = PrizeSetType1['Daily_SetPrize']
        // return false;
        let apiCall = true
        if (pType) {
            if (_.isEmpty(PrizeSetType1['Daily1_newrow'])) {
                notify.show("Please enter prizes", 'error', 5000)
                apiCall = false
            }

            _.map(PrizeSetType1['Daily1_newrow'], (rowcheck, idx) => {
                let rowNum = parseInt(idx) + parseInt(1)

                if (rowcheck.prize_type === "") {
                    notify.show("Please enter the prize type for row " + rowNum, 'error', 5000)
                    apiCall = false
                }
                // else if (rowcheck.prize_type != '3' && (isNaN(rowcheck.amount) || rowcheck.amount == "0")) {
                //     notify.show("Please enter the amount for row " + rowNum, 'error', 5000)
                //     apiCall = false
                // }
                else if (rowcheck.amount === "") {
                    notify.show("Please enter the value for row " + rowNum, 'error', 5000)
                    apiCall = false
                }
                // else if (rowcheck.prize_type != '3' && (isNaN(rowcheck.correct) || rowcheck.correct == "0")) {
                //     notify.show("Please enter the amount for row " + rowNum, 'error', 5000)
                //     apiCall = false
                // }
                else if ( idx == 0 ? "" : rowcheck.correct === "") {
                    notify.show("Please enter the correct pick value for row " + rowNum, 'error', 5000)
                    apiCall = false
                }

            })
        }

        return apiCall
    }
    CreateTournament = () => {
        let { checkedMatch, EndDate, PrizeSetType1,selected_league, prize_type_perfect_amount, handlePerfectScored, UploadLogoName, checkedTie, perfectScoreShowToggle, setCurrencyvalue, tournamentName, selectAll, selectAllID, perfectAmount, perfectMoneyName, fixtureList, selected_sport, storeSeasonId, SetSponsor, PrizeSetType, question, startRange, endRange, entryFee, bonusAllowed } = this.state
        this.dateCheck()

        if (checkedMatch == true){

           var checkedMatchValue = '1'
        }else{
            var checkedMatchValue = '0' 
        }


        if (this.state.entryFee && this.state.entryFee.length > 5) {
            notify.show("Entry fee should not be greater than 99999", "error", 3000);
            return false
        }

        if (bonusAllowed && bonusAllowed > 100 && setCurrencyvalue != 2) {
            notify.show("Bonus should not be greater than 100%", "error", 3000);
            return false
        }

        if (checkedTie) {
            if (startRange == "") {
                notify.show("Please enter start range.", "error", 3000);
                return false
            }

            if (endRange == "") {
                notify.show("Please enter end range.", "error", 3000);
                return false
            }
            if (question == "" || question.length < 4) {
                notify.show("Please enter question with min 4 characters.", "error", 3000);
                return false
            }
            if (Number(endRange) <= Number(startRange)) {
                notify.show("Min and Max range should not be same and min should be less than max ", "error", 3000);
                return false
            }
        }

        if (!this.checkDistribution()) {
            return false
        }
        if(handlePerfectScored){
        if (!this.checkDistribution1()) {
            return false
        }
    }
        

        if (SetSponsor == "1" && _isEmpty(this.state.BannerArr)) {
            notify.show("Please upload minimum 1 and maximum 5 banner", 'error', 5000)
            return false
        }

        // if (handlePerfectScored == true) {
        //     if (perfectMoneyName == 'null') {
        //         notify.show("Please enter perfect score prize type.", "error", 3000);
        //         return false
        //     }
        //     if (perfectAmount == '') {
        //         notify.show("Please enter perfect score value.", "error", 3000);
        //         return false
        //     }
        // }

        if (EndDate == null) {
            notify.show("Tournament End Date is required", 'error', 5000)
            return false
        }  

        this.setState({ subPosting: true })

        let params = {}
        let selSeasonId = []
        _Map(this.state.fixtureList, (obj, idx) => {
            if (obj.is_selected) {
                selSeasonId.push(obj.season_id)
            }
        })
        //return true
        if (handlePerfectScored && checkedTie) {
            params = {
                "sports_id": selected_sport,
                "league_id": selected_league,
                "name": tournamentName,
                "image": UploadLogoName,
                "season_ids": selSeasonId,
                // "season_ids": this.state.selectAll ? selectAllID : selSeasonId,
                "prize_detail": PrizeSetType.Daily_newrow,
                "currency_type": "1",
                "entry_fee": entryFee,
                "max_bonus": bonusAllowed || 0,
                "banner_images": this.state.BannerArr,
                "tie_breaker_question": {
                    "question": question,
                    "start": startRange,
                    "end": endRange
                },
                "perfect_score": PrizeSetType1.Daily1_newrow,
                // "perfect_score": { "amount": perfectAmount, "prize_type": perfectMoneyName },
                "currency_type": setCurrencyvalue,
                "end_date": moment.utc(EndDate).format("YYYY-MM-DD HH:mm:ss"),
                "auto_match_publish": checkedMatchValue
            }
        }
        if (!handlePerfectScored && !checkedTie) {
            params = {
                "sports_id": selected_sport,
                "league_id": selected_league,
                "name": tournamentName,
                "image": UploadLogoName,
                "season_ids": selSeasonId,
                // "season_ids": this.state.selectAll ? selectAllID : selSeasonId,
                "prize_detail": PrizeSetType.Daily_newrow,
                "currency_type": "1",
                "entry_fee": entryFee,
                "max_bonus": bonusAllowed,
                "banner_images": this.state.BannerArr,
                "currency_type": setCurrencyvalue,
                "end_date": moment.utc(EndDate).format("YYYY-MM-DD HH:mm:ss"),
                "auto_match_publish": checkedMatchValue
            }



        }

        if (handlePerfectScored && !checkedTie) {
            params = {
                "sports_id": selected_sport,
                "league_id": selected_league,
                "name": tournamentName,
                "image": UploadLogoName,
                "season_ids": selSeasonId,
                // "season_ids": this.state.selectAll ? selectAllID : selSeasonId,
                "prize_detail": PrizeSetType.Daily_newrow,
                "currency_type": "1",
                "entry_fee": entryFee,
                "max_bonus": bonusAllowed,
                "banner_images": this.state.BannerArr,
                "perfect_score": PrizeSetType1.Daily1_newrow,
                // "perfect_score": { "amount": perfectAmount, "prize_type": perfectMoneyName },
                "currency_type": setCurrencyvalue,
                "end_date": moment.utc(EndDate).format("YYYY-MM-DD HH:mm:ss"),
                "auto_match_publish": checkedMatchValue
            }
        }

        if (checkedTie && !handlePerfectScored) {
            params = {
                "sports_id": selected_sport,
                "auto_match_publish": checkedMatchValue,
                "end_date": moment.utc(EndDate).format("YYYY-MM-DD HH:mm:ss"),
                "league_id": selected_league,
                "name": tournamentName,
                "image": UploadLogoName,
                "season_ids": selSeasonId,
                // "season_ids": this.state.selectAll ? selectAllID : selSeasonId,
                "prize_detail": PrizeSetType.Daily_newrow,
                "currency_type": "1",
                "entry_fee": entryFee,
                "max_bonus": bonusAllowed,
                "banner_images": this.state.BannerArr,
                "currency_type": setCurrencyvalue,
                "tie_breaker_question": {
                    "question": question,
                    "start": startRange,
                    "end": endRange

                }
            }
        }

        // console.log(params); return false;
        getPickemSaveTournament(params).then((responseJson) => {
            this.setState({ subPosting: false })
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                this.props.history.push('/pickem/picks?pctab=2')
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            } else{
                notify.show(responseJson.error && responseJson.error.name ? responseJson.error.name : responseJson.message, 'error', 5000)
            }

        }).catch((e) => {
            this.setState({
                posting: false
            })
        })

    }

    // updateTournament = () => {
    //     let { PickemId, BannerArr, UploadLogoName, TotalFixtures } = this.state

    //     let params = {
    //         "pickem_id": PickemId,
    //         "sponsor_banners": BannerArr,
    //         "image": UploadLogoName,
    //         "total_fixtures": TotalFixtures,
    //         "season_game_uids": this.getSelectedLeague().join(),
    //     }
    //     this.setState({ subPosting: true })
    //     PT_addMatchesToTournament(params).then((responseJson) => {
    //         this.setState({ subPosting: false })
    //         if (responseJson.response_code === NC.successCode) {
    //             this.props.history.push('/pickem/tournament-detail/' + PickemId + '/' + 1)
    //         } else if (responseJson.response_code == NC.sessionExpireCode) {
    //             WSManager.logout();
    //             this.props.history.push('/login');
    //         }

    //     }).catch((e) => {
    //         this.setState({
    //             posting: false
    //         })
    //     })
    // }

    getTournamentMasterData = () => {
        this.setState({ posting: true })
        let params = {
            "sports_id": this.state.selected_sport,
            "status": "live"
        }
        getPickemTournamentList(params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;

                let mArr = (responseJson.merchandise_list) ? responseJson.merchandise_list : [];
                let tempArr = [];

                mArr.map(function (lObj) {
                    tempArr.push({ value: lObj.merchandise_id, label: lObj.name + '(' + lObj.price + ')' });
                });
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

        if (this.state.PickemId > 0) {
            params.edit = 1;
        }
        getPickemAllLeagues(params).then((responseJson) => {
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
            if (name == 'bonusAllowed' || name == 'startRange' || name == 'endRange') {
                value = value.replace(/[^0-9]/g, '');
                this.setState({ [name]: value }, () => {
                    if ((name == 'startRange' || name == 'endRange') && this.state.endRange !== '') {
                        if (Number(this.state.endRange) <= Number(this.state.startRange)) {
                            notify.show("Min and Max range should not be same and min should be less than max ", "error", 3000);
                        }

                    }
                })
            }
            else {
                this.setState({ [name]: value })
            }

            if (name == 'entryFee' && value.length > 5) {
                notify.show("Entry fee should not be greater than 99999", "error", 3000);
                this.setState({ [name]: '' })
            }

            if (name == 'bonusAllowed' && value > 100 && this.state.setCurrencyvalue != 2) {
                notify.show("Bonus should not be greater than 100%", "error", 3000);
            }

            if (name === 'TotalFixtures' && value.length > 3) {
                notify.show("Total fixtures length can not be exceed more than 3 digit.", "error", 3000);
                this.setState({ [name]: '' })
            }

            if (name == 'endRange' && value > 999999) {

                notify.show("End range should not be greater than 999999", "error", 3000);
            }

            // if(this.state.startRange == this.state.endRange){
            //     alert('radheee')
            // }


        }
    }

    handleLeague = (value, dropName) => {

        if (value.value) {
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



    getLeagueSeasion = () => {
        this.setState({ posting: true })
        let { selected_sport, selected_league, FromDate, ToDate } = this.state
        let params = {
            "sports_id": selected_sport,
            "league_id": selected_league,
        }
        getPickemFixtureList(params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    fixtureList: responseJson.data,
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

    handleFilter = (value) => {
        if (value) {
            let filteredList = [];
            let tempList = this.state.fixtureMainList && this.state.fixtureMainList.data;
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


    handleChkVal = (e) => {
        if (e) {
            let value = e.target.checked;
            this.setState({
                selectAll: value,
            }, () => {
                this.selectAll();
            })
        }
    }


    selectAll() {
        // let season_id = [];
        // _.map(this.state.fixtureList, (item, index) => {
        //     season_id.push(item.season_id)
        // })
        let season_id = [];
        _.map(this.state.fixtureList, (item, index) => {
            item.is_selected = this.state.selectAll;
            season_id.push(item.season_id)
        })

        this.setState({
            fixtureList: this.state.fixtureList,
            selectAllID: season_id
        })


        // this.setState({
        //     selectAllID: season_id
        // })
    }

    search() {
        let { keyword, fixtureMainList } = this.state
        if (keyword.length >= 1) {
            var fixtureLists = fixtureMainList.data.filter((item) => {
                let condition = (item.home.toLowerCase().includes(keyword.toLowerCase()) || item.away.toLowerCase().includes(keyword.toLowerCase()));
                // if (fixtureFilterSelected.value == 2) {
                //     return (item.is_selected && condition);
                // }
                // else if (fixtureFilterSelected.value == 3) {
                //     return (!item.is_selected && condition);
                // } else {
                return condition;
                // }
            });
            this.setState({ fixtureList: fixtureLists })

        } else {
            this.setState({ fixtureList: fixtureMainList })
        }
    }

    dateCheck = (dateType = '') => {
        let { FromDate, ToDate, TodayDate } = this.state
        if (dateType == 'FromDate' && FromDate <= TodayDate) {
            notify.show(PT_START_G_CUR, "error", 5000)
            return false;
        }
        else if (dateType == 'ToDate' && ToDate <= FromDate) {
            notify.show(PT_START_G_END, "error", 5000)
            return false;
        }
        else {
            return true;
        }
    }

    handleScore = () => {
        this.setState({
            handlePerfectScored: !this.state.handlePerfectScored
        })
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date, selectAll: false }, () => {
            this.dateCheck(dateType)

            if (this.dateCheck(dateType) && !_isEmpty(this.state.selected_league))
                this.getLeagueSeasion()
        })
    }

    toggle = () => {
        this.setState({ uplogottShow: !this.state.uplogottShow })
    }
    toggleTieBreaker = () => {
        this.setState({ tieBreakerShow: !this.state.tieBreakerShow })
    }
    togglePerfectScore = () => {
        this.setState({ perfectScoreShowToggle: !this.state.perfectScoreShowToggle })
    }

    onChangeImage = (event) => {
        this.setState({ UpLogoPosting: true });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();

        data.append("file_name", file);
        data.append("type", "logo");
        WSManager.multipartPost(NC.baseURL + NC.PICKEM_DO_UPLOAD, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        UploadLogoName: Response.data.image_url,
                    })
                }
                this.setState({ UpLogoPosting: false })
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    removeFile = () => {
        let { PickemId, UploadLogoName } = this.state
        let params = {
            "pickem_id": PickemId,
            "image": UploadLogoName
        }
        if (_isEmpty(PickemId)) {
            this.setState({
                UpLogoPosting: true,
                UploadLogoName: '',
            });
        } else {
            PT_removeTournamentLogo(params).then((responseJson) => {
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

    // onUploadBanner = (event) => {
    //     let { BannerArr } = this.state
    //     if (!_isEmpty(BannerArr) && BannerArr.length >= 5) {
    //         notify.show("You can add upto 5 banner", "error", 3000);
    //         return
    //     }
    //     const file = event.target.files[0];
    //     if (!file) {
    //         return;
    //     }

    //     var data = new FormData();
    //     data.append("file_name", file);
    //     data.append("type", "sponsor");

    //     let tempBannerArr = !_isUndefined(BannerArr) ? BannerArr : []

    //     WSManager.multipartPost(NC.baseURL + NC.PICKEM_DO_UPLOAD, data)
    //         .then(Response => {
    //             if (Response.response_code == NC.successCode) {
    //                 let newImg = Response.data.image_url ? Response.data.image_url : ''
    //                 tempBannerArr.push(newImg)
    //                 this.setState({ BannerArr: tempBannerArr })
    //             }
    //             this.setState({ UpLogoPosting: false })
    //         }).catch(error => {
    //             notify.show(NC.SYSTEM_ERROR, "error", 3000);
    //         });
    // }


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
        data.append("type", "sponsor");
        let tempBannerArr = !_isUndefined(BannerArr) ? BannerArr : []
        WSManager.multipartPost(NC.baseURL + NC.PICKEM_DO_UPLOAD, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    let newImg = Response.data.image_url ? Response.data.image_url : ''
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
        let { PickemId } = this.state
        let params = {
            "pickem_id": PickemId,
            "image": img_name
        }
        if (!_isEmpty(PickemId) && !_isUndefined(img_name)) {
            PT_removeTournamentBanner(params).then((responseJson) => {
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

    uploadMultipleFiles = (e) => {
        this.setState({ Posting: true, deletePosting: false, newPhotoArr: [] })
        let fileObj = []
        let fileArray = []
        fileObj.push(e.target.files)

        var data = new FormData();

        for (let i = 0; i < fileObj[0].length; i++) {
            fileArray.push(URL.createObjectURL(fileObj[0][i]))
            data.append('file[' + i + ']', fileObj[0][i]);
        }

        this.setState({
            fileArray: fileArray
        }, () => {
            WSManager.multipartPost(NC.baseURL + NC.UPLOAD_ABOUT_US, data)
                .then(Response => {
                    if (Response.response_code == NC.successCode) {
                        if (_.isEmpty(Response.data)) {
                            notify.show("Something went wrong due to more images uploaded.Please try again", "error", 5000);
                            this.setState({ fileArray: [] })
                        } else {
                            this.setState({
                                newPhotoArr: Response.data,
                                UpLogoPosting: false,
                            });
                        }
                        this.setState({ UpLogoPosting: false });
                    }
                    this.setState({ Posting: false, deletePosting: true, fileArray: [] });
                }).catch(error => {
                    notify.show(NC.SYSTEM_ERROR, "error", 3000);
                });
        })
    }
    renderDistributionTable = (CallType) => {
        let { PrizeSetType, PrizeTypeOpt, MerchandiseList, merchandiseObj, TotalRealDistri, editMode, currency_type_option } = this.state
        return (
            <div className="fixed-set-prize">
                {/* <div className="pt-setp-stype"> */}
                <div className="set-prizes-title">Set Prizes
                    <i className="icon-info-border ml-2" id={CallType + '_PrizeTT'}>
                        <Tooltip placement="right" isOpen={PrizeSetType[CallType + '_PrizeToolTip']} target={CallType + '_PrizeTT'} toggle={() => this.SetPrizeToggle(CallType, '_PrizeToolTip')}>{PT_SET_PRIZE}</Tooltip>
                    </i>
                </div>
                {/* <div className="select-prize-op">
                        <div className="common-cus-checkbox">
                            <label className="com-chekbox-container">
                                <span className="opt-text">Yes</span>
                                <input
                                    disabled={editMode}
                                    type="checkbox"
                                    name="selectSetPrize"

                                    defaultChecked={PrizeSetType[CallType + '_SetPrize']}
                                    checked={PrizeSetType[CallType + '_SetPrize']}

                                    onChange={(e) => this.handleSetPrize(e, CallType, 'SetPrize')}
                                />
                                <span className="com-chekbox-checkmark"></span>
                            </label>
                        </div>
                    </div> */}
                {/* </div> */}
                {/* <div> */}
                {/* <div className="set-prizes-title">Tie Breaker</div> */}
                {/* <div className="select-prize-op">
                        <div className="common-cus-checkbox">
                            <label className="com-chekbox-container"> */}
                {/* <span className="opt-text">Yes</span> */}
                {/* <input
                                    disabled={editMode || !PrizeSetType[CallType + '_SetPrize']}
                                    type="checkbox"
                                    name="selectTieBreaker"
                                    checked={PrizeSetType['TieBreaker']}
                                    onChange={(e) => this.handleSetPrize(e, CallType, 'TieBreaker')}
                                />
                                <span className="com-chekbox-checkmark"></span> */}
                {/* </label>
                        </div>
                    </div> */}
                {/* </div> */}
                {PrizeSetType[CallType + '_SetPrize'] && (
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
                                                    // value='01'
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
                                                options={PrizeSetType['TieBreaker'] ? [...PrizeTypeOpt, merchandiseObj] : PrizeTypeOpt}
                                                onChange={(e) => this.handlePrizeType(e, CallType, idx, 'prize_type')}
                                            />
                                        </div>
                                        <div className="fx-distribution clearfix float-left">
                                            {
                                                // newrow.prize_type != 3 &&
                                                <Input
                                                    disabled={editMode}
                                                    type="text"
                                                    // value='Iphone'
                                                    maxLength={newrow.prize_type == '3' ? '' : 5}
                                                    name='value'
                                                    value={newrow.amount}
                                                    onChange={(e) => this.handlePrizeDis(e, CallType, idx, 'amount')}
                                                />
                                            }

                                            {/* {
                                                newrow.prize_type == 3 &&
                                                <div className="prize-type clearfix float-left">
                                                    <Select
                                                        disabled={editMode}
                                                        searchable={false}
                                                        clearable={false}
                                                        name="value"
                                                        placeholder="Merchandise"
                                                        className="pd-select"
                                                        value={newrow.amount}
                                                        options={MerchandiseList}
                                                        onChange={(e) => this.handlePrizeType(e, CallType, idx, 'amount')}
                                                    />
                                                </div>
                                            } */}

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
                            <div className="pt-btn-wdt">
                                <div
                                    className="add-prize-btn"
                                    onClick={() => editMode ? null : this.addPrizeRow(CallType)}>
                                    Add Prize
                                    <i className="icon-plus icon-style ml-2"></i>
                                </div>
                            </div>
                            <div className="pt-total-dis">
                                <div className="pt-tot-dtl pt-tot-sty">{HF.getCurrencyCode()}{TotalRealDistri}</div>
                                <div className="pt-total-prz">
                                    <div className="pt-tot-dtl">Total Prize</div>
                                    <div className="pt-tot-dtl pt-real">(only real cash)</div>
                                </div>
                            </div>
                        </div>
                    </div>)}
            </div>
        )
    }




    renderTieBreaker = (CallType) => {
        let { PrizeSetType, editMode, tieBreakerShow } = this.state
        return (
            <div className="fixed-set-prize show-grid">
                <div className="pt-setp-stype">
                    <div className="set-prizes-title">Tie Breaker
                        <i className="icon-info-border ml-2  cursor-pointer" id="tieBreaker">
                            <Tooltip placement="right" isOpen={tieBreakerShow} target="tieBreaker" toggle={this.toggleTieBreaker}>{PT_TIE_BREAKER}</Tooltip>
                        </i>

                    </div>
                    <div className="select-prize-op mb-0">
                        <div className="common-cus-checkbox">
                            <label className="com-chekbox-container">
                                <span className="opt-text">Yes</span>
                                <input
                                    disabled={editMode}
                                    type="checkbox"
                                    name="selectSetPrize"
                                    checked={this.state.checkedTie}
                                    // checked={PrizeSetType[CallType + '_SetPrize']}
                                    onChange={(e) => this.handleTieBreaker(e, CallType, 'SetPrize')}
                                />
                                <span className="com-chekbox-checkmark"></span>
                            </label>
                        </div>
                    </div>
                </div>
                {this.state.checkedTie && <Row className="mt-5">
                    <Col md={4}>
                        <div className="">
                            <label className="pt-label">Question</label>
                            <Input
                                type="textarea"
                                disabled={editMode}
                                minLength="4"
                                maxLength="150"
                                className="required tie-br-question"
                                id="question"
                                name="question"
                                // value={question}
                                onChange={(e) => this.handleFieldVal(e)}
                            />
                        </div>
                    </Col>
                    <Col md={4}>
                        <div className="">
                            <label className="pt-label">Range</label>
                            <div className="d-flex">
                                <Input
                                    //type="number"
                                    disabled={editMode}
                                    // maxLength="50"
                                    className="required mr-4"
                                    id="startRange"
                                    name="startRange"
                                    value={this.state.startRange}
                                    // value={question}
                                    placeholder="Start"
                                    onChange={(e) => this.handleFieldVal(e)}
                                />
                                <Input
                                    //type="text"
                                    disabled={editMode}
                                    maxLength="999999"
                                    className="required"
                                    id="endRange"
                                    name="endRange"
                                    value={this.state.endRange}
                                    placeholder="End"
                                    // value={question}
                                    onChange={(e) => this.handleFieldVal(e)}
                                />
                            </div>
                        </div>
                    </Col>
                </Row>}
            </div>
        )
    }

    renderperfectScore = (CallType) => {
        let { PrizeSetType1, editMode, checkedPerfect, perfectMoneyName, perfectScoreShowToggle, handlePerfectScored, PrizeTypeOpt, merchandiseObj, TotalRealDistri } = this.state
        return (
            <div className="fixed-set-prize show-grid">
                <div className="pt-setp-stype">
                    <div className="set-prizes-title">Perfect Score
                        <i className="ml-2 icon-info-border cursor-pointer" id='perfectScoreSection'>
                            <Tooltip
                                placement="right"
                                isOpen={perfectScoreShowToggle}
                                target='perfectScoreSection'
                                toggle={this.togglePerfectScore}>
                                {PERFECT_SCORE_INFO}
                            </Tooltip>
                        </i>
                    </div>
                    <div className="select-prize-op mb-0">
                        <div className="common-cus-checkbox">
                            <label className="com-chekbox-container">
                                <span className="opt-text">Yes</span>
                                <input
                                    disabled={editMode}
                                    type="checkbox"
                                    name="selectSetPrize"
                                    checked={handlePerfectScored}
                                    // defaultChecked={PrizeSetType[CallType + '_SetPrize']}
                                    // checked={PrizeSetType[CallType + '_SetPrize']}
                                    onChange={(e) => this.handleScore(e)}
                                />
                                <span className="com-chekbox-checkmark"></span>
                            </label>
                        </div>
                    </div>
                </div>



                <div >
                     {handlePerfectScored &&
                    <>
                        {
                            _.map(PrizeSetType1[CallType + '_newrow'], (newrow, idx) => {
                                return (
                                    <Row className="mt-5">
                                        <Col md={4}>
                                            <div className="">
                                                <label className="pt-label" >Prize Type</label>
                                                <Select
                                                    disabled={editMode}
                                                    className="pt-sel-league"
                                                    id="prize_type_perfect_score"
                                                    name="prize_type_perfect_score"
                                                    placeholder="Prize Type"
                                                    options={this.state.customOption}
                                                    onChange={(e) => this.handlePrizeType1(e, CallType, idx, 'prize_type')}
                                                    // onChange={(e) =>
                                                    //     this.setState({ perfectMoneyName: e.label == 'Real Money' ? 1 : e.label == 'Bonus' ? 0 : e.label == 'Coin' ? 2 : 'null' })
                                                    // }
                                                    value={newrow.prize_type}
                                                    // value={perfectMoneyName}
                                                />
                                            </div>
                                        </Col>
                                        <Col md="4">

                                            <div className="">
                                                <label className="pt-label" >Value</label>
                                                <Input
                                                    type="number"
                                                    disabled={editMode}
                                                    maxLength="70"
                                                    className="required"
                                                    // id="prize_type_perfect_amount"
                                                    // name="prize_type_perfect_amount"
                                                    id="amount"
                                                    name="amount"
                                                    // value={entryFee}
                                                    value={newrow.amount}
                                                    onChange={(e) => this.handlePrizeDis1(e, CallType, idx, 'amount')}
                                                    // onChange={(e) => this.setState({ perfectAmount: e.target.value })}
                                                />
                                            </div>
                                        </Col>
                                        {this.state.questionView && idx != 0 && <Col md="4">

                                            <div className="">
                                                <label className="pt-label" >Correct Pick</label>
                                                <Input
                                                    type="number"
                                                    disabled={editMode}
                                                    maxLength="70"
                                                    className="required"
                                                    id="correct"
                                                    name="correct"
                                                    value={newrow.correct}
                                                    onChange={(e) => this.handlePrizeDis1(e, CallType, idx, 'correct')}
                                                    // value={entryFee}
                                                    // onChange={(e) => this.setState({ perfectAmount: e.target.value })}
                                                />
                                            </div>
                                            {
                                                <div className="new-cancel-container">

                                                    {(!editMode && idx >= 0) && <i className="icon-cross icon-style" onClick={() => this.removePTRow(CallType, idx)}></i>
                                                    }

                                                </div>
                                            }
                                        </Col>}

                                    </Row>
                                )
                            })
                        }
                        <Row>
                            <Col md="4">
                                <div className="new-prize-dist-table prize-dist-table pt-btn-wdt">
                                    <div
                                        className="add-prize-btn"
                                        onClick={() => editMode ? null : this.addPrizePTRow(CallType)}>
                                        Add Prize
                                        <i className="icon-plus icon-style ml-2"></i>
                                    </div>
                                </div>
                            </Col>
                        </Row>
                    </>
    }
                </div>



                {/* {handlePerfectScored &&
                    <>

                        <Row className="mt-5">
                            <Col md={4}>
                                <div className="">
                                    <label className="pt-label" >Prize Type</label>
                                    <Select
                                        disabled={editMode}
                                        className="pt-sel-league"
                                        id="prize_type_perfect_score"
                                        name="prize_type_perfect_score"
                                        placeholder="Prize Type"
                                        options={this.state.customOption}
                                        onChange={(e) =>
                                            this.setState({ perfectMoneyName: e.label == 'Real Money' ? 1 : e.label == 'Bonus' ? 0 : e.label == 'Coin' ? 2 : 'null' })
                                        }
                                        value={perfectMoneyName}
                                    />
                                </div>
                            </Col>
                            <Col md="4">

                                <div className="">
                                    <label className="pt-label" >Value</label>
                                    <Input
                                        type="number"
                                        disabled={editMode}
                                        maxLength="70"
                                        className="required"
                                        id="prize_type_perfect_amount"
                                        name="prize_type_perfect_amount"
                                        // value={entryFee}
                                        onChange={(e) => this.setState({ perfectAmount: e.target.value })}
                                    />
                                </div>
                            </Col>
                        </Row>
                        
                    </>
                } */}


            </div>
        )
    }



    handleSetPrize = (e, CallType, CallFrom) => {
        let { PrizeSetType } = this.state
        if (e) {
            let name = e.target.name;

            if (CallFrom == 'TieBreaker') {
                PrizeSetType['TieBreaker'] = (PrizeSetType['TieBreaker']) ? false : true
            } else {
                PrizeSetType[CallType + '_SetPrize'] = (name == 'selectSetPrize' && PrizeSetType[CallType + '_SetPrize']) ? false : true
                if (!PrizeSetType[CallType + '_SetPrize']) {
                    PrizeSetType[CallType + '_newrow'] = []
                    PrizeSetType['TieBreaker'] = false
                } else {
                    PrizeSetType[CallType + '_newrow'] = [{ min: "1", max: "1", prize_type: "1", amount: "0" }]
                }
            }

            this.setState({
                PrizeSetType: PrizeSetType
            })
        }
    }

    handleTieBreaker = (e, CallType, CallFrom) => {
        this.setState({
            checkedTie: !this.state.checkedTie
        })
        let { PrizeSetType } = this.state
        if (e) {
            let name = e.target.name;

            if (CallFrom == 'TieBreaker') {
                PrizeSetType['TieBreaker'] = (PrizeSetType['TieBreaker']) ? false : true
            } else {
                PrizeSetType[CallType + '_SetPrize'] = (name == 'selectSetPrize' && PrizeSetType[CallType + '_SetPrize']) ? false : true
                if (!PrizeSetType[CallType + '_SetPrize']) {
                    PrizeSetType[CallType + '_newrow'] = []
                    PrizeSetType['TieBreaker'] = false
                } else {
                    PrizeSetType[CallType + '_newrow'] = [{ min: "1", max: "1", prize_type: "1", amount: "0" }]
                }
            }

            this.setState({
                PrizeSetType: PrizeSetType
            })
        }
    }

    handleAutomatchAddition = (e) => {

        let { checkedMatch } = this.state
        console.log(checkedMatch,'before')
        this.setState({
            checkedMatch: !this.state.checkedMatch
        })
        // console.log(checkedMatch, 'after')
        // let { PrizeSetType } = this.state
        // if (e) {
        //     let name = e.target.name;

        //     if (CallFrom == 'TieBreaker') {
        //         PrizeSetType['TieBreaker'] = (PrizeSetType['TieBreaker']) ? false : true
        //     } else {
        //         PrizeSetType[CallType + '_SetPrize'] = (name == 'selectSetPrize' && PrizeSetType[CallType + '_SetPrize']) ? false : true
        //         if (!PrizeSetType[CallType + '_SetPrize']) {
        //             PrizeSetType[CallType + '_newrow'] = []
        //             PrizeSetType['TieBreaker'] = false
        //         } else {
        //             PrizeSetType[CallType + '_newrow'] = [{ min: "1", max: "1", prize_type: "1", amount: "0" }]
        //         }
        //     }

        //     this.setState({
        //         PrizeSetType: PrizeSetType
        //     })
        // }
    }




    addPrizeRow = (CallType) => {

        let { PrizeSetType } = this.state
        let newrow_arr = PrizeSetType[CallType + '_newrow'] != null ? PrizeSetType[CallType + '_newrow'] : []
        let minString = !_.isEmpty(newrow_arr) ? (parseInt(newrow_arr[newrow_arr.length - 1].max) + parseInt(1)).toString() : '1'
        let newrow = {
            'min': minString ? minString : '1',
            'max': !isNaN(minString) ? minString : "",
            'prize_type': "1",
            'amount': "0",
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
        this.setState({
            setPrizeTypeValue: e.value
        })
        let { PrizeSetType } = this.state
        if (e) {
            PrizeSetType[CallType + '_newrow'][indx][key] = e.value
            if (key === 'prize_type') {
                PrizeSetType[CallType + '_newrow'][indx]['amount'] = ''
            }
            this.setState({ PrizeSetType: PrizeSetType })
        }
    }
 
    handlePrizeType1 = (e, CallType, indx, key) => {
        this.setState({
              perfectMoneyName: e.label == 'Real Money' ? 1 : e.label == 'Bonus' ? 0 : e.label == 'Coin' ? 2 : 'null'
        })
        let { PrizeSetType1 } = this.state
        if (e) {
            PrizeSetType1[CallType + '_newrow'][indx][key] =  e.value == 1 ? 1 : e.value == 0 ? 0 : e.value == 2 ? 2 : 'null'
            if (key === 'prize_type') {
                PrizeSetType1[CallType + '_newrow'][indx]['amount'] = ''
            }
            this.setState({ PrizeSetType1: PrizeSetType1 })
        }
    }

    getTotRealDistribution = (pay_list) => {
        let TotReal = 0
        
        if(pay_list.Daily_newrow){
            if (!_isEmpty(pay_list) && !_isUndefined(!_isEmpty(pay_list.Daily_newrow))) {
                 pay_list.Daily_newrow.map((pData) => {
                    if (pData.prize_type == '1' && !_isEmpty(pData.amount) && !_isEmpty(pData.max) && !_isEmpty(pData.min)) {
                        TotReal += !_.isUndefined(pData.amount) ? (parseInt(pData.amount) * ((parseInt(pData.max) - parseInt(pData.min)) + 1)) : 0
                    }
                });
                this.setState({ TotalRealDistri: TotReal })
            }
        }else{
            if (!_isEmpty(pay_list) &&  !_isUndefined(!_isEmpty(pay_list.Daily1_newrow))) {
                pay_list.Daily1_newrow.map((pData) => {
                    if (pData.prize_type == '1' && !_isEmpty(pData.amount)  && !_isEmpty(pData.correct)) {
                        TotReal += !_.isUndefined(pData.amount) ? (parseInt(pData.amount)) : 0
                    }
                });
                this.setState({ TotalRealDistri: TotReal })
            }
        }
        // if (!_isEmpty(pay_list) && !_isUndefined(!_isEmpty(pay_list.Daily_newrow)) && !_isUndefined(!_isEmpty(pay_list.Daily1_newrow))) {
        //     pay_list.Daily1_newrow || pay_list.Daily_newrow.map((pData) => {
        //         if (pData.prize_type == '1' && !_isEmpty(pData.amount) && !_isEmpty(pData.max) && !_isEmpty(pData.min)) {
        //             TotReal += !_.isUndefined(pData.amount) ? (parseInt(pData.amount) * ((parseInt(pData.max) - parseInt(pData.min)) + 1)) : 0
        //         }
        //     });
        //     this.setState({ TotalRealDistri: TotReal })
        // }
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

        if (key === 'max') {
            PrizeSetType[CallType + '_newrow'][indx]['amount'] = '0'
        }

        if (totalRow > (indx + 1) && name === 'max') {
            PrizeSetType[CallType + '_newrow'][indx + 1]['min'] = (parseInt(value) + parseInt(1)).toString()
            for (var i = indx + 1; i < totalRow; i++) {
                if (i != indx + 1) { PrizeSetType[CallType + '_newrow'][i]['min'] = '' }
                PrizeSetType[CallType + '_newrow'][i]['max'] = ''
                PrizeSetType[CallType + '_newrow'][i]['amount'] = '0'
            }
        }
        this.setState({ PrizeSetType: PrizeSetType }, () => {
            // if (key === 'amount') {
            this.getTotRealDistribution(this.state.PrizeSetType)
            // }
        })
    }
    handlePrizeDis1 = (e, CallType, indx, key) => {
        
        let { PrizeSetType1 } = this.state;
        let name = e.target.name;
        let value = e.target.value;
    
        var totalRow = PrizeSetType1[CallType + '_newrow'].length;
        if(name == 'correct' && indx != 1){
            let ttt = parseInt(PrizeSetType1[CallType + '_newrow'][indx - 1][key])
            if(parseInt(value) >= ttt || parseInt(value) == 0){
                if(parseInt(value) == 0){
                    notify.show("Value must be greater than 0", "error", 3000);
                }
                else{
                    notify.show("Value must be less than the previous jackpot question value", "error", 3000);
                }
                // PrizeSetType1[CallType + '_newrow'][indx][key] = value.toString()
            }
            else{
                PrizeSetType1[CallType + '_newrow'][indx][key] = value.toString()
            }
        }
        else{
            if(name == 'correct'){
                if(value == '' || parseInt(value) > 0 && 999 >= parseInt(value)){
                    PrizeSetType1[CallType + '_newrow'][indx][key] = value.toString()
                }
                else{
                    notify.show("Enter value between 1 to 999", "error", 3000);
                }
            }
            else{
                if(value == '' || parseInt(value) > 0 && 999999 >= parseInt(value)){
                    PrizeSetType1[CallType + '_newrow'][indx][key] = value.toString()
                }
                else{
                    notify.show("Enter value between 1 to 999999", "error", 3000);
                }
                // PrizeSetType1[CallType + '_newrow'][indx][key] = value.toString()
            }
        }

        if (name == 'correct' && totalRow > (indx + 1)) {
            for (var i = indx + 1; i < totalRow; i++) {
                if (i != indx + 1) 
                {
            }
                // PrizeSetType1[CallType + '_newrow'][i]['amount'] = ''
                PrizeSetType1[CallType + '_newrow'][i]['correct'] = ''
            }
        }
        this.setState({ PrizeSetType1: PrizeSetType1}, () => {
            this.getTotRealDistribution(this.state.PrizeSetType1)
            
        })
    }
    

    storeSeasonIds = () => {
        let season_id = [];
        _.map(this.state.fixtureList, (item, index) => {
            if (item.is_selected) {
                season_id.push(item.season_id)
            }

        })
        this.setState({
            storeSeasonId: season_id
        })
    }

    selectCurrenvyType = (e) => {
        this.setState({
            setCurrencyvalue: e.value
        })
    }

    addPrizePTRow = (CallType) => {
        let { PrizeSetType1 } = this.state
        let newrow_arr = PrizeSetType1[CallType + '_newrow'] != null ? PrizeSetType1[CallType + '_newrow'] : []
        let newrow = {
            'prize_type': "1",
            'amount': "",
            'correct' :''
        }

        newrow_arr.push(newrow)
        PrizeSetType1[CallType + '_newrow'] = (newrow_arr)
        this.setState({
            PrizeSetType1: PrizeSetType1,
            questionView: true
        });
    }

    removePTRow = (CallType, idx) => {
        let { PrizeSetType1, PrizeSetType } = this.state
        PrizeSetType1[CallType + '_newrow'].splice(idx, 1)
        this.setState({
            PrizeSetType1: PrizeSetType1
        }, () => {
            this.getTotRealDistribution(this.state.PrizeSetType1)
        });
    }

    handleDates = (date, dateType) => {
        this.validateDate(date)
        this.setState({
            Validedate: this.validateDate(date)
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
    validateDate = (date) => {
        let TodayDate = new Date()
        TodayDate.setMinutes(TodayDate.getMinutes() + 5);
        if (date <= TodayDate) {
            notify.show('Please select time more than 5 min from current time', "error", 5000)
            return false;
        }
        else return true
    } 

    render() {
        let { EndDate , leagueList, selectAll, setCurrencyvalue, currency_type_option, tournamentName, fixtureList, FromDate, TotalRealDistri, ToDate, UploadLogoName, uplogottShow, filteredList, sponsorttShow, SetSponsor, TodayDate, selected_league, editMode, subPosting, BannerArr, fixtureFilterSelected, fixtureFilter, storeSeasonId } = this.state
        const sameDateProp = {
            disabled_date: editMode,
            show_time_select: true,
            time_format: "HH:mm",
            time_intervals: 10,
            time_caption: "time",
            date_format: 'dd/MM/yyyy h:mm aa',
            handleCallbackFn: this.handleDate,
            class_name: 'pt-datep',
            year_dropdown: true,
            month_dropdown: true,
        }
        const FromDateProps = {
            ...sameDateProp,
            min_date: new Date(TodayDate),
            max_date: null,
            sel_date: FromDate ? new Date(FromDate) : null,
            date_key: 'FromDate',
            place_holder: 'From Date',
        }
        const ToDateProps = {
            ...sameDateProp,
            min_date: new Date(FromDate),
            max_date: null,
            sel_date: ToDate ? new Date(ToDate) : null,
            date_key: 'ToDate',
            place_holder: 'To Date',
        }

        const sameDateProps = {
            show_time_select: true,
            time_format: "HH:mm",
            time_intervals: 5,
            time_caption: "time",
            date_format: 'dd/MM/yyyy h:mm aa',
            handleCallbackFn: this.handleDates,
            class_name: 'Select-control inPut icon-calender',
            year_dropdown: true,
            month_dropdown: true,
            className: ''
        }

        const DateProps = {
            ...sameDateProps,
            min_date: new Date(TodayDate),
            max_date: null,
            sel_date: EndDate,
            date_key: 'EndDate',
            place_holder: 'Select Date',
            // className: 'icon-calender Ccalender'
        }

        return (
            <div className="pt-create-tournament">
                <Row>
                    <Col md={12}>
                        <h2 className="h2-cls float-left">
                            {editMode ? 'Update ' : 'Create '} Tournament
                        </h2>
                        {/* <div
                            onClick={() => {
                                this.props.history.goBack()
                            }}
                            className="float-right back-to-fixtures mt-1">
                            <img src={Images.RIGHTARROW} alt="" />
                            {'< '}Back
                        </div> */}
                    </Col>
                </Row>
                <div className="pt-white-box">
                    <Row>
                        <Col md={4}>
                            <div className="">
                                <label className="pt-label" >League</label>
                                <Select
                                    disabled={editMode}
                                    className="pt-sel-league"
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
                                <label className="pt-label" >Enter Tournament Name</label>
                                <Input
                                    disabled={editMode}
                                    maxLength="70"
                                    className="required"
                                    id="tournamentName"
                                    name="tournamentName"
                                    value={tournamentName}
                                    onChange={(e) => this.handleFieldVal(e)}
                                />
                            </div>
                        </Col>
                        <Col md={4}>
                            <div className="">
                                <label className="pt-label" >Entry Fee</label>
                                <InputGroup className="mb-3">
                                    {/* <InputGroup.Text id="basic-addon1" className="currency"><i className="icon-rupess" /></InputGroup.Text> */}
                                    {/* <select className="select-pickem-currency" onChange={(e) => this.selectCurrenvyType(e)}>
                                        <option>{currency_type_option}</option>
                                    </select> */}
                                    <Select
                                        disabled={editMode}
                                        searchable={false}
                                        clearable={false}
                                        placeholder="Type"
                                        className="select-pickem-currency"
                                        options={currency_type_option}
                                        value={setCurrencyvalue}
                                        onChange={(e) => this.selectCurrenvyType(e)}
                                    />
                                    <Input
                                        type="number"
                                        disabled={editMode}
                                        className="required"
                                        id="entryFee"
                                        name="entryFee"
                                        onChange={(e) => this.handleFieldVal(e)}
                                    />
                                </InputGroup>
                                <p className="free-contest">Enter 0 to create a free contest</p>
                            </div>
                        </Col>
                        {setCurrencyvalue == 1 &&
                            <Col md={4} className="mt-30">
                                {/* <div className="float-left d-inline-flex">
                                <div className="float-left">
                                    <label className="pt-label">Start Date</label>
                                    <SelectDate DateProps={FromDateProps} />
                                </div>
                                <div className="float-left">
                                    <label className="pt-label">End Date</label>
                                    <SelectDate DateProps={ToDateProps} />
                                </div>
                            </div> */}

                                <div className="">
                                    <label className="pt-label" >Bonus Allowed (in %)
                                        <i className="ml-2 icon-info-border cursor-pointer" id='uplogott'>
                                            <Tooltip
                                                placement="right"
                                                isOpen={uplogottShow}
                                                target='uplogott'
                                                toggle={this.toggle}>
                                                {CONTEST_BONUS}
                                            </Tooltip>
                                        </i>
                                    </label>
                                    <Input
                                        // disabled={editMode}
                                        className="required"
                                        id="bonusAllowed"
                                        name="bonusAllowed"
                                        value={this.state.bonusAllowed}
                                        onChange={(e) => this.handleFieldVal(e)}
                                        disabled={this.state.entryFee == 0}
                                        placeholder="0"
                                    />
                                </div>
                            </Col>
                        }
                        <Col md={4} className="mt-30">
                            <div className="mt-0">
                                <label className="pt-label uplogo-btn">
                                    Upload Logo (Optional)
                                </label>
                                <div className="pt-image-box">
                                    {!_.isEmpty(UploadLogoName) ?
                                        <Fragment>
                                            <img className="" src={UploadLogoName} width="100" height="100" />
                                            <div
                                                onClick={this.removeFile}
                                                className="pt-remove-img text-left">
                                                Remove image
                                            </div>
                                        </Fragment>
                                        :
                                        <Fragment>
                                            <Input
                                                type="file"
                                                name='UploadLogoName'
                                                id="UploadLogoName"
                                                className="pt-up-btn"
                                                onChange={this.onChangeImage}
                                            />
                                            <div className="pt-upload">
                                                Upload Image
                                                <div className="pt-banner-sz">
                                                    Size 470 * 200
                                                </div>
                                            </div>
                                        </Fragment>
                                    }
                                </div>
                            </div>
                        </Col>
                        <Col sm={4} className="mt-30">

                            <div className='addFixturesOptionsItem'>
                                <div className='inputFields inPutBg'>

                                    <label className="pt-label" htmlFor="CandleDetails">Tournament Ends On</label>
                                    <>
                                        <SelectDate DateProps={DateProps} />
                                        <i className='icon-calender Ccalender trntcalender'></i>
                                    </>


                                </div>
                            </div>

                        </Col>  
                    </Row>
                    <Row>

                        <Col sm={4} className="mt-30">

                            <div className='addFixturesOptionsItem'>
                                <div className='inputFields inPutBg'>
                                    <label className="pt-label" htmlFor="CandleDetails">Automatic Match Addition</label>
                                    <>
                                        <div className="select-prize-op mb-0">
                                                <div className="common-cus-checkbox">
                                                    <label className="com-chekbox-container">
                                                        <span className="opt-text pickem-lble">Yes</span>
                                                        <input
                                                            // disabled={editMode}
                                                            type="checkbox"
                                                        name="auto_match_publish"
                                                        checked={this.state.checkedMatch}
                                                            // checked={PrizeSetType[CallType + '_SetPrize']}
                                                            onChange={(e) => this.handleAutomatchAddition(e)}
                                                        />
                                                        <span className="com-chekbox-checkmark"></span>
                                                    </label>
                                                </div>
                                        </div>
                                    </>
                                </div>
                            </div>

                        </Col>  
                    </Row>
                    {/* {this.getSelectedLeague().length != 0 ?
                        <label className="fixtudefre-label">ect Pick'em
                            Select Pick'em
                            <span className="pt-sel-fx">
                                {' (' + this.getSelectedLeague().length + '/' + TotalFixture + " Pick'em selected)"}
                            </span>
                        </label> : */}
                    <label className="fixture-label mt-4 pt-4">Select Pick'em</label>
                    {/* } */}
                    <div className="pt-fixture-view">
                        <div className="fixture-view-header">
                            <div className="select-all-parent">
                                <input type="checkbox"
                                    defaultChecked={selectAll}
                                    checked={selectAll}
                                    onChange={(e) => this.handleChkVal(e)}
                                />
                                <label className="pt-select-all">Select All</label>
                            </div>
                            <div className="right-item">
                                <Select
                                    className="pt-filter-dopdown"
                                    id="selected_league"
                                    name="selected_league"
                                    placeholder="All"
                                    value={fixtureFilterSelected}
                                    options={fixtureFilter}
                                    onChange={(e) => this.handleFilter(e, 'fixtureFilter')}
                                />
                                <FormGroup className="float-right">
                                    <InputGroup className="pt-search-wrapper">
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
                        {/* <div className="line" /> */}
                        <div>
                            <Row>{
                                _Map(this.state.fixtureList, (item, idx) => {
                                    return (
                                        <Col md={3} key={idx}>
                                            <div className="view-picks pt-detail">
                                                <div
                                                    className={`cricket-fixture-card ${item.allow_draw == "1" ? "pm-soccer-card" : ''}`}
                                                    onClick={() => {
                                                        item.is_selected = !item.is_selected;
                                                        let isAllSelected = this.isAllSelected();
                                                        this.setState({ selectAll: isAllSelected }, () => {
                                                            this.setState({ fixtureList: this.state.fixtureList })
                                                        })
                                                        // let storeSeasonId = this.storeSeasonIds(item.season_id, item.is_selected);
                                                    }}
                                                >
                                                    <div className="live-comp-date pt-c-gray">
                                                        {/* <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM - hh:mm A" }} /> */}
                                                        <>
                                                            {HF.getFormatedDateTime(item.scheduled_date, "D MMM - hh:mm A")}
                                                        </>
                                                        {item.is_selected &&
                                                            <div className="right-selection pickem-selection">
                                                                <img src={Images.tick} className="rght-img" />
                                                            </div>
                                                        }
                                                    </div>
                                                    <div className="bg-container">
                                                        <div className="card-set clearfix">
                                                            <div className="home-left">
                                                                <div className="home-left-cont">
                                                                    <img className="team-img"
                                                                        src={item.home_flag ? NC.S3 + NC.FLAG + item.home_flag : Images.no_image}
                                                                    />
                                                                    <div className="pick-option">{item.home}</div>
                                                                </div>
                                                            </div>

                                                            {
                                                                item.allow_draw == "1" &&
                                                                <div className="home-left">
                                                                    <div className="home-left-cont">
                                                                        <img className="team-img"
                                                                            src={Images.DRAW_IMG}
                                                                        />
                                                                        <div className="pick-option">DRAW</div>
                                                                    </div>
                                                                </div>
                                                            }

                                                            <div className="home-right">
                                                                <div className="home-right-cont">
                                                                    <img
                                                                        className="team-img"
                                                                        src={item.away_flag ? NC.S3 + NC.FLAG + item.away_flag : Images.no_image}
                                                                    />
                                                                    <div className="pick-option">{item.away}</div>
                                                                </div>
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


                </div>
                <div className="pt-white-box">
                    <Row>
                        <Col md="12">
                            {this.renderDistributionTable('Daily')}
                        </Col>
                    </Row>
                </div>
                <div className="pt-white-box">
                    <Row>
                        <Col md="12">
                            {this.renderTieBreaker()}
                        </Col>
                    </Row>
                </div>
                <div className="pt-white-box">
                    {/* <Row>
                        <Col md="12"> */}
                    {this.renderperfectScore('Daily1')}
                    {/* </Col>
                    </Row> */}
                </div>
                <div className="pt-sponsor-box pt-white-box">
                    <Row>
                        <Col md={3}>
                            <div className="">
                                <label className="pt-label">
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
                                        <span className="opt-text">Yes</span>
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
                        {/* {
                            (SetSponsor == '1') &&
                            <Col md={9}>
                                <div className="pt-uplogo-box">
                                    <div className="pt-banner-box">
                                        <Fragment>
                                            <div className="pt-banner-sz clearfix">
                                                <div className="pt-btn-txt">
                                                    <div className="pt-upload">
                                                        <Input
                                                            type="file"
                                                            name='UploadBanner'
                                                            id="UploadBanner"
                                                            className="pt-up-btn"
                                                            onChange={this.onUploadBanner}
                                                        />
                                                        Upload Banner
                                                    </div>
                                                    <div className="pt-banner-sz">

                                                    </div>
                                                </div>
                                                <div className="pt-banner-text">
                                                    You can add upto 5 banner
                                                </div>
                                            </div>
                                        </Fragment>
                                    </div>
                                </div>
                            </Col>
                        } */}


                        {
                            (SetSponsor == '1') &&
                            <Col md={9}>
                                <div className="dfs-uplogo-box">
                                    <div className="dfs-banner-box">
                                        <Fragment>
                                            <div className="dfs-banner-sz clearfix">
                                                <div className="dfs-btn-txt d-flex">
                                                    <div className="dfs-upload">
                                                        {
                                                            !this.state.UpLogoPosting &&
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
                                                    <div className="dfs-banner-text">
                                                        You can add upto 5 banner
                                                    </div>

                                                </div>
                                                <div className="dfs-banner-sz">
                                                    Size 1300 X 240
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
                                            <div className="pt-banner-img">
                                                <i
                                                    onClick={() => this.removeBanner(item)}
                                                    className="icon-close"></i>
                                                <img src={item} className="img-cover" />
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
                        <div className="pt-btn-sub">
                            <Button
                                disabled={this.state.apiCall == false}
                                onClick={
                                    () =>
                                        // editMode ? this.updateTournament() : 
                                        this.CreateTournament()}
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
        let season_game_uid = [];
        _.map(this.state.fixtureList, (item, index) => {
            if (item.is_selected)
                season_game_uid.push(item.season_game_uid);
        })
        return season_game_uid;
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
export default PTCreateTournament;
