var GroupDesigner = function () {
};

GroupDesigner.prototype = {
    groupSide : null,
    sideChanged : null,
    defaultTextOpt : {},
    initialize: function (textSize, textFamily, sizeArray) {
        debugger;
        this.sizeArray = sizeArray;

        this.observeGroupNameAdd();
        this.observeNameOrNumberClick();
        this.observeGroupNumberAdd();
        this.observeAddAnotherButton();
        this.observeGroupTextFontChange();
        this.observeGroupTextSizeChange();
        this.observeValidation();
        
//        this.observeNameNumberChangeSide();
        
        
//        Groupdesigner.prototype.defaultTextOpt = {
//            fontFamily: textFamily,
//            fontSize: textSize,
//            text: '',
//        };
    },
    observeNameOrNumberClick: function () {
        jQuery("#isname , #isnumber").click(function (e) {

            if (this.name == 'isname') {
                var items = document.getElementsByClassName('group-name');
            }
            else if (this.name == 'isnumber') {
                var items = document.getElementsByClassName('group-number');
            }
            for (var i = 0; i < items.length; i++)
            {
                if (this.checked == true) {
                    jQuery(items[i]).removeAttr('disabled');
                } else {
                    jQuery(items[i]).attr('disabled', 'disabled');
                    ;
                }
            }
        });
    },
    observeGroupNameAdd: function () {
        jQuery('#isname').on('click', function (e) {
            debugger;
            this.productDesigner = ProductDesigner.prototype;
            var canvas = this.productDesigner.canvas;
            var allObj = canvas.getObjects();
            for (var i = 0; i < allObj.length; i++)
            {
                if (allObj[i].name == 'groupTabName')
                {
                    allObj[i].selectable = true;
                    canvas.setActiveObject(allObj[i]);
                }
            }

            var obj = canvas.getActiveObject();
            if (e.target.checked == true)
            {
                var text = 'EXAMPLE';
                var textObjectData = {
                    fontSize: jQuery('#group_font_size_selection').val(),
                    fontFamily: jQuery('#group_font_selection').val(),
                    top: 10,
                    fill: '#000000',
                    tab: "grouporder",
                    group_type: "name",
                    obj_side: this.productDesigner.data.product.images[this.productDesigner.currentProductColor][this.productDesigner.currentProduct].side,
                    isScaleObj: true
                };
                if (obj && (obj.type == 'text' && obj.name == 'groupTabName')) {
                    if (text != obj.getText()) {
                        var cmd = new UpdateCommand(canvas, obj, {text: text}, true);
                        cmd.exec();
                        History.prototype.push(cmd);
                        //this.groupSide[ProductDesigner.currentProduct] = 1;
                    }
                } else {
                    var textObject = new fabric.Text(text, textObjectData);
                    var cmd = new InsertCanvasObject(this.productDesigner, textObject, true, 'groupTabName');
                    cmd.exec();
                    History.prototype.push(cmd);
                    //this.groupSide[ProductDesigner.currentProduct] = 1;
                }

                if (jQuery('#group-table')[0].tBodies[0].rows.length == 0) {
                    getGroupHtml(this.sizeArray, jQuery('#add_another_button'));
                    this.observeValidation();
                    //this.setProductSizes();
                }

                var len = jQuery('#group-table')[0].tBodies[0].rows.length;
                for (var j = 0; j < len; j++) {

                    jQuery(jQuery('#group-table')[0].tBodies[0].rows[0].children[0].childNodes[0]).addClass('required-entry');
                    jQuery(jQuery('#group-table')[0].tBodies[0].rows[j].children[2].childNodes[0]).addClass('validate-select');
                    jQuery(jQuery('#group-table')[0].tBodies[0].rows[j].children[3].childNodes[0]).addClass('required-entry validate-digits');
                }
                //ProductDesigner.setPrintSize();
                //ProductDesigner.reloadPrice(len);

                jQuery('.product-side-img').each(function (index, val) {
                    var currentProduct = val.getAttribute('data-image_id');
                    if (ProductDesigner.currentProduct == currentProduct) {
                        jquery('#group_side_label').show();
                        $('group_side_value').innerHTML = this.productDesigner.data.product.images[ProductDesigner.currentProductColor][ProductDesigner.currentProduct].side;
                    }

                }.bind(this));
                //Groupdesigner.prototype.groupSide = this.productDesigner.currentDesignArea;

                jQuery('#name-number-content').removeClass('disabled');
                jQuery('#group_font_properties').removeClass('disabled');

            } else {

                var cmd = new RemoveCanvasObject(this.productDesigner, obj, 'groupTab');
                cmd.exec();

                var len = jQuery('#group-table')[0].tBodies[0].rows.length;
                for (var k = 0; k < len; k++) {

                    jQuery(jQuery('#group-table')[0].tBodies[0].rows[0].children[0].childNodes[0]).addClass('required-entry');
                }

                jQuery('.product-side-img').each(function (index, val) {
                    var currentProduct = val.getAttribute('data-image_id');
                    if (ProductDesigner.currentProduct == currentProduct) {
                        jQuery('#group_side_label').show();
                        jQuery('#group_side_value').innerHTML = ProductDesigner.prototype.data.product.images[ProductDesigner.currentProductColor][ProductDesigner.currentProduct].side;
                    }

                }.bind(this));
                //Groupdesigner.prototype.groupSide = this.productDesigner.currentDesignArea;

                if (jQuery('#isname').prop("checked") == false && jQuery('#isnumber').prop("checked") == false)
                {
                    //deleteAllRow();
                    jQuery('#group_side_label').hide();
                    //Groupdesigner.prototype.groupSide = null;
                    jQuery('#name-number-content').addClass('disabled');
                    jQuery('#group_font_properties').addClass('disabled');
                }

                var len = jQuery('#group-table')[0].tBodies[0].rows.length;
                //ProductDesigner.setPrintSize();
                //ProductDesigner.reloadPrice(len);

            }
        });
    },
    observeGroupNumberAdd: function () {
        jQuery('#isnumber').on('click', function (e) {
            this.productDesigner = ProductDesigner.prototype;
            var canvas = this.productDesigner.canvas;
            var allObj = canvas.getObjects();
            for (var i = 0; i < allObj.length; i++)
            {
                if (allObj[i].name == 'groupTabNumber')
                {
                    allObj[i].selectable = true;
                    canvas.setActiveObject(allObj[i]);
                }
            }

            var obj = canvas.getActiveObject();



            if (e.target.checked == true)
            {
                var text = '00';
                var textObjectData = {
                    fontSize: jQuery('#group_font_size_selection').val(),
                    fontFamily: jQuery('#group_font_selection').val(),
                    top: 60,
                    fill: '#000000',
                    tab: "grouporder",
                    group_type: "number",
                    obj_side: this.productDesigner.data.product.images[this.productDesigner.currentProductColor][this.productDesigner.currentProduct].side,
                    isScaleObj: true
                };
                if (obj && (obj.type == 'text' && obj.name == 'groupTabNumber')) {
                    if (text != obj.getText()) {
                        var cmd = new UpdateCommand(canvas, obj, {text: text}, true);
                        cmd.exec();
                        History.prototype.push(cmd);
                        //this.groupSide[ProductDesigner.currentProduct] = 1;
                    }
                } else {
                    var textObject = new fabric.Text(text, textObjectData);
                    var cmd = new InsertCanvasObject(this.productDesigner, textObject, true, 'groupTabNumber');
                    cmd.exec();
                    History.prototype.push(cmd);
                    //this.groupSide[ProductDesigner.currentProduct] = 1;
                }

                if (jQuery('#group-table')[0].tBodies[0].rows.length == 0) {
                    getGroupHtml(this.sizeArray, jQuery('#add_another_button'));
                    this.observeValidation();
                    //this.setProductSizes();
                }

                var len = jQuery('#group-table')[0].tBodies[0].rows.length;
                for (var j = 0; j < len; j++) {

                    jQuery(jQuery('#group-table')[0].tBodies[0].rows[0].children[0].childNodes[0]).addClass('required-entry');
                    jQuery(jQuery('#group-table')[0].tBodies[0].rows[j].children[2].childNodes[0]).addClass('validate-select');
                    jQuery(jQuery('#group-table')[0].tBodies[0].rows[j].children[3].childNodes[0]).addClass('required-entry validate-digits');
                }

                //ProductDesigner.setPrintSize();
                //ProductDesigner.reloadPrice(len);
                jQuery('.product-side-img').each(function (index, val) {
                    var currentProduct = val.getAttribute('data-image_id');
                    if (ProductDesigner.currentProduct == currentProduct) {
                        jquery('#group_side_label').show();
                        $('group_side_value').innerHTML = this.productDesigner.data.product.images[ProductDesigner.currentProductColor][ProductDesigner.currentProduct].side;
                    }

                }.bind(this));
                //Groupdesigner.prototype.groupSide = this.productDesigner.currentDesignArea;

                jQuery('#name-number-content').removeClass('disabled');
                jQuery('#group_font_properties').removeClass('disabled');

            } else {
                var cmd = new RemoveCanvasObject(this.productDesigner, obj, 'groupTab');
                cmd.exec();

                var len = jQuery('#group-table')[0].tBodies[0].rows.length;
                for (var k = 0; k < len; k++) {

                    jQuery(jQuery('#group-table')[0].tBodies[0].rows[0].children[0].childNodes[0]).addClass('required-entry');
                }

                jQuery('.product-side-img').each(function (index, val) {
                    var currentProduct = val.getAttribute('data-image_id');
                    if (ProductDesigner.currentProduct == currentProduct) {
                        jQuery('#group_side_label').show();
                        jQuery('#group_side_value').innerHTML = ProductDesigner.prototype.data.product.images[ProductDesigner.currentProductColor][ProductDesigner.currentProduct].side;
                    }

                }.bind(this));
                //Groupdesigner.prototype.groupSide = this.productDesigner.currentDesignArea;

                if (jQuery('#isname').prop("checked") == false && jQuery('#isnumber').prop("checked") == false)
                {
                    debugger;
                    //deleteAllRow();
                    jQuery('#group_side_label').hide();
                    //Groupdesigner.prototype.groupSide = null;
                    jQuery('#name-number-content').addClass('disabled');
                    jQuery('#group_font_properties').addClass('disabled');
                }

                var len = jQuery('#group-table')[0].tBodies[0].rows.length;
                //ProductDesigner.setPrintSize();
                //ProductDesigner.reloadPrice(len);

            }
        });
    },
    observeValidation: function () {
        
        jQuery('.group-name').each(function (index, val) {
            
            jQuery('#name_0').on('keyup', function (e, ele) {
                
                if (e.target.value != null) {
                    if (e.target.hasAttribute('validation-failed'))
                    {
                        e.target.removeClass('validation-failed');
                        e.element().next().remove();
                    }
                }
                debugger;
                Groupdesigner.updateNameText('#name_0');
            }.bind(this));

            jQuery(val.id).on('focus', function (e, ele) {
                Groupdesigner.prototype.updateNameText(index);
            });
        });

        jQuery('.group-number').each(function (index, val) {

            jQuery(val.id).on('keyup', function (e, ele) {

                if (e.element().value != null) {
                    if (e.element().hasClassName('validation-failed'))
                    {
                        e.element().removeClassName('validation-failed');
                        e.element().next().remove();

                    }
                }
                //Groupdesigner.updateNumberText(index);
            }.bind(this));

            jQuery(index.id).on('focus', function (e, ele) {
                Groupdesigner.updateNumberText(index);
            });
        });

        jQuery('.group-size').each(function (index, val) {

            jQuery(index.id).on('change', function (e, ele) {

                if (e.element().value != null) {
                    if (e.element().hasClassName('validation-failed'))
                    {
                        e.element().removeClassName('validation-failed');
                        e.element().next().remove();
                    }

                    Groupdesigner.observeSizeQty();
                }
            });
        });
        jQuery('.group-qty').each(function (index, val) {

            jQuery(index.id).on('focus', function (e, ele) {

                e.element().placeholder = '';

            }.bind(this));

            jQuery(index.id).on('blur', function (e, ele) {

                e.element().placeholder = 0;

            });

            jQuery(index.id).on('keyup', function (e, ele) {

                if (e.element().value != null) {
                    if (e.element().hasClassName('validation-failed'))
                    {
                        e.element().removeClassName('validation-failed');
                        e.element().next().remove();
                    }
                }
                //ProductDesigner.reloadPrice();
            });
        });
    },
        
    updateNameText: function (index) {
        
        this.productDesigner = ProductDesigner.prototype;
        var canvas = this.productDesigner.canvas;
        var allObj = canvas.getObjects();
        for (var i = 0; i < allObj.length; i++)
        {
            if (allObj[i].name == 'groupTabName')
            {

                if (jQuery(index).val() == '' && allObj[i].text.length == 1)
                {
                    var text = jQuery(index).val();
                    var textObjectData = {
                        fontSize: allObj[i].fontSize,
                        fontFamily: jQuery('#group_font_selection').val(),
                        fill: '#000000',
                        top: allObj[i].top,
                        left: allObj[i].left,
                        obj_side: this.productDesigner.data.product.images[this.productDesigner.currentProductColor][this.productDesigner.currentProduct].side,
                        tab: "grouporder"
                    };

                    var cmd = new UpdateCommand(canvas, allObj[i], {text: text}, true);
                    cmd.exec();
                }

                if (allObj[i].text != jQuery(index).val() && jQuery(index).val() != '') {

                    var text = jQuery(index).val();
                    var textObjectData = {
                        fontSize: allObj[i].fontSize,
                        fontFamily: jQuery('#group_font_selection').val(),
                        fill: '#000000',
                        top: allObj[i].top,
                        left: allObj[i].left,
                        obj_side: this.productDesigner.data.product.images[this.productDesigner.currentProductColor][this.productDesigner.currentProduct].side,
                        tab: "grouporder"
                    };

                    var cmd = new UpdateCommand(canvas, allObj[i], {text: text}, true);
                    cmd.exec();
                }
            }

            if (allObj[i].name == 'groupTabNumber')
            {
                if (allObj[i].text != index.up().next().down().value && index.up().next().down().value != '') {

                    var text = index.up().next().down().value;
                    var textObjectData = {
                        fontSize: allObj[i].fontSize,
                        fontFamily: $('group_font_selection').value,
                        fill: '#000000',
                        top: allObj[i].top,
                        left: allObj[i].left,
                        obj_side: window.ProductDesigner.data.product.images[window.ProductDesigner.currentProductColor][window.ProductDesigner.currentProduct].side,
                        tab: "grouporder"
                    };

                    var cmd = new UpdateCommand(canvas, allObj[i], {text: text}, true);
                    cmd.exec();
                }
            }
        }
    },
    
    observeGroupTextFontChange: function () {

        jQuery('#group_font_selection').on('change', function (e) {
            
            this.productDesigner = ProductDesigner.prototype;
            var canvas = this.productDesigner.canvas;
            var obj = canvas.getActiveObject();
            if (obj && obj.type == 'text' && (obj.tab == 'grouporder')) {
                var cmd = new UpdateCommand(canvas, obj, {fontFamily: jQuery('#group_font_selection').val()}, true);
                cmd.exec();
                History.prototype.push(cmd);
            }
        }.bind(this));
    },
    
    observeGroupTextSizeChange: function () {
        jQuery('#group_font_size_selection').on('input', function (e) {


            this.productDesigner = ProductDesigner.prototype;
            var canvas = this.productDesigner.canvas;
            var obj = canvas.getActiveObject();

            if (obj && obj.type == 'text' && (obj.tab == 'grouporder')) {
                var cmd = new UpdateCommand(canvas, obj, {fontSize: jQuery('#group_font_size_selection').val()}, true);
                cmd.exec();
                History.prototype.push(cmd);
            }

        }.bind(this));
    },
     observeAddAnotherButton: function () {

        jQuery('#add_another_button').on('click', function (e) {
            getGroupHtml(this.sizeArray, jQuery('#add_another_button'));
            this.observeValidation();
            //this.setProductSizes();
        }.bind(this));
    }
};