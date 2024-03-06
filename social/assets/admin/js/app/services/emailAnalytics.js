/*
 |------------------------------------------------
 | Function for Get Data for Email Analytics Data
 |------------------------------------------------
 */
app.factory('emailAnalyticsChartData', function ($q, $http, appInfo) {
    return {
        emailAnalyticsData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('email_analytics');
            /* Make HTTP request for get Email Anlytics data */
            $http.post(base_url + 'admin_api/emailanalytics/email_analytics',reqData).success(function (data) {
                HideInformationMessage('email_analytics');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        emailAnalyticsLineChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('line_chart');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/emailanalytics/line_chart',reqData).success(function (data) {
                HideInformationMessage('line_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getEmailAnalyticsStatistcs: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('statistcs_list');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/emailanalytics/statistcs_list',reqData).success(function (data) {
                HideInformationMessage('statistcs_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getSentEmailsStatistcs: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('sent_emails_statistcs');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/emailanalytics/sent_emails_statistcs',reqData).success(function (data) {
                HideInformationMessage('sent_emails_statistcs');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getEmailsClickUrlList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('email_click_url_list');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/emailanalytics/email_click_url_list',reqData).success(function (data) {
                HideInformationMessage('email_click_url_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        resendCommunication: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('resend_communication');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/communication/resend_communication', reqData).success(function (data) {
                HideInformationMessage('resend_communication');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            fetchedData = deferred.promise;
            return fetchedData;
        }
    }
});