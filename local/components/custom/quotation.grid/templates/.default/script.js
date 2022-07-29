function openNewQuotation()
{
    BX.SidePanel.Instance.open('/local/components/custom/quotation.system/iframe_component.php',
    {
		
		requestMethod: "post",
        cacheable: false,
        allowChangeHistory: false,
        requestParams: 
        { 
            "ACTION": "NEW"
        }
    });
}
