<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php echo text_output($header, 'h1');?>

<?php echo form_open('extensions/ExtensionManager/Manage/manage/submit');?>
<table class="table100 zebra">
	<tbody>
		<tr>
			<th><?php echo $labels['extension_name']; ?></th>
			<th><?php echo $labels['extension_availability']; ?></th>
			<th><?php echo $labels['extension_status']; ?></th>
			<th><?php echo $labels['extension_note']; ?></th>
		</tr>
<?php foreach ( $extensions as $extName => $extData ) { ?>
	<tr class="<?php echo $extData['classes']?>">
		<td class="ext-ExtensionManager-cell-name">
			<strong class="fontMedium"><?php echo $extName; ?></strong>
<?php if ( $extData['labels']['description'] ) { ?>
			<p class="fontSmall gray"><?php echo $extData['labels']['description'];?></p>
<?php } ?>
			<p class="fontSmall gray"><?php echo $extData['labels']['details'];?></p>
		</td>
		<td class="ext-ExtensionManager-cell-availability"><?php echo $extData['labels']['availability']; ?></span></td>
		<td class="ext-ExtensionManager-cell-status">
			<?php echo form_checkbox('enabled_extension[]', $extName, $extData['checkbox']['value'], 'class="hud" id="activate_extension"' . $extData['checkbox']['disabled'] );?>
			<?php echo form_label($extData['labels']['enabled'], 'primary');?>
		</td>
		<td class="ext-ExtensionManager-cell-note"><?php echo $extData['labels']['note']; ?></td>
	</tr>
<?php } ?>
	</tbody>
</table>
<div class="ext-ExtensionManager-button-apply">
	<?php echo form_button( $buttons['apply'] ); ?>
</div>
