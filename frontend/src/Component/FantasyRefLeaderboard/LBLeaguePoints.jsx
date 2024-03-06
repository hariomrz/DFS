import React from 'react';
import { MyContext } from '../../views/Dashboard';
import { getLineupWithScore,stockLineupWithScore ,getTeamDetail} from "../../WSHelper/WSCallings";
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { _Map, Utilities } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import FieldView from "../../views/FieldView";
import { geFantasyRefLBHistory,getStockLBHistory } from "../../WSHelper/WSCallings";
import STeamPreview from './STeamPreview';

class LBLeaguePoints extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            history_id: '',
            u_name: '',
            detailData: '',
            total_points: 0,
            SelectedLineup: '',
            showFieldV: false,
            AllLineUPData: {},
            lmc_sport:'',
            isViewAll:false,
            type:this.props.match && this.props.match.params && this.props.match.params.type ? this.props.match.params.type: '0'
        };
    }

    componentDidMount() {
        if (this.props.match && this.props.match.params.history_id) {
            let { history_id, u_name,type } = this.props.match.params;
            let lnm = Utilities.replaceAll(u_name, '_', ' ')
            this.setState({
                history_id,
                u_name: lnm,
            }, () => {
                this.getData()
            })
        }
    }
    getData = () => {
        let param = {
            history_id: this.state.history_id
        }
        let apiCall = this.state.type == 3 || this.state.type == 4 ||  this.state.type == 5  ? getStockLBHistory : geFantasyRefLBHistory
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                if (responseJson.data) {
                    let tp = 0;
                    responseJson.data['match'] = (responseJson.data.match || []).reverse();
                    _Map((responseJson.data.match || []), (item) => {
                        tp = tp + parseFloat(item.score)
                    })
                    this.setState({
                        detailData: responseJson.data,
                        u_name: responseJson.data.user_name || this.state.u_name,
                        total_points: tp
                    })
                }
            }
        })
    }

    getLineupScoreData = (teamItem) => {
        let lineupData = this.state.AllLineUPData && this.state.AllLineUPData[this.state.SelectedLineup] ? this.state.AllLineUPData[this.state.SelectedLineup] : '';
        if (lineupData && this.state.type != '3') {
            this.setState({
                showFieldV: true
            });
        } else {
            let isStock = this.state.type == '3' || this.state.type == '4' || this.state.type == '5'   ? true : false;
            let param = {
                'lineup_master_contest_id': teamItem.lmc_id,
                "sports_id": teamItem.sports_id,
                ...(!isStock && {"lineup_master_id": teamItem.lm_id})
            }

            let apiCall = isStock ? stockLineupWithScore : getTeamDetail //getLineupWithScore
            apiCall(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    let lData;
                    if(!isStock){
                        let data = responseJson.data
                        data['all_position'] = responseJson.data.pos_list;    
                        lData = this.state.AllLineUPData;
                        lData[teamItem.lmc_id] = data;    
                    }
                    this.setState({
                        AllLineUPData: isStock ? responseJson.data:lData,
                        lineupData: isStock ? responseJson.data:lData,
                    }, () => {
                        if(isStock){
                            this.GoToFieldView()
                        }
                        else{
                            this.setState({
                                showFieldV: true
                            });
                        }
                    })
                }
            })
        }
    }

    hideFieldV = () => {
        this.setState({
            showFieldV: false,
            SelectedLineup: ''
        });
    }

    GoToFieldView = () => {
        this.setState({
            isViewAll: true
        })
    }

    onViewAllHide = () => {
        this.setState({
            isViewAll: false
        })
    }
    retrunFormate= (item)=>{
        let startDate = Utilities.getUtcToLocal(item.date);
        let endDate = Utilities.getUtcToLocal(item.end_date);
        let startDateFormate =  Utilities.getFormatedDate({ date: startDate, format: 'DD MMM hh:mm A' })
        let endDateFormate =  Utilities.getFormatedDate({ date: endDate, format: 'hh:mm A' })

        // dateformaturl = new Date(dateformaturl);
        // let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        // let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        // dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        return  startDateFormate + " - "+ endDateFormate
    }
    showFView=(item)=>{
        if(this.state.SelectedLineup != '' && this.state.SelectedLineup != item.lmc_id){
            this.setState({
                showFieldV: false,
                SelectedLineup: ''
            },()=>{
                this.setState({ SelectedLineup: item.lmc_id, lmc_sport: item.sports_id }, () => this.getLineupScoreData(item))
            });
        }
        else{
            this.setState({ SelectedLineup: item.lmc_id, lmc_sport: item.sports_id }, () => this.getLineupScoreData(item))
        }
    }
    render() {
        let subT = AL.MATCHES + ' ' + (this.state.detailData.match || []).length
        const HeaderOption = {
            referalLeaderboradTitle: this.state.u_name,
            referalLeaderboradSubTitle: subT,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            back: true,
            profilePic: this.state.detailData.image || ''
        }
        let isStock = this.state.type == '3' || this.state.type == '4' || this.state.type == '5' ? true : false;
        let lineupData = this.state.AllLineUPData && this.state.AllLineUPData[this.state.SelectedLineup] ? this.state.AllLineUPData[this.state.SelectedLineup] : ''
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container user-leagues-detail">
                        <CustomHeader
                            {...this.props}
                            HeaderOption={HeaderOption}
                        />
                        {
                            this.state.detailData && (this.state.detailData.match || []).map((item, idx) => {
                                return (
                                    <div onClick={() => this.showFView(item)} key={item.name + idx} className="cursor-pointer league-sec">
                                        <div className="left-part">
                                            <div className="lg-det">
                                                {
                                                    this.state.type == 5 ?  <span> {this.retrunFormate(item)} </span>: isStock ?  <span>{item.name}</span> : !isStock && <div> <span>{item.name} </span> | {item.league}</div>
                                                }
                                                {/* { isStock &&  <span>{item.name}</span> }
                                                {
                                                   !isStock && <div> <span>{item.name} </span> | {item.league}</div>
                                                } */}
                                            </div>
                                            <div className="lg-subdet">
                                                {Utilities.getFormatedDate({ date: item.date, format: 'DD MMM' })} | {item.team}
                                            </div>
                                        </div>
                                        <div className="right-part">
                                            <div className="pts">{this.state.type == 5 ? Utilities.getExactValueSP(parseFloat(item.accuracy_percent)) : item.score}</div>
                                            <div className="total-pts">{this.state.type == 5 ? AL.ACCURACY + ' '+ '%' : AL.TOTAL_POINTS}</div>
                                        </div>
                                    </div>
                                )
                            })
                        }
                        {
                            this.state.type !=5 &&
                         <a href style={{ fontSize: 15, pointerEvents: 'none', paddingTop: 16, paddingBottom: 12 }} className="btn btn-primary bottom" >{AL.TOTAL_POINTS} <span style={{ fontSize: 14 }}>: {this.state.type == 4 ? parseFloat(this.state.total_points).toFixed(2)  : this.state.total_points}</span></a>

                        }
                        {
                            this.state.SelectedLineup && this.state.type != '3' && this.state.type != '4' && <>
                            {console.log('lineupData',lineupData)}
                            {console.log('allPosition',lineupData.all_position)}
                            <FieldView
                                SelectedLineup={lineupData ? lineupData.lineup : ''}
                                MasterData={lineupData || ''}
                                isFrom={'rank-view'}
                                isFromLBPoints={true}
                                team_name={lineupData ? (lineupData.team_name || '') : ''}
                                showFieldV={this.state.showFieldV}
                                userName={this.state.u_name}
                                hideFieldV={this.hideFieldV.bind(this)}
                                current_sport={this.state.lmc_sport}
                                allPosition={lineupData.all_position}
                                updateTeamDetails={new Date().valueOf()}
                            />
                            </>
                        }
                          {/* {
                            this.state.isViewAll && isStock &&
                           // <STeamPreview isFrom={'preview'} isTeamPrv={'true'} />
                            <STeamPreview isFrom={'point'} isViewAllShown={this.state.isViewAll} onViewAllHide={this.onViewAllHide} preTeam={this.state.AllLineUPData.lineup}  isTeamPrv={'true'} />

                        } */}
                         {
                            this.state.isViewAll && isStock &&<STeamPreview total_score={this.state.AllLineUPData && this.state.AllLineUPData.team_info ? (this.state.AllLineUPData.team_info.total_score || 0) : 0} status={2} userName={this.state.u_name} isFrom={'point'} type={this.state.type}
                            openTeam={this.state.AllLineUPData ? this.state.AllLineUPData.lineup : ''} isViewAllShown={this.state.isViewAll} onViewAllHide={this.onViewAllHide}/>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default LBLeaguePoints;
