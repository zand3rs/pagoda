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
        <!--
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
        //-->
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
        <!--
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
        //-->
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Logout'), array('action' => 'logout')); ?> </li>
		<li><?php echo $this->Html->link(__('Change Mobile'), array('action' => 'change_mobile')); ?> </li>
        <?php if ($user['User']['mobile_status'] !== 'VERIFIED'): ?>
        <li><?php echo $this->Html->link(__('Resend Mobile Pin'), array('action' => 'generate_pin')); ?> </li>
        <li><?php echo $this->Html->link(__('Verify Mobile'), array('action' => 'verify')); ?> </li>
        <?php endif; ?>
        <li><?php echo $this->Html->link(__('My Clippings'), array('controller' => 'bookmarks')); ?> </li>
	</ul>
</div>
