import React, { Component } from 'react';
import { Card, CardBody, Col, Row, Label, Modal, ModalBody, ModalHeader, ModalFooter, Form, FormGroup, Input, InputGroup, InputGroupAddon, InputGroupText, Button } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import LS from 'local-storage';
var globalThis = null;
class Teams extends Component {
  constructor(props) {
    super(props);
    this.state = {
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      updatedFlag: '',
      updatedFlagURL: '',
      updatedFlagName: '',
      updatedJersey: '',
      updatedJerseyName: '',
      updatedJerseyURL: '',
      search_text: '',
      teamList: [],
      sportList: [],
      posting: false,
      loadMoring: true,
      editTeamModal: false,
      team_data: { team_name: "", team_abbr: "", association_id: "", sports_id: "", jersey: "", flag: "", twitter_handles: "" },
      expZoomIn: false,
      sportsListFormated: [],
      leagueList: [],
      selected_league: "",
      savePosting: true
    };
  }

  componentDidMount() {
    globalThis = this;
    this.GetAllLeagueList();
  }

  // GET ALL LEAGUE LIST
  GetAllLeagueList = () => {
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_ALL_LEAGUE_LIST, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        this.setState({
          posting: false
        }, () => {
          this.createLeagueList(responseJson);
          this.GetAllTeamList();
        })
      } else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  createLeagueList = (list) => {
    let leagueArr = list;
    let tempArr = [{ value: "", label: "All" }];

    leagueArr.map(function (lObj, lKey) {
      tempArr.push({ value: lObj.league_id, label: lObj.league_name });
    });
    this.setState({ leagueList: tempArr });
  }

  // GET ALL TEAM LIST
  GetAllTeamList = () => {
    let param = {
      "sports_id": this.state.selected_sport,
      "league_id": this.state.selected_league,
      "items_perpage": NC.ITEMS_PERPAGE_LG,
      "total_items": 0,
      "current_page": 1,
      "sort_order": "ASC",
      "sort_field": "team_name",
      "search_text": this.state.search_text
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_ALL_TEAM_LIST, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        this.setState({
          posting: false,
          teamList: responseJson.result
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  searchTeam = () => {
    this.setState({
      posting: false,
      search_text: this.state.search_text
    }, function () {
      this.GetAllTeamList();
    })
  }

  toggleEditTeam(item, index) {
    if (item) {
      this.setState({
        editTeamModal: !this.state.editTeamModal,
        team_data: { ...this.state.team_data, ...item, team_index: index },
      });
    } else {
      this.setState({
        editTeamModal: !this.state.editTeamModal
      });
    }
  }

  onDrop(e) {
    e.preventDefault();
    let reader = new FileReader();
    let mImage = e.target.files[0];
    if (e.target.name == 'flag') {
      reader.onloadend = () => {
        this.setState({
          updatedFlag: mImage,
          selectedImage: reader.result
        }, function () {
          this.uploadFlagImage();
        });
      }
    }
    else if (e.target.name == 'jersey') {
      reader.onloadend = () => {
        this.setState({
          updatedJersey: mImage,
          selectedImage: reader.result
        }, function () {
          this.uploadJerseyImage();
        });
      }
    }
    reader.readAsDataURL(mImage)
  }

  uploadFlagImage() {
    this.setState({
      isUploadingFlag: true,
      savePosting: true
    });

    var data = new FormData();
    data.append("file", this.state.updatedFlag);
    data.append("name", this.state.updatedFlag.name);
    data.append("team_id", this.state.team_data.team_id);

    var xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.addEventListener("readystatechange", function () {
      if (this.readyState === 4) {

        var response = JSON.parse(this.responseText);

        globalThis.setState({ isUploadingFlag: false });
        if (response != '' && response.response_code === NC.successCode) {
          var imagePath = response.data.image_url;
          globalThis.setState({
            updatedFlagURL: imagePath,
            updatedFlagName: response.data.image_name,
            savePosting: false
          })
        }
        else {
          notify.show(response.message, "error", 5000);
        }
      }
    });

    xhr.open("POST", NC.baseURL + NC.UPLOAD_TEAM_FLAG);
    xhr.setRequestHeader('Sessionkey', WSManager.getToken());
    xhr.send(data);
  }

  uploadJerseyImage() {
    this.setState({
      isUploadingJersey: true,
      savePosting: true
    });
    var data = new FormData();
    data.append("file", this.state.updatedJersey);
    data.append("name", this.state.updatedJersey.name);
    data.append("player_id", this.state.team_data.team_id);
    data.append("sports_id", this.state.selected_sport)
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.addEventListener("readystatechange", function () {
      if (this.readyState === 4) {
        var response = JSON.parse(this.responseText);
        globalThis.setState({ isUploadingJersey: false });
        if (response != '' && response.response_code === NC.successCode) {
          var imagePath = response.data.image_url;
          globalThis.setState({
            updatedJerseyURL: imagePath,
            updatedJerseyName: response.data.image_name,
            savePosting: false
          })
        }
        else {
          notify.show(response.message, "error", 5000);
        }
      }
    });

    xhr.open("POST", NC.baseURL + NC.UPLOAD_TEAM_JERSEY);
    xhr.setRequestHeader('Sessionkey', WSManager.getToken());
    xhr.send(data);
  }

  // SAVE TEAM 
  saveTeam = () => {
    let teamData = this.state.team_data;
    let teamList = _.cloneDeep(this.state.teamList)
    let selectedObj = teamList[teamData.team_index];
    let param = {
      team_id: teamData.team_id,
      team_abbr: teamData.team_abbr,
      team_name: teamData.team_name,
      twitter_handles: teamData.twitter_handles,
      sports_id: teamData.sports_id
    };
    if (this.state.updatedFlagName != '' && this.state.updatedFlagURL != '') {
      param.flag = this.state.updatedFlagName;
      selectedObj.flag = this.state.updatedFlagName;
      selectedObj.flag_url = this.state.updatedFlagURL;
    }

    if (this.state.updatedJerseyName != '' && this.state.updatedJerseyURL != '') {
      param.jersey = this.state.updatedJerseyName;
      selectedObj.jersey = this.state.updatedJerseyName;
      selectedObj.jersey_url = this.state.updatedJerseyURL;
    }
    this.setState({
      posting: true
    })

    WSManager.Rest(NC.baseURL + NC.EDIT_TEAM_INFO, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        teamList[param.team_index] = selectedObj;
        this.setState({
          posting: false,
          editTeamModal: !this.state.editTeamModal,
          updatedFlag: '',
          updatedFlagURL: '',
          updatedFlagName: '',
          updatedJersey: '',
          updatedJerseyName: '',
          updatedJerseyURL: '',
          team_data: { team_name: "", team_abbr: "", association_id: "", twitter_handles: "", sports_id: "", flag: "", jersey: "" },
          teamList: teamList
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
      else {
        this.setState({
          posting: false
        })
      }
    })
  }
  // SAVE TEAM End

  handleFieldVal = (e) => {

    const name = e.target.name;
    const value = e.target.value;
    this.setState({
      [name]: value
    }, function () {
      if (name === 'search_text') {
        this.GetAllTeamList();
      }
    })
  }

  handleSelect = (value, dropName) => {
    if (value) {
      if (dropName == "selected_league") {
        this.setState({ selected_league: value.value }, function () {
          this.GetAllTeamList();
        });
      } else {
        this.setState({ selected_sport: value.value }, function () {
          this.GetAllTeamList();
        });
      }
    }
  }

  render() {
    let {
      leagueList,
      teamList,
      team_data,
      savePosting
    } = this.state

    return (
      <div className="animated fadeIn team-list">
        <Col lg={12}>
          <Row className="dfsrow">
            <h2 className="h2-cls">Team List</h2>
          </Row>
        </Col>
        <Row>
          <Col xs="12" sm="12" md="12">
            <FormGroup className="float-right">
              <InputGroup>
                <Input type="text" id="search_text" name="search_text" value={this.state.search_text} onChange={(e) => this.handleFieldVal(e)} placeholder="Team Name" />
                <InputGroupAddon addonType="append" onClick={() => this.searchTeam()}>
                  <InputGroupText><i className="fa fa-search"></i></InputGroupText>
                </InputGroupAddon>
              </InputGroup>
            </FormGroup>
            {/* <FormGroup className="float-right">
              <Select
                className="dfs-selector"
                id="selected_league"
                name="selected_league"
                placeholder="Select League"
                value={this.state.selected_league}
                options={leagueList}
                onChange={(e) => this.handleSelect(e, 'selected_league')}
              />
            </FormGroup> */}
          </Col>
        </Row>
        {
          !_.isEmpty(teamList)
            ?
            _.map(teamList, (item, index) => {
              return (
                <Row key={item.team_id}>
                  <Col xs="12" sm="12" md="12" >
                    <Card className="mb-2">
                      <CardBody className="p-0">
                        <Row>
                          <Col sm="4" md="4" lg="4" className="team-item">
                            <Label><strong className="teamrow-heading">{item.team_name} ({item.team_abbr})</strong></Label>
                          </Col>
                          <Col sm="4" md="4" lg="4" className="team-item">
                            <span>
                              <img src={NC.S3 + NC.FLAG + item.flag} height="30" width="30" className="img-circle mr-3" />
                              <span className="text-muted pointer" onClick={() => this.toggleEditTeam(item, index)}>Upload Logo</span>
                            </span>
                          </Col>
                          <Col sm="4" md="4" lg="4" className="team-item">
                            <span>
                              <img src={NC.S3 + NC.JERSY + item.jersey} height="30" width="30" className="img-circle" />
                              <button className="btn btn-link text-muted" onClick={() => this.toggleEditTeam(item, index)}>Upload T-shirt</button>
                            </span>
                          </Col>
                        </Row>
                      </CardBody>
                    </Card>
                  </Col>
                </Row>
              )
            })
            :
            <div className="no-records">No Records Found.</div>
        }


        {/** EDIT TEAM FLAG/LOGO MODAL END */}
        <Modal isOpen={this.state.editTeamModal} toggle={() => this.toggleEditTeam()} className={this.props.className}>
          <ModalHeader>Edit Team ({team_data.team_name})</ModalHeader>
          <ModalBody>
            <Form method="post" className="form-horizontal" >
              <Row>
                <Col xs="12">
                  <FormGroup row>
                    <Col md="3">
                      <Label htmlFor="flag"><strong>Logo</strong></Label>
                    </Col>
                    <Col xs="12" md="9">
                      <Input id="flag" type="file" name="flag" placeholder="Flag"
                        accept="image/*"
                        ref={(ref) => this.upload = ref}
                        onChange={this.onDrop.bind(this)}
                      />
                      <div className='avatar_container edit mt-2'>
                        {(this.state.updatedFlagURL != '' || team_data.flag_url != "") && !this.state.isUploadingFlag &&
                         <img className='avatar_container' width='72px' height='72px' src={(this.state.updatedFlagURL != '') ? this.state.updatedFlagURL : team_data.flag_url} />
                        }
                      </div>
                    </Col>
                  </FormGroup>
                </Col>
              </Row>
              <Row>
                <Col xs="12">
                  <FormGroup row>
                    <Col md="3">
                      <Label htmlFor="jersey"><strong>T-shirt</strong></Label>
                    </Col>
                    <Col xs="12" md="9">
                      <Input type="file" id="jersey" name="jersey" placeholder="Jersey"
                        accept="image/*"
                        ref={(ref) => this.upload = ref}
                        onChange={this.onDrop.bind(this)}
                      />
                      <div className='avatar_container edit mt-2'>
                        {(this.state.updatedJerseyURL != '' || team_data.jersey_url != '') && !this.state.isUploadingJersey &&
                          <img className='avatar_container' width='72px' height='72px' src={(this.state.updatedJerseyURL != '') ? this.state.updatedJerseyURL : team_data.jersey_url} />
                        
                        }
                      </div>
                    </Col>
                  </FormGroup>
                </Col>
              </Row>
            </Form>
          </ModalBody>
          <ModalFooter className="pt-0 justify-content-center border-0">
            <Button
              className="btn xbtn-outline-danger xbtn-ladda btn-secondary-outline"
              onClick={() => this.saveTeam()}
              disabled={savePosting}
            >Save</Button>
          </ModalFooter>
        </Modal>
        {/** EDIT TEAM FLAG/LOGO MODAL END*/}
      </div>
    );
  }
}

export default Teams;