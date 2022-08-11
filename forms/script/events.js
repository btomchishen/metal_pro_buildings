$(document).ready(function () {
    // Get FormType and DealID
    let element = document.getElementsByClassName('formTypeAndDealID')[0].id.split('#');
    let formType = element[0];
    let dealID = element[1];
    let ID = element[2];

    let form = '';
    switch (formType) {
        case 'QuonsetForm':
            form = new QuonsetForm(formType, dealID, ID);
            break;
        case 'StraightWallForm':
            form = new StraightWallForm(formType, dealID, ID);
            break;
        case 'QuonsetPartsOrder':
            form = new QuonsetPartsOrder(formType, dealID, ID);
            break;
        case 'RevisionToPurchaseOrder':
            form = new RevisionToPurchaseOrder(formType, dealID, ID);
            break;
        case 'StraightWallPartsOrder':
            form = new StraightWallPartsOrder(formType, dealID, ID);
            break;
    }

    form.fillLeadsFromDeal();

    $('.money-input').maskMoney();

    var primaryPhone = document.getElementById('primary_phone');
    var secondaryPhone = document.getElementById('secondary_phone');
    var maskOptions = {
        mask: '+{1} 000 000-0000'
    };
    var primaryPhoneMask = IMask(primaryPhone, maskOptions);
    var secondaryPhoneMask = IMask(secondaryPhone, maskOptions);

    $(document).on('click', '#btn', function () {
        form.sendForm('UPDATE');
    });

    $(document).on('click', '#btn1', function () {
        form.sendForm('NEW');
    });

    $(document).on('click', '#delete_btn', function () {
        form.deleteForm();
    });

    $(document).on('change', '#BUILDING_PRICE', function () {
        form.calculatePrices('PRICE');
    });

    $(document).on('change', '#TAX_RATE', function () {
        form.calculatePrices('TAX');
    });

    $(document).on('change', '#SUB_TOTAL', function () {
        form.calculatePrices('SUB_TOTAL');
    });

    $(document).on('change', '#ADDENDUM_1', function () {
        form.getAddendum();
    });

    if (formType == 'StraightWallForm') {
        // StraightWallForm
        $(document).on('change', '#MODEL_TYPE', function () {
            form.showFieldsByModelType();
        });

        $(document).on('change', '#GUTTERS_DOWNS', function () {
            form.showGuttersDowns(true);
        });

        $(document).on('click', '#IS_INSULATION', function () {
            form.showFieldsByInsulation();
        });

        $(document).on('click', '#IS_LEW_OPEN', function () {
            form.showFieldsByIsOpen(this.id);
        });

        $(document).on('change', '#LEW_1_QTY', function () {
            form.showFieldsByFrameQTY(this.id);
        });

        $(document).on('change', '#LEW_2_QTY', function () {
            form.showFieldsByFrameQTY(this.id);
        });

        $(document).on('click', '#IS_REW_OPEN', function () {
            form.showFieldsByIsOpen(this.id);
        });

        $(document).on('change', '#REW_1_QTY', function () {
            form.showFieldsByFrameQTY(this.id);
        });

        $(document).on('change', '#REW_2_QTY', function () {
            form.showFieldsByFrameQTY(this.id);
        });

        $(document).on('click', '#IS_FSW_OPEN', function () {
            form.showFieldsByIsOpen(this.id);
        });

        $(document).on('change', '#FSW_1_QTY', function () {
            form.showFieldsByFrameQTY(this.id);
        });

        $(document).on('change', '#FSW_2_QTY', function () {
            form.showFieldsByFrameQTY(this.id);
        });

        $(document).on('click', '#IS_BSW_OPEN', function () {
            form.showFieldsByIsOpen(this.id);
        });

        $(document).on('change', '#BSW_1_QTY', function () {
            form.showFieldsByFrameQTY(this.id);
        });

        $(document).on('change', '#BSW_2_QTY', function () {
            form.showFieldsByFrameQTY(this.id);
        });

        $(document).on('click', '#IS_ACCESSORIES', function () {
            form.showFieldsByAccessory();
        });

        $(document).on('change', '#SERVICE_DOOR', function () {
            form.showFieldsByDoor(this.id);
        });

        $(document).on('change', '#SERVICE_DOOR_FRAME', function () {
            form.showFieldsByDoor(this.id);
        });

        $(document).on('change', '#WINDOW_FRAME', function () {
            form.showFieldsByDoor(this.id);
        });

        $(document).on('change', '#OTHERS_1', function () {
            form.showFieldsByDoor(this.id);
        });

        $(document).on('change', '#OTHERS_2', function () {
            form.showFieldsByDoor(this.id);
        });

        $(document).on('change', '#OTHERS_3', function () {
            form.showFieldsByDoor(this.id);
        });

        $(document).on('change', '#FOUNDATION_DRAWINGS', function () {
            form.showFieldsByFoundationDrawings();
        });
    }

    if (formType == 'QuonsetForm') {
        // Quonset Form
        $(document).on('click', '#IS_USA_ORDER', function () {
            form.showFieldsByUSAOrder();
        });

        $(document).on('change', '#SERIES', function () {
            form.showFieldsBySeries();
        });

        $(document).on('click', '#IS_SEA_CONTAINER_BUILDING', function () {
            form.showFieldsBySeaContainer();
        });

        $(document).on('change', '#EXPOSURE_CONDITIONS', function () {
            form.changeFieldsByExposureConditions();
        });

        $(document).on('change', '#FRONT_WALL_FRAME', function () {
            form.showFieldsByWallFrame(this.id);
        });

        $(document).on('change', '#REAR_WALL_FRAME', function () {
            form.showFieldsByWallFrame(this.id);
        });

        $(document).on('click', '#IS_ACCESSORIES1', function () {
            form.showFieldsByAccessory();
        });

        $(document).on('change', '#ACCESSORY_1', function () {
            form.changeFieldsByAccessory1(this.id);
            form.setAccessoryQuantity(this.id);
        });

        $(document).on('change', '#ACCESSORY_2', function () {
            form.changeFieldsByAccessory1(this.id);
            form.setAccessoryQuantity(this.id);
        });

        $(document).on('change', '#ACCESSORY_3', function () {
            form.changeFieldsByAccessory1(this.id);
            form.setAccessoryQuantity(this.id);
        });

        $(document).on('change', '#ACCESSORY_4', function () {
            form.changeFieldsByAccessory1(this.id);
            form.setAccessoryQuantity(this.id);
        });

        $(document).on('change', '#ACCESSORY_5', function () {
            form.changeFieldsByAccessory1(this.id);
            form.setAccessoryQuantity(this.id);
        });

        $(document).on('change', '#FRONT_WALL_FRAME_1', function () {
            form.setWallQuantity(this.id);
        });

        $(document).on('change', '#FRONT_WALL_FRAME_2', function () {
            form.setWallQuantity(this.id);
        });

        $(document).on('change', '#REAR_WALL_FRAME_1', function () {
            form.setWallQuantity(this.id);
        });

        $(document).on('change', '#REAR_WALL_FRAME_2', function () {
            form.setWallQuantity(this.id);
        });

    }

    if (formType == 'QuonsetPartsOrder') {
        // Quonset Parts Order
        $(document).on('change', '#IS_PICK_UP', function () {
            form.changeFieldsByPickUp();
        });

        $(document).on('change', '#PARTS_1', function () {
            form.changeFieldsByParts(this.id);
        });

        $(document).on('change', '#PARTS_2', function () {
            form.changeFieldsByParts(this.id);
        });

        $(document).on('change', '#PARTS_3', function () {
            form.changeFieldsByParts(this.id);
        });

        $(document).on('change', '#PARTS_4', function () {
            form.changeFieldsByParts(this.id);
        });

        $(document).on('change', '#PARTS_5', function () {
            form.changeFieldsByParts(this.id);
        });

        $(document).on('change', '#PARTS_6', function () {
            form.changeFieldsByParts(this.id);
        });
    }

    if (formType == 'RevisionToPurchaseOrder') {
        // Revision to Purchase Order
        $(document).on('change', '#TOTAL_AMOUNT', function () {
            form.calculatePrices();
        });

        $(document).on('change', '#ORIGINAL_CONTRACT_AMOUNT', function () {
            form.calculatePrices();
        });

        $(document).on('change', '#CHANGE_1', function () {
            form.changeFieldsByChange(this.id);
        });

        $(document).on('change', '#CHANGE_2', function () {
            form.changeFieldsByChange(this.id);
        });

        $(document).on('change', '#CHANGE_3', function () {
            form.changeFieldsByChange(this.id);
        });

        $(document).on('change', '#CHANGE_4', function () {
            form.changeFieldsByChange(this.id);
        });

        $(document).on('change', '#CHANGE_5', function () {
            form.changeFieldsByChange(this.id);
        });

    }
});