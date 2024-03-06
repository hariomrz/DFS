import React, { Component, Suspense, lazy } from 'react';
import { _Map } from '../../Utilities/Utilities';
import { MyContext } from '../../views/Dashboard';
import * as AL from "../../helper/AppLabels";
import DFSTourCard from "./DFSTournCard";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

class DFSTourSlider extends Component {
    constructor(props) {
        super(props)
        this.state = {
        }
    }

    joinTournament=(item)=>{
        this.props.joinTournament(item)
    }

    render() {
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
        const {
            List,
            isFrom,
            MerchandiseList
        } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <React.Fragment>
                        <div className="label-text">
                            {AL.TOURNAMENT}
                            <a href onClick={this.props.viewAll}>{AL.VIEW} {AL.ALL}</a>
                        </div>
                        <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                            {
                                _Map(List,(item,idx)=>{
                                    item['is_tournament'] = '1'
                                    return(
                                        <div className="pick-tour-card-wrap">
                                            <DFSTourCard
                                            data={{
                                                item: item,
                                                itemIndex: idx,
                                                isFrom: isFrom,
                                                showHTPModal: ()=>this.showHTPModal(),
                                                joinTournament: ()=>this.joinTournament(item),
                                                MerchandiseList: MerchandiseList
                                            }}
                                        />
                                        </div>                      
                                    )
                                })
                            }                        
                        </ReactSlickSlider></Suspense>
                    </React.Fragment>
                )
                }
            </MyContext.Consumer>
        )
    }
}

export default DFSTourSlider;