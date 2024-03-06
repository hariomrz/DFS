import React, { Component } from 'react';
import { Col, Row, Button, Input, Tooltip } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import _, { isUndefined } from 'lodash';
import LS from 'local-storage';
import { notify } from 'react-notify-toast';
import HF, { _times, _Map, _isUndefined, _isEmpty, _cloneDeep, _isNull } from "../../helper/HelperFunction";
import Images from '../../components/images';
import { PT_TIE_BREAKER } from '../../helper/Message';
export default class PFAddMatch extends Component {
    constructor(props) {
        super(props);
        let filter = {
            current_page: 1,
            items_perpage: 50,
            type: 1
        }
        this.state = {
            league_id: '',
            season_id: '',
            sports_id: '',
            season_game_uid: '',
            away_flag: '',
            away_id: '',
            home_id: '',
            home_flag: '',
            correct: '',
            // question: '',
            wrong: '',
            league_name: '',
            match: '',
            scheduled_date: '',
            modified_date: '',
            question: [],
            queFilled: 0,
            draftQuestion: false,
            tieBreakerShow: false,
            checkedTie: false,
            tieBreakerQue: {},
            startRange: '',
            endRange: '',
            isStatsValid: true,
            showQueInfo: false,
            checkSDPosting: true,
            checkPMPosting: true
        };
    }


    componentDidMount = () => {
        this.setLocaltionProps();
        //    this.getLocalProps();

        // this.GetContestTemplateMasterData();

    }
    getLocalProps = () => {
        let matchDetails = LS.get('matchDetails')
        this.setState({
            league_id: matchDetails[0].league_id,
            season_id: matchDetails[0].season_id,
            sports_id: matchDetails[0].sports_id,
        })
    }
    // get props from local
    setLocaltionProps = () => {
        if (this.props && this.props.location && this.props.location.state) {
            const { sports_id, season_id, season_game_uid, away_flag, away_id, home_id, home_flag, league_id, correct, question, league_name, match, scheduled_date, modified_date, wrong } = this.props.location.state;
            this.setState({
                league_id: league_id,
                season_id: season_id,
                sports_id: sports_id,
                season_game_uid: season_game_uid,
                away_flag: away_flag,
                away_id: away_id,
                home_id: home_id,
                home_flag: home_flag,
                correct: correct,
                questionCount: question,
                wrong: wrong,
                league_name: league_name,
                match: match,
                scheduled_date: scheduled_date,
                modified_date: modified_date,
            }, () => { this.questionList() })
        }
        else {
            let matchDetails = JSON.parse(localStorage.getItem("matchDetails"));
            this.setState({
                league_id: matchDetails[0].league_id,
                season_id: matchDetails[0].season_id,
                season_game_uid: matchDetails[0].season_game_uid,
                away_flag: matchDetails[0].away_flag,
                away_id: matchDetails[0].away_id,
                home_id: matchDetails[0].home_id,
                home_flag: matchDetails[0].home_flag,
                correct: matchDetails[0].correct,
                questionCount: matchDetails[0].question,
                wrong: matchDetails[0].wrong,
                league_name: matchDetails[0].league_name,
                match: matchDetails[0].match,
                scheduled_date: matchDetails[0].scheduled_date,
                modified_date: matchDetails[0].modified_date,
            }, () => { this.questionList() })

        }
    }
    questionList = () => {
        let questionLenght = this.state.questionCount;
        let lsQList = LS.get('queList') || []
        lsQList = lsQList.question_info || []
        let tieBreaker = LS.get('queList').tie_breaker.tie_breaker_question || {}
        tieBreaker = this.callJsonParser(tieBreaker)
        if (lsQList.length > 0) {
            let indents = [];
            for (let i = 0; i < questionLenght; i++) {
                let OptionImg = lsQList[i] && lsQList[i].option_images ? this.callJsonParser(lsQList[i].option_images) : {}
                let OptionStats = lsQList[i] && lsQList[i].option_stats ? this.callJsonParser(lsQList[i].option_stats) : {}

                indents.push({
                    'pick_id': lsQList[i] && lsQList[i].pick_id ? lsQList[i].pick_id : '',
                    'qno': i + 1,
                    'name': lsQList[i] && lsQList[i].name ? lsQList[i].name : '',
                    'details': lsQList[i] && lsQList[i].details ? lsQList[i].details : '',
                    'option_1': lsQList[i] && lsQList[i].option_1 ? lsQList[i].option_1 : '',
                    'option_2': lsQList[i] && lsQList[i].option_2 ? lsQList[i].option_2 : '',
                    'option_3': lsQList[i] && lsQList[i].option_3 ? lsQList[i].option_3 : '',
                    'option_4': lsQList[i] && lsQList[i].option_4 ? lsQList[i].option_4 : '',
                    'selQueType': OptionImg && OptionImg.option_1 ? 2 : 1, // 2 for image type question and 1 for text type
                    'option_images': OptionImg && OptionImg.option_1 ? OptionImg : {},
                    'option_stats': OptionStats && OptionStats.option_1 ? OptionStats : {},
                    'is_stats': OptionStats && OptionStats.option_1 ? 1 : 0,
                    'stats_text': lsQList[i] && lsQList[i].stats_text ? lsQList[i].stats_text : '',
                });
            }
            this.setState({
                question: indents,
                draftQuestion: true,
                checkedTie: tieBreaker && tieBreaker.question ? true : false,
                tieBreakerQue: tieBreaker && tieBreaker.question ? tieBreaker : {},
            }, () => {
                this.validateQueList()
            })
        }
        else {
            let indents = [];
            for (let i = lsQList.length; i < questionLenght; i++) {
                indents.push({
                    'qno': i + 1,
                    'name': '',
                    'details': '',
                    'option_1': '',
                    'option_2': '',
                    'option_3': '',
                    'option_4': '',
                    'selQueType': 1,
                    'option_images': {},
                    'option_stats': {},
                    'stats_text' : ''
                });
            }
            this.setState({
                question: indents
            }, () => {
                this.validateQueList()
            })
        }
    }
    handleInputChange = (event, item, idx) => {
        let name = event.target.name
        let value = event.target.value
        let tmpArray = this.state.question
        tmpArray[idx][name] = value
        this.setState({ question: tmpArray }, () => {
            // LS.set('queList',tmpArray)
            this.validateQueList()
        })
    }

    callJsonParser = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    componentWillUnmount = () => {
    }

    validateQueList = () => {
        const { tieBreakerQue, checkedTie } = this.state

        let isTieBreakerValid = checkedTie ? (tieBreakerQue.question && tieBreakerQue.question != '' && tieBreakerQue.question.length >= 4 && tieBreakerQue.end && tieBreakerQue.end != '' && tieBreakerQue.start && tieBreakerQue.start != '' && (parseInt(tieBreakerQue.end) > parseInt(tieBreakerQue.start)) ? true : false) : true
        let count = 0;

        for (let obj of this.state.question) {
            if (obj.selQueType == 2) {
                if (
                    obj.name != '' && obj.name.length >= 4 &&
                    (obj.details == '' || (obj.details != '' && obj.details.length >= 4)) &&
                    obj.option_1 != '' && obj.option_1.length >= 1 &&
                    obj.option_2 != '' && obj.option_2.length >= 1 &&
                    !isUndefined(obj.option_images.option_1) && obj.option_images.option_1 != '' &&
                    !isUndefined(obj.option_images.option_2) && obj.option_images.option_2 != '' &&
                    isTieBreakerValid &&
                    ((obj.option_3 == '' && isUndefined(obj.option_images.option_3)) || (obj.option_3 != '' && obj.option_images.option_3 && obj.option_images.option_3 != '')) &&
                    (isUndefined(obj.is_stats) || obj.is_stats != 1 || (obj.is_stats == 1 && obj.option_stats.option_1 != '' && parseInt(obj.option_stats.option_1) > 0 &&
                        (isUndefined(obj.option_stats.option_2) || (obj.option_stats.option_2 != '' && parseInt(obj.option_stats.option_2) > 0)) &&
                        (isUndefined(obj.option_stats.option_3) || (obj.option_stats.option_3 != '' && parseInt(obj.option_stats.option_3) > 0))
                    ))
                ) {
                    count = count + 1
                }
            }
            else {
                if (
                    obj.name != '' && obj.name.length >= 4 &&
                    (obj.details == '' || (obj.details != '' && obj.details.length >= 4)) &&
                    obj.option_1 != '' && obj.option_1.length >= 1 &&
                    obj.option_2 != '' && obj.option_2.length >= 1 &&
                    isTieBreakerValid
                ) {
                    count = count + 1
                }
            }
        }
        this.setState({
            queFilled: count,
            draftQuestion: count == 0 ? false : this.state.draftQuestion
        })
    }

    saveDraft = () => {
        let tmpArray = []
        this.setState({
            checkSDPosting: false
        })
        for (let obj of this.state.question) {
            if (obj.name != '' || obj.option_1 != '' || obj.option_2 != '') {
                tmpArray.push(obj)
            }
        }
        let params = {
            question: tmpArray,
            season_id: this.state.season_id,
            tie_breaker_question: this.state.tieBreakerQue
        }
        WSManager.Rest(NC.baseURL + NC.PF_SAVE_DRAFT, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, "success", 5000);
                this.props.history.push({ pathname: '/picksfantasy/fixture/', state: { isFrom: '2', activeTab: '2' } })
            } else {
                notify.show(Response.message, 'error', 5000)
            }
            this.setState({
                checkSDPosting: true
            })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    publishFixture = () => {
        let { season_id, league_id } = this.state
        let tmpArray = []
        this.setState({
            checkPMPosting: false
        })
        for (let obj of this.state.question) {
            if (obj.name != '' || obj.option_1 != '' || obj.option_2 != '') {
                tmpArray.push(obj)
            }
        }
        let params = {
            question: this.state.question,
            season_id: this.state.season_id,
            tie_breaker_question: this.state.tieBreakerQue
        }
        WSManager.Rest(NC.baseURL + NC.PF_PUBLISH_FIXTURE, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.props.history.push({ pathname: '/picksfantasy/createtemplatecontest/' + league_id + '/' + season_id })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({
                checkPMPosting: true
            })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    toggleTieBreaker = () => {
        this.setState({ tieBreakerShow: !this.state.tieBreakerShow }, () => { this.validateQueList() })
    }

    handleTieBreaker = () => {
        this.setState({
            checkedTie: !this.state.checkedTie
        }, () => {
            // this.checkValid()
            this.setState({
                tieBreakerQue: this.state.checkedTie ? {
                    end: "",
                    question: "",
                    start: ""
                } : ''
            })
            this.validateQueList()
        })
    }

    handleFieldVal = (e) => {
        if (e) {
            let name = e.target.name
            let value = e.target.value
            if (name == 'startRange' || name == 'endRange') {
                value = value.replace(/[^0-9]/g, '');
                let tieBreakerQue = this.state.tieBreakerQue
                // tieBreakerQue[name == 'startRange' ? 'start' : 'end']= value
                if (name == 'startRange') {
                    if (999999 >= value) {
                        tieBreakerQue['start'] = value
                    }
                }
                else {
                    if (999999 >= value) {
                        tieBreakerQue['end'] = value
                    }
                }
                this.setState({ tieBreakerQue: tieBreakerQue }, () => {
                    if ((name == 'startRange' || name == 'endRange') && tieBreakerQue.end !== '') {
                        if (Number(tieBreakerQue.end) <= Number(tieBreakerQue.start)) {
                            notify.show("Min and Max range should not be same and min should be less than max ", "error", 3000);
                        }

                    }
                    this.validateQueList()
                })
            }
            else {
                let tieBreakerQue = this.state.tieBreakerQue
                tieBreakerQue['question'] = value
                this.setState({ tieBreakerQue: tieBreakerQue }, () =>
                    this.validateQueList()
                )
            }

            if (name == 'endRange' && value > 999999) {

                notify.show("End range should not be greater than 999999", "error", 3000);
            }
            // this.checkValid()
        }
    }

    checkValid = () => {
        if (!this.state.checkedTie) {
            return this.state.queFilled == 0 && !this.state.draftQuestion
        }
        else {
            return this.state.queFilled == 0 && !this.state.draftQuestion && (this.state.endRange == '' || this.state.startRange == '' || this.state.tieBreakerQue == '')
        }
    }

    handleQueTypeSel = (e, indx) => {
        let UpdateQue = this.state.question
        UpdateQue[indx]['selQueType'] = e.target.value
        UpdateQue[indx]['option_images'] = {}
        UpdateQue[indx]['option_stats'] = {}
        UpdateQue[indx]['option_1'] = ''
        UpdateQue[indx]['option_2'] = ''
        UpdateQue[indx]['option_3'] = ''
        UpdateQue[indx]['option_4'] = ''
        UpdateQue[indx]['stats_text'] = ''
        UpdateQue[indx]['is_stats'] = 0
        
        this.setState({
            question: UpdateQue
        }, () => {

            this.validateQueList()
        })
    }

    onChangeImage = (event, indx, subIdx) => {
        // this.setState({ UpLogoPosting: true });
        let List = this.state.question
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("file_name", file);
        data.append("type", "option_image");
        WSManager.multipartPost(NC.baseURL + NC.PF_DO_UPLOAD_LOGO, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        uploadedImgName: Response.data.image_name,
                        uploadedImgUrl: Response.data.image_url,
                    }, () => {
                        List[indx].option_images[subIdx] = this.state.uploadedImgName
                        this.setState({
                            question: List
                        }, () => {
                            this.validateQueList()
                        })
                    })
                }
                // this.setState({ UpLogoPosting: false })
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    removeApiCall = (optImgValue, que, opt, idx) => {
        let quesTmp = this.state.question
        quesTmp[idx].option_images[opt] = ''
        let params = {
            type: 'option_image',
            file_name: optImgValue
        }
        WSManager.Rest(NC.baseURL + NC.PF_REMOVE_MEDIA, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    removeImg: false,
                    question: quesTmp
                }, () => {
                    this.validateQueList()
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    renderImageblock = (que, idx, opt) => {
        let optImgValue = que.option_images && que['option_images'][opt] ? que['option_images'][opt] : ''
        return (<div className={`img-block ${!_.isEmpty(optImgValue) && 'pt-2'}`}>
            {!_.isEmpty(optImgValue) ?
                <div className='upload-opt-img'>
                    <img className="img-cover" src={NC.S3 + NC.PICK_FANTASY + optImgValue} />
                    <div
                        onClick={() => this.removeApiCall(optImgValue, que, opt, idx)}
                        className="dfs-remove-img">
                        <i className="icon-close"></i>
                    </div>
                </div>
                :
                <>
                    <Input
                        type="file"
                        name='UploadLogoName'
                        id="UploadLogoName"
                        className="img-up-ip"
                        onChange={(e) => this.onChangeImage(e, idx, opt)}
                    />

                    <div className="dfs-upload" onChange={(e) => this.onChangeImage(e, idx, opt)}>
                        <i className="icon-camera"></i>
                        <div className="dfs-banner-sz">
                            Please select image or drag here (200 X 200)
                        </div>
                    </div>
                </>
            }
        </div>)
    }

    handleStats = (e, idx, isfor) => {
        let value = e.target.value;
        value = value.replace(/[^0-9]/g, '');
        let List = this.state.question
        if (value != '' && (value > 9999 || value == 0)) {
            notify.show('Stats value should be between 1 to 9999', "error", 5000);
        }
        else {

            List[idx]['option_stats'][isfor] = value
            this.setState({
                question: List
            }, () => {
                this.validateQueList()
            })
        }
    }
    // ghjkloiuytrfghjkmnbvcdxszaqwer
    handleImgInputChange = (event, item, idx) => {
        let name = event.target.name
        let value = event.target.value
        let tmpArray = this.state.question
        if (value.length > 30) {
            notify.show('Option length should not be greater than 30 ', "error", 5000);
        }
        else {
            tmpArray[idx][name] = value
            this.setState({ question: tmpArray }, () => {
                // LS.set('queList',tmpArray)
                this.validateQueList()
            })
        }
    }

    handleStatsChecked = (value, idx) => {
        let QList = this.state.question
        QList[idx]['is_stats'] = value == 1 ? 0 : 1
        if (QList[idx]['is_stats'] == 0) {
            QList[idx]['option_stats'] = {}
        }
        this.setState({
            question: QList
        }, () => {
            this.validateQueList()
        })
    }
    toggleQueInfo = () => {
        this.setState({ showQueInfo: !this.state.showQueInfo })
    }

    render() {
        let { home_flag, away_flag, match, scheduled_date, league_name, question, questionCount, queFilled, draftQuestion, tieBreakerShow, tieBreakerQue, showQueInfo, checkSDPosting, checkPMPosting } = this.state
        return (
            <>
                <Row>
                    <Col sm={12}>
                        <div className='quesFlex'>
                            <div className='singleFixture'>
                                <div>
                                    <img className="matchLogo" src={NC.S3 + NC.FLAG + home_flag}></img>
                                </div>
                                <div className='matchDetails'>
                                    <span className='fixture-name'>{match ? match : 'TBA VS TBA'}</span>
                                    <span className='fixture-time'>{scheduled_date &&
                                        // WSManager.getUtcToLocalFormat(scheduled_date, 'D-MMM-YYYY hh:mm A')
                                        HF.getFormatedDateTime(scheduled_date, 'D-MMM-YYYY hh:mm A')
                                    }</span>
                                    <span className='fixture-title'>{league_name && league_name}</span>
                                </div>
                                <div>
                                    <img className="matchLogo" src={NC.S3 + NC.FLAG + away_flag}></img>
                                </div>
                            </div>
                            <span className='back-to-fixture' onClick={() => {
                                this.props.history.push({
                                    pathname: '/picksfantasy/fixture'
                                })
                            }}> {'<'} Back to fixture</span>
                        </div>
                    </Col>
                </Row>
                <Row>
                    <Col sm={12}>
                        <div className='teamSearch'>
                            <h2 className='set-picks'>Set Pick</h2>
                            <div className="progress-view-wrapper">
                                <img src={Images.SALARY_REVIEW_BAR} alt="" />
                                <div className="progress-text">
                                    <span>Create Picks</span> <span>Select Contest</span>
                                </div>
                            </div>
                        </div>
                    </Col>
                    <Col md={4}>

                    </Col>
                </Row>
                <hr />

                {
                    question && question.length > 0 && _Map(question, (que, idx) => {
                        return (
                            <Row>
                                <Col sm={12} >
                                    <div className='questions'>
                                        <div className='questionBox'>
                                            <label className="filter-label">Question {que.qno} </label>
                                            <Input
                                                id="exampleText"
                                                type="textarea"
                                                placeholder='Write a Question…'
                                                rows={5}
                                                cols={5}
                                                name="name"
                                                value={que.name}
                                                maxLength={250}
                                                onChange={(e) => this.handleInputChange(e, que, idx)}
                                            />
                                            {
                                                que.name && que.name.length < 4 &&
                                                <div className="inpt-info-txt">
                                                    Minimum Question size should be 4 characters
                                                </div>
                                            }

                                            <div className='infBox mt-1'>
                                                <label className="filter-label">Info </label>
                                                <Input
                                                    id="details"
                                                    name="details"
                                                    type="textarea"
                                                    placeholder='Write a Info…'
                                                    maxLength={250}
                                                    rows={5}
                                                    cols={5}
                                                    value={que.details}
                                                    onChange={(e) => this.handleInputChange(e, que, idx)}
                                                />
                                            </div>
                                            {
                                                que.details && que.details.length < 4 &&
                                                <div className="inpt-info-txt">
                                                    Minimum Info size should be 4 characters
                                                </div>
                                            }
                                        </div>
                                        <div className='Options'>
                                            <div className="selec-opt-type">
                                                <div className="form-group gray-form-group">
                                                    {
                                                        que.selQueType == 2 &&
                                                        <i className="que-info-i icon-info-border ml-2  cursor-pointer" id="queInfo">
                                                            <Tooltip placement="right" isOpen={showQueInfo} target="queInfo" toggle={this.toggleQueInfo}>
                                                                Guidelines for clear display: First Name - Max 11 Characters, Middle and/or Last Name - Max 18 characters
                                                            </Tooltip>
                                                        </i>
                                                    }

                                                    <label htmlFor="prize_pool" className="fixturevs">Answer type</label>
                                                    <div className="input-box radio-input-box p-0 pt-2">
                                                        <ul className="coupons-option-list">
                                                            <li className="coupons-option-item">
                                                                <div className="custom-radio">
                                                                    <input
                                                                        type="radio"
                                                                        className="custom-control-input"
                                                                        id={"is_text" + idx}
                                                                        name={"is_text" + idx}
                                                                        value={1}
                                                                        checked={que.selQueType == 1}
                                                                        onChange={(e) => this.handleQueTypeSel(e, idx)}
                                                                    />
                                                                    <label className="custom-control-label" htmlFor={"is_text" + idx}>
                                                                        <span className="input-text">Text</span>
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li className="coupons-option-item">
                                                                <div className="custom-radio">
                                                                    <input
                                                                        type="radio"
                                                                        className="custom-control-input"
                                                                        id={"is_img" + idx}
                                                                        name={"is_img" + idx}
                                                                        value={2}
                                                                        checked={que.selQueType == 2}
                                                                        onChange={(e) => this.handleQueTypeSel(e, idx)} />
                                                                    <label className="custom-control-label" htmlFor={"is_img" + idx}>
                                                                        <span className="input-text">Images</span>
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            {
                                                                // que.selQueType == 2 &&  
                                                                <li className="coupons-option-item ml-3">
                                                                    <div className="common-cus-checkbox stats-checkbox">
                                                                        <label className="com-chekbox-container">
                                                                            <span className="opt-text">Add Stats</span>
                                                                            <input
                                                                                type="checkbox"
                                                                                name="selectTieBreaker"
                                                                                checked={que.is_stats == 1}
                                                                                onChange={() => this.handleStatsChecked(que.is_stats, idx)}
                                                                            />
                                                                            <span className={`com-chekbox-checkmark`}></span>
                                                                        </label>
                                                                    </div>
                                                                </li>
                                                            }
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            {
                                                que.selQueType == 2 ?
                                                    <div className="img-option-sec">
                                                        <div className="opt">
                                                            <div className="opt-tp">
                                                                {this.renderImageblock(que, idx, 'option_1')}
                                                                <Input
                                                                    id="option_01"
                                                                    name='option_1'
                                                                    type='text'
                                                                    value={que.option_1}
                                                                    placeholder='Option 1'
                                                                    maxLength={30}
                                                                    onChange={(e) => this.handleImgInputChange(e, que, idx)}
                                                                />
                                                                {/* {
                                                        que.option_1 && que.option_1.length < 4 &&
                                                        <div className="inpt-info-txt">
                                                            Minimum option size should be 4 characters
                                                        </div>
                                                    } */}
                                                            </div>
                                                            {
                                                                que.is_stats == 1 &&
                                                                <div className="btm">
                                                                    <input
                                                                        disabled={que.is_stats != 1}
                                                                        type="text"
                                                                        className="gry-input-blk"
                                                                        id={"img-txt" + idx}
                                                                        name={"img-txt" + idx}
                                                                        value={que.option_stats['option_1'] || ''}
                                                                        placeholder={'Enter Stats Value'}
                                                                        onChange={(e) => this.handleStats(e, idx, 'option_1')} />
                                                                </div>
                                                            }
                                                        </div>
                                                        <div className="opt">
                                                            <div className="opt-tp">
                                                                {this.renderImageblock(que, idx, 'option_2')}
                                                                <Input
                                                                    id="option_02"
                                                                    name='option_2'
                                                                    type='text'
                                                                    value={que.option_2}
                                                                    placeholder='Option 2'
                                                                    maxLength={30}
                                                                    onChange={(e) => this.handleImgInputChange(e, que, idx)}
                                                                />
                                                                {/* {
                                                        que.option_2 && que.option_2.length < 4 &&
                                                        <div className="inpt-info-txt">
                                                            Option length is 4
                                                        </div>
                                                    }    */}
                                                            </div>
                                                            {
                                                                que.is_stats == 1 &&
                                                                <div className="btm">
                                                                    <input
                                                                        disabled={!(que.option_stats['option_1'])}
                                                                        type="text"
                                                                        className="gry-input-blk"
                                                                        id={"img-txt" + idx}
                                                                        name={"img-txt" + idx}
                                                                        value={que.option_stats['option_2'] || ''}
                                                                        placeholder={'Enter Stats Value'}
                                                                        onChange={(e) => this.handleStats(e, idx, 'option_2')} />
                                                                </div>
                                                            }
                                                        </div>
                                                        <div className="opt">
                                                            <div className="opt-tp">
                                                                {this.renderImageblock(que, idx, 'option_3')}
                                                                <Input
                                                                    id="option_03"
                                                                    name='option_3'
                                                                    type='text'
                                                                    value={que.option_3}
                                                                    placeholder='Option 3'
                                                                    maxLength={30}
                                                                    onChange={(e) => this.handleImgInputChange(e, que, idx)}
                                                                />
                                                                {/* {
                                                        que.option_3 && que.option_3.length < 4 &&
                                                        <div className="inpt-info-txt">
                                                            Minimum option size should be 4 characters
                                                        </div>
                                                    }  */}
                                                            </div>
                                                            {
                                                                que.is_stats == 1 &&
                                                                <div className="btm">
                                                                    <input
                                                                        disabled={!(que.option_stats['option_2'])}
                                                                        type="text"
                                                                        className="gry-input-blk"
                                                                        id={"img-txt" + idx}
                                                                        name={"img-txt" + idx}
                                                                        value={que.option_stats['option_3'] || ''}
                                                                        placeholder={'Enter Stats Value'}
                                                                        onChange={(e) => this.handleStats(e, idx, 'option_3')} />
                                                                </div>
                                                            }
                                                        </div>
                                                    </div>
                                                    :
                                                    <div className="txt-option-sec">
                                                        <div>
                                                            <label className="filter-label">Options 1 </label>
                                                            <Input
                                                                id="option_1"
                                                                name='option_1'
                                                                type='text'
                                                                value={que.option_1}
                                                                placeholder='Write a Options 1..'
                                                                maxLength={30}
                                                                onChange={(e) => this.handleInputChange(e, que, idx)}
                                                            />
                                                            {/* {
                                                    que.option_1 && que.option_1.length < 4 &&
                                                    <div className="inpt-info-txt">
                                                        Minimum option size should be 4 characters
                                                    </div>
                                                }    */}
                                                            {
                                                                que.is_stats == 1 &&
                                                                <div className="btm btm-new">
                                                                    <input
                                                                        disabled={que.is_stats != 1}
                                                                        type="text"
                                                                        className="gry-input-blk"
                                                                        id={"img-txt" + idx}
                                                                        name={"img-txt" + idx}
                                                                        value={que.option_stats['option_1'] || ''}
                                                                        placeholder={'Enter Stats Value'}
                                                                        onChange={(e) => this.handleStats(e, idx, 'option_1')} />
                                                                </div>
                                                            }

                                                        </div>

                                                        <div>

                                                            <label className="filter-label">Options 2 </label>
                                                            <Input
                                                                id="option_2"
                                                                name='option_2'
                                                                type='text'
                                                                value={que.option_2}
                                                                placeholder='Write a Options 2..'
                                                                maxLength={30}
                                                                onChange={(e) => this.handleInputChange(e, que, idx)}
                                                            />
                                                            {/* {
                                                    que.option_2 && que.option_2.length < 4 &&
                                                    <div className="inpt-info-txt">
                                                        Minimum option size should be 4 characters
                                                    </div>
                                                }   */}
                                                            {
                                                                que.is_stats == 1 &&
                                                                <div className="btm btm-new">
                                                                    <input
                                                                        disabled={!(que.option_stats['option_1'])}
                                                                        type="text"
                                                                        className="gry-input-blk"
                                                                        id={"img-txt" + idx}
                                                                        name={"img-txt" + idx}
                                                                        value={que.option_stats['option_2'] || ''}
                                                                        placeholder={'Enter Stats Value'}
                                                                        onChange={(e) => this.handleStats(e, idx, 'option_2')} />
                                                                </div>
                                                            }
                                                        </div>

                                                        <div>
                                                            <label className="filter-label">Options 3 </label>
                                                            <Input
                                                                id="option_3"
                                                                name='option_3'
                                                                type='text'
                                                                value={que.option_3}
                                                                placeholder='Optional'
                                                                maxLength={30}
                                                                onChange={(e) => this.handleInputChange(e, que, idx)}
                                                            />
                                                            {/* {
                                                    que.option_3 && que.option_3.length < 4 &&
                                                    <div className="inpt-info-txt">
                                                        Minimum option size should be 4 characters
                                                    </div>
                                                }    */}
                                                            {
                                                                que.is_stats == 1 &&
                                                                <div className="btm btm-new">
                                                                    <input
                                                                        disabled={!(que.option_stats['option_2'])}
                                                                        type="text"
                                                                        className="gry-input-blk"
                                                                        id={"img-txt" + idx}
                                                                        name={"img-txt" + idx}
                                                                        value={que.option_stats['option_3'] || ''}
                                                                        placeholder={'Enter Stats Value'}
                                                                        onChange={(e) => this.handleStats(e, idx, 'option_3')} />
                                                                </div>
                                                            }
                                                        </div>

                                                        <div>
                                                            <label className="filter-label">Options 4 </label>
                                                            <Input
                                                                id="option_4"
                                                                name='option_4'
                                                                type='text'
                                                                value={que.option_4}
                                                                placeholder='Optional'
                                                                maxLength={30}
                                                                onChange={(e) => this.handleInputChange(e, que, idx)}
                                                            />
                                                            {/* {
                                                    que.option_4 && que.option_4.length < 4 &&
                                                    <div className="inpt-info-txt">
                                                        Minimum option size should be 4 characters
                                                    </div>
                                                }  */}
                                                            {
                                                                que.is_stats == 1 &&
                                                                <div className="btm btm-new">
                                                                    <input
                                                                        disabled={!(que.option_stats['option_3'])}
                                                                        type="text"
                                                                        className="gry-input-blk"
                                                                        id={"img-txt" + idx}
                                                                        name={"img-txt" + idx}
                                                                        value={que.option_stats['option_4'] || ''}
                                                                        placeholder={'Enter Stats Value'}
                                                                        onChange={(e) => this.handleStats(e, idx, 'option_4')} />
                                                                </div>
                                                            }
                                                        </div>


                                                    </div>
                                            }
                                            {que.is_stats == 1 && 
                                            <div className="states-input-view">
                                                <Input
                                                // id="exampleText"
                                                type="text"
                                                placeholder='Stats Title'
                                                name="stats_text"
                                                value={que.stats_text}
                                                maxLength={30}
                                                onChange={(e) => this.handleInputChange(e, que, idx)}
                                            />
                                            
                                            </div>
                    }
                                        </div>
                                    </div>
                                </Col>
                            </Row>
                        )
                    })
                }
                {
                    <div className="fixed-set-prize show-grid">
                        <div className="pt-setp-stype">
                            <div className="set-prizes-title">Tie Breaker
                                <i className="icon-info-border ml-2  cursor-pointer" id="tieBreaker">
                                    <Tooltip placement="right" isOpen={tieBreakerShow} target="tieBreaker" toggle={this.toggleTieBreaker}>{PT_TIE_BREAKER}</Tooltip>
                                </i>

                            </div>
                            <div className="select-prize-op mb-0">
                                <div className="common-cus-checkbox">
                                    <label className="com-chekbox-container">
                                        <span className="opt-text">Yes</span>
                                        <input
                                            //    disabled={editMode}
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={this.state.checkedTie}
                                            // checked={PrizeSetType[CallType + '_SetPrize']}
                                            onChange={(e) => this.handleTieBreaker()}
                                        />
                                        <span className="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        {this.state.checkedTie && <Row className="mt-5">
                            <Col md={4}>
                                <div className="">
                                    <label className="pt-label">Question</label>
                                    <Input
                                        type="textarea"
                                        //    disabled={editMode}
                                        minLength="4"
                                        maxLength="150"
                                        className="required tie-br-question"
                                        id="question"
                                        name="question"
                                        value={tieBreakerQue && tieBreakerQue.question}
                                        placeholder="Tie Breker Question"
                                        onChange={(e) => this.handleFieldVal(e)}
                                    />
                                </div>
                                {
                                    tieBreakerQue && tieBreakerQue.question && tieBreakerQue.question.length < 4 &&
                                    <div className="inpt-info-txt">
                                        Minimum Tie Breaker Question size should be 4 characters
                                    </div>
                                }
                            </Col>
                            <Col md={4}>
                                <div className="">
                                    <label className="pt-label">Range</label>
                                    <div className="d-flex">
                                        <Input
                                            type="text"
                                            //    disabled={editMode}
                                            // maxLength="50"
                                            className="required mr-4"
                                            id="startRange"
                                            name="startRange"
                                            value={tieBreakerQue && tieBreakerQue.start}
                                            // value={question}
                                            placeholder="Start"
                                            onChange={(e) => this.handleFieldVal(e)}
                                        />
                                        <Input
                                            type="text"
                                            //    disabled={editMode}
                                            maxLength="999999"
                                            max={999999}
                                            className="required"
                                            id="endRange"
                                            name="endRange"
                                            value={tieBreakerQue && tieBreakerQue.end}
                                            placeholder="End"
                                            // value={question}
                                            onChange={(e) => this.handleFieldVal(e)}
                                        />
                                    </div>
                                </div>
                            </Col>
                        </Row>}
                    </div>
                }
                <Row>
                    <Col sm={12}>
                        <div className='QusBtn'>
                            {/* this.state.checkedTie ? (this.state.endRange != '' && this.state.startRange != '' && this.state.tieBreakerQue != '' */}
                            <Button
                                //   disabled={this.checkValid()} 
                                disabled={!checkSDPosting && this.state.queFilled == 0 && !this.state.draftQuestion}
                                onClick={() => this.saveDraft()} className="btn-secondary-outline btn btn-secondary  ml-3">Save as Draft</Button>
                            <Button disabled={checkPMPosting && queFilled != questionCount ? true : (!checkPMPosting ? true : false)} onClick={() => this.publishFixture()} className="btn-secondary-outline btn btn-secondary  ml-3">Publish Match</Button>
                        </div>
                    </Col>
                </Row>
            </>
        )
    }
}
