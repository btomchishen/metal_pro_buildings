$(document).ready(function()
{ 
    $('.money-input').maskMoney();
    $(".select2-list").select2();
    showDeleteBtn();
    $(".hide_selector span, .hide_selector div").hide();
});

$(document).on('click', '.quatation-save-btn', function()
{
    var purpose = $(this).attr("data");
    var quotationID = $(".quatation-form").attr('data');
    var formContainer = $(".form-container");
    var formData = new FormData($(".quatation-form")[0]);
    if(purpose == "SAVE")
    {
        formData.append("SAVE_DATA", 'Y');
        formData.append("ENTITY_TYPE", $(".quatation-form").data("entity"));
    }
    if(purpose == "UPDATE")
    {
        formData.append("UPDATE_DATA", 'Y');
        formData.append("QUOTATION_ID", $(".quatation-form").attr('data'));
        formData.append("ENTITY_TYPE", $(".quatation-form").data("entity"));
    }
    formData.append("ACCESSORIES_COUNT", $(".accessories-tr").length);
    formData.append("DOORS_COUNT", $(".doors-tr").length);
    console.log(formData);
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function()
        {
            $('.quatation-save-btn').addClass("ui-btn-wait");
        },
        success: function(data)
        {
            $('.quatation-save-btn').removeClass("ui-btn-wait");
            var res = JSON.parse(data);
            if(res.STATUS == "ERROR")
            {
                BX.UI.Notification.Center.notify({
                    content: "Error while saving data!<br/>"
                });
                for(var i=0; i < res.FIELD.length; i++)
                {
                    BX.UI.Notification.Center.notify({
                        content: "The '" + res.FIELD[i].FIELD_NAME + "' filed is required!<br/>"
                    });
                    $('[name="'+ res.FIELD[i].FIELD_CODE +'"]').addClass("invalid");
                    $('[name="'+ res.FIELD[i].FIELD_CODE +'"]').siblings(".select2-container").addClass("invalid");
                }
            }
            else
            {
                if(res.WEIGHT_ERROR == "true")
                {
                    BX.UI.Notification.Center.notify({
                        content: "Weight exceeds 15,000 lbs or its a special zone - check the estimates"
                    });
                }
                BX.UI.Notification.Center.notify({
                    content: "Changes saved successfully!"  
                });

                if(purpose == "SAVE")
                {
                    setTimeout(function()
                    {
                        var previousSlider = BX.SidePanel.Instance.getPreviousSlider(BX.SidePanel.Instance.getSliderByWindow(window));
                        var parentWindow = previousSlider ? previousSlider.getWindow() : top;
                        parentWindow.location.reload();
                        BX.SidePanel.Instance.close(false);
                    },1000);
                }
            }

            if(purpose == "UPDATE")
            {
                $.ajax({
                    type: 'POST',
                    url: "", 
                    data: {"MODE_SWITCH": "Y", "ACTION": "SHOW", "QUATATION_ID": quotationID},
                    success: function(data)
                    {

                        formContainer.html(data);
                        $('[data-role="edit_quotation"]').removeAttr("disabled");
                        $(".hide_selector span, .hide_selector div").hide();
                    }  
                })
            }
        }
    })
    return false;
});

$(document).on("change", "#PSF", function()
{
    var psf = $(this).val();
    var province = document.getElementById('building_province');
    var city = document.getElementById('building_city');
    var quotationID = $(".quatation-form").attr('data');
    var formContainer = $(".form-container");
    var formData = new FormData($(".quatation-form")[0]);
    formData.append("SELECT_PSF", 'Y');
    formData.append("QUOTATION_ID", $(".quatation-form").attr('data'));
    formData.append("ENTITY_TYPE", $(".quatation-form").data("entity"));
    
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: formData,
        processData: false,
        contentType: false,
        success: function(data)
        {
            var dataArray = JSON.parse(data);
            province.value = dataArray[0];
            var cityContainer = $("#building_city");
            $.ajax({
                type: 'POST',
                url: component_path + "/ajax.php", 
                data: {"SELECT_CITY": "Y", "PROVINCE_ID": province.value},
                success: function(data)
                {
                    var cities = JSON.parse(data);
                    cityContainer.find('option').remove();
                    for(var i=0; i < cities.length; i++) 
                        cityContainer.append("<option value='" + cities[i].ID + "'>" + cities[i].UF_CITY + "</option>");
                    city.value = dataArray[1];

                }
            })
        }
    })
});

$(document).on("change", "#building_province", function()
{
    var cityContainer = $("#building_city");
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: {"SELECT_CITY": "Y", "PROVINCE_ID": $(this).val()},
        success: function(data)
        {
            var cities = JSON.parse(data);
            cityContainer.find('option').remove();
            for(var i=0; i < cities.length; i++) 
                cityContainer.append("<option value='" + cities[i].ID + "'>" + cities[i].UF_CITY + "</option>");
        }
    })
});

$(document).on("change", "#building_province", function()
{
    var quotationID = $(".quatation-form").attr('data');
    var formContainer = $(".form-container");
    var formData = new FormData($(".quatation-form")[0]);
    formData.append("SELECT_BUILDING_CITY", 'Y');
    formData.append("QUOTATION_ID", $(".quatation-form").attr('data'));
    formData.append("ENTITY_TYPE", $(".quatation-form").data("entity"));
    formData.append("ACCESSORIES_COUNT", $(".accessories-tr").length);
    formData.append("DOORS_COUNT", $(".doors-tr").length);
    console.log(formData);
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: formData,
        processData: false,
        contentType: false,
        success: function(data)
        {
            setTotalCost($('[name="COST"]'), data);
        }
    })
});

$(document).on("change", "#building_city", function()
{
    var quotationID = $(".quatation-form").attr('data');
    var formContainer = $(".form-container");
    var formData = new FormData($(".quatation-form")[0]);
    formData.append("SELECT_BUILDING_CITY", 'Y');
    formData.append("QUOTATION_ID", $(".quatation-form").attr('data'));
    formData.append("ENTITY_TYPE", $(".quatation-form").data("entity"));
    formData.append("ACCESSORIES_COUNT", $(".accessories-tr").length);
    formData.append("DOORS_COUNT", $(".doors-tr").length);
    console.log(formData);
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: formData,
        processData: false,
        contentType: false,
        success: function(data)
        {
            setTotalCost($('[name="COST"]'), data);
        }
    })
});

$(document).on("change", "#foundation_system", function()
{
    var quotationID = $(".quatation-form").attr('data');
    var formContainer = $(".form-container");
    var formData = new FormData($(".quatation-form")[0]);
    formData.append("SELECT_FOUNDATION", 'Y');
    formData.append("QUOTATION_ID", $(".quatation-form").attr('data'));
    formData.append("ENTITY_TYPE", $(".quatation-form").data("entity"));
    formData.append("ACCESSORIES_COUNT", $(".accessories-tr").length);
    formData.append("DOORS_COUNT", $(".doors-tr").length);
    console.log(formData);
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: formData,
        processData: false,
        contentType: false,
        success: function(data)
        {
            setTotalCost($('[name="COST"]'), data);
        }
    })
});

$(document).on("change", "#front_wall", function()
{
    var frontWallType = document.getElementById('front_wall').value;
    if(frontWallType == 3)
    {
        document.getElementById('front_wall_offset').disabled = false;
    }
    else
    {
        document.getElementById('front_wall_offset').disabled = true;
        document.getElementById('front_wall_offset').value = "NO";
    }

    var quotationID = $(".quatation-form").attr('data');
    var formContainer = $(".form-container");
    var formData = new FormData($(".quatation-form")[0]);
    formData.append("SELECT_WALL", 'Y');
    formData.append("QUOTATION_ID", $(".quatation-form").attr('data'));
    formData.append("ENTITY_TYPE", $(".quatation-form").data("entity"));
    formData.append("ACCESSORIES_COUNT", $(".accessories-tr").length);
    formData.append("DOORS_COUNT", $(".doors-tr").length);
    console.log(formData);
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: formData,
        processData: false,
        contentType: false,
        success: function(data)
        {
            setTotalCost($('[name="COST"]'), data);
        }
    })
});

$(document).on("change", "#rear_wall", function()
{
    var reartWallType = document.getElementById('rear_wall').value;
    if(reartWallType == 3)
    {
        document.getElementById('rear_wall_offset').disabled = false;
    }
    else
    {
        document.getElementById('rear_wall_offset').disabled = true;
        document.getElementById('rear_wall_offset').value = "NO";
    }

    var quotationID = $(".quatation-form").attr('data');
    var formContainer = $(".form-container");
    var formData = new FormData($(".quatation-form")[0]);
    formData.append("SELECT_WALL", 'Y');
    formData.append("QUOTATION_ID", $(".quatation-form").attr('data'));
    formData.append("ENTITY_TYPE", $(".quatation-form").data("entity"));
    formData.append("ACCESSORIES_COUNT", $(".accessories-tr").length);
    formData.append("DOORS_COUNT", $(".doors-tr").length);
    console.log(formData);
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: formData,
        processData: false,
        contentType: false,
        success: function(data)
        {
            setTotalCost($('[name="COST"]'), data);
        }
    })
});

$(document).on("change", "#doors", function()
{
    var quotationID = $(".quatation-form").attr('data');
    var formContainer = $(".form-container");
    var formData = new FormData($(".quatation-form")[0]);
    formData.append("SELECT_DOOR", 'Y');
    formData.append("QUOTATION_ID", $(".quatation-form").attr('data'));
    formData.append("ENTITY_TYPE", $(".quatation-form").data("entity"));
    formData.append("ACCESSORIES_COUNT", $(".accessories-tr").length);
    formData.append("DOORS_COUNT", $(".doors-tr").length);
    console.log(formData);
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: formData,
        processData: false,
        contentType: false,
        success: function(data)
        {
            setTotalCost($('[name="COST"]'), data);
        }
    })
});

$(document).on("change", ".accessories-type", function()
{
    var accessoriesContainer = $(this).closest(".accessories-tr").find(".accessories-list");
    $.ajax({
        type: 'POST',
        url: component_path + "/ajax.php", 
        data: {"SELECT_ACCESSORIES": "Y", "ACCESSORY_TYPE_ID": $(this).val()},
        success: function(data)
        {
            var accessories = JSON.parse(data);
            accessoriesContainer.find('option').remove();
            accessoriesContainer.append("<option value=''>Not selected</option>");
            for(var i=0; i < accessories.length; i++) 
            {
                var separator = accessories[i].UF_WIDTH !== "" ||  accessories[i].UF_HEIGHT !== "" ? "/" : "";
                accessoriesContainer.append("<option data-width='" + accessories[i].UF_WIDTH + "'" + "' data-height='" 
                    + accessories[i].UF_HEIGHT + "'" + "' value='" + accessories[i].ID + "'>" 
                    + accessories[i].UF_ACCESSORIES_TYPE + " " + accessories[i].UF_WIDTH + separator + accessories[i].UF_HEIGHT + "</option>");
            }  
        }
    })
});

$(document).on("change", ".accessories-list", function(){
    var searchIndex = getInputIndex($(this));
    setHeightWidth('ACCESSORIES_WIDTH' + searchIndex, 'ACCESSORIES_HEIGHT' + searchIndex, $(this), $(this).val());
    var quantityInput = $(this).closest("tr").find('.accessory-quantity');
    var amountInput = $(this).closest("tr").find("[name='ACCESSORIES_AMOUNT"+ searchIndex +"']");
    quantityInput.val(1);
    setAccessoriesAmount($(this), quantityInput, amountInput, dataObj.ACCESSORIES);
    accessoriesTotalAmount($(".accessory-amount-input"), $('[name="TOTAL_ACCESSORIES_AMOUNT"]'));
});

$(document).on("change", ".accessory-quantity", function()
{
    var searchIndex = getInputIndex($(this));
    var accessoryInput = $(this).closest("tr").find('.accessories-list');
    var amountInput = $(this).closest("tr").find("[name='ACCESSORIES_AMOUNT"+ searchIndex +"']");
    setAccessoriesAmount(accessoryInput, $(this), amountInput, dataObj.ACCESSORIES);
});

$(document).on("change", ".doors-list", function()
{
    var searchIndex = getInputIndex($(this));
    setHeightWidth('DOOR_WIDTH' +  searchIndex, 'DOOR_HEIGHT' + searchIndex, $(this), $(this).val())
    var quantityInput = $(this).closest("tr").find('[name="DOOR_QUANTITY'+ searchIndex +'"]');
    var amountInput = $(this).closest("tr").find('[name="DOOR_AMOUNT'+ searchIndex +'"]');
    quantityInput.val(1);
    setAccessoriesAmount($(this), quantityInput, amountInput, dataObj.DOORS);
    accessoriesTotalAmount($(".doors-amount-input"), $('[name="TOTAL_DOOR_AMOUNT"]'));
});

$(document).on("change", ".door-quantity", function()
{
    var searchIndex = getInputIndex($(this));
    var accessoryInput = $(this).closest("tr").find('.doors-list');
    var amountInput = $(this).closest("tr").find("[name='DOOR_AMOUNT"+ searchIndex +"']");
    setAccessoriesAmount(accessoryInput, $(this), amountInput, dataObj.DOORS);
});

$(document).on("change", ".doors-width", function()
{
    var searchIndex = getInputIndex($(this));
    var width = $(this).val();
    var container = $(this).closest("tr");
    var height = container.find(".doors-height").val();
    sortDoors(container, height, width);
    container.find("[name='DOOR_QUANTITY"+ searchIndex +"'], [name='DOOR_AMOUNT"+ searchIndex +"']").val("");
});

$(document).on("change", ".doors-height", function()
{
    var searchIndex = getInputIndex($(this));
    var height = $(this).val();
    var container = $(this).closest("tr");
    var width = container.find(".doors-width").val();
    sortDoors(container, height, width);
    container.find("[name='DOOR_QUANTITY"+ searchIndex +"'], [name='DOOR_AMOUNT"+ searchIndex +"']").val("");
    accessoriesTotalAmount($(".doors-amount-input"), $('[name="TOTAL_DOOR_AMOUNT"]'));
});

$(document).on("change", ".doors-amount-input, .door-quantity", function()
{
    accessoriesTotalAmount($(".doors-amount-input"), $('[name="TOTAL_DOOR_AMOUNT"]'));
});

$(document).on("change", ".accessory-amount-input, .accessory-quantity", function()
{
    accessoriesTotalAmount($(".accessory-amount-input"), $('[name="TOTAL_ACCESSORIES_AMOUNT"]'));
});
$(document).on("click", ".add-accessories", function()
{
    var elemenentNumber = $('.accessories-type').length;
    $(".del-accessories-btn").css("display","");
    var newElem = $('.accessories-tr:first').clone();
    newElem.appendTo(".accessories-block");
    newElem.find(".accessories-type, .accessories-list, .select2-container").remove();
    newElem.find(".narrow-input").val("");
    newElem.find("[name='ACCESSORIES_QUANTITY']").attr("name", 'ACCESSORIES_QUANTITY_' + elemenentNumber);
    newElem.find("[name='ACCESSORIES_WIDTH']").attr("name", 'ACCESSORIES_WIDTH_' + elemenentNumber);
    newElem.find("[name='ACCESSORIES_HEIGHT']").attr("name", 'ACCESSORIES_HEIGHT_' + elemenentNumber);
    newElem.find("[name='ACCESSORIES_AMOUNT']").attr("name", 'ACCESSORIES_AMOUNT_' + elemenentNumber);
    newElem.find(".accessory-type-wrap").append('<select style="width:100%" class="crm-item-table-select accessories-type select2-list" name="ACCESSORIES_TYPE_' + elemenentNumber + '" sale_order_marker="Y"></select>');
    newElem.find(".accessory-list-wrap").append('<select style="width:100%" class="crm-item-table-select select2-list accessories-list" name="ACCESSORY_' + elemenentNumber + '" sale_order_marker="Y"></select>');
    newElem.find(".accessories-type, .accessories-list").append("<option value=''>Not selected</option>");
    for(var i=0; i < dataObj.ACCESSORIES_TYPE.length; i++)
        newElem.find(".accessories-type").append("<option value='" +  dataObj.ACCESSORIES_TYPE[i].ID + "'>" +  dataObj.ACCESSORIES_TYPE[i].UF_ACCESSORIES_TYPE_LIST + "</option>");
    newElem.find(".accessories-list").append('<option value="">Not selected</option>')
    for(var i=0; i < dataObj.ACCESSORIES.length; i++)
        appendAccesories(newElem.find(".accessories-list"), i);
    $(".select2-list").select2();
    $('.money-input').maskMoney();
    return false;
});

$(document).on("click", ".add-doors", function()
{
    var elemenentNumber = $('.doors-list').length;
    $(".del-doors-btn").css("display","");
    var newElem = $('.doors-tr:first').clone();
    newElem.appendTo(".doors-block");
    newElem.find(".doors-list, .select2-container").remove();
    newElem.find(".narrow-input").val("");
    newElem.find("[name='DOOR_QUANTITY']").attr("name", 'DOOR_QUANTITY_' + elemenentNumber);
    newElem.find("[name='DOOR_WIDTH']").attr("name", 'DOOR_WIDTH_' + elemenentNumber);
    newElem.find("[name='DOOR_HEIGHT']").attr("name", 'DOOR_HEIGHT_' + elemenentNumber);
    newElem.find("[name='DOOR_AMOUNT']").attr("name", 'DOOR_AMOUNT_' + elemenentNumber);
    newElem.find(".doors-list-wrap").append('<select style="width:100%" class="crm-item-table-select doors-list select2-list" name="DOOR_' + elemenentNumber + '" sale_order_marker="Y"></select>');
    newElem.find(".doors-list").append('<option value="">Not selected</option>');
    for(var i=0; i < dataObj.DOORS.length; i++)
        appendDoors (newElem.find(".doors-list"), i);
    $(".select2-list").select2();
    $('.money-input').maskMoney();
    return false;
});

$(document).on("click", ".del-accessories-btn, .del-doors-btn", function()
{
    $(this).closest("tr").remove();
    if($(this).data("block") == "door")
        accessoriesTotalAmount($(".doors-amount-input"), $('[name="TOTAL_DOOR_AMOUNT"]'));
    if($(this).data("block") == "accessory")
        accessoriesTotalAmount($(".accessory-amount-input"), $('[name="TOTAL_ACCESSORIES_AMOUNT"]'));
    if($(".del-accessories-btn").length == 1)
        $(".del-accessories-btn").css("display","none");
    if($(".del-doors-btn").length == 1)
        $(".del-doors-btn").css("display","none");
});

$(document).on("mouseover", "#PSF", function()
{
    var country = document.getElementById('building_country').value;
    if(country == "US")
    {
        document.getElementById('PSF').removeAttribute("readonly");
    } 
});

$(document).on("change", "#building_country", function()
{
    var country = $(this).val();
    if(country == "US")
    {
        document.getElementById('PSF').removeAttribute("readonly");
        document.getElementById('edit_freight_manually').checked = true;
    }
    else
    {
        document.getElementById('PSF').value = '';
        document.getElementById('PSF').setAttribute("readonly", "readonly");
    }
    $('select.accessories-list').each(function(index) {
        for(var i = 0; i < dataObj.ACCESSORIES.length; i++)
        {
            if(dataObj.ACCESSORIES[i].ID == $(this).val())
            {  
                var accessoryPrice = country == "СA" ? dataObj.ACCESSORIES[i].CA_PRICE : dataObj.ACCESSORIES[i].US_PRICE;
                var quantity = $(this).parents("tr").find(".accessory-quantity").val();
                $(this).parents("tr").find(".accessory-amount-input").val("$" + accessoryPrice * quantity);
            }
        }
    });
    $('select.doors-list').each(function(index) {
        for(var i = 0; i < dataObj.DOORS.length; i++)
        {
            if(dataObj.DOORS[i].ID == $(this).val())
            {  
                var accessoryPrice = country == "СA" ? dataObj.DOORS[i].CA_PRICE : dataObj.DOORS[i].US_PRICE;
                var quantity = $(this).parents("tr").find(".door-quantity").val();
                $(this).parents("tr").find(".doors-amount-input").val("$" + accessoryPrice * quantity);
            }
        }
    });
    accessoriesTotalAmount($(".accessory-amount-input"), $('[name="TOTAL_ACCESSORIES_AMOUNT"]'));
    accessoriesTotalAmount($(".doors-amount-input"), $('[name="TOTAL_DOOR_AMOUNT"]'));
});

$(document).on('focus', '.invalid', function()
{    
    $(this).removeClass("invalid");
});

$(document).on('click', '[data-role="edit_quotation"]', function()
{
    var formContainer = $(".form-container");
    $.ajax({
        type: 'POST',
        url: "", 
        data: {"MODE_SWITCH": "Y", "ACTION": "EDIT", "QUATATION_ID":$(".quatation-form").attr('data')},
        success: function(data)
        {
            formContainer.html(data);
            $('[data-role="edit_quotation"]').attr("disabled", "disabled");
            showDeleteBtn();
            var frontWallType = document.getElementById('front_wall').value;
            var rearWallType = document.getElementById('rear_wall').value;
            if(frontWallType == 3)
                document.getElementById('front_wall_offset').disabled = false;
            else
                document.getElementById('front_wall_offset').disabled = true;
            if(rearWallType == 3)
                document.getElementById('rear_wall_offset').disabled = false;
            else
                document.getElementById('rear_wall_offset').disabled = true;
        }  
    })
});
$(document).on('click', '[data-role="show_calculation"]', function()
{
    BX.SidePanel.Instance.open('/local/components/custom/quotation.system_test_avivi/templates/.default/calculation.php',
    {

        requestMethod: "post",
        allowChangeHistory: false,
        cacheable: false,
        requestParams: dataObj.CALCULATION

    });
});

function setHeightWidth(widthInput, heightInput, element, value)
{
    var width = parseInt(element.find("option[value='"+ value +"']").data("width"));
    var height = parseInt(element.find("option[value='"+ value +"']").data("height"));
    width = !isNaN(width) ? width : '';
    height= !isNaN(height) ? height : '';
    element.closest("tr").find('[name=' + widthInput + ']').val(width);
    element.closest("tr").find('[name=' + heightInput + ']').val(height);
}
function setAccessoriesAmount(accessoryInput, quantityInput, amountInput, accessoryList)
{
    var country = $("#building_country").val();
    for(var i=0; i < accessoryList.length; i++)
    {
        if(accessoryList[i].ID == accessoryInput.val())
        {  
            var accessoryPrice = country == "СA" ? accessoryList[i].CA_PRICE : accessoryList[i].US_PRICE;
            if(accessoryPrice)
                amountInput.val("$" + accessoryPrice * parseInt(quantityInput.val()));
            else
                amountInput.val("");
        }
        if(accessoryInput.val() == "")
            amountInput.val("");
    }
}
function setTotalCost(amountInput, amount){
    amountInput.val("$" + amount);
}

function sortDoors(container, height, width)
{
    var selectName = container.find(".doors-list").attr("name");
    container.find(".doors-list, .select2-container").remove();
    var doorListContainer = container.find(".doors-list-wrap");
    doorListContainer.append('<select style="width:100%" class="crm-item-table-select doors-list select2-list" name="'+selectName+'" sale_order_marker="Y"></select>');
    container.find(".doors-list").append("<option value=''>Not selected</option>");
    if(height !== "" && width !== "")
    {

        for(var i=0; i < dataObj.DOORS.length; i++)
        {
            if(parseInt(dataObj.DOORS[i].UF_WIDTH) == parseInt(width) && parseInt(dataObj.DOORS[i].UF_HEIGHT) == parseInt(height))
                appendDoors(container.find(".doors-list"), i);  
        }
    }
    else if (height !== "" || width !== "")
    {
        for(var i=0; i < dataObj.DOORS.length; i++)
        {
            if(width !== "")
            {
                if(parseInt(dataObj.DOORS[i].UF_WIDTH) == parseInt(width))
                    appendDoors(container.find(".doors-list"), i);
            }
            else
            {
                if(parseInt(dataObj.DOORS[i].UF_HEIGHT) == parseInt(height))
                    appendDoors(container.find(".doors-list"), i);
            }
        }
    }
    else
    {
        for(var i=0; i < dataObj.DOORS.length; i++)
            appendDoors(container.find(".doors-list"), i);
    }
    $(".select2-list").select2();
}

function appendDoors (container, i)
{
    var separator = dataObj.DOORS[i].UF_WIDTH !== "" ||  dataObj.DOORS[i].UF_HEIGHT !== "" ? "/" : "";
    container.append("<option data-width='" +  dataObj.DOORS[i].UF_WIDTH + "' data-height='" +  dataObj.DOORS[i].UF_HEIGHT + "' value='" +  dataObj.DOORS[i].ID + "'>" + 
        dataObj.DOORS[i].UF_ACCESSORIES_TYPE + " " +  dataObj.DOORS[i].UF_WIDTH + separator +  dataObj.DOORS[i].UF_HEIGHT + "</option>");
}

function appendAccesories(container, i)
{
    var separator = dataObj.ACCESSORIES[i].UF_WIDTH !== "" ||  dataObj.ACCESSORIES[i].UF_HEIGHT !== "" ? "/" : "";
    container.append("<option data-width='" +  dataObj.ACCESSORIES[i].UF_WIDTH + "' data-height='" +  dataObj.ACCESSORIES[i].UF_HEIGHT + "' value='" +  dataObj.ACCESSORIES[i].ID + "'>" + 
        dataObj.ACCESSORIES[i].UF_ACCESSORIES_TYPE + " " + dataObj.ACCESSORIES[i].UF_WIDTH + separator + dataObj.ACCESSORIES[i].UF_HEIGHT + "</option>");
}

function accessoriesTotalAmount(accessoriesAmountInputs, totalInput)
{
    var totalAmount = 0;
    for(var i = 0; i < accessoriesAmountInputs.length; i++)
    {
        if($(accessoriesAmountInputs[i]).val() !== "")
        {
            if($(accessoriesAmountInputs[i]).val().indexOf('$') != -1)
                amount = parseFloat($(accessoriesAmountInputs[i]).val().slice(1).replace(/,/, ""));
            else
                amount = parseFloat($(accessoriesAmountInputs[i]).val().replace(/,/, ""));
            totalAmount += amount;
        }
    }
    totalInput.val("$" + totalAmount.toFixed(2))
}

function IsJsonString(str) 
{
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function getInputIndex (input)
{
    if(input.attr("name").search('_') != -1 && /\d/g.test(input.attr("name")) != false) 
    {
        var index = input.attr("name").split("_");
        index = index[index.length - 1];
    }
    else
    {
        index = 0;
    }
    var searchIndex = index == 0 ? "" : "_" + index;
    return searchIndex;
}
function showDeleteBtn()
{
    if($(".del-accessories-btn").length > 1)
        $(".del-accessories-btn").css("display","");
    if($(".del-doors-btn").length > 1)
        $(".del-doors-btn").css("display","");
}