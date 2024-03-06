import React, { useState, useEffect } from "react";
import ReactSwipeButton from "react-swipe-button";
import { Modal } from "react-bootstrap";
import * as AppLabels from "../helper/AppLabels";
import { CommonLabels } from "../helper/AppLabels";
import { Images } from "OpinionTrade/Lib";
import * as WSC from "WSHelper/WSConstants";
import WSManager from 'WSHelper/WSManager';
import Slider from "react-rangeslider";
import { Utilities } from "Utilities/Utilities";
import _ from 'lodash';
import ls from 'local-storage';
import dicord from '../OpinionTrade/Lib/coin.wav'
import { getUserBalance} from "WSHelper/WSCallings";
import { notify } from 'react-notify-toast';

const API = {
  GET_QUESTION_DETAILS: WSC.oTradeURL + "trade/lobby/get_question_details",
  SAVE_TEAM: WSC.oTradeURL + "trade/lobby/save_team",
}
const ContestDetailsModal = (props) => {
  const [value, setValue] = useState(1);
  const [status, setStatus] = useState(props.status);
  const [pricevalue, setPricevalue] = useState(props.status == 'yes' ? parseFloat(props.contestDetail.option1_val).toFixed(1) : parseFloat(props.contestDetail.option2_val).toFixed(1));
  const [entervalue, setEntervalue] = useState("1");
  const [quantity, setQuantity] = useState("1");
  const [is_swipe, setSwipe] = useState(false);
  const [is_open, setOpen] = useState(props.IsShow);
  const [detailQuestion, setQuestionDetails] = useState(undefined);
  const [myBalance, setBalance] = useState(props.contestDetail.currency_type == 1 ? (parseFloat(WSManager.getBalance().real_amount) + parseFloat(WSManager.getBalance().winning_amount)) : parseFloat(WSManager.getBalance().point_amount));
  const [audio] = useState(new Audio(dicord));

  useEffect(() => {
    let params = { "question_id": props.contestDetail.question_id }
    WSManager.Rest(API.GET_QUESTION_DETAILS, params).then(({ response_code, data, ...res }) => {
      if (response_code == WSC.successCode) {
        setQuestionDetails(data)
      }
    });
  }, [])
  useEffect(() => {
    setPricevalue(status == 'yes' ? parseFloat(props.contestDetail.option1_val).toFixed(1) : parseFloat(props.contestDetail.option2_val).toFixed(1))
  }, [status])
  const applySaveTeam = () => {
    setSwipe(true)
    let params = { "sports_id": props.contestDetail.sports_id, "question_id": props.contestDetail.question_id, "option_id": status == 'yes' ? 1 : 2, "entry_fee": pricevalue, "quantity": value }
    WSManager.Rest(API.SAVE_TEAM, params).then(({ response_code,message, data, ...res }) => {
      if (response_code == WSC.successCode) {
        Utilities.showToast(res.message, 1000);
        getUserBalance().then((responseJson) => {
          if (responseJson && responseJson.response_code == WSC.successCode) {
              WSManager.setAllowedBonusPercantage(responseJson.data.allowed_bonus_percantage)
              WSManager.setBalance(responseJson.data.user_balance);
          }
      })
        setOpen(false)
        props.initialize_questionList()
        props.BtnClose()
        props.setTradeSuccess(true)
        if(!props.isDetailed){
          props.setTradeItem(props.contestDetail)
        }
      }else{
        Utilities.showToast(message, 5000);
        setOpen(false)
        props.initialize_questionList()
        props.BtnClose()
      }
    });
  }
  const handleValueChange = (value) => {
    if (value > 5000) {
      return false
    }
    setEntervalue(value);
    if (value <= 50) {
      setQuantity(1)
    } else if (value <= 500) {
      setQuantity(2)
    }else if (value <= 2000) {
      setQuantity(3)
    } else {
      setQuantity(4)
    }
    setValue(value);
  };



  const handleChange = (value) => {
    setValue(value);
    handleValueChange(value)
    // audio.play()
  };
  const onPriceChange = (pricevalue) => {
    setPricevalue(pricevalue);
  };
  const Pricehandle = _.debounce(onPriceChange, 10);
  const PricehandleChange = (pricevalue) => {
    Pricehandle(pricevalue);
  };
  const navigateWallet = (amount) => {
    ls.set('fromOpinion', { params: { amount: amount }, url: window.location.pathname })
    props.navigateWallet()
  }
  const returnQuantities = () => {
    let objQuantity = detailQuestion.trade[status == 'yes' ? "2" : "1"];
    let quantity = objQuantity[`${parseFloat(10 - pricevalue).toFixed(1)}`]
    return quantity
  }
  const exceptThisSymbols = ["e", "E", "+", "-", "."];
  let WCount = (Utilities.numberWithCommas(Utilities.kFormatter((myBalance).length || 0))) 
  return (
    <div>
      <Modal
        show={is_open}
        dialogClassName="custom-modal Contest-Details-Modal opinion-modal "
        className="center-modal "
      >
        <Modal.Header className="opinion-header">
           <div className={'left-ele coin-wall-ani'}>
              <a href className={"header-action coin-wall-ani"}>
                  <span className="frontspan">
                      <i className="icon-wallet-ic"></i>
                  </span>
                  <span className={"backspan " + (WCount > 5 && " WCount")}>{props.contestDetail.currency_type == 1 ? '₹' : <img className="coin-img" src={Images.IC_COIN} alt="" />} {Utilities.numberWithCommas(Utilities.kFormatter(myBalance))}</span>
              </a>
          </div>
          <Modal.Title>{'PLACE ORDER'}</Modal.Title>
          <a href onClick={props.BtnClose} className="modal-close">
            <i className="icon-close"></i>
          </a>
        </Modal.Header>
        <Modal.Body className={is_swipe ? "pointer-events-none" : "pointer-events-auto"}>
          <div className="ot-team-info">
            <div className="ot-team-up-text">
              {props.contestDetail.question}
            </div>
            <div className="ot-team-view-toggle">
              <div
                onClick={() => setStatus('yes')}
                className={`ot-item ${status == "yes" ? " active-back-yes" : ""
                  }`}
              >
                {
                    props.contestDetail.currency_type == '1' ?
                        <span>{`${props.contestDetail.option1} | ₹${props.contestDetail.option1_val}`}</span>
                        :
                        <>
                            <span>{`${props.contestDetail.option1} | `}</span>
                            <img alt="" src={Images.IC_COIN} />
                            <span>{`${props.contestDetail.option1_val}`}</span>
                        </>
                }
              </div>
              <div
                onClick={() => setStatus('no')}
                className={`ot-item ${status == "no" ? " active-back-no" : ""
                  }`}
              >
                 {
                    props.contestDetail.currency_type == '1' ?
                        <span>{`${props.contestDetail.option2} | ₹${props.contestDetail.option2_val}`}</span>
                        :
                        <>
                            <span>{`${props.contestDetail.option2} | `}</span>
                            <img alt="" src={Images.IC_COIN} />
                            <span>{`${props.contestDetail.option2_val}`}</span>
                        </>
                }
              </div>
            </div>
          </div>

          <div className="ot-price-body">
            <div className="upper-box">
              <div className="ot-lf-rgt-group">
                <div className="ot-lft lbl">
                  <i className="icon-flash-outline" />
                  <span className="lbl"> {AppLabels.Price}</span>
                </div>
                <div className="ot-rgt">
                  <i className="icon-lock-outline ic-up" />{" "}
                  <span className="lbl">{props.contestDetail.currency_type == 1 ? '₹' : <img alt="" src={Images.IC_COIN} />}{parseFloat(pricevalue).toFixed(1)}</span>
                  {
                    detailQuestion &&
                    <div className="qnt-availability ot-sm-text">{`${returnQuantities()} ${CommonLabels.QUANTITIES_AT_TEXT} `}{props.contestDetail.currency_type == 1 ? '₹' : <img style={{ marginRight: '2px' }} alt="" src={Images.IC_COIN} />}{(pricevalue) + '!'}</div>
                  }
                </div>
              </div>
              <div className="ot-range-custom">
                <Slider
                  min={0.5}
                  step={0.5}
                  max={9.5}
                  value={pricevalue}
                  onChange={PricehandleChange}
                />
              </div>
              <div className="ot-range-text">
                <span className="ot-sm-text">{props.contestDetail.currency_type == 1 ? '₹' : <img alt="" src={Images.IC_COIN} />}0.5</span>
                <span className="ot-sm-text">{props.contestDetail.currency_type == 1 ? '₹' : <img alt="" src={Images.IC_COIN} />}9.5</span>
              </div>
            </div>
            <div className="low-box">
              <div className="ot-lf-rgt-group">
                <div className="ot-lft lbl">
                  <i className="icon-flash-outline"></i>
                  <span className="lbl"> {CommonLabels.QUANTITY_TEXT}</span>
                </div>
                <div className="ot-rgt enter-qnt">
                  <input
                    type="number"
                    onKeyDown={e => exceptThisSymbols.includes(e.key) && e.preventDefault()}
                    onChange={(e) => handleValueChange(e.target.value)}
                    className="form-control"
                    value={entervalue}
                  />
                  <div className="ot-sm-text">
                    {CommonLabels.ENTER_QUANTITY_TEXT}
                  </div>
                </div>
              </div>
              <div className="ot-price-button">
                <button onClick={() => { setQuantity(1); setValue(1); handleValueChange(1) }} className={"btn " + (quantity == 1 ? "active" : "")}>1-50</button>
                <button onClick={() => { setQuantity(2); setValue(51); handleValueChange(51) }} className={"btn " + (quantity == 2 ? "active" : "")}>51-500</button>
                <button onClick={() => { setQuantity(3); setValue(501); handleValueChange(501) }} className={"btn " + (quantity == 3 ? "active" : "")}>501-2000</button>
                <button onClick={() => { setQuantity(4); setValue(2001); handleValueChange(2001) }} className={"btn " + (quantity == 4 ? "active" : "")}>2001-5000</button>
              </div>
              <div className="ot-range-custom">
                <Slider
                  min={quantity == 1 ? 1 : quantity == 2 ? 51 : quantity == 3 ?501:2001}
                  max={quantity == 1 ? 50 : quantity == 2 ? 500 : quantity == 3 ?2000:5000}
                  step={1}
                  value={value}
                  onChange={handleChange}
                />
                {/* <div className='value ot-sm-text'>{value}</div> */}
              </div>
              <div className="ot-range-text">
                <span className="ot-sm-text"> {quantity == 1 ? 1 : quantity == 2 ? 51 : quantity == 3 ?501:2001}</span>
                <span className="ot-sm-text"> {quantity == 1 ? 50 : quantity == 2 ? 500 : quantity == 3 ?2000:5000}</span>
              </div>
            </div>
          </div>

        </Modal.Body>
            <Modal.Footer style={{  opacity: pricevalue * value == 0 ? 0.4 : 1 }} className={"opinion-footer ot-swipe-button " + ` ${is_swipe ? `swipe-${status}-done` : `${status}-bg`}`}>
              <div className="ot-bottom-box">
                <div className="invest">
                  <span className="ot-sm-text">{CommonLabels.YOU_INVEST_TEXT}</span>
                  <div>
                    <img src={props.contestDetail.currency_type == 1 ? Images.IC_RUPEE : Images.COIN_IMG} alt="" />
                    <span>
                      {props.contestDetail.currency_type == 1 ? '₹' : <img style={{ width: '16px', height: '16px', marginBottom: '3px' }} alt="" src={Images.IC_COIN} />}{parseFloat(pricevalue * value).toFixed(1)}
                    </span>
                  </div>
                </div>
                <div className="invest">
                  <span className="ot-sm-text"> {AppLabels.YOU_GET}</span>
                  <div>
                    <img src={Images.IC_BADGE} alt="" />
                    <span>
                      {props.contestDetail.currency_type == 1 ? '₹' : <img style={{ width: '16px', height: '16px', marginBottom: '3px' }} alt="" src={Images.IC_COIN} />}{detailQuestion ? detailQuestion.cap * value : '-'}
                    </span>
                  </div>
                </div>
              </div>
              {
                myBalance >= parseFloat(pricevalue * value) ?
                  <ReactSwipeButton
                    text={status == 'yes' ? CommonLabels.SWIP_RIGHT_FOR_YES : CommonLabels.SWIP_RIGHT_FOR_NO}
                    text_unlocked={'Processing...'}
                    color="#fff"
                    onSuccess={() => applySaveTeam()}
                  />
                :
                  <div onClick={() => navigateWallet(parseFloat(pricevalue * value) - myBalance)} className="add-fund-view">
                    <span>Add Fund</span>
                  </div>
              }
            </Modal.Footer>
      </Modal>
    </div>
  );
};

export default ContestDetailsModal;
