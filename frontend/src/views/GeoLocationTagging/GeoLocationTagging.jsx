import React from "react";
import { Col, FormGroup, Row } from "react-bootstrap";
import Images from "../../components/images";
import * as AppLabels from "../../helper/AppLabels";
import { selectStyle } from '../../helper/input-style';
import { Utilities } from "../../Utilities/Utilities";
import { getAllCountries, getAllStates } from "../../WSHelper/WSCallings";
import CustomLoader from "../../helper/CustomLoader";

export default class GeoLocationTagging extends React.Component {
  constructor(props) {
    super(props)
    this.state = {
      isCMounted: false,
      single_country: [],
      allState: [],
      allCountry: [],
      selectedCountryName: '',
      selectedStateName: '',
      banned_state: [],
      isBanned: false,
      isLoader: true,
      blockedCountry: false
    }
  }
  componentDidMount() {

    this.setState({
      isCMounted: true
    }, () => {
      const { login_data, banned_state } = Utilities.getMasterData()
      this.setState({
        single_country: login_data && login_data.split("_"),
        banned_state: banned_state && banned_state.split(",")
      }, () => {
        const { single_country } = this.state
        if (single_country[0] == 1) {
          this.getStateList(single_country[3])
        } else {
          getAllCountries().then((responseJson) => {
            if (responseJson) {
              const countries = [];
              responseJson.map((data) => {
                let obj = { value: data.master_country_id, label: data.country_name, abbr: data.abbr }
                countries.push(obj)
              })
              this.setState({
                allCountry: countries,
                isLoader: false,
              })
            }
          })
        }
      })
    })
  }


  handleCountryChange = (e) => {
    let { allCountry } = this.state
    let { a_country } = Utilities.getMasterData()
    let countryIndex = allCountry.filter(obj => obj.label == e.target.value)

    this.setState({
      blockedCountry: a_country.includes(countryIndex[0].abbr) ? false : true,
      selectedCountryName: countryIndex && countryIndex[0] && countryIndex[0].value,
      selectedStateName: ''
    }, () => {
      this.getStateList(this.state.selectedCountryName)
    })
  }

  handleStateChange = (e) => {
    this.setState({
      selectedStateName: e.target.value
    }, () => {
      const { banned_state } = this.state
      let isbann = banned_state.filter(obj => obj == this.state.selectedStateName)
      this.setState({
        isBanned: isbann.length != 0 ? true : false
      })
    })
  }


  getStateList = (master_country_id) => {
    getAllStates({
      "master_country_id": master_country_id
    }).then((responseJson) => {
      if (responseJson) {
        const states = [];
        responseJson.map((data) => {
          states.push({ value: data.master_state_id, label: data.state_name })
        })
        this.setState({
          allState: states,
          isLoader: false,
        })
      }
    })
  }

  handlerLocationOn = () => {
    if (window.ReactNativeWebView && Utilities.getMasterData().bs_a && Utilities.getMasterData().bs_a == '1') {
      let dataLoc = { "bs_a": Utilities.getMasterData().bs_a, "bs_fs": Utilities.getMasterData().bs_fs, "bs_tm": Utilities.getMasterData().bs_tm }
      let data = {
        action: 'location',
        targetFunc: 'recalllocation',
        locationData: dataLoc
      }
      window.ReactNativeWebView.postMessage(JSON.stringify(data));
    }
  }

  render() {

    const { single_country, allState, allCountry, selectedCountryName, selectedStateName, isBanned, isLoader, blockedCountry } = this.state

    return (
      isLoader ?
        <CustomLoader />
        :
        <div className="web-container geo_location_wrap">
          <div>
            <img src={Images.GEO_LOCATION} className="geo_loca_image" />
          </div>

          <div className="p-4">
            <div className="geo_description">
              <h3 className="dfs">{AppLabels.DAILY_FANTASY_SPORTS}</h3>
              <p className="turn_on">{AppLabels.TURN_ON_LOCATION}</p>
            </div>



            <Row className="input_field">
              <Col xs={12} className={'success'}>
                {single_country && single_country[0] == "0" &&
                  <FormGroup
                    className={`input-label-center `}
                    controlId="formBasicText">
                    <label for="select-state" style={selectStyle.label} className="select_label">{AppLabels.SELECT_COUNTRY}</label>
                    <select
                      className='select-field-transparent'
                      id="select-state"
                      onChange={(e) => this.handleCountryChange(e)}
                    >
                      <option value="none" selected disabled hidden>{AppLabels.SELECT}</option>
                      {allCountry.map((item) => {
                        return <option>{item.label}</option>
                      })
                      }
                    </select>
                    <i className="icon-arrow-down" />
                  </FormGroup>
                }
              </Col>
            </Row>


            {!blockedCountry && <Row className="input_field">
              <Col xs={12} className={'success'}>
                <FormGroup
                  className={`input-label-center `}
                  controlId="formBasicText">
                  <label for="select-state" style={selectStyle.label} className="select_label">{AppLabels.SELECT_STATE}</label>
                  <select
                    className='select-field-transparent'
                    id="select-state"
                    onChange={(e) => this.handleStateChange(e)}
                    placeholder="Select"
                  >
                    <option value="" hidden selected={selectedStateName == ''}>{AppLabels.SELECT}</option>
                    {allState.map((item) => {
                      return (
                        <option>{item.label}</option>
                      )
                    })
                    }
                  </select>
                  <i className="icon-arrow-down" />
                </FormGroup>
              </Col>
            </Row>}


            <div className="text-center">
              <button className={"location_button"} disabled={selectedStateName == '' || isBanned || blockedCountry} onClick={() => this.handlerLocationOn()}>{AppLabels.TURN_ON_LOCATION_ACCESS}</button>
            </div>

            {
              (selectedStateName != '' || blockedCountry) &&
              <div className="geo_description mt-3">
                <img src={(isBanned || blockedCountry) ? Images.SAD : Images.HAPPY} className="happy_img" />
                <h3 className="dfs">{(isBanned || blockedCountry) ? AppLabels.OOPS_FANTASY_RESTRICTED_LOCATION : AppLabels.YAY_YOU_CAN_PLAY}</h3>
                <p className="turn_on">{(isBanned || blockedCountry) ? AppLabels.OOPS_DESCRIPTION : AppLabels.YAY_DESCRIPTION}</p>
              </div>
            }

            <div className="geo_description mt-4 pt-4">
              <p className="turn_on mb-0">{AppLabels.PERMITTED_REGION_BLOCKED}</p>
              <p className="turn_on">{AppLabels.WRITE_US_TO}<span className="support">{AppLabels.SUPPORT_VINFOTECH}</span></p>
            </div>

          </div>

        </div>
    )
  }
}