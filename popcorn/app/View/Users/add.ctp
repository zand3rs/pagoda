<div class="users form">
<?php echo $this->Form->create('User');?>
	<fieldset>
		<legend><?php echo __('Add User'); ?></legend>
	<?php
		echo $this->Form->input('email');
		echo $this->Form->input('mobile');
		echo $this->Form->input('pin_code');
		echo $this->Form->input('mobile_status');
		echo $this->Form->input('mobile_verification_expiry');
		echo $this->Form->input('date_registered');
		echo $this->Form->input('auth_code');
		echo $this->Form->input('access_token');
		echo $this->Form->input('refresh_token');
		echo $this->Form->input('token_type');
		echo $this->Form->input('token_expiry');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index'));?></li>
	</ul>
</div>
