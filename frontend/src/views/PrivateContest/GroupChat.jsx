import React from 'react';
import _ from "lodash";
import Message from "./Message";
import firebase from "firebase";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import moment from "moment";
import Moment from "react-moment";
import WSManager from '../../WSHelper/WSManager';
import { Utilities } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import * as WSC from "../../WSHelper/WSConstants";
import { DARK_THEME_ENABLE} from '../../helper/Constants';

var today = moment();
var yesterday = moment().subtract(1, 'day');
var mContext = null;

export default class GroupChat extends React.Component {
    constructor(props) {
        super(props);
        console.log(props.location.state, "props");
        this.state = {
            message: "",
            contestUID:this.props.match.params.groupId,
            allDeviceList:[],
            mute_status: "2",
            HeaderOption: {
                isPrimary: DARK_THEME_ENABLE ? false : true,
                    back: true,
                    fixture: false,
                    filter: false,
                    title: 'GroupChat',
                    hideShadow: false,
                    goBackLobby: false,
                    mute_status: "2",
                    rightAction: () => this.toggleNotification(),
                    userDeviceData:''
                  },
        }
    }

    componentWillMount = (e) => {
        try {
            this.messageRef = firebase
                .database()
                .ref()
                .child("group_message")
                .child(this.state.contestUID);

            //list group members
            this.groupMembersRef = firebase
            .database()
            .ref()
            .child("group_members")
            .child(this.state.contestUID);

            this.groupMembersNotiRef = firebase
            .database()
            .ref()
            .child("group_members")
            .child(this.state.contestUID)
            .child(WSManager.getProfile().user_id);

            //update last read
            this.lastReadStatusRef = firebase
            .database()
            .ref()
            .child("user_last_msg_read")
            .child(WSManager.getProfile().user_id)
            .child(this.state.contestUID);
            this.listenMessages();
        } catch (e) {
           
        }
    }

    listenMessages() {
        this.messageRef.limitToLast(500).on("value", message => {
            console.log(message.val(), 'message.....');
            if (message.val() != null) {
                let msgList = Object.values(message.val());
                var groupedMsgList = _.map(_.groupBy(msgList, "date"), mlist =>
                    mlist.map(msgList => _.omit(msgList, "date"))
                );
                this.setState({
                    chatCount: msgList.length,
                    list: groupedMsgList
                },()=>{
                    let mList = document.getElementById('message-list');
                    mList.scrollTo({top:mList.scrollHeight});
                });
                if(this.lastReadStatusRef!=null){
                    let lastItem = msgList[(msgList.length-1)];
                    let newItem = {'last_read':lastItem.messageDate}
                    this.lastReadStatusRef.remove();
                    this.lastReadStatusRef.push(newItem);
                }
            }
        });

        this.groupMembersRef.limitToLast(5000).on("value", message => {
            console.log(message, 'message');
            if (message.val() != null) {
                let membersList = Object.values(message.val());
                console.log(membersList);
                // console.log('membersList', membersList);
                let allDeviceList = [];
                for(let i=0;i<membersList.length;i++)
                {
                    if (membersList[i].userId == WSManager.getProfile().user_id)
                    {
                        let tempUserDeviceData = membersList[i];
                        // console.log('userDeviceData', tempUserDeviceData);
                        this.setState({ userDeviceData: tempUserDeviceData })
                        this.setState({ mute_status: tempUserDeviceData.muteNotification })
                    }
                    // if(membersList[i].deviceList && membersList[i].userId!=WSManager.getProfile().user_id)
                    else
                    {
                        if (membersList[i].deviceList && membersList[i].muteNotification != "1")
                        {
                            let memberDevices = membersList[i].deviceList;
                            if(memberDevices)
                            {
                                for(let k=0;k<memberDevices.length;k++){
                                    allDeviceList.push(memberDevices[k].device_id);
                                }
                            }
                        }
                    }
                }
                this.setState({allDeviceList:allDeviceList}, () => {
                    console.log(allDeviceList, 'allDeviceList');
                })
            }
        });
    }

    handleChange(event) {
        this.setState({ message: event.target.value });
    }

    handleSend() {
        if (this.state.message.trim().length > 0) {
            var newItem = {
                userName: WSManager.getProfile().user_name,
                userId: WSManager.getProfile().user_id,
                userImage: WSManager.getProfile().image !== '' ? Utilities.getThumbURL(WSManager.getProfile().image):'',
                messageDate: moment(new Date(), "dd-MM-yyyy hh:mm a").toString(),
                date: new Date().toDateString(),
                message: this.state.message
            };
            this.messageRef.push(newItem);
            this.triggerFirebaseNotification(this.state.message, this.state.contestUID, newItem.userName)
            this.setState({ message: "" });
            let mList = document.getElementById('message-list');
            mList.scrollTo({top:mList.scrollHeight,behavior: 'smooth'});
        }
    }

    componentWillUnmount(){
        this.lastReadStatusRef = null;
    }
    
    toggleNotification=()=>{
        let {userDeviceData} = this.state;
        if (userDeviceData.muteNotification == "0")
        {
            userDeviceData.muteNotification = "1";
        }
        else if(userDeviceData.muteNotification == "1")
        {
            userDeviceData.muteNotification = "0";
        }

        // Firebase.database().ref(`/group_members/${this.state.contestUID}/${WSManager.getProfile().user_id}`).set(userDeviceData);
        // console.log('userDeviceData updated', userDeviceData);
        mContext.groupMembersNotiRef.set(userDeviceData);

        this.setState({userDeviceData:userDeviceData})
        this.setState({ mute_status: userDeviceData.muteNotification })
    }

    triggerFirebaseNotification(msg, groupid) {
        const { location} = this.props;
        const { first_name, last_name, user_name} = WSManager.getProfile();
        const { fc1, fc2 } = Utilities.getMasterData();
        const FCM_CLOUD_KEY = fc1 + fc2;
        let deviceId = this.state.allDeviceList;
        console.log('deviceId',deviceId);
        if(deviceId && deviceId.length>0){
            let apiHeader = {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json',
                'Authorization': 'key=' + FCM_CLOUD_KEY
            }
            let _title = (first_name ? `${first_name} ${last_name}` : user_name).replace(/^\s+|\s+$/gm,'');
            let _groupname = location.state ? (location.state.childItem ? location.state.childItem.contest_name : WSC.AppName) : WSC.AppName


            
            let mData = {
                "message": msg,
                "body": msg,
                "title": `${_title} in "${_groupname}"`, // WSC.AppName,
                "sound": "default",
                "group_id": groupid,
            };
            let param = {
                "registration_ids": deviceId, 
                "notification": mData,
                "data": mData 
            };

            fetch('https://fcm.googleapis.com/fcm/send', {
                method: 'POST',
                headers: apiHeader,
                body: JSON.stringify(param)
            })
            .then(responseJson => {
                console.log(responseJson);
                return responseJson;
            })
            .catch((error) => {
                console.error(error);
                return {};
            });
        }
    }

    handleKeyPress(event) {
        if (event.key !== "Enter") return;
        this.handleSend();
    }

    render() {
        let {HeaderOption} = this.state;
        HeaderOption.mute_status = this.state.mute_status;
        mContext = this;
        // console.log("this.state.mute_status", HeaderOption)
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container chat-container">
                        <Helmet titleTemplate={""}>
                            <title>{"GroupChat"}</title>
                            <meta name="description" content={""} />
                        </Helmet>
                        <CustomHeader
                            ref={(ref) => this.headerRef = ref}
                            HeaderOption={HeaderOption}
                            {...this.props} />
                        <div className="group-chat">
                            <div ref={el => {this.messagesEnd = el;}} className="message-list" id='message-list'>
                                    {_.map(this.state.list, (obj, index) => (
                                        <div>
                                            <div className="date-header">
                                                {moment(obj[0].messageDate).isSame(today, 'day') ? 'Today' : moment(obj[0].messageDate).isSame(yesterday, 'day') ? 'Yesterday' : <Moment date={obj[0].messageDate} format="MMM DD, YYYY" />}
                                            </div>
                                            {_.map(obj, (item, index) => (
                                                <Message key={index} message={item} />
                                            ))}
                                        </div>
                                    ))}
                            </div>
                            <div className='footer-container'>
                                <div className="typing-box">
                                    <input
                                        id='chat_input'
                                        className="form__input"
                                        type="text"
                                        placeholder="Write a message..."
                                        value={this.state.message}
                                        onChange={this.handleChange.bind(this)}
                                        onKeyPress={this.handleKeyPress.bind(this)}
                                    />
                                </div>
                                <div className="form_button">
                                    <i onClick={this.handleSend.bind(this)} className={'icon-send '+(this.state.message.trim().length===0?' disable':'')}></i>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}