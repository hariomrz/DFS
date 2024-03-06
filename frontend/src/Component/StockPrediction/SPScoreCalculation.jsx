import React from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { getSPScoreCalculation } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { Utilities,_Map } from '../../Utilities/Utilities';

class SPScoreCalc extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            selectedLineup: '',
            CollectionData: '',
            teamPlayerData: '',
            contestId: '',
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
            "collection_id": this.state.CollectionData.collection_id,
            "stock_type":"2",
            "contest_id": this.state.contestId
        }
        getSPScoreCalculation(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                let tmpC = responseJson.data.lineup
                let IsName = tmpC[0] && tmpC[0].name ? true : false
                let filterArry = [] 
                if(IsName){
                    filterArry = tmpC.sort((a, b) => a.name.localeCompare(b.name))
                }
                else{
                    filterArry = tmpC.sort((a, b) => a.stock_name.localeCompare(b.stock_name))
                }
                this.setState({
                    teamPlayerData: filterArry,
                    totalPer: responseJson.data.percent_change
                },()=>{
                })
            }
        })
    }

    render() {
        const { 
            isShow, isHide, status
        } = this.props;
        const { teamPlayerData,totalPer} = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={isShow} onHide={isHide} bsSize="large" className="sp-score-calc">
                            <Modal.Header>
                                <Modal.Title>
                                    <a href onClick={isHide} className="modal-close">
                                        <i className="icon-close"></i>
                                    </a>
                                    {AL.SCORE_CALCULATION}
                                </Modal.Title>
                            </Modal.Header>
                            <Modal.Body>
                                <div className="sp-high-sec">
                                    <div className="lbl">{AL.OPENING}</div>
                                    <div className="lbl">{status == 1 ? AL.CURRENT : AL.CLOSING}</div>
                                    <div className="lbl">{AL.PREDICTED}</div>
                                    <div className="lbl">{AL.ACCURACY}</div>
                                </div>
                                {
                                    teamPlayerData && teamPlayerData.length > 0 &&
                                    _Map(teamPlayerData, (item, idx) => {
                                        let isNeg = item.accuracy_percent && parseFloat(item.accuracy_percent) < 0 ? true : false
                                        return(
                                            <div className="sp-stk-dtl" key={item.stock_id}>
                                                <div className="stk-nm"> 
                                                    <img src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" /> 
                                                    <span>{item.name}</span>
                                                </div>
                                                <div className="stk-otr-dlt">
                                                    <div className="lbl">{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(item.open_price)}</div>
                                                    <div className="lbl">{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(item.close_price)}</div>
                                                    <div className="lbl">{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(item.user_price)}</div>
                                                    <div className={`lbl ${isNeg ? ' lbl-dang' : ' lbl-succ'}`}>{item.accuracy_percent}%</div>
                                                </div>
                                            </div>
                                        )
                                    })
                                }
                                <div className="ttl-scr">
                                   {parseFloat(totalPer).toFixed(2)}%
                                    <span>{AL.TOTAL}</span>
                                </div>
                            </Modal.Body>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default SPScoreCalc;