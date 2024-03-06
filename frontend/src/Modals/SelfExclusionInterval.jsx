import React, { Suspense, lazy} from 'react';
import * as AL from "../helper/AppLabels";
import { _Map} from "../Utilities/Utilities";
import { MyContext } from './../InitialSetup/MyProvider';
import { Utilities } from '../Utilities/Utilities';
const ReactSlidingPane = lazy(()=>import('../Component/CustomComponent/ReactSlidingPane'));
export default class SelfExclusionInterval extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            isPaneOpen: false,
            isPaneOpenLeft: false,
            isPaneOpenBottom: true,
            isLoading: false,
            DSE_LIM: this.props.data.DSE_LIM,
            USE_LIM: this.props.data.USE_LIM,
            LimitArray: [],
            equalpart: 5
        };
    }

    componentDidMount() {
        this.setInterval()
    }

    setInterval=()=>{
        const {DSE_LIM} = this.state;
        this.setState({
            isLoading: true
        })
        let min = parseFloat(DSE_LIM.default_limit);
        let max = parseFloat(DSE_LIM.max_limit);
        let equalpart = this.state.equalpart - 1;
        let diff = (max-min)/equalpart;

        var val = min;
        this.state.LimitArray.push(val);
        for (let i = 1; i <= equalpart; i++) {
            if(i != equalpart){
                val = this.state.LimitArray[i - 1];
                val = parseFloat(val) + parseFloat(diff);
            }
            else{
                val = max
            }
            this.state.LimitArray.push(Math.ceil(val))
        }
        this.setState({
            isLoading: false
        })
        return this.state.LimitArray;
    }

    render() {
        const { LimitArray , isLoading} = this.state;
        const { showLimit , hideLimit,data} = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                   <div className="filter-container">
                   <div ref={ref => this.el = ref} >
                   <Suspense fallback={<div />} ><ReactSlidingPane
                           isOpen={showLimit}
                           from='bottom'
                           width='100%'
                           overlayClassName={'filter-custom-overlay bottom-tab-height'}
                           onRequestClose={this.props.hideLimit}
                       >
                            <div className="filter-body self-exc-limit-wrap">
                                <div className="header-text">{AL.SET_LOSING_LIMIT}</div>
                                <ul>
                                    {
                                        !isLoading && LimitArray && _Map(LimitArray,(item,idx)=>{
                                            return (
                                                <li key={idx} className={data.SelectedInt==item ? 'active' : ''} onClick={()=>this.props.setNewinterval(item)}>{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(item)}</li>
                                            )
                                        })
                                    }
                                </ul>
                            </div>
                            </ReactSlidingPane></Suspense>
                        </div>
                        </div>
                )}
            </MyContext.Consumer>
        );
    }
}