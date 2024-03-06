import React, { Component, Fragment } from "react";
import { Row, Col, Table, Input, Button } from 'reactstrap';
import Select from 'react-select';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import HF from '../../helper/HelperFunction';
import SelectDropdown from "../../components/SelectDropdown";
import { UP_SCORE_MSG, UP_SCORE_SUB_MSG } from '../../helper/Message';
import { updateNewMasterScoringPoints } from '../../helper/WSCalling';
import PromptModal from '../../components/Modals/PromptModal';
const cricket_format = [
    { value: 1, label: 'One day' },
    { value: 2, label: 'Test' },
    { value: 3, label: 'T20' },
    { value: 4, label: 'T10' }
]
class ManageScoring extends Component {
    constructor(props) {
        super(props)
        this.state = {
            master_sports: [],
            selected_sports: 7,
            selected_cate: 0,
            selected_format: 1,
            MasterScoringRules: [],
            RequestArr: [],
            UpdateLoading: false,
            updateArr: [],
            catFilters: [],
            sports_list: HF.getSportsData() ? HF.getSportsData() : [],
            ModalOpen: false,
        }
    }
    componentDidMount() {
        this.getScoringFilter()
        this.getSCoringRule()
    }
    getScoringFilter = () => {
        let param = {}
        WSManager.Rest(NC.baseURL + NC.GET_SCORING_FILTERS, param).then(responseJson => {
            if (responseJson.response_code === NC.successCode) {
                let catFilters = responseJson.data.filters
                this.setState({ catFilters: catFilters })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    getSCoringRule() {
        let { selected_sports, selected_cate, selected_format } = this.state
        const param = {
            sport_id: selected_sports,
            format: selected_format,
            cat_id: selected_cate
        }

        WSManager.Rest(NC.baseURL + NC.GET_SCORING_RULES, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({ MasterScoringRules: responseJson.data.master_scoring_rules });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }
    handleSportSelect = (value) => {
        if (value != null)
            this.setState({
                selected_format: 1,
                selected_sports: value.value,
                selected_cate: 0
            }, () => {
                let spCate = []
                let tempSpoCate = this.state.catFilters[this.state.selected_sports].scoring_cat

                _.map(tempSpoCate, function (CFormat) {
                    spCate.push({
                        value: CFormat.master_scoring_category_id,
                        label: CFormat.scoring_category_name
                    });
                })
                this.setState({ sportsCate: spCate })
            })
    }
    handleCateSelect = (value) => {
        if (value != null)
            this.setState({ selected_cate: value.value }, () => {
                this.getSCoringRule()
            })
    }
    handleCricketFormat = (value) => {
        if (value != null)
            this.setState({ selected_format: value.value }, () => {
                this.getSCoringRule()
            })
    }
    pointChange = (idx, value) => {
        let temp = this.state.MasterScoringRules;
        temp[idx].score_points = value

        let dict = { score_points: "", master_scoring_id: "" }
        dict.score_points = temp[idx].score_points
        dict.master_scoring_id = temp[idx].master_scoring_id

        let temparray = this.state.updateArr;
        let isExistIdx = -1;
        _.map(temparray, (item, idx) => {
            if (item.master_scoring_id == dict.master_scoring_id) {
                isExistIdx = idx;
            }
        });
        if (isExistIdx >= 0) {
            temparray[isExistIdx] = dict;
        } else {
            temparray.push(dict)
        }

        this.setState({
            MasterScoringRules: temp,
            UpdateLoading: true,
            RequestArr: temparray,
            updateArr: temparray
        })
    }
    updateScoring() {

        let RequestArr = { scoring: this.state.RequestArr, sports_id: this.state.selected_sports }
        this.setState({ UpdateLoading: false })
        WSManager.Rest(NC.baseURL + NC.UPDATE_MASTER_SCORING_POINTS, RequestArr).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show("Score points updated successfully", "success", 3000);
                this.setState({ UpdateLoading: true })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })

    }

    scoreModalToggle = () => {
        this.setState({
            ModalOpen: !this.state.ModalOpen
        })
    }

    newUpdateScore = () => {
        this.setState({ modalPosting: true })
        let params = {
            sports_id: this.state.selected_sports
        }

        updateNewMasterScoringPoints(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    ModalOpen: false
                }, this.getSCoringRule)
            }
            this.setState({ modalPosting: false, })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    render() {
        let { sports_list, selected_sports, selected_cate, MasterScoringRules, selected_format, UpdateLoading, sportsCate, modalPosting, ModalOpen } = this.state
        const Sports_Props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: sports_list,
            place_holder: "Select Sports",
            selected_value: selected_sports,
            modalCallback: this.handleSportSelect
        }

        const Format_Props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: cricket_format,
            place_holder: "Select Format",
            selected_value: selected_format,
            modalCallback: this.handleCricketFormat
        }

        const Type_Props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: sportsCate,
            place_holder: "Select Category",
            selected_value: selected_cate,
            modalCallback: this.handleCateSelect
        }

        let modalProps = {
            publishModalOpen: ModalOpen,
            publishPosting: modalPosting,
            modalActionNo: this.scoreModalToggle,
            modalActionYes: this.newUpdateScore,
            MainMessage: UP_SCORE_MSG,
            SubMessage: UP_SCORE_SUB_MSG,
        }

        return (
            <Fragment>
                {ModalOpen && <PromptModal {...modalProps} />}
                <Row className="mt-4">
                    <Col md={12}>
                        <h1 className="h1-cls">Manage Scoring</h1>
                    </Col>
                </Row>
                <Row className="filters-box mt-4">
                    <Col md={12}>
                        {
                            HF.allowNetworkGame() == '1' &&
                            <div className="filters-area">
                                <Button
                                    onClick={() => this.scoreModalToggle()}
                                    className="rules-up-btn"
                                >Update New Scores</Button>
                            </div>
                        }
                        <div className="filters-area mr-3">
                            <SelectDropdown SelectProps={Type_Props} />
                        </div>
                        {
                            selected_sports == 7 &&
                            <div className="filters-area mr-3">
                                <SelectDropdown SelectProps={Format_Props} />
                            </div>
                        }
                        <div className="filters-area mr-3">
                            <SelectDropdown SelectProps={Sports_Props} />
                        </div>
                    </Col>
                </Row>
                <Row className="scoring-main">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead>
                                <tr>
                                    <th className="left-th pl-4">Rule Description	</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            {
                                _.map(MasterScoringRules, (item, idx) => {
                                    return (
                                        <tbody key={idx}>
                                            <tr>
                                                <td className="pl-4">{item.score_position}</td>
                                                <td>
                                                    <Input
                                                        type="number"
                                                        name={`score_points_${idx}`}
                                                        className="form-control"
                                                        value={item.score_points}
                                                        onChange={(e) => this.pointChange(idx, e.target.value)}
                                                        disabled={HF.allowNetworkGame() == '1'}
                                                    />
                                                </td>
                                            </tr>
                                        </tbody>
                                    )
                                })
                            }
                        </Table>
                    </Col>
                </Row>
                {
                    (!_.isEmpty(MasterScoringRules) && HF.allowNetworkGame() != '1') &&
                    (
                        <Row className="update-btn-box">
                            <Col md={12}>
                                <button disabled={!UpdateLoading} className="btn-secondary" onClick={() => this.updateScoring()}>Update</button>
                            </Col>
                        </Row>
                    )
                }
            </Fragment>
        )
    }
}
export default ManageScoring