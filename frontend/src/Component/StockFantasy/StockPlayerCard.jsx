import React from 'react';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Highcharts from 'highcharts';
import HighchartsReact from 'highcharts-react-official';
import { getStockCardDetails } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { Modal, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { DARK_THEME_ENABLE, GameType,SELECTED_GAMET } from '../../helper/Constants';
import Moment from "react-moment";
var dateF = 1;
class StockPlayerCard extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            graphData: {},
            dateFilter: 1,
            playerAPICard: ''
        };
    }

    componentDidMount = () => {
        this.getPlayerCardDetails(this.props.playerData);
    }


    getPlayerCardDetails = async (playerParams) => {
        let param = {
            "stock_id": playerParams.stock_id,
            "day_filter": this.state.dateFilter
        }
        var api_response_data = await getStockCardDetails(param);
        if (api_response_data.data) {
            this.setState({
                playerAPICard: api_response_data.data,
                graphData: api_response_data.data.history || [],
            }, () => {
                this.makeChart();
            })
        }
    }

    formateTooltip(tooltip, x = this.x, points = this.points) {
        let date = new Date(x)
        let format = dateF === 1 ? 'hh:mm a' : dateF === 5 ? 'DD MMM YYYY' : 'ddd, DD MMM'
        let s = Utilities.getFormatedDateTime(date, format)
        let t = `<b style="font-size: 11px;position: relative;top: -1px;">${s}</b>`;
        points.forEach((point) =>
            t += `<br/>${AL.Price}: <b>${point.y}</b>`
        );
        return t;
    }

    makeChart() {
        let { graphData } = this.state;
        let data = []
        _Map(graphData, (item) => {
            let tmpArry = [new Date(item.schedule_date).getTime(), parseFloat(item.price)]
            data.push(tmpArry)
        })
        const options = {
            credits: {
                enabled: false
            },
            chart: {
                zoomType: ''
            },
            title: {
                text: ''
            },
            xAxis: {
                visible: false,
                labels: {
                    enabled: false
                },
                gridLineColor: 'transparent',
                type: 'datetime'
            },
            yAxis: {
                gridLineColor: 'transparent',
                labels: {
                    enabled: false
                },
                title: {
                    text: ''
                }
            },
            legend: {
                enabled: false
            },
            colors: [{
                linearGradient: {
                    x1: 1,
                    x2: 1,
                    y1: 0,
                    y2: 1
                },
                stops: [
                    [0, DARK_THEME_ENABLE ? "#D0A825" : '#6C72BC'],
                    [1, DARK_THEME_ENABLE ? "#D0A82500" : '#6C72BC00']
                ]
            }],
            plotOptions: {
                area: {
                    marker: {
                        radius: 0
                    },
                    lineWidth: 1,
                    lineColor: DARK_THEME_ENABLE ? "#D0A825" : '#6C72BC',
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null
                }
            },
            tooltip: {
                formatter: this.formateTooltip,
                shared: true,
                backgroundColor: '#fff',
                borderWidth: 1,
                borderColor: DARK_THEME_ENABLE ? "#D0A825" : '#6C72BC',
                borderRadius: 4
            },
            series: [{
                type: 'area',
                name: '',
                data: data
            }]
        };
        this.setState({ graphData: options })
    }

    setDateFilter = (filter, playerData) => {
        dateF = filter;
        this.setState({ dateFilter: filter }, () => this.getPlayerCardDetails(playerData))
    }

    showPer=(pPer)=>{
        let num = pPer.toString(); //If it's not already a String
        num = num.slice(0, (num.indexOf("."))+3); //With 3 exposing the hundredths place
        Number(num); //If you need it back as a Number
        return Utilities.numberWithCommas(num)
    }

    render() {

        const { mShow, mHide, playerData, buySellAction, addToWatchList, isFrom, isPreview ,isFCap,isSPRoster,IncZIndex,isBSBtn} = this.props;
        const { playerAPICard, dateFilter } = this.state
        let cPrice = parseFloat(playerAPICard.current_price || '0');
        let pPrice = parseFloat(playerAPICard.pr_price || '0');
        let tLow = parseFloat(playerAPICard.t_min_price || '0');
        let tHigh = parseFloat(playerAPICard.t_max_price || '0');
        let twLow = parseFloat(playerAPICard.min_price || '0');
        let twHigh = parseFloat(playerAPICard.max_price || '0');
        let pDiff = cPrice - pPrice;
        let isDown = cPrice < pPrice;
        let pPer = (pDiff / pPrice) * 100;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="header-circular-modal overflow-hidden stock-fm sf-player-c"
                        className={"center-modal" + (isPreview ? ' bg-black' : '')}
                        backdropClassName={`${IncZIndex ? ' inc-zindex' : ''}`}
                    >
                        <Modal.Header >
                            <a href className="close" onClick={mHide}>
                                <i className="icon-close"></i>
                            </a>
                            <div className="modal-img-wrap">
                                <div className="wrap">
                                    <img className="player-image" src={playerData.logo ? Utilities.getStockLogo(playerData.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                                </div>
                            </div>
                            {playerAPICard.name || playerData.stock_name}
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    <div className="page-body rules-scoring-body p-0">
                                        {
                                        // isFrom !== 'stockitem' && 
                                        !isPreview && !isFCap && 
                                            <div className="btn-c">
                                                {
                                                SELECTED_GAMET == GameType.StockFantasy && isBSBtn &&
                                                    <div className="buy-sell-btn">
                                                    <a href onClick={() => buySellAction(1, playerData)} className={"btn-v-buy" + (parseInt(playerData.action || '0') === 1 ? ' selected' : '')}>
                                                        {AL.BUY}
                                                    </a>
                                                    <a href onClick={() => buySellAction(2, playerData)} className={"btn-v-sell" + (parseInt(playerData.action || '0') === 2 ? ' selected' : '')}>
                                                        {AL.SELL}
                                                    </a>
                                                </div>
                                                }
                                                
                                                <a href onClick={() => addToWatchList(playerData)} className={"btn-watch-l" + (playerData.is_wish.toString() === "1" ? ' active' : '')}>
                                                    <i className="icon-wishlist" />{playerData.is_wish.toString() === "1" ? AL.ADDED_TO_WATCHLIST : AL.ADD_TO_WATCHLIST}
                                                </a>
                                            </div>
                                        }
                                        {
                                            SELECTED_GAMET == GameType.StockPredict ?
                                            <div className="stk-dtl-prz">
                                                <span>{Utilities.getMasterData().currency_code} {cPrice.toFixed(2)}</span>
                                                <span className={`stk-pr ${(isDown ? ' down' : '')}`}>{(isDown ? "" : "+") + (this.showPer(pPer || 0) || 0) + "%"}</span>
                                                <span>{(isDown ? "" : "+") + pDiff.toFixed(2)}</span>
                                            </div>
                                            :
                                            <div style={{marginBottom:10}} className="point-c">
                                                <span className={"p1" + (isDown ? ' down' : '')}>{Utilities.numberWithCommas(cPrice.toFixed(2))}<i className={isDown ? "icon-stock_down" : "icon-stock_up"} /></span>
                                                <span className="p2">{(isDown ? "" : "+") + (this.showPer(pPer || 0) || 0) + "%"}<span>{(isDown ? "" : "+") + pDiff.toFixed(2)}</span></span>
                                                {/* <span className="p3">167.47% Gain</span> */}
                                            </div>
                                        }
                                        <div className="pts-upd">
                                            {AL.POINTS_UPDATED_ON}
                                            <Moment date={playerData.price_updated_at} format={" MMM DD - hh:mm A"} /> 
                                        </div>
                                        <div className="pts-upd">
                                            {AL.POINTS_UPDATED_AT}
                                            <Moment date={playerData.price_updated_at} format={" MMM DD - hh:mm A"} /> 
                                        </div>
                                        {this.state.graphData.series && <HighchartsReact
                                            containerProps={{ style: { height: "150px", width: '100%'} }} //marginTop: '-15px' 
                                            highcharts={Highcharts}
                                            options={this.state.graphData}
                                        />}
                                        <div className="time-vc">
                                            <a onClick={() => this.setDateFilter(1, playerData)} className={dateFilter === 1 ? 'active' : ''} href>1D</a>
                                            <a onClick={() => this.setDateFilter(2, playerData)} className={dateFilter === 2 ? 'active' : ''} href>1W</a>
                                            <a onClick={() => this.setDateFilter(3, playerData)} className={dateFilter === 3 ? 'active' : ''} href>1M</a>
                                            <a onClick={() => this.setDateFilter(4, playerData)} className={dateFilter === 4 ? 'active' : ''} href>3M</a>
                                            <a onClick={() => this.setDateFilter(5, playerData)} className={dateFilter === 5 ? 'active' : ''} href>1Y</a>
                                        </div>
                                        <div className="performance-v">
                                            <span className="lbl">{AL.PERFORMANCE}<OverlayTrigger rootClose trigger={['click']} placement="right" overlay={
                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                    <strong>{AL.LATEST_PERFORMANCE}</strong>
                                                </Tooltip>
                                            }><i className="icon-info" /></OverlayTrigger></span>
                                            <div className="details-v">
                                                <div className="todays-v low">
                                                    <span>{AL.DAY_LOW}</span>
                                                    <span className="pts">{Utilities.numberWithCommas(tLow.toFixed(2))}</span>
                                                </div>
                                                <div className="todays-v">
                                                    <span>{AL.DAY_HIGH}</span>
                                                    <span className="pts">{Utilities.numberWithCommas(tHigh.toFixed(2))}</span>
                                                </div>
                                            </div>
                                            <div className="details-v top-b">
                                                <div className="todays-v low">
                                                    <span>{AL.WEEK_LOW}</span>
                                                    <span className="pts">{Utilities.numberWithCommas(twLow.toFixed(2))}</span>
                                                </div>
                                                <div className="todays-v">
                                                    <span>{AL.WEEK_HIGH}</span>
                                                    <span className="pts">{Utilities.numberWithCommas(twHigh.toFixed(2))}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}
export default StockPlayerCard;