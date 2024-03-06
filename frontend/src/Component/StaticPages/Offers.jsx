import React from 'react';
import CustomHeader from '../../components/CustomHeader';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { getStaticPageData } from '../../WSHelper/WSCallings';
import { NoDataView } from '../../Component/CustomComponent';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import { Utilities } from '../../Utilities/Utilities';

export default class Offers extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            pageData: { "page_title": "Offers", "page_content": "" }
        }
    }
    componentDidMount() {
        Utilities.setScreenName('privacypolicy')
        
        this.getPageContentData()
    }
    getPageContentData = async () => {
        var page_alias = "offers";
        let param = {
            "page_alias": page_alias
        }

        var api_response_data = await getStaticPageData(param);
        if (api_response_data) {
            this.setState({
                pageData: api_response_data
            })
        }
    }
    render() {
        const HeaderOption = {
            back: this.props.history.length > 1,
            filter: false,
            title: this.state.pageData.page_title,
            hideShadow: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page transparent-header">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.offers.title}</title>
                            <meta name="description" content={MetaData.offers.description} />
                            <meta name="keywords" content={MetaData.offers.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="webcontainer-inner">
                            <div className="page-body">
                                {this.state.pageData && this.state.pageData.page_content 
                                    ?
                                    <div dangerouslySetInnerHTML={{ __html: this.state.pageData.page_content }}></div>
                                    :
                                    <NoDataView
                                        BG_IMAGE={Images.no_data_bg_image}
                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                        MESSAGE_1={AL.NO_FIXTURES_MSG1}
                                        MESSAGE_2={AL.NO_FIXTURES_MSG2}
                                        onClick_2={this.joinContest}
                                    />
                                }
                            </div>
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}