import React, { Component, Fragment } from "react";
import { Row, Col, Button, Input, CardBody, Card, Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import _ from 'lodash';
import * as NC from '../../helper/NetworkingConstants';
import WSManager from "../../helper/WSManager";
import HF, { _isEmpty, _isNull } from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import SelectDropdown from "../../components/SelectDropdown";
import { notify } from 'react-notify-toast';
import { QZ_add, QZ_update_question, QZ_check_quiz_exist } from '../../helper/WSCalling';
import queryString from 'query-string';
import Images from "../../components/images";
var AnsOptions = [
    { value: 1, label: 'Option 1' },
    { value: 2, label: 'Option 2' },
    { value: 3, label: 'Option 3' },
    { value: 4, label: 'Option 4' }
]
class CreateQuiz extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Today: new Date(),
            // SelectedDate: new Date(),
            SelectedAns: '',
            SelectVisibleQues: '',
            QuesOptions: [],
            PrizeType: 2,
            PrizeValue: '',
            EditQuesUid: '',
            InputArr: {
                // "scheduled_date": new Date(),
                "scheduled_date": null,
                "visible_questions": '0',
                "question_text": "",
                "question_image" : "",
                "options": [],
                "option1": '',
                "option2": '',
                "option3": '',
                "option4": '',
                "correct_ans": '',
                "prize_type": 2,
                "prize_value": '',
                "time_cap": '',
            },
            EditBtn: false,
            EditFlag: this.props.match.params.edit ? this.props.match.params.edit : false,
            QuesActiveTab: '',
            scheDateIstoday: false,
            todayVisibleCount: '0',
            addImageQuiz: false,
            fileUplode: '',
            isEdit:false,
            ImageName:'',
            isValid : false
        }
    }

    componentDidMount = () => {
        if (HF.allowQuiz() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.QuestOpt(11)
        if (this.state.EditFlag != '0') {
            this.setEditData()
        }
    }

    setEditData = () => {
        
        this.scrollTop()
        let st = this.props.location.state
        let { EditFlag } = this.state
        let quesdata = st ? st.ques_data : {}
        if (!_isEmpty(quesdata)) {
            this.setState({ QuesActiveTab: quesdata.active_tab })
        }
        if (!_isEmpty(quesdata) && EditFlag == '4') {
            this.setState({
                ImageName : '',
                fileUplode :'',
                InputArr: {
                    "scheduled_date": new Date(quesdata.scheduled_date),
                    "visible_questions": quesdata.visible_questions,
                    "question_text": '',
                    "options": [],
                    "option1": '',
                    "option2": '',
                    "option3": '',
                    "option4": '',
                    "correct_ans": '',
                    "prize_type": 2,
                    "prize_value": '',
                    "time_cap": '',
                }
            })
        }
        else if (!_isEmpty(quesdata) && (EditFlag == '1' || EditFlag == '2')) {
            var c_ans = quesdata.options.findIndex(x => x.is_correct === "1");
            this.setState({
                EditBtn: true,
                EditQuesUid: quesdata.question_uid,
                ImageName : quesdata.question_image,
                InputArr: {
                    "scheduled_date": this.state.EditFlag == '1' ? new Date(quesdata.scheduled_date) : null,
                    "visible_questions": this.state.EditFlag == '1' ? quesdata.visible_questions : null,
                    "question_text": quesdata.question_text,
                    "options": [],
                    "option1": quesdata.options[0] ? quesdata.options[0].option_text : '',
                    "option2": quesdata.options[1] ? quesdata.options[1].option_text : '',
                    "option3": quesdata.options[2] ? quesdata.options[2].option_text : '',
                    "option4": quesdata.options[3] ? quesdata.options[3].option_text : '',
                    "correct_ans": c_ans + 1,
                    "prize_type": quesdata.prize_type,
                    "prize_value": quesdata.prize_value,
                    "time_cap": quesdata.time_cap,
                }
            })
        }
        else {
            let flg = (this.state.EditFlag == '2') ? '1' : '0'
            notify.show('Please select question', 'error', 5000)
            this.props.history.push('/coins/quiz/questions?t=' + flg)
        }
    }

    QuestOpt = (num) => {
        let optArr = []
        for (let index = 1; index < num; index++) {
            optArr.push({ value: index, label: index })
        }
        this.setState({ QuesOptions: optArr })
    }

    handleDate = (date, dateType) => {
        let newArr = this.state.InputArr
        newArr[dateType] = date
        this.removeErrorFld('scheduled_date');

        let d = this.state.Today
        let date_flag = date.setHours(0, 0, 0, 0) == d.setHours(0, 0, 0, 0)

      

        this.setState({
            InputArr: newArr, EditBtn: false, scheDateIstoday: date_flag
        }, () => {
            this.checkQzExist()
        })
    }

    handleSelectChange = (value, name) => {
        if (!_isNull(value)) {
            let newArr = this.state.InputArr
            if (name === 'correct_ans' && ((value.value == 3 && _isEmpty(newArr.option3)) || (value.value == 4 && _isEmpty(newArr.option4)))) {
                notify.show("You have not provided this option", "error", 2000);
                return false;
            }
            if (name === 'correct_ans')
                this.removeErrorFld('correct_ans');
            if (name === 'visible_questions')
                this.removeErrorFld('visible_questions');

            newArr[name] = value.value
            this.setState({ InputArr: newArr, EditBtn: false })
        }
    }

    handleInputChange = (e) => {
        let tempArr = this.state.InputArr
        let inp_name = e.target.getAttribute("data-inp");
        let name = e.target.name;
        let value = e.target.value;

        if ((name == 'prize_value' || name == 'time_cap') && HF.isFloat(value)) {
            value = this.state.InputArr[name]
            let msg = inp_name + ' can not be decimal'
            notify.show(msg, 'error', 1500)
            return false
        }
        this.removeFromInp(name)
        tempArr[name] = value
        this.setState({ InputArr: tempArr, EditBtn: false }, () => {
            let flag = false
            let msg = ''
            if (name == 'prize_value' && (Number(this.state.InputArr[name]) < 1 || Number(this.state.InputArr[name])) > 99999) {
                msg = 'Prize value should be in the range of 1 to 99999'
                flag = true
            }
            else if (name == 'time_cap' && (Number(this.state.InputArr[name]) < 5 || Number(this.state.InputArr[name])) > 60) {
                msg = 'Timer (Sec) should be in the range of 5 to 60'
                flag = true
            }
            if (flag) {
                tempArr[name] = ''
                notify.show(msg, 'error', 2000)
                this.setState({ InputArr: tempArr })
                return false
            }
        });
    }

    handleOptionChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        let tempArr = this.state.InputArr
        tempArr[name] = value
        this.removeFromInp(name)
        this.setState({ InputArr: tempArr, EditBtn: false });
    }

    checkValidation = () => {
        let { InputArr,ImageName } = this.state
        const addImageQuestion = { "question_image": ImageName};
        const abcd = {...InputArr, ...addImageQuestion};
        this.removeErrorFld('scheduled_date');
        this.removeErrorFld('visible_questions');
        this.removeErrorFld('question_text');
       
       
        this.removeErrorFld('option1');
        this.removeErrorFld('option2');
        this.removeErrorFld('correct_ans');
        this.removeErrorFld('prize_value');
        this.removeErrorFld('time_cap');

        if (abcd.scheduled_date == null) {
            notify.show("Please select schedule date", "error", 2000);
            this.errorFld('scheduled_date');
            return false;
        }

        if (abcd.visible_questions == '') {
            notify.show("Please select questions a user can see", "error", 2000);
            this.errorFld('visible_questions');
            return false;
        }
      

        // else if (abcd.question_text == '' || abcd.question_text.length < 6 || abcd.question_text.length > 250) {
        //     notify.show("Please type your question in the range of 6 to 250", "error", 2000);
        //  this.errorFld('question_text');
        //     return false;
        // }

        else if (abcd.option1 == '' || abcd.option1.length < 1 || abcd.option1.length > 30) {
            notify.show("Option 1 is mandotory", "error", 2000);
            this.errorFld('option1');
            return false;
        }

        else if (abcd.option2 == '' || abcd.option2.length < 1 || abcd.option2.length > 30) {
            notify.show("Option 2 is mandotory", "error", 2000);
            this.errorFld('option2');
            return false;
        }

        else if (abcd.correct_ans == '') {
            notify.show("Please select correct answer", "error", 2000);
            this.errorFld('correct_ans');
            return false;
        }

        else if (abcd.prize_value == '' || abcd.prize_value < 1 || abcd.prize_value > 99999) {
            notify.show("Prize value should be in the range of 1 to 99999", "error", 2000);
            this.errorFld('prize_value');
            return false;
        }

        else if (abcd.time_cap == '' || abcd.time_cap < 5 || abcd.time_cap > 60) {
            notify.show("Timer (Sec) should be in the range of 5 to 60", "error", 2000);
            this.errorFld('time_cap');
            return false;
        }
           if (abcd.question_image == '') {
             if (abcd.question_text == '' || abcd.question_text.length < 6 || abcd.question_text.length > 250) {
            notify.show("Please type your question in the range of 6 to 250", "error", 2000);
            this.errorFld('question_text');
            return false;
        }
        return true;
        }
        else {
            return true;
        }
    }

    saveAndNext = () => {
        
        let { InputArr, EditQuesUid, EditFlag,ImageName } = this.state
        const addImageQuestion = { "question_image": ImageName};

        this.setState({ Posting: true })
        var np = InputArr

        if (!this.checkValidation()) {
            return false
        }

        np.scheduled_date = np.scheduled_date ? HF.getFormatedDateTime(np.scheduled_date, 'YYYY-MM-DD') : '';

        if (_isEmpty(np.options)) {
            np.options.push(
                {
                    "text": np.option1,
                    "is_correct": np.correct_ans == 1 ? 1 : 0
                },
                {
                    "text": np.option2,
                    "is_correct": np.correct_ans == 2 ? 1 : 0
                },
                {
                    "text": np.option3,
                    "is_correct": np.correct_ans == 3 ? 1 : 0
                },
                {
                    "text": np.option4,
                    "is_correct": np.correct_ans == 4 ? 1 : 0
                }
            )
        }
        const param = {...np, ...addImageQuestion};
        let params = param
        delete params.option1
        delete params.option2
        delete params.option3
        delete params.option4
        let URL = QZ_add
        if (EditFlag == '1') {
            URL = QZ_update_question
            params.question_uid = EditQuesUid
        }
        URL(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                if (!_isEmpty(EditQuesUid)) {
                    this.props.history.push('/coins/quiz/questions?t=0')
                } else {
                    this.setState({
                        ImageName: '',
                        fileUplode :"",
                        InputArr: {
                            "scheduled_date": np.scheduled_date,
                            "visible_questions": np.visible_questions,
                            "question_text": "",
                            "options": [],
                            "option1": '',
                            "option2": '',
                            "option3": '',
                            "option4": '',
                            "correct_ans": '',
                            "prize_type": 2,
                            "prize_value": '',
                            "time_cap": '',
                        }
                    })
                }
                this.scrollTop()
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ Posting: false })
        }).catch(error => {
            this.setState({ Posting: false })
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    Reset = () => {
        this.scrollTop()
        this.setState({
            ImageName :'',
            fileUplode :'',
            isValid : false,
            InputArr: {
                "scheduled_date": '',
                "visible_questions": '',
                "question_text": "",
                "options": [],
                "option1": '',
                "option2": '',
                "option3": '',
                "option4": '',
                "correct_ans": '',
                "prize_type": 2,
                "prize_value": '',
                "time_cap": '',
            },
            scheDateIstoday: false,
        })
    }

    checkQzExist = () => {
        let { InputArr } = this.state
        let params = {
            "scheduled_date": InputArr.scheduled_date ? HF.getFormatedDateTime(InputArr.scheduled_date, 'YYYY-MM-DD') : ''
        }
        QZ_check_quiz_exist(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                let newArr = this.state.InputArr
                newArr['visible_questions'] = Response.data ? Response.data.visible_questions : ''
                this.setState({
                    InputArr: newArr,
                    todayVisibleCount: Response.data ? Response.data.visible_questions : '0',
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    errorFld = (id) => {
        document.getElementById(id).classList.add('error-bdr');
        document.getElementById(id).focus();
    }
    removeErrorFld = (id) => {
        document.getElementById(id).classList.remove('error-bdr');
    }
    removeFromInp = (name) => {
        if (name == 'question_text')
            this.removeErrorFld('question_text');
        if (name == 'option1')
            this.removeErrorFld('option1');
        if (name == 'option2')
            this.removeErrorFld('option2');
        if (name == 'prize_value')
            this.removeErrorFld('prize_value');
        if (name == 'time_cap')
            this.removeErrorFld('time_cap');
    }

    scrollTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    addImageModalOpen = (item, isEdit) => {
        this.setState({
            addImageQuiz: true
        })
    }
    addImageModalClose = (item) => {
        if(item =="close"){
            this.setState({
                addImageQuiz: false,
                ImageName : '',
                fileUplode : '',
                isValid : false
            })
        }else{
        this.setState({
            addImageQuiz: false
        })
    }
    }
    
    onChangeImage = (event) => {
        this.setState({
            fileUplode: event.target.files[0].name,
        }, function () {
            this.validateFields()
        });

        const file = event.target.files[0];
        if (!file) {
            return;
        }

        var data = new FormData();
        data.append("file_name", file);
        data.append("type", "quiz");
        WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD_WHATSNEW_IMG, data)
            .then(responseJson => {
                notify.show(responseJson.message, "success", 3000)
                this.setState({
                    fileUplode: responseJson.data.image_url,
                    ImageName: responseJson.data.image_name,
                });
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }
    validateFields = () => {
        const { fileUplode,ImageName,isEdit,isValid } = this.state
        console.log("first",fileUplode)
        console.log("second",ImageName)
        if(fileUplode != ''  && ImageName != '' ){
            this.setState({
                isValid: true
            })
        }
        // this.setState({
        //     isValid:(isEdit? ImageName : fileUplode != '')
        // })
    }
  
    removeQuizImg = (image_name) =>{
        if(image_name == ''){
            this.setState({addImageQuiz : false})
        }else{
        WSManager.Rest(NC.baseURL + NC.REMOVE_MEDIA, {
            "file_name": image_name, "type": "quiz"
        }).then(({ response_code, message }) => {
            if (response_code == NC.successCode) {
                this.setState({ImageName : '',fileUplode :'',isValid : false,addImageQuiz : false})
                notify.show(message, "success", 3000)
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
}
    

    render() {
        let { Today, QuesOptions, PrizeType, InputArr, EditQuesUid, EditBtn, EditFlag, QuesActiveTab, scheDateIstoday, todayVisibleCount, addImageQuiz,ImageName,fileUplode } = this.state
        const date_props = {
            disabled_date: (EditFlag == '1' || EditFlag == '4') ? true : false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'form-control',
            year_dropdown: true,
            month_dropdown: true,
            min_date: Today,
            max_date: null,
            sel_date: InputArr.scheduled_date ? new Date(InputArr.scheduled_date) : null,
            date_key: 'scheduled_date',
            place_holder: 'Select Date',
        }

        const Select_Props = {
            select_id: '',
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: AnsOptions,
            place_holder: "Select Correct Answer",
            selected_value: InputArr.correct_ans,
            select_name: 'correct_ans',
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        const Select_vis_Ques = {
            // is_disabled: ((EditFlag == '4' && QuesActiveTab == '2') || ((EditFlag == '0' || EditFlag == '2') && InputArr.visible_questions != '0' && (scheDateIstoday))) ? true : false,

            is_disabled: (
                (EditFlag == '4' && QuesActiveTab == '2')
                ||
                // (((EditFlag == '0' && todayVisibleCount != '0') || (EditFlag == '2' && InputArr.visible_questions != '0')) 
                (((EditFlag == '0' || EditFlag == '2') && (todayVisibleCount != '0'))
                    &&
                    (scheDateIstoday))
            ) ? true : false,

            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: QuesOptions,
            place_holder: "Select",
            selected_value: InputArr.visible_questions,
            select_name: 'visible_questions',
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        return (
            <div className="create-quiz">
                <Row>
                    <Col md={12}>
                        <h1 className="h1-cls">Create Quiz</h1>
                    </Col>
                </Row>
                <Card>
                    <Row>
                        <Col md={4} id="scheduled_date">
                            <label htmlFor="date">When will this be aired?<span className="asterrisk">*</span></label>
                            <SelectDate DateProps={date_props} />
                        </Col>
                        <Col md={4} id="visible_questions">
                            <label htmlFor="questions_no">How many questions a user can see?<span className="asterrisk">*</span></label>
                            <SelectDropdown SelectProps={Select_vis_Ques} />
                        </Col>
                    </Row>
                    <hr />
                    <Row>
                        <Col md={4}>
                            <div className="quiz-new-changes-view">
                                <label htmlFor="Question">Question<span className="asterrisk">*</span></label>
                           

                               {(ImageName && fileUplode ) ? 
                               <div className="add-image-view"> <span className="icon-view"><i className="icon-close" onClick={() => this.removeQuizImg(ImageName)} /> </span> <span className="text-view">{ImageName}</span> </div>
                               : 
                                <div className="add-image-view" onClick={() => this.addImageModalOpen()}><span className="icon-view"><i className="icon-add" /></span> <span className="text-view">Add Image </span></div>
        }
                            </div>

                            <Input
                                maxLength={250}
                                type="textarea"
                                id="question_text"
                                name="question_text"
                                value={InputArr.question_text}
                                placeholder="Type your question"
                                data-inp='Question'
                                onChange={this.handleInputChange}
                            />
                        </Col>
                        <Col md={8}>
                            <Row>
                                <Col md={6}>
                                    <label htmlFor="option_1">Option 1<span className="asterrisk">*</span></label>
                                    <Input
                                        maxLength={30}
                                        type="text"
                                        id="option1"
                                        name="option1"
                                        value={InputArr.option1}
                                        placeholder="Enter Option 1"
                                        data-inp='Option 1'
                                        onChange={this.handleOptionChange}
                                    />
                                </Col>
                                <Col md={6}>
                                    <label htmlFor="option_2">Option 2<span className="asterrisk">*</span></label>
                                    <Input
                                        maxLength={30}
                                        type="text"
                                        id="option2"
                                        name="option2"
                                        value={InputArr.option2}
                                        placeholder="Enter Option 2"
                                        data-inp='Option 2'
                                        onChange={this.handleOptionChange}
                                    />
                                </Col>
                            </Row>
                            <Row className="mt-30">
                                <Col md={6}>
                                    <label htmlFor="option_3">Option 3</label>
                                    <Input
                                        maxLength={30}
                                        type="text"
                                        id="option3"
                                        name="option3"
                                        value={InputArr.option3}
                                        placeholder="Enter Option 3"
                                        onChange={this.handleOptionChange}
                                    />
                                </Col>
                                <Col md={6}>
                                    <label htmlFor="option_4">Option 4</label>
                                    <Input
                                        maxLength={30}
                                        type="text"
                                        id="option4"
                                        name="option4"
                                        value={InputArr.option4}
                                        placeholder="Enter Option 4"
                                        onChange={this.handleOptionChange}
                                    />
                                </Col>
                            </Row>
                        </Col>
                    </Row>
                    <hr />
                    <Row>
                        <Col md={4} id="correct_ans">
                            <label htmlFor="answer">Correct Answer<span className="asterrisk">*</span></label>
                            <SelectDropdown SelectProps={Select_Props} />
                        </Col>
                        <Col md={4}>
                            <label htmlFor="prize_type">Prize Type<span className="asterrisk">*</span></label>
                            <div className="cq-pt-list">
                                <div className={`${PrizeType == '2' ? 'active' : ''}`}>Coins</div>
                                <div className={`disable ${PrizeType == '3' ? 'active' : ''}`}>Bonus</div>
                                <div className={`disable ${PrizeType == '1' ? 'active' : ''}`}>Real Money</div>
                            </div>
                        </Col>
                        <Col md={4}>
                            <label htmlFor="prize_value">Prize Value<span className="asterrisk">*</span></label>
                            <Input
                                type="number"
                                id="prize_value"
                                name="prize_value"
                                value={InputArr.prize_value}
                                data-inp='Prize value'
                                placeholder="Enter Prize Value"
                                onChange={this.handleInputChange}
                            />
                        </Col>
                        <Col md={4} className="mt-30">
                            <label htmlFor="timer">Timer (Sec)<span className="asterrisk">*</span></label>
                            <Input
                                id="time_cap"
                                type="number"
                                name="time_cap"
                                value={InputArr.time_cap}
                                placeholder="Enter Time"
                                data-inp='Timer (Sec)'
                                onChange={this.handleInputChange}
                            />
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="cq-btns">
                                <Button
                                    // disabled={EditBtn}
                                    className="btn-secondary"
                                    onClick={this.saveAndNext}
                                >
                                    {EditFlag == '1' ? 'Update' : 'Save and Next'}
                                </Button>

                                {
                                    !EditQuesUid &&
                                    <Button
                                        className="btn-secondary-outline"
                                        onClick={this.Reset}
                                    >
                                        Reset
                                    </Button>
                                }
                                <Button
                                    className="btn-secondary-outline"
                                    onClick={() => this.props.history.push('/coins/quiz/questions?t=2')}
                                >Exit</Button>
                            </div>
                        </Col>
                    </Row>
                </Card>

                <Modal
                    isOpen={addImageQuiz}
                    toggle={() => this.addImageModalClose()}
                    className="add-whats-new-con add-quiz-modal-view"
                >

                    <ModalHeader className="add-whats-new">
                        Add Image
                        <i className='icon-close'
                        onClick={() => this.removeQuizImg(ImageName)}
                        //  onClick={() => this.addImageModalClose("close")} 
                         />
                    </ModalHeader>
                    <ModalBody >
                        <Row>
                            <Col md={12}>
                                <label className='title-lable'>Image</label>
                                <div className="sf-image">
                                    <Input
                                        type="file"
                                        name='banner_image'
                                    onChange={this.onChangeImage}
                                    />
                                        {this.state.fileUplode 
                                     ?
                                        <div>
                                            <img className="img-cover preview-view-img"
                                            src={(this.state.fileUplode ) ?  this.state.fileUplode : Images.no_image} />
                                        </div> :
                                        <div className="sf-icon-txt" >
                                            {ImageName ? 
                                              <img className="def-addphoto  preview-view-img"  
                                              src={(ImageName) ? NC.S3 + NC.QUIZ_IMG + ImageName : Images.IMAGE_GALLARY}
                                               alt="" />
                                            :
                                            <>
                                            <div className="for-size-text">Add or drag and drop the image here.(Size 670*376)</div>
                                            <img className="def-addphoto" src={Images.IMAGE_GALLARY} alt="" />
                                            </>
                                            }
                                           

                                        </div>
                                    }
                                </div>



                            </Col></Row>



                    </ModalBody>
                    <ModalFooter className="request-footer request-footer-view">
                        <Row className="text-center mt-30">
                            <Col md={12}>
                                <Button className='btn-secondary'
                                disabled={!this.state.isValid}
                                onClick={() => this.addImageModalClose()}
                                >
                                    Save
                                </Button>
                            </Col>
                        </Row>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
}
export default CreateQuiz

