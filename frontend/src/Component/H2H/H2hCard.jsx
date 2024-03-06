import React from 'react';
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';

class H2hCard extends React.Component {

    constructor(props) {
        super(props);
        this.state = {

        }
    }

    componentDidMount() {
    }




    render() {

        const { } = this.props


        return (
            <div className='h2h-card' onClick={() => this.props.goToDetail()}>
                <div className='h2h-card-header'>
                    <div>
                        <img src={Images.H2H} alt="" />
                    </div>
                    <div>
                        <h4>{AppLabels.H2H_CHALLENGE}</h4>
                        <p>{AppLabels.H2H_CARD_HEAD}</p>
                    </div>
                    <div className='info-hh' onClick={(e) => this.props.H2hModalShow(e)}><i className='icon-question'></i></div>
                </div>

                {/* <p className='h2h-card-body'>{AppLabels.H2H_CARD_DESC}</p> */}
                {/* <button className='btn btn-primary'>{AppLabels.SEE_ALL_CONTESTS}</button> */}
                <div className='h2h-btm-box'>
                    <div className='left-box-h2h'>
                        <div className='left-box-view-new'>
                        <p className='icon-name'>
                            <span><i className='icon-h2h-logo' /></span>

                        </p>
                        <span className='name-contest'>{this.props.c_name != '' ? this.props.c_name :
                            <span>{AppLabels.WIN} {' '} {this.props.getPrizeAmount(this.props.item, 1)}</span>
                        }</span>
                        </div>
                        {this.props.e_fees !== '0' ?
                            <div className='ef-btn'>
                                {this.props && this.props.item && this.props.item.currency_type == "1" ? Utilities.getMasterData().currency_code : 
                                this.props.item.currency_type == "2" ? <img className="img-coin" style={{ height: 15, width: 15, marginRight: 5 }} alt='' src={Images.IC_COIN} /> : ''
                                }
                                {' '}{this.props.e_fees}
                            </div>
                            :
                            <div className='ef-btn'>{AppLabels.FREE}</div>
                        }
                    </div>
                    <div className='rt-box-h2h'>
                        <span className='vm-txt'>{AppLabels.SEE_MORE}</span>
                        <div className='arrow-icon-container arrow-df'>
                            <i className="icon-arrow-right iocn-first"></i>
                            <i className="icon-arrow-right iocn-second"></i>
                            <i className="icon-arrow-right iocn-third"></i>

                        </div>

                    </div>
                </div>

            </div >
        )
    }
}

export default H2hCard;