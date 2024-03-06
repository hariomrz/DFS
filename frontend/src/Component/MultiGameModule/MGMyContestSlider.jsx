import React, { Component, Suspense, lazy } from "react";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../InitialSetup/MyProvider';
import * as AppLabels from "../helper/AppLabels";
import { MatchCard } from "../Component/CustomComponent";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

class MGMyContestSlider extends Component {
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

    gotoDetails = (ContestListItem, event) => {
        this.props.gotoDetails(ContestListItem, event);
    }
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

    render() {
        const {FixtureData,timerCallback} = this.props;
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
                   <div className="my-lobby-fixture">
                    <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                        {
                            FixtureData.map((item,idx)=>{
                                return(
                                    <ul>
                                        <li key={idx} style={{position: 'relative',padding: '0 3px'}}>
                                            {
                                                 (item.status == 0 && item.is_live != 1) && (item.playing_announce == "1" || item.delay_minute > "0") &&
                                                <div className="match-delay-info">
                                                    {
                                                        item.playing_announce == "1" && 
                                                        <span >{AppLabels.LINEUP_OUT}</span>
                                                    }
                                                    {
                                                        item.playing_announce =="1" && item.delay_minute 
                                                        > "0" &&
                                                            <span className="seperator-class"> | </span>
                                                    }
                                                    {
                                                        item.delay_minute > "0" &&
                                                        <span>
                                                            <OverlayTrigger trigger={['click']} placement="left" overlay={
                                                                <Tooltip id="tooltip" className={"tooltip-featured" + (item.delay_message != '' ? ' display-tooltip' : ' hide-tooltip')}>
                                                                    <strong> {item.delay_message} </strong>
                                                                </Tooltip>
                                                            }>
                                                            <span  onClick={(e)=>e.stopPropagation()} className="cursor-pointer">{AppLabels.DELAYED} {item.delay_text}</span>
                                                            </OverlayTrigger>
                                                        </span>
                                                    }
                                                </div>                                            
                                            }
                                            <MatchCard item={item} gotoDetails={this.gotoDetails} fixtureCardLg={true} timerCallback={timerCallback} isFrom='MyContestSlider' />
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

export default MGMyContestSlider ;