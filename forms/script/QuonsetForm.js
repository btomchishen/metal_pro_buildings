class QuonsetForm extends Form {
    formType = '';
    dealID = 0;
    ID = 0;

    constructor(formType, dealID, ID) {
        super();

        this.formType = formType;
        this.dealID = dealID;
        this.ID = ID;
    }

    /**
     * Show fields if #IS_USA_ORDER.checked == true
     * Yes: #USA_BUILDING_DRAWINGS, #USA_BUILDING_USE
     * No: #USA_BUILDING_DRAWINGS, #USA_BUILDING_USE
     */
    showFieldsByUSAOrder() {
        super.showFieldsByCheckbox('IS_USA_ORDER');
    }

    /**
     * Show fields if #SERIES.value == 'Q-Series'
     * Yes: #IS_SEA_CONTAINER_BUILDING
     * No: #IS_SEA_CONTAINER_BUILDING, #SEA_CONTAINER_STYLE, #SEA_CONTAINER_DESIGN, #FRONT_WALL_EXTENSION, #FRONT_WALL_SEA_CONTAINER_HEIGHT, #REAR_WALL_EXTENSION, #REAR_WALL_SEA_CONTAINER_HEIGHT
     */
    showFieldsBySeries() {
        let series = document.getElementById('SERIES').value;

        var json = super.getJson();

        var yes = json['SERIES'].yes;
        var no = json['SERIES'].no;

        switch (series) {
            case 'Q-Series':
                super.changeInputSettings(yes);
                document.getElementById('IS_SEA_CONTAINER_BUILDING_text').style.display = 'block';
                break;
            default:
                super.changeInputSettings(no);
                document.getElementById('IS_SEA_CONTAINER_BUILDING_text').style.display = 'none';
                document.getElementById('IS_SEA_CONTAINER_BUILDING').checked = false;
                break;
        }
    }

    /**
     * Show fields if #IS_SEA_CONTAINER_BUILDING.checked == true;
     * Yes: #SEA_CONTAINER_STYLE, #SEA_CONTAINER_DESIGN, #FRONT_WALL_EXTENSION, #FRONT_WALL_SEA_CONTAINER_HEIGHT, #REAR_WALL_EXTENSION, #REAR_WALL_SEA_CONTAINER_HEIGHT
     * No: #SEA_CONTAINER_STYLE, #SEA_CONTAINER_DESIGN, #FRONT_WALL_EXTENSION, #FRONT_WALL_SEA_CONTAINER_HEIGHT, #REAR_WALL_EXTENSION, #REAR_WALL_SEA_CONTAINER_HEIGHT
     */
    showFieldsBySeaContainer() {
        super.showFieldsByCheckbox('IS_SEA_CONTAINER_BUILDING');
    }

    /**
     * Show fields if #FRONT_WALL_FRAME.value == 'Framed Opening' || #REAR_WALL_FRAME.value == 'Framed Opening'
     * Yes: #FRONT_WALL_FRAME_QTY1, #FRONT_WALL_FRAME_1, #FRONT_WALL_FRAME_QTY_2, #FRONT_WALL_FRAME_2
     * No: #FRONT_WALL_FRAME_QTY1, #FRONT_WALL_FRAME_1, #FRONT_WALL_FRAME_QTY_2, #FRONT_WALL_FRAME_2
     * @param id
     */
    showFieldsByWallFrame(id) {
        let wallFrame = document.getElementById(id).value;

        let yes = [
            {id: id + '_QTY1', style: 'block'},
            {id: id + '_1', style: 'block'},
            {id: id + '_QTY2', style: 'block'},
            {id: id + '_2', style: 'block'},
        ];

        let no = [
            {id: id + '_QTY1', style: 'none'},
            {id: id + '_1', style: 'none'},
            {id: id + '_QTY2', style: 'none'},
            {id: id + '_2', style: 'none'},
        ];

        switch (wallFrame) {
            case 'Framed Opening':
                super.changeInputSettings(yes);
                break;
            default:
                super.changeInputSettings(no);
                break;
        }
    }

    /**
     * Show fields if #IS_ACCESSORIES1.checked == true
     * Yes: #ACCESSORY_1, #ACCESSORY_2, #ACCESSORY_3, #ACCESSORY_4, #ACCESSORY_4
     * No: #ACCESSORY_1, #ACCESSORY_QTY_1, #ACCESSORY_2, #ACCESSORY_QTY_2, #ACCESSORY_3, #ACCESSORY_QTY_3, #ACCESSORY_4, #ACCESSORY_QTY_4, #ACCESSORY_4, #ACCESSORY_QTY_4
     */
    showFieldsByAccessory() {
        super.showFieldsByCheckbox('IS_ACCESSORIES1');
    }

    /**
     * Show fields if #ACCESSORY_{1,2,3,4,5}.value != ''
     * Yes: #ACCESSORY_QTY_{1,2,3,4,5}
     * No: #ACCESSORY_QTY_{1,2,3,4,5}
     * @param id
     */
    changeFieldsByAccessory1(id) {
        let accessory = document.getElementById(id).value;

        let value = id.replace('ACCESSORY', '');

        let yes = [{id: 'ACCESSORY_QTY' + value, style: 'block'},];

        let no = [{id: 'ACCESSORY_QTY' + value, style: 'none'},];

        if (accessory == '')
            super.changeInputSettings(no);
        else
            super.changeInputSettings(yes);
    }

    /**
     * Show fields if #EXPOSURE_CONDITIONS.value == 'Shadow/Drift'
     * Yes: SHADOW_DRIFT
     * No: SHADOW_DRIFT
     */
    changeFieldsByExposureConditions() {
        let exposureCondition = document.getElementById('EXPOSURE_CONDITIONS').value;

        let yes = [{id: 'SHADOW_DRIFT', style: 'block'},];

        let no = [{id: 'SHADOW_DRIFT', style: 'none'},];

        if (exposureCondition == 'Shadow/Drift')
            super.changeInputSettings(yes);
        else
            super.changeInputSettings(no);
    }

    setWallQuantity(id) {
        let wallFrame = document.getElementById(id).value;

        let wallFrameQuantityId = id.substr(0, id.length - 1);
        wallFrameQuantityId += 'QTY' + id[id.length - 1];

        let wallFrameQuantity = document.getElementById(wallFrameQuantityId);

        if (wallFrame != '' && wallFrameQuantity.value == '')
            wallFrameQuantity.value = 1;
        else if(wallFrame == '' && wallFrameQuantity.value != '')
            wallFrameQuantity.value = '';
    }

    setAccessoryQuantity(id) {
        let valueList = [
            'Pre-Fab Foundation (materials only)',
            'Pre-Fab Foundation package (materials & installation)',
            'Caulking',
            'Insulation R-13 Single layer faced blanket',
            'Insulation R-13 Single layer faced blanket (arches only)',
            'Insulation R-20 Single layer faced blanket insulation',
            'Insulation R-20 Single layer faced blanket (arches only)',
            'Refer to Notes'
        ];

        let accessory = document.getElementById(id);
        let accessoryQuantity = document.getElementById('ACCESSORY_QTY' + id.replace('ACCESSORY', ''))
        
        for (let i = 0; i < valueList.length; i++) {
            if (accessory.value == valueList[i])
                accessoryQuantity.value = 'Included';
        }
    }
}