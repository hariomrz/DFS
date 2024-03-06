import React, { useEffect, useState } from 'react';
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { BannerRedirectLink, Utilities, _Map, _filter, _isEmpty, _isUndefined, getPropsName } from '../../../Utilities/Utilities';
import { CommonLabels } from "../../../helper/AppLabels";
import { FormGroup, Tab } from 'react-bootstrap';
import ScrollSportstab from '../../TourLeaderboard/ScrollSportstab';
import PropsCard from '../Common/PropsCard/PropsCard';
import PropsEntryStripe from '../Common/PropsEntryStripe/PropsEntryStripe';
import WSManager from '../../../WSHelper/WSManager';
import PropsPlayerCard from '../Common/PropsModal/PropsPlayerCard';
import { AppSelectedSport } from '../../../helper/Constants';
import PropsRulesScoring from '../Common/PropsModal/PropsRulesScoring';
import PropsHTPModal from '../Common/PropsModal/PropsHTPModal';
import * as Constants from "../../../helper/Constants";
import { getLobbyBanner } from '../../../WSHelper/WSCallings';
import CustomHeader from '../../../components/CustomHeader';
import PQueue from "p-queue/dist";
import { LobbyBannerSlider } from '../../CustomComponent';
import ls from 'local-storage';
import { useLocation, useParams } from 'react-router-dom';
import PropsLobbyFilter from '../Common/PropsModal/PropsLobbyFilter';


const API = {
  GET_USER_CONTEST: WSC.propsURL + "props/lobby/get_lobby_player_list",
  GET_PLAYER_CARD: WSC.propsURL + "props/lobby/get_player_card_stats",
  GET_PAYOUT_MASTER_DATA: WSC.propsURL + "props/lobby/get_payout_master_data",
}
var bannerData = {}


const PropsLobby = ({ is_add_screen = false, isSportIdListener, ...props }) => {

  const { user_team_id } = useParams()
  const { state } = useLocation()
  const [seletcedProps, setSeletcedProps] = useState(ls.get('picksList') || []);
  const [playerList, setPlayerList] = useState(0);   //store player data
  const [propsList, setPropsList] = useState(Utilities.getPropsIds(AppSelectedSport));   // store props data

  const [activeTab, setActiveTab] = useState(null);

  const [searchField, setSearchField] = useState('');
  const [searchVal, setSearchVal] = useState('');
  const [restricted, setRestricted] = useState(false);

  const [showRulesModal, setShowRulesModal] = useState(false); //rules modal
  const [showHtp, setHtp] = useState(false); //HTP modal
  const [filterDataLeague, setFilterDataLeague] = useState([])
  const [bannerList, setBannerList] = useState([])
  const [showRFHTPModal, setShowRFHTPModal] = useState(false)

  const [filteredData, setFilteredData] = useState(false)
  const [filteredList, setFilteredList] = useState([])
  const [selectedFilteredArray, setSelectedFilteredArray] = useState([])
  const [playerListAll, setPlayerListAll] = useState([])
  const [payoutData, setPayoutData] = useState([])
  const [pickRange, setPickRange] = useState('')

  const [player_idx, setPlayerIdx] = useState(null);   //store player data
  const [playerItem, setPlayerItem] = useState({
    player_season_id: null,
    item: {}
  });   //store player data
  const [showPlayerCard, setShowPlayerCard] = useState(false);   //store player data
  const [reloadVal, setReloadVal] = useState('')
  const [filteredLeague, setFilteredLeague] = useState([])
  const [filterbyLeague, setFilterbyLeague] = useState([])
  const [applyFilter, setApplyFilter] = useState(0)

  const sequentiallyCalling = () => {
    const queue = new PQueue({ concurrency: 1 });
    const myPromises = [
      () => new Promise(resolve => {
        if (is_add_screen) {
          resolve()
        } else {
          getBannerList(resolve);
        }
      })
    ];
    queue.addAll(myPromises);
  }

  // Method(s)
  //rules modal
  const checkOldUrl = () => {
    let url = window.location.href;
    let sports = '#' + Utilities.getSelectedSportsForUrl();
    if (!url.includes(sports)) {
      url = url + sports
    }
    if (!url.includes('#props')) {
      url = url + "#props";
    }
    window.history.replaceState("", "", url);
  }

  const handleRules = () => {
    setShowRulesModal(true)
  }

  const closeRules = () => {
    setShowRulesModal(false)
  }

  //HTP modal
  const handleHTP = () => {
    setHtp(true)
  }

  const closeHTP = () => {
    setHtp(false)
  }

  //open player card modal
  const openPlayerCard = (item, idx) => {
    setShowPlayerCard(true)
    setPlayerIdx(idx)
    console.log("item", item)
    setPlayerItem((prev => ({
      player_season_id: item.season_prop_id,
      tournament_type: item.tournament_type || '',
      item
    })))
    // let data = {
    //   id: id,
    //   curridx: idx
    // }
    // WSManager.Rest(API.GET_PLAYER_CARD, { 'season_prop_id': id }).then((responseJson) => {
    //   if (responseJson.response_code == WSC.successCode) {
    //   }
    // });
  }





  const handleChecked = (item) => {
    const _idx = seletcedProps.findIndex((obj) => obj.season_prop_id === item.season_prop_id);
    if (_idx === -1) {
      if (restricted) {
        Utilities.showToast(CommonLabels.EXCEED_PICKS, 3000)
      } else {
        item['is_selected'] = 1
        setSeletcedProps((prevData) => [...prevData, item]);
      }
    } else {
      setSeletcedProps((prevData) => prevData.filter((obj, index) => index !== _idx));
    }
  }

  const onTabClick = (e, index) => {
    setActiveTab(e.prop_id);
  };

  const getUserFixtureTeams = () => {
    WSManager.Rest(API.GET_USER_CONTEST, { 'sports_id': AppSelectedSport }).then((responseJson) => {
      if (responseJson.response_code == WSC.successCode) {
        let prop_id = responseJson.data.props[0].prop_id;
        let props = responseJson && responseJson.data && responseJson.data.picks.filter((obj) => obj.prop_id == prop_id)
        // setPlayerList(props)
        setSearchField(props)
        setPropsList(responseJson.data.props)
        setPlayerListAll(responseJson.data.picks)
        Utilities.setPropsIds(responseJson.data.props, AppSelectedSport)
        setActiveTab(prop_id)

        let temp = []
        responseJson.data.picks.length > 0 && responseJson.data.picks.map((item) => {

          if (!temp.some(obj => obj.season_id == item.season_id)) {
            temp.push({
              match: item.home + ' vs ' + item.away,
              season_id: item.season_id,
              date: item.scheduled_date
            })
          }
        })

        const uniqueArray = responseJson.data.picks.filter((value, index, self) =>
          index === self.findIndex((t) => (
            t.league_id === value.league_id && t.league_name === value.league_name
          ))
        )

        setFilteredList(temp)
        setFilteredLeague(uniqueArray)
      }
    });
  }



  const payoutMasterData = () => {
    if (_isEmpty(Utilities.getPayoutMdta())) {
      WSManager.Rest(API.GET_PAYOUT_MASTER_DATA).then((responseJson) => {
        if (responseJson.response_code == WSC.successCode) {
          setPayoutData(responseJson.data.payout)
          setPickRange(responseJson.data.picks_range)
          Utilities.setPayoutMdta(responseJson.data)
        }
      });
    }
    else {
      setPayoutData(Utilities.getPayoutMdta().payout)
      setPickRange(Utilities.getPayoutMdta().picks_range)
    }
  }


  const parseBannerData = (bdata) => {
    let refData = '';
    let temp = [];
    _Map(bdata, (item, idx) => {
      if (item.game_type_id == 0 || WSManager.getPickedGameTypeID() == item.game_type_id) {
        if (item.banner_type_id == 2) {
          refData = item;
        }
        if (item.banner_type_id == 1) {
          let dateObj = Utilities.getUtcToLocal(item.schedule_date)
          if (Utilities.minuteDiffValue({ date: dateObj }) < 0) {
            temp.push(item);
          }
        }
        else {
          temp.push(item);
        }
      }
    })
    if (refData) {
      setTimeout(() => {
        CustomHeader.showRCM(refData);
      }, 200);
    }
    setBannerList(temp)
  }



  const getBannerList = (resolve = () => { }) => {
    let sports_id = Constants.AppSelectedSport;

    if (sports_id == null)
      return;
    if (bannerData[sports_id]) {
      parseBannerData(bannerData[sports_id])
    } else {
      setTimeout(async () => {
        let param = {
          "sports_id": sports_id
        }
        var api_response_data = await getLobbyBanner(param);
        if (api_response_data && param.sports_id == Constants.AppSelectedSport) {
          resolve()
          bannerData[sports_id] = api_response_data;
          parseBannerData(api_response_data)
        }
      }, 1500);
    }
  }

  const handleSearch = (e, val) => {
    setSearchVal(e.target.value)
    // if (e && e.target && e.target.value && e.target.value.length > 0) {
    //   let searchValue = playerList.filter((obj) => {
    //     let f_name = (obj.full_name.toLowerCase())
    //     if (f_name.includes(e.target.value.toLowerCase())) {
    //       return obj
    //     }
    //   })


    //   setPlayerList(searchValue)
    // }
    // else {
    //   setPlayerList(searchField)
    // }

  }

  // const debouncedOnChange = debounce(handleSearch, 3000)

  const redirectLink = (result, isRFBanner) => {
    if (isRFBanner) {
      showRFHTPModalFn()
    }
    else {
      if (WSManager.loggedIn()) {
        BannerRedirectLink(result, props)
      }
      else {
        props.history.push({ pathname: '/signup' })
      }
    }
  }

  const showRFHTPModalFn = () => {
    setShowRFHTPModal(true)
  }
  const hideRFHTPModalFn = () => {
    setShowRFHTPModal(false)
  }

  const getFilteredData = () => {
    setFilteredData(true)
  }

  const hideFilteredData = () => {
    setFilteredData(false)
  }

  const filterDatabyMatch = (data) => {
    setFilteredData(false)
    if (data.length == 0) return;
    setSelectedFilteredArray(data)
    setFilterbyLeague([])
    setApplyFilter(num => num + 1)

  }

  const filterDatabyLeague = (data) => {
    setFilteredData(false)
    if (data.length == 0) return;
    setSelectedFilteredArray([])
    setFilterbyLeague(data)
    setApplyFilter(num => num + 1)
  }

  const submitFilter = () => {
    setSelectedFilteredArray([])
    setFilterbyLeague([])
    setApplyFilter(num => num + 1)
    setFilteredData(false)
  }

  //  Lifecycle(s)

  useEffect(() => {
    let list = ls.get('picksList') ? ls.get('picksList') : []
    if (list.length == 0) {
      setSeletcedProps([])
    }
    setSelectedFilteredArray([])
    setFilterbyLeague([])
    setSearchVal('')
    if (!window.location.href.includes('my-contests')) {
      getUserFixtureTeams();
    }
    sequentiallyCalling()
  }, [AppSelectedSport])


  useEffect(() => {
    if (isSportIdListener != null) {
      ls.set('picksList', [])
      setSeletcedProps([])
    }
  }, [isSportIdListener])

  // useEffect(() => {
  //   if(!activeTab) return;
  //   const filterddata = _filter(playerListAll, obj => {
  //     return (obj.prop_id == activeTab) &&
  //       (searchVal != '' ? obj.full_name.toLowerCase().includes(searchVal.toLowerCase()) : true) &&
  //       (!_isEmpty(selectedFilteredArray) ? selectedFilteredArray.includes(obj.season_id) : true)
  //   })
  //   console.log('Filter Apply');
  //   setPlayerList(filterddata)    
  // }, [activeTab])


  useEffect(() => {
    if (applyFilter != 0 || activeTab || searchVal != '') {
      const filterddata = _filter(playerListAll, obj => {
        return (obj.prop_id == activeTab) &&
          (searchVal != '' ? obj.full_name.toLowerCase().includes(searchVal.toLowerCase()) : true) &&
          (!_isEmpty(selectedFilteredArray) ? selectedFilteredArray.includes(obj.season_id) : true) &&
          (!_isEmpty(filterbyLeague) ? filterbyLeague.includes(obj.league_id) : true)
      })
      setPlayerList(filterddata)
    }
  }, [applyFilter, activeTab, searchVal])

  useEffect(() => {
    let range = pickRange.split("-")
    setRestricted(seletcedProps.length == range[1])
    ls.set('picksList', seletcedProps)
  }, [seletcedProps, pickRange])

  useEffect(() => {
    checkOldUrl();
    payoutMasterData()
    ls.remove('in_params')
    ls.remove('isProps')
    let list = ls.get('picksList') ? ls.get('picksList') : []
    if (!user_team_id && list.length == 0) {
      ls.set('picksList', [])
    }
  }, [])

  useEffect(() => {
    return () => {
      ls.set('picksList', [])
    }
  }, [])


  let bannerLength = bannerList && bannerList.length

  const PropsEntryStripeData = {
    count: seletcedProps.length,
    list: propsList,
    is_add_screen,
    ...(user_team_id ? { team_details: state.team_details } : {}),
    payoutData: payoutData,
    pickRange: pickRange
  }

  const {
    allow_props
  } = Utilities.getMasterData()



  return (

    <React.Fragment>
      <div className='props-web-container'>
        <i className={`icon-filter ${props.isFrom == "add_player" ? "add-filter" : "props-filter"} `} onClick={() => getFilteredData()}></i>

        {selectedFilteredArray.length > 0 ? <span className={`filteractive ${props.isFrom == "add_player" ? "add-filter-player" : ""} `}></span> : ''}
        {filterbyLeague.length > 0 ? <span className={`filteractive ${props.isFrom == "add_player" ? "add-filter-player" : ""} `}></span> : ''}
        {
          !is_add_screen && bannerLength > 0 &&
          <div className={bannerLength > 0 ? 'banner-v animation mb-2' : 'banner-v mb-2'}>
            {
              bannerLength > 0 && <LobbyBannerSlider BannerList={bannerList} redirectLink={redirectLink.bind(this)} />
            }
          </div>
        }

        {
          !is_add_screen &&
          <div className='props-header'>
            <div className='props-head-bg'>
              {CommonLabels.PROPS}
            </div>
            <div className='props-head-rules'>
              <span className='props-htp' onClick={() => handleHTP()}>{AppLabels.HOW_TO_PLAY}? </span> |
              <span className='props-rules' onClick={() => handleRules()}>{AppLabels.RULES}</span>
            </div>
          </div>
        }

        <ScrollSportstab
          tabsContainerClassName="sp-tb-scroll-container"
          activeTab={activeTab}
          {...props}
        >
          {({ Tab }) => {
            return (
              _Map(propsList, (item, idx) => {
                return (
                  <Tab className="sp-item"
                    onClick={() => onTabClick(item, idx)}
                  >
                    <span {...{ className: `props-name ${activeTab == item.prop_id ? 'active' : ''}` }}>
                      {item.name}
                    </span>
                  </Tab>
                );
              })
            );
          }}
        </ScrollSportstab>

        <div className='pick-player'>
          <i className="icon-star-rounded" />
          <div>
            {CommonLabels.PICK_TEXT} {' '} {pickRange} {' '} {CommonLabels.PICK_TWO_TO_TEN}
          </div>
        </div>


        <label className='search-box'>
          <i className="icon-search-bg"></i>
          <FormGroup
            className={`input-label-center input-transparent`}
            controlId="formBasicText">
            <input
              placeholder={CommonLabels.SEARCH}
              type="text"
              autoComplete='off'
              id='SearchVal'
              name='SearchVal'
              className='search-input'
              value={searchVal}
              onChange={(e) => handleSearch(e)}
            />
          </FormGroup>
        </label>
        <div className={`props-jersey ${seletcedProps.length > 0 ? 'active' : ''}`}>
          {!_isEmpty(playerList) && playerList.map((item, idx) => {
            return (
              <PropsCard {...{
                ...props,
                idx,
                handleChecked,
                openPlayerCard,
                item,
                seletcedProps,
                pickRange,
                prop_name: getPropsName(propsList, item.prop_id)
              }}
              />
            )
          })
          }
          {
            searchVal != "" && _isEmpty(playerList) && <div className='props-no-data'>{CommonLabels.PROPS_NO_SEARCH1}<br />{CommonLabels.PROPS_NO_SEARCH2}</div>
          }
          {
            searchVal == "" && _isEmpty(playerList) && <div className='props-no-data'>{AppLabels.NO_TRANSACTION_DATA}</div>
          }



        </div>
      </div>
      {!_isEmpty(seletcedProps) && <PropsEntryStripe {...PropsEntryStripeData} />}


      {
        showRulesModal &&
        <PropsRulesScoring
          showRulesModal={showRulesModal}
          closeRules={closeRules}
        />
      }

      {
        showHtp &&
        <PropsHTPModal
          showHtp={showHtp}
          closeHTP={closeHTP}
        />
      }

      {
        <PropsLobbyFilter {...{
          filteredData,
          hideFilteredData,
          filteredList,
          filteredLeague,
          filterDatabyMatch,
          submitFilter,
          selectedFilteredArray,
          filterDatabyLeague,
          filterbyLeague
        }}
        />
      }



      {
        showPlayerCard &&
        <PropsPlayerCard
          seletcedProps={seletcedProps}
          handleChecked={handleChecked}
          list={playerList}
          player_idx={player_idx}
          player_season_id={playerItem.player_season_id}
          tournament_type={playerItem.tournament_type}
          item={playerItem.item}
          onHide={() => [setShowPlayerCard(false), setPlayerIdx(null)]}
          PropsEntryStripeData={PropsEntryStripeData}
          propsList={propsList}
        />
      }

    </React.Fragment >

  )
};

export default PropsLobby;
