import React, { Component } from 'react';
import { Row, Col, TabContent, TabPane, Nav, NavItem, NavLink } from 'reactstrap';
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import SportsDropdown from "../../components/SportsDropdown";
import LS from 'local-storage';
import queryString from 'query-string';
import HF from '../../helper/HelperFunction';

class PredictionFixture extends Component {
    constructor(props) {
        super(props)
        this.state = {
            FixtureList: [],
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            SelectedSports: "7",
            activeTab: "1"
        }
    }
    componentDidMount() {
        let values = queryString.parse(this.props.location.search)
        this.setState({
            activeTab: !_.isEmpty(values) ? (values.tab) ? values.tab : '1' : "1",
        },()=>{
                this.getLiveFixtures(this.state.activeTab)
        })        
    }

    sportsCallback = (SelectedSports) => {
        let { activeTab } = this.state
        this.setState({ SelectedSports: SelectedSports },
            () => {
                this.getLiveFixtures(activeTab)
            })
    }

    getLiveFixtures = (match_type) => {
        let { CURRENT_PAGE, PERPAGE, SelectedSports } = this.state
        let params = {}
        params = {
            sports_id: SelectedSports,
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            match_type: match_type //live
        }

        WSManager.Rest(NC.baseURL + NC.GET_SEASON_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    FixtureList: Response.data.fixtures.result,
                    Total: Response.data.fixtures.total,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handlePageChange(current_page, match_type) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => { this.getLiveFixtures(match_type) });
        }
    }

    PageRedirection = (item, calltype) => {
        let { SelectedSports } = this.state
        LS.set('selected_fixture', item);
        this.props.history.push({ pathname: '/prediction/set-prediction/' + calltype + '/' + item.season_game_uid + '/' + SelectedSports })
    }

    toggle(tab) {
        if (this.state.activeTab !== tab) {
            this.setState({
                activeTab: tab,
                CURRENT_PAGE: 1
            }, function () {
                this.getLiveFixtures(tab)
            })
        }
    }

    render() {
        let { FixtureList, PERPAGE, Total, activeTab, CURRENT_PAGE } = this.state
        const sports_Props = {
            modalCallback: this.sportsCallback
        }
        return (
            <React.Fragment>
                <div className="pre-fixture">
                    <Row>
                        <Col md={12} className="mt-4">
                            <div className="pre-sports-select float-left">
                                <SportsDropdown SportsProps={sports_Props} />
                            </div>
                        </Col>
                    </Row>

                    <Row className="user-navigation">
                        <div className="w-100">
                            <Nav tabs>
                                <NavItem className={activeTab == "1" ? "active" : ""}
                                    onClick={() => { this.toggle("1"); }}>
                                    <NavLink>
                                        Live
                                    </NavLink>
                                </NavItem>
                                {

                                    <NavItem className={activeTab == "2" ? "active" : ""}
                                        onClick={() => { this.toggle("2"); }}>
                                        <NavLink>
                                            Upcoming
                                        </NavLink>
                                    </NavItem>
                                }

                                <NavItem className={activeTab == "3" ? "active" : ""}
                                    onClick={() => { this.toggle("3"); }}>
                                    <NavLink>
                                        Completed
                                    </NavLink>
                                </NavItem>
                            </Nav>
                            <TabContent activeTab={activeTab} className="p-4">
                                <TabPane tabId="1">
                                    <Row>
                                        {
                                            _.map(FixtureList, (item, idx) => {
                                                return (
                                                    <Col md={4} key={idx}>
                                                        <div className="fixture-card"
                                                            onClick={() => this.PageRedirection(item, 1)}
                                                        >
                                                            <img src={NC.S3 + NC.FLAG + item.home_flag} alt="" className="team-img float-left" />
                                                            <img src={NC.S3 + NC.FLAG + item.away_flag} alt="" className="team-img float-right" />
                                                            <div className="fixture-container">
                                                                <div className="fixture-name">{item.home} vs {item.away}</div>
                                                                <div className="fixture-time">
                                                                    {/* {WSManager.getUtcToLocalFormat(item.scheduled_date_time, 'D-MMM-YYYY hh:mm A')} */}
                                                                    {HF.getFormatedDateTime(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                                </div>
                                                                <div className="fixture-title">{item.subtitle}</div>
                                                                {
                                                                    item.question_count == 0 ?
                                                                        <div className="fixture-question active-color"> Set Prediction</div>
                                                                        :
                                                                        <div className="fixture-question">{item.question_count} Questions</div>
                                                                }
                                                            </div>
                                                        </div>
                                                    </Col>
                                                )
                                            })
                                        }
                                    </Row>
                                    {Total > PERPAGE &&
                                        <Row>
                                            <Col md={12}>
                                                <div className="custom-pagination userlistpage-paging float-right">
                                                    <Pagination
                                                        activePage={CURRENT_PAGE}
                                                        itemsCountPerPage={PERPAGE}
                                                        totalItemsCount={Total}
                                                        pageRangeDisplayed={3}
                                                        onChange={e => this.handlePageChange(e, 1)}
                                                    />
                                                </div>
                                            </Col>
                                        </Row>
                                    }
                                </TabPane>
                                {
                                    (activeTab == "2") &&
                                    <TabPane tabId="2">
                                        <Row>
                                            {
                                                _.map(FixtureList, (item, idx) => {
                                                    return (
                                                        <Col md={4} key={idx}>
                                                            <div className="fixture-card"
                                                                onClick={() => this.PageRedirection(item, 2)}
                                                            >

                                                                <img src={NC.S3 + NC.FLAG + item.home_flag} alt="" className="team-img float-left" />
                                                                <img src={NC.S3 + NC.FLAG + item.away_flag} alt="" className="team-img float-right" />
                                                                <div className="fixture-container">
                                                                    <div className="fixture-name">{item.home} vs {item.away}</div>
                                                                    <div className="fixture-time">
                                                                        {/* {WSManager.getUtcToLocalFormat(item.scheduled_date_time, 'D-MMM-YYYY hh:mm A')} */}
                                                                        {HF.getFormatedDateTime(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                                    </div>
                                                                    <div className="fixture-title">{item.subtitle}</div>
                                                                    {
                                                                        item.question_count == 0 ?
                                                                            <div className="fixture-question active-color"> Set Prediction</div>
                                                                            :
                                                                            <div className="fixture-question">{item.question_count} Questions</div>
                                                                    }
                                                                </div>
                                                            </div>
                                                        </Col>
                                                    )
                                                })
                                            }
                                        </Row>
                                        {Total > PERPAGE &&
                                            <Row>
                                                <Col md={12}>
                                                    <div className="custom-pagination userlistpage-paging float-right">
                                                        <Pagination
                                                            activePage={CURRENT_PAGE}
                                                            itemsCountPerPage={PERPAGE}
                                                            totalItemsCount={Total}
                                                            pageRangeDisplayed={3}
                                                            onChange={e => this.handlePageChange(e, 2)}
                                                        />
                                                    </div>
                                                </Col>
                                            </Row>
                                        }
                                    </TabPane>
                                }
                                {
                                    activeTab == "3" &&
                                    <TabPane tabId="3">
                                        <Row>
                                            {
                                                _.map(FixtureList, (item, idx) => {
                                                    return (
                                                        <Col md={4} key={idx}>
                                                            <div className="fixture-card">
                                                                <img src={NC.S3 + NC.FLAG + item.home_flag} alt="" className="team-img float-left" />
                                                                <img src={NC.S3 + NC.FLAG + item.away_flag} alt="" className="team-img float-right" />
                                                                <div className="fixture-container">
                                                                    <div className="fixture-name">{item.home} vs {item.away}</div>
                                                                    <div className="fixture-time">
                                                                        {/* {WSManager.getUtcToLocalFormat(item.scheduled_date_time, 'D-MMM-YYYY hh:mm A')} */}
                          {HF.getFormatedDateTime(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                                    
                                                                    </div>
                                                                    <div className="fixture-title">{item.subtitle}</div>
                                                                    <div
                                                                        onClick={() => this.PageRedirection(item, 3)}
                                                                        className="fixture-question">{item.question_count} Questions</div>
                                                                </div>
                                                            </div>
                                                        </Col>
                                                    )
                                                })
                                            }
                                        </Row>
                                        {Total > PERPAGE &&
                                            <Row>
                                                <Col md={12}>
                                                    <div className="custom-pagination userlistpage-paging float-right">
                                                        <Pagination
                                                            activePage={CURRENT_PAGE}
                                                            itemsCountPerPage={PERPAGE}
                                                            totalItemsCount={Total}
                                                            pageRangeDisplayed={3}
                                                            onChange={e => this.handlePageChange(e, 3)}
                                                        />
                                                    </div>
                                                </Col>
                                            </Row>
                                        }
                                    </TabPane>
                                }
                            </TabContent>
                        </div>
                    </Row>
                </div>
            </React.Fragment >
        );
    }
}

export default PredictionFixture;
