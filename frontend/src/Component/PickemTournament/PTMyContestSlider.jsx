import React, { Component, lazy, Suspense } from "react";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as AL from "../../helper/AppLabels";
import { Utilities, _Map, isDateTimePast } from "../../Utilities/Utilities";
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from "../../Component/CustomComponent";
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));

class PTMyContestSlider extends Component {
    constructor(props) {
        super(props);
        this.state = {
            checked: false,
            tooltipOpen: false,
            emailID: ''
        };
        this.next = this.next.bind(this);
        this.previous = this.previous.bind(this);
    }

    next() {
        this.slider.slickNext();
    }
    previous() {
        this.slider.slickPrev();
    }



    showTourTimimg = (item) => {
        let sDate = new Date(Utilities.getUtcToLocal(item.start_date))
        let game_starts_in = Date.parse(sDate)
        item['game_starts_in'] = game_starts_in;
        return <>
            {
                item.status == 3 ?
                    <div className="tag comp">
                        {AL.COMPLETED}
                    </div>
                    :
                    <>
                        {
                            // Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') >= Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                            isDateTimePast(item.start_date)
                                ?
                                <div className="tag live">
                                    <span></span>{AL.LIVE}
                                </div>
                                :
                                <span>
                                    {
                                        Utilities.showCountDown(item) ?
                                            <div className="countdown time-line">
                                                {item.game_starts_in &&
                                                    (Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) &&
                                                    <CountdownTimer deadlineTimeStamp={item.game_starts_in} />
                                                }
                                            </div>
                                            :
                                            <MomentDateComponent data={{ date: item.start_date, format: "D MMM - hh:mm A " }} />
                                    }
                                </span>
                        }
                    </>
            }
        </>
    }

    renderPrefix = (i) => {
        var j = i % 10,
            k = i % 100;
        if (j == 1 && k != 11) {
            return i + "st";
        }
        if (j == 2 && k != 12) {
            return i + "nd";
        }
        if (j == 3 && k != 13) {
            return i + "rd";
        }
        return i + "th";
    }

    gotoDetails=(item)=>{
        this.props.gotoDetails(item)
    }

    render() {
        const { FixtureData, timerCallback } = this.props;
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: 2.2,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: false,
            autoplaySpeed: 5000,
            centerMode: false,
            centerPadding: "13px",
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        // className: "center",
                        // centerPadding: "13px",
                        slidesToShow: 1.6,
                    }

                },
                {
                    breakpoint: 360,
                    settings: {
                        // className: "center",
                        // centerPadding: "13px",
                        slidesToShow: 1.6,
                    }

                }
            ]
        };

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"my-lobby-fixture mt-0"}>
                        <Suspense fallback={<div />} >
                            <ReactSlickSlider settings={settings}>
                                {
                                    FixtureData.map((item, idx) => {
                                        return (
                                            <ul key={item.collection_master_id + idx}>
                                                <li key={idx} style={{ position: 'relative', padding: '0 3px' }}>
                                                    <div className={`new-dm-fixture-card with-tour`}
                                                        onClick={()=>this.gotoDetails(item)}>
                                                        <div className="time-sec">
                                                            {this.showTourTimimg(item)}
                                                        </div>
                                                        <div className="tour-title">{item.name}</div>
                                                        <div className="tag-sec">
                                                            <div className="tag"> {item.match_count || 0} {AL.FIXTURES}</div>
                                                        </div>
                                                        <div className="btm-sec">
                                                            {
                                                                (item.status == 3 || (Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')))
                                                                ?
                                                                <span className={`rank-txt pickem-rank ${item.is_winner && item.is_winner != '0' ? ' succ' : ''}`}>
                                                                    {
                                                                        item.is_winner && item.is_winner != '0' && <i className="icon-trophy"></i>
                                                                    }
                                                                    {item.game_rank == '0' && <span>--</span>}
                                                                    {item.game_rank !== '0' && <span>{this.renderPrefix(item.game_rank)} {AL.RANK}</span>}
                                                                </span>
                                                                :
                                                                <span className='rank-txt'>
                                                                    <span>{item.predict_count || 0} {AL.PREDICTED}</span>
                                                                </span>
                                                            }
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>

                                        )
                                    })
                                }
                            </ReactSlickSlider></Suspense>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default PTMyContestSlider;