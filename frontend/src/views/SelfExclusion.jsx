import React from 'react';
import { NavLink, } from "react-router-dom";
import { FormGroup, Checkbox } from 'react-bootstrap';
import * as AL from "../helper/AppLabels";
import CustomHeader from '../components/CustomHeader';
import { MyContext } from '../InitialSetup/MyProvider';
import {SelfExclusionInterval} from "../Modals";
import {callUserSelfExcl,setSelfExcl} from "../WSHelper/WSCallings";
import * as WSC from "../WSHelper/WSConstants";
import { Utilities } from '../Utilities/Utilities';
import {DARK_THEME_ENABLE} from "../helper/Constants";

export default class SelfExclusion extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {          
            SEAgree: false,
            showLimit: false,
            isLoading: false ,
            DSE_LIM: [],
            USE_LIM: [],
            SelectedInt: '',
            isFrom: this.props && this.props.location && this.props.location.state && this.props.location.state.isFrom
        }
    }

    componentDidMount() {
        this.callUserSelfExcApi();
    }

    callUserSelfExcApi=async (data)=>{
        this.setState({ isLoading: true })
        callUserSelfExcl().then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let data = responseJson.data;
                this.setState({ 
                    DSE_LIM: data.default_self_exclusion ,
                    USE_LIM: data.user_self_exclusion ,
                    SelectedInt: data.user_self_exclusion && data.user_self_exclusion.max_limit ? data.user_self_exclusion.max_limit : data.default_self_exclusion.default_limit
                },()=>{
                    this.setState({
                        isLoading: false 
                    })
                })
            }
        })
    }

    submitUserLimit=()=>{
        let param = {
            "max_limit" : this.state.SelectedInt
        }
        setSelfExcl(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(AL.LIMIT_CHANGED_SUCCESSFULLY, 2000);
                if(this.state.isFrom == 'my-wallet'){
                    this.props.history.goBack();
                }
                else{
                    this.props.history.replace('/lobby' + "#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash());
                }
            }
            else {
                Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
            }
        })
    }

    showLimit=()=>{
        this.setState({
            showLimit: true
        })
    }
    
    hideLimit=()=>{
        this.setState({
            showLimit: false
        })
    }

    setNewinterval=(item)=>{
        this.setState({
            SelectedInt : item,
            showLimit: false
        })
    }

    render() {
        const HeaderOption = {
            back: true,
            goBackLobby: this.state.isFrom == 'my-wallet' ? false : true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            title: AL.SELF_EXCLUSION,
            isFrom: 'self-exclusion'
        }
        const {
            SEAgree,showLimit,DSE_LIM,USE_LIM,SelectedInt
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed self-exc-wrap">
                       <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                       <div className="webcontainer-inner">
                            <div className="self-desc">{AL.SELF_EXC_TEXT}</div>
                            <div className="amt-sect">
                                <div className="amt-label">{AL.CURRENT_LIMIT} (*{AL.DEFAULT_LIMIT_IS} {Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseInt(DSE_LIM.default_limit))})</div>
                                <div className="amt-value" onClick={()=>this.showLimit()}>{Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseInt(SelectedInt))}</div>
                                <div className="amt-limit">*{AL.SELF_EXC_HELP_TEXT}</div>
                            </div>
                            <div className="checkbox-sec">
                                <FormGroup>
                                    <Checkbox className="custom-checkbox" value=""
                                        onClick={() => this.setState({
                                            SEAgree: !this.state.SEAgree
                                        })}
                                        checked={SEAgree} name="self_exclusion" id="self_exclusion">
                                        <span className="auth-txt sm">{AL.SELF_EXC_CHECKBOX_TEXT}</span>
                                    </Checkbox>
                                </FormGroup>
                            </div>
                            <div className="btm-fixed-text">
                                {AL.SELF_EXC_SUPPORT_TEXT}   
                               <NavLink exact to="/contact-us">
                                    {AL.SUPPORT_TEAM}
                                </NavLink>                           
                            </div>
                            <a href className={"btn btn-primary btn-btm-fixed" + (SEAgree ? '' : ' disabled')} onClick={()=>this.submitUserLimit()}>{AL.SUBMIT}</a>
                            {/* <a href className={"btn btn-primary btn-btm-fixed" + (DSE_LIM.default_limit != SelectedInt && SEAgree ? '' : ' disabled')} onClick={()=>this.submitUserLimit()}>{AL.SUBMIT}</a> */}
                       </div>

                        {
                            showLimit &&
                            <SelfExclusionInterval 
                                data={{
                                    DSE_LIM: DSE_LIM,
                                    USE_LIM: USE_LIM,
                                    SelectedInt: SelectedInt
                                }} 
                                setNewinterval={this.setNewinterval}
                                hideLimit={this.hideLimit}
                                showLimit={showLimit}
                            />
                        }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}