import React, { useState, useEffect } from "react";
import { Row, Col, Table, Input } from 'reactstrap';
import HF, { _Map, _times, _isEmpty } from '../../helper/HelperFunction';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import { H2H_GET_H2H_GAME_USERS } from "../../helper/WSCalling"
import Loader from '../../components/Loader';
import CommonPagination from '../../components/CommonPagination';
import { useHistory, useLocation } from 'react-router-dom';
import Images from "../../components/images";
const PERPAGE = NC.ITEMS_PERPAGE
// const PERPAGE = 1
function H2hContestUser() {
    const history = useHistory();
    const [CURRENT_PAGE, setPage] = useState(1);
    const [userList, setUserData] = useState([]);
    const [Total, setTotalUser] = useState(0);
    const [listLoding, setIsLoading] = useState(true);
    const [Keyword, setSearchWord] = useState('');
    const [GameInfo, setGameInfo] = useState({});


    const search = useLocation().search;
    const coll_id = new URLSearchParams(search).get('cid');
    const temp_id = new URLSearchParams(search).get('tid');

    const handlePageChange = (current_page) => {
        setIsLoading(true);
        setTotalUser(0);
        setPage(current_page);
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
            collection_master_id: coll_id,
            contest_template_id: temp_id,
            keyword: Keyword,
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }

        H2H_GET_H2H_GAME_USERS(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                setGameInfo(ResponseJson.data.game_info);
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
        <div className="h2h-user animate-left">
            <Row>
                <Col md={12}>
                    <div className="h2hMthDtl">
                        <div className="h2hMth">
                            {GameInfo.collection_name}
                        </div>
                        {/* <div className="h2hConDtl">
                            Head 2 Head , WIN400
                        </div> */}
                        <div className="h2hConDtl">
                            {HF.getFormatedDateTime(GameInfo.season_scheduled_date, 'DD MMM YYYY, hh:mm A')}
                        </div>
                    </div>
                </Col>
            </Row>
            <Row>
                <Col md={12}>
                    <div className="search-input float-right mb-30">
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
            <div className="h2hUsers">
                <Row>
                    {
                        Total > 0 ?
                            _Map(userList, (item, idx) => {
                                return (
                                    <Col md={3} key={idx}>
                                        <div className="h2hUdtl">
                                            <figure className="h2hUimg">
                                                <img src={!_isEmpty(item.image) ? NC.S3 + NC.THUMB + item.image : Images.no_image} className="img-cover" />
                                            </figure>
                                            <div className="h2hUname">
                                                <a href={"/admin/#/profile/" + item.user_unique_id + '?tab=pers'}>
                                                    {item.name}
                                                </a>
                                            </div>
                                            <div className="h2hRole">{item.level}</div>
                                        </div>
                                    </Col>
                                )
                            })
                            :
                            (Total == 0 && !listLoding) ?
                                <div className="no-records">
                                    {NC.NO_RECORDS}</div>
                                :
                                <Loader />

                    }
                </Row>
            </div>
            {
                <CommonPagination {...pagination_props} />
            }
        </div>
    );
}
export default H2hContestUser