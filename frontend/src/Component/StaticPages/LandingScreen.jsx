import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { createBrowserHistory } from 'history';
import { Utilities } from '../../Utilities/Utilities';
import { changeLanguageString } from "../../helper/AppLabels";
import { withTranslation } from "react-i18next";
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";

const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

class LandingScreen extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
        }
    }

    UNSAFE_componentWillMount() {
            this.checkForUserRefferal()
    }

    componentDidMount() {
        if (WSManager.getAppLang() == null) {
            WSManager.setAppLang('en');
        }
        changeLanguageString();
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'visitors');
    }

    checkForUserRefferal() {
        if ( parsed && parsed.referral ) {
            WSManager.setReferralCode(parsed.referral)
            this.moveToSignUp();
        } else {
            WSManager.setReferralCode("")
            this.props.history.replace("/lobby");
        }
    }

    moveToSignUp() {
        this.props.history.push({
            pathname: '/signup'
        })
    }

    render() {
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="registration-wrap" />
                )}
            </MyContext.Consumer>
        )
    }
}
export default withTranslation()(LandingScreen)