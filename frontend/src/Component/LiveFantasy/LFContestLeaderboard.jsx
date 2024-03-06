import React from 'react';
import { Modal, Table } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';
import WSManager from '../../WSHelper/WSManager';
import * as WSC from "../../WSHelper/WSConstants";
import InfiniteScroll from 'react-infinite-scroll-component';
import ls from 'local-storage';

import { getContestLeaderboardLF } from '../../WSHelper/WSCallings';
var globalThis = null;

export default class LFContestLeaderborad extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            isLoaderShow: false,
            isLoadMoreLoaderShow: false,
            hasMore: true,
            leaderBoardData:this.props.leaderBoardData,
            pageNo: 1,
            page_size: 500,

        };

    }

    componentDidMount() {
        ls.set("isULF", false)
        //this.getContestLeaderboardCall()
    }

    getContestLeaderboardCall = async () => {
        this.setState({ isLoaderShow: true })

        let param = {
            "contest_id": this.props.updateMatchRank.contest_id,
            "type": 1,
            "page_size": this.state.page_size,
            "page_no": this.state.pageNo,
        }
        getContestLeaderboardLF(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })

            if (responseJson && responseJson.response_code == WSC.successCode) {
                // this.setState({ matchPlayerList: responseJson.data })
                this.setState({
                    leaderBoardData: this.state.pageNo == 1 ? responseJson.data : [...this.state.leaderBoardData, ...responseJson.data],

                    hasMore: responseJson.data.length === this.state.page_size,
                    pageNo: this.state.pageNo + 1,
                })
            }
           
        })

    }

    getPrizeAmount = (prize_data) => {
        let prize_detail = prize_data.prize_detail && this.isJsonString(prize_data.prize_detail) ? JSON.parse(prize_data.prize_detail) : prize_data.prize_detail

        let prizeAmount = this.getWinCalculation(prize_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span style={{color:"#ffffff"}} className="contest-prizes">
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div style={{color:'#ffffff',marginLeft:1}} className="contest-listing-prizes" ><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock',color:'#ffffff' }}> <img style={{height:18,width:18,marginRight:5,marginTop:-2}} className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AL.PRIZES
                }
            </React.Fragment>
        )


    }

     /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
      handleRefresh = () => {
        if (!globalThis.isLoaderShow) {
            globalThis.setState({ hasMore: false, pageNo: 1, isRefresh: true, AllLineUPData: {} }, () => {
                globalThis.getContestLeaderboardCall();                
               
            })
        }
    }
    onLoadMore() {
        if (!this.state.isLoaderShow && this.state.hasMore) {
            this.setState({ hasMore: false })
            this.getNewLeaderboard()
            if(this.state.status == 1){
            }
        }
    }
    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
            var amount = 0;
            if (lObj.max_value) {
                amount = parseFloat(lObj.max_value);
            } else {
                amount = parseFloat(lObj.amount);
            }
            if (lObj.prize_type == 3) {
                prizeAmount['is_tie_breaker'] = 1;
            }
            if (lObj.prize_type == 0) {
                prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
            } else if (lObj.prize_type == 2) {
                prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
            } else {
                prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
            }
        })
        return prizeAmount;
    }
    isTrophyShow =(item)=>{
        const {updateMatchRank } = this.props;
        let prize_detail = updateMatchRank.prize_detail && this.isJsonString(updateMatchRank.prize_detail) ? JSON.parse(updateMatchRank.prize_detail) : updateMatchRank.prize_detail

        let pd_length = prize_detail && prize_detail.length > 0 ? prize_detail.length : false;
        let isWinner = pd_length  ? parseInt(prize_detail[pd_length - 1].max) >= parseInt(item.game_rank) : false;
        return isWinner;
    }
    isJsonString= (str) => {
        try {
            console.log("true")
            JSON.parse(str);
        } catch (e) {
            console.log("false")

            return false;
        }
        return true;
    }
    render() {

        const { MShow, MHide,updateMatchRank } = this.props;
        const { leaderBoardData,isLoaderShow } = this.state;

        return (
            <Modal
                show={MShow}
                dialogClassName="custom-modal contest-leaderbrd-modal"
                className="center-modal"
            >
                <Modal.Header >
                    <div style={{color:"#ffffff"}} className='Cont-nm'>{AL.WIN + " "}{" "}{this.getPrizeAmount(updateMatchRank)}</div>
                    <div href="" onClick={MHide} className="close">
                        <i className="icon-close"></i>
                    </div>
                </Modal.Header>
                <Modal.Body>
                    <Table>
                    <InfiniteScroll
                                dataLength={leaderBoardData.length}
                                next={() => this.onLoadMore()}
                                hasMore={!this.state.isLoaderShow && this.state.hasMore}
                                scrollableTarget={'scrollableTarget'}
                                pullDownToRefreshThreshold={300}
                                refreshFunction={this.handleRefresh}
                                loader={
                                    this.state.isLoadMoreLoaderShow &&
                                    <h4 className='table-loader'>{AL.LOADING_MSG}</h4>
                                }
                                pullDownToRefreshContent={
                                    <h3 style={{ textAlign: 'center', fontSize: 14 }}>&#8595; {AL.PULL_DOWN_TO_REFRESH}</h3>
                                }
                                releaseToRefreshContent={
                                    <h3 style={{ textAlign: 'center', fontSize: 14 }}>&#8593; {AL.RELEASE_TO_REFRESH}</h3>
                                }>
                            <thead>
                                <tr>
                                    <th>{AL.RANK}</th>
                                    <th>{AL.USER}</th>
                                    <th>{AL.POINTS}</th>
                                </tr>
                            </thead>
                            <tbody>  {
                                leaderBoardData.length > 0 && leaderBoardData.map((item, idx) => {
                                    return (
                                        WSManager.getProfile().user_id == item.user_id ?
                                            <tr key={idx} className="own-row">
                                                <td className="rank">{item.game_rank}</td>
                                                <td className="user-info">
                                                    <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />

                                                    <div className="nm">{item.user_name}</div>
                                                </td>
                                                <td className="pts">{
                                                    this.isTrophyShow(item) && <i style={{marginRight:4}} className='icon-trophy icon-color'></i>
                                                    // <i className='icon-trophy icon-color'></i>
                                                }{item.total_score}</td>
                                            </tr>
                                            :
                                            <tr key={idx}>
                                                <td className="rank">{item.game_rank}</td>
                                                <td className="user-info">
                                                <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />
                                                <div className="nm">{item.user_name}</div>
                                                </td>
                                                <td className="pts">{
                                                    this.isTrophyShow(item) && <i style={{marginRight:4}} className='icon-trophy icon-color'></i>
                                                    // <i className='icon-trophy icon-color'></i>
                                                }{item.total_score}</td>
                                            </tr>

                                    )
                                })
                            }


                        </tbody>
                                    
                            </InfiniteScroll>
                       
                        
                    </Table>
                </Modal.Body>
            </Modal>
        );
    }
}