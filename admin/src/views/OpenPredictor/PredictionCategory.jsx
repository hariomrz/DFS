import React, { Component } from 'react';
import { Row, Col, Button, TabContent, TabPane, Nav, NavItem, NavLink } from 'reactstrap';
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import LS from 'local-storage';
import queryString from 'query-string';
class PredictionCategory extends Component {
    constructor(props) {
        super(props)
        this.state = {
            FixtureList: [],
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            activeTab: "1"
        }
    }
    componentDidMount() {
        let values = queryString.parse(this.props.location.search)
        this.setState({
            activeTab: !_.isEmpty(values) ? (values.tab) ? values.tab : '1' : "1",
        }, () => {
            this.getLiveFixtures(this.state.activeTab)
        }) 
    }

    getLiveFixtures = (match_type) => {
        let { CURRENT_PAGE, PERPAGE } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            status: match_type //live
        }

        WSManager.Rest(NC.baseURL + NC.OP_GET_CATEGORY_LIST_BY_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    FixtureList: Response.data.category_list,
                    Total: Response.data.total,
                })
                window.scrollTo({
                    top: 0,
                    left: 0,
                    behavior: 'smooth'
                });
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
    PageRedirection = (item, type) => {
        LS.set('selected_fixture', item);
        this.props.history.push({ pathname: '/open-predictor/set-prediction/' + item.category_id + '/' + type })
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
        return (
            <React.Fragment>
                <div className="pre-fixture op-categories animated fadeIn">
                    <Row>
                        <Col md={12} className="mt-4">
                            <div className="op-btn-box">
                                <Button onClick={() => this.props.history.push('/open-predictor/create-category/')} className="btn-secondary-outline">Create Category</Button>
                            </div>
                        </Col>
                    </Row>

                    <Row className="user-navigation">
                        <div className="w-100">
                            <Nav tabs>
                                <NavItem className={activeTab == "1" ? "active" : ""}
                                    onClick={() => { this.toggle("1"); }}>
                                    <NavLink>
                                        Active
                                    </NavLink>
                                </NavItem>
                                {

                                    <NavItem className={activeTab == "2" ? "active" : ""}
                                        onClick={() => { this.toggle("2"); }}>
                                        <NavLink>
                                            Inactive / Completed
                                        </NavLink>
                                    </NavItem>
                                }
                            </Nav>
                            <TabContent activeTab={activeTab} className="p-4">
                                <TabPane tabId="1">
                                    <Row>
                                        {
                                            _.map(FixtureList, (item, idx) => {
                                                return (
                                                    <Col md={3} key={idx} className="pr-0 mb-3">
                                                        <div className="category-card cursor-pointer cursor-pointer"
                                                            onClick={() => this.PageRedirection(item, 1)}
                                                        >
                                                            <img src={NC.S3 + NC.OP_CATEGORY + item.image} alt="" className="cat-img" />
                                                            <div className="cat-info-box">
                                                                <div className="cat-title">
                                                                    {item.name}
                                                                </div>
                                                                <div className="cat-questions">
                                                                    {item.question_count}{' '}Questions</div>
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
                                                        <Col md={3} key={idx} className="pr-0 mb-3">
                                                            <div className="category-card cursor-pointer cursor-pointer"
                                                                onClick={() => this.PageRedirection(item, item.completed_count > 0 ? 2 : 1)}
                                                            >
                                                                <img src={NC.S3 + NC.OP_CATEGORY + item.image} alt="" className="cat-img" />
                                                                <div className="cat-info-box">
                                                                    <div className="cat-title">
                                                                        {item.name}
                                                                    </div>
                                                                    <div className="cat-questions">
                                                                        {item.completed_count}{' '}Questions</div>
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
                            </TabContent>
                        </div>
                    </Row>
                </div>
            </React.Fragment>
        );
    }
}

export default PredictionCategory;
