import React from 'react';
import CustomHeader from '../../components/CustomHeader';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import * as AppLabels from "../../helper/AppLabels";
import { getStaticPageData } from '../../WSHelper/WSCallings';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';


export default class HowToPlay extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            pageData: {"page_title":AppLabels.How_to_Play,"page_content":""}
        }
    }
    UNSAFE_componentWillMount() {
        Utilities.setScreenName('howtoplay')

        this.getPageContentData()
    }

    getPageContentData=async()=> {
        var page_alias = "how_it_works";
        let param = {
            "page_alias": page_alias
        }
        
        var api_response_data = await getStaticPageData(param);
        if(api_response_data){
            this.setState({
                pageData: api_response_data
            })
        }
    }
    
    render() {
        const HeaderOption = {
            back: this.props.history.length > 1,
            filter: false,
            title: AppLabels.HOW_TO_PLAY_FANTASY_SPORTS,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page transparent-header web-container-fixed">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.howtoplay.title}</title>
                            <meta name="description" content={MetaData.howtoplay.description} />
                            <meta name="keywords" content={MetaData.howtoplay.keywords}></meta>
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