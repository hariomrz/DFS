import React, { Component, Fragment } from 'react';
import Select from 'react-select';
import {
  Card, CardBody, CardHeader, Col, Row, Input, Button, Table, Tooltip
} from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import LS from 'local-storage';
import Images from '../../components/images';
import { notify } from 'react-notify-toast';
import HF from '../../helper/HelperFunction';
import { REVERSE_FANTASY_TT, SCRATCH_WIN_CHECK, CONTEST_TIE_BRE_TT, CONTEST_SPONSOR_TT, CONTEST_RECUR_TT, CONTEST_PIN, CONTEST_PRZ_DIS_TT, CONTEST_BONUS } from '../../helper/Message';
class LSF_CreateContest extends Component {

  constructor(props) {
    super(props);
    this.toggle = this.toggle.bind(this);
    this.toggle2 = this.toggle2.bind(this);
    this.state = {
      
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,
      season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
      collection_id: (this.props.match.params.collection_id) ? this.props.match.params.collection_id : '',
      ActiveFxType: (this.props.match.params.category) ? this.props.match.params.category : '1',
      ActiveTab: (this.props.match.params.activeTab) ? this.props.match.params.activeTab : '1',
      FixtureValue: (this.props.match.params.fxvalue) ? this.props.match.params.fxvalue : '1',
      fixtureDetail: {},
      groupList: [],
      contestType: [],
      multipleLineupList: [],
      entryFeeType: [],
      winnerPlace: [],
      payout_data: [],
      rows: [{}],
      contestObject: {
        'is_pin_contest': false, 'is_auto_recurring': false, 'multiple_lineup': '1',
        'entry_fee_type': '1', 'max_bonus_allowed': '0', 'prize_type': '1'
        , 'prize_pool_type': '1', "master_contest_type_id": "1",
        "group_id": "1", "is_auto_recurring": false, 'site_rake': '10', 'site_rake_max': 0,
        'minimum_size': '2', 'size': '2',
        'custom_total_percentage': '100', 'custom_total_amount': '0', 'prize_value_type': '1', 'is_tie_breaker': false, 'sponsor_name': '',
        'sponsor_logo': '', 'sponsor_link': '', 'set_sponsor': '0', 'sponsor_contest_dtl_image': '', 'is_reverse': false, 'is_scratchwin': false,
        'brokerage': 0
      },
      IsValidate: false,
      IsFormValid: true,
      isShowRecurrenceToolTip: false,
      isShowBonusToolTip: false,
      isShowAutoToolTip: false,
      isShowCustomToolTip: false,
      isShowGuaranteeToolTip: false,
      
      selectedLeague: [],
      leagueList: [],
      merchandiseList: [],
      merchandiseArrList: {},
      multiSelectClassName: "multi-select",
      prizeType: [],
      merchandiseObj: {},
      prize_profit: { min_total: 0, max_total: 0, min_gross_profit: 0, max_gross_profit: 0, min_net_profit: 0, max_net_profit: 0 },
      SPONSER_IMAGE_NAME: '',
      SPONSER_PIMAGE_NAME: '',
      wrongLink: true,
      savePosting: false,
      isShowHeadName: false,
      SPONSER_DTLMAGE_NAME: '',
      SPONSER_PIDTLMAGE_NAME: '',
      TotalMerchadiseDistri: 0,
      SWinToolTip: false,
    };
  }

  componentDidMount() {
    if (HF.allowLiveStockFantasy() == '0') {
      notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
      this.props.history.push('/dashboard')
    }
    // this.GetFixtureDetail();
    this.initPayoutData();
    this.GetContestTemplateMasterData();
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
    WSManager.Rest(NC.baseURL + NC.SF_GET_CONTEST_TEMPLATE_MASTER_DATA, {}).then((responseJson) => {
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
          merchandiseList.push({
            value: lObj.merchandise_id,
            label: lObj.name + '(' + lObj.price + ')',
            price: lObj.price,
          });
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

  handleSelect = (eleObj, dropName) => {
    if (eleObj != null) {
      let contestObject = _.clone(this.state.contestObject);
      contestObject[dropName] = eleObj.value;
      if (dropName == "entry_fee_type" && eleObj.value == "0") {
        contestObject["max_bonus_allowed"] = "100";
      } else if (dropName == "entry_fee_type" && eleObj.value == "1") {
        contestObject["max_bonus_allowed"] = this.state.max_bonus_allowed;
      } else if (dropName == "entry_fee_type" && eleObj.value == "2") {
        contestObject["max_bonus_allowed"] = "0";
      }
      this.setState({ contestObject: contestObject });
    }
  }

  getTotMerchandise = () => {
    let TotMerDis = 0
    this.state.payout_data.map((pData) => {
      TotMerDis += !_.isUndefined(pData.mer_price) ? parseInt(pData.mer_price) : 0
    });
    this.setState({ TotalMerchadiseDistri: TotMerDis })
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
        payout_data[idx]['mer_price'] = (eleObj.price * ((parseInt(payout_data[idx]['max']) - parseInt(payout_data[idx]['min'])) + 1));
      } else {
        payout_data[idx]['mer_price'] = 0
      }
      this.setState({ payout_data: payout_data }, () => {
        this.getTotMerchandise()
        this.calculateProfitData();
      });
    }
  }

  // resetPrizePool = () => {
  //   let contestObject = _.cloneDeep(this.state.contestObject)
  //   let max_prize_pool = parseFloat(contestObject.minimum_size * contestObject.entry_fee);
  //   contestObject['prize_pool_type'] = "1";
  //   contestObject.prize_pool = max_prize_pool
  //   this.setState({ contestObject: contestObject })
  // }

  handleFieldVal = (e, tindex, element_id) => {
    if (e) {
      WSManager.removeErrorClass("contest_template_form", element_id);
      let name = '';
      let value = '';
      name = e.target.name;
      value = e.target.value;
      let contestObject = _.cloneDeep(this.state.contestObject)

      if (tindex == "entry_fee") {
        value = value.replace('-', "");
      }

      if (tindex == "max_bonus_allowed" || tindex == "prize_pool" || tindex == "minimum_size" || tindex == "size" || tindex == "entry_fee") {
        value = value.replace(/[^0-9]/g, '');
      }
      contestObject[tindex] = value

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

      if (tindex == "max_bonus_allowed" && value > 100) {
        notify.show("Max alloed bonus should be less than equal to 100%", "error", 3000);
      }
      // if (tindex == "prize_pool") {
      //   let max_prize_pool = parseFloat(contestObject.minimum_size * contestObject.entry_fee).toFixed(2);
      //   if (parseFloat(value).toFixed(2) != max_prize_pool) {
      //     contestObject['prize_pool_type'] = "2";
      //     contestObject['prize_value_type'] = "0";
      //   } else {
      //     contestObject['prize_pool_type'] = "1";
      //   }
      // }

      this.setState({
        contestObject: contestObject
      }, function () {
        if (tindex == "prize_pool" || tindex == "max_bonus_allowed") {
          this.initPayoutData();
        }
      })
    }
  }

  handleCheckboxFieldVal = (e, tindex) => {
    if (e) {
      let name = '';
      let value = '';
      name = e.target.name;
      value = e.target.value;
      let contestObject = _.cloneDeep(this.state.contestObject);
      contestObject[tindex] = value == 'false' ? true : false;
      this.setState({
        contestObject: contestObject
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

      //Start Logic for on change contest type
      if (tindex === 'prize_pool_type' && value === '1' && contestObject.minimum_size && contestObject.size && contestObject.entry_fee) {
        let prize_pool = (contestObject.minimum_size * contestObject.entry_fee);
        prize_pool = prize_pool.toFixed(0);
        contestObject['prize_pool'] = prize_pool;
      }
      if (tindex === 'prize_pool_type' && value === '2')
      {
        contestObject['prize_value_type'] = '0';
      }
      //End Logic for on change contest type

      this.setState({
        contestObject: contestObject
      }, function () {
        this.initPayoutData();
      })
    }
  }

  handlePrizeTieBreaker = (e, tindex) => {
    if (e) {
      let value = '';
      value = e.target.value;
      let contestObject = _.cloneDeep(this.state.contestObject);
      contestObject[tindex] = value == 'false' ? true : false;
      this.setState({
        contestObject: contestObject
      }, function () {
        this.initPayoutData();
      })
    }
  }

  CreateContest = () => {
    let { collection_id, ActiveFxType, ActiveTab, FixtureValue } = this.state
    let contestObject = _.cloneDeep(this.state.contestObject);
    if (parseInt(contestObject.max_bonus_allowed) > 100) {
      notify.show("Max alloed bonus should be less than equal to 100%", "error", 3000);
      return false;
    }
    if (parseInt(contestObject.minimum_size) < 2) {
      notify.show("minimum size should be greater than equal to 2.", "error", 3000);
      return false
    }

    if (contestObject.set_sponsor == '1') {
      if (contestObject.sponsor_logo == '' && contestObject.sponsor_contest_dtl_image == '') {
        notify.show("Please fill sponser info.", "error", 3000);
        return false
      }
      if (contestObject.sponsor_logo != '' && contestObject.sponsor_link == '') {
        notify.show("Sponsored link can not be empty", "error", 3000);
        return false
      }
      if (contestObject.sponsor_contest_dtl_image != '' && contestObject.sponsor_link == '') {
        notify.show("Sponsored link can not be empty", "error", 3000);
        return false
      }
    }
    if (parseFloat(contestObject.prize_pool) < parseFloat(this.state.prize_profit.min_total)) {
      notify.show("Winners prize should be less than or equal to prize pool.", "error", 3000);
      return false
    }
    if (contestObject.master_contest_type_id != "" && contestObject.master_contest_type_id != "5" && contestObject.master_contest_type_id != "6") {
      if (parseInt(contestObject.minimum_size) < parseInt(this.state.winnerPlace[contestObject.master_contest_type_id])) {
        notify.show("Invalid winner selection. winner should be less then or equal to min size.", "error", 3000);
        return false
      }
    }

    if (contestObject.set_sponsor == 1 && !this.state.wrongLink) {
      notify.show("please enter a valid sponsered link", "error", 5000)
      return false;
    }

    let is_valid = 1;
    if ((parseFloat(contestObject.prize_pool) > 0 && parseFloat(contestObject.entry_fee) > 0) || this.state.payout_data.length > 1) {
      if (this.state.payout_data.length > 1) {

        _.map(this.state.payout_data, (value, key) => {
          if (parseFloat(value.amount) <= 0 || value.amount == "" || value.amount == "NaN") {
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
    }


    if (is_valid == 1) {
      
      contestObject['collection_id'] = collection_id;
      contestObject['payout_data'] = this.state.payout_data;
      contestObject['season_scheduled_date'] = (collection_id) ? this.state.season_scheduled_date : this.state.fixtureDetail.season_scheduled_date;
      contestObject['category_id'] = (collection_id) ? ActiveFxType : '1';


      if (contestObject.entry_fee_type === '2')
        contestObject['max_bonus_allowed'] = 0;//for coin entry
      contestObject['stock_type'] = "4";


      if (WSManager.validateFormFields("contest_contest_form")) {
        this.setState({ posting: true, savePosting : true })
        let params = contestObject;

        WSManager.Rest(NC.baseURL + NC.SF_CREATE_CONTEST, params).then((responseJson) => {
          if (responseJson.response_code === NC.successCode) {
            notify.show(responseJson.message, "success", 5000);

            this.props.history.push({ pathname: '/livestockfantasy/fixturecontest/' + ActiveTab + '/' + collection_id });
            
          }
          this.setState({ posting: false, savePosting: false })
        })

      } else {
        notify.show("Please fill required fields.", "error", 3000);
        return false;
      }
    }
  }
  replaceAll = (str, find, replace) => {
    return str.replace(new RegExp(find, 'm'), replace);

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
      value = value.replace(/[^0-9]/g, '');
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
    if (contestObject.prize_pool_type == '2') {
      /**For Guranteed contest min max distribution same */
      max_value = prize_pool
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
      notify.show("Please set prize pool.", "error", 3000);
      return false;
    }

    if (payout_data.length > 0) {
      var max = payout_data[payout_data.length - 1].max;
      max = parseInt(max) + 1;
      var size = this.state.contestObject.size;
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

  validatePrizeData = (e, index, merchandise) => {
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
    let per = parseFloat(item_val / (prize_pool / 100));

    console.log("per==", per);

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
      per = parseFloat((item_val * 100) / prize_pool);
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
    payout_data[index]['max_value'] = parseFloat(max_prize_pool).toFixed(2);
    this.setState({ payout_data: payout_data }, () => {
      console.log("payout_data===", this.state.payout_data);

    });
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
      //for coin entry
      if (contestObject.entry_fee_type === "2") {
        contestObject.max_bonus_allowed = "0"
      }

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

      let mxgp = prize_profit.max_gross_profit > 0 ? prize_profit.max_gross_profit : 0;

      if (mxgp == 0 || pp == 0) {
        contestObject.site_rake_max = 0;
      }
      else {
        contestObject.site_rake_max = ((mxgp * 100) / prize_pool).toFixed(2);

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
    var max = payout_data[index].mer_price = 0;
    if (parseInt(min) > parseInt(max)) {
      notify.show("Maximum size should not be less than Minimum size.", "error", 3000);
    }

    if (payout_data.length > 0) {
      var size = this.state.contestObject.size;
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
    var prize_pool = parseFloat(this.state.contestObject.prize_pool);
    var person = (max - min) + 1;
    if (isNaN(prize_pool)) {
      notify.show("Please set prize pool.", "error", 3000);
    }

    if (isNaN(per)) {
      //notify.show("Percent Should be a number.", "error", 3000);
    }
    var amount = ((prize_pool * per) / 100) / person;
    if (isNaN(amount)) {
      var amount = 0;
    }
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
      notify.show("Please set prize pool.", "error", 3000);
    }

    if (isNaN(amount)) {
      notify.show("Amount Should be a number.", "error", 3000);
    }

    var per = ((amount * 100) / prize_pool) * person;
    if (isNaN(per)) {
      per = 0;
    }

    payout_data[index].per = parseFloat(per);
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

    WSManager.multipartPost(NC.baseURL + NC.SF_UPLOAD_CONTEST_TEMPLATE_SPONSER, data)
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

  onChangeContDtlImage = (event) => {
    let contestObject = _.cloneDeep(this.state.contestObject);
    this.setState({
      SPONSER_DTLMAGE_NAME: URL.createObjectURL(event.target.files[0]),
    });
    const file = event.target.files[0];
    if (!file) {
      return;
    }
    var data = new FormData();
    data.append("file", file);
    if (this.state.SPONSER_PIDTLMAGE_NAME != '') {
      data.append("previous_img", this.state.SPONSER_PIDTLMAGE_NAME);
    }

    WSManager.multipartPost(NC.baseURL + NC.SF_DO_UPLOAD_SPONSOR_CONTEST_DTL, data)
      .then(Response => {
        if (Response.response_code == NC.successCode) {
          contestObject.sponsor_contest_dtl_image = Response.data.image_name
          this.setState({
            SPONSER_PIDTLMAGE_NAME: Response.data.image_name,
            contestObject: contestObject
          });
        } else {
          this.setState({
            SPONSER_DTLMAGE_NAME: null
          });
        }
      }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000);
      });
  }

  resetFile = (flag) => {
    if (flag === 1) {
      this.setState({ SPONSER_IMAGE_NAME: null });
    }
    if (flag === 2) {
      this.setState({ SPONSER_DTLMAGE_NAME: null });
    }
  }


  toggle() {
    this.setState({
      isShowRecurrenceToolTip: !this.state.isShowRecurrenceToolTip
    });
  }
  toggle1 = () => {
    this.setState({
      isShowBonusToolTip: !this.state.isShowBonusToolTip
    });
  }
  toggle2 = () => {
    this.setState({
      isShowContestnameToolTip: !this.state.isShowContestnameToolTip
    });
  }
  toggle3 = () => {
    this.setState({
      isShowPincontestToolTip: !this.state.isShowPincontestToolTip
    });
  }
  toggle4 = () => {
    this.setState({
      isShowPrizepoolToolTip: !this.state.isShowPrizepoolToolTip
    });
  }
  toggle5 = () => {
    this.setState({
      isShowNEBAToolTip: !this.state.isShowNEBAToolTip
    });
  }
  toggle7 = () => {
    this.setState({
      isShowPDToolTip: !this.state.isShowPDToolTip
    });
  }
  toggle8 = () => {
    this.setState({
      isShowTBToolTip: !this.state.isShowTBToolTip
    });
  }
  toggle9 = () => {
    this.setState({
      isShowSPToolTip: !this.state.isShowSPToolTip
    });
  }
  toggle10 = () => {
    this.setState({
      isShowBrkgToolTip: !this.state.isShowBrkgToolTip
    });
  }

  toggleHeaderName = () => {
    this.setState({
      isShowHeadName: !this.state.isShowHeadName
    });
  }

  reverseFantTogg = () => {
    this.setState({
      RevFantToolTip: !this.state.RevFantToolTip
    });
  }

  SWinToggle = () => {
    this.setState({
      SWinToolTip: !this.state.SWinToolTip
    });
  }

  render() {
    const classes = 'tooltip-inner'
    let {
      savePosting,
      fixtureDetail,
      groupList,
      contestObject,
      multipleLineupList,
      entryFeeType,
      payout_data,
      prize_profit,
      SPONSER_IMAGE_NAME,
      SPONSER_PIMAGE_NAME,
      SPONSER_DTLMAGE_NAME,
      SPONSER_PIDTLMAGE_NAME,
      TotalMerchadiseDistri,
      RevFantToolTip,
      SWinToolTip,
    } = this.state

    return (
      <div className="SpCreateContest ftp-wrap animate-left">
        <Row className="mb-1">
          <Col xs="12" lg="12">
            <Card className="recentcom">
              <CardHeader className="contestcreate">
                <h5 className="DFScontest">Contest Contest</h5>
              </CardHeader>
              <CardBody className="contestcard">
                <Row>
                 
                  
                  <Col sm={4}>
                    <div class="form-group gray-form-group">
                      <label for="template_name" className="fixturevs">Enter Contest Name
                          <span className="btn-information"><img id="isShowContestnameToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowContestnameToolTip} target="isShowContestnameToolTip" toggle={this.toggle2}>
                            Contest name
                            </Tooltip>
                        </span>
                      </label>
                      <input className="contestname required" id="contest_name" name="contest_name" value={contestObject.contest_name} onChange={(e) => this.handleFieldVal(e, 'contest_name', 'contest_name')} placeholder="Contest Name (eg. WIN 540)"></input>
                    </div>
                  </Col>
                  
                  <Col sm={4}>
                    <div class="form-group gray-form-group">
                      <label for="template_name" className="fixturevs font-sm">Contest Header Name (Optional)
                      <span className="btn-information">
                          <img id="isShowHeadName" className="infobtn" src={Images.INFO} />
                          <Tooltip
                            placement="top"
                            isOpen={this.state.isShowHeadName}
                            target="isShowHeadName"
                            toggle={this.toggleHeaderName}>
                            This will be shown on contest card (If you leave it blank, max contest prize will be visible)
                            </Tooltip>
                        </span>
                      </label>
                      <input
                        className="contestname"
                        id="contest_title"
                        name="contest_title"
                        value={contestObject.contest_title}
                        onChange={(e) => this.handleFieldVal(e, 'contest_title', 'contest_title')}
                        placeholder="Contest Header Name"
                      >
                      </input>
                    </div>
                  </Col>
                
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
                  
                  <Col sm={4}>
                    <div className="form-group gray-form-group">
                      <label for="entry_fee_type" className="fixturevs">Entry Fee</label>
                      <Select
                        className="entry-fee-select gray-select-field ml-0"
                        id="entry_fee_type"
                        name="entry_fee_type"
                        placeholder="Select Entry Fee Type"
                        value={contestObject.entry_fee_type}
                        options={entryFeeType}
                        onChange={(e) => this.handleSelect(e, 'entry_fee_type')}
                      />
                      <input type="text" pattern="[0-9]+" className="contestname xentryfee entryfee-wdt" name="entry_fee" value={contestObject.entry_fee} onChange={(e) => { this.handleFieldVal(e, 'entry_fee', 'entry_fee'); this.calculatePrizePool(e, 'entry_fee') }} placeholder={HF.getCurrencyCode()}></input>
                      <div className="field-info-text">Enter 0 to create a free contest</div>
                    </div>
                  </Col>

                  <Col sm={4} className="mt-4">
                    <div className="form-group gray-form-group">
                      <label htmlFor="prize_pool" className="fixturevs">Contest Type</label>
                      <div className="input-box radio-input-box p-0 pt-2">
                        <ul className="coupons-option-list">
                          <li className="coupons-option-item">
                            <div className="custom-radio">
                              <input
                                type="radio"
                                className="custom-control-input"
                                id="is_auto"
                                name="prize_pool_type"
                                value="1"
                                checked={contestObject.prize_pool_type === '1'}
                                onChange={(e) => this.handlePrizeInPercentage(e, 'prize_pool_type')} />
                              <label className="custom-control-label" htmlFor="is_auto">
                                <span className="input-text">Auto</span>
                              </label>
                            </div>
                          </li>
                          <li className="coupons-option-item">
                            <div className="custom-radio">
                              <input
                                type="radio"
                                className="custom-control-input"
                                id="is_fixed_value"
                                name="prize_pool_type"
                                value="2"
                                checked={contestObject.prize_pool_type === '2'}
                                onChange={(e) => this.handlePrizeInPercentage(e, 'prize_pool_type')} />
                              <label className="custom-control-label" htmlFor="is_fixed_value">
                                <span className="input-text">Guaranteed</span>
                              </label>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </Col>

                  <Col sm={4} className="mt-4">
                    <div className="form-group gray-form-group">
                      <label for="prize_pool" className="fixturevs">Prize Pool
                          <span className="btn-information"><img id="isShowPrizepoolToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowPrizepoolToolTip} target="isShowPrizepoolToolTip" toggle={this.toggle4}>
                            Prize pool is the amount which will be distributed amongs winners.
                            </Tooltip>
                        </span>
                        {/* {contestObject.prize_pool_type == '2' && <div className="guaranteed-box">
                          <span className="guaranteed-contest">
                            Guaranteed
                          </span>
                          <Button
                            className="btn-secondary-outline"
                            onClick={this.resetPrizePool}
                          >
                            Reset
                          </Button>
                        </div>} */}
                      </label>
                      <input
                        disabled={contestObject.prize_pool_type == '1'}
                        type="number"
                        className="contestname xprizepool required" name="prize_pool"
                        id="prize_pool"
                        value={contestObject.prize_pool}
                        onChange={(e) => this.handleFieldVal(e, 'prize_pool', 'prize_pool')}
                        placeholder="Enter Prize Pool"></input>
                      {/* <div className="field-info-text">Edit to the prize pool will make this guaranteed contest</div> */}
                    </div>
                  </Col>

                  {/* <Col sm={4} className="mt-4">
                    <div class="form-group">
                      <label for="multiple_lineup" className="entriesno">Number of entries allowed
                        <span className="btn-information"><img id="isShowNEBAToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowNEBAToolTip} target="isShowNEBAToolTip" toggle={this.toggle5}>
                            Make text Teams allowed. info icon text will be " It means with how many teams user can join this contest.
                            </Tooltip>
                        </span>
                      </label>
                      <Select
                        Searchable={0}
                        className="gray-select-field"
                        id="multiple_lineup"
                        name="multiple_lineup"
                        placeholder="Select Team Count"
                        value={contestObject.multiple_lineup}
                        options={multipleLineupList}
                        onChange={(e) => this.handleSelect(e, 'multiple_lineup')}
                      />
                    </div>
                  </Col> */}

                  <Col sm={4} className="">
                    <div className="form-group gray-form-group">
                      <label for="prize_pool" className="fixturevs">Brokerage (%)
                          <span className="btn-information"><img id="isShowBrkgToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowBrkgToolTip} target="isShowBrkgToolTip" toggle={this.toggle10}>
                          This amount will be debited on every transaction by user.
                            </Tooltip>
                        </span>
                      </label>
                      <input
                        // disabled={contestObject.prize_pool_type == '1'}
                        type="number"
                        className="contestname xprizepool required" name="brokerage"
                        id="brokerage"
                        value={contestObject.brokerage}
                        onChange={(e) => this.handleFieldVal(e, 'brokerage', 'brokerage')}
                        placeholder="Enter Brokerage %"></input>
                      {/* <div className="field-info-text">Edit to the prize pool will make this guaranteed contest</div> */}
                    </div>
                  </Col>
                
                  <Col sm={4} className="mt-4">
                    <div class="form-group">
                      <label for="is_auto_recurring" className="fixturevs">Recurrence
                        <span className="btn-information"><img id="isShowRecurrenceToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowRecurrenceToolTip} target="isShowRecurrenceToolTip" toggle={this.toggle}>
                            {CONTEST_RECUR_TT}
                            </Tooltip>
                        </span>
                      </label>
                      <div className="autorecurrentdiv">
                        <Input type="checkbox" id="is_auto_recurring" name="is_auto_recurring" value={contestObject.is_auto_recurring} onChange={(e) => this.handleCheckboxFieldVal(e, 'is_auto_recurring')} className="custom-control-input"></Input>
                        <label className="custom-control-label" for="is_auto_recurring">Auto - Recurrent</label>
                      </div>
                    </div>
                  </Col>
                
                  {/* <Col sm={4} className="mt-4">
                    <div class="form-group gray-form-group">
                      <label for="template_name" className="fixturevs">Pin contest
                          <span className="btn-information"><img id="isShowPincontestToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowPincontestToolTip} target="isShowPincontestToolTip" toggle={this.toggle3}>
                            {CONTEST_PIN}
                            </Tooltip>
                        </span>
                      </label>
                      <div className="autorecurrentdiv">
                        <Input type="checkbox" id="is_pin_contest" name="is_pin_contest" value={contestObject.is_pin_contest} onChange={(e) => this.handleCheckboxFieldVal(e, 'is_pin_contest')} className="custom-control-input"></Input>
                        <label className="custom-control-label" for="is_pin_contest"></label>
                      </div>
                    </div>
                  </Col> */}
                  {/* {
                    HF.allowScratchWin() == '1' &&
                    <Col sm={4} className="mt-4">
                      <div className="form-group">
                        <label htmlFor="is_scratchwin" className="fixturevs">Scratch & Win
                        <span className="btn-information">
                            <img id="swin" className="infobtn" src={Images.INFO} />
                            <Tooltip placement="left" isOpen={SWinToolTip} target="swin" toggle={this.SWinToggle}>{SCRATCH_WIN_CHECK}</Tooltip>
                          </span>
                        </label>
                        <div className="autorecurrentdiv">
                          <Input
                            type="checkbox"
                            id="is_scratchwin"
                            name="is_scratchwin"
                            value={contestObject.is_scratchwin}
                            onChange={(e) => this.handleCheckboxFieldVal(e, 'is_scratchwin')}
                            className="custom-control-input" />
                          <label className="custom-control-label" htmlFor="is_scratchwin"></label>
                        </div>
                      </div>
                    </Col>
                  } */}
                </Row>
              </CardBody>
            </Card>
          </Col>
        </Row>
        <Row className="mb-1">
          <Col xs="12" lg="12">
            <Card className="recentcom">
              <CardBody className="contestcard">
                <Row>
                  <Col sm={4}>
                    <label className="fixturevs">Prize Distribution
                        <span className="btn-information"><img id="isShowPDToolTip" className="infobtn" src={Images.INFO} />
                        <Tooltip placement="right" isOpen={this.state.isShowPDToolTip} target="isShowPDToolTip" toggle={this.toggle7}>
                          {CONTEST_PRZ_DIS_TT}
                       </Tooltip>
                      </span>
                    </label>
                    <div className="input-box radio-input-box">
                      <ul className="coupons-option-list">
                        <li className="coupons-option-item">
                          <div className="custom-radio">
                            <input disabled={contestObject.prize_pool_type == '2'} type="radio" className="custom-control-input" id="is_percentage" name="prize_value_type" value="1" checked={contestObject.prize_value_type === '1'} onChange={(e) => this.handlePrizeInPercentage(e, 'prize_value_type')} />
                            <label className="custom-control-label" for="is_percentage">
                              <span className="input-text">In Percentage</span>
                            </label>
                          </div>
                        </li>
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
                  <Col sm={4}>
                    <label className="fixturevs">Tie Breaker
                        <span className="btn-information"><img id="isShowTBToolTip" className="infobtn" src={Images.INFO} />
                        <Tooltip placement="right" isOpen={this.state.isShowTBToolTip} target="isShowTBToolTip" toggle={this.toggle8}>
                          {CONTEST_TIE_BRE_TT}
                       </Tooltip>
                      </span>
                    </label>
                    <div className="autorecurrentdiv ">
                      <Row>
                        <Col sm={12}><Input type="checkbox" id="is_tie_breaker" name="is_tie_breaker" value={contestObject.is_tie_breaker} onChange={(e) => this.handlePrizeTieBreaker(e, 'is_tie_breaker')} className="custom-control-input"></Input>
                          <label className="custom-control-label" for="is_tie_breaker">Yes</label></Col>
                      </Row>
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
                          <th className="">
                            Total Distribution
                                <div>{contestObject.minimum_size} Users</div>
                          </th>
                          <th className="">
                            Total Distribution
                                <div>{contestObject.size} Users</div>
                          </th>
                          <th>&nbsp;</th>
                        </tr>
                      </thead>
                      <tbody>
                        {payout_data.map((item, idx) => (
                          <tr id="addr0" key={idx}>
                            <td className="rank-input-filed">
                              <Input
                                disabled="1"
                                className="gray-form-control required"
                                type="text"
                                name="min"
                                value={payout_data[idx].min}
                                onChange={(e) => this.validatePrizeData(e, idx, 0)}
                                placeholder="01"
                              />
                              <span className="span">-</span>
                              <Input
                                className="gray-form-control required"
                                type="text"
                                name="max"
                                value={payout_data[idx].max}
                                onChange={(e) => {
                                  this.onChangeMax(idx);
                                  this.validatePrizeData(e, idx, 0)
                                }}
                                placeholder="02"
                              />
                            </td>
                            <td>
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
                            <td className="percentage-td">
                              {payout_data[idx].prize_type == 3 &&
                                <Select
                                  isSearchable={false}
                                  isClearable={false}
                                  className="position gray-select-field"
                                  name="value"
                                  placeholder="Select Merchandise"
                                  value={payout_data[idx].amount}
                                  // value={payout_data[idx].mer_id}
                                  options={this.state.merchandiseList}
                                  onChange={(e) => {
                                    this.handleSelectPayout(e, idx, 'amount')
                                    // this.validatePrizeData(e, idx, 1);
                                    // this.onChangeAmount(idx)
                                  }}
                                />
                              }
                              {payout_data[idx].prize_type != 3 &&
                                <Input className="gray-form-control required" type="text" name="amount" value={payout_data[idx].amount} onChange={(e) => { this.validatePrizeData(e, idx, 0); this.onChangeAmount(idx) }} placeholder="0" />
                              }
                            </td>
                            <td className="user-prize-distribution">
                              {payout_data[idx].prize_type == 1 &&
                                <i>{HF.getCurrencyCode()}</i>
                              }
                              {payout_data[idx].prize_type == 0 &&
                                <i className="icon-bonus"></i>
                              }
                              {payout_data[idx].prize_type == 2 &&
                                <img src={Images.REWARD_ICON} />
                              }
                              {payout_data[idx].min_value}
                              {
                                (payout_data[idx].prize_type == 3 && !_.isUndefined(payout_data[idx].mer_price) && !_.isEmpty(payout_data[idx].min_value) && payout_data[idx].min_value != '0.00') && (
                                  '(' + payout_data[idx].mer_price + ')'
                                )
                              }
                            </td>
                            <td className="user-prize-distribution">
                              {payout_data[idx].prize_type == 1 &&
                                <i>{HF.getCurrencyCode()}</i>
                              }
                              {payout_data[idx].prize_type == 0 &&
                                <i className="icon-bonus"></i>
                              }
                              {payout_data[idx].prize_type == 2 &&
                                <img src={Images.REWARD_ICON} />
                              }
                              {payout_data[idx].max_value}
                              {
                                (payout_data[idx].prize_type == 3 && !_.isUndefined(payout_data[idx].mer_price) && !_.isEmpty(payout_data[idx].max_value) && payout_data[idx].max_value != '0.00') && (
                                  '(' + payout_data[idx].mer_price + ')'
                                )
                              }
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
                              <div className="add-prize-subtext">Site Rake is
                                <span style={{ width: "auto", display: 'inlineBlock', marginLeft: 3, marginRight: 3 }} className={contestObject.site_rake <= 0 ? "text-danger" : ""}>

                                  {
                                    contestObject.prize_pool <= 0 &&
                                    prize_profit.min_gross_profit + " Rs"
                                  }
                                  {
                                    (!contestObject.prize_pool || contestObject.prize_pool > 0) &&
                                      contestObject.prize_pool_type == '1' ? contestObject.site_rake + "%" : ""
                                  }
                                </span>
                                based on the real cash distribution above.
                                {

                                  contestObject.prize_pool_type == '200000' &&
                                  <div>
                                    In case of min{contestObject.prize_pool_type == '1' ? ' users' : ''}
                                    <span style={{ width: "auto", display: 'inlineBlock', marginLeft: 3, marginRight: 3 }} className={contestObject.site_rake <= 0 ? "text-danger" : ""}>
                                      {
                                        (!contestObject.prize_pool || contestObject.prize_pool > 0) &&
                                          contestObject.prize_pool_type == '1' ? contestObject.site_rake + "% " :
                                          ""
                                      }
                                    </span>

                                    & max users &nbsp;
                                <span style={{ width: "auto", display: 'inlineBlock', marginLeft: 3, marginRight: 3 }} className={contestObject.site_rake_max <= 0 ? "text-danger" : ""}>
                                      {
                                        (!contestObject.prize_pool || contestObject.prize_pool > 0) &&

                                          contestObject.prize_pool_type == '2' ? contestObject.site_rake + "% " :
                                          contestObject.site_rake_max + "% "

                                      }
                                    </span>
                                  </div>
                                }
                              </div>
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
                                  <td>{prize_profit.max_total}</td>
                                </tr>
                                <tr>
                                  <td className={prize_profit.min_gross_profit < 0 ? "text-danger" : ""}>{prize_profit.min_gross_profit}</td>
                                  <td>Gross Profit</td>
                                  <td className={prize_profit.max_gross_profit < 0 ? "text-danger" : ""}>{prize_profit.max_gross_profit}</td>
                                </tr>
                                <tr>
                                  <td className={prize_profit.min_net_profit < 0 ? "text-danger" : ""}>{prize_profit.min_net_profit}</td>
                                  <td>
                                    Net Profit <span>(After Bonus Deducted)</span>
                                  </td>
                                  <td className={prize_profit.max_net_profit < 0 ? "text-danger" : ""}>{prize_profit.max_net_profit}</td>
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

        {contestObject.is_tie_breaker && <Row className="mb-1 sponser-section">
          <Col xs="12" lg="12">
            <div className="mer-dis-note">
              <span className="font-weight-bold mr-2">Note:</span>
              Merchandise value {HF.getCurrencyCode()}{TotalMerchadiseDistri} will be extra apart from total real cash distribution in this contest.
                </div>
          </Col>
        </Row>}

        <Row className="mb-1 sponser-section">
          <Col xs="12" lg="12">
            <Card className="recentcom">
              <CardBody className="contestcard">
                <Row>
                  <Col sm={4} md={4}>
                    <label for="set_sponsor1" className="fixturevs">Sponsored
                        <span className="btn-information"><img id="isShowSPToolTip" className="infobtn" src={Images.INFO} />
                        <Tooltip placement="right" isOpen={this.state.isShowSPToolTip} target="isShowSPToolTip" toggle={this.toggle9}>{CONTEST_SPONSOR_TT}</Tooltip>
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
                      <Col sm={8} md={8}>
                        <Row>
                          <Col sm={6} md={6}>
                            <div class="form-group multiselect-wrapper spr-link-wdt">
                              <label for="league_id" className="fixturevs ">Sponsored Link</label>
                              <input disabled={contestObject.set_sponsor == 0} className="contestname gray-form-control" id="sponsor_link" name="sponsor_link" maxLength={255}
                                value={contestObject.sponsor_link}
                                onChange={(e) => this.handleFieldVal(e, 'sponsor_link', 'sponsor_link')} placeholder="sponsored link"></input>

                            </div>
                          </Col>
                        </Row>
                        <Row className="mt-30">
                          <Col sm={6} md={6}>
                            <div class="xupload-sponsored-img">
                              <figure className="upload-spr-img con-d-spr-img">
                                {!_.isEmpty(SPONSER_DTLMAGE_NAME) ?
                                  <Fragment>
                                    <a
                                      href
                                      onClick={() => this.resetFile(2)}
                                    >
                                      <i className="icon-close"></i>
                                    </a>
                                    <img className="img-cover" src={NC.S3 + NC.SPONSER_IMG_PATH + SPONSER_PIDTLMAGE_NAME} />
                                  </Fragment>
                                  :
                                  <Fragment>
                                    <Input
                                      type="file"
                                      name='merchandise_image'
                                      id="merchandise_image"
                                      className="gift_spr_image"
                                      onChange={(e) => this.onChangeContDtlImage(e)}
                                    />
                                    <i onChange={(e) => this.onChangeContDtlImage(e)} className="icon-camera"></i>
                                  </Fragment>
                                }
                              </figure>
                              <div className="spr-help-text">Wiil be shown on contest detail page,Minimum Size of 1100 by 88.</div>
                            </div>
                          </Col>
                          <Col sm={6} md={6}>
                            <div class="xupload-sponsored-img">
                              <figure className="upload-spr-img con-d-spr-img">
                                {!_.isEmpty(SPONSER_IMAGE_NAME) ?
                                  <Fragment>
                                    <a
                                      href
                                      onClick={() => this.resetFile(1)}
                                    >
                                      <i className="icon-close"></i>
                                    </a>
                                    <img className="img-cover" src={NC.S3 + NC.SPONSER_IMG_PATH + SPONSER_PIMAGE_NAME} />
                                  </Fragment>
                                  :
                                  <Fragment>
                                    <Input                                      
                                      type="file"
                                      name='merchandise_image'
                                      id="merchandise_image"
                                      className="gift_spr_image"
                                      onChange={(e) => this.onChangeImage(e)}
                                    />
                                    <i onChange={(e) => this.onChangeImage(e)} className="icon-camera"></i>
                                  </Fragment>
                                }
                              </figure>
                              <div className="spr-help-text">Wiil be shown on contest card on listing page,Minimum Size of 1036 by 60.</div>
                            </div>
                          </Col>
                        </Row>
                      </Col>
                    </Fragment>
                  }
                </Row>
              </CardBody>
            </Card>
          </Col>
        </Row>

        <Row className="verifyrow">
          <Col lg={12}>
            <Button 
            onClick={() => { this.CreateContest() }} 
            disabled={savePosting}
            className='btn-secondary-outline'>Create Contest</Button>
          </Col>
        </Row>

      </div>
    );
  }
}

export default LSF_CreateContest;