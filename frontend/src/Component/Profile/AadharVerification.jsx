import React from 'react'
import * as AppLabels from '../../helper/AppLabels'
import MetaData from '../../helper/MetaData'
import { Helmet } from 'react-helmet'
import CustomLoader from '../../helper/CustomLoader'
import AadharVerificationManual from './AadharVerificationManual'
import AadharVerificationAutokyc from './AadharVerificationAutokyc'
import WSManager from '../../WSHelper/WSManager'
import { Utilities } from '../../Utilities/Utilities'
import * as Constants from "../../helper/Constants";
import CustomHeader from '../../components/CustomHeader'

export default class AadharVerification extends React.Component {
    constructor(props) {
        super(props)
        const adr_mode = Utilities.getMasterData().adr_mode
        this.state = {
            manualVerification: adr_mode == 1 ? false : true
        }
    }
    toggleView = () => {
        this.setState({
            manualVerification: !this.state.manualVerification
        })
    }

    backAction = () => {
        const { history } = this.props
        const { manualVerification } = this.state
        console.log(manualVerification);
        if (manualVerification) {
            this.toggleView()
        } else {
            history.goBack()
        }

    }
    render() {
        const {
            refreshPage,
            userName,
            aadhar_number,
            aadhar_name,
            imageUrl,
            imageBackUrl,
            ageConsent,
            confirmConsent,
            stateConsent
        } = this.state;

        const HeaderOption = {
            back: true,
            notification: false,
            hideShadow: true,
            title: WSManager.getProfile().aadhar_status == "1" ? AppLabels.AADHAR + " " + AppLabels.DETAILS : AppLabels.AadharVerification,
            fromProfile: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true
        }
        const { history } = this.props
        const { manualVerification } = this.state
        const AadhaarProps = {
            ...this.props,
            toggleView: this.toggleView
        }

        return (
            <div className="web-container transparent-header web-container-fixed verify-account aadhaar">
                {this.state.isLoading && <CustomLoader />}
                <Helmet titleTemplate={`${MetaData.template} | %s`}>
                    <title>{MetaData.aadhaar.title}</title>
                    <meta
                        name="description"
                        content={MetaData.aadhaar.description}
                    />
                    <meta
                        name="keywords"
                        content={MetaData.aadhaar.keywords}
                    ></meta>
                </Helmet>
                <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                <div className="app-header-style">
                    <div className="row-container">
                        <div className="section-min section-left">
                            {
                                Utilities.getMasterData().adr_mode == 1 ?
                                <a className="header-action" onClick={this.backAction}> <i className="icon-left-arrow" /></a>
                                : 
                                <a className="header-action" onClick={history.goBack}> <i className="icon-left-arrow" /></a>
                            }
                        </div>
                        {/* <div className="section-middle">
                            <div className="app-header-text app-header-text">
                                {WSManager.getProfile().aadhar_status == 1 ? AppLabels.AADHAAR_CARD_DETAIL : AppLabels.AadharVerification}
                            </div>
                        </div> */}
                        <div className="section-min section-right"></div>
                    </div>
                </div>

                <div className={manualVerification ? '' : 'fx-height'}>
                    {manualVerification ?
                        <AadharVerificationManual {...AadhaarProps} />
                        :
                        <AadharVerificationAutokyc {...AadhaarProps} />
                    }
                </div>
            </div>
        )
    }
}