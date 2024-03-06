import * as NC from "../src/helper/NetworkingConstants";
import WSManager from "../src/helper/WSManager";
import HF from './helper/HelperFunction';
let int_version = localStorage.getItem('int_version')

const items = {
  items: [{
    name: 'Dashboard',
    url: '/dashboard',
  },
  {
    name: 'DFS',
    url: '/game_center',
    children: (NC.ALLOW_DFS == 1) ? [{
      name: int_version == 0 ? 'Fixture' : 'Games',
      url: '/game_center/DFS',
    },
    {
      name: 'Contest Dashboard',
      url: '/game_center/contest/contestlist',
    },
    {
      name: 'Contest Template',
      url: '/game_center/contesttemplate',
    },
    {
      name: 'System User Reports',
      url: '/game_center/system_user_report',
    },
    {
      name: 'Contest category',
      url: '/game_center/category',
    },
    {
      name: 'Create Tournament',
      url: '/game_center/tournament',
    },
    {
      name: 'League Management',
      url: '/game_center/league-management',
    }, 
    {
      name: 'Teams',
      url: '/game_center/Teams',
    },
    {
      name: 'Player Management',
      url: '/game_center/player-management',
    },
    {
      name: 'Manage Scoring',
      url: '/game_center/manage_scoring'
    },
    {
      name: 'Booster Configuration',
      url: '/game_center/settings/booster',
    }

    ] : [{
      name: int_version == '0' ? 'Fixture' : 'Games',
      url: '/game_center/DFS',
    },
    {
      name: 'Contest Dashboard',
      url: '/game_center/contest/contestlist',
    }
    

    ],
  },
  {
    name: 'Multigame',
    url: '/multigame',
    children: [{
      name:  int_version == '0' ? 'Fixture' : 'Games',
      url: '/multigame/Fixtures',
  
    },
    {
      name: 'Contest  List',
      url: '/contest/multigamecontest',
  
    }
    ],
  },
  {
    name: 'Manual Payment',
    url: '/manual_payment',
    children: 
      [{
        name: 'Payment Setup',
        url: '/manual_payment/payment-setup',
      },
      {
        name: 'Reports',
        url: '/manual_payment/reports',
      }],
    
  },
  {
    name: 'Live Fantasy',
    url: '/livefantasy',
    children: [{
      name: 'Fixture',
      url: '/livefantasy/fixture',
    }, {
      name: 'Contest Dashboard',
      url: '/livefantasy/contestlist',
    }, {
      name: 'Contest category',
      url: '/livefantasy/category',
    }, {
      name: 'Merchandise',
      url: '/livefantasy/merchandise',
    }, {
      name: 'Contest Template',
      url: '/livefantasy/contesttemplate',
    }, {
      name: 'Private Contest Dashboard',
      url: '/livefantasy/pc-dashboard',
    }, {
      name: 'Private Contest Setting',
      url: '/livefantasy/pc-setting',
    }, {
      name: 'Contest Report ',
      url: '/livefantasy/user-contest-report',
    },
    {
      name: 'User Money Paid ',
      url: '/livefantasy/user-money-paid',
    },]
  },

  {
      name: 'Props Fantasy',
      url: '/propsFantasy',
      children: [   
        {
          name: 'League Management',
          url: '/propsFantasy/league-managment',
        },  
      {
        name: 'Teams Management',
          url: '/propsFantasy/teams',
      },
      {
        name: 'Players Management',
        url: '/propsFantasy/player-management',
      },
        {
          name: 'User Report',
          url: '/propsFantasy/user-report',
        },
        {
          name: 'Players',
          url: '/propsFantasy/players',
        },
       {
          name: 'Setting',
          url: '/propsFantasy/setting',
        }
      ]
    },
     {
      // opinion_trade
      name: 'Opinion Trade',
      url: '/opinionTrading',
      children: [   
        {
          name: 'League Management',
          url: '/opinionTrading/league-managment',
        },  
      {
        name: 'Teams Management',
          url: '/opinionTrading/teams',
      },
      {
        name: 'Template',
        url: '/opinionTrading/template',
      },
        {
          name: 'Fixture',
          url: '/opinionTrading/fixture',
        },
        {
          name: 'Report',
          url: '/opinionTrading/report',
        },
      //  {
      //     name: 'Setting',
      //     url: '/propsFantasy/setting',
      //   }
      ]
    },
  {
    name: 'Picks Fantasy',
    url: '/picksfantasy',
    children: [{
      name: 'Fixture',
      url: '/picksfantasy/fixture',
    },
    {
      name: 'Contest Dashboard',
      url: '/picksfantasy/contestdashboard',
    },
    {
      name: 'Leagues Management',
      url: '/picksfantasy/leagues',
    },
    {
      name: 'Contest Report',
      url: '/picksfantasy/contest-report',
    },
    //{
    //   name: 'Contest Report',
    //   url: '/pickfantasy/contestreport',
    // }, 
    {
      name: 'Team / Player Management',
      url: '/picksfantasy/team_player_management',
    },
    {
      name: 'Contest Template',
      url: '/picksfantasy/contest-template',
    }
    ]
  },
  {
    name: 'Stock Fantasy',
    url: '/stockfantasy',
    children: [{
      name: 'Fixture',
      url: '/stockfantasy/fixture',
    }, {
      name: 'Contest Dashboard',
      url: '/stockfantasy/contestlist',
    }, {
      name: 'Contest category',
      url: '/stockfantasy/category',
    }, {
      name: 'Merchandise',
      url: '/stockfantasy/merchandise',
    }, {
      name: 'Contest Template',
      url: '/stockfantasy/contesttemplate',
    }, {
      name: 'Stocks',
      url: '/stockfantasy/stock',
    }, {
      name: 'Manual Closure',
      url: '/stockfantasy/manual_close',
    }],
  },
  {
    name: 'Equity Stock Fantasy',
    url: '/equitysf/',
    children: [{
      name: 'Fixture',
      url: '/equitysf/fixture',
    }, {
      name: 'Contest Dashboard',
      url: '/equitysf/contestlist',
    }, {
      name: 'Contest category',
      url: '/equitysf/category',
    }, {
      name: 'Merchandise',
      url: '/equitysf/merchandise',
    }, {
      name: 'Contest Template',
      url: '/equitysf/contesttemplate',
    }, {
      name: 'Stocks',
      url: '/equitysf/stock',
    }, {
      name: 'Manual Closure',
      url: '/equitysf/manual_close',
    }],
  },
  {
    name: 'Stock Predict',
    url: '/stockpredict',
    children: [
      {
        name: 'Create Candle',
        url: '/stockpredict/create-candle/0',
      },
      {
        name: 'Contest List',
        url: '/stockpredict/fixture',
      },
      {
        name: 'Contest Dashboard',
        url: '/stockpredict/contestlist',
      },
      {
        name: 'Merchandise',
        url: '/stockpredict/merchandise',
      },
      {
        name: 'Contest Template',
        url: '/stockpredict/contesttemplate',
      },
      {
        name: 'Stocks',
        url: '/stockpredict/stock',
      },
      {
        name: 'Stock Rates',
        url: '/stockpredict/manual_close',
      },
    ],
  },
  {
    name: 'Finance ERP',
    url: '/erp',
    children: [{
      name: 'Dashboard',
      url: '/erp/dashboard',
    }, {
      name: 'Custom',
      url: '/erp/custom',
    }]
  },
  {
    name: 'Leaderboard',
    url: '/leaderboard',
    children: [{
      name: 'Referral Leaderboard',
      url: '/leaderboard/referral'
    },
    {
      name: 'Depositors Leaderboard',
      url: '/leaderboard/depositors'
    },
    {
      name: 'Winnings Leaderboard',
      url: '/leaderboard/winnings'
    },
    {
      name: 'Time Spent Leaderboard',
      url: '/leaderboard/timespent'
    },
    // {
    //   name: 'Feedback Leaderboard',
    //   url: '/leaderboard/feedback'
    // },
    {
      name: 'Teams Leaderboard',
      url: '/leaderboard/topteams'
    },
    {
      name: 'Withdrawal Leaderboard',
      url: '/leaderboard/withdrawal'
    },
    ]
  },
  {
    name: 'Live Stock Fantasy',
    url: '/livestockfantasy',
    children: [
      {
        name: 'Create Candle',
        url: '/livestockfantasy/create-candle/0',
      },
      {
        name: 'Contest List',
        url: '/livestockfantasy/fixture',
      },
      {
        name: 'Contest Dashboard',
        url: '/livestockfantasy/contestlist',
      },
      {
        name: 'Merchandise',
        url: '/livestockfantasy/merchandise',
      },
      {
        name: 'Contest Template',
        url: '/livestockfantasy/contesttemplate',
      }, 
      {
        name: 'Stocks',
        url: '/livestockfantasy/stock',
      },
      {
        name: 'Stock Rates',
        url: '/livestockfantasy/manual_close',
      },
    ],
  }
  ]
};

items.items.push({
  name: 'Private Contest',
  url: '/private-contest',
  children: [{
    name: 'Contest Dashboard',
    url: '/private-contest/dashboard',
  }, {
    name: 'Setting',
    url: '/private-contest/setting',
  }],
})

items.items.push({
  name: 'Network Game',
  url: '/network-game',
  children: [{
    name: 'Network Contest',
    url: '/network-game/',
  },
  {
    name: 'Contest Report',
    url: '/network-game/contest-report',
  },
  {
    name: 'Commission History',
    url: '/network-game/commission-history',
  },
  {
    name: 'Account Statement',
    url: '/network-game/contest-details',
  },
  ],
});

items.items.push({
  name: 'Admin Role Management',
  url: '/manage-role',
  children: [{
    name: 'Add Role',
    url: '/admin-role/add-role',

  },
  {
    name: 'Manage Roles',
    url: '/manage-role',
  }
  ],
})


// if (WSManager.getKeyValueInLocal('ALLOW_COIN_MODULE') == 1) {
items.items.push({
  name: "Pick'em Tournament",
  url: '/pickem',
  children: [
    {
      name: 'Dashboard',
      url: '/pickem/picks',
    },
    {
      name: 'Create tournament',
      url: '/pickem/tournament',
    },    
    {
      name: 'League Management',
      url: '/pickem/league-management',
    }, 
    // {
    //   name: 'Dashboard',
    //   url: '/pickem/dashboard',
    // },
    // {
    //   name: 'Leagues/Players',
    //   url: '/pickem/leagues',
    // },
    // {
    //   name: 'View sports',
    //   url: '/pickem/view-sports',
    // },
    // {
    //   name: 'Merchandise',
    //   url: '/pickem/merchandise',
    // },
    // {
    //   name: 'Setprize',
    //   url: '/pickem/setprize',
    // },
  ],
});
// }


items.items.push({
  name: 'Sports Predictor',
  url: '/prediction',
  children: NC.SHOW_PREDICTION_CHILD == "1" ? [{
    name: 'Dashboard',
    url: '/prediction/dashboard',
  },
  {
    name: 'Fixture',
    url: '/prediction/fixture',
  },
  ] : null,
});



items.items.push({
  name: 'Open Predictor with Pool',
  url: '/open-predictor',
  children: NC.SHOW_OP_PREDICTION_CHILD == "1" ? [{
    name: 'Dashboard',
    url: '/open-predictor/dashboard',
  },
  {
    name: 'Category',
    url: '/open-predictor/category',
  },
  ] : null,
});


items.items.push({
  name: 'Open Predictor with Prize',
  url: '/prize-open-predictor',
  children: NC.SHOW_OP_PREDICTION_CHILD == "1" ? [{
    name: 'Dashboard',
    url: '/prize-open-predictor/dashboard',
  },
  {
    name: 'Category',
    url: '/prize-open-predictor/category',
  },
  ] : null,
});




items.items.push({
  name: 'Marketing',
  url: '/marketing',
  children: [{
    name: 'Referral Amount',
    url: '/marketing/referral_amount',
  },
  {
    name: 'Promo code',
    url: '/marketing/promo_code',
  },
  {
    name: 'Communication Dashboard',
    url: '/marketing/communication_dashboard',

  },
  {
    name: 'Communication Campaign',
    url: '/marketing/new_campaign',
  },
  {
    name: 'Manage Templates',
    url: '/marketing/custome-template',
  },
  // {
  //   name: 'Referral Setprize',
  //   url: '/marketing/referral_setprize',
  // },
  // {
  //   name: 'Referral Leaderboard',
  //   url: '/marketing/referral_leaderboard',
  // },
  {
    name: 'Marketing Leaderboard',
    url: '/marketing/marketing_leaderBoard',
  },
  ],
});


if (NC.ALLOW_DEAL) {
  items.items.push({
    name: 'Deals',
    url: '/deals',
    children: [{
      name: 'Deals',
      url: '/deals/deal_list',

    },],
  });
}

items.items.push({
  name: 'User Management',
  url: '/user_management',
  children: [{
    name: 'Manage User',
    url: '/manage_user',

  },
  {
    name: 'Add User',
    url: '/add_user',

  },
  {
    name: 'System users',
    url: '/system-users/userslist',
  },
  {
    name: 'Manage Rookie',
    url: '/user_management/viewrookie',
  },
  {
    name: 'Self Exclude',
    url: '/user_management/self-exclude',
  },
  {
    name: 'Manage H2H Challenge',
    url: '/user_management/h2h/',
    children: [{
      name: 'Dashboard View',
      url: '/user_management/h2h/dashboard/',
      class: 'nav-sub-child'
    }, {
      name: 'CMS',
      url: '/user_management/h2h/h2hcms/',
      class: 'nav-sub-child'
    },]
  },
  ],
})

items.items.push({
  name: 'Content Management',
  url: '/cms',
  children: [{
    name: 'Lobby Banner',
    url: '/cms/lobby_banner/',
  },
  {
    name: 'App Banner',
    url: '/cms/app_banner/',
  },
  {
    name: 'Manage Front Image',
    url: '/cms/background_image',
  },
  {
    name: 'CMS',
    url: '/cms/cms',
  },
  {
    name: 'Hub Page',
    url: '/cms/hub-page',
  },
  {
    name: 'Lobby',
    url: '/cms/lobby',
  },
  ],
})
items.items.push({
  name: 'Report',
  url: '/report/',
  children: [{
    name: 'User Report',
    url: '/report/user_report',
  },
  {
    name: 'User Money Paid',
    url: '/report/user_money_paid',
  },
  {
    name: 'User Deposit Amount',
    url: '/report/user_deposit_amount',
  },
  {
    name: 'Referral Report',
    url: '/report/referral_report',
  },
  {
    name: 'Contest Report',
    url: '/report/contest_report',
  },
  {
    name: 'Participant Report',
    url: '/report/participant',
  },
  // {
  //   name: 'Match Report',
  //   url: '/report/match_report',
  // },
  {
    name: 'Stock Report',
    url: '/report/stock_report',
  },
  ],
})
items.items.push({
  name: 'Manage Finance',
  url: '/finance',
  children: [{
    name: 'Withdrawal List',
    url: '/finance/withdrawal_list',
  },
  {
    name: 'Transaction List',
    url: '/finance/transaction_list',
  }
  ],
})

items.items.push({
  name: 'Coins',
  url: '/coins',
  children: [{
    name: 'Dashboard',
    url: '/coins/dashboard',
  },
  {
    name: 'Redeem',
    url: '/coins/redeem',
  },
  {
    name: 'Feedbacks',
    url: '/coins/promotions',
  },
  {
    name: 'Spin the wheel',
    url: '/coins/spinthewheel',
  },
  {
    name: 'Buy Coins',
    url: '/coins/buy-coins',
  },
  {
    name: 'Subscription',
    url: '/coins/subscription',
  },
  {
    name: 'Rewards Dashboard',
    url: '/coins/quiz/reward-dashboard/',
  },
  {
    name: 'Quiz',
    url: '/coins/quiz/',
    children: [{
      name: 'Quiz Dashboard',
      url: '/coins/quiz/dashboard/',
      class: 'nav-sub-child'
    }, {
      name: 'Create Quiz',
      url: '/coins/quiz/create-quiz/0',
      class: 'nav-sub-child'
    },
    {
      name: 'Question List',
      url: '/coins/quiz/questions/',
      class: 'nav-sub-child'
    },
    ]
  },
  {
    name: 'Reports',
    url: '/coins/reports/',
  },
  ]
});



items.items.push({
  name: 'Settings',
  url: '/settings',
  children: [
  {
    name: 'Add Merchandise',
    url: '/merchandise',
  },
  {
    name: 'Manage Avatars',
    url: '/manage-avatars'
  },
  {
    name: 'Deposit & Withdrawal',
    url: '/settings/minimum-withdrawal',
  },
  {
    name: 'Wallet',
    url: '/settings/wallet',
  },
  {
    name: 'Email',
    url: '/settings/email',
  },
  {
    name: 'Prize Cron',
    url: '/settings/prize_cron',
  },
  {
    name: 'Manage Reward',
    url: '/settings/reward',
  },
  {
    name: 'Banned states configuration',
    url: '/settings/banned-states',
  },
  {
    name: "What's New",
    url: "/settings/what's-new",
  },
   {
    name: "Payment Management",
    url: "/settings/PaymentManagement",
  },
  {
    name: "Mobile App",
    url: "/settings/MobileApp",
  }
  ],
})


items.items.push({
  name: 'Distributors',
  url: '/distributors',
  children: null,
})


items.items.push({
  name: 'Affiliate',
  url: '/affiliates',

    children: [

    {
      name: 'Affiliate',
       url: '/affiliates',
    },
    {
      name: 'Report',
      url: '/affiliate/Report',
    }
  ],
})
items.items.push({
  name: 'New Affiliate',
  url: '/new-affiliates'
})

items.items.push({
  name: 'Change Password',
  url: '/change-password',
})




items.items.push({
  name: 'Accounting',
  url: '/accounting',
  children: [
    // {
    //   name: 'GST Dashboard',
    //   url: '/accounting/dashboard',
    // },
    {
      name: 'GST Report',
      url: '/accounting/gst-reports',
    },
    {
      name: 'TDS Report',
      url: '/accounting/tds-reports',
    }
  ],
})

// For XP Module

items.items.push({
  name: 'XP Module',
  url: '/xp',
  children: [{
    name: 'Levels',
    url: '/xp/add-level',
  },
  {
    name: 'Rewards',
    url: '/xp/rewards',
  },
  {
    name: 'Activity',
    url: '/xp/activity',
  },
  {
    name: 'Leaderboard',
    url: '/xp/level-leaderboard',
  },
  {
    name: 'Activities Wise Leaderboard',
    url: '/xp/activity-leaderboard',
  }
  ],
})



export default items;
