import React from 'react';
import { Row, Col } from 'react-bootstrap';
import { MyContext } from '../../views/Dashboard';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import Skeleton from 'react-loading-skeleton';
import InfiniteScroll from 'react-infinite-scroll-component';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { NoDataView } from '../../Component/CustomComponent';
import { Utilities, _times, _debounce, _Map, _filter } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';


class LBAllPrizes extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            pDetail: this.props.location.state.prizeDetail,
            isLeague: this.props.location.state.isLeague
        };
    }

    componentDidMount() {
    }

    addAbr=(value)=>{
        let val = parseInt(value)
        if(val == 1){
            return 'st'
        }
        else if(val == 2){
            return 'nd'
        }
        else if(val == 3){
            return 'rd'
        }
        else if(val == 4 || val == 5){
            return 'th'
        }
        else{
            return ''
        }
    }

    render() {
        const {
            pDetail,
            isLeague
        } = this.state;
        const HeaderOption = {
            title: AL.PRIZE,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            back: true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container all-prizes-container new-all-prize-wrap">
                        <CustomHeader
                            {...this.props}
                            HeaderOption={HeaderOption}
                        />
                        <div className="dis-sec-heading">
                            <span>{AL.DISTRIBUTION}</span>
                        </div>
                        {
                            pDetail && pDetail.length > 0 &&
                            <Row>
                                {
                                    _Map((pDetail),(item,idx)=>{
                                        return(
                                            <Col sm={4} xs={4} className="prz-dtl-wrap">
                                                <div className="prz-det">
                                                    <div className="prz-dtl-ab">
                                                        {
                                                            item.prize_type == 0 ?
                                                            <span className="cont-prz">
                                                                <i className="icon-bonus"></i>
                                                            </span>
                                                            :
                                                            item.prize_type == 1 ?
                                                                <span className="cont-prz">{Utilities.getMasterData().currency_code}</span>
                                                                :
                                                                item.prize_type == 2 ?
                                                                    <span className="cont-prz">
                                                                        <img alt='' src={Images.COIN_BAG} className="coinimg" />
                                                                    </span>
                                                                    :
                                                                    item.prize_type == 3 ?
                                                                    <span className="cont-prz merc">
                                                                        <img alt='' src={Images.MERCHANDISE_GIFT} />
                                                                    </span>
                                                                    :
                                                                    ''
                                                        }
                                                        {(item.prize_type == 0 || item.prize_type == 2 || item.prize_type == 1 )&&
                                                            <span className="amt">{item.amount}</span>
                                                        }
                                                    </div>
                                                </div>
                                                <div className="prz-rank">
                                                    {item.min == item.max ? 
                                                        <>{item.min}{this.addAbr(item.min)}</> : 
                                                        item.min + '-' + item.max
                                                    }
                                                </div>
                                                <div className="prz-name">
                                                    {
                                                        item.prize_type == 0 ?
                                                        AL.BONUS
                                                        :
                                                        item.prize_type == 2 ?
                                                        AL.COINS
                                                        :
                                                        item.prize_type == 1 ?
                                                        AL.CASH_LW
                                                        : 
                                                        item.amount
                                                    }
                                                </div>
                                            </Col>
                                        )
                                    })
                                }
                            </Row>
                        }
                        <div className="btm-bck-lead" onClick={()=> this.props.history.goBack()}>
                            {AL.LEADERBOARD}
                        </div>
                        {
                            pDetail && pDetail.length == 0 &&
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                MESSAGE_1={'No data screen'}
                                MESSAGE_2={''}
                            />
                        }
                    </div>

                )}
            </MyContext.Consumer>
        );
    }
}

export default LBAllPrizes;
