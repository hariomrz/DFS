import React from 'react';

import * as AppLabels from "../../helper/AppLabels";
import { Utilities, _Map } from '../../Utilities/Utilities';
import Images from '../../components/images';

export default class PFMyLeaderboardItem extends React.Component {

    render() {
        let { userRank, index, openLineup, isExpanded, contestItem, SelectedLineup } = this.props;

        return (
            <div key={index} 
                onClick={(e) => openLineup(e,userRank,1)} 
                className={"ranking-list pointer-cursor my-ranking-list" + (SelectedLineup == userRank.lineup_master_contest_id ? ' sel-active' : '')}>
                <div className="display-table-cell text-center">
                    <div className="rank">{userRank.game_rank}</div>
                </div>
                <div className={"display-table-cell pl-1 pt3 pb3 xleaderboard-grd" + (isExpanded ? " " : '')}>
                    <div className="user-name-container mt6">
                        {
                            // !isExpanded &&
                            index == 0 &&
                            <div className="user-name">{AppLabels.You}</div>
                        }
                        <div className={"user-team-name" + (!isExpanded ? ' ' : '')}>
                            {

                                <span className="won-amount">
                                    {
                                        userRank.prize_data && userRank.prize_data.length > 0 ?

                                            _Map(userRank.prize_data, (prizeItem, idx) => {

                                                return (

                                                    (prizeItem.prize_type == 0) ?
                                                        <span className="contest-prizes" >
                                                            {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                                                            {userRank.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}
                                                        </span>
                                                        :
                                                        (prizeItem.prize_type == 1) ?
                                                            <span className="contest-prizes" >

                                                                {<span style={{ display: 'inlineBlock' }}>{Utilities.getMasterData().currency_code}</span>}
                                                                {userRank.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}
                                                            </span>
                                                            :
                                                            (prizeItem.prize_type == 2) ?
                                                                <span className="contest-prizes" >
                                                                    {<span style={{ display: 'inlineBlock' }}>
                                                                        <img style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />
                                                                        {userRank.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}</span>}

                                                                </span>
                                                                :
                                                                (prizeItem.prize_type == 3) ?
                                                                    <span className="contest-prizes" >
                                                                        {<span style={{ display: 'inlineBlock' }}>{userRank.prize_data.length === idx + 1 ? prizeItem.name : prizeItem.name + "/"}</span>}

                                                                    </span> : ''



                                                )


                                            })
                                            :
                                            userRank.won_prize != "" &&
                                            <span className="won-amount">
                                                {contestItem.prize_type == 1 &&
                                                    <React.Fragment>
                                                        {Utilities.getMasterData().currency_code}
                                                    </React.Fragment>
                                                }
                                                {contestItem.prize_type == 0 &&
                                                    <i className="icon-bonus"></i>
                                                }
                                                {userRank.won_prize} <span className="won">{AppLabels.WON.toLowerCase()} - </span>
                                            </span>


                                    }
                                </span>
                            }

                            {userRank.prize_data && userRank.prize_data.length > 0 ?
                                <React.Fragment>

                                    <span style={{color:'#5DBE7D'}} className="won"> {AppLabels.WON} -  </span>{userRank.team_name}
                                </React.Fragment> : " " + userRank.team_name
                            }
                            {/* <a href onClick={(e)=>this.props.TeamComparison(e,userRank,'')}>Comparison</a> */}
                        </div>
                        {/* <a href onClick={(e)=>openLineup(e,userRank,3)}>
                            <i className="icon-ground"></i>
                        </a> */}
                    </div>
                </div>
                <div className="display-table-cell leaderboard-grd">
                        <a href onClick={(e)=>openLineup(e,userRank,3)}>
                            <i className="icon-ground"></i>
                        </a>
                    <div className="points">{userRank.total_score}</div>
                </div>
                <div className='space' />
                {/* </div> */}

            </div>
        )
    }

}