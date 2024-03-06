import React, { Component, Fragment } from "react";
import { Button, Row, Col, FormGroup, Input, InputGroup, Card, CardBody, Tooltip, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import Select from 'react-select';
import LS from 'local-storage';
import _ from 'lodash';
import Images from '../../components/images';
import moment from 'moment';
import { notify } from 'react-notify-toast';
import Slider from "react-slick";
import HF from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";

class MiniLeagueDetail extends Component {

    constructor(props) {
        super(props);
        this.state = {
            posting: false,
            mini_league_uid: (this.props.match.params.mini_league_uid) ? this.props.match.params.mini_league_uid : '',
            miniLeagueDetail:[],
            miniLeagueLeaderboard:[],
            MerchandiseList:[],
            PrizeDetail:[],
        };
    }

    componentDidMount() {
    
        this.GetMiniLeagueDetail();
    }

    GetMiniLeagueDetail = () => {
        this.setState({ posting: true })
        WSManager.Rest(NC.baseURL + NC.GET_MINILEAGUE_DETAIL, { "sports_id": this.state.selected_sport,"mini_league_uid":this.state.mini_league_uid}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;
                this.setState({
                    posting: false,
                    miniLeagueDetail:responseJson,
                    MerchandiseList:responseJson.merchandise,
                    PrizeDetail:responseJson.prize_distibution_detail

                })
                this.GetMiniLeagueLeaderboard();
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            this.setState({ posting: false })
        }).catch((e) => {
            this.setState({ posting: false })
        })
    }
    GetMiniLeagueLeaderboard = () => {
        this.setState({ posting: true })
        WSManager.Rest(NC.baseURL + NC.GET_MINILEAGUE_LEADERBOARD, { "sports_id": this.state.selected_sport,"mini_league_uid":this.state.mini_league_uid}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;
                this.setState({
                    posting: false,
                    miniLeagueLeaderboard:responseJson
                })
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            this.setState({ posting: false })
        }).catch((e) => {
            this.setState({ posting: false })
        })
    }

    render() {
        const { miniLeagueDetail, miniLeagueLeaderboard, MerchandiseList, PrizeDetail} = this.state;
        const settings = {
            dots: false,
            infinite: false,
            speed: 500,
            slidesToShow: 4,
            slidesToScroll: 1,
            arrows:false,            
          };
        return (
            <div className="animated fadeIn mini-leaguedetail">
            <Row>
                <Col md={6}>
                    <h2 className="mini-league-name h2-cls">{miniLeagueDetail.mini_league_name}</h2>
                    <span >
                    {/* <MomentDateComponent data={{ date: miniLeagueDetail.scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                    {HF.getFormatedDateTime(miniLeagueDetail.scheduled_date, "D-MMM-YYYY hh:mm A")}

                    </span>
                </Col>
                <Col md={6}>
                <div className="contest-tempalte-wrapper">
                    {(miniLeagueDetail.title)?'Title ':''}<h6 className="mini-league-name">{miniLeagueDetail.title}</h6>
                </div>   
                    <label className="backtofixtures pull-right" onClick={() => this.props.history.push({ pathname: '/game_center/DFS', search: '?fixtab=2' })}> {'<'} Back to List</label>
                
                </Col>
            </Row>
            <Row>
                <Col className="heading-box">
                    <div className="contest-tempalte-wrapper">
                        <h3 className="h3-cls">Fixture</h3>
                    </div>
                </Col>
            </Row>
            <div className="border-bottom mb-4"></div>
            <Row>
            <Col lg={12}>
                  <Slider {...settings}>
                                      
                      {
                      !_.isEmpty(miniLeagueDetail) 
                      ?
                        _.map(miniLeagueDetail.season_list, (fixtureitem, fixtureindex) => {
                          if(typeof fixtureitem.season_game_uid == 'undefined') return false;
                            return (  
                        <Card className="livecard">
                            <div className="carddiv" >
                              <Col>
                              <img className="cardimg" src={NC.S3+NC.FLAG+fixtureitem.home_flag}></img>
                              </Col>
                              <Col>
                              <h4 className="livcardh3">{(fixtureitem.home) ? fixtureitem.home : 'TBA'} VS {(fixtureitem.away) ? fixtureitem.away : 'TBA'}</h4>
                                    <h6 className="livcardh6dfs">
                                        {/* <MomentDateComponent data={{ date: fixtureitem.scheduled_date_time, format: "D-MMM-YYYY hh:mm A" }} /> */}
                    {HF.getFormatedDateTime(fixtureitem.scheduled_date_time, "D-MMM-YYYY hh:mm A")}

                                    </h6>
                                    {<h6 className="livcardh6dfs">{fixtureitem.league_abbr} -{fixtureitem.format_str} </h6>}
                              </Col>
                              <Col>
                              <img className="cardimg" src={NC.S3+NC.FLAG+fixtureitem.away_flag}></img>
                              </Col>
                            </div>
                        </Card>
                        )
                        }):''
                      }
                  </Slider>
                  
            </Col>

            </Row>
            <Row>
                <Col className="heading-box">
                    <div className="contest-tempalte-wrapper">
                        <h3 className="h3-cls">Images</h3>
                    </div>
                </Col>
            </Row>
            <div className="border-bottom mb-4"></div>
            <Row className="added-merchandise-list-wrap ml-img-box">
                <Col xs={6}>
                <label>Background Image </label>
                {miniLeagueDetail.bg_image && 
                    <img className="img-cover mini-bg-image" height="150" src={ NC.S3 + NC.SPONSER_IMG_PATH + miniLeagueDetail.bg_image} />
                }
                {miniLeagueDetail.bg_image=='' && 
                   <p>No background image assigned</p>
                }
                </Col>      
                <Col xs={6}>
                <label>Sponsor Logo </label>
                {miniLeagueDetail.sponsor_logo && 
                    <img className="img-cover" height="150" src={ NC.S3 + NC.SPONSER_IMG_PATH + miniLeagueDetail.sponsor_logo} />
                }
                {miniLeagueDetail.sponsor_logo=='' ||  miniLeagueDetail.sponsor_logo==null && 
                   <p>No sponsor assigned</p>
                }
                </Col>
            </Row>

            <Row>
                <Col className="heading-box">
                    <div className="contest-tempalte-wrapper">
                        <h3 className="h3-cls">Prizes</h3>
                    </div>
                </Col>
            </Row>
            <div className="border-bottom mb-4"></div>
            <Row className="added-merchandise-list-wrap">
                        <Col xs={12}>
                            <div className="added-merchandise-list">
                                {_.map(PrizeDetail, (item, idx) => {
                                    return (
                                        <div className="merchandise-info-wrap" id={'name' + idx}>
                                            <div className="merchandise-img-wrap">
                                               {/*  <a href onClick={() => this.editMerchandise(item)} >
                                                    <i className="icon-edit"></i>
                                                </a> */}
                                                <span className="pull-left text-line">From rank </span>
                                                <span className="pull-left text-line text-rank">{item.min}-{item.max}</span>
                                                { item.prize_type==3  &&

                                                
                                                _.map(MerchandiseList, (mitem, idx) => {
                                                    return (
                                                    (mitem.merchandise_id==item.amount) ? 
                                                    <img src={NC.S3 + NC.MERCHANDISEIMG + mitem.image_name} alt="" />:''
                                                    )
                                                    })
                                                

                                                }
                                                <span className="bigiconclass">
                                                {item.prize_type == 1 && 
                                                // <i className="icon-rupess"></i>  
                                                        HF.getCurrencyCode()
                                                }
                                                {item.prize_type == 0 && 
                                                <i className="icon-bonus"></i>  
                                                }
                                                {item.prize_type == 2 && 
                                                <img src={Images.REWARD_ICON} /> 
                                                }
                                                </span>
                                            </div>
                                            <div className="merchandise-related-data">
                                                <div className="merchandise-label">
                                                    {item.prize_type == 1 && HF.getCurrencyCode()
                                                // <i className="icon-rupess"></i>  
                                                }
                                                {item.prize_type == 0 && 
                                                <i className="icon-bonus"></i>  
                                                }
                                                {item.prize_type == 2 && 
                                                <img src={Images.REWARD_ICON} /> 
                                                }

                                                    {(item.prize_type==3)?' '+item.max_value:item.amount}
                                                    
                                                    </div>
                                                {/* <div className="amt">{item.amount}</div> */}
                                            </div>
                                        </div>
                                    )
                                })

                                }
                            </div>

                            {PrizeDetail.length==0 && 
                                <span>No Prizes assigned</span>
                            }
                        </Col>
                    </Row>
                   
            {Date.now() > (miniLeagueDetail.scheduled_date_time*1000) && 
           
            <Row>
                <Col className="heading-box">
                    <div className="contest-tempalte-wrapper">
                        <h3 className="h3-cls">Leaderboard</h3>
                    </div>
                </Col>
            </Row>
            }
            {Date.now() > (miniLeagueDetail.scheduled_date_time*1000) &&  
            <div className="border-bottom mb-4"></div>
            }
            {Date.now() > (miniLeagueDetail.scheduled_date_time*1000) && 
            <Row className="scoring-main">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead>
                                <tr>
                                    <th className="left-th pl-4">Rank	</th>
                                    <th className="">Name	</th>
                                    <th className="">Points</th>
                                    {miniLeagueDetail.status !=0 &&
                                    <th className="pull-right pr-4">Prize</th>
                                    }
                                </tr>
                            </thead>
                            {
                                _.map(miniLeagueLeaderboard.users, (item, idx) => { 
                                    return (
                                        <tbody key={idx}>
                                            <tr>
                                                <td className="pl-4">{item.user_rank}</td>
                                                <td className="pl-4 text-ellipsis" onClick={() => this.props.history.push("/profile/" + item.user_unique_id)}>{item.user_name}</td>
                                                <td className="pl-4">
                                                    {item.scores}
                                                </td>
                                                {miniLeagueDetail.status !=0 &&
                                                <td className="pull-right pr-4">
                                                    {item.prize_data && 
                                                    <div className="merchandise-label">
                                                        
                                                        {item.prize_data[0].prize_type == 1 && 
                                                        // <i className="icon-rupess"></i>  
                                                            HF.getCurrencyCode()
                                                        }
                                                        {item.prize_data[0].prize_type == 0 && 
                                                        <i className="icon-bonus"></i>  
                                                        }
                                                        {item.prize_data[0].prize_type == 2 && 
                                                        <img src={Images.REWARD_ICON} /> 
                                                        }

                                                        {(item.prize_data[0].prize_type==3)?' '+item.prize_data[0].name:item.prize_data[0].amount}
                                                    
                                                    </div>
                                                    }
                                                </td>
                                                }
                                            </tr>
                                        </tbody>
                                    )
                                })
                            }
                        </Table>
                    </Col>
                </Row>
               
            }

            </div>
            )
    }


}
export default MiniLeagueDetail;
