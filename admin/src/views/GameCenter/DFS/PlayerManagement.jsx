import React, { Component } from 'react';
import { Card, CardBody, Col, Row, Label, Modal, ModalBody, ModalHeader, ModalFooter, Form, FormGroup, Input, InputGroup, InputGroupAddon, InputGroupText, Button, Table } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import LS from 'local-storage';
import Images from "../../../components/images";
import Pagination from "react-js-pagination";
var globalThis = null;
class PlayerManagement extends Component {
  constructor(props) {
    super(props);
    this.state = {
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      updatedPImage: '',
      updatedPImageName: '',
      updatedPImageURL: '',
      search_text: '',
      teamList: [],
      sportList: [],
      posting: false,
      loadMoring: true,
      editTeamModal: false,
      player_data: { player_name: "", image: ""},
      expZoomIn: false,
      sportsListFormated: [],
      savePosting: true,
      CURRENT_PAGE: 1,
      PERPAGE: NC.ITEMS_PERPAGE_LG,
      TotalCount: 0
    };
  }

  componentDidMount() {
    globalThis = this;
    this.GetAllPlayerList();
  }

  handlePageChange(current_page) {
    if (current_page !== this.state.CURRENT_PAGE) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.GetAllPlayerList()
        });
    }
}

  // GET ALL Player LIST
  GetAllPlayerList = () => {
    let param = {
      "sports_id": this.state.selected_sport,
      "items_perpage": this.state.PERPAGE,
      "current_page": this.state.CURRENT_PAGE,
      "search": this.state.search_text
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_ALL_PLAYER_LIST, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        this.setState({
          posting: false,
          teamList: responseJson.player_list,
          TotalCount: responseJson.total || 0
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
      this.GetAllPlayerList();
    })
  }

  toggleEditTeam(item, index) {
    if (item) {
      this.setState({
        editTeamModal: !this.state.editTeamModal,
        player_data: { ...this.state.player_data, ...item, team_index: index },
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
    reader.onloadend = () => {
      this.setState({
        updatedPImage: mImage,
        selectedImage: reader.result
      }, function () {
        this.uploadPlayerImage();
      });
    }
    reader.readAsDataURL(mImage)
  }

  uploadPlayerImage() {
    this.setState({
      isUploadingPImage: true,
      savePosting: true
    });
    var data = new FormData();
    data.append("file", this.state.updatedPImage);
    data.append("name", this.state.updatedPImage.name);
    data.append("player_id", this.state.player_data.player_id);

    var xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.addEventListener("readystatechange", function () {
      if (this.readyState === 4) {
        var response = JSON.parse(this.responseText);
        globalThis.setState({ isUploadingPImage: false });
        if (response != '' && response.response_code === NC.successCode) {
          var imagePath = response.data.image_url;
          globalThis.setState({
            updatedPImageURL: imagePath,
            updatedPImageName: response.data.image_name,
            savePosting: false
          })
        }
        else {
          notify.show(response.message, "error", 5000);
        }
      }
    });

    xhr.open("POST", NC.baseURL + NC.UPLOAD_PLAYER_JERSEY);
    xhr.setRequestHeader('Sessionkey', WSManager.getToken());
    xhr.send(data);
  }

  // SAVE TEAM 
  saveTeam = () => {
    let playerData = this.state.player_data;
    let teamList = _.cloneDeep(this.state.teamList)
    let selectedObj = teamList[playerData.team_index];
    let param = {
      player_id: playerData.player_id,
      // image: playerData.image
    };
    if (this.state.updatedPImageName != '' && this.state.updatedPImageURL != '') {
      param.image = this.state.updatedPImageName;
      selectedObj.image = this.state.updatedPImageName;
      selectedObj.image_url = this.state.updatedPImageURL;
    }
    this.setState({
      posting: true
    })

    WSManager.Rest(NC.baseURL + NC.SAVE_PLAYER_IMAGE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        teamList[param.team_index] = selectedObj;
        this.setState({
          posting: false,
          editTeamModal: !this.state.editTeamModal,
          updatedPImage: '',
          updatedPImageName: '',
          updatedPImageURL: '',
          player_data: { player_name: "", image: "" },
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
        this.GetAllPlayerList();
      }
    })
  }

  render() {
    let {
      teamList,
      player_data,
      savePosting,
      TotalCount,
      PERPAGE,
      CURRENT_PAGE
    } = this.state

    return (
      <div className="animated fadeIn team-list">
        <Col lg={12}>
          <Row className="dfsrow">
            <h2 className="h2-cls">Player Management</h2>
          </Row>
        </Col>
        <Row>
          <Col xs="12" sm="12" md="12">
            <FormGroup className="float-right">
              <InputGroup>
                <Input type="text" id="search_text" name="search_text" value={this.state.search_text} onChange={(e) => this.handleFieldVal(e)} placeholder="Player Name/Country" />
                <InputGroupAddon addonType="append" onClick={() => this.searchTeam()}>
                  <InputGroupText><i className="fa fa-search"></i></InputGroupText>
                </InputGroupAddon>
              </InputGroup>
            </FormGroup>
          </Col>
        </Row>
        
        {
          !_.isEmpty(teamList)
            ?
            <>
            <Row>
                <Col sm={12} className="table-responsive common-table player-mng-tbl">
                    <Table>
                        <thead>
                            <tr>
                                <th>Player Name</th>
                                <th>ID</th>
                                <th>Country</th>
                                <th className="wid-20">Display Name</th>
                                <th>Position</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        {
                            _.map(teamList, (item, index) => {
                            return (
                                <tr key={item.player_uid}>
                                    <td>{item.full_name}</td>
                                    <td>{item.player_uid}</td>
                                    <td>{item.country || '-'}</td>
                                    <td className="wid-20">{item.display_name}</td>
                                    <td>{item.position || '-'}</td>
                                    <td className="plyr-img-sec">
                                      <span className="img-wrap">
                                        {
                                          item.image ?
                                          <img src={NC.S3 + NC.JERSY + item.image} className="img-circle" />:
                                          <img src={Images.DEFAULT_USER} className="img-circle" />
                                        }
                                      </span> 
                                      <span className="txt" onClick={() => this.toggleEditTeam(item, index)}>Upload Image</span>
                                    </td>
                                </tr>
                           
                        )
                        })
                    }

                    </tbody>
                    </Table>
                    <div className="custom-pagination float-right">
                        <Pagination
                            activePage={CURRENT_PAGE}
                            itemsCountPerPage={PERPAGE}
                            totalItemsCount={TotalCount}
                            pageRangeDisplayed={5}
                            onChange={e => this.handlePageChange(e)}
                        />
                    </div>
                </Col>
            </Row>
            </>
            :
            <div className="no-records">No Records Found.</div>
        }


        {/** EDIT Player Image MODAL END */}
        <Modal isOpen={this.state.editTeamModal} toggle={() => this.toggleEditTeam()} className={'edit-player-img-modal'}>
          <ModalHeader>
            Upload Image
          </ModalHeader>
          <ModalBody>
            <Form method="post" className="form-horizontal" >
              <Row>
                <Col xs="12">
                  <FormGroup row>
                    <Col sm={6} className="text-right" style={{paddingRight: 20}}>
                      <div className='avatar_container edit mt-2'>
                        {(this.state.updatedPImageURL != '' || player_data.image_url != '') && !this.state.isUploadingPImage &&
                          <img className='avatar_container' width='150px' height='150px' src={(this.state.updatedPImageURL != '') ? this.state.updatedPImageURL : player_data.image_url} />
                        }
                      </div>
                    </Col>
                    <Col sm={6} style={{paddingLeft: 20,paddingTop: 70}}>
                      <Input type="file" id="pimage" name="pimage" placeholder="pplayer-image"
                        accept="image/*"
                        ref={(ref) => this.upload = ref}
                        onChange={this.onDrop.bind(this)}
                      />
                    </Col>
                  </FormGroup>
                </Col>
              </Row>
            </Form>
          </ModalBody>
          <ModalFooter className="justify-content-center border-0">
            <Button
              className="btn xbtn-outline-danger xbtn-ladda btn-secondary-outline"
              onClick={() => this.saveTeam()}
              disabled={savePosting}
            >Save</Button>
          </ModalFooter>
        </Modal>
        {/** EDIT Player Image MODAL END*/}
      </div>
    );
  }
}

export default PlayerManagement;