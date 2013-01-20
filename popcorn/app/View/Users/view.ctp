<div class="users view">
<h2><?php  echo __('User');?></h2>
	<dl>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($user['User']['email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile'); ?></dt>
		<dd>
			<?php echo h($user['User']['mobile']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Pin Code'); ?></dt>
		<dd>
			<?php echo h($user['User']['pin_code']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Pin Expiry'); ?></dt>
		<dd>
			<?php echo h($user['User']['pin_expiry']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile Status'); ?></dt>
		<dd>
			<?php echo h($user['User']['mobile_status']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Date Registered'); ?></dt>
		<dd>
			<?php echo h($user['User']['date_registered']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Access Token'); ?></dt>
		<dd>
			<?php echo h($user['User']['access_token']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Token Type'); ?></dt>
		<dd>
			<?php echo h($user['User']['token_type']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Token Expiry'); ?></dt>
		<dd>
			<?php echo h($user['User']['token_expiry']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('Verify Mobile'), array('action' => 'verify')); ?> </li>
	</ul>
</div>
