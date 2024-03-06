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
export default class Filter extends React.Component {

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
                entryFee: { master_min: 0, master_max: 1000, min: 0, max: 1000 },
                winnings: { master_min: 0, master_max: 1000, min: 0, max: 1000 },
                entries: { master_min: 0, master_max: 1000, min: 0, max: 1000 }
            }

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
            // this.setState({f_league_id: nextProps.leagueList})
            this.setState({f_league_id: ''})
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
        if ((window.location.pathname == '/lobby' && Constants.SELECTED_GAMET != Constants.GameType.Pickem) || path.indexOf('/contest-listing')>0) {
            this.getFilterMasterData();
        }
    }

    static reloadLobbyFilter() {
        if(globalThis && globalThis.getFilterMasterData){
            globalThis.getFilterMasterData();
        }
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

            let FilterObj = {
                entry_fee_from: this.state.contestListFilterObj.entryFee.min,
                entry_fee_to: this.state.contestListFilterObj.entryFee.max,
                participants_from: this.state.contestListFilterObj.entries.min,
                participants_to: this.state.contestListFilterObj.entries.max,
                prizepool_from: this.state.contestListFilterObj.winnings.min,
                prizepool_to: this.state.contestListFilterObj.winnings.max,
                isApplied:true
            };
            this.props.filterContestList(FilterObj);

        });

    }

    ResetContestListFilter = () => {

        CustomHeader.changeFilter(false);
        this.setState({ f_league_id: "",isFilterselected: false });
        this.setState({ isPaneOpenBottom: true }, function () {

            let previousObj = this.state.contestListFilterObj;
            previousObj.entryFee.min = previousObj.entryFee.master_min;
            previousObj.entryFee.max = previousObj.entryFee.master_max;

            previousObj.winnings.min = previousObj.winnings.master_min;
            previousObj.winnings.max = previousObj.winnings.master_max;

            previousObj.entries.min = previousObj.entries.master_min;
            previousObj.entries.max = previousObj.entries.master_max;

            this.setState({ contestListFilterObj: previousObj }, function () {


            });


            let FilterObj = {
                entry_fee_from: "",
                entry_fee_to: "",
                participants_from: "",
                participants_to: "",
                prizepool_from: "",
                prizepool_to: "",
                isReset:true
            };
            this.props.filterContestList(FilterObj);

        });

    }

    checkContestFilterOptions = (filterOption) => {
        if (this.state.isPaneOpenBottom == false) {
            this.setState({ key: 1 });
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
            this.setState({ contestListFilterObj: previousObj });
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
   


    render() {

        const { leagueList, dataFor , MPCat, pickemSFilter, selectedFSport} = this.state;
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
                                        <div className="filter-body">
                                            <Tabs
                                                activeKey={this.state.key}
                                                onSelect={this.handleSelect}
                                                id="controlled-tab-example" className="custom-nav-tabs tabs-three contest-filter-tab"
                                            >
                                                <Tab eventKey={1} title={AppLabels.WINNINGS}>

                                                    {this.state.refresh && <div className="slider-header">
                                                    <Suspense fallback={<div />} >
                                                        <RangeSlider defaultValue={[this.state.contestListFilterObj.winnings.min, this.state.contestListFilterObj.winnings.max]} min={this.state.contestListFilterObj.winnings.master_min} max={this.state.contestListFilterObj.winnings.master_max} onAfterChange={this.onWinningChange} /></Suspense>
                                                        <div className="slider-value text-center">{AppLabels.WINNINGS}
                                                            {' '}   {Utilities.numberWithCommas(this.state.contestListFilterObj.winnings.min)} - {Utilities.numberWithCommas(this.state.contestListFilterObj.winnings.max)}
                                                        </div>
                                                    </div>}

                                                </Tab>
                                                <Tab eventKey={2} title={AppLabels.ENTRY_FEE}>
                                                    <div className="slider-header">
                                                    <Suspense fallback={<div />} ><RangeSlider defaultValue={[this.state.contestListFilterObj.entryFee.min, this.state.contestListFilterObj.entryFee.max]} min={this.state.contestListFilterObj.entryFee.master_min} max={this.state.contestListFilterObj.entryFee.master_max} onAfterChange={this.onEntryFeeChange}/></Suspense>
                                                        <div className="slider-value text-center">{AppLabels.ENTRY_FEE}
                                                            {' '}  {Utilities.numberWithCommas(this.state.contestListFilterObj.entryFee.min)} - {Utilities.numberWithCommas(this.state.contestListFilterObj.entryFee.max)}
                                                        </div>
                                                    </div>

                                                </Tab>
                                                <Tab eventKey={3} title={AppLabels.MAX_ENTRIES}>
                                                    <div className="slider-header">
                                                    <Suspense fallback={<div />} >
                                                        <RangeSlider defaultValue={[this.state.contestListFilterObj.entries.min, this.state.contestListFilterObj.entries.max]} min={this.state.contestListFilterObj.entries.master_min} max={this.state.contestListFilterObj.entries.master_max} onAfterChange={this.onEntriesChange}/></Suspense>
                                                        <div className="slider-value text-center">
                                                        {AppLabels.ENTRIES}  {Utilities.numberWithCommas(this.state.contestListFilterObj.entries.min)} - {Utilities.numberWithCommas(this.state.contestListFilterObj.entries.max)}
                                                        </div>
                                                    </div>

                                                </Tab>
                                            </Tabs>
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
