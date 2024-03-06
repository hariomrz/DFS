import React,{lazy, Suspense} from 'react';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { Utilities } from '../Utilities/Utilities';
import CountdownTimer from './CountDownTimer';
import { AppSelectedSport } from '../helper/Constants';
import { MomentDateComponent } from '../Component/CustomComponent';
const ReactSlickSlider = lazy(()=>import('../Component/CustomComponent/ReactSlickSlider'));

// this component contains fixture list of lobby

export default class CollectionSlider extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            sports_id: AppSelectedSport,
            isExist:false
        }
    }

    componentDidMount() {
    }

    FixtureListFunction = (item, isFrom,contestSliderData) => {
        let collectionFor = item.status == 0 && (item.game_starts_in > item.today) ? 0 : 1; // 1 is for live and 0 is for upcoming
        let tmpFixtures = this.props.FixtureSelected;
        let isItemSelected = tmpFixtures ? tmpFixtures.includes(item) : false
        return (
            <React.Fragment>
                {((isFrom == 'LiveContest' && (item.status == 2 || collectionFor == 1)) || isFrom == 'CompletedContest') &&
                    <div className="collection-list livecontest-collection-list">
                        <div className={"display-table" + (item.status == 2 ? ' completed-border' :  item.status == 1 || collectionFor == 1 ? ' live-border' : ' upcoming-border')}>
                            <div className="display-table-cell text-left v-mid ">
                                <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="team-img" />
                                <span className="team-name text-uppercase">{item.home}</span>
                                {this.state.sports_id != 8 && item.score_data && item.score_data[1] ?
                                    <span className="match-score">
                                    
                                        {item.score_data[1].home_team_score}/
                                        {item.score_data[1].home_wickets}
                                        <span className="over"> {item.score_data[1].home_overs}</span>
                                    </span>
                                    :
                                    this.state.sports_id == 5 && item.score_data ?
                                    <span className="match-score">
                                     {item.score_data.home_score} 
                                   </span> : ''

                                }
                                {this.state.sports_id != 8 && !item.score_data &&
                                    <span className="match-score">
                                        {0}/
                                        {0}
                                        <span className="over"> {0}</span>
                                    </span>
                                }
                                {this.state.sports_id == 8 && item.score_data &&
                                    <span className="match-score">
                                        {item.score_data.home_score}
                                    </span>
                                }
                            </div>
                            <span className="slash"></span>
                            {isFrom == 'LiveContest' &&
                                <div className={"collection-status" + (item.status == 2 ? ' completed-status' : (item.status == 1 || collectionFor == 1 ? ' live-status' :''))}>
                                    {item.status == 2 ?
                                        <span>
                                            <div>Completed</div>
                                        </span> :
                                        item.status == 1 || collectionFor == 1 ?
                                        <span>
                                            <span className="circle-divider"></span>
                                            <div>Live</div>
                                        </span>
                                        :''
                                        
                                    }
                                </div>
                            }
                            <div className="display-table-cell text-right v-mid ">
                                <img src={Utilities.teamFlagURL(item.away_flag)} alt="" className="team-img" />
                                <span className="team-name text-uppercase">{item.away}</span>
                                {this.state.sports_id != 8 && item.score_data && item.score_data[1] ?
                                    <span className="match-score">
                                        {item.score_data[1].away_team_score}/
                                        {item.score_data[1].away_wickets}
                                        <span className="over"> {item.score_data[1].away_overs}</span>
                                    </span>
                                    :
                                    this.state.sports_id == 5 && item.score_data ?
                                    <span className="match-score">
                                        {item.score_data.away_score}
                                      </span>
                                      : ''

                                }
                                {this.state.sports_id != 8 && !item.score_data &&
                                    <span className="match-score">
                                        {0}/
                                        {0}
                                        <span className="over"> {0}</span>
                                    </span>
                                }
                                {this.state.sports_id == 8 && item.score_data &&
                                    <span className="match-score">
                                        {item.score_data.away_score}
                                    </span>
                                }
                            </div>
                        </div>
                    </div>
                }
                {/* {((isFrom != 'LiveContest' && isFrom != 'CompletedContest') || (isFrom == 'LiveContest' && collectionFor == 0)) &&
                    <div className="collection-list">
                        <div className= {"display-table " + (isItemSelected ? "selection-border" : "" )}>
                            <div className="display-table-cell text-center v-mid w20">
                                <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="team-img" />
                            </div>
                            <div className="display-table-cell text-center v-mid w-lobby-40">
                                <div className="team-block">
                                    <span className="team-name text-uppercase">{item.home}</span>
                                    <span className="verses">{AppLabels.VS}</span>
                                    <span className="team-name text-uppercase">{item.away}</span>
                                </div>
                                <div className="match-timing">
                                    {
                                        Utilities.showCountDown(item) ?
                                            <div className="countdown time-line">
                                                {item.game_starts_in && <CountdownTimer deadlineTimeStamp={item.game_starts_in} currentDateTimeStamp={item.today} />}
                                            </div> :
                                            <span><MomentDateComponent data={{date:item.season_scheduled_date,format:"D MMM - hh:mm A "}} /></span>
                                    }
                                </div>
                            </div>
                            <div className="display-table-cell text-center v-mid w20">
                                <img src={Utilities.teamFlagURL(item.away_flag)} alt="" className="team-img" />
                            </div>
                        </div>
                    </div>
                } */}
            </React.Fragment>

            /* <div className={"collection-list" + (isFrom == 'LiveContest' ? (item.status == 2 ? ' completed-collection-list' : (item.status == 0 ? ' live-collection-list' : '')) : '')}>
            </div> */
        );
    }

    onFixtureSelect = (item) => {
        const { contestSliderData,getFilterList,keyId, showContestItem, isFrom } = this.props;
        if(isFrom == "Roster" ) {
            let tmpFixtures = this.props.FixtureSelected;
            if(tmpFixtures.includes(item)){
                let indexObj = tmpFixtures.indexOf(item);
                tmpFixtures.splice(indexObj, 1);
                getFilterList(tmpFixtures)
            }else{
                    getFilterList([...tmpFixtures, item])
            }
        }
    }

 
   render() {
        const { contestSliderData,collectionInfo, isFrom, CollectionInfoShow } = this.props;
        var settings = {
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: false,
            centerPadding: '100px 0 5px',
            initialSlide: 0,
            // variableWidth: true,
            className: "center",
            centerMode: true,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: isFrom == "CompletedContest" ? '60px 0 0' : '60px 0 10px',
                    }
                },
                {
                    breakpoint: 414,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: isFrom == 'ContestListing' ? '80px 0 0' : isFrom == "CompletedContest" ? '60px 0 0' : '60px 0 10px',
                    }
                },
                {
                    breakpoint: 360,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: isFrom == "CompletedContest" ? '40px 0 0' : '40px 0 5px',
                    }
                },
                {
                    breakpoint: 320,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: isFrom == "CompletedContest" ? '10px 0 0' : '10px 0 5px',
                    }
                }
            ]
        };

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"contest-collection-slider fixture-list-content " + (isFrom == "ContestListing" ? 'pl5' : '') + (isFrom == "Roster" ? 'contest-collection-slider-roster' : '')}>
                        {contestSliderData &&
                                <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                                    {
                                        contestSliderData.match_list && contestSliderData.match_list.length >= 2 && contestSliderData.match_list.map((item, index) => {
                                            return (
                                                <div key={item.season_game_uid} onClick={()=>this.onFixtureSelect(item)} className="collection-list-slider">
                                                    {this.FixtureListFunction(item, isFrom,contestSliderData)}
                                                </div>
                                            );
                                        })
                                    }
                                </ReactSlickSlider></Suspense>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}