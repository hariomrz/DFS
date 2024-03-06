import React, { Component } from 'react';
import { Col, Row } from 'reactstrap';
import _ from "lodash";
import LS from 'local-storage';
import Select from 'react-select';
import 'react-select/dist/react-select.min.css';
import { withRouter } from 'react-router'
import HF from '../../helper/HelperFunction';
import SelectDropdown from "../../components/SelectDropdown";

class MasterSportSelection extends Component {

  constructor(props) {
    super(props);

    this.toggle = this.toggle.bind(this);

    this.state = {      
      selected_sport: 0,
      sportsFiterFlag: true,
      UrlHash: window.location.hash,
      sports_list: HF.getSportsData() ? HF.getSportsData() : [],
    };
  }

  toggle() {
    this.setState({ collapse: !this.state.collapse });
  }  

  changeSelectedSport = (e) => {
    console.log(e,'changeSelectedSportchangeSelectedSport')
    if (e) {
      this.setState({ selected_sport: e.value })
      this.props.masterSportsChange(e.value);
      LS.set('selected_sport', e.value);
      LS.set('selectedSports', e.value);
      window.location.reload();
    }
  }

  componentDidMount() {
    if (this.state.UrlHash == '#/manage_scoring') {
      this.setState({ sportsFiterFlag: false })
    }
    else {
      this.setState({ sportsFiterFlag: true })
    }

    var selected_sport = LS.get('selected_sport');
    if (!selected_sport) {
      selected_sport = HF.getMasterData().default_sport;
    }
    this.setState({ selected_sport: selected_sport })
    LS.set('selected_sport', selected_sport);
  }

  componentWillReceiveProps(nextProps) {
    let pathname = nextProps.location.pathname    
    if (pathname == '/game_center/manage_scoring' || pathname.includes("viewrookie") || pathname.includes("quiz") || pathname.includes('/stockfantasy') || pathname.includes('/stock_report') || pathname.includes("/equitysf") || pathname.includes("/picksfantasy") || pathname.includes("/propsFantasy")|| pathname.includes("/opinionTrading")) {
      this.setState({ sportsFiterFlag: false })
    }
    else {
      this.setState({ sportsFiterFlag: true })
    }
  }

  render() {
    const Select_Props = {
      is_disabled: false,
      is_searchable: true,
      is_clearable: false,
      menu_is_open: false,
      select_name: "selected_sport",
      id : "selected_sport",
      class_name: "sports-seletor",
      sel_options: this.state.sports_list,
      place_holder: "Select Sport",
      selected_value: this.state.selected_sport,
      modalCallback: this.changeSelectedSport
    }
    return (
      <div className="animated fadeIn">
        {
          this.state.sportsFiterFlag &&
          <Row>
            <Col xl="6" sm="8">
              <SelectDropdown SelectProps={Select_Props} />
            </Col>
          </Row>
        }
      </div>
    );
  }
}
export default withRouter(MasterSportSelection)