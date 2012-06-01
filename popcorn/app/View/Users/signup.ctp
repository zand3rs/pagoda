<div class="users form">
<?php echo $this->Form->create('User');?>
	<fieldset>
		<legend><?php echo __('Sign Up'); ?></legend>
	<?php
		echo $this->Form->input('mobile');
		echo $this->Form->input('email', array('type' => 'hidden'));
		echo $this->Form->input('pin_code', array('type' => 'hidden'));
		echo $this->Form->input('auth_code', array('type' => 'hidden'));
		echo $this->Form->input('access_token', array('type' => 'hidden'));
		echo $this->Form->input('token_type', array('type' => 'hidden'));
		echo $this->Form->input('token_expiry', array('type' => 'hidden'));
		echo $this->Form->input('date_registered', array('type' => 'hidden', 'value' => date('Y-m-d H:i:s')));
		echo $this->Form->input('mobile_verification_expiry', array('type' => 'hidden', 'value' => $this->Time->format('Y-m-d H:i:s', '+2 days', true)));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
