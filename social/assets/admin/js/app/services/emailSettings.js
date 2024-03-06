/*
 |--------------------------------------------------------------------------
 | Function for Get Data for email setting serivce
 | getSmtpEmailList
 |--------------------------------------------------------------------------
 */
app.factory('emailSettingData', function ($q, $http, appInfo) {
    return {
        getSmtpSettingEmailList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('emailsetting');
            /* Make HTTP request for smtp setting list */
            $http.post(base_url + 'admin_api/emailsetting', reqData).success(function (data) {
                HideInformationMessage('emailsetting');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },        
        CreateSmtpSetting: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('create_smtp_setting');
            /* Make HTTP request for create smtp setting */
            $http.post(base_url + 'admin_api/emailsetting/create_smtp_setting', reqData).success(function (data) {
                HideInformationMessage('create_smtp_setting');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },        
        getSmtpSettingDetails: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_smtp_setting_details');
            /* Make HTTP request for smtp setting details */
            $http.post(base_url + 'admin_api/emailsetting/get_smtp_setting_details', reqData).success(function (data) {
                HideInformationMessage('get_smtp_setting_details');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateSmtpSettingStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('emails_update_status');
            /* Make HTTP request for update status */
            $http.post(base_url + 'admin_api/emailsetting/update_status', reqData).success(function (data) {
                HideInformationMessage('emails_update_status');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getEmailTypeList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('emailtype');
            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'admin_api/emailsetting/emailtype', reqData).success(function (data) {
                HideInformationMessage('emailtype');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateSmtpEmailsStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('update_emailtype_status');
            /* Make HTTP request for update status */
            $http.post(base_url + 'admin_api/emailsetting/update_emailtype_status', reqData).success(function (data) {
                HideInformationMessage('update_emailtype_status');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getEmailDataService: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('email_setting_param');
            $http.post(base_url + 'admin_api/emailsetting/email_setting_param.json', reqData).success(function (data) {
                HideInformationMessage('email_setting_param');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateEmailDetails: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('update_emailtype_details');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/emailsetting/update_emailtype_details', reqData).success(function (data) {
                HideInformationMessage('update_emailtype_details');
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
