import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import Images from '../../components/images';
import { _Map } from '../../Utilities/Utilities';

export default class RookieFeature extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            activeKey: 1,
            faqs: [
                {
                    question_id: 1,
                    question: 'What is Rookie Contest?',
                    answer: 'Compete with another Rookie in a safe environment and get better at playing. Get rewarded in form of bonus, cash and coins. We will never bid you goodbye empty-handed'
                },
                {
                    question_id: 2,
                    question: 'How to Play Rookie Contest?',
                    answer: 'If you are a Rookie, then Join Rookie contests, Create Team, Win Rewards'
                }
            ]
        }
    }

    handleSelect = (activeKey) => {
        if (activeKey == this.state.activeKey) {
            this.setState({ activeKey: '' });
        } else {
            this.setState({ activeKey });
        }
    }

    render() {
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container static-page static-page-new web-container-fixed rookie-f">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.ROOKIE.title}</title>
                            <meta name="description" content={MetaData.ROOKIE.description} />
                            <meta name="keywords" content={MetaData.ROOKIE.keywords}></meta>
                        </Helmet>
                        <div>
                            <div className='head-v'>
                                {
                                    this.props.history.length > 1 && <a href className="header-action" onClick={() => this.props.history.goBack()}><i className="icon-left-arrow"></i></a>
                                }
                                <img className='r-logo' src={Images.ROOKIE_LOGO} alt='' />
                            </div>
                            <div className="ex-view">
                                <img className='r-lines' src={Images.ROOKIE_LINES} alt='' />
                                <div className='msg'>
                                    Exclusive contest uniquely made for Newbies
                                </div>
                            </div>
                            <img style={{ width: '100%', height: '100%', objectFit: 'contain', marginTop: 20 }} src={Images.ROOKIE_STADIUM} alt='' />
                            <div className='d-flex mt-5'>
                                <img style={{ width: '36%', height: '100%', objectFit: 'contain', marginTop: -25 }} src={Images.ROOKIE_ARROW} alt='' />
                                <img style={{ width: '64%', height: '100%', objectFit: 'contain' }} src={Images.ROOKIE_WINNER} alt='' />
                            </div>
                            <div className="ex-view smallv">
                                <div className='msg'>
                                    Rookie helps you to bring out the pro gamer in you
                                </div>
                                <div className='details'>
                                    Compete with the players whose stats compare with you and get a chance to win at basic level
                                </div>
                            </div>
                            <div className='play-nv'>
                                <img src={Images.ROOKIE_WHATS} alt='' />
                                <button onClick={() => this.props.history.length > 1 ? this.props.history.goBack() : this.props.history.push('/lobby')} type="button" className="btn-rounded btn">
                                    Play Now!
                                </button>
                            </div>
                            <div className="ex-view smallv faq">
                                <div className='msg'>
                                    Frequently Ask Questions
                                </div>
                            </div>
                            {
                                _Map(this.state.faqs, (obj, indx) => {
                                    return (
                                        <div key={obj.question_id} className={"ques-view" + (this.state.activeKey == obj.question_id ? ' active-q' : '')} onClick={() => this.handleSelect(obj.question_id)}>
                                            <div className='details'>
                                                {obj.question}<span className="plus-minus"><i className={this.state.activeKey == obj.question_id ? "icon-remove" : "icon-plus-ic"} /></span>
                                            </div>
                                            <div className="ans-item">{obj.answer}</div>
                                        </div>
                                    )
                                })
                            }
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}