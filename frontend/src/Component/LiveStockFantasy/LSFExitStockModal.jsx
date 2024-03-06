import React from 'react';
import { Modal, Button, FormGroup } from 'react-bootstrap';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import { inputStyle } from '../../helper/input-style';

export default class LSFExitStockModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            shareNumber: this.props.item && this.props.item.shareValue ? this.props.item.shareValue : 0,
            isChanged: true,
            stockPrize: this.props.item && this.props.item.stockPrize ? this.props.item.stockPrize : '',
            remaingBudget: parseFloat(this.props.salaryCap),
            EnteredAmount: this.props.item && this.props.item.stockPrize ? this.props.item.stockPrize : '',
            brokPerVal: this.props.brokerage ? this.props.brokerage : 0,
            brokerageValue: 0
        };

    }

    handleChangeAmount = (e) => {
        const name = e.target.name;
        const value = e.target.value;
       
        if (value != undefined && value != '') {
            this.setState({ [name]: value }, () => {
                let stockCount = parseFloat(value)  
               this.setState({stockCount:parseInt(stockCount)})
               let p= parseInt(stockCount) * parseFloat(this.props.item.current_price);
               let total = parseFloat(p)
               this.setState({
                   stockPrize:parseFloat(Utilities.getExactValue(p)),
                   totalTrans:parseFloat(Utilities.getExactValue(total))
                })
                

            });
        }
        else {               
            this.setState({ stockPrize: 0 ,stockCount: value})
        }
    }

    calcBrkValue = (brk, amt) => {
        return brk * amt / 100;
    }

    sendDataToRoster = (item, action, pDiff, remaingBudget, stockPrize, shareValue, brkValu) => {
        if (this.state.stockPrize != 0 && this.state.stockPrize != '') {
            this.props.addDataFromBytSell(item, action, pDiff, remaingBudget, stockPrize, shareValue, brkValu)

        }
    }

    submitAction = (item, action,stockPrize, stockCount) => {
        if(action == 3 || (parseInt(stockCount) > 0 && parseInt(stockCount) < parseInt(item.lot_size))){
            this.props.submitExitAction(item, action,stockPrize, stockCount)
        }
    }

    calcTotalStkAmt=(item)=>{
        return (parseFloat(item.current_price) * parseFloat(item.lot_size))
    }

    render() {
        const { mShow, mHide, item, action, pDiff, maxCapPerStock, minCapPerStock, salaryCap, removeDataFromList, isMarketOpen } = this.props;
        const { shareNumber, stockPrize, remaingBudget, EnteredAmount, brokerageValue, totalTrans, brokPerVal,stockCount } = this.state;
        let stockPrizeData = parseFloat(Utilities.getExactValue(stockPrize != '' ? stockPrize : 0));
        let remaingBudgetData;
      
        let a = parseFloat(Utilities.getExactValue(this.props.salaryCap));
        let b = parseFloat(Utilities.getExactValue(stockPrizeData));
        let finalBudget = Utilities.subNumber(a, b);

        remaingBudgetData = finalBudget > 500000 ? 500000 : finalBudget < 0 ? 0.0 : finalBudget;
        let totalStockTrans = this.calcTotalStkAmt(item)
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal buy-sell-equity-modal header-circular-modal overflow-hidden stock-buy-sell lsf-stk-act-modal"
                        className="center-modal"
                    >
                        <Modal.Header >
                            <a href className="close" onClick={mHide}>
                                <i className="icon-close"></i>
                            </a>
                            <div className="modal-img-wrap">
                                <div style={{ backgroundColor: '#ffffff', border: '1px solid #999999' }} className="wrap">
                                    <img style={{ height: 30, width: 30 }} className="player-image" src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />

                                </div>
                            </div>
                            {item.stock_name}
                            <div className='exit-lbl'>
                            {
                                action == 2 ? AL.EXIT_PARTIAL : AL.EXIT_ALL
                            }
                            </div>
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    <div className='budget-container budget-container2'>
                                        <div className='value-transaction'>
                                            <div className='trans-value-label'>{AL.TOTAL_SHARES}:</div>
                                            <div className='trans-value'>{item.lot_size ? item.lot_size : 0}</div>
                                        </div>
                                        {
                                            action == 2 &&
                                            <div className='value-transaction'>
                                                <div className='trans-value-label'>{AL.SCRIPS_TO_EXIT}:</div>
                                                <div className='trans-value'>
                                                    <input
                                                        id='stockCount'
                                                        name='stockCount'
                                                        type='number'
                                                        value={stockCount}
                                                        maxLength={6}
                                                        onChange={this.handleChangeAmount}
                                                        styles={inputStyle}

                                                    />
                                                </div>
                                            </div>
                                        }
                                        {
                                            isMarketOpen ?
                                            <div className='value-transaction'>
                                                <div className='trans-value-label'>{AL.TOTAL_VALUE}:</div>
                                                {
                                                    action == 2 ?
                                                    <div className='trans-value'>{Utilities.getMasterData().currency_code}{this.state.stockPrize ? Utilities.numberWithCommas(parseFloat(Utilities.getExactValue(totalTrans != '' ? totalTrans : 0))) : 0}</div>
                                                    :
                                                    <div className='trans-value'>{Utilities.getMasterData().currency_code}{totalStockTrans ? Utilities.numberWithCommas(parseFloat(Utilities.getExactValue(totalStockTrans))) : 0}</div>
                                                }
                                            </div>
                                            :
                                            <div className='market-close-lbl'>
                                                {AL.MARKET_CLOSE_MSG}
                                            </div>
                                        }
                                    </div>
                                    {
                                        action == 3 &&
                                        <div className="exit-confirm-txt">
                                            {AL.EXIT_ALL_CONFIRM_TEXT}
                                        </div>
                                    }
                                    {
                                        action == 2 ?
                                            <div className="btn-footer">
                                                <Button className={`btn ${parseInt(stockCount) > 0 && parseInt(stockCount) < parseInt(item.lot_size) ? '' : ' disabled'}`} onClick={() => this.submitAction(item, action,parseFloat(Utilities.getExactValue(stockPrize)), stockCount,remaingBudgetData)}>{AL.EXIT}</Button>
                                                <Button onClick={mHide}>{AL.CANCEL}</Button>
                                            </div>
                                            :
                                            <div className="btn-footer">
                                                <Button onClick={() => this.submitAction(item, action,parseFloat(Utilities.getExactValue(totalStockTrans)), item.lot_size,remaingBudgetData)}>{AL.YES}</Button>
                                                <Button onClick={mHide}>{AL.NO}</Button>
                                            </div>
                                    }

                                    {/* <div onClick={() => this.sendDataToRoster(item, 1, pDiff, remaingBudgetData, parseFloat(Utilities.getExactValue(stockPrize)), shareNumber,brokerageValue)} className={'done-btn' + (this.state.stockPrize == 0 || parseFloat(remaingBudgetData) <= 0 || criteria == 0 ? ' disable-btn' : '')}>{AL.BUY}</div>
                                    */}


                                </div>
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>


                )}
            </MyContext.Consumer>
        );
    }
}
