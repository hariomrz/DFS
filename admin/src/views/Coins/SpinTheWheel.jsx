import React, { Component, Fragment } from "react";
import { Row, Col, Input, Button, Table } from 'reactstrap';
import Select from 'react-select';
import _ from 'lodash';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import WSManager from '../../helper/WSManager';
import HF from "../../helper/HelperFunction";
const PrizeTypeOpt = [
    { label: 'Bonus Cash', value: '1' },
    { label: 'Real Cash', value: '0' },
    { label: 'Coins', value: '2' },
    { label: 'Merchandise', value: '3' },
]

const WinTypeOption = [
    { label: 'Win', value: '1' },
    { label: 'Loss', value: '0' },
]

const activeStatusOption = [
    { label: 'Active', value: '1' },
    { label: 'In active', value: '0' },
]

class Spinthewheel extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            PERPAGE: 999999,
            SetPrizeDaily: false,
            SetPrizeWeek: false,
            SetPrizeMonth: false,
            selectSetPrize: false,
            selectUnsetPrize: true,
            ProbabilityoPt: [],
            SliceList: [],
            formValid: true,
        }
    }

    componentDidMount() {
        if (HF.getMasterData().allow_spin != '1') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getProbability()
        this.getMerchandiseList()
        this.getSliceList()
    }

    getProbability = () => {
        let items = []
        for (var index = 0; index < 100; index++) {
            items.push({ label: index, value: index })
        }
        this.setState({ ProbabilityoPt: items })
    }

    getSliceList = () => {
        WSManager.Rest(NC.baseURL + NC.WHEEL_SLICES_LIST, {}).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({ SliceList: Response.data.result })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getMerchandiseList = () => {
        let { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            sort_field: "added_date",
            sort_order: "DESC",
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }
        WSManager.Rest(NC.baseURL + NC.GET_MERCHANDISE_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                let tempMList = [];
                Response.data.merchandise_list.map(function (item, lKey) {
                    tempMList.push({ value: item.merchandise_id, label: item.name });
                });
                this.setState({
                    MerchandiseList: tempMList
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getMerchantName = (m_id) => {
        let MerchandiseList = this.state.MerchandiseList
        let tempMIdArr = []
        MerchandiseList.map(function (item, lKey) {
            tempMIdArr.push(item.value);
        });

        if (tempMIdArr.includes(m_id)) {
            if (!_.isEmpty(MerchandiseList)) {
                return MerchandiseList.find(x => x.value === m_id).label
            }
        } else {
            return ''
        }
    }

    handleInpuChange = (e, indx, keyname) => {
        let tempSlList = this.state.SliceList

        if (e) {
            if (e.target.value.length < 7)
            {
                tempSlList[indx][keyname] = HF.decimalValidate(e.target.value, 3);
            }else{

            }
            let cashTypeTxt = ''
            let tCashTyp = tempSlList[indx]['cash_type']
            cashTypeTxt = tCashTyp == '0' ? ' Real Cash' : tCashTyp == '1' ? ' Bonus Cash' : tCashTyp == '2' ? ' Coins' : tCashTyp == '3' ? ' ' : ' --'
            let apndTxt = tempSlList[indx]['amount'] + cashTypeTxt
            tempSlList[indx]['result_text'] = 'You won ' + apndTxt

            if (tempSlList[indx]['cash_type'] == "3") {
                tempSlList[indx]['slice_name'] = apndTxt
            } else {
                tempSlList[indx]['slice_name'] = apndTxt
            }

            this.setState({ SliceList: tempSlList }, () => {

                let sNameArr = []
                _.map(this.state.SliceList, (sName) => {
                    sNameArr.push(sName.amount);
                })
                let res = sNameArr.every(val => {
                    return (!_.isEmpty(val) && val !== '0' && val.length < 7) ? true : false
                });

                this.setState({ formValid: !res })
            })
        }
    }

    handlePrizeType = (e, indx, keyname) => {        
        let { SliceList } = this.state
        if (e) {
            SliceList[indx][keyname] = e.value
            let cashType = ''
            if (SliceList[indx]['cash_type'] != '3') {
                cashType = SliceList[indx]['cash_type'] == '0' ? ' Real Cash' : SliceList[indx]['cash_type'] == '1' ? ' Bonus Cash' : SliceList[indx]['cash_type'] == '2' ? ' Coins' : SliceList[indx]['cash_type'] == '3' ? ' ' : ' --'
            }

            let apndTxt = SliceList[indx]['amount'] + cashType
            if (SliceList[indx]['cash_type'] == '3') {
                apndTxt = this.getMerchantName(SliceList[indx]['amount'])
            }

            SliceList[indx]['result_text'] = 'You won ' + apndTxt 
            
            if (SliceList[indx]['cash_type'] == "3") {
                SliceList[indx]['slice_name'] = apndTxt
            }else{
                SliceList[indx]['slice_name'] = apndTxt
            }

            if (SliceList[indx]['win'] === '0') {
                SliceList[indx]['result_text'] = 'Please try again tomorrow'
                SliceList[indx]['slice_name'] = 'Please try again tomorrow'
            }
            this.setState({ SliceList: SliceList })
            if (e.value == '3' && keyname === "cash_type" && _.isEmpty(this.getMerchantName(SliceList[indx]['amount']))) {
                this.setState({ formValid: true })
            }else{
                this.setState({ formValid: false })
            }

        }
    }

    updateSliceList = () => {
        this.setState({ formValid: true })
        let { SliceList } = this.state
        WSManager.Rest(NC.baseURL + NC.SLICES_UPDATE, SliceList).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ formValid: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    render() {
        let { SliceList, ProbabilityoPt, formValid, MerchandiseList } = this.state
        return (
            <div className="spinthewheel">
                <Row>
                    <Col md={12} className="mt-4">
                        <h2 className="h2-cls float-left">SPIN THE WHEEL</h2>
                    </Col>
                </Row>
                <Row className=" mt-3">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Prize</th>
                                    <th>Amount</th>
                                    <th>Probability</th>
                                    <th>Win</th>
                                    <th>Wheel conetent</th>
                                    <th>Result text</th>
                                </tr>
                            </thead>
                            {
                                _.map(SliceList, (newrow, idx) => {
                                    return (
                                        <tbody key={idx}>
                                            <tr>
                                                <td>
                                                    <Select
                                                        className="sw-prize-type"
                                                        searchable={false}
                                                        clearable={false}
                                                        placeholder="Select"
                                                        name='status'
                                                        value={newrow.status}
                                                        options={activeStatusOption}
                                                        onChange={(e) => this.handlePrizeType(e, idx, 'status')}
                                                    />
                                                </td>
                                                <td>
                                                    <Select
                                                        className="sw-prize-type"
                                                        disabled={newrow.win === '0'}
                                                        readOnly={newrow.win === '0'}
                                                        searchable={false}
                                                        clearable={false}
                                                        placeholder="Select"
                                                        name='cash_type'
                                                        value={newrow.cash_type}
                                                        options={PrizeTypeOpt}
                                                        onChange={(e) => this.handlePrizeType(e, idx, 'cash_type')}
                                                    />
                                                </td>
                                                <td>
                                                    {
                                                        newrow.cash_type !== '3' &&
                                                        <Fragment>
                                                            <Input
                                                                className="sw-prize-type"
                                                                disabled={newrow.win === '0'}
                                                                readOnly={newrow.win === '0'}
                                                                type="number"
                                                                name='amount'
                                                                maxLength={6}
                                                                value={newrow.amount}
                                                                onChange={(e) => this.handleInpuChange(e, idx, 'amount')}
                                                            />
                                                            <div className="spw-e-hgt">
                                                                {
                                                                    (_.isEmpty(newrow.amount) || newrow.amount === '0' || newrow.amount.length > 6) &&
                                                                    <span className="spw-empty-msg">Please enter min 1 and max upto 6 digit</span>
                                                                }
                                                            </div>                                                            
                                                        </Fragment>
                                                    }

                                                    {
                                                        newrow.cash_type === '3' &&
                                                        <Select
                                                            className="sw-prize-type"
                                                            disabled={newrow.win === '0'}
                                                            searchable={false}
                                                            clearable={false}
                                                            placeholder="Select"
                                                            name='amount'
                                                            value={newrow.amount}
                                                            options={MerchandiseList}
                                                            onChange={(e) => this.handlePrizeType(e, idx, 'amount')}
                                                        />
                                                    }
                                                </td>
                                                <td>
                                                    <Select
                                                        searchable={false}
                                                        clearable={false}
                                                        placeholder="Select"
                                                        name='probability'
                                                        value={newrow.probability}
                                                        options={ProbabilityoPt}
                                                        onChange={(e) => this.handlePrizeType(e, idx, 'probability')}
                                                    />
                                                </td>
                                                <td>
                                                    <Select
                                                        searchable={false}
                                                        clearable={false}
                                                        placeholder="Select"
                                                        name='win'
                                                        value={newrow.win}
                                                        options={WinTypeOption}
                                                        onChange={(e) => this.handlePrizeType(e, idx, 'win')}
                                                    />
                                                </td>
                                                <td>
                                                    <Input
                                                        readOnly={true}
                                                        disabled={true}
                                                        type="text"
                                                        name='slice_name'
                                                        maxLength={50}
                                                        value={newrow.slice_name}
                                                        title={newrow.slice_name}
                                                    />
                                                </td>
                                                <td>
                                                    <Input
                                                        readOnly={true}
                                                        disabled={true}
                                                        type="text"
                                                        name='result_text'
                                                        value={newrow.result_text}
                                                        title={newrow.result_text}
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
                <Row className="m-5 text-center">
                    <Col md={12}>
                        <Button
                            disabled={formValid}
                            className="btn-secondary-outline"
                            onClick={() => this.updateSliceList()}
                        >Update</Button>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default Spinthewheel
