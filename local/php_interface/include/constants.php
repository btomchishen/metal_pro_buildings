<?
define("PURCHASE_ORDER_LIST", 10);
define("USE_EXPOSURE_LIST", 11);
define("SERIES_LIST", 12);
define("FOUNDATION_SYSTEM_LIST", 13);
define("WALL_TYPE_LIST", 14);
define("ACCESSORIES_TYPE_LIST", 15);
define("PROVINCES_HIGHLOAD", 3);
define("CITIES_HIGHLOAD", 4);
define("MODEL_HIGHLOAD", 1);
define("ACCESSORIES_HIGHLOAD", 2);
define("LOADING_TABLE_HIGHLOAD", 6);
define("USE_EXPOSURE_LIST_HIGHLOAD", 11);
define("GAUGE_VARIANTS_HIGHLOAD", 16);
define("MODEL_LIVE_LOAD_HIGHLOAD", 7);
define("RETAILED_PRICE_HIGHLOAD", 8);
define("CONSTANTS_HIGHLOAD", 9);
define("SHIPPING_WEIGHT_HIGHLOAD", 18);
define("WEIGHT_MEASURES_HIGHLOAD", 19);
define("WEIGHT_HIGHLOAD", 17);
define("FREIGHT_COST_HIGHLOAD", 5);
define("QUOTATION_SYSTEM_HIGHLOAD", 20);
define("EMAILS_SOURCES_HIGHLOAD", 22);
define("ENDWALL_EXTENSION_HIGHLOAD", 23);

define("OPEN_WALL_TYPE", 1);
define("SOLID_WALL_TYPE", 2);
define("MINI_SERIE", 5);
define("Q_SERIE", 1);
define("S_SERIE", 2);

define("FOUNDATION_SYSTEM_THROUGH", 1);
define("FOUNDATION_SYSTEM_INDUSTRIAL_BASEPLATES", 2);
define("FOUNDATION_SYSTEM_CHANNEL", 3);
define("FOUNDATION_SYSTEM_MONOLITHIC_POUR", 4);

define("ON_PROVINCE", 5);

//Constants
define("ARCH_RETAIL_PRICE_FOR_CHANNEL", 1);
define("ARCH_RETAIL_PRICE_FOR_MINI_CHANNEL", 2);
define("ARCH_RETAIL_PRICE_FOR_INDUSTRIAL", 3);
define("ENDWALL_RETAIL_PRICE_FOR_CHANNEL", 4);
define("ENDWALL_RETAIL_PRICE_FOR_MINI_CHANNEL", 5);
define("ENDWALL_RETAIL_PRICE_FOR_INDUSTRIAL", 6);
define("DRAWINGS", 7);
define("ENDWALL_FREIGHT", 8);
define("BASEPLATE_FREIGHT", 9);
define("CA_VARIABLE_1", 10);
define("CA_VARIABLE_2", 11);
define("US_VARIABLE_1", 12);
define("US_VARIABLE_2", 13);

//Lead
define("STYLE_FIELD", "UF_CRM_F27C976E");
define("WIDTH_FIELD", "UF_CRM_4DDB6A2B");
define("LENGTH_FIELD", "UF_CRM_6D19B84");
define("HEIGHT_FIELD", "UF_CRM_F2E1E039");
define("PROJECT_DETAILS_FIELD", "UF_CRM_A34000ED");
define("MODEL_FIELD", "UF_CRM_16545DDD");
define("LEAD_NUMBER_FIELD", "UF_CRM_6AC1ECC9");
define("STATE_FIELD", "UF_CRM_1620224095");
define("STATE_FIELD_ID", "601");
define("PROVINCE_FIELD", "UF_CRM_1607507237");
define("PROVINCE_FIELD_ID", "174");

//Sources 
define("BRAEMAR_SOURCE_ID", "225A5CAD");
define("PIONEER_SOURCE_ID", "B9448ECE");

//Emails addresses
define("BRAEMAR_EMAIL_ADRESS", "1");
define("PIONEER_EMAIL_ADRESS", "2");

//Users id
define("DEBBIE_SIN", "24");
define("MELVYN_HO", "72");
define("LOURDES_MARC", "67");
define("KRYSTAL_WILLIAMS", "71");
define("ABBE_SIN", "59");

//Projects id
define("OPERATIONS", "14");

//Mailbox id
define("ADMIN_MAILBOX_ID", "1");

define("EXTERNAL_CALL_TYPE", "1");

// Avivi #48605 Check Mailbox and add to the Company card
define('CRM_INCOMING_EMAIL_ACTIVITY_MAILBOX_ID', 1);
define('CRM_INCOMING_EMAIL_ACTIVITY_EMAIL_FROM_FILTER', [
    'docusign@metalprobuildings.com',
    'engineering@metalprobuildings.com',
    'delivery@metalprobuildings.com',
    'Drawings@metalprobuildings.com',
    'Costing@metalprobuildings.com',
    'cmb@metalprobuildings.com',
    'sbc-orders@metalprobuildings.com',
    'iq@metalprobuildings.com',
    'pioneerinvoice@metalprobuildings.com',
]);
define('HB_CRM_IEA_EMAIL_COMPANY_BIND', 24);

// Avivi #49545 CRM Analytics Report
define('HB_NEW_LEADS', 25);
define('NEW_LEAD_COUNT_STATUS_ID', 'E4B0A778');

define('HB_REPORT_ANALYTICS_CONFIG', 29);

// Forms
define('FORMS_HIGHLOAD', 38);
define('COLORS_HIGHLOAD', 33);
define('REQUESTED_DELIVERY_MONTH_HIGHLOAD', 36  );
define('TAX_RATE_HIGHLOAD', 32);
define('FORM_ACCESSORY_HIGHLOAD', 34);
define('PARTS_HIGHLOAD', 37);
define('VENDOR_ID', 'UF_CRM_B08E81E8');
define('LEAD_ID', 'UF_CRM_6D6DE9FF');
define('MAILING_ADDRESS', 'UF_CRM_61DDDDE44BA87');
define('SHIPPING_ADDRESS', 'UF_CRM_61DDDDE46CDF0');
define('PRIMARY_PHONE', 'UF_CRM_601977A87EC73');
define('SECONDARY_PHONE', 'UF_CRM_601977A8A72DB');
define('MODEL_TYPE', 'UF_CRM_1637343846672');
define('MODEL_TYPE_FIELD_ID', 856);
define('BUILDING_USE', 'UF_CRM_601977A8D1631');
define('BUILDING_WIDTH', 'UF_CRM_601977A9B99EF');
define('BUILDING_LENGTH', 'UF_CRM_601977A9CD6DB');
define('BUILDING_HEIGHT', 'UF_CRM_601977A9E2216');
define('BUILDING_PRICE', 'UF_CRM_1638995468154');
define('ADDENDUM_PERMIT_APPROVAL', 'Order conditional upon Permit Approval until _____________________. Buyer undertakes to use best efforts to obtain approval and shall provide contact information for City, Town, Municipality from which approval is sought. Buyer agrees to provide Permit Application to Seller, if requested. If conditional order is cancelled by Buyer, any deposits shall be reimbursed (less $_____________ for Certified Engineered Drawings). Deposit shall be non-refundable after the above noted expiry date. If Building Permit is denied, the Buyer undertakes to provide the Seller supporting documentation in order for refund to be processed. The Seller shall not provide Engineer Drawings to Buyer until Financing condition is waived, unless expressly consented to by Metal Pro Buildings’ management.');
define('ADDENDUM_FINANCING_APPROVAL', 'Order conditional upon Financing Approval until _____________________. Buyer undertakes to provide requisite information for Application Processing and understands that this conditional agreement becomes firm upon Buyer’s financing approval. Buyer agrees to provide Financial Application to Seller, if requested. If conditional order is cancelled by Buyer, any deposits shall be reimbursed (less $_____________ for Certified Engineered Drawings). Deposit shall be non-refundable after the above noted expiry date. If financing application is denied, the Buyer undertakes to provide the Seller supporting documentation in order for refund to be processed. The Seller shall not provide Engineer Drawings to Buyer until Financing condition is waived, unless expressly consented to by Metal Pro Buildings’ management.');
define('ADDENDUM_BUYER_APPROVAL', 'Order conditional upon Buyer Approval until _____________________. If conditional order is cancelled by Buyer, any deposits shall be reimbursed (less $_____________ for Certified Engineered Drawings). Deposit shall be non-refundable after the above noted expiry date. If Building Permit is denied, the Buyer undertakes to provide the Seller supporting documentation in order for refund to be processed. The Seller shall not provide Engineer Drawings to Buyer until Financing condition is waived, unless expressly consented to by Metal Pro Buildings’ management.');
define('FOUNDATION_SYSTEM', 'UF_CRM_41A39AA');
define('FOUNDATION_SYSTEM_FIELD_ID', 367);
define('GAUGE', 'UF_CRM_1638985090885');
define('GAUGE_FIELD_ID', 967);
define('PROVINCE', 'UF_CRM_1645643434352');

// Avivi #30293 Limit Access to Edit Deal Card
define('DENIED_USERS_HLBT', 39);