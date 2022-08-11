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
     * Show fields if #PARTS_{1,2,3,4,5,6}.value != ''
     * Yes: #PARTS_QTY_{1,2,3,4,5,6}
     * No: #PARTS_QTY_{1,2,3,4,5,6}
     * @param id
     */
    changeFieldsByParts(id) {
        let parts = document.getElementById(id).value;

        let value = id.replace('PARTS', '');

        let yes = [{id: 'PARTS_QTY' + value, style: 'block'},];

        let no = [{id: 'PARTS_QTY' + value, style: 'none'},];

        if (parts == '')
            super.changeInputSettings(no);
        else
            super.changeInputSettings(yes);
    }

    changeFieldsByPickUp() {
        let isPickUp = document.getElementById('IS_PICK_UP').checked;

        if (isPickUp == true)
            document.getElementById('PICK_UP').value = 'Pick Up from Storage Yard';
        else
            document.getElementById('PICK_UP').value = '';
    }
}