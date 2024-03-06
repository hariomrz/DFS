import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { getStaticPageData } from '../../WSHelper/WSCallings';
import { DARK_THEME_ENABLE } from '../../helper/Constants';

export default class Partners extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            pageData: { "page_title": "Partners", "page_content": "" }
        }
    }
    componentDidMount() {
        this.getPageContentData()
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
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.partners.title}</title>
                            <meta name="description" content={MetaData.partners.description} />
                            <meta name="keywords" content={MetaData.partners.keywords}></meta>
                        </Helmet>
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