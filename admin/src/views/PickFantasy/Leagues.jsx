import React, { Component, Fragment } from "react";
import {Table, Col, Row, Label, Modal, ModalBody, ModalHeader, ModalFooter, Form, FormGroup,
     Input, InputGroup, InputGroupAddon, InputGroupText, Button, CardHeader, Collapse, Tooltip } from 'reactstrap';
import _ from 'lodash';
import { changeStatus, getTeamPlayer, uploadTeamPlayerLogo, getLeagueListss, getSports, createSports, createTeamPlayerAPI, deleteTeam,deleteLeague, createLeagues, deleteSport, updateSport, addTeamPlayer,deleteLeaguePlayer } from "../../helper/WSCalling";
import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import Images from '../../components/images';
import { MSG_DELETE_GROUP, MSG_DELETE_SPORTS, MSG_DELETE_TEAM_PLAYER, MSG_NO_LEAGUE_LIST, } from "../../helper/Message";
import moment from 'moment';
import Select from 'react-select';
import LS from 'local-storage';
import HelperFunction, { _isUndefined, HF } from "../../helper/HelperFunction";

import { Accordion, Card } from "react-bootstrap";


class Leagues extends Component {
    constructor(props) {
        super(props)
     
        this.state = {
            CURRENT_PAGE: 1,
            ITEMS_PERPAGE: NC.ITEMS_PERPAGE_LG,
            addMoreModalOpen: false,
            addLeagueModalOpen: false,
            addSportModalOpen: false,
            deleteModalOpen: false,
            deleteTeamModalOpen: false,
            deleteLeagueModalOpen:false,
            deleteSportModalOpen: false,
            leagueList: [],
            ListPosting: false,
            Total: 0,
            CreatePosting: true,
            deletePosting: false,
            EditFlag: false,
            selectedSport:'1',
            selectedSport: (LS.get('selectedSport')) ? LS.get('selectedSport') : '1',
            sportsOptions: [],
            search_text: '',
            team_id:'',
            league_name:'',
            leagueLenght: false,
            sportLenght: false,
            sportsList:[],
            sports_id:'',
            league_id:'',
            teamPlayerOptions:'',
            selectedTeamPlayer:'',
            AddPlayer: false,
        
    }
}
  

    componentDidMount = () => {
        this.getSports()
        if(this.props && this.props.location && this.props.location.state && this.props.location.state.showLeaguePopup){
            this.addSportToggle()
        }
    }

    getTeamPlayer = () => {
        this.setState({ ListPosting: true })
        let { CURRENT_PAGE, ITEMS_PERPAGE } = this.state
        let params = {
            "sports_id": this.state.selectedSport,
            "limit": "all",
        }

        getTeamPlayer(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let teamPlayerOptions= [];
                _.map(ResponseJson.data.result, function (data){
                    teamPlayerOptions.push({
                                value: data.team_id,
                                label: data.team_name,
                            })
                })
                this.setState({
                    teamPlayerOptions: teamPlayerOptions,
                })  

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }


    addLeagueToggle = () =>{
        this.setState({ 
            addLeagueModalOpen: !this.state.addLeagueModalOpen,  league_name: '',})
    }
    addSportToggle = () =>{
        this.setState({ 
            addSportModalOpen: !this.state.addSportModalOpen,
            sport_name:'',
            EditFlag:false,
            sportLenght:false,
        })
    }

    editSportsToggle=(index, item)=>{
        if(!_isUndefined(item)){
            this.setState({
                sport_name: item.name,
                sports_id: item.sports_id,
                EditFlag: true,
                sportLenght: true,
            })
        }
    }

    addMoreToggle = (index, item) => {   
        if (!_.isUndefined(item))
        {
            this.setState({
                EditFlag: true,
                EditIndex: index,
                teamPlayerName: item.team_name,
                teamPlayerLogo: item.flag,
                team_abbr: item.team_abbr,
                team_id: item.team_id,
                GroupIcon: (!_.isNull(item.flag) && !_.isEmpty(item.flag)) ? NC.S3 +  NC.FLAG + item.flag : '',
            })
        }
        this.setState({ addMoreModalOpen: !this.state.addMoreModalOpen },()=>{
            if (!this.state.addMoreModalOpen)
               { 
                   this.setState({
                        EditFlag: false,
                        EditIndex: '',
                        teamPlayerName: '',
                        team_abbr: '',
                        GroupId: '',
                       GroupIcon: '',
                       CreatePosting: true,
                       teamPlayerLogo: '',
                })
            }
        })
    }

    getLeagueList = () => {
        this.setState({ ListPosting: true })
        let params = {
            "sports_id": this.state.selectedSport,
            "search_text": this.state.search_text,
        }

        getLeagueListss(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let data = ResponseJson.data
                this.setState({
                    leagueList: data,
                    ListPosting: false,
                    activeAccID: data.length > 0 && data[0].league_id
                },()=>{this.getTeamPlayer()})
            }
        })
    }
// Get all Sport from API
    getSports = () =>{
        let params = {}
        getSports(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let sportsOptions= [];
                _.map(ResponseJson.data, function (data){
                            sportsOptions.push({
                                value: data.sports_id,
                                label: data.name
                            })
                })
                this.setState({
                    sportsOptions: sportsOptions,
                    sportsList: ResponseJson.data,
                },()=>{ this.getLeagueList()})   
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    // Get all Sport from API
  

    handleInputChange = (event) =>{
        let name = event.target.name
        let value = event.target.value        
        this.setState({ [name]: value },()=>{
            let valid = true                 
            if ((this.state.teamPlayerName.length > 2) && (this.state.team_abbr.length > 2) && !_.isEmpty(this.state.teamPlayerLogo)){
                valid = false
            }
            this.setState({ CreatePosting: valid })
        })
    }
    handleLeague = (event) =>{
        let name = event.target.name
        let value = event.target.value        
        if(name.length > 3){
            this.setState({
                league_name: value,
                leagueLenght: true
            })
        }
    }
    handleSportName = (event) =>{
        let name = event.target.name
        let value = event.target.value        
        if(name.length > 3){
            this.setState({
                sport_name: value,
                sportLenght: true,
            })
        }
    }

    changeStatusToggle = (item) => {
        console.log(item);
        
        let params = {
            "sports_id": item.sports_id,
            "status": item.status == 0 ? 1 : 0,
        }

        changeStatus(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
               this.getSports()

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    addLeagueModal = () => {
        let { leagueLenght, sportsOptions, league_name } = this.state
        return (
            <div>
                <Modal className="addSportModal top30 crossClear" isOpen={this.state.addLeagueModalOpen}
                    toggle={this.addLeagueToggle}>
                    <ModalHeader>
                         <Row>
                            <Col md={12} className='simpleflex'>
                                <span className="h3-cls"> Add League</span>
                                <span className="Quest"> {'?'}</span>
                                
                            </Col>
                        </Row>
                    </ModalHeader>    
                    <ModalBody>
                        <Row>
                            <Col md={12}>
                            <label>Select Sport</label>
                                <Select
                                    isClearable={true}
                                    id="selectedSport"
                                    name="selectedSport"
                                    placeholder="Select Sport"
                                    value={this.state.selectedSport}
                                    options={sportsOptions}
                                    onChange={(e) => this.handleSport(e, 'selectedSport')}
                                />
                            </Col> 
                        </Row>
                        <Row className="mt-20">
                            <Col md={12}>
                                <label>Enter League Name</label>
                                <Input
                                    maxLength="300"
                                    placeholder="Enter League Name"
                                    type="text"
                                    name="league_name"
                                    value={league_name}
                                    onChange={(e) => this.handleLeague(e)}
                                />
                            </Col>
                        </Row>
                           
                    </ModalBody>
                    <ModalFooter>
                        <Button className="btn-secondary-outline"
                            disabled={!leagueLenght}
                            onClick={(e)=>this.createLeague()}>Save</Button>

                         <Button className="btn-default-gray" onClick={this.addLeagueToggle}>Cancel</Button>    
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
    addSportModal = () => {
        let { sportLenght, sport_name, sportsList, ListPosting, EditFlag } = this.state
        return (
            <div>
                <Modal size="lg"  className="addSportModal top20" isOpen={this.state.addSportModalOpen}
                    toggle={this.addSportToggle}>
                    <ModalHeader>
                        <Row>
                            <Col md={12} className='simpleflex'>
                                <span className="h3-cls"> {EditFlag ? 'Update ' : 'Add'} Sport</span>
                                <span onClick={this.addSportToggle}><i className="icon-close"></i></span>
                            </Col>
                        </Row>
                    </ModalHeader>   
                    <ModalBody>
                        <Row className="">
                            <Col md={8}>
                                <label>Sports Name</label>
                                <Input
                                    maxLength="300"
                                    placeholder="Enter Sport Name"
                                    type="text"
                                    name="sport_name"
                                    value={sport_name}
                                    onChange={(e) => this.handleSportName(e)}
                                />
                            </Col>
                            <Col md={4} className="mt-30">
                                <Button className="btn-secondary-outline mr-15"
                                 disabled={!sportLenght}   
                                 onClick={EditFlag ? this.updateSport : this.createSports}>{EditFlag ? 'Update' :'Add'}</Button>{' '}                    
                                 {/* onClick={(e)=>this.createSports()}>Add</Button> */}
                                
                                <Button className="btn-default-gray" onClick={this.addSportToggle}>Cancel</Button>   
                            </Col>
                        </Row>
                        <hr />
                        <Row className="mt-30">
                            <Col md={12} className="table-responsive common-table teamPlayerTable">
                                <Table className="mb-0">
                                    <thead>
                                        <tr>
                                            <th>Sport Name</th>
                                            <th>Created On</th>
                                            <th className="">Action  </th>
                                            <th className="">Active / Inactive  </th>
                                        </tr>
                                    </thead>
                                    {
                                        sportsList &&
                                            _.map(sportsList, (item, idx) => {
                                                return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td className="pl-3">{item.name ? item.name : "--"}</td>
                                                        <td className="pl-3">
                                                        { (item.sports_id == 1 ||  item.sports_id == 2) ? ''   : 
                                                        // moment(item.created_date).format('DD/MM/YYYY')
                                                        <>
                                                        {HelperFunction.getFormatedDateTime(item.created_date, 'DD/MM/YYYY' )}
                                                        </>
                                                        
                                                        }</td>
                                                       
                                                        <td>
                                                        {
                                                        (item.sports_id == 1 ||  item.sports_id == 2) ? '' 
                                                        : 
                                                        <>
                                                        <i onClick={() => this.editSportsToggle(idx, item)} className="icon-edit mr-15"></i>
                                                        <i onClick={() => this.deleteSportsToggle(item.sports_id)}className="icon-delete"></i>
                                                        </>
                                                        }
                                                        </td>
                                                        <td>
                                                            <div className="activate-module">
                                                                <label className="global-switch">
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={item.status == "0" ? false : true}
                                                                        onChange={() => this.changeStatusToggle(item)}
                                                                    />
                                                                    <span className="switch-slide round">
                                                                        <span className={`switch-on ${item.status == "0" ? 'active' : ''}`}></span>
                                                                        <span className={`switch-off ${item.status == "1" ? 'active' : ''}`}></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                )
                                            })
                                }
                                </Table>
                            </Col>
                        </Row>
                    </ModalBody>
                    <ModalFooter>
                         
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
    deleteGroupToggle = (dUserIdx, team_id) => {
        this.setState({ 
            dUserIdx: dUserIdx,
            team_id: team_id,
            deleteModalOpen: !this.state.deleteModalOpen 
        })
    }
    deleteTeamToggle = (dUserIdx, teamPlayer) => {
        
        console.log('teamPlayer', teamPlayer)
        this.setState({ 
            dUserIdx: dUserIdx,
            team_id: teamPlayer.team_id,
            league_id: teamPlayer.league_id,
            deleteTeamModalOpen: !this.state.deleteTeamModalOpen 
        })
    }
    deleteLeagueToggle = (dUserIdx, league_id) => {
        console.log('delete leagueID', league_id)
        this.setState({ 
            dUserIdx: dUserIdx,
            league_id: league_id,
            deleteLeagueModalOpen: !this.state.deleteLeagueModalOpen 
        })
    }
    deleteSportsToggle = (sports_id) => {
        console.log('deleteSportsToggle ID', sports_id)
        this.setState({ 
            sports_id: sports_id,
            deleteSportModalOpen: !this.state.deleteSportModalOpen 
        })
    }

    deleteGroupModal = () => {
        let { deletePosting } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal" isOpen={this.state.deleteModalOpen}
                    toggle={this.deleteGroupToggle}>
                    <ModalBody className="text-center">
                        <h5>{MSG_DELETE_GROUP}</h5>
                    </ModalBody>
                    <ModalFooter className="justify-content-center">
                        <Button className="btn-default-gray" onClick={this.deleteGroupToggle}>No</Button>
                        <Button className="btn-secondary-outline"
                            disabled={deletePosting}
                            onClick={this.deleteTeam}>Yes</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
    deleteLeagueModal = () => {
        let { deletePosting } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal" isOpen={this.state.deleteLeagueModalOpen}
                    toggle={this.deleteLeagueToggle}>
                    <ModalBody className="text-center">
                        <h5>{MSG_DELETE_GROUP}</h5>
                    </ModalBody>
                    <ModalFooter className="justify-content-center">
                        <Button className="btn-default-gray" onClick={this.deleteLeagueToggle}>No</Button>
                        <Button className="btn-secondary-outline"
                            disabled={deletePosting}
                            onClick={this.deleteLeague}>Yes</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
    deleteTeamModal = () => {
        let { deletePosting, deleteTeamModalOpen } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal" isOpen={this.state.deleteTeamModalOpen}
                    toggle={this.deleteTeamToggle}>
                    <ModalBody className="text-center">
                        <h5>{MSG_DELETE_TEAM_PLAYER}</h5>
                    </ModalBody>
                    <ModalFooter className="justify-content-center">
                        <Button className="btn-default-gray" onClick={()=>{this.setState({deleteTeamModalOpen: !this.state.deleteTeamModalOpen})}}>No</Button>
                        <Button className="btn-secondary-outline"
                            disabled={deletePosting}
                            onClick={this.deleteTeam}>Yes</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
    deleteSportModal = () => {
        let { deletePosting } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal" isOpen={this.state.deleteSportModalOpen}
                    toggle={this.deleteSportsToggle}>
                    <ModalBody className="text-center">
                        <h5>{MSG_DELETE_SPORTS}</h5>
                    </ModalBody>
                    <ModalFooter className="justify-content-center">
                        <Button className="btn-default-gray" onClick={this.deleteSportsToggle}>No</Button>
                        <Button className="btn-secondary-outline"
                            onClick={this.deleteSport}>Yes</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    deleteTeam = () => {
        let { team_id, league_id } = this.state 
        this.setState({ deletePosting: true })
        let params = {
            "team_id": team_id,
            "league_id": league_id,
        }
        deleteLeaguePlayer(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({ 
                    deleteTeamModalOpen: !this.state.deleteTeamModalOpen,
                    deletePosting: false ,
                })
                this.getLeagueList()
                notify.show(ResponseJson.message, "success", 3000)
            }else{
                this.setState({ 
                    deleteTeamModalOpen: !this.state.deleteTeamModalOpen,
                    deletePosting: false ,
                })
            }
        }).catch(error => {
            this.deleteTeamToggle()
            this.setState({ deletePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    deleteLeague = () => {
        let { league_id, leagueList } = this.state        
        this.setState({ deletePosting: true })
        let params = {
            "league_id": league_id
        }
        deleteLeague(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.deleteLeagueToggle()
                notify.show(ResponseJson.message, "success", 3000)
                _.remove(leagueList,(item)=>{
                    return item.league_id == league_id
                })
                this.setState({ 
                    leagueList: leagueList,
                    deletePosting: false 
                })
            } else {
                this.deleteLeagueToggle()
                this.setState({ deletePosting: false })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ deletePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    deleteSport = () => {
        let { sports_id, sportsList } = this.state        
        this.setState({ deletePosting: true })
        let params = {
            "sports_id": sports_id
        }
        deleteSport(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.deleteSportsToggle()
                this.getSports()
                notify.show(ResponseJson.message, "success", 3000)
                _.remove(sportsList,(item)=>{
                    return item.sports_id == sports_id
                })
              
            } else {
                this.deleteSportsToggle()
                this.setState({ deletePosting: false })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ deletePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }


    createLeague=()=>{
        let {league_name} = this.state
        let params ={
            "sports_id": this.state.selectedSport,
            "name": league_name
        }
        createLeagues(params).then(ResponseJson => {
            if(ResponseJson.response_code == NC.successCode){
                this.addLeagueToggle()
                this.getLeagueList()
                notify.show(ResponseJson.message, "success", 3000)
            }
        })
    }
    createSports = () =>{
        let {sport_name} = this.state
        let params ={
            "name": sport_name
        }
        createSports(params).then(ResponseJson => {
            if(ResponseJson.response_code == NC.successCode){
                this.setState({
                    addSportModalOpen: true,
                },()=>{
                    this.getLeagueList()
                    this.getSports()
                    this.setState({
                        sport_name:'',
                        sportLenght: false,
                    })
                })
                notify.show(ResponseJson.message, "success", 3000)
            }
        })
    }

    createTeamPlayerFn = () => {
        this.setState({ CreatePosting: true })        
        let { teamPlayerName, team_abbr, teamPlayerLogo } = this.state
        let params = {
            "sports_id": this.state.selectedSport,
            "team_name": teamPlayerName,
            "team_abbr": team_abbr,
            "flag": teamPlayerLogo,
        }
        createTeamPlayerAPI(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.addMoreToggle()
                this.getLeagueList()
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({ 
                    teamPlayerName: '',
                    GroupIcon:'', 
                    teamPlayerLogo:'', 
                })
            } else {                
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ CreatePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    
    updateSport = () =>{
        let { sports_id, sport_name  } = this.state
        this.setState({ CreatePosting: true })
        let params = {
            "sports_id": sports_id,
            "name": sport_name,
        }
        console.log('updtatesSport', params)
        updateSport(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 3000)
                this.getSports()
                this.setState({
                    sport_name: '',
                    EditFlag: false,
                    sportLenght: false,
                    })
                } else {
                    this.setState({ 
                        CreatePosting: false, 
                    })
                    notify.show(NC.SYSTEM_ERROR, "error", 3000)
                }
        }).catch(error => {
            this.setState({ CreatePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    updateTeamPlayerFn = () => {
        this.setState({ CreatePosting: true })
        let { teamPlayerLogo, teamPlayerName, leagueList, EditIndex, team_abbr, team_id } = this.state
        let params = {
            "sports_id": this.state.selectedSport,
            "team_name": teamPlayerName,
            "team_abbr": team_abbr,
            "team_id": team_id,
            "flag": teamPlayerLogo,
        }
        let TempUsersList = leagueList        
        createTeamPlayerAPI(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                console.log('Edit player', ResponseJson);
                this.addMoreToggle()
                this.getLeagueList()
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({ 
                    teamPlayerName: '',
                    GroupIcon:'', 
                    teamPlayerLogo:'', 
                })
            } else {
                this.setState({ CreatePosting: false })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ CreatePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getLeagueList()
        });
    }

    onChangeImage = (event) => {
        this.setState({
            GroupIcon: URL.createObjectURL(event.target.files[0]),
            CreatePosting: true
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("file", file);
        uploadTeamPlayerLogo(data).then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        teamPlayerLogo: Response.data.image_name,
                    });

                } 
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }
    addTeamPlayer = () =>{
        let { team_id, league_id } = this.state
        let params = {
            "league_id": league_id,
            "team_id": team_id,
        }
        addTeamPlayer(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.getLeagueList();
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({
                    selectedTeamPlayer: '',
                })
            } 
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        });
    }
    resetFile = () => {
        this.setState({
            GroupIcon: null,
            teamPlayerLogo: '',
            CreatePosting: true,
        });
    }
    handleSport  = (e, name) =>{
        this.setState({
            selectedSport: e.value,
            leagueList: []
        }, ()=>{ this.getLeagueList()})
    }
    handleTeamPlayer  = (e, name, item) =>{
        this.setState({
            [name]: e.value,
            league_id: item.league_id,
            team_id: e.value,
        },()=>{
            this.getLeagueList()
        })
    }

    searchTeam = () => {
        this.setState({
          posting: false,
          search_text: this.state.search_text
        }, function () {
          this.getLeagueList();
        })
      }
      handleFieldVal = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({
          [name]: value
        }, function () {
          if (name === 'search_text') {
            this.getLeagueList();
          }
        })
      }  

      setActiveLeague=(id)=>{
        if(this.state.activeAccID == id){
            this.setState({
                activeAccID: ''
            })
        }
        else{
            this.setState({
                activeAccID: id
            })
        }
      }
      AddPlayerRow = (flag)=>{
        if(!flag){
            this.setState({
                selectedTeamPlayer: '',
                AddPlayer: flag
            })
        }else{
            this.setState({
                AddPlayer: flag
            })
        }
      } 
      goToFixture = () =>{
        this.props.history.push({ pathname: '/picksfantasy/fixture'})
      }

    render() {
        let { leagueList, Total, ListPosting, CURRENT_PAGE, ITEMS_PERPAGE, sportsOptions,activeAccID,teamPLayerList, teamPlayerOptions, selectedTeamPlayer, AddPlayer } = this.state

        return (
            <React.Fragment>
                <div className="system-userlist">
                    {this.addLeagueModal()}
                    {this.addSportModal()}
                    {this.deleteTeamModal()}
                    {this.deleteGroupModal()}
                    {this.deleteLeagueModal()}
                    {this.deleteSportModal()}
                    <Row>
                        <Col md={4}>
                            <div className="float-left">
                                <h2 className="h2-cls mt-2">Leagues / Players</h2>
                            </div>
                        </Col>
                        <Col md={8}>
                        <Button onClick={this.addSportToggle} className="add-button-pick ml-3"> Add / View Sports</Button>
                        <Button onClick={this.addLeagueToggle} className="add-button-pick ml-3"> Add League</Button>
                        <Button onClick={()=>this.goToFixture()} className="add-button-pick ml-3"> Add Fixture</Button> 

                            
                            
                        </Col>
                    </Row>
                    <hr />
                    <Row>
                        <Col md={12}>
                        <div className="teamSearch">
                            <div className="selectSports">
                                <label className="filter-label">Select Sports</label>
                                <Select
                                    className="dfs-selector"
                                    isClearable={true}
                                    id="selectedSport"
                                    name="selectedSport"
                                    placeholder="Select Sport"
                                    value={this.state.selectedSport}
                                    options={sportsOptions}
                                    onChange={(e) => this.handleSport(e, 'selectedSport')}
                                />
                            </div>    
                            <div className="searchBox">
                                <label className="filter-label">Search</label>
                                <InputGroup>
                                    <Input type="text" id="search_text" name="search_text" value={this.state.search_text} onChange={(e) => this.handleFieldVal(e)} placeholder="Search League" />
                                    <InputGroupAddon addonType="append" onClick={() => this.searchTeam()}>
                                    <InputGroupText><i className="fa fa-search"></i></InputGroupText>
                                    </InputGroupAddon>
                                </InputGroup>
                            </div>    
                        </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                        {    
                        !_.isEmpty(leagueList) ? 
                          
                            _.map(leagueList, (item, idx) => {
                                return (
                                    <Accordion key={item.league_id} className="league-list">
                                        <Card>
                                            <Accordion.Toggle as={Card.Header} eventKey={item.league_id} className="super-league">
                                                <div className="cricket-super-league">
                                                  {item.league_name}
                                                </div>
                                                <div>
                                                    <i
                                                     onClick={() => this.deleteLeagueToggle(idx, item.league_id)}
                                                    className="icon-delete"></i>
                                                </div>
                                            </Accordion.Toggle>

                                            <Accordion.Collapse eventKey={item.league_id}>
                                                <Card.Body>
                                                    <Row className="player_list">


                                                        <ul className="list-unstyled player_list_item">
                                                        { !_.isEmpty(item.player_list) ?
                                                         _.map(item.player_list, (teamPlayer, id)=>{
                                                            return(
                                                                    <li key={teamPlayer.team_id}>
                                                                        <div>
                                                                            <span className="player_list_item_logo">
                                                                                <img className="" src={teamPlayer.flag ? NC.S3 +  NC.FLAG + teamPlayer.flag : Images.DEF_ADDPHOTO } />
                                                                            </span>
                                                                            <span> 
                                                                                {teamPlayer.team_name}
                                                                            </span>
                                                                        </div>
                                                                        <div>
                                                                            <i
                                                                                onClick={() => this.deleteTeamToggle(id, teamPlayer)}
                                                                                className="icon-delete"></i>
                                                                        </div>
                                                                         
                                                                    </li>
                                                                )
                                                            })
                                                            : <li>No Team / Players yet</li>
                                                        }
                                                         </ul>

                                                    </Row>
                                                    <Row>
                                                        {
                                                            AddPlayer && 
                                                     
                                                            <div className="TeamList">
                                                                <div>
                                                                    <label className="filter-label">Select Team / Player</label>
                                                                    <Select
                                                                        className="dfs-selector"
                                                                        isClearable={true}
                                                                        id="selectedTeamPlayer"
                                                                        name="selectedTeamPlayer"
                                                                        placeholder="Select Team/Player"
                                                                        value={this.state.selectedTeamPlayer}
                                                                        options={teamPlayerOptions}
                                                                        onChange={(e) => this.handleTeamPlayer(e, 'selectedTeamPlayer', item)}
                                                                    />
                                                                </div>
                                                                <div>
                                                                <span className="doneText mr-15" onClick={() => this.addTeamPlayer()}>Done</span>

                                                                <span className="CrossIcone" onClick={() => this.AddPlayerRow(false)}><i className="icon-close"></i></span>
                                                                </div>
                                                            </div>
                                                        }
                                                    </Row>
                                                    <Row>
                                                        <div onClick={()=>this.AddPlayerRow(true)} className="add-team-players">
                                                                <i className="icon-plus"></i> Add Team / Players
                                                                
                                                        </div>
                                                    </Row>
                                                
                                                   
                                                
                                                </Card.Body>

                                            </Accordion.Collapse>
                                        </Card>

                                    </Accordion>
                                    )
                                })
                               
                                
                         
                            : <div className="noData">
                                   No data found
                            </div>
                        }  
                        </Col>
                        
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div onClick={()=>this.addLeagueToggle()} className="addLeague">
                                    <i className="icon-plus"></i> Add League
                            </div>
                        </Col>
                    </Row>
                    {/* <Row>
                        <Col md={12}>
                        {  _.map(leagueList, (item, idx) => {
                            return (
                               <div onClick={()=>this.setActiveLeague(item.league_id)}>
                                    test 
                                    {
                                        activeAccID == item.league_id &&
                                        <div>{item.league_name}</div>
                                    }
                               </div>
                            )
                            })}     
                        </Col>
                        
                    </Row> */}
                  
                    <Row>
                        <Col md={12}>
                            {
                                Total > NC.ITEMS_PERPAGE &&
                                (<div className="custom-pagination float-right mt-5">
                                    <Pagination
                                        activePage={CURRENT_PAGE}
                                        itemsCountPerPage={ITEMS_PERPAGE}
                                        totalItemsCount={Total}
                                        pageRangeDisplayed={5}
                                        onChange={e => this.handlePageChange(e)}
                                    />
                                </div>)
                            }
                        </Col>
                    </Row>
                    {

                    }
                </div>
            </React.Fragment>
        )
    }
}
export default Leagues;