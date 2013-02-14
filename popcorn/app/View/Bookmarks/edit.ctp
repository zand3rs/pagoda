<div class="bookmarks form">
<?php echo $this->Form->create('Bookmark');?>
	<fieldset>
		<legend><?php echo __('Edit Clip!'); ?></legend>
	<?php
		echo $this->Form->input('id', array('type' => 'hidden'));
		echo $this->Form->input('user_id', array('type' => 'hidden'));
		echo $this->Form->input('title');
		echo $this->Form->input('url');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('My Clippings'), array('action' => 'index'));?></li>
	</ul>
</div>
