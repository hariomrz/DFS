import React from 'react';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { getStaticPageData } from '../../WSHelper/WSCallings';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';

export default class ResponsibleGaming extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            pageData: { "page_title": AppLabels.RESPONSIBLE_GAMING, "page_content": "" },
            pageTitle: ''
        }
    }
    componentDidMount() {
        Utilities.setScreenName('ContactUs')
        
        this.getPageContentData();
    }

    getPageContentData=async()=> {
        var page_alias = "responsible";
        let param = {
            "page_alias": page_alias
        }
        console.log('page_alias',page_alias)
        
        var api_response_data = await getStaticPageData(param);
        if(api_response_data){
            console.log('api_response_data',api_response_data)
            this.setState({
                pageData: api_response_data,
                pageTitle: api_response_data.page_title
            })
        }
    }
    render() {
        const HeaderOption = {
            back: this.props.history.length > 1,
            filter: false,
            title: this.state.pageTitle ? this.state.pageTitle : AppLabels.RESPONSIBLE_GAMING,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page transparent-header web-container-fixed">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.ContactUs.title}</title>
                            <meta name="description" content={MetaData.ContactUs.description} />
                            <meta name="keywords" content={MetaData.ContactUs.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="webcontainer-inner">
                            <div className="page-body">                            
                                <div dangerouslySetInnerHTML={{__html: this.state.pageData.page_content}}></div>
                            </div>
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}