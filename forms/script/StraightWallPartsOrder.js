class StraightWallPartsOrder extends Form {
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
     * Show fields if #ACCESSORY_{1,2,3,4,5,6}.value != ''
     * Yes: #ACCESSORY_QTY_{1,2,3,4,5,6}
     * No: #ACCESSORY_QTY_{1,2,3,4,5,6}
     * @param id
     */
    showAccessory(id) {
        let accessory = document.getElementById(id).value;

        let value = id.replace('ACCESSORY', '');

        let yes = [{id: 'ACCESSORY_QTY' + value, style: 'block'},];

        let no = [{id: 'ACCESSORY_QTY' + value, style: 'none'},];

        if (accessory == '')
            super.changeInputSettings(no);
        else
            super.changeInputSettings(yes);
    }
}