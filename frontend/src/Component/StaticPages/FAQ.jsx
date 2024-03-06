import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { getStaticPageData } from '../../WSHelper/WSCallings';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import { _Map } from '../../Utilities/Utilities';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';

export default class FAQ extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            activeKey: '',
            pageData: { "page_title": AppLabels.FAQS, "page_content": "" },
            AllCat: []
        }
    }

    componentDidMount() {
        Utilities.setScreenName('faqs')
        
        this.getPageContentData();
    }

    getPageContentData = async () => {
        var page_alias = "faq";
        let param = {
            "page_alias": page_alias
        }
        var api_response_data = await getStaticPageData(param);
        if (api_response_data) {
            this.setState({
                pageData: api_response_data,
                AllCat: api_response_data.all_category || []
            })
        }
    }

    handleSelect = (activeKey) => {
        if (activeKey == this.state.activeKey) {
            this.setState({ activeKey: '' });
        } else {
            this.setState({ activeKey });
        }
    }

    render() {
        const HeaderOption = {
            back: this.props.history.length > 1,
            filter: false,
            title: '',
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page static-page-new transparent-header web-container-fixed ">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.faqs.title}</title>
                            <meta name="description" content={MetaData.faqs.description} />
                            <meta name="keywords" content={MetaData.faqs.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="webcontainer-inner">
                            <div className="world-map-img">
                                <img src={Images.WORLD_MAP} alt='' />
                                <span className="page-text-center">{this.state.pageData.page_title}</span>
                            </div>
                            <div className="page-body">
                                {
                                    _Map(this.state.AllCat, (item, idx) => {
                                        return (
                                            <div key={item.category_name} className="html-view">
                                                <div className="page-title">{item.category_name}</div>
                                                {
                                                    _Map(item.questions, (obj, indx) => {
                                                        return (
                                                            <div key={obj.question_id} className={"ques-view" + (this.state.activeKey == obj.question_id ? ' active-q' : '')} onClick={() => this.handleSelect(obj.question_id)}>
                                                                <div className="ques-item"><span className="plus-minus"><i className={this.state.activeKey == obj.question_id ? "icon-remove" : "icon-plus-ic"} /></span>{obj.question}</div>
                                                                <div className="ans-item">{obj.answer}</div>
                                                            </div>
                                                        )
                                                    })
                                                }
                                            </div>
                                        )
                                    })
                                }
                            </div>
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}