import React from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { getStockScoreCalculation } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { Utilities } from '../../Utilities/Utilities';

class StockScoreCalculation extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            selectedLineup: '',
            CollectionData: '',
            teamPlayerData: ''
        };


    }

    componentDidMount() {
        this.setState({ selectedLineup: this.props.selectedLineup, CollectionData: this.props.CollectionData }, () => {
            this.getTeamPlayers()
        })
    }

    getTeamPlayers = (item) => {

        let param = {
            "lineup_master_id": this.state.selectedLineup,
            "collection_id": this.state.CollectionData.collection_master_id,
        }
        getStockScoreCalculation(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    teamPlayerData: responseJson.data
                })
            }
        })
    }

    render() {
        const { isViewAllShown, onViewAllHide, total_score } = this.props;
        const { teamPlayerData } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={isViewAllShown} onHide={onViewAllHide} bsSize="large" className="stock-team-view-modal">
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
                                <div className="player-list-container scoring">
                                    <div className="item-header">
                                        <span style={{ width: '16%' }}>Shares</span>
                                        <span style={{ width: '12%' }}>B/S</span>
                                        <span style={{ width: '22%' }}>Opening</span>
                                        <span style={{ width: '25%' }}>Closing</span>
                                        <span style={{ width: '25%' }}>Gain/Loss</span>
                                    </div>
                                    {
                                        (teamPlayerData || []).map((item, index) => {
                                            let pDiff = parseFloat(item.price_diff || 0).toFixed(2);
                                            pDiff = pDiff == 0 ? 0 : pDiff;
                                            return (
                                                <div key={index}>
                                                    <div className='scoring-name-c'>
                                                        <img className="player-image" src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                                                        <div className="player-name">
                                                            {item.name || item.stock_name}
                                                        </div>
                                                    </div>
                                                    <div className="value-v">
                                                        <span style={{ width: '16%' }}>{item.lot_size}</span>
                                                        <span style={{ width: '12%' }} className='bt'>{item.type == 1 ? 'B' : "S"}</span>
                                                        <span style={{ width: '22%' }}>{item.open_price}</span>
                                                        <span style={{ width: '25%' }} className={pDiff < 0 ? 'down' : 'buy'}><i className={pDiff < 0 ? "icon-stock_down" : "icon-stock_up"} />1710.00</span>
                                                        <span style={{ width: '25%' }}>{pDiff > 0 ? '+' : ''}{Utilities.numberWithCommas(pDiff)}{/*(0.25%)*/}</span>
                                                    </div>
                                                </div>
                                            )
                                        })
                                    }
                                </div>
                                {total_score && <button className="btn-primary bottom btn btn-primary-bottom-stk pts" ><span>{AL.TOTAL_POINTS}</span><span style={{ fontSize: 20 }}>{Utilities.numberWithCommas(total_score)}</span></button>}
                            </Modal.Body>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default StockScoreCalculation;