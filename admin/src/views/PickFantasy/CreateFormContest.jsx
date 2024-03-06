import React, { Component, Fragment } from 'react';
import Select from 'react-select';
import { Card, CardBody, CardHeader, Col, Row, Input, Button, Table, Tooltip } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import LS from 'local-storage';
import MultiSelect from "@khanacademy/react-multi-select";
import Images from '../../components/images';
import { notify } from 'react-notify-toast';
import HF from '../../helper/HelperFunction';
import { REVERSE_FANTASY_TT, SCRATCH_WIN_CHECK, CONTEST_PRZ_DIS_TT, CONTEST_TIE_BRE_TT, CONTEST_HEADER_NAME_TT, CONTEST_SPONSOR_TT, CONTEST_RECUR_TT, CONTEST_BONUS, SECOND_INNING } from '../../helper/Message';
import { Base64 } from 'js-base64';
class PFCreateFormContest extends Component {
  constructor(props) {
    super(props);
    this.toggle = this.toggle.bind(this);
    this.state = {
      selectedLeague: [],
      leagueList: [],
      groupList: [],
      merchandiseList: [],
      merchandiseArrList: {},
      multipleLineupList: [],
      entryFeeType: [],
      // selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      contestTemplate: { 'league_id': [], 'multiple_lineup': '1', 'entry_fee_type': '1', 'max_bonus_allowed': '0', 'prize_type': '1', 'prize_pool_type': '1', "master_contest_type_id": "1", "group_id": "1", "is_auto_recurring": false, 'site_rake': 0, 'site_rake_max': 0, 'minimum_size': '2', 'size': '2', 'custom_total_percentage': '100', 'custom_total_amount': '0', 'prize_value_type': '0', 'is_tie_breaker': true, 'sponsor_name': '', 'sponsor_logo': '', 'sponsor_link': '', 'set_sponsor': '0', 'sponsor_contest_dtl_image': ''},
      IsValidate: false,
      IsFormValid: true,
      payout_data: [],
      rows: [{}],
      isShowRecurrenceToolTip: false,
      isShowBonusToolTip: false,
      isShowAutoToolTip: false,
      isShowCustomToolTip: false,
      isShowGuaranteeToolTip: false,
      isShowPDToolTip: false,
      isShowTieToolTip: false,
      isShowSPToolTip: false,
      multiSelectClassName: "multi-select",
      prizeType: [],
      merchandiseObj: {},
      prize_profit: { min_total: 0, max_total: 0, min_gross_profit: 0, max_gross_profit: 0, min_net_profit: 0, max_net_profit: 0 },
      SPONSER_IMAGE_NAME: '',
      SPONSER_PIMAGE_NAME: '',
      wrongLink: true,
      isShowHeadName: false,
      SPONSER_DTLMAGE_NAME: '',
      SPONSER_PIDTLMAGE_NAME: '',
      TotalMerchadiseDistri: 0,
      RevFantToolTip: false,
      SWinToolTip: false,
      contest_template_id: (this.props.match.params.contest_template_id) ? Base64.decode(this.props.match.params.contest_template_id) : false,
      copyPosting: false,
      SecInniToolTip: false,
      season_id: '',
      league_id: '',
      sports_id: '',
    };
  }

  componentDidMount() {
    if (this.state.contest_template_id) {
      this.GetContestTemplateDetails();
    }
    this.initPayoutData();
    this.setLocaltionProps();
    this.getLocalProps();
    
    // this.GetContestTemplateMasterData();
    
  }
  getLocalProps = () =>{
    let matchDetails = LS.get('matchDetails')
    this.setState({
        league_id: matchDetails[0].league_id,
        season_id: matchDetails[0].season_id,
        sports_id: matchDetails[0].sports_id,
    })
  }
  setLocaltionProps=()=>{
    let sports_id = LS.get("selectedSport") ? LS.get("selectedSport") : [];
    this.setState({
      sports_id: sports_id ? sports_id : '1',
    },()=>{
      this.GetContestTemplateMasterData()
      this.GetGroupList()
    })
}
  handleSelect = (eleObj, dropName) => {
    if (eleObj != null) {
      let contestTemplate = _.clone(this.state.contestTemplate);
      contestTemplate[dropName] = eleObj.value;

      if (dropName == "entry_fee_type" && eleObj.value == "0") {
        contestTemplate["max_bonus_allowed"] = "100";
      } else if (dropName == "entry_fee_type" && eleObj.value == "1") {
        contestTemplate["max_bonus_allowed"] = this.state.max_bonus_allowed;
      } else if (dropName == "entry_fee_type" && eleObj.value == "2") {
        contestTemplate["max_bonus_allowed"] = "0";
      }


      this.setState({ contestTemplate: contestTemplate });
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

  GetGroupList = () =>{
    this.setState({ posting: true })
    let param = {}
    WSManager.Rest(NC.baseURL + NC.PF_GET_GROUP_LIST, { "sports_id": this.state.sports_id }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        let groupList = [];
        responseJson.map(function (lObj, lKey) {
          groupList.push({ value: lObj.group_id, label: lObj.group_name })
        })
        this.setState({
          groupList: groupList,
        })
      }
    })
  }
   
  GetContestTemplateMasterData = () => {
    this.setState({ posting: true })
    WSManager.Rest(NC.baseURL + NC.PF_MASTER_DATA, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        console.log('responseJson Template', responseJson)

        let contestTemplate = _.clone(this.state.contestTemplate);
        contestTemplate['max_bonus_allowed'] = responseJson.max_bonus_allowed;
        this.setState({ contestTemplate: contestTemplate });

        let prizeTypeTmp = [];
        let merchandiseObj = {};
        responseJson.prize_type.map(function (lObj, lKey) {
          if (lObj.value == 3) {
            merchandiseObj = lObj;
          } else {
            prizeTypeTmp.push(lObj);
          }
        });
        
        this.setState({
          entryFeeType: responseJson.currency_type,
          multipleLineupList: responseJson.multiple_lineup,
          prizeType: responseJson.prize_type,
          max_bonus_allowed: responseJson.max_bonus_allowed,
        },()=>{console.log('entryFeeType', this.state.entryFeeType)})

        let merchandiseList = [];
        let merchandiseArrList = {};
        // responseJson.merchandise_list.map(function (lObj, lKey) {
        //   merchandiseList.push({
        //     value: lObj.merchandise_id,
        //     label: lObj.name + '(' + lObj.price + ')',
        //     price: lObj.price,
        //   });
        //   merchandiseArrList[lObj.merchandise_id] = lObj.name;
        // });
        
       
        this.setState({
          // merchandiseObj: merchandiseObj,
         
          // merchandiseList: merchandiseList,
         
          
          // merchandiseArrList: merchandiseArrList
        });
      }
      this.setState({ posting: false })
    })
  }

  handleFieldVal = (e, tindex, element_id) => {
    if (e) {
      WSManager.removeErrorClass("contest_template_form", element_id);
      let name = '';
      let value = '';
      name = e.target.name;
      value = e.target.value;
      let contestTemplate = _.cloneDeep(this.state.contestTemplate)
      if (tindex == "max_bonus_allowed" || tindex == "prize_pool" || tindex == "minimum_size" || tindex == "size" || tindex == "entry_fee") {
        value = value.replace(/[^0-9]/g, '');
      }
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

      if (tindex == "max_bonus_allowed" && value > 100) {
        notify.show("Max alloed bonus should be less than equal to 100%", "error", 3000);
      }
      // if (tindex == "prize_pool") {
      //   let max_prize_pool = parseFloat(contestTemplate.minimum_size * contestTemplate.entry_fee).toFixed(2);
      //   if (parseFloat(value).toFixed(2) != max_prize_pool) {
      //     contestTemplate['prize_pool_type'] = "2";
      //     contestTemplate['prize_value_type'] = "0";
      //   } else {
      //     contestTemplate['prize_pool_type'] = "1";
      //   }
      // }

      this.setState({
        contestTemplate: contestTemplate
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
      let contestTemplate = _.cloneDeep(this.state.contestTemplate);
      contestTemplate[tindex] = value == 'false' ? true : false;
      this.setState({
        contestTemplate: contestTemplate
      }, function () { })
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

  handlePrizeInPercentage = (e, tindex) => {
    if (e) {
      let value = e.target.value;
      let contestTemplate = _.cloneDeep(this.state.contestTemplate);
      contestTemplate[tindex] = value;

      //Start Logic for on change contest type
      if (tindex === 'prize_pool_type' && value === '1' && contestTemplate.minimum_size && contestTemplate.size && contestTemplate.entry_fee) {
        let prize_pool = (contestTemplate.minimum_size * contestTemplate.entry_fee);
        prize_pool = prize_pool.toFixed(0);
        contestTemplate['prize_pool'] = prize_pool;
      }
      if (tindex === 'prize_pool_type' && value === '2') {
        contestTemplate['prize_value_type'] = '0';
      }
      //End Logic for on change contest type

      this.setState({
        contestTemplate: contestTemplate
      }, function () {
        this.initPayoutData();
      })
    }
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

  CreateContestTemplate = () => {
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    if (parseInt(contestTemplate.max_bonus_allowed) > 100) {
      notify.show("Max alloed bonus should be less than equal to 100%", "error", 3000);
      return false;
    }
    if (parseInt(contestTemplate.minimum_size) < 2) {
      notify.show("minimum size should be greater than equal to 2.", "error", 3000);
      return false
    }
    // if (contestTemplate.set_sponsor == '1' && (contestTemplate.sponsor_name == '' || contestTemplate.sponsor_logo == '')) 


    // if (contestTemplate.set_sponsor == '1') {
    //   if (contestTemplate.sponsor_logo == '' && contestTemplate.sponsor_contest_dtl_image == '') {
    //     notify.show("Please fill sponser info.", "error", 3000);
    //     return false
    //   }
    //   if (contestTemplate.sponsor_logo != '' && contestTemplate.sponsor_link == '') {
    //     notify.show("Sponsored link can not be empty", "error", 3000);
    //     return false
    //   }
    //   if (contestTemplate.sponsor_contest_dtl_image != '' && contestTemplate.sponsor_link == '') {
    //     notify.show("Sponsored link can not be empty", "error", 3000);
    //     return false
    //   }
    // }
    if (parseFloat(contestTemplate.prize_pool) < parseFloat(this.state.prize_profit.min_total)) {
      notify.show("Winners prize should be less than or equal to prize pool.", "error", 3000);
      return false
    }

    let is_valid = 1;
    if ((parseFloat(contestTemplate.prize_pool) > 0 && parseFloat(contestTemplate.entry_fee) > 0) || this.state.payout_data.length > 1) {
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
    // let temPayOutData = this.state.payout_data
    // _.map(temPayOutData, (value, key) => {
    //   if (!_.isUndefined(value.min_mer_value)) {
    //     temPayOutData[key].min_value = value.min_mer_value
    //     temPayOutData[key].max_value = value.max_mer_value

    //     delete temPayOutData[key].mer_id
    //     delete temPayOutData[key].min_mer_value
    //     delete temPayOutData[key].max_mer_value
    //   }
    // })
    // this.setState({
    //   payout_data: temPayOutData
    // })

    if (is_valid == 1) {

      contestTemplate['payout_data'] = this.state.payout_data;
      contestTemplate['league_id'] = this.state.league_id;
      contestTemplate['season_id'] = this.state.season_id;
      contestTemplate['sports_id'] = this.state.sports_id ? this.state.sports_id : '1';

      if (contestTemplate.entry_fee_type === '2')
        contestTemplate['max_bonus_allowed'] = 0;//for coin entry


      if (WSManager.validateFormFields("contest_template_form")) {
        if ((contestTemplate.set_sponsor == 1 && this.state.wrongLink) || contestTemplate.set_sponsor == 0) {
         
          this.setState({ posting: true })
          let params = contestTemplate;
         
          WSManager.Rest(NC.baseURL + NC.PF_NEW_CREATE_CONTEST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
              notify.show(responseJson.message, "success", 5000);
              this.props.history.push({ pathname: '/picksfantasy/contest-list/'+ this.state.league_id + '/' + this.state.season_id })
            }
            this.setState({ posting: false })
          })
        } else {
          notify.show("Error in sponsor.", "error", 5000)
        }
        if (contestTemplate.set_sponsor == 1 && !this.state.wrongLink) {
          notify.show("please enter a valid sponsered link", "error", 5000)
        }

      } else {
        notify.show("Please fill required fields.", "error", 3000);
        return false;
      }
    }
  }

  calculatePrizePool = (e, tindex, element_value) => {
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    if (tindex == "prize_pool_type") {
      contestTemplate[tindex] = element_value
      if (element_value == 0 || element_value == 1) {
        // contestTemplate['site_rake'] = this.state.site_rake;
      }
    } else {
      let value = e.target.value;
      value = value.replace(/[^0-9]/g, '');
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

    if (parseInt(contestTemplate.minimum_size) < 2) {
      notify.show("minimum size should be greater than equal to 2.", "error", 3000);
      return false
    }

    if (contestTemplate.minimum_size && contestTemplate.size && parseInt(contestTemplate.minimum_size) > parseInt(contestTemplate.size)) {
      notify.show("size should be greater than min size.", "error", 3000);
      return false;
    }

    if (contestTemplate.minimum_size && contestTemplate.size && contestTemplate.entry_fee) {
      let prize_pool = (contestTemplate.minimum_size * contestTemplate.entry_fee);
      prize_pool = prize_pool.toFixed(0);
      contestTemplate['prize_pool'] = prize_pool;
    }
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
    if (contestTemplate.prize_pool_type == '2') {
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
    this.setState({ payout_data: payout_data, contestTemplate: contestTemplate }, function () {
      this.calculateProfitData();
      this.getTotMerchandise()
    });
  }

  addRow = () => {
    var payout_data = this.state.payout_data;
    var prize_pool = 0;
    if (typeof this.state.contestTemplate.prize_pool != "undefined" && this.state.contestTemplate.prize_pool != "") {
      prize_pool = this.state.contestTemplate.prize_pool;
    }
    if (isNaN(prize_pool)) {
      notify.show("Please set prize pool.", "error", 3000);
      return false;
    }

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

  validatePrizeData = (e, index, merchandise) => {
    let { name, value } = e.target;

    // let name = ''
    // let value = 0
    // if (merchandise)
    // {
    //   name = 'amount'
    //   // value = e.value
    //   value = e.price
    // }else{
    //   name = e.target.name
    //   value = e.target.value
    // }

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
    let prize_pool = this.state.contestTemplate.prize_pool;
    let max_prize_pool = item_val;
    let per = parseFloat(item_val / (prize_pool / 100));
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
      per = parseFloat((item_val * 100) / prize_pool);
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
      // prize_pool = parseFloat(this.state.contestTemplate.entry_fee * contestTemplate.size);
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
      //for coin entry
      if (contestTemplate.entry_fee_type === "2") {
        contestTemplate.max_bonus_allowed = "0"
      }
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

      let mxgp = prize_profit.max_gross_profit > 0 ? prize_profit.max_gross_profit : 0;

      if (mxgp == 0 || pp == 0) {
        contestTemplate.site_rake_max = 0;
      }
      else {
        contestTemplate.site_rake_max = ((mxgp * 100) / prize_pool).toFixed(2);
      }
    }
    this.setState({ prize_profit: prize_profit, contestTemplate: contestTemplate });
  }

  totalPercentage = () => {
    var per_temp = 0;
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    contestTemplate.custom_total_percentage = 100;
    contestTemplate.custom_total_amount = 0;

    _.map(this.state.payout_data, (value, key) => {
      if (value) {
        per_temp += parseFloat(value.per);
        var min = parseInt(value.min);
        var max = parseInt(value.max);
        contestTemplate.custom_total_amount += parseFloat(value.amount * (max - min + 1));
        if (value.amount <= 0) {
          notify.show("Please fill or delete the unfilled row.", "error", 3000);
        }

        if (this.state.contestTemplate.prize_pool_type == '2') {
          var size = this.state.contestTemplate.size;
        } else {
          var size = this.state.contestTemplate.minimum_size;
        }
        if (parseInt(min) > parseInt(max)) {
          var msg = "Maximum size should not be less than Minimum size.";
          notify.show(msg, "error", 3000);
        }

      }
    });

    contestTemplate.custom_total_percentage = per_temp.toFixed(2);
    contestTemplate.custom_total_amount = contestTemplate.custom_total_amount.toFixed(2);
    this.setState({ contestTemplate: contestTemplate });
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

    this.setState({ payout_data: payout_data }, () => {
      console.log("this.state.payout_data==", this.state.payout_data);

    });

    this.onChangePercentage(index);
  }

  onChangePercentage = (index) => {
    let payout_data = this.state.payout_data;
    var per = parseFloat(payout_data[index].per);
    var max = parseInt(payout_data[index].max);
    var min = parseInt(payout_data[index].min);
    var prize_pool = parseFloat(this.state.contestTemplate.prize_pool);
    var person = (max - min) + 1;
    if (isNaN(prize_pool)) {
      notify.show("Please set prize pool.", "error", 3000);
    }

    if (isNaN(per)) {
      // notify.show("Percent Should be a number.", "error", 3000);
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
    var prize_pool = parseFloat(this.state.contestTemplate.prize_pool);
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
      isShowPDToolTip: !this.state.isShowPDToolTip
    });
  }
  toggle3 = () => {
    this.setState({
      isShowTieToolTip: !this.state.isShowTieToolTip
    });
  }
  toggle9 = () => {
    this.setState({
      isShowSPToolTip: !this.state.isShowSPToolTip
    });
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

    WSManager.multipartPost(NC.baseURL + NC.UPLOAD_CONTEST_TEMPLATE_SPONSER, data)
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

  onChangeContDtlImage = (event) => {
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
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

    WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD_SPONSOR_CONTEST_DTL, data)
      .then(Response => {
        if (Response.response_code == NC.successCode) {
          contestTemplate.sponsor_contest_dtl_image = Response.data.image_name
          this.setState({
            SPONSER_PIDTLMAGE_NAME: Response.data.image_name,
            contestTemplate: contestTemplate
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

  GetContestTemplateDetails = () => {
    this.setState({ posting: true })
    WSManager.Rest(NC.baseURL + NC.GET_CONTEST_TEMPLATE_DETAILS, { "contest_template_id": this.state.contest_template_id }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setPreTemplateData(responseJson.data)
      }
      this.setState({ posting: false })
    })
  }

  setPreTemplateData = (routeState) => {
    this.setState({ copyPosting: true })
    let tempCopyD = this.state.contestTemplate
    tempCopyD.group_id = routeState.group_id;
    tempCopyD.contest_name = routeState.contest_name;
    tempCopyD.contest_title = routeState.contest_title;
    tempCopyD.minimum_size = routeState.minimum_size;
    tempCopyD.size = routeState.size;
    tempCopyD.entry_fee = routeState.entry_fee;
    tempCopyD.prize_pool = routeState.prize_pool;
    tempCopyD.max_bonus_allowed = routeState.max_bonus_allowed;
    tempCopyD.multiple_lineup = routeState.multiple_lineup;
    tempCopyD.set_sponsor = routeState.set_sponsor;
    tempCopyD.sponsor_logo = routeState.sponsor_logo;
    tempCopyD.sponsor_contest_dtl_image = routeState.sponsor_contest_dtl_image;

    tempCopyD.sponsor_link = routeState.sponsor_link;
    tempCopyD.prize_value_type = routeState.prize_value_type;
    tempCopyD.entry_fee_type = routeState.entry_fee_type;
    tempCopyD.prize_pool_type = routeState.prize_pool_type;

    if (routeState.is_auto_recurring == 1) {
      tempCopyD.is_auto_recurring = true;
    }
    if (routeState.is_tie_breaker == 1) {
      tempCopyD.is_tie_breaker = true;
    }
    setTimeout(() => {
      this.setState({
        contestTemplate: tempCopyD,
        payout_data: routeState.prize_distibution_detail,
        selectedLeague: routeState.template_leagues,
        SPONSER_DTLMAGE_NAME: routeState.sponsor_contest_dtl_image,
        SPONSER_PIDTLMAGE_NAME: routeState.sponsor_contest_dtl_image,
        SPONSER_IMAGE_NAME: routeState.sponsor_logo,
        SPONSER_PIMAGE_NAME: routeState.sponsor_logo,
        copyPosting: false,
      }, () => {
        this.getTotMerchandise()
      })
    }, 1000);
  }

  SecInniToggle = () => {
    this.setState({
      SecInniToolTip: !this.state.SecInniToolTip
    });
  }

  render() {
    let {
      groupList,
      contestTemplate,
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
      SecInniToolTip,
      selected_sport,
    } = this.state

    return (
      <div className="create-template create-template-gry-input ftp-wrap animate-left">
        <form id="contest_template_form">
          <Row className="mb-1">
            <Col xs="12" lg="12">
              <Card className="recentcom pb-4">
                <CardHeader className="contestcreate">
                  <h5 className="DFScontest">Create Contest Template</h5>
                </CardHeader>

                <CardBody className="contestcard">
                  <Row>
                    <Col sm={4}>
                      <div className="form-group">
                        <label htmlFor="group_id" className="fixturevs">Select contest group</label>
                        <Select
                          className="gray-select-field"
                          id="group_id"
                          name="group_id"
                          placeholder="Select Group"
                          value={contestTemplate.group_id}
                          options={groupList}
                          onChange={(e) => this.handleSelect(e, 'group_id')}
                        />
                      </div>
                    </Col>
                    <Col sm={4}>
                      <div className="form-group gray-form-group">
                        <label htmlFor="contest_name" className="fixturevs">Enter Contest Name
                        </label>
                        <input className="contestname required" id="contest_name" name="contest_name" value={contestTemplate.contest_name} onChange={(e) => this.handleFieldVal(e, 'contest_name', 'contest_name')} placeholder="Contest Name here (eg. WIN 540)"></input>
                      </div>
                    </Col>
                    <Col sm={4}>
                      <div className="form-group gray-form-group">
                        <label htmlFor="contest_name" className="fixturevs font-sm">Contest Header Name(Optional)
                        <span className="btn-information">
                            <img id="isShowHeadName" className="infobtn" src={Images.INFO} />
                            <Tooltip
                              placement="top"
                              isOpen={this.state.isShowHeadName}
                              target="isShowHeadName"
                              toggle={this.toggleHeaderName}>
                              {CONTEST_HEADER_NAME_TT}
                            </Tooltip>
                          </span>
                        </label>
                        <input
                          className="contestname"
                          id="contest_title"
                          name="contest_title"
                          value={contestTemplate.contest_title}
                          onChange={(e) => this.handleFieldVal(e, 'contest_title', 'contest_title')}
                          placeholder="Contest Header Name"
                        ></input>
                      </div>
                    </Col>
                  </Row>
                  <Row className="mt-4">
                    <Col sm={4}>
                      <label htmlFor="minimum_size" className="fixturevs">Participants</label>
                      <ul className="minmax-list">
                        <li className="minmax-item mr-3">
                          <div className="form-group minmax-size gray-form-group">
                            <input disabled={contestTemplate.group_id == HF.get_h2h_group_id()} type="number"
                              className={`contestname min-max-size required ${(contestTemplate.group_id == HF.get_h2h_group_id()) ? 'c-disable' : ''}`}
                              name="minimum_size" value={contestTemplate.minimum_size} onChange={(e) => (this.handleFieldVal(e, 'minimum_size', 'minimum_size'), this.calculatePrizePool(e, 'minimum_size'))} placeholder="Min. Size"></input>
                          </div>
                        </li>
                        <li className="minmax-item">
                          <div className="form-group minmax-size gray-form-group">
                            <input disabled={contestTemplate.group_id == HF.get_h2h_group_id()} type="number"
                              className={`contestname min-max-size required ${(contestTemplate.group_id == HF.get_h2h_group_id()) ? 'c-disable' : ''}`}
                              name="size" value={contestTemplate.size} onChange={(e) => (this.handleFieldVal(e, 'size', 'size'), this.calculatePrizePool(e, 'size'))} placeholder="Max. Size"></input>
                          </div>
                        </li>
                      </ul>
                    </Col>
                    <Col sm={4}>
                      <div className="form-group gray-form-group">
                        <label htmlFor="entry_fee_type" className="fixturevs">Entry Fee</label>
                        <Select
                          className="entry-fee-select gray-select-field temp-entry-fee"
                          id="entry_fee_type"
                          name="entry_fee_type"
                          placeholder="Select Entry Fee Type"
                          value={contestTemplate.entry_fee_type}
                          options={entryFeeType}
                          onChange={(e) => this.handleSelect(e, 'entry_fee_type')}
                        />
                        <input type="text" className="contestname xentryfee entryfee-wdt" name="entry_fee" value={contestTemplate.entry_fee} onChange={(e) => { this.handleFieldVal(e, 'entry_fee', 'entry_fee'); this.calculatePrizePool(e, 'entry_fee') }}></input>
                        <div className="field-info-text">Enter 0 to create a free contest</div>
                      </div>
                    </Col>
                    <Col sm={4}>
                      <div className="form-group gray-form-group">
                        <label htmlFor="prize_pool" className="fixturevs">Prize Pool</label>
                        <input
                          disabled={contestTemplate.prize_pool_type == '1'}
                          type="number"
                          className="contestname required"
                          name="prize_pool"
                          id="prize_pool"
                          value={contestTemplate.prize_pool} onChange={(e) => this.handleFieldVal(e, 'prize_pool', 'prize_pool')} placeholder="Enter Prize Pool" />
                        {/* <div className="field-info-text">Edit to the prize pool will make this guaranteed contest</div> */}
                      </div>
                    </Col>
                  </Row>
                  <Row className="mt-4">
                    <Col sm={4}>
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
                                  checked={contestTemplate.prize_pool_type === '1'}
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
                                  checked={contestTemplate.prize_pool_type === '2'}
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
                    <Col sm={4} className="">
                      <div className="form-group">
                        <label htmlFor="multiple_lineup" className="entriesno">Number of entries allowed</label>
                        <Select
                          Searchable={0}
                          className="gray-select-field"
                          id="multiple_lineup"
                          name="multiple_lineup"
                          placeholder="Select Team Count"
                          value={contestTemplate.multiple_lineup}
                          options={multipleLineupList}
                          onChange={(e) => this.handleSelect(e, 'multiple_lineup')}
                        />
                      </div>
                    </Col>
                    {
                      contestTemplate.entry_fee_type !== '2' &&
                      <Col sm={4}>
                        <div className="form-group gray-form-group">
                          <label htmlFor="max_bonus_allowed" className="fixturevs">Bonus Allowed %
                          <span className="btn-information"><img id="TooltipExample1" className="infobtn" src={Images.INFO} />
                              <Tooltip placement="right" isOpen={this.state.isShowBonusToolTip} target="TooltipExample1" toggle={this.toggle1}>
                                {CONTEST_BONUS}
                              </Tooltip>
                            </span>
                          </label>
                          <input type="text" max={100} maxLength={3} disabled={contestTemplate.entry_fee_type == "0" || contestTemplate.entry_fee == "0"} className="contestname required" id="max_bonus_allowed" name="max_bonus_allowed" value={contestTemplate.max_bonus_allowed} onChange={(e) => this.handleFieldVal(e, 'max_bonus_allowed', 'max_bonus_allowed')} placeholder="Bonus Allowed %"></input>
                        </div>
                      </Col>
                    }
                  </Row>
                  <Row className="mt-4">
                    {
                      contestTemplate.group_id != HF.get_h2h_group_id() &&
                      <Col sm={4}>
                        <div className="form-group">
                          <label htmlFor="is_auto_recurring" className="fixturevs">Recurrence
                        <span className="btn-information"><img id="TooltipExample" className="infobtn" src={Images.INFO} />
                              <Tooltip placement="right" isOpen={this.state.isShowRecurrenceToolTip} target="TooltipExample" toggle={this.toggle}>
                                {CONTEST_RECUR_TT}
                              </Tooltip>
                            </span>
                          </label>
                          <div className="autorecurrentdiv">
                            <Input
                              checked={contestTemplate.is_auto_recurring}
                              type="checkbox"
                              id="is_auto_recurring"
                              name="is_auto_recurring"
                              value={contestTemplate.is_auto_recurring}
                              onChange={(e) => this.handleCheckboxFieldVal(e, 'is_auto_recurring')}
                              className="custom-control-input"></Input>
                            <label className="custom-control-label" htmlFor="is_auto_recurring">Auto - Recurrent</label>
                          </div>
                        </div>
                      </Col>
                    }
                
                   
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
                          <Tooltip placement="right" isOpen={this.state.isShowPDToolTip} target="isShowPDToolTip" toggle={this.toggle2}>{CONTEST_PRZ_DIS_TT}</Tooltip>
                        </span>
                      </label>
                      <div className="input-box radio-input-box">
                        <ul className="coupons-option-list">
                          <li className="coupons-option-item">
                            <div className="custom-radio">
                              <input disabled={contestTemplate.prize_pool_type == '2'} type="radio" className="custom-control-input" id="is_percentage" name="prize_value_type" value="1" checked={contestTemplate.prize_value_type === '1'} onChange={(e) => this.handlePrizeInPercentage(e, 'prize_value_type')} />
                              <label className="custom-control-label" htmlFor="is_percentage">
                                <span className="input-text">In Percentage</span>
                              </label>
                            </div>
                          </li>
                          <li className="coupons-option-item">
                            <div className="custom-radio">
                              <input type="radio" className="custom-control-input" id="is_fixed_value" name="prize_value_type" value="0" checked={contestTemplate.prize_value_type === '0'} onChange={(e) => this.handlePrizeInPercentage(e, 'prize_value_type')} />
                              <label className="custom-control-label" htmlFor="is_fixed_value">
                                <span className="input-text">In Fixed Value</span>
                              </label>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </Col>
                    <Col sm={4}>
                      <label className="fixturevs">Tie Breaker
                        <span className="btn-information"><img id="isShowTieToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowTieToolTip} target="isShowTieToolTip" toggle={this.toggle3}>{CONTEST_TIE_BRE_TT}</Tooltip>
                        </span>
                      </label>
                      <div className="autorecurrentdiv ">
                        <Row>
                          <Col sm={12}>
                            <Input
                              checked={contestTemplate.is_tie_breaker}
                              type="checkbox"
                              id="is_tie_breaker"
                              name="is_tie_breaker"
                              value={contestTemplate.is_tie_breaker}
                              // onChange={(e) => this.handlePrizeTieBreaker(e, 'is_tie_breaker')}
                              className="custom-control-input"></Input>
                            <label className="custom-control-label" htmlFor="is_tie_breaker">Yes</label></Col>
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
                                <div>{contestTemplate.minimum_size} Users</div>
                            </th>
                            <th className="">
                              Total Distribution
                                <div>{contestTemplate.size} Users</div>
                            </th>
                            <th>&nbsp;</th>
                          </tr>
                        </thead>
                        <tbody>
                          {payout_data.map((item, idx) => (
                            <tr id="addr0" key={idx}>
                              <td className="rank-input-filed">
                                <Input disabled="1" className="gray-form-control required" type="text" name="min" value={payout_data[idx].min} onChange={(e) => this.validatePrizeData(e, idx, 0)} placeholder="01" />
                                <span className="span">-</span>
                                <Input className="gray-form-control required" type="text" name="max" value={payout_data[idx].max} onChange={(e) => { this.onChangeMax(idx); this.validatePrizeData(e, idx, 0) }} placeholder="02" />
                              </td>
                              <td>
                                <Select
                                  isSearchable={false}
                                  isClearable={false}
                                  className="position gray-select-field"
                                  name="payout_prize_type"
                                  placeholder="Select Type"
                                  value={payout_data[idx].prize_type}
                                  options={this.state.prizeType}
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
                                      this.handleSelectPayout(e, idx, 'amount');
                                      // this.validatePrizeData(e, idx, 1);
                                      // this.onChangeAmount(idx) 
                                    }}
                                  />
                                }
                                {payout_data[idx].prize_type != 3 &&
                                  <Input
                                    className="gray-form-control required"
                                    type="text"
                                    name="amount"
                                    value={payout_data[idx].amount}
                                    onChange={(e) => {
                                      this.validatePrizeData(e, idx, 0);
                                      this.onChangeAmount(idx)
                                    }}
                                    placeholder="0"
                                  />
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
                                Add Prizes  <img src={Images.ADDBTN} />
                                <div className="add-prize-subtext">Site Rake is
                                <span style={{ width: "auto", display: 'inlineBlock', marginLeft: 3, marginRight: 3 }} className={contestTemplate.site_rake <= 0 ? "text-danger" : ""}>

                                    {
                                      contestTemplate.prize_pool <= 0 &&
                                      prize_profit.min_gross_profit + " Rs"
                                    }


                                    {
                                      (!contestTemplate.prize_pool || contestTemplate.prize_pool > 0) &&
                                      // (contestTemplate.prize_pool > 0) &&
                                      contestTemplate.site_rake + "% "
                                    }

                                  </span>
                                  based on the real cash distribution above.
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

          {contestTemplate.is_tie_breaker && <Row className="mb-1 sponser-section">
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
                      <label htmlFor="set_sponsor1" className="fixturevs">Sponsored
                        <span className="btn-information"><img id="isShowSPToolTip" className="infobtn" src={Images.INFO} />
                          <Tooltip placement="right" isOpen={this.state.isShowSPToolTip} target="isShowSPToolTip" toggle={this.toggle9}>
                            {CONTEST_SPONSOR_TT}
                          </Tooltip>
                        </span>
                      </label>
                      <div className="autorecurrentdiv custom-checkbox-new mt-0">
                        <input
                          checked={contestTemplate.set_sponsor == '1' ? true : false}
                          className="styled-checkbox"
                          id="set_sponsor"
                          type="checkbox"
                          name="set_sponsor"
                          value={contestTemplate.set_sponsor}
                          onChange={(e) => this.handleSponsore(e, 'set_sponsor')} />
                        <label htmlFor="set_sponsor">Yes</label>
                      </div>

                    </Col>
                    {contestTemplate.set_sponsor == 1 &&
                      <Fragment>
                        <Col sm={8} md={8}>
                          <Row>
                            <Col sm={6} md={6}>
                              <div className="form-group multiselect-wrapper spr-link-wdt">
                                <label htmlFor="league_id" className="fixturevs ">Sponsored Link</label>
                                <input disabled={contestTemplate.set_sponsor == 0} className="contestname gray-form-control" id="sponsor_link" name="sponsor_link" maxLength={255}
                                  value={contestTemplate.sponsor_link}
                                  onChange={(e) => this.handleFieldVal(e, 'sponsor_link', 'sponsor_link')} placeholder="sponsored link"></input>

                              </div>
                            </Col>
                          </Row>
                          <Row className="mt-30">
                            <Col md={6}>
                              <div className="xsponsored-img-box">
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
                                        name='merchandise_cd_image'
                                        id="merchandise_cd_image"
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
                            <Col md={6}>
                              <div className="xsponsored-img-box">
                                <figure className="upload-spr-img con-card-spr-img">
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
            <Col lg={12} className="btn-temp">
              <Button disabled={this.state.posting} onClick={() => { this.CreateContestTemplate() }} className=' btn-secondary-outline'>Submit</Button>
            </Col>
          </Row>

        </form>
      </div>
    );
  }
}

export default PFCreateFormContest;
