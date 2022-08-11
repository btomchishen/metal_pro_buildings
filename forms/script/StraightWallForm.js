class StraightWallForm extends Form {
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
     * Show fields if #MODEL_TYPE == 'Traditional Rigid System' || #MODEL_TYPE == 'Cee-Channe l- CMB'
     * Traditional Rigid System:
     *      Yes: #RAIN_LOAD, #SA_02, #SA_05, #SA_1, #SA_2, #LEW_AREAS, #REW_AREAS, #FSW_AREAS, #BSW_AREAS, #PRIMER_COLOR, #FRAME_COLUMN, #FRAME_RAFTER, #RIGIG_FRAMES, #BASE_CONDITIONS
     *      No: -
     * Cee-Channe l- CMB:
     *      Yes: #SA_02, #SA_05, #SA_1, #SA_2
     *      No: #RAIN_LOAD, #LEW_AREAS, #REW_AREAS, #FSW_AREAS, #BSW_AREAS, #PRIMER_COLOR, #FRAME_COLUMN, #FRAME_RAFTER, #RIGIG_FRAMES, #BASE_CONDITIONS
     * Default:
     *      Yes: -
     *      No: #RAIN_LOAD, #SA_02, #SA_05, #SA_1, #SA_2, #LEW_AREAS, #REW_AREAS, #FSW_AREAS, #BSW_AREAS, #PRIMER_COLOR, #FRAME_COLUMN, #FRAME_RAFTER, #RIGIG_FRAMES, #BASE_CONDITIONS
     */
    showFieldsByModelType() {
        let modelType = document.getElementById('MODEL_TYPE').value;

        var json = super.getJson();

        var RigidArray = json['MODEL_TYPE'].RigidArray;
        var ChannelArray = json['MODEL_TYPE'].ChannelArray;
        var Default = json['MODEL_TYPE'].Default;

        switch (modelType) {
            case 'Traditional Rigid System':
                super.changeInputSettings(RigidArray);
                break;
            case 'Cee-Channe l- CMB':
                super.changeInputSettings(ChannelArray);
                break;
            default:
                super.changeInputSettings(Default);
        }
    }

    /**
     * Show field if #GUTTES_DOWNS.value == 'Included' || #GUTTES_DOWNS.value == 'Not Included'
     * Included: #GUTTERS_DOWNS_COLOR
     * Not Included: #GUTTERS_DOWNS_COLOR
     * Default: #GUTTERS_DOWNS_COLOR
     */
    showGuttersDowns(isNew) {
        let guttersDowns = document.getElementById('GUTTERS_DOWNS').value;

        let included = [{id: 'GUTTERS_DOWNS_COLOR', style: 'block'}];
        let notIncluded = [{id: 'GUTTERS_DOWNS_COLOR', style: 'none'}];

        switch (guttersDowns) {
            case 'Included':
                super.changeInputSettings(included);
                if(isNew == true)
                    document.getElementById('GUTTERS_DOWNS_COLOR').value = 'N/A';
                break;
            case 'Not Included':
                super.changeInputSettings(notIncluded);
                break;
            default:
                super.changeInputSettings(notIncluded);
                break;
        }
    }

    /**
     * Show fields if #IS_INSULATION.checked == true
     * Yes: #ROOF_INSULATION, #WALL_INSULATION, #ROOF_LINER, #WALL_LINER
     * No: #ROOF_INSULATION, #WALL_INSULATION, #ROOF_LINER, #WALL_LINER
     */
    showFieldsByInsulation() {
        super.showFieldsByCheckbox('IS_INSULATION');
    }

    /**
     * Show fields if #IS_{LEW, REW, FSW, BSW}_OPEN.checked == true
     * Yes: #IS_{LEW, REW, FSW, BSW}_OPEN_1_QTY, #IS_{LEW, REW, FSW, BSW}_OPEN_2_QTY
     * No: #IS_{LEW, REW, FSW, BSW}_OPEN_1_QTY, #IS_{LEW, REW, FSW, BSW}_OPEN_2_QTY
     * @param id
     */
    showFieldsByIsOpen(id) {
        let element = document.getElementById(id).checked;
        // IS_LEW_OPEN
        let value = id.replace('IS_', '');
        value = value.replace('_OPEN', '');

        let yes = [
            {id: value + '_1_QTY', style: 'block'},
            {id: value + '_2_QTY', style: 'block'},
        ];

        let no = [
            {id: value + '_1_QTY', style: 'none'},
            {id: value + '_1_FRAME', style: 'none'},
            {id: value + '_2_QTY', style: 'none'},
            {id: value + '_2_FRAME', style: 'none'},
        ];

        switch (element) {
            case true:
                super.changeInputSettings(yes);
                break;
            case false:
                super.changeInputSettings(no);
                break;
        }
    }

    /**
     * Show fields if #IS_ACCESSORIES.checked == true
     * Yes: #SERVICE_DOOR, #SERVICE_DOOR_FRAME, #WINDOW_FRAME, #OTHERS_1, #OTHERS_2, #OTHERS_3
     * No: #SERVICE_DOOR, #SERVICE_DOOR_FRAME, #WINDOW_FRAME, #OTHERS_1, #OTHERS_2, #OTHERS_3
     */
    showFieldsByAccessory() {
        super.showFieldsByCheckbox('IS_ACCESSORIES');
    }

    /**
     * Show fields if #SERVICE_DOOR.value != '' || #SERVICE_DOOR_FRAME.value != ''
     * Yes: #SERVICE_DOOR_QTY, #SERVICE_DOOR_FRAME_QTY
     * No: #SERVICE_DOOR_QTY, #SERVICE_DOOR_FRAME_QTY
     * @param id
     */
    showFieldsByDoor(id) {
        let serviceDoor = document.getElementById(id).value;

        let yes = [
            {id: id + '_QTY', style: 'block'},
        ];

        let no = [
            {id: id + '_QTY', style: 'none'},
        ];

        if (serviceDoor == '')
            super.changeInputSettings(no);
        else
            super.changeInputSettings(yes);
    }

    showFieldsByFrameQTY(id) {
        let element = document.getElementById(id).value;

        let value = id.replace('QTY', '');

        let yes = [
            {id: value + 'FRAME', style: 'block'},
        ];

        let no = [
            {id: value + 'FRAME', style: 'none'},
        ];

        if (element != '')
            super.changeInputSettings(yes);
        else
            super.changeInputSettings(no);
    }

    showFieldsByFoundationDrawings() {
        let foundationDrawings = document.getElementById('FOUNDATION_DRAWINGS').value;

        let included = [{id: 'FOUNDATION_DRAWINGS_SEND', style: 'block'}];
        let notIncluded = [{id: 'FOUNDATION_DRAWINGS_SEND', style: 'none'}];

        switch (foundationDrawings) {
            case 'Included':
                super.changeInputSettings(included);
                break;
            default:
                super.changeInputSettings(notIncluded);
                break;
        }
    }
}