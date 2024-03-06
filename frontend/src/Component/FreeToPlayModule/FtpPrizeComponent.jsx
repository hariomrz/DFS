import React, { Component } from 'react';
import { Col} from 'react-bootstrap';
import { _isUndefined, Utilities } from '../../Utilities/Utilities';
import Images from '../../components/images';

class FtpPrizeComponent extends Component {
    constructor(props) {
        super(props);

    }

    setCurrentMaxPrize = (minMaxValue, prizeItem) => {
        var maxMini = prizeItem.max - prizeItem.min + 1;
        var finalPrize = (minMaxValue / maxMini)
        return finalPrize;
    }
    render() {
        let item= this.props.prizeListitem;
        let merchandiseList= this.props.merchandiseList;
        let isFrom = this.props.from; 

        return (
            <Col xs={3} sm={3} md={4} className="Ftp-prize-section center-grid">
                <div className={"Ftp-prize-inner-section" + (item.prize_type == 0 ? ' padding-bonus-icon' : '')}>
                    {
                        item.prize_type == 0 ?
                            <div className="bonus-icon-prize">
                                <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>
                            </div>
                            : item.prize_type == 1 ?
                                <React.Fragment>
                                    <div className="currency-icon-prize">{Utilities.getMasterData().currency_code}</div>

                                </React.Fragment>
                                :
                                <React.Fragment>
                                    {
                                        item.prize_type == 2 ?
                                            <img alt="" src={Images.IC_COIN} width="50px" />
                                            :
                                            <div>
                                                {
                                                    item.prize_type == 3 &&
                                                    merchandiseList && merchandiseList.map((merchandise, index) => {
                                                        return (
                                                            <React.Fragment key={index}>
                                                                {item.amount == merchandise.merchandise_id &&
                                                                    <img alt='' style={{ resizeMode: 'contain' }} src={Utilities.getMerchandiseURL(merchandise.image_name)} width="60px" height="60px" />
                                                                }

                                                            </React.Fragment>
                                                        );
                                                    })


                                                }
                                            </div>
                                    }
                                </React.Fragment>


                    }


                </div>
                <div className="rank-ribbon">
                    <span className="ribbon-text">{item.min == item.max ? item.min == 1 ? (item.min + 'st') : item.min == 2 ? (item.min + 'nd') : item.min == 3 ? (item.min + 'rd') : item.min : item.min + ' - ' + item.max}</span>
                </div>
                <div className="prize-name">
                    {
                        isFrom == 'LeagueDetails'
                        ?
                        item.prize_type == 3 ? item.max_value : this.setCurrentMaxPrize(item.min_value, item) 
                        :
                        item.prize_type == 3 ? item.max_value :

                        <div>

                            {
                                item.prize_type == 1 ?
                                    <React.Fragment>
                                        <div className="currency-icon-prize">{Utilities.getMasterData().currency_code}</div>
                                        {Utilities.getExactValueContest(this.setCurrentMaxPrize(item.min_value, item))}
                                    </React.Fragment>
                                    :
                                    item.prize_type == 0 ?
                                        <React.Fragment>
                                            <div className="bonus-icon-prize-amount">
                                                <span className="contest-prizes"><div className="icon-bonus"></div></span>
                                            </div>
                                            {parseFloat(this.setCurrentMaxPrize(item.min_value, item)).toFixed(0)}
                                        </React.Fragment>
                                        :
                                        item.prize_type == 2 ?
                                            <React.Fragment>
                                                <img className="contest-prizes coin-cp" style={{ height: '12px', marginTop: '-2px', width: '15px',height:'20px',verticalAlign: 'middle' }} alt="" src={Images.IC_COIN} />
                                                {parseFloat(this.setCurrentMaxPrize(item.min_value, item)).toFixed(0)}
                                            </React.Fragment>
                                            :
                                            <React.Fragment>
                                                {parseFloat(this.setCurrentMaxPrize(item.min_value, item)).toFixed(0)}
                                            </React.Fragment>
                            }

                        </div>

                    }
                    
                </div>
            </Col>
        )
    }

}
export default FtpPrizeComponent;