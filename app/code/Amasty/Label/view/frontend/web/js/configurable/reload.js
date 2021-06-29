define([
    'jquery'
], function ($) {
    return function (imageContainer, productId, reloadUrl, inProductList) {
        if (!this.labels) {
            this.labels = [];
        }

        if (this.labels.indexOf(productId) === -1) {
            this.labels.push(productId);
            $.ajax({
                url: reloadUrl,
                data: {
                    product_id: productId,
                    in_product_list: inProductList
                },
                method: 'GET',
                cache: true,
                dataType: 'json',
                showLoader: false
            }).done(function (data) {
                if (data.labels) {
                    imageContainer.last().after(data.labels);
                }
            });
        }

        imageContainer.find('.amasty-label-for-' + productId).show();
    }
});
