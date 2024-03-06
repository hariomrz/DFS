import React, { Suspense, lazy } from 'react';
import { FormGroup, Button, Checkbox } from 'react-bootstrap';
import Modal from 'react-modal';
import * as AppLabels from "../../helper/AppLabels";
import { _isEmpty } from "../../Utilities/Utilities";
import { MyContext } from '../../InitialSetup/MyProvider';
const ReactSlidingPane = lazy(()=>import('../../Component/CustomComponent/ReactSlidingPane'));

export default class StockRosterFilterEq extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            isPaneOpen: false,
            isPaneOpenLeft: false,
            isPaneOpenBottom: true,
            checkbox: false,
            selectedIndustry: this.props.selectedIndustry
        };
    }

    componentDidMount() {
        Modal.setAppElement(this.el);
    }

    handleTeamChange = (item) => {
        this.setState({
            selectedIndustry: item
        })
    }

    render() {

        const { filterArry, onSelected } = this.props;


        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="filter-container">
                        <div ref={ref => this.el = ref} >
                            <Suspense fallback={<div />} ><ReactSlidingPane
                                isOpen={this.state.isPaneOpenBottom}
                                from='bottom'
                                width='100%'
                                onRequestClose={this.handleFilterClose}
                            >
                                <div className="filter-header shadow">
                                    <i className="icon-reload" onClick={() => onSelected('')}></i>
                                    {AppLabels.Filters}
                                    <Button className="done-btn active" onClick={() => onSelected(this.state.selectedIndustry)}>{AppLabels.DONE}</Button>
                                </div>
                                <div className="filter-body">
                                    <ul className='pt10'>
                                        {
                                            !_isEmpty(filterArry)
                                                ?
                                                filterArry.map((item, index) => {
                                                    return (
                                                        <li className='pt10 pb10 pl15 pr15 bottom-padding' key={"leagueList" + index}>
                                                            <FormGroup>
                                                                <Checkbox className="custom-checkbox" value={item.industryID} onChange={() => this.handleTeamChange(item)} checked={this.state.selectedIndustry ? (this.state.selectedIndustry.industryID == item.industryID) : false} name="lobby_filter_leagues" id={"industry-" + item.industryID}>
                                                                    <span>{item.industryName}</span>
                                                                </Checkbox>
                                                            </FormGroup>
                                                        </li>
                                                    );


                                                })


                                                :
                                                <li></li>

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