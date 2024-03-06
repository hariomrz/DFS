import React, { Component, lazy, Suspense } from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import * as AppLabels from "../../helper/AppLabels";
import SPFixtureCard from "./SPFixtureCard";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

class SPMyContestSlider extends Component {
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

    render() {
        const {MyContestList} = this.props;
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
                <div className="sp-my-contest">
                    <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                        {
                            MyContestList && MyContestList.length > 0 &&
                                MyContestList.map((item,idx)=>{
                                    return(
                                        <ul >
                                            <li key={idx}>
                                                <SPFixtureCard 
                                                    data={{
                                                        isFrom: 'SPLobbyMyContest',
                                                        item:item
                                                    }}
                                                    showRulesModal={this.props.showRulesModal}
                                                    onEdit={this.props.onEdit}
                                                />
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

export default SPMyContestSlider ;