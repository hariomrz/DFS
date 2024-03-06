import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import * as AL from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';

class SponserBySection extends Component {
    constructor(props) {
        super(props)
        this.state = {
        }
    }

    render() {

        const { item } = this.props;

        return (
            <MyContext.Provider >
                <div className="sponser-by-section">
                    <div className="sponser-by-inner-section margin-more">
                        <span>{AL.SPONSORED_BY}</span>
                        {
                            window.ReactNativeWebView ?
                                <a
                                    href
                                    onClick={(event) => Utilities.callNativeRedirection(Utilities.getValidSponserURL(item.sponsor_link, event))}>
                                    <img alt='' className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={item.img} />
                                </a>
                                :
                                <a className="image-sponser"
                                    href={Utilities.getValidSponserURL(item.sponsor_link)}
                                    onClick={(event) => event.stopPropagation()}
                                    target='__blank'>
                                    <img alt='' className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={item.img} />
                                </a>
                        }
                    </div>
                </div>
            </MyContext.Provider>
        )
    }
}

export default SponserBySection;