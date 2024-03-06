import React, { Component, Fragment } from "react";
import {Table, Card, CardBody, Col, Row, Label, Modal, ModalBody, ModalHeader, ModalFooter, Form, FormGroup, Input, InputGroup, InputGroupAddon, InputGroupText, Button } from 'reactstrap';
import _, { upperCase } from 'lodash';
import { uploadTeamPlayerLogo, getTeamPlayer, getSports, deleteGroup, updateGroup, createTeamPlayerAPI, deleteTeam } from "../../helper/WSCalling";
import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import Images from '../../components/images';
import { MSG_DELETE_TEAM } from "../../helper/Message";
import Select from 'react-select';
import LS from 'local-storage';


class TeamPlayerManagement extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            ITEMS_PERPAGE: NC.ITEMS_PERPAGE_LG,
            addMoreModalOpen: false,
            deleteModalOpen: false,
            teamPLayerList: [],
            ListPosting: false,
            Total: 0,
            CreatePosting: true,
            deletePosting: false,
            EditFlag: false,
            teamPlayerName: '',
            team_abbr: '',
            teamPlayerLogo: '',
            selectedSport:'1',
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            sportsOptions: [],
            search_text: '',
            team_id:'',
        }
    }

    componentDidMount = () => {
        this.getTeamPlayer()
        this.getSports()
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

    getTeamPlayer = () => {
        this.setState({ ListPosting: true })
        let { CURRENT_PAGE, ITEMS_PERPAGE } = this.state
        let params = {
            "sports_id": this.state.selectedSport,
            "current_page": CURRENT_PAGE,
            "items_perpage": ITEMS_PERPAGE,
            "sort_field": "team_name",
            "sort_order": "ASC",
            "search_text": this.state.search_text
        }

        getTeamPlayer(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (CURRENT_PAGE == 1)
                    this.setState({
                        Total: ResponseJson.data.total
                    })
                this.setState({
                    teamPLayerList: ResponseJson.data.result,
                    ListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
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
                })   
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleInputChange = (event) =>{
        let name = event.target.name
        let value = event.target.value        
        this.setState({ [name]: value },()=>{
            let valid = true                 
            if ((this.state.teamPlayerName.length > 2) && (this.state.team_abbr.length > 1) && !_.isEmpty(this.state.teamPlayerLogo)){
                valid = false
            }
            this.setState({ CreatePosting: valid })
        })
    }

    addMoreModal = () => {
        let { GroupIcon, CreatePosting, teamPlayerName, EditFlag, team_abbr, teamPlayerLogo } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal addPlayerModel" isOpen={this.state.addMoreModalOpen}
                    toggle={this.addMoreToggle}>
                    <ModalHeader>
                         <Row>
                            <Col md={12} className='simpleflex'>
                                <h3 className="h3-cls">{EditFlag ? 'Update ' : 'Add'} Team / Players</h3>
                            </Col>
                        </Row>
                    </ModalHeader> 
                       
                    <ModalBody>
                        <Row>
                            <Col md={12}>
                                
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <label>Team / Player Name</label>
                                <Input
                                    maxLength="25"
                                    placeholder="Team / Player Name"
                                    type="text"
                                    name="teamPlayerName"
                                    value={teamPlayerName}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </Col>
                        </Row>
                        <Row className="mt-4">
                            <Col md={12}>
                                <label>Abbreviation</label>
                                <Input
                                    maxLength="300"
                                    placeholder="Abbreviation"
                                    type="text"
                                    name="team_abbr"
                                    value={team_abbr}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </Col>
                        </Row>
                        <Row className="mt-4">
                            <Col md={12}>
                                <label htmlFor="Redeem">Image</label>
                                     <div className="TeamLogo">
                                            {!_.isEmpty(GroupIcon) ?
                                                <Fragment>
                                                    <i onClick={this.resetFile} className="icon-close"></i>
                                                    <img className="img-cover" src={GroupIcon} />
                                                </Fragment>
                                                :
                                                <Fragment>
                                                    <img className="def-addphoto" src={Images.DEF_ADDPHOTO} alt="" />
                                                    <Input
                                                        accept="image/x-png,
                                                        image/jpeg,image/jpg"
                                                        type="file"
                                                        name='GroupIcon'
                                                        id="GroupIcon"
                                                        onChange={this.onChangeImage}
                                                    />
                                                    <span className="DropImage">Drop your image here, or <span>browse</span></span>
                                                    <span>Size - 128*128</span>
                                                </Fragment>
                                            }
                                        </div>
                                </Col>
                        </Row>
                    </ModalBody>
                    <ModalFooter>
                        <Button className="btn-secondary-outline"
                            // disabled={CreatePosting}
                            disabled={_.isEmpty(teamPlayerLogo) || _.isEmpty(team_abbr) || _.isEmpty(teamPlayerName)}
                            onClick={EditFlag ? this.updateTeamPlayerFn : this.createTeamPlayerFn}>{EditFlag ? 'Update' :'Add'}</Button>{' '}

                         <Button className="btn-default-gray" onClick={this.addMoreToggle}>Cancel</Button>    
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

    deleteGroupModal = () => {
        let { deletePosting } = this.state
        return (
            <div>
                <Modal className="DeleteModal" isOpen={this.state.deleteModalOpen}
                    toggle={this.deleteGroupToggle}>
                    <ModalHeader className="simpleflex">
                        <span className="h3-cls">Confirmation</span>
                        <span onClick={this.deleteGroupToggle}><i className="icon-close"></i></span>
                    </ModalHeader>     
                    <ModalBody className="text-center">
                        <h5>Are you sure you want to delete <br></br> this entry?</h5>
                    </ModalBody>
                    <ModalFooter className="justify-content-center">
                        <Button className="btn-secondary-outline"
                            disabled={deletePosting}
                            onClick={this.deleteTeam}>Delete</Button>{' '}
                        <Button className="btn-default-gray" onClick={this.deleteGroupToggle}>Cancel</Button>    
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    deleteTeam = () => {
        let { team_id, teamPLayerList } = this.state        
        this.setState({ deletePosting: true })
        let params = {
            "team_id": team_id
        }
        deleteTeam(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.deleteGroupToggle()
                notify.show(ResponseJson.message, "success", 3000)
                _.remove(teamPLayerList,(item)=>{
                    return item.team_id == team_id
                })
                this.setState({ 
                    teamPLayerList: teamPLayerList,
                    teamPlayerName: '',
                    deletePosting: false 
                })
            } else {
                this.deleteGroupToggle()
                this.setState({ deletePosting: false })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ deletePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    createTeamPlayerFn = () => {
        this.setState({ CreatePosting: true })        
        let { teamPlayerName, team_abbr, teamPlayerLogo } = this.state
        let params = {
            "sports_id": this.state.selectedSport,
            "team_name": teamPlayerName,
            "team_abbr": upperCase(team_abbr),
            "flag": teamPlayerLogo,
        }
        createTeamPlayerAPI(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.addMoreToggle()
                this.getTeamPlayer()
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

    updateTeamPlayerFn = () => {
        this.setState({ CreatePosting: true })
        let { teamPlayerLogo, teamPlayerName, GroupId, teamPLayerList, EditIndex, team_abbr, team_id } = this.state
        let params = {
            "sports_id": this.state.selectedSport,
            "team_name": teamPlayerName,
            "team_abbr": team_abbr,
            "team_id": team_id,
            "flag": teamPlayerLogo,
        }
        console.log('params', params)
        let TempUsersList = teamPLayerList        
        createTeamPlayerAPI(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                console.log('Edit player', ResponseJson);
                this.addMoreToggle()
                this.getTeamPlayer()
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
            this.getTeamPlayer()
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
        data.append("file_name", file);
        // data.append("type", "jersey");
        data.append("type", "flag");
        uploadTeamPlayerLogo(data).then(Response => {
                if (Response.response_code == NC.successCode) {
                    console.log('Response', Response)
                    this.setState({
                        teamPlayerLogo: Response.data.image_name,
                    });

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
        }, ()=>{ this.getTeamPlayer()})
    }

    searchTeam = () => {
        this.setState({
          posting: false,
          search_text: this.state.search_text
        }, function () {
          this.getTeamPlayer();
        })
      }
      handleFieldVal = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({
          [name]: value
        }, function () {
          if (name === 'search_text') {
            console.log('object', name)
            this.getTeamPlayer();
          }
        })
      }  

    render() {
        let { teamPLayerList, Total, ListPosting, CURRENT_PAGE, ITEMS_PERPAGE, sportsOptions } = this.state
        return (
            <React.Fragment>
                <div className="system-userlist">
                    {this.addMoreModal()}
                    {this.deleteGroupModal()}
                    <Row>
                        <Col md={6}>
                            <div className="float-left">
                                <h2 className="h2-cls mt-2">Team / Player Name</h2>
                            </div>
                        </Col>
                        <Col md={6}>
                            <Button onClick={this.addMoreToggle} className="add-button-pick"> Add Team / Players</Button>
                        </Col>
                    </Row>
                    <hr />
                    <Row>
                        <Col>
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
                                    <label className="filter-label">Search </label>
                                    <InputGroup>
                                        <Input type="text" id="search_text" name="search_text" value={this.state.search_text} onChange={(e) => this.handleFieldVal(e)} placeholder="Team/Player Name" />
                                        <InputGroupAddon addonType="append" onClick={() => this.searchTeam()}>
                                        <InputGroupText><i className="fa fa-search"></i></InputGroupText>
                                        </InputGroupAddon>
                                    </InputGroup>
                                </div>    
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table teamPlayerTable">
                            <Table className="mb-0">
                                <thead>
                                    <tr>
                                        <th>Team / Player Name</th>
                                        <th>Abbrevation</th>
                                        <th className="">Image</th>
                                        <th className="">Action  </th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _.map(teamPLayerList, (item, idx) => {
                                            return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="pl-3">{item.team_name ? item.team_name : "--"}</td>
                                                    <td className="pl-3">{item.team_abbr ? item.team_abbr : "--"}</td>
                                                    <td className="pl-3">
                                                        <div className="su-profile-img img-center">
                                                                <img className="img-cover" src={item.flag ? NC.S3 +  NC.FLAG + item.flag : Images.DEF_ADDPHOTO } />
                                                        </div>
                                                    </td>                                
                                                    
                                                    <td>
                                                        <i
                                                            onClick={() => this.addMoreToggle(idx, item)}
                                                            className="icon-edit ml-4"></i>
                                                        <i
                                                        onClick={() => this.deleteGroupToggle(idx, item.team_id)}
                                                        className="icon-delete"></i>
                                                            
                                                    </td>
                                                </tr>
                                            </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan="8">
                                                    {(Total == 0 && !ListPosting) ?
                                                        <div className="no-records">
                                                            {NC.NO_RECORDS}</div>
                                                        :
                                                        <Loader />
                                                    }
                                                </td>
                                            </tr>
                                        </tbody>
                                }
                            </Table>
                        </Col>
                    </Row>
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
export default TeamPlayerManagement