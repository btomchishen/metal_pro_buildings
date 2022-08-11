class RevisionToPurchaseOrder extends Form {
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
     * Show fields if #CHANGE_{1,2,3,4,5}.value != ''
     * Yes: #DESCRIPTION{1,2,3,4,5}
     * No: #DESCRIPTION{1,2,3,4,5}
     * @param id
     */
    changeFieldsByChange(id) {
        let change = document.getElementById(id).value;

        let value = id.replace('CHANGE', '');

        let yes = [{id: 'DESCRIPTION' + value, style: 'block'},];

        let no = [{id: 'DESCRIPTION' + value, style: 'none'},];

        if (change == '')
            super.changeInputSettings(no);
        else
            super.changeInputSettings(yes);
    }
}