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
import { MomentDateComponent } from "../../components/CustomComponent";
import HF from '../../helper/HelperFunction';


class CreateMiniLeague extends Component {

  constructor(props) {
    super(props);

    this.state = {
      contestTemplate: { 'name': '', 'title': '', 'league_id': [], 'multiple_lineup': '1', 'entry_fee_type': '1', 'max_bonus_allowed': '0', 'prize_type': '1', 'prize_pool_type': '1', "master_contest_type_id": "1", "group_id": "1", "is_auto_recurring": false, 'site_rake': 0, 'custom_total_percentage': '100', 'custom_total_amount': '0', 'prize_value_type': '0', 'is_tie_breaker': true, 'sponsor_name': '', 'sponsor_logo': '', 'sponsor_link': '', 'set_sponsor': 0 },
      payout_data: [],
      selected_league: "",
      league_start_date: "",
      league_end_date: "",
      SubstitutesAllowed: "0",
      leagueList: [],
      leagueListM: [],
      selectSetPrize: false,
      selectUnsetPrize: true,
      fixtureList: [],
      fixtureFilter: [{ label: "All", id: 1 }, { label: "Selected", value: 2 }, { label: "Unselected", value: 3 }],
      fixtureFilterSelected: { label: "All", value: 1 },
      fixtureMainList: [],
      roster_list: [1, 2, 3],
      fixtureDetail: {},
      name: '',
      accordion: [],
      activeTab: 1,

      posting: false,
      keyword: '',
      selectAll: false,

      dropdownOpen: new Array(19).fill(false),
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      prizeType: [],
      merchandiseObj: {},
      prize_profit: { min_total: 0, max_total: 0, min_gross_profit: 0, max_gross_profit: 0, min_net_profit: 0, max_net_profit: 0 },
      SPONSER_IMAGE_NAME: '',
      SPONSER_PIMAGE_NAME: '',
      BG_IMAGE_NAME: '',
      BG_PIMAGE_NAME: '',
      wrongLink: true
    };
  }

  componentDidMount() {
    this.GetAllLeagueList();
    this.initPayoutData();

    this.GetContestTemplateMasterData();
  }

  GetContestTemplateMasterData = () => {
    this.setState({ posting: true })
    WSManager.Rest(NC.baseURL + NC.GET_CONTEST_TEMPLATE_MASTER_DATA, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;

        let contestTemplate = _.clone(this.state.contestTemplate);
        contestTemplate['max_bonus_allowed'] = responseJson.max_bonus_allowed;
        this.setState({ contestTemplate: contestTemplate });

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
  initPayoutData = () => {
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    var payout_data = [];
    var prize_pool = 0;
    if (typeof this.state.contestTemplate.prize_pool != "undefined" && this.state.contestTemplate.prize_pool != "") {
      prize_pool = this.state.contestTemplate.prize_pool;
    }
    contestTemplate.custom_total_percentage = 100;
    contestTemplate.custom_total_amount = prize_pool;
    if (isNaN(prize_pool)) {
      return false;
    }
    prize_pool = parseFloat(prize_pool).toFixed(0);
    let max_prize_pool = parseFloat(contestTemplate.size * contestTemplate.entry_fee).toFixed(0);
    if (isNaN(max_prize_pool)) {
      max_prize_pool = 0;
    }
    let amount = prize_pool;
    let min_value = prize_pool;
    let max_value = max_prize_pool;
    if (contestTemplate.prize_value_type == 1) {
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
    this.setState({ payout_data: payout_data, contestTemplate: contestTemplate }, function () {
      this.calculateProfitData();
    });
  }


  calculatePrizePool = (e, tindex, element_value) => {
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    if (tindex == "prize_pool_type") {
      //this.initPayoutData();
      contestTemplate[tindex] = element_value
      if (element_value == 0 || element_value == 1) {
        // contestTemplate['site_rake'] = this.state.site_rake;
      }
    } else {
      let value = e.target.value;
      contestTemplate[tindex] = value
    }
    contestTemplate['prize_pool'] = "";

    //set site_rake=0 for free contest
    if (contestTemplate.entry_fee && contestTemplate.entry_fee <= 0) {
      contestTemplate['max_bonus_allowed'] = "0";
      contestTemplate['prize_pool_type'] = "1";
    }

    if ((contestTemplate.entry_fee && contestTemplate.entry_fee > 0 && tindex == "entry_fee") || (contestTemplate.entry_fee && contestTemplate.entry_fee > 0)) {
      contestTemplate['max_bonus_allowed'] = this.state.max_bonus_allowed;
    }
    this.setState({ contestTemplate: contestTemplate });

    /* if (parseInt(contestTemplate.minimum_size) < 2) {
      notify.show("minimum size should be greater than equal to 2.", "error", 3000);
      return false
    } */

    /* if (contestTemplate.minimum_size && contestTemplate.size && parseInt(contestTemplate.minimum_size) > parseInt(contestTemplate.size)) {
      notify.show("size should be greater than min size.", "error", 3000);
      return false;
    } */

    /* if (contestTemplate.minimum_size && contestTemplate.size && contestTemplate.entry_fee) {
      //let prize_pool = (contestTemplate.minimum_size * contestTemplate.entry_fee) - ((contestTemplate.minimum_size * contestTemplate.entry_fee * contestTemplate.site_rake) / 100);
      let prize_pool = (contestTemplate.minimum_size * contestTemplate.entry_fee);
      prize_pool = prize_pool.toFixed(0);
      contestTemplate['prize_pool'] = prize_pool;
    } */
    this.setState({ contestTemplate: contestTemplate }, function () {
      if (this.state.contestTemplate.prize_pool_type != '0') {
        this.initPayoutData();
      }
    });
  }

  initPayoutData = () => {
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    var payout_data = [];
    var prize_pool = 0;
    if (typeof this.state.contestTemplate.prize_pool != "undefined" && this.state.contestTemplate.prize_pool != "") {
      prize_pool = this.state.contestTemplate.prize_pool;
    }
    contestTemplate.custom_total_percentage = 100;
    contestTemplate.custom_total_amount = prize_pool;
    if (isNaN(prize_pool)) {
      return false;
    }
    prize_pool = parseFloat(prize_pool).toFixed(0);
    let max_prize_pool = parseFloat(contestTemplate.size * contestTemplate.entry_fee).toFixed(0);
    if (isNaN(max_prize_pool)) {
      max_prize_pool = 0;
    }
    let amount = prize_pool;
    let min_value = prize_pool;
    let max_value = max_prize_pool;
    if (contestTemplate.prize_value_type == 1) {
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
    this.setState({ payout_data: payout_data, contestTemplate: contestTemplate }, function () {
      this.calculateProfitData();
    });
  }

  addRow = () => {
    var payout_data = this.state.payout_data;

   
    var prize_pool = 0;
    if (typeof this.state.contestTemplate.prize_pool != "undefined" && this.state.contestTemplate.prize_pool != "") {
      prize_pool = this.state.contestTemplate.prize_pool;
    }
    /* if (isNaN(prize_pool)) {
      notify.show("Please set prize pool.", "error", 3000);
      return false;
    } */
    if (payout_data.length > 0) {
      var max = payout_data[payout_data.length - 1].max;
      max = parseInt(max) + 1;
      var size = this.state.contestTemplate.size;
      if (max > parseInt(size)) {
        return true;
      }
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
      /* payout_data[index]['max'] = payout_data[index].min;
      value=payout_data[index].min; */
      notify.show("Maximum size should not be less than Minimum size.", "error", 3000);
    }
    if (name == "min" || name == "max") {
      item_val = payout_data[index]['amount'];
    }
    if (name == "max") {
      payout_data[index]['max'] = value;
    }
    let prize_pool = this.state.contestTemplate.prize_pool;
    let max_prize_pool = item_val;
    let per = parseFloat(item_val / (prize_pool / 100)).toFixed(2);
    if (this.state.contestTemplate.prize_value_type === "1") {
      per = item_val;
      item_val = parseFloat((per / 100) * prize_pool).toFixed(2);
      max_prize_pool = parseFloat(this.state.contestTemplate.size * this.state.contestTemplate.entry_fee).toFixed(2);
      max_prize_pool = parseFloat((per / 100) * max_prize_pool).toFixed(2);
      if (payout_data[index]['min'] != payout_data[index]['max']) {
        var tmp_count = parseInt(payout_data[index]['max']) - parseInt(payout_data[index]['min']) + 1;
        item_val = parseFloat(item_val / tmp_count).toFixed(2);
        max_prize_pool = parseFloat(max_prize_pool / tmp_count).toFixed(2);
      }
    } else {
      per = parseFloat((item_val * 100) / prize_pool).toFixed(2);
      if (this.state.contestTemplate.prize_pool_type != "2") {
        max_prize_pool = parseFloat(this.state.contestTemplate.size * this.state.contestTemplate.entry_fee).toFixed(2);
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

    if (max_prize_pool == 0 && this.state.contestTemplate.entry_fee == 0 && this.state.contestTemplate.prize_pool == 0) {
      max_prize_pool = item_val;
    }

    payout_data[index][name] = value;
    payout_data[index]['per'] = per;
    payout_data[index]['min_value'] = parseFloat(item_val).toFixed(2);
    payout_data[index]['max_value'] = parseFloat(max_prize_pool).toFixed(2);
    this.setState({ payout_data: payout_data });
    this.calculateProfitData();
  }

  calculateProfitData = () => {
    let prize_profit = _.cloneDeep(this.state.prize_profit);
    let min_total = 0;
    let max_total = 0;
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    let prize_pool = contestTemplate.prize_pool;
    if (contestTemplate.prize_pool_type == 2) {
      prize_pool = parseFloat(this.state.contestTemplate.entry_fee * contestTemplate.minimum_size);
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
      contestTemplate.site_rake = 0;
      prize_profit.min_gross_profit = 0;
    } else {
      prize_profit.min_gross_profit = parseFloat(prize_pool - min_total).toFixed(2);
    }
    if (!contestTemplate.entry_fee) {
      prize_profit.max_gross_profit = 0;
      prize_profit.min_net_profit = 0;
      prize_profit.max_net_profit = 0;
    } else {
      prize_profit.max_gross_profit = parseFloat((contestTemplate.entry_fee * contestTemplate.size) - max_total).toFixed(2);
      prize_profit.min_net_profit = parseFloat(prize_profit.min_gross_profit - (((contestTemplate.entry_fee * contestTemplate.max_bonus_allowed) / 100) * contestTemplate.minimum_size)).toFixed(2);
      prize_profit.max_net_profit = parseFloat(prize_profit.max_gross_profit - (((contestTemplate.entry_fee * contestTemplate.max_bonus_allowed) / 100) * contestTemplate.size)).toFixed(2);
    }
    if (contestTemplate.prize_pool_type == "2") {
      prize_profit.max_gross_profit = parseFloat(prize_profit.min_gross_profit).toFixed(2);
      prize_profit.max_net_profit = parseFloat(prize_profit.min_net_profit).toFixed(2);
    }
    if (prize_pool == 0) {
      contestTemplate.site_rake = 0;
    } else {
      let mgp = prize_profit.min_gross_profit > 0 ? prize_profit.min_gross_profit : 0;
      let pp = prize_pool > 0 ? prize_pool : 0;
      if (mgp == 0 || pp == 0) {
        contestTemplate.site_rake = 0;
      }
      else {
        contestTemplate.site_rake = ((mgp * 100) / prize_pool).toFixed(2);
      }
    }
    this.setState({ prize_profit: prize_profit, contestTemplate: contestTemplate });
  }

  totalPercentage = () => {
    var per_temp = 0;
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    contestTemplate.custom_total_percentage = 100;
    contestTemplate.custom_total_amount = 0;
    if (this.state.payout_data.length > 0 && this.state.selectSetPrize == true) {
      _.map(this.state.payout_data, (value, key) => {
        if (value) {
          per_temp += parseFloat(value.per);
          var min = parseInt(value.min);
          var max = parseInt(value.max);
          contestTemplate.custom_total_amount += parseFloat(value.amount * (max - min + 1));
          if (value.amount <= 0) {
            notify.show("Please fill or delete the unfilled row.", "error", 3000);
          }

          /* if (this.state.contestTemplate.prize_pool_type == '2') {
            var size = this.state.contestTemplate.size;
          } else {
            var size = this.state.contestTemplate.minimum_size;
          } */
          /* if (parseInt(min) > parseInt(max) || parseInt(size) < parseInt(max)) {
            var msg = "Maximum size should not be less than Minimum size.";
            if (parseInt(size) < parseInt(max)) {
              msg = "Maximum size should not be less than Minimum size.";
            }
            notify.show(msg, "error", 3000);
          } */

        }
      });
    }

    contestTemplate.custom_total_percentage = per_temp.toFixed(2);
    contestTemplate.custom_total_amount = contestTemplate.custom_total_amount.toFixed(2);
    this.setState({ contestTemplate: contestTemplate });

    /*if (contestTemplate.custom_total_percentage > 100) {
      notify.show("Percentage should not be greater than 100%", "error", 3000);
    }
 
    if (contestTemplate.custom_total_percentage < 100) {
      notify.show("Percentage should not be less than 100%", "error", 3000);
    }*/
  }

  onChangeMax = (index) => {
    let payout_data = this.state.payout_data;
    var min = payout_data[index].min;
    var max = payout_data[index].max;
    if (parseInt(min) > parseInt(max)) {
      notify.show("Maximum size should not be less than Minimum size.", "error", 3000);
      /* payout_data[index]['max'] = min;
      this.setState({ payout_data: payout_data });
      return false */
    }

    if (payout_data.length > 0) {
      var size = this.state.contestTemplate.size;
      _.map(payout_data, (value, key) => {
        if (key > index && key > 0) {
          var max = payout_data[key - 1].max;
          if (max < size) {
            payout_data[key].min = parseInt(max) + 1;
            payout_data[key].max = parseInt(max) + 1;
            payout_data[key].per = 0;
            payout_data[key].amount = parseFloat(0);
          }
          else {
            this.removeRow(key);
          }
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
    var prize_pool = parseFloat(this.state.contestTemplate.prize_pool);
   
    var person = (max - min) + 1;
    /* if (isNaN(prize_pool)) {
      notify.show("Please set prize pool.", "error", 3000);
    } */

    if (isNaN(per)) {
      // notify.show("Percent Should be a number.", "error", 3000);
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
    var prize_pool = parseFloat(this.state.contestTemplate.prize_pool);
    if (max < min) {
      max = min;
      notify.show("max winner should be greater then min.", "error", 3000);
      return false;
    }
    var person = (max - min) + 1;
   
    /*  if (isNaN(prize_pool)) {
       notify.show("Please set prize pool.", "error", 3000);
     } */
   
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
  handlePrizeInPercentage = (e, tindex) => {
    if (e) {
      let value = e.target.value;
      let contestTemplate = _.cloneDeep(this.state.contestTemplate);
      contestTemplate[tindex] = value;
      this.setState({
        contestTemplate: contestTemplate
      }, function () {
        this.initPayoutData();
      })
    }
  }
  handleSponsore = (e, tindex) => {
    if (e) {
      let value = '';
      value = e.target.value;
      let contestTemplate = _.cloneDeep(this.state.contestTemplate);
      contestTemplate[tindex] = value == '0' ? '1' : '0';
      this.setState({
        contestTemplate: contestTemplate
      })
    }
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

  GetAllLeagueList = () => {
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_LEAGUE_LIST_MINILEAGUE, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        this.setState({
          posting: false
        }, () => {
          this.createLeagueList(responseJson);
        })
      } else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
      this.setState({
        posting: false
      })
    }).catch((e) => {
      this.setState({
        posting: false
      })
    })
  }

  createLeagueList = (list) => {
    let leagueArr = list;
    let tempArr = [];

    leagueArr.map(function (lObj, lKey) {
      tempArr.push({ value: lObj.league_id, label: lObj.league_name });
    });
    this.setState({ leagueListM: list, leagueList: tempArr });
  }

  Create = () => {
    if (this.state.selected_league == '') {
      notify.show("Please select league.", "error", 3000);
      return false;
    }

    else if (this.state.contestTemplate.name == '') {
      notify.show("Please enter name.", "error", 3000);
      return false;
    }
    else if (this.state.contestTemplate.name.length < 3) {
      notify.show("Name length should be greater than or equal to 3.", "error", 3000);
      return false;
    }
    else if (this.state.contestTemplate.name.length > 30) {
      notify.show("Name length should be less than or equal to 30.", "error", 3000);
      return false;
    }

    else if (this.getSelectedLeague().length <= 0) {
      notify.show("Please select atleast one fixture.", "error", 3000);
      return false;
    }
    else if (this.state.SubstitutesAllowed == '') {
      notify.show("The Allowed substitutes field is required.", "error", 3000);
      return false;
    }
    else if (parseInt(this.state.SubstitutesAllowed) < 0) {
      notify.show("The Allowed substitutes should be greater than or equal to 0 ", "error", 3000);
      return false;
    }
    else if (parseInt(this.state.SubstitutesAllowed) > 999) {
      notify.show("The Allowed substitutes should be less than or equal to 999 ", "error", 3000);
      return false;
    }
    else if (this.state.contestTemplate.title.length < 3 && this.state.contestTemplate.title != '') {
      notify.show("Title length should be greater than or equal to 3.", "error", 3000);
      return false;
    }


    if (parseFloat(this.state.contestTemplate.prize_pool) < parseFloat(this.state.prize_profit.min_total)) {
      notify.show("Winners prize should be less than or equal to prize pool.", "error", 3000);
      return false
    }

    if (this.state.contestTemplate.set_sponsor == '1' && (this.state.contestTemplate.sponsor_name == '' || this.state.contestTemplate.sponsor_logo == '')) {
      notify.show("Please fill sponser info.", "error", 3000);
      return false
    }

    if (this.state.contestTemplate.set_sponsor == 1 && !this.state.wrongLink) {
      notify.show("please enter a valid sponsered link", "error", 5000)
      return false
    }
    if (this.state.contestTemplate.title == '') {
      notify.show("please enter title", "error", 5000)
      return false
    }

    let is_valid = 1;
    if (this.state.payout_data.length >= 1 && this.state.selectSetPrize == true) {
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
    if (is_valid == 0) { return false; }

    this.setState({ posting: true })
    
    //return false;      
    WSManager.Rest(NC.baseURL + NC.CREATE_MINILEAGUE, {
      "is_tie_breaker": "1",
      "sports_id": this.state.selected_sport,
      "mini_league_name": this.state.contestTemplate.name,
      "title": this.state.contestTemplate.title,
      "bg_image": this.state.contestTemplate.bg_image,
      "league_id": this.state.selected_league,
      "seasons": this.getSelectedLeague(),
      "prize_distibution_detail": this.state.payout_data,
      "sponsor_name": this.state.contestTemplate.sponsor_name,
      "sponsor_logo": this.state.contestTemplate.sponsor_logo,
      "sponsor_link": this.state.contestTemplate.sponsor_link,
      "set_sponsor": this.state.contestTemplate.set_sponsor,
      "set_prize": (this.state.selectSetPrize == true) ? "1" : "0"


    }).then((responseJson) => {
      this.setState({ posting: false })
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        responseJson = responseJson.data;
        this.props.history.push({ pathname: '/game_center/DFS/', search: '?fixtab=2' });
      } else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }

    }).catch((e) => {
      this.setState({ posting: false })
    })



  }


  handleFieldVal = (e, tindex, element_id) => {
    if (e) {
      let name = '';
      let value = '';
      name = e.target.name;
      value = e.target.value;
      let contestTemplate = _.cloneDeep(this.state.contestTemplate)
      contestTemplate[tindex] = value

      var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
      if (name == 'sponsor_link' && value != '' && !pattern.test(value)) {
        this.setState({
          wrongLink: false
        })
        notify.show("please enter a valid sponsered link", "error", 5000);
      }
      else if (name == 'sponsor_link') {
        this.setState({
          wrongLink: true
        })
      }
      this.setState({
        contestTemplate: contestTemplate
      }, function () { })
    }
  }

  handleFieldSearch = (e) => {


    if (e) {

      let name = '';
      let value = '';
      name = e.target.name;
      value = e.target.value;

      this.setState({
        'keyword': value
      }, function () {
        this.search();

      })
    }
  }

  handleLeague = (value, dropName) => {
    if (value) {
      if (dropName == "selected_league") {
        this.setState({ selected_league: value.value, fixtureList: [], fixtureMainList: [] }, function () {
          this.getLeagueSeasion();
          this.setState({
            league_start_date: this.getSeasionDate('start'),
            league_end_date: this.getSeasionDate('end')
          });
        });
      }
    }
  }


  getLeagueSeasion = () => {

    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_LEAGUE_SEASIONS_MINILEAGUE, {
      "league_id": this.state.selected_league, team_uid: this.state.team_uid
    }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        this.setState({
          posting: false,
          fixtureList: responseJson.season_list,
          fixtureMainList: responseJson.season_list
        }, () => {

        })
      } else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
      this.setState({
        posting: false
      })
    }).catch((e) => {
      this.setState({
        posting: false
      })
    })
  }

  getSeasionDate(type) {
    let selecteditem;
    this.state.leagueListM.map((item, index) => {
      if (item.league_id == this.state.selected_league) {
        selecteditem = item;
      }
    })

    if (type == 'start') {
      return selecteditem.league_schedule_date;
    } else {
    

      return selecteditem.league_last_date;
    }

  }

  handleFilter = (value, dropName) => {

    if (value) {
      let filteredList = [];
      let tempList = this.state.fixtureMainList;
      this.setState({ fixtureFilterSelected: value }, function () {
        if (value.value == 2) {
          filteredList = tempList.filter(function (item) {
            return item.is_selected
          });
        } else if (value.value == 3) {
          filteredList = tempList.filter(function (item) {
            return !item.is_selected
          });
        } else {
          filteredList = tempList;
        }

        this.setState({ fixtureList: filteredList })

      });


    }
  }
  selectAll() {
    _.map(this.state.fixtureList, (item, index) => {
      item.is_selected = this.state.selectAll;
    })

    this.setState({
      fixtureList: this.state.fixtureList
    })

  }
  handleChkVal = (e) => {

    if (e) {
      let value = e.target.checked;

      this.setState({
        selectAll: value,
      }, function () {
        this.selectAll();
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
  search() {
    if (this.state.keyword.length > 1) {
      var fixtureLists = this.state.fixtureMainList.filter((item) => {
        let reshome = item.home.toLowerCase();
        let resaway = item.away.toLowerCase();
        return reshome.includes(this.state.keyword.toLowerCase()) || resaway.includes(this.state.keyword.toLowerCase());
        // return item.home.toLowerCase() == this.state.keyword.toLowerCase() || item.away.toLowerCase() == this.state.keyword.toLowerCase();

      });
      this.setState({ fixtureList: fixtureLists })

    } else {
      this.setState({ fixtureList: this.state.fixtureMainList })

    }
  }

  onChangeImage = (event) => {
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
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

    if (this.state.SPONSER_PIMAGE_NAME != '') {
      data.append("source", 'edit');

    } else {

      data.append("source", 'add');

    }


    WSManager.multipartPost(NC.baseURL + NC.UPLOAD_MINILEAGUE_SPONSER, data)
      .then(Response => {
        if (Response.response_code == NC.successCode) {
          contestTemplate.sponsor_logo = Response.data.image_name
          this.setState({
            SPONSER_PIMAGE_NAME: Response.data.image_name,
            contestTemplate: contestTemplate
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
      SPONSER_PIMAGE_NAME: '',
      SPONSER_IMAGE_NAME: null
    });
  }


  onChangeImageBG = (event) => {
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    this.setState({
      BG_IMAGE_NAME: URL.createObjectURL(event.target.files[0]),
    });
    const file = event.target.files[0];
    if (!file) {
      return;
    }
    var data = new FormData();
    data.append("file", file);
    if (this.state.BG_PIMAGE_NAME != '') {
      data.append("previous_img", this.state.BG_PIMAGE_NAME);
    }

    if (this.state.BG_PIMAGE_NAME != '') {
      data.append("source", 'edit');

    } else {

      data.append("source", 'add');

    }


    WSManager.multipartPost(NC.baseURL + NC.UPLOAD_MINILEAGUE_BGIMAGE, data)
      .then(Response => {
        if (Response.response_code == NC.successCode) {
          contestTemplate.bg_image = Response.data.file_name
          this.setState({
            BG_PIMAGE_NAME: Response.data.file_name,
            contestTemplate: contestTemplate
          });
        } else {
          this.setState({
            BG_IMAGE_NAME: null
          });
        }
      }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000);
      });
  }

  resetFileBG = () => {
    this.setState({
      BG_IMAGE_NAME: null
    });
  }

  handlePrizeTieBreaker = (e, tindex) => {
    if (e) {
      let value = '';
      value = e.target.value;
      let contestTemplate = _.cloneDeep(this.state.contestTemplate);
      contestTemplate[tindex] = value == 'false' ? true : false;
      this.setState({
        contestTemplate: contestTemplate
      }, function () {
        this.initPayoutData();
      })
    }
  }
  toggle2 = () => {
    this.setState({
      isShowPDToolTip: !this.state.isShowPDToolTip
    });
  }

  toggle3 = () => {
    this.setState({
      isShowSPToolTip: !this.state.isShowSPToolTip
    });
  }
  render() {
    const {
      SPONSER_PIMAGE_NAME,
      SPONSER_IMAGE_NAME,

      BG_PIMAGE_NAME,
      BG_IMAGE_NAME,

      prize_profit,
      payout_data,
      leagueList,
      contestTemplate,
      selectAll,
      name,
      selectSetPrize,
      selectUnsetPrize,
      isShowPDToolTip,
      isShowSPToolTip

    } = this.state

    return (
      <div className="create-ml-parent">
        <Row>
          <Col md={12}>
            <div className="screen-header">
              <div className="sc-title">Create Mini League</div>
              <div
                onClick={() => this.props.history.push('/game_center/DFS?pctab=2')}
                className="sc-back-arrow">{'< Go Back'}
              </div>
            </div>
          </Col>
        </Row>
        <div className="create-ml-parent white-box">
          <div className="d-flex">
            <div className="ml-first-row">
              <label>Select League</label>
              <Select
                className="league-selector-create-tournament"
                id="selected_league"
                name="selected_league"
                placeholder="Select League"
                value={this.state.selected_league}
                options={leagueList}
                onChange={(e) => this.handleLeague(e, 'selected_league')}
              />
            </div>
            <div className="ml-first-row">
              <label className="select-league-label" >Mini League Name</label>
              <input maxlength="30" className="tournament-name required" id="name" name="name"
                value={contestTemplate.name} onChange={(e) => this.handleFieldVal(e, 'name', 'name')}
                placeholder="Mini League Name"></input>

            </div>
            <div className="ml-first-row">
              <label className="select-league-label" >Title</label>
              <input maxlength="40" className="tournament-name required" id="title" name="title"
                value={contestTemplate.title} onChange={(e) => this.handleFieldVal(e, 'title', 'title')}
                placeholder="Title"></input>

            </div>
          </div>

          <div className="fixture-view">
            <label className="fixture-label">Select Fixtures</label>
            <div className="fixture-view-header">
              <div className="select-all-parent">
                <label className="select-all">Select All</label>
                <input type="checkbox"
                  defaultChecked={selectAll}
                  checked={selectAll}
                  onChange={(e) => this.handleChkVal(e)}
                />
                <label className="select-all">Yes</label>
              </div>
              <div className="right-item">
                <Select
                  className="fixture-filter-selector"
                  id="selected_league"
                  name="selected_league"
                  placeholder="All"
                  value={this.state.fixtureFilterSelected}
                  options={this.state.fixtureFilter}
                  onChange={(e) => this.handleFilter(e, 'fixtureFilter')}
                />
                <FormGroup className="float-right">
                  <InputGroup className="search-wrapper">
                    <i className="icon-search" onClick={() => { this.search(); }}></i>
                    <Input type="text" id="keyword" name="keyword" value={this.state.keyword} onChange={(e) => this.handleFieldSearch(e)} onKeyPress={event => { if (event.key === 'Enter') { this.search() } }} placeholder="Search" />
                  </InputGroup>
                </FormGroup>
              </div>
            </div>
            <div className="line" />
            <div>
              <Row>{
                _.map(this.state.fixtureList, (item, idx) => {
                  return (

                    <Col md={3} key={idx} >
                      <div className="fixture-data" onClick={() => {
                        item.is_selected = !item.is_selected;
                        let isAllSelected = this.isAllSelected();


                        this.setState({ selectAll: isAllSelected }, () => {
                          this.setState({ fixtureList: this.state.fixtureList }, () => { })

                        })



                      }}>
                        <img src={NC.S3 + NC.FLAG + item.home_flag} className="team-image" />
                        <div className="center-view">
                          <label className="team-name">{item.home + ' vs ' + item.away}</label>
                          <label className="time">
                            {/* <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                            {HF.getFormatedDateTime(item.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                          </label>
                        </div>
                        <img src={NC.S3 + NC.FLAG + item.away_flag} className="team-image" />

                        {item.is_selected &&
                          <div className="right-selection">
                            <i className="icon-righttick"></i>
                          </div>
                        }
                      </div>
                    </Col>

                  )
                })
              }
              </Row>
            </div>

          </div>
          <Row>
            <Col md={12}>
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
            </Col>
          </Row>

        </div>
        {this.state.selectSetPrize &&
          <Row className="mb-1">
            <Col xs="12" lg="12">
              <Card className="recentcom">
                <CardBody className="contestcard">
                  <Row>
                    <Col sm={4}>
                      <label className="fixturevs">Prize Distribution
                                    <span className="btn-information"><img id="isShowPDToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowPDToolTip} target="isShowPDToolTip" toggle={this.toggle2}>
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
                              <input type="radio" className="custom-control-input" id="is_fixed_value" name="prize_value_type" value="0" checked={contestTemplate.prize_value_type === '0'} onChange={(e) => this.handlePrizeInPercentage(e, 'prize_value_type')} />
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
                                <Input className="gray-form-control required" type="text" name="max" value={this.state.payout_data[idx].max} onChange={(e) => { this.onChangeMax(idx); this.validatePrizeData(e, idx) }} placeholder="02" />
                              </td>
                              <td>
                                <Select
                                  isSearchable={false}
                                  isClearable={false}
                                  className="position gray-select-field"
                                  name="payout_prize_type"
                                  placeholder="Select Type"
                                  value={payout_data[idx].prize_type}
                                  options={contestTemplate.is_tie_breaker == 1 ? [...this.state.prizeType, this.state.merchandiseObj] : this.state.prizeType}
                                  onChange={(e) => this.handleSelectPayout(e, idx, 'prize_type')}
                                />
                              </td>
                              <td className="percentage-td">
                                {payout_data[idx].prize_type == 3 &&
                                  <Select
                                    isSearchable={false}
                                    isClearable={false}
                                    className=" eachinput position gray-select-field"
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
                        <Tooltip placement="right" isOpen={this.state.isShowSPToolTip} target="isShowSPToolTip" toggle={this.toggle3}>
                          Here you can upload contest/league sponsor image and link. It will be visible on contest info screen and fixture card. Priority is given to league/HOF sponsor.
                                    </Tooltip>
                      </span>
                    </label>
                    <div className="set-prize-option">
                      <div className="prize-option">
                        <input type="checkbox"
                          name="set_sponsor"
                          value={contestTemplate.set_sponsor}
                          onChange={(e) => this.handleSponsore(e, 'set_sponsor')}


                        />
                        <span className="opt-text">Yes</span>
                      </div>
                    </div>
                  </Col>
                  {contestTemplate.set_sponsor == 1 &&
                    <Fragment>
                      <Col sm={4} md={4}>
                        <div className="form-group multiselect-wrapper">
                          <label for="league_id" className="fixturevs ">Sponsored by</label>
                          <input disabled={contestTemplate.set_sponsor == '0'} className="contestname required gray-form-control" id="sponsor_name" name="sponsor_name" maxLength={30}
                            value={contestTemplate.sponsor_name}
                            onChange={(e) => this.handleFieldVal(e, 'sponsor_name', 'sponsor_name')} placeholder="sponsored name"></input>

                        </div>
                        <div className="form-group multiselect-wrapper mt-4">
                          <label for="league_id" className="fixturevs ">Sponsored Link</label>
                          <input disabled={contestTemplate.set_sponsor == 0} className="contestname gray-form-control" id="sponsor_link" name="sponsor_link" maxLength={255}
                            value={contestTemplate.sponsor_link}
                            onChange={(e) => this.handleFieldVal(e, 'sponsor_link', 'sponsor_link')} placeholder="sponsored link"></input>

                        </div>
                      </Col>
                      <Col sm={4} md={4}>
                        <div className="xupload-sponsored-img">
                          <figure className="upload-img">
                            {!_.isEmpty(SPONSER_IMAGE_NAME) ?
                              <Fragment>
                                <a
                                  href
                                  onClick={() => this.resetFile()}
                                >
                                  <i className="icon-close"></i>
                                </a>
                                <img className="img-cover" src={NC.S3 + NC.SPONSER_IMG_PATH + SPONSER_PIMAGE_NAME} />
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
                          <div className="figure-help-text">Please upload image with maximum size of 150 by 150.</div>
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
                      <label for="set_sponsor1" className="fixturevs">Upload BG Image       (Optional)
                                    </label>

                    </Col>
                    <Col sm={4} md={4}>
                      <div className="xupload-sponsored-img">
                        <figure className="upload-img">
                          {!_.isEmpty(BG_IMAGE_NAME) ?
                            <Fragment>
                              <a
                                href
                                onClick={() => this.resetFileBG()}
                              >
                                <i className="icon-close"></i>
                              </a>
                              <img className="img-cover" src={NC.S3 + NC.SPONSER_IMG_PATH + BG_PIMAGE_NAME} />
                            </Fragment>
                            :
                            <Fragment>
                              <Input
                                accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                type="file"
                                name='merchandise_image'
                                id="merchandise_image"
                                className="gift_image"
                                onChange={(e) => this.onChangeImageBG(e)}
                              />
                              <i onChange={(e) => this.onChangeImageBG(e)} className="icon-camera"></i>
                            </Fragment>
                          }
                        </figure>
                        <div className="figure-help-text">Please upload image size of 335 by 400.</div>
                      </div>
                    </Col>
                  </Fragment>

                </Row>
              </CardBody>
            </Card>
          </Col>
        </Row>


        <Row>
          <Col md={12}>
            <div className="cr-trmnt-slr">
              <Button disabled={this.state.posting}
                onClick={() => {
                  this.Create();
                }}
                className='btn-secondary-outline'>
                Submit</Button>
            </div>

          </Col>

        </Row>

      </div>

    )
  }

  getFormatedDate = (date) => {
    date = WSManager.getUtcToLocal(date);
    return moment(date).format('LLLL');
  }
  getSelectedLeague() {
    let season_game_uid = [];
    _.map(this.state.fixtureList, (item, index) => {
      if (item.is_selected)
        season_game_uid.push(item.season_game_uid);
    })
    return season_game_uid;
  }
  isAllSelected() {
    let isAllSelected = true;
    for (let i = 0; i < this.state.fixtureList.length; i++) {
      if (!this.state.fixtureList[i].is_selected) {
        isAllSelected = false;
        break;
      }
    }
    return isAllSelected;
  }
}
export default CreateMiniLeague;
