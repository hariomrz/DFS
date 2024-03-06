import React, { Component } from "react";
import Images from "../../components/images";
import Moment from "react-moment";
import WSManager from "../../WSHelper/WSManager";
export default class Message extends Component {
  render() {
    const {
      userId,
      userImage,
      userName,
      message,
      messageDate
    } = this.props.message;
    return (
      <div
        className={
          WSManager.getProfile().user_id === userId
            ? "message-box-right"
            : "message-box-left"
        }
      >
        {WSManager.getProfile().user_id !== userId&&
          <img
            alt=""
            className="user-img"
            src={userImage ? userImage : Images.DEFAULT_AVATAR}
          />
        }
        <div className="msg-date-container">
          <div className="msg-view">
          {WSManager.getProfile().user_id !== userId&&
            <div className="message-author">{userName}</div>
          }
            <div className="text-message">{message}</div>
          </div>
          <div className="message-date">
            <Moment date={messageDate} format="hh:mm a" />
          </div>
        </div>
      </div>
    );
  }
}