import React, { Component, Suspense, lazy } from 'react';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { MyContext } from '../../views/Dashboard';
import StockFixtureCard from "./StockFixtureCard";
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));

class MyStockSlider extends Component {
    constructor(props) {
        super(props)
        this.state = {
        }
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
            autoplay: false,
            autoplaySpeed: 5000,
            centerMode: true,
            centerPadding: "15px",
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "15px",
                    }

                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "15px",
                    }

                }
            ]
        };
        const {
            List,
            isFrom,
            btnAction
        } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}>
                        {
                            _Map(List, (item, idx) => {
                                if (item.scheduled_date) {
                                    let sDate = new Date(Utilities.getUtcToLocal(item.scheduled_date))
                                    let game_starts_in = Date.parse(sDate)
                                    item['game_starts_in'] = game_starts_in;
                                    item['season_scheduled_date'] = item.scheduled_date;
                                }
                                return (
                                    <div key={item.collection_id + idx} className={List.length > 1 ? "pick-tour-card-wrap" : ''}>
                                        <StockFixtureCard
                                            data={{
                                                item: item,
                                                isFrom: isFrom,
                                                btnAction: () => btnAction(item)
                                            }}
                                        />
                                    </div>
                                )
                            })
                        }
                    </ReactSlickSlider></Suspense>
                )
                }
            </MyContext.Consumer>
        )
    }
}

export default MyStockSlider;