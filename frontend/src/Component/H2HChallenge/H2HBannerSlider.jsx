import React, { Component, lazy, Suspense } from "react";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as AppLabels from "../../helper/AppLabels";
import { Utilities } from "../../Utilities/Utilities";
import Images from "../../components/images";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

class H2HBannerSlider extends Component {
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

    render() {
        const {BannerData} = this.props;
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
                   <div className={"banner-data" }>
                    <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                        {
                            BannerData.map((item,idx)=>{
                                return(
                                   // backgroundImage: `url("https://via.placeholder.com/500")` 
                                 //  Utilities.getH2HLogo(item.bg_image)
                                    <div className='b-container'>
                                      <div style={{backgroundImage:`url(${Utilities.getH2HLogo(item.bg_image)})` }} className='image' >
                                      <div className='banner-inner-bg'>
                                            <div className='data-c'>
                                                <img alt='' src={Utilities.getH2HLogo(item.image_name)} className="image"></img>

                                                <div className="label-name">{item.name}</div>

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

export default H2HBannerSlider ;