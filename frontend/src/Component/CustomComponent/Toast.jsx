import React from "react";
import { setValue } from "../../helper/Constants";

export default class CustomToast extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isVisible: false,
            message: '',
            icon: '',
            duration: 2000,
            type: '',
        }
    }


    UNSAFE_componentWillMount() {
        setValue.setToastObject(this);
    }


    showToast = (data) => {
        if (this.timeout) {
            clearTimeout(this.timeout);
            this.timeout = null;
        }
        this.setState({
            isVisible: true,
            message: data.message,
            icon: data.icon,
            duration: data.duration,
            type: data.type,
        }, () => {
            this.timeout = setTimeout(() => {
                this.setState({
                    isVisible: false,
                    message: '',
                    icon: '',
                    duration: 100000,
                    type: '',
                });
            }, this.state.duration);
        })
    }

    hideToast = () => {
        this.setState({ isVisible: false });
        if (this.timeout) {
            clearTimeout(this.timeout);
            this.timeout = null;
        }
    }

    componentWillUnmount() {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        this.setState = () => {
            return;
        };
    }

    render() {
        var { message, icon, isVisible } = this.state;
        return (
            <div className={"toast-wrapper" + (isVisible ? ' show' : '')} style={{ zIndex: 99999 }}>
                <div className="toast-inner-wrapper blur-bg" />
                <div className="toast-inner-wrapper">
                    <span className="icon-wrap">
                        {icon.includes('/') ? <img src={icon} alt="" /> : <i className={icon}></i>}
                    </span>
                    <span className="text-wrap">{message}</span>
                    <i className="icon-close" onClick={this.hideToast}></i>
                </div>
            </div>
        )
    }
}
