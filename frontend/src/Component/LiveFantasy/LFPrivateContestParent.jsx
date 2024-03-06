import React from "react";
import { MyContext } from "../../InitialSetup/MyProvider";
import * as AppLabels from "../../helper/AppLabels";
import CustomHeader from '../../components/CustomHeader';
import { _isUndefined} from '../../Utilities/Utilities';
import { getFixtureDetailLF} from '../../WSHelper/WSCallings';
import * as Constants from "../../helper/Constants";
import * as WSC from "../../WSHelper/WSConstants";
import HaveALeagueCodeClass from "../../views/HaveALeagueCodeClass";
import { LFCreatePrivateContest } from ".";
import WSManager from "../../WSHelper/WSManager";

export default class LFPrivateContestParent extends React.Component {
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
        WSManager.setH2hMessage(false);
        WSManager.setPickedGameType(Constants.GameType.LiveFantasy)
        const matchParam = this.props.match.params;
        let url = window.location.href;
        if(this.state.LobyyData ==undefined){
            this.FixtureDetail(matchParam)

        }
        this.headerRef.GetHeaderProps("lobbyheader", '', '', this.state.LobyyData ? this.state.LobyyData : (this.props.location&& this.props.location.state)?this.props.location.state.LobyyData:null);

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

    

    FixtureDetail = async (CollectionData) => {
        let param = {
            "sports_id": Constants.AppSelectedSport,
            "collection_id": CollectionData.collection_master_id,
        }
        var api_response_data = await getFixtureDetailLF(param);
        if (api_response_data) {
            let mTab = this.state.activeTab;
            this.setState({
                activeTab:mTab,
                LobyyData: api_response_data.data
            },()=>{
                this.headerRef.GetHeaderProps("lobbyheader", '', '', this.state.LobyyData ? this.state.LobyyData : null);
            })
            this.setState({
                activeTab:mTab,
                FixturedDetail: api_response_data.data,
            })
            
        }
    }
  
    render() {
        let {HeaderOption,activeTab,LobyyData, isSecIn, isStockF}  = this.state;
        HeaderOption= {
            back: true,
            isPrimary: true,
            fixture: true,
            title: '',
            hideShadow: false,
            goBackLobby: false,
            over:(this.state.LobyyData ?  this.state.LobyyData && this.state.LobyyData.overs : '')

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
                            <LFCreatePrivateContest 
                                {...this.props}
                                LobyyData={LobyyData}
                             
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