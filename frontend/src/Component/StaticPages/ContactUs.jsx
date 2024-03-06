import React, {lazy, Suspense} from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities } from '../../Utilities/Utilities';
import { getStaticPageData } from '../../WSHelper/WSCallings';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import { DARK_THEME_ENABLE } from '../../helper/Constants';
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

export default class ContactUs extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            pageData: { "page_title": AppLabels.CONTACT_US, "page_content": "" },
            pageTitle: '',
            customData: ''
        }
    }
    componentDidMount() {
        Utilities.setScreenName('ContactUs')

        this.getPageContentData();
    }

    getPageContentData = async () => {
        var page_alias = "contact_us";
        let param = {
            "page_alias": page_alias
        }

        var api_response_data = await getStaticPageData(param);
        if (api_response_data) {
            this.setState({
                pageData: api_response_data,
                pageTitle: api_response_data.page_title,
                customData: api_response_data.custom_data
            })
        }
    }

    openURL = (target_url) => {
        if (window.ReactNativeWebView) {
            let data = {
                action: 'predictionLink',
                targetFunc: 'predictionLink',
                type: 'link',
                url: target_url,
                detail: { url: target_url }
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        } else {
            window.open(target_url, "_blank");
        }
    }

    OpenAppURL = (target_url) => {
        // if (!window.ReactNativeWebView) {
            window.location.href = target_url;
        // }
    }

    render() {
        const HeaderOption = {
            back: this.props.history.length > 1,
            filter: false,
            title: '',
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        var settings = {
            infinite: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: false,
            centerPadding: '95px 0 10px',
            initialSlide: 0,
            className: "img-slide",
            centerMode: true,
            autoplay: true,
            autoplaySpeed: 4000,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1,
                    }
                },
                {
                    breakpoint: 414,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '56px 0 10px',
                    }
                }
            ]
        };

        const { customData, pageData, pageTitle } = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page static-page-new transparent-header web-container-fixed">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.ContactUs.title}</title>
                            <meta name="description" content={MetaData.ContactUs.description} />
                            <meta name="keywords" content={MetaData.ContactUs.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="webcontainer-inner">
                            <div className="world-map-img">
                                <img src={Images.WORLD_MAP} alt='' />
                                <span className="page-text-center">{AppLabels.WE_WHOULD_LOVE}</span>
                            </div>
                            {
                                customData && <div className="page-body">
                                    <div className="html-view">
                                        <div className="page-title">{pageTitle ? pageTitle : AppLabels.CONTACT_US}</div>
                                        <div className="contact-us-page-body" dangerouslySetInnerHTML={{ __html: pageData.page_content }}></div>
                                    </div>
                                    <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                                        {
                                            customData && customData.photos && customData.photos.map((item) => {
                                                return (
                                                    <img style={{ paddingRight: 10, paddingLeft: 10 }} key={item} src={Utilities.getCMSURL(item)} alt="" />
                                                );
                                            })
                                        }
                                    </ReactSlickSlider></Suspense>
                                    <div className="html-view1">
                                        <div className="contact-details">
                                            {
                                                customData.email && <div className="contact-item" >
                                                    <span style={{ minWidth: 80 }}><i className="icon-common-shape" /><i className="icon-mail-ic" /></span>
                                                    <span onClick={() => this.OpenAppURL(`mailto:${customData.email}`)} className="contact-text">{customData.email}</span>
                                                </div>
                                            }
                                            {
                                                customData.phone1 && <div className="contact-item"  >
                                                    <span style={{ minWidth: 80 }}><i className="icon-common-shape" /><i className="icon-phone-ic" /></span>
                                                    <span><span onClick={() => this.OpenAppURL(`tel:${customData.phone1}`)} className="contact-text">{customData.phone1}</span>{customData.phone2 && <span onClick={() => this.OpenAppURL(`tel:${customData.phone2}`)} className="contact-text">{', ' + customData.phone2}</span>}</span>
                                                </div>
                                            }
                                            {
                                                customData.address && <div className="contact-item" >
                                                    <span style={{ minWidth: 80 }}><i className="icon-common-shape" /><i className="icon-location-ic" /></span>
                                                    <span onClick={() => this.openURL('https://www.google.com/maps/place/' + customData.address)} className="contact-text">{customData.address}</span>
                                                </div>
                                            }
                                        </div>
                                        <div className="follow-container">
                                            {(customData.whatsapp || customData.facebook || customData.instagram || customData.twitter || customData.linkdin) && <span className="span-follow">{AppLabels.FOLLOW_US}</span>}
                                            <div className="follow-links-c">
                                                {customData.whatsapp && <i className="icon-whatsapp" onClick={() => this.openURL(customData.whatsapp)} />}
                                                {customData.facebook && <i className="icon-fb-empty" onClick={() => this.openURL(customData.facebook)} />}
                                                {customData.instagram && <i className="icon-instagram" onClick={() => this.openURL(customData.instagram)} />}
                                                {customData.twitter && <i className="icon-twitter-empty" onClick={() => this.openURL(customData.twitter)} />}
                                                {customData.linkdin && <i className="icon-linkedin" onClick={() => this.openURL(customData.linkdin)} />}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            }
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}