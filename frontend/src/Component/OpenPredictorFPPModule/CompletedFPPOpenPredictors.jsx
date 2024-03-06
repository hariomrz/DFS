import React from 'react';
import { NoDataView } from '../CustomComponent';
import { _times, _Map } from '../../Utilities/Utilities';
import { getMyFPPOpenPrediction } from '../../WSHelper/WSCallings';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import OpenPredictorFPPCard from './OpenPredictorFPPCard';
import * as Constants from "../../helper/Constants";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import Images from '../../components/images';
import ViewProofFPPModal from "./ViewProofFPPModal";

class CompletedFPPOpenPredictors extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            ccList: [],
            isLoading: false,
            showProofModal: false,
            viewProofData: ''
        };
    };

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyContestList(item) {

        var param = {
            "category_id": item.category_id,
            "status": Constants.CONTEST_COMPLETED
        }
        this.setState({
            isLoading: true
        })
        getMyFPPOpenPrediction(param).then((responseJson) => {
            this.setState({
                isLoading: false
            })
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ ccList: responseJson.data.predictions || [] })
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

    showProofModalFn=(item)=>{
        let traverse = true;
        _Map(item.option,(opt,idx)=>{
            if(traverse && opt.is_correct == 1){
                this.setState({
                    correctAns: opt.option
                },()=>{
                    this.setState({
                        viewProofData: item,
                        showProofModal: true
                    })
                })
            }
            traverse = true
        })
    }
    hideProofModalFn=()=>{
        this.setState({
            showProofModal: false
        })
    }

    render() {
        return (
            <div>
                {
                    this.state.ccList.length > 0 && <ul className="list-pred">
                        {
                            this.state.ccList.map((item, indx) => {
                                return (
                                    <OpenPredictorFPPCard
                                        {...this.props}
                                        key={item.prediction_master_id}
                                        data={{
                                            itemIndex: indx,
                                            item: item,
                                            status: Constants.CONTEST_COMPLETED,
                                            LobbyData: this.props.selectedFixture,
                                            ShowProofModalFn: this.showProofModalFn
                                        }} />
                                )
                            })
                        }
                    </ul>
                }
                {
                    this.state.ccList.length === 0 && !this.state.isLoading &&
                    <NoDataView
                        BG_IMAGE={Images.no_data_bg_image}
                        // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                        CENTER_IMAGE={Images.NO_DATA_VIEW}
                        MESSAGE_1={AppLabels.NO_COMPLETED_CONTEST1 + ' ' + AppLabels.NO_COMPLETED_CONTEST2}
                        MESSAGE_2={''}
                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                        onClick={this.goToLobby}
                    />
                }
                {
                    this.state.ccList.length === 0 && this.state.isLoading &&
                    _times(7, (idx) => {
                        return (
                            this.Shimmer(idx)
                        )
                    })
                }
                {
                    this.state.showProofModal &&
                    <ViewProofFPPModal 
                        data={{ 
                            mShow: this.state.showProofModal,
                            mHide: this.hideProofModalFn,
                            viewProofData: this.state.viewProofData,
                            correctAns: this.state.correctAns
                        }}
                    />
                }
            </div>
        )
    }
    Shimmer = (index) => {
        return (
            <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#161920" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
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
            </SkeletonTheme>
        )
    }
}
export default CompletedFPPOpenPredictors;