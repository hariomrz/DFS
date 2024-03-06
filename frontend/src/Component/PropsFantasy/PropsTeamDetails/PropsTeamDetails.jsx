import React, { useEffect, useState } from 'react';
import CustomHeader from '../../../components/CustomHeader';
import PropsEntry from '../Common/PropsEntry/PropsEntry';
import * as AppLabels from "../../../helper/AppLabels";
import { CommonLabels } from "../../../helper/AppLabels";
import WSManager from '../../../WSHelper/WSManager';
import * as WSC from "../../../WSHelper/WSConstants";
import { useLocation, useParams } from 'react-router-dom';
import { Utilities, _Map, _filter, _isEmpty, getPropsName, isDateTimePast } from '../../../Utilities/Utilities';
import { AppSelectedSport } from '../../../helper/Constants';
import Images from '../../../components/images';
import { ConfirmationPopup, Thankyou } from '../../../Modals';
import ls from 'local-storage';
import { ClearConfirmModal, RefundModal } from '../Common';

const API = {
  GET_PAYOUT_MASTER_DATA: WSC.propsURL + "props/lobby/get_payout_master_data",
  GET_USER_TEAM_DETAIL: WSC.propsURL + "props/lobby/get_user_team_detail",
}

const PropsTeamDetails = ({ ...props }) => {
  const { user_team_id, props_status } = useParams();
  const [team_detail, setTeamDetail] = useState({});
  const [player_detail, setPlayerDetail] = useState([]);
  const [propsIDs, setPropsIds] = useState(Utilities.getPropsIds(AppSelectedSport))
  const [master, setMaster] = useState({
    payout: [],
    user_setting: {},
    selectedPayout: {}
  })

  const HeaderOption = {
    back: true,
    notification: false,
    hideShadow: true,
    isPrimary: true,
    title: props_status == 1 ? "LIVE" : "COMPLETED",
    redirectTo: props_status == 1 ? "/my-contests?contest=live" : "/my-contests?contest=completed",
    livedot: props_status == 1
  }


  const getPayout = (data, players) => {
    return _filter(data, obj => Number(obj.picks) == players.length)[0] || {}
  }

  const getUserTeamDetail = (user_team_id, props_status, payout) => {
    const params = {
      "user_team_id": user_team_id,
      "status": props_status,
      ...(_isEmpty(Utilities.getPropsIds(AppSelectedSport)) ? { is_props: 1 } : {})
    }
    WSManager.Rest(API.GET_USER_TEAM_DETAIL, params).then(({ response_code, data, ...res }) => {
      if (response_code == WSC.successCode) {
        setTeamDetail(data.team_detail)
        setPlayerDetail(data.player_detail)

        setMaster((prev) => ({
          ...prev,
          selectedPayout: getPayout(payout, data.player_detail)
        }))

        if (_isEmpty(Utilities.getPropsIds(AppSelectedSport))) {
          setPropsIds(data.props)
          Utilities.setPropsIds(data.props, AppSelectedSport)
        }
      }
    });
  }


  const getPayoutMaster = () => {
    if (_isEmpty(Utilities.getPayoutMdta())) {
      WSManager.Rest(API.GET_PAYOUT_MASTER_DATA, {}).then(({ response_code, data, ...res }) => {
        if (response_code == WSC.successCode) {
          setMaster((prev) => ({
            ...prev,
            payout: data.payout,
            user_setting: data.user_setting
          }))
          getUserTeamDetail(user_team_id, props_status, data.payout)
        }
      });
    }
    else {
      setMaster((prev) => ({
        ...prev,
        payout: Utilities.getPayoutMdta().payout,
        user_setting: Utilities.getPayoutMdta().user_setting
      }))
      getUserTeamDetail(user_team_id, props_status, Utilities.getPayoutMdta().payout)
    }
  }

  useEffect(() => {
    getPayoutMaster()
    return () => { }
  }, [user_team_id, props_status])

  const playerStatus = (status) => {
    switch (status) {
      case '1':
        return 'correct'
      case '2':
        return 'wrong'
      case '3':
        return 'notplaying'
      default:
        return ''
    }

  }

  return (
    <div className='web-container web-container-fixed'>
      <CustomHeader HeaderOption={HeaderOption} {...props} />
      <div className='props-my-entry props-my-entry-gap'>
        <div className="ptd-header">
          <div className='title'>{team_detail.team_name}</div>
          <div className="ptc-tags">
            {
              team_detail.payout_type == 2 &&
              <div className="ptctag">{CommonLabels.POWER_PLAY}</div>
            }
            {
              team_detail.payout_type == 1 &&
              <div className="ptctag">{CommonLabels.FLEXPLAY}</div>
            }
          </div>
        </div>

        <div className="ptc-detail-box">
          <div className="ptcd-top">
            <div className="line1">{CommonLabels.POWER_PLAY}</div>
            <div className="line2">{CommonLabels.YOU_MUST_HIT} {master.selectedPayout.correct || '--'} {CommonLabels.OUT_OF} {master.selectedPayout.picks || '--'} {CommonLabels.IN_THE_ENTRY}</div>
            <div className="line3">{master.selectedPayout.correct || '--'} {CommonLabels.CORRECT_PAYS} <span>{Number(parseFloat(master.selectedPayout.points || 0).toFixed(2)) || '--'}X</span></div>
          </div>
          <div className="ptcd-footer">
            <div className="ptcd-left">
              {AppLabels.ENTRY}
              <img className="coin-img" src={Images.IC_COIN} alt="" />
              {parseInt(team_detail.entry_fee)}
            </div>
            <div className="ptcd-right">
              {
                props_status != 1 ?
                  AppLabels.WINNINGS
                  :
                  team_detail.probable_winning <= 0 ? AppLabels.WINNINGS : CommonLabels.PROBABLE_WINNING
              }
              <img className="coin-img" src={Images.IC_COIN} alt="" />
              <div className="winings">{parseInt(props_status == 1 ? team_detail.probable_winning : team_detail.winning)}</div>
            </div>
          </div>
        </div>

        {
          _Map(player_detail, (item, idx) => {
            return (
              <div {...{ key: idx, className: `ptc-player-card ${playerStatus(item.status)}` }}>
                <div className="ptcp-left">
                  <img src={Utilities.playerJersyURL(item.player_image != "" ? item.player_image : item.team_id == item.away_id ? item.away_jersey : item.home_jersey)} className='jimg' />
                </div>
                <div className="ptcp-middle">
                  <div className="name">{item.full_name}</div>
                  <div className="position">{item.team_id == item.away_id ? item.away : item.home} - {item.position}</div>
                  <div className="schedule">{Utilities.getFormatedDateTime(item.scheduled_date, 'ddd, MMM DD hh:mm A')} {' '}
                    vs {item.team_id == item.away_id ? item.home : item.away}</div>
                  <div className="points-props">
                    <span className='point'>{item.projection_points}</span>
                    <span className="props-name">{getPropsName(propsIDs, item.prop_id)}</span>
                  </div>
                </div>
                <div className="ptcp-right">
                  <div className='more-less-block'>
                    <div {...{ className: `toggle-btn ${item.type == 1 ? 'active' : ''}` }}>
                      {AppLabels.MORE}
                    </div>
                    <div {...{ className: `toggle-btn ${item.type == 2 ? 'active' : ''}` }}>
                      {AppLabels.STCK_PS_29}
                    </div>
                  </div>
                  {
                    (item.status == 3) ?
                      <div className="lbl">{CommonLabels.DID_NOT_PLAY}</div>
                      :
                      <>
                        {(isDateTimePast(item.scheduled_date) && (item.status != 0)) &&
                          <div className="lbl">
                            {AppLabels.ACTUAL} {Number(parseFloat(item.score || 0).toFixed(2))}</div>
                        }
                      </>
                    //rounf off
                  }
                </div>
              </div>
            )
          })
        }
      </div>
    </div>
  );
};

export default PropsTeamDetails;
