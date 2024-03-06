import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Utilities } from '../../Utilities/Utilities';
import Images from '../../components/images';
import { Nav, NavItem } from 'react-bootstrap';
class OpenPredictorFixture extends Component {
    constructor(props) {
        super(props)
        this.state = {
            isActive: this.props.isActive || false,
            isMyContest: this.props.isMyContest || false,
            isLive: false
        }
    }
    UNSAFE_componentWillReceiveProps(nextProps) {
        if (nextProps.isActive != this.props.isActive) {
            this.setState({
                isActive: nextProps.isActive
            })
        }
    }

    onSelect = (e) => {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }
        const { item, onSelect } = this.props;
        if (this.state.isActive) {
            if (!this.state.isMyContest) {
                onSelect('')
            }
        } else {
            onSelect(item)
        }

    }

    render() {
        const { item } = this.props;
        const { isActive } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    // <li onClick={this.onSelect} key={item.season_game_uid} className={"fixture-card-wrapper open-prediction-card-wrapper prediction-card-wrapper squz-pred-card-wrap " +(isActive ? ' selected' : '')   + " pointer-cursor" + (isActive ? ' active-item' : '')}
                    // >
                    //     <div className="fixture-card-body">
                    //         <img className="img-pp" src={Utilities.getCategoryURL(item.image)} alt="" />
                    //         <div className="match-info-section bg-without">
                    //             <div style={{left:'10px',bottom:"5px"}} className="category-name">
                    //                 {item.name || item.category_name}
                    //             </div>
                    //         </div>
                    //     </div>
                    // </li>
                    <NavItem key={item.season_game_uid}
                        onClick={this.onSelect} eventKey={item.season_game_uid}
                        className={(isActive ? ' active' : '')}>
                        <span>
                            {item.name || item.category_name}
                        </span>
                    </NavItem>



                )}
            </MyContext.Consumer>
        )
    }
}

export default OpenPredictorFixture;