import React from 'react';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import CustomHeader from '../../components/CustomHeader';
import { getStaticPageData } from '../../WSHelper/WSCallings';
import MetaComponent from '../MetaComponent';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { Utilities } from '../../Utilities/Utilities';

export default class DeleteAccount extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            pageData: {"page_title":AppLabels.DELETE_ACCOUNT,"page_content":""},
            is_visible: false
        }
    }
    componentDidMount() {
        var scrollComponent = this;
        document.addEventListener("scroll", function(e) {
            scrollComponent.toggleVisibility();
        });
        Utilities.setScreenName('deleteaccount')
        
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
        var page_alias = "delete_account";
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
                          <MetaComponent page="deleteaccount"/> 
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