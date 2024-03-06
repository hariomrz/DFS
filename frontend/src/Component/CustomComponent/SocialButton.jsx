import PropTypes from 'prop-types'
import React, { Component } from 'react';

import SocialLogin from 'react-social-login'

class SocialButton extends Component {

  static propTypes = {
    triggerLogin: PropTypes.func.isRequired,
    triggerLogout: PropTypes.func.isRequired
  }

  render() {
    const { children, triggerLogin, triggerLogout, className, ...props } = this.props;
    return (
      <button type='button' className={className} onClick={triggerLogin} {...props}>
        {children}
      </button>
    )
  }
}

export default SocialLogin(SocialButton)