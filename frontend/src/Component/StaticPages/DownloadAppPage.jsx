import React, { Component } from 'react'

import CustomLoader from '../../helper/CustomLoader';
import Images from '../../components/images'
import DownloadButton from '../../Component/DownloadButton'
import { getSourceUrl } from "../../WSHelper/WSCallings";
import * as WSC from "../../WSHelper/WSConstants";
import queryString from 'query-string';
import { Utilities } from '../../Utilities/Utilities';
import * as Constants from "../../helper/Constants";

export default class DownloadAppPage extends Component {
    constructor(props) {
        super(props);
        this.state = {
            posting: true,
            StockPE: false,
            sportsList: Utilities.getMasterData().sports_hub
        }
    }
    
    componentDidMount() {
        let url = this.props.location.search;
        let urlParams = queryString.parse(url);
        
        this.checkStkOnly()
        if(urlParams.surl) {
            getSourceUrl({
                "surl": urlParams.surl
            }).then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    this.setState({
                        posting: false 
                    });
                    Utilities.setCpSession(responseJson.data)
                } else {
                    this.setState({
                        posting: false 
                    });
                }
            })
        } else {
            this.setState({
                posting: false 
            });
        }
    }

    checkStkOnly=()=>{
        let tmpAry = []
        for(var item of this.state.sportsList){
            tmpAry.push(item.game_key)
        }
        let StockPE = tmpAry && (
                (tmpAry.length == 1 && tmpAry.includes(Constants.GameType.StockFantasyEquity)) || 
                (tmpAry.length == 1 && tmpAry.includes(Constants.GameType.StockFantasy)) || 
                // (tmpAry.length == 1 && tmpAry.includes('allow_stock_predict')) || 
                (tmpAry.length == 2 && tmpAry.includes(Constants.GameType.StockFantasy && Constants.GameType.StockFantasyEquity))  
                // || (tmpAry.length == 2 && tmpAry.includes(Constants.GameType.StockFantasy && 'allow_stock_predict')) || 
                // (tmpAry.length == 2 && tmpAry.includes('allow_stock_predict' && Constants.GameType.StockFantasyEquity)) || 
                // (tmpAry.length == 3 && tmpAry.includes(Constants.GameType.StockFantasy && Constants.GameType.StockFantasyEquity && 'allow_stock_predict'))
                ) ? true : false;
        this.setState({
            StockPE : StockPE
        })
    }
    
    render() {
        const { posting,StockPE } = this.state;
        
        return (
            <div className="web-container with-bg-white">
                <div className="app-header-style">
                    <div className="app-header-text">Download App</div>
                </div>
                <div className="download-app-body">
                    <img alt="" src={Images.BRAND_LOGO_FULL} className="logo-lg" />
                    {
                        StockPE ?
                        <div className="fantasy-expirence-text">The Best Stock Fantasy Experience</div>
                        :
                        <div className="fantasy-expirence-text">The Best Fantasy Sports Experience</div>
                    }
                    {/* <img id="apkLink" onclick="myFunction('REACT_APK_PATH')" alt="" src={Images.DOWNLOAD_APP_BTN}
                        className="logo-lg" style={{'margin-top': '100px', cursor: 'pointer'}} /> */}
                    <div className="download-app-button-wrap">
                        {
                            posting ?
                            <CustomLoader />
                            :
                            <DownloadButton isFull />
                        }
                    </div>
                </div>
            </div>
        )
    }
}
