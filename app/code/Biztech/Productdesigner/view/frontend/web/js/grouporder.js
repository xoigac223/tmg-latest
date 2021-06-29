/*(function($){
        $(document).ready(function() {
                $("#isname , #isnumber").click(function (e){

                        if(this.name == 'isname'){

                            var items = document.getElementsByClassName('group-name');

                        }
                        else if(this.name == 'isnumber'){
                            var items = document.getElementsByClassName('group-number');
                        }
                        for (var i = 0; i < items.length; i++)
                        {
                            if(this.checked == true){
                                items[i].removeAttribute('disabled');
                            }else{
                                items[i].setAttribute('disabled', 'disabled');;
                            }
                        }
                });
        });
})(jQuery);*/



function getGroupHtml(sizeArray,element,group_order_details)
{

    
    document.getElementById('group-table-content').style.display = 'block';
    var tBody =  document.getElementById('group-table').tBodies[0];
    var rowCount =  tBody.rows.length;
    if(rowCount != 0)
        {     var lastRow =  tBody.getElementsByTagName('tr')[rowCount - 1];
        var test = lastRow.id.split('-');
        rowCount = parseInt(test[3]);
        rowCount++;
    }


    var isName = document.getElementById('isname').checked;
    var isNumber = document.getElementById('isnumber').checked;
    var trElement = document.createElement("tr");
    trElement.id = 'group-table-row-' + rowCount;

    var nametdElement = document.createElement("td");
	nametdElement.className = 'input-name-class';
    var name = document.createElement("input");
    name.name = 'name_' + rowCount;
    name.id = 'name_' + rowCount;
    name.className = 'input-text group-name';
    if(isName == false){
        name.setAttribute('disabled', 'disabled');
    }
    if(group_order_details != null){
      name.value = group_order_details.name;
    }
    nametdElement.appendChild(name);

    var numbertdElement = document.createElement("td");
	numbertdElement.className = 'input-number-class';
    var number = document.createElement("input");
    number.name = 'number_' + rowCount;
    number.id = 'number_' + rowCount;
    number.className = 'input-text group-number';
    if(isNumber == false){
        number.setAttribute('disabled', 'disabled');
    }
    if(group_order_details != null){
      number.value = group_order_details.number;
    }
    numbertdElement.appendChild(number);

    var sizetdElement = document.createElement("td");
	
    var select = document.createElement("select");
    select.name = 'size_' + rowCount;
    select.id = 'size_' + rowCount;
    select.className = 'group-size';

    var option = document.createElement("option");
    option.textContent = '';
    option.value = '';
    select.appendChild(option);
    

    
    sizeArray = JSON.parse(sizeArray)
    //sizeArray = sizeArray.evalJSON();
    for(var i = 0; i < sizeArray.length; i++) {
        var opt = sizeArray[i];
        var option = document.createElement("option");
        option.textContent = opt.label;
        option.value = opt.value_index;
        option.setAttribute("data-products",opt.products);
        select.appendChild(option);
    }

     if(group_order_details != null){
      select.value = group_order_details.size;
    }
    sizetdElement.appendChild(select);
     if(sizeArray.length == 0)
    {
        sizetdElement.style.display = "none";        
    }
    /*var qtytdElement = document.createElement("td");
    var qty = document.createElement("input");
    qty.name = 'qty_' + rowCount;
    qty.id = 'qty_' + rowCount;
    qty.className = 'input-text group-qty qty';

    if(group_order_details != null){
      qty.value = group_order_details.qty;
    }else{
    qty.value = '1';
    }

    qtytdElement.appendChild(qty);*/


    var deletetdElement = document.createElement("td");
    var button = document.createElement("button");
    button.id = 'delete_row_' + rowCount;
    button.className ='button delete_row_buttons'
    button.setAttribute('onclick', 'deleteRow('+rowCount+')');
    button.setAttribute('title', 'Delete');
    var spanEle =     document.createElement("span");
    spanEle.className = 'sprite ico-delete';
    var text = document.createTextNode('Delete');
    spanEle.appendChild(text);
    button.appendChild(spanEle);
    deletetdElement.appendChild(button);

    trElement.appendChild(nametdElement);
    trElement.appendChild(numbertdElement);
    trElement.appendChild(sizetdElement);
    //trElement.appendChild(qtytdElement);
    trElement.appendChild(deletetdElement);
    tBody.appendChild(trElement);
    
    jQuery('#add_another_button').show();
    element[0].childNodes[0].childNodes[0].innerHTML = 'Add';


    var len = jQuery('#group-table')[0].tBodies[0].rows.length;

    
    for(var j = 0;j<len;j++){

        if(jQuery('#isname').is(':checked')){
            jQuery(jQuery('#group-table')[0].tBodies[0].rows[0].children[0].childNodes[0]).addClass('required-entry');
        }
        if(jQuery('#isnumber').is(':checked')){
            jQuery(jQuery('#group-table')[0].tBodies[0].rows[j].children[1].childNodes[0]).addClass('required-entry');
        }
        jQuery(jQuery('#group-table')[0].tBodies[0].rows[j].children[2].childNodes[0]).addClass('validate-select');
        jQuery(jQuery('#group-table')[0].tBodies[0].rows[j].children[3].childNodes[0]).addClass('required-entry validate-digits');
    }
    

    //Groupdesigner.observeValidation();

    jQuery('#size-chart').addClass('disabled');

    //ProductDesigner.reloadPrice(len);
    /*Groupdesigner.observeNameNumberObject()
    Groupdesigner.observeGroupNameAdd();
    Groupdesigner.observeGroupNumberAdd();
    if(rowCount > 0)
    {
    Groupdesigner.observeCanvasClear();
    }*/



}


function deleteRow(rowCount)
{
    var tBody =  document.getElementById('group-table').tBodies[0];
    var trElement = document.getElementById('group-table-row-' + rowCount);
    tBody.removeChild(trElement);
    var rowLength = tBody.rows.length;
    if(rowLength == 0)
        {
        document.getElementById('group-table-content').style.display = 'none';
        jQuery('#add_another_button').hide();
        document.getElementById('add_another_button').childNodes[0].childNodes[0].innerHTML = 'Add';
        jQuery('#isname').prop('checked',false);
        jQuery('#isnumber').prop('checked',false);        
        jQuery('#size-chart').removeClass('disabled');

        this.productDesigner = ProductDesigner.prototype;
        var canvas = this.productDesigner.canvas;
        var allObj = canvas.getObjects();
        for(var i=0; i<allObj.length; i++)
        {
            if(allObj[i].name == 'groupTabName')
                {
                allObj[i].selectable = true;
                canvas.setActiveObject(allObj[i]);
            }
        }
        var obj = canvas.getActiveObject();
        if(obj){
            var cmd = new RemoveCanvasObject(this.productDesigner, obj,undefined,'groupTab');
            cmd.exec();
        }

        var allObj = canvas.getObjects();
        for(var i=0; i<allObj.length; i++)
        {
            if(allObj[i].name == 'groupTabNumber')
                {
                allObj[i].selectable = true;
                canvas.setActiveObject(allObj[i]);
            }
        }

        var obj = canvas.getActiveObject();
        if(obj){
            var cmd = new RemoveCanvasObject(this.productDesigner, obj,undefined,'groupTab');
            cmd.exec();
        }


    }
    //ProductDesigner.reloadPrice(rowLength);

    /*Groupdesigner.observeNameNumberObject()
    Groupdesigner.observeGroupNameAdd();
    Groupdesigner.observeGroupNumberAdd();*/
}


function deleteAllRow()
{
    var tBody =  document.getElementById('group-table').tBodies[0];

    tBody.innerHTML = '';
    var rowLength = tBody.rows.length;
    if(rowLength == 0)
        {
        document.getElementById('group-table-content').style.display = 'none';
        jQuery('#add_another_button').hide();
        document.getElementById('add_another_button').childNodes[0].childNodes[0].innerHTML = 'Add';
        jQuery('#isname').prop('checked',false);
        jQuery('#isnumber').prop('checked',false);        
        jQuery('#size-chart').removeClass('disabled');


        this.productDesigner = ProductDesigner.prototype;
        var canvas = this.productDesigner.canvas;
        var allObj = canvas.getObjects();
        for(var i=0; i<allObj.length; i++)
        {
            if(allObj[i].name == 'groupTabName')
                {
                allObj[i].selectable = true;
                canvas.setActiveObject(allObj[i]);
            }
        }
        var obj = canvas.getActiveObject();
        if(obj && obj.tab == 'grouporder'){
            var cmd = new RemoveCanvasObject(this.productDesigner, obj,undefined,'groupTab');
            cmd.exec();
        }

        var allObj = canvas.getObjects();
        for(var i=0; i<allObj.length; i++)
        {
            if(allObj[i].name == 'groupTabNumber')
                {
                allObj[i].selectable = true;
                canvas.setActiveObject(allObj[i]);
            }
        }

        var obj = canvas.getActiveObject();
        if(obj && obj.tab == 'grouporder'){
            var cmd = new RemoveCanvasObject(this.productDesigner, obj,undefined,'groupTab');
            cmd.exec();
        }


    }
    //ProductDesigner.reloadPrice(rowLength);


}