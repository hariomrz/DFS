import React from 'react';
import { Modal } from 'react-bootstrap';
import { getReferalPrizes } from "../WSHelper/WSCallings";
import * as AL from "../helper/AppLabels";
import * as WSC from "../WSHelper/WSConstants";
import { Utilities, _Map } from '../Utilities/Utilities';
import Moment from 'react-moment';
import { MyContext } from '../InitialSetup/MyProvider';
import Images from '../components/images';

export default class ReferalPrizesModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            show: false,
            referalPrizeDataList: [],
            sponsorLogo:'',
            sponsorLink:'',
            prize_distribution_detail: [],
            filerByTime: [
                {
                    value: 'today',
                    label: AL.TODAY,
                    prize_cat_id: '1'
                },
                {
                    value: 'this_week',
                    label: AL.THIS_WEEK,
                    prize_cat_id: '2'
                },
                {
                    value: 'this_month',
                    label: AL.THIS_MONTH,
                    prize_cat_id: '3'
                },
            ],
            filterDataBy: 'today',
            filterById: '1',
        };
    }

    handleTimeFilter = (filterBy, id) => {
        this.setState({
            filterDataBy: filterBy,
            filterById: id,

        }, () => {
            this.getLeaderboardData();
        })
    }

    componentDidMount() {
        //this.getCategory();
        this.setState({
            filterDataBy:this.props.filterDataBy,
            filterById:this.props.filterById
        })
        this.getLeaderboardData();
    }


    getLeaderboardData() {
        let param = {}
        getReferalPrizes(param).then((responseJson) => {
            let prize_distribution = [];
            let sponsorLogVar = '';
            let sponsorLinkvar ='';
            if (responseJson.response_code === WSC.successCode) {
                responseJson.data && responseJson.data.map((item, index) => {

                    if (this.state.filterDataBy == 'today' && item.name == 'Daily') {
                        prize_distribution = item.prize_distribution_detail
                        sponsorLogVar = item.sponsor_logo
                        sponsorLinkvar = item.sponsor_link

                    }
                    else if (this.state.filterDataBy == 'this_week' && item.name == 'Weekly') {
                        prize_distribution = item.prize_distribution_detail
                        sponsorLogVar = item.sponsor_logo
                        sponsorLinkvar = item.sponsor_link
                        

                    }
                    else if (this.state.filterDataBy == 'this_month' && item.name == 'Monthly') {
                        prize_distribution = item.prize_distribution_detail
                        sponsorLogVar = item.sponsor_logo
                        sponsorLinkvar = item.sponsor_link
                    

                    }

                })
                this.setState({
                    referalPrizeDataList: responseJson.data,
                    prize_distribution_detail: prize_distribution,
                    sponsorLogo :sponsorLogVar,
                    sponsorLink:sponsorLinkvar
                }, () => {
                    // this.showSponser()


                })
            }
        })
    }

    render() {
        const { IsCollectionInfoShow, IsCollectionInfoHide } = this.props;
        const { filterDataBy, filerByTime, filterById, prize_distribution_detail } = this.state
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={IsCollectionInfoShow} onHide={() => IsCollectionInfoHide()} bsSize="large" dialogClassName="referal-prize-modal" className="center-modal">
                            <Modal.Header closeButton>
                                <div className="header-modal-title">{AL.ALL_PRIZES} </div>

                            </Modal.Header>
                            <Modal.Body>

                                <div>
                                    <img src={Images.REFERAL_BG} className='modal-referal-img'></img>
                                    <div className="strip-prize-status">
                                        <div className="fixed-ch-view-prizes-modal">
                                            <div className="filter-time-section-prizes-modal">
                                                <ul className="filter-time-wrap-prizes-modal">
                                                    {
                                                        _Map(filerByTime, (item, idx) => {
                                                            return (
                                                                <li
                                                                    href
                                                                    className={"filter-time-btn-modal" +
                                                                        (item.value == filterDataBy ? ' active' : '') +
                                                                        (item.prize_cat_id == 2 && filterById == 2 && this.props.STARTDATE ? ' with-date' : '')
                                                                    }
                                                                    onClick={() => this.handleTimeFilter(item.value, item.prize_cat_id)}
                                                                >
                                                                    {item.label}
                                                                    {
                                                                        (item.prize_cat_id == 2 && filterById == 2) && this.props.STARTDATE &&
                                                                        <span>
                                                                            <Moment date={this.props.STARTDATE} format={"D MMM "} />
                                                                            {/* <MomentDateComponent data={{ date: STARTDATE, format: "D MMM " }} /> */}
                                                            -
                                                            <Moment date={this.props.ENDDATE} format={" D MMM "} />
                                                                            {/* <MomentDateComponent data={{ date: ENDDATE, format: "D MMM " }} /> */}
                                                                        </span>
                                                                    }
                                                                </li>
                                                            )
                                                        })
                                                    }
                                                </ul>
                                            </div>


                                        </div>
                                    </div>

                                    {



                                        prize_distribution_detail && prize_distribution_detail.map((itemPrize, index) => {
                                            return (
                                                <div className="referal-strip">
                                                    <div className="referal-prize-rank" >

                                                        {itemPrize.min == itemPrize.max ? itemPrize.min : itemPrize.min + "-" + itemPrize.max}

                                                    </div>
                                                    <div className="win-amount" >

                                                        {itemPrize.prize_type == 0 ?
                                                            <div>
                                                                <div className="contest-listing-prizes"><i className="icon-bonus" /></div>
                                                                {itemPrize.amount}
                                                            </div>
                                                            :
                                                            itemPrize.prize_type == 1 ?
                                                                <div>
                                                                    <span className="contest-prizes">{Utilities.getMasterData().currency_code}</span>
                                                                    {itemPrize.amount}
                                                                </div>
                                                                :
                                                                itemPrize.prize_type == 2 ?
                                                                    <span style={{ marginLeft: '13px', display: 'inlineBlock' }}> <img style={{ height: '14px', width: '14px', marginTop: '-5px' }} className="img-coin" src={Images.IC_COIN} />{itemPrize.amount}</span>
                                                                    :
                                                                    itemPrize.amount



                                                        }

                                                    </div>
                                                </div>

                                            );

                                        })
                                    }
                                  {
                                        this.state.sponsorLogo && this.state.sponsorLogo != '' &&
                                        <div className="sponsored-section">
                                        <span className="sponsored-text">{AL.SPONSOR_BY}</span>
                                        <img src={Utilities.getOpenPredFPPURL(this.state.sponsorLogo)} alt=""/>
                                    </div>

                                  }

                                </div>

                            </Modal.Body>
                        </Modal>

                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}