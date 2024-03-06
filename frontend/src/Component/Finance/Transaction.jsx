import React from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map, _debounce, _filter } from '../../Utilities/Utilities';
import { Helmet } from "react-helmet";
import { getTranscationHistory } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import Images from '../../components/images';
import InfiniteScroll from 'react-infinite-scroll-component';
import CustomHeader from '../../components/CustomHeader';
import MetaData from "../../helper/MetaData";
import * as AppLabels from "../../helper/AppLabels";
import {TransactionList, NoDataView} from "../CustomComponent";
import * as Constants from "../../helper/Constants";

var transactionData = {};
export default class Transaction extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            hasMore: false,
            transactionHistoryList: [],
            isLoaderShow: false,
            offset: 1,
            pageSize: 20,
            ShimmerList: [1, 2, 3, 4, 5],
            activeTab: '',
            windowWidth:window.innerWidth > 550 ? 550 : window.innerWidth,
            isFromNotification: (this.props.location && this.props.location.state) ? (this.props.location.state.from == 'notification' ? true : false) : false,
             navList: [
                {
                    'lb': AppLabels.ALL,
                    'src': '',
                    'bns': '',
                    'coin': ''
                },
                {
                    'lb': AppLabels.WINNINGS,
                    'src': '3',
                    'bns': '',
                    'coin': ''
                },
                {
                    'lb': AppLabels.DEPOSIT,
                    'src': '7',
                    'bns': '',
                    'coin': ''
                },
                // {
                //     'lb': AppLabels.WITHDRAW,
                //     'src': '8',
                //     'bns': '',
                //     'coin': ''
                // },
                {
                    'lb': AppLabels.BONUS,
                    'src': '',
                    'bns': '1',
                    'coin': ''
                }
            ]
        }
    }

    componentDidMount() {
        window.addEventListener('resize', (event)=>{
            this.setState({
                windowWidth:window.innerWidth > 550 ? 550 : window.innerWidth,
            })            
          });
    }
    
    UNSAFE_componentWillMount() {
        var tmpArray = this.state.navList;
        if (Utilities.getMasterData().a_coin == "1") {
            tmpArray = [
                ...tmpArray, ...[{
                    'lb': AppLabels.COINS,
                    'src': '',
                    'bns': '',
                    'coin': '1'
                }]
            ]
        }
        if (Constants.OnlyCoinsFlow == 1 || Constants.OnlyCoinsFlow == 2) {
            tmpArray = _filter(tmpArray, (obj) => {
                return obj.lb != AppLabels.WITHDRAW && obj.lb != AppLabels.DEPOSIT
            });
        }
        if (Constants.OnlyCoinsFlow == 1) {
            tmpArray = _filter(tmpArray, (obj) => {
                return obj.lb != AppLabels.BONUS
            });
        }
        this.setState({
            navList: tmpArray
        })

        Utilities.scrollToTop()
        this.setLocationStateData()
        Utilities.setScreenName('transactions')
    }

    componentWillUnmount() {
        transactionData = {}
        window.removeEventListener('resize',()=>{});
        if(this.state.isFromNotification){
            this.callTransactionHistoryApi('', '', '')

        }
    }

    scrollToBottom = () => {
        let elm = document.getElementsByClassName("active");
        if (elm.length > 0 && elm[0].localName === "li") {
            elm[0].scrollIntoView();
        }
    }

    setLocationStateData = () => {
        if (this.props.location && this.props.location.state) {
            this.setState({ activeTab: this.props.location.state.tab || AppLabels.ALL }, () => {
                if (this.state.activeTab === AppLabels.COINS) {
                    this.callTransactionHistoryApi('', '', 1);
                    this.scrollToBottom();
                } else {
                    this.callTransactionHistoryApi('', '', '')
                }
            });
        }
    }

    onTabChange = _debounce((item) => {
        this.setState({ offset: 1, activeTab: item.lb }, () => {
            this.callTransactionHistoryApi(item.src, item.bns, item.coin)
        })
    }, 100)


    callTransactionHistoryApi(source, onlyBonus, onlyCoins) {
        let dataKey = source + onlyBonus + onlyCoins + this.state.offset + this.state.activeTab;
        if (transactionData[dataKey]) {
            this.parseResponseData(transactionData[dataKey]);
        }
        else {
            let param = {
                "page_no": this.state.offset,
                "page_size": this.state.pageSize,
                "only_bonus": onlyBonus, 
                "only_coins": onlyCoins, 
                "only_winning": this.state.activeTab === AppLabels.WINNINGS ? 1 : 0, 
                "only_real": this.state.activeTab === AppLabels.DEPOSIT ? 1 : 0, 
                "source": source 
            }

            this.setState({ isLoaderShow: true })
            getTranscationHistory(param).then((responseJson) => {
                this.setState({
                    isLoaderShow: false,
                })
                if (responseJson.response_code == WSC.successCode) {
                    transactionData[dataKey] = responseJson.data;
                    this.parseResponseData(responseJson.data);
                }
            })
        }
    }

    parseResponseData(data) {
    
        if (this.state.offset === 1) {
            this.setState({
                offset: this.state.offset + 1,
                transactionHistoryList: data,
                hasMore: data.length === this.state.pageSize
            })
        }
        else {
            this.setState({
                offset: this.state.offset + 1,
                transactionHistoryList: [...this.state.transactionHistoryList, ...data],
                hasMore: data.length === this.state.pageSize
            });
        }
    }

    fetchMoreData = (item) => {
        if (this.state.activeTab === item.lb) {
            this.callTransactionHistoryApi(item.src, item.bns, item.coin)
        }
    }
    render() {
        const { hasMore } = this.state
        const HeaderOption = {
            back: true,
            title: AppLabels.TRANSACTIONS,
            hideShadow: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true
        }

        const { transactionHistoryList, isLoaderShow } = this.state;
        var activeSTIDx = 0;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed trans-web-container">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.transactions.title}</title>
                            <meta name="description" content={MetaData.transactions.description} />
                            <meta name="keywords" content={MetaData.transactions.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <Tab.Container id="tabs-with-dropdown" defaultActiveKey={this.state.activeTab} className="default-tab">
                            <Row className="clearfix">
                                <Col sm={12}>
                                    <Nav bsStyle="tabs">
                                        {
                                            _Map(this.state.navList, (item, idx) => {
                                                if(item.lb == this.state.activeTab){
                                                    activeSTIDx = idx;
                                                }
                                                return <NavItem eventKey={item.lb} onClick={() => this.onTabChange(item)} key={idx} >{item.lb}</NavItem>

                                            })
                                        }
                                        <span style={{ width: 'calc(100% / ' + this.state.navList.length + ')',minWidth:90, left: ((this.state.windowWidth/this.state.navList.length) < 90 ? (90 * activeSTIDx) : (this.state.windowWidth/this.state.navList.length * activeSTIDx)) + 'px' }} className="active-nav-indicator trans"></span>
                                    </Nav>
                                </Col>
                                <Col sm={12}>
                                <div className="transaction-list">
                                <div className="trans-wrap">
                                    <div className="trans-header-wrap">
                                        <div className="trans-head"></div>
                                        <div className="trans-head">
                                            <span>
                                                <i className="icon-remove"></i>
                                            </span>
                                        </div>
                                        <div className="trans-head">
                                            <span>
                                                <i className="icon-plus"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <Tab.Content animation>
                                        {
                                            _Map(this.state.navList, (item, key) => {
                                                return (
                                                    <Tab.Pane eventKey={item.lb} key={key + item.lb}>
                                                            <InfiniteScroll
                                                                dataLength={transactionHistoryList.length}
                                                                next={()=>this.fetchMoreData(item)}
                                                                hasMore={hasMore}
                                                                scrollableTarget='trans-list'
                                                                scrollThreshold={'50px'}
                                                            >
                                                                {transactionHistoryList != '' ? 
                                                                    <TransactionList transactionHistoryList={transactionHistoryList} selectedTAB={this.state.activeTab} id="trans-list"/>
                                                                    :
                                                                    !isLoaderShow ? <NoDataView 
                                                                        BG_IMAGE={Images.no_data_bg_image}
                                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                                        MESSAGE_1={AppLabels.NO_DATA_FOUND}
                                                                        MESSAGE_2={''}
                                                                    />
                                                                    : null
                                                                }
                                                            </InfiniteScroll>
                                                        </Tab.Pane>
                                                    )
                                                })

                                        }
                                    </Tab.Content>
                                    </div>
                                    </div>
                                </Col>
                            </Row>
                        </Tab.Container>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}