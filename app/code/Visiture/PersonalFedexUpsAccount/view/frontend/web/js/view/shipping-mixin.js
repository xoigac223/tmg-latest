define([
    'jquery',
    'uiRegistry',
    'mageUtils',
    'ko',
    'Visiture_PersonalFedexUpsAccount/js/action/set-customer-shipping-information',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator'
], function ($, registry, utils, ko, setCustomerShippingInformationAction, setShippingInformationAction, stepNavigator) {
    return function (target) {

        return target.extend({
            defaults: {
                template: 'Visiture_PersonalFedexUpsAccount/shipping'
            },

            showAdditionalDataMethodCode:function()
            {
                var key = 'checkout.steps.shipping-step.shippingAddress.shippingMethodAdditional.customer_shipping';
                var customerShippingElement = registry.get(key);
                var methodCode = ko.utils.unwrapObservable(customerShippingElement.methodCode);
                return methodCode;
            },

            isshowAdditionalData:function()
            {
                var key = 'checkout.steps.shipping-step.shippingAddress.shippingMethodAdditional.customer_shipping';
                var customerShippingElement = registry.get(key);
                var isSelected = ko.utils.unwrapObservable(customerShippingElement.isSelected);
                return isSelected;
            },

            setShippingInformation: function () {
                var key = 'checkout.steps.shipping-step.shippingAddress.shippingMethodAdditional.customer_shipping';
                var customerShippingElement = registry.get(key);
                var isSelected = ko.utils.unwrapObservable(customerShippingElement.isSelected);
                
                if (this.validateShippingInformation()) {
                    if(isSelected){
                        setCustomerShippingInformationAction().done(
                            function (response) {
                                if(response.success)
                                {
                                    jQuery(".customer-shipping-message").hide();
                                    customerShippingElement.custommsg('');
                                    setShippingInformationAction().done(
                                        function () {
                                            stepNavigator.next();
                                        }
                                    );
                                }
                                else
                                {
                                    customerShippingElement.custommsg(response.msg);
                                    jQuery(".customer-shipping-message").show();
                                    return false;
                                }
                            }
                        );
                    }
                    else
                    {
                        setShippingInformationAction().done(
                            function () {
                                stepNavigator.next();
                            }
                        );
                    }
                }
            },
            validateShippingInformation: function () {
                var key = 'checkout.steps.shipping-step.shippingAddress.shippingMethodAdditional.customer_shipping';
                var customerShippingElement = registry.get(key);
                var isSelected = ko.utils.unwrapObservable(customerShippingElement.isSelected);
                if (customerShippingElement && isSelected) {
                    var personal_ac_number = $('[name="personal_ac_number"]').val();
                    var personal_ac_type = $('[name="personal_ac_type"]').val();

                    if (utils.isEmpty(personal_ac_number) || utils.isEmpty(personal_ac_type)) {
                        customerShippingElement.custommsg(
                            $.mage.__('Please enter a valid account number and type.')
                        );
                        jQuery(".customer-shipping-message").show();
                        return false;
                    }
                    jQuery(".customer-shipping-message").hide();
                    customerShippingElement.acnumber(personal_ac_number);
                    customerShippingElement.actype(personal_ac_type);
                    customerShippingElement.custommsg('');
                }
                return this._super();
            }
        });
    }
});

