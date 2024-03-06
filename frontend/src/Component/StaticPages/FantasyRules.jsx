import React, { lazy, Suspense } from 'react';
import { Tabs, Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import {Utilities, _Map, _isEmpty} from "../../Utilities/Utilities";
import CustomHeader from '../../components/CustomHeader';
import {SportsIDs} from "../../JsonFiles";
import { AppSelectedSport, DARK_THEME_ENABLE } from '../../helper/Constants';
import { getRulePageData } from '../../WSHelper/WSCallings';
import WSManager from '../../WSHelper/WSManager';
import Slider from "react-slick";

export default class FantasyRules extends React.Component {
    constructor(props) {
        super(props);
        this.handleSelect = this.handleSelect.bind(this);
        this.state = {
            pageData: { "page_title": AppLabels.FANTASY + ' ' + AppLabels.RULES, "page_content": "" },
            key: 1,
            rulesAndScoringArray: '',
            rulesAndScoringArrayTest: '',
            rulesAndScoringArrayT20: '',
            rulesAndScoringArrayT10: '',
            rulesAndScoringArrayODI: '',
            SLIST: [],
            ACSPORTTAB: AppSelectedSport,
            isLoading: true,
            windowWidth: window.innerWidth > 550 ? 550 : window.innerWidth, 
        }
    }

    componentDidMount() {
        Utilities.setScreenName('rulesscoring')
        this.setData()
        window.addEventListener('resize', (event)=>{
            this.setState({
                windowWidth: window.innerWidth > 550 ? 550 : window.innerWidth
            })            
          });
    }

    componentWillUnmount() {
        window.removeEventListener('resize',()=>{});
    }

    callGET_SCORING_MASTER_DATA = async () => {
        let param = {
            "sports_id": this.state.ACSPORTTAB
        }

        this.setState({
            isLoading: true
        })

        var api_response_data = await getRulePageData(param);
        if (api_response_data) {
            if (this.state.ACSPORTTAB == SportsIDs.cricket) {
                this.setState({
                    rulesAndScoringArray: api_response_data,
                    rulesAndScoringArrayTest: api_response_data.test,
                    rulesAndScoringArrayT20: api_response_data.tt,
                    rulesAndScoringArrayT10: api_response_data.t10,
                    rulesAndScoringArrayODI: api_response_data.one_day
                })
            }
            else {
                this.setState({
                    rulesAndScoringArray: api_response_data
                })
            }
        }
        this.setState({
            isLoading: false
        })
    }

    handleSelect(key) {
        this.setState({ key });
    }

    setData = () => {
        const sports_id = Utilities.getUrlSports();
        const fantasy_list = Utilities.getMasterData().fantasy_list;
        this.setState({
            SLIST: fantasy_list || [],
            ACSPORTTAB: sports_id || AppSelectedSport
        }, () => {
            const { SLIST, ACSPORTTAB } = this.state
            const index = SLIST.findIndex(item => item.sports_id === ACSPORTTAB);
            if (index !== -1) {
                console.log(`Index of 'Bob': ${index}`);
                let last = SLIST.length - index
                console.log(last);
                let _idx = last == 1 ? index - 2 : last == 2 ? index -1 : index
                this.slider.slickGoTo(_idx)
            } else {
                console.log("'Bob' not found in the array.");
            }
            this.callGET_SCORING_MASTER_DATA()
        })
    }

    onTabClick = (item) => {
        this.setState({ ACSPORTTAB: item.sports_id }, () => {
            this.callGET_SCORING_MASTER_DATA()
        });
    }

    renderTopSportsTab = () => {
        let { SLIST, ACSPORTTAB } = this.state;
        const appLang = WSManager.getAppLang();
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: SLIST.length > 2 ? 3 : 2,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: false,
            centerMode: false,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 2 ,
                        className: "center",
                        centerMode: SLIST.length > 2 ? true : false,
                        centerPadding: '30px 0 10px',
                        infinite: true,
                        initialSlide: 1,
                    }

                }
            ]
        };
        return (
            <Tab.Container id='top-sports-slider' onSelect={() => console.log('onSelect')} activeKey={ACSPORTTAB} defaultActiveKey={ACSPORTTAB}>
                <Row className="clearfix">
                    <Col className="sports-tab-nav sports-tab-rules  p-0" xs={12}>
                        <Nav>
                        <Slider ref={(c) => (this.slider = c)} {...settings}>
                                {
                                    _Map(SLIST, (item, idx) => {
                                        return (
                                            <NavItem
                                                style={{ width: 'calc(100% / ' + SLIST.length + ')' }}
                                                key={item.sports_id}
                                                onClick={() => this.onTabClick(item, idx)}
                                                eventKey={item.sports_id}
                                                className={item.sports_id == ACSPORTTAB ? 'active' : ''}
                                                >
                                                <span>
                                                    {item[appLang] || item.sports_name}
                                                </span>
                                            </NavItem>
                                        )
                                    })
                                }
                            </Slider>
                        </Nav>
                    </Col>
                </Row>
            </Tab.Container>
        )
    }

    render() {
        const { isLoading } = this.state
        const HeaderOption = {
            back: this.props.history.length > 1,
            filter: false,
            title: this.state.pageData.page_title,
            isPrimary:DARK_THEME_ENABLE ? false : true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page web-container-fixed rules-scoring-static-page">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.rulesscoring.title}</title>
                            <meta name="description" content={MetaData.rulesscoring.description} />
                            <meta name="keywords" content={MetaData.rulesscoring.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="webcontainer-inner dashboard-container">
                            {
                                this.renderTopSportsTab()
                            }
                            <div className={"page-body rules-scoring-body" + (this.state.ACSPORTTAB != SportsIDs.cricket ? ' p25' : '')}>


                                {!isLoading && this.state.ACSPORTTAB == SportsIDs.cricket &&


                                    <Tabs
                                        activeKey={this.state.key}
                                        onSelect={this.handleSelect}
                                        id="controlled-tab-example" className="custom-nav-tabs"
                                    >
                                        <Tab eventKey={1} title={AppLabels.TEST}>
                                            {
                                                this.state.rulesAndScoringArrayTest &&
                                                _Map(this.state.rulesAndScoringArrayTest, (item, idx) => {
                                                    return (
                                                        <React.Fragment key={item.name}>
                                                            <div className="type-heading">{item.name}</div>
                                                            <ul className="scoring-chart">
                                                                {
                                                                    item.rules &&
                                                                    _Map(item.rules, (scoring, idx) => {
                                                                        return (
                                                                            <li key={scoring.master_scoring_id}>
                                                                                <div className="display-table">
                                                                                    <div className="text-block">{scoring.score_position}</div>
                                                                                    <div className="value-block">{scoring.score_points}</div>
                                                                                </div>
                                                                            </li>
                                                                        );
                                                                    })
                                                                }
                                                            </ul>
                                                        </React.Fragment>

                                                    );
                                                })
                                            }
                                        </Tab>
                                        <Tab eventKey={2} title={AppLabels.ODI} >
                                            {
                                                this.state.rulesAndScoringArrayODI &&
                                                _Map(this.state.rulesAndScoringArrayODI, (item, idx) => {
                                                    return (
                                                        <React.Fragment key={item.name}>
                                                            <div className="type-heading">{item.name}</div>
                                                            <ul className="scoring-chart">
                                                                {
                                                                    item.rules &&
                                                                    _Map(item.rules, (scoring, idx) => {
                                                                        return (
                                                                            <li key={scoring.master_scoring_id}>
                                                                                <div className="display-table">
                                                                                    <div className="text-block">{scoring.score_position}</div>
                                                                                    <div className="value-block">{scoring.score_points}</div>
                                                                                </div>
                                                                            </li>
                                                                        );
                                                                    })
                                                                }
                                                            </ul>
                                                        </React.Fragment>

                                                    );
                                                })
                                            }
                                        </Tab>
                                        <Tab eventKey={3} title={AppLabels.T20}>
                                            {
                                                this.state.rulesAndScoringArrayT20 &&
                                                _Map(this.state.rulesAndScoringArrayT20, (item, idx) => {
                                                    return (
                                                        <React.Fragment key={item.name}>
                                                            <div className="type-heading">{item.name}</div>
                                                            <ul className="scoring-chart">
                                                                {
                                                                    item.rules &&
                                                                    _Map(item.rules, (scoring, idx) => {
                                                                        return (
                                                                            <li key={scoring.master_scoring_id}>
                                                                                <div className="display-table">
                                                                                    <div className="text-block">{scoring.score_position}</div>
                                                                                    <div className="value-block">{scoring.score_points}</div>
                                                                                </div>
                                                                            </li>
                                                                        );
                                                                    })
                                                                }
                                                            </ul>
                                                        </React.Fragment>

                                                    );
                                                })
                                            }
                                        </Tab>
                                        <Tab eventKey={4} title={'T10'}>
                                            {
                                                this.state.rulesAndScoringArrayT10 &&
                                                _Map(this.state.rulesAndScoringArrayT10, (item, idx) => {
                                                    return (
                                                        <React.Fragment key={item.name}>
                                                            <div className="type-heading">{item.name}</div>
                                                            <ul className="scoring-chart">
                                                                {
                                                                    item.rules &&
                                                                    _Map(item.rules, (scoring, idx) => {
                                                                        return (
                                                                            <li key={scoring.master_scoring_id}>
                                                                                <div className="display-table">
                                                                                    <div className="text-block">{scoring.score_position}</div>
                                                                                    <div className="value-block">{scoring.score_points}</div>
                                                                                </div>
                                                                            </li>
                                                                        );
                                                                    })
                                                                }
                                                            </ul>
                                                        </React.Fragment>

                                                    );
                                                })
                                            }
                                        </Tab>
                                        {/* <span style={{ width: 'calc(100% / ' + 4 + ')', left: ((this.state.windowWidth - 50)/4 * (this.state.key - 1)) + 'px' }} className="active-nav-indicator rule"></span> */}
                                    </Tabs>
                                }
                                {!isLoading && this.state.ACSPORTTAB != SportsIDs.cricket &&

                                    <React.Fragment>
                                        {_isEmpty(this.state.rulesAndScoringArray) &&
                                            <div className="text-center">{AppLabels.NO_SCORING_RULES}</div>
                                        }

                                        {
                                            _Map(this.state.rulesAndScoringArray, (list, idx) => {
                                                return (
                                                    <>
                                                        <div className="type-heading">{list.name}</div>
                                                        <ul className="scoring-chart">
                                                            {
                                                                _Map(list.rules, (item) => {
                                                                    return (
                                                                        <li key={item.master_scoring_id}>
                                                                            <div className="display-table">
                                                                                <div className="text-block">{item.score_position}</div>
                                                                                <div className="value-block">{item.score_points}</div>
                                                                            </div>
                                                                        </li>
                                                                    );
                                                                })

                                                            }
                                                        </ul>
                                                    </>
                                                )
                                            })
                                        }
                                        
                                    </React.Fragment>
                                }

                            </div>
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}