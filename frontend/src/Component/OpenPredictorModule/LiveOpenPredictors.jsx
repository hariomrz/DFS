import React from 'react';
import { _times } from '../../Utilities/Utilities';
import { CONTEST_LIVE } from '../../helper/Constants';
import { getMyOpenPrediction } from '../../WSHelper/WSCallings';
import { NoDataView } from '../CustomComponent';
import Skeleton from 'react-loading-skeleton';
import OpenPredictorCard from './OpenPredictorCard';
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";


class LiveOpenPredictors extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            lcList: [],
            isLoading: false
        };
    };

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyContestList(item) {

        var param = {
            "category_id": item.category_id,
            "status": CONTEST_LIVE
        }
        this.setState({
            isLoading: true
        })
        getMyOpenPrediction(param).then((responseJson) => {
            this.setState({
                isLoading: false
            })
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ lcList: responseJson.data.predictions || [] })
            }
        })
    }

    componentDidMount() {
        this.getMyContestList(this.props.selectedFixture)
    }

    /**
     * @description Call this function when you want to go fo lobby screen
    */
    goToLobby = () => {
        this.props.history.push({ pathname: '/' })
    }

    render() {
        return (
            <div>
                {
                    this.state.lcList.length > 0 && <ul className="list-pred">
                        {
                            this.state.lcList.map((item, indx) => {
                                return (
                                    <OpenPredictorCard
                                        {...this.props}
                                        key={item.prediction_master_id}
                                        data={{
                                            itemIndex: indx,
                                            item: item,
                                            status: CONTEST_LIVE,
                                            LobbyData: this.props.selectedFixture
                                        }} />
                                )
                            })
                        }
                    </ul>
                }
                {
                    this.state.lcList.length === 0 && !this.state.isLoading &&
                    <NoDataView
                        BG_IMAGE={Images.no_data_bg_image}
                        // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                        CENTER_IMAGE={Images.NO_DATA_VIEW}
                        MESSAGE_1={AppLabels.NO_LIVE_CONTEST1 + ' ' + AppLabels.NO_LIVE_CONTEST2}
                        MESSAGE_2={''}
                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                        onClick={this.goToLobby}
                    />
                }
                {
                    this.state.lcList.length === 0 && this.state.isLoading &&
                    _times(7, (idx) => {
                        return (
                            this.Shimmer(idx)
                        )
                    })
                }
            </div>
        )
    }
    Shimmer = (index) => {
        return (
            <div key={index} className="contest-list m m-t-10">
                <div className="shimmer-container">
                    <div className="shimmer-top-view">
                        <div className="shimmer-image predict">
                            <Skeleton width={24} height={24} />
                        </div>
                        <div className="shimmer-line predict">
                            <div className="m-v-xs">
                                <Skeleton height={8} width={'70%'} />
                            </div>
                            <Skeleton height={34} />
                            <Skeleton height={34} />
                        </div>
                    </div>
                    <div className="shimmer-bottom-view m-0 pt-3">
                        <div className="progress-bar-default">
                            <Skeleton height={8} width={'70%'} />
                            <div className="d-flex justify-content-between">
                                <Skeleton height={4} width={110} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default LiveOpenPredictors;