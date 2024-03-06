import React from 'react';
import { Modal, Table } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { getLSFUserLineup } from '../../WSHelper/WSCallings';
import StockItem from '../StockFantasy/StockItem';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { MomentDateComponent} from '../../Component/CustomComponent';
import { SELECTED_GAMET, GameType } from '../../helper/Constants';
import Images from '../../components/images';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import highcharts3d from "highcharts/highcharts-3d";
highcharts3d(Highcharts);
class LSFTeamPreview extends React.Component {
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
            let stockPrize = (parseFloat(data.lot_size) * parseFloat(data.current_price) || 0) 
            if (stockPrize) {
                let a = parseFloat(totalAmountValue)
                let b =  data.status == '0' ? parseFloat(data.total_trade_value || 0) : parseFloat(Utilities.getExactValueSP(stockPrize))
                let c = parseFloat(Utilities.addNumber(a,b))

                // let c = parseFloat(Utilities.addNumber(totalAmountValue,b))
                // let d = parseFloat(Utilities.addNumber(c,b))

                totalAmountValue = parseFloat(Utilities.getExactValueSP(c))
                if(parseInt(data.lot_size) > 0 ){
                    tempList.push({ name: data.stock_name, y: data.status == '0' ? parseFloat(totalAmountValue) : parseFloat(stockPrize), action: data.action, color: '#5DBE7D' })
                }
            }
            else {
                let prize = (parseFloat(data.lot_size) * parseFloat(data.current_price));
                let a = parseFloat(totalAmountValue)
                let b = data.status == '0' ? parseFloat(data.total_trade_value || 0) : parseFloat(prize)
                let c = parseFloat(Utilities.addNumber(a,b))
                totalAmountValue =  parseFloat(Utilities.getExactValueSP(c))
                // if(parseInt(data.lot_size) > 0){
                    tempList.push({ name: data.stock_name, y: data.status == '0' ? parseFloat(totalAmountValue) : parseFloat(prize), action: data.action, color: '#5DBE7D' })
                // }
             
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
            otherObject["y"]=  parseFloat(Utilities.getExactValueSP(c)) 
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
            "collection_id": this.state.CollectionData.collection_master_id
        }
        let apiCall = getLSFUserLineup
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
        let downArray = [];
        let upArry = [];
        if(!this.props.preTeam){
            this.setPieChartData(data.lineup);
           }
        // _Map((data.lineup || []), (item) => {
        //     let act = this.state.isFrom === 'point' ? item.type : item.action;
        //     if (parseInt(act || '0') === 2) {
        //         downArray.push(item)
        //     } else {
        //         if(parseInt(item.lot_size) > 0){
        //             upArry.push(item)
        //         }
        //     }
        // })

        let filterUpArry = data.lineup //this.filterArry(data.lineup)
        this.setState({
            team_name: data.team_name,
            teamPlayerData: {
                up_stock: filterUpArry
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
                        <Modal show={isViewAllShown} onHide={onViewAllHide} bsSize="large" className={`stock-team-view-modal lsf-stock-preview`}>
                            <Modal.Header>
                                <Modal.Title>
                                    <a href onClick={onViewAllHide} className={"modal-close left-side"}>
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
                                            <div className='budget'>
                                                {Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(this.state.salary_cap)))}
                                                {
                                                    CollectionData.brokerage && parseFloat(CollectionData.brokerage) > 0 &&
                                                    <>{' (' + CollectionData.brokerage + '%)'}</>
                                                }
                                            </div>
                                        </div>
                                        {isFrom === "point" && <div className="contests-detail">{status == 1 ? AL.LIVE : AL.COMPLETED}{CollectionData.scheduled_date ? 
                                        <> | <MomentDateComponent data={{ date: CollectionData.scheduled_date, format: "DD MMM hh:mm a" }} /> <MomentDateComponent data={{ date: CollectionData.end_date, format: "- hh:mm a" }} /> </> : ''}
                                        </div>}
                                    </div>

                                   
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
                                    

                                </Modal.Title>
                            </Modal.Header>
                            
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
                                                                <>
                                                                {
                                                                    // item.lot_size && parseInt(item.lot_size) > 0 &&
                                                                    <StockItem isPreview={true} isFrom={isFrom === 'roster' ? '' : isFrom} key={item.stock_id + index} item={item} openTeam={openTeam} StockSettingValue={StockSettingValue} isTeamPrv={isTeamPrv || false} />
                                                                }
                                                                </>
                                                            )
                                                        })
                                                    }
                                                </div>}
                                                {isFrom === 'point' && <button className="btn-primary bottom btn btn-primary-bottom-stk pts" ><span>{AL.TOTAL_POINTS}</span><span style={{ fontSize: 20 }}>{total_score}</span></button>}
                                                
                                            </div>
                                            :
                                            <div>
                                                <div className='container-top'>
                                                    <div className='buy-container'>
                                                        <div className='oval'> </div>
                                                        <div className='b-s-o-text'>{AL.BUY} </div>
                                                    </div>
                                                    {/* <div className='sell-container'>
                                                        <div className='oval'> </div>
                                                        <div className='b-s-o-text'>{AL.SELL} </div>
                                                    </div> */}
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
                            <Modal.Footer >
                                {
                                    this.state.listPieStatus == 0  &&
                                    <div className='bottom-conatiner'>
                                        <div className='total-conatiner'>
                                            <div>
                                                <div className="lbl">
                                                    {Utilities.getMasterData().currency_code}
                                                    {Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(this.state.totalAmount)))} 
                                                    {" + "}{Utilities.numberWithCommas(unusedValue)}
                                                </div>
                                                <div className="val">{AL.INVESTED+ ' + '+ AL.UNUSED}</div>
                                            </div>
                                            {/* <div>
                                                <div className="lbl">{" + "}{Utilities.numberWithCommas(unusedValue)}</div>
                                                <div className="val">{AL.UNUSED}</div>
                                            </div> */}
                                            {/* <div className='total'>{AL.INVESTED}{' + '}{AL.UNUSED}</div>
                                            <div className='total-amount'>
                                                {Utilities.getMasterData().currency_code}
                                                {Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(this.state.totalAmount)))} 
                                                {" + "}{Utilities.numberWithCommas(unusedValue)}
                                            </div> */}
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

export default LSFTeamPreview;