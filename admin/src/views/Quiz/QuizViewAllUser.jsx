import React, { useState, useEffect } from "react";
import { Row, Col, Table, Input } from 'reactstrap';
import HF, { _Map } from '../../helper/HelperFunction';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import { QZ_get_quiz_leaderboard } from "../../helper/WSCalling"
import Loader from '../../components/Loader';
import Pagination from "react-js-pagination";
import { useHistory, useLocation } from 'react-router-dom';
import Images from "../../components/images";
const PERPAGE = NC.ITEMS_PERPAGE
function QuizViewAllUser() {
    const history = useHistory();
    const [CURRENT_PAGE, setPage] = useState(1);
    const [userList, setUserData] = useState([]);
    const [Total, setTotalUser] = useState(0);
    const [listLoding, setIsLoading] = useState(true);
    const [Keyword, setSearchWord] = useState('');


    const search = useLocation().search;
    const callFrom = new URLSearchParams(search).get('view');

    const handlePageChange = (current_page) => {
        if (current_page != CURRENT_PAGE)
        {
            setIsLoading(true);
            setTotalUser(0);
            setPage(current_page);
        }
    };

    const handleSearch = (e) => {
        setIsLoading(true);
        setTotalUser(0);
        setPage(1);
        setSearchWord(e.target.value);
    };

    useEffect(() => {
        setIsLoading(true);
        let params = {
            keyword: Keyword,
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }
        QZ_get_quiz_leaderboard(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                setUserData(ResponseJson.data.result);
                setTotalUser(ResponseJson.data.total);
                setIsLoading(false);
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }, [CURRENT_PAGE, Keyword]);

    return (
        <div className="view-rookie animate-left">
            <Row>
                <Col md={12}>
                    <h2 className="h2-cls">
                        {callFrom == '1' && "H2H Challenger's Participation"}
                        {callFrom == '2' && "Upcoming contest user tracking"}
                    </h2>

                    <label className="back-btn" onClick={() => history.push('/coins/quiz/dashboard/')}> {'<'} Back to Quiz Dashboard</label>
                </Col>
            </Row>
            <Row>
                <Col md={12}>
                    <div className="search-input mb-30">
                        <Input
                            name="search-user"
                            id="search-user"
                            className="search-input"
                            placeholder="Search"
                            onChange={handleSearch}
                        />
                    </div>
                </Col>
            </Row>
            <Row>
                <Col md={12} className="table-responsive common-table">
                    <Table>
                        <thead className="height-40">
                            <tr>
                                <th className="text-left pl-5">Participation</th>
                                <th>Rank</th>
                                <th>Quizzes Played</th>
                                <th>Prize Type</th>
                                <th>Winnings</th>
                            </tr>
                        </thead>
                        {
                            Total > 0 ?
                                _Map(userList, (item, idx) => {
                                    return (
                                        <tbody key={idx}>
                                            <tr>
                                                <td className="text-left pl-5">
                                                    <a className="text-click" href={"/admin/#/profile/" + item.user_unique_id + '?tab=pers'}>
                                                        {item.user_name ? item.user_name : '--'}
                                                    </a>
                                                </td>
                                                <td>{item.rank_value}</td>
                                                <td>{item.quiz_played}</td>
                                                <td><img src={Images.REWARD_ICON} alt="" className="mr-2" />Coins</td>
                                                <td>{item.winnings}</td>
                                            </tr>
                                        </tbody>
                                    )
                                })
                                :
                                <tbody>
                                    <tr>
                                        <td colSpan="8">
                                            {(Total == 0 && !listLoding) ?
                                                <div className="no-records">
                                                    {NC.NO_RECORDS}</div>
                                                :
                                                <Loader />
                                            }
                                        </td>
                                    </tr>
                                </tbody>
                        }
                    </Table>
                </Col>
            </Row>
            {
                Total > PERPAGE &&
                <div className="custom-pagination lobby-paging">
                    <Pagination
                        activePage={CURRENT_PAGE}
                        itemsCountPerPage={PERPAGE}
                        totalItemsCount={Total}
                        pageRangeDisplayed={5}
                        onChange={handlePageChange}
                    />
                </div>
            }
        </div>
    );
}
export default QuizViewAllUser