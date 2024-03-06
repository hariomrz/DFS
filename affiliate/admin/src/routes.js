import React from 'react';
import Loadable from 'react-loadable'
import DefaultLayout from './containers/DefaultLayout';

function Loading() {
  return <div>Loading...</div>;
}

const AffiliateLanding = Loadable({
  loader: () => import('./views/Affiliate/AffiliateLanding'),
  loading: Loading,
});
const CreateAffiliate = Loadable({
  loader: () => import('./views/Affiliate/CreateAffiliate'),
  loading: Loading,
});
const ViewAffiliateDetail = Loadable({
  loader: () => import('./views/Affiliate/ViewAffilliateDetail'),
  loading: Loading,
});
const TrackURl = Loadable({
  loader: () => import('./views/Affiliate/TracingUrL'),
  loading: Loading,
});
const TrackURlDetail = Loadable({
  loader: () => import('./views/Affiliate/TrackUrlDetail'),
  loading: Loading,
});
const CreateCampaign = Loadable({
  loader: () => import('./views/Affiliate/CreateCampaign'),
  loading: Loading,
});
const UserDashboard = Loadable({
  loader: () => import('./views/Affiliate/UserLoginViews/Dashboard'),
  loading: Loading,
});
const UserAffillilateDetail = Loadable({
  loader: () => import('./views/Affiliate/UserLoginViews/UserAffilliateDetail'),
  loading: Loading,
});
const CampaignUserDetail = Loadable({
  loader: () => import('./views/Affiliate/UserLoginViews/CampaignUserDetail'),
  loading: Loading,
});

const routes = [
  { path: '/', name: 'Home', component: DefaultLayout, exact: true },
  { path: '/affiliate-list', name: 'Affiliate Landing', component: AffiliateLanding , exact: true},
  { path: '/create-affilate', name: 'Create Affiliate', component: CreateAffiliate, exact: true },
  { path: '/view-affilate/:affiliate_id', name: 'View Affiliate', component: ViewAffiliateDetail },
  { path: '/track-url/:campaign_id', name: 'Track Url', component: TrackURl },
  { path: '/track-url-detail', name: 'Track Url Detail', component: TrackURlDetail },
  { path: '/create-campaign/:affiliate_id', name: 'Create Campaign', component: CreateCampaign },
  { path: '/user-affiliate-list', name: 'User Dashboard', component: UserDashboard },
  { path: '/user-affiliate-detail/:campaign_id', name: 'User Affilliate Detail', component: UserAffillilateDetail },
  { path: '/user-campaign-detail/:user_id', name: 'User Campaign Detail', component: CampaignUserDetail },
];

export default routes;
