import React, { useState, useEffect, useMemo } from "react";
import * as WSC from "WSHelper/WSConstants";
import WSManager from "WSHelper/WSManager";
import {
  SelectedFixtureItem,
  FixtureItem,
  EmptyScreen,
} from "OpinionTrade/Components";
import { useHistory, useParams } from "react-router-dom";
import CustomHeader from "components/CustomHeader";
import {
  AppSelectedSport,
  SELECTED_GAMET,
  DARK_THEME_ENABLE,
} from "helper/Constants";
import ls from "local-storage";
import { _Map, _debounce, Utilities, isDesktop } from "Utilities/Utilities";
import HowtoPlayModal from "Modals/HowtoPlayModal/HowtoPlayModal";
import LobbyBanner from "Local/LobbyBanner";
import { getLobbyBanner } from "WSHelper/WSCallings";
import { Helper } from "Local";
import OpinionTradeSucess from "Modals/OpinionTradeSucess";
import OpinionRules from "Modals/OpinionRules";

const { Utils, _, Trans } = Helper;
const API = {
  GET_LOBY_FIXTURE: WSC.oTradeURL + "trade/lobby/get_loby_fixture",
  GET_QUESTION_LIST: WSC.oTradeURL + "trade/lobby/get_question_list",
  GET_MY_ENTRY: WSC.oTradeURL + "trade/lobby/get_my_entry",
};
const Lobby = (props) => {
  const [selectedFixtureList, setSelectedFixtureList] = useState([]);
  const [trade_data,setTradeData] = useState({});
  const [fixtureList, setFixtureList] = useState([]);
  const [myEntriesfixtureList, setMyEntriesfixtureList] = useState([]);
  const [selectedSeasonID, setSeasonID] = useState("-1");
  const [toggle, setToggle] = useState(true);
  const [isLoading, setLoading] = useState(true);
  const [hasMore, setHasMore] = useState(false);
  const [page, setPageNo] = useState(1);
  const [ sports_id ,setSportsID ] = useState(null);
  const [bannerList, setBannerList] = useState([]);
  const [tradeSuccess ,setTradeSuccess] = useState(false)
  const [tradeItem ,setTradeItem] = useState(undefined)
  const [showRules, setShowRules] = useState(false);

  const [headerOption, setHeaderOption] = useState({
    menu: true,
    back: false,
    notification: true,
    filter: false,
    MLogo: true,
    isPrimary: DARK_THEME_ENABLE ? false : true,
  });
  let history = useHistory();
  const hideRulesScoring = () => setShowRules(false);

  useEffect(()=>{
    setSportsID(AppSelectedSport)
    getBannerList(AppSelectedSport)
    ls.remove("fromOpinion");

  },[AppSelectedSport])
  const get_question_list = () => {
    let params = {
      sports_id: sports_id,
      season_id: selectedSeasonID,
      limit: "20",
      page: 1,
    };
    WSManager.Rest(API.GET_QUESTION_LIST, params).then(
      ({ response_code, data, ...res }) => {
        if (response_code == WSC.successCode) {
          setLoading(false);
          setHasMore(data.question.length < parseInt(data.total));
          if(data.trade_data){
            setTradeData({...data.trade_data})
          }else{
            setTradeData({})
          }
          setSelectedFixtureList([]);
          setSelectedFixtureList(data.question);

        }
      }
    );
  };
  const get_entry_list = () => {
    let params = {
      sports_id: sports_id,
      season_id: selectedSeasonID,
      limit: "20",
      page: 1,
    };
    WSManager.Rest(API.GET_MY_ENTRY, params).then(
      ({ response_code, data, ...res }) => {
        if (response_code == WSC.successCode) {
          setLoading(false);
          setHasMore(data.answer.length < parseInt(data.total));
          setSelectedFixtureList(data.answer);
          setMyEntriesfixtureList(data.match);
          setTradeData({})
        }
      }
    );
  };


  const getBannerList = async (sportsId) => {
      let param = {
        sports_id: sportsId,
      };
      var api_response_data = await getLobbyBanner(param);
      if (api_response_data) {
        parseBannerData(api_response_data);
      }
  };

  const parseBannerData = (bdata) => {
    let refData = "";
    let temp = [];
    _.map(bdata, (item, idx) => {
      if (
        item.game_type_id == 0 ||
        WSManager.getPickedGameTypeID() == item.game_type_id
      ) {
        if (item.banner_type_id == 2) {
          refData = item;
        }
        if (item.banner_type_id == 1) {
          let dateObj = Utils.getUtcToLocal(item.schedule_date);
          if (Utilities.minuteDiffValue({ date: dateObj }) < 0) {
            temp.push(item);
          }
        } else {
          temp.push(item);
        }
      }
    });
    setBannerList(temp);
  };

 

  
  useEffect(() => {
    if (selectedSeasonID != "-1" && sports_id != null) {
      setToggle(true);
      setLoading(true);
      setSelectedFixtureList([]);
      setPageNo(1);
      setHasMore(false);
      get_question_list();
    }
  }, [selectedSeasonID, sports_id]);
  useEffect(() => {
    if (page > 1) {
      if (toggle) {
        let params = {
          sports_id: sports_id,
          season_id: selectedSeasonID,
          limit: "20",
          page: page,
        };
        WSManager.Rest(API.GET_QUESTION_LIST, params).then(
          ({ response_code, data, ...res }) => {
            if (response_code == WSC.successCode) {
              setHasMore(
                [...selectedFixtureList, ...data.question].length <
                  parseInt(data.total)
              );
              setSelectedFixtureList([
                ...selectedFixtureList,
                ...data.question,
              ]);
              if(data.trade_data){
                setTradeData({...trade_data,...data.trade_data})
              }

            }
          }
        );
      } else {
        let params = {
          sports_id: sports_id,
          season_id: selectedSeasonID,
          limit: "20",
          page: page,
        };
        WSManager.Rest(API.GET_MY_ENTRY, params).then(
          ({ response_code, data, ...res }) => {
            if (response_code == WSC.successCode) {
              setLoading(false);
              setHasMore(
                [...selectedFixtureList, ...data.answer].length <
                  parseInt(data.total)
              );
              setSelectedFixtureList([...selectedFixtureList, ...data.answer]);
              setMyEntriesfixtureList([...myEntriesfixtureList, ...data.match]);
              setTradeData({})

            }
          }
        );
      }
    }
  }, [page]);
  useEffect(() => {
    if(sports_id != null){
      let params = { sports_id: sports_id };
      WSManager.Rest(API.GET_LOBY_FIXTURE, params).then(
        ({ response_code, data, ...res }) => {
          if (response_code == WSC.successCode) {
            setFixtureList([]);
            setFixtureList(data);
            setPageNo(1);
            setSeasonID("");
          }
        }
      );
    }
  }, [sports_id]);
  const fetchMoreData = () => {
    if (fixtureList.length > 0) {
      setPageNo(parseInt(page) + 1);
    }
  };
  const initialize_questionList = () => {
    setSelectedFixtureList([]);
    setPageNo(1);
    setHasMore(false);
    get_question_list();
  };
  const initialize_entryList = () => {
    setSelectedFixtureList([]);
    setPageNo(1);
    setHasMore(false);
    get_entry_list();
  };
  const handleToggleClick = () => {
    setToggle(!toggle);
    if (WSManager.loggedIn()) {
      setLoading(true);
      if (toggle) {
        initialize_entryList();
      } else {
        initialize_questionList();
      }
    } else {
      history.push({ pathname: `/signup` });
    }
  };
  const timerCallback = () => {
    if (toggle) {
      initialize_questionList();
    }
  };

  const [howtomodal, sethowtomodal] = useState(false);

  const handleClose = () => sethowtomodal(false);
  const handleShow = () => sethowtomodal(true);

  const navigateDetails = () => {
    if(WSManager.loggedIn()){
        let fixture = fixtureList.find((e) => e.season_id == tradeItem.season_id);
        history.push({ pathname: `/question-details/${fixture.home}_vs_${fixture.away}/${tradeItem.question_id}`, state: { itemFixture: tradeItem, fixture: fixture } })
        setTradeItem(undefined)
    }else{
        history.push({ pathname: `/signup` })
    }
}
  const itemSelectedFixture = useMemo(() => {
    return (
      <SelectedFixtureItem
        timerCallback={timerCallback}
        setTradeSuccess={setTradeSuccess}
        setTradeItem={setTradeItem}
        initialize_questionList={initialize_questionList}
        toggle={toggle}
        trade_data={trade_data}
        fetchMoreData={fetchMoreData}
        fixtureList={toggle ? fixtureList : myEntriesfixtureList}
        selectedFixtureList={selectedFixtureList}
        hasMore={hasMore}
      />
    );
  }, [fixtureList, selectedFixtureList, hasMore, toggle, myEntriesfixtureList]);

  const actionSelectSeasonID = (season_id) => {
    setPageNo(1);
    if (selectedSeasonID == "" && season_id == "") {
      setSeasonID(-1);
      setTimeout(() => {
        setSeasonID(season_id);
      }, 100);
    } else {
      setSeasonID(selectedSeasonID == season_id?'':season_id);
    }
  };
  const itemFixture = useMemo(() => {
    return (
      <FixtureItem
        timerCallback={timerCallback}
        fixtureList={fixtureList}
        selectedSeasonID={selectedSeasonID}
        actionSelectSeasonID={actionSelectSeasonID}
      />
    );
  }, [fixtureList, selectedSeasonID]);
  return (
    <div className="container ot-lobby-wrap">
      <CustomHeader {...props} HeaderOption={headerOption} />

      <div className="ot-text-opinion-game-container">
        <div className="ot-text-opinion-game">
          <div className="gradient-bg" />
          <span>Opinion Game</span>
        </div>
        <div className="contain-share">
            <div onClick={() => {handleShow()}} className="txt-how-play">How to Play?</div>
            <div className="line-vertical"/>
            <div onClick={() => {setShowRules(true)}} className="txt-how-play">Rules</div>
        </div>
      </div>
      {
        bannerList && bannerList.length > 0 &&
        <div className="ot-card-container">
          <LobbyBanner BannerList={bannerList} />
        </div>
      }
      <div className="ot-match-conatiner">
        {itemFixture}
        <div className="ot-question-container">
          <div className="question-filter-header">
            <div className="view-question-toggle">
              <div
                onClick={handleToggleClick}
                className={
                  "item-toggle-view " + (toggle ? "active-bg-clr bg-left" : "")
                }
              >
                <span className={toggle ? "active-clr" : ""}>Questions</span>
              </div>
              <div
                onClick={handleToggleClick}
                className={
                  "item-toggle-view " + (toggle ? "" : "active-bg-clr bg-right")
                }
              >
                <span className={toggle ? "" : "active-clr"}>My Joined</span>
              </div>
            </div>
            
          </div>
          
          {selectedFixtureList.length > 0 && !isLoading
            ? itemSelectedFixture
            : !isLoading && (
                <EmptyScreen
                  title={
                    toggle
                      ? "There is no question available"
                      : "You haven’t choose any question"
                  }
                  toggle={toggle}
                  btnTitle={"Go to Questions"}
                  actionBtn={actionSelectSeasonID}
                />
              )}
         
        </div>
        {<HowtoPlayModal show={howtomodal} hide={handleClose} />}
        {tradeSuccess && <OpinionTradeSucess  MShow={tradeSuccess} MHide={(val)=>{if(val){navigateDetails()}setTradeSuccess(false)}} />}
        {showRules && <OpinionRules MShow={showRules} MHide={hideRulesScoring} />}

      </div>
    </div>
  );
};

export default Lobby;
