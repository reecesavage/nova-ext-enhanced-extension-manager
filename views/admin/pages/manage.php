<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<?php if($this->session->flashdata('error')){ ?>
<div class="flash_message flash-error">
	<p><?php echo $this->session->flashdata('error'); ?></p></div>
<?php } ?>


<?php if($this->session->flashdata('success')){ ?>
<div class="flash_message flash-success">
	<p><?php echo $this->session->flashdata('success'); ?></p></div>
<?php } ?>


<?php echo text_output($header, 'h1', 'page-head');?>

<hr>
</br>

<div class="zebra">
<?php foreach ( $extensions as $extName => $extData ) {


 
 ?>
    

	<div id="<?php echo $extName;?>" class="padding_p5_0_p5_0">
		<table class="table100">
         <tr>
         	<td class="col_250"><?php echo text_output( $extData['title'], 'h2' ); ?></td>

         	<td class="col_100">
				<span class="ext-extensionManager-box-status-available"><?php echo $extData['status']['available']; ?></span> |
			<span class="ext-extensionManager-box-status-enabled"><?php echo $extData['status']['enabled']; ?></span>			
			</td>

			<td class="col_100">Version: <?=$extData['version']?></td>

			<td class="align_right align_middle col_100">	<?php if ( !$extData['mandatory'] ) { ?>
					<?php echo form_open( 'extensions/ext_nova_enhanced_extension_manager/Manage/toggle/' . $extName );?>
					<?php echo form_button( $extData['button'] );?>
					<?php echo form_hidden( 'action', $extData['action'] );?>
					<?php echo form_close(); ?>
				<?php } ?></td>
         </tr>
		</table>


		<div id="tr_<?php echo $extName;?>" class="hidden">
					<table class="table100">
						<tr>
                          <td class="align_top">
								 Description : <?=$extData['description']?>
							</td>
						</tr>
							<tr>
							<td class="align_top col_150">
								 Author : <?php echo $extData['details']; ?>
							</td>
							</tr>
							<tr>
							<td class="align_top col_150">
								 Required Extensions :  <?php echo $extData['required_extension']; ?>
							</td>
							</tr>

							<tr>
							<td class="align_top col_150">
								 Repository : <a target="_blank" href="<?php echo $extData['repository']; ?>"><?php echo $extData['repository']; ?></a>
							</td>
							</tr>

							<tr>


					
								

							</tr>

							

							
						
					</table>
				</div>


		<table class="table100">
					<tr>
						<td class="align_right fontSmall UITheme">
							<button class="button-small" curAction="more" id="<?php echo $extName;?>">
								<span class="ui-icon ui-icon-triangle-1-s float_right"></span>
								<span class="text">More</span>&nbsp;
							</button>
						</td>
					</tr>
				</table>

	</div>

<?php } ?>
</div>


<div>	
					<?php echo form_open_multipart( 'extensions/ext_nova_enhanced_extension_manager/Manage/upload/');?>
					  <input type="file" accept=".zip" name="upload_file" required >
					<?php echo form_button( $buttons['upload'] );?>
					<?php echo form_close(); ?>
				</div>



				<?php echo form_open('extensions/ext_nova_enhanced_extension_manager/Manage/config/');?>


			<p>
				<kbd>Directory</kbd>
				application/<input type="text" name="directory" required value="<?=$directory?>">	
			</p>

			<br>
			<button name="submit" type="submit" class="button-main" value="Submit"><span>Create Directory</span></button>
<?php echo form_close(); ?>




<div class="ext-extensionManager-credits fontSmall">
	<a href="https://github.com/reecesavage/nova-ext-enhanced-extension-manager" target="_blank">Enhanced Extension Manager</a> | Forked from  <a href="https://github.com/mooeypoo/Nova-ExtensionManager">ExtensionManager </a>
	Enhancements developed by <a href="https://simcentral.org/" target="_blank">Sim Central</a>
	
</div>
