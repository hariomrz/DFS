import React from 'react';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import * as AppLabels from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';

export default class PageNotFound extends React.Component {
    gotoScreen = () => {
        if (this.props.history && this.props.history.replace) {
            this.props.history.replace('/lobby#' + Utilities.getSelectedSportsForUrl())
        } else {
            window.location.replace('/lobby#' + Utilities.getSelectedSportsForUrl())
        }
    }

    UNSAFE_componentWillMount(){
        Utilities.setScreenName('PageNotFound')
    }
    render() {
        return (
            <div>
                <Helmet titleTemplate={`${MetaData.template} | %s`}>
                    <title>{MetaData.PageNotFound.title}</title>
                </Helmet>

                <div className="container-404">
                    <div className="content-wrapper">
                        <h1>{AppLabels.Oops}</h1>
                        <p>
                            {AppLabels.Looks_like_you_have} <a href onClick={this.gotoScreen} className="lobby-a">{AppLabels.Lobby}</a>
                        </p>
                        <button onClick={this.gotoScreen} className="btn lobby-btn">
                            {AppLabels.Go_Back_to_Lobby}

                        </button>
                    </div>
                </div>
            </div>
        )
    }

}