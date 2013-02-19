<div class="users form">
<?php echo $this->Form->create('User');?>
	<fieldset>
		<legend><?php echo __('Change Mobile Number'); ?></legend>
	<?php
		echo $this->Form->input('mobile');
	?>
    <div>Enter your Smart mobile number (ex. 639181234567)</div>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back'), array('controller' => 'users', 'action' => 'index')); ?> </li>
	</ul>
</div>
