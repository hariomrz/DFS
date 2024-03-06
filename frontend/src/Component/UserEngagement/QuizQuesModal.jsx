import React from 'react';
import { Modal,ProgressBar } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { CountdownCircleTimer } from 'react-countdown-circle-timer';
import ReactSpeedometer from "react-d3-speedometer";
import { getQuizQuestion, getQuizCheckAns, applyQuizClaim } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import { _Map, Utilities } from '../../Utilities/Utilities';
import WSManager from "../../WSHelper/WSManager";
import Particles from '../../Component/CustomComponent/Particles';
import QuitQuizAlert from "./QuitQuizAlert";
import CustomLoader from '../../helper/CustomLoader';

export default class QuizQuesModal extends React.Component {
    static id = 1;
    constructor(props, context) {
        super(props, context);
        this.state = {
            isWrongAns: false,
            isCorrectAns: false,
            isTimeoutAns: false,
            isShowQuestions: false,
            renderTime: '',
            isSpeedoValue: 0,
            isTimeOver: false,
            ISLOAD: false,
            QuesData:[],
            totalQue: 0,
            QueNoCount: 0,
            reload: false,
            crtAnsCount: 0,
            selOptUID: '',
            PRFLD: WSManager.getProfile(),
            particles: [],
            totalWonAmt: 0,
            showAlert: false,
            userSelAnsArray:[],
            ansmark: false,
            corOptUid: '',
            initAni: false
        };

    }

    componentDidMount() {
        setTimeout(() => {
            this.setState({
                initAni: true
            })
        }, 100);
        this.callQuizQuestionApi()
    }

    /**
     * CUSTOME HEADER UI
     */
    _customeHeader = () => {
        return <div className='header-custome'>
            <div className='w10'>
                <img src={Images.QUIZ_ICON} />
            </div>
            <div className='header-title'>
                {AL.QUIZ}
            </div>
            <div className='w10' />

        </div>

    }

    /**
     * WRONG ANS UI
     */

    _wrongAns = () => {
        return <div className='wrong-center-holder'>
            <div className='title-center'>
                <img src={Images.OHNO_ICON} />
            </div>
            <div className='title-center'>
                {AL.OH_NO}!
            </div>
            <div className='sub-title  mt40'>
                <div className='sub-text-inner'>
                    <span className='sub-text-p'> {AL.ALL_ANS_WRONG}
                    </span>

                    <span className='sub-text-p2 mt30'>{AL.BETTER_LUCK}.</span>
                </div>
            </div>

        </div>
    }

    /**
     * WEHN ANS IS CORRECT 
     */

    _correctAns = () => {
        const {initAni} = this.state;
        return <div className='correct-center'>
            <div className={`top-sec-wrap ${initAni ? ' InitAni' : ''}`}>
                <div className="top-bg-header "></div>
                {/* <div className="profile-holder-outer"> */}
                    <img src={Images.COIN_WIN} alt="" className="coin-win-bg" />
                    <img src={Images.U_DID_IT} alt="" className="u-did-it" />
                    <img src={Images.WON_SMILEY} alt="" className="won-smiley" />
                {/* </div> */}
            </div>
            <div className='center-p-1'>
                {/* <img src={Images.CLAIM_BG} className='w-full' /> */}
                
                <div className='correct-title text-uppercase'>
                    {AL.AWESOME}!
                </div>
                <div className='correct-lower '>
                    {AL.YOU_ARE}
                    <span> <img src={Images.IC_COIN} className='star' />
                    {this.state.totalWonAmt} {AL.coins} </span>
                    {AL.RICHER_TODAY}
                </div>
                <div onClick={()=>this.claimReward(true)} className="button button-primary-blue-white padding-more isbtn">{AL.CLAIM_NOW}</div>
            </div>
        </div>
    }

    /**
     * QUESTION LIST  AND UI
     */

    _questionListUI = (data,idx) => {
        
        const {selOptUID,isTimeOver,isSpeedoValue,isAnsTrue,showAlert,ansmark,corOptUid} = this.state;
        let optionList = data && data.options;
        let queWonAmt = data && data.prize_type == "2" ? parseInt(data.prize_value) : 0;
          
        return <div className='question-list' key={idx} >
            {
                !isTimeOver && 
                <div className='timer-holder'>
                    <CountdownCircleTimer
                        isPlaying={showAlert ? false : true}
                        duration={parseInt(data.time_cap) || 0}
                        size={100}
                        strokeWidth={5}
                        colors={[
                            ['#009933', 0.33],
                            ['#e6e600', 0.33],
                            ['#cc3300', 0.33],
                        ]}
                        onComplete={() => this._istimerOver(data.question_uid)}
                    >
                      {({ remainingTime }) => (<><div className="timer-value">{remainingTime} <div className="timer-text">Sec</div></div>
                
                      </>)}
                    </CountdownCircleTimer>
                </div>
            }
            {
                isTimeOver &&
                 <div className='timer-holder spomtr mb10'>
                    <ReactSpeedometer
                        segments={2}
                        segmentColors={selOptUID == '' ? ['#e6e6e6', '#e6e6e6'] : isAnsTrue ? ['#e6e6e6', '#03CC89'] : ['#F36E6E', '#e6e6e6']}
                        needleHeightRatio={0.5}
                        value={isSpeedoValue}
                        needleColor={'#000000'}
                        textColor={'none'}
                        height={120}
                        width={240}
                        minValue={-1000}
                        maxValue={1000}
                    />
                    {
                        selOptUID == ''
                        ?
                        <div className="out-of-time">{AL.RAN_OUT_OF_TIME}</div>
                        :
                        isAnsTrue ?
                        <div className="corr-ans">{AL.THAT_CORRECT}</div>
                        :
                        <div className="corr-ans">{AL.INCORRECT}</div>
                    }
                </div>
            }
            {data.question_text &&
            <div className='question-holder'>
                {data.question_text}
            </div>
    }
      {data.question_image &&
           <div className="img-container-question">
            <img alt="" src={Utilities.getQuizImg(data.question_image)} />
           </div>
    }
            {/* question_image */}
           
            <div className='ques-list-holder'>
                {
                    optionList.map((item, index) => {
                        return <div key={item.option_uid} className={((index > 0 ? 'questions mt20 ': 'questions ') + ( 
                            selOptUID ? (selOptUID == item.option_uid ? (isAnsTrue ? ' crt-ans' : !isAnsTrue ? (!ansmark ? ' wrng-ans' : '') : ' ') : (!ansmark ? (corOptUid && corOptUid != selOptUID && corOptUid == item.option_uid ? ' crt-ans' : ' ') : '')) : isTimeOver ? ' ' : ''
                        ))} onClick={()=>{selOptUID == '' && !isTimeOver && this.getQuizCheckAns(item.option_uid, data.question_uid, queWonAmt, index)}} >
                            {item.option_text} <i className="icon-tick-ic"></i><i className="icon-close"></i>
                        </div>
                    })
                }
                {data.prize_type == 2 &&
                    <div className="ques-win-amt">
                        <img src={Images.IC_COIN} alt="" /> {queWonAmt}
                    </div>
                }
            </div>
        </div>
    }


    /**
     * TIMER OVER METHOD
     */

    _istimerOver = (QUID) => {
        this.setState({
            isTimeOver: true
        },()=>{
            if(this.state.selOptUID == ''){
                // this.state.userSelAnsArray.push({"question_uid": QUID,"option_uid": ""})
                let isQueRem = this.state.QuesData.length > this.state.QueNoCount + 1 ? true : false
                setTimeout(() => {
                    this.setState({
                        isTimeOver: false,
                        QueNoCount: this.state.QueNoCount + 1,
                        isAnsTrue: false,
                        isSpeedoValue: 0,
                        selOptUID: '',
                        isWrongAns: !isQueRem && this.state.totalWonAmt == 0 ? true : false,
                        isCorrectAns: !isQueRem && this.state.totalWonAmt > 0 ?true : false
                    },()=>{
                        this.scrollSection()
                    })
                }, 4000);
            }
        })
    }

    scrollSection=()=>{
        this._div.scrollTop = 0
    }

    /**
     * Progress bar 
     */
    progressbar=(currentQue)=>{
        return <div className='progressbar-view-container'>
            <ProgressBar now={this.ShowProgressBar(this.state.QueNoCount + 1,this.state.totalQue)} />
            <div className="progress-bar-values">
                <span>{this.state.QueNoCount + 1}</span>/{this.state.totalQue} {AL.QUESTIONS}
            </div>
        </div>
    }
    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }
    callQuizQuestionApi = () => {
        let param = {
            "quiz_uid": this.props.QData.quiz_uid
        }
        this.setState({ ISLOAD: true })
        getQuizQuestion(param).then((responseJson) => {
            this.setState({ ISLOAD: false })
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    QuesData: responseJson.data,
                    isShowQuestions: true,
                    isCorrectAns:false,   
                    isWrongAns:false,
                    totalQue: responseJson.data.length
                })
            }
        })
    }
    
    getQuizCheckAns = (optUID,queUID,queWonAmt, index) => {
        let param = {
            "question_uid": queUID,
            "option_uid": optUID
        }
        this.state.userSelAnsArray.push({"question_uid": queUID,"option_uid": optUID})
        this.setState({selOptUID: optUID,ansmark: true})
        getQuizCheckAns(param).then((responseJson) => {
            // this.setState({ reload: false })
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let CorrectAns = responseJson.data.is_correct;
                this.setState({
                    is_correct: CorrectAns,
                    isTimeOver: true,
                    isAnsTrue: CorrectAns == 0 ? false : true,
                    isSpeedoValue: CorrectAns == 0 ? -500 : 500,
                    crtAnsCount: CorrectAns == 0 ? this.state.crtAnsCount : this.state.crtAnsCount + 1,
                    totalWonAmt: CorrectAns == 0 ? parseInt(this.state.totalWonAmt) : parseInt(this.state.totalWonAmt) + parseInt(queWonAmt),
                    ansmark: false,
                    corOptUid: responseJson.data.correct_option_uid
                    // isCorrectAns: CorrectAns == 0 ? false : true,
                    // isWrongAns: CorrectAns == 0 ? true : false,
                    // reload: true
                }
                ,()=>{
                    setTimeout(() => {
                        this.setState({
                            QueNoCount: this.state.QueNoCount + 1,
                            // reload: false,
                            isTimeOver: false,
                            isAnsTrue: false,
                            isSpeedoValue: 0,
                            selOptUID: '',
                            ansmark: false,
                            corOptUid: ''
                            // isCorrectAns: false,
                            // isWrongAns: false
                        },()=>{
                            this.showFinalResult()
                            this.scrollSection()

                            const { totalQue, QueNoCount, is_correct} = this.state;
                            Utilities.gtmEventFire('play_quiz', {
                                no_of_questions: totalQue,
                                question_no: QueNoCount,
                                quiz_selected_answer: index + 1,
                                is_ans_correct: is_correct == 1 ? 'Yes' : 'No'
                            })
                        })                        
                    }, 4000);
                }
                )
            }
        })
    }

    showFinalResult=()=>{
        const {crtAnsCount,QueNoCount,QuesData} = this.state;
        if(QueNoCount == QuesData.length){
            if(crtAnsCount == 0){
                this.setState({
                    isWrongAns: true,
                    isShowQuestions: false
                })
            }
            else{
                this.setState({
                    isCorrectAns: true,
                    isShowQuestions: false
                },()=>{
                    this.handleOnClick()
                })
            }
        }
    }

    clean(id) {
        this.setState({
            particles: this.state.particles.filter(_id => _id !== id)
        });
    }
    
    handleOnClick = () => {
        const id = QuizQuesModal.id;
        QuizQuesModal.id++;

        this.setState({
            particles: [...this.state.particles, id]
        });
        setTimeout(() => {
            this.clean(id);
            // this.props.preData.mHide();
        }, 5000);
    }

    showQuizAlert=()=>{
        this.setState({
            showAlert: true
        })
    }
    hideQuizAlert=()=>{
        let isLastQue = this.state.QuesData.length == this.state.QueNoCount + 1
        this.setState({
            showAlert: false,
            QueNoCount: this.state.QueNoCount + 1,
        },()=>{
            // if(isLastQue){
                this.showFinalResult()
                this.scrollSection()
            // }
        })
    }
    hideQuizAlertQuiz=()=>{
        let isLastQue = this.state.crtAnsCount > 0
        if(isLastQue > 0){
            this.setState({
                showAlert: false,
                isCorrectAns : true,
                isShowQuestions: false
            },()=> {
                this._correctAns()
            })
        }else{
            this.setState({
                showAlert: false,
                isWrongAns : true,
                isShowQuestions: false
            },()=> {
                this._wrongAns()
            })
        }

        // this.setState({
        //     showAlert: false,
        //     // QueNoCount: this.state.QueNoCount + 1,
        // },()=>{
        //     if(isLastQue){
        //         // this.claimReward(true)
        //         this._correctAns()
        //     }else{
        //         // this.props.isHide()
        //         this._wrongAns()
        //     }
           
        // })
    }

    claimReward=(showSucc)=>{   
        if(showSucc) {
            let param = {
                "quiz_uid": this.props.QData.quiz_uid,
                "questions": this.state.userSelAnsArray
            }
            this.setState({ ISLOAD: true })
            applyQuizClaim(param).then((responseJson) => {
                this.setState({ ISLOAD: false })
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    if(showSucc){
                        this.props.showClaimSuccModal(this.state.totalWonAmt)
                    }
                    else{
                        this.props.isHide()
                    }
                }
            })
        }else{
            this.props.isHide()
        }
       
    }

    render() {

        const { isShow, isHide, QData } = this.props;
        const {
            isWrongAns,
            isCorrectAns,
            isTimeoutAns,
            isShowQuestions,
            renderTime,
            ISLOAD,
            QuesData,
            totalQue,
            reload,
            QueNoCount,
            particles,
            showAlert
        } = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isShow}
                        // onHide={isHide}
                        dialogClassName="custom-modal leaderboard-modal"
                        className="modal-full-screen quiz-question  particles"
                    >
                            <Modal.Header >
                                {
                                    !isWrongAns && !isCorrectAns &&
                                    <a href onClick={()=>this.showQuizAlert()} className="qmclose">
                                        <i className="icon-close"></i>
                                    </a>
                                }
                                {
                                    this._customeHeader()
                                }
                            </Modal.Header>
                            <Modal.Body  >
                                <div className="MB-wrap" ref={(ref) => this._div = ref} >
                                    { !isWrongAns && !isCorrectAns && this.progressbar()}
                                    {
                                        isWrongAns && this._wrongAns()
                                    }
                                    {
                                        isCorrectAns && 
                                        this._correctAns()
                                    }
                                    {
                                        isWrongAns && <div className='lets-play'>
                                            <div onClick={()=>this.claimReward(false)} className="button button-primary-blue-white padding-more isbtn">{AL.OKAY}</div>
                                        </div>
                                    }
                                    {
                                        isShowQuestions && QuesData.length > 0 && QuesData.length >= QueNoCount + 1 && !reload &&
                                        // _Map(QuesData,(item,idx)=>{
                                        //     return(
                                                this._questionListUI(QuesData[QueNoCount],QueNoCount)
                                        //     )
                                        // })
                                    }
                                    {
                                        reload && QuesData && QuesData.length == 0 && <CustomLoader isFrom={this.state.isFrom} />
                                    }
                                </div>


                            </Modal.Body>


                        {particles.map(id => (
                            <Particles key={id} count={Math.floor(window.innerWidth / 5)} />
                        ))}

                        {
                            showAlert &&
                            <QuitQuizAlert 
                                isShow={showAlert}
                                isHide={this.hideQuizAlert}
                                close={this.hideQuizAlertQuiz}
                                // close={isHide}
                            />
                        }
                    </Modal>
                )
                }
            </MyContext.Consumer>
        );
    }
}