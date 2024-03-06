import React, { Component } from 'react';
import { DropdownMenu, DropdownToggle, Nav,Row} from 'reactstrap';
import PropTypes from 'prop-types';

import { AppAsideToggler, AppHeaderDropdown, AppNavbarBrand, AppSidebarToggler } from '@coreui/react';

import logo from '../../assets/img/brand/logo.png'
import sygnet from '../../assets/img/brand/sygnet.svg'
import avatar from '../../assets/img/avatars/default_user.png'
import list from '../../assets/img/avatars/list.png'
import msg from '../../assets/img/avatars/msg.png'
import bell from '../../assets/img/avatars/bell.png'
import Col from 'reactstrap/lib/Col';
import WSManager from '../../helper/WSManager';

const propTypes = {
  children: PropTypes.node,
};

const defaultProps = {};

class DefaultHeader extends Component {

  onMasterSportsChange = (selected_sport) => {
    //console.log(selected_sport);
  }

  render() {

    // eslint-disable-next-line
    const { children, ...attributes } = this.props;

    return (
      <React.Fragment>
        <AppSidebarToggler className="d-lg-none" display="md" mobile />
       
        <AppNavbarBrand
          full={{ src: logo, width: 89, height: 25, alt: 'CoreUI Logo' }}
          minimized={{ src: sygnet, width: 30, height: 30, alt: 'CoreUI Logo' }}
        />
        <AppSidebarToggler className="d-md-down-none" display="lg" />        
           
        <Nav className="ml-auto top-navigation" navbar>
          {
         WSManager.getRole() ==  1 && <a  className="btn btn-default" style={{ color: '#fff' }} href="#/logout" onClick={()=>{window.open('location', '_self').close()}}>Back to admin</a>
          }
            {
         WSManager.getRole() ==  2 && <a  className="btn btn-default" style={{ color: '#fff' }} href="#/logout" onClick={()=>{WSManager.logout()}}>Log Out</a>
          }
        </Nav>
      </React.Fragment>
    );
  }
}

DefaultHeader.propTypes = propTypes;
DefaultHeader.defaultProps = defaultProps;

export default DefaultHeader;
