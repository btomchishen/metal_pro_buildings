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

}