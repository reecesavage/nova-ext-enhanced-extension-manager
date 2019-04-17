<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<?php echo text_output($header, 'h1', 'page-head');?>

<div class="ext-extensionManager-container">
<?php foreach ( $extensions as $extName => $extData ) { ?>
	<div class="ext-extensionManager-box <?php echo $extData['classes']; ?>">
		<div class="ext-extensionManager-box-title"><?php echo text_output( $extName, 'h2' ); ?></div>
		<div class="ext-extensionManager-box-status fontSmall">
			<span class="ext-extensionManager-box-status-available"><?php echo $extData['status']['available']; ?></span> |
			<span class="ext-extensionManager-box-status-enabled"><?php echo $extData['status']['enabled']; ?></span>
		</div>
		<div class="ext-extensionManager-box-content">
			<div class="ext-extensionManager-box-content-description"><?php echo text_output( $extData['description'], 'p' ); ?></div>
			<div class="ext-extensionManager-box-content-actions">
				<?php if ( !$extData['mandatory'] ) { ?>
					<?php echo form_open( 'extensions/ExtensionManager/Manage/toggle/' . $extName );?>
					<?php echo form_button( $extData['button'] );?>
					<?php echo form_hidden( 'action', $extData['action'] );?>
					<?php echo form_close(); ?>
				<?php } ?>
			</div>
			<div class="ext-extensionManager-box-content-details fontSmall gray"><?php echo $extData['details']; ?></div>
		</div>
	</div>
<?php } ?>
