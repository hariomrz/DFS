import React from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { getStockScoreCalculationEquity } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { Utilities } from '../../Utilities/Utilities';

class StockScoreCalcEquity extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            selectedLineup: '',
            CollectionData: '',
            teamPlayerData: '',
            BuyStkArry: [],
            SellStkArry: [],
            contestId: '',
            totalScore: 0,
            totalPer: 0,
            remCap: 0
        };


    }

    componentDidMount() {
        this.setState({ selectedLineup: this.props.selectedLineup, CollectionData: this.props.CollectionData,contestId: this.props.contestId }, () => {
            this.getTeamPlayers()
        })
    }

    getTeamPlayers = () => {
        let param = {
            "lineup_master_id": this.state.selectedLineup,
            "collection_id": this.state.CollectionData.collection_master_id,
            "stock_type":"2",
            "contest_id": this.state.contestId
        }
        getStockScoreCalculationEquity(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                console.log('responseJson.data',responseJson.data.total_score);
                this.setState({
                    teamPlayerData: responseJson.data.lineup,
                    totalScore: responseJson.data.total_score,
                    totalPer: responseJson.data.percent_change,
                    remCap: responseJson.data.remaining_cap || 0 
                },()=>{
                    this.setDataWithStockType(responseJson.data.lineup)
                })
            }
        })
    }

    setDataWithStockType=(teamPlayerData)=>{
        let tmpBuyArry = [];
        let tmpSellArry = [];
        for( var item of teamPlayerData){
            if(item.type == 1){
                tmpBuyArry.push(item)
            }
            else{
                tmpSellArry.push(item)
            }
        }
        let filterBuyArry = this.filterArry(tmpBuyArry)
        let filterSellArry = this.filterArry(tmpSellArry)
        console.log('filterBuyArry',filterBuyArry)
        console.log('filterSellArry',filterSellArry)
        this.setState({
            BuyStkArry: filterBuyArry,
            SellStkArry: filterSellArry
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
            // if (item.captain == 1 || item.captain == 2) {
            //     FinalArry.push(item);
            // }
            // else{
                if(item.player_role != 1 && item.player_role != 2){
                    tmpC.push(item)
                }
            // }
        }
        tmpC = tmpC.sort((a, b) =>  a.name.localeCompare(b.name))
       
        return  FinalArry.concat(tmpC)
    }

    calStockPrize=(stkPrc,size,PR)=>{
        let PRole = PR || 0
        let calcPrc = 0;
        calcPrc = parseFloat(stkPrc) * parseFloat(size)
        // if(PRole == 1){
        //     calcPrc = calcPrc*2
        //     return parseFloat(calcPrc).toFixed(2)
        // }
        // else if(PRole == 2){
        //     calcPrc = calcPrc*1.5
        //     return parseFloat(calcPrc).toFixed(2)
        // }
        // else{           
            // return Utilities.numberWithCommas(parseFloat(calcPrc).toFixed(2))
            return parseFloat(calcPrc).toFixed(2)
        // }
    }
    
    showDiff=(opnPrz,clsPrz,lotsize,SType,showCurr,playerRole)=>{
        let calResult = 0
        let PRole = playerRole ? playerRole : 0
        if(lotsize){
            opnPrz = parseFloat(opnPrz) * parseInt(lotsize)
            clsPrz = parseFloat(clsPrz) * parseInt(lotsize)
            calResult =  parseFloat(clsPrz) - parseFloat(opnPrz)
        }
        else{
            calResult = parseFloat(clsPrz) - parseFloat(opnPrz)
        }

        if(showCurr){
            let isNeg = calResult < 0 ? true : false
            calResult = PRole == 1 ? (calResult*2) : PRole == 2 ? (calResult*1.5) : calResult
            return <>{(isNeg && SType != 2 || !isNeg && SType == 2) && '-'} {Utilities.getMasterData().currency_code}{parseFloat(isNeg ? Math.abs(calResult) : calResult).toFixed(2)}</>
        }
        else{
            if(SType == 2 && calResult > 0){
                calResult = PRole == 1 ? (calResult*2) : PRole == 2 ? (calResult*1.5) : (calResult)
                calResult = '-' + calResult
            }
            else{
                calResult = PRole == 1 ? (SType == 2 ? Math.abs(calResult*2) : (calResult*2)) : PRole == 2 ? (SType == 2 ? Math.abs(calResult*1.5) : (calResult*1.5)) : (SType == 2 ? Math.abs(calResult) : calResult)
            }
            return Utilities.numberWithCommas(parseFloat(calResult).toFixed(2))
        }
    }

    opnTotal=()=>{
        let {BuyStkArry,SellStkArry} = this.state;
        let TAmt = 0;
        for(var item of BuyStkArry){
            let itemPrize = this.calStockPrize(item.publish_closing_rate,item.user_lot_size)
            TAmt = parseFloat(TAmt) + parseFloat(itemPrize)
        }
        for(var item of SellStkArry){
            let itemPrize = this.calStockPrize(item.publish_closing_rate,item.user_lot_size)
            TAmt = parseFloat(TAmt) + parseFloat(itemPrize)
        }
        return Utilities.numberWithCommas(parseFloat(TAmt).toFixed(2))
    }

    showTotalScore=()=>{
        const {remCap} = this.state;
        let totalScr = 0
        for(var item of this.state.BuyStkArry){
            let resScore = this.calStockPrize(item.result_rate,item.user_lot_size,item.player_role)
            totalScr = parseFloat(totalScr) + parseFloat(resScore)
        }
        for(var item of this.state.SellStkArry){
            let resScore = this.calStockPrize(item.result_rate,item.user_lot_size,item.player_role)
            totalScr = parseFloat(totalScr) + parseFloat(resScore)
        }
        totalScr = totalScr + parseFloat(remCap)
        return Utilities.numberWithCommas(parseFloat(totalScr).toFixed(2))
    }


    render() {
        const { isViewAllShown, onViewAllHide, total_score,StockSettingValue ,status} = this.props;
        const { teamPlayerData, BuyStkArry, SellStkArry,totalScore,totalPer,remCap } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={isViewAllShown} onHide={onViewAllHide} bsSize="large" className="stock-team-view-modal  eqt-stk">
                            <Modal.Header>
                                <Modal.Title>
                                    <a href onClick={onViewAllHide} className="modal-close">
                                        <i className="icon-close"></i>
                                    </a>
                                    <div className="name-container">
                                        <div className="team-name">
                                            {'Score Calculation'}
                                        </div>
                                    </div>
                                </Modal.Title>
                            </Modal.Header>
                            <Modal.Body style={{ minHeight: '90vh', paddingBottom: '60px' }}>
                                <div className="stock-type m-t-20">{AL.BUY_STOCK}</div>
                                <div className="player-list-container scoring">
                                    <div className="item-header">
                                        <span style={{ width: '16%' }}>{AL.SHARES}</span>
                                        <span style={{ width: '25%' }}>{AL.OPENING}</span>
                                        <span style={{ width: '25%' }}>{status == 1 ? AL.CURRENT : AL.CLOSING}</span>
                                        <span style={{ width: '25%' }}>{AL.GAINLOSS}</span>
                                    </div>
                                    {
                                        (BuyStkArry || []).map((item, index) => {
                                            // let pDiff = parseFloat(item.price_diff || 0).toFixed(2);
                                            // pDiff = pDiff == 0 ? 0 : pDiff;
                                            let pDiff = pDiff == 0 ? 0 : parseFloat(item.price_diff).toFixed(2);
                                            return (
                                                <div key={index}>
                                                    <div className='scoring-name-c'>
                                                        <img className="player-image" src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                                                        <div className="player-name">
                                                            {item.name || item.stock_name} 
                                                            {
                                                                (item.player_role == 1 || item.player_role == 2) &&
                                                                <span>{item.player_role == 1 ? 'A' : 'B'}</span>
                                                            }
                                                        </div>
                                                    </div>
                                                    <div className="value-v">
                                                        <span style={{ width: '16%' }}>{item.user_lot_size}</span>
                                                        <span style={{ width: '25%' }}>
                                                            {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(this.calStockPrize(item.publish_closing_rate,item.user_lot_size))}
                                                            <span className="inn-block opn-prc">
                                                                {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(item.publish_closing_rate)}
                                                            </span>
                                                        </span>
                                                        <span style={{ width: '25%' }}>
                                                            {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(this.calStockPrize(item.result_rate,item.user_lot_size,item.player_role))} 
                                                            {/* {   
                                                                (item.player_role == 1 || item.player_role == 2) &&
                                                                <span className="player-role-pts">
                                                                    {
                                                                        item.player_role == 1 ? 
                                                                        <>({StockSettingValue && StockSettingValue.c_point}x)</> : 
                                                                        <>({StockSettingValue && StockSettingValue.vc_point}x)</>
                                                                    }
                                                                </span>
                                                            } */}
                                                            <span className={"inn-block" + (pDiff.includes('-') ? ' text-danger' : ' text-succ')}>
                                                                <i className={ pDiff.includes('-') ? "icon-stock_down" : "icon-stock_up"}></i> {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(item.result_rate)}
                                                            </span>
                                                        </span>
                                                        <span style={{ width: '25%' }} className="bold-text"> 
                                                            {/* {this.showDiff(item.publish_closing_rate || 0 ,item.result_rate,item.user_lot_size || 0,1,true,item.player_role)} */}
                                                            {Utilities.numberWithCommas(item.gain_loss)}
                                                            {
                                                                (item.player_role == 1 || item.player_role == 2) &&
                                                                <span className="player-role-pts">
                                                                    {
                                                                        item.player_role == 1 ? 
                                                                        <>({StockSettingValue && StockSettingValue.c_point}x)</> : 
                                                                        <>({StockSettingValue && StockSettingValue.vc_point}x)</>
                                                                    }
                                                                </span>
                                                            }
                                                            <span className="inn-block nrml-text">
                                                                {/* {this.showDiff(item.publish_closing_rate || 0,item.result_rate || 0,1,1)}  */}
                                                                {Utilities.numberWithCommas(item.price_diff)}
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                            )
                                        })
                                    }
                                </div>
                                
                                <div className="stock-type">{AL.SELL_STOCK}</div>
                                <div className="player-list-container scoring">
                                    {
                                        (SellStkArry || []).map((item, index) => {
                                            let pDiff = parseFloat(item.price_diff && item.price_diff == 0 ? 0:item.price_diff  || 0).toFixed(2);
                                            pDiff = pDiff == 0 ? 0 : pDiff;
                                            return (
                                                <div key={index}>
                                                    <div className='scoring-name-c'>
                                                        <img className="player-image" src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                                                        <div className="player-name">
                                                            {item.name || item.stock_name}
                                                            {
                                                                (item.player_role == 1 || item.player_role == 2) &&
                                                                <span>{item.player_role == 1 ? 'A' : 'B'}</span>
                                                            }
                                                        </div>
                                                    </div>
                                                    <div className="value-v">
                                                        <span style={{ width: '16%' }}>{item.user_lot_size}</span>
                                                        <span style={{ width: '25%' }}>
                                                            {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(this.calStockPrize(item.publish_closing_rate,item.user_lot_size))}
                                                            <span className="inn-block opn-prc">
                                                                {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(item.publish_closing_rate)}
                                                            </span>
                                                        </span>
                                                        <span style={{ width: '25%' }}>
                                                            {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(this.calStockPrize(item.result_rate && item.result_rate ,item.user_lot_size,item.player_role))}
                                                            {/* {   
                                                                (item.player_role == 1 || item.player_role == 2) &&
                                                                <span className="player-role-pts">
                                                                    {
                                                                        item.player_role == 1 ? 
                                                                        <>({StockSettingValue && StockSettingValue.c_point}x)</> : 
                                                                        <>({StockSettingValue && StockSettingValue.vc_point}x)</>
                                                                    }
                                                                </span>
                                                            } */}
                                                            <span className={"inn-block" + (pDiff && pDiff.includes('-') ? ' text-danger' : ' text-succ')}>
                                                                <i className={pDiff && pDiff.includes('-') ? "icon-stock_down" : "icon-stock_up"}></i> {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(item.result_rate)}
                                                            </span>
                                                        </span>
                                                        <span style={{ width: '25%'}} className="bold-text"> 
                                                            {/* {this.showDiff(item.publish_closing_rate || 0 ,item.result_rate,item.user_lot_size || 0,2,true,item.player_role)} */}
                                                            {Utilities.numberWithCommas(item.gain_loss)}
                                                            {   
                                                                (item.player_role == 1 || item.player_role == 2) &&
                                                                <span className="player-role-pts">
                                                                    {
                                                                        item.player_role == 1 ? 
                                                                        <>({StockSettingValue && StockSettingValue.c_point}x)</> : 
                                                                        <>({StockSettingValue && StockSettingValue.vc_point}x)</>
                                                                    }
                                                                </span>
                                                            }
                                                            <span className="inn-block nrml-text">
                                                                {/* {this.showDiff(item.publish_closing_rate || 0,item.result_rate || 0,1,2)}  */}
                                                                {Utilities.numberWithCommas(item.price_diff)}
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                            )
                                        })
                                    }
                                </div>
                                <div className="btm-ttl-sec">
                                    <span style={{ width: '50%' }}>{Utilities.getMasterData().currency_code}{this.opnTotal()} + {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(remCap)}
                                        <span className="lbl-for">{AL.INVESTED} + {AL.UNUSED}</span>
                                    </span>
                                    <span style={{ width: '49%',textAlign: 'right'}}>
                                        {/* {Utilities.getMasterData().currency_code}{this.showTotalScore()}  */}
                                        {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(totalScore).toFixed(2))} 
                                        <span className="ttl-per">({totalPer}%)</span>
                                        <span className="lbl-for">{AL.CURRENT_AMOUNT} ({AL.GAINLOSS})</span>
                                    </span>
                                </div>
                            </Modal.Body>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default StockScoreCalcEquity;