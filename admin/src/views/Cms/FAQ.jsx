import React, { Component } from "react";
import { Input, Row, Col, Button } from "reactstrap";
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
class FAQ extends Component {
    constructor(props) {
        super(props)
        this.state = {
            page_id: (this.props.page_id) ? this.props.page_id : this.props.match.params.page_id,
            LanguageType: 'en',
            AddQAList: [{ "row_id": 0, 'question': '', 'answer': '' }],
            languageOptions: [],
            CategoryOptions: [],
            ViewCateData: '',
            QuestionList: [],
            updatePosting: true,
            delPosting: false
        }
    }

    componentDidMount = () => {
        if (!_.isEmpty(this.state.page_id)) {
            this.getLanguage()
            this.getCategory()
            this.getQuestions()
        }
    }

    getLanguage() {
        WSManager.Rest(NC.baseURL + NC.GET_LANGUAGE_LIST, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let ResponseData = responseJson.data.language_list
                let TempLang = []
                let TempLangDict = {}
                _.map(ResponseData, (language, idx) => {
                    TempLangDict = {
                        "label": language,
                        "value": idx,
                    }
                    TempLang.push(TempLangDict)
                })
                this.setState({
                    languageOptions: TempLang
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    getCategory() {
        let params = { language: this.state.LanguageType }
        WSManager.Rest(NC.baseURL + NC.GET_FAQ_CATEGORY, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let ResponseData = responseJson.data
                let TempCate = []
                let TempCateDisct = {}
                _.map(ResponseData, (item, idx) => {
                    TempCateDisct = {
                        "label": item.category,
                        "value": item.category_id,
                    }
                    TempCate.push(TempCateDisct)
                })
                this.setState({
                    CategoryOptions: TempCate
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    getQuestions() {
        let params = { language: this.state.LanguageType }
        WSManager.Rest(NC.baseURL + NC.GET_FAQ_QUESTION_ANSWER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    QuestionList: responseJson.data
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    handleLangChange = (value) => {
        this.setState({ LanguageType: value.value }, () => {
            this.getCategory()
            this.getQuestions()
        })
    }

    handleCateChange = (value) => {
        this.setState({ CategoryType: value.value })
    }

    deleteQuestionRow = (removeIndex) => {
        let { AddQAList } = this.state

        let tempPlaList = AddQAList
        _.remove(tempPlaList, (item) => {
            if (item.row_id == removeIndex) {
                return true;
            }
        })
        this.setState({ AddQAList: tempPlaList }, () => {
        })
    }

    addNewQuestion = () => {
        let tempPlaArr = this.state.AddQAList

        let lastRowId = 0
        if (!_.isEmpty(tempPlaArr)) {
            let arrId = parseInt(tempPlaArr.length) - 1
            lastRowId = parseInt(tempPlaArr[arrId].row_id) + 1
        }
        let tempAddPlaRow = {
            "row_id": lastRowId,
            'question': '',
            'answer': ''
        }
        tempPlaArr.push(tempAddPlaRow)
        this.setState({
            AddQAList: tempPlaArr,
        })
    }

    handleInputChange = (e, index, name) => {
        let tempPlaList = this.state.AddQAList
        if (!_.isNull(e)) {
            tempPlaList[index][name] = e.target.value
            this.setState({
                AddQAList: tempPlaList
            }, () => {
            })
        }
    }

    submitQuesAns = () => {
        this.setState({ submitPosting: true })
        let { page_id, PageTitle, MetaKeyword, MetaDesc, AddQAList, Description, LanguageType, CategoryType } = this.state
        let inputValid = false

        _.map(AddQAList, (item) => {
            if (item.question == "" || item.answer == "") {
                inputValid = true
            }
        })

        if (_.isEmpty(CategoryType)) {
            this.setState({ submitPosting: false })
            notify.show("Please select category.", "error", 3000)
            return false
        }

        if (inputValid) {
            this.setState({ submitPosting: false })
            notify.show("Please fill the complete form or delete row.", "error", 3000)
            return false
        }

        let params = {
            page_alias: "faq",
            language: LanguageType,
            category_id: CategoryType,
            questions: AddQAList
        }
        

        WSManager.Rest(NC.baseURL + NC.ADD_QUESTION_ANSWER, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.getQuestions()
                this.setState({ AddQAList: [{ "row_id": 0, 'question': '', 'answer': '' }]})
                notify.show(ResponseJson.message, "success", 3000)
            }
            this.setState({ submitPosting: false })
        }).catch(error => {
            this.setState({ submitPosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    showCategory = (show_idx) => {
        if (show_idx === this.state.ViewCateData) {
            show_idx = ''
        }
        this.setState({ ViewCateData: show_idx })
    }

    toggleActionPopup = (question_id, idx, cate_idx) => {
        this.setState({
            Message: NC.MSG_DELETE_QUES,
            catIdxVal: cate_idx,
            idxVal: idx,
            QuestionID: question_id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    deleteQuestion = () => {
        let { QuestionID, idxVal, catIdxVal } = this.state
        this.setState({ delPosting :  true })
        let params = {
            question_id: QuestionID,
            page_alias: "faq",
        }
        let TempQList = this.state.QuestionList
        WSManager.Rest(NC.baseURL + NC.DELETE_QUESTION_ANSWER, params).then(Response => {
            if (Response.response_code == NC.successCode) {

                TempQList[catIdxVal].question_count = TempQList[catIdxVal].question_count - 1

                _.map(TempQList, function (item, catIdx) {
                    _.remove(item.questions, function (ques, idx) {
                        return idx == idxVal && catIdx == catIdxVal
                    })
                })
                this.setState({ QuestionList: TempQList, delPosting: false })
                this.toggleActionPopup(QuestionID, idxVal)
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    updateQA = (e, cat_idx, idx) => {
        if (!_.isNull(e)) {
            let { QuestionList } = this.state
            let tempQL = QuestionList
            let value = e.target.value
            let name = e.target.name
            tempQL[cat_idx].questions[idx][name] = value
            tempQL[cat_idx].questions[idx].status = 1
            this.setState({ QuestionList: tempQL, updatePosting : false })
        }
    }
    
    updateQAInDb = () => {
        this.setState({ updatePosting : true })
        let { QuestionList, LanguageType } = this.state
        let saveArr = []   
        QuestionList.map((item, c_idx)=>(
            _.map(item.questions,(qa, q_idx)=>{
                if (_.isEmpty(qa.question) || _.isEmpty(qa.answer)) {
                    this.setState({ updatePosting: false })
                    notify.show("Please enter question and answer for question " + (q_idx + 1) + " or delete row for " + item.category_name + " category.", "error", 5000)
                    return false
                } 
                if (qa.status === 1)    
                {
                    saveArr.push(qa)
                }           
            })
        ))

        let params = {
            page_alias: "faq",
            questions : saveArr,
            language : LanguageType
        }
        // notify.show("ResponseJson.message", "success", 3000)
        // this.getQuestions()
        
        WSManager.Rest(NC.baseURL + NC.UPDATE_QUESTION_ANSWER, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 3000)
                // this.getQuestions()
            }
            this.setState({ updatePosting: false })
        }).catch(error => {
            this.setState({ updatePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })         
    }

    render() {
        let { CategoryOptions, CategoryType, languageOptions, LanguageType, AddQAList, submitPosting, ViewCateData, QuestionList, Message, ActionPopupOpen, updatePosting, delPosting } = this.state

        const ActionCallback = {
            posting: delPosting,
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.deleteQuestion,
        }

        return (
            <div className="about-us-box">
                <ActionRequestModal {...ActionCallback} />
                <div className="page-heading">FAQ</div>
                <div className="faq-bg-box">
                    <div className="faq-top-box bor-bottom">
                        <Row>
                            <Col md={3}>
                                <div className="au-title mb-1">Select Language</div>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    options={languageOptions}
                                    placeholder="Select Language"
                                    value={LanguageType}
                                    onChange={e => this.handleLangChange(e)}
                                />
                            </Col>
                            {/* <Col md={3}>
                                <div className="au-title mb-1">Title</div>
                                <Input
                                    type="text"
                                    name="Title"
                                // value={!_.isEmpty(CustomData) ? CustomData.email : ''}
                                // onChange={this.handleInput}
                                />
                            </Col> */}
                        </Row>
                    </div>
                    <div className="faq-top-box">
                        <Row>
                            <Col md={3}>
                                <div className="au-title mb-1">Select Category</div>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    options={CategoryOptions}
                                    placeholder="Select Category"
                                    value={CategoryType}
                                    onChange={e => this.handleCateChange(e)}
                                />
                            </Col>
                            <Col md={9}>
                                {
                                    _.map(AddQAList, (item, idx) => {
                                        return (
                                            <div className="mt-30">
                                                <Input
                                                    type="text"
                                                    name="question"
                                                    placeholder={"Ques " + (idx + 1)}
                                                    value={item.question}
                                                    onChange={e => this.handleInputChange(e, idx, 'question')}
                                                />
                                                <Input
                                                    type="textarea"
                                                    name="answer"
                                                    className="inp-answer"
                                                    placeholder="Answer"
                                                    value={item.answer}
                                                    onChange={e => this.handleInputChange(e, idx, 'answer')}
                                                />
                                                {(idx > 0) && <div
                                                    onClick={() => this.deleteQuestionRow(item.row_id)}
                                                    className="icon-cross"
                                                ></div>}
                                            </div>
                                        )
                                    })
                                }
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <div
                                    onClick={this.addNewQuestion}
                                    className="add-more-q"
                                >+ Add more question</div>
                            </Col>
                        </Row>
                        <Row className="text-center">
                            <Col md={12}>
                                <Button
                                    className="btn-secondary-outline"
                                    onClick={this.submitQuesAns}
                                    disabled={submitPosting}
                                >Submit</Button>
                            </Col>
                        </Row>
                    </div>
                </div>
                {
                    _.map(QuestionList, (item, cate_idx) => {
                        return (
                            <div key={cate_idx} className="q-list-box">
                                <Row>
                                    <Col md={12}>
                                        <div
                                            onClick={() => this.showCategory(cate_idx)}
                                            className="cate-box"
                                        >
                                            <div className="float-left">
                                                <div className="cate-name">{item.category_name}</div>
                                                <div className="cate-q-count">{item.question_count} Question</div>
                                            </div>
                                            <div className="float-right">
                                                <div className={`${ViewCateData !== cate_idx ? "arrow-down" : "arrow-up"}`}></div>
                                            </div>
                                        </div>
                                    </Col>
                                </Row>
                                <Row>
                                    <Col md={12}>
                                        <div
                                            className={`qa-list faq-bg-box clearfix ${ViewCateData !== cate_idx ? 'hide-cate' : ''}`}
                                        >
                                            {
                                                _.map(item.questions, (item, idx) => {
                                                    return (
                                                        <div className="mt-30">
                                                            <Input
                                                                type="text"
                                                                name="question"
                                                                placeholder={"Ques "}
                                                                value={item.question}
                                                                onChange={(e) => this.updateQA(e, cate_idx, idx)}
                                                            />
                                                            <Input
                                                                type="textarea"
                                                                name="answer"
                                                                className="inp-answer"
                                                                placeholder="Answer"
                                                                value={item.answer}
                                                                onChange={(e) => this.updateQA(e, cate_idx, idx)}
                                                            />
                                                            <div
                                                                onClick={() => this.toggleActionPopup(item.question_id, idx, cate_idx)}
                                                                className="icon-cross"
                                                            ></div>
                                                        </div>
                                                    )
                                                })
                                            }
                                            <div className="update-qa-box">
                                                <Button
                                                    disabled={updatePosting}
                                                    onClick={this.updateQAInDb}
                                                    className="btn-secondary-outline">Update</Button>
                                            </div>
                                        </div>
                                    </Col>
                                </Row>
                            </div>
                        )
                    })
                }
            </div>
        )
    }
}
export default FAQ