import React from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _isUndefined, _isEmpty, _debounce, _filter, _Map } from '../../Utilities/Utilities';
import { my_contest_config } from '../../JsonFiles';
import { getPTMyContest } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import queryString from 'query-string';
import CustomHeader from '../../components/CustomHeader';
import { NoDataView } from '../CustomComponent';
import PTLiveContest from './PTLiveContest';
import PTUpcomingContest from './PTUpcomingContest';
import PTCompleted from './PTCompleted';
import PTCardTournament from './PTCardTournament';


/**
  * @class MyContest
  * @description My contest listing of current loggedin user for selected sports
  * @author Vinfotech
*/
export default class PTCompletedList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoaderShow: false,
            ShimmerList: [1, 2, 3, 4, 5],
            tournamentCard: []

        }
    }

    componentDidMount() {
      this.getMyCollectionsList()
    }



  

  
    getMyCollectionsList = async (status) => {
        var param = {
            "sports_id": Constants.AppSelectedSport,
            "status": 2,
        }
        this.setState({ isLoaderShow: true })

        let apiStatus = getPTMyContest
        var responseJson = await apiStatus(param);
        this.setState({ tournamentCard: responseJson.data, isLoaderShow: false })


    }

   
    gotoDetails = (item) => {
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/pickem/detail/' + item.tournament_id,
            state: {
                tourId: item.tournament_id
            }
        })
    }
   

  
    render() {
        const { tournamentCard
        } = this.state;

        let HeaderOption = {
            title: AppLabels.COMPLETED_PT_TOUR,
            back: true,
            MLogo: false,
            hideShadow: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true,
            notification: false,
            title_text_view :true

        };
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container my-contest-style tab-two-height web-container-fixed pickem-tour-mycontest mt-5"}>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        {
                            tournamentCard && tournamentCard.length > 0 &&
                            <div className="tour-list tour-list-new">
                                {
                                    _Map(tournamentCard, (item, idx) => {
                                        return (
                                            <PTCardTournament
                                                item={item}
                                                KEY={idx}
                                                gotoDetails={() => this.gotoDetails(item)}
                                            // joinTournament={(e)=>this.joinTournament(e,item)}
                                            />
                                        )
                                    })
                                }
                            </div>
                        }
                        {
                            tournamentCard && tournamentCard.length == 0 &&
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                            />
                        }
                      

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}



