import React from 'react';
import { Tabs, Tab, FormGroup } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import { inputStyle } from '../../helper/input-style';

class BuySellStockModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            shareNumber: this.props.item && this.props.item.shareValue ? this.props.item.shareValue : '',
            isChanged: true,
            stockPrize: this.props.item && this.props.item.stockPrize ? this.props.item.stockPrize : 0,
            remaingBudget: parseFloat(this.props.salaryCap)

        };

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
                    this.setState({ stockPrize: parseFloat(Utilities.getExactValue(prizeWillBe)) }, () => { })
                });
            }
            else {
                this.setState({ stockPrize: 0 }, () => { })
                this.setState({ [name]: value })

            }
        }


    }
    sendDataToRoster = (item, action, pDiff, remaingBudget, stockPrize, shareValue, isUpdate) => {
        if (this.state.stockPrize != 0) {
            this.props.addDataFromBytSell(item, action, pDiff, remaingBudget, stockPrize, shareValue, isUpdate)

        }
    }
    

    removedata = (item, remaingBudgetData) => {
        //let updateData = (parseFloat(this.props.salaryCap) + parseFloat(this.props.item.stockPrize))
        let a= parseFloat(Utilities.getExactValue(this.props.salaryCap));
        let b =  parseFloat(Utilities.getExactValue(this.props.item.stockPrize));
        let updateData = Utilities.addNumber(a,b);
        let remaingBudget = parseFloat(updateData) > 500000 ? 500000 : parseFloat(updateData) < 0 ? 0.0 : parseFloat(updateData);
        this.props.removeDataFromList(item, remaingBudget)
    }
    render() {

        const { mShow, mHide, item, action, pDiff, maxCapPerStock, minCapPerStock, salaryCap, isUpdate, removeDataFromList } = this.props;
        const { shareNumber, stockPrize, remaingBudget } = this.state;
        let stockPrizeData = parseFloat(Utilities.getExactValue(stockPrize));
        let minCapPerStockData = (parseFloat(minCapPerStock));
        let maxCapPerStockData = (parseFloat(maxCapPerStock));
        let criteria = (stockPrizeData > minCapPerStockData && parseFloat(stockPrizeData) <= parseFloat(maxCapPerStockData)) ? 1 : 0
        let remaingBudgetData;
        if (isUpdate) {
            let a= parseFloat(Utilities.getExactValue(this.props.salaryCap));
            let b =  parseFloat(Utilities.getExactValue(this.props.item.stockPrize));
            let updateData = Utilities.addNumber(a,b);
            let c= parseFloat(Utilities.getExactValue(updateData));
            let d =  parseFloat(Utilities.getExactValue(stockPrizeData));
            let finalBudget = Utilities.subNumber(c,d);
            remaingBudgetData = parseFloat(finalBudget) > 500000 ? 500000 : parseFloat(finalBudget) < 0 ? 0.0 : parseFloat(finalBudget);

        }
        else {
            let a= parseFloat(Utilities.getExactValue(this.props.salaryCap));
            let b =  parseFloat(Utilities.getExactValue(stockPrizeData));
            let finalBudget = Utilities.subNumber(a,b);

            remaingBudgetData = finalBudget > 500000 ? 500000 : finalBudget < 0 ? 0.0 : finalBudget;
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal buy-sell-equity-modal header-circular-modal overflow-hidden stock-buy-sell"
                        className="center-modal"
                    >
                        <Modal.Header >
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
                                    <div className={"team-vs-team" + (/*down*/pDiff < 0 ? ' down' : '')}>
                                        <i className={/*down*/pDiff < 0 ? "icon-stock_down" : "icon-stock_up"} />
                                        {Utilities.numberWithCommas(item.current_price)}

                                    </div>
                                    <div className='enter-number-of-shar'>{AL.ENTER_NUMBER_OF_SHARE} {" "} {item.action == 1 ? AL.BUY : action == 1 ? AL.BUY : AL.SELL}</div>
                                    <div>
                                        <FormGroup
                                            className='input-label-center input-with-cancel'
                                            controlId="formBasicText"
                                        >
                                            <span className="promocode-input">
                                                {
                                                    <input
                                                        id='shareNumber'
                                                        name='shareNumber'
                                                        type='text'
                                                        value={shareNumber}
                                                        maxLength={5}
                                                        onChange={this.handleChange}
                                                        styles={inputStyle}

                                                    />
                                                }

                                            </span>
                                            <a className={"stock-value"}>
                                                {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(Utilities.getExactValue(stockPrize)))}
                                            </a>


                                        </FormGroup>

                                    </div>
                                    <div className='min-and-max-value-to'>{AL.MIN_MAX_VALUE_TO_BUY}</div>
                                    <div className='min-max-value'>{Utilities.getMasterData().currency_code} {" "}{Utilities.numberWithCommas(minCapPerStock)} {"-"} {Utilities.getMasterData().currency_code} {" "}{Utilities.numberWithCommas(maxCapPerStock)}</div>
                                    <div className='min-and-max-value-to no-margin'>{AL.REMAINING_BUDGET}</div>
                                    <div className='min-max-value'>{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(parseFloat(Utilities.getExactValue(remaingBudgetData)))}</div>
                                    <div onClick={() => this.sendDataToRoster(item, action, pDiff, remaingBudgetData, parseFloat(Utilities.getExactValue(stockPrize)), shareNumber, isUpdate)} className={'done-btn' + (this.state.stockPrize == 0 || parseFloat(remaingBudgetData) <= 0 || criteria == 0 ? ' disable-btn' : '')}>{isUpdate ? item.action == 1 ? AL.UPDATE + ' ' + AL.BUY : AL.UPDATE + ' ' + AL.SELL : AL.DONE}</div>
                                    {
                                        isUpdate &&
                                        <div onClick={() => this.removedata(item, remaingBudgetData)} className='remove-stock-from-list'>{AL.REMOVE_STOCK_FROM_LIST}</div>
                                    }



                                </div>
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}
export default BuySellStockModal;