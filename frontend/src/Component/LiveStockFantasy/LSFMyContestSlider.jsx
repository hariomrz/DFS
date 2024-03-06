import React, { Component, lazy, Suspense } from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import * as AL from "../../helper/AppLabels";
import LSFFixtureCard from "./LSFFixtureCard";
import { Utilities } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from "../../Component/CustomComponent";
import Images from '../../components/images';
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

class LSFMyContestSlider extends Component {
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

    // gotoDetails = (ContestListItem, event) => {
    //     this.props.gotoDetails(ContestListItem, event);
    // }
    next() {
        this.slider.slickNext();
    }
    previous() {
        this.slider.slickPrev();
    }
    BeforeChange=(oldIndex, newIndex)=>{
        // if(oldIndex == 18 && newIndex == 19){
        //     this.props.getMyLobbyFixturesList()
        // }
    }

    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
          <React.Fragment>
            {
              prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                <span>
                  {Utilities.getMasterData().currency_code}
                  {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                </span>
                : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span> <i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                  : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span> <img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                    : AL.PRIZES
            }
          </React.Fragment>
        )
    }

    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
        var amount = 0;
        if (lObj.max_value) {
            amount = parseFloat(lObj.max_value);
        } else {
            amount = parseFloat(lObj.amount);
        }
        if (lObj.prize_type == 3) {
            prizeAmount['is_tie_breaker'] = 1;
        }
        if (lObj.prize_type == 0) {
            prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
        } else if (lObj.prize_type == 2) {
            prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
        } else {
            prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
        }
        })
        return prizeAmount;
    }

    render() {
        const {MyContestList} = this.props;
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: 2.7,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay:false,
            autoplaySpeed:5000,
            centerMode: false,
            // centerPadding: "13px",
            beforeChange: this.BeforeChange,
            responsive: [
                {
                    breakpoint: 600,
                    settings: {
                        // className: "center",
                        // centerPadding: "13px",
                        slidesToScroll: 1,
                        slidesToShow: 2.2,
                    }
    
                },
                {
                    breakpoint: 500,
                    settings: {
                        slidesToScroll: 1,
                        slidesToShow: 2,
                    }
    
                },
                {
                    breakpoint: 400,
                    settings: {
                        slidesToScroll: 1,
                        slidesToShow: 1.6,
                    }
    
                }
            ]
        };

        return (
            <MyContext.Consumer>
                {(context) => (
                //    <div className={BannerList.length == 1 ? 'single-banner-wrap' : ''}>
                <div className="lsf-my-contest">
                    <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                        {
                            MyContestList && MyContestList.length > 0 &&
                                MyContestList.map((item,idx)=>{
                                    return(
                                        <ul >
                                            <li key={idx} onClick={(e) => this.props.onEdit(e, item)}>
                                                <div className="lsf-fix-card">
                                                    <div className={`crd-xtra-info ${(item.is_live == 1 || item.status == 2 || item.status == 3) ? ' display-flex' : ''}`}>
                                                        {
                                                            item.is_live == 1 ?
                                                            <span className="live-status"> 
                                                                <span className="live-dot"></span> {AL.LIVE} 
                                                            </span>
                                                            :
                                                            (item.status == 2 || item.status == 3) ?
                                                            <span className="comp-status"> 
                                                            {AL.COMPLETED} 
                                                            </span>
                                                            :
                                                            <div className={`candel-dt`} >
                                                                {
                                                                    Utilities.showCountDown(item, true) ?
                                                                    <>
                                                                        <div className="countdown time-line">
                                                                            <>Starts in </>
                                                                            {item.game_starts_in && <CountdownTimer
                                                                            deadlineTimeStamp={item.game_starts_in}
                                                                            timerCallback={this.props.timerCallback}
                                                                            hideHrs={true}
                                                                            // hideSecond={true}
                                                                            />}
                                                                        </div>
                                                                    </>
                                                                    :
                                                                    <><MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM hh:mm A " }} /> - <MomentDateComponent data={{ date: item.end_date, format: "D MMM hh:mm A " }} /></>
                                                                }
                                                            </div> 
                                                        }
                                                        {/* <img src={Images.REVERSE_ARROW_IMG} alt="reverse-arrow-img" className="rev-img" /> */}
                                                    </div>
                                                    <div className="crd-bd-hd" 
                                                        // onClick={(event) => { isFrom != 'SPLobbyMyContest' && this.props.ContestDetailShow(item, 1, event) }}
                                                        >
                                                        <span className="win-amt">{AL.WIN} {this.getPrizeAmount(item)}</span>
                                                        <span className="candel-nm">{item.contest_title ? " - " + item.contest_title : ""}</span>
                                                    </div>
                                                    {/* <div className={`amt-sec success`}> {Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(item.total_score || 0)))}{}</div> */}
                                                    
                                                    {/* <div className={`candel-dt xmb-0`} >
                                                        {
                                                            Utilities.showCountDown(item, true) ?
                                                            <>
                                                                <div className="countdown time-line">
                                                                    <>Starts in </>
                                                                    {item.game_starts_in && <CountdownTimer
                                                                    deadlineTimeStamp={item.game_starts_in}
                                                                    timerCallback={this.props.timerCallback}
                                                                    hideHrs={true}
                                                                    // hideSecond={true}
                                                                    />}
                                                                </div>
                                                            </>
                                                            :
                                                            <><MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM hh:mm A " }} /> - <MomentDateComponent data={{ date: item.end_date, format: "D MMM hh:mm A " }} /></>
                                                        }
                                                    </div> */}
                                                    <div className="rank-sec">
                                                        {/* <i className="icon-sheild"></i> */}
                                                        
                                                        <span> {(item.is_live == 1 || item.status == 2 || item.status == 3) ? (item.game_rank ? item.game_rank : 0) : '--'}</span>{AL.RANK} 
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

export default LSFMyContestSlider ;