<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');?>
<?
use Bitrix\Main\Context;
$request = Context::getCurrent()->getRequest();
?>
<div class="calculation_global_wrapper">
    <table class="calculation">
        <thead>
            <tr>
                <th colspan="5"><?=GetMessage('MODEL_CALCULATION');?><?=isset($request["MODEL"]) && !empty($request["MODEL"]) ? $request["MODEL"] : '';?></th>
                <th colspan="5"><?=GetMessage('GA_CALCULATION');?><?=isset($request["GAUGE_INDEX"]) && !empty($request["GAUGE_INDEX"]) ? $request["GAUGE_INDEX"] : '';?></th>
            </tr>
        </thead>
        <tbody>
            <tr class="section_head">
                <td colspan="10"><?=GetMessage('ARCHES_CALCULATION');?></td>
            </tr>
            <tr class="section">
                <td><?=isset($request["ARCHES"]) && !empty($request["ARCHES"]) ? $request["ARCHES"] : '';?></td>
                <td><?=GetMessage('ARCHES_CALCULATION');?></td>
                <td><?=isset($request["ARCH_UNIT_COST"]) && !empty($request["ARCH_UNIT_COST"]) ? '$'.number_format($request["ARCH_UNIT_COST"], 2, '.', ',') : '';?></td>
                <td>=</td>
                <td><?=isset($request["ARCHES_COST"]) && !empty($request["ARCHES_COST"]) ? '$'.number_format($request["ARCHES_COST"], 2, '.', ',') : '';?></td>
                <td></td>
                <td><?=GetMessage("CAULKING_CALCULATION");?></td>
                <td><?=isset($request["ARCHES_CAULKING"]) && !empty($request["ARCHES_CAULKING"]) ? '$'.number_format($request["ARCHES_CAULKING"], 2, '.', ',') : '';?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="7"></td>
                <td class="total" colspan="3"><?=GetMessage('TOTAL_CALCULATION');?><?=isset($request["ARCHES_TOTAL"]) && !empty($request["ARCHES_TOTAL"]) ? '$'.number_format($request["ARCHES_TOTAL"], 2, '.', ',') : '';?></td>
            </tr>

            <tr class="section_head">
                <td class="border_r" colspan="5"><?=GetMessage('FRONT_WALL_CALCULATION');?></td>
                <td colspan="5"><?=GetMessage('REAR_WALL_CALCULATION');?></td>
            </tr>
            <tr class="section">
                <td><?=isset($request["ENDWALL_FRONT_QUANTITY"]) && !empty($request["ENDWALL_FRONT_QUANTITY"]) ? $request["ENDWALL_FRONT_QUANTITY"] : 0;?></td>
                <td><?=GetMessage('ENDWALLS_FRONT_CALCULATION');?></td>
                <td><?=isset($request["ENDWALLS_FRONT"]) && !empty($request["ENDWALLS_FRONT"]) ? '$'.number_format($request["ENDWALLS_FRONT"], 2, '.', ',') : '';?></td>
                <td>=</td>
                <td class="border_r"><?='$'.number_format(($request["ENDWALL_FRONT_QUANTITY"] * $request["ENDWALLS_FRONT"]), 2, '.', ',')?></td>
                <td><?=isset($request["ENDWALL_REAR_QUANTITY"]) && !empty($request["ENDWALL_REAR_QUANTITY"]) ? $request["ENDWALL_REAR_QUANTITY"] : 0;?></td>
                <td><?=GetMessage('ENDWALLS_REAR_CALCULATION');?></td>
                <td><?=isset($request["ENDWALLS_REAR"]) && !empty($request["ENDWALLS_REAR"]) ? '$'.number_format($request["ENDWALLS_REAR"], 2, '.', ',') : '';?></td>
                <td>=</td>
                <td><?='$'.number_format(($request["ENDWALL_REAR_QUANTITY"] * $request["ENDWALLS_REAR"]), 2, '.', ',')?></td>
            </tr>
            <tr class="section">
                <td><?=isset($request["OUTER_CA_FRONT_QUANTITY"]) && !empty($request["OUTER_CA_FRONT_QUANTITY"]) ? $request["OUTER_CA_FRONT_QUANTITY"] : 0;?></td>
                <td><?=GetMessage('OUTER_CA_FRONT_CALCULATION');?></td>
                <td><?=isset($request["OUTER_CA_FRONT"]) && !empty($request["OUTER_CA_FRONT"]) ? '$'.number_format($request["OUTER_CA_FRONT"], 2, '.', ',') : '';?></td>
                <td>=</td>
                <td class="border_r"><?='$'.number_format(($request["OUTER_CA_FRONT"] * $request["OUTER_CA_FRONT_QUANTITY"]), 2, '.', ',')?></td>
                <td><?=isset($request["OUTER_CA_REAR_QUANTITY"]) && !empty($request["OUTER_CA_REAR_QUANTITY"]) ? $request["OUTER_CA_REAR_QUANTITY"] : 0;?></td>
                <td><?=GetMessage('OUTER_CA_REAR_CALCULATION');?></td>
                <td><?=isset($request["OUTER_CA_REAR"]) && !empty($request["OUTER_CA_REAR"]) ? '$'.number_format($request["OUTER_CA_REAR"], 2, '.', ',') : '';?></td>
                <td>=</td>
                <td><?='$'.number_format(($request["OUTER_CA_REAR"] * $request["OUTER_CA_REAR_QUANTITY"]), 2, '.', ',')?></td>
            </tr>
            <tr class="section">
                <td><?=isset($request["FRONT_WALL_SEA_HEIGHT"]) && !empty($request["FRONT_WALL_SEA_HEIGHT"]) ? $request["FRONT_WALL_SEA_HEIGHT"] : 0;?></td>
                <td><?=GetMessage('ENDWALL_EXTENSION');?></td>
                <td><?=isset($request["ENDWALL_EXTENSION"]) && !empty($request["ENDWALL_EXTENSION"]) ? '$'.number_format($request["ENDWALL_EXTENSION"], 2, '.', ',') : '';?></td>
                <td>=</td>
                <td class="border_r"><?='$'.number_format(($request["ENDWALL_EXTENSION"] * $request["FRONT_WALL_SEA_HEIGHT"]), 2, '.', ',')?></td>
                <td><?=isset($request["REAR_WALL_SEA_HEIGHT"]) && !empty($request["REAR_WALL_SEA_HEIGHT"]) ? $request["REAR_WALL_SEA_HEIGHT"] : 0;?></td>
                <td><?=GetMessage('ENDWALL_EXTENSION');?></td>
                <td><?=isset($request["ENDWALL_EXTENSION"]) && !empty($request["ENDWALL_EXTENSION"]) ? '$'.number_format($request["ENDWALL_EXTENSION"], 2, '.', ',') : '';?></td>
                <td>=</td>
                <td><?='$'.number_format(($request["ENDWALL_EXTENSION"] * $request["REAR_WALL_SEA_HEIGHT"]), 2, '.', ',')?></td>
            </tr>
            <tr class="section">
                <td><?=isset($request["FRONT_WALL_OFFSET"]) && !empty($request["FRONT_WALL_OFFSET"]) ? $request["FRONT_WALL_OFFSET"] : 0;?></td>
                <td><?=GetMessage('ENDWALL_OFFSET');?></td>
                <td><?=isset($request["ENDWALLS_FRONT"]) && !empty($request["ENDWALLS_FRONT"]) ? '$'.number_format($request["ENDWALLS_FRONT"] * 0.1, 2, '.', ',') : '';?></td>
                <td>=</td>
                <td class="border_r"><?='$'.number_format(($request["ENDWALLS_FRONT"]*0.1 * $request["FRONT_WALL_OFFSET"]), 2, '.', ',')?></td>
                <td><?=isset($request["REAR_WALL_OFFSET"]) && !empty($request["REAR_WALL_OFFSET"]) ? $request["REAR_WALL_OFFSET"] : 0;?></td>
                <td><?=GetMessage('ENDWALL_OFFSET');?></td>
                <td><?=isset($request["ENDWALLS_REAR"]) && !empty($request["ENDWALLS_REAR"]) ? '$'.number_format($request["ENDWALLS_REAR"] * 0.1, 2, '.', ',') : '';?></td>
                <td>=</td>
                <td><?='$'.number_format(($request["ENDWALLS_REAR"]*0.1 * $request["REAR_WALL_OFFSET"]), 2, '.', ',')?></td>
            </tr>
            <tr class="border_t">
                <td colspan="7"></td>
                <td class="total" colspan="3"><?=GetMessage('TOTAL_CALCULATION');?><?=isset($request["WALL_TOTAL"]) && !empty($request["WALL_TOTAL"]) ? '$'.number_format($request["WALL_TOTAL"], 2, '.', ',') : '';?></td>
            </tr>

            <tr class="section_head">
                <td class="border_r" colspan="5"><?=GetMessage('CHANNEL_FOUNDATION_SYSTEMS_CALCULATION');?></td>
                <td colspan="5"><?=GetMessage('BASEPLATE_ARCH_FOUNDATION_SYSTEM_CALCULATION');?></td>
            </tr>
            <tr class="section">
                <td><?=isset($request["CHANNEL_ARCH_LENGTH"]) && !empty($request["CHANNEL_ARCH_LENGTH"]) ? round($request["CHANNEL_ARCH_LENGTH"], 2) : 0;?></td>
                <td><?=GetMessage('CHANNEL_ARCH_CALCULATION');?></td>
                <td><?=isset($request["CHANNEL_ARCH_UNIT_COST"]) && !empty($request["CHANNEL_ARCH_UNIT_COST"]) ? '$'.number_format($request["CHANNEL_ARCH_UNIT_COST"], 2, '.', ',') : '$0,00';?></td>
                <td>=</td>
                <td class="border_r"><?=isset($request["CHANNEL_ARCH_TOTAL"]) && !empty($request["CHANNEL_ARCH_TOTAL"]) ? '$'.number_format($request["CHANNEL_ARCH_TOTAL"], 2, '.', ',') : '$0,00';?></td>
                <td><?=isset($request["ARCH_BASEPLATE_LENGTH"]) && !empty($request["ARCH_BASEPLATE_LENGTH"]) ? round($request["ARCH_BASEPLATE_LENGTH"], 2) : 0;?></td>
                <td><?=GetMessage('ARCH_BASEPLATE_CALCULATION');?></td>
                <td><?=isset($request["ARCH_BASEPLATE_UNIT_COST"]) && !empty($request["ARCH_BASEPLATE_UNIT_COST"]) ? '$'.number_format($request["ARCH_BASEPLATE_UNIT_COST"], 2, '.', ',') : '$0,00';?></td>
                <td>=</td>
                <td><?=isset($request["ARCH_BASEPLATE_TOTAL"]) && !empty($request["ARCH_BASEPLATE_TOTAL"]) ? '$'.number_format($request["ARCH_BASEPLATE_TOTAL"], 2, '.', ',') : '$0,00';?></td>
            </tr>
            <tr class="section">
                <td><?=isset($request["CHANNEL_ENDWALL_LENGTH"]) && !empty($request["CHANNEL_ENDWALL_LENGTH"]) ? round($request["CHANNEL_ENDWALL_LENGTH"], 2) : 0;?></td>
                <td><?=GetMessage('CHANNEL_ENDWALL_CALCULATION');?></td>
                <td><?=isset($request["CHANNEL_ENDWALL_UNIT_COST"]) && !empty($request["CHANNEL_ENDWALL_UNIT_COST"]) ? '$'.number_format($request["CHANNEL_ENDWALL_UNIT_COST"], 2, '.', ',') : '$0,00';?></td>
                <td>=</td>
                <td class="border_r"><?=isset($request["CHANNEL_ENDWALL_TOTAL"]) && !empty($request["CHANNEL_ENDWALL_TOTAL"]) ? '$'.number_format($request["CHANNEL_ENDWALL_TOTAL"], 2, '.', ',') : '$0,00';?></td>
                <td><?=isset($request["ENDWALL_BASEPLATE_LENGTH"]) && !empty($request["ENDWALL_BASEPLATE_LENGTH"]) ? round($request["ENDWALL_BASEPLATE_LENGTH"], 2) : 0;?></td>
                <td><?=GetMessage('ENDWALL_BASEPLATE_CALCULATION');?></td>
                <td><?=isset($request["ENDWALL_UNIT_COST"]) && !empty($request["ENDWALL_UNIT_COST"]) ? '$'.number_format($request["ENDWALL_UNIT_COST"], 2, '.', ',') : '$0,00';?></td>
                <td>=</td>
                <td><?=isset($request["ENDWALL_BASEPLATE_TOTAL"]) && !empty($request["ENDWALL_BASEPLATE_TOTAL"]) ? '$'.number_format($request["ENDWALL_BASEPLATE_TOTAL"], 2, '.', ',') : '$0,00';?></td>
            </tr>
            <tr class="border_t">
                <td colspan="7"></td>
                <td class="total" colspan="3"><?=GetMessage('TOTAL_CALCULATION');?><?=isset($request["FOUNDATION_SYSTEM_TOTAL"]) && !empty($request["FOUNDATION_SYSTEM_TOTAL"]) ? '$'.number_format($request["FOUNDATION_SYSTEM_TOTAL"], 2, '.', ',') : '$0,00';?></td>
            </tr>

            <tr class="section_head">
                <td colspan="10"><?=GetMessage('ANCHORS_WEDGES_CALCULATION');?></td>
            </tr>
            <tr class="section">
                <td><?=isset($request["ARCH_ANCHOR_WEDGES_QUANTITY"]) && !empty($request["ARCH_ANCHOR_WEDGES_QUANTITY"]) ? $request["ARCH_ANCHOR_WEDGES_QUANTITY"] : 0;?></td>
                <td><?=GetMessage('ARCH_ANCHOR_WEDGES_CALCULATION');?></td>
                <td><?=isset($request["ARCH_ANCHOR_WEDGES"]) && !empty($request["ARCH_ANCHOR_WEDGES"]) ? '$'.number_format($request["ARCH_ANCHOR_WEDGES"], 2, '.', ',') : '$0,00';?></td>
                <td>=</td>
                <td><?=isset($request["ARCH_ANCHOR_WEDGES_COST"]) && !empty($request["ARCH_ANCHOR_WEDGES_COST"]) ? '$'.number_format($request["ARCH_ANCHOR_WEDGES_COST"], 2, '.', ',') : '$0,00';?></td>
                <td><?=isset($request["ENDWALL_ANCHOR_WEDGES_QUANTITY"]) && !empty($request["ENDWALL_ANCHOR_WEDGES_QUANTITY"]) ? round($request["ENDWALL_ANCHOR_WEDGES_QUANTITY"]) : 0;?></td>
                <td><?=GetMessage('ENDWALL_ANCHOR_WEDGES_CALCULATION');?></td>
                <td><?=isset($request["ENDWALL_ANCHOR_WEDGES"]) && !empty($request["ENDWALL_ANCHOR_WEDGES"]) ? '$'.number_format($request["ENDWALL_ANCHOR_WEDGES"], 2, '.', ',') : '$0,00';?></td>
                <td>=</td>
                <td><?=isset($request["ENDWALL_ANCHOR_WEDGES_COST"]) && !empty($request["ENDWALL_ANCHOR_WEDGES_COST"]) ? '$'.number_format($request["ENDWALL_ANCHOR_WEDGES_COST"], 2, '.', ',') : '$0,00';?></td>
            </tr>
            <tr>
                <td colspan="7"></td>
                <td class="total" colspan="3"><?=GetMessage('TOTAL_CALCULATION');?><?=isset($request["ANCHORS_WEDGES_TOTAL"]) && !empty($request["ANCHORS_WEDGES_TOTAL"]) ? '$'.number_format($request["ANCHORS_WEDGES_TOTAL"], 2, '.', ',') : '$0,00';?></td>
            </tr>

            <tr class="section_head">
                <td colspan="10"><?=GetMessage('ACCESSORIES_CALCULATION');?></td>
            </tr>
            <?foreach($request["ACCESSORIES"] as $accessory):?>
            <tr class="section">
                <td><?=isset($accessory["ACCESSORIES_QUANTITY"]) && !empty($accessory["ACCESSORIES_QUANTITY"]) ? $accessory["ACCESSORIES_QUANTITY"] : 0;?></td>
                <td><?=isset($accessory["VALUE"]) && !empty($accessory["VALUE"]) ? $accessory["VALUE"] : "";?></td>
                <td><?=isset($accessory["ACCESSORIES_AMOUNT"]) && !empty($accessory["ACCESSORIES_AMOUNT"]) ? '$'.number_format(($accessory["ACCESSORIES_AMOUNT"] / $accessory["ACCESSORIES_QUANTITY"]), 2, '.', ',') : '$0,00';?></td>
                <td>=</td>
                <td><?='$'.number_format($accessory["ACCESSORIES_AMOUNT"], 2, '.', ',')?></td>
            </tr>
            <?endforeach;?>
            <tr>
                <td colspan="7"></td>
                <td class="total" colspan="3"><?=GetMessage('TOTAL_CALCULATION');?><?=isset($request["ACCESSORIES_BLOCK_TOTAL"]) && !empty($request["ACCESSORIES_BLOCK_TOTAL"]) ? '$'.number_format($request["ACCESSORIES_BLOCK_TOTAL"], 2, '.', ',') : '$0,00';?></td>
            </tr>

            <tr class="section_head">
                <td colspan="10"><?=GetMessage('DOORS_CALCULATION');?> </td>
            </tr>
            <tr class="sub_head">
                <td colspan="2"></td>
                <td class="sub_head_name"><?=GetMessage('WIDTH_CALCULATION');?></td>
                <td></td>
                <td class="sub_head_name"><?=GetMessage('HEIGHT_CALCULATION');?></td>
            </tr>
            <?foreach($request["DOORS"] as $door):?>
            <tr class="section">
                <td><?=isset($door["DOOR_QUANTITY"]) && !empty($door["DOOR_QUANTITY"]) ? $door["DOOR_QUANTITY"] : 0;?></td>
                <td><?=isset($door["VALUE"]) && !empty($door["VALUE"]) ? $door["VALUE"] : "";?></td>
                <td><?=isset($door["DOOR_WIDTH"]) && !empty($door["DOOR_WIDTH"]) ? $door["DOOR_WIDTH"] : "";?></td>
                <td>x</td>
                <td><?=isset($door["DOOR_HEIGHT"]) && !empty($door["DOOR_HEIGHT"]) ? $door["DOOR_HEIGHT"] : "";?></td>
                <td colspan="2"></td>
                <td><?=isset($door["DOOR_AMOUNT"]) && !empty($door["DOOR_AMOUNT"]) ? '$'.number_format(($door["DOOR_AMOUNT"] / $door["DOOR_QUANTITY"]), 2, '.', ',') : '$0,00';?></td>
                <td>=</td>
                <td><?='$'.number_format($door["DOOR_AMOUNT"], 2, '.', ',')?></td>
            </tr>
            <?endforeach;?>
            <tr>
                <td colspan="7"></td>
                <td class="total" colspan="3"><?=GetMessage('TOTAL_CALCULATION');?><?=isset($request["DOORS_BLOCK_TOTAL"]) && !empty($request["DOORS_BLOCK_TOTAL"]) ? '$'.number_format($request["DOORS_BLOCK_TOTAL"], 2, '.', ',') : '$0,00';?></td>
            </tr>
            <tr class="section_head">
                <td colspan="10" style="height: 33px"></td>
            </tr>

			<tr>
                <td colspan="5"></td>
                <td colspan="2">BUILDING COST (WITHOUT FACTOR)</td>
				<td class="cost" colspan="3">Cost (/w out factor): <?=isset($request["COST_WITHOUT_FACTOR"]) && !empty($request["COST_WITHOUT_FACTOR"]) ? '$'.number_format($request["COST_WITHOUT_FACTOR"], 2, '.', ',') : '$0,00';?></td>
            </tr>

            <tr>
                <td colspan="5"></td>
                <td colspan="2">BUILDING COST (WITH FACTOR)</td>
                <td class="cost" colspan="3"><?=GetMessage('СOST_CALCULATION');?><?=isset($request["COST"]) && !empty($request["COST"]) ? '$'.number_format($request["COST"], 2, '.', ',') : '$0,00';?></td>
            </tr>





            <tr class="section_head_wrap">
                <td class="border_none" colspan="4"></td>
                <td class="section_head" colspan="6"><?=GetMessage('SUMMARY_CALCULATION');?></td>
            </tr>


            <tr class="section">
                <td class="border_none" colspan="4"></td>
                <td class="name" colspan="3"><?=GetMessage('DRAWINGS_CALCULATION');?></td>
                <td colspan="1">=</td>
                <td colspan="2"><?=isset($request["DRAWINGS"]) && !empty($request["DRAWINGS"]) ? '$'.number_format($request["DRAWINGS"], 2, '.', ',') : '$0,00';?></td>
            </tr>

            <tr class="section">
                <td class="border_none" colspan="4"></td>
                <td class="name" colspan="3"><?=GetMessage('ESTIMATED_FREIGHT_CALCULATION');?> </td>
                <td colspan="1">=</td>
                <td colspan="2"><?=isset($request["ARCHES_FREIGHT_COST"]) && !empty($request["ARCHES_FREIGHT_COST"]) ? '$'.number_format($request["ARCHES_FREIGHT_COST"], 2, '.', ',') : '$0,00';?></td>
            </tr>


            <tr class="section">
                <td class="border_none" colspan="4"></td>
                <td class="name" colspan="3"><?=GetMessage('SUB_TOTAL_CALCULATION');?></td>
                <td colspan="1">=</td>
                <td colspan="2"><?=isset($request["SUB_TOTAL"]) && !empty($request["SUB_TOTAL"]) ? '$'.number_format($request["SUB_TOTAL"], 2, '.', ',') : '$0,00';?></td>
            </tr>

				<tr class="section">
                <td class="border_none" colspan="4"></td>
				<td class="name" colspan="3">VENDOR BUILDING COST</td>
                <td colspan="1">=</td>
                <td colspan="2"><?=isset($request["VENDOR_BUILDING_COST"]) && !empty($request["VENDOR_BUILDING_COST"]) ? '$'.number_format($request["VENDOR_BUILDING_COST"], 2, '.', ',') : '$0,00';?></td>
            </tr>

            <tr class="section">
                <td class="border_none" colspan="4"></td>
				<td class="name" colspan="3">VENDOR TOTAL COST (BUILDING + FREIGHT + DRAWINGS)</td>
                <td colspan="1">=</td>
                <td colspan="2"><?=isset($request["TOTAL_COST"]) && !empty($request["TOTAL_COST"]) ? '$'.number_format($request["TOTAL_COST"], 2, '.', ',') : '$0,00';?></td>
            </tr>

            <tr class="section">
                <td class="border_none" colspan="4"></td>
                <td class="name" colspan="3">SUGGESTED SALE PRICE</td>
                <td colspan="1">=</td>
                <td colspan="2"><?=isset($request["ASKING"]) && !empty($request["ASKING"]) ? '$'.number_format($request["ASKING"], 2, '.', ',') : '$0,00';?></td>
            </tr>

			<tr class="section">
                <td class="border_none" colspan="4"></td>
                <td class="name" colspan="3">BUILDING RETAIL PRICE</td>
                <td colspan="1">=</td>
                <td colspan="2"><?=isset($request["SOLD_FOR"]) && !empty($request["SOLD_FOR"]) ? '$'.number_format($request["SOLD_FOR"], 2, '.', ',') : '$0,00';?></td>
            </tr>


            <tr>
                <td class="border_none" colspan="4"></td>
                <td colspan="2"></td>
				<td colspan="1">Arches Weight (lb):</td>
                <td colspan="3"><?=isset($request["ACTUAL_ARCHES_WEIGHT"]) && !empty($request["ACTUAL_ARCHES_WEIGHT"]) ? ''.number_format($request["ACTUAL_ARCHES_WEIGHT"], 2, '.', ',') : '0.00';?> lbs</td>
            </tr>
<tr>
                <td class="border_none" colspan="4"></td>
                <td colspan="2"></td>
				<td colspan="1">Front Endwall Weight:</td>
                <td colspan="3"><?=isset($request["ENDWALLS_FRONT_TOTAL_LBS"]) && !empty($request["ENDWALLS_FRONT_TOTAL_LBS"]) ? ''.number_format($request["ENDWALLS_FRONT_TOTAL_LBS"], 2, '.', ',') : '0.00';?> lbs</td>
            </tr>

<tr>
                <td class="border_none" colspan="4"></td>
                <td colspan="2"></td>
				<td colspan="1">Rear Endwall Weight:</td>
                <td colspan="3"><?=isset($request["ENDWALLS_REAR_TOTAL_LBS"]) && !empty($request["ENDWALLS_REAR_TOTAL_LBS"]) ? ''.number_format($request["ENDWALLS_REAR_TOTAL_LBS"], 2, '.', ',') : '0.00';?> lbs</td>
            </tr>


<tr>
                <td class="border_none" colspan="4"></td>
                <td colspan="2"></td>
				<td colspan="1">Total Weight:</td>
                <td colspan="3"><?=isset($request["ENDWALLS_TOTAL_LBS2"]) && !empty($request["ENDWALLS_TOTAL_LBS2"]) ? ''.number_format($request["ENDWALLS_TOTAL_LBS2"], 2, '.', ',') : '0.00';?> lbs</td>
            </tr>

            <tr class="section">
                <td class="border_none" colspan="4"></td>
                <td class="name" colspan="2"><?=GetMessage('DEPOSIT_REQUIRED_CALCULATION');?></td>
                <td colspan="1">-</td>
                <td colspan="1">=</td>
                <td colspan="2"><?=isset($request["DEPOSIT_REQUIRED"]) && !empty($request["DEPOSIT_REQUIRED"]) ? '$'.number_format($request["DEPOSIT_REQUIRED"], 2, '.', ',') : '$0,00';?></td>
            </tr>
            <tr class="section">
                <td class="border_none" colspan="4"></td>
                <td class="name" colspan="2"><?=GetMessage('PROFIT_CALCULATION');?></td>
                <td colspan="1">-</td>
                <td colspan="1">=</td>
                <td class="profit_bg_color" colspan="2"><?=isset($request["PROFIT"]) && !empty($request["PROFIT"]) ? '$'.number_format($request["PROFIT"], 2, '.', ',') : '$0,00';?></td>
            </tr>
        </tbody>
        <tfoot class="calculation_footer">
            <tr>
                <td colspan="2"><?=GetMessage('CITY_CALCULATION');?> <?=isset($request["СITY"]) && !empty($request["СITY"]) ? $request["СITY"] : '';?></td>
                <td colspan="3"><?=GetMessage('SNOW_LOAD_CALCULATION');?> <?=isset($request["SNOW_LOAD"]) && !empty($request["SNOW_LOAD"]) ? $request["SNOW_LOAD"] : '';?></td>
                <td colspan="2"><?=GetMessage('RAIN_LOAD_CALCULATION');?> <?=isset($request["RAIN_LOAD"]) && !empty($request["RAIN_LOAD"]) ? $request["RAIN_LOAD"] : '';?></td>
                <td colspan="3"><?=GetMessage('WIND_LOAD_CALCULATION');?> <?=isset($request["WIND_LOAD"]) && !empty($request["WIND_LOAD"]) ? $request["WIND_LOAD"] : '';?></td>
            </tr>
            <tr>

				<?php if (($request["ACTUAL_USE_EXPOSURE"] <= 2 && $request["REQUIRED_LIVE_LOAD_CATEGORY_II"] > $request["ACTUAL_MODEL_LIVE_LOAD_PSF"]) || ($request["ACTUAL_USE_EXPOSURE"] >= 3 && $request["REQUIRED_LIVE_LOAD_CATEGORY_I"] > $request["ACTUAL_MODEL_LIVE_LOAD_PSF"])) { ?>
				<td style="background-color: #c53436; color: white;" colspan="3"><?=GetMessage('MODEL_LIVE_LOAD_CALCULATION');?> <?=isset($request["ACTUAL_MODEL_LIVE_LOAD_PSF"]) && !empty($request["ACTUAL_MODEL_LIVE_LOAD_PSF"]) ? $request["ACTUAL_MODEL_LIVE_LOAD_PSF"] : '';?></td>
				<?php } else { ?>
					<td colspan="3"><?=GetMessage('MODEL_LIVE_LOAD_CALCULATION');?> <?=isset($request["ACTUAL_MODEL_LIVE_LOAD_PSF"]) && !empty($request["ACTUAL_MODEL_LIVE_LOAD_PSF"]) ? $request["ACTUAL_MODEL_LIVE_LOAD_PSF"] : '';?></td>
				<?php } ?>

                <td colspan="3"><?=GetMessage('LIVE_LOAD_CATEGORY_I_CALCULATION');?> <?=isset($request["REQUIRED_LIVE_LOAD_CATEGORY_I"]) && !empty($request["REQUIRED_LIVE_LOAD_CATEGORY_I"]) ? $request["REQUIRED_LIVE_LOAD_CATEGORY_I"] : '';?></td>
                <td colspan="4"><?=GetMessage('LIVE_LOAD_CATEGORY_II_CALCULATION');?> <?=isset($request["REQUIRED_LIVE_LOAD_CATEGORY_I"]) && !empty($request["REQUIRED_LIVE_LOAD_CATEGORY_II"]) ? $request["REQUIRED_LIVE_LOAD_CATEGORY_II"] : '';?></td>
            </tr>
        </tfoot>
    </table>
</div>
<style>
.calculation_global_wrapper table{
    border-collapse: collapse;
    color: #565e6a;
    font-size: 13px;
    background-color: white;
    width: 100%;
    border: 1px solid grey;
}

.calculation_global_wrapper table th, td {
    padding: 7px;
    text-align: center;
    border: 1px solid grey;
}

td.name{
    text-align: left !important;
}

tr.section{
    padding: 10px 0;
}

.section_head{
    background-color: #d4d4d4;
    text-align: center;
    font-size: 15px;
}

table .calculation_footer *{
    text-align: left;
    border: 1px solid grey;
}

table .calculation_footer tr:first-child{
    background-color: #a8b4c5;
}

table .calculation_footer tr:last-child{
    background-color: #afc1e0;
}

.profit_bg_color{
    background-color: rgb(184, 208, 232);
}

.total, .cost{
    background-color: rgb(247, 223, 149);
    text-align: right;
}

.border_r{
    border-right: 1px solid grey;
}

.border_t{
    border-top: 1px solid grey;
}

.border_none{
    border: none;
}

</style>