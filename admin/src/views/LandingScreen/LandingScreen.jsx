import React from "react";
import ReportImage from "../../assets/img/3d-report.png";
import winImage from "../../assets/img/win.png";
import hierarchicalImage from "../../assets/img/hierarchical-structure.png";
import dashboardImage from "../../assets/img/dashboad.png";
import appbannerImage from "../../assets/img/appbanner.png";
import userengagementImage from "../../assets/img/userengagement.png";
import whatsnewImage from "../../assets/img/whatsnew.png";
import arrowImage from "../../assets/img/arrow.png";
import { Link } from "react-router-dom";

const reportCardsInfo = [
  {
    text: "Match Report",
    icon: ReportImage,
    path: "/report/match_report"
  },
  {
    text: "Contest Report",
    icon: winImage,
    path: "report/contest_report"
  },
  {
    text: "Referral Report",
    icon: hierarchicalImage,
    path: "/report/referral_report"
  }
];

const goCardsInfo = [
  {
    heading: "Dashboard",
    content:
      "Track the real -time insights, trends and performance indicators in an excellent visual representation for an informed decision making.",
    image: dashboardImage,
    path: "/dashboard"
  },
  {
    heading: "New App Banner",
    content:
      "Highlight the recent updates, features, or changes to inform users about the latest offers or promotion.   ",
    image: appbannerImage,
    path: "/cms/app_banner/"
  },
  {
    heading: "User Engagement",
    content:
      "Control the user access and permissions within a system or application and get a detailed analysis of every user registered.",
    image: userengagementImage,
    path: "/manage_user"
  },
  {
    heading: "What’s New",
    content:
      "Inform the users about new additions in the application, encouraging them to explore and take advantage of the new features.",
    image: whatsnewImage,
    path: "/settings/what's-new"
  }
];

const rightsectionInfo = [
  {
    title: "Withdarwal Requests",
    text: "Check if there are any new withdrawal request from the users"
  },
  {
    title: "Transaction",
    text: "Track all the in-flow and out flow of various currencies available."
  },
  {
    title: "Deposits and Withdrawal",
    text:
      "Set the minimum and maximum cap for a user when he adds or withdraws from his wallet."
  },
  {
    title: "Need help?",
    text: "support@vinfotech.com"
  }
];

const LandingScreen = props => {
  return (
    <div className="landing-screen-wrap">
      <div className="landingscreen-header">
        <p className="welcome">
          Welcome Admin <i className="icon-arrow-right iocn-third"></i>
        </p>
        <div className="text">
          <p>What do you want to start with?</p>
        </div>
        {/* <Link to="/report/match_report"> redirect</Link> */}
      </div>
      <div className="landing-screen-content">
        <div className="left-section ">
          <div className="report-cards ">
            {reportCardsInfo.map((data, index) => (
              <div className="report-card" key={data.text}>
                <Link to={data.path} style={{ textDecoration: "none" }}>
                  <div className="report-card-group ">
                    <div className="report-card-text">{data.text}</div>
                    <div className="report-icons ">
                      <img src={data.icon} />
                    </div>
                  </div>
                </Link>
              </div>
            ))}
          </div>
          <div className="info-cards ">
            {goCardsInfo.map((data, index) => (
              <div className="info-card" key={data.heading}>
                <div className="info-card-group">
                  <div>
                    <div className="info-card-heading">{data.heading}</div>
                    <div className="info-card-content">{data.content}</div>
                  </div>
                  <div className="info-card-image">
                    <img src={data.image} className="img-fluid" />
                  </div>
                </div>
                <Link to={data.path} style={{ textDecoration: "none" }}>
                  <div className="go-button-div">
                    <button className="go-button">
                      Go
                      <div className="go-button-arrow-container float-right mt-1 mr-2">
                        <i className="icon-arrow" />
                        <i className="icon-arrow" />
                        <i className="icon-arrow" />
                      </div>
                    </button>
                    <span className="line"></span>
                  </div>
                </Link>
              </div>
            ))}
          </div>
        </div>
        <div className="right-section">
          <div
            onClick={() => {
              props.history.push("/finance/withdrawal_list");
            }}
            className="right-section-card"
            style={{
              background: " radial-gradient(circle, #009E81 0%, #00D6BB 100%)"
            }}
          >
            <div className="title">Withdrawal Requests</div>
            <div className="text">
              Check if there are any new withdrawal request from the users
            </div>
          </div>

          <div
            onClick={() => {
              props.history.push("/finance/transaction_list");
            }}
            className="right-section-card"
            style={{
              background: "radial-gradient(circle, #7635D7 0%, #B17DFF 100%)"
            }}
          >
            <div className="title">Transaction</div>
            <div className="text">
              Track all the in-flow and out flow of various currencies
              available.
            </div>
          </div>
          <div
            onClick={() => {
              props.history.push("/settings/minimum-withdrawal");
            }}
            className="right-section-card"
            style={{
              background: " radial-gradient(circle, #D68500 0%, #FFC96C 100%)"
            }}
          >
            <div className="title">Deposits and Withdrawal</div>
            <div className="text">
              Set the minimum and maximum cap for a user when he adds or
              withdraws from his wallet.
            </div>
          </div>
          <div
            className="needhelp-card"
            style={{
              padding: "18px 5px 11px 5px",
              height: "96px",
              background: "#EFEFEF",
              border: " 1px solid #F8436E",
              borderRadius: "10px",
              cursor: "pointer"
            }}
          >
            <div
              className="needhelp-title"
              style={{
                color: "#111111",
                fontFamily: "MuliBold",
                fontSize: "24px",
                fontWeight: "300",
                lineHeight: "26px",
                marginBottom: "12px",
                marginLeft: "20px"
              }}
            >
              Need help?
            </div>
            <div
              className="needhelp-text"
              style={{
                color: "#979797",
                fontFamily: "MuliBold",
                fontSize: "16px",
                fontWeight: "300",
                lineHeight: "17px",
                borderRadius: " 0 0 8px 8px",
                background: "white"
              }}
            >
              <p style={{ marginLeft: "10px", padding: "8px" }}>
                support@vinfotech.com
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LandingScreen;
