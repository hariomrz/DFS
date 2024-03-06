import React, { Component, lazy, Suspense } from "react";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as AppLabels from "../../helper/AppLabels";
import { Utilities } from "../../Utilities/Utilities";
import Images from "../../components/images";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

class H2HJoinedContestSlider extends Component {
    constructor(props) {
        super(props);
        this.state = {
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

    getPrizeAmount = (prize_data,status) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span style={{color:status == 1 ? '#ffffff':''}} className={"contest-prizes"}>
                             {AppLabels.WIN} {''}
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div  style={{color:status == 1 ? '#ffffff':''}} className="contest-listing-prizes" > {AppLabels.WIN} {" "}<i style={{lineHeight:'3px'}} className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <div style={{ display: 'flex',alignItems: 'center',justifyContent: 'center' }}> {AppLabels.WIN} {" "} <img style={{height:13,width:13,marginLeft:4}} className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</div>
                                : (AppLabels.WIN) +" "+ AppLabels.PRIZES
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
        const {JoineContestData} = this.props;
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay:false,
            autoplaySpeed:5000,
            centerMode: true,
            centerPadding: "13px",
            beforeChange: this.BeforeChange,
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }
    
                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }
    
                }
            ]
        };
    
        return (
            <MyContext.Consumer>
                {(context) => (
                //    <div className={BannerList.length == 1 ? 'single-banner-wrap' : ''}>
                   <div className={"joined-contest-data" }>
                    <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                        {
                            JoineContestData.map((item,idx)=>{
                                return(
                                   // backgroundImage: `url("https://via.placeholder.com/500")` 
                                 //  Utilities.getH2HLogo(item.bg_image)
                                    <div className='b-container'>
                                      <div className='main-c' >
                                            <div className='data-user-opponent'>
                                                <div className='you-container'>
                                                    <div className='image-bg'>
                                                        <img src={item.own && item.own.image ? Utilities.getThumbURL(item.own.image) : Images.USER_OPP} className='image-user'></img>
                                                    </div>
                                                </div>
                                                <div className='contest-detail-conatiner'>
                                                    <div className='prize-pool-text'>
                                                        {
                                                            item.contest_title ?
                                                            <div>{item.contest_title}</div>
                                                            :
                                                            <div>
                                                            {this.getPrizeAmount(item, 1)}
                                                            </div>
                                                        }
                                                        </div>
                                                    <img src={Images.FLASH} className='flash-icon'></img>
                                                    {
                                                        item.opponent && item.opponent.user_name ?
                                                            <div className='matched-with-opp-con'>
                                                                <div className='opp-label'>{AppLabels.MATCHED_WITH}</div>
                                                                <div onClick={(e)=>this.props.getOpponentDetail(e,item.opponent)} className='opp-name'>{item.opponent.user_name}</div>

                                                            </div>
                                                            :
                                                            <div className='waiting-for-opponent'>{AppLabels.WATEING_FOR_OPPONENT}</div>
                                                    }
                                                   

                                                </div>
                                                <div className='opp-container'>
                                                    <div className='image-bg'>
                                                        <img src={item.opponent && item.opponent.image ? Utilities.getThumbURL(item.opponent.image) : Images.USER_OPP} className='image-opp'></img>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        

                                    </div>

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

export default H2HJoinedContestSlider ;