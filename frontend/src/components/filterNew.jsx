import React, { lazy, Suspense } from 'react';
import { FormGroup, Button, Checkbox, Tabs, Tab } from 'react-bootstrap';
import WSManager from '../WSHelper/WSManager';
import Modal from 'react-modal';
import {getFilterData,getStockFilterData} from "../WSHelper/WSCallings";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { _isEmpty,Utilities } from "../Utilities/Utilities";
import { MyContext } from './../InitialSetup/MyProvider';
import { AppSelectedSport } from '../helper/Constants';
import CustomHeader from "../components/CustomHeader";
import { Sports } from "../JsonFiles";
import * as Constants from "../helper/Constants";
const RangeSlider = lazy(()=>import('../Component/CustomComponent/RangeSlider'));
const ReactSlidingPane = lazy(()=>import('../Component/CustomComponent/ReactSlidingPane'));
var globalThis = null;
var filterData = {};
export default class FilterNew extends React.Component {

    constructor(props) {
        super(props);
        this.handleSelect = this.handleSelect.bind(this);
        this.state = {
            isPaneOpen: false,
            isPaneOpenLeft: false,
            isPaneOpenBottom: true,
            checkbox: false,
            isFilterselected: this.props.isFilterselected || false,
            key: 1,
            leagueList: this.props.leagueList ? this.props.leagueList : [],
            f_league_id: [],
            dataFor: this.props.filterDataBy || '',
            MPCat: this.props.filterByCat || '',
            selectedFSport: this.props.selectedFSport || '',
            pickemSFilter: this.props.FitlerOptions.showPickLFitlers ? this.props.selectedFilter.league_id : '',
            pickemLFT: this.props.FitlerOptions.showPickLFitlers ? this.props.selectedFilter.feed_type : '',
            refresh: true,
            contestListFilterObj: {
                entryFee: { master_min: 0, master_max: 100, min: 0, max: 100 },
                winnings: { master_min: 0, master_max: 100, min: 0, max: 100 },
                entries: { master_min: 0, master_max: 100, min: 0, max: 100 }
            },
            contestListEntryFee: [
                {
                    status: 1,
                    entry_fee_from : 0, 
                    entry_fee_to : 50
                },
                {
                    status: 2,
                    entry_fee_from : 51, 
                    entry_fee_to : 100
                },
                {
                    status: 3,
                    entry_fee_from : 101, 
                    entry_fee_to : 1001
                },
                {
                    status: 4,
                    entry_fee_from : 1001, 
                    entry_fee_to : 'Above'
                },
            ],
            contestListprizepool: [
                {
                    status: 1,
                    prizepool_from : 0, 
                    prizepool_to : 50
                },
                {
                    status: 2,
                    prizepool_from : 51, 
                    prizepool_to : 100
                },
                {
                    status: 3,
                    prizepool_from : 101, 
                    prizepool_to : 1001
                },
                {
                    status: 4,
                    prizepool_from : 1001, 
                    prizepool_to :'Above'
                },
            ],
            
            contestListSlotsSize: [
                {
                    status: 1,
                    participants_from: 0,
                    participants_to : 2
                },
                {
                    status: 2,
                    participants_from: 3,
                    participants_to : 10
                },
                {
                    status: 3,
                    participants_from: 11,
                    participants_to : 100
                },
                {
                    status: 4,
                    participants_from: 101,
                    participants_to : 1000
                },
                {
                    status: 5,
                    participants_from: 1001,
                    participants_to : 'Above'
                },
            ],
            selectedCLFilter :  {
                entry_fee_from: 0,
                entry_fee_to: 'Above',
                participants_from: 0,
                participants_to:'Above',
                prizepool_from: 0,
                prizepool_to:'Above',
                isApplied: false
            },
            CLEntryFee: '',
            CLPrizePool: '',
            CLSlotsSize: '',
            CLEntryFeeValue: '',
            CLPrizePoolValue: '',
            CLSlotsSizeValue: '',
            CLValue : false,

        };
    }

    handleSelect(key) {
        this.setState({ key });
    }

    handleInputChange = (e) => {
        this.setState({
            [e.target.name]: !JSON.parse(e.target.value)
        });
    }

    UNSAFE_componentWillReceiveProps(nextProps){
        if(nextProps.leagueList != undefined && nextProps.leagueList != this.state.f_league_id){
            this.setState({f_league_id: nextProps.leagueList})
        }     
        if(nextProps.customLeagues != this.props.customLeagues){
            this.parseFilterResponse({
                league_list : nextProps.customLeagues || []
            });
        }               
        if(this.state.dataFor != this.props.filterDataBy){
            this.setState({
                dataFor: this.props.filterDataBy,
            });
        }               
        if(this.state.MPCat != this.props.filterByCat){
            this.setState({
                MPCat: this.props.filterByCat,
            });
        }               
        if(this.state.selectedSport != this.props.selectedSport){
            this.setState({
                selectedSport: this.props.selectedSport,
            });
        }               
        if(nextProps.FitlerOptions.showPickLFitlers && this.state.pickemSFilter != nextProps.selectedFilter.league_id){
            this.setState({
                pickemSFilter: nextProps.selectedFilter.league_id,
            });
        }               
        if(nextProps.FitlerOptions.entry_fee_max || nextProps.FitlerOptions.prizepool_max || nextProps.FitlerOptions.participants_max){
            let filteropt = nextProps.FitlerOptions
            const {contestListFilterObj}=this.state
            let tmp = {
                entryFee: { 
                    master_min: filteropt.entry_fee_min, 
                    master_max: filteropt.entry_fee_max, 
                    min: contestListFilterObj.entryFee.min, 
                    max: filteropt.entry_fee_to //contestListFilterObj.entryFee.max
                 },
                winnings: { 
                    master_min: filteropt.prizepool_min, 
                    master_max: filteropt.prizepool_max, 
                    min: contestListFilterObj.winnings.min, 
                    max: filteropt.prizepool_to //contestListFilterObj.winnings.max
                },
                entries: { 
                    master_min: filteropt.participants_min, 
                    master_max: filteropt.participants_max, 
                    min: contestListFilterObj.entries.min,
                    max: filteropt.participants_to //contestListFilterObj.entries.max
                }
            }
            let tabCount = this.tabcount(filteropt)
            this.setState({
                contestListFilterObj: tmp,
                key: tmp.winnings.master_max > 0 ? 1 : (tmp.entryFee.master_max > 0 ? 2 : 3),
                tabCount: tabCount
            });
        }    
        if (nextProps.FitlerOptions && Constants.SELECTED_GAMET == Constants.GameType.DFS) {
            // console.log('nextProps.FitlerOptions',nextProps.FitlerOptions)
            this.setState({
                selectedCLFilter: nextProps.FitlerOptions,
            });
        }           
    }

    tabcount=(data)=>{
        let count = 0;
        if(data.entry_fee_max > 0){
            count = count + 1
        }
        if(data.prizepool_max > 0){
            count = count + 1
        }
        if(data.participants_max > 0){
            count = count + 1
        }
        return count
    }

    componentWillUnmount() {
        this.setState = () => {
            return;
        };
    }

    componentDidMount() {
        this.setState({
            isFilterselected:this.props.isFilterselected  || false
        })
        Modal.setAppElement(this.el);
        globalThis = this;
        let path = window.location.pathname;
        // if ((window.location.pathname == '/lobby' && Constants.SELECTED_GAMET != Constants.GameType.Pickem)){ //|| path.indexOf('/contest-listing')>0) {
        //     this.getFilterMasterData();
        // }
    }

    static reloadLobbyFilter() {
        // if(globalThis && globalThis.getFilterMasterData){
        //     globalThis.getFilterMasterData();
        // }
    }

    getFilterMasterData = async () => {
        if(this.props.stock){
            let param = {
                "collection_id": this.props.stock_collection
            }
            let responseJson = await getStockFilterData(param);
            if (responseJson.response_code === WSC.successCode) {
                this.parseFilterResponse(responseJson.data);
            }
        }else if(this.props.customLeagues){
            this.parseFilterResponse({
                league_list : this.props.customLeagues || []
            });
        }else{
        let selectedSports = AppSelectedSport;
        if (filterData[selectedSports]) {
            this.parseFilterResponse(filterData[selectedSports]);
        } else {
            if (selectedSports) {
                let param = {
                    "sports_id": selectedSports
                }
                if(this.props.isSecIn){
                    param['is_2nd_inning'] = 1
                }
                var api_response_data = await getFilterData(param);
                 if (api_response_data) {
                    filterData[selectedSports] = api_response_data;
                    this.parseFilterResponse(api_response_data);
                }
            }
        }
    }
    }

    parseFilterResponse(resp) {
        let previousObj = this.state.contestListFilterObj;
        if(this.props.customLeagues && this.props.customLeagues.length>0){
            this.setState({ leagueList: this.props.customLeagues });
        } else{
            this.setState({ leagueList: {} });
        }
        if (resp.league_list && resp.league_list.length > 0) {
            this.setState({ leagueList: resp.league_list })
        }

        if (resp.winning && typeof resp.winning != 'undefined') {
            previousObj.winnings.master_min = resp.winning.min;
            previousObj.winnings.min = resp.winning.min;
            previousObj.winnings.master_max = resp.winning.max;
            previousObj.winnings.max = resp.winning.max;
        }

        if (resp.entry_fee && typeof resp.entry_fee != 'undefined') {
            previousObj.entryFee.master_min = resp.entry_fee.min;
            previousObj.entryFee.min = resp.entry_fee.min;
            previousObj.entryFee.master_max = resp.entry_fee.max;
            previousObj.entryFee.max = resp.entry_fee.max;
        }

        if (resp.entries && typeof resp.entries != 'undefined') {
            previousObj.entries.master_min = resp.entries.min;
            previousObj.entries.min = resp.entries.min;
            previousObj.entries.master_max = resp.entries.max;
            previousObj.entries.max = resp.entries.max;
        }
        this.setState({
            refresh: false
        },()=>{
            this.setState({
                refresh: true
            })
        })
    }

   

    handleLobbyFilter = () => {
        if(this.props.FitlerOptions.showLFitler || this.props.FitlerOptions.showMPFitler || this.props.FitlerOptions.showPickLFitlers || this.props.FitlerOptions.showHubFitlers){
            this.setState({ isPaneOpenBottom: true });
        }else{
            this.setState({ isPaneOpenBottom: false });
        }
       this.props.hideFilter();
      
    }

    LobbyFilterSelect = () => {
        this.setState({ 
            isPaneOpenBottom: true,
            isFilterselected: true 
        }, function () {            
            let FilterObj = { league_id: this.state.f_league_id };
            this.props.filterLobbyResults(FilterObj);
            CustomHeader.changeFilter( (!_isEmpty(FilterObj.league_id) && typeof FilterObj.league_id != 'undefined') ? true : false,'');            
        });
    }
    PickemLobbyFilterSelect = () => {
        this.setState({ 
            isPaneOpenBottom: true,
            isFilterselected: true 
        }, function () {
            let filterBy = {
                league_id : this.state.pickemSFilter,
                // feed_type: this.state.pickemLFT
            };
            this.props.filterByLeague(filterBy)     
            CustomHeader.changeFilter(filterBy == '' ? false : true,'');            
        });
    }

    LeaderboardFilterSelect=()=>{
        this.setState({
            isFilterselected: this.state.dataFor ? true : false,
            isPaneOpenBottom:true
        },()=>{
            let filterBy = this.state.dataFor;
            let showName = Constants.SELECTED_GAMET === Constants.GameType.Pickem ? filterBy.league_name : filterBy.name;
            this.props.filterLeaderboard(filterBy);
            CustomHeader.changeFilter(filterBy != '' ? true : false, showName);
        })
    }
    MPFilterSelect=()=>{
        this.setState({
            isPaneOpenBottom:true,
            isFilterselected: true
        },()=>{
            let filterBy = this.state.MPCat;
            this.props.MPFilterSelect(filterBy);
            CustomHeader.changeFilter(true,filterBy.category_name);
        })
    }
    SportsFilterSelect=()=>{
        this.setState({
            isPaneOpenBottom:true,
            isFilterselected: this.state.selectedFSport ? true : false
        },()=>{
            let filterBy = this.state.selectedFSport;
            this.props.filterBySport(filterBy);
            CustomHeader.changeFilter(filterBy == '' ? false : true,filterBy.sports_id);
        })
    }

    ResetLobbyFilter = () => {
        this.setState({ f_league_id: [] });
        this.setState({ isPaneOpenBottom: true }, function () {

            let selectFilterObj = { league_id: this.state.f_league_id };
            this.props.filterLobbyResults(selectFilterObj);

        });

    }

    ResetPickemLobbyFilter=()=>{
        this.setState({
            pickemSFilter: ''
        });
        this.setState({ isPaneOpenBottom: true }, function () {
            let pickemSFilter = {
                league_id : this.state.pickemSFilter,
                // feed_type : this.state.pickemLFT
            } ;
            this.props.filterByLeague(pickemSFilter);
            CustomHeader.changeFilter(pickemSFilter == '' ? false : true,'');    
        });
    }

    checkLobbyFilterOptions = (filterOption) => {
        if (this.state.isPaneOpenBottom == false) {
            if (typeof filterOption.filtered_league_id != 'undefined') {
                this.setState({ f_league_id: filterOption.filtered_league_id != "" ? [].concat(filterOption.filtered_league_id) : [] });
            }
            this.setState({ isPaneOpenBottom: true });
        }
        return true;
    }

    handleLeagueSelect = (e) => {

        let league_value = e.target.value;

        let leagues = [].concat(this.state.f_league_id);

        let index = leagues.indexOf(league_value);
        if (index > -1) {
            leagues.splice(index, 1);
        }
        else {
            leagues.push(e.target.value);
        }


        this.setState({
            f_league_id: leagues
        });
    }
    handlePickemLeagueSelect = (value) => {
        this.setState({
            pickemSFilter: value
        });
    }
    handleTimeChange = (e) => {
        this.setState({
            dataFor: e
        })
    }
    handleMPCategory = (e) => {
        this.setState({
            MPCat: e
        })
    }
    handleSportChange = (e) => {
        this.setState({
            selectedFSport: e
        })
    }
    

    AllLeagueSelect = (e) => {
        this.setState({
            f_league_id: []
        });
    }
   
    handleContestListFilter = () => {
        this.setState({ isPaneOpenBottom: false });
        this.props.hideFilter();
    }

    ContestListFilterSelect = () => {
        
        this.setState({ 
            isPaneOpenBottom: true,
            
        }, function () {
// console.log('ContestListFilterSelect',this.state.contestListFilterObj)
            // let FilterObj = {
            //     entry_fee_from: this.state.contestListFilterObj.entryFee.min,
            //     entry_fee_to: this.state.contestListFilterObj.entryFee.max,
            //     participants_from: this.state.contestListFilterObj.entries.min,
            //     participants_to: this.state.contestListFilterObj.entries.max,
            //     prizepool_from: this.state.contestListFilterObj.winnings.min,
            //     prizepool_to: this.state.contestListFilterObj.winnings.max,
            //     isApplied:true
            // };
            // console.log('FilterObj',FilterObj)
            // this.props.filterContestList(FilterObj);

            let FilterObj = this.state.selectedCLFilter
            FilterObj.isApplied = true
            this.props.filterContestList(FilterObj);

        });

    }

    // ResetContestListFilter = () => {

    //     CustomHeader.changeFilter(false);
    //     this.setState({ f_league_id: "",isFilterselected: false });
    //     this.setState({ isPaneOpenBottom: true }, function () {

    //         let previousObj = this.state.contestListFilterObj;
    //         previousObj.entryFee.min = previousObj.entryFee.master_min;
    //         previousObj.entryFee.max = previousObj.entryFee.master_max;

    //         previousObj.winnings.min = previousObj.winnings.master_min;
    //         previousObj.winnings.max = previousObj.winnings.master_max;

    //         previousObj.entries.min = previousObj.entries.master_min;
    //         previousObj.entries.max = previousObj.entries.master_max;

    //         this.setState({ contestListFilterObj: previousObj }, function () {


    //         });


    //         let FilterObj = {
    //             entry_fee_from: "",
    //             entry_fee_to: "",
    //             participants_from: "",
    //             participants_to: "",
    //             prizepool_from: "",
    //             prizepool_to: "",
    //             isReset:true
    //         };
    //         this.props.filterContestList(FilterObj,previousObj);

    //     });

    // }

    ResetContestListFilter = () => {

        CustomHeader.changeFilter(false);
        this.setState({ f_league_id: "", isFilterselected: false,CLValue :false,CLEntryFee: '', CLPrizePool: '', CLSlotsSize: '' });
        this.setState({ isPaneOpenBottom: true }, function () {
            this.setState({
                selectedCLFilter :  {
                    entry_fee_from: 0,
                    entry_fee_to: 'Above',
                    participants_from: 0,
                    participants_to:'Above',
                    prizepool_from: 0,
                    prizepool_to:'Above',
                    isApplied: false
                }
            })
            let FilterObj = {
                entry_fee_from: "",
                entry_fee_to: "",
                participants_from: "",
                participants_to: "",
                prizepool_from: "",
                prizepool_to: "",
                isReset: true
            };
            this.props.filterContestList(FilterObj);

        });

    }
    checkContestFilterOptions = (filterOption) => {
        if (this.state.isPaneOpenBottom == false) {
            // this.setState({ key: 1 });
            let previousObj = this.state.contestListFilterObj;
            previousObj.entryFee.min = (filterOption.entry_fee_from != "") ? filterOption.entry_fee_from : previousObj.entryFee.master_min;
            previousObj.entryFee.max = (filterOption.entry_fee_to != "") ? filterOption.entry_fee_to : previousObj.entryFee.master_max;

            previousObj.winnings.min = (filterOption.prizepool_from != "") ? filterOption.prizepool_from : previousObj.winnings.master_min;
            previousObj.winnings.max = (filterOption.prizepool_to != "") ? filterOption.prizepool_to : previousObj.winnings.master_max;

            previousObj.entries.min = (filterOption.participants_from != "") ? filterOption.participants_from : previousObj.entries.master_min;
            previousObj.entries.max = (filterOption.participants_to != "") ? filterOption.participants_to : previousObj.entries.master_max;

            this.setState({ contestListFilterObj: previousObj });
            this.setState({ isPaneOpenBottom: true });
        }
        return true;
    }

    onWinningChange = (rangeValue) => {
        if (rangeValue) {
            let previousObj = this.state.contestListFilterObj;

            previousObj.winnings.min = rangeValue[0];
            previousObj.winnings.max = rangeValue[1];
            // console.log('onWinningChange',previousObj)
            this.setState({ contestListFilterObj: previousObj },()=>{

            // console.log('onWinningChange1==',this.state.contestListFilterObj)
            });
        }
    }

    onEntryFeeChange = (rangeValue) => {
        if (rangeValue) {
            let previousObj = this.state.contestListFilterObj;

            previousObj.entryFee.min = rangeValue[0];
            previousObj.entryFee.max = rangeValue[1];
            this.setState({ contestListFilterObj: previousObj });
        }
    }

    onEntriesChange = (rangeValue) => {
        if (rangeValue) {
            let previousObj = this.state.contestListFilterObj;
            previousObj.entries.min = rangeValue[0];
            previousObj.entries.max = rangeValue[1];
            this.setState({ contestListFilterObj: previousObj });
        }
    }
   
    CLFilter=(item,type)=>{
        let tmpFilter = this.state.selectedCLFilter

        if(type == 'entry_fee'){
            tmpFilter.entry_fee_from = item.entry_fee_from;
            tmpFilter.entry_fee_to = item.entry_fee_to;
        }
        if(type == 'prize_pool'){
            tmpFilter.prizepool_from = item.prizepool_from;
            tmpFilter.prizepool_to =item.prizepool_to;
        }
        if(type == 'slot_size'){
            tmpFilter.participants_from = item.participants_from;
            tmpFilter.participants_to = item.participants_to;
        }
        this.setState({
            selectedCLFilter: tmpFilter
        })
    }

    render() {

        const { leagueList, dataFor , MPCat, pickemSFilter, selectedFSport,tabCount,contestListEntryFee,contestListprizepool,contestListSlotsSize} = this.state;
        const { FitlerOptions } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="filter-container ">
                        <div ref={ref => this.el = ref}>
                        {(FitlerOptions && FitlerOptions.showHubFitlers) && 
                                <React.Fragment>
                                    <Suspense fallback={<div />} ><ReactSlidingPane
                                        isOpen={this.state.isPaneOpenBottom}
                                        from='bottom'
                                        width='100%'
                                        onRequestClose={this.handleLobbyFilter}
                                        >
                                        <div className="filter-header shadow">
                                            {AppLabels.Filters}
                                            <Button 
                                                className="done-btn active" 
                                                onClick={this.SportsFilterSelect}
                                            >
                                                    {AppLabels.DONE}
                                            </Button>
                                        </div>
                                        <div className="filter-body hub-filter">
                                            <ul className='pt10'>
                                                <li className='pt10 pb10 pl15 pr15'>
                                                    <FormGroup>
                                                        <Checkbox className="custom-checkbox" value="" onChange={()=>this.handleSportChange('')} checked={selectedFSport == ''} name="all_leagues" id="all_leagues">
                                                            <span>{AppLabels.ALL}</span>
                                                        </Checkbox>
                                                    </FormGroup>
                                                </li>
                                                {
                                                    this.props.sportsList && this.props.sportsList.map((item, index) => { 
                                                        return (
                                                            <li className='pt10 pb10 pl15 pr15 bottom-padding' key={"leagueList" + index}>
                                                                <FormGroup>
                                                                    <Checkbox className="custom-checkbox" value={item.sports_id} onChange={()=>this.handleSportChange(item)}  
                                                                    checked={selectedFSport && selectedFSport.sports_id == item.sports_id} name="lb-filter" id={item.sports_id}>
                                                                            <span>
                                                                            {
                                                                                Sports.url[item.sports_id + (WSManager.getAppLang() || '')]
                                                                            }
                                                                            </span>
                                                                    </Checkbox>
                                                                </FormGroup>
                                                            </li>
                                                        );
                                                    })
                                                }
                                            </ul>
                                        </div>
                                    </ReactSlidingPane></Suspense>
                                </React.Fragment>
                            }
                            {(Constants.SELECTED_GAMET != Constants.GameType.Pickem) && (FitlerOptions && FitlerOptions.showLobbyFitler) && this.checkLobbyFilterOptions(FitlerOptions) &&
                                <React.Fragment>
                                    <Suspense fallback={<div />} ><ReactSlidingPane
                                        isOpen={this.state.isPaneOpenBottom}
                                        from='bottom'
                                        width='100%'
                                        onRequestClose={this.handleLobbyFilter}
                                        >
                                        <div className="filter-header shadow">
                                            <i className="icon-reload" onClick={this.ResetLobbyFilter}></i>
                                            {AppLabels.Filters}
                                            <Button className="done-btn active" onClick={this.LobbyFilterSelect}>{AppLabels.DONE}</Button>
                                        </div>
                                        <div className="filter-body">


                                            <ul className='pt10'>
                                                <li className='pt10 pb10 pl15 pr15'>
                                                    <FormGroup>
                                                        <Checkbox className="custom-checkbox" value="" onChange={this.AllLeagueSelect} checked={this.state.f_league_id.length == 0} name="all_leagues" id="all_leagues">
                                                            <span>{AppLabels.ALL}</span>
                                                        </Checkbox>
                                                    </FormGroup>
                                                </li>
                                                {
                                                    !_isEmpty(this.props.customLeagues || leagueList)
                                                        ?
                                                        (this.props.customLeagues || leagueList).map((item, index) => {                                                            return (
                                                                <li className='pt10 pb10 pl15 pr15 bottom-padding' key={"leagueList" + index}>
                                                                    <FormGroup>
                                                                        <Checkbox className="custom-checkbox" value={item.league_id} onChange={this.handleLeagueSelect} checked={this.state.f_league_id.indexOf(item.league_id + "") != -1} name="lobby_filter_leagues" id={"lobbyfilter-" + item.league_id}>
                                                                            <span>{item.league_name}</span>
                                                                        </Checkbox>
                                                                    </FormGroup>
                                                                </li>
                                                            );


                                                        })


                                                        :
                                                        <li></li>

                                                }

                                            </ul>


                                        </div>
                                    </ReactSlidingPane></Suspense>
                                </React.Fragment>
                            }
                            {(Constants.SELECTED_GAMET == Constants.GameType.Pickem) && (FitlerOptions && FitlerOptions.showPickLFitlers) && 
                                <React.Fragment>
                                    <Suspense fallback={<div />} ><ReactSlidingPane
                                        isOpen={this.state.isPaneOpenBottom}
                                        from='bottom'
                                        width='100%'
                                        onRequestClose={this.handleLobbyFilter}
                                        >
                                        <div className="filter-header shadow">
                                            <i className="icon-reload" onClick={this.ResetPickemLobbyFilter}></i>
                                            {AppLabels.Filters}
                                            <Button className="done-btn active" onClick={this.PickemLobbyFilterSelect}>{AppLabels.DONE}</Button>
                                        </div>
                                        <div className="filter-body">


                                            <ul className='pt10'>
                                                <li className='pt10 pb10 pl15 pr15'>
                                                    <FormGroup>
                                                        <Checkbox className="custom-checkbox" value="" onChange={()=>this.handlePickemLeagueSelect('')} checked={pickemSFilter == ''} name="all_leagues" id="all_leagues">
                                                            <span>{AppLabels.ALL}</span>
                                                        </Checkbox>
                                                    </FormGroup>
                                                </li>
                                                {
                                                    this.props.leagueList && this.props.leagueList.length > 0 
                                                        ?
                                                        this.props.leagueList.map((item, index) => {                                                            return (
                                                                <li className='pt10 pb10 pl15 pr15 bottom-padding' key={"leagueList" + index}>
                                                                    <FormGroup>
                                                                        <Checkbox className="custom-checkbox" value={item.league_id} 
                                                                            onChange={()=>this.handlePickemLeagueSelect(item.league_id)} 
                                                                            // onChange={()=>this.handlePickemLeagueSelect(item.league_id,item.feed_type)} 
                                                                            checked={pickemSFilter == item.league_id } 
                                                                            name="lobby_filter_leagues" id={"lobbyfilter-" + item.league_id}>
                                                                            <span>{item.league_name}</span>
                                                                        </Checkbox>
                                                                    </FormGroup>
                                                                </li>
                                                            );


                                                        })


                                                        :
                                                        <li></li>

                                                }

                                            </ul>


                                        </div>
                                    </ReactSlidingPane></Suspense>
                                </React.Fragment>
                            }
                            {(FitlerOptions && FitlerOptions.showLFitler) && 
                                <React.Fragment>
                                    <Suspense fallback={<div />} ><ReactSlidingPane
                                        isOpen={this.state.isPaneOpenBottom}
                                        from='bottom'
                                        width='100%'
                                        onRequestClose={this.handleLobbyFilter}
                                        >
                                        <div className="filter-header shadow">
                                            {AppLabels.Filters}
                                            <Button className="done-btn active" onClick={this.LeaderboardFilterSelect}>{AppLabels.DONE}</Button>
                                        </div>
                                        <div className="filter-body open-pred">
                                            <ul className='pt10'>
                                                <li className='pt10 pb10 pl15 pr15'>
                                                    <FormGroup>
                                                        <Checkbox className="custom-checkbox" value="" onChange={()=>this.handleTimeChange('')} checked={dataFor == ''} name="lb-filter" id="all_leagues">
                                                            <span>{AppLabels.ALL}</span>
                                                        </Checkbox>
                                                    </FormGroup>
                                                </li>
                                                
                                                {
                                                    this.props.filerObj && this.props.filerObj.map((item, index) => { 
                                                        return (
                                                            <li className='pt10 pb10 pl15 pr15 bottom-padding' key={"leagueList" + index}>
                                                                <FormGroup>
                                                                    {
                                                                        Constants.SELECTED_GAMET === Constants.GameType.Pickem ?
                                                                        <Checkbox className="custom-checkbox" value={item.league_id} onChange={()=>this.handleTimeChange(item)}  
                                                                        checked={dataFor.league_id == item.league_id} name="lb-filter" id={item.league_name}>
                                                                            <span>{item.league_name}</span>
                                                                        </Checkbox>
                                                                        :
                                                                        <Checkbox className="custom-checkbox" value={item.category_id} onChange={()=>this.handleTimeChange(item)}  
                                                                        checked={dataFor.category_id == item.category_id} name="lb-filter" id={item.name}>
                                                                            <span>{item.name}</span>
                                                                        </Checkbox>
                                                                    }
                                                                </FormGroup>
                                                            </li>
                                                        );


                                                    })
                                                }

                                            </ul>


                                        </div>
                                    </ReactSlidingPane></Suspense>
                                </React.Fragment>
                            }
                            {(FitlerOptions && FitlerOptions.showMPFitler) && 
                                <React.Fragment>
                                    <Suspense fallback={<div />} ><ReactSlidingPane
                                        isOpen={this.state.isPaneOpenBottom}
                                        from='bottom'
                                        width='100%'
                                        onRequestClose={this.handleLobbyFilter}
                                        >
                                        <div className="filter-header shadow">
                                            {AppLabels.Filters}
                                            <Button 
                                                className="done-btn active" 
                                                onClick={this.MPFilterSelect}
                                            >
                                                    {AppLabels.DONE}
                                            </Button>
                                        </div>
                                        <div className="filter-body">
                                            <ul className='pt10'>
                                                {
                                                    this.props.filerObj && this.props.filerObj.map((item, index) => { 
                                                        return (
                                                            <li className='pt10 pb10 pl15 pr15 bottom-padding' key={"leagueList" + index}>
                                                                <FormGroup>
                                                                    <Checkbox className="custom-checkbox" value={item.category_id} onChange={()=>this.handleMPCategory(item)}  
                                                                    checked={MPCat.category_id == item.category_id} name="lb-filter" id={item.category_name}>
                                                                        <span>{item.category_name}</span>
                                                                    </Checkbox>
                                                                </FormGroup>
                                                            </li>
                                                        );
                                                    })
                                                }
                                            </ul>
                                        </div>
                                    </ReactSlidingPane></Suspense>
                                </React.Fragment>
                            }                           
                            {(FitlerOptions && FitlerOptions.showContestListFitler) && this.checkContestFilterOptions(FitlerOptions) &&
                                <React.Fragment>
                                    <Suspense fallback={<div />} ><ReactSlidingPane
                                        isOpen={this.state.isPaneOpenBottom}
                                        from='bottom'
                                        width='100%'
                                        onRequestClose={this.handleLobbyFilter}>
                                        <div className="filter-header">
                                            <i className="icon-reload" onClick={this.ResetContestListFilter}></i>
                                            {AppLabels.FILTERS}
                                            <Button className="done-btn" onClick={this.ContestListFilterSelect}>{AppLabels.DONE}</Button>
                                        </div>
                                        {/* <div className={`filter-body ${tabCount == 1 ? 'single-tab' : tabCount == 2 ? 'double-tab' :''}`}>
                                            <Tabs
                                                activeKey={this.state.key}
                                                onSelect={this.handleSelect}
                                                id="controlled-tab-example" className={`custom-nav-tabs tabs-three contest-filter-tab ${'tabs-'+tabCount}`}
                                            >
                                                {
                                                    parseFloat(this.state.contestListFilterObj.winnings.master_max) > 0 &&
                                                    <Tab eventKey={1} title={AppLabels.WINNINGS}>

                                                        {this.state.refresh && <div className="slider-header">
                                                        <Suspense fallback={<div />} >
                                                            <RangeSlider defaultValue={[this.state.contestListFilterObj.winnings.min, this.state.contestListFilterObj.winnings.max]}
                                                                min={this.state.contestListFilterObj.winnings.master_min}
                                                                max={this.state.contestListFilterObj.winnings.master_max} 
                                                                onAfterChange={this.onWinningChange} /></Suspense>
                                                            <div className="slider-value text-center">{AppLabels.WINNINGS}
                                                                {' '}   {Utilities.numberWithCommas(this.state.contestListFilterObj.winnings.min) || 0} - {Utilities.numberWithCommas(this.state.contestListFilterObj.winnings.max) || 0} 
                                                            </div>
                                                        </div>}

                                                    </Tab>
                                                }
                                                {console.log('this.state.contestListFilterObj',this.state.contestListFilterObj)}
                                                {
                                                    parseFloat(this.state.contestListFilterObj.entryFee.master_max) > 0 &&
                                                    <Tab eventKey={2} title={AppLabels.ENTRY_FEE}>
                                                        <div className="slider-header">
                                                        <Suspense fallback={<div />} ><RangeSlider defaultValue={[this.state.contestListFilterObj.entryFee.min, this.state.contestListFilterObj.entryFee.max]} min={this.state.contestListFilterObj.entryFee.master_min} max={this.state.contestListFilterObj.entryFee.master_max} onAfterChange={this.onEntryFeeChange}/></Suspense>
                                                            <div className="slider-value text-center">{AppLabels.ENTRY_FEE}
                                                                {' '}  {Utilities.numberWithCommas(this.state.contestListFilterObj.entryFee.min) || 0} - {Utilities.numberWithCommas(this.state.contestListFilterObj.entryFee.max) || 0}
                                                            </div>
                                                        </div>

                                                    </Tab>
                                                }
                                                {
                                                    parseFloat(this.state.contestListFilterObj.entries.master_max) > 0 &&
                                                    <Tab eventKey={3} title={AppLabels.MAX_ENTRIES}>
                                                        <div className="slider-header">
                                                        <Suspense fallback={<div />} >
                                                            <RangeSlider defaultValue={[this.state.contestListFilterObj.entries.min, this.state.contestListFilterObj.entries.max]} min={this.state.contestListFilterObj.entries.master_min} max={this.state.contestListFilterObj.entries.master_max} onAfterChange={this.onEntriesChange}/></Suspense>
                                                            <div className="slider-value text-center">
                                                            {AppLabels.ENTRIES}  {Utilities.numberWithCommas(this.state.contestListFilterObj.entries.min) || 0} - {Utilities.numberWithCommas(this.state.contestListFilterObj.entries.max) || 0}
                                                            </div>
                                                        </div>

                                                    </Tab>
                                                }
                                            </Tabs>
                                        </div> */}
                                        
                                        <div className="filter-body filter-body-new">
                                            <div className="entry-fee-view-con">
                                                <div className='entry-fee-text'>{AppLabels.Entry_fee}</div>
                                                <div className="entry-fee-container">
                                                    {contestListEntryFee.map((item, idx) => {
                                                        return (
                                                            <div key={idx} 
                                                                className={`entry-fee-view ${this.state.selectedCLFilter.entry_fee_from == item.entry_fee_from && this.state.selectedCLFilter.entry_fee_to == item.entry_fee_to? ' selected-entry-fee' : ''}`} 
                                                                onClick={() => this.CLFilter(item, 'entry_fee')}>
                                                                    {Utilities.getMasterData().currency_code}{item.entry_fee_from}
                                                                    {item.entry_fee_to == 'Above' ? <> & {AppLabels.ABOVE_TEXT}</>:<> - {Utilities.getMasterData().currency_code}{item.entry_fee_to}</>}
                                                                </div>
                                                        )
                                                    })}
                                                </div>
                                            </div>
                                            <div className="entry-fee-view-con">
                                                <div className='entry-fee-text'>{AppLabels.PRIZE_POOL}</div>
                                                <div className="entry-fee-container">
                                                    {contestListprizepool.map((item, idx) => {
                                                        return (
                                                            <div key={idx} 
                                                            className={`entry-fee-view ${this.state.selectedCLFilter.prizepool_from == item.prizepool_from && this.state.selectedCLFilter.prizepool_to == item.prizepool_to? ' selected-entry-fee' : ''}`} 
                                                             onClick={() => this.CLFilter(item, 'prize_pool')}>
                                                                { Utilities.getMasterData().currency_code}{item.prizepool_from}
                                                                {item.prizepool_to == 'Above' ?<> & {AppLabels.ABOVE_TEXT}</>: <> - {Utilities.getMasterData().currency_code}{item.prizepool_to} </>}
                                                            </div>
                                                        )
                                                    })}
                                                </div>
                                            </div>
                                            <div className="entry-fee-view-con">
                                                <div className='entry-fee-text'>{AppLabels.SLOTS_SIZE}</div>
                                                <div className="entry-fee-container">

                                                    {contestListSlotsSize.map((item, idx) => {
                                                        return (
                                                            <div key={idx} 
                                                            className={`entry-fee-view ${this.state.selectedCLFilter.participants_from == item.participants_from && this.state.selectedCLFilter.participants_to == item.participants_to? ' selected-entry-fee' : ''}`}  
                                                            onClick={() => this.CLFilter(item, 'slot_size')}>
                                                                {item.participants_from}  
                                                                {item.participants_to == 'Above' ? <> & {AppLabels.ABOVE_TEXT}</> : item.participants_to ? <>-{item.participants_to}</> : '' } 
                                                            </div>

                                                        )
                                                    })}
                                                </div>
                                            </div>
                                           
                                        </div>
                                    </ReactSlidingPane></Suspense>
                                </React.Fragment>
                            }
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}