import React, { useEffect, useState } from 'react';
import { Col, Nav, NavItem, Row, Tab } from 'react-bootstrap';
import { _Map, _debounce, _isEmpty, _isNull } from '../../../Utilities/Utilities';
import { AppSelectedSport } from '../../../helper/Constants';
import * as AppLabels from "../../../helper/AppLabels";
import * as Constants from "../../../helper/Constants";
import * as WSC from "../../../WSHelper/WSConstants";
import { useLocation, useParams } from 'react-router-dom';
import { my_contest_config } from '../../../JsonFiles';
import LiveProps from './LiveProps';
import UpcomingProps from './UpcomingProps';
import CompletedProps from './CompletedProps';
import ls from 'local-storage';
import { CommonLabels } from "../../../helper/AppLabels";


const PropsMyContest = (props) => {
  // Const and State
  const { search } = useLocation()
  const searchParams = new URLSearchParams(search);
  const contest = searchParams.get("contest");
  const [selectedTab, setSelectedTab] = useState(null)
  const [state, setState] = useState({
    sports_id: AppSelectedSport,
    MESSAGE_1: '',
    MESSAGE_2: ''
  })

  // Method(s)
  /**
     * @description Event of tab click (Live, Upcoming, Completed)
     * @param selectedTab value of selected tab
     */
  const onTabClick = _debounce((tab) => {
    if (selectedTab == tab) return
    window.history.replaceState("", "", "/my-contests?contest=" + my_contest_config.contest[tab]);
    setSelectedTab(tab)
  }, 300)

  const goLobby = () => {
    props.history.push({ pathname: '/' })
  }


  // Lifecycle(s)
  useEffect(() => {
    ls.remove('in_params')
    ls.remove('isProps')
    if (contest in my_contest_config.contest_url) {
      setSelectedTab(my_contest_config.contest_url[contest])
    } else {
      setSelectedTab(0)
      window.history.replaceState("", "", "/my-contests?contest=" + my_contest_config.contest[0]);
    }
    return () => { }
  }, [])

  useEffect(() => {
    let MESSAGE_1 = selectedTab == Constants.CONTEST_UPCOMING ? CommonLabels.ENTRY_MSG_1 : (selectedTab == Constants.CONTEST_LIVE ? CommonLabels.ENTRY_MSG_2 : CommonLabels.ENTRY_MSG_3)
    let MESSAGE_2 = selectedTab == Constants.CONTEST_UPCOMING ? AppLabels.NO_UPCOMING_CONTEST2 : (selectedTab == Constants.CONTEST_LIVE ? AppLabels.NO_LIVE_CONTEST2 : AppLabels.NO_COMPLETED_CONTEST2)
    setState((prev) => ({
      ...prev,
      MESSAGE_1: MESSAGE_1,
      MESSAGE_2: MESSAGE_2
    }))
    return () => { }
  }, [selectedTab])

  useEffect(() => {
    if(selectedTab != 0 && !_isNull(selectedTab)) {
      setSelectedTab(0)
      window.history.replaceState("", "", "/my-contests?contest=" + my_contest_config.contest[0]);
    }
    return () => { }
}, [AppSelectedSport])

  // Destructuring
  const { MESSAGE_1, MESSAGE_2 } = state
  const ComponentProps = { 
    selectedTab: selectedTab, 
    goLobby, 
    MESSAGE_1, 
    MESSAGE_2
   }
  return (
    <>
      <div className={"web-container my-contest-style xtab-two-height web-container-fixed"}>
        <div className={"tabs-primary"}>
          <Tab.Container id='my-contest-tabs' activeKey={selectedTab} onSelect={() => { }} defaultActiveKey={selectedTab}>
            <Row className="clearfix">
              <Col className="top-fixed my-contest-tab circular-tab circular-tab-new xnew-tab" xs={12}>
                <Nav>
                  <NavItem onClick={() => onTabClick(Constants.CONTEST_UPCOMING)} eventKey={Constants.CONTEST_UPCOMING}>{AppLabels.UPCOMING}</NavItem>
                  <NavItem onClick={() => onTabClick(Constants.CONTEST_LIVE)} eventKey={Constants.CONTEST_LIVE} className="live-contest"><span><span className="live-indicator"></span> {AppLabels.LIVE} </span></NavItem>
                  <NavItem onClick={() => onTabClick(Constants.CONTEST_COMPLETED)} eventKey={Constants.CONTEST_COMPLETED}>{AppLabels.COMPLETED}</NavItem>
                </Nav>
              </Col>
              <Col className="top-tab-margin" xs={12}>
                {

                  <Tab.Content animation>
                    <Tab.Pane eventKey={Constants.CONTEST_LIVE}>
                      <LiveProps {...ComponentProps} />
                    </Tab.Pane>
                    <Tab.Pane eventKey={Constants.CONTEST_UPCOMING}>
                      <UpcomingProps {...ComponentProps} />
                    </Tab.Pane>
                    <Tab.Pane eventKey={Constants.CONTEST_COMPLETED}>
                      <CompletedProps {...ComponentProps} />
                    </Tab.Pane>
                  </Tab.Content>
                }
              </Col>
            </Row>
          </Tab.Container>
        </div>
      </div>
    </>
  );
};

export default PropsMyContest;
