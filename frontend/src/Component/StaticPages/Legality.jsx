import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { getStaticPageData } from '../../WSHelper/WSCallings';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import * as AppLabels from "../../helper/AppLabels";
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';


export default class Legality extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            pageData: { "page_title": AppLabels.LEGALITY, "page_content": "" },
            pageTitle: ''
        }
    }
    componentDidMount() {
        Utilities.setScreenName('ContactUs')
        this.getPageContentData();
    }

    getPageContentData=async()=> {
        var page_alias = "legality";
        let param = {
            "page_alias": page_alias
        }
        
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
            title: this.state.pageTitle ? this.state.pageTitle : AppLabels.LEGALITY,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page transparent-header web-container-fixed esport-wrap">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.termsconditions.title}</title>
                            <meta name="description" content={MetaData.termsconditions.description} />
                            <meta name="keywords" content={MetaData.termsconditions.keywords}></meta>
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
