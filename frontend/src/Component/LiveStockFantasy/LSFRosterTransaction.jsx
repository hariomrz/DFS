import React, { Component, Suspense, lazy } from 'react';
import { Table } from 'react-bootstrap';
import { Helmet } from 'react-helmet';
import CustomHeader from '../../components/CustomHeader';
import { getLSFUserTransaction } from "../../WSHelper/WSCallings";
import MetaData from "../../helper/MetaData";
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { Utilities, _Map } from '../../Utilities/Utilities';
import Images from '../../components/images';
import { MomentDateComponent, NoDataView } from '../CustomComponent';
const StockPlayerCard = lazy(() => import('../StockFantasy/StockPlayerCard'));

export default class LSFRosterTransaction extends Component {
    constructor(props, context) {
        super(props, context);
        this.state ={
            LobbyData: '',
            HeaderOption: {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                title: '',
                hideShadow: false,
                stockContestTitle: '',
                stockContestDate: ''
            },
            showPlayerCard: false,
            playerDetails: {},
            salaryCap: 0,
            LMID: '',
            isloading: false
        }
    }

    componentDidMount() {
    }
    
    UNSAFE_componentWillMount = () => {
        this.setLocationStateData();
    }
    
    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
        var amount = 0;
        if (lObj.max_value) {
            amount = parseFloat(lObj.max_value);
        } else {
            amount = parseFloat(lObj.amount);
        }
        if (lObj.prize_type == 3) {
            prizeAmount['is_tie_breaker'] = 1;
        }
        if (lObj.prize_type == 0) {
            prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
        } else if (lObj.prize_type == 2) {
            prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
        } else {
            prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
        }
        })
        return prizeAmount;
    }

    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
          <React.Fragment>
            {
              prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                <span>
                  {Utilities.getMasterData().currency_code}
                  {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                </span>
                : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span> <i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                  : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span> <img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                    : AL.PRIZES
            }
          </React.Fragment>
        )
    }

    setContestTitle=(item)=>{
        return <>{AL.WIN} {this.getPrizeAmount(item)} {item.contest_title ? " - " + item.contest_title : ""}</> 
    }

    setLocationStateData=()=>{
        if (this.props.location && this.props.location.state) {
            let data = this.props.location.state.nextStepData ? this.props.location.state.nextStepData : this.props.location.state
            this.setState({
                LobbyData: data.LobbyData,
                salaryCap: data.salaryCap,
                LMID: data.LMID || '',
            },()=>{
                let ContestTitle = this.setContestTitle(data.LobbyData)
               this.setState({
                    HeaderOption: {
                        back: true,
                        isPrimary: DARK_THEME_ENABLE ? false : true,
                        title: '',
                        hideShadow: false,
                        stockContestTitle: ContestTitle,
                        stockContestDate: this.state.LobbyData
                    },
               })
               this.getTransaction()
            })
        }
    }

    /** 
    * @description api call to get transaction 
    */
     getTransaction = () => {
        this.setState({isloading: true})
        let param = {
            "contest_id": this.state.LobbyData.contest_id,
            "lineup_master_id": this.state.LMID && this.state.LMID != '' ? this.state.LMID : this.state.LobbyData.lineup_master_id,
        }
        getLSFUserTransaction(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    transList: responseJson.data,
                    isloading: false
                })
            }
        })
    }

    PlayerCardShow = (e, item) => {
        e.stopPropagation();
        item.collection_master_id = this.state.collectionMasterId;
        this.setState({
            playerDetails: item,
            showPlayerCard: true
        });
    }

    PlayerCardHide = () => {
        this.setState({
            showPlayerCard: false,
            playerDetails: {}
        });
    }

    renderList=(item,idx,isBrkItem)=>{
        return <tr className='idx+transaction_id'>
            <td className="stk-det-sec w60" 
                // onClick={(e)=>this.PlayerCardShow(e, item)}
            >
                <div className="stk-img"> <img src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" /></div>
                <div className="stk-nm">
                    <span>{item.display_name || item.stock_name || item.name}</span>
                </div>
                <div className="action-lbl">
                    {item.type == 2 ?
                        <>{AL.EXIT_PARTIAL}: {item.lot_size}</>
                        :
                        item.type == 3 ?
                            <>{AL.EXIT_ALL}: {item.lot_size}</>
                            :
                            <>{isBrkItem ? AL.BROKERAGE : <>{AL.BOUGHT}: {item.lot_size}</> }</>
                    }
                </div>
                <div className="action-time"><MomentDateComponent data={{ date: item.added_date, format: "D MMM YYYY, hh:mm A " }} /> </div>
            </td>
            <td className='debit-trans w20'>
                {item.type == 1 ? 
                    <>
                        {
                            item.status == 0 ?
                            <span>Pending</span>
                            :
                            '-' + (isBrkItem ? item.brokerage : item.trade_value) 
                        }
                    </>
                    : 
                    ''
                }
            </td>
            <td className='credit-trans w20'>
                {item.type == 2 || item.type == 3 ? 
                    <>
                        {
                            item.status == 0 ?
                            <span>Pending</span>
                            :
                            '+' + item.trade_value 
                        }
                    </>
                    : 
                    ''
                }
            </td>
        </tr>
    }
    
    render() {
        var {
            LobbyData,
            HeaderOption,
            transList,
            showPlayerCard,
            playerDetails,
            salaryCap,
            isloading
        } = this.state;
        return (
            <div className={"web-container white-bg lsf-transaction"}>
                <Helmet titleTemplate={`${MetaData.template} | %s`}>
                    <title>{MetaData.lineup.title}</title>
                    <meta name="description" content={MetaData.lineup.description} />
                    <meta name="keywords" content={MetaData.lineup.keywords}></meta>
                </Helmet>
                <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                <div className="webcontainer-inner">
                    <div className="balance-strip">
                        <div className="inn-strip">
                            <i className="icon-no-currency"></i>
                            <div className="lbl">{AL.BALANCE}</div>
                            <div className='val'>{ Utilities.numberWithCommas(parseFloat(Utilities.getExactValue(salaryCap))) || 0}</div>
                        </div>
                    </div>
                    <Table>
                        <thead>
                            <tr>
                                <th className='w60'>{AL.SCRIPS}</th>
                                <th className='text-center w20'><span><i className="icon-remove"></i></span></th>
                                <th className='text-center w20'><span><i className="icon-plus-ic"></i></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            {
                                transList && transList.length > 0 && !isloading && _Map(transList,(item,idx)=>{
                                    return <>
                                        {this.renderList(item,idx,false)}
                                        {
                                            item.brokerage && parseFloat(item.brokerage) > 0 && this.renderList(item,idx,true)
                                        }
                                    </>
                                })
                            }
                            {
                                !isloading && transList && transList.length == 0 && 
                                <tr>
                                    <td colSpan={3} className="border-0">
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                            MESSAGE_1={AL.NO_TRANSACTION_DATA}
                                            BUTTON_TEXT={AL.RESET + ' ' + AL.FILTERS}
                                            onClick={this.showLFilter}
                                        />
                                    </td>
                                </tr>
                            }
                        </tbody>
                    </Table>
                </div>
                {/* {
                    showPlayerCard &&
                    <Suspense fallback={<div />} >
                        <StockPlayerCard
                            mShow={showPlayerCard}
                            mHide={this.PlayerCardHide}
                            playerData={playerDetails}
                            buySellAction={this.buySellAction}
                            addToWatchList={this.addToWatchList} 
                        />
                    </Suspense>

                } */}
            </div>
        );
    }
}
