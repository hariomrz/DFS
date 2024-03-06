import React from 'react';
import { Modal, Button, FormGroup } from 'react-bootstrap';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import { inputStyle } from '../../helper/input-style';

export default class LSFStockBuyModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            shareNumber: this.props.item && this.props.item.shareValue ? this.props.item.shareValue : 0,
            isChanged: true,
            stockPrize: this.props.item && this.props.item.stockPrize ? this.props.item.stockPrize : '',
            remaingBudget: parseFloat(this.props.salaryCap),
            EnteredAmount: this.props.item && this.props.item.stockPrize ? this.props.item.stockPrize : '',
            brokPerVal : this.props.brokerage ? this.props.brokerage : 0,
            brokerageValue: 0
        };

    }

    handleChangeAmount = (e) => {
        const name = e.target.name;
        const value = e.target.value;
       // const onlyNumber = /^[0-9\b]+$/;


        // if (value != undefined && value != '' && !onlyNumber.test(value)) {
        //     return;
        // }
        // else {
            if (value != undefined && value != '') {
                this.setState({ [name]: value }, () => {
                   // let prizeWillBe = (parseFloat(this.props.item.current_price ? this.props.item.current_price : 0) * parseFloat(value))
                   // this.setState({ stockPrize: parseFloat(Utilities.getExactValue(prizeWillBe)) }, () => { })
                   let stockCount = parseFloat(value) / parseFloat(this.props.item.current_price) 
                   this.setState({shareNumber:parseInt(stockCount)})
                   let p= parseInt(stockCount) * parseFloat(this.props.item.current_price);
                   let brkVal= this.state.brokPerVal ? this.calcBrkValue(this.state.brokPerVal,p) : 0;
                   let total = parseFloat(p) + parseFloat(brkVal)
                   this.setState({
                       stockPrize:parseFloat(Utilities.getExactValueSP(p)),
                       brokerageValue: brkVal == 0 ? 0 : parseFloat(Utilities.getExactValueSP(brkVal)),
                       totalTrans:parseFloat(Utilities.getExactValueSP(total))
                    })
                    

                });
            }
            else {
                
                this.setState({ [name]: value })                
                this.setState({ stockPrize: 0 }, () => { })

                //this.setState({ stockPrize: 0 }, () => { })
               // this.setState({ [name]: value })

            }
    }

    calcBrkValue=(brk,amt)=>{
        return parseFloat(brk) > 0 ? brk * amt / 100 : 0;
    }

    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        const onlyNumber = /^[0-9\b]+$/;


        if (value != undefined && value != '' && !onlyNumber.test(value)) {
            return;
        }
        else {
            if (value != undefined && value != '') {
                this.setState({ [name]: value }, () => {
                    let prizeWillBe = (parseFloat(this.props.item.current_price ? this.props.item.current_price : 0) * parseFloat(value))
                    this.setState({ stockPrize: parseFloat(Utilities.getExactValueSP(prizeWillBe)) }, () => { })
                });
            }
            else {
                this.setState({ stockPrize: 0 }, () => { })
                this.setState({ [name]: value })

            }
        }


    }
    sendDataToRoster = (item, action, pDiff, remaingBudget, stockPrize, shareValue,brkValu) => {
        if (this.state.stockPrize != 0 && this.state.stockPrize != '') {
            this.props.addDataFromBytSell(item, action, pDiff, remaingBudget, stockPrize, shareValue, brkValu)

        }
    }
    
    removedata = (item, remaingBudgetData) => {
        //let updateData = (parseFloat(this.props.salaryCap) + parseFloat(this.props.item.stockPrize))
        let a = parseFloat(Utilities.getExactValueSP(this.props.salaryCap));
        let b = parseFloat(Utilities.getExactValueSP(this.props.item.stockPrize !='' ? this.props.item.stockPrize:0 ));
        let updateData = Utilities.addNumber(a, b);
        let remaingBudget = parseFloat(updateData) > 500000 ? 500000 : parseFloat(updateData) < 0 ? 0.0 : parseFloat(updateData);
        this.props.removeDataFromList(item, remaingBudget)
    }

    render() {
        const { mShow, mHide, item, action, pDiff, maxCapPerStock, minCapPerStock, isMarketOpen } = this.props;
        const { shareNumber, stockPrize, remaingBudget,EnteredAmount,brokerageValue, totalTrans,brokPerVal} = this.state;
        let stockPrizeData = parseFloat(Utilities.getExactValueSP(stockPrize!='' ? stockPrize: 0 ));
        let minCapPerStockData = (parseFloat(minCapPerStock));
        let maxCapPerStockData = (parseFloat(maxCapPerStock));
        let criteria = 1 //(stockPrizeData > minCapPerStockData && parseFloat(stockPrizeData) <= parseFloat(maxCapPerStockData)) ? 1 : 0
        let remaingBudgetData;
        // if (isUpdate) {
        //     let a = parseFloat(Utilities.getExactValue(this.props.salaryCap));
        //     let b = parseFloat(Utilities.getExactValue(this.props.item.stockPrize!=''? this.props.item.stockPrize : 0));
        //     let updateData = Utilities.addNumber(a, b);
        //     let c = parseFloat(Utilities.getExactValue(updateData));
        //     let d = parseFloat(Utilities.getExactValue(stockPrizeData)) + parseFloat(Utilities.getExactValue(brokerageValue));
        //     let finalBudget = Utilities.subNumber(c, d);
        //     remaingBudgetData = parseFloat(finalBudget) > 500000 ? 500000 : parseFloat(finalBudget) < 0 ? 0.0 : parseFloat(finalBudget);

        // }
        // else {
            let a = parseFloat(Utilities.getExactValueSP(this.props.salaryCap));
            let b = parseFloat(Utilities.getExactValueSP(stockPrizeData)) + parseFloat(Utilities.getExactValueSP(brokerageValue));
            let finalBudget = Utilities.subNumber(a, b);

            remaingBudgetData = finalBudget > 500000 ? 500000 : finalBudget < 0 ? 0.0 : finalBudget;
        // }

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
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    <div className={"team-vs-team" + (pDiff < 0 ? ' down' : '')}>
                                        <i className={pDiff < 0 ? "icon-stock_down" : "icon-stock_up"} />
                                        {Utilities.numberWithCommas(item.current_price)}

                                    </div>
                                    <div className='enter-number-of-shar'>{AL.ENTER_NUMBER_OF_SHARE} {" "} {AL.BUY}</div>
                                    <div style={{ marginLeft: 20 }}>

                                        <span style={{ position:"unset",marginTop:5,top:0,left:0,float:'left' }} className="forminput-currency">
                                            {Utilities.getMasterData().currency_code}</span>
                                        <FormGroup
                                            className='input-label-center input-with-cancel'
                                            controlId="formBasicText"
                                        >

                                            <span className="promocode-input">
                                                {
                                                    <input
                                                        id='EnteredAmount'
                                                        name='EnteredAmount'
                                                        type='number'
                                                        value={parseFloat(Utilities.getExactValueSP(EnteredAmount))}
                                                        maxLength={6}
                                                        onChange={this.handleChangeAmount}
                                                        styles={inputStyle}

                                                    />
                                                }

                                            </span>
                                            <div className='total-stock-container'>
                                                <div className='inner-container'>
                                                    <div className='vertical-line'>   </div>
                                                    <div className='colum-conatiner'>
                                                        <div className='has-no-shares'> {AL.HAS_OF_SHARES}  </div>
                                                        <div className='exact-stock'> {this.state.shareNumber ?  this.state.shareNumber : 0}  </div>

                                                    </div>

                                                </div>

                                            </div>


                                        </FormGroup>

                                    </div>
                                    <div className='budget-container'>
                                        <div className='value-transaction'>
                                            <div className='trans-value-label'>{AL.VALUE_OF_TRANSACTION}</div>
                                            <div className='trans-value'>
                                                {Utilities.getMasterData().currency_code}
                                                {this.state.stockPrize ? 
                                                    Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(this.state.stockPrize !='' ? this.state.stockPrize : 0))) 
                                                    :
                                                    0 
                                                }
                                            </div>
                                        </div>
                                        <div className='value-transaction'>
                                            <div className='trans-value-label'>{AL.BROKERAGE} ({brokPerVal}%)</div>
                                            <div className='trans-value'>
                                                {Utilities.getMasterData().currency_code}
                                                {this.state.stockPrize ? 
                                                    <>{brokerageValue == 0 ? 0 : Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(brokerageValue !='' ? brokerageValue : 0)))}</> 
                                                    :0 
                                                }
                                            </div>
                                        </div>
                                        {
                                            isMarketOpen ?
                                            <div className='value-transaction'>
                                                <div className='trans-value-label'>{AL.NET_TRANS_VALUE}</div>
                                                <div className='trans-value'>{Utilities.getMasterData().currency_code}{this.state.stockPrize ? Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(totalTrans !='' ? totalTrans : 0))) :0 }</div>
                                            </div>
                                            :
                                            <div className='market-close-lbl'>
                                                {AL.MARKET_CLOSE_MSG}
                                            </div>
                                        }
                                        <div className='remaining-budget-container'>
                                        <div className='budget-value-label'>{AL.BALANCE}</div>
                                            <div className='budget-value'>{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(remaingBudgetData ? remaingBudgetData : this.props.salaryCap)))}</div>
                                        </div>

                                    </div>
                                    <div onClick={() => this.sendDataToRoster(item, 1, pDiff, remaingBudgetData, parseFloat(Utilities.getExactValueSP(stockPrize)), shareNumber,brokerageValue)} className={'done-btn mt-0' + (this.state.stockPrize == 0 || parseFloat(remaingBudgetData) <= 0  ? ' disable-btn' : '')}>{AL.BUY}</div>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>


                )}
            </MyContext.Consumer>
        );
    }
}
