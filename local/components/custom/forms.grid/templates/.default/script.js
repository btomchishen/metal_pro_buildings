function openNewForm(dealID) {
    let formType = document.getElementById('FORMS_SELECT1').value;
    if(formType != '##')
        window.open('https://dev.metalpro.site/forms/index.php?FORM_TYPE=' + formType + '&DEAL_ID=' + dealID, 'blank');
    else
    {
        BX.UI.Notification.Center.notify({
            content: "Choose Form Type and press button again",
        });
    }
}