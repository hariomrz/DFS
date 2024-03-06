import React from "react";
import { MyContext } from "../../InitialSetup/MyProvider";
import * as AppLabels from "../../helper/AppLabels";
import CustomHeader from '../../components/CustomHeader';
import { _isUndefined} from '../../Utilities/Utilities';
import { getFixtureDetail, getFixtureDetailMultiGame, getStockFixtureDetail} from '../../WSHelper/WSCallings';
import * as Constants from "../../helper/Constants";
import HaveALeagueCodeClass from "../HaveALeagueCodeClass";
import CreatePrivateContest from "./CreatePrivateContest";
import * as WSC from "../../WSHelper/WSConstants";

export default class PrivateContestParent extends React.Component {
    constructor(props) {
      super(props);
      this.state = {
          activeTab:0,
          contestName:'',
          maxParticipants:'',
          entryFee:'',
          noOfWinner:'',
          isMultiEntry:true,
          contestDescription:'',
          winningDistribution:[],
          showPrizeBreakupModal:false,
          prize_distribution_data:null,
          LobyyData:(!_isUndefined(this.props.location.state) ? this.props.location.state.LobyyData : null),
          isSecIn:(!_isUndefined(this.props.location.state) ? this.props.location.state.isSecIn : false),
          isStockF:(!_isUndefined(this.props.location.state) ? this.props.location.state.isStockF : false),
          windowWidth:window.innerWidth > 550 ? 540 : window.innerWidth,
          HeaderOption: {
            back: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true,
            fixture: true,
            filter: false,
            title: '',
            hideShadow: false,
            goBackLobby: false,
            howToPlayPrivate:true
          }
      };
    }

    handleTab(tab){
        let mTab = "";
        if(tab==0){
            mTab = "create";
        }
        if(tab==1){
            mTab = "join";
        }
        this.setState({activeTab:tab})
        let url = window.location.href;
            if (url.includes('#')) {
                url = url.split('#')[0];
            }
        window.history.replaceState("", "", url + "#" + mTab);
    }

    componentDidMount(){
        const matchParam = this.props.match.params;
        let url = window.location.href;
        if(url.includes('stock-fantasy') || Constants.SELECTED_GAMET == Constants.GameType.StockFantasy){
            this.getStockFixtureDetail(matchParam)
        }else{
            this.FixtureDetail(matchParam)
            this.headerRef.GetHeaderProps("lobbyheader", '', '', this.state.LobyyData ? this.state.LobyyData : (this.props.location&& this.props.location.state)?this.props.location.state.LobyyData:null);
        }
        if (url.includes('#')) {
            let tab = url.split('#')[1];
            url = url.split('#')[0];
            this.setState({ 
                activeTab: (tab=='create'?0:1)
            })
            window.history.replaceState("", "", url + "#" + tab);
        }
        else{
            window.history.replaceState("", "", url + "#create");
        }
    }

    getStockFixtureDetail = async (CollectionData) => {
        if (!(this.state.LobyyData && this.state.LobyyData.category_id)) {
            let param = {
                "collection_id": CollectionData.collection_master_id,
            }
            var api_response_data = await getStockFixtureDetail(param);
            if (api_response_data.response_code === WSC.successCode && api_response_data.data) {
                let url = window.location.href;
                let catID = 1;
                if(url.includes('week')){
                    catID = 2
                }else if(url.includes('month')){
                    catID = 3
                }
                api_response_data.data['category_id'] = catID;
                api_response_data.data['collection_master_id'] = CollectionData.collection_master_id;
                api_response_data.data['season_scheduled_date'] = api_response_data.data.scheduled_date;
                let lData = api_response_data.data;
                let mTab = this.state.activeTab;
                this.setState({
                    LobyyData: lData,
                    activeTab:mTab,
                    FixturedDetail: lData,
                    isStockF: true
                })
            }
        }
    }

    FixtureDetail = async (CollectionData) => {
        if (!(this.state.LobyyData && this.state.LobyyData.home)) {
            let param = {
                "sports_id": Constants.AppSelectedSport,
                "collection_master_id": CollectionData.collection_master_id,
            }
            let apiStatus = Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? getFixtureDetailMultiGame : getFixtureDetail;
            var apiResponseData = await apiStatus(param);

            if (apiResponseData) {
                let mTab = this.state.activeTab;
                let api_response_data = apiResponseData
                if(api_response_data.match) {
                    const { match, ..._apiResponseData } = apiResponseData
                    api_response_data = {..._apiResponseData, match_list: match}
                }
                if (_isUndefined(this.props.location.state)) {
                    this.setState({
                        activeTab:mTab,
                        LobyyData: api_response_data
                    },()=>{
                        this.headerRef.GetHeaderProps("lobbyheader", '', '', this.state.LobyyData ? this.state.LobyyData : null);
                    })
                }
                this.setState({
                    activeTab:mTab,
                    FixturedDetail: api_response_data,
                })
                if (Constants.SELECTED_GAMET == Constants.GameType.MultiGame) {
                    this.setState({
                        HeaderOption: {
                            back: true,
                            fixture: true,
                            filter: false,
                            hideShadow: this.state.FixturedDetail && this.state.FixturedDetail.match_list && this.state.FixturedDetail.match_list.length > 1 ? true : false,
                            goBackLobby: !_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                        }
                    })
                }
            }
        }
    }
  
    render() {
        let {HeaderOption,activeTab,LobyyData, isSecIn, isStockF}  = this.state;
        console.log("for sthgh???", this.props)
        if(this.state.isStockF){
            let catID = LobyyData ? (LobyyData.category_id || '') : ''
            HeaderOption = {
                back: true,
                isPrimary: Constants.DARK_THEME_ENABLE ? false : true,
                filter: false,
                title: '',
                hideShadow: false,
                goBackLobby: false,
                screentitle: (catID.toString() === "1" ? AppLabels.DAILY : catID.toString() === "2" ? AppLabels.WEEKLY : AppLabels.MONTHLY) + ' ' + AppLabels.STOCK_FANTASY,
                minileague:true,
                leagueDate: {
                    scheduled_date: LobyyData.scheduled_date || LobyyData.season_scheduled_date || '',
                    end_date: catID.toString() === "1" ? '' : LobyyData.end_date,
                    game_starts_in: LobyyData.game_starts_in || ''
                },
            }
        }
        return (
            <MyContext.Consumer>
            {(context) => (
                <div className="web-container private-contest-parent private-contest-main-wrap">
                    <CustomHeader
                    LobyyData={LobyyData}
                    ref={(ref) => this.headerRef = ref}
                    HeaderOption={HeaderOption}
                    {...this.props} />

                    <div className="tab-group" id='tab-group'>
                        <ul>
                            <li style={{ width: '50%' }} className={this.state.activeTab === 0 ? 'active' : ' inactive'} onClick={() => this.handleTab(0)}>
                                <a href>{AppLabels.CREATE_A_CONTEST}</a>
                            </li>
                            <li style={{ width: '50%' }} className={activeTab === 1 ? 'active' : ' inactive'} onClick={() => this.handleTab(1)}>
                                <a href>{AppLabels.JOIN_CONTEST}</a>
                            </li>
                            <span style={{ width: '50%', left: 'calc(' + (100/2 * this.state.activeTab) + '%)'}} className="active-nav-indicator con-list"></span>
                        </ul>
                    </div>
                    {LobyyData!=null&&
                            <div className='row'>
                        {this.state.activeTab == 0 &&
                            <CreatePrivateContest 
                                {...this.props}
                                LobyyData={LobyyData}
                                isSecIn={isSecIn}
                                isStockF={isStockF}
                                isSecondStrip = {this.state.LobyyData && this.state.LobyyData['2nd_total'] > 0 ? true : false}
                            />
                        }
                        {this.state.activeTab == 1 &&
                            <div className='tab-parent-container'>
                                <HaveALeagueCodeClass 
                                    {...this.props}
                                    isSecIn={isSecIn}
                                    isStockF={isStockF}
                                    LobyyData={LobyyData}
                                    from={'tab'}
                                />
                            </div>
                        }
                    </div>
                    }
                </div>
            )}
            </MyContext.Consumer>
        )
    }
}