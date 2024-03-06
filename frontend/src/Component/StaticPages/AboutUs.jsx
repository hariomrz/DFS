import React from 'react';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { getStaticPageData } from '../../WSHelper/WSCallings';
import MetaComponent from '../MetaComponent';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';

export default class AboutUs extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            pageData: { "page_title": AppLabels.ABOUT_US, "page_content": "" }
        }
    }
    componentDidMount() {
        this.getPageContentData();
    }

    getPageContentData = async () => {
        var page_alias = "about";
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

    UNSAFE_componentWillMount(){
        Utilities.setScreenName('aboutus')
    }
    render() {
        const HeaderOption = {
            back: this.props.history.length > 1,
            filter: false,
            title: this.state.pageData.page_title,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page transparent-header web-container-fixed">
                        {/* <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.aboutus.title}</title>
                            <meta name="description" content={MetaData.aboutus.description} />
                            <meta name="keywords" content={MetaData.aboutus.keywords}></meta>
                        </Helmet> */}
                        <MetaComponent page="aboutus"/> 
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="webcontainer-inner">
                            
                            <div className="page-body">
                                <div dangerouslySetInnerHTML={{ __html: this.state.pageData.page_content }}></div>
                            </div>
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}