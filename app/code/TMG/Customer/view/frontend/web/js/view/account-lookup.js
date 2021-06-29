define([
    'ko',
    'jquery',
    'uiComponent',
    'mage/storage',
    'mage/url',
    'TMG_Customer/js/action/get-account-email-lookup',
    'TMG_Customer/js/action/get-account-id-lookup',
    'Magento_Customer/js/customer-data',
    'mage/validation',
    'mage/translate'
], function (
         ko,
         $,
         Component,
         storageManager,
         urlBuilder,
         getAccountEmailLookup,
         getAccountIdLookup,
         customerData
     ){

    'use strict';
    return Component.extend({

        // Observables
        showLoader: ko.observable(true),
        showEmailLookup: ko.observable(true),
        showIdLookup: ko.observable(true),

        // displayContent: ko.observable(true),

        initialize: function () {
            this._super();
            this.isLoading(true);
            this.initLookupForm();
            this.initViews();
            this.isLoading(false);
        },

        initViews: function()
        {
            // Vars
            this.$registerForm = $('#form-validate');
            this.$emailLookupField = $('#tmg-account-email-lookup');
            // Visibility
            this.showEmailLookup(true);
            this.showIdLookup(false);
            this.showRegisterForm(false);
        },

        showRegisterForm: function(show)
        {
            if(show) {
                this.$registerForm.show();
            } else {
                this.$registerForm.hide();
            }
        },

        isLoading: function(status)
        {
            // console.log('isLoading',status);
            this.showLoader(status);
            return this;
        },

        initLookupForm: function()
        {
            // Init validation
            this.$dataForm = $('#tmg-account-lookup-form');
            var ignore = null;

            this.$dataForm.mage('validation', {
                ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
            }).find('input:text').attr('autocomplete', 'off');

            return this;
        },


        /////////
        // Events

        onEmailLookupClick: function(ev)
        {
            // Validate Email
            if(this.$dataForm.validation('isValid')) {
                // Show Loader
                this.isLoading(true);
                // Call Ajax
                getAccountEmailLookup(this.$emailLookupField.val(),this);
            }
            return this;
        },

        onIdLookupClick: function($widget,event)
        {
            var $element = $(event.currentTarget),
                id = $element.attr('id'),
                data = {email: this.$emailLookupField.val(),accountId: $element.parent().find('input.input-text').val()};

            switch (id) {
                case 'tmg-magnet-account-id-lookup-button':
                    data.accountType = 'magnet'
                    break;
                case 'tmg-asi-account-id-lookup-button':
                    data.accountType = 'asi';
                    break;
                case 'tmg-ppai-account-id-lookup-button':
                    data.accountType = 'ppai';
                    break;
                case 'tmg-sage-account-id-lookup-button':
                    data.accountType = 'sage';
                    break;
                default:
                    break;
            }

            if(this.$dataForm.validation('isValid')) {
                // Show Loader
                this.isLoading(true);
                // Call Ajax
                getAccountIdLookup(data,this);
            }

        },

        onNotAMemberClick: function()
        {
            this.showLoader(true);
            this.showEmailLookup(false);
            this.showIdLookup(false);
            this.showRegisterForm(true);
            this.showLoader(false);
        },

        onGoBackClick: function()
        {
            this.showLoader(true);
            this.showEmailLookup(true);
            this.showIdLookup(true);
            this.showRegisterForm(false);
            this.showLoader(false);
        },


        //////////////
        // Handlers

        emailLookupResponseHandler: function(params,response)
        {
            // Main Error
            if(response.error) {
                this.emailLookupShowError(response.message);
                return;
            }

            var result = response.result;

            // OK
            if(result.status && result.has_tmg_account) {
                this.showIdLookup(true);
                return this;
            }
            // Magento Account Exists
            if(!result.status && (result.has_magento_account || result.has_tmg_login)) {
                this.emailLookupMagentoAccountExistError(params, response);
                return this;
            }
            // Account Doesn't Exist
            this.showEmailLookup(false);
            this.showIdLookup(false);
            // Preset Email
            $('#email_address').val(params.email);
            this.showRegisterForm(true);
            return this;

        },

        /**
         *
         * @param params
         * @param response
         * @returns {exports}
         */
        idLookupResponseHandler: function(params, response)
        {
            // Main Error
            if(response.error) {
                this.idLookupShowError(response.message);
                return this;
            }

            var result = response.result;

            // // OK
            if(result.status && result.data) {

                var preparedData = this.mapAccountLookupData(params, result.data);

                // Fill
                this.fillFormFields(preparedData);

                // Handle Views
                this.showEmailLookup(false);
                this.showIdLookup(false);
                this.showRegisterForm(true);

            // @todo ADD Login lready Exist Validation & Error Message
            // } else if (???) {

            } else {
                this.idLookupShowError(result.message);
            }

            return this;

        },

        //////////////////////
        // FORM DATA Handling

        mapAccountLookupData: function(params, response)
        {
            var countryId = this.getCountryId(response.Country)
            var regionData = this.getRegionData(response.State,countryId);

            var result = {
                'personal_information': {
                    'firstname': response.FirstName,
                    'lastname': response.LastName,
                    'tmg_telephone': response.WorkPhone,
                    'tmg_fax': response.Fax,
                    // Hidden Fields
                    'tmg_account_id': response.AccountID,
                    'tmg_encrypt_account': response.EncryptAccount,
                },
                'login_information' : {
                    'email': params.email,
                },
                'company_information': {
                    'tmg_company_name': response.CompanyName,
                    'tmg_magnet_account_id': response.MagnetAccount,
                    'tmg_asi_account_id': response.ASIAccount,
                    'tmg_ppai_account_id': response.PPAIAccount,
                    'tmg_sage_account_id': response.SAGEAccount,
                },
                'contact_information': {
                    'create_address': true,
                    'telephone': response.WorkPhone,
                    'street_1': response.Address1,
                    'street_2': response.Address2,
                    // 'street[]': response.xxx,
                    'country_id': countryId,
                    'region_id': regionData.id,
                    'region': regionData.name,
                    'city': response.City,
                    'postcode': response.ZipCode,
                    // Hidden Fields
                    'default_billing': response.DefaultBillingAddress,
                    'default_shipping': response.DefaultShippingAddress,
                },
            };

            return result;
        },

        fillFormFields: function(data)
        {
            $.each(data, function(sectionName,sectionData){
                $.each(sectionData,function(fieldName,fieldValue) {

                    // Skip RegionId
                    if(fieldName == 'region_id') {
                        return;
                    }
                    // Find Selector
                    var _selector =  "[name='" + fieldName + "']";
                    // console.log('SECTION: ',sectionName, 'FIELD: ', $(_selector).length ,fieldName, ' -> ', fieldValue);
                    if ($(_selector).length == 0) {
                        _selector =  "#" + fieldName;
                        // console.log('SECTION: ',sectionName, 'FIELD: ', $(_selector).length ,fieldName, ' -> ', fieldValue);
                    }
                    // Set Value
                    $(_selector).val(fieldValue);
                    // Fire Next Events
                    if(fieldName == 'country_id') {
                        // console.log('______ CHANGE FIRED _______')
                        $(_selector).trigger('change');
                    }

                });

                if(sectionName == 'contact_information') {
                    $("[name='region_id']").val(sectionData.region_id);
                }

            });

        },

        getRegionData: function(regionName,countryId)
        {
            var result = {
                'id': false,
                'code': false,
                'name': regionName,
            };

            if(!(countryId in this.regionJson)) {
                return result;
            }

            $.each(this.regionJson[countryId], function(regionId,regionData){
                if(regionData.name.toLowerCase() == regionName.toLowerCase()) {
                    result.id = regionId;
                    result.code = regionData.code;
                    result.name = regionData.name;
                    return false;
                }
            });

            return result;

        },

        getCountryId: function(countryName)
        {
            return (countryName.toLowerCase() == 'can') ? 'CA' : 'US';
        },

        ///////////////////
        // ERROR Handlers

        /**
         * @param err
         */
        emailLookupErrorHandler: function(err)
        {
            return this.emailLookupShowError(err);
        },

        /**
         * @param data
         * @param params
         * @returns {*}
         */
        emailLookupMagentoAccountExistError: function(data, params)
        {
            var _message = 'There is already an account with this email address. If you are sure that it is your email address please '
                + '<a href="' + urlBuilder.build('customer/account/login/') + '">login</a> '
                + 'or click <a href="' + urlBuilder.build('customer/account/forgotpassword/') + '">here</a> '
                + 'to get your password and access your account.';
            return this.showWarning(_message);
        },

        /**
         *
         * @param error
         * @returns {*|exports}
         */
        emailLookupShowError: function(error)
        {
            return this.showError(error);
        },

        /**
         *
         * @param error
         * @returns {*|exports}
         */
        idLookupErrorHandler: function(params,error)
        {
            return this.showError(error);
        },

        /**
         * @param error
         */
        idLookupShowError: function(error)
        {
            return this.showError(error);
        },

        /**
         *
         */
        clearMessages: function()
        {
            customerData.set('messages',{'error': null});
            customerData.set('messages',{'warning': null});
            customerData.set('messages',{'success': null});
            return this
        },

        showMessage: function(type,message)
        {
            // this.clearMessages();
            customerData.set('messages', {
                messages: [{
                    type: type,
                    text: message
                }]
            });
            return this;
        },

        /**
         * @param error
         * @returns {exports}
         */
        showSuccess: function(message)
        {
            return this.showMessage('success',message);
        },

        /**
         *
         * @param message
         * @returns {*}
         */
        showError: function(message) {
            return this.showMessage('error',message);
        },

        /**
         *
         * @param message
         * @returns {*}
         */
        showWarning: function(message) {
            return this.showMessage('warning',message);
        },

    });
});
