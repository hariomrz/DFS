import React, { Component } from 'react'
import { Helmet } from 'react-helmet'
import MetaData from '../../helper/MetaData'
import CustomHeader from '../../components/CustomHeader'
import {
  DARK_THEME_ENABLE,
  setValue,
  StockSetting,
} from '../../helper/Constants'
import * as AL from '../../helper/AppLabels'
import { _Map, Utilities } from '../../Utilities/Utilities'
import WSManager from '../../WSHelper/WSManager'
import StockItem from './StockItem'
import {
  addRemoveStockWishlist,
  getStockStatictics,
} from '../../WSHelper/WSCallings'
import * as WSC from '../../WSHelper/WSConstants'
import Moment from 'react-moment'
class StockFStatistics extends Component {
  constructor(props) {
    super(props)
    this.state = {
      HeaderOption: {
        back: true,
        isPrimary: DARK_THEME_ENABLE ? false : true,
        title: '',
        screentitle: AL.STATISTICS,
        minileague: true,
        leagueDate: {
          scheduled_date: '', //new Date(),
          end_date: '',
        },
        currentDate_btm: Date().toLocaleString(),
        market_time: '9:20 AM to 3:20 PM',
      },
      filerTimeV: [
        {
          label: AL.ODAY,
          id: '1',
        },
        {
          label: AL.FDAYS,
          id: '2',
        },
        {
          label: AL.OMONTHS,
          id: '3',
        },
      ],
      filterBy: '1',
      viewMoreType: 0,
      statsData: {},
      viewMoreG: true,
      viewMoreL: true,
      StockSettingValue: [],
    }
  }

  componentDidMount() {
    this.getStatsData()
  }

  getStatsData = () => {
    let param = {
      day_filter: this.state.filterBy,
      type: this.state.viewMoreType,
    }
    getStockStatictics(param).then((responseJson) => {
      if (responseJson.response_code == WSC.successCode) {
        if (this.state.viewMoreType === 0) {
          this.setState({
            statsData: responseJson.data,
          })
        } else if (this.state.viewMoreType === 1) {
          let data = this.state.statsData
          data['gainers'] = responseJson.data
          this.setState({
            statsData: data,
          })
        } else if (this.state.viewMoreType === 2) {
          let data = this.state.statsData
          data['losers'] = responseJson.data
          this.setState({
            statsData: data,
          })
        }
      }
    })
  }

  handleTimeFilter = (id) => {
    if (this.state.filterBy !== id) {
      this.setState(
        {
          filterBy: id,
          viewMoreType: 0,
          viewMoreG: true,
          viewMoreL: true,
        },
        () => {
          this.getStatsData()
        },
      )
    }
  }

  addToWatchList = (item, type) => {
    let tmpstatsData = this.state.statsData
    let typeArray = tmpstatsData[type]
    let idx = typeArray.indexOf(item)
    if (WSManager.loggedIn()) {
      let param = {
        stock_id: item.stock_id,
      }
      addRemoveStockWishlist(param).then((responseJson) => {
        if (responseJson.response_code == WSC.successCode) {
          typeArray[idx]['is_wish'] =
            typeArray[idx]['is_wish'] == '1' ? '0' : '1'
          let Msg =
            typeArray[idx]['is_wish'] == '1'
              ? 'Stock added to watchlist'
              : 'Stock removed from watchlist'
          Utilities.showToast(Msg, 5000)
          tmpstatsData[type] = typeArray
          this.setState({
            statsData: tmpstatsData,
          })
        }
      })
    } else {
      this.props.history.push('/signup')
    }
  }

  viewMore = (type) => {
    this.setState(
      {
        viewMoreType: type,
        viewMoreG: type == 1 ? false : this.state.viewMoreG,
        viewMoreL: type == 2 ? false : this.state.viewMoreL,
      },
      () => {
        this.getStatsData()
      },
    )
  }

  render() {
    const {
      HeaderOption,
      filerTimeV,
      filterBy,
      statsData,
      StockSettingValue,
    } = this.state
    let day =
      this.state.filterBy == 1
        ? AL.YESTERDAY
        : this.state.filterBy == 2
        ? AL.THIS_WEEK
        : this.state.filterBy == 3
        ? AL.THIS_MONTH
        : ''
    let currentDate = Date.now()
    return (
      <div className="web-container stocks-stats">
        <Helmet titleTemplate={`${MetaData.template} | %s`}>
          <title>{MetaData.STATISTICS.title}</title>
          <meta name="description" content={MetaData.STATISTICS.description} />
          <meta name="keywords" content={MetaData.STATISTICS.keywords}></meta>
        </Helmet>

        <CustomHeader HeaderOption={HeaderOption} {...this.props} />
        <div className="inner-v">
          <ul className="filter-time-v">
            {_Map(filerTimeV, (item) => {
              return (
                <li
                  key={item.id}
                  className={
                    'time-btn' + (item.id === filterBy ? ' active' : '')
                  }
                  onClick={() => this.handleTimeFilter(item.id)}
                >
                  {item.label}
                </li>
              )
            })}
          </ul>
          {/* <div className="time-upd">
            <Moment date={currentDate} format={'MMM DD - hh:mm A'} />
          </div> */}
          <>
            <div className="item-header">
              <span>
              {AL.TOP} 5 {AL.GAINER} 
              </span>
              {(statsData.gainers || []).length > 4 && this.state.viewMoreG && (
                <a onClick={() => this.viewMore(1)} href className="v-more">
                  {AL.VIEW_ALL}
                </a>
              )}
            </div>
            {(statsData.gainers || []).map((item, index) => {
              return (
                <StockItem
                  day={day}
                  isFrom="stats"
                  key={item.stock_id + index}
                  item={item}
                  addToWatchList={(obj) => this.addToWatchList(obj, 'gainers')}
                />
              )
            })}
          </>
          <>
            <div className="item-header down">
              <span>
              {AL.TOP} 5 {AL.LOSERS}  
              </span>
              {(statsData.losers || []).length > 4 && this.state.viewMoreL && (
                <a onClick={() => this.viewMore(2)} href className="v-more">
                  {AL.VIEW_ALL}
                </a>
              )}
            </div>
            {(statsData.losers || []).map((item, index) => {
              return (
                <StockItem
                  day={day}
                  isFrom="stats"
                  key={item.stock_id + index}
                  item={item}
                  down={true}
                  addToWatchList={(obj) => this.addToWatchList(obj, 'losers')}
                />
              )
            })}
          </>
        </div>
        <button
          className="btn-primary bottom btn btn-primary-bottom-stk"
          onClick={() =>
            WSManager.loggedIn()
              ? this.props.history.push('/stock-fantasy/my-watchlist')
              : this.props.history.push('/signup')
          }
        >
          {AL.VIEW_WATCHLIST}
        </button>
      </div>
    )
  }
}

export default StockFStatistics
