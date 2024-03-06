import React, { Component } from 'react';
import CustomLoader from '../../helper/CustomLoader';
import { Utilities } from '../../Utilities/Utilities';

class ErrorBoundary extends Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false };
  }
  componentDidCatch(error, info) {
    this.setState({ 
      hasError: true
    }, () => {
      Utilities.gtmEventFire('error_boundary')
      setTimeout(() => { 
        if(process.env.NODE_ENV == 'production'){
          window.location.assign('/lobby')
        }
       }, 2000)
    });
    console.error(error.message)
    console.warn(info)
  }
  render() {
    if (this.state.hasError) {
      return (
       <CustomLoader />
      );
    }
    return this.props.children;
  }
}
export default ErrorBoundary;
