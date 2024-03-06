import React from 'react';
import * as AppLabels from "../../helper/AppLabels";
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { getStaticPageData } from '../../WSHelper/WSCallings';
import MetaComponent from '../MetaComponent';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';

export default class TermsCondition extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            pageData: {"page_title":AppLabels.TERMS_CONDITION,"page_content":""},
            is_visible: false
        }
    }
    componentDidMount() {
        var scrollComponent = this;
        document.addEventListener("scroll", function(e) {
            scrollComponent.toggleVisibility();
        });
        Utilities.setScreenName('termsconditions')
        
        this.getPageContentData();        
    }

    toggleVisibility() {
        if (window.pageYOffset > 200) {
          this.setState({
            is_visible: true
          });
        } else {
          this.setState({
            is_visible: false
          });
        }
    }
    scrollToTop() {
        window.scrollTo({
          top: 0,
          behavior: "smooth"
        });
    }

    getPageContentData=async()=> {
        var page_alias = "terms_of_use";
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
            title: this.state.pageData.page_title,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page transparent-header web-container-fixed static-page-TC">
                        {/* <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.termsconditions.title}</title>
                            <meta name="description" content={MetaData.termsconditions.description} />
                            <meta name="keywords" content={MetaData.termsconditions.keywords}></meta>
                        </Helmet> */}
                          <MetaComponent page="termsconditions"/> 
                          <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        
                        <div className="webcontainer-inner">   
                        {
                            this.state.is_visible &&
                            <a className="fixed-scroll" href onClick={() => this.scrollToTop()}>
                                <i className="icon-left-arrow"></i>   
                            </a>
                        }
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