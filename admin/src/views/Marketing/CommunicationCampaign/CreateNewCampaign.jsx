import React, { Component, Fragment } from 'react';
import Select from 'react-select';
import {
    UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem,
    Card, CardBody, Col, Row, CardHeader, Button,
    FormGroup,
    Input,
    Label,
    Modal, ModalBody, ModalHeader, TabContent, TabPane
} from 'reactstrap';
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import 'spinkit/css/spinkit.css';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import LS from 'local-storage';
import queryString from 'query-string';
import * as MODULE_C from "../Marketing.config";
import moment from 'moment';
import ActionRequestModal from '../../../components/ActionRequestModal/ActionRequestModal';
import Loader from '../../../components/Loader';
import HF, { _isUndefined, _isEmpty, _isNull } from '../../../helper/HelperFunction';
import { MSG_DELETE_UB_LIST, CD_SCHEDULE_D } from "../../../helper/Message";
import Images from '../../../components/images';
import SelectDate from "../../../components/SelectDate";
import { Base64 } from 'js-base64';
class CreateNewCampaign extends Component {

    constructor(props) {
        super(props);

        this.state = {
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            userSegmentParam: {
                all_user: null,
                login: null,
                signup: null,
                non_login: null,
                fixture_participation: null,
                custom: null,
                last_7_days: null,
                // to_date: "",
                // from_date: "",
                to_date: new Date(),
                from_date: new Date(),
                schedule_date: null,
                season_game_uid: null,
                contest_id: null,
                home: 'IND',
                away: 'PAK',
                home_flag: 'https://communication-dashboard.s3.amazonaws.com/upload/flag/flag_default.jpg',
                away_flag: 'https://communication-dashboard.s3.amazonaws.com/upload/flag/flag_default.jpg',
                season_scheduled_date: '2019-12-29',
                collection_name: '',
                mobile : null,
                web: null,
                notification_on: null,
                notification_off: null,
                noti_schedule: '1',
            },
            total_users: 0,
            templateList: [],
            notificationData: {
                email_template_id: "",
                promo_code_id: "",
                user_ids: [],
                email: false,
                message: false,
                notification: false,
                custom_sms: '',
                campaign_url: '',
                campaign_name: '',
                campaign_generated_url: '',
                custom_notification_subject: '',
                custom_notification_text: '',
                custom_notification_landing_page: '',
                custom_notification_header_image: '',
                custom_notification_body_image: '',
                deal_id: '',
                promoCodeId: '',
            },
            communication_review_modal: false,
            previewObj: {},
            activeTab: '1',
            depositPromocodes: [],
            matchList: [],
            templateParam: { type: 1 },
            cd_email_template_id: null,
            for_str: null,
            promo_code_id: '',
            cd_balance: {},
            balance_will_used: {
                email_count: 0,
                sms_count: 0,
                notification_count: 0
            },
            isLoading: false,
            copySuccess: '',
            campaign_url_error: '',
            EditMsgType: false,
            EditNotiType: false,
            UserbaseList: [],
            CateTemplateList: [],
            UpmgFixtures: [],
            ContestList: [],
            idxFlag: 0,
            selectedCate: '',
            MessageType: '',
            FirstCall: true,
            todayDate: new Date(),
            fromCommDashCall: false,
            cBoxDisableFlag: false,
            DealRs: '',
            PcType: '',
            // CategoryNotIn: ['2', '14', '15'],
            CategoryNotIn: ['2'],
            pcCodeIn: ['52', '53', '54', '55'],
            DealIn: ['31'],
            AllDeals: [],
            TemplateName: '',
            Recent_Sche_Id: '',
            EditScheData: [],
        };
    }

    componentDidMount() {
        var parsed = queryString.parse(this.props.location.search);
        /**Start code to edit scheduler */
        if (parsed.editid) {
            this.setState({
                Recent_Sche_Id: parsed.editid
            }, ()=>{
                this.getEditScheData()
            });
        /**End code to edit scheduler */
        }
        else {  
            /**Code to start promote redirection */    
            let tempUSParam = this.state.userSegmentParam
            tempUSParam.all_user = 1
            this.setState({ userSegmentParam: tempUSParam }, () => {
                this.getFilterResultTest()
            })
              
            var templateType = {};
            var contestObj = {};
            var promo_code_id = '';

            if (parsed.email_template_id) {
                this.setState({ selectedCate: parsed.email_template_id }, () => {
                    this.getCatTemplates()
                    /**Start for promote fixture or contest */
                    if (parsed.season_game_uid || parsed.contest_id) {
                        this.setState({
                            notificationData: {
                                ...this.state.notificationData,
                                // promo_code_id: parsed.email_template_id == '4' ? '4' : '',
                                promo_code_id: parsed.email_template_id == '4' ? '4' : parsed.email_template_id == '7' ? '11' : '',
                                fixture_id: parsed.season_game_uid,
                                contest_id: parsed.contest_id,
                                league_id: parsed.league_id,                        
                            },                    
                        }, () => {
                            this.setState({ fromCommDashCall: true }, this.getUpcomingFixture)
                        });
                    }
                    /**End for promote fixture or contest */
                    /**Start for promote deal */
                    if (parsed.deal_id) {
                        this.setState({
                            notificationData: {
                                ...this.state.notificationData,
                                deal_id: parsed.deal_id ? Base64.decode(parsed.deal_id) : '',
                                promo_code_id: parsed.promo_code_template_id,
                            },
                            DealRs: parsed.amt ? Base64.decode(parsed.amt) : '',
                        }, () => {
                            this.setState({ fromCommDashCall: true }, this.getDeals)
                        });
                    }
                    /**End for promote deal */

                    /**Start for promote promocode */
                    if (parsed.pc_id) {
                        this.setState({
                            notificationData: {
                                ...this.state.notificationData,
                                promoCodeId: parsed.pc_id ? Base64.decode(parsed.pc_id) : '',
                                promo_code_id: parsed.promo_code_template_id,
                            },
                            PcType: parsed.pct ? Base64.decode(parsed.pct) : '',
                            TemplateName: parsed.template_name ? parsed.template_name : '',
                        }, () => {                        
                                this.setState({ fromCommDashCall: true }, this.getDepositPromotionsPromocodes)
                        });
                    }
                    /**End for promote promocode */
                })

                if (parsed.contest_id) {
                    templateType.type = 0;
                    contestObj.contest_id = parsed.contest_id;
                }

                if (parsed.promo_code_id) {
                    promo_code_id = parsed.promo_code_id;
                }

                if (parsed.season_game_uid) {
                    this.getUpcomingLiveMatchs(parsed.season_game_uid);
                    templateType.type = 2;
                }

                // if (parsed.deal_id) {
                //     templateType.type = 1;
                // }

                this.setState({
                    cd_email_template_id: parsed.email_template_id,
                    promo_code_id: promo_code_id,
                    templateParam: {
                        ...this.state.templateParam,
                        cd_email_template_id: parsed.email_template_id,
                        type: templateType.type
                    }
                }, () => {
                    this.getSegmentationTemplate();
                });
            }

            if (parsed.fixture_participation) {
                this.setState({
                    userSegmentParam: {
                        ...this.state.userSegmentParam,
                        fixture_participation: parsed.fixture_participation
                    }
                })
            }

            if (parsed.email) {
                this.setState({
                    notificationData: {
                        ...this.state.notificationData,
                        email: true
                    }
                });
            }

            if (parsed.for_str) {
                this.setState({
                    for_str: parsed.for_str
                });
            }

            if (parsed.message) {
                this.setState({
                    notificationData: {
                        ...this.state.notificationData,
                        message: true
                    }
                });
            }

            if (parsed.notification) {
                this.setState({
                    notificationData: {
                        ...this.state.notificationData,
                        notification: true
                    }
                });
            }

            if (parsed.all_user || parsed.all_user === 0) {
                this.setState({
                    userSegmentParam: {
                        ...this.state.userSegmentParam,
                        all_user: true,
                        ...contestObj
                    }
                },
                () => {
                    if(parsed.edit)
                    {
                        this.setState({
                            idxFlag : parsed.all_user,
                            notificationData : {
                                notification : parsed.notification
                            },
                            userSegmentParam: {
                                noti_schedule: parsed.noti_schedule,
                                schedule_date: new Date(WSManager.getUtcToLocal(parsed.schedule_date)),
                            }
                            
                        })
                    }
                    this.getFilterResultTest();
                });
            }

            if (Boolean(parsed.login)) {
                this.setState({
                    userSegmentParam: {
                        ...this.state.userSegmentParam,
                        login: true,
                        ...contestObj
                    }
                },
                    () => {
                        this.getFilterResultTest();
                    });

            }

            if (parsed.signup) {
                this.setState({
                    userSegmentParam: {
                        ...this.state.userSegmentParam,
                        signup: true,
                        ...contestObj
                    }
                },
                    () => {
                        this.getFilterResultTest();
                    });

            }        
        }/* Edit else closed */
    }

    toggleRecentCModal = () => {
        let { userSegmentParam } = this.state
        this.setState({
            communication_review_modal: !this.state.communication_review_modal,
        });
        var email_body = this.state.CateTemplateListData.email_body;
        email_body = email_body.replace("{{offer_percentage}}", 10);
        email_body = email_body.replace("{{promo_code}}", "FIRSTDEPOSIT");
        email_body = email_body.replace("{{amount}}", 10);
        email_body = email_body.replace("{{year}}", (new Date()).getFullYear());
        email_body = email_body.replace("{{SITE_TITLE}}", 'Fantasy Sports');
        email_body = email_body.replace("{{home}}", userSegmentParam.home);
        email_body = email_body.replace("{{away}}", userSegmentParam.away);
        email_body = email_body.replace("{{home_flag}}", userSegmentParam.home_flag);
        email_body = email_body.replace("{{away_flag}}", userSegmentParam.away_flag);
        email_body = email_body.replace("{{season_scheduled_date}}", userSegmentParam.season_scheduled_date);
        email_body = email_body.replace("{{collection_name}}", userSegmentParam.collection_name);
        this.setState({
            CateTemplateListData: {
                ...this.state.CateTemplateListData,
                email_body: email_body,
            }
        });
    }
    getSegmentationTemplate() {
        let { templateParam, CategoryNotIn } = this.state
        this.setState({ isLoading: true });
        WSManager.Rest(NC.baseURL + MODULE_C.GET_TEMPLATE_CATEGORY, templateParam).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code === NC.successCode) {

                const templates = [];
                let forContest = {};
                if (templateParam.type == 1 || templateParam.type == 2) {
                    forContest = responseJson.data.filter(temp => (!CategoryNotIn.includes(temp.category_id)))
                } else {
                    forContest = responseJson.data.filter(temp => temp.category_id == this.state.selectedCate);
                }

                forContest.map((data) => {
                    templates.push({
                        value: data.category_id,
                        label: data.category_name,
                        detail: data
                    })
                    return '';
                })
                
                this.setState({ templateList: templates },
                    () => {

                        if (this.state.cd_email_template_id) {
                            this.setState({
                                notificationData: { ...this.state.notificationData, email_template_id: this.state.cd_email_template_id, }
                            }, () => {

                                if (templates.length) {
                                    this.setState({ previewObj: templates[0].detail });
                                }
                            });

                            if (this.state.promo_code_id) {
                                this.getDepositPromotionsPromocodes()
                            }
                        }
                    });

            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                this.props.history.push('/login');
            } else {
                this.setState({ posting: false });
            }
        })
    }

    getSelectedSport = () => {
        let spNm = HF.getSportsData() ? HF.getSportsData() : []
        if (!_.isEmpty(spNm)) {
            var sportName = spNm.filter(function (item) {
                return item.value === LS.get('selected_sport') ? true : false;
            });
            return {
                sports_id: sportName[0].value,
                sports_name: sportName[0].label.toLowerCase()
            };
        }
    }

    notifyBySelection() {
        let { EditMsgType, EditNotiType, notificationData, userSegmentParam, TemplateName, templateParam, Recent_Sche_Id } = this.state
        var sportName = this.getSelectedSport();

        let param = {
            ...notificationData,
            ...userSegmentParam,
            ...sportName,
            'template_name': TemplateName,
            'type': templateParam.type,
        };
        /**Start code for edit schedule notification*/
        if (!_isEmpty(Recent_Sche_Id)) {
            param.recent_communication_id = Recent_Sche_Id
        }
        /**End code for edit schedule notification*/

        if(userSegmentParam['noti_schedule'] == '2')
        {
            param.schedule_date = HF.dateInUtc(userSegmentParam['schedule_date'])
        }

        if (EditMsgType && _.isEmpty(notificationData.custom_sms)) {
            notify.show("Please enter custom message", "error", 3000);
            return false
        }
        if (EditNotiType && _.isEmpty(notificationData.custom_notification_text)) {
            notify.show("Please enter custom notification", "error", 3000);
            return false
        }

        if (EditNotiType && notificationData.custom_notification_landing_page <= 0) {
            notify.show("Please select redirect option", "error", 3000);
            return false
        }

        this.setState({ isLoading: true });
        WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION, param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code === NC.successCode) {

                notify.show(responseJson.message, "success", 3000);
                this.props.history.push('/marketing/new_campaign');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                this.props.history.push('/login');
            } else {
                this.setState({ posting: false });
            }
        })
    }

    getUpcomingLiveMatchs = (sid) => {
        this.setState({ isLoading: true });
        let { selected_sport } = this.state
        let params = {
            sports_id: selected_sport
        }
        WSManager.Rest(NC.baseURL + MODULE_C.GET_LIVE_UPCOMING_MATCHS, params).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code === NC.successCode) {

                var matchList = [];
                responseJson.data.collection.map((data, key) => {
                    matchList.push({
                        value: data.season_game_uid, label: data.collection_name + ' ' + 
                        // moment(data.season_scheduled_date).format("YYYY-MM-DD hh:mm A")
                        HF.getFormatedDateTime(data.season_scheduled_date, "YYYY-MM-DD hh:mm A"),
                        detail: data
                    })
                    return '';
                })

                this.setState({
                    matchList: matchList,
                },
                    () => {

                        if (sid) {
                            this.setState({
                                userSegmentParam: { ...this.state.userSegmentParam, season_game_uid: sid }
                            },
                                () => {
                                    this.getFilterResultTest();
                                });
                        }
                    });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                this.props.history.push('/login');
            } else {
                this.setState({ posting: false });
            }
        })

    }


    exportUser = () => {

        var pairs = [];

        _.map(this.state.userSegmentParam, (val, key) => {
            pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(val));
        });

        var query_string = pairs.join('&');


        window.open(NC.baseURL + MODULE_C.EXPORT_FILTER_DATA + '?' + query_string, '_blank');
    };

    checkFromOtherSource = () => {
        return this.state.for_str === null
    }

    handleActivityValue = (e, option, flag) => {
        if (e) {
            let value = e.target.value;
            let id = e.target.id;
            if (option === "activity") {
                // this.state.userSegmentParam.non_login = null
                this.state.userSegmentParam.all_user = null
                this.state.userSegmentParam.login = null
                this.state.userSegmentParam.signup = null
                this.state.userSegmentParam.fixture_participation = null
                this.state.userSegmentParam.last_7_days = null
                this.state.userSegmentParam.custom = null

                this.state.userSegmentParam.mobile = null
                this.state.userSegmentParam.web = null
                this.state.userSegmentParam.non_login = null

                this.state.userSegmentParam.notification_on = null
                this.state.userSegmentParam.notification_off = null
                
                if (this.checkFromOtherSource()) {
                    this.state.userSegmentParam.season_game_uid = null
                }
            } 
            else if (option === "device_activity"){ 
                this.state.userSegmentParam.mobile = null
                this.state.userSegmentParam.web = null
                this.state.userSegmentParam.non_login = null
                this.state.userSegmentParam.notification_on = null
                this.state.userSegmentParam.notification_off = null
                this.state.userSegmentParam.last_7_days = null
                this.state.userSegmentParam.custom = null
            }
            else if (option === "notify_activity"){ 
                this.state.userSegmentParam.notification_on = null
                this.state.userSegmentParam.notification_off = null
                this.state.userSegmentParam.last_7_days = null
                this.state.userSegmentParam.custom = null
            }
            else {
                this.state.userSegmentParam.last_7_days = null
                this.state.userSegmentParam.custom = null
            }

            this.state.userSegmentParam[id] = value

            if (this.checkFromOtherSource()) {
                this.setState({
                    templateParam: { type: 1 }
                });
            }
            /**Start edit case change userbase no notification and template change  */
            let noti_false = false
            let edit_prc_id = ''
            if (!_isEmpty(this.state.Recent_Sche_Id)) {
                noti_false = true
                edit_prc_id = this.state.notificationData.promo_code_id
            }
            /**End edit case change userbase no notification and template change  */
            this.setState({
                userSegmentParam: this.state.userSegmentParam,
                notificationData: { 
                    ...this.state.notificationData, 
                    email: false, 
                    message: false, 
                    notification: noti_false, 
                    promo_code_id: edit_prc_id 
                },
                total_users: 0,
                depositPromocodes: []

            }, function () {
                if (flag) {
                    this.getSegmentationTemplate();
                }
            })
        }
    }

    getFilterResultTest = () => {
        let param = this.state.userSegmentParam;
        this.setState({ isLoading: true });
        WSManager.Rest(NC.baseURL + MODULE_C.GET_FILTER_RESULT_TEST, param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    total_users: responseJson.data.total_users,
                    balance_will_used: {
                        email_count: 0,
                        sms_count: 0,
                        notification_count: 0,

                    }
                }, () => {
                    if (this.state.FirstCall) {
                        this.getUserbaseList()
                        this.getSegmentationTemplate()
                        this.setState({ FirstCall: false })

                        this.getCatTemplates()

                        /*Sart code action for edit data */
                        if (!_isEmpty(this.state.Recent_Sche_Id))
                        {
                            this.editScheApiCall()
                        }
                        /*End code action for edit data */                        
                    }
                });
                this.state.notificationData.user_ids = responseJson.data.user_ids;
                this.setState({ notificationData: this.state.notificationData });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                this.props.history.push('/login');
            } else {
                this.setState({ posting: false });
            }
        })
    }

    getDepositPromotionsPromocodes = () => {
        let { TemplateName } = this.state        
        let params = {
            template_name: TemplateName,
        }
        WSManager.Rest(NC.baseURL + MODULE_C.GET_DEPOSIT_PROMOCODES, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                console.log('responseJson.data.promocodes', responseJson.data.promocodes)
                this.setState({ depositPromocodes: responseJson.data.promocodes },
                    () => {
                        if (this.state.promo_code_id) {
                            this.setState({
                                notificationData: { ...this.state.notificationData, promo_code_id: this.state.promo_code_id },
                                promo_code_id: ''
                            },);
                        }
                    });

            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                this.props.history.push('/login');
            } else {
                this.setState({ posting: false });
            }
        })
    }
    
    handleDate = (e, name) => {        
        this.state.userSegmentParam[name] = e
        this.setState({ userSegmentParam: this.state.userSegmentParam });

    }

    handleFixture = (selectedOption) => {
        this.setState({
            userSegmentParam: {
                ...this.state.userSegmentParam,
                season_game_uid: selectedOption.value,
                home: selectedOption.detail.home,
                away: selectedOption.detail.away,
                home_flag: selectedOption.detail.home_flag,
                away_flag: selectedOption.detail.away_flag,
                season_scheduled_date: selectedOption.detail.season_scheduled_date,

            },
            templateParam: { type: 2 }
        }, () => {
            // this.getSegmentationTemplate();
        });

    }

    getCatTemplates = () => {
        let { notificationData } = this.state
console.log('notificationDatanotificationData', notificationData)
        let param = {
            "contest_id": notificationData ? notificationData.contest_id : '',
            "fixture_id": notificationData ? notificationData.fixture_id : '',
            "promo_code_id": notificationData ? notificationData.promoCodeId : '',
            "template_id": notificationData ? notificationData.promo_code_id : '',
            "category_id": this.state.selectedCate,
            "message_type": '',
            "item_perpage": '',
            "current_page": '',
            "league_id": notificationData ? notificationData.league_id : '',
            "deal_id": notificationData ? notificationData.deal_id : '',
            // "pr_code_id": notificationData ? notificationData.pc_id : '',
        }
        WSManager.Rest(NC.baseURL + MODULE_C.GET_CUSTOME_TEMPLATE, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var template_list = [];
                _.map(responseJson.data, (item) => {
                    template_list.push({
                        value: item.cd_email_template_id,
                        label: item.template_name
                    });
                });

                notificationData['notification_type'] = responseJson.data ? responseJson.data[0].notification_type : ''

                if (_.isEmpty(notificationData.promo_code_id) && _.isEmpty(notificationData.promoCodeId)) {
                    this.setState({
                        CateTemplateList: template_list
                    });
                }

                let saveDataFlag = false
                if ((!_.isEmpty(notificationData.promo_code_id) && !_.isEmpty(notificationData.email_template_id)) && (!_.isEmpty(notificationData.promoCodeId) || !_.isEmpty(notificationData.fixture_id) || !_.isEmpty(notificationData.deal_id))) {
                    saveDataFlag = true                    
                }
                else {
                    if (!_.isEmpty(notificationData.email_template_id) && !_.isEmpty(notificationData.promo_code_id))
                    {
                        if (notificationData.email_template_id == '1' && notificationData.notification_type != '120') 
                        {
                            /**Promotion for Deposit */
                            saveDataFlag = true
                        }
                        // else if ((notificationData.email_template_id == '2' || notificationData.email_template_id == '4') &&
                        //     (notificationData.notification_type != '121')) {
                        //     /**Promotion for Fixture = 4*/
                        //     /**Promotion for Contest = 2*/
                        //     saveDataFlag = true
                        // }
                        else if ((notificationData.email_template_id == '4') &&
                            (notificationData.notification_type != '300')) {
                            /**Promotion for Fixture = 4*/
                            saveDataFlag = true
                        }
                        else if (notificationData.email_template_id == '2') {
                            /**Promotion for Contest = 2*/
                            saveDataFlag = true
                        }
                        else if ((notificationData.email_template_id == '7') &&
                            (notificationData.notification_type != '131')) {
                            /**Fixture Delay */
                            saveDataFlag = true
                        }
                        else if (
                            (notificationData.email_template_id != '1') &&
                            (notificationData.email_template_id != '2') &&
                            (notificationData.email_template_id != '4') &&
                            (notificationData.email_template_id != '7') &&
                            (notificationData.email_template_id != '14') &&
                            (notificationData.email_template_id != '15')
                        ) {
                            saveDataFlag = true
                        }
                    }                    
                }
                if (saveDataFlag) {
                    this.setState({
                        CateTemplateListData: responseJson.data ? responseJson.data[0] : [],
                        MessageType: responseJson.data ? responseJson.data[0].message_type : '',
                        EditMsgType: false,
                        EditNotiType: false,
                        cBoxDisableFlag: false,
                    }, () => {
                        console.log("CateTemplateListData==", this.state.CateTemplateListData);

                    });
                }

            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    handleChange = (selectedOption) => {
        if (!selectedOption) {
            return false;
        }
        
        if (selectedOption && selectedOption.value == '10') {
            //custom notification checkbox enable
            this.setState({ cBoxDisableFlag: false })
        }

        this.setState({
            notificationData: {
                ...this.state.notificationData,
                email: false,
                message: false,
                notification: false,
                custom_sms: '',
                campaign_url: '',
                campaign_name: '',
                campaign_generated_url: '',
                custom_notification_subject: '',
                custom_notification_text: '',
                custom_notification_landing_page: '',
                promoCodeId: '',
                contest_id: '',
                fixture_id: '',
            }
        },
            () => {

                if (selectedOption && selectedOption.value == '3') {
                    //get percentage refer friend
                    this.getRenderedTempate(selectedOption.detail);
                }
                //check for deposit template
                if (selectedOption && (selectedOption.value == '1' || selectedOption.value == '15')) {
                    //get percentage promocodes for deposit
                    this.getDepositPromotionsPromocodes();
                }
                else {
                    this.setState({
                        depositPromocodes: []
                    });
                }

                let value = selectedOption.value;
                let label = selectedOption.label;
                this.setState({ selectedCate: value }, this.getCatTemplates)


                if (label == "Custom SMS") {
                    this.setState({ EditMsgType: true, EditNotiType: false })
                }
                else if (label == "Custom Notification") {
                    this.setState({ EditNotiType: true, EditMsgType: false, })
                } else {
                    this.setState({ EditNotiType: false, EditMsgType: false, })
                }

                this.setState({
                    notificationData: {
                        ...this.state.notificationData,
                        email_template_id: value,
                        promo_code_id: ''
                    },
                    previewObj: selectedOption.detail,
                    MessageType: '',

                });
            });

    }

    getRenderedTempate = (detail) => {
        WSManager.Rest(NC.baseURL + MODULE_C.RENDER_EMAIL_BODY, detail).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    previewObj: {
                        ...this.state.previewObj,
                        email_body: responseJson.data.email_body,
                        message_body: responseJson.data.message_body
                    }
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                this.props.history.push('/login');
            } else {
                this.setState({ posting: false });
            }
        })
    }

    handleTemplateChange = (selectedOption) => {
        let value = selectedOption.value;
        let label = selectedOption.label;
        this.setState({
            notificationData: {
                ...this.state.notificationData,
                promo_code_id: value,
                
                email: false,
                message: false,
                notification: false,

                promoCodeId: '',
                // contest_id: '',
                fixture_id: '',                
            },
            CateTemplateListData: {
                ...this.state.CateTemplateListData,
                email_body: '',
                subject: '',
                message_body: '',
            },
            TemplateName: label,
        }, () => {

            this.getCatTemplates()
            if (value == '4') {
                this.getUpcomingFixture()
            }
            if (value == '11') {
                this.getDelayedFixture()
            }
            if (value == '31') {
                this.getDeals()
            }
            
            if (this.state.pcCodeIn.includes(value) && (label === 'contest_join_promocode' || label === 'deposit_promocode' || label === 'deposit_range_promocode' || label === 'first_deposit_promocode')) {
                //get percentage promocodes for deposit
                this.getDepositPromotionsPromocodes();
            }
            if (
                (this.state.notificationData.email_template_id == "1" && (value == 1 || value == 2))
                ||
                (this.state.notificationData.email_template_id == "4" && value == 4)
                ||
                (this.state.notificationData.email_template_id == "7" && value == 11)
                ||
                (this.state.notificationData.email_template_id == "15" && this.state.pcCodeIn.includes(value))
                ||
                (this.state.notificationData.email_template_id == "14" && this.state.DealIn.includes(value))
            ) {               
                this.setState({
                    cBoxDisableFlag: true,
                    EditMsgType: false,
                    EditNotiType: false,
                })
            }
        });
    }

    handlePromocodeChange = (selectedOption) => {
        let value = selectedOption.value;
        this.setState({
            notificationData: {
                ...this.state.notificationData,
                promoCodeId: value
            },
        }, () => {
            this.getCatTemplates()
        });
    }

    handleFixtureChange = (selectedOption) => {
        let value = selectedOption.value;
        this.setState({
            notificationData: {
                ...this.state.notificationData,
                fixture_id: value
            },
        }, () => {
            this.getCatTemplates()
        });
    }

    handleContestChange = (selectedOption) => {
        let value = selectedOption.value;
        this.setState({
            notificationData: {
                ...this.state.notificationData,
                fixture_id: '',
                contest_id: value
            },
        }, () => {
            this.getCatTemplates()
        });
    }

    handleNotificationType = (e) => {

        let value = e.target.value;
        let id = e.target.id;

        if (id == 'message' || id == 'email')
        {
            this.state.userSegmentParam['noti_schedule'] = '1'
            this.state.userSegmentParam['schedule_date'] = null
        }

        var notificationData = _.cloneDeep(this.state.notificationData);
        notificationData[id] = value == 'false' ? true : false;
        this.setState({ notificationData: notificationData });
    }

    handleCustomSMS = (e) => {
        let value = e.target.value;
        var notificationData = _.cloneDeep(this.state.notificationData);
        notificationData.custom_sms = value;
        this.setState({ notificationData: notificationData });
    }

    handleLandingPage = (selectedOption) => {

        if (!selectedOption) {
            return false;
        }

        let value = selectedOption.value;
        this.setState({
            notificationData: { ...this.state.notificationData, custom_notification_landing_page: value },
        });

    }
    handleCustomNotification = (e) => {

        let value = e.target.value;
        let id = e.target.id;
        var notificationData = _.cloneDeep(this.state.notificationData);
        notificationData[id] = value;
        this.setState({ notificationData: notificationData }, () => {

        });
    }

    handleSelectUserbase = (item, idx_val) => {

        let SegParamArray = this.state.userSegmentParam
        SegParamArray.user_base_list_id = item
        SegParamArray.all_user = null
        if (idx_val === 0) {
            SegParamArray.all_user = 1
        }
        this.setState({
            selectedUbId: item,
            idxFlag: idx_val,
            userSegmentParam: SegParamArray
        }, () => {
            this.getSegmentationTemplate()
            this.getFilterResultTest()
        })
    }

    handleEditCustomMsg = () => {
        let { notificationData, CateTemplateListData } = this.state
        this.setState({ EditMsgType: !this.state.EditMsgType }, () => {
            let tempNotData = notificationData
            tempNotData.custom_sms = CateTemplateListData ? CateTemplateListData.message_body + '\n' + CateTemplateListData.message_url : ''
            this.setState({ notificationData: notificationData })
        })
    }

    handleEditCustomNoti = () => {
        let { notificationData, CateTemplateListData } = this.state
        this.setState({ EditNotiType: !this.state.EditNotiType }, () => {
            let tempNotData = notificationData
            tempNotData.custom_notification_subject = CateTemplateListData ? CateTemplateListData.subject : ''

            tempNotData.custom_notification_text = CateTemplateListData ? CateTemplateListData.message_body : ''

            tempNotData.custom_notification_landing_page = CateTemplateListData ? CateTemplateListData.redirect_to : ''

            this.setState({ notificationData: notificationData })
        })
    }

    getUserbaseList = () => {
        let params = {}
        WSManager.Rest(NC.baseURL + MODULE_C.GET_USER_BASE_LIST, params).then((responseJson) => {
            let firstUBItem = {
                "user_base_list_id": "",
                "list_name": "All Users",
                "count": this.state.total_users.toString()
            }
            responseJson.data.unshift(firstUBItem)
            if (responseJson.response_code === NC.successCode) {
                this.setState({ UserbaseList: responseJson.data });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    //function to toggle action popup
    toggleActionPopup = (ub_id, idx) => {
        this.setState({
            Message: MSG_DELETE_UB_LIST,
            idxVal: idx,
            UB_ID: ub_id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    deleteUbList = () => {
        let { UB_ID, idxVal } = this.state
        let params = {
            user_base_list_id: UB_ID
        }

        let TempUserbaseList = this.state.UserbaseList
        WSManager.Rest(NC.baseURL + MODULE_C.DELETE_USER_BASE_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                _.remove(TempUserbaseList, function (item, idx) {
                    return idx == idxVal
                })
                this.setState({ UserbaseList: TempUserbaseList })
                this.toggleActionPopup(UB_ID, idxVal)
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getUpcomingFixture() {
        let { notificationData, fromCommDashCall } = this.state
        let param = {
            email_template_id: notificationData.email_template_id,
            sports_id: this.state.selected_sport,
            currentPage: 0,
            pageSize: NC.ITEMS_PERPAGE_LG,
            pagesCount: 1,
            sort_field: "recent_communication_id",
            sort_order: "DESC",
        }
        WSManager.Rest(NC.baseURL + MODULE_C.GET_RECENT_COMMUNICATION_LIST, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let resFixture = []
                if (responseJson.data.fixtures && responseJson.data.fixtures.data) {
                    resFixture = responseJson.data.fixtures.data
                    var fixture_list = [];
                    resFixture.map((data) => {
                        fixture_list.push({
                            value: data.season_game_uid, label: data.collection_name + ' ' + 
                            // moment(WSManager.getUtcToLocal(data.season_scheduled_date)).format("YYYY-MM-DD hh:mm A")
                            HF.getFormatedDateTime(data.season_scheduled_date, "YYYY-MM-DD hh:mm A")
                            ,
                            detail: data
                        })
                    })
                }
                this.setState({
                    UpmgFixtures: fixture_list
                }, () => {
                    if (fromCommDashCall) {
                        this.getCatTemplates()
                    }
                });

            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                this.props.history.push('/login');
            }
        });
    }

    getDelayedFixture() {
        let { notificationData, fromCommDashCall } = this.state
        let param = {
            email_template_id: notificationData.email_template_id,
            sports_id: this.state.selected_sport,
            currentPage: 0,
            pageSize: NC.ITEMS_PERPAGE_LG,
            pagesCount: 1,
            sort_field: "recent_communication_id",
            sort_order: "DESC",
        }
        WSManager.Rest(NC.baseURL + MODULE_C.GET_DELAYED_FIXTURES, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let resFixture = []
                if (responseJson.data.fixtures && responseJson.data.fixtures.data) {
                    resFixture = responseJson.data.fixtures.data
                    var fixture_list = [];
                    resFixture.map((data) => {
                        fixture_list.push({
                            value: data.season_game_uid, label: data.collection_name + ' ' + 
                            // moment(WSManager.getUtcToLocal(data.season_scheduled_date)).format("YYYY-MM-DD hh:mm A")
                            HF.getFormatedDateTime(data.season_scheduled_date, "YYYY-MM-DD hh:mm A"),
                            detail: data
                        })
                    })
                }
                this.setState({
                    UpmgFixtures: fixture_list
                }, () => {
                    if (fromCommDashCall) {
                        this.getCatTemplates()
                    }
                });

            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                this.props.history.push('/login');
            }
        });
    }

    getFixtureContest() {
        let { notificationData } = this.state
        let params = {
            email_template_id: notificationData.email_template_id,
            sports_id: this.state.selected_sport,
            currentPage: 0,
            pageSize: NC.ITEMS_PERPAGE_LG
        }
        WSManager.Rest(NC.baseURL + MODULE_C.GET_LIVE_UPCOMING_MATCHS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var contest_list = [];
                responseJson.data.collection.map((data, key) => {
                    contest_list.push({
                        value: data.season_game_uid,
                        label: data.collection_name
                    })
                    return '';
                })
                this.setState({
                    ContestList: contest_list
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                this.props.history.push('/login');
            }
        });
    }

    onChangeImage = (event) => {

        if (event) {
            let imgUrl = event.target.name
            this.setState({
                [imgUrl]: URL.createObjectURL(event.target.files[0]),
                SavePosting: true
            });
            const file = event.target.files[0];
            if (!file) {
                return;
            }
            var data = new FormData();
            data.append("file", file);

            let apiURL = NC.HEADER_IMAGE
            if (imgUrl === 'NotiBodyImg') {
                apiURL = NC.BODY_IMAGE
            }

            WSManager.multipartPost(NC.baseURL + apiURL, data)
                .then(Response => {
                    if (Response.response_code == NC.successCode) {                        
                        var notificationData = _.cloneDeep(this.state.notificationData);
                        if (imgUrl === 'NotiBodyImg') {
                            notificationData['custom_notification_body_image'] = Response.data.image_name;
                        }
                        if (imgUrl === 'NotiHeadImg') {
                            notificationData['custom_notification_header_image'] = Response.data.image_name;
                        }
                        this.setState({
                            notificationData: notificationData,
                            
                        });

                    } else {
                        this.setState({ [imgUrl]: '' });
                        notify.show(NC.SYSTEM_ERROR, "error", 3000);
                    }
                    this.setState({ SavePosting: false });
                }).catch(error => {
                    notify.show(NC.SYSTEM_ERROR, "error", 3000);
                });
        } else {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        }
    }

    resetFile = (n_flag) => {

        var notificationData = _.cloneDeep(this.state.notificationData);
        if (n_flag === 'NotiBodyImg') {
            notificationData['custom_notification_body_image'] = '';
        }
        if (n_flag === 'NotiHeadImg') {
            notificationData['custom_notification_header_image'] = '';
        }
        this.setState({
            [n_flag]: null,
            notificationData: notificationData,
            SavePosting: false,
        });
    }

    scheduleChange = (event) => {
        let name = event.target.name
        let value = event.target.value
        let tempUsP = this.state.userSegmentParam
        if (value == '1')
        {
            tempUsP['schedule_date'] = null
            this.setState({ userSegmentParam: tempUsP })
        }

        tempUsP[name] = value
        this.setState({ userSegmentParam: tempUsP })
    }

    schdDateChange = (e, name) => {        
        if (e <= this.state.todayDate) {
            notify.show(CD_SCHEDULE_D, "error", 5000)
            return false;
        }else{
            this.state.userSegmentParam[name] = e
            this.setState({ userSegmentParam: this.state.userSegmentParam });
        }
    }

    getDeals = () => {
        let { notificationData } = this.state
        let params = {
            deal_id: notificationData.deal_id
        }
        WSManager.Rest(NC.baseURL + MODULE_C.CD_GET_DEALS_LIST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let deals = []
                _.map(responseJson.data, function (itm) {
                    let msg = ''
                    let cur = HF.getCurrencyCode()
                    if(Number(itm.amount))
                        msg += cur + itm.amount
                    if(Number(itm.bonus))
                        msg += ' Benefits of {B' + itm.bonus
                    if(Number(itm.coin))
                        msg += ',C' + itm.coin
                    if(Number(itm.cash))
                        msg += ','+cur + itm.cash
                    deals.push({
                        value: itm.deal_id,
                        label: msg+'}'
                    });
                })
                this.setState({ AllDeals: deals });
            }
            else {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            }
        })
    }

    handleDealChange = (selectedOption) => {
        let value = selectedOption.value;
        this.setState({
            notificationData: {
                ...this.state.notificationData,
                deal_id: value
            },
        }, () => {
            this.getCatTemplates()
        });
    }
    
    getEditScheData = () => {
        let params = {
            'recent_communication_id': this.state.Recent_Sche_Id
        }
        WSManager.Rest(NC.baseURL + MODULE_C.GET_SCHEDULED_DATA, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let edtdata = ResponseJson.data ? ResponseJson.data : []
                
                this.setState({
                    EditScheData: edtdata,
                    idxFlag: edtdata.user_base_list_id ? edtdata.user_base_list_id : 0,
                    selectedCate: edtdata.email_template_id,
                    userSegmentParam: {
                        ...this.state.userSegmentParam,
                        all_user: edtdata.all_user,
                        login: edtdata.login,
                        signup: edtdata.signup,
                        non_login: edtdata.non_login,
                        fixture_participation: edtdata.fixture_participation,
                        custom: edtdata.custom,
                        last_7_days: edtdata.last_7_days,
                        to_date: new Date(edtdata.to_date),
                        from_date: new Date(edtdata.from_date),
                        schedule_date: new Date(WSManager.getUtcToLocal(edtdata.schedule_date)),
                        season_game_uid: edtdata.season_game_uid,
                        contest_id: edtdata.contest_id,
                        season_scheduled_date: edtdata.season_scheduled_date,
                        collection_name: edtdata.collection_name,
                        mobile: edtdata.mobile,
                        web: edtdata.web,
                        notification_on: edtdata.notification_on,
                        notification_off: edtdata.notification_off,
                        noti_schedule: edtdata.noti_schedule,
                        user_base_list_id: edtdata.user_base_list_id,
                    },
                    notificationData: {
                        ...this.state.notificationData,
                        email_template_id: edtdata.email_template_id,
                        email: edtdata.email,
                        message: edtdata.message,
                        notification: edtdata.notification,
                        custom_sms: edtdata.custom_sms,
                        campaign_url: edtdata.campaign_url,
                        campaign_name: edtdata.campaign_name,
                        campaign_generated_url: edtdata.campaign_generated_url,
                        custom_notification_subject: edtdata.custom_notification_subject,
                        custom_notification_text: edtdata.custom_notification_text,
                        custom_notification_landing_page: edtdata.custom_notification_landing_page,
                    },
                    CateTemplateListData: {
                        ...this.state.CateTemplateListData,
                        email_body: edtdata.email_body,
                        subject: edtdata.subject,
                        message_body: edtdata.message_body,
                    },
                    templateParam: {
                        ...this.state.templateParam,
                        cd_email_template_id: edtdata.email_template_id,
                        type: edtdata.type,
                    }
                },()=>{
                    this.getFilterResultTest()
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    
    editScheApiCall = () => {
        console.log("-----Called editScheApiCall-----");
        
        let { EditScheData } = this.state
        this.setState({
            notificationData: {
                ...this.state.notificationData,
                promo_code_id: EditScheData.promo_code_id
            }
        },()=>{
                if (EditScheData.fixture_participation == '1' || EditScheData.notification_type == '435') {
                    /**Code for by Fixture Participation dropdown */
                    this.byFixturePartiCommonFunction(EditScheData, 'editschedeal')
                }
            
                if (EditScheData.notification_type == '434') {
                    /**Code for promote deal */
                    this.dealCommonFunction(EditScheData.deal_id, 'editschedeal')
                }
                else if (EditScheData.notification_type == '435') {
                    /**Code for promote promocode */
                    this.promocodeCommonFunction(EditScheData, 'editschedeal')
                }
                else if (EditScheData.notification_type == '300') {
                    /**Code for promote fixture */
                    this.fixtureCommonFunction(EditScheData, 'editschedeal')
                }
                else if (EditScheData.notification_type == '131') {
                    /**Code for delay fixture */
                    this.delayFixtureCommonFunction(EditScheData, 'editschedeal')
                }
                else if (EditScheData.notification_type == '135') {
                    /**Code for promote promocode */
                    this.setState({ 
                        NotiHeadImg: NC.S3 + NC.PUSH_HEADER + EditScheData.custom_notification_header_image,
                        NotiBodyImg: NC.S3 + NC.PUSH_BODY + EditScheData.custom_notification_body_image,
                        EditNotiType :  true
                     })
                }
                else{
                    this.getCatTemplates()
                }
        });
    }

    dealCommonFunction = (dealid, condition_flag) => {
        /**Start for promote deal */ 
            /**Start to get all deal */
                this.getDeals()      
            /**End to get all deal */ 
            this.setState({
                notificationData: {
                    ...this.state.notificationData,
                    deal_id: dealid ? dealid : '',
                },                
            }, () => {
                this.setState({ fromCommDashCall: true }, () => {
                    if(condition_flag == 'editschedeal')
                    {
                        this.getCatTemplates()
                    }
                })
            });        
        /**End for promote deal */
    }
    
    promocodeCommonFunction = (data, condition_flag) => {
        /**Start for promote promocode */
        let { EditScheData } = this.state
            this.setState({
                notificationData: {
                    ...this.state.notificationData,
                    promoCodeId: EditScheData.promoCodeId ? EditScheData.promoCodeId : '',
                    promo_code_id: EditScheData.promo_code_id ? EditScheData.promo_code_id : '',
                },
                TemplateName: EditScheData.template_name ? EditScheData.template_name : '',
                // TemplateName: 'deposit_range_promocode',
            }, () => {
                this.setState({ fromCommDashCall: true }, () => {
                    this.getDepositPromotionsPromocodes()
                    this.getCatTemplates()
                })
            });
        
        /**End for promote promocode */
    }
    
    fixtureCommonFunction = (data, condition_flag) => {
        /**Start for promote fixture */
            if (data.season_game_uid || data.contest_id) {
                this.setState({
                    notificationData: {
                        ...this.state.notificationData,
                        promo_code_id: data.email_template_id == '4' ? '4' : data.email_template_id == '7' ? '11' : '',
                        fixture_id: data.season_game_uid,
                        contest_id: data.contest_id,
                        league_id: data.league_id,                        
                    },                    
                }, () => {
                    this.setState({ fromCommDashCall: true }, this.getUpcomingFixture)
                });
            }
        /**End for promote fixture */
    }
    
    /**Start for edit delay fixture */
    delayFixtureCommonFunction = (data, condition_flag) => {
            let { EditScheData } = this.state
            this.setState({
            notificationData: {
                ...this.state.notificationData,
                    fixture_id: EditScheData.season_game_uid,
                    contest_id: EditScheData.contest_id,
                    league_id: EditScheData.league_id,
            },
        }, () => {
            this.setState({ fromCommDashCall: true }, () => {
                this.getDelayedFixture()
                this.getCatTemplates()
            })
        }); 
    }
    /**End for edit delay fixture */
    
    /**Start for edit by Fixture Participants */
    byFixturePartiCommonFunction = (data, condition_flag) => {
            let { EditScheData } = this.state
            this.setState({
            notificationData: {
                ...this.state.notificationData,
                    fixture_id: EditScheData.season_game_uid,
                    contest_id: EditScheData.contest_id,
                    league_id: EditScheData.league_id,
            },
        }, () => {
            this.setState({ fromCommDashCall: true }, () => {
                this.getUpcomingLiveMatchs()
                this.getCatTemplates()
            })
        }); 
    }
    /**End for edit by Fixture Participants */

    render() {
        let {
            cBoxDisableFlag,
            UpmgFixtures,
            MessageType,
            CateTemplateListData,
            selectedCate,
            CateTemplateList,
            EditNotiType,
            UserbaseList,
            EditMsgType,
            userSegmentParam,
            total_users,
            templateList,
            notificationData,
            matchList,
            cd_balance,
            previewObj,
            Message,
            ActionPopupOpen,
            idxFlag,
            DealRs,
            PcType,
            AllDeals,
            DealIn,
        } = this.state;

        const ActionCallback = {
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.deleteUbList,
        }

        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'mr-3 dsh-datepicker',
            year_dropdown: true,
            month_dropdown: true,
        }
        const FromDateProps = {
            ...sameDateProp,
            min_date: false,
            max_date: new Date(userSegmentParam.to_date),
            sel_date: new Date(userSegmentParam.from_date),
            date_key: 'from_date',
            place_holder: 'From Date',
        }
        const ToDateProps = {
            ...sameDateProp,
            min_date: new Date(userSegmentParam.from_date),
            max_date: new Date(),
            sel_date: new Date(userSegmentParam.to_date),
            date_key: 'to_date',
            place_holder: 'To Date',
        }

        let schedBtnCond = ((notificationData.notification && !notificationData.email && !notificationData.message) && (userSegmentParam.noti_schedule == '1' ||(userSegmentParam.noti_schedule == '2' && !_isNull(userSegmentParam.schedule_date))))
       
        let commCond = (notificationData.notification || notificationData.email || notificationData.message)

        let noSchedBtnCond = ((commCond) && (userSegmentParam.noti_schedule == '1' || (userSegmentParam.noti_schedule == '2' && !_isNull(userSegmentParam.schedule_date))))
        
        let customBtnCond = ((commCond) && notificationData.email_template_id && (schedBtnCond || noSchedBtnCond))
        
        let defineBtnCond = ((commCond) && notificationData.email_template_id && notificationData.promo_code_id && (schedBtnCond || noSchedBtnCond))
        
        const scheduleDateProps = {
            disabled_date: false,
            show_time_select: true,
            time_format: 'HH:mm',
            time_intervals: 10,
            time_caption: 'time',
            date_format: 'dd/MM/yyyy h:mm aa',
            handleCallbackFn: this.schdDateChange,
            class_name: 'mr-3 dsh-datepicker',
            year_dropdown: true,
            month_dropdown: true,
            min_date: new Date(),
            max_date: null,
            sel_date: userSegmentParam.schedule_date ? new Date(userSegmentParam.schedule_date) : null,
            date_key: 'schedule_date',
            place_holder: 'Select Date',
        }
        
        return (
            <div className="animated fadeIn new-campaign comm-campaign-box">
                <ActionRequestModal {...ActionCallback} />
                {
                    this.state.isLoading &&
                    <Row>
                        <div className="loader-body">
                            <Loader />
                        </div>
                    </Row>
                }
                <div className="new campaign mb-4">
                    <Row>
                        <Col sm={6} >
                            <h2 className="h2-cls">
                                Communication campaign
                                {this.state.for_str}
                            </h2>
                            {
                                DealRs && 
                                <div className="deal-rs">Rs {DealRs}</div>
                            }
                            {
                                PcType && 
                                <div className="deal-rs">{PcType}</div>
                            }
                        </Col>
                    </Row>
                </div>

                <Row>
                    <Col lg="12">
                        <Card className="card userbase">
                            <div className="userbase-head-wrapper">
                                <div className="select-userbase">Select Userbase</div>
                                <Button
                                    onClick={() => this.props.history.push('/marketing/userbase-list/0')}
                                    className="btn-secondary-outline float-right"> + Create List </Button>
                            </div>
                            <CardBody>
                                <Row>
                                    <Col md={12} className="userbase-wrapper">
                                        <ul className="userbase-list mb-0">
                                            {
                                                _.map(UserbaseList, (item, idx) => {
                                                    return (
                                                        <li
                                                            key={idx}
                                                            className={`userbase-item ${idxFlag == idx ? 'selected' : ''}`}

                                                        >
                                                            {idx != 0 && <UncontrolledDropdown direction="left">
                                                                <DropdownToggle tag="i" caret={false} className="icon-more">
                                                                </DropdownToggle>
                                                                <DropdownMenu>
                                                                    <DropdownItem
                                                                        onClick={() => this.toggleActionPopup(item.user_base_list_id, idx)}
                                                                    >
                                                                        <i className="icon-delete1"></i>Delete
                            </DropdownItem>
                                                                    <DropdownItem
                                                                        onClick={() => this.props.history.push('/marketing/userbase-list/' + item.user_base_list_id)}
                                                                    >
                                                                        <i className="icon-edit"></i>Edit
                            </DropdownItem>
                                                                </DropdownMenu>
                                                            </UncontrolledDropdown>}
                                                            <div
                                                                className="ub-click"
                                                                onClick={() => this.handleSelectUserbase(item.user_base_list_id, idx)}
                                                            >
                                                                <div className="item-label text-ellipsis" title={item.list_name}>{item.list_name}</div>
                                                                <div className="item-count">{item.count}</div>
                                                            </div>
                                                        </li>
                                                    )
                                                })
                                            }
                                        </ul>
                                    </Col>
                                </Row>
                                {
                                    idxFlag == 0 &&
                                    <Row className="mt-4">
                                        <Col md={12}>
                                            <FormGroup>
                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="all_user" className="custom-control-input" value="1" checked={(userSegmentParam.all_user) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'activity', 1)}></input>
                                                    <label className="custom-control-label" htmlFor="all_user">All Users</label>
                                                </div>

                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="login" className="custom-control-input" value="1" checked={(userSegmentParam.login) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'activity', 1)}></input>
                                                    <label className="custom-control-label" htmlFor="login">Login Activity</label>
                                                </div>

                                                {/* <div
                                                    className="custom-control custom-radio custom-control-inline">
                                                    <input
                                                        type="radio"
                                                        id="non_login"
                                                        className="custom-control-input"
                                                        value="1"
                                                        checked={(userSegmentParam.non_login) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'activity', 1)}></input>
                                                    <label className="custom-control-label" htmlFor="non_login">
                                                        No Login Activity
                                                    </label>
                                                </div> */}

                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="signup" className="custom-control-input" value="1" checked={(userSegmentParam.signup) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'activity', 1)}></input>
                                                    <label className="custom-control-label" htmlFor="signup">Signup Activity</label>
                                                </div>
                                                {
                                                    this.state.for_str === null &&
                                                    <React.Fragment>
                                                        <div className="custom-control custom-radio custom-control-inline">
                                                            <input
                                                                type="radio"
                                                                id="fixture_participation"
                                                                className="custom-control-input"
                                                                value="1"
                                                                checked={(userSegmentParam.fixture_participation) ? true : false}
                                                                onChange={(e) => {
                                                                    this.handleActivityValue(e, 'activity', 0);
                                                                    this.getUpcomingLiveMatchs()
                                                                }}
                                                            ></input>
                                                            <label className="custom-control-label" htmlFor="fixture_participation">By Fixture Participation</label>
                                                        </div>
                                                    </React.Fragment>
                                                }
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                }
    
                                {
                                    /**---Start code for devices---*/
                                    (idxFlag == 0 && (userSegmentParam.login || userSegmentParam.signup)) &&
                                    <Row>
                                        <Col md={12}>
                                            <FormGroup>
                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input 
                                                        type="radio" 
                                                        id="mobile" 
                                                        className="custom-control-input" 
                                                        value="1" 
                                                        checked={(userSegmentParam.mobile) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'device_activity', 1)} />
                                                    <label className="custom-control-label" htmlFor="mobile">mobile</label>
                                                </div>

                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input 
                                                        type="radio" 
                                                        id="web" 
                                                        className="custom-control-input" 
                                                        value="1" 
                                                        checked={(userSegmentParam.web) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'device_activity', 1)} />
                                                    <label className="custom-control-label" htmlFor="web">web</label>
                                                </div>

                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input
                                                        type="radio"
                                                        id="non_login"
                                                        className="custom-control-input"
                                                        value="1"
                                                        checked={(userSegmentParam.non_login) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'device_activity', 1)}></input>
                                                    <label className="custom-control-label" htmlFor="non_login">
                                                        No Login Activity
                                                    </label>
                                                </div>
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                    /**---End code for devices---*/
                                }


                                {
                                    /**---Start code for notification type---*/
                                    (idxFlag == 0 && (userSegmentParam.mobile)) &&
                                    <Row>
                                        <Col md={12}>
                                            <FormGroup>
                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input
                                                        type="radio"
                                                        id="notification_on"
                                                        className="custom-control-input"
                                                        value="1"
                                                        checked={(userSegmentParam.notification_on) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'notify_activity', 1)} />
                                                    <label className="custom-control-label" htmlFor="notification_on">Notification ON</label>
                                                </div>

                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input
                                                        type="radio"
                                                        id="notification_off"
                                                        className="custom-control-input"
                                                        value="1"
                                                        checked={(userSegmentParam.notification_off) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'notify_activity', 1)} />
                                                    <label className="custom-control-label" htmlFor="notification_off">notification off</label>
                                                </div>
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                    /**---End code for notification type---*/
                                }

                                {
                                    (idxFlag == 0 && userSegmentParam.fixture_participation) &&
                                    <Row>
                                        <Col lg={4}>
                                            <div className="match-dropdown">
                                                <Select class="form-control"
                                                    value={userSegmentParam.season_game_uid}
                                                    onChange={this.handleFixture}
                                                    options={matchList}>
                                                    <div className="Select-placeholder">Select Match</div>
                                                </Select>
                                            </div>
                                        </Col>
                                    </Row>
                                }


                                {
                                    (idxFlag == 0 && (userSegmentParam.web || userSegmentParam.mobile || userSegmentParam.non_login)) &&
                                    <Row>
                                        <Col md={12} >
                                            <FormGroup>
                                                <div className="custom-control custom-radio custom-control-inline last-7-days">
                                                    <input type="radio" id="last_7_days" className="custom-control-input" value="1" checked={(userSegmentParam.last_7_days) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'duration')}></input>
                                                    <label className="custom-control-label" htmlFor="last_7_days">Last 7 days</label>
                                                </div>


                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="custom" className="custom-control-input" value="1" checked={(userSegmentParam.custom) ? true : false}
                                                        onChange={(e) => this.handleActivityValue(e, 'duration')}></input>
                                                    <label className="custom-control-label" htmlFor="custom">Custom</label>
                                                </div>

                                            </FormGroup>
                                        </Col>
                                    </Row>
                                }


                                {
                                    idxFlag == 0 &&
                                    userSegmentParam.custom && (userSegmentParam.login || userSegmentParam.signup || userSegmentParam.non_login) &&

                                    <FormGroup>
                                        <Row>
                                            <Col md={3}>
                                                <Label className="d-block" htmlFor="from_date"> From Date</Label>
                                                <SelectDate DateProps={FromDateProps} />
                                            </Col>
                                            <Col md={3}>
                                                <Label className="d-block" htmlFor="to_date">To Date</Label>
                                                <SelectDate DateProps={ToDateProps} />
                                            </Col>
                                        </Row>
                                    </FormGroup>

                                }
                                {idxFlag == 0 &&
                                    <Row className="mB20 mT20">
                                        <Col lg={12}>
                                            <div className="getresultbtn">
                                                {
                                                    <Button outline color="danger" onClick={() => this.getFilterResultTest()}>Get Results</Button>
                                                }
                                            </div>
                                            <div className="total-u-box">
                                                <span className="totaluser">Total Users</span>
                                                <span className="t-count">{total_users}</span>
                                            </div>
                                        </Col>
                                    </Row>
                                }
                            </CardBody>
                        </Card>
                        {
                            total_users > 0 &&
                            <Card className="card templates">
                                <CardHeader className="userbasebar">
                                    Select Templates
                                    <a
                                        className="btn-new-cate"
                                        onClick={() => this.props.history.push('/marketing/custome-template?category=true')}>
                                        Create new category
                                    </a>
                                </CardHeader>

                                <CardBody>
                                    <Row>
                                        <Col lg={4}>
                                            <Select
                                                disabled={(!_.isEmpty(templateList) && templateList.length == 1) ? true : false}
                                                className="mt-1"
                                                value={notificationData.email_template_id}
                                                onChange={this.handleChange}
                                                options={templateList}
                                            />
                                        </Col>
                                        {
                                            (selectedCate != '' && selectedCate != '9' && selectedCate != '10') &&
                                            <Col lg={4}>
                                                <Select
                                                    className="template-dd"
                                                    value={notificationData.promo_code_id}
                                                    onChange={this.handleTemplateChange}
                                                    options={CateTemplateList}
                                                />
                                            </Col>
                                        }

                                        {
                                            (
                                                ((notificationData.promo_code_id == '1' || notificationData.promo_code_id == '2' || this.state.pcCodeIn.includes(notificationData.promo_code_id)))
                                            ) &&
                                            <Col lg={4}>
                                                <Select
                                                    className="promocode-dd"
                                                    value={notificationData.promoCodeId}
                                                    onChange={this.handlePromocodeChange}
                                                    options={this.state.depositPromocodes} />
                                            </Col>
                                        }
                                        {
                                            ((notificationData.email_template_id == '2' && notificationData.promo_code_id == '2')) &&
                                            <Col lg={4}>
                                                <Select
                                                    className="contest-dd"
                                                    value={notificationData.contest_id}
                                                    onChange={this.handleContestChange}
                                                    options={this.state.ContestList} />
                                            </Col>
                                        }
                                        {
                                            ((notificationData.email_template_id == '4'
                                                && notificationData.promo_code_id == '4') || (notificationData.email_template_id == '7'
                                                    && notificationData.promo_code_id == '11')) &&
                                            <Col lg={4}>
                                                <Select
                                                    className="fixture-dd"
                                                    value={notificationData.fixture_id}
                                                    onChange={this.handleFixtureChange}
                                                    options={UpmgFixtures} />
                                            </Col>
                                        }
                                        {
                                            (DealIn.includes(notificationData.promo_code_id)) &&
                                            <Col lg={4}>
                                                <Select
                                                    className="deal-dd"
                                                    value={notificationData.deal_id}
                                                    onChange={this.handleDealChange}
                                                    options={AllDeals} />
                                            </Col>
                                        }
                                    </Row>
                                    <Row className="mt-4">
                                        {
                                            userSegmentParam.notification_off !== '1' &&
                                            <Col md={6}>
                                                <div className="custom-control custom-checkbox custom-control-inline">
                                                    <Input
                                                        disabled={
                                                            (
                                                                notificationData.email_template_id == '9' ||
                                                                MessageType == '1' ||
                                                                cBoxDisableFlag
                                                            )
                                                        }
                                                        type="checkbox"
                                                        id="notification" className="custom-control-input"
                                                        onChange={this.handleNotificationType}
                                                        checked={notificationData.notification}
                                                        value={notificationData.notification}></Input>
                                                    <label className="custom-control-label" htmlFor="notification">Notification</label>
                                                </div>
                                            </Col>
                                        }
                                        {
                                        // (!_isUndefined(CateTemplateListData) && !_isEmpty(CateTemplateListData.sms_body)) && 
                                        // <Col md={4}>
                                        //     <div className="custom-control custom-checkbox custom-control-inline">
                                        //         <Input
                                        //             disabled={(
                                        //                 notificationData.email_template_id == '10' ||
                                        //                 MessageType == '2' ||
                                        //                 cBoxDisableFlag
                                        //             )
                                        //             }
                                        //             type="checkbox"
                                        //             id="message"
                                        //             className="custom-control-input"
                                        //             onChange={this.handleNotificationType}
                                        //             checked={notificationData.message == true}
                                        //             value={notificationData.message}></Input>
                                        //         <label className="custom-control-label" htmlFor="message">Message</label>
                                        //     </div>
                                        // </Col>
                                        }
                                        <Col md={6}>
                                            <div className="custom-control custom-checkbox custom-control-inline">
                                                <Input
                                                    disabled={(
                                                        (!_.isEmpty(CateTemplateListData) && _.isEmpty(CateTemplateListData.email_body)) || notificationData.email_template_id == '9' || notificationData.email_template_id == '10' || MessageType == '1' || MessageType == '2'
                                                    )
                                                    }


                                                    type="checkbox"
                                                    id="email"
                                                    className="custom-control-input"
                                                    onChange={this.handleNotificationType}
                                                    checked={notificationData.email}
                                                    value={notificationData.email}
                                                ></Input>
                                                <label className="custom-control-label" htmlFor="email">Email</label>
                                            </div>
                                        </Col>
                                    </Row>
                                    <Row className="popcardrow popcardrow-copy">
                                        {
                                            (userSegmentParam.notification_off !== '1') &&
                                            <Col md={6}>
                                            {
                                                <div className="notification-text-box">
                                                    {(MessageType == '2' || MessageType == '0' || notificationData.email_template_id == '10') &&
                                                        <Fragment>
                                                        <div className="cam-noti-subject clearfix">
                                                                {
                                                                    (notificationData.email_template_id != '10' && !cBoxDisableFlag) &&
                                                                    <i
                                                                        className="icon-edit"
                                                                        onClick={() => this.handleEditCustomNoti()}
                                                                    ></i>
                                                                }
                                                                {(EditNotiType || previewObj.template_name == 'custom-notification')
                                                                    ?
                                                                    <Fragment>
                                                                        <Input
                                                                            maxLength={30}
                                                                            type="text"
                                                                            name="custom_notification_subject"
                                                                            id="custom_notification_subject"
                                                                            placeholder="Enter Subject"
                                                                            value={notificationData.custom_notification_subject}
                                                                            onChange={(e) => { this.handleCustomNotification(e); }}
                                                                        />
                                                                        <div className="head-img-dashbox">
                                                                            {!_.isEmpty(this.state.NotiHeadImg) ?
                                                                                <Fragment>
                                                                                <i onClick={() => this.resetFile('NotiHeadImg')} className="icon-close"></i>
                                                                                    <img className="img-cover" src={this.state.NotiHeadImg} />
                                                                                </Fragment>
                                                                                :
                                                                                <Fragment>
                                                                                    <input
                                                                                        accept="image/x-png,
                                                                                        image/jpeg,image/jpg"
                                                                                        type="file"
                                                                                        name='NotiHeadImg'
                                                                                        id="NotiHeadImgName"
                                                                                        className="head-img-inpt"
                                                                                        onChange={this.onChangeImage}
                                                                                    />
                                                                                <span className="head-img-txt">
                                                                                    Upload 
                                                                                    Image 
                                                                                    192*192
                                                                                </span>
                                                                                </Fragment>
                                                                            }
                                                                        </div>    
                                                                    </Fragment>
                                                                    :
                                                                    <Fragment>
                                                                        <div className="noti-sub-txt">
                                                                            {CateTemplateListData ? CateTemplateListData.subject : ''}
                                                                        </div>
                                                                        {
                                                                            CateTemplateListData.header_image &&
                                                                            <div className="noti-img-head-view">
                                                                                <img
                                                                                    className="img-cover"
                                                                                    src={CateTemplateListData.header_image ? NC.S3 + NC.PUSH_HEADER + CateTemplateListData.header_image : Images.NO_IMAGE} />
                                                                            </div>
                                                                        }
                                                                    </Fragment>
                                                                }
                                                            </div>
                                                            <div className="cam-noti-desc">
                                                                {
                                                                    (EditNotiType || previewObj.template_name == 'custom-notification') ?
                                                                        <Fragment>
                                                                            <a className="pull-right" href="https://coolsymbol.com/emojis/emoji-for-copy-and-paste.html" target="_blank">Emoji Keyboard</a>
                                                                            <Input
                                                                                maxLength={160}
                                                                                type="textarea"
                                                                                name="custom_notification_text"
                                                                                id="custom_notification_text"
                                                                                placeholder="Enter Body"
                                                                                value={notificationData.custom_notification_text}
                                                                                onChange={this.handleCustomNotification}
                                                                            />
                                                                            {/* Start: Hide because Pushnotificaton doesn't have option for banner image */}
                                                                            <div className="head-img-dashbox noti-lg-image" style={{display: 'none'}}>
                                                                                {!_.isEmpty(this.state.NotiBodyImg) ?
                                                                                    <Fragment>
                                                                                        <i onClick={() => this.resetFile('NotiBodyImg')} className="icon-close"></i>
                                                                                        <img className="img-cover" src={this.state.NotiBodyImg} />
                                                                                    </Fragment>
                                                                                    :
                                                                                    <Fragment>
                                                                                        <input
                                                                                            accept="image/x-png,
                                                                                            image/jpeg,image/jpg"
                                                                                            type="file"
                                                                                            name='NotiBodyImg'
                                                                                            id="NotiBodyImgName"
                                                                                            className="head-img-inpt temp-body-box"
                                                                                            onChange={this.onChangeImage}
                                                                                        />
                                                                                    <span className="head-img-txt font-lg">
                                                                                        Upload Image<br /> 720x240
                                                                                    </span>
                                                                                    </Fragment>
                                                                                }
                                                                            </div>
                                                                            {/* End */}
                                                                            <div className="mt-3">
                                                                                <Select
                                                                                    value={notificationData.custom_notification_landing_page}
                                                                                    onChange={this.handleLandingPage}
                                                                                    options={MODULE_C.notification_landing_pages}
                                                                                    className="mySelect"
                                                                                />
                                                                            </div>
                                                                        </Fragment>
                                                                        :
                                                                        <Fragment>
                                                                            <div className="noti-body-txt">
                                                                                {CateTemplateListData ? CateTemplateListData.message_body : ''}
                                                                            </div>
                                                                            {
                                                                                CateTemplateListData.body_image &&
                                                                                <div className="noti-img-body-view">
                                                                                    <img
                                                                                        className="img-cover"
                                                                                        src={CateTemplateListData.body_image ? NC.S3 + NC.PUSH_BODY + CateTemplateListData.body_image : Images.NO_IMAGE} />
                                                                                </div>
                                                                            }
                                                                        </Fragment>
                                                                    }
                                                            </div>
                                                        </Fragment>
                                                    }
                                                </div>
                                            }
                                            </Col>
                                        }
                                        {
                                            // (!_isUndefined(CateTemplateListData) && !_isEmpty(CateTemplateListData.sms_body)) &&
                                            // <Col md={4}>
                                            // {
                                            //     <div className="notification-text-box">
                                            //         {(MessageType == '1' || MessageType == '0' || notificationData.email_template_id == '9') &&
                                            //             <Fragment>
                                            //                 <div className="cam-noti-subject">
                                            //                     {/* {
                                            //                         (notificationData.email_template_id != '9' && !cBoxDisableFlag) &&
                                            //                         <i
                                            //                             className="icon-edit"
                                            //                             onClick={() => this.handleEditCustomMsg()}
                                            //                         ></i>
                                            //                     } */}
                                            //                 </div>
                                            //                 <div className="cam-noti-desc">
                                            //                     { CateTemplateListData ? CateTemplateListData.sms_body : '' }
                                            //                 </div>
                                            //             </Fragment>}
                                            //     </div>
                                            // }
                                            // </Col>
                                        }
                                        <Col md={6}>
                                            {
                                                <div className="notification-text-box">
                                                    <div className="preview-box">
                                                        {
                                                            (MessageType == '0' && !_.isEmpty(CateTemplateListData.email_body)) &&
                                                            <Button
                                                                className="btn-secondary-outline preview-btn"
                                                                onClick={() => this.toggleRecentCModal()}
                                                            >Preview</Button>}
                                                    </div>
                                                </div>}
                                        </Col>
                                    </Row>
                                    {
                                        (notificationData.notification && !notificationData.email && !notificationData.message) &&
                                        <Fragment>
                                            <Row className="noti-schedule">
                                                <Col md={12}>
                                                    <div className="custom-control custom-radio custom-control-inline">
                                                        <input
                                                            type="radio"
                                                            name="noti_schedule"
                                                            className="custom-control-input"
                                                            value="1"
                                                            checked={(userSegmentParam.noti_schedule == '1')}
                                                            onChange={(e) => this.scheduleChange(e)}></input>
                                                        <label className="custom-control-label" htmlFor="noti_schedule">Send Now</label>
                                                    </div>

                                                    <div className="custom-control custom-radio custom-control-inline mr-0">
                                                        <input
                                                            type="radio"
                                                            name="noti_schedule"
                                                            className="custom-control-input"
                                                            value="2"
                                                            checked={(userSegmentParam.noti_schedule == '2')}
                                                            onChange={(e) => this.scheduleChange(e)}></input>
                                                        <label className="custom-control-label" htmlFor="noti_schedule">Send Later</label>
                                                    </div>
                                                </Col>
                                            </Row>
                                            {
                                                userSegmentParam.noti_schedule == '2' &&
                                                <Row className="noti-schedule">
                                                    <Col md={12}>
                                                        <div className="sche-pos">
                                                        <label>
                                                            <SelectDate DateProps={scheduleDateProps} />
                                                            <i className="icon-calender"></i>
                                                        </label>                                                            
                                                        </div>
                                                    </Col>
                                                </Row>
                                            }                                        
                                        </Fragment>
                                    }
                                    
                                    <Row className="align-items-left mt-5">
                                        <Col lg={12}>
                                            <Col md={12} className="sendbtn text-center">
                                                {
                                                    // notificationData.email_template_id == '9' || notificationData.email_template_id == '10' ?
                                                        <Button
                                                            className="btn-secondary-outline"
                                                            // disabled={!((notificationData.notification == true || notificationData.email == true || notificationData.message == true) && notificationData.email_template_id)}
                                                            disabled={(notificationData.email_template_id == '9' || notificationData.email_template_id == '10') ? !customBtnCond : !defineBtnCond}
                                                            onClick={() => this.notifyBySelection()}>
                                                            Send
                                                        </Button>
                                                        // :
                                                        // <Button
                                                        //     className="btn-secondary-outline"
                                                        //     disabled={!((notificationData.notification == true || notificationData.email == true || notificationData.message == true) && notificationData.email_template_id && notificationData.promo_code_id)}
                                                        //     onClick={() => this.notifyBySelection()}>
                                                        //     Send
                                                        // </Button>
                                                }
                                            </Col>
                                        </Col>
                                    </Row>
                                </CardBody>
                            </Card>
                        }

                    </Col>
                </Row>
                <Modal isOpen={this.state.communication_review_modal} toggle={this.toggleRecentCModal} className={this.props.className} className="modal-md">
                    <ModalHeader toggle={this.toggleRecentCModal} className="promotion">
                        <h5 className="promotion title"> Preview</h5>
                    </ModalHeader>
                    <ModalBody>
                        <div className="popuppreviewtab">
                            <TabContent activeTab={this.state.activeTab}>
                                <TabPane tabId="1">
                                    <Row>
                                        <Col sm="12" className="temptab">
                                            <div className="subjecttemp mb-20">
                                                <text className="subject">Subject - {CateTemplateListData ? CateTemplateListData.subject : ''}</text>
                                            </div>
                                            <div dangerouslySetInnerHTML={{ __html: CateTemplateListData ? CateTemplateListData.email_body : '' }}>
                                            </div>
                                        </Col>
                                    </Row>
                                </TabPane>
                            </TabContent>
                        </div>
                        <div className="templatepreview">

                        </div>
                    </ModalBody>
                </Modal>
            </div >
        );
    }
}
export default CreateNewCampaign;
