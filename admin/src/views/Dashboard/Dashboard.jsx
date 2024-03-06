import React, { Component, Fragment } from 'react';
import NumberFormat from 'react-number-format';
import { Col, Row, ButtonGroup, Table, Tooltip, Nav, NavItem, NavLink, TabContent, TabPane } from 'reactstrap';

import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import moment from 'moment';
import Images from '../../components/images';
import HighGraph from '../../components/HighGraph';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import HF from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
import Depositor_Ldr from '../Leaderboard/Depositor_Leaderboard';
import Referral_Ldr from '../Leaderboard/Referral_Leaderboard';
import Winning_Ldr from '../Leaderboard/Winning_Leaderboard';
import TimeSpent_Ldr from '../Leaderboard/TimeSpent_Leaderboard';
import { DASH_ACTIVE_USER, DASH_PASSIVE_USER, DASH_REGIS_USER, DASH_DEPOSIT, DASH_APP_USAGE } from "../../helper/Message";
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import { notify } from 'react-notify-toast'
import { getAppUsageData } from "../../helper/WSCalling";
import Loader from '../../components/Loader';

import TopTeam_Leaderboard from '../Leaderboard/TopTeam_Leaderboard';
import Withdrawal_Leaderboard from '../Leaderboard/Withdrawal_Leaderboard';

class Dashboard extends Component {

  constructor(props) {
    super(props);
    this.state = {
      startDate: HF.getFirstDateOfMonth(),
      endDate: new Date(),
      filtertypeDeposit: 'daily',
      filtertypeSiterake: 'daily',
      filtertypeSiterake_lf: 'daily',
      filtertypeFreepaid: 'daily',
      filtertypeFreepaid_lf : 'daily',
      filtertypeReferral: 'daily',
      filtertype: 'daily',
      tooltipOpen: false,
      ShowNewVisitor: false,
      ShowSignUp: false,
      ShowTotalUserDisplay: false,
      ShowFirstTimeDisplay: false,
      ShowUserSegregation: false,
      ShowUserLeaderBoard: false,
      ShowTotalDepositGraph: false,
      ShowTotalSiteRake: false,
      ShowTotalFreePaidUsers: false,
      ShowTotalReferrals: false,
      Visitors: 0,
      ApplyPosting: false,
      leaderboardTT: false,
      activeLdrBrd: HF.allowCoinOnly() != 1 ? '1' : '2',
      dbCallback: false,
      redirectPath: 'depositors',
      ShowPassiveTT: false,
      ActiveUser: 0,
      PassiveUser: 0,
      AppUsageData: [],
      SegPosting: true,
      ldrPosting: false,
    };
    this.handleChange = this.handleChange.bind(this);
    this.handleChangeEnd = this.handleChangeEnd.bind(this);

  }
  getFormatedDate = (date) => {
    return moment(date).format('LLLL');
  }
  handleChange(date) {
    this.setState({
      startDate: date
    });
  }
  handleChangeEnd(date) {
    this.setState({
      endDate: date
    });
  }

  getCalculatedsummary = () => {
    let { startDate, endDate, filtertype } = this.state
    let param = {
      filtertype: this.state.filtertype,
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_CALCULATED_SUMMARY, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        this.setState({
          posting: false,
          TotalUsers: responseJson.signup,
          TotalBalance: responseJson.wallet_balance,
          FirstTimetotalDeposit: responseJson.deposit,
          FirstTimetotalUser: responseJson.deposit_users,
          TotalBalanceUserCount: responseJson.wallet_balance_users,
          PercentSignup: responseJson.percent_signup,
          PercentFirsttimeDeposit: responseJson.percent_firsttime,
          ActiveUser: responseJson.active_users,
          PassiveUser: responseJson.passive_users,
          PercentActive: responseJson.percent_active,
          PercentPassive: responseJson.percent_passive,
          ldrPosting: false
        })
        if (this.state.PercentFirsttimeDeposit > 0) {
          this.setState({
            HighGraphProgressConfigOption: {
              title: {
                verticalAlign: 'middle',
                floating: true,
                text: this.state.PercentFirsttimeDeposit + ' %',
                style: {
                  fontSize: '10px',
                  fontWeight: '600'
                }
              },
              tooltip: false,
              chart: {
                type: 'pie',
                height: '200px',
              },
              plotOptions: {
                pie: {
                  dataLabels: false,
                  innerSize: '80%'
                }
              },

              series: [{
                data: [{

                  name: 'Deposit',
                  y: this.state.PercentFirsttimeDeposit,
                  color: '#2B2F47'
                },
                {

                  name: 'Deposit',
                  y: (100 - this.state.PercentFirsttimeDeposit),
                  color: '#C2C2C2'
                },
                ],

              }
              ],
              LineData: [],
              GraphHeaderTitle: [],
              credits: {
                enabled: false,
              },
              filtertype: this.state.filtertype

            },

          })
        } else {
          this.setState({
            HighGraphProgressConfigOption: {
              containerProps: { className: 'chartContainer' },
              title: {
                verticalAlign: 'middle',
                floating: true,
                text: '0 %',
                style: {
                  fontSize: '10px',
                }
              },
              tooltip: false,
              chart: {
                type: 'pie',
                height: '120px',
              },
              plotOptions: {
                pie: {
                  dataLabels: false,
                  innerSize: '70%'
                }
              },
              series: [{
                data: [{
                  name: '',
                  y: 0,
                  color: '#2B2F47'
                },
                {
                  name: '',
                  y: 100,
                  color: '#C2C2C2'
                },
                ],

              }
              ],
              LineData: [],
              GraphHeaderTitle: [],
              credits: {
                enabled: false,
              },
              filtertype: this.state.filtertype

            },

          })
        }
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })


  }
  getTimelines = () => {
    let { startDate, endDate, filtertypeDeposit } = this.state
    let param = {
      filtertype: this.state.filtertypeDeposit,
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_DASHBOARD_TIMELINES, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let graphdata = responseJson.data.userdepositedata;
        let usergraphdata = responseJson.data.userdata;
        let monthyear = responseJson.data.monthyear;

        let grandTotalDepositAmt = responseJson.data.grandTotalDepositAmt;
        let grandTotalUser = responseJson.data.grandTotalUser;

        let graphintdata = graphdata.map(Number);
        let usergraphintdata = usergraphdata.map(Number);
        this.setState({
          posting: false,
        })
        this.setState({
          HighGraphConfigOption: {
            title: {
              text: ''
            },
            xAxis: {
              categories: monthyear
            },
            legend: {
              align: 'center',
              verticalAlign: 'top',
              layout: 'vertical',
              floating: false,
              y: -15
            },
            yAxis: [{ // Primary yAxis
              labels: {
                format: HF.getCurrencyCode() + ' {value}'

              },
              title: {
                text: 'Deposit'

              }
            }, { // Secondary yAxis
              title: {
                text: 'Users'

              },
              labels: {
                format: '{value}'

              },
              opposite: true
            }],
            series: [{
              data: graphintdata,
              name: 'Deposit',
              color: '#2B2F47'
            },
            {
              data: usergraphintdata,
              name: 'User', yAxis: 1,
              color: '#F77084'
            }],
            LineData: [{ title: 'Total Deposit', value: HF.getCurrencyCode() + ' ' + grandTotalDepositAmt }, { title: 'Total Users', value: grandTotalUser }],
            GraphHeaderTitle: [{ title: 'Users' }, { title: 'Total Deposit' }],
            credits: {
              enabled: false,
            },
            filtertype: this.state.filtertype

          },

        })



      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })


  }
  getSegregation = () => {
    let { startDate, endDate, filtertype } = this.state
    let param = {
      filtertype: filtertype,
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({ SegPosting: true })
    WSManager.Rest(NC.baseURL + NC.GET_DASHBOARD_SEGREGATION, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        responseJson = responseJson.data;
        this.setState({
          HighGraphUserSegregation: {
            chart: {
              type: 'bar',
              height: '550px'
            },
            tooltip: false,
            plotOptions: {
              bar: {
                dataLabels: {
                  enabled: true,
                  align: 'auto',
                  x: 0,
                  y: -10
                },
                borderRadius: 0,
                minPointLength: 10,
                pointHeight: 12,
                pointWidth: 16

              }
            },
            title: {
              text: 'Event Tracking',
              align: 'left',
              color: '#000',

            },
            legend: false,
            credits: {
              enabled: false
            },
            xAxis: {
              categories: ['Signup', 'Login', 'Contest Join', 'Deposit', 'APK Download', 'Shared Application Link', 'Installed App', 'Uninstalled App'],
              min: 0,
              tickWidth: 0,
              crosshair: false,
              lineWidth: 0,
              gridLineWidth: 0,
              title: '',
            },
            yAxis: {
              visible: false,
            },

            series: [{
              name: '',
              data: [parseInt(responseJson.sign_up), parseInt(responseJson.login), parseInt(responseJson.join_contest), parseInt(responseJson.Paymentgateway), parseInt(responseJson.download_apk), parseInt(responseJson.send_download_link), parseInt(responseJson.install_count), parseInt(responseJson.uninstall_count)],
              color: '#F77084',
            }],
            LineData: [{ title: '', value: '' }],
            GraphHeaderTitle: [{ title: 'test' }, { title: 'test' }],
            credits: {
              enabled: false,
            }

          },
          SegPosting: false
        })
        this.setState({
          HighGraphPieConfigOption: {
            title: {
              text: ''
            },

            chart: {
              type: 'pie',
              height: '200px',
            },
            plotOptions: {
              pie: {
                dataLabels: false,
                innerSize: '80%',
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                  enabled: true,
                  format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                }
              }
            },
            series: [{
              data: responseJson.appUsage, color: '#F77084'
            }],
            LineData: [],
            GraphHeaderTitle: [],
            credits: {
              enabled: false,
            }
          }
        })
        this.setState({
          HighGraphBrowserUsageConfigOption: {
            title: {
              text: ''
            },

            chart: {
              type: 'pie',
              height: '200px',
            },
            plotOptions: {
              pie: {
                dataLabels: false,
                innerSize: '80%',
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                  enabled: true,
                  format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                }
              }
            },
            legends: true,
            series: [{
              data: responseJson.browserUsage, color: '#F77084'
            }],
            LineData: [],
            GraphHeaderTitle: [],
            credits: {
              enabled: false,
            }
          }
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })
  }

  getActiveUsers = () => {
    let { startDate, endDate } = this.state
    let param = {
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({
      posting: true,
      dailyActive: { Visitors: 0, loggedInusers: 0 },
      monthlyActive: { Visitors: 0, loggedInusers: 0 },
    })
    WSManager.Rest(NC.baseURL + NC.GET_DASHBOARD_ACTIVE_USERS, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        //uncomment when data come direclty from google 
        this.setState({
          dailyActive: responseJson.daily,
          monthlyActive: responseJson.monthly,
          ApplyPosting: false
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })
  }

  getFreePaidUsers = () => {
    let { startDate, endDate, filtertypeFreepaid } = this.state
    let param = {
      filtertype: filtertypeFreepaid,
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_DASHBOARD_FREEPAID_USERS, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let usergraphdata = responseJson.data.paid_users;
        let monthyear = responseJson.data.monthyear;
        let usergraphintdata = usergraphdata.map(Number);
        //free users data
        let freeusergraphdata = responseJson.data.free_users;
        let freeusergraphintdata = freeusergraphdata.map(Number);

        this.setState({
          posting: false,
        })
        this.setState({
          HighGraphFreePaidUserConfigOption: {
            title: {
              text: ''
            },
            legend: {
              align: 'center',
              verticalAlign: 'top',
              layout: 'vertical',
              floating: false,
              y: -15
            },
            xAxis: {
              categories: monthyear
            },
            yAxis: [{ // Primary yAxis
              labels: {
                format: '{value}'

              },
              title: {
                text: 'Users'

              },

            }, { // Secondary yAxis
              title: {
                text: ''

              },
              labels: {
                format: '{value}'

              },
              opposite: true
            }],
            series: [{
              data: usergraphintdata,
              name: 'Paid',
              color: '#F77084'
            },
            {
              data: freeusergraphintdata,
              name: 'Free', yAxis: 0,
              color: '#2B2F47'
            }],
            LineData: [{ title: 'Total Free Users VS Total Paid Users', value: '' }],
            GraphHeaderTitle: [{ title: 'Free User' }, { title: 'Paid User' }],
            credits: {
              enabled: false,
            }
          }
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })
  }
  getFreePaidUsers_lf = () => {
    let { startDate, endDate, filtertypeFreepaid_lf } = this.state
    let param = {
      filtertype: filtertypeFreepaid_lf,
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.LF_GET_DASHBOARD_FREEPAID_USERS, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let usergraphdata = responseJson.data.paid_users;
        let monthyear = responseJson.data.monthyear;
        let usergraphintdata = usergraphdata.map(Number);
        //free users data
        let freeusergraphdata = responseJson.data.free_users;
        let freeusergraphintdata = freeusergraphdata.map(Number);

        this.setState({
          posting: false,
        })
        this.setState({
          HighGraphFreePaidUserConfigOption_lf: {
            title: {
              text: ''
            },
            legend: {
              align: 'center',
              verticalAlign: 'top',
              layout: 'vertical',
              floating: false,
              y: -15
            },
            xAxis: {
              categories: monthyear
            },
            yAxis: [{ // Primary yAxis
              labels: {
                format: '{value}'

              },
              title: {
                text: 'Live Fantasy Users',
                fontSize : '12px',

              },

            }, { // Secondary yAxis
              title: {
                text: ''

              },
              labels: {
                format: '{value}'

              },
              opposite: true
            }],
            series: [{
              data: usergraphintdata,
              name: 'Paid',
              color: '#F77084'
            },
            {
              data: freeusergraphintdata,
              name: 'Free', yAxis: 0,
              color: '#2B2F47'
            }],
            LineData: [{ title: 'Total Free Users VS Total Paid Users', value: '' }],
            GraphHeaderTitle: [{ title: 'Free User' }, { title: 'Paid User' }],
            credits: {
              enabled: false,
            }
          }
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })
  }
  getDevices = () => {
    let { startDate, endDate, filtertype } = this.state
    let param = {
      filtertype: filtertype,
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_DASHBOARD_DEVICES, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({
          posting: false,
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })
  }
  getSiterake = () => {
    let { startDate, endDate, filtertypeSiterake } = this.state
    let param = {
      filtertype: filtertypeSiterake,
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_DASHBOARD_SITERAKE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let totalsiterakeamount = responseJson.data.totalsiterakeamount;
        let usergraphdata = responseJson.data.userdata;
        let monthyear = responseJson.data.monthyear;
        let totalsiterakeamountint = totalsiterakeamount.map(Number);
        let usergraphintdata = usergraphdata.map(Number);

        let grandTotalSiterake = responseJson.data.grandTotalSiterake;
        let grandTotalUser = responseJson.data.grandTotalUser;

        this.setState({
          posting: false,
        })
        this.setState({
          HighGraphSiteRakeConfigOption: {
            title: {
              text: ''
            },
            legend: {
              align: 'center',
              verticalAlign: 'top',
              layout: 'vertical',
              floating: false,
              y: -15
            },
            pointInterval: 4,
            xAxis: {
              categories: monthyear
            },
            yAxis: [
              { // Primary yAxis
                labels: {
                  format: HF.getCurrencyCode() + ' {value}'

                },
                title: {
                  text: 'Siterake'

                }
              },
              { // Secondary yAxis
                title: {
                  text: 'Users'

                },
                labels: {
                  format: '{value}'

                },
                opposite: true
              }
            ],
            series: [{
              data: usergraphintdata,
              yAxis: 1,
              name: 'User',
              color: '#2B2F47'

            },
            {
              data: totalsiterakeamountint,
              name: 'Deposit',
              color: '#F77084'
            }],
            LineData: [{ title: 'Total Site Rake', value: HF.getCurrencyCode() + ' ' + grandTotalSiterake }, { title: 'Total Users', value: grandTotalUser }],
            GraphHeaderTitle: [{ title: 'Contests' }, { title: 'Site Rake Earned' }],
            credits: {
              enabled: false,
            }
          },
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })
  }

  getSiterake_lf = () => {
    let { startDate, endDate, filtertypeSiterake_lf } = this.state
    let param = {
      filtertype: filtertypeSiterake_lf,
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.LF_GET_DASHBOARD_SITERAKE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let totalsiterakeamount = responseJson.data.totalsiterakeamount;
        let usergraphdata = responseJson.data.userdata;
        let monthyear = responseJson.data.monthyear;
        let totalsiterakeamountint = totalsiterakeamount.map(Number);
        let usergraphintdata = usergraphdata.map(Number);
        let grandTotalSiterake = responseJson.data.grandTotalSiterake;
        let grandTotalUser = responseJson.data.grandTotalUser;
        this.setState({
          posting: false,
        })
        this.setState({
          HighGraphSiteRakeConfigOption_lf: {
            title: {
              text: ''
            },
            legend: {
              align: 'center',
              verticalAlign: 'top',
              layout: 'vertical',
              floating: false,
              y: -15
            },
            pointInterval: 4,
            xAxis: {
              categories: monthyear
            },
            yAxis: [
              { // Primary yAxis
                labels: {
                  format: HF.getCurrencyCode() + ' {value}'

                },
                title: {
                  text: 'Live Fantasy Siterake'

                }
              },
              { // Secondary yAxis
                title: {
                  text: 'Users'

                },
                labels: {
                  format: '{value}'

                },
                opposite: true
              }
            ],
            series: [{
              data: usergraphintdata,
              yAxis: 1,
              name: 'User',
              color: '#2B2F47'

            },
            {
              data: totalsiterakeamountint,
              name: 'Deposit',
              color: '#F77084'
            }],
            LineData: [{ title: 'Total Site Rake', value: HF.getCurrencyCode() + ' ' + grandTotalSiterake }, { title: 'Total Users', value: grandTotalUser }],
            GraphHeaderTitle: [{ title: 'Contests' }, { title: 'Site Rake Earned' }],
            credits: {
              enabled: false,
            }
          },
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })
  }


  getReferral = () => {
    let { startDate, endDate, filtertypeReferral } = this.state
    let param = {
      filtertype: filtertypeReferral,
      startDate: HF.dateInUtc(startDate),
      endDate: HF.dateInUtc(endDate),
    }
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_DASHBOARD_REFERRAL, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let totalRefAmt = responseJson.data.totalRefAmt;
        let totalUserCount = responseJson.data.totalUserCount;

        let grandTotalAmount = responseJson.data.grandTotalAmount;
        let grandTotalUser = responseJson.data.grandTotalUser;

        let monthyear = responseJson.data.monthyear;
        let totalUserCountInt = totalUserCount.map(Number);
        let totalRefAmtInt = totalRefAmt.map(Number);
        this.setState({
          posting: false,
        })
        this.setState({
          HighGraphReferralConfigOption: {
            title: {
              text: ''
            },
            legend: {
              align: 'center',
              verticalAlign: 'top',
              layout: 'vertical',
              floating: false,
              y: -15
            },
            global: {
              useUTC: false
            },
            xAxis: {
              categories: monthyear,
            },
            yAxis: [{ // Primary yAxis
              labels: {
                format: HF.getCurrencyCode() + ' {value}'
              },
              title: {
                text: 'Referral'
              }
            },
            { // Secondary yAxis
              title: {
                text: 'Users'
              },
              labels: {
                format: '{value}'
              },
              opposite: true
            }],
            series: [{
              data: totalUserCountInt,
              name: 'User',
              color: '#2B2F47',
              yAxis: 1,
            },
            {
              data: totalRefAmtInt,
              name: 'Amount',
              color: '#F77084'
            }],
            LineData: [{ title: 'Total Referral Amount Distributed', value: HF.getCurrencyCode() + ' ' + grandTotalAmount }, { title: 'Total No of Referrals', value: grandTotalUser }],
            GraphHeaderTitle: [{ title: 'Amount Distributed' }, { title: 'Referrals' }],
            credits: {
              enabled: false,
            }
          },
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })
  }
  onDepositRadioBtnClick(rSelected) {
    this.setState({ filtertypeDeposit: rSelected }, () => { this.getTimelines(); });
  }
  onSiteRakeRadioBtnClick(rSelected) {
    this.setState({ filtertypeSiterake: rSelected }, () => { this.getSiterake(); });
  }
  onSiteRakeRadioBtnClick_lf(rSelected) {
    this.setState({ filtertypeSiterake_lf: rSelected }, () => { this.getSiterake_lf(); });
  }
  onFreePaidBtnClick(rSelected) {
    this.setState({ filtertypeFreepaid: rSelected }, () => { this.getFreePaidUsers(); });
  }
  onFreePaidBtnClick_lf(rSelected) {
    this.setState({ filtertypeFreepaid_lf: rSelected }, () => { this.getFreePaidUsers_lf(); });
  }
  onReferralRadioBtnClick(rSelected) {
    this.setState({ filtertypeReferral: rSelected }, () => { this.getReferral(); });
  }

  NewVisitorToggle = () => {
    this.setState({
      ShowNewVisitor: !this.state.ShowNewVisitor
    });
  }

  SignUpToggle = () => {
    this.setState({
      ShowSignUp: !this.state.ShowSignUp
    });
  }

  TotalUserDisplayToggle = () => {
    this.setState({
      ShowTotalUserDisplay: !this.state.ShowTotalUserDisplay
    });
  }

  FirstTimeDisplayToggle = () => {
    this.setState({
      ShowFirstTimeDisplay: !this.state.ShowFirstTimeDisplay
    });
  }

  UserSegregationToggle = () => {
    this.setState({
      ShowUserSegregation: !this.state.ShowUserSegregation
    });
  }

  AppUsageToggle = () => {
    this.setState({
      ShowAppUsage: !this.state.ShowAppUsage
    });
  }

  UserLeaderBoardToggle = () => {
    this.setState({
      ShowUserLeaderBoard: !this.state.ShowUserLeaderBoard
    });
  }

  TotalDepositGraphToggle = () => {
    this.setState({
      ShowTotalDepositGraph: !this.state.ShowTotalDepositGraph
    });
  }

  TotalSiteRakeToggle = () => {
    this.setState({
      ShowTotalSiteRake: !this.state.ShowTotalSiteRake
    });
  }

  TotalFreePaidUsersToggle = () => {
    this.setState({
      ShowTotalFreePaidUsers: !this.state.ShowTotalFreePaidUsers
    });
  }

  TotalReferralsToggle = () => {
    this.setState({
      ShowTotalReferrals: !this.state.ShowTotalReferrals
    });
  }

  applyFilter = () => {
    this.setState({ ApplyPosting: true, ldrPosting : true })
    this.getCalculatedsummary(); this.getTimelines(); this.getFreePaidUsers();this.getFreePaidUsers_lf(); this.getDevices(); this.getSiterake_lf(); this.getReferral(); this.getSegregation(); this.getActiveUsers(); this.appUsageGraph();
  }

  leaderboardToggle = () => {
    this.setState({
      leaderboardTT: !this.state.leaderboardTT
    });
  }

  toggleLdrBoard(tab) {
    this.setState({ ldrPosting : true })
    if (this.state.activeLdrBrd !== tab) {
      let pth = ''
      if (tab === '1') {
        pth = 'depositors'
      } else if (tab === '2') {
        pth = 'referral'
      } else if (tab === '4') {
        pth = 'winnings'
      } else if (tab === '5') {
        pth = 'timespent'
      } else if (tab === '6') {
        pth = 'topteams'
      } else if (tab === '7') {
        pth = 'withdrawal'
      }
      this.setState({
        activeLdrBrd: tab,
        redirectPath: pth,
        ldrPosting: false
      });
    }
  }

  passiveUToggle = () => {
    this.setState({
      ShowPassiveTT: !this.state.ShowPassiveTT
    });
  }

  getSummaryHtml = () => {
    let { activeLdrBrd, ldrPosting, dbCallback, redirectPath, Visitors, TotalUsers, ShowPassiveTT, ShowNewVisitor, ActiveUser, PassiveUser, PercentSignup, PercentActive, PercentPassive, appUsageGraph, AppUsageData, ANDROID, ANDROID_PER, IOS, IOS_PER, WEB, WEB_PER, SegPosting, startDate, endDate } = this.state

    const lbrdProps = {
      dbCallback : dbCallback,
      from_date: startDate,
      to_date: endDate,
    }

    return (
      <Fragment>
        <Row className="dashboard-row dashboard-main">
          <Col lg={12} className="row date-filter">
            <div className="datepicker-wrapper">
              <div className="float-left">
                <span className="mr-2">From</span>
                <DatePicker
                  maxDate={new Date(this.state.endDate)}
                  className="date-input"
                  showYearDropdown='true'
                  selected={this.state.startDate}
                  onChange={this.handleChange}
                  dateFormat='dd/MM/yyyy'
                />
              </div>
              <div className="float-left">
                <span className="to-seprate">To</span>
                <DatePicker
                  minDate={new Date(this.state.startDate)}
                  maxDate={new Date()}
                  className="date-input"
                  showYearDropdown='true'
                  selected={this.state.endDate}
                  onChange={this.handleChangeEnd}
                  popperPlacement="bottom-end"
                  dateFormat='dd/MM/yyyy'
                />
              </div>
              <div className="float-left">
                <button
                  disabled={this.state.ApplyPosting}
                  type="button"
                  className="ml-3 xautobtn btn btn-secondary xdashboard-apply-btn" onClick={this.applyFilter}
                >Apply
            </button>
              </div>
            </div>
          </Col>
          <Col lg={12} className="row top-rank-box">
            <Col md={3} className="card1 pl-0 animate-left">
              <div className="cardbox ">
                <div className="visitor-col">
                  <p className="text">Active Users</p>
                  <p className="value">
                    {/* {ActiveUser ? ActiveUser : '--'} */}
                    <NumberFormat
                      value={ActiveUser}
                      displayType={'text'}
                      thousandSeparator={true}
                      prefix={''}
                    />
                  </p>
                </div>
                <div className="compair-box">
                  <div className="info-icon-wrapper">
                    <i className="icon-info" id="NewVisitorTooltip">
                      <Tooltip
                        placement="top"
                        isOpen={ShowNewVisitor}
                        target="NewVisitorTooltip"
                        toggle={this.NewVisitorToggle}>
                        <p>{DASH_ACTIVE_USER}</p>
                      </Tooltip>
                    </i>
                  </div>
                  <div className="compair-wrapper">
                    <span>
                      <p style={{ color: PercentActive < 0 ? "red" : "green" }}>
                        {PercentActive} %
                              {(PercentActive > 0) &&
                          <i className="icon-Path"></i>
                        }
                        {(PercentActive < 0) &&
                          <i className="icon-down-arrow"></i>
                        }
                      </p>
                    </span>
                  </div>
                </div>

              </div>

            </Col>
            <Col md={3} className="card2 animate-left">
              <div className="cardbox ">
                <div className="visitor-col">
                  <p className="text">Registered Users</p>
                  <p className="value">
                    <NumberFormat
                      value={TotalUsers}
                      displayType={'text'}
                      thousandSeparator={true}
                      prefix={''}
                    />
                  </p>
                </div>
                <div className="compair-box">
                  <div className="info-icon-wrapper">
                    <i className="icon-info" id="SignUpTooltip">
                      <Tooltip placement="top" isOpen={this.state.ShowSignUp} target="SignUpTooltip" toggle={this.SignUpToggle}>
                        <p>{DASH_REGIS_USER}</p>
                      </Tooltip>

                    </i>
                  </div>
                  <div className="compair-wrapper">
                    <span>
                      <p style={{ color: PercentSignup < 0 ? "red" : "green" }}>
                        {PercentSignup} %
                              {(PercentSignup > 0) &&
                          <i className="icon-Path"></i>
                        }
                        {(PercentSignup < 0) &&
                          <i className="icon-down-arrow"></i>
                        }
                      </p>
                    </span>
                  </div>
                </div>
              </div>
            </Col>
            <Col md={3} className="card2 animate-left">
              <div className="cardbox ">
                <div className="visitor-col">
                  <p className="text">Passive Users</p>
                  <p className="value">
                    <NumberFormat
                      value={PassiveUser}
                      displayType={'text'}
                      thousandSeparator={true}
                      prefix={''} />
                  </p>
                </div>
                <div className="compair-box">
                  <div className="info-icon-wrapper">
                    <i className="icon-info" id="passiveUToggle">
                      <Tooltip
                        placement="top"
                        isOpen={ShowPassiveTT}
                        target="passiveUToggle"
                        toggle={this.passiveUToggle}
                      >
                        <p>{DASH_PASSIVE_USER}</p>
                      </Tooltip>
                    </i>
                  </div>
                  <div className="compair-wrapper">
                    <span>
                      <p style={{ color: PercentPassive < 0 ? "red" : "green" }}>
                        {PercentPassive} %
                              {(PercentPassive > 0) &&
                          <i className="icon-Path"></i>
                        }
                        {(PercentPassive < 0) &&
                          <i className="icon-down-arrow"></i>
                        }
                      </p>
                    </span>
                  </div>
                </div>
              </div>
            </Col>
            {HF.allowCoinOnly() != 1 && <Col md={3} className="card4 pr-0 animate-left">
              <div className="cardbox padd-10 lastbox">
                <div className="deposite-wrapper-left">
                  <ul>
                    <li>
                      <div className="depositers-container">
                        <p className="text">Depositors</p>
                        <p className="value"> <NumberFormat value={(this.state.FirstTimetotalDeposit > 0) ? this.state.FirstTimetotalUser : '0'} displayType={'text'} thousandSeparator={true} prefix={''} /></p>
                      </div>
                    </li>
                    <li>
                      <div className="depositers-container">
                        <p className="text">Amount Deposited</p>
                        <p className="value">
                          {HF.getCurrencyCode()}
                          <NumberFormat value={this.state.FirstTimetotalDeposit ? this.state.FirstTimetotalDeposit : '0'} displayType={'text'} thousandSeparator={true} prefix={''} /> </p>
                      </div>
                    </li>
                  </ul>
                </div>
                <div className="deposite-wrapper-right circle-graph">
                  <div className="info-icon-wrapper">
                    <i className="icon-info" id="FirstTimeDisplayTooltip">
                      <Tooltip placement="top" isOpen={this.state.ShowFirstTimeDisplay} target="FirstTimeDisplayTooltip" toggle={this.FirstTimeDisplayToggle}>
                        {DASH_DEPOSIT}
                      </Tooltip>
                    </i>
                  </div>
                  <div className={this.state.PercentFirsttimeDeposit > 0 ? 'compair-wrapper nonzero' : 'compair-wrapper zero'} >
                    {this.state.HighGraphProgressConfigOption &&
                      <HighGraph
                        {...this.props} HighGraphConfigOption={this.state.HighGraphProgressConfigOption}
                      > </HighGraph>
                    }
                  </div>
                </div>
              </div>
            </Col>}
          </Col>


          {HF.allowCoinOnly() != 1 && <Col className="row analytics-graph-container pb-0">
            <Col md={HF.allowDFS() == 1 ? 6 : 12} className="pl-0 animate-top">
            <div className="leaderbaordhead">Total Deposit</div>
              <div className="graph-container">
                <Row className="graph-align">
                  <Col sm={6}>
                    <div className="tabbtn custom-graph">
                      <ButtonGroup>
                        <span className={this.state.filtertypeDeposit === 'daily' ? 'active' : ''} onClick={() => this.onDepositRadioBtnClick('daily')}>Daily</span>

                        <span className={this.state.filtertypeDeposit === 'weekly' ? 'active' : ''} onClick={() => this.onDepositRadioBtnClick('weekly')}>Weekly</span>

                        <span className={this.state.filtertypeDeposit === 'monthly' ? 'active' : ''} onClick={() => this.onDepositRadioBtnClick('monthly')}>Monthly</span>

                      </ButtonGroup>
                    </div>
                  </Col>
                  <Col sm={6}>
                    <div className="info-icon-wrapper">
                      <i className="icon-info" id="TotalDepositGraphTooltip">
                        <Tooltip placement="top" isOpen={this.state.ShowTotalDepositGraph} target="TotalDepositGraphTooltip" toggle={this.TotalDepositGraphToggle}>
                          This shows the graphical representation of total amount deposited into the system. Below the graph is the real time measure of total amount deposited and No. of users into the system.
                        </Tooltip>
                      </i>
                    </div>
                  </Col>
                </Row>

                {this.state.HighGraphConfigOption &&
                  <HighGraph
                    {...this.props} HighGraphConfigOption={this.state.HighGraphConfigOption}
                  > </HighGraph>
                }
              </div>
            </Col>
            {
              HF.allowDFS() == 1 && 
            <Col md={6} className="pr-0 animate-top">
            <div className="leaderbaordhead">Daily Fantasy</div>
              <div className="graph-container">
                <Row className="graph-align">
                  <Col sm={6}>
                    <div className="tabbtn custom-graph">
                      <ButtonGroup>
                        <span onClick={() => this.onSiteRakeRadioBtnClick('daily')} className={this.state.filtertypeSiterake === 'daily' ? 'active' : ''} >Daily</span>
                        <span className="custombtn" onClick={() => this.onSiteRakeRadioBtnClick('weekly')} className={this.state.filtertypeSiterake === 'weekly' ? 'active' : ''}>Weekly</span>
                        <span className="gauranteedbtn" onClick={() => this.onSiteRakeRadioBtnClick('monthly')} className={this.state.filtertypeSiterake === 'monthly' ? 'active' : ''}>Monthly</span>
                      </ButtonGroup>
                    </div>
                  </Col>
                  <Col sm={6}>
                    <div className="info-icon-wrapper">
                      <i className="icon-info" id="TotalSiteRakeTooltip">
                        <Tooltip placement="top" isOpen={this.state.ShowTotalSiteRake} target="TotalSiteRakeTooltip" toggle={this.TotalSiteRakeToggle}>
                          This shows the graphical representation of total Site Rake earned by the admin. Below the graph, is the real time measure of total users and total Site Rake earned by the admin.
                        </Tooltip>
                      </i>
                    </div>
                  </Col>
                </Row>
                {this.state.HighGraphSiteRakeConfigOption &&
                  <HighGraph
                    {...this.props} HighGraphConfigOption={this.state.HighGraphSiteRakeConfigOption}
                  > </HighGraph>
                }
              </div>
            </Col>
            }
          </Col>}

        

          {HF.allowLiveFantsy() == 1 && <Col lg={12} className="row analytics-graph-container">
          <Col md={6} className="pl-0">
          <div className="leaderbaordhead">Live Fantasy
          </div>
              <div className="graph-container">
                <Row className="graph-align">
                  <Col sm={6}>
                    <div className="tabbtn custom-graph">
                      <ButtonGroup>
                        <span onClick={() => this.onFreePaidBtnClick_lf('daily')} className={this.state.filtertypeFreepaid_lf === 'daily' ? 'active' : ''} >Daily</span>

                        <span onClick={() => this.onFreePaidBtnClick_lf('weekly')} className={this.state.filtertypeFreepaid_lf === 'weekly' ? 'active' : ''}>Weekly </span>

                        <span onClick={() => this.onFreePaidBtnClick_lf('monthly')} className={this.state.filtertypeFreepaid_lf === 'monthly' ? 'active' : ''}>Monthly</span>
                      </ButtonGroup>
                    </div>
                  </Col>
                  <Col sm={6}>
                    <div className="info-icon-wrapper">
                      <i className="icon-info" id="TotalFreePaidUsers">
                        <Tooltip placement="top" isOpen={this.state.ShowTotalFreePaidUsers} target="TotalFreePaidUsers" toggle={this.TotalFreePaidUsersToggle}>
                          This shows the graphical representation of total free user vs total paid user.
                        </Tooltip>
                      </i>
                    </div>
                  </Col>
                </Row>

                {this.state.HighGraphFreePaidUserConfigOption_lf &&
                  <HighGraph
                    {...this.props} HighGraphConfigOption={this.state.HighGraphFreePaidUserConfigOption_lf}
                  > </HighGraph>
                }
              </div>
            </Col>
            <Col md={6} className="pr-0 animate-top">
            <div className="leaderbaordhead">Live Fantasy </div>
              <div className="graph-container">
                <Row className="graph-align">
                  <Col sm={6}>
                    <div className="tabbtn custom-graph">
                      <ButtonGroup>
                        <span onClick={() => this.onSiteRakeRadioBtnClick_lf('daily')} className={this.state.filtertypeSiterake_lf === 'daily' ? 'active' : ''} >Daily</span>
                        <span className="custombtn" onClick={() => this.onSiteRakeRadioBtnClick_lf('weekly')} className={this.state.filtertypeSiterake_lf === 'weekly' ? 'active' : ''}>Weekly</span>
                        <span className="gauranteedbtn" onClick={() => this.onSiteRakeRadioBtnClick_lf('monthly')} className={this.state.filtertypeSiterake_lf === 'monthly' ? 'active' : ''}>Monthly</span>
                      </ButtonGroup>
                    </div>
                  </Col>
                  <Col sm={6}>
                    <div className="info-icon-wrapper">
                      <i className="icon-info" id="TotalSiteRakeTooltip">
                        <Tooltip placement="top" isOpen={this.state.ShowTotalSiteRake} target="TotalSiteRakeTooltip" toggle={this.TotalSiteRakeToggle}>
                          This shows the graphical representation of total Site Rake earned by the admin. Below the graph, is the real time measure of total users and total Site Rake earned by the admin.
                        </Tooltip>
                      </i>
                    </div>
                  </Col>
                </Row>
                {this.state.HighGraphSiteRakeConfigOption_lf &&
                  <HighGraph
                    {...this.props} HighGraphConfigOption={this.state.HighGraphSiteRakeConfigOption_lf}
                  > </HighGraph>
                }
              </div>
            </Col>
          </Col>}

          <Col lg={12} className="row graphrow col mb-3">
            <Col md={6} className="pl-0 animate-top">
              <div className="leaderbaordhead">User Segregation <i className="icon-info" id="UserSegregationTooltip">
                <Tooltip placement="top" isOpen={this.state.ShowUserSegregation} target="UserSegregationTooltip" toggle={this.UserSegregationToggle}>
                  This shows the click wise segregation of total no. of users visiting the website.
              </Tooltip>
              </i>
              </div>
              <div className="graph-container user-segregation-container pt-4">
                {
                  (!SegPosting && this.state.HighGraphUserSegregation) ?
                    <HighGraph
                      {...this.props} HighGraphConfigOption={this.state.HighGraphUserSegregation}
                    > </HighGraph>
                    :
                    <Loader />
                }

              </div>
            </Col>
            <Col md={6} className="app-usage-col pr-0 animate-top">
              <div className="leaderbaordhead">App Usage <i className="icon-info" id="AppUsageTooltip">
                <Tooltip placement="top" isOpen={this.state.ShowAppUsage} target="AppUsageTooltip" toggle={this.AppUsageToggle}>{DASH_APP_USAGE}</Tooltip>
              </i>
              </div>
              <div className="graph-container app-usage-container">
                <div className="leaderbaordhead">By Device</div>
                <Row>
                  <Col lg={4}>
                    <div className="legend-container">
                      <div className="lgnd-color lgnd-clr-mbl"></div>
                      <div className="lgnd-info-contain">
                        <div className="lgnd-device">Mobile {AppUsageData.mobile_per ? AppUsageData.mobile_per : 0}%</div>
                        <div className="lgnd-device-name">iOS Browser - {AppUsageData.ios_browser ? AppUsageData.ios_browser : 0}%</div>
                        <div className="lgnd-device-name">iOS App - {AppUsageData.ios_app ? AppUsageData.ios_app : 0}%</div>
                        <div className="lgnd-device-name">Android Browser - {AppUsageData.android_mobile_web ? AppUsageData.android_mobile_web : 0}%</div>
                        <div className="lgnd-device-name">Android App - {AppUsageData.android_app ? AppUsageData.android_app : 0}%</div>
                      </div>
                    </div>
                    <div className="legend-container">
                      <div className="lgnd-color lgnd-clr-tblt"></div>
                      <div className="lgnd-info-contain">
                        <div className="lgnd-device">Tablet  {AppUsageData.tablet_per ? AppUsageData.tablet_per : 0}%</div>
                        <div className="lgnd-device-name">iPad Browser - {AppUsageData.ipad ? AppUsageData.ipad : 0}%</div>
                        <div className="lgnd-device-name">Android Browser - {AppUsageData.android_tab ? AppUsageData.android_tab : 0}%</div>
                      </div>
                    </div>
                    <div className="legend-container">
                      <div className="lgnd-color lgnd-clr-desk"></div>
                      <div className="lgnd-info-contain">
                        <div className="lgnd-device">Desktop {AppUsageData.desktop_per ? AppUsageData.desktop_per : 0}%</div>
                      </div>
                    </div>
                  </Col>
                  <Col lg={8}>
                    {
                      appUsageGraph &&
                      <HighchartsReact
                        highcharts={Highcharts}
                        options={appUsageGraph}
                      />
                    }
                  </Col>
                </Row>
                <div className="leaderbaordhead">App Usage</div>
                <Row>
                  <Col lg={4}>
                    <div className="au-container">
                      <div className="au-d-type">Android</div>
                      <div className="au-d-percent">{ANDROID_PER}%</div>
                      <div className="au-d-user">{ANDROID} User{ANDROID > 1 && "s"}</div>
                    </div>
                  </Col>
                  <Col lg={4}>
                    <div className="au-container">
                      <div className="au-d-type">iOS</div>
                      <div className="au-d-percent">{IOS_PER}%</div>
                      <div className="au-d-user">{IOS} User{IOS > 1 && "s"}</div>
                    </div>
                  </Col>
                  <Col lg={4}>
                    <div className="au-container">
                      <div className="au-d-type">Web</div>
                      <div className="au-d-percent">{WEB_PER}%</div>
                      <div className="au-d-user">{WEB} User{WEB > 1 && "s"}</div>
                    </div>
                  </Col>
                </Row>
              </div>
            </Col>

          </Col>
        
          {HF.allowCoinOnly() != 1 && <Col lg={12} className="row">
            {
              HF.allowDFS() == 1 &&
              <Col md={6} className="pl-0">
                <div className="leaderbaordhead">Daily Fantasy</div>
                <div className="graph-container">
                  <Row className="graph-align">
                    <Col sm={6}>
                      <div className="tabbtn custom-graph">
                        <ButtonGroup>
                          <span onClick={() => this.onFreePaidBtnClick('daily')} className={this.state.filtertypeFreepaid === 'daily' ? 'active' : ''} >Daily</span>

                          <span onClick={() => this.onFreePaidBtnClick('weekly')} className={this.state.filtertypeFreepaid === 'weekly' ? 'active' : ''}>Weekly </span>

                          <span onClick={() => this.onFreePaidBtnClick('monthly')} className={this.state.filtertypeFreepaid === 'monthly' ? 'active' : ''}>Monthly</span>
                        </ButtonGroup>
                      </div>
                    </Col>
                    <Col sm={6}>
                      <div className="info-icon-wrapper">
                        <i className="icon-info" id="TotalFreePaidUsers">
                          <Tooltip placement="top" isOpen={this.state.ShowTotalFreePaidUsers} target="TotalFreePaidUsers" toggle={this.TotalFreePaidUsersToggle}>
                            This shows the graphical representation of total free user vs total paid user.
                          </Tooltip>
                        </i>
                      </div>
                    </Col>
                  </Row>

                  {this.state.HighGraphFreePaidUserConfigOption &&
                    <HighGraph
                      {...this.props} HighGraphConfigOption={this.state.HighGraphFreePaidUserConfigOption}
                    > </HighGraph>
                  }
                </div>
              </Col>
            }

            <Col md={HF.allowDFS() == 1 ? 6 : 12} className="pr-0">
            <div className="leaderbaordhead">Total Referral Amount Distributed</div>
              <div className="graph-container">
                <Row className="graph-align">
                  <Col sm={6}>
                    <div className="tabbtn custom-graph">
                      <ButtonGroup>
                        <span onClick={() => this.onReferralRadioBtnClick('daily')} className={this.state.filtertypeReferral === 'daily' ? 'active' : ''} >Daily</span>
                        <span onClick={() => this.onReferralRadioBtnClick('weekly')} className={this.state.filtertypeReferral === 'weekly' ? 'active' : ''}>Weekly</span>
                        <span onClick={() => this.onReferralRadioBtnClick('monthly')} className={this.state.filtertypeReferral === 'monthly' ? 'active' : ''}>Monthly</span>
                      </ButtonGroup>
                    </div>
                  </Col>
                  <Col sm={6}>
                    <div className="info-icon-wrapper">
                      <i className="icon-info" id="TotalReferralsTooltip">
                        <Tooltip placement="top" isOpen={this.state.ShowTotalReferrals} target="TotalReferralsTooltip" toggle={this.TotalReferralsToggle}>
                          This shows the graphical representation of total amount Distributed as referrals.
                Below the graph is the real measure of total amount distributed and total no. of referrals.
                        </Tooltip>
                      </i>
                    </div>
                  </Col>
                </Row>

                {this.state.HighGraphReferralConfigOption &&
                  <HighGraph
                    {...this.props} HighGraphConfigOption={this.state.HighGraphReferralConfigOption}
                  > </HighGraph>
                }
              </div>
            </Col>
          </Col>}
        </Row>
        
        <Row>
          <Col lg={12} className="pl-0 dash-ldrboard">
            <div className="leaderbaordhead">Leaderboard <i className="icon-info" id="leaderboard-tt">
              <Tooltip placement="top" isOpen={this.state.leaderboardTT} target="leaderboard-tt" toggle={this.leaderboardToggle}>Leaderboard</Tooltip>
            </i>
            </div>
            <div className="user-navigation">
              <div className="w-100">
                <Nav tabs>
                  {HF.allowCoinOnly() != 1 && <NavItem className={this.state.activeLdrBrd === '1' ? "active" : ""}
                    onClick={() => { this.toggleLdrBoard('1'); }}>
                    <NavLink>
                      Amount Deposited
                    </NavLink>
                  </NavItem>}
                  <NavItem className={this.state.activeLdrBrd === '2' ? "active" : ""}
                    onClick={() => { this.toggleLdrBoard('2'); }}>
                    <NavLink>
                      Referrals
                    </NavLink>
                  </NavItem>
                  {
                    HF.allowDFS() == 1 &&
                    <NavItem className={this.state.activeLdrBrd === '4' ? "active" : ""}
                      onClick={() => { this.toggleLdrBoard('4'); }}>
                      <NavLink>
                        Winnings
                      </NavLink>
                    </NavItem>
                  }

                  <NavItem className={this.state.activeLdrBrd === '5' ? "active" : ""}
                    onClick={() => { this.toggleLdrBoard('5'); }}>
                    <NavLink>
                      Total Time Spent
                    </NavLink>
                  </NavItem>
                  {
                    HF.allowDFS() == 1 &&
                    <NavItem className={this.state.activeLdrBrd === '6' ? "active" : ""}
                      onClick={() => { this.toggleLdrBoard('6'); }}>
                      <NavLink>
                        Top Team
                      </NavLink>
                    </NavItem>
                  }
                  {HF.allowCoinOnly() != 1 &&<NavItem className={this.state.activeLdrBrd === '7' ? "active" : ""}
                    onClick={() => { this.toggleLdrBoard('7'); }}>
                    <NavLink>
                      Withdrawal
                    </NavLink>
                  </NavItem>}
                </Nav>
              </div>
            </div>
            <div className="view-all-box w-100 clearfix">
              <a
                className="view-all"
                onClick={() => this.props.history.push('/leaderboard/' + redirectPath + '?rdr=true')}
              >
                View All
                    </a>
            </div>
            <TabContent activeTab={activeLdrBrd}>
              {
                (activeLdrBrd == '1') &&
                <TabPane tabId="1">
                  {
                    (HF.allowCoinOnly() != 1 && !ldrPosting && !dbCallback) &&
                    // <Depositor_Ldr dbCallback={dbCallback} />
                    <Depositor_Ldr {...lbrdProps} />
                  }
                </TabPane>
              }
              {
                (activeLdrBrd == '2') &&
                <TabPane tabId="2">
                  {
                    (!ldrPosting && !dbCallback) &&
                    <Referral_Ldr {...lbrdProps} />
                  }
                </TabPane>
              }
              {
                (activeLdrBrd == '4') &&
                <TabPane tabId="4">
                  {
                    (!ldrPosting && !dbCallback) &&
                    <Winning_Ldr {...lbrdProps} />
                  }
                </TabPane>
              }
              {
                (activeLdrBrd == '5') &&
                <TabPane tabId="5">
                  {
                    (!ldrPosting && !dbCallback) &&
                    <TimeSpent_Ldr {...lbrdProps} />
                  }
                </TabPane>
              }
              {
                (activeLdrBrd == '6') &&
                <TabPane tabId="6">
                  {
                    (!ldrPosting && !dbCallback) &&
                    <TopTeam_Leaderboard {...lbrdProps} />
                  }
                </TabPane>
              }
              {
                (activeLdrBrd == '7') &&
                <TabPane tabId="7">
                  {
                    (HF.allowCoinOnly() != 1 && !ldrPosting && !dbCallback) &&
                    <Withdrawal_Leaderboard {...lbrdProps} />
                  }
                </TabPane>
              }
            </TabContent>
          </Col>
        </Row>
      </Fragment>
    )
  }

  //Api call for coin redeemed graph
  appUsageGraph = () => {
    let { startDate, endDate } = this.state    
    let params = {
      from_date: moment(HF.dateInUtc(startDate)).format("YYYY-MM-DD"),
      to_date: moment(HF.dateInUtc(endDate)).format("YYYY-MM-DD"),
    }

    getAppUsageData(params).then(ResponseJson => {
      if (ResponseJson.response_code == NC.successCode) {
        this.setState({
          AppUsageSeries: ResponseJson.data.series_data,
          AppUsageData: ResponseJson.data ? ResponseJson.data : [],

          IOS: ResponseJson.data.ios ? ResponseJson.data.ios : 0,
          IOS_PER: ResponseJson.data.ios_per ? ResponseJson.data.ios_per : 0,
          WEB: ResponseJson.data.web ? ResponseJson.data.web : 0,
          WEB_PER: ResponseJson.data.web_per ? ResponseJson.data.web_per : 0,
          ANDROID: ResponseJson.data.android ? ResponseJson.data.android : 0,
          ANDROID_PER: ResponseJson.data.android_per ? ResponseJson.data.android_per : 0,
          MOBILE_PER: ResponseJson.data.mobile_per ? ResponseJson.data.mobile_per : 0,
          IOS_BROWSER: ResponseJson.data.ios_browser ? ResponseJson.data.ios_browser : 0,
          IOS_APP: ResponseJson.data.ios_app ? ResponseJson.data.ios_app : 0,
          ANDROID_MOBILE_WEB: ResponseJson.data.android_mobile_web ? ResponseJson.data.android_mobile_web : 0,
          ANDROID_APP: ResponseJson.data.android_app ? ResponseJson.data.android_app : 0,
          TABLET_PER: ResponseJson.data.tablet_per ? ResponseJson.data.tablet_per : 0,
          IPAD: ResponseJson.data.ipad ? ResponseJson.data.ipad : 0,
          ANDROID_TAB: ResponseJson.data.android_tab ? ResponseJson.data.android_tab : 0,
          DESKTOP_PER: ResponseJson.data.desktop_per ? ResponseJson.data.desktop_per : 0,
        }, () => {
          //Start Coin Redeemed Graph
          this.setState({
            appUsageGraph: {
              title: {
                text: ''
              },
              chart: {
                type: 'pie'
              },
              plotOptions: {
                pie: {
                  borderWidth: 0,
                  dataLabels: false,
                  innerSize: '74%',
                  allowPointSelect: true,
                  cursor: 'pointer',
                  stacking: 'normal'
                }
              },
              series: [{
                data: this.state.AppUsageSeries,
                name: '',
              }],
              LineData: [],
              GraphHeaderTitle: [],
              credits: {
                enabled: false,
              },
              legend: {
                enabled: false
              }
            }
          })
          //End Coin Redeemed Graph
        })
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  componentDidMount() {
    this.getTimelines();
    this.getFreePaidUsers();
    this.getFreePaidUsers_lf();
    this.getDevices();
    this.getSiterake();
    this.getSiterake_lf(); 
    this.getReferral();
    this.getCalculatedsummary();
    this.getSegregation();
    this.getActiveUsers();

    this.appUsageGraph();
  }

  render() {
    return (
      this.getSummaryHtml()
    );
  }
}


export default Dashboard;
