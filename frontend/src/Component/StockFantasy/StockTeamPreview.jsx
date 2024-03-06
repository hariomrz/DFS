import React from 'react';
import { Modal, Table } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { getStockUserLineup,getStockUserLineupEquity,getSPUserLineup } from '../../WSHelper/WSCallings';
import StockItem from './StockItem';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { MomentDateComponent} from '../../Component/CustomComponent';
import { SELECTED_GAMET, GameType } from '../../helper/Constants';
import Images from '../../components/images';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import highcharts3d from "highcharts/highcharts-3d";
highcharts3d(Highcharts);
class StockTeamPreview extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            show: false,
            openTeam: '',
            teamPlayerData: {},
            CollectionData: '',
            team_name: '',
            isFrom: '',
            userName: '',
            listPieStatus:0,
            sourcePieData:{},
            salary_cap:this.props.salary_cap,
            totalAmount:0.0,
            isSPTP: SELECTED_GAMET == GameType.StockPredict ,
            allStkList: []

        };


    }

    componentDidMount() {
        this.setState({ openTeam: this.props.openTeam, CollectionData: this.props.CollectionData, isFrom: this.props.isFrom, userName: this.props.userName }, () => {
            if(this.props.isFrom === 'point'){
                this.parseTeamData({ lineup: this.props.openTeam })
            }else if (this.props.isFrom === 'roster') {
                this.parseTeamData({ lineup: this.props.preTeam })
            } else {
                this.getTeamPlayers(this.state.openTeam)
            }
        })
        this.setPieChartData(this.props.preTeam);

    }
    setPieChartData=(preTeam)=>{
        let tempList = [];
        let otherObject = {};
        let totalAmountValue= 0.0
        preTeam && preTeam.length >0 && preTeam.map((data, key) => {
            if (data.stockPrize) {
                let a = parseFloat(totalAmountValue)
                let b =  parseFloat(Utilities.getExactValue(data.stockPrize))
                let c = parseFloat(Utilities.addNumber(a,b))

                // let c = parseFloat(Utilities.addNumber(totalAmountValue,b))
                // let d = parseFloat(Utilities.addNumber(c,b))

                totalAmountValue = parseFloat(Utilities.getExactValue(c))
                tempList.push({ name: data.stock_name, y: parseFloat(data.stockPrize), action: data.action, color: data.action == 1 ? '#5DBE7D' : '#EB4A3C' })
            }
            else {
                let prize = (parseFloat(data.user_lot_size) * parseFloat(data.current_price));
                let a = parseFloat(totalAmountValue)
                let b = parseFloat(prize)
                let c = parseFloat(Utilities.addNumber(a,b))
                totalAmountValue =  parseFloat(Utilities.getExactValue(c))
                tempList.push({ name: data.stock_name, y: parseFloat(prize), action: data.action, color: data.action == 1 ? '#5DBE7D' : '#EB4A3C' })
             
            }
        })
        otherObject["name"]=AL.UNUSED
        if(this.props.isFrom === 'point' || this.props.isFrom === 'roster'){
            otherObject["y"]=parseFloat(this.state.salary_cap)
        }
        else{
            let a = parseFloat(500000)
            let b = parseFloat(totalAmountValue)
            let c = parseFloat(Utilities.subNumber(a,b))
            otherObject["y"]=  parseFloat(Utilities.getExactValue(c))

        }
        otherObject["action"]=3
        otherObject["color"]='#D8D8D8'
        tempList.push(otherObject)
        let tmpLineupArray =tempList.sort((a, b) => (b.action - a.action))
        this.setState({totalAmount:totalAmountValue})
        this.setState({sourcePieData:{
            chart: {
              type: 'pie',
              options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
              }
            },
            title: {
              text: ''
            },
            accessibility: {
              point: {
                valueSuffix: ''
              }
            },
            tooltip: {
              pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
              pie: {
                size:200,
                innerSize: 100,
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                  enabled: true,
                  format: '{point.name}',

                }
              }
            },
            credits: {
              enabled: false,
            },
            series: [{
              type: 'pie',
              name: 'Stocks',
              data: tmpLineupArray
            }]
          }
        })
    }

    getTeamPlayers = (item) => {

        let param = {
            "lineup_master_id": item.lineup_master_id,
            "collection_id": this.state.isSPTP ? this.state.CollectionData.collection_id : this.state.CollectionData.collection_master_id,
        }
        let apiCall = this.state.isSPTP ? getSPUserLineup : SELECTED_GAMET ==  GameType.StockFantasyEquity ? getStockUserLineupEquity : getStockUserLineup
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                if(this.state.isSPTP){
                    this.setState({
                        allStkList: responseJson.data.lineup,
                        team_name: responseJson.data.team_name,
                    })
                }
                else{
                    this.parseTeamData(responseJson.data)
                }
            }
        })
    }

    parseTeamData = (data) => {
        let upArry = [];
        let downArray = [];
        if(!this.props.preTeam){
            this.setPieChartData(data.lineup);
           }
        _Map((data.lineup || []), (item) => {
            let act = this.state.isFrom === 'point' ? item.type : item.action;
            if (parseInt(act || '0') === 2) {
                downArray.push(item)
            } else {
                upArry.push(item)
            }
        })

        let filterUpArry = this.filterArry(upArry)
        let filterDwnArry = this.filterArry(downArray)
        this.setState({
            team_name: data.team_name,
            teamPlayerData: {
                up_stock: filterUpArry,
                down_stock: filterDwnArry
            }
        })
    }

    filterArry=(array)=>{
        let FinalArry = []
        let tmpC = [];
        let CItem = array.filter((obj) => { return obj.player_role == 1 });
        let VCItem = array.filter((obj) => { return obj.player_role == 2 });
        if(CItem && CItem.length != 0 && CItem[0]){
            FinalArry.push(CItem[0]);
        }
        if(VCItem && VCItem.length != 0 && VCItem[0]){
            FinalArry.push(VCItem[0]);
        }
        for (var item of array) {
            // if (item.player_role == 1 || item.player_role == 2) {
            //     FinalArry.push(item);
            // }
            // else{
                if(item.player_role != 1 && item.player_role != 2){
                    tmpC.push(item)
                }
            // }
        }
        tmpC = tmpC.sort((a, b) =>  (a.name ? a.name.localeCompare(b.name) : a.stock_name.localeCompare(b.stock_name)))
       
        return  FinalArry.concat(tmpC)
    }
    
    setlistPieStatus = (value) => {
        this.setState({listPieStatus:value},()=>{
           // HeaderOpt.listPieOption(value)

        })
    }
 

    render() {
        let a = parseFloat(500000)
        let b = parseFloat(this.state.totalAmount)
        let unusedValue = this.props.isFrom === 'point' || this.props.isFrom === 'roster' ? parseFloat(this.state.salary_cap) : Utilities.subNumber(a,b)

        const { isViewAllShown, onViewAllHide, status, total_score,StockSettingValue, isTeamPrv,IncZIndex} = this.props;
        const { teamPlayerData, openTeam, isFrom, team_name, userName, CollectionData,isSPTP,allStkList } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={isViewAllShown} onHide={onViewAllHide} bsSize="large" className={`stock-team-view-modal`}>
                            <Modal.Header>
                                <Modal.Title>
                                    <a href onClick={onViewAllHide} className={"modal-close" + (SELECTED_GAMET == GameType.StockFantasyEquity ? ' left-side' : '')}>
                                        <i className="icon-close"></i>
                                    </a>
                                    <div className="name-container">
                                        <div className="team-name">
                                            {
                                                isFrom === "preview"
                                                    ?
                                                    team_name
                                                    :
                                                    isFrom === "point"
                                                        ?
                                                        userName : AL.TEAM_PREVIEW.replace(AL.Team, AL.PORTFOLIO)
                                            }
                                        </div>
                                        {isFrom === "point" && <div className="contests-detail">{status == 1 ? AL.LIVE : AL.COMPLETED}{CollectionData.scheduled_date ? 
                                        <> | <MomentDateComponent data={{ date: CollectionData.scheduled_date, format: "DD MMM hh:mm a" }} /> <MomentDateComponent data={{ date: CollectionData.end_date, format: "- hh:mm a" }} /> </> : ''}
                                        </div>}
                                    </div>

                                    {
                                        SELECTED_GAMET == GameType.StockFantasyEquity &&
                                        <div className="pie-list-view">
                                            <div className="header-pie-list-view">
                                                <div className={"list-view-container" + (this.state.listPieStatus == 0 ? ' active' : '')}>
                                                    <i className={"icon-list-ic icon-list" + (this.state.listPieStatus == 0 ? ' list-active' : '')} onClick={() => this.setlistPieStatus(0)} >

                                                    </i>
                                                </div>
                                                <div className={"pi-view-container" + (this.state.listPieStatus == 1 ? ' active' : '')} >
                                                    <i className={"icon-pie icon-pi" + (this.state.listPieStatus == 1 ? ' pi-active' : '')} onClick={() => this.setlistPieStatus(1)} >

                                                    </i>
                                                </div>

                                            </div>

                                        </div>
                                    }
                                    

                                </Modal.Title>
                            </Modal.Header>
                            {
                                isSPTP ?
                                <Modal.Body>
                                    <div className="sp-team-prv-body">
                                        <Table className="SPTP-table">
                                            <thead>
                                                <tr>
                                                    <th>{AL.SCRIPS}</th>
                                                    <th>{AL.PREDICTION}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {
                                                    (allStkList || []).map((item,idx)=>{
                                                        return(
                                                            <tr>
                                                                <td className="stk-det-sec" onClick={(e)=>this.props.PlayerCardShow(e, item)}>
                                                                    <div className="img-wrap">
                                                                        <img src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                                                                    </div>
                                                                    <div className="player-name-container">
                                                                        <div className="player-name">{item.display_name || item.stock_name || item.name}</div>
                                                                        <div className="team-vs-team down">
                                                                            <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} />
                                                                            {item.current_price}
                                                                            <span>{item.price_diff}</span>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td className="stk-pr-amt">
                                                                   {Utilities.numberWithCommas(parseFloat(item.user_price).toFixed(2))}
                                                                </td>
                                                            </tr>
                                                        )
                                                    })
                                                }
                                            </tbody>
                                        </Table>
                                    </div>
                                </Modal.Body>
                                :
                                <Modal.Body style={isFrom === 'point' ? {minHeight: '100%',paddingBottom: '60px'} :  SELECTED_GAMET == GameType.StockFantasyEquity && this.state.listPieStatus == 0 ? {paddingBottom: '50px'} : {} }>
                                    <div>
                                        {
                                            this.state.listPieStatus == 0 ?
                                                <div>
                                                    {(teamPlayerData.up_stock || []).length > 0 && <div className="player-list-container">
                                                        <div className="item-header">
                                                            <span>{AL.BUY_STOCK} <i className="icon-stock_up" /></span>
                                                        </div>
                                                        {
                                                            (teamPlayerData.up_stock || []).map((item, index) => {
                                                                return (
                                                                    <StockItem isPreview={true} isFrom={isFrom === 'roster' ? '' : isFrom} key={item.stock_id + index} item={item} openTeam={openTeam} StockSettingValue={StockSettingValue} isTeamPrv={isTeamPrv || false} />
                                                                )
                                                            })
                                                        }
                                                    </div>}
                                                    {
                                                        (teamPlayerData.down_stock || []).length > 0 && <div className="player-list-container down">
                                                            <div className="item-header">
                                                                <span>{AL.SELL_STOCK} <i className="icon-stock_down" /></span>
                                                            </div>
                                                            {
                                                                (teamPlayerData.down_stock || []).map((item, index) => {
                                                                    return (
                                                                        <StockItem isPreview={true} isFrom={isFrom === 'roster' ? '' : isFrom} key={item.stock_id + index} item={item} openTeam={openTeam} down={true} StockSettingValue={StockSettingValue} isTeamPrv={isTeamPrv || false} />
                                                                    )
                                                                })
                                                            }
                                                        </div>
                                                    }
                                                    {isFrom === 'point' && <button className="btn-primary bottom btn btn-primary-bottom-stk pts" ><span>{AL.TOTAL_POINTS}</span><span style={{ fontSize: 20 }}>{total_score}</span></button>}
                                                    
                                                </div>
                                                :
                                                <div>
                                                    <div className='container-top'>
                                                        <div className='buy-container'>
                                                            <div className='oval'> </div>
                                                            <div className='b-s-o-text'>{AL.BUY} </div>
                                                        </div>
                                                        <div className='sell-container'>
                                                            <div className='oval'> </div>
                                                            <div className='b-s-o-text'>{AL.SELL} </div>
                                                        </div>
                                                        <div className='other-container'>
                                                            <div className='oval'> </div>
                                                            <div className='b-s-o-text'>{AL.UNUSED} </div>
                                                        </div>

                                                    </div>
                                                    <div style={{ marginTop: 20 }} className='pie-chart'>
                                                        <HighchartsReact
                                                            highcharts={Highcharts}
                                                            options={this.state.sourcePieData}
                                                        />
                                                    </div>
                                                </div>
                                        }                                                                             
                                    </div>
                                    
                                
                                </Modal.Body>
                            }
                            <Modal.Footer >
                                {
                                    this.state.listPieStatus == 0 && SELECTED_GAMET == GameType.StockFantasyEquity &&
                                    <div className='bottom-conatiner'>
                                        <div className='total-conatiner'>
                                            <div className='total'>{AL.INVESTED}{' + '}{AL.UNUSED}</div>
                                            <div className='total-amount'>{Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(Utilities.getExactValue(this.state.totalAmount)))} {" + "}{Utilities.numberWithCommas(unusedValue)}</div>

                                        </div>
                                    </div>
                                }
                        </Modal.Footer>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default StockTeamPreview;