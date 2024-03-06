import React from 'react';
import {  Tabs, Tab } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import {Utilities, _Map} from "../../Utilities/Utilities";
import CustomHeader from '../../components/CustomHeader';
import {SportsIDs} from "../../JsonFiles";
import { AppSelectedSport } from '../../helper/Constants';
import { getRulePageData } from '../../WSHelper/WSCallings';

var mContext = undefined;
export default class NewRulesScoring extends React.Component {
    constructor(props) {
        super(props);
        this.handleSelect = this.handleSelect.bind(this);
        this.state = {
            pageData: { "page_title": AppLabels.RULES_SCORING, "page_content": "" },
            key: 1,
            rulesAndScoringArray: '',
            rulesAndScoringArrayTest: '',
            rulesAndScoringArrayT20: '',
            rulesAndScoringArrayT10: '',
            rulesAndScoringArrayODI: ''
        }
    }
    
    componentDidMount() {
        Utilities.setScreenName('rulesscoring')
        
        mContext = this;
        this.callGET_SCORING_MASTER_DATA()
    }
    callGET_SCORING_MASTER_DATA=async()=> {
        let param = {
            "sports_id": AppSelectedSport
        }

        var api_response_data = await getRulePageData(param);
        if(api_response_data){
            if (AppSelectedSport == SportsIDs.cricket) {
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
    }

    static reload() {
        if(mContext && window.location.pathname.startsWith("/rules-and-scoring")){
            mContext.setState({ rulesAndScoringArray: [], rulesAndScoringArrayTest:[], rulesAndScoringArrayT20:[], rulesAndScoringArrayT10:[], rulesAndScoringArrayODI:[] }, ()=>{
                mContext.callGET_SCORING_MASTER_DATA()
            })
        }
    }

    handleSelect(key) {
        this.setState({ key });
    }


    render() {
        const HeaderOption = {
            back: this.props.history.length > 1,
            filter: false,
            title: this.state.pageData.page_title
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page transparent-header web-container-fixed">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.rulesscoring.title}</title>
                            <meta name="description" content={MetaData.rulesscoring.description} />
                            <meta name="keywords" content={MetaData.rulesscoring.keywords}></meta>
                        </Helmet>

                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        
                        <div className="webcontainer-inner">  
                            <div className="page-body rules-scoring-body">


                                {AppSelectedSport == SportsIDs.cricket &&


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
                                                    <React.Fragment>
                                                        <div className="type-heading">{item.name}</div>
                                                        <ul className="scoring-chart">
                                                            <li className="header-block">
                                                                <div className="display-table">
                                                                    <div className="text-block"></div>
                                                                    <div className="value-block">Old Points</div>
                                                                    <div className="value-block">New Points</div>
                                                                </div>
                                                            </li>
                                                            {
                                                                item.rules && 
                                                                    _Map(item.rules, (scoring, idx) => {
                                                                        return (
                                                                            <li>
                                                                                <div className="display-table">
                                                                                    <div className="text-block">{scoring.score_position}</div>
                                                                                    <div className="value-block">{scoring.score_points}</div>
                                                                                    <div className={"value-block" + (scoring.score_points != scoring.new_score_points ? ' new-value-block' : '')}>{scoring.new_score_points}</div>
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
                                                    <React.Fragment>
                                                        <div className="type-heading">{item.name}</div>
                                                        <ul className="scoring-chart">
                                                            <li className="header-block">
                                                                <div className="display-table">
                                                                    <div className="text-block"></div>
                                                                    <div className="value-block">Old Points</div>
                                                                    <div className="value-block">New Points</div>
                                                                </div>
                                                            </li>
                                                            {
                                                                item.rules && 
                                                                    _Map(item.rules, (scoring, idx) => {
                                                                        return (
                                                                            <li>
                                                                                <div className="display-table">
                                                                                    <div className="text-block">{scoring.score_position}</div>
                                                                                    <div className="value-block">{scoring.score_points}</div>
                                                                                    <div className={"value-block" + (scoring.score_points != scoring.new_score_points ? ' new-value-block' : '')}>{scoring.new_score_points}</div>
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
                                                    <React.Fragment>
                                                        <div className="type-heading">{item.name}</div>
                                                        <ul className="scoring-chart">
                                                            <li className="header-block">
                                                                <div className="display-table">
                                                                    <div className="text-block"></div>
                                                                    <div className="value-block">Old Points</div>
                                                                    <div className="value-block">New Points</div>
                                                                </div>
                                                            </li>
                                                            {
                                                                item.rules && 
                                                                    _Map(item.rules, (scoring, idx) => {
                                                                        return (
                                                                            <li>
                                                                                <div className="display-table">
                                                                                    <div className="text-block">{scoring.score_position}</div>
                                                                                    <div className="value-block">{scoring.score_points}</div>
                                                                                    <div className={"value-block" + (scoring.score_points != scoring.new_score_points ? ' new-value-block' : '')}>{scoring.new_score_points}</div>
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
                                                    <React.Fragment>
                                                        <div className="type-heading">{item.name}</div>
                                                        <ul className="scoring-chart">
                                                            <li className="header-block">
                                                                <div className="display-table">
                                                                    <div className="text-block"></div>
                                                                    <div className="value-block">Old Points</div>
                                                                    <div className="value-block">New Points</div>
                                                                </div>
                                                            </li>
                                                            {
                                                                item.rules && 
                                                                    _Map(item.rules, (scoring, idx) => {
                                                                        return (
                                                                            <li>
                                                                                <div className="display-table">
                                                                                    <div className="text-block">{scoring.score_position}</div>
                                                                                    <div className="value-block">{scoring.score_points}</div>
                                                                                    <div className={"value-block" + (scoring.score_points != scoring.new_score_points ? ' new-value-block' : '')}>{scoring.new_score_points}</div>
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
                                    </Tabs>
                                }
                                {AppSelectedSport != SportsIDs.cricket &&

                                    <React.Fragment>
                                        { !this.state.rulesAndScoringArray.rules  &&
                                            <div className="text-center">{AppLabels.NO_SCORING_RULES}</div>
                                        }
                                        {
                                            this.state.rulesAndScoringArray.rules && 
                                                <div className="type-heading">{AppLabels.NORMAL}</div>
                                        }
                                        <ul className="scoring-chart">
                                            <li className="header-block">
                                                <div className="display-table">
                                                    <div className="text-block"></div>
                                                    <div className="value-block">Old Points</div>
                                                    <div className="value-block">New Points</div>
                                                </div>
                                            </li>
                                            {
                                            this.state.rulesAndScoringArray.rules && 
                                            _Map(this.state.rulesAndScoringArray.rules , (item, idx) => {
                                                return (
                                                        <li>
                                                            <div className="display-table">
                                                                <div className="text-block">{item.score_position}</div>
                                                                <div className="value-block">{item.score_points}</div>
                                                                <div className={"value-block" + (item.score_points != item.new_score_points ? ' new-value-block' : '')}>{item.new_score_points}</div>
                                                            </div>
                                                        </li>
                                                    );
                                                })

                                            }
                                        </ul>
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