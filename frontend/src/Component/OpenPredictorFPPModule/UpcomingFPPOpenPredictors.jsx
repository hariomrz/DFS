import React from 'react';
import { NoDataView } from '../CustomComponent';
import { _filter, _times } from '../../Utilities/Utilities';
import { getMyFPPOpenPrediction } from '../../WSHelper/WSCallings';
import Skeleton from 'react-loading-skeleton';
import OpenPredictorFPPCard from './OpenPredictorFPPCard';
import Images from '../../components/images';
import * as Constants from "../../helper/Constants";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";

class UpcomingFPPOpenPredictors extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            ucList: [],
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
            "status": Constants.CONTEST_UPCOMING
        }
        this.setState({
            isLoading: true
        })
        getMyFPPOpenPrediction(param).then((responseJson) => {
            this.setState({
                isLoading: false
            })
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ ucList: responseJson.data.predictions || [] })
            }
        })
    }

    componentDidMount() {
        this.getMyContestList(this.props.selectedFixture)
    }

    timerCompletionCall = (item) => {
        this.deleteFixture(item)
    }

    deleteFixture = (item) => {
        let fArray = _filter(this.state.ucList, (obj) => {
            return item.prediction_master_id != obj.prediction_master_id
        })
        this.setState({
            ucList: fArray
        })
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
                    this.state.ucList.length > 0 && <ul className="list-pred">
                        {
                            this.state.ucList.map((item, indx) => {
                                return (
                                    <OpenPredictorFPPCard
                                        {...this.props}
                                        key={item.prediction_master_id}
                                        data={{
                                            itemIndex: indx,
                                            item: item,
                                            status: Constants.CONTEST_UPCOMING,
                                            timerCallback: () => this.timerCompletionCall(item),
                                            LobbyData: this.props.selectedFixture
                                        }} />
                                )
                            })
                        }
                    </ul>
                }
                {
                    this.state.ucList.length === 0 && !this.state.isLoading &&
                    <NoDataView
                        BG_IMAGE={Images.no_data_bg_image}
                        // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                        CENTER_IMAGE={Images.NO_DATA_VIEW}
                        MESSAGE_1={AppLabels.NO_UPCOMING_CONTEST1 + ' ' + AppLabels.NO_UPCOMING_CONTEST2}
                        MESSAGE_2={''}
                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                        onClick={this.goToLobby}
                    />
                }
                {
                    this.state.ucList.length === 0 && this.state.isLoading &&
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

export default UpcomingFPPOpenPredictors;