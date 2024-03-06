import React, { useState, useEffect } from "react";
import { Row, Col, Table, Input } from 'reactstrap';
import HF, { _Map, _times } from '../../helper/HelperFunction';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import { H2H_GET_UPCOMING_GAME_LIST } from "../../helper/WSCalling"
import Loader from '../../components/Loader';
import Pagination from "react-js-pagination";
import { useHistory, useLocation } from 'react-router-dom';
import CommonPagination from '../../components/CommonPagination';

const PERPAGE = NC.ITEMS_PERPAGE
function ViewAllH2hUSer() {
    const history = useHistory();
    const [CURRENT_PAGE, setPage] = useState(1);
    const [ContestList, setUserData] = useState([]);
    const [Total, setTotalUser] = useState(0);
    const [listLoding, setIsLoading] = useState(true);
    const [Keyword, setSearchWord] = useState('');


    const search = useLocation().search;
    const callFrom = new URLSearchParams(search).get('view');

    const handlePageChange = (current_page) => {
        if (current_page != CURRENT_PAGE) {
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
        H2H_GET_UPCOMING_GAME_LIST(params).then(ResponseJson => {
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

    const pagination_props = {
        current_page: CURRENT_PAGE,
        per_page: PERPAGE,
        total: Total,
        page_range_displayed: 5,
        handle_page_change: handlePageChange,
    }
    return (
        <div className="view-rookie animate-left">
            <Row>
                <Col md={12}>
                    <h2 className="h2-cls">
                        {callFrom == '1' && "H2H Challenger's Participation"}
                        {callFrom == '2' && "Upcoming contest user tracking"}
                    </h2>

                    <label className="back-btn" onClick={() => history.push('/user_management/h2h/dashboard/')}> {'<'} Back to H2H Dashboard</label>
                </Col>
            </Row>
            <Row>
                <Col md={12}>
                    <div className="search-input float-right mb-30">
                        <Input
                            name="search-user"
                            id="search-user"
                            className="search-input"
                            placeholder="Search by Fixture"
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
                                <th className="text-left pl-5">Fixture</th>
                                <th>Template Header</th>
                                <th>Date Time</th>
                                <th>Unmatched Contest</th>
                                <th>Total Contest</th>
                                <th>User Joined</th>
                            </tr>
                        </thead>
                        {
                            Total > 0 ?
                                _Map(ContestList, (item, idx) => {
                                    return (
                                        <tbody key={idx}>
                                            <tr>
                                                <td className="text-left pl-5">
                                                    {item.collection_name ? item.collection_name : '--'}
                                                </td>
                                                <td>{item.template_name ? item.template_name : '--'}</td>
                                                <td>{HF.getFormatedDateTime(item.season_scheduled_date, 'DD MMM YYYY, hh:mm A')}</td>
                                                <td>{item.unmatched ? item.unmatched : '--'}</td>
                                                <td>{item.total ? item.total : '--'}</td>
                                                <td className="text-click">
                                                    <a href={"/admin/#/user_management/h2h/user?cid=" + item.collection_master_id + '&tid=' + item.contest_template_id}>
                                                        {item.total_users ? item.total_users : '--'}
                                                    </a>
                                                </td>
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
            <CommonPagination {...pagination_props} />
        </div>
    );
}
export default ViewAllH2hUSer