import React, { Component } from 'react';
import { ReCaptcha } from 'react-recaptcha-v3';

export default class ReactCaptcha extends Component {
    constructor(props) {
        super(props);
        this.state = {
            scriptLoaded: false
        };
    }
    componentDidMount() {
        var script = document.createElement("script");
        script.src = "https://www.google.com/recaptcha/api.js?render=" + process.env.REACT_APP_CAPTCHA_SITEKEY;
        script.type = "text/javascript";
        script.onload = script.onreadystatechange = () => {
            if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") {
                this.setState({
                    scriptLoaded: true
                });
            }
        };
        document.getElementsByTagName("head")[0].appendChild(script);
    }

    componentWillUnmount() {
        this.setState = () => {
            return;
        };
    }

    render() {
        const { verifyCallback } = this.props;
        return (
            this.state.scriptLoaded && <ReCaptcha
                sitekey={process.env.REACT_APP_CAPTCHA_SITEKEY}
                verifyCallback={verifyCallback}
            />
        )
    }
}
