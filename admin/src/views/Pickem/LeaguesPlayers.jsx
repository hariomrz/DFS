import React, { Component, Fragment } from 'react';
import { Row, Col, Input, ModalBody, Modal, Button, ModalHeader, ModalFooter } from 'reactstrap';
import _ from 'lodash';
import Images from '../../components/images';
import Select from 'react-select';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { getAllSport, createLeague, getNewLeagues, createPlayer, getPlayers, savePlayerImage, editPlayer, editLeague } from '../../helper/WSCalling';
import moment from 'moment';
import Loader from '../../components/Loader';
import InfiniteScroll from 'react-infinite-scroll-component';
import LS from 'local-storage';
import WSManager from '../../helper/WSManager';
import { ERR_LEAGUE_MSG, ERR_PLAYER_NAME_MSG, ERR_PLAYER_ABBR_MSG } from "../../helper/Message";
import SelectDropdown from "../../components/SelectDropdown";
class LeaguesPlayers extends Component {
    constructor(props) {
        super(props);
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            leagueModalOpen: false,
            playerModalOpen: false,
            SelectedSport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            SelectedChange: "7",
            HideBox: 0,
            LeagueList: [],
            legueFormValid: false,
            LeagueMsg: true,
            PlayerFormValid: false,
            PlayerNameMsg: true,
            PlayerAbbrMsg: true,
            SearchValue: "",
            PagePosting: false,
            PlayersPosting: false,
            hasMore: false,
            disabledLeague: false,
        };
        this.searchReq = _.debounce(this.searchReq.bind(this), 500)
    }

    componentDidMount() {
        this.getAllLeagues()
    }

    playerInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        this.setState({ [name]: value }, () => this.validatePlayerForm(name, value))
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => this.validateLeagueForm(dateType, date))
    }

    addLeagueModalToggle = (item, flag) => {
        this.setState({
            ITEM_EDIT_LEAGUE: item,
            EDIT_LEAGUE_ID: !_.isEmpty(item) ? item.league_id : '',
            LeagueName: !_.isEmpty(item) ? item.league_name : '',
            FromDate: !_.isEmpty(item) ? new Date(WSManager.getUtcToLocal(item.league_schedule_date)) : '',
            ToDate: !_.isEmpty(item) ? new Date(WSManager.getUtcToLocal(item.league_last_date)) : '',
            SelectedChange: !_.isEmpty(item) ? item.sports_id : '',
            leagueAddEditFlag: flag,
            leagueModalOpen: !this.state.leagueModalOpen,
            LeagueMsg: true,
            disabledLeague: false,
        }, this.getAllSport)
        if (flag == 2) {
            this.setState({
                legueFormValid: true,
                disabledLeague: true
            })
        }
    }

    leagueInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        this.setState({ [name]: value }, () => this.validateLeagueForm(name, value))
    }

    addLeagueModal() {
        let { leagueAddEditFlag, disabledLeague, legueFormValid, SelectedChange, SportsOptions, LeagueName, FromDate, ToDate, LeagueMsg } = this.state
        return (
            <Modal isOpen={this.state.leagueModalOpen} toggle={() => this.addLeagueModalToggle('', '')} className="add-league-modal modal-sm">
                <ModalHeader>{leagueAddEditFlag === 1 ? 'Add' : 'Edit'} League</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <label>Select Sport</label>
                            <Select
                                isSearchable={true}
                                className="form-control"
                                options={SportsOptions}
                                value={SelectedChange}
                                onChange={e => this.handleTypeChange(e)}
                                placeholder="Select Your Sport"
                                disabled={disabledLeague}
                            />
                        </Col>
                    </Row>
                    <Row className="mt-30">
                        <Col md={12}>
                            <label>Enter League Name</label>
                            <Input
                                maxLength={30}
                                type="text"
                                name="LeagueName"
                                placeholder="Enter League Name"
                                value={LeagueName}
                                onChange={(e) => this.leagueInputChange(e)}
                            />
                            {
                                !LeagueMsg &&
                                <span className="color-red">{ERR_LEAGUE_MSG}</span>
                            }
                        </Col>
                    </Row>
                    <Row className="mt-30">
                        <div className="date-box">
                            <Col md={12}>
                                <label>Select date</label>
                                <Row>
                                    <Col md={12}>
                                        <label className="xmb-0 d-block">
                                            <DatePicker
                                                disabled={disabledLeague}
                                                minDate={new Date()}
                                                className="form-control"
                                                showYearDropdown='true'
                                                selected={FromDate}
                                                onChange={e => this.handleDateFilter(e, "FromDate")}
                                                placeholderText="Start Date"
                                                showTimeSelect
                                                timeFormat="HH:mm"
                                                timeIntervals={10}
                                                timeCaption="time"
                                                dateFormat="dd/MM/yyyy h:mm aa"
                                            />
                                            <i className="icon-calender"></i>
                                        </label>
                                    </Col>
                                </Row>
                                <Row>
                                    <Col md={12}>
                                        <label className="mb-0 d-block">
                                            <DatePicker
                                                disabled={disabledLeague}
                                                minDate={FromDate}
                                                className="form-control xml-3"
                                                showYearDropdown='true'
                                                selected={ToDate}
                                                onChange={e => this.handleDateFilter(e, "ToDate")}
                                                placeholderText="End Date"
                                                showTimeSelect
                                                timeFormat="HH:mm"
                                                timeIntervals={10}
                                                timeCaption="time"
                                                dateFormat="dd/MM/yyyy h:mm aa"
                                            />
                                            <i className="icon-calender"></i>
                                        </label>
                                    </Col>
                                </Row>
                            </Col>
                        </div>
                    </Row>
                </ModalBody>
                <ModalFooter>
                    <Button
                        disabled={!legueFormValid}
                        className="btn-secondary-outline"
                        onClick={this.createNewLeague}>Save</Button>
                </ModalFooter>
            </Modal>
        )
    }

    validateLeagueForm(name, value) {
        let { LeagueName, FromDate, ToDate } = this.state
        let ValidLeague = LeagueName
        let ValidStartDate = FromDate ? moment(FromDate).format("YYYY-MM-DD") : ''
        let ValidToDate = ToDate ? moment(ToDate).format("YYYY-MM-DD") : ''

        switch (name) {
            case 'LeagueName':
                ValidLeague = (value.length > 2 && value.length < 61) ? true : false;
                this.setState({ LeagueMsg: ValidLeague })
                break;
            case 'FromDate':
                ValidStartDate = !_.isUndefined(value) ? true : false;
                break;
            case 'ToDate':
                ValidToDate = !_.isUndefined(value) ? true : false;
                break;
            default:
                break;
        }
        this.setState({
            legueFormValid: ValidLeague && ValidStartDate && ValidToDate
        })
    }

    addPlayerModalToggle = (leagueName, leagueId, leagueItem, itemIndex, addEditFlag) => {
        this.setState({
            TEAM_ID: !_.isEmpty(leagueItem) ? leagueItem.team_id : '',
            LEAGUE_ID: leagueId,
            addEditFlag: addEditFlag,
            LEAGUE_NAME: leagueName,
            PlayerName: !_.isEmpty(leagueItem) ? leagueItem.team_name : '',
            PlayerAbbr: !_.isEmpty(leagueItem) ? leagueItem.team_abbr : '',
            image_name: !_.isEmpty(leagueItem) ? leagueItem.flag : '',
            fileName: !_.isEmpty(leagueItem) ? NC.S3 + NC.PT_TEAM_FLAG + leagueItem.flag : '',
            ITEM_INDEX: itemIndex,
            playerModalOpen: !this.state.playerModalOpen
        })
        if (addEditFlag == 2) { this.setState({ PlayerFormValid: true }) }
    }

    addPlayerModal() {
        let { addEditFlag, fileName, LEAGUE_NAME, PlayerFormValid, PlayerName, PlayerAbbr, PlayerNameMsg, PlayerAbbrMsg } = this.state
        return (
            <Modal isOpen={this.state.playerModalOpen} toggle={() => this.addPlayerModalToggle('', '', '', '', '')} className="add-league-modal modal-md">
                <ModalHeader>{addEditFlag == 1 ? "Add" : "Edit"} Team/Player ({LEAGUE_NAME})</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={6}>
                            <label>Team/Player Name</label>
                            <Input
                                maxLength={30}
                                type="text"
                                name="PlayerName"
                                placeholder="Enter Name"
                                value={PlayerName}
                                onChange={(e) => this.playerInputChange(e)}
                            />
                            {
                                !PlayerNameMsg &&
                                <span className="color-red">{ERR_PLAYER_NAME_MSG}</span>
                            }
                        </Col>
                        <Col md={6}>
                            <label>Abbreviation</label>
                            <Input
                                maxLength={7}
                                type="text"
                                name="PlayerAbbr"
                                placeholder="Enter Abbr"
                                value={PlayerAbbr}
                                onChange={(e) => this.playerInputChange(e)}
                            />
                            {
                                !PlayerAbbrMsg &&
                                <span className="color-red">{ERR_PLAYER_ABBR_MSG}</span>
                            }
                        </Col>
                    </Row>
                    <Row className="mt-5">
                        <Col md={12}>
                            <div className="upload-img-box">
                                <span>Image</span>
                                <span className="no-img">
                                    <img src={fileName ? fileName : Images.no_image} className="img-cover" alt="" />
                                </span>
                                <span className="upload-box">
                                    <Button>Upload</Button>
                                    <Input
                                        accept="image/x-png"
                                        type="file"
                                        className="select-img"
                                        onChange={this.onChangeImage}
                                    />
                                    <div className="pt-help-text">Size 64 * 42</div>
                                </span>
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter>
                    <Button
                        disabled={!PlayerFormValid}
                        className="btn-secondary-outline"
                        onClick={this.createNewPlayer}>Save</Button>
                </ModalFooter>
            </Modal>
        )
    }

    validatePlayerForm(name, value) {
        let { PlayerName, PlayerAbbr, image_name } = this.state
        let ValidPlayerName = PlayerName
        let ValidPlayerAbbr = PlayerAbbr
        let ValidLogo = image_name
        switch (name) {
            case 'PlayerName':
                ValidPlayerName = (value.length > 2 && value.length < 31) ? true : false;
                this.setState({ PlayerNameMsg: ValidPlayerName })
                break;
            case 'PlayerAbbr':
                ValidPlayerAbbr = (value.length > 1 && value.length < 8) ? true : false;
                this.setState({ PlayerAbbrMsg: ValidPlayerAbbr })
                break;
            case 'image_name':
                ValidLogo = (value.length > 1) ? true : false;
                break;
            default:
                break;
        }
        this.setState({
            PlayerFormValid: ValidPlayerName && ValidPlayerAbbr && ValidLogo
        })
    }

    handleTypeChange = (value) => {
        this.setState({ SelectedChange: value.value })
    }

    onChangeImage = (event) => {
        this.setState({
            fileName: URL.createObjectURL(event.target.files[0]),
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("file", file);
        savePlayerImage(data).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    image_name: Response.data.image_name
                }, () => this.validatePlayerForm('image_name', this.state.image_name));
            } else {
                this.setState({
                    fileName: '',
                    image_name: '',
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        });
    }

    togglePlayersBox = (index, league_id) => {
        this.setState({ PlayersList: [] })
        this.getAllPlayers(league_id)
        this.setState({ HideBox: index })
    }

    getAllSport() {
        getAllSport({}).then(Response => {
            let options = []
            let obj = {}
            if (Response.response_code == NC.successCode) {
                this.setState({
                    AllSports: Response.data
                }, () => {
                    _.map(this.state.AllSports, (item, idx) => {
                        obj = { value: item.sports_id, label: item.sports_name }
                        options.push(obj)
                    })
                    this.setState({
                        SportsOptions: options
                    })
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    diff_minutes(fromdate, todate) {
        let today = new Date(fromdate);
        let Christmas = new Date(todate);
        let diffMs = (Christmas - today);
        let diffDays = Math.floor(diffMs / 86400000); // days
        let diffHrs = Math.floor((diffMs % 86400000) / 3600000); // hours
        let diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000);//Min 
        return diffMins
    }

    createNewLeague = () => {
        this.setState({ legueFormValid: false })
        let { SelectedChange, LeagueName, FromDate, ToDate, EDIT_LEAGUE_ID, leagueAddEditFlag, ITEM_EDIT_LEAGUE } = this.state
        let params = ""
        let URL = ""
        if (leagueAddEditFlag == 1) {            
            // if (this.diff_minutes(FromDate, ToDate) <= 0) {
            if (ToDate <= FromDate) {
                notify.show("League end date should be greater than start date", "error", 5000)
                this.setState({ legueFormValid: true })
                return false;
            }
            params = {
                sports_id: SelectedChange,
                league_name: LeagueName,
                league_schedule_date: FromDate ? moment.utc(FromDate).format() : '',
                league_last_date: ToDate ? moment.utc(ToDate).format() : '',
            }
            
            URL = createLeague(params)
        } else {
            params = {
                league_id: EDIT_LEAGUE_ID,
                league_name: LeagueName
            }
            URL = editLeague(params)
        }

        URL.then(Response => {
            if (Response.response_code == NC.successCode) {
                this.addLeagueModalToggle(ITEM_EDIT_LEAGUE, 1)
                this.getAllLeagues()
                this.setState({
                    LeagueName: '',
                    FromDate: '',
                    ToDate: '',
                    HideBox: 0,
                    legueFormValid: true
                })
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    createNewPlayer = () => {
        this.setState({ PlayerFormValid: false })
        let { PlayerName, PlayerAbbr, LEAGUE_ID, TEAM_ID, image_name, addEditFlag } = this.state

        let params = {
            team_name: PlayerName,
            team_abbr: PlayerAbbr,
            image_name: image_name
        }

        let URL = ''
        if (addEditFlag == 1) {
            params.league_id = LEAGUE_ID
            URL = createPlayer(params)
        } else {
            params.team_id = TEAM_ID
            URL = editPlayer(params)
        }

        URL.then(Response => {
            if (Response.response_code == NC.successCode) {
                this.addPlayerModalToggle('', '', '', '', '')
                this.getAllPlayers(LEAGUE_ID)
                this.setState({
                    PlayerName: '',
                    PlayerAbbr: '',
                    image_name: '',
                    PlayerFormValid: true
                })
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getAllLeagues = () => {
        this.setState({ PagePosting: true })
        let { SelectedSport, SearchValue, PERPAGE, CURRENT_PAGE, EDIT_LEAGUE_ID } = this.state

        let params = {
            sports_id: SelectedSport,
            search_text: SearchValue,
            limit: PERPAGE,
            current_page: CURRENT_PAGE,
            type: '2',
        }

        getNewLeagues(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                if (CURRENT_PAGE > 1) {
                    this.setState({
                        LeagueList: [...this.state.LeagueList, ...Response.data.league_list],
                    })
                } else {
                    this.setState({
                        LeagueList: Response.data.league_list,
                    })
                }

                this.setState({
                    hasMore: Response.data.league_list.length == PERPAGE,
                    PagePosting: false
                }, () => {
                    if (!_.isEmpty(this.state.LeagueList))
                        this.getAllPlayers(!_.isEmpty(EDIT_LEAGUE_ID) ? EDIT_LEAGUE_ID : this.state.LeagueList[0].league_id)
                })

            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getAllPlayers = (ID) => {
        this.setState({ PlayersPosting: true })
        let params = {
            league_id: ID
        }
        getPlayers(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    PlayersList: Response.data.team_list,
                    PlayersPosting: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleSearchInput = (e) => {
        this.setState({
            CURRENT_PAGE: 1,
            SearchValue: e.target.value
        }, this.searchReq)
    }

    searchReq() {
        this.getAllLeagues()
    }

    fetchMoreData = () => {
        let CURRENT_PAGE = this.state.CURRENT_PAGE + 1;
        this.setState({
            CURRENT_PAGE
        }, this.getAllLeagues
        );
    }

    handleSportsChange = (value) => {
        if (!_.isNull(value)) {
            this.setState({ SelectedSports: value.value }, () => {
                this.getAllLeagues()
            })
        }
    }

    render() {
        let { PagePosting, HideBox, LeagueList, PlayersList, SearchLeague, PlayersPosting, hasMore, SelectedSports, sportsOptions } = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "pt-sel-sport",
            sel_options: sportsOptions,
            place_holder: "Select Sports",
            selected_value: SelectedSports,
            modalCallback: this.handleSportsChange
        }
        return (
            <Fragment>
                <div className="leagues-player">
                    <Row>
                        <Col md={12}>
                            <div className="float-left">
                                <h2 className="h2-cls">Leagues/Players</h2>
                            </div>
                            <div className="float-right mt-3">
                                <div
                                    onClick={() => {
                                        this.props.history.goBack()
                                    }}
                                    className="back-to-fixtures">{'< Back to Manage league'}
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <hr className="pt-mg"/>
                    <Row>
                        <Col md={12}>
                            {/* <div className="float-left">
                                <div className="search-box">
                                    <label className="sel-lable" htmlFor="ptSelectSport">Select Sport</label>
                                    <SelectDropdown SelectProps={Select_Props} />
                                </div>
                            </div> */}
                            <div className="float-right">
                                <div className="search-box">
                                    <Input
                                        type="text"
                                        className="search-league"
                                        name="SearchLeague"
                                        placeholder="Search League"
                                        value={SearchLeague}
                                        onChange={(e) => this.handleSearchInput(e)}
                                    />
                                    <i className="icon-search"></i>
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <Row className="league-accordian">
                        <Col md={12}>
                            <InfiniteScroll
                                dataLength={LeagueList.length}
                                next={this.fetchMoreData.bind()}
                                hasMore={hasMore}
                                loader={PagePosting && <Loader hide />}
                            >
                                {
                                    !PagePosting ?
                                        _.map(LeagueList, (item, idx) => {
                                            return (
                                                <Fragment key={idx}>
                                                    <div className="league-box">
                                                        <div onClick={() => this.togglePlayersBox(idx, item.league_id)} className="league-name">{item.league_name}</div>
                                                        <i onClick={() => this.addLeagueModalToggle(item, '2')} className="icon-edit"></i>
                                                    </div>

                                                    <div className={`player-info clearfix ${HideBox != idx ? "hide-players" : ""}`} >
                                                        {
                                                            (!PlayersPosting) ?
                                                                // HideBox == idx ?
                                                                _.map(PlayersList, (leagues, index) => {
                                                                    return (
                                                                        <Fragment key={index}>
                                                                            <div className="team-name">
                                                                                <figure className="img-container">
                                                                                    <img src={leagues.flag ? NC.S3 + NC.PT_TEAM_FLAG + leagues.flag : Images.dummy_user} alt="" className="img-cover" />
                                                                                    <div className="league">{leagues.team_name}</div>
                                                                                </figure>
                                                                                <i onClick={() => this.addPlayerModalToggle(item.league_name, item.league_id, leagues, index, 2)} className="icon-edit"></i>
                                                                            </div>
                                                                        </Fragment>
                                                                    )
                                                                })
                                                                // :
                                                                // ''
                                                                :
                                                                <Loader hide />
                                                        }
                                                        <label onClick={() => this.addPlayerModalToggle(item.league_name, item.league_id, '', '', 1)} className="add-league league-name">
                                                            <i className="icon-addmore float-left"></i>
                                                            <span className="league-title">Add Team / Players</span>
                                                        </label>
                                                    </div>
                                                </Fragment>
                                            )
                                        })
                                        :
                                        <Loader />
                                }
                            </InfiniteScroll>
                            {this.addPlayerModal()}
                            {this.addLeagueModal()}
                            <label onClick={() => this.addLeagueModalToggle('', 1)} className="add-league league-box">
                                <i className="icon-addmore float-left"></i>
                                <span className="league-title">Add League</span>
                            </label>

                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}

export default LeaguesPlayers;
