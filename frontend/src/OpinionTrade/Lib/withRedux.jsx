import React from 'react';
import { withTranslation } from 'react-i18next';
import { bindActionCreators } from "redux";
import { connect } from "react-redux";
import { Actions } from "./reducers";
import _ from 'lodash';


const withMyHoc = (ReduxWrappedComponent) => props => {
  return (
      <ReduxWrappedComponent {...props} />
  )
}

// redux props
function mapStateToProps(state) {
  return {
    moduleRoot: state
  };
}
function mapDispatchToProps(dispatch) {
  return {
    moduleActions: bindActionCreators(Actions, dispatch)
  };
}

const withRedux = (WrappedComponent) =>
  connect(
    mapStateToProps,
    mapDispatchToProps
  )(withMyHoc(withTranslation()(WrappedComponent)));

export default withRedux;