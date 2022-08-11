class Form {
    /**
     * Filling form fields from Deal information or HLBT
     */
    fillLeadsFromDeal() {
        var $this = this;

        $.ajax({
            type: 'POST',
            url: '/forms/ajax/ajax.php',
            data: {
                "ACTION": "GET_DATA",
                "FORM_TYPE": this.formType,
                "DEAL_ID": this.dealID,
                "ID": this.ID
            },
            beforeSend: function () {
                // Show image container
                $("#loader").show();
            },
            success: function (data) {
                let response = JSON.parse(data);

                for (const key in response.data) {
                    if (document.getElementsByName(key)[0]) {
                        if (document.getElementsByName(key)[0].type == 'checkbox') {
                            switch (response.data[key]) {
                                case 'Yes':
                                    document.getElementsByName(key)[0].checked = true;
                                    break;
                                case 'No':
                                    document.getElementsByName(key)[0].checked = false;
                                    break;
                            }
                        }
                        document.getElementsByName(key)[0].value = response.data[key];
                    }
                }

                for (const key in response.list) {
                    if (document.getElementsByName(key)[0])
                        document.getElementsByName(key)[0].innerHTML = response.list[key];
                }

                $this.calculatePrices();

                $this.fillChangedFields();
            },
            complete: function (data) {
                // Hide image container
                $("#loader").hide();
            }
        })
    }

    /**
     * Save form to HLBT
     */
    sendForm(action) {
        var formData = new FormData($("form#wall_form")[0]);

        formData.append("ACTION", "SAVE_DATA");
        formData.append("ACTION1", action);
        formData.append("FORM_TYPE", this.formType);
        formData.append("DEAL_ID", this.dealID);
        formData.append("ID", this.ID);

        var $this = this;
        $.ajax({
            type: 'POST',
            url: '/forms/ajax/ajax.php',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                // Show image container
                $("#loader").show();
            },
            success: function (data) {
                try {
                    let response = JSON.parse(data);

                    BX.UI.Notification.Center.notify({
                        content: "PO Created",
                    });

                    window.open(response.filePath, '_blank');
                    window.location.replace('https://dev.metalpro.site/crm/deal/details/' + $this.dealID + '/');
                } catch (e) {
                    BX.UI.Notification.Center.notify({
                        content: data,
                    });
                }
            },
            complete: function (data) {
                // Hide image container
                $("#loader").hide();
            }
        })
    }

    deleteForm() {
        var formData = new FormData($("form#wall_form")[0]);

        formData.append("ACTION", "DELETE");
        formData.append("FORM_TYPE", this.formType);
        formData.append("DEAL_ID", this.dealID);
        formData.append("ID", this.ID);

        var $this = this;
        $.ajax({
            type: 'POST',
            url: '/forms/ajax/ajax.php',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                // Show image container
                $("#loader").show();
            },
            success: function (data) {
                let response = JSON.parse(data);

                BX.UI.Notification.Center.notify({
                    content: "PO Deleted",
                });

                window.location.replace('https://dev.metalpro.site/crm/deal/details/' + $this.dealID + '/');

                console.log(response);
            },
            complete: function (data) {
                // Hide image container
                $("#loader").hide();
            }
        })
    }

    /**
     * Fill fields when form open first time
     */
    fillChangedFields() {
        if (this.formType == 'StraightWallForm') {
            this.showFieldsByModelType();
            this.showGuttersDowns(false);
            this.showFieldsByInsulation();
            this.showFieldsByIsOpen('IS_LEW_OPEN');
            this.showFieldsByFrameQTY('LEW_1_QTY');
            this.showFieldsByFrameQTY('LEW_2_QTY');
            this.showFieldsByIsOpen('IS_REW_OPEN');
            this.showFieldsByFrameQTY('REW_1_QTY');
            this.showFieldsByFrameQTY('REW_2_QTY');
            this.showFieldsByIsOpen('IS_FSW_OPEN');
            this.showFieldsByFrameQTY('FSW_1_QTY');
            this.showFieldsByFrameQTY('FSW_2_QTY');
            this.showFieldsByIsOpen('IS_BSW_OPEN');
            this.showFieldsByFrameQTY('BSW_1_QTY');
            this.showFieldsByFrameQTY('BSW_2_QTY');
            this.showFieldsByAccessory();
            this.showFieldsByDoor('SERVICE_DOOR');
            this.showFieldsByDoor('SERVICE_DOOR_FRAME');
            this.showFieldsByDoor('WINDOW_FRAME');
            this.showFieldsByDoor('OTHERS_1');
            this.showFieldsByDoor('OTHERS_2');
            this.showFieldsByDoor('OTHERS_3');
            this.showFieldsByFoundationDrawings();
        } else if (this.formType == 'QuonsetForm') {
            this.showFieldsByUSAOrder();
            this.showFieldsBySeries();
            this.showFieldsBySeaContainer();
            this.changeFieldsByExposureConditions();
            this.showFieldsByWallFrame('FRONT_WALL_FRAME');
            this.showFieldsByWallFrame('REAR_WALL_FRAME');
            this.showFieldsByAccessory();
            this.changeFieldsByAccessory1('ACCESSORY_1');
            this.changeFieldsByAccessory1('ACCESSORY_2');
            this.changeFieldsByAccessory1('ACCESSORY_3');
            this.changeFieldsByAccessory1('ACCESSORY_4');
            this.changeFieldsByAccessory1('ACCESSORY_5');
        } else if (this.formType == 'QuonsetPartsOrder') {
            this.changeFieldsByParts('PARTS_1');
            this.changeFieldsByParts('PARTS_2');
            this.changeFieldsByParts('PARTS_3');
            this.changeFieldsByParts('PARTS_4');
            this.changeFieldsByParts('PARTS_5');
            this.changeFieldsByParts('PARTS_6');
        } else if (this.formType = 'RevisionToPurchaseOrder') {
            this.changeFieldsByChange('CHANGE_1');
            this.changeFieldsByChange('CHANGE_2');
            this.changeFieldsByChange('CHANGE_3');
            this.changeFieldsByChange('CHANGE_4');
            this.changeFieldsByChange('CHANGE_5');
        }
    }

    /**
     * Get options list from HLBT for filling selects
     *
     * @param action
     */
    getList(action) {
        var $this = this;

        $.ajax({
            type: 'POST',
            url: '/forms/ajax/ajax.php',
            data: {
                "ACTION": action,
                "FORM_TYPE": this.formType,
                "DEAL_ID": this.dealID
            },
            beforeSend: function () {
                // Show image container
                $("#loader").show();
            },
            success: function (data) {
                let response = JSON.parse(data);

                for (const key in response.list) {
                    if (document.getElementsByName(key)[0])
                        document.getElementsByName(key)[0].innerHTML = response.list[key];
                }

                if (action == 'GET_ACCESSORIES') {
                    $this.changeFieldsByAccessory1('ACCESSORY_1');
                    $this.changeFieldsByAccessory1('ACCESSORY_2');
                    $this.changeFieldsByAccessory1('ACCESSORY_3');
                    $this.changeFieldsByAccessory1('ACCESSORY_4');
                    $this.changeFieldsByAccessory1('ACCESSORY_5');
                }
            },
            complete: function (data) {
                // Hide image container
                $("#loader").hide();
            }
        })
    }

    /**
     * Calculate Prices
     */
    calculatePrices(calculateBy) {
        let buildingPrice = document.getElementById('BUILDING_PRICE').value;
        let taxRate = document.getElementById('TAX_RATE').value;
        let subTotal = document.getElementById('SUB_TOTAL').value;

        let totalAmount = 0;
        let originalAmount = 0;

        if (this.formType == 'RevisionToPurchaseOrder') {
            totalAmount = document.getElementById('TOTAL_AMOUNT').value;
            originalAmount = document.getElementById('ORIGINAL_CONTRACT_AMOUNT').value;
        }

        $.ajax({
            type: 'POST',
            url: '/forms/ajax/ajax.php',
            data: {
                "ACTION": "RECALCULATE_PRICES",
                "CALCULATE_BY": calculateBy,
                "BUILDING_PRICE": buildingPrice,
                "TAX_RATE": taxRate,
                "SUB_TOTAL": subTotal,
                "TOTAL_AMOUNT": totalAmount,
                "ORIGINAL_CONTRACT_AMOUNT": originalAmount,
                "FORM_TYPE": this.formType,
                "DEAL_ID": this.dealID
            },
            beforeSend: function () {
                // Show image container
                $("#loader").show();
            },
            success: function (data) {
                let response = JSON.parse(data);

                for (const key in response.prices) {
                    if (document.getElementsByName(key)[0])
                        document.getElementsByName(key)[0].value = response.prices[key];
                }
            },
            complete: function (data) {
                // Hide image container
                $("#loader").hide();
            }
        })
    }

    /**
     * Get Addendum
     */
    getAddendum() {
        let addendum1 = document.getElementById('ADDENDUM_1').value;

        $.ajax({
            type: 'POST',
            url: '/forms/ajax/ajax.php',
            data: {
                "ACTION": "GET_ADDENDUM",
                "FORM_TYPE": this.formType,
                "DEAL_ID": this.dealID,
                "ADDENDUM_1": addendum1
            },
            beforeSend: function () {
                // Show image container
                $("#loader").show();
            },
            success: function (data) {
                let response = JSON.parse(data);

                document.getElementById('ADDENDUM').value = response.addendum;
                document.getElementById('ADDENDUM_LABEL').innerText = response.addendumLabel;
            },
            complete: function (data) {
                // Hide image container
                $("#loader").hide();
            }
        })
    }

    /**
     * Get data from /forms/ajax/array.json file with data for showing or hiding form inputs
     *
     * @returns {any}
     */
    getJson() {
        var ajax = $.ajax({
            type: 'GET',
            async: false,
            url: '/forms/ajax/parse_json_arrays.php',
            dataType: 'json',
        });

        return JSON.parse(ajax.responseText);
    }

    /**
     * Show fields depends on checkbox with ID = elementID
     *
     * @param elementID
     * @param yesArray
     * @param noArray
     */
    showFieldsByCheckbox(elementID) {
        let element = document.getElementById(elementID).checked;

        var json = this.getJson();

        if (json[elementID].yes)
            var yesArray = json[elementID].yes;
        if (json[elementID].no)
            var noArray = json[elementID].no;

        switch (element) {
            case true:
                this.changeInputSettings(yesArray);
                break;
            case false:
                this.changeInputSettings(noArray);
                break;
        }
    }

    /**
     * Change input style.display (block or none)
     *
     * @param inputArray
     */
    changeInputSettings(inputArray) {
        inputArray.forEach(element => {
            document.getElementById(element.id).style.display = element.style;

            document.querySelector(`[for="` + element.id + `"]`).style.display = element.style;
            if (element.style == 'none')
                document.getElementById(element.id).value = '';
        });
    }
}