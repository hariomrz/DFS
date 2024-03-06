import React from 'react';
import { changeLanguageString } from "../../helper/AppLabels";
import { withTranslation } from "react-i18next";
import { Utilities } from '../../Utilities/Utilities';
import LanguagePopup from "../../Modals/LanguagePopup";
import WSManager from "../../WSHelper/WSManager";
import { ALLOW_LANG } from '../../helper/Constants';

class SelectLanguage extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            showLanguagePopup: false,
            languageList: ALLOW_LANG,
            defaultLang: WSManager.getAppLang() || Utilities.getMasterData().default_lang
        }
    }

    componentDidMount() {
        if (WSManager.getAppLang() == null) {
            WSManager.setAppLang(this.state.defaultLang);
        }
        changeLanguageString();
    }

    handleChange = (selectedLang) => {
        if (this.props.i18n.language != selectedLang.value) {
            if (window.ReactNativeWebView) {
                let data = {
                    action: 'back',
                    locale:selectedLang.value,
                    targetFunc:'handleLanguageChange'
                }
                window.ReactNativeWebView.postMessage(JSON.stringify(data));
            }
            this.props.i18n.changeLanguage(selectedLang.value);
            Utilities.gtmEventFire('change_language', {
                selected_lang: selectedLang.value
            })
            WSManager.setAppLang(selectedLang.value);
            changeLanguageString();
            window.location.reload();
        }
    };

    LanguagePopupShow = () => {
        this.setState({
            showLanguagePopup: true
        });
    }
    /**
     * 
     * @description method to hide collection info model.
     */
    LanguagePopupHide = () => {
        this.setState({
            showLanguagePopup: false,
        });
    }
    render() {
        const {
            showLanguagePopup,
            languageList,
            defaultLang
        } = this.state;

        const { isBottomFixed, boxView } = this.props;

        return (
            <div>
                {languageList.length >= 2 &&
                    <div className={"language-wrapper" + (isBottomFixed ? ' language-btm-fixed' : '') + (boxView ? ' language-box-view' : '') + (languageList.length == 2 ? ' two-lang-wrap' : '')}>
                        <ul>
                            {
                                languageList && languageList.slice(0, 3).map((item, idx) => {
                                    return (
                                        <React.Fragment key={idx}>
                                            {idx < 2 &&
                                                <li className={item.value == defaultLang ? 'active' : ''}>
                                                    <a href
                                                        onClick={() => this.handleChange(item)}
                                                    >{item.label}</a>
                                                </li>
                                            }
                                            {
                                                idx === 2 &&
                                                <li>
                                                    <a href onClick={() => this.LanguagePopupShow()}>
                                                        <i className="icon-more-large rotate-90deg"></i>
                                                    </a>
                                                </li>
                                            }
                                        </React.Fragment>
                                    )
                                })
                            }
                        </ul>
                    </div>
                }
                {showLanguagePopup &&
                    <LanguagePopup {...this.props} IsLanguagePopupShow={showLanguagePopup} IsLanguagePopupHide={this.LanguagePopupHide} LanguageList={languageList} DefaultLanguage={defaultLang} />
                }
            </div>
        );
    }
}

export default withTranslation()(SelectLanguage)