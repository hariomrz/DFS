import React from 'react';

/**
  * @description This component is responsible to represents if there is not any data available
  * @return UI components
*/
class NoDataView extends React.Component {

    render() {
        let { CENTER_IMAGE, MESSAGE_1, MESSAGE_2, BUTTON_TEXT, onClick, BUTTON_TEXT_2, onClick_2, CLASS } = this.props;

        return (
            <div className={"no-data-container " + (CLASS || '')}>
                <div className='no-data-view-text'>
                {
                    MESSAGE_1 &&
                    <h3>{MESSAGE_1}</h3>
                }
                
                {
                    MESSAGE_2 &&
                    <h2>{MESSAGE_2}</h2>
                }
                </div>
                <div className="no-data-view-new">
                    <img alt="" className="" src={CENTER_IMAGE} />
                </div>
                {/* <div className="background-image">
                    <img alt="" className="center-image site-logo" src={CENTER_IMAGE} />
                </div> */}
                {/* {
                    MESSAGE_1 &&
                    <h3>{MESSAGE_1}</h3>
                }
                {
                    MESSAGE_2 &&
                    <h2>{MESSAGE_2}</h2>
                } */}
                {
                    BUTTON_TEXT && onClick &&
                    <div onClick={() => onClick()} className="no-data-button mt30">
                        <span>{BUTTON_TEXT}</span>
                    </div>
                }
                {
                    BUTTON_TEXT_2 && onClick_2 &&
                    <div onClick={() => onClick_2()} className="no-data-button mt15">
                        <span>{BUTTON_TEXT_2}</span>
                    </div>
                }
            </div>
        );
    }

}

export default NoDataView;