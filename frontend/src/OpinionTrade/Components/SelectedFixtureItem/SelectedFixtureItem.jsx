import React, { useState } from "react";
import { Utilities } from "Utilities/Utilities";
import InfiniteScroll from 'react-infinite-scroll-component';
import ContestDetailsModal from "Modals/ContestDetailsModal";
import { useHistory } from 'react-router-dom';
import { Images } from "OpinionTrade/Lib";
import moment from "moment";
import WSManager from "WSHelper/WSManager";
import {Countdown} from ".."

const FLAG_URL = process.env.REACT_APP_S3_URL + 'upload/flag/';

const SelectedFixtureItem = ({ setTradeSuccess,setTradeItem,fixtureList,timerCallback,trade_data,handleShow,setShowRules, selectedFixtureList, fetchMoreData, hasMore, toggle = true, isDetailed = false ,setShowShareM,initialize_questionList}) => {
    const [contestDeatailModal, setContestDeatailModal] = useState(false);
    const [contestDetail, setContestDetail] = useState([])
    const [status, setStatus] = useState()
    let history = useHistory();
    const is_exist_new_tag = (added_date) => {
        var start_time =  new Date(moment.utc(added_date).local()).getTime();
        var end_time = new Date().getTime();
        return ((end_time - start_time) / 1000) < 7200
    }
    const is_exist_live_tag=(scheduled_date)=>{
        var start_time = new Date(moment.utc(scheduled_date).local()).getTime();
        var end_time = new Date().getTime();

         return end_time >= start_time
    }
    const openModal = (item, status) => {
        if(WSManager.loggedIn()){
            setContestDeatailModal(true);
            setContestDetail(item)
            setStatus(status)
        }else{
            history.push({ pathname: `/signup` })
        }
    };
    const BtnClose = () => {
        setContestDeatailModal(false);
        setContestDetail()
        setStatus()
    };
    const navigateDetails = (item) => {
        if(WSManager.loggedIn()){
            let fixture = fixtureList.find((e) => e.season_id == item.season_id);
            history.push({ pathname: `/question-details/${fixture.home}_vs_${fixture.away}/${item.question_id}`, state: { itemFixture: item, fixture: fixture } })
        }else{
            history.push({ pathname: `/signup` })
        }
    }
    const navigateWallet = () => {
        if(WSManager.loggedIn()){
            history.push({ pathname: `/my-wallet` })
        }else{
            history.push({ pathname: `/signup` })
        }
    }
    return (
        <>
            <InfiniteScroll
                dataLength={selectedFixtureList.length}
                next={fetchMoreData}
                hasMore={hasMore}
                scrollableTarget="scrollableDiv"
                className={toggle?"":'pading-btm'}
            >
                {
                    selectedFixtureList.map((item, idx) => {
                        if(fixtureList == undefined || Utilities.getMasterData().fantasy_list == undefined){return null}
                        let itemMatch = fixtureList.find((e) => e.season_id == item.season_id);
                        let dictSports = Utilities.getMasterData().fantasy_list.find((e) => e.sports_id == item.sports_id)
                        if (!itemMatch) { return null }
                        let itemQuantity = (toggle && trade_data) ? trade_data[item.question_id] :undefined
                        return (
                            <div style={{marginBottom:isDetailed?'10px':'15px'}} key={idx} className='ot-fixture-view '>
                                <div className='fixture-title-view'>
                                    <div style={{ cursor: isDetailed ? "auto" : "pointer" }} onClick={() => isDetailed ? null : navigateDetails(item)} className='text-fixture'>{item.question}</div>
                                    <div className='view-tag-container'>
                                        <div className='view-tag'>
                                            {
                                               item.added_date && is_exist_new_tag(item.added_date) &&
                                                <div className='item-new-tag'>
                                                    <span>New</span>
                                                </div>
                                            }
                                            {
                                                is_exist_live_tag(item.scheduled_date) &&
                                                <div className='item-live-tag'>
                                                    <div className="ovel"/>
                                                    <span>Live</span>
                                                </div>
                                            }
                                            {
                                                dictSports &&
                                                <div className='item-sports-tag'>
                                                    <span>{dictSports.sports_name}</span>
                                                </div>
                                            }
                                            
                                        </div>
                                        {
                                            isDetailed ?
                                                <div className="contain-share">
                                                    <div onClick={() => {handleShow()}} className="txt-how-play">How to Play?</div>
                                                    <div className="line-vertical"/>
                                                    <div onClick={() => {setShowRules(true)}} className="txt-how-play">Rules</div>
                                                    <div className="line-vertical"/>
                                                    <div onClick={()=>setShowShareM(true)} className="view-share-container">
                                                        <img alt="" src={Images.IC_SHARE}/>
                                                    </div>
                                                </div>
                                            :
                                                <>
                                                    {
                                                        ((itemQuantity && itemQuantity.user_total_trade >= 1 && toggle) || (!toggle && item.total_trade > 0)) ?
                                                            <div className="quantity-container">
                                                                {
                                                                    ((toggle && itemQuantity.user_matched_trade == itemQuantity.user_total_trade) || (!toggle && item.total_trade == item.matched_trade)) ?
                                                                    <>
                                                                        <img alt="" src={Images.IC_MATCH_TRADE}/>
                                                                        <div className='quantity-text match-trade'>{`${toggle?itemQuantity.user_total_trade:item.total_trade} Qty Matched`}</div>
                                                                    </>
                                                                    :
                                                                    <>
                                                                        <img alt="" src={Images.IC_UNMATCH_TRADE}/>
                                                                        <div className='quantity-text'>{`${toggle?itemQuantity.user_matched_trade:item.matched_trade}/${toggle?itemQuantity.user_total_trade:item.total_trade} Qty Matched`}</div>
                                                                    </>
                                                                }
                                                            </div>
                                                        : toggle &&
                                                            <div className="quantity-container">
                                                                <img alt="" src={Images.IC_TRADE}/>
                                                                <div className='quantity-text no-trade'>{itemQuantity?`${itemQuantity.total_unmatched} Trades`:'0 Trade'}</div>
                                                            </div>
                                                    }
                                                </>
                                        }
                                    </div>
                                    <div className='enable-item'>
                                        <img className='ic-flag' alt="" src={FLAG_URL + itemMatch.home_flag} />
                                        <div className='view-flag-team'>
                                            <div className='team-vs'>{`${itemMatch.home}  vs  ${itemMatch.away}`}</div>
                                        </div>
                                        <img className='ic-flag' alt="" src={FLAG_URL + itemMatch.away_flag} />
                                        <div className="line-vertical" />
                                        <div key={item.scheduled_date} className='team-schedule-date'>
                                             <Countdown scheduled_date={Utilities.getLocalToUtc(item.scheduled_date, 'DD MMM , hh:mm A')} deadlineTimeStamp={new Date(moment.utc(item.scheduled_date).local()).getTime()} timerCallback={timerCallback} />
                                         </div>
                                    </div>
                                </div>
                                
                                {
                                    toggle ?
                                        <div className='answer-title-view'>
                                            <div className='view-question-toggle'>
                                                <div onClick={() => is_exist_live_tag(item.scheduled_date) ? null : openModal(item, "yes")} style={{ cursor: is_exist_live_tag(item.scheduled_date) ? "auto" : "pointer" }} className={'item-yes'}>
                                                    {
                                                        item.currency_type == '1' ?
                                                            <span>{`${item.option1} | ₹${item.option1_val}`}</span>
                                                            :
                                                            <>
                                                                <span>{`${item.option1} | `}</span>
                                                                <img alt="" src={Images.IC_COIN} />
                                                                <span>{`${item.option1_val}`}</span>
                                                            </>
                                                    }
                                                </div>
                                                <div onClick={() => is_exist_live_tag(item.scheduled_date) ? null : openModal(item, "no")} style={{ cursor: is_exist_live_tag(item.scheduled_date) ? "auto" : "pointer" }} className={'item-no'}>
                                                    {
                                                        item.currency_type == '1' ?
                                                            <span>{`${item.option2} | ₹${item.option2_val}`}</span>
                                                            :
                                                            <>
                                                                <span>{`${item.option2} | `}</span>
                                                                <img alt="" src={Images.IC_COIN} />
                                                                <span>{`${item.option2_val}`}</span>
                                                            </>
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                        :
                                        <div className="option-title-view">
                                            <div className="text-container-opt">
                                                <div className="text-investment">{'Investment: '}{item.currency_type == '1' ? '₹' : <img alt="" src={Images.IC_COIN} />}{item.entry_fee}</div>
                                                <div className="line-verticle" />
                                                <div className="text-currentreturn">{'Current Returns: '}{item.currency_type == '1' ? '₹' : <img alt="" src={Images.IC_COIN} />}{item.return_val}</div>
                                            </div>
                                        </div>

                                }
                            </div>
                        )
                    })
                }
            </InfiniteScroll>
            {contestDeatailModal && (
                <ContestDetailsModal isDetailed={isDetailed} setTradeSuccess={setTradeSuccess} setTradeItem={setTradeItem} initialize_questionList={initialize_questionList} navigateWallet={navigateWallet} status={status} IsShow={contestDeatailModal} contestDetail={contestDetail} BtnClose={BtnClose} />
            )}

        </>

    )

};

export default SelectedFixtureItem;
