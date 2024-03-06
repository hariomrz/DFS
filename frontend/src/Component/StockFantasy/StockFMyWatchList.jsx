import React, { Component } from 'react'
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { DARK_THEME_ENABLE,setValue, StockSetting } from '../../helper/Constants';
import * as AL from "../../helper/AppLabels";
import StockItem from './StockItem';
import { getStockWishlist, addRemoveStockWishlist } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import { _Map, Utilities } from '../../Utilities/Utilities';

class StockFMyWatchList extends Component {
    constructor(props) {
        super(props);
        this.state = {
            HeaderOption: {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                title: AL.MY_WATCHLIST
            },
            filerTimeV: [
                {
                    label: AL.ODAY,
                    id: '1'
                },
                {
                    label: AL.FDAYS,
                    id: '2'
                },
                {
                    label: AL.OMONTHS,
                    id: '3'
                },
            ],
            myList: [],
            StockSettingValue: [],
            filterBy: '1',
            viewMoreType: 0,
            isLoading:false
        }
    }

    componentDidMount() {
        this.callGetStockWishlist()
    }

    callGetStockWishlist() {
        let param = {
            "day_filter": this.state.filterBy
        }
        this.setState({ isLoading: true })
        getStockWishlist(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let data = responseJson.data || [];
                _Map(data, (item) => {
                    item['is_wish'] = '1'
                })
                this.setState({
                    myList: data
                })
            }
        })
    }

    addToWatchList = (item) => {
        let idx = this.state.myList.indexOf(item)
        let param = {
            "stock_id": item.stock_id,
        }
        addRemoveStockWishlist(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let tmpAllList = this.state.myList;
                tmpAllList.splice(idx, 1)
                let Msg = 'Stock removed from watchlist'
                Utilities.showToast(Msg, 5000);
                this.setState({
                    myList: tmpAllList
                });
            }
        })
    }

    handleTimeFilter = (id) => {
        if (this.state.filterBy !== id) {
            this.setState({
                filterBy: id
            }, () => {
                this.callGetStockWishlist(); //this.getStatsData()
            })
        }
    }

    // getStatsData = () => {
    //     let param = {
    //         "day_filter": this.state.filterBy,
    //         "type": this.state.viewMoreType
    //     }
    //     getStockStatictics(param).then((responseJson) => {
    //         if (responseJson.response_code == WSC.successCode) {
    //             this.setState({
    //                 statsData: responseJson.data
    //             })
    //         }
    //     })
    // }

    render() {
        const { HeaderOption, myList,StockSettingValue,filerTimeV ,filterBy,isLoading} = this.state
        return (
            <div className="web-container stocks-stats" >
                <Helmet titleTemplate={`${MetaData.template} | %s`}>
                    <title>{MetaData.MYWATCHLIST.title}</title>
                    <meta name="description" content={MetaData.MYWATCHLIST.description} />
                    <meta name="keywords" content={MetaData.MYWATCHLIST.keywords}></meta>
                </Helmet>

                <CustomHeader
                    HeaderOption={HeaderOption}
                    {...this.props}
                />
                <div style={{ marginTop: 56 }} className="inner-v">
                    <ul className="filter-time-v" style={{paddingBottom: 20}}>
                        {
                            _Map(filerTimeV, (item) => {
                                return (
                                    <li key={item.id} className={"time-btn" + (item.id === filterBy ? ' active' : '')}
                                        onClick={() => this.handleTimeFilter(item.id)}
                                    >
                                        {item.label}
                                    </li>
                                )
                            })
                        }
                    </ul>
                    <div className="item-header watchlist">
                        <span>{AL.STOCKS}</span>
                        <span></span>
                    </div>
                    {
                        !isLoading && (myList).map((item, index) => {
                            return (
                                <StockItem isFrom='wishlist' key={item.stock_id + index} item={item} down={item.price_diff < 0} addToWatchList={this.addToWatchList} 
                               
                                />
                            )
                        })
                    }
                </div>
                <button className="btn-primary bottom btn btn-primary-bottom-stk" onClick={() => this.props.history.push('/stock-fantasy/statistics')}>{AL.VIEW} {AL.STATISTICS}</button>
            </div>
        )
    }
}

export default StockFMyWatchList;