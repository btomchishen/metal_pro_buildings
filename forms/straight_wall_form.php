<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/autoload.php';

CJSCore::Init(array("jquery", "ajax"));
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

global $APPLICATION;
$APPLICATION->SetTitle("Straight Wall Form");
// Js Files
$APPLICATION->AddHeadScript('/forms/script/Form.js');
$APPLICATION->AddHeadScript('/forms/script/QuonsetForm.js');
$APPLICATION->AddHeadScript('/forms/script/StraightWallForm.js');
$APPLICATION->AddHeadScript('/forms/script/events.js');
$APPLICATION->AddHeadScript('/local/assets/js/jquery.maskMoney.js');
// Style Files
$APPLICATION->SetAdditionalCSS("/forms/style/style.css", true);

$request = Context::getCurrent()->getRequest();
?>
    <script src="https://unpkg.com/imask"></script>

    <!-- Image loader -->
    <div id='loader' style='display: none; width: 100px;height: 100px;position: fixed;top: 50%;left: 50%;margin-left: -50px;margin-top:-50px;'>
        <img src='/forms/loader.gif'>
    </div>
    <!-- Image loader -->
<div class="formTypeAndDealID" id="<?=$request['FORM_TYPE'].'#'.$request['DEAL_ID'].'#'.$request['ID'];?>">
    <form id="wall_form" class="wall_form" method="post" action="">
        <!-- Sales Rep Dynamic List, get employees from Sales Department -->
        <label for="SALES_REP">Sales Rep</label><select id="SALES_REP" type="text" class="s100" name="SALES_REP"></select>
        <!-- Customer Field -->
        <label for="CUSTOMER">Customer Name</label><input id="CUSTOMER" type="text" class="w100" name="CUSTOMER"><br>
        <!-- Company Field -->
        <label for="COMPANY">Company</label><input id="COMPANY" type="text" class="w100" name="COMPANY"><br>
        <!-- Project Field -->
        <label for="PROJECT">Project Name (shows on Cert of Design & Drawing)</label><input id="PROJECT" type="text" class="w100" name="PROJECT"><br>
        <!-- Account Number Field -->
        <label for="ACCOUNT_NUMBER">Account No.</label><input id="ACCOUNT_NUMBER" type="text" class="w100" name="ACCOUNT_NUMBER"><br>
        <!-- Vendor ID Field -->
        <label for="VENDOR_ID">Vendor ID</label><input id="VENDOR_ID" type="text" class="w100" name="VENDOR_ID"><br>
        <!-- Order Status Static List -->
        <label for="ORDER_STATUS">Order Status</label><select id="ORDER_STATUS" type="text" class="s100" name="ORDER_STATUS">
            <option value=""></option>
            <option value="New Order">New Order</option>
            <option value="Drawings Only">Drawings Only</option>
        </select><br>
        <!-- Mailing Address Field -->
        <label for="MAILING_ADDRESS">Mailing Address</label><input id="MAILING_ADDRESS" type="text" class="w100" name="MAILING_ADDRESS"><br>
        <!-- Site Address Field -->
        <label for="SITE_ADDRESS">Site Address</label><input id="SITE_ADDRESS" type="text" class="w100" name="SITE_ADDRESS"><br>
        <!-- Shipping Address Field -->
        <label for="SHIPPING_ADDRESS">Shipping Address</label><input id="SHIPPING_ADDRESS" type="text" class="w100" name="SHIPPING_ADDRESS"><br>
        <!-- Primary Phone Field -->
        <label for="PRIMARY_PHONE">Primary phone</label><input id="primary_phone" type="tel" class="w100" name="PRIMARY_PHONE"><br>
        <!-- Secondary Phone Field -->
        <label for="SECONDARY_PHONE">Secondary phone</label><input id="secondary_phone" type="tel" class="w100" name="SECONDARY_PHONE"><br>
        <!-- Work Field -->
        <label for="WORK">Work</label><input id="WORK" type="text" class="w100" name="WORK"><br>
        <!-- Email Field -->
        <label for="EMAIL">Email</label><input id="EMAIL" type="email" class="w100" name="EMAIL"><br>
        <!-- Model Type Static List -->
        <label for="MODEL_TYPE">Model Type</label><select id="MODEL_TYPE" type="text" class="s100" name="MODEL_TYPE">
            <option value="" selected></option>
            <option value="Cee-Channel - R&N" >Cee-Channel - R&N</option>
            <option value="Hybrid" >Hybrid</option>
            <option value="Traditional Rigid System" >Traditional Rigid System</option>
            <option value="Cee-Channe l- CMB" >Cee-Channel - CMB</option>
        </select><br>
        <!-- Building Code Field -->
        <label for="BUILDING_CODE">Building Code</label><input id="BUILDING_CODE" type="text" class="w100" name="BUILDING_CODE"><br>
        <!-- Risk Category Static List -->
        <label for="RISK_CATEGORY">Risk Category</label><select id="RISK_CATEGORY" type="text" class="s100" name="RISK_CATEGORY">
            <option value="I-(Low) Agricultural, Storage" >I-(Low) Agricultural, Storage&nbsp;&nbsp;</option>
            <option value="II-(Normal) Standard Building" >II-(Normal) Standard Building&nbsp;&nbsp;</option>
            <option value="III-(High) Schools, Large Occupancy" >III-(High) Schools, Large Occupancy&nbsp;&nbsp;</option>
            <option value="IV-(Post) Hospitals, Fire, SheltersT" >IV-(Post) Hospitals, Fire, Shelters&nbsp;&nbsp;</option>
        </select><br>
        <!-- Roof Snow Load Field -->
        <label for="ROOF_SNOW_LOAD">Roof Snow Load</label><input id="ROOF_SNOW_LOAD" type="text" class="w100" name="ROOF_SNOW_LOAD"><br>
        <!-- Collateral Field -->
        <label for="COLLATERAL">Collateral</label><input id="COLLATERAL" type="text" class="w100" name="COLLATERAL"><br>
        <!-- Ground Snow Load Field -->
        <label for="GROUND_SNOW_LOAD">Ground Snow Load Ss</label><input id="GROUND_SNOW_LOAD" type="text" class="w100" name="GROUND_SNOW_LOAD"><br>
        <!-- Rain Load Field (showing only if #MODEL_TYPE == 'Traditional Rigid System') -->
        <label for="RAIN_LOAD" style="display: none">Rain Load Sr</label><input id="RAIN_LOAD" type="text" class="w100" name="RAIN_LOAD" style="display: none"><br>
        <!-- Sa 0.2 Field (showing only if #MODEL_TYPE == 'Traditional Rigid System' || 'Cee-Channe l- CMB') -->
        <label for="SA_02" style="display: none">Sa (0.2)</label><input id="SA_02" type="text" class="w100" name="SA_02" style="display: none"><br>
        <!-- Sa 0.5 Field (showing only if #MODEL_TYPE == 'Traditional Rigid System' || 'Cee-Channe l- CMB') -->
        <label for="SA_05" style="display: none">Sa (0.5)</label><input id="SA_05" type="text" class="w100" name="SA_05" style="display: none"><br>
        <!-- Sa 1.0 Field (showing only if #MODEL_TYPE == 'Traditional Rigid System' || 'Cee-Channe l- CMB') -->
        <label for="SA_1" style="display: none">Sa (1.0)</label><input id="SA_1" type="text" class="w100" name="SA_1" style="display: none"><br>
        <!-- Sa 2.0 Field (showing only if #MODEL_TYPE == 'Traditional Rigid System' || 'Cee-Channe l- CMB') -->
        <label for="SA_2" style="display: none">Sa (2.0)</label><input id="SA_2" type="text" class="w100" name="SA_2" style="display: none"><br>
        <!-- Building Use Static List -->
        <label for="BUILDING_USE">Building Use</label><select id="BUILDING_USE" type="text" class="s100" name="BUILDING_USE">
            <option value="" selected>&nbsp;&nbsp;</option>
            <option value="Commercial Storage - Category I" >Commercial Storage - Category I&nbsp;&nbsp;</option>
            <option value="Garage - Category I" >Garage - Category I&nbsp;&nbsp;</option>
            <option value="Residential Storage - Category I" >Residential Storage - Category I&nbsp;&nbsp;</option>
            <option value="Commercial Warehouse - Category I" >Commercial Warehouse - Category I&nbsp;&nbsp;</option>
            <option value="Private Workshop - Category II" >Private Workshop - Category I&nbsp;&nbsp;</option>
            <option value="Retail Stores - Category II" >Retail Stores - Category II&nbsp;&nbsp;</option>
            <option value="Residential House - Category II" >Residential House - Category II&nbsp;&nbsp;</option>
        </select><br>
        <!-- Building Width Field -->
        <label for="BUILDING_WIDTH">Exterior Building Width LEW & REW (FT) </label><input id="BUILDING_WIDTH" type="text" class="w100" name="BUILDING_WIDTH"><br>
        <!-- Building Length Field -->
        <label for="BUILDING_LENGTH">Building Length FSW & BSW (FT)</label><input id="BUILDING_LENGTH" type="text" class="w100" name="BUILDING_LENGTH"><br>
        <!-- Front Wall Height Field -->
        <label for="FRONT_WALL_HEIGHT">Front Wall Exterior Eave Height (FT)</label><input id="FRONT_WALL_HEIGHT" type="text" class="w100" name="FRONT_WALL_HEIGHT"><br>
        <!-- Rear Wall Height Field -->
        <label for="REAR_WALL_HEIGHT">Rear Wall Exterior Eave Height (FT)</label><input id="REAR_WALL_HEIGHT" type="text" class="w100" name="REAR_WALL_HEIGHT"><br>
        <!-- Roof Pitch Static List -->
        <label for="ROOF_PITCH">Roof Pitch (max 3:12 for Cee-Channel)</label><select id="ROOF_PITCH" type="text" class="s100" name="ROOF_PITCH">
            <option value="" selected>&nbsp;&nbsp;</option>
            <option value="1:12" >1:12&nbsp;&nbsp;</option>
            <option value="2:12" >2:12&nbsp;&nbsp;</option>
            <option value="3:12" >3:12&nbsp;&nbsp;</option>
            <option value="4:12" >4:12&nbsp;&nbsp;</option>
            <option value="8:12" >8:12&nbsp;&nbsp;</option>
            <option value="Refer to Notes" >Refer to Notes&nbsp;&nbsp;</option>
        </select><br>
        <!-- Primer Color Static List (show only if #MODEL_TYPE == 'Traditional Rigid System') -->
        <label for="PRIMER_COLOR">Primer Color</label><select id="PRIMER_COLOR" type="text" class="s100" name="PRIMER_COLOR">
            <option value="N/A" >N/A</option>
            <option value="RED frames, RED secondary" >RED frames, RED secondary</option>
        </select><br>
        <!-- Left Endwall Frame Static List -->
        <label for="LEFT_ENDWALL_FRAME">Left Endwall Frame</label><select id="LEFT_ENDWALL_FRAME" type="text" class="s100" name="LEFT_ENDWALL_FRAME">
            <option value="N/A" >N/A&nbsp;&nbsp;</option>
            <option value="Bearing Frame" >Bearing Frame&nbsp;&nbsp;</option>
            <option value="Non-Expandable" >Non-Expandable&nbsp;&nbsp;</option>
            <option value="Expandable" >Expandable&nbsp;&nbsp;</option>
            <option value="Rigid Frame" >Rigid Frame&nbsp;&nbsp;</option>
            <option value="Post and Beam" >Post and Beam&nbsp;&nbsp;</option>
        </select><br>
        <!-- Right Endwall Frame Static List -->
        <label for="RIGHT_ENDWALL_FRAME">Right Endwall Frame</label><select id="RIGHT_ENDWALL_FRAME" type="text" class="s100" name="RIGHT_ENDWALL_FRAME">
            <option value="N/A" >N/A&nbsp;&nbsp;</option>
            <option value="Bearing Frame" >Bearing Frame&nbsp;&nbsp;</option>
            <option value="Non-Expandable" >Non-Expandable&nbsp;&nbsp;</option>
            <option value="Expandable" >Expandable&nbsp;&nbsp;</option>
            <option value="Rigid Frame" >Rigid Frame&nbsp;&nbsp;</option>
            <option value="Post and Beam" >Post and Beam&nbsp;&nbsp;</option>
        </select><br>
        <!-- Frame Column Static List (show only if #MODEL_TYPE == 'Traditional Rigid System') -->
        <label for="FRAME_COLUMN">Frame Column</label><select id="FRAME_COLUMN" type="text" class="s100" name="FRAME_COLUMN">
            <option value="Straight" >Straight</option>
            <option value="Tapered" >Tapered</option>
        </select><br>
        <!-- Frame Rafter Static List (show only if #MODEL_TYPE == 'Traditional Rigid System') -->
        <label for="FRAME_RAFTER">Frame Rafter</label><select id="FRAME_RAFTER" type="text" class="s100" name="FRAME_RAFTER">
            <option value="Straight" >Straight</option>
            <option value="Tapered" >Tapered</option>
        </select><br>
        <!-- Rigid Frames Static List (show only if #MODEL_TYPE == 'Traditional Rigid System') -->
        <label for="RIGID_FRAMES">Rigid Frames</label><select id="RIGID_FRAMES" type="text" class="s100" name="RIGID_FRAMES">
            <option value="N/A" >N/A</option>
            <option value="Rigid" >Rigid</option>
        </select><br>
        <!-- Base Conditions Static List (show only if #MODEL_TYPE == 'Traditional Rigid System') -->
        <label for="BASE_CONDITIONS">Base Conditions</label><select id="BASE_CONDITIONS" type="text" class="s100" name="BASE_CONDITIONS">
            <option value="Base Angle" >Base Angle</option>
            <option value="Base Angle w/Trim" >Base Angle w/Trim</option>
            <option value="Base Angle w/Flashing" >Base Angle w/Flashing</option>
            <option value="Base Angle w/Notch" >Base Angle w/Notch</option>
            <option value="Base Channel w/Notch" >Base Channel w/Notch</option>
            <option value="Base Channel w/Flashing" >Base Channel w/Flashing</option>
        </select><br>
        <!-- LEW Bracing Static List -->
        <label for="LEW_BRACING">LEW Bracing (Self Bracing for Cee-Channel)</label><select id="LEW_BRACING" type="text" class="s100" name="LEW_BRACING">
            <option value="Self Bracing" >Self Bracing&nbsp;&nbsp;</option>
            <option value="Cable" >Cable&nbsp;&nbsp;</option>
            <option value="Rod" >Rod&nbsp;&nbsp;</option>
        </select><br>
        <!-- REW Bracing Static List -->
        <label for="REW_BRACING">REW Bracing (Self Bracing for Cee-Channel)</label><select id="REW_BRACING" type="text" class="s100" name="REW_BRACING">
            <option value="Self Bracing" >Self Bracing&nbsp;&nbsp;</option>
            <option value="Cable" >Cable&nbsp;&nbsp;</option>
            <option value="Rod" >Rod&nbsp;&nbsp;</option>
        </select><br>
        <!-- FSW Bracing Static List -->
        <label for="FSW_BRACING">FSW Bracing (Self Bracing for Cee-Channel)</label><select id="FSW_BRACING" type="text" class="s100" name="FSW_BRACING">
            <option value="Self Bracing" >Self Bracing&nbsp;&nbsp;</option>
            <option value="Cable" >Cable&nbsp;&nbsp;</option>
            <option value="Rod" >Rod&nbsp;&nbsp;</option>
        </select><br>
        <!-- BSW Bracing Static List -->
        <label for="BSW_BRACING">BSW Bracing (Self Bracing for Cee-Channel)</label><select id="BSW_BRACING" type="text" class="s100" name="BSW_BRACING">
            <option value="Self Bracing" >Self Bracing&nbsp;&nbsp;</option>
            <option value="Cable" >Cable&nbsp;&nbsp;</option>
            <option value="Rod" >Rod&nbsp;&nbsp;</option>
        </select><br>
        <!-- Roof Color Dynamic List, get from HighLoadBlock with ID = COLORS_HIGHLOAD (constant) -->
        <label for="ROOF_COLOR">Roof Color</label><select id="ROOF_COLOR" type="text" class="s100" name="ROOF_COLOR"></select><br>
        <!-- Wall Color Dynamic List, get from HighLoadBlock with ID = COLORS_HIGHLOAD (constant) -->
        <label for="WALL_COLOR">Wall Color</label><select id="WALL_COLOR" type="text" class="s100" name="WALL_COLOR"></select><br>
        <!-- Trim Color Dynamic List, get from HighLoadBlock with ID = COLORS_HIGHLOAD (constant) -->
        <label for="TRIM_COLOR">Trim Color</label><select id="TRIM_COLOR" type="text" class="s100" name="TRIM_COLOR"></select><br>
        <!-- Gutters Downs Static List -->
        <label for="GUTTERS_DOWNS">Gutters Downs</label><select id="GUTTERS_DOWNS" type="text" class="s100" name="GUTTERS_DOWNS">
            <option value="Not Included">Not included</option>
            <option value="Included">Included</option>
        </select><br>
        <!-- Gutters Downs Color Dynamic List, get from HighLoadBlock with ID = COLORS_HIGHLOAD (constant) (showing only if #GUTTERS_DOWNS.value == 'Included') -->
        <label for="GUTTERS_DOWNS_COLOR" style="display: none">Gutters & Downspouts Color (R&N building must same as Trim color)</label>
        <select id="GUTTERS_DOWNS_COLOR" style="display: none;" type="text" class="s100" name="GUTTERS_DOWNS_COLOR">
        </select><br>
        <!-- Is LEW Open checkbox -->
        <label for="IS_LEW_OPEN">Do you need framed openings on LEW?</label>
        <div class="c100">
            <div>Yes</div>
            <input id="IS_LEW_OPEN" type="checkbox" class="" name="IS_LEW_OPEN"><br>
        </div>
        <!-- LEW Quantity 1 Field (showing only if #IS_LEW_OPEN.checked == true) -->
        <label for="LEW_1_QTY" style="display: none">Left End Wall (EWB) Framed Opening 1 QTY </label><input id="LEW_1_QTY" type="text" class="w100" name="LEW_1_QTY" style="display: none;"><br>
        <!-- LEW 1 Frame Field (showing only if #LEW_1_QTY != 0) -->
        <label for="LEW_1_FRAME" style="display: none">LEW (EWB) Frame 1 W' X H'</label><input id="LEW_1_FRAME" type="text" class="w100" name="LEW_1_FRAME" style="display: none;"><br>
        <!-- LEW Quantity 2 Field (showing only if #IS_LEW_OPEN.checked == true) -->
        <label for="LEW_2_QTY" style="display: none">Left End Wall (EWB) Framed Opening 2 QTY </label><input id="LEW_2_QTY" type="text" class="w100" name="LEW_2_QTY" style="display: none;"><br>
        <!-- LEW 2 Frame Field (showing only if #LEW_2_QTY != 0) -->
        <label for="LEW_2_FRAME" style="display: none">LEW (EWB) Frame 2 W' X H'</label><input id="LEW_2_FRAME" type="text" class="w100" name="LEW_2_FRAME" style="display: none;"><br>
        <!-- LEW Areas Field (showing only if #MODEL_TYPE.value == 'Traditional Rigid System') -->
        <label for="LEW_AREAS" style="display: none">LEW Open Wall Areas</label><input id="LEW_AREAS" type="text" class="w100" name="LEW_AREAS" style="display: none;"><br>
        <!-- Is REW Open checkbox -->
        <label for="IS_REW_OPEN">Do you need framed openings on REW?</label>
        <div class="c100">
            <div>Yes</div>
            <input id="IS_REW_OPEN" type="checkbox" class="" name="IS_REW_OPEN"><br>
        </div>
        <!-- REW Quantity 1 Field (showing only if #IS_REW_OPEN.checked == true) -->
        <label for="REW_1_QTY" style="display: none">Right End Wall (EWD) Frame Opening 1 QTY</label><input id="REW_1_QTY" type="text" class="w100" name="REW_1_QTY" style="display: none;"><br>
        <!-- REW 1 Frame Field (showing only if #REW_1_QTY != 0) -->
        <label for="REW_1_FRAME" style="display: none">REW (EWD) Frame 1 W' X H'</label><input id="REW_1_FRAME" type="text" class="w100" name="REW_1_FRAME" style="display: none;"><br>
        <!-- REW Quantity 2 Field (showing only if #IS_REW_OPEN.checked == true) -->
        <label for="REW_2_QTY" style="display: none">Right End Wall (EWD) Frame Opening 2 QTY</label><input id="REW_2_QTY" type="text" class="w100" name="REW_2_QTY" style="display: none;"><br>
        <!-- REW 2 Frame Field (showing only if #REW_2_QTY != 0) -->
        <label for="REW_2_FRAME" style="display: none">REW (EWD) Frame 2 W' X H'</label><input id="REW_2_FRAME" type="text" class="w100" name="REW_2_FRAME" style="display: none;"><br>
        <!-- REW Areas Field (showing only if #MODEL_TYPE.value == 'Traditional Rigid System') -->
        <label for="REW_AREAS" style="display: none">REW Open Wall Areas</label><input id="REW_AREAS" type="text" class="w100" name="REW_AREAS" style="display: none"><br>
        <!-- Is FSW Open checkbox -->
        <label for="IS_FSW_OPEN">Do you need framed openings on FSW?</label>
        <div class="c100">
            <div>Yes</div>
            <input id="IS_FSW_OPEN" type="checkbox" class="" name="IS_FSW_OPEN"><br>
        </div>
        <!-- FSW Quantity 1 Field (showing only if #IS_FSW_OPEN.checked == true) -->
        <label for="FSW_1_QTY" style="display: none">Front Side Wall (SWA) Frame Opening 1 QTY</label><input id="FSW_1_QTY" type="text" class="w100" name="FSW_1_QTY" style="display: none;"><br>
        <!-- FSW 1 Frame Field (showing only if #FSW_1_QTY != 0) -->
        <label for="FSW_1_FRAME" style="display: none">FSW (SWA) Frame 1 W' X H'</label><input id="FSW_1_FRAME" type="text" class="w100" name="FSW_1_FRAME" style="display: none;"><br>
        <!-- FSW Quantity 2 Field (showing only if #IS_FSW_OPEN.checked == true) -->
        <label for="FSW_2_QTY" style="display: none">Front Side Wall (SWA) Frame Opening 2 QTY</label><input id="FSW_2_QTY" type="text" class="w100" name="FSW_2_QTY" style="display: none;"><br>
        <!-- FSW 12 Frame Field (showing only if #FSW_2_QTY != 0) -->
        <label for="FSW_2_FRAME" style="display: none">FSW (SWA) Frame 2 W' X H'</label><input id="FSW_2_FRAME" type="text" class="w100" name="FSW_2_FRAME" style="display: none;"><br>
        <!-- FSW Areas Field (showing only if #MODEL_TYPE.value == 'Traditional Rigid System') -->
        <label for="FSW_AREAS" style="display: none">FSW Open Wall Areas</label><input id="FSW_AREAS" type="text" class="w100" name="FSW_AREAS" style="display: none"><br>
        <!-- Is BSW Open checkbox -->
        <label for="IS_BSW_OPEN">Do you need framed openings on BSW?</label>
        <div class="c100">
            <div>Yes</div>
            <input id="IS_BSW_OPEN" type="checkbox" class="" name="IS_BSW_OPEN"><br>
        </div>
        <!-- BSW Quantity 1 Field (showing only if #IS_BSW_OPEN.checked == true) -->
        <label for="BSW_1_QTY" style="display: none">Back Side Wall (SWC) Frame Opening 1 QTY</label><input id="BSW_1_QTY" type="text" class="w100" name="BSW_1_QTY" style="display: none;"><br>
        <!-- BSW 1 Frame Field (showing only if #BSW_1_QTY != 0) -->
        <label for="BSW_1_FRAME" style="display: none">BSW (SWC) Frame 1 W' X H'</label><input id="BSW_1_FRAME" type="text" class="w100" name="BSW_1_FRAME" style="display: none;"><br>
        <!-- BSW Quantity 2 Field (showing only if #IS_BSW_OPEN.checked == true) -->
        <label for="BSW_2_QTY" style="display: none">Back Side Wall (SWC) Frame Opening 2 QTY</label><input id="BSW_2_QTY" type="text" class="w100" name="BSW_2_QTY" style="display: none;"><br>
        <!-- BSW 2 Frame Field (showing only if #BSW_2_QTY != 0) -->
        <label for="BSW_2_FRAME" style="display: none">BSW (SWC) Frame 2 W' X H'</label><input id="BSW_2_FRAME" type="text" class="w100" name="BSW_2_FRAME" style="display: none;"><br>
        <!-- BSW Areas Field (showing only if #MODEL_TYPE.value == 'Traditional Rigid System') -->
        <label for="BSW_AREAS" style="display: none">BSW Open Wall Areas</label><input id="BSW_AREAS" type="text" class="w100" name="BSW_AREAS" style="display: none"><br>
        <!-- Is Accessories checkbox -->
        <label for="IS_ACCESSORIES">Accessories</label>
        <div class="c100">
            <div>Yes</div>
            <input id="IS_ACCESSORIES" type="checkbox" class="" name="IS_ACCESSORIES"><br>
        </div>
        <!-- Service Door Static List (showing only if #IS_ACCESSORIES.checked == true) -->
        <label for="SERVICE_DOOR" style="display: none;">Service Door</label><select id="SERVICE_DOOR" type="text" style="display: none;" class="s100" name="SERVICE_DOOR">
            <option value="" selected></option>
            <option value="3x7 non-insulated" >3'W x 7'H non-insulated</option>
            <option value="3x7 Insulated" >3'W x7'H Insulated</option>
            <option value="6x7 non-insulated" >6'W x7'H non-Insulated</option>
            <option value="6x7 Insulated" >6'W x7'H Insulated</option>
        </select><br>
        <!-- Service Door Quantity Field (showing only if #SERVICE_DOOR.VALUE != '') -->
        <label for="SERVICE_DOOR_QTY" style="display: none">Service Door QTY</label><input id="SERVICE_DOOR_QTY" type="text" class="w100" name="SERVICE_DOOR_QTY" style="display: none;"><br>
        <!-- Service Door Frame Static List (showing only if #IS_ACCESSORIES.checked == true) -->
        <label for="SERVICE_DOOR_FRAME" style="display: none;">Service Door Frame</label><select id="SERVICE_DOOR_FRAME" type="text" style="display: none;" class="s100" name="SERVICE_DOOR_FRAME">
            <option value="" selected></option>
            <option value="3x7" >3'W x 7'H</option>
        </select><br>
        <!-- Service Door Frame Quantity Field (showing only if #SERVICE_DOOR_FRAME.VALUE != '') -->
        <label for="SERVICE_DOOR_FRAME_QTY" style="display: none">Service Door Frame QTY</label><input id="SERVICE_DOOR_FRAME_QTY" type="text" class="w100" name="SERVICE_DOOR_FRAME_QTY" style="display: none;"><br>
        <!-- Window Frame Field (showing only if #IS_ACCESSORIES.checked == true) -->
        <label for="WINDOW_FRAME" style="display: none">Window Frame</label><input id="WINDOW_FRAME" type="text" class="w100" name="WINDOW_FRAME" style="display: none;"><br>
        <!-- Window Frame Quantity Field (showing only if #WINDOW_FRAME.VALUE != '') -->
        <label for="WINDOW_FRAME_QTY" style="display: none">Window Frame QTY</label><input id="WINDOW_FRAME_QTY" type="text" class="w100" name="WINDOW_FRAME_QTY" style="display: none;"><br>
        <!-- Others 1 Field (showing only if #IS_ACCESSORIES.checked == true) -->
        <label for="OTHERS_1" style="display: none">Others 1</label><input id="OTHERS_1" type="text" class="w100" name="OTHERS_1" style="display: none;"><br>
        <!-- Others 1 Quantity Field (showing only if #OTHERS_1.VALUE != '') -->
        <label for="OTHERS_1_QTY" style="display: none">Others 1 QTY</label><input id="OTHERS_1_QTY" type="text" class="w100" name="OTHERS_1_QTY" style="display: none;"><br>
        <!-- Others 2 Field (showing only if #IS_ACCESSORIES.checked == true) -->
        <label for="OTHERS_2" style="display: none">Others 2</label><input id="OTHERS_2" type="text" class="w100" name="OTHERS_2" style="display: none;"><br>
        <!-- Others 2 Quantity Field (showing only if #OTHERS_2.VALUE != '') -->
        <label for="OTHERS_2_QTY" style="display: none">Others 2 QTY</label><input id="OTHERS_2_QTY" type="text" class="w100" name="OTHERS_2_QTY" style="display: none;"><br>
        <!-- Others 3 Field (showing only if #IS_ACCESSORIES.checked == true) -->
        <label for="OTHERS_3" style="display: none">Others 3</label><input id="OTHERS_3" type="text" class="w100" name="OTHERS_3" style="display: none;"><br>
        <!-- Others 3 Quantity Field (showing only if #OTHERS_3.VALUE != '') -->
        <label for="OTHERS_3_QTY" style="display: none">Others 3 QTY</label><input id="OTHERS_3_QTY" type="text" class="w100" name="OTHERS_3_QTY" style="display: none;"><br>
        <!-- Is Insulation checkbox -->
        <label for="IS_INSULATION">Do you need Insulation or Liner</label>
        <div class="c100">
            <div>Yes</div>
            <input id="IS_INSULATION" type="checkbox" class="" name="IS_INSULATION"><br>
        </div>
        <!-- Roof Insulation Static List -->
        <label for="ROOF_INSULATION" style="display: none;">Roof Insulation</label><select id="ROOF_INSULATION" type="text" style="display: none;" class="s100" name="ROOF_INSULATION">
            <option value="Not Included" >Not Included</option>
            <option value="R13" >R13</option>
            <option value="R20" >R20</option>
            <option value="R38" >R38</option>
            <option value="R60" >R60</option>
            <option value="R30" >R30</option>
        </select><br>
        <!-- Wall Insulation Static List -->
        <label for="WALL_INSULATION" style="display: none;">Wall Insulation</label><select id="WALL_INSULATION" style="display: none;" type="text" class="s100" name="WALL_INSULATION">
            <option value="Not Included" >Not Included</option>
            <option value="R13" >R13</option>
            <option value="R20" >R20</option>
            <option value="R38" >R38</option>
            <option value="R60" >R60</option>
            <option value="R30" >R30</option>
        </select><br>
        <!-- Roof Liner Static List -->
        <label for="ROOF_LINER" style="display: none;">Roof Liner</label><select id="ROOF_LINER" style="display: none;" type="text" class="s100" name="ROOF_LINER">
            <option value="Not Included" >Not Included</option>
            <option value="Included" >Included</option>
        </select><br>
        <!-- Wall Liner Static List -->
        <label for="WALL_LINER" style="display: none;">Wall Liner</label><select id="WALL_LINER" style="display: none;" type="text" class="s100" name="WALL_LINER">
            <option value="Not Included" >Not Included</option>
            <option value="Included" >Included</option>
        </select><br>
        <!-- Foundation Drawings Static List -->
        <label for="FOUNDATION_DRAWINGS">Foundation Drawings</label><select id="FOUNDATION_DRAWINGS" type="text" class="s100" name="FOUNDATION_DRAWINGS">
            <option value="Supplied by Customer">Supplied by Customer</option>
            <option value="Included">Included</option>
        </select><br>
        <!-- Foundation Drawings Send Static List -->
        <label for="FOUNDATION_DRAWINGS_SEND" style="display: none">How to send Foundation Drawing? (Please Select)</label><select id="FOUNDATION_DRAWINGS_SEND" style="display: none" type="text" class="s100" name="FOUNDATION_DRAWINGS_SEND">
            <option value="" selected></option>
            <option value="Digital Stamped Foundation Drawings (Email Only)" >Digital Stamped Foundation Drawings (Email Only)</option>
            <option value="Digital Stamped Foundation Drawings (Mail)" >Digital Stamped Foundation Drawings (Mail)</option>
        </select><br>
        <!-- Requested Delivery Month Dynamic List, get from HighLoadBlock with ID = REQUESTED_DELIVERY_MONTH_HIGHLOAD (constant) -->
        <label for="REQUESTED_DELIVERY_MONTH">Requested Delivery Month</label><select type="text" class="s100" name="REQUESTED_DELIVERY_MONTH"></select><br>
        <!-- Building Price Field, get from Deal Field with ID = BUILDING_PRICE (constant) -->
        <label for="BUILDING_PRICE">Building Price</label><input id="BUILDING_PRICE" type="text" class="w100 money-input" name="BUILDING_PRICE" placeholder="$0.00"><br>
        <!-- Tax Rate Dynamic List, get from HighLoadBlock with ID = TAX_RATE_HIGHLOAD (constant) -->
        <label for="TAX_RATE">Tax Rate</label><select id="TAX_RATE" type="text" class="s100" name="TAX_RATE"></select><br>
        <!-- Tax Dynamic Field. Tax = BuildingPrice * (TaxRate / 100) -->
        <label for="TAX">Tax</label><input type="text" class="w100 money-input" name="TAX" placeholder="$0.00"><br>
        <!-- Sub Total Dynamic Field. SubTotal = BuildingPrice + Tax -->
        <label for="SUB_TOTAL">Sub Total</label><input id="SUB_TOTAL" type="text" class="w100 money-input" name="SUB_TOTAL" placeholder="$0.00"><br>
        <!-- First Deposit Dynamic Field. FirstDeposit = SubTotal * 0.25 -->
        <label for="FIRST_DEPOSIT">First Deposit</label><input type="text" class="w100 money-input" name="FIRST_DEPOSIT" placeholder="$0.00"><br>
        <!-- First Deposit Status Static List -->
        <label for="FIRST_DEPOSIT_STATUS">First Deposit Status</label><select type="text" class="s100" name="FIRST_DEPOSIT_STATUS">
            <option value=""></option>
            <option value="Due Today">DUE Today</option>
            <option value="Paid">PAID Thank You!</option>
        </select><br>
        <!-- Payment Method Static List -->
        <label for="PAYMENT_METHOD">Payment Method</label><select type="text" class="s100" name="PAYMENT_METHOD">
            <option value="Credit Card">Credit Card&nbsp;&nbsp;</option>
            <option value="Bill Pay / Wire Transfer">Bill Pay / Wire Transfer&nbsp;&nbsp;</option>
            <option value="Pay Pal">Pay Pal&nbsp;&nbsp;</option>
        </select><br>
        <!-- Second Deposit Dynamic Field. SecondDeposit = SubTotal * 0.25 -->
        <label for="SECOND_DEPOSIT">Second Deposit</label><input type="text" class="w100 money-input" name="SECOND_DEPOSIT" placeholder="$0.00"><br>
        <!-- Second Deposit Status Static List -->
        <label for="SECOND_DEPOSIT_STATUS">Second Deposit Status</label><select type="text" class="s100" name="SECOND_DEPOSIT_STATUS">
            <option value="" selected>&nbsp;&nbsp;</option>
            <option value="Due as Requested">Due as Requested&nbsp;&nbsp;</option>
            <option value="Due Today">DUE today&nbsp;&nbsp;</option>
            <option value="Paid">PAID Thank You!&nbsp;&nbsp;</option>
        </select><br>
        <!-- Balance Remaining Dynamic Field. BalanceRemaining = SubTotal * 0.5 -->
        <label for="BALANCE_REMAINING">Balance Remaining</label><input type="text" class="w100 money-input" name="BALANCE_REMAINING" placeholder="$0.00"><br>
        <!-- Notes Field -->
        <label for="NOTES">Notes</label><textarea type="text" class="w100" name="NOTES"></textarea><br>
        <!-- Addendum 1 Static List -->
        <label for="ADDENDUM_1">Addendum 1</label><select id="ADDENDUM_1" type="text" class="s100" name="ADDENDUM_1">
            <option value="NO">Please Select If Applicable&nbsp;&nbsp;</option>
            <option value="PERMIT_APPROVAL">Conditional Order - Permit Approval&nbsp;&nbsp;</option>
            <option value="FINANCING_APPROVAL">Conditional Order - Financing Approval&nbsp;&nbsp;</option>
            <option value="BUYER_APPROVAL">Conditional Order - Buyer Approval&nbsp;&nbsp;</option>
        </select><br>
        <!--
        Addendum Label Dynamic Label, get from #ADDENDUM_1 and equal to it value
        Addendum Dynamic Field, get from #ADDENDUM_1 and equal to constant with the same name
         -->
        <label id="ADDENDUM_LABEL" for="ADDENDUM">Addendum</label><textarea id="ADDENDUM" type="text" class="w100" name="ADDENDUM"></textarea><br>
        <!-- Notes to Office Field -->
        <label for="NOTES_TO_OFFICE">Notes to back office - List any relevant info here!</label><textarea type="text" class="w100" name="NOTES_TO_OFFICE"></textarea><br>
        <!-- Submit Button. Save form to HighLoadBlock with ID = FORMS_HIGHLOAD, create PDF file and then send it to Sales Rep -->
        <input type="button" id="btn" class="submitFF" name="submit" value="Update">
        <!-- Save as New. Save form to HighLoadBlock with ID = FORMS_HIGHLOAD, create PDF file and then send it to Sales Rep -->
        <input type="button" id="btn1" class="submitFF" name="submit" value="Save New">
        <!-- Delete button -->
        <input type="button" id="delete_btn" class="deleteFF" name="delete" value="Delete">
        <!-- Clear Fields Button -->
        <input type="reset" id="res" class="resetFF" name="reset" value="Reset">
    </form>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>