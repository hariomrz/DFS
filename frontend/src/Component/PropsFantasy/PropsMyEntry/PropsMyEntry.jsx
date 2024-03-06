import React, { useEffect, useState } from 'react';
import CustomHeader from '../../../components/CustomHeader';
import PropsEntry from '../Common/PropsEntry/PropsEntry';
import * as AppLabels from "../../../helper/AppLabels";
import { CommonLabels } from "../../../helper/AppLabels";
import WSManager from '../../../WSHelper/WSManager';
import * as WSC from "../../../WSHelper/WSConstants";
import { useLocation, useParams } from 'react-router-dom';
import { Utilities, _Map, _filter, _isEmpty, getPropsName } from '../../../Utilities/Utilities';
import { AppSelectedSport } from '../../../helper/Constants';
import Images from '../../../components/images';
import { ConfirmationPopup, DownloadAppBuyCoinModal, Thankyou } from '../../../Modals';
import ls from 'local-storage';
import { ClearConfirmModal, RefundModal } from '../Common';
import { getUserBalance } from '../../../WSHelper/WSCallings';
import PropsPlayerCard from '../Common/PropsModal/PropsPlayerCard';

const API = {
  GET_PAYOUT_MASTER_DATA: WSC.propsURL + "props/lobby/get_payout_master_data",
  SAVE_TEAM: WSC.propsURL + "props/lobby/save_team",
  GET_USER_LINEUP: WSC.propsURL + "props/lobby/get_user_lineup",
}


const PropsMyEntry = (props) => {
  // State & const 
  let { payoutData } = props;
  const {
    allow_props
  } = Utilities.getMasterData()
  const { user_team_id } = useParams()
  const { state = {} } = useLocation()
  const HeaderOption = {
    back: true,
    notification: false,
    hideShadow: true,
    isPrimary: true,
    MLogo: true,
    user_team_id
    // ...(user_team_id ? { redirectTo: "/my-contests?contest=upcoming" } : { goBackLobby: true })
  }
  const [propsList, setPropsList] = useState(ls.get('picksList') || [])
  const [winings, setWinings] = useState(null)
  const [posting, setPosting] = useState(true)
  const [showConfirmation, setShowConfirmation] = useState(false)
  const [showThankYouModal, setShowThankYouModal] = useState(false)
  const [clearConfirmShow, setClearConfirm] = useState(false)
  const [refundModalShow, setRefundModal] = useState(false)
  const [showDAM, setShowDAM] = useState(false)
  const [pl, setPL] = useState([])
  const [propsIDs, setPropsIds] = useState(state.props_list || Utilities.getPropsIds(AppSelectedSport))
  const [userBal, setUserBal] = useState({});
  const [isEntryChanges, setEntryChanges] = useState(false);
  const [isEntryFeeChanges, setEntryFeeChanges] = useState(false);
  const [form, setForm] = useState({
    ...(user_team_id ?
      {
        entry_fee: state.team_details.entry_fee,
        isInvalid: false
      }
      :
      {
        entry_fee: '',
        isInvalid: null
      }
    )
  })

  const [master, setMaster] = useState({
    payout: [],
    user_setting: {},
    selectedPayout: {},
    picksRange: ''
  })
  // Props Player Card
  const [showPlayerCard, setShowPlayerCard] = useState(false);   //store player data
  const [player_idx, setPlayerIdx] = useState(null);   //store player data
  const [playerItem, setPlayerItem] = useState({
    player_season_id: null,
    tournament_type: null,
    item: {}
  });   //store player data


  //open player card modal
  const openPlayerCard = (item, idx) => {
    setShowPlayerCard(true)
    setPlayerIdx(idx)
    setPlayerItem((prev => ({
      player_season_id: item.season_prop_id,
      tournament_type: item.tournament_type || '',
      item
    })))
  }


  const { selectedPayout, payout } = master

  // Method(s)
  const getPayout = (data) => {
    return _filter(data, obj => Number(obj.picks) == propsList.length)[0] || {}
  }

  const getUserLineup = () => {
    WSManager.Rest(API.GET_USER_LINEUP, {
      "user_team_id": user_team_id,
      ...(_isEmpty(Utilities.getPropsIds(AppSelectedSport)) ? { is_props: 1 } : {})
    }).then(({ response_code, data, ...res }) => {
      if (response_code == WSC.successCode) {
        let _pl = _Map(data.lineup, obj => {
          return { pid: obj.season_prop_id, type: obj.type }
        })
        setPropsList(data.lineup)
        setPL(_pl)
        ls.set('picksList', data.lineup)
        if (_isEmpty(Utilities.getPropsIds(AppSelectedSport))) {
          setPropsIds(data.props)
          Utilities.setPropsIds(data.props, AppSelectedSport)
        }
      }
    });
  }

  const getPayoutMaster = () => {
    if (_isEmpty(Utilities.getPayoutMdta())) {
      WSManager.Rest(API.GET_PAYOUT_MASTER_DATA, {
        ...(_isEmpty(Utilities.getPropsIds(AppSelectedSport)) ? { sports_id: AppSelectedSport } : {})
      }).then(({ response_code, data, ...res }) => {
        if (response_code == WSC.successCode) {
          setMaster((prev) => ({
            ...prev,
            payout: data.payout,
            user_setting: data.user_setting,
            selectedPayout: getPayout(data.payout),
            picksRange: data.picks_range
          }))
          if (user_team_id && !state.apicall) {
            getUserLineup()
          }
        }
        setPosting(false)
      });
    }
    else {
      setMaster((prev) => ({
        ...prev,
        payout: Utilities.getPayoutMdta().payout,
        user_setting: Utilities.getPayoutMdta().user_setting,
        selectedPayout: getPayout(Utilities.getPayoutMdta().payout),
        picksRange: Utilities.getPayoutMdta().picks_range
      }))
      if (user_team_id && !state.apicall) {
        getUserLineup()
      }
      setPosting(false)
    }
  }



  const JoinGameApiCall = (dataFromConfirmPopUp) => {
    var currentEntryFee = 0;
    currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;

    if (
      (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2 && (parseInt(currentEntryFee) <= parseInt(dataFromConfirmPopUp.balanceAccToMaxPercent))) ||
      (dataFromConfirmPopUp.FixturedContestItem.currency_type != 2 && (parseFloat(currentEntryFee) <= parseFloat(dataFromConfirmPopUp.balanceAccToMaxPercent)))
    ) {
      // this.CallJoinGameApi(dataFromConfirmPopUp);
      saveTeamDetailing()
    }
    else {
      if (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2) {


        if (Utilities.getMasterData().allow_buy_coin == 1) {
          WSManager.setFromConfirmPopupAddFunds(true);
          WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
          WSManager.setPaymentCalledFrom("ContestListing")
          props.history.push({
            pathname: '/buy-coins', params: {
              "payout_type": selectedPayout.payout_type,
              "currency_type": allow_props.coins == 1 ? "2" : "1",
              "entry_fee": form.entry_fee,
              "pl": pl,
              "team_name": `${propsList.length} pick entry`,
              "sports_id": AppSelectedSport,
              ...(user_team_id ? { "user_team_id": user_team_id } : {})
            }, contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'contestList', isProps: 'isProps' }
          });

        }
        else {
          props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow', isProps: 'isProps' } })
        }
      }

      else {
        WSManager.setFromConfirmPopupAddFunds(true);
        WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
        WSManager.setPaymentCalledFrom("ContestListing")
        props.history.push({
          pathname: '/add-funds',
          params: {
            "payout_type": selectedPayout.payout_type,
            "currency_type": allow_props.coins == 1 ? "2" : "1",
            "entry_fee": form.entry_fee,
            "pl": pl,
            "team_name": `${propsList.length} pick entry`,
            "sports_id": AppSelectedSport,
            ...(user_team_id ? { "user_team_id": user_team_id } : {})
          },
          contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { amountToAdd: dataFromConfirmPopUp.AmountToAdd, isProps: 'isProps' }
        });
      }
    }
  }



  const SaveTeam = (dataFromConfirmPopUp) => {
    if (dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1) {
      JoinGameApiCall(dataFromConfirmPopUp)
    } else {
      JoinGameApiCall(dataFromConfirmPopUp)
    }
    ls.set('picksList', [])
  }

  const saveTeamDetailing = (val) => {
    const params = {
      "payout_type": selectedPayout.payout_type,
      "currency_type": allow_props.coins == 1 ? "2" : "1",
      "entry_fee": form.entry_fee,
      "pl": pl,
      "team_name": `${propsList.length} pick entry`,
      "sports_id": AppSelectedSport,
      ...(user_team_id ? { "user_team_id": user_team_id } : {})
    }
    WSManager.Rest(API.SAVE_TEAM, params).then(({ response_code, message, ...res }) => {
      if (response_code == WSC.successCode) {
        Utilities.showToast(message, 3000);
        if (val == 0) {
          props.history.push('/my-contests')
        }
        else {
          setShowConfirmation(false)
          setShowThankYouModal(true)
        }
        ls.set('picksList', [])
      }
    });
  }


  const toggleHandle = (item, type) => {
    if (user_team_id) {
      setEntryChanges(true)
    }
    const _idx = pl.findIndex((obj) => obj.pid === item.season_prop_id);
    if (_idx === -1) {
      setPL((prevData) => [...prevData, { pid: item.season_prop_id, type }]);
    } else {
      setPL((prevData) => prevData.filter((obj, index) => index !== _idx));
      setPL((prevData) => [...prevData, { pid: item.season_prop_id, type }]);
    }

    const _propsList = _Map(propsList, obj => {
      if (obj.season_prop_id === item.season_prop_id) {
        obj.type = type
      }
      return obj
    })
    setPropsList(_propsList)
  }

  const removePicks = (item) => {
    if (propsList.length == 1 && !user_team_id) {
      props.history.push('/')
      ls.set('picksList', [])
    }
    else {
      setPropsList((prevData) => prevData.filter((obj) => obj.season_prop_id != item.season_prop_id));
      setPL((prevData) => prevData.filter((obj) => obj.pid != item.season_prop_id));
    }
  }
  const handleKeyPress = (event) => {
    const keyCode = event.keyCode || event.which;
    const allowedKeys = [8, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57]; // Allowed keys: 0-9, Backspace
    if (!allowedKeys.includes(keyCode)) {
      event.preventDefault();
    }
  };

  const formHandler = (e) => {
    const { max_bet, min_bet } = allow_props

    let value = e.target.value
    // const value = e.target.value.replace(/\D/g, "");
    setEntryFeeChanges(user_team_id && Number(state.team_details.entry_fee) != value)
    setForm((prev) => ({
      ...prev,
      entry_fee: value,
      isInvalid: Number(value) > max_bet || Number(value) < min_bet
    }))

  }

  const clearAll = () => {
    if (user_team_id) {
      setPropsList([]);
    } else {
      ls.set('picksList', [])
      props.history.push('/lobby')
    }
  }

  const showDownloadApp = () => {
    setShowConfirmation(false)
    setShowDAM(true)
  }

  const hideDownloadApp = () => {
    setShowDAM(false)
  }

  // Lifecycle(s)
  useEffect(() => {
    getPayoutMaster()
    return () => { }
  }, [])

  useEffect(() => {
    const { selectedPayout } = master
    setWinings(Number(parseFloat(selectedPayout.points * form.entry_fee || 0).toFixed(2)))
    return () => { }
  }, [form, master])

  useEffect(() => {
    setMaster((prev) => ({
      ...prev,
      selectedPayout: getPayout(payout)
    }))
    ls.set('picksList', propsList)
    setClearConfirm(false)
    return () => { }
  }, [propsList, pl])

  useEffect(() => {
    let _pl = _Map(propsList, obj => {
      return { pid: obj.season_prop_id, type: obj.type }
    })
    setPL(_pl)
    return () => { }
  }, [propsList])


  const ConfirmPropsObj = {
    ...allow_props,
    team_name: `${propsList.length} pick entry`,
  }

  const isPlLength = () => {
    const _pl = _filter(pl, o => o.type)
    return _pl.length
  }

  const remainingAmountCal = (val) => {
    setRefundModal(false)
    getUserBalance().then((responseJson) => {
      if (responseJson.response_code == WSC.successCode) {
        setUserBal(responseJson.data.user_balance)
        saveTeamDetailing(val)
      }
    })
  }
  /* 
  More/Less &&	Entry	|| More/Less	&& Entry	
  y	x	x	x	Redirect + toast
  y	 Y+	x	Y+	Confirm popup + Toast
  y	 Y-	x	Y-	Refund Popup + Toast
  */

  const submitUpdate = () => {
    if (master.user_setting.status == "0") {
      saveTeamDetailing()
    } else {
      if (user_team_id) {
        switch (true) {
          case (isEntryChanges && !isEntryFeeChanges) || (!isEntryChanges && !isEntryFeeChanges):
            saveTeamDetailing(0)
            break;
          case (isEntryChanges && isEntryFeeChanges && Number(state.team_details.entry_fee) < form.entry_fee)
            || (!isEntryChanges && isEntryFeeChanges && Number(state.team_details.entry_fee) < form.entry_fee):
            setShowConfirmation(true)
            break;
          case (isEntryChanges && isEntryFeeChanges && Number(state.team_details.entry_fee) > form.entry_fee) || (!isEntryChanges && isEntryFeeChanges && Number(state.team_details.entry_fee) > form.entry_fee):
            setRefundModal(true)
            break;
        }
      } else {
        setShowConfirmation(true)
      }
    }
  }

  let range = master.picksRange.split("-")

  return (

    <div className='web-container web-container-fixed props-web-container'>

      <CustomHeader HeaderOption={HeaderOption} {...props} />

      <div className='props-my-entry'>
        <div className='entry-heading'>
          <div>
            <span className='curr-entry'>{CommonLabels.CURRENT_ENTRY}</span>
            {
              !_isEmpty(propsList) &&
              <span className='players'>{propsList.length} {AppLabels.PLAYERS}</span>
            }
          </div>

          <div className={`${!_isEmpty(propsList) ? 'clear' : 'blur-clear'} `} onClick={() => !_isEmpty(propsList) ? setClearConfirm(true) : ''}>
            {CommonLabels.CLEAR_ALL}
          </div>
        </div>

        <div className='pick-player mt-4 mb-4'>
          <i className="icon-star-rounded" />
          <div>
            {CommonLabels.PICK_TEXT} {' '} {master.picksRange} {' '} {CommonLabels.PICK_TWO_TO_TEN}
          </div>
        </div>
        <div className="entry-list">
          {
            _Map(propsList, (item, idx) => {
              return (
                <PropsEntry {...{ key: idx, idx, item, toggleHandle, removePicks, prop_name: getPropsName(propsIDs, item.prop_id), openPlayerCard }} />
              )
            })
          }
          {
            user_team_id && propsList.length < range[1] &&
            <a className="addplayer-btn" onClick={() => props.history.push({ pathname: `/props-fantasy/add-player/${user_team_id}`, state: { team_details: state.team_details } })}>{CommonLabels.ADD_PLAYERS}</a>
          }
        </div>


        <div {...{ className: `props-myentry-bottom ${posting || isPlLength() != selectedPayout.picks ? 'not-allowed' : ''}` }}>
          {/* <div {...{ className: `props-myentry-bottom ${posting || pl.length != selectedPayout.picks ? 'not-allowed' : ''}` }}> */}
          <div className='payouts'>
            <div className='curr-entry payout'>{CommonLabels.PAYOUTS}
              {/* <i className='icon-ic-info'></i> */}

            </div>
            <div className='avl-payouts'>
              (
              {CommonLabels.POWER_PLAY_PAYOUTS_ARE}{" "}
              {master.payout.map((obj, idx) => {
                return <span>{obj.picks}{master.payout.length != idx + 1 && ','}</span>
              })})
            </div>
            {
              allow_props.powerplay == "1" &&
              <div className='payout-block'>
                <h6 className='heading'>{CommonLabels.POWER_PLAY}</h6>
                <h6 className='desc'>{CommonLabels.YOU_MUST_HIT} {selectedPayout.correct || '--'} {CommonLabels.OUT_OF} {selectedPayout.picks || '--'} {CommonLabels.IN_THE_ENTRY}</h6>
                <h6>
                  <span className='correct'>{selectedPayout.correct || '--'} {CommonLabels.CORRECT_PAYS}</span>
                  <span className='points'>{Number(parseFloat(selectedPayout.points || 0).toFixed(2)) || '--'}X</span>
                </h6>
              </div>
            }
          </div>
          <div className='entry-to-win'>
            <div {...{ className: `input-block ${form.isInvalid ? 'error' : ''}` }}>
              <div className='entry'>
                {AppLabels.ENTRY}
              </div>
              {/* .curreny-sym */}
              <div className='entry-form-input'>
                <div className="curreny-type">
                  <img className="coin-img" src={Images.IC_COIN} alt="" />
                </div>
                <input
                  type='number'
                  className='input-num'
                  value={parseInt(form.entry_fee)}
                  onChange={formHandler}
                  onKeyPress={handleKeyPress}
                />
              </div>
            </div>
            <div className='to-win'>
              <h6 className='to-win'>{CommonLabels.TO_WIN}</h6>
              <h6>
                <img className="coin-img" src={Images.IC_COIN} alt="" />
                <span className='val'>{winings || '--'}</span>
              </h6>
            </div>
          </div>
        </div>
      </div>

      <button className='propsentry-footer'
        onClick={() => submitUpdate()


        } disabled={posting || pl.length != selectedPayout.picks || form.isInvalid || !form.entry_fee || propsList.filter((obj) => !obj.type).length != 0}>
        {user_team_id ? AppLabels.UPDATE : AppLabels.CONTEST_JOIN_NOW}
      </button>


      {showConfirmation && (
        <ConfirmationPopup
          IsConfirmationPopupShow={showConfirmation}
          IsConfirmationPopupHide={() => setShowConfirmation(false)}
          ConfirmationClickEvent={SaveTeam}
          fromContestListingScreen={false}
          TeamListData={{}}
          TotalTeam={{}}
          FixturedContest={{
            currency_type: allow_props.coins == 1 ? 2 : 1,
            entry_fee: form.entry_fee
          }}
          CreateTeamClickEvent={() => { }}
          lobbyDataToPopup={{}}
          createdLineUp={""}
          selectedLineUps={""}
          // showDownloadApp={() => { }}
          propsData={ConfirmPropsObj}
          showDownloadApp={showDownloadApp}
          remaining_coin={state && state.team_details && state.team_details.entry_fee ? form.entry_fee - state.team_details.entry_fee : form.entry_fee}
          isProps
        />
      )}

      {
        showThankYouModal &&
        <Thankyou
          ThankyouModalShow={() => setShowThankYouModal(true)}
          ThankYouModalHide={() => setShowThankYouModal(false)}
          goToLobbyClickEvent={() => props.history.push('/lobby')}
          seeMyContestEvent={() => props.history.push('/my-contests')}
          isProps
          user_team_id={user_team_id}
        />
      }
      {
        showDAM &&
        <DownloadAppBuyCoinModal
          hideM={hideDownloadApp}
        />
      }
      <ClearConfirmModal {...{
        isShow: clearConfirmShow,
        confirm: clearAll,
        defined: () => setClearConfirm(false)
      }} />
      {
        user_team_id &&
        <RefundModal {...{
          isShow: refundModalShow,
          confirm: () => state.team_details.entry_fee - form.entry_fee >= 0 ? remainingAmountCal(0) : [setRefundModal(false)
            ,
          setShowConfirmation(true)
          ],
          defined: () => setRefundModal(false),
          remaining_coin: state.team_details.entry_fee - form.entry_fee,
          allow_props
        }} />
      }

      {
        showPlayerCard &&
        <PropsPlayerCard
          seletcedProps={propsList}
          // handleChecked={handleChecked}
          list={propsList}
          player_idx={player_idx}
          player_season_id={playerItem.player_season_id}
          tournament_type={playerItem.tournament_type}
          item={playerItem.item}
          onHide={() => [setShowPlayerCard(false), setPlayerIdx(null)]}
          // PropsEntryStripeData={PropsEntryStripeData}
          propsList={propsIDs}
          hideStrip={true}
        />
      }
    </div>
  );
};

export default PropsMyEntry;
