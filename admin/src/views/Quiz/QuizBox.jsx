import React, { Component, Fragment } from "react";
import { UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, } from 'reactstrap';
import ReadMoreAndLess from 'react-read-more-less';
import HF, { _Map } from '../../helper/HelperFunction';
import Images from '../../components/images';
import HelperFunction from "../../helper/HelperFunction";
import * as NC from "../../helper/NetworkingConstants";
export default class QuizBox extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }

    getBgColor = (prediction_count, total_user_joined) => {
        return (prediction_count != "0" && total_user_joined != "0") ? ((prediction_count / total_user_joined) * 100) : "0"
    }

    render() {
        let { item, activeTab, btnPosting } = this.props
        console.log("first",item)
        return (
            <div className={`quiz-box ${item.is_hide == "1" ? ' quiz-hide' : ''}`}>
                <div className="clearfix">
                    <div className="ques">
                        <ReadMoreAndLess
                            ref={this.ReadMore}
                            charLimit={90}
                            readMoreText="Read more"
                            readLessText="Read less"
                        >
                            {item.question_text ? item.question_text : "-" }
                        </ReadMoreAndLess>
                    </div>
                    
                    <div className="ques-action">
                        <UncontrolledDropdown direction="left">
                            <DropdownToggle tag="i" caret={false} className="icon-more">
                            </DropdownToggle>
                            <DropdownMenu>
                                {
                                    activeTab == '0' &&
                                    <Fragment>
                                        <DropdownItem
                                            onClick={() => this.props.modal_action_no(item)}
                                        >
                                            <i className="icon-delete1"></i>Delete
                                    </DropdownItem>
                                        <DropdownItem
                                            onClick={() => this.props.edit_question(item, '1')}
                                        >
                                            <i className="icon-edit"></i>Edit
                                    </DropdownItem>
                                    </Fragment>
                                }
                                {
                                    activeTab != '1' &&
                                    <DropdownItem onClick={() => !btnPosting ? this.props.show_hide_ques(item) : null}>
                                        <i className={`${item.is_hide == "0" ? 'icon-hide' : 'icon-show'}`}></i>
                                        {item.is_hide == "0" ? "Hide" : "Show"}
                                    </DropdownItem>
                                }

                                {
                                    activeTab == '1' &&
                                    <DropdownItem
                                        onClick={() => this.props.edit_question(item, '2')}
                                    >
                                        {/* <i className="icon-hide"></i> */}
                                        Move to Create Quiz
                                    </DropdownItem>
                                }

                            </DropdownMenu>
                        </UncontrolledDropdown>
                    </div>

                </div>
                <div className={`without-img-view-quiz ${item.question_image ? "img-view-quiz" : ''}` }>
                    <ul className="pool-list">
                        {
                            _Map(item.options, (options, idx) => {
                                return (
                                    <li
                                        key={idx}
                                        className={`clearfix pool-item ${options.is_correct == '1' ? "active" : ""}`}>
                                        <div className="float-left answer-opt">
                                            {options.option_text}
                                        </div>
                                    </li>
                                )
                            })
                        }
                    </ul>
                   {
                     (item.question_image) &&
                     <div className="img-container-quiz">
                     <img src={NC.S3 + NC.QUIZ_IMG + item.question_image } />
                     </div>
                   }
                </div>
                <div className="clearfix qzFtr">
                    <div className="float-left">
                        <div className="qz-info">
                            Time  : <span>{item.time_cap} Sec</span>
                        </div>
                    </div>
                    <div className="float-right">
                        <div className="qz-info">
                            Prize :  <span>
                                <img src={Images.REWARD_ICON} alt="" />
                                {item.prize_value}
                            </span>                          
                        </div>
                    </div>                    
                </div>
            </div>
        )
    }
}