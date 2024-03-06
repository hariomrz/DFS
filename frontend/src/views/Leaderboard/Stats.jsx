import React, {lazy} from 'react';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Skeleton from 'react-loading-skeleton';
import { Utilities, _filter, _Map } from '../../Utilities/Utilities';

const Shimmer = () => {
    return (
        <div className="ranking-list shimmer margin-2p stats-table">
            <div className="display-table-cell pointer-cursor">
                <figure className="user-img shimmer">
                    <Skeleton circle={true} width={40} height={40} />
                </figure>
                <div className="user-name-container shimmer">
                    <Skeleton width={'80%'} height={8} />
                    <Skeleton width={'40%'} height={5} />
                </div>
            </div>
            <div className="display-table-cell pointer-cursor">
                <figure className="user-img shimmer">
                    <Skeleton circle={true} width={40} height={40} />
                </figure>
                <div className="user-name-container shimmer">
                    <Skeleton width={'80%'} height={8} />
                    <Skeleton width={'40%'} height={5} />
                </div>
            </div>
        </div>
    )
}

export default class Stats extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            sort_order: "ASC",
            sort_field: "points"
        }
    }
    componentDidMount() {   
    }    

    render() {
        const {
            statsData
        } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="fixture-stats-wrap bg-white">
                        <div className="stats-section">
                            <div className="thead-sec text-center">
                            <div className="dis-tabcell">{AL.SHOWING_PLAYER_STATS_BY_MATCH}</div>
                            </div>
                            <div className="thead-sec three">
                                <div className="dis-tabcell">{AL.PLAYER}</div>
                                <div  className="dis-tabcell cursor-pointer" onClick={() => {
                                    this.setState({ 
                                        sort_field: 'points',
                                        statsData: statsData.sort((a, b) => (this.state.sort_order == 'ASC' ? a.fantasy_score - b.fantasy_score : b.fantasy_score - a.fantasy_score)) , 
                                        sort_order: this.state.sort_order == 'ASC' ? 'DESC' : 'ASC'})
                                }}>{AL.POINTS} {this.state.sort_field =='points' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>} </div>
                                <div className="dis-tabcell cursor-pointer" onClick={() => {
                                    this.setState({ 
                                        sort_field: 'selBy',
                                        statsData: statsData.sort((a, b) => (this.state.sort_order == 'ASC' ? a.selected_by - b.selected_by : b.selected_by - a.selected_by)) , 
                                        sort_order: this.state.sort_order == 'ASC' ? 'DESC' : 'ASC'})
                                }}>{AL.SEL_BY} {this.state.sort_field =='selBy' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>} </div>
                            </div>
                            <table>
                                <tbody>
                                    {
                                        _Map(statsData,(item,idx)=>{
                                            return(
                                                <tr key={item.player_uid + idx} className={item.user_selected == 1 ? 'selected-tr' : ''}>
                                                    <td>
                                                        <div className="player-img">
                                                            <img src={Utilities.playerJersyURL(item.jersey)} alt=""/>
                                                        </div>
                                                        <div className="plyr-nm">{item.display_name}</div>
                                                        <div className="plyr-abbr">{item.team_abbr || item.team_abbreviation} | {item.position}</div>
                                                    </td>
                                                    <td>{item.fantasy_score}</td>
                                                    <td>{item.selected_by}</td>
                                                </tr>
                                            )
                                        })
                                    }
                                </tbody>
                            </table>
                            
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}