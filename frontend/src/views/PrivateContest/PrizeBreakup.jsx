import React from 'react';
import { Modal, Button, FormGroup, Row, Col } from 'react-bootstrap';
import { MyContext } from "../../InitialSetup/MyProvider";
import { _Map ,Utilities} from '../../Utilities/Utilities';
import * as AppLabels from "../../helper/AppLabels";
import Images from '../../components/images';

export default class PrizeBreakup extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            noOfWinner:this.props.noOfWinner,
            entryFee:this.props.entryFee,
            hostRake:this.props.hostRake,
            siteRake:this.props.siteRake,
            prize_distribution_data : this.props.prize_distribution_data,
            optionSelectedIndex:this.props.optionSelectedIndex,
            selectedType:this.props.selectedType
        }
    }

    getPrizeDistributionData(){
        let {prize_distribution_data,noOfWinner} = this.state;
        let selectedPrizeData = prize_distribution_data[noOfWinner];
        for (let i=0;i<selectedPrizeData.length;i++){
            let allItems = selectedPrizeData[i];
            let totalWinning = 0;
            for (let j=0;j<allItems.length;j++){
                let winValue = this.calculateWinning(allItems[j].per,j)
                allItems[j].winning = winValue.toFixed(2);
                totalWinning = totalWinning+ winValue;
            }
            selectedPrizeData[i].totalWinning = totalWinning;
        }
        return selectedPrizeData;
    }

    calculateWinning(percentValue,childItemindex){
        let minNoOfParticipants = 2;
        let {entryFee,siteRake,hostRake} = this.state;
        let totalAmount = parseFloat(entryFee)*minNoOfParticipants;
        let totalRake = parseFloat(siteRake)+parseFloat(hostRake);
        let amountAfterSiteRake,winningAmt;

        if (this.state.selectedType == 2) {
            amountAfterSiteRake = totalAmount;
            winningAmt = (amountAfterSiteRake * percentValue) / 100;
            return Math.floor(winningAmt);

        }
        else {
            amountAfterSiteRake = totalAmount - ((totalAmount * totalRake) / 100);
            winningAmt = (amountAfterSiteRake * percentValue) / 100;
            return winningAmt;

        }
    }

    getWinning(totalWinning){
        if(totalWinning){
            if(totalWinning%1==0){
                return totalWinning;
            }
            else{
                return totalWinning.toFixed(2)
            }
        }
        return '0';
    }

    render() {
        let {prize_distribution_data,optionSelectedIndex} = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                    show={this.props.isShow}
                    onHide={()=>this.props.isHide(optionSelectedIndex)}
                    dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden prize-breakup-modal"
                    className="center-modal"
                    >
                            <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap">
                                    <i className="icon-prize-breakup"></i>   
                                </div>
                            </div>
                            {AppLabels.PRIZE_BREAKUP}
                        </Modal.Header>
                        <Modal.Body className="static-page">
                            <React.Fragment>
                                <div className="webcontainer-inners mt-0">  
                                    <div className='prize-breakup-list'>
                                       
                                       {prize_distribution_data&&
                                        _Map(this.getPrizeDistributionData(), (item, index) => {
                                            return(
                                                <div className='prize-breakup-item'>
                                                    <div onClick={()=>this.setState({optionSelectedIndex:index})} className={'prize-breakup-header '+(index===optionSelectedIndex?'selected':'')}>
                                                        <div className={'header-item '+(index===optionSelectedIndex?'selected':'')}>
                                                            {index===optionSelectedIndex?
                                                            <i className='icon-tick-circular'/>
                                                            :
                                                            <div className='option-unselect'></div>
                                                            }
                                                            <span> {AppLabels.PRIZE_OPTION}{' '}{(index+1)}</span>
                                                        </div>
                                                        <div className={'header-item text-right-align '+(index===optionSelectedIndex?'selected':'')}>{AppLabels.TOTAL_WINNINGS} {this.state.selectedType == 2 ? <img src={Images.IC_COIN} alt="" width='14px' />:Utilities.getMasterData().currency_code+' '}{this.getWinning(item.totalWinning)}</div>
                                                    </div>
                                                    <div className={'table-container '+(index===optionSelectedIndex?'selected':'')}>
                                                        <div className='table-header'>
                                                            <div className='header-item'>{AppLabels.RANK}</div>
                                                            <div className='header-item left-align'>{AppLabels.WINNING+'%'}</div>
                                                            <div className='header-item left-align'>{AppLabels.WINNING}</div>
                                                        </div>
                                                    
                                                        { _Map(item, (childItem, childItemindex) => {
                                                            return(
                                                                <div className='table-body'>
                                                                    <div className='table-item'>{(childItem.min===childItem.max)?childItem.min:(childItem.min+'-'+childItem.max)}</div>
                                                                    <div className='table-item left-align'>{childItem.per}{'%'}</div>
                                                                    <div className='table-item bold left-align'>{
                                                    this.state.selectedType == 2 ?
                                                            <div>
                                                                <img src={Images.IC_COIN} alt="" width='14px' />
                                                                {
                                                                    childItem.winning && childItem.winning
                                                                }
                                                            </div>
                                                            :Utilities.getMasterData().currency_code + ' ' + childItem.winning
                                                    }</div>
                                                                </div>
                                                            )
                                                        })
                                                    }
                                                    </div>
                                                </div>
                                            )
                                        })   
                                    }
                                    </div>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}