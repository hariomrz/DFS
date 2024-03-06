import React from 'react';
import ls from 'local-storage';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { getMyDFSTournament } from '../../WSHelper/WSCallings';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { _times,Utilities ,_Map} from '../../Utilities/Utilities';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { AppSelectedSport, DARK_THEME_ENABLE } from '../../helper/Constants';
import InfiniteScroll from 'react-infinite-scroll-component';
import DFSTourCard from "./DFSTournCard";


export default class DFSTourList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            status: 0,
            limit: 20,
            offset: 0,
            hasMore: false,
            isLoading: false,
            TourList:[],
            MerchandiseList:[]
        }
    }

    componentWillMount() {
        if(Utilities.getMasterData().a_dfst == 1){
            ls.set('isDfsTourEnable',true)
        }
        this.setLocationStateData()
    }

    componentDidMount() {
    }    
    
    setLocationStateData=()=>{
        if(this.props && this.props.location && this.props.location.state){
            const {status} = this.props.location.state;
            this.setState({
                status: status
            },()=>{
                this.getMyTournamentList()
            })
        }
    }

    getMyTournamentList() {
        var param = {
            "sports_id": AppSelectedSport,
            "status": this.state.status
        }

        param['limit'] = this.state.limit;
        param['offset'] = this.state.offset;

        if (!param.offset || param.offset == 0) {
            this.setState({ isLoading: true })
        }
        getMyDFSTournament(param).then((responseJson) => {
            this.setState({
                isLoading: false
            })
            if (responseJson.response_code === WSC.successCode) {
                let data = responseJson.data;
                if (param.offset == 0) {
                    this.setState({
                        TourList: data.tournament_list,
                        MerchandiseList: data.merchandise_list,
                        hasMore : data.tournament_list.length >= param.limit,
                        offset: data.tournament_list.length,
                    })
                }
                else{
                    this.setState({
                        TourList: [...this.state.TourList, ...data.tournament_list],
                        hasMore : data.tournament_list.length >= param.limit,
                        offset: this.state.offset + data.tournament_list.length,
                    })
                }
            }
        })
    }

    fetchMoreData = () => {
        if (!this.state.isLoading && this.state.hasMore) {
            this.getMyTournamentList()
        }
    }

    joinTournament=(item)=>{
        const {status} = this.state;
        item['is_tournament'] = '1'
        let isFor = (item.status == 2 || item.status == 3) ? 'completed' : 'upcoming';
        let leaguename = item.league_name.replace(/ /g, '');
        let tournamentId = item.tournament_id;
        let leagueId = item.league_id;
        let dateformaturl = Utilities.getUtcToLocal(item.start_date);//season_scheduled_date
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let tourPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/tournament/' + tournamentId + "/" + leagueId + "/"  + leaguename + "/" + dateformaturl
        this.props.history.push({ 
            pathname: tourPath.toLowerCase(), 
            state: {
                data: item,
                isFor: isFor || 'upcoming',
                MerchandiseList: this.state.MerchandiseList
            } 
        })
    }

    render() {
        const { status,isLoading, hasMore,TourList,MerchandiseList } = this.state;
        const HeaderOption = {
            back: true,
            title: status == '2' ? AppLabels.COMPLETED_TOURNAMENTS : status == '1' ? AppLabels.LIVE_TOURNAMENTS : AppLabels.UPCOMING_TOURNAMENTS,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed pick-tour-list">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTourList.title}</title>
                            <meta name="description" content={MetaData.DFSTourList.description} />
                            <meta name="keywords" content={MetaData.DFSTourList.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <InfiniteScroll                                    
                            dataLength={TourList.length}
                            pullDownToRefresh={false}
                            hasMore={hasMore && !isLoading}
                            next={this.fetchMoreData.bind(this)}
                        >
                            {
                                TourList && TourList.length > 0 && !isLoading &&
                                _Map(TourList,(item,idx)=>{
                                    return(
                                        <DFSTourCard 
                                            data={{
                                                item: item,
                                                isFrom: status,
                                                MerchandiseList: MerchandiseList,
                                                joinTournament: this.joinTournament.bind(this),
                                                history: this.props.history
                                            }}
                                        />
                                    )
                                })
                            }
                        </InfiniteScroll>
                        {
                            TourList.length === 0 && isLoading &&
                            <div className="mycontest-shimmer-wrap">
                                {
                                    _times(7, (idx) => {
                                        return (
                                            this.Shimmer(idx)
                                        )
                                    })
                                }
                            </div>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
    Shimmer = (index) => {
        return (
            <SkeletonTheme key={index} color={DARK_THEME_ENABLE ? "#030409" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>  
                <div className="contest-list pickem-lobby-shimmer">
                    <div className="shimmer-container">
                        <div className="shimmer-top-view">
                            <div className="shimmer-top-inner-view">
                                <div className="shimmer-image predict ">
                                    <Skeleton width={24} height={24} circle={true} />
                                </div>
                                <div className="shimmer-line predict">
                                    <Skeleton height={34} width={34} />
                                </div>                                                        
                                <div className="shimmer-line predict">
                                    <Skeleton height={14} width={'50%'} />
                                </div>                                                        
                            </div>
                            <div className="shimmer-top-inner-view">
                                <div className="shimmer-image predict">
                                    <Skeleton width={24} height={24} circle={true} />
                                </div>
                                <div className="shimmer-line predict">
                                    <Skeleton height={34} width={34} />
                                </div>                                                        
                                <div className="shimmer-line predict">
                                    <Skeleton height={14} width={'50%'} />
                                </div>                                                        
                            </div>
                        </div>
                        <div className="shimmer-bottom-view m-0">
                            <div className="progress-bar-default">
                                <Skeleton height={8} width={'100%'} />
                            </div>
                            <div className="progress-bar-default">
                                <Skeleton height={8} width={'100%'} />
                            </div>
                        </div>
                    </div>
                </div>
            </SkeletonTheme>
        )
    }
}