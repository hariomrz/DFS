import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities } from '../../Utilities/Utilities';
import { getAffilateUserSummary, getTdsDocument } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../CustomComponent';
import WSManager from "../../WSHelper/WSManager";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import CustomLoader from '../../helper/CustomLoader';
import _ from 'lodash';
import app_config from "../../InitialSetup/AppConfig";
import ViewTDSFileModal from "./ViewTDSFileModal"
class TDSDashboard extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            profileDetail: WSManager.getProfile(),
            userAffiliateData: '',
            TDSDOCLIST: [],
            PNO: 1,
            PSIZE: 20,
            HMORE: false,
            ISLOAD: false,
            isLoading: false,
            total: '',
            fy: '',
            showTdsModal: false,
            selectedItem: ""
        }
    }

    componentDidMount() {
        this.getList()
        Utilities.setScreenName('TDSDashboard')
    }



    downloadFile = (item) => {
        try {
            let pdfUrl = `${app_config.s3.BUCKET}upload/tds/${item}`
            Utilities.downloadFile(pdfUrl)
        } catch (error) {
            console.log(error);
        }
    }

    exportTDSReport = () => {
        var query_string = '';
        var export_url = 'user/finance/get_tds_report?';
        Utilities.exportFunction(query_string, export_url)
    }

    getList() {
        const { PNO, PSIZE, TDSDOCLIST, profileDetail } = this.state;

        this.setState({ ISLOAD: true });
        getTdsDocument('').then((responseJson) => {
            this.setState({ ISLOAD: false });
            if (responseJson.response_code === WSC.successCode) {
                let listTmp = responseJson.data.result || [];
                this.setState({
                    TDSDOCLIST: PNO == 1 ? listTmp : [...TDSDOCLIST, ...listTmp],
                    HMORE: listTmp.length >= PSIZE ? true : false,
                    PNO: listTmp.length >= PSIZE ? PNO + 1 : PNO,
                    total: responseJson.data.total,
                    fy: responseJson.data.fy
                })
            }
        })
    }



    hideBecomeAM = () => {
        this.setState({
            showTdsModal: false
        })
    }

    showTdsModal = (item) => {
        this.setState({
            selectedItem: item,
            showTdsModal: true
        })
    }

    render() {
        const HeaderOption = {
            back: true,
            infoIcon: true,
            title: AppLabels.TDS_DASHBOARD,
            hideShadow: true,
            isTdsText:true,
            isPrimary:true
        }

        const { fy, total, TDSDOCLIST, ISLOAD, HMORE, isLoading } = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed wallet-wrapper">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.TDSDashboard.title}</title>
                            <meta name="description" content={MetaData.TDSDashboard.description} />
                            <meta name="keywords" content={MetaData.TDSDashboard.keywords}></meta>
                        </Helmet>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        {
                            isLoading && <CustomLoader />
                        }
                        {!isLoading && <>
                            <div className='tds-wrapper'>
                                {!_.isEmpty(TDSDOCLIST)&&<>
                                    <div className='tds-report-block-csv'>
                                        <div>
                                            <h4 className='csv-head'>{AppLabels.DOWNLOAD_TDS_REPORT} <span>({AppLabels.CSV})</span></h4>
                                            <span className='csv-sub-head'>{AppLabels.FY} {fy}</span>
                                        </div>
                                        <div className='csv-icon' onClick={() => this.exportTDSReport()}><i className='icon-download-csv'></i></div>
                                    </div>
                                    <div className='tds-certificate-head'>
                                        <div className='certf-head'>{AppLabels.TDS_CERTIFICATE}</div>
                                        <div className='certf-count'>
                                            <span>{AppLabels.CERTIFICATES_FOUND}</span>
                                            <h4>{total}</h4>
                                        </div>
                                    </div>
                                    <div className='tds-list'>
                                        <div className='tds-list-head'>
                                            <div className='s-no'>{AppLabels.S_NO}</div>
                                            <div>{AppLabels.DOC_INFO}</div>
                                            <div className='shift-r'>{AppLabels.ACTION}</div>
                                        </div>
                                        <div className='tds-list-body'>

                                            {
                                                _.map(TDSDOCLIST, (item, indx) => {
                                                    return (
                                                        <div className='tds-list-row' key={indx}>
                                                            <div className='count-show'><span>{indx + 1}</span></div>
                                                            <div className='tds-doc-info'>
                                                                <h4>{item.file_name}</h4>
                                                                <p>{AppLabels.UPLOADED_ON}: <span><MomentDateComponent data={{ date: item.date_added, format: "DD/MM/YY" }} /></span>  </p>
                                                                <p>{AppLabels.FY} {item.fy} | {item.gov_id}</p>
                                                            </div>
                                                            <div className='tds-action'>
                                                                {/* <i className='icon-view-eye' onClick={() => this.showTdsModal(item)}></i> */}
                                                                <i onClick={() => this.downloadFile(item.file_name)} className='icon-download-pdf'></i>
                                                            </div>
                                                        </div>
                                                    )
                                                })
                                            }


                                        </div>
                                    </div>
                                </>}
                                {(_.isEmpty(TDSDOCLIST) && !ISLOAD) &&
                                    <div className='no-data-container tds-no-data'>
                                        <img src={Images.NO_DATA_SHADE} alt="" />
                                        <h3>{AppLabels.RECORDS_NOT_FOUND}</h3>
                                    </div>}

                                <div className='tds-support-txt'>
                                    <p>{AppLabels.PAN_NOT_UPDATED}</p>
                                    <p>
                                        {AppLabels.MAIL_TO} <a href={`mailto:${Utilities.getMasterData().support_id}`}>{Utilities.getMasterData().support_id}</a> {AppLabels.FOR_ADDING_OR_REVISING_PAN_DETAILS}</p>
                                </div>

                            </div>
                        </>}
                        {
                            this.state.showTdsModal && <ViewTDSFileModal {...this.props} preData={{
                                mShow: this.state.showTdsModal,
                                mHide: this.hideBecomeAM,
                                selectedItem: this.state.selectedItem,
                                downloadFile: this.downloadFile
                            }} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

export default TDSDashboard;