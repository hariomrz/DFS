import React from 'react';
import { withTranslation } from 'react-i18next';
import { bindActionCreators } from "redux";
import { connect } from "react-redux";
import { Actions } from "ReduxLib/reducers";
import { Helmet } from 'react-helmet';
import _ from 'lodash';
const {
  REACT_APP_META_TITLE,
  REACT_APP_META_DESCRIPTION
} = process.env

const withMyHoc = (ReduxWrappedComponent) => props => {
  const { meta } = props;
  return (
    <>
      {
        !_.isUndefined(meta) &&
        <Helmet
          defaultTitle={REACT_APP_META_TITLE}
          titleTemplate={`%s • ${REACT_APP_META_TITLE}`}>
          <title>{meta.title}</title>
          <meta name="description" content={meta.description || REACT_APP_META_DESCRIPTION} />
        </Helmet>
      }
      <ReduxWrappedComponent {...props} />
    </>
  )
}

// redux props
function mapStateToProps(state) {
  return {
    root: state
  };
}
function mapDispatchToProps(dispatch) {
  return {
    actions: bindActionCreators(Actions, dispatch)
  };
}

const withRedux = (WrappedComponent) =>
  connect(
    mapStateToProps,
    mapDispatchToProps
  )(withMyHoc(withTranslation()(WrappedComponent)));

export default withRedux;