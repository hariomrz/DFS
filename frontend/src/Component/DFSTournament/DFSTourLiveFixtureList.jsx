import React, { Component} from "react";
import Helmet from 'react-helmet';
import ls from 'local-storage';
import MetaData from '../../helper/MetaData';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import { _filter, _Map ,Utilities} from '../../Utilities/Utilities';
import { getDFSTourMatch} from '../../WSHelper/WSCallings';
import { MyContext } from '../../views/Dashboard';
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import { NoDataView } from '../CustomComponent';
import Images from '../../components/images';
import * as Constants from "../../helper/Constants";
import CustomHeader from '../../components/CustomHeader';
import DFSTourFixtureCard from "./DFSTourFixtureCard";
import InfiniteScroll from "react-infinite-scroll-component";
var globalThis = null;

/**
  * @description Display shimmer effects while loading list
  * @return UI components
*/
const Shimmer = ({ index }) => {
    return (
        <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#161920" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div key={index} className="pickem-card-shimmer m">
                <div className="shimmer-container">
                    <div className="shimmer-top-view text-center">
                        <div className="shimmer-line">
                            <Skeleton height={15} width={100}  />
                        </div>
                    </div>
                    <div className="shimmer-bottom-view">
                        <div className="display-table">
                            <div className="display-table-cell v-mid">
                                <Skeleton circle={true} height={46} width={46} />
                            </div>
                            <div className="display-table-cell v-mid">
                                <Skeleton circle={true} height={46} width={46} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
        </SkeletonTheme>
    )
}

class DFSTourLiveFixtureList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            HOS: {
                back: true,
                fixture: true,
                // title: '',
                hideShadow: false,
                isPrimary: Constants.DARK_THEME_ENABLE ? false : true,
                status: 1
            },
            ShimmerList: [1, 2, 3, 4, 5],
            PickemConfigData:'',
            showShareM: false,
            TourData: [] ,
            TourMatchList:[],
            isListLoading: false,
            pickUID: '',
            pickData: [],
            itemIndex: '',
            limit: 20,
            offset: 0,
            hasMore: false,
        }
    }

    componentDidMount() { 
        globalThis = this;            
    }
    
    componentWillMount() {
        if(Utilities.getMasterData().a_dfst == 1){
            ls.set('isDfsTourEnable',true)
        }
        this.setLocationStateData()   
    }    

    setLocationStateData=()=>{
        if( this.props && this.props.location && this.props.location.state) {
            const {TourData} = this.props.location.state
            this.setState({
                TourData: TourData,
            },()=>{
                this.getTournamentMatch()  
            })
        }
    }

    getTournamentMatch=()=>{   
        let param = {
            "tournament_id":this.state.TourData.tournament_id,
            "league_id":this.state.TourData.league_id,
            "type":'live'
        }
        param['limit'] = this.state.limit;
        param['offset'] = this.state.offset;
        if (!param.offset || param.offset == 0) {
            this.setState({ isListLoading: true })
        }
        getDFSTourMatch(param).then((responseJson) => {
            this.setState({ isListLoading: false })
            if (responseJson.response_code === WSC.successCode) {
                let data = responseJson.data
                if (param.offset == 1) {
                    this.setState({
                        TourMatchList: data,
                        hasMore : data.length >= param.limit,
                        offset: this.state.offset + data.length,
                    })
                }
                else{
                    this.setState({
                        TourMatchList: [...this.state.TourMatchList, ...data],
                        hasMore : data.length >= param.limit,
                        offset: this.state.offset + data.length,
                    })
                }
            }
        })
    }

    fetchMoreData = () => {
        if (!this.state.isListLoading && this.state.hasMore) {
            this.getTournamentMatch()
        }
    }

    removePrecitedQue = (item) => {
        this.deleteFixture(item)
    }

    timerCallback = (item) => {
        this.deleteFixture(item)
    }

    deleteFixture = (item) => {
        this.setState({
            isListLoading: true
        })
        let fArray = _filter(this.state.FixtureList, (obj) => {
            return item.pickem_id != obj.pickem_id
        })
        this.setState({
            FixtureList: fArray,
            isListLoading: false
        },()=>{
            if(fArray.length <= 5 && this.state.hasMore){
                this.setState({
                    offset: fArray.length
                },()=>{
                    this.fetchMoreData()
                })
            }
        })
    }

    renderListView=(list,isFor)=>{
        let isFrom = isFor;
        const {PickemConfigData,TourData} = this.state;
        return(
            <div className="tour-live-sec">
                <div className="tour-tab-prd-wrap">
                    {
                        _Map(list,(item,idx)=>{
                            return(
                                <DFSTourFixtureCard 
                                    {...this.props} 
                                    data={item}
                                    isFrom={isFrom}
                                    timerCallback= {() => globalThis.timerCallback(item)}
                                />
                            )
                        })
                    }
                </div>
            </div>
        )
    }

    renderNoDataView=()=>{
        return(
            <NoDataView 
                BG_IMAGE={Images.no_data_bg_image}
                // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                MESSAGE_1={AL.MORE_COMING_SOON}
                MESSAGE_2={''}//{AL.NO_DATA_TO_SHOW}
            />
        )
    }

    render() {
        const {
            HOS,
            TourData,
            TourMatchList,
            isListLoading,
            resetPick,
            editPick,
            pickUID,
            pickData,
            ShimmerList,
            itemIndex,
            hasMore
        } = this.state;
        const {
        } = this.props;
       return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container pick-tour-conatiner tour-live-list-wrap">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTourLiveList.title}</title>
                            <meta name="description" content={MetaData.DFSTourLiveList.description} />
                            <meta name="keywords" content={MetaData.DFSTourLiveList.keywords}></meta>
                        </Helmet>
                        <CustomHeader 
                            {...this.props} 
                            LobyyData={TourData} 
                            HeaderOption={HOS}  
                        />
                        <InfiniteScroll                                    
                            dataLength={TourMatchList.length}
                            pullDownToRefresh={false}
                            hasMore={hasMore && !isListLoading}
                            next={this.fetchMoreData.bind(this)}
                        >
                            {
                                !isListLoading && TourMatchList && TourMatchList.length > 0 &&
                                this.renderListView(TourMatchList,'LTLobby')
                            }
                                
                        </InfiniteScroll>
                        {
                            isListLoading && TourMatchList && TourMatchList.length == 0 &&
                            ShimmerList.map((item, index) => {
                                return (
                                    <Shimmer key={index} index={index} />
                                )
                            })
                        }
                        {
                            !isListLoading && TourMatchList && TourMatchList.length == 0 &&
                            this.renderNoDataView()
                        }
                    </div>
                )
                }
            </MyContext.Consumer>
        )
    }
}

export default DFSTourLiveFixtureList;