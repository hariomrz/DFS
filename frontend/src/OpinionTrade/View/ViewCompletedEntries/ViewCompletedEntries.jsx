
import React, { useState, useEffect } from 'react';
import * as WSC from "WSHelper/WSConstants";
import WSManager from 'WSHelper/WSManager';
import { useHistory, useParams } from 'react-router-dom';
import { Images } from 'OpinionTrade/Lib';
import CustomHeader from 'components/CustomHeader';
import { DARK_THEME_ENABLE } from 'helper/Constants';
import { EmptyScreen } from 'OpinionTrade/Components';
import InfiniteScroll from 'react-infinite-scroll-component';
import {Countdown} from "../../Components"
import moment from "moment";
import { Utilities } from "Utilities/Utilities";

const FLAG_URL = process.env.REACT_APP_S3_URL + 'upload/flag/';
const API = {
      GET_COMPLETED_LIST: WSC.oTradeURL + "trade/lobby/get_completed_list",
      GET_LIVE_TRADE: WSC.oTradeURL + "trade/lobby/get_live_trade",
      GET_COMPLETED_QUESTION: WSC.oTradeURL + "trade/lobby/get_completed_question",
}

const ViewCompletedEntries = (props) => {
      let history = useHistory();
      const [completedList, setCompletedList] = useState([])
      const [expandList, setExpandList] = useState([])
      const [isLoading, setLoading] = useState(true);
      const [hasMore, setHasMore] = useState(false);
      const [page, setPageNo] = useState(1);
      const [activeTab, setActiveTab] = useState(0);
      const [infoData, setInfoData] = useState({
            today_real_invest: 0,
            today_real_winning: 0,
            total_real_invest: 0,
            total_real_winning: 0,
            today_coin_invest: 0,
            today_coin_winning: 0,
            total_coin_invest: 0,
            total_coin_winning: 0,
            currency_coin: 0,
            currency_real: 0
      });
      const { sports_id } = useParams()

      const [headerOption, setHeaderOption] = useState({
            menu: false,
            back: true,
            notification: true,
            filter: false,
            MLogo: false,
            title: 'My joined',
            isPrimary: DARK_THEME_ENABLE ? false : true,

      })
      const navigateDetails = (tradeItem) => {
            if(WSManager.loggedIn()){
                history.push({ pathname: `/question-details/${tradeItem.home}_vs_${tradeItem.away}/${tradeItem.question_id}`, state: { itemFixture: tradeItem, fixture: tradeItem } })
            }else{
                history.push({ pathname: `/signup` })
            }
        }
      const nFormatter = (num) => {
            if (num >= 1000000000) {
                  return (num / 1000000000).toFixed(1).replace(/\.0$/, '') + 'G';
            }
            if (num >= 1000000) {
                  return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
            }
            if (num >= 1000) {
                  return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
            }
            return num;
      }
      
      useEffect(() => {
            setLoading(true)
            setPageNo(1)
            if(activeTab == 1){
                  let params = { "sports_id": sports_id, "limit": "10", "page": 1 }
                  WSManager.Rest(API.GET_COMPLETED_LIST, params).then(({ response_code, data, ...res }) => {
                        if (response_code == WSC.successCode) {
                              setLoading(false)
                              setHasMore(data.completed.length < parseInt(data.total))
                              setCompletedList(data.completed)
                              let info = {
                                    'today_real_invest': parseFloat(data.today.real_invest),
                                    'today_real_winning': parseFloat(data.today.real_winning),
                                    'total_real_invest': parseFloat(data.total_real_invest),
                                    'total_real_winning': parseFloat(data.total_real_winning),
                                    'today_coin_invest': parseFloat(data.today.coin_invest),
                                    'today_coin_winning': parseFloat(data.today.coin_winning),
                                    'total_coin_invest': parseFloat(data.total_coin_invest),
                                    'total_coin_winning': parseFloat(data.total_coin_winning),
                                    'currency_coin': parseFloat(data.currency_coin),
                                    'currency_real': parseFloat(data.currency_real)
                              }
                              setInfoData(info);
                        }
                  });
            }else{
                  let params = { "sports_id": sports_id, "limit": "10", "page": 1 }
                  WSManager.Rest(API.GET_LIVE_TRADE, params).then(({ response_code, data, ...res }) => {
                        if (response_code == WSC.successCode) {
                              setLoading(false)
                              setHasMore(data.answer.length < parseInt(data.total))
                              let array = [];
                              data.answer.map((item,idx)=>{
                                    let matchItem = data.match.find((e)=>e.season_id == item.season_id)
                                    array.push({
                                          ...matchItem,
                                          ...item
                                    })
                              })
                              setCompletedList(array)
                              let info = {
                                    'today_real_invest': 0,
                                    'today_real_winning': 0,
                                    'total_real_invest': parseFloat(data.current_trade.real_invest),
                                    'total_real_winning': parseFloat(data.current_trade.real_return),
                                    'today_coin_invest': 0,
                                    'today_coin_winning': 0,
                                    'total_coin_invest': parseFloat(data.current_trade.coin_invest),
                                    'total_coin_winning': parseFloat(data.current_trade.coin_return),
                                    'currency_coin': parseFloat(data.current_trade.currency_coin),
                                    'currency_real': parseFloat(data.current_trade.currency_real)
                              }
                              setInfoData(info);
                        }
                  });
            }
            
      }, [activeTab])
      useEffect(() => {
            if (page > 1) {
                  let params = { "sports_id": sports_id, "limit": "10", "page": page }
                  if(activeTab == 1){
                        WSManager.Rest(API.GET_COMPLETED_LIST, params).then(({ response_code, data, ...res }) => {
                              if (response_code == WSC.successCode) {
                                    setHasMore([...completedList, ...data.completed].length < parseInt(data.total))
                                    setCompletedList([...completedList, ...data.completed])
                              }
                        });
                  }else{
                        WSManager.Rest(API.GET_LIVE_TRADE, params).then(({ response_code, data, ...res }) => {
                              if (response_code == WSC.successCode) {
                                    let array = [];
                                    data.answer.map((item,idx)=>{
                                          let matchItem = data.match.find((e)=>e.season_id == item.season_id)
                                          array.push({
                                                home:matchItem.home,
                                                home_flag:matchItem.home_flag,
                                                away:matchItem.away,
                                                away_flag:matchItem.away_flag,
                                                ...item
                                          })
                                    })
                                    setHasMore([...completedList, ...array].length < parseInt(data.total))
                                    setCompletedList([...completedList, ...array])
                              }
                        });
                  }
            }
      }, [page])
      const fetchMoreData = () => {
            if (completedList.length > 0) {
                  setPageNo(parseInt(page) + 1)
            }
      }
      const expandItem =(item)=>{
            let itemExpand = expandList.find((e)=>e.season_id == item.season_id);
            if(!itemExpand){
                  let params = { "season_id": item.season_id}
                  WSManager.Rest(API.GET_COMPLETED_QUESTION, params).then(({ response_code, data, ...res }) => {
                        if (response_code == WSC.successCode) {
                              item.arrayData = [...data]
                              item.isExpand = true;
                              setExpandList([...expandList,...[item]])

                        }
                  });
            }else{
                  itemExpand.isExpand = itemExpand.isExpand?false:true;
                  setExpandList([...expandList.filter((e)=>e.season_id != item.season_id),...[itemExpand]])
            }
      }
      return (
            <div className="web-container web-container-fixed ot-entries-wrap">
                  <CustomHeader {...props} HeaderOption={headerOption} />

                  <div className='right-container'>

                        <div className='container-list'>
                              <div className="view-question-completed">
                                    <div
                                          onClick={() => setActiveTab(0)}
                                          className={"item-completed-view "}>
                                          <span className={activeTab == 0 ? "active-clr" : ""}>LIVE OPINION</span>
                                          {
                                                activeTab == 0 &&
                                                <div className='brder-white' />
                                          }
                                    </div>
                                    <div
                                          onClick={() => setActiveTab(1)}
                                          className={"item-completed-view "}>
                                          <span className={activeTab == 0 ? "" : "active-clr"}>CLOSED OPINION</span>
                                          {
                                                activeTab == 1 &&
                                                <div className='brder-white' />
                                          }
                                    </div>
                              </div>
                              
                              <div className='container-total-trades'>
                                    <div className='view-total-trades'>
                                          <div className='text-view-total-trades'>
                                                <span>{activeTab==1?'Total Trades':"Current Trades"}</span>
                                          </div>
                                          <div className='view-investment-container'>
                                                <div className='view-investment'>
                                                      <div className='view-you-investment'>
                                                            <div className='text-investment'>Investment</div>
                                                            <div className='invest-value-container-view'>
                                                                  <div className={'invest-value-container ' + (infoData.currency_real == 0 ? "hide" : "")}>
                                                                        <img className='img-rupee' alt='' src={Images.IC_RUPEE} />
                                                                        {
                                                                              isLoading ?
                                                                              <span>--</span>
                                                                              :
                                                                              <span>₹{nFormatter(infoData.total_real_invest)}</span>
                                                                        }
                                                                  </div>
                                                                  {(infoData.currency_real == 1 && infoData.currency_coin == 1) && <div className='verticle-line' />}
                                                                  <div className={'invest-value-container ' + (infoData.currency_coin == 0 ? "hide" : "")}>
                                                                        <img className='img-rupee' alt='' src={Images.COIN_IMG} />
                                                                        {
                                                                               isLoading ?
                                                                               <span>--</span>
                                                                               :
                                                                              <span>{nFormatter(infoData.total_coin_invest)}</span>
                                                                        }
                                                                  </div>
                                                            </div>
                                                      </div>
                                                      <div className='line-verticle' />
                                                      <div className='view-you-investment'>
                                                            <div className='text-investment'>Winnings</div>
                                                            <div className='invest-value-container-view'>
                                                                  <div className={'invest-value-container ' + (infoData.currency_real == 0 ? "hide" : "")}>
                                                                        <img className='img-reward' alt='' src={Images.IC_BADGE} />
                                                                        {
                                                                               isLoading ?
                                                                               <span>--</span>
                                                                               :
                                                                              <span>₹{nFormatter(infoData.total_real_winning)}</span>
                                                                        }
                                                                  </div>
                                                                  {(infoData.currency_real == 1 && infoData.currency_coin == 1) && <div className='verticle-line' />}
                                                                  <div className={'invest-value-container ' + (infoData.currency_coin == 0 ? "hide" : "")}>
                                                                        <img className='img-coin' alt='' src={Images.IC_COIN} />
                                                                        {
                                                                               isLoading ?
                                                                               <span>--</span>
                                                                               :
                                                                              <span>{nFormatter(infoData.today_coin_winning)}</span>
                                                                        }
                                                                  </div>
                                                            </div>

                                                      </div>

                                                </div>
                                          </div>
                                          {
                                                activeTab==1&&
                                                <div className='total-winning-container'>
                                                      <div className='view-today'>
                                                            <span>Today</span>
                                                      </div>
                                                      {
                                                            (infoData.currency_real == 1 && infoData.currency_coin == 1) ?
                                                                  <span>{`Investment: ₹${infoData.today_real_invest} | `} <img className='img-coin' alt='' src={Images.IC_COIN} />{infoData.today_coin_invest} <br />{`Winnings: ₹${infoData.today_real_winning} | `}<img className='img-coin' alt='' src={Images.IC_COIN} />{infoData.today_coin_winning}</span>
                                                                  : (infoData.currency_real == 1) ?
                                                                        <span>{`Investment: ₹${infoData.today_real_invest} | `} {`Winnings: ₹${infoData.today_real_winning} `}</span>
                                                                        :
                                                                        <span>{`Investment: `} <img className='img-coin' alt='' src={Images.IC_COIN} />{infoData.today_coin_invest + " | "}{`Winnings: `}<img className='img-coin' alt='' src={Images.IC_COIN} />{infoData.today_coin_winning}</span>
                                                      }
                                                </div>
                                          }
                                    </div>
                              </div>
                              <>
                                    {
                                          completedList.length > 0 && !isLoading ?
                                                <div className='item-completed-container'>
                                                      <InfiniteScroll
                                                            dataLength={completedList.length}
                                                            next={fetchMoreData}
                                                            hasMore={hasMore}
                                                            scrollableTarget="scrollableDiv"
                                                      >
                                                            {
                                                                  completedList.map((item, idx) => {
                                                                        let itemExpand = activeTab == 1?expandList.find((e)=>e.season_id == item.season_id):undefined;
                                                                        return (
                                                                              <div onClick={()=>activeTab==0?null:expandItem(item)} key={idx} className={'item-container '+(activeTab==1?'click-cursor':"")}>
                                                                                    <div className='enable-item'>
                                                                                          <div className='flag-conatiner'>
                                                                                                <div className='flag-item-view'>
                                                                                                      <img className='ic-flag' alt="" src={FLAG_URL + item.home_flag} />
                                                                                                      <div className='view-flag-team'>
                                                                                                            <div className='team-vs'>{`${item.home}  V  ${item.away}`}</div>
                                                                                                      </div>
                                                                                                      <img className='ic-flag' alt="" src={FLAG_URL + item.away_flag} />
                                                                                                </div>
                                                                                                <div key={item.scheduled_date} className='team-schedule-date'>
                                                                                                      <Countdown isCompleted={activeTab == 1} scheduled_date={Utilities.getLocalToUtc(item.scheduled_date, 'DD MMM , hh:mm A')} deadlineTimeStamp={new Date(moment.utc(item.scheduled_date).local()).getTime()} timerCallback={()=>{}} />
                                                                                                </div>
                                                                                          </div>
                                                                                          {
                                                                                                activeTab == 1 && !isLoading ?
                                                                                                      <div className='win-view'>
                                                                                                            <div className='left-line'/>
                                                                                                            <div className='total-win'>Total Won</div>
                                                                                                            {
                                                                                                                  item.currency_type == 1?
                                                                                                                        <div className='total-value'>₹{item.winning}</div>
                                                                                                                  :
                                                                                                                        <div className='total-value'><img className='img-coin' alt='' src={Images.IC_COIN} />{item.winning}</div>
                                                                                                            }
                                                                                                      </div>
                                                                                                :!isLoading &&
                                                                                                <>
                                                                                                      {
                                                                                                            (item.total_trade >= 1)  &&
                                                                                                                  <div className="quantity-container">
                                                                                                                  {
                                                                                                                        (item.total_trade == item.matched_trade) ?
                                                                                                                        <>
                                                                                                                              <img alt="" src={Images.IC_MATCH_TRADE}/>
                                                                                                                              <div className='quantity-text match-trade'>{`${item.total_trade} Qty Matched`}</div>
                                                                                                                        </>
                                                                                                                        :
                                                                                                                        <>
                                                                                                                              <img alt="" src={Images.IC_UNMATCH_TRADE}/>
                                                                                                                              <div className='quantity-text'>{`${item.matched_trade}/${item.total_trade} Qty Matched`}</div>
                                                                                                                        </>
                                                                                                                  }
                                                                                                                  </div>
                                                                                                      }
                                                                                                </>
                                                                                          }
                                                                                    </div>
                                                                                    {activeTab==0 && !isLoading && <div onClick={()=>navigateDetails(item)} className='item-txt-question'>{item.question}</div>}

                                                                                    <div className={'investment-container '+((activeTab==1 && itemExpand && itemExpand.isExpand)?"border-none":"")}>
                                                                                          {
                                                                                                item.currency_type == 1 ?
                                                                                                      <div className='text-investment'>{`Investment: ₹${item.entry_fee}`}</div>
                                                                                                      :
                                                                                                      <div className='text-investment'>{'Investment: '}<img className='img-coin' alt='' src={Images.IC_COIN} />{item.entry_fee}</div>
                                                                                          }
                                                                                          {
                                                                                                activeTab == 0 && !isLoading &&
                                                                                                  <div className="line-verticle" />
                                                                                          }
                                                                                          { 
                                                                                                activeTab == 0 && !isLoading &&
                                                                                                <>
                                                                                                      {
                                                                                                            item.currency_type == 1 ?
                                                                                                                  <div className='text-investment active-clr'>{`Returns: ₹${item.return_val}`}</div>
                                                                                                                  :
                                                                                                                  <div className='text-investment active-clr'>{'Returns: '}<img className='img-coin' alt='' src={Images.IC_COIN} />{item.return_val}</div>
                                                                                                      }
                                                                                                </>
                                                                                          }
                                                                                    </div>
                                                                                    <div className='expand-contains'>
                                                                                          {
                                                                                                (activeTab==1 && itemExpand && itemExpand.isExpand) &&
                                                                                                      <>
                                                                                                            {
                                                                                                                  itemExpand.arrayData.map((itemChild,idx)=>{
                                                                                                                        return(
                                                                                                                              <div className='expand-container'>
                                                                                                                                    <div className='text-question'>{itemChild.question}</div>
                                                                                                                                    <div className='expand-invest-container'>
                                                                                                                                          {
                                                                                                                                                itemChild.currency_type == 1 ?
                                                                                                                                                      <div className='text-investment'>{`Investment: ₹${itemChild.entry_fee}`}</div>
                                                                                                                                                      :
                                                                                                                                                      <div className='text-investment'>{'Investment: '}<img className='img-coin' alt='' src={Images.IC_COIN} />{itemChild.entry_fee}</div>
                                                                                                                                          }
                                                                                                                                          {
                                                                                                                                                itemChild.currency_type == 1 ?
                                                                                                                                                      <div className='text-investment active-clr'>{`Winning: ₹${itemChild.winning}`}</div>
                                                                                                                                                      :
                                                                                                                                                      <div className='text-investment active-clr'>{'Winning: '}<img className='img-coin' alt='' src={Images.IC_COIN} />{itemChild.winning}</div>
                                                                                                                                          }
                                                                                                                                    </div>
                                                                                                                              </div>
                                                                                                                        )
                                                                                                                  })
                                                                                                            }
                                                                                                      </>
                                                                                                
                                                                                          }
                                                                                    </div>

                                                                              </div>
                                                                        )
                                                                  })
                                                            }
                                                      </InfiniteScroll>
                                                </div>
                                                : !isLoading &&
                                                <EmptyScreen title={"There is no record available"} />
                                    }
                              </>
                        </div>
                  </div>
            </div>
      );
};

export default ViewCompletedEntries;
