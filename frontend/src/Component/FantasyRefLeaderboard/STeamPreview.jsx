import React from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { getStockUserLineup } from '../../WSHelper/WSCallings';
import StockItem from '../../Component/StockFantasy/StockItem';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { MomentDateComponent} from '../../Component/CustomComponent';

class STeamPreview extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            show: false,
            openTeam: '',
            teamPlayerData: {},
            CollectionData: '',
            team_name: '',
            isFrom: '',
            userName: ''
        };


    }

    componentDidMount() {
        this.setState({ openTeam: this.props.openTeam, CollectionData: this.props.CollectionData, isFrom: this.props.isFrom, userName: this.props.userName }, () => {
            if(this.props.isFrom === 'point'){
                this.parseTeamData({ lineup: this.props.openTeam })
            }else if (this.props.isFrom === 'roster') {
                this.parseTeamData({ lineup: this.props.preTeam })
            } else {
                this.getTeamPlayers(this.state.openTeam)
            }
        })
    }

    getTeamPlayers = (item) => {

        let param = {
            "lineup_master_id": item.lineup_master_id,
            "collection_id": this.state.CollectionData.collection_master_id,
        }
        getStockUserLineup(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                this.parseTeamData(responseJson.data)
            }
        })
    }

    parseTeamData = (data) => {
        let upArry = [];
        let downArray = [];
        _Map((data.lineup || []), (item) => {
            let act = this.state.isFrom === 'point' ? item.type : item.action;
            if (parseInt(act || '0') === 2) {
                downArray.push(item)
            } else {
                upArry.push(item)
            }
        })

        this.setState({
            team_name: data.team_name,
            teamPlayerData: {
                up_stock: upArry,
                down_stock: downArray
            }
        })
    }

    render() {
        const { isViewAllShown, onViewAllHide, status, total_score,StockSettingValue, isTeamPrv } = this.props;
        const { teamPlayerData, openTeam, isFrom, team_name, userName, CollectionData } = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={isViewAllShown} onHide={onViewAllHide} bsSize="large" className="stock-team-view-modal">
                            <Modal.Header>
                                <Modal.Title>
                                    <a href onClick={onViewAllHide} className="modal-close">
                                        <i className="icon-close"></i>
                                    </a>
                                    <div className="name-container">
                                        <div className="team-name">
                                            {
                                                isFrom === "preview"
                                                    ?
                                                    team_name
                                                    :
                                                    isFrom === "point"
                                                        ?
                                                        userName : AL.TEAM_PREVIEW.replace(AL.Team, AL.PORTFOLIO)
                                            }
                                        </div>
                                        {/* {isFrom === "point" && <div className="contests-detail">{status == 1 ? AL.LIVE : AL.COMPLETED}{CollectionData.scheduled_date ? 
                                        <> | <MomentDateComponent data={{ date: CollectionData.scheduled_date, format: "DD MMM hh:mm a" }} /> <MomentDateComponent data={{ date: CollectionData.end_date, format: "- hh:mm a" }} /> </> : ''}
                                        </div>} */}
                                    </div>

                                </Modal.Title>
                            </Modal.Header>
                            <Modal.Body style={isFrom === 'point' ? {minHeight: '100%',paddingBottom: '60px'} : {}}>
                                {(teamPlayerData.up_stock || []).length > 0 && <div className="player-list-container">
                                    <div className="item-header">
                                        <span>{AL.BUY_STOCK} <i className="icon-stock_up" /></span>
                                    </div>
                                    {
                                        (teamPlayerData.up_stock || []).map((item, index) => {
                                            return (
                                                <StockItem type={this.props.type ? this.props.type:0} isPreview={true} isFrom={isFrom === 'roster' ? '' : isFrom} key={item.stock_id + index} item={item} openTeam={openTeam} StockSettingValue={StockSettingValue} isTeamPrv={isTeamPrv || false} />
                                            )
                                        })
                                    }
                                </div>}
                                {
                                    (teamPlayerData.down_stock || []).length > 0 && <div className="player-list-container down">
                                        <div className="item-header">
                                            <span>{AL.SELL_STOCK} <i className="icon-stock_down" /></span>
                                        </div>
                                        {
                                            (teamPlayerData.down_stock || []).map((item, index) => {
                                                return (
                                                    <StockItem  type={this.props.type ? this.props.type:0} isPreview={true} isFrom={isFrom === 'roster' ? '' : isFrom} key={item.stock_id + index} item={item} openTeam={openTeam} down={true} StockSettingValue={StockSettingValue} isTeamPrv={isTeamPrv || false} />
                                                )
                                            })
                                        }
                                    </div>
                                }
                            {isFrom === 'point' && this.props.type !=5 &&  <button className="btn-primary bottom btn btn-primary-bottom-stk pts" ><span>{AL.TOTAL_POINTS}</span><span style={{fontSize:20}}>{total_score}</span></button>}
                            </Modal.Body>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default STeamPreview;