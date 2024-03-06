import { Utilities } from 'Utilities/Utilities';
import React from 'react';
import Loadable from "react-loadable";

function Loading(props) {
  if (props.error) {
    return <div className='loading-component'><div>Error! </div><button className='btn btn-primary btn-rounded' onClick={props.retry}>Retry</button></div>;
  } else if (props.timedOut) {
    return <div className='loading-component'><div>Taking a long time...</div> <button className='btn btn-primary btn-rounded' onClick={props.retry}>Retry</button></div>;
  } else if (props.pastDelay) {
    return <div className='loading-component load' />;
  } else {
    return null;
  }
}

// OnBoarding: Mobile
const MobileLogin = Loadable({
  loader: () => import("Component/OnBoarding/MobileFlow/MobileLogin"),
  loading: Loading
});
const VerifyMobile = Loadable({
  loader: () => import("Component/OnBoarding/MobileFlow/VerifyMobile"),
  loading: Loading
});
const PickUsername = Loadable({
  loader: () => import("Component/OnBoarding/MobileFlow/PickUsername"),
  loading: Loading
});
const ReferralCode = Loadable({
  loader: () => import("Component/OnBoarding/MobileFlow/ReferralCode"),
  loading: Loading
});
const PickEmail = Loadable({
  loader: () => import("Component/OnBoarding/MobileFlow/PickEmail"),
  loading: Loading
});
const PickMobileNo = Loadable({
  loader: () => import("Component/OnBoarding/MobileFlow/PickMobileNo"),
  loading: Loading
});

// OnBoarding: Email
const ForgotEmailPassword = Loadable({
  loader: () => import("Component/OnBoarding/EmailFlow/ForgotEmailPassword"),
  loading: Loading
});
const ResetPassword = Loadable({
  loader: () => import("Component/OnBoarding/EmailFlow/ResetPassword"),
  loading: Loading
});
const ChangePassword = Loadable({
  loader: () => import("Component/OnBoarding/EmailFlow/ChangePassword"),
  loading: Loading
});
const EmailLogin = Loadable({
  loader: () => import("Component/OnBoarding/EmailFlow/EmailLogin"),
  loading: Loading
});
const SetPassword = Loadable({
  loader: () => import("Component/OnBoarding/EmailFlow/SetPassword"),
  loading: Loading
});
const VerifyEmail = Loadable({
  loader: () => import("Component/OnBoarding/EmailFlow/VerifyEmail"),
  loading: Loading
});
const UpdateMobileNo = Loadable({
  loader: () => import("Component/OnBoarding/EmailFlow/UpdateMobileNo"),
  loading: Loading
});
const EnterPassword = Loadable({
  loader: () => import("Component/OnBoarding/EmailFlow/EnterPassword"),
  loading: Loading
});
const CreateAccount = Loadable({
  loader: () => import("Component/OnBoarding/EmailFlow/CreateAccount"),
  loading: Loading
});

const GeoFencing = Loadable({
  loader: () => import("./../GeoFencing"),
  loading: Loading
});
const GeoLocationTagging = Loadable({
  loader: () => import("./../../views/GeoLocationTagging/GeoLocationTagging"),
  loading: Loading
});
const Guideline = Loadable({
  loader: () => import("./../Guideline"),
  loading: Loading
});
const Gameshub = Loadable({
  loader: () => import("./../../Component/SportsHub/SportsHub"),
  loading: Loading
});
const Notification = Loadable({
  loader: () => import("./../../views/Notification"),
  loading: Loading
});
const BannedState = Loadable({
  loader: () => import("./../../Component/CustomComponent/BannedState"),
  loading: Loading
});

// Finance Module
const MyWallet = Loadable({
  loader: () => import("Component/Finance/MyWallet"),
  loading: Loading
});
const Transaction = Loadable({
  loader: () => import("Component/Finance/Transaction"),
  loading: Loading
});
const AddFunds = Loadable({
  loader: () => import("Component/Finance/AddFunds"),
  loading: Loading
});
const Withdraw = Loadable({
  loader: () => import("Component/Finance/Withdraw"),
  loading: Loading
});
const PaymentMethod = Loadable({
  loader: () => import("Component/Finance/PaymentMethod"),
  loading: Loading
});
const BuyCoins = Loadable({
  loader: () => import("Component/Finance/BuyCoins"),
  loading: Loading
});
const CashfreePG = Loadable({
  loader: () => import("Component/Finance/CashfreePG"),
  loading: Loading
});
const StripePG = Loadable({
  loader: () => import("Component/Finance/StripePG"),
  loading: Loading
});
const DirectPayPG = Loadable({
  loader: () => import("Component/Finance/DirectPayPG"),
  loading: Loading
});
const QrCodeCryptoModal = Loadable({
  loader: () => import("Component/Finance/QrCodeCryptoModal"),
  loading: Loading
});

// Profile
const Profile = Loadable({
  loader: () => import("Component/Profile/MyProfile"),
  loading: Loading
});
const ProfileEdit = Loadable({
  loader: () => import("Component/Profile/ProfileEdit"),
  loading: Loading
});
const VerifyAccount = Loadable({
  loader: () => import("Component/Profile/VerifyAccount"),
  loading: Loading
});
const PanVerification = Loadable({
  loader: () => import("Component/Profile/PanCardVerification"),
  loading: Loading
});
const BankVerification = Loadable({
  loader: () => import("Component/Profile/BankVerification"),
  loading: Loading
});
const AadharVerification = Loadable({
  loader: () => import("Component/Profile/AadharVerification"),
  loading: Loading
});

// StaticPages
const LandingScreen = Loadable({
  loader: () => import("Component/StaticPages/LandingScreen"),
  loading: Loading
});
const TermsCondition = Loadable({
  loader: () => import("Component/StaticPages/TermsCondition"),
  loading: Loading
});
const RulesScoring = Loadable({
  loader: () => import("Component/StaticPages/RulesScoring"),
  loading: Loading
});
const PrivacyPolicy = Loadable({
  loader: () => import("Component/StaticPages/PrivacyPolicy"),
  loading: Loading
});
const FAQ = Loadable({
  loader: () => import("Component/StaticPages/FAQ"),
  loading: Loading
});
const AboutUs = Loadable({
  loader: () => import("Component/StaticPages/AboutUs"),
  loading: Loading
});
const HowToPlay = Loadable({
  loader: () => import("Component/StaticPages/HowToPlay"),
  loading: Loading
});
const ContactUs = Loadable({
  loader: () => import("Component/StaticPages/ContactUs"),
  loading: Loading
});
const Offers = Loadable({
  loader: () => import("Component/StaticPages/Offers"),
  loading: Loading
});
const Legality = Loadable({
  loader: () => import("Component/StaticPages/Legality"),
  loading: Loading
});
const HowItWorks = Loadable({
  loader: () => import("Component/StaticPages/HowItWorks"),
  loading: Loading
});
const RefundPolicy = Loadable({
  loader: () => import("Component/StaticPages/RefundPolicy"),
  loading: Loading
});
const NewRulesScoring = Loadable({
  loader: () => import("Component/StaticPages/NewRulesScoring"),
  loading: Loading
});
const RookieFeature = Loadable({
  loader: () => import("Component/StaticPages/RookieFeature"),
  loading: Loading
});
const DownloadAppPage = Loadable({
  loader: () => import("Component/StaticPages/DownloadAppPage"),
  loading: Loading
});
const DeleteAccount = Loadable({
  loader: () => import("Component/StaticPages/DeleteAccount"),
  loading: Loading
});
const FantasyRules = Loadable({
  loader: () => import("Component/StaticPages/FantasyRules"),
  loading: Loading
});
const ResponsibleGaming = Loadable({
  loader: () => import("Component/StaticPages/ResponsibleGaming"),
  loading: Loading
});
// Others
const ReferFriendStandard = Loadable({
  loader: () => import("views/ReferFriendStandard"),
  loading: Loading
});
const EditReferralCode = Loadable({
  loader: () => import("views/EditReferralCode"),
  loading: Loading
});
const Feed = Loadable({
  loader: () => import("Component/Feed/Feed"),
  loading: Loading
});
const AffiliateProgram = Loadable({
  loader: () => import("Component/BecomeAffiliate/AffiliateProgram"),
  loading: Loading
})
const EarnCoins = Loadable({
  loader: () => import("Component/CoinsModule/EarnCoins"),
  loading: Loading
});
const FeedbackQA = Loadable({
  loader: () => import("Component/CoinsModule/FeedbackQA"),
  loading: Loading
});
const RedeemCoins = Loadable({
  loader: () => import("Component/CoinsModule/RedeemCoins"),
  loading: Loading
});
const XPLevels = Loadable({
  loader: () => import("Component/XPModule/XPLevels"),
  loading: Loading
});
const XPPoints = Loadable({
  loader: () => import("Component/XPModule/XPPoints"),
  loading: Loading
});
const XPPointsHistory = Loadable({
  loader: () => import("Component/XPModule/XPPointsHistory"),
  loading: Loading
});
const TDSDashboard = Loadable({
  loader: () => import("Component/TDS/TDSDashboard"),
  loading: Loading
});
const FantasyRefLeaderboard = Loadable({
  loader: () => import("Component/FantasyRefLeaderboard/FantasyRefLeaderboard"),
  loading: Loading
});
const FantasyLeagueLeaderboard = Loadable({
  loader: () => import("Component/FantasyRefLeaderboard/FantasyLeagueLeaderboard"),
  loading: Loading
});
const LBLeaguePoints = Loadable({
  loader: () => import("Component/FantasyRefLeaderboard/LBLeaguePoints"),
  loading: Loading
});
const Contest = Loadable({
  loader: () => import("views/Contest"),
  loading: Loading
});
const SelfExclusion = Loadable({
  loader: () => import("views/SelfExclusion"),
  loading: Loading
});
const LBAllPrizes = Loadable({
  loader: () => import("Component/FantasyRefLeaderboard/LBAllPrizes"),
  loading: Loading
});
const DMContest = Loadable({
  loader: () => import("Component/DFSWithMultigame/DMContest"),
  loading: Loading
});
const ReferalLeaderBoard = Loadable({
  loader: () => import("views/ReferalLeaderBoard"),
  loading: Loading
});

 // pageType =>  Public = 0, Common = 1, Private = 2
const RouteMap = {
  header: {
    nav: [
      {
        name: 'GAMESHUB',
        path: '/sports-hub',
        key: 'sports-hub',
        pageType: 1,
        exact: false
      },
      {
        name: `Lobby`,
        path: '/lobby/',
        key: 'game_hub_lobby',
        pageType: 1,
        exact: true
      },
      {
        name: 'Leaderboard',
        path: '/global-leaderboard',
        key: 'global-leaderboard',
        pageType: 2,
        exact: true
      },
      // {
      //   ...(leaderboard && leaderboard.length > 0 ?
      //     {
      //       name: 'Leaderboard',
      //       path: '/global-leaderboard',
      //       key: 'global-leaderboard',
      //       pageType: 2,
      //       exact: true
      //     }
      //     :
      //     {}
      //   )
      // },
      // {
      //   name: 'My Wallet',
      //   path: '/my-wallet',
      //   key: 'my-wallet',
      //   pageType: 2,
      //   exact: true
      // },
      // {
      //   name: 'Profile',
      //   path: '/my-profile',
      //   key: 'my-profile',
      //   pageType: 2,
      //   exact: true
      // },
    ],
    option: {
      isShow: true,
      isFooterShow: true,
    }
  },
  route: [
    {
      path: "/geofencing",
      name: "GeoFencing",
      Component: GeoFencing,
      exact: true,
      pageType: 2,
      meta: {
        title: "Geo Fencing",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/guideline",
      name: "Guideline",
      Component: Guideline,
      exact: true,
      pageType: 2,
      meta: {
        title: "Guideline",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        nofif: false
      }
    },
    {
      path: '/sports-hub',
      name: "Gameshub",
      Component: Gameshub,
      exact: true,
      pageType: 1,
      meta: {
        title: "Gameshub",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        nofif: false
      }
    },

    {
      path: "/notification",
      name: "Notification",
      Component: Notification,
      exact: true,
      pageType: 2,
      meta: {
        title: "Notification",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        nofif: false
      }
    },
    {
      path: "/change-password",
      name: "ChangePassword",
      Component: ChangePassword,
      exact: true,
      pageType: 2,
      meta: {
        title: "Change Password",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        nofif: false
      }
    },
    {
      path: "/signup",
      name: "signup",
      Component: Utilities.getMasterData().login_flow === '1' ? EmailLogin : MobileLogin,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: 'login-screen',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/set-password",
      name: "set-password",
      Component: SetPassword,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/password",
      name: "password",
      Component: EnterPassword,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/verify",
      name: "verify",
      Component: Utilities.getMasterData().login_flow === '1' ? VerifyEmail : VerifyMobile,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: 'login-screen',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/referral",
      name: "referral",
      Component: ReferralCode,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: 'login-screen',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/pick-username",
      name: "pick-username",
      Component: PickUsername,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: 'login-screen',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/email",
      name: "email",
      Component: PickEmail,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/enter-email",
      name: "enter-email",
      Component: ForgotEmailPassword,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/create-account",
      name: "create-account",
      Component: CreateAccount,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/forgot-password",
      name: "forgot-password",
      Component: ResetPassword,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/forgot-password",
      name: "forgot-password",
      Component: ResetPassword,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/pick-mobile",
      name: "pick-mobile",
      Component: Utilities.getMasterData().login_flow === '1' ? UpdateMobileNo : PickMobileNo,
      exact: true,
      pageType: 0,
      meta: {
        title: "Signup",
        description: "",
      },
      className: 'login-screen',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/geo_location",
      name: "geo_location",
      Component: GeoLocationTagging,
      exact: true,
      pageType: 1,
      meta: {
        title: "Signup",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/banned-state",
      name: "banned-state",
      Component: BannedState,
      exact: true,
      pageType: 1,
      meta: {
        title: "Signup",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isShow: false,
        isFooterShow: false,
      }
    },
    {
      path: "/my-wallet",
      name: "MyWallet",
      Component: MyWallet,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {},
      option: {}
    },
    {
      path: "/transactions",
      name: "MyWallet",
      Component: Transaction,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/add-funds",
      name: "add-funds",
      Component: AddFunds,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/withdraw",
      name: "withdraw",
      Component: Withdraw,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/payment-method",
      name: "payment-method",
      Component: PaymentMethod,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/buy-coins",
      name: "buy-coins",
      Component: BuyCoins,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/cashfree",
      name: "cashfree",
      Component: CashfreePG,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/stripe",
      name: "stripe",
      Component: StripePG,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/directpay",
      name: "directpay",
      Component: DirectPayPG,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/tds-dashboard",
      name: "tds-dashboard",
      Component: TDSDashboard,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Wallet",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/delete-account",
      name: "delete-account",
      Component: DeleteAccount,
      exact: true,
      pageType: 2,
      meta: {
        title: "All Prizes",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/self-exclusion",
      name: "self-exclusion",
      Component: SelfExclusion,
      exact: true,
      pageType: 2,
      meta: {
        title: "Playing Limit",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/my-profile",
      name: "my-profile",
      Component: Profile,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Profile",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/edit-profile",
      name: "edit-profile",
      Component: ProfileEdit,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Profile",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/verify-account",
      name: "verify-account",
      Component: VerifyAccount,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Profile",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/pan-verification",
      name: "pan-verification",
      Component: PanVerification,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Profile",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/bank-verification",
      name: "bank-verification",
      Component: BankVerification,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Profile",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/aadhar-verification",
      name: "aadhar-verification",
      Component: AadharVerification,
      exact: true,
      pageType: 2,
      meta: {
        title: "My Profile",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/about-us",
      name: "about-us",
      Component: AboutUs,
      exact: true,
      pageType: 1,
      meta: {
        title: "About Us",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/faq",
      name: "faq",
      Component: FAQ,
      exact: true,
      pageType: 1,
      meta: {
        title: "FAQ",
        description: "",
      },
      className: 'remove-mobile-header',
      theme: {}
    },
    {
      path: "/terms-condition",
      name: "terms-condition",
      Component: TermsCondition,
      exact: true,
      pageType: 1,
      meta: {
        title: "Terms Condition",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/fantasy-rules",
      name: "fantasy-rules",
      Component: FantasyRules,
      exact: true,
      pageType: 1,
      meta: {
        title: "Fantasy Rules",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/privacy-policy",
      name: "privacy-policy",
      Component: PrivacyPolicy,
      exact: true,
      pageType: 1,
      meta: {
        title: "Privacy Policy",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/contact-us",
      name: "contact-us",
      Component: ContactUs,
      exact: true,
      pageType: 1,
      meta: {
        title: "Contact Us",
        description: "",
      },
      className: 'remove-mobile-header',
      theme: {}
    },
    {
      path: "/refund-policy",
      name: "refund-policy",
      Component: RefundPolicy,
      exact: true,
      pageType: 1,
      meta: {
        title: "Refund Policy",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/legality",
      name: "legality",
      Component: Legality,
      exact: true,
      pageType: 1,
      meta: {
        title: "Legality",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/offers",
      name: "offers",
      Component: Offers,
      exact: true,
      pageType: 1,
      meta: {
        title: "Offers",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/how-it-works",
      name: "how-it-works",
      Component: HowItWorks,
      exact: true,
      pageType: 1,
      meta: {
        title: "How It Works",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/responsible-gaming",
      name: "responsible-gaming",
      Component: ResponsibleGaming,
      exact: true,
      pageType: 1,
      meta: {
        title: "Responsible Gaming",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/refer-friend",
      name: "refer-friend",
      Component: ReferFriendStandard,
      exact: true,
      pageType: 2,
      meta: {
        title: "Refer a Friend",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/edit-referral-code",
      name: "edit-referral-code",
      Component: EditReferralCode,
      exact: true,
      pageType: 2,
      meta: {
        title: "Refer a Friend",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/feed",
      name: "feed",
      Component: Feed,
      exact: true,
      pageType: 2,
      meta: {
        title: "Feed",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/affiliate-program",
      name: "affiliate-program",
      Component: AffiliateProgram,
      exact: true,
      pageType: 2,
      meta: {
        title: "Affiliate Program",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/fantasy-rules",
      name: "fantasy-rules",
      Component: FantasyRules,
      exact: true,
      pageType: 1,
      meta: {
        title: "Fantasy Rules",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/rules-and-scoring",
      name: "rules-and-scoring",
      Component: RulesScoring,
      exact: true,
      pageType: 1,
      meta: {
        title: "Rules Scoring",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/new-rules-and-scoring",
      name: "new-rules-and-scoring",
      Component: NewRulesScoring,
      exact: true,
      pageType: 1,
      meta: {
        title: "Rules Scoring",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/earn-coins",
      name: "earn-coins",
      Component: EarnCoins,
      exact: true,
      pageType: 1,
      meta: {
        title: "Earn Coins",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/rewards",
      name: "rewards",
      Component: RedeemCoins,
      exact: true,
      pageType: 1,
      meta: {
        title: "Rewards",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/experience-points",
      name: "experience-points",
      Component: XPPoints,
      exact: true,
      pageType: 1,
      meta: {
        title: "Experience Points",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/experience-points-levels",
      name: "experience-points-levels",
      Component: XPLevels,
      exact: true,
      pageType: 1,
      meta: {
        title: "Experience Points",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/experience-points-history",
      name: "experience-points-history",
      Component: XPPointsHistory,
      exact: true,
      pageType: 1,
      meta: {
        title: "Experience Points",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/global-leaderboard",
      name: "global-leaderboard",
      Component: FantasyRefLeaderboard,
      exact: true,
      pageType: 2,
      meta: {
        title: "Leaderboard",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/league-leaderboard/:lbid/:status/:lname",
      name: "global-leaderboard",
      Component: FantasyLeagueLeaderboard,
      exact: true,
      pageType: 2,
      meta: {
        title: "Leaderboard",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/leaderboard-details/:history_id/:u_name",
      name: "global-leaderboard",
      Component: LBLeaguePoints,
      exact: true,
      pageType: 2,
      meta: {
        title: "Leaderboard",
        description: "",
      },
      className: '',
      theme: {}
    },
    {
      path: "/:sportsId/contest/:contest_unique_id",
      name: "Contest",
      Component: Contest,
      exact: true,
      pageType: 1,
      meta: {
        title: "Contest",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isFooterShow: false,
      }
    },
    {
      path: "/:sportsId/multi-with-dfs/:contest_unique_id",
      name: "DMContest",
      Component: DMContest,
      exact: true,
      pageType: 1,
      meta: {
        title: "Contest",
        description: "",
      },
      className: '',
      theme: {},
      option: {
        isFooterShow: false,
      }
    },
    {
      path: "/all-prizes",
      name: "LBAllPrizes",
      Component: LBAllPrizes,
      exact: true,
      pageType: 1,
      meta: {
        title: "All Prizes",
        description: "",
      },
      className: '',
      theme: {},
      option: {
      }
    },
    {
      path: "/feedback",
      name: "FeedbackQA",
      Component: FeedbackQA,
      exact: true,
      pageType: 1,
      meta: {
        title: "Feedback",
        description: "",
      },
      className: '',
      theme: {},
      option: {
      }
    },
    {
      path: "/refer-friend-leaderboard",
      name: "ReferalLeaderBoard",
      Component: ReferalLeaderBoard,
      exact: true,
      pageType: 1,
      meta: {
        title: "LeaderBoard",
        description: "",
      },
      className: 'rf-leaderboard-large',
      theme: {},
      option: {
      }
    },
    {
      redirect: true,
      from: "/more",
      to: '/lobby'
    },
    {
      redirect: true,
      from: "/:sports_id/contest-listing/:collection_master_id/:myKey",
      to: '/lobby'
    },
    {
      redirect: true,
      from: "/my-contests",
      to: '/lobby'
    }
  ],
  more:   {
    name: 'More',
    pageType: 1,
    className:'more',
    key: 'more',
    child: [
      {
        name: 'More Menu',
        pageType: 2,
        menu: [
          {
            name: 'My Wallet',
            path: '/my-wallet',
            pageType: 2,
            exact: true,
            page_key: "_"
          },
          {
            name: 'Feed',
            path: '/feed',
            pageType: 2,
            exact: true,
            page_key: "allow_social"
          },
          {
            name: 'Refer a Friend',
            path: '/refer-friend',
            pageType: 2,
            exact: true,
            page_key: "_"
          },
          {
            name: 'Affiliate Program',
            path: '/affiliate-program',
            pageType: 2,
            exact: true,
            page_key: "a_module"
          },
          {
            name: 'Have a Contest Code',
            path: '/dfs/private-contest',
            pageType: 2,
            exact: true,
            page_key: "_"
          },
          {
            name: 'Earn Coins',
            path: '/earn-coins',
            pageType: 2,
            exact: true,
            page_key: "a_coin"
          },
          {
            name: 'Redeem',
            path: '/rewards',
            pageType: 2,
            exact: true,
            page_key: "a_coin"
          },
          {
            name: 'How to Earn XP Points',
            path: '/experience-points',
            pageType: 2,
            exact: true,
            page_key: "a_xp_point"
          },
          {
            name: 'Playing Limit',
            path: '/self-exclusion',
            pageType: 2,
            exact: true,
            page_key: "allow_self_exclusion"
          },
          {
            name: 'Delete My Account',
            path: '/delete-account',
            pageType: 2,
            exact: true,
            page_key: "delete_account"
          }
        ]
      },
      {
        name: 'Others',
        className:'other',
        pageType: 1,
        menu:[
          {
            name:'About Us',
            path: '/about-us',
            exact: true,
            page_key: "about"
          },
          { 
            name:'FAQ',
            path: '/faq', 
            exact: true,
            page_key: "faq"
          },
          {
            name:'Terms & Conditions',
            path: '/terms-condition',
            exact: true,
            page_key: "terms_of_use"
          },
          {
            name:'Fantasy Rules',
            path: '/fantasy-rules',
            exact: true,
            page_key: "rules_and_scoring"
          },
          {
            name:'Privacy Policy',
            path: '/privacy-policy',
            exact: true,
            page_key: "privacy_policy"
          },
          {
            name:'Contact Us',
            path: '/contact-us',
            exact: true,
            page_key: "contact_us"
          },
          {
            name:'Refund Policy',
            path: '/refund-policy',
            exact: true,
            page_key: "refund_policy"
          },
          {
            name:'Legality',
            path: '/legality',
            exact: true,
            page_key: "legality"
          },
          {
            name:'Offers',
            path: '/offers',
            exact: true,
            page_key: "offers"
          },
          {
            name:'How it works',
            path: '/how-it-works',
            exact: true,
            page_key: "how_it_works"
          },
          {
            name:'Responsible Gaming',
            path: '/responsible-gaming',
            exact: true,
            page_key: "responsible"
          },
        ]
      },
    ]
  }
}

export default RouteMap;
