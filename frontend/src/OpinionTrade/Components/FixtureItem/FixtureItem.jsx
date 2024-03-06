import React from "react";
import { Images } from "OpinionTrade/Lib";
import { Utilities } from "Utilities/Utilities";
import { Countdown } from "..";
import moment from "moment";
import { ScrollSync, ScrollSyncPane } from 'react-scroll-sync';

const FLAG_URL = process.env.REACT_APP_S3_URL + 'upload/flag/';
const FixtureItem = ({ timerCallback, fixtureList, selectedSeasonID, actionSelectSeasonID }) => {
   
    return (
        <div className="ot-match-list-container">
            {
                fixtureList.length > 0 &&
                    <>
                        {
                            fixtureList.length > 5 ?
                            <ScrollSync>
                                <div style={{ display: 'flex',flexDirection:'column',position: 'relative'}}>
                                        <ScrollSyncPane>
                                                <div className='ot-match-list contains-scroll-disable'>
                                                        {
                                                        fixtureList.map((item, idx) => {
                                                            if(idx % 2 != 0){return null}
                                                            return (
                                                                <div key={idx} onClick={() => actionSelectSeasonID(item.season_id)} className={"ot-fixture-team " + (selectedSeasonID == item.season_id ? "active-view" : "")}>
                                                                    {
                                                                        item.is_pin_season == 1 &&
                                                                        <img className="pinned-fixture" alt="" src={Images.IC_PINS}/>
                                                                    }
                                                                    <div className="team-flag-view">
                                                                        <img alt="" src={FLAG_URL + item.home_flag} />
                                                                    </div>
                                                                    <div className="title-match-vs">
                                                                        <div className="txt-title">{`${item.home}  vs  ${item.away}`}</div>

                                                                        <div className="text-schedule-date">
                                                                            {
                                                                                is_exist_live_tag(item.scheduled_date) ?
                                                                                    <span className="view-tag">Live</span>
                                                                                    :
                                                                                    <Countdown scheduled_date={Utilities.getUtcToLocal(item.scheduled_date, 'DD MMM , hh:mm A')} deadlineTimeStamp={new Date(Utilities.getUtcToLocal(item.scheduled_date, 'DD MMM , hh:mm A')).getTime()} timerCallback={timerCallback} />
                                                                                }
                                                                        </div>
                                                                    </div>
                                                                    <div className="team-flag-view">
                                                                        <img alt="" src={FLAG_URL + item.away_flag} />
                                                                    </div>

                                                                </div>
                                                            )
                                                        })
                                                    }
                                                </div>
                                        </ScrollSyncPane>
                                        <ScrollSyncPane>
                                                <div style={{marginTop:'-8px',paddingLeft:'30px'}} className='ot-match-list'>
                                                        {
                                                        fixtureList.map((item, idx) => {
                                                            if(idx % 2 == 0){return null}
                                                            return (
                                                                <div key={idx} onClick={() => actionSelectSeasonID(item.season_id)} className={"ot-fixture-team " + (selectedSeasonID == item.season_id ? "active-view" : "")}>
                                                                     {
                                                                        item.is_pin_season == 1 &&
                                                                        <img className="pinned-fixture" alt="" src={Images.IC_PINS}/>
                                                                    }
                                                                    <div className="team-flag-view">
                                                                        <img alt="" src={FLAG_URL + item.home_flag} />
                                                                    </div>
                                                                    <div className="title-match-vs">
                                                                        <div className="txt-title">{`${item.home}  vs  ${item.away}`}</div>

                                                                        <div className="text-schedule-date">
                                                                            {
                                                                                is_exist_live_tag(item.scheduled_date) ?
                                                                                    <span className="view-tag">Live</span>
                                                                                    :
                                                                                    <Countdown scheduled_date={Utilities.getUtcToLocal(item.scheduled_date, 'DD MMM , hh:mm A')} deadlineTimeStamp={new Date(Utilities.getUtcToLocal(item.scheduled_date, 'DD MMM , hh:mm A')).getTime()} timerCallback={timerCallback} />
                                                                                }
                                                                        </div>
                                                                    </div>
                                                                    <div className="team-flag-view">
                                                                        <img alt="" src={FLAG_URL + item.away_flag} />
                                                                    </div>

                                                                </div>
                                                            )
                                                        })
                                                    }
                                                </div>
                                        </ScrollSyncPane>
                                </div>
                            </ScrollSync>
                            :
                                <ItemFixture timerCallback={timerCallback} fixtureList={fixtureList} selectedSeasonID={selectedSeasonID} actionSelectSeasonID={actionSelectSeasonID}/>
                        }
                    </>
            }
        </div>

    )
};
const is_exist_live_tag = (scheduled_date) => {
    var start_time = new Date(moment.utc(scheduled_date).local()).getTime();
    var end_time = new Date().getTime();

    return end_time >= start_time
}
const ItemFixture = ({ timerCallback, fixtureList, selectedSeasonID, actionSelectSeasonID }) => {
    return(
        <div className='ot-match-list'>
            {
                fixtureList.map((item, idx) => {
                    return (
                        <div key={idx} onClick={() => actionSelectSeasonID(item.season_id)} className={"ot-fixture-team " + (selectedSeasonID == item.season_id ? "active-view" : "")}>
                             {
                                item.is_pin_season == 1 &&
                                <img className="pinned-fixture" alt="" src={Images.IC_PINS}/>
                            }
                            <div className="team-flag-view">
                                <img alt="" src={FLAG_URL + item.home_flag} />
                            </div>
                            <div className="title-match-vs">
                                <div className="txt-title">{`${item.home}  vs  ${item.away}`}</div>

                                <div className="text-schedule-date">
                                    {
                                        is_exist_live_tag(item.scheduled_date) ?
                                            <span className="view-tag">Live</span>
                                            :
                                            <Countdown scheduled_date={Utilities.getUtcToLocal(item.scheduled_date, 'DD MMM , hh:mm A')} deadlineTimeStamp={new Date(Utilities.getUtcToLocal(item.scheduled_date, 'DD MMM , hh:mm A')).getTime()} timerCallback={timerCallback} />
                                    }
                                </div>
                            </div>
                            <div className="team-flag-view">
                                <img alt="" src={FLAG_URL + item.away_flag} />
                            </div>

                        </div>
                    )
                })
            }
        </div>
    )
}

export default FixtureItem;
