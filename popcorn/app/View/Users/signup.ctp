<div class="users form">
<?php echo $this->Form->create('User');?>
	<fieldset>
		<legend><?php echo __('Sign Up'); ?></legend>
	<?php
		echo $this->Form->input('mobile');
		echo $this->Form->input('email', array('type' => 'hidden'));
		echo $this->Form->input('auth_code', array('type' => 'hidden'));
		echo $this->Form->input('access_token', array('type' => 'hidden'));
		echo $this->Form->input('token_type', array('type' => 'hidden'));
		echo $this->Form->input('token_expiry', array('type' => 'hidden'));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
