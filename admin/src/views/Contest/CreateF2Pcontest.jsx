import React, { Component, Fragment } from 'react';
import Select from 'react-select';
import { Card, CardBody, CardHeader, Col, Row, Input, Button, Table, Tooltip } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import LS from 'local-storage';
import Images from '../../components/images';
import { notify } from 'react-notify-toast';
class CreateF2PContest extends Component {

  constructor(props) {
    super(props);
    this.toggle2 = this.toggle2.bind(this);
    this.state = {
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,
      season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
      collection_master_id: (this.props.match.params.collection_master_id) ? this.props.match.params.collection_master_id : '',
      fixtureDetail: {},
      groupList: [],
      contestType: [],
      multipleLineupList: [],
      entryFeeType: [],
      winnerPlace: [],
      payout_data: [],
      contestObject: {
        "prize_pool_type": '2', "contest_name": "",
        'prize_type': '1', "master_contest_type_id": "1",
        'minimum_size': '2', 'size': '2',
        'prize_value_type': '0', 'is_tie_breaker': "1", 'sponsor_name': '',
        'sponsor_logo': '', 'sponsor_link': '', 'set_sponsor': '0', "multiple_lineup": "1", "video_link": ''
      },
      merchandiseList: [],
      merchandiseArrList: {},      
      prizeType: [],
      merchandiseObj: {},
      prize_profit: { min_total: 0, max_total: 0, min_gross_profit: 0, max_gross_profit: 0, min_net_profit: 0, max_net_profit: 0 },
      SPONSER_IMAGE_NAME: '',
      SPONSER_PIMAGE_NAME: '',
      selectSetPrize: false,
      selectUnsetPrize: true,
      wrongLink: true
    };
  }

  componentDidMount() {
    this.GetFixtureDetail();
    this.initPayoutData();
    this.GetContestTemplateMasterData();
  }

  GetFixtureDetail = () => {
    let param = {
      "league_id": this.state.league_id,
      "sports_id": this.state.selected_sport,
      "season_game_uid": this.state.season_game_uid,
      "collection_master_id": this.state.collection_master_id,
    }
    this.setState({ posting: true });

    WSManager.Rest((!this.state.collection_master_id) ? NC.baseURL + NC.GET_SEASON_DETAILS : NC.baseURL + NC.GET_COLLECTION_SEASON_DETAILS, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let fixtureDetail = responseJson.data;
        this.setState({ posting: false, fixtureDetail: fixtureDetail }, function () {
          this.GetContestTemplateMasterData();
        });

        if (this.state.collection_master_id) {
          let tempArr = [];
          let tempArr1 = [];
          _.map(fixtureDetail, (fixtureitem, fixtureindex) => {
            if (typeof fixtureitem.season_game_uid != 'undefined') {
              tempArr.push(fixtureitem.season_game_uid);

            }
          });

          this.setState({ season_game_uid: tempArr, season_scheduled_date: fixtureDetail[0].season_scheduled_date })
        }
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  initPayoutData = () => {
    let contestObject = _.cloneDeep(this.state.contestObject);
    var payout_data = [];
    var prize_pool = 0;
    if (typeof this.state.contestObject.prize_pool != "undefined" && this.state.contestObject.prize_pool != "") {
      prize_pool = this.state.contestObject.prize_pool;
    }
    contestObject.custom_total_percentage = 100;
    contestObject.custom_total_amount = prize_pool;
    if (isNaN(prize_pool)) {
      return false;
    }
    var empty_payout = {
      min: 1,
      max: 1,
      per: 100,
      amount: parseFloat(prize_pool).toFixed(2),
      row: ''
    };
    payout_data.push(empty_payout);
    this.setState({ payout_data: payout_data, contestObject: contestObject });
  }

  GetContestTemplateMasterData = () => {
    this.setState({ posting: true })
    WSManager.Rest(NC.baseURL + NC.GET_CONTEST_TEMPLATE_MASTER_DATA, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;

        let contestObject = _.clone(this.state.contestObject);
        contestObject['max_bonus_allowed'] = responseJson.max_bonus_allowed;
        this.setState({ contestObject: contestObject });

        let groupList = [];
        responseJson.group_list.map(function (lObj, lKey) {
          groupList.push({ value: lObj.group_id, label: lObj.group_name });
        });
        let merchandiseList = [];
        let merchandiseArrList = {};
        responseJson.merchandise_list.map(function (lObj, lKey) {
          merchandiseList.push({ value: lObj.merchandise_id, label: lObj.name });
          merchandiseArrList[lObj.merchandise_id] = lObj.name;
        });
        let prizeTypeTmp = [];
        let merchandiseObj = {};
        responseJson.prize_type.map(function (lObj, lKey) {
          if (lObj.value == 3) {
            merchandiseObj = lObj;
          } else {
            prizeTypeTmp.push(lObj);
          }
        });
        this.setState({ prizeType: prizeTypeTmp, merchandiseObj: merchandiseObj, max_bonus_allowed: responseJson.max_bonus_allowed, groupList: groupList, merchandiseList: merchandiseList, multipleLineupList: responseJson.multiple_lineup, entryFeeType: responseJson.currency_type, merchandiseArrList: merchandiseArrList });
      }
      this.setState({ posting: false })
    })
  }

  handleSelectPayout = (eleObj, idx, key_name) => {
    if (eleObj != null) {
      let payout_data = _.clone(this.state.payout_data);
      let key_value = eleObj.value;

      payout_data[idx]['min_value'] = "";
      payout_data[idx]['max_value'] = "";
      payout_data[idx]['amount'] = ""

      payout_data[idx][key_name] = key_value;
      if (payout_data[idx]['prize_type'] == 3) {
        payout_data[idx]['per'] = 0;
        payout_data[idx]['min_value'] = "";
        payout_data[idx]['max_value'] = "";
      }
      if (key_name == "amount" && payout_data[idx]['prize_type'] == 3) {
        payout_data[idx]['per'] = 0;
        payout_data[idx]['min_value'] = this.state.merchandiseArrList[key_value];
        payout_data[idx]['max_value'] = this.state.merchandiseArrList[key_value];
      }
      this.setState({ payout_data: payout_data }, () => {
        this.calculateProfitData();
      });
    }
  }

  handleFieldVal = (e, tindex, element_id) => {
    if (e) {
      WSManager.removeErrorClass("contest_template_form", element_id);
      let name = '';
      let value = '';
      name = e.target.name;
      value = e.target.value;
      let contestObject = _.cloneDeep(this.state.contestObject)
      contestObject[tindex] = value

      var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
      if (name == 'sponsor_link' && value != '' && !pattern.test(value)) {

        this.setState({
          wrongLink: false
        })
        notify.show("please enter a valid sponsered link", "error", 5000);
      }
      else if (name == 'video_link' && value != '' && !value.match(/^(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/)) {
        this.setState({
          wrongLink: false
        })

        notify.show("please enter a valid youtube link", "error", 5000);
      } else {
        this.setState({
          wrongLink: true
        })
      }


      if (tindex == "max_bonus_allowed" && value > 100) {
        notify.show("Max alloed bonus should be less than equal to 100%", "error", 3000);
      }
      if (tindex == "prize_pool") {
        let max_prize_pool = parseFloat(contestObject.minimum_size * contestObject.entry_fee).toFixed(2);
        if (parseFloat(value).toFixed(2) != max_prize_pool) {
          contestObject['prize_pool_type'] = "2";
          contestObject['prize_value_type'] = "0";
        } else {
          contestObject['prize_pool_type'] = "2";
        }
      }

      this.setState({
        contestObject: contestObject
      }, function () {
        if (tindex == "prize_pool" || tindex == "max_bonus_allowed") {
          this.initPayoutData();
        }
      })
    }
  }

  handleSponsore = (e, tindex) => {
    if (e) {
      let value = '';
      value = e.target.value;
      let contestObject = _.cloneDeep(this.state.contestObject);
      contestObject[tindex] = value == '0' ? '1' : '0';
      this.setState({
        contestObject: contestObject
      })
    }
  }

  handlePrizeInPercentage = (e, tindex) => {
    if (e) {
      let value = e.target.value;
      let contestObject = _.cloneDeep(this.state.contestObject);
      contestObject[tindex] = value;
      this.setState({
        contestObject: contestObject
      }, function () {
        this.initPayoutData();
      })
    }
  }

  handleSetPrize = (e) => {

    if (e) {
      let value = e.target.checked;
      let name = e.target.name;

      this.setState({
        selectSetPrize: (name == 'selectSetPrize') ? true : false,
        selectUnsetPrize: (name == 'selectUnsetPrize') ? true : false
      }, function () {
      })
    }
  }
  
  CreateContest = () => {
    let contestObject = _.cloneDeep(this.state.contestObject);
    if (contestObject.master_contest_type_id != "" && contestObject.master_contest_type_id != "5" && contestObject.master_contest_type_id != "6") {
      if (parseInt(contestObject.minimum_size) < parseInt(this.state.winnerPlace[contestObject.master_contest_type_id])) {
        notify.show("Invalid winner selection. winner should be less then or equal to min size.", "error", 3000);
        return false
      }
    }

    if (contestObject.set_sponsor == '1' && (contestObject.sponsor_name == '' || contestObject.sponsor_logo == '')) {
      notify.show("Please fill sponser info.", "error", 3000);
      return false
    }

    if (contestObject.set_sponsor == 1 && !this.state.wrongLink) {
      notify.show("please enter a valid sponsered link", "error", 5000);
      return false
    }
    let yt = contestObject.video_link;
    if (yt != '' && !yt.match(/^(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/)) {
      notify.show("please enter a valid youtube link", "error", 5000);
      return false
    }

    let is_valid = 1;
    if (this.state.payout_data.length > 0 && this.state.selectSetPrize == true) {
      _.map(this.state.payout_data, (value, key) => {
        if (parseFloat(value.amount) <= 0) {
          is_valid = 0;
          notify.show("Please fill or delete unfilled rows", "error", 3000);
        }

        if (parseFloat(value.max) < parseFloat(value.min)) {
          is_valid = 0;
          notify.show("Maximum cant be less than minimum value", "error", 3000);
        }

        if (isNaN(value.amount)) {
          is_valid = 0;
          notify.show("Please set amount", "error", 3000);
        }

        if (key > 0) {
          let last_min = this.state.payout_data[key - 1].min;
          if (parseFloat(last_min) >= parseFloat(value.min)) {

            is_valid = 0;
            notify.show("Please remove duplicate rank distribution", "error", 3000);
          }

          let last_max = this.state.payout_data[key - 1].max;
          if (parseFloat(last_max) >= parseFloat(value.min)) {

            is_valid = 0;
            notify.show("Please remove duplicate rank distribution", "error", 3000);
          }
        }
      });
    }
    if (is_valid == 1) {
      contestObject['payout_data'] = (this.state.selectSetPrize == true) ? this.state.payout_data : [];
      contestObject['sports_id'] = this.state.selected_sport;
      contestObject['league_id'] = this.state.league_id;
      contestObject['season_game_uid'] = this.state.season_game_uid;
      contestObject['season_scheduled_date'] = (this.state.collection_master_id) ? this.state.season_scheduled_date : this.state.fixtureDetail.season_scheduled_date;
      contestObject['set_prize'] = (this.state.selectSetPrize == true) ? "1" : "0"
      if (WSManager.validateFormFields("contest_contest_form")) {
        this.setState({ posting: true })
        let params = contestObject;
        WSManager.Rest(NC.baseURL + NC.CREATE_F2P_CONTEST, params).then((responseJson) => {
          if (responseJson.response_code === NC.successCode) {
            notify.show(responseJson.message, "success", 5000);

            this.props.history.push({ pathname: '/contest/fixturecontest/' + this.state.league_id + '/' + this.state.season_game_uid })

          }
          this.setState({ posting: false })
        })
      } else {
        notify.show("Please fill required fields.", "error", 3000);
        return false;
      }
    }
  }

  calculatePrizePool = (e, tindex, element_value) => {
    let contestObject = _.cloneDeep(this.state.contestObject);
    if (tindex == "prize_pool_type") {
      contestObject[tindex] = element_value
      if (element_value == 0 || element_value == 1) {
        // contestObject['site_rake'] = this.state.site_rake;
      }
    } else {
      let value = e.target.value;
      contestObject[tindex] = value
    }
    contestObject['prize_pool'] = "";

    //set site_rake=0 for free contest
    if (contestObject.entry_fee && contestObject.entry_fee <= 0) {
      contestObject['max_bonus_allowed'] = "0";
      contestObject['prize_pool_type'] = "1";
    }

    if ((contestObject.entry_fee && contestObject.entry_fee > 0 && tindex == "entry_fee") || (contestObject.entry_fee && contestObject.entry_fee > 0)) {
      contestObject['max_bonus_allowed'] = this.state.max_bonus_allowed;
    }
    this.setState({ contestObject: contestObject });

    if (parseInt(contestObject.minimum_size) < 2) {
      notify.show("minimum size should be greater than equal to 2.", "error", 3000);
      return false
    }

    if (contestObject.minimum_size && contestObject.size && parseInt(contestObject.minimum_size) > parseInt(contestObject.size)) {
      notify.show("size should be greater than min size.", "error", 3000);
      return false;
    }

    if (contestObject.minimum_size && contestObject.size && contestObject.entry_fee) {
      let prize_pool = (contestObject.minimum_size * contestObject.entry_fee);
      prize_pool = prize_pool.toFixed(0);
      contestObject['prize_pool'] = prize_pool;
    }
    this.setState({ contestObject: contestObject }, function () {
      if (this.state.contestObject.prize_pool_type != '0') {
        this.initPayoutData();
      }
    });
  }

  initPayoutData = () => {
    let contestObject = _.cloneDeep(this.state.contestObject);
    var payout_data = [];
    var prize_pool = 0;
    if (typeof this.state.contestObject.prize_pool != "undefined" && this.state.contestObject.prize_pool != "") {
      prize_pool = this.state.contestObject.prize_pool;
    }
    contestObject.custom_total_percentage = 100;
    contestObject.custom_total_amount = prize_pool;
    if (isNaN(prize_pool)) {
      return false;
    }
    prize_pool = parseFloat(prize_pool).toFixed(0);
    let max_prize_pool = parseFloat(contestObject.size * contestObject.entry_fee).toFixed(0);
    if (isNaN(max_prize_pool)) {
      max_prize_pool = 0;
    }
    let amount = prize_pool;
    let min_value = prize_pool;
    let max_value = max_prize_pool;
    if (contestObject.prize_value_type == 1) {
      min_value = parseFloat(amount * prize_pool / 100).toFixed(2);
      max_value = parseFloat(amount * max_value / 100).toFixed(2);
    }
    var empty_payout = {
      min: 1,
      max: 1,
      prize_type: 1,
      per: 100,
      amount: amount,
      min_value: min_value,
      max_value: max_value
    };
    payout_data.push(empty_payout);
    this.setState({ payout_data: payout_data, contestObject: contestObject }, function () {
      this.calculateProfitData();
    });
  }

  addRow = () => {
    var payout_data = this.state.payout_data;
    var prize_pool = 0;
    if (typeof this.state.contestObject.prize_pool != "undefined" && this.state.contestObject.prize_pool != "") {
      prize_pool = this.state.contestObject.prize_pool;
    }
    if (isNaN(prize_pool)) {
      /*  notify.show("Please set prize pool.", "error", 3000);
       return false; */
    }

    if (payout_data.length > 0) {
      var max = payout_data[payout_data.length - 1].max;
      max = parseInt(max) + 1;
      var item = {
        min: max,
        max: max,
        prize_type: 1,
        per: 0,
        amount: 0,
        min_value: '',
        max_value: ''
      };
      payout_data.push(item);
      this.setState({ payout_data: payout_data });
    }
  };

  removeRow = (index) => {
    let payout_data = this.state.payout_data;
    if (payout_data.length > 1) {
      payout_data[index] = [];
      payout_data.splice(index, 1);
      this.setState({ payout_data: payout_data }, function () { });
      this.totalPercentage();
      this.calculateProfitData();
    }
  };

  validatePrizeData = (e, index) => {
    let { name, value } = e.target;
    const payout_data = this.state.payout_data;
    let item_val = value;
    if (value < payout_data[index].min && value != '' && name == 'max') {
      notify.show("Maximum size should not be less than Minimum size.", "error", 3000);
    }
    if (name == "min" || name == "max") {
      item_val = payout_data[index]['amount'];
    }
    if (name == "max") {
      payout_data[index]['max'] = value;
    }
    let prize_pool = this.state.contestObject.prize_pool;
    let max_prize_pool = item_val;
    let per = parseFloat(item_val / (prize_pool / 100)).toFixed(2);
    if (this.state.contestObject.prize_value_type === "1") {
      per = item_val;
      item_val = parseFloat((per / 100) * prize_pool).toFixed(2);
      max_prize_pool = parseFloat(this.state.contestObject.size * this.state.contestObject.entry_fee).toFixed(2);
      max_prize_pool = parseFloat((per / 100) * max_prize_pool).toFixed(2);
      if (payout_data[index]['min'] != payout_data[index]['max']) {
        var tmp_count = parseInt(payout_data[index]['max']) - parseInt(payout_data[index]['min']) + 1;
        item_val = parseFloat(item_val / tmp_count).toFixed(2);
        max_prize_pool = parseFloat(max_prize_pool / tmp_count).toFixed(2);
      }
    } else {
      per = parseFloat((item_val * 100) / prize_pool).toFixed(2);
      if (this.state.contestObject.prize_pool_type != "2") {
        max_prize_pool = parseFloat(this.state.contestObject.size * this.state.contestObject.entry_fee).toFixed(2);
        max_prize_pool = parseFloat((per / 100) * max_prize_pool).toFixed(2);
      }
    }


    if (isNaN(max_prize_pool)) {
      max_prize_pool = 0;
    }

    if ((parseInt(payout_data[index]['max']) > parseInt(payout_data[index]['min']))) {
      var tmp_user_count = parseInt(payout_data[index]['max']) - parseInt(payout_data[index]['min']) + 1;
      item_val = item_val * tmp_user_count;
      max_prize_pool = max_prize_pool * tmp_user_count;
    }

    if (isNaN(item_val) || item_val < 0) {
      item_val = 0;
    }
    if (isNaN(max_prize_pool) || max_prize_pool < 0) {
      max_prize_pool = 0;
    }

    if (max_prize_pool == 0 && this.state.contestObject.entry_fee == 0 && this.state.contestObject.prize_pool == 0) {
      max_prize_pool = item_val;
    }

    payout_data[index][name] = value;
    payout_data[index]['per'] = per;
    payout_data[index]['min_value'] = parseFloat(item_val).toFixed(2);
    payout_data[index]['max_value'] = parseFloat(item_val).toFixed(2);
    this.setState({ payout_data: payout_data });
    this.calculateProfitData();
  }

  calculateProfitData = () => {
    let prize_profit = _.cloneDeep(this.state.prize_profit);
    let min_total = 0;
    let max_total = 0;
    let contestObject = _.cloneDeep(this.state.contestObject);
    let prize_pool = contestObject.prize_pool;
    if (contestObject.prize_pool_type == 2) {
      prize_pool = parseFloat(this.state.contestObject.entry_fee * contestObject.minimum_size);
    }
    _.map(this.state.payout_data, (value, key) => {
      if (value.prize_type == "1" && value.min_value && value.max_value) {
        min_total = parseFloat(min_total) + parseFloat(value.min_value);
        max_total = parseFloat(max_total) + parseFloat(value.max_value);
      }
    });
    prize_profit.min_total = parseFloat(min_total).toFixed(2);
    prize_profit.max_total = parseFloat(max_total).toFixed(2);
    if (!prize_pool) {
      contestObject.site_rake = 0;
      prize_profit.min_gross_profit = 0;
    } else {
      prize_profit.min_gross_profit = parseFloat(prize_pool - min_total).toFixed(2);
    }
    if (!contestObject.entry_fee) {
      prize_profit.max_gross_profit = 0;
      prize_profit.min_net_profit = 0;
      prize_profit.max_net_profit = 0;
    } else {
      prize_profit.max_gross_profit = parseFloat((contestObject.entry_fee * contestObject.size) - max_total).toFixed(2);
      prize_profit.min_net_profit = parseFloat(prize_profit.min_gross_profit - (((contestObject.entry_fee * contestObject.max_bonus_allowed) / 100) * contestObject.minimum_size)).toFixed(2);
      prize_profit.max_net_profit = parseFloat(prize_profit.max_gross_profit - (((contestObject.entry_fee * contestObject.max_bonus_allowed) / 100) * contestObject.size)).toFixed(2);
    }
    if (contestObject.prize_pool_type == "2") {
      prize_profit.max_gross_profit = parseFloat(prize_profit.min_gross_profit).toFixed(2);
      prize_profit.max_net_profit = parseFloat(prize_profit.min_net_profit).toFixed(2);
    }
    if (prize_pool == 0) {
      contestObject.site_rake = 0;
    } else {
      let mgp = prize_profit.min_gross_profit > 0 ? prize_profit.min_gross_profit : 0;
      let pp = prize_pool > 0 ? prize_pool : 0;
      if (mgp == 0 || pp == 0) {
        contestObject.site_rake = 0;
      }
      else {
        contestObject.site_rake = ((mgp * 100) / prize_pool).toFixed(2);
      }
    }
    this.setState({ prize_profit: prize_profit, contestObject: contestObject });
  }

  totalPercentage = () => {
    var per_temp = 0;
    let contestObject = _.cloneDeep(this.state.contestObject);
    contestObject.custom_total_percentage = 100;
    contestObject.custom_total_amount = 0;

    _.map(this.state.payout_data, (value, key) => {
      if (value) {
        per_temp += parseFloat(value.per);
        var min = parseInt(value.min);
        var max = parseInt(value.max);
        contestObject.custom_total_amount += parseFloat(value.amount * (max - min + 1));
        if (value.amount <= 0) {
          notify.show("Please fill or delete the unfilled row.", "error", 3000);
        }

        if (this.state.contestObject.prize_pool_type == '2') {
          var size = this.state.contestObject.size;
        } else {
          var size = this.state.contestObject.minimum_size;
        }
        if (parseInt(min) > parseInt(max)) {
          var msg = "Maximum size should not be less than Minimum size.";
          notify.show(msg, "error", 3000);
        }

      }
    });

    contestObject.custom_total_percentage = per_temp.toFixed(2);
    contestObject.custom_total_amount = contestObject.custom_total_amount.toFixed(2);
    this.setState({ contestObject: contestObject });
  }

  onChangeMax = (index) => {
    let payout_data = this.state.payout_data;


    var min = payout_data[index].min;
    var max = payout_data[index].max;

    if (parseInt(min) > parseInt(max)) {
      notify.show("Maximum size should not be less than Minimum size.", "error", 3000);
    }

    if (payout_data.length > 0) {
      var size = this.state.contestObject.size;
      _.map(payout_data, (value, key) => {
        if (key > index && key > 0) {
          var max = payout_data[key - 1].max;
          payout_data[key].min = parseInt(max) + 1;
          payout_data[key].max = parseInt(max) + 1;
          payout_data[key].per = 0;
          payout_data[key].amount = parseFloat(0);
        }
      })
    }
    var remove_item = payout_data.length - index;
    remove_item = remove_item - 1;
    if (remove_item > 0) {
      payout_data = payout_data.splice(-remove_item);
    }

    this.setState({ payout_data: payout_data });

    this.onChangePercentage(index);
  }

  onChangePercentage = (index) => {
    let payout_data = this.state.payout_data;
    var per = parseFloat(payout_data[index].per);
    var max = parseInt(payout_data[index].max);
    var min = parseInt(payout_data[index].min);
    var prize_pool = parseFloat(this.state.contestObject.prize_pool);
    var person = (max - min) + 1;
    if (isNaN(prize_pool)) {
      //notify.show("Please set prize pool.", "error", 3000);
    }

    if (isNaN(per)) {
      //notify.show("Percent Should be a number.", "error", 3000);
    }
    var amount = ((prize_pool * per) / 100) / person;
    payout_data[index].amount = parseFloat(amount).toFixed(2);
    this.setState({ payout_data: payout_data });
    this.totalPercentage();
  }

  onChangeAmount = (index) => {
    let payout_data = this.state.payout_data;
    var amount = parseFloat(payout_data[index].amount);
    var max = parseInt(payout_data[index].max);
    var min = parseInt(payout_data[index].min);
    var prize_pool = parseFloat(this.state.contestObject.prize_pool);
    if (max < min) {
      max = min;
      notify.show("max winner should be greater then min.", "error", 3000);
      return false;
    }
    var person = (max - min) + 1;
    if (isNaN(prize_pool)) {
      // notify.show("Please set prize pool.", "error", 3000);
    }

    if (isNaN(amount)) {
      notify.show("Amount Should be a number.", "error", 3000);
    }

    var per = ((amount * 100) / prize_pool) * person;
    if (isNaN(per)) {
      per = 0;
    }
    payout_data[index].per = parseFloat(per).toFixed(2);
    this.setState({ payout_data: payout_data }, function () {
      this.totalPercentage();
    });
  }
  onChangeImage = (event) => {
    let contestObject = _.cloneDeep(this.state.contestObject);
    this.setState({
      SPONSER_IMAGE_NAME: URL.createObjectURL(event.target.files[0]),
    });
    const file = event.target.files[0];
    if (!file) {
      return;
    }
    var data = new FormData();
    data.append("file", file);
    if (this.state.SPONSER_PIMAGE_NAME != '') {
      data.append("previous_img", this.state.SPONSER_PIMAGE_NAME);
    }

    WSManager.multipartPost(NC.baseURL + NC.UPLOAD_CONTEST_TEMPLATE_SPONSER, data)
      .then(Response => {
        if (Response.response_code == NC.successCode) {
          contestObject.sponsor_logo = Response.data.image_name
          this.setState({
            SPONSER_PIMAGE_NAME: Response.data.image_name,
            contestObject: contestObject
          });
        } else {
          this.setState({
            SPONSER_IMAGE_NAME: null
          });
        }
      }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000);
      });
  }

  resetFile = () => {
    this.setState({
      SPONSER_IMAGE_NAME: null
    });
  }

  toggle2 = () => {
    this.setState({
      isShowContestnameToolTip: !this.state.isShowContestnameToolTip
    });
  }
 

  toggle7 = () => {
    this.setState({
      isShowPDToolTip: !this.state.isShowPDToolTip
    });
  }


  toggle9 = () => {
    this.setState({
      isShowSPToolTip: !this.state.isShowSPToolTip
    });
  }

  render() {
    let {
      contestObject,
      payout_data,
      prize_profit,
      SPONSER_IMAGE_NAME,
      SPONSER_PIMAGE_NAME,
      selectSetPrize,
      selectUnsetPrize,
    } = this.state
    return (
      <div className="animated fadeIn  create-dfs-contest">
        <Row className="mb-1">
          <Col xs="12" lg="12">
            <Card className="recentcom">
              <CardHeader className="contestcreate">
                <h5 className="DFScontest">Create F2P Contest</h5>
              </CardHeader>
              <CardBody className="contestcard">
                <Row>
                  <Col sm={4} md={4}>
                    <div class="form-group gray-form-group">
                      <label for="template_name" className="fixturevs">Enter Contest Name (Optional)
                          <span className="btn-information"><img id="isShowContestnameToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowContestnameToolTip} target="isShowContestnameToolTip" toggle={this.toggle2}>
                            Contest name
                            </Tooltip>
                        </span>
                      </label>
                      <input className="contestname required" id="contest_name" name="contest_name" value={contestObject.contest_name} onChange={(e) => this.handleFieldVal(e, 'contest_name', 'contest_name')} placeholder="Contest Name (eg. WIN 540)"></input>
                    </div>
                  </Col>
                </Row>

                <Row className="mt-4">
                  <Col sm={4}>
                    <label for="minimum_size" className="fixturevs">Participants</label>
                    <ul className="minmax-list">
                      <li className="minmax-item mr-3">
                        <div className="form-group minmax-size gray-form-group">
                          <input type="number" className="contestname min-max-size xminsize required" name="minimum_size" value={contestObject.minimum_size} onChange={(e) => (this.handleFieldVal(e, 'minimum_size', 'minimum_size'), this.calculatePrizePool(e, 'minimum_size'))} placeholder="Min. Size"></input>
                        </div>
                      </li>
                      <li className="minmax-item">
                        <div className="form-group minmax-size gray-form-group">
                          <input type="number" className="contestname min-max-size xmaxsize required" name="size" value={contestObject.size} onChange={(e) => (this.handleFieldVal(e, 'size', 'size'), this.calculatePrizePool(e, 'size'))} placeholder="Max. Size"></input>
                        </div>
                      </li>
                    </ul>
                  </Col>
                </Row>
              </CardBody>
            </Card>
          </Col>
        </Row>
        <Row>
          <Col md={12}>
            <Card className="recentcom">
              <CardBody className="contestcard">
                <div className="set-prizes">Set Prizes</div>
                <div className="set-prize-option">
                  <div className="prize-option">
                    <input type="checkbox"
                      name="selectSetPrize"
                      defaultChecked={selectSetPrize}
                      checked={selectSetPrize}
                      onChange={(e) => this.handleSetPrize(e)}
                    />
                    <span className="opt-text">Yes</span>
                  </div>
                  <div className="prize-option">
                    <input name="selectUnsetPrize" type="checkbox"
                      defaultChecked={selectUnsetPrize}
                      checked={selectUnsetPrize}
                      onChange={(e) => this.handleSetPrize(e)}
                    />
                    <span className="opt-text">No</span>
                  </div>
                </div>
              </CardBody>
            </Card>
          </Col>
        </Row>
        {this.state.selectSetPrize &&
          <Row className="mb-1">
            <Col xs="12" lg="12">
              <Card className="recentcom">
                <CardBody className="contestcard">
                  <Row>
                    <Col sm={4}>
                      <label className="fixturevs">Prize Distribution
                        <span className="btn-information"><img id="isShowPDToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowPDToolTip} target="isShowPDToolTip" toggle={this.toggle7}>
                            Customise how you want to give prizes to contest winners.
  In the case of Percentage, the prize pool will be increased according to each new team entry.
  By selecting Fixed value you are defining exact prize for each rank and it will be treated as a guaranteed contest. Prizes will not increase according to entries.
                       </Tooltip>
                        </span>
                      </label>
                      <div className="input-box radio-input-box">
                        <ul className="coupons-option-list">
                          <li className="coupons-option-item">
                            <div className="custom-radio">
                              <input type="radio" className="custom-control-input" id="is_fixed_value" name="prize_value_type" value="0" checked={contestObject.prize_value_type === '0'} onChange={(e) => this.handlePrizeInPercentage(e, 'prize_value_type')} />
                              <label className="custom-control-label" for="is_fixed_value">
                                <span className="input-text">In Fixed Value</span>
                              </label>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </Col>
                  </Row>
                  <Row>
                    <Col xs={12}>
                      <Table responsive className="prize-distribution-table">
                        <thead>
                          <tr>
                            <th>Rank</th>
                            <th>Prize Type</th>
                            <th>Distribution</th>
                            <th>&nbsp;</th>
                          </tr>
                        </thead>
                        <tbody>
                          {payout_data.map((item, idx) => (
                            <tr id="addr0" key={idx}>
                              <td className="rank-input-filed">
                                <Input disabled="1" className="gray-form-control required" type="text" name="min" value={payout_data[idx].min} onChange={(e) => this.validatePrizeData(e, idx)} placeholder="01" />
                                <span className="span">-</span>
                                <Input className="gray-form-control required" type="text" name="max" value={payout_data[idx].max} onChange={(e) => { this.onChangeMax(idx); this.validatePrizeData(e, idx) }} placeholder="02" />
                              </td>
                              <td className="prdis-select">
                                <Select
                                  isSearchable={false}
                                  isClearable={false}
                                  className="position gray-select-field"
                                  name="payout_prize_type"
                                  placeholder="Select Type"
                                  value={payout_data[idx].prize_type}
                                  options={contestObject.is_tie_breaker == 1 ? [...this.state.prizeType, this.state.merchandiseObj] : this.state.prizeType}
                                  onChange={(e) => this.handleSelectPayout(e, idx, 'prize_type')}
                                />
                              </td>
                              <td className="percentage-td prdis-select">
                                {payout_data[idx].prize_type == 3 &&
                                  <Select
                                    isSearchable={false}
                                    isClearable={false}
                                    className="eachinput position gray-select-field"
                                    name="value"
                                    placeholder="Select Merchandise"
                                    value={payout_data[idx].amount}
                                    options={this.state.merchandiseList}
                                    onChange={(e) => this.handleSelectPayout(e, idx, 'amount')}
                                  />
                                }
                                {payout_data[idx].prize_type != 3 &&
                                  <Input className="eachinput posclass gray-form-control required" type="text" name="amount" value={payout_data[idx].amount} onChange={(e) => { this.validatePrizeData(e, idx); this.onChangeAmount(idx) }} placeholder="0" />

                                }
                                <div class="each">(Each)</div>
                              </td>
                              <td>
                                {payout_data.length > 1 && idx > 0 &&
                                  <img onClick={() => this.removeRow(idx)} className="" src={Images.CENCLEBTN} />
                                }
                              </td>
                            </tr>
                          ))}
                          <tr>

                            <td className="rank-input-filed" colSpan={3}>
                              <div className="add-more-prizes" onClick={this.addRow}>
                                Add Prizes <img src={Images.ADDBTN} />
                              </div>
                            </td>

                            <td className="user-total-prize-distribution " colSpan={2}>
                              <table>
                                <tbody>
                                  <tr>
                                    <td>{prize_profit.min_total}</td>
                                    <td>
                                      Total <span>(Only Real Cash)</span>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>

                          </tr>
                        </tbody>
                      </Table>
                    </Col>
                  </Row>
                </CardBody>
              </Card>
            </Col>
          </Row>
        }
        <Row className="mb-1 sponser-section">
          <Col xs="12" lg="12">
            <Card className="recentcom">
              <CardBody className="contestcard">
                <Row>
                  <Col sm={4} md={4}>
                    <label for="set_sponsor1" className="fixturevs">Sponsored
                        <span className="btn-information"><img id="isShowSPToolTip" className="infobtn" src={Images.INFO} />
                        <Tooltip placement="right" isOpen={this.state.isShowSPToolTip} target="isShowSPToolTip" toggle={this.toggle9}>
                          Here you can upload contest/league sponsor image and link. It will be visible on contest info screen and fixture card. Priority is given to league/HOF sponsor.
                        </Tooltip>
                      </span>
                    </label>
                    <div className="autorecurrentdiv custom-checkbox-new mt-0">
                      <input class="styled-checkbox" id="set_sponsor" type="checkbox" name="set_sponsor"
                        value={contestObject.set_sponsor}
                        onChange={(e) => this.handleSponsore(e, 'set_sponsor')} />
                      <label for="set_sponsor">Yes</label>
                    </div>

                  </Col>
                  {contestObject.set_sponsor == 1 &&
                    <Fragment>
                      <Col sm={4} md={4}>
                        <div class="form-group multiselect-wrapper">
                          <label for="league_id" className="fixturevs ">Sponsored by</label>
                          <input disabled={contestObject.set_sponsor == '0'} className="contestname required gray-form-control" id="sponsor_name" name="sponsor_name" maxLength={30}
                            value={contestObject.sponsor_name}
                            onChange={(e) => this.handleFieldVal(e, 'sponsor_name', 'sponsor_name')} placeholder="sponsored name"></input>

                        </div>
                        <div class="form-group multiselect-wrapper mt-4">
                          <label for="league_id" className="fixturevs ">Sponsored Link</label>
                          <input disabled={contestObject.set_sponsor == 0} className="contestname gray-form-control" id="sponsor_link" name="sponsor_link" maxLength={255}
                            value={contestObject.sponsor_link}
                            onChange={(e) => this.handleFieldVal(e, 'sponsor_link', 'sponsor_link')} placeholder="sponsored link"></input>

                        </div>
                      </Col>
                      <Col sm={4} md={4}>
                        <div class="xupload-sponsored-img">
                          <figure className="upload-img">
                            {!_.isEmpty(SPONSER_IMAGE_NAME) ?
                              <Fragment>
                                <a
                                  href
                                  onClick={() => this.resetFile()}
                                >
                                  <i className="icon-close"></i>
                                </a>
                                <img className="img-cover fp-sponsor-img" src={NC.S3 + NC.SPONSER_IMG_PATH + SPONSER_PIMAGE_NAME} />
                              </Fragment>
                              :
                              <Fragment>
                                <Input
                                  accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                  type="file"
                                  name='merchandise_image'
                                  id="merchandise_image"
                                  className="gift_image"
                                  onChange={(e) => this.onChangeImage(e)}
                                />
                                <i onChange={(e) => this.onChangeImage(e)} className="icon-camera"></i>
                              </Fragment>
                            }
                          </figure>
                          <div className="figure-help-text">Please upload image with maximum size of 1036x60.</div>
                        </div>
                      </Col>
                    </Fragment>
                  }
                </Row>
              </CardBody>
            </Card>
          </Col>
        </Row>

        <Row className="mb-1 sponser-section">
          <Col xs="12" lg="12">
            <Card className="recentcom">
              <CardBody className="contestcard">
                <Row>
                  <Fragment>
                    <Col sm={4} md={4}>

                      <div class="form-group multiselect-wrapper mt-4">
                        <label for="league_id" className="fixturevs ">Sponsored Video Link (Optional)</label>
                        <input className="contestname gray-form-control" id="video_link" name="video_link" maxLength={255}
                          value={contestObject.video_link}
                          onChange={(e) => this.handleFieldVal(e, 'video_link', 'video_link')} placeholder="Youtube video link"></input>

                      </div>
                    </Col>
                  </Fragment>

                </Row>
              </CardBody>
            </Card>
          </Col>
        </Row>

        <Row className="verifyrow">
          <Col lg={12}>
            <Button onClick={() => { this.CreateContest() }} className='btn-secondary-outline'>Create Contest</Button>
          </Col>
        </Row>

      </div>
    );
  }
}

export default CreateF2PContest;