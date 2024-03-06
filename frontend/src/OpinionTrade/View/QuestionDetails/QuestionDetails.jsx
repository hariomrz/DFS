
import React, { useState, useEffect, useMemo } from 'react';
import * as WSC from "WSHelper/WSConstants";
import WSManager from 'WSHelper/WSManager';
import { useHistory, useParams, useLocation } from 'react-router-dom';
import { Images } from 'OpinionTrade/Lib';
import CustomHeader from 'components/CustomHeader';
import { DARK_THEME_ENABLE } from 'helper/Constants';
import { EmptyScreen, SelectedFixtureItem } from 'OpinionTrade/Components';
import moment from 'moment';
import { Utilities } from 'Utilities/Utilities';
import ls from 'local-storage';
import InfiniteScroll from 'react-infinite-scroll-component';
import { getUserBalance} from "WSHelper/WSCallings";
import OpinionShare from 'Modals/OpinionShare';
import HowtoPlayModal from "Modals/HowtoPlayModal/HowtoPlayModal";
import OpinionRules from 'Modals/OpinionRules';
import OpinionTradeSucess from "Modals/OpinionTradeSucess";
import CancelOrder from 'Modals/CancelOrder';

const API = {
      GET_MY_QUESTION: WSC.oTradeURL + "trade/lobby/get_my_question",
      GET_TRADE_ACTIVITY: WSC.oTradeURL + "trade/lobby/get_trade_activity",
      GET_MY_JOINED: WSC.oTradeURL + "trade/lobby/get_my_joined",
      CANCEL_ANWSER: WSC.oTradeURL + "trade/lobby/cancel_anwser",
}

const QuestionDetails = (props) => {
      let history = useHistory();
      const [selectedFixtureList, setSelectedFixtureList] = useState([])
      const [order_book, setOrderBook] = useState([])
      const [trade_data, setTradeData] = useState(undefined)
      const [fixtureList, setFixtureList] = useState([])
      const [activityData, setActivity] = useState({
            page_no: 1,
            hasMore: false,
            arrayData: []
      })
      const [joinData, setJoinData] = useState({
            page_no: 1,
            hasMore: false,
            arrayData: [],
            invest_data: undefined
      })
      const { question_id } = useParams()
      const location = useLocation();
      const [activeTab, setActiveTab] = useState(0);
      const [activeTabJoin, setActiveTabJoin] = useState(0);
      const [isLoading, setLoading] = useState(true);
      const [showShareM, setShowShareM]  = useState(false);
      const [isCancelOrder, setCancelOrder]  = useState(false);
      const headerOption = {
            menu: false,
            back: true,
            notification: true,
            filter: false,
            MLogo: false,
            title: 'Question Details',
            isPrimary: DARK_THEME_ENABLE ? false : true,

      }
      const [howtomodal, sethowtomodal] = useState(false);
      const [showRules, setShowRules] = useState(false);
      const [tradeSuccess ,setTradeSuccess] = useState(false)

      const handleClose = () => sethowtomodal(false);
      const hideRulesScoring = () => setShowRules(false);
      const handleShow = () => sethowtomodal(true);
      const handleCancelOrderhide = () => setCancelOrder(false);
      
      useEffect(() => {
            if (activeTab == 1 && activityData.arrayData.length == 0) {
                  setLoading(true)
                  setActiveTabJoin(0)
                  setActivity({ page_no: 1, hasMore: false, arrayData: [] })
                  GET_TRADE_ACTIVITY()
            } else if (activeTab == 2 && joinData.arrayData.length == 0) {
                  setLoading(true)
                  setActiveTabJoin(0)
                  setJoinData({ page_no: 1, hasMore: false, arrayData: [], invest_data: undefined })
                  GET_MY_JOINED()
            }
      }, [activeTab])
      useEffect(() => {
            ls.remove('fromOpinion')
            GET_MY_QUESTION()
      }, [])
      const initialize_questionList = () => {
            setActivity({ page_no: 1, hasMore: false, arrayData: [] })
            setJoinData({ page_no: 1, hasMore: false, arrayData: [], invest_data: undefined })
            setActiveTabJoin(0)
            setLoading(true)
            GET_MY_QUESTION()
           
      }
      const hideShareM =() =>{
            setShowShareM(false)
      }
      const fetchMoreDataActivity = () => {
            if (activityData.arrayData.length > 0 && activityData.hasMore) {
                  let obj = activityData;
                  obj.page_no = parseInt(activityData.page_no) + 1;
                  setActivity(obj)
                  GET_TRADE_ACTIVITY()
            }
      };
      const fetchMoreDataJoin = () => {
            if (joinData.arrayData.length > 0 && joinData.hasMore) {
                  let obj = joinData;
                  obj.page_no = parseInt(joinData.page_no) + 1;
                  setJoinData(obj)
                  GET_MY_JOINED()
            }
      };
      const GET_TRADE_ACTIVITY = () => {
            let params = { "question_id": question_id, "limit": "20", "page": activityData.page_no }
            WSManager.Rest(API.GET_TRADE_ACTIVITY, params).then(({ response_code, data }) => {
                  if (response_code == WSC.successCode) {
                        let arrUser = [];
                        let user_data = data.user_data;
                        (data.result || []).map((itm) => {
                              arrUser.push({
                                    left_name: itm.answer == 1 ? (user_data[itm.user_id] ? user_data[itm.user_id].name : 'Trade') : (user_data[itm.m_user_id] ? user_data[itm.m_user_id].name : 'Trade'),
                                    left_img: itm.answer == 1 ? (user_data[itm.user_id] ? Utilities.getThumbURL(user_data[itm.user_id].image) : Images.DEFAULT_AVATAR) : (user_data[itm.m_user_id] ? Utilities.getThumbURL(user_data[itm.m_user_id].image) : Images.DEFAULT_AVATAR),
                                    left_percentage: itm.answer == 1 ? parseFloat(itm.entry_fee).toFixed(1) * 10 : 100 - (parseFloat(itm.entry_fee).toFixed(1) * 10),
                                    right_name: itm.answer == 2 ? (user_data[itm.user_id] ? user_data[itm.user_id].name : 'Trade') : (user_data[itm.m_user_id] ? user_data[itm.m_user_id].name : 'Trade'),
                                    right_img: itm.answer == 2 ? (user_data[itm.user_id] ? Utilities.getThumbURL(user_data[itm.user_id].image) : Images.DEFAULT_AVATAR) : (user_data[itm.m_user_id] ? Utilities.getThumbURL(user_data[itm.m_user_id].image) : Images.DEFAULT_AVATAR),
                                    right_percentage: itm.answer == 2 ? parseFloat(itm.entry_fee).toFixed(1) * 10 : 100 - (parseFloat(itm.entry_fee).toFixed(1) * 10),
                                    ...itm,
                              })
                        })

                        let obj = activityData;
                        obj.hasMore = (data.result.length == 20 && [...obj.arrayData, ...arrUser].length < parseInt(data.total))
                        obj.arrayData = [...obj.arrayData, ...arrUser]
                        setActivity({...obj,arrayData:[]})
                        setActivity(obj)
                        setLoading(false)
                  }
            });
      }
      const GET_MY_JOINED = () => {
            let params = { "question_id": question_id, season_id: selectedFixtureList[0].season_id, "limit": "20", "page": joinData.page_no }
            WSManager.Rest(API.GET_MY_JOINED, params).then(({ response_code, data }) => {
                  if (response_code == WSC.successCode) {
                        let arrUser = [];
                        let user_data = data.user_data;
                        (data.result || []).map((itm) => {
                              arrUser.push({
                                    left_name: itm.answer == 1 ? (user_data[itm.user_id] ? user_data[itm.user_id].name : 'Trade') : (user_data[itm.m_user_id] ? user_data[itm.m_user_id].name : 'Trade'),
                                    left_img: itm.answer == 1 ? (user_data[itm.user_id] ? Utilities.getThumbURL(user_data[itm.user_id].image) : Images.DEFAULT_AVATAR) : (user_data[itm.m_user_id] ? Utilities.getThumbURL(user_data[itm.m_user_id].image) : Images.DEFAULT_AVATAR),
                                    left_percentage: itm.answer == 1 ? parseFloat(itm.entry_fee).toFixed(1) * 10 : 100 - (parseFloat(itm.entry_fee).toFixed(1) * 10),
                                    right_name: itm.answer == 2 ? (user_data[itm.user_id] ? user_data[itm.user_id].name : 'Trade') : (user_data[itm.m_user_id] ? user_data[itm.m_user_id].name : 'Trade'),
                                    right_img: itm.answer == 2 ? (user_data[itm.user_id] ? Utilities.getThumbURL(user_data[itm.user_id].image) : Images.DEFAULT_AVATAR) : (user_data[itm.m_user_id] ? Utilities.getThumbURL(user_data[itm.m_user_id].image) : Images.DEFAULT_AVATAR),
                                    right_percentage: itm.answer == 2 ? parseFloat(itm.entry_fee).toFixed(1) * 10 : 100 - (parseFloat(itm.entry_fee).toFixed(1) * 10),
                                    ...itm,
                              })
                        })

                        let obj = joinData;
                        obj.hasMore = (data.result.length == 20 && [...obj.arrayData, ...arrUser].length < parseInt(data.total))
                        obj.arrayData = [...obj.arrayData, ...arrUser]
                        obj.invest_data = data.invest_data

                        setJoinData({...obj,arrayData:[]})
                        setJoinData(obj)
                        setLoading(false)
                  }
            });
      }
      const GET_MY_QUESTION = () => {
            if (location && location.state && location.state.itemFixture && location.state.fixture) {
                  setFixtureList([location.state.fixture])
                  setSelectedFixtureList([location.state.itemFixture])
            }
            let params = { "question_id": question_id }
            WSManager.Rest(API.GET_MY_QUESTION, params).then(({ response_code, data }) => {
                  if (response_code == WSC.successCode) {
                        setFixtureList([{ away: data.away, away_flag: data.away_flag, home: data.home, home_flag: data.home_flag, season_id: data.season_id }])
                        setSelectedFixtureList([{ sports_id: data.sports_id, ...data.question }])
                        setTradeData(data.trade_data || undefined)
                        setLoading(false)
                        let arrOrder = [];
                        (data.order_book || []).map((itm) => {
                              let findObj = arrOrder.find((e) => (e.left_entry_fee == parseFloat(itm.entry_fee).toFixed(1) && itm.answer == 1) || (e.right_entry_fee == parseFloat(itm.entry_fee).toFixed(1) && itm.answer == 2));
                              if (!findObj) {
                                    let filledObj = data.order_book.find((e) => e.entry_fee == (10 - itm.entry_fee) && itm.answer != e.answer)
                                    arrOrder.push({
                                          left_entry_fee: itm.answer == 1 ? parseFloat(itm.entry_fee).toFixed(1) : parseFloat(10 - itm.entry_fee).toFixed(1),
                                          left_total: itm.answer == 1 ? itm.total : (filledObj ? filledObj.total : 0),
                                          left_match: itm.answer == 1 ? itm.matched : (filledObj ? filledObj.matched : 0),
                                          left_unmatch: itm.answer == 1 ? itm.unmatched : (filledObj ? filledObj.unmatched : 0),
                                          right_entry_fee: itm.answer == 2 ? parseFloat(itm.entry_fee).toFixed(1) : parseFloat(10 - itm.entry_fee).toFixed(1),
                                          right_total: itm.answer == 2 ? itm.total : (filledObj ? filledObj.total : 0),
                                          right_match: itm.answer == 2 ? itm.matched : (filledObj ? filledObj.matched : 0),
                                          right_unmatch: itm.answer == 2 ? itm.unmatched : (filledObj ? filledObj.unmatched : 0),
                                    })
                              }
                        })
                        setOrderBook(arrOrder.sort((a, b) => a.left_entry_fee > b.left_entry_fee ? 1 : -1))
                        setActiveTab(0)

                  }
            });
      }
      const CANCEL_ANWSER = () => {
            setLoading(true)
            setCancelOrder(false)
            let params = { "season_id": selectedFixtureList[0].season_id, "type": activeTabJoin == 0 ? '1' : "2", "question_id": question_id }
            WSManager.Rest(API.CANCEL_ANWSER, params).then(({ response_code, data, ...res }) => {
                  if (response_code == WSC.successCode) {
                        Utilities.showToast(res.message, 1000);
                        initialize_questionList()
                        getUserBalance().then((responseJson) => {
                              if (responseJson && responseJson.response_code == WSC.successCode) {
                                  WSManager.setAllowedBonusPercantage(responseJson.data.allowed_bonus_percantage)
                                  WSManager.setBalance(responseJson.data.user_balance);
                              }
                          })
                  }
            });
      }
      const itemSelectedFixture = useMemo(() => { return <SelectedFixtureItem setTradeSuccess={setTradeSuccess} handleShow={handleShow} setShowRules={setShowRules} initialize_questionList={initialize_questionList} fixtureList={fixtureList} selectedFixtureList={selectedFixtureList} isDetailed={true} setShowShareM={setShowShareM}/> }, [fixtureList, selectedFixtureList])
      const returnQuantity = (key) => {
            return joinData.invest_data[key]
      }

      const is_exist_live_tag = (scheduled_date) => {
            var start_time = new Date(moment.utc(scheduled_date).local()).getTime();
            var end_time = new Date().getTime();

            return !(end_time >= start_time)
      }
      return (
            <div className="web-container web-container-fixed ot-completed-wrap">
                  <CustomHeader {...props} HeaderOption={headerOption} />
                  <div className='right-container'>
                        <div className='container-list-question'>
                              {itemSelectedFixture}
                              {
                                    <div className='container-total-trades'>
                                          <div className='tab-container-option'>
                                                <div onClick={() => setActiveTab(0)} className={'tab-view ' + (activeTab == 0 ? 'active-indiactor' : '')}>
                                                      <span className={activeTab == 0 ? 'active-clr' : ''}>{`Order book`}</span>
                                                </div>
                                                <div onClick={() => setActiveTab(1)} className={'tab-view ' + (activeTab == 1 ? 'active-indiactor' : '')}>
                                                      <span className={activeTab == 1 ? 'active-clr' : ''}>{`Activity`}</span>
                                                </div>
                                                <div onClick={() => setActiveTab(2)} className={'tab-view ' + (activeTab == 2 ? 'active-indiactor' : '')}>
                                                      <span className={activeTab == 2 ? 'active-clr' : ''}>{`my joined`}</span>
                                                </div>
                                          </div>
                                          <div className='sparater-line' />
                                          <div className='view-total-trades-question'>
                                                {
                                                      activeTab == 0 ?
                                                            <>
                                                                  {
                                                                        order_book.length > 0 ?
                                                                              <div className='order-container'>
                                                                                    {
                                                                                          selectedFixtureList.length > 0 &&
                                                                                          <div className="toggle-container">
                                                                                                <div className='toogle-text'>{selectedFixtureList[0].option1}</div>
                                                                                                <div className='toogle-text'>{selectedFixtureList[0].option2}</div>
                                                                                          </div>
                                                                                    }
                                                                                    <div className='view-list-order'>
                                                                                          {
                                                                                                order_book.map((item, idx) => {
                                                                                                      let left_top = (parseInt(item.left_match) / (parseInt(item.left_total) / 100));
                                                                                                      let left_bottom = (parseInt(item.left_unmatch) / (parseInt(item.left_total) / 100));
                                                                                                      let right_top = (parseInt(item.right_match) / (parseInt(item.right_total) / 100));
                                                                                                      let right_bottom = (parseInt(item.right_unmatch) / (parseInt(item.right_total) / 100));
                                                                                                      return (
                                                                                                            <div className='view-order'>
                                                                                                                  <div className='left_view'>
                                                                                                                        <div className='text-entry'>{item.left_entry_fee}</div>
                                                                                                                        <div className='contains-graph left'>
                                                                                                                              <div style={{ width: `${item.left_match == 0?0:left_top<25?25:left_top}%`, backgroundColor: '#A7CAFE' }} className='filled-view-progress left'>
                                                                                                                                    <span style={{ color: '#28292D' }}>{item.left_match == 0?'':item.left_match}</span>
                                                                                                                              </div>
                                                                                                                              <div style={{ width: `${item.left_unmatch == 0?0:left_bottom<25?25:left_bottom}%`, backgroundColor: '#BCBBBB' }} className='filled-view-progress left'>
                                                                                                                                    <span style={{ color: '#fff' }}>{item.left_unmatch == 0?'':item.left_unmatch}</span>
                                                                                                                              </div>
                                                                                                                        </div>
                                                                                                                  </div>
                                                                                                                  <div className='vertical-line' />
                                                                                                                  <div className='left_view right'>
                                                                                                                        <div className='contains-graph right'>
                                                                                                                              <div style={{ width: `${item.right_match == 0?0:right_top<25?25:right_top}%`, backgroundColor: '#A7CAFE' }} className='filled-view-progress right'>
                                                                                                                                    <span style={{ color: '#28292D' }}>{item.right_match == 0?'':item.right_match}</span>
                                                                                                                              </div>
                                                                                                                              <div style={{ width: `${item.right_unmatch == 0?0:right_bottom<25?25:right_bottom}%`, backgroundColor: '#BCBBBB' }} className='filled-view-progress right'>
                                                                                                                                    <span style={{ color: '#fff' }}>{item.right_unmatch == 0?'':item.right_unmatch}</span>
                                                                                                                              </div>
                                                                                                                        </div>
                                                                                                                        <div className='text-entry'>{item.right_entry_fee}</div>
                                                                                                                  </div>
                                                                                                            </div>
                                                                                                      )
                                                                                                })
                                                                                          }
                                                                                    </div>
                                                                              </div>
                                                                              : !isLoading &&
                                                                              <EmptyScreen title={"There is no records available"} />
                                                                  }
                                                            </>
                                                            : activeTab == 1 ?
                                                                  <div className='container-total-participent'>
                                                                        {
                                                                              activityData.arrayData.length > 0 &&
                                                                                    <span>Participants</span>
                                                                        }
                                                                        <div className='participent-conatins'>
                                                                              {
                                                                                    activityData.arrayData.length > 0 ?
                                                                                          <InfiniteScroll
                                                                                                dataLength={activityData.arrayData.length}
                                                                                                next={fetchMoreDataActivity}
                                                                                                hasMore={activityData.hasMore}
                                                                                                scrollableTarget="scrollableDiv"
                                                                                                className={''}
                                                                                          >
                                                                                                {
                                                                                                      activityData.arrayData.map((item, idx) => {
                                                                                                            return (
                                                                                                                  <div className='item-participent'>
                                                                                                                        <div className='item-user'>
                                                                                                                              <img alt='' src={item.left_img} />
                                                                                                                              <div className='text-user-name'>{item.left_name}</div>
                                                                                                                        </div>
                                                                                                                        <div className='tag-container'>
                                                                                                                              <div className='tag-container-view'>
                                                                                                                                    <div style={{ width: `${item.left_percentage > 20 ? item.left_percentage : 25}%` }} className='left_view'>
                                                                                                                                          {
                                                                                                                                                selectedFixtureList[0].currency_type == 1 ?
                                                                                                                                                      <span>₹{item.left_percentage / 10}</span>
                                                                                                                                                      :
                                                                                                                                                      <span><img alt='' src={Images.IC_COIN} />{item.left_percentage / 10}</span>
                                                                                                                                          }
                                                                                                                                    </div>
                                                                                                                                    <div style={{ width: `${item.right_percentage > 20 ? item.right_percentage : 25}%` }} className='right_view'>
                                                                                                                                          {
                                                                                                                                                selectedFixtureList[0].currency_type == 1 ?
                                                                                                                                                      <span>₹{item.right_percentage / 10}</span>
                                                                                                                                                      :
                                                                                                                                                      <span><img alt='' src={Images.IC_COIN} />{item.right_percentage / 10}</span>
                                                                                                                                          }
                                                                                                                                    </div>
                                                                                                                              </div>
                                                                                                                              <div className={'text-match ' + (item.matchup_id != 0 ? "" : "pending-clr")}>{item.matchup_id != 0 ? 'MATCHED' : 'PENDING'}</div>
                                                                                                                        </div>
                                                                                                                        <div className='item-user'>
                                                                                                                              <img alt='' src={item.right_img} />
                                                                                                                              <div className='text-user-name'>{item.right_name}</div>
                                                                                                                        </div>
                                                                                                                  </div>
                                                                                                            )
                                                                                                      })
                                                                                                }
                                                                                          </InfiniteScroll>
                                                                                          : !isLoading &&
                                                                                          <EmptyScreen title={"There is no records available"} />
                                                                              }
                                                                        </div>
                                                                  </div>
                                                                  :
                                                                  <div className='container-total-participent'>
                                                                  {
                                                                              (joinData.invest_data && selectedFixtureList.length > 0) &&
                                                                                  <>
                                                                                    <div className='view-join-toggle'>
                                                                                          <div onClick={() => setActiveTabJoin(0)}  className={'btn-view-toggle '+(activeTabJoin == 0?"active-yes-bg":"")}><div className={'txt-view-toggle '+(activeTabJoin==0?"active-yes-clr":"")}>{`${selectedFixtureList[0].option1} (${returnQuantity('quntity_yes')})`}</div></div>
                                                                                          <div onClick={() => setActiveTabJoin(1)}  className={'btn-view-toggle '+(activeTabJoin == 1?"active-no-bg":"")}><div className={'txt-view-toggle '+(activeTabJoin==1?"active-no-clr":"")}>{`${selectedFixtureList[0].option2} (${returnQuantity('quntity_no')})`}</div></div>
                                                                                    </div>
                                                                                    <div className='view-join-trades-question'>
                                                                                                <div className={'view-total-records ' + (is_exist_live_tag(selectedFixtureList[0].scheduled_date) ? "btn-brder" : "")}>
                                                                                                      <div className='item-total-records'>
                                                                                                            <span>Total Quantity</span>
                                                                                                            <div className='txt-quantity-val'>{returnQuantity(activeTabJoin == 0 ? 'quntity_yes' : 'quntity_no')}</div>
                                                                                                      </div>
                                                                                                      <div className='line-vertical' />
                                                                                                      <div className='item-total-records'>
                                                                                                            <span>Total Investment</span>
                                                                                                            {
                                                                                                                  selectedFixtureList[0].currency_type == 1?
                                                                                                                        <div className='txt-quantity-val'>₹{returnQuantity(activeTabJoin == 0 ? 'total_yes' : 'total_no')}</div>
                                                                                                                  :
                                                                                                                        <div className='txt-quantity-val'><img alt='' src={Images.IC_COIN}/>{returnQuantity(activeTabJoin == 0 ? 'total_yes' : 'total_no')}</div>
                                                                                                            }
                                                                                                      </div>
                                                                                                </div>
                                                                                                {
                                                                                                      (is_exist_live_tag(selectedFixtureList[0].scheduled_date) && (activeTabJoin == 0?joinData.invest_data.cancel_btn_yes ==1:joinData.invest_data.cancel_btn_no ==1)) &&
                                                                                                      <div className='view-cancel-container'>
                                                                                                            <span>Cancel Unmatched</span>
                                                                                                            <div style={{ pointerEvents: isLoading ? "none" : "auto" }} onClick={() => setCancelOrder(true)} className='cancel-view'><div className='txt-cancel-view'>Cancel ALL</div></div>
                                                                                                      </div>
                                                                                                }
                                                                                    </div>
                                                                                  </>
                                                                        }
                                                                        <div className='participent-conatins'>
                                                                              {
                                                                                    joinData.arrayData.length > 0 ?
                                                                                          <InfiniteScroll
                                                                                                dataLength={joinData.arrayData.length}
                                                                                                next={fetchMoreDataJoin}
                                                                                                hasMore={joinData.hasMore}
                                                                                                scrollableTarget="scrollableDiv"
                                                                                                className={''}
                                                                                          >
                                                                                                {
                                                                                                      joinData.arrayData.map((item, idx) => {
                                                                                                            return (
                                                                                                                  <div className='item-participent'>
                                                                                                                        <div className='item-user'>
                                                                                                                              <img alt='' src={item.left_img} />
                                                                                                                              <div className='text-user-name'>{item.left_name}</div>
                                                                                                                        </div>
                                                                                                                        <div className='tag-container'>
                                                                                                                              <div className='tag-container-view'>
                                                                                                                                    <div style={{ width: `${item.left_percentage > 20 ? item.left_percentage : 25}%` }} className='left_view'>
                                                                                                                                          {
                                                                                                                                                selectedFixtureList[0].currency_type == 1 ?
                                                                                                                                                      <span>₹{item.left_percentage / 10}</span>
                                                                                                                                                      :
                                                                                                                                                      <span><img alt='' src={Images.IC_COIN} />{item.left_percentage / 10}</span>
                                                                                                                                          }
                                                                                                                                    </div>
                                                                                                                                    <div style={{ width: `${item.right_percentage > 20 ? item.right_percentage : 25}%` }} className='right_view'>
                                                                                                                                          {
                                                                                                                                                selectedFixtureList[0].currency_type == 1 ?
                                                                                                                                                      <span>₹{item.right_percentage / 10}</span>
                                                                                                                                                      :
                                                                                                                                                      <span><img alt='' src={Images.IC_COIN} />{item.right_percentage / 10}</span>
                                                                                                                                          }
                                                                                                                                    </div>
                                                                                                                              </div>
                                                                                                                              <div className={'text-match ' + (item.matchup_id != 0 ? "" : "pending-clr")}>{item.matchup_id != 0 ? 'MATCHED' : 'PENDING'}</div>
                                                                                                                        </div>
                                                                                                                        <div className='item-user'>
                                                                                                                              <img alt='' src={item.right_img} />
                                                                                                                              <div className='text-user-name'>{item.right_name}</div>
                                                                                                                        </div>
                                                                                                                  </div>
                                                                                                            )
                                                                                                      })
                                                                                                }
                                                                                          </InfiniteScroll>
                                                                                          : !isLoading &&
                                                                                          <EmptyScreen title={"There is no records available"} />
                                                                              }
                                                                        </div>
                                                                  </div>
                                                }
                                          </div>
                                    </div>
                              }
                              
                        </div>
                  </div>
                  {
                        showShareM && selectedFixtureList.length > 0 &&
                        <OpinionShare
                              preData={{
                              mShow: showShareM,
                              mHide: hideShareM,
                              itemQuestion:selectedFixtureList[0],

                              }}
                        />
                  }
                  {showRules && <OpinionRules MShow={showRules} MHide={hideRulesScoring} />}

                  {<HowtoPlayModal show={howtomodal} hide={handleClose} />}
                  {tradeSuccess && <OpinionTradeSucess  isDetails={true} MShow={tradeSuccess} MHide={()=>{setTradeSuccess(false)}} />}
                  {(isCancelOrder && selectedFixtureList.length > 0) && (<CancelOrder joinData={joinData} activeTabJoin={activeTabJoin} CANCEL_ANWSER={CANCEL_ANWSER} itemFixture={selectedFixtureList[0]} IsShow={isCancelOrder} BtnClose={handleCancelOrderhide} />
            )}
            </div>
      );
};

export default QuestionDetails;
