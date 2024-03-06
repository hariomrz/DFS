import React, { Component } from "react";
import Pagination from "react-js-pagination";

class CommonPagination extends Component {
    render(){
        let { current_page, per_page, total, page_range_displayed } = this.props        
        return (
            total > per_page &&
            <div className="comPagination">
                <div className="custom-pagination lobby-paging">
                    <Pagination
                        activePage={current_page}
                        itemsCountPerPage={per_page}
                        totalItemsCount={total}
                        pageRangeDisplayed={page_range_displayed}
                        onChange={(e)=>this.props.handle_page_change(e)}
                    />
                </div>
            </div>
        );
    }
}
export default CommonPagination