import React, { Component ,lazy, Suspense} from 'react';
import {Button} from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { _Map } from '../../Utilities/Utilities';
const ReactSlidingPane = lazy(()=>import('../../Component/CustomComponent/ReactSlidingPane'));

export default class PTScoreSelModal extends Component {

    constructor(props) {
        super(props);
        this.state = {
            isPaneOpen: false,
            isPaneOpenLeft: false,
            isPaneOpenBottom: true,
        }
    }

    render() {
        const {list,scorePredictFor,selectedawayScore,selectedhomeScore,scorePredictItem}= this.props;

        return (
        <div>
            <React.Fragment>
                    <Suspense fallback={<div />} ><ReactSlidingPane
                        isOpen={this.state.isPaneOpenBottom}
                        from='bottom'
                        width='100%'
                        onRequestClose={this.props.hideScoreMdl}
                        >
                        <div className="filter-body score-predict-filter">
                            <ul>
                                 <li className="heading">{AL.PREDICT} {AL.SCORE}</li>
                                 {(scorePredictItem.away_predict && scorePredictItem.home_predict )  && <li onClick={()=>this.props.userScoreSelection('')}>-</li>}
                                {
                                    _Map(list,(item,idx)=>{
                                        return (
                                            <li className={`${scorePredictFor == 'home' ? (((scorePredictItem.home_predict && scorePredictItem.home_predict == item) || (selectedhomeScore != '' && selectedhomeScore == item)) ? 'active' : '') : (((scorePredictItem.away_predict && scorePredictItem.away_predict == item) || (selectedawayScore != '' && selectedawayScore == item)) ? 'active' : '')}`}
                                             onClick={()=>this.props.userScoreSelection(item)}>{item}</li>
                                        )
                                    })
                                }
                            </ul>
                        </div>
                    </ReactSlidingPane></Suspense>
                </React.Fragment>
        </div>
        )
    }
}
