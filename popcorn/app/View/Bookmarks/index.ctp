<div class="bookmarks index">
	<h2><?php echo __('Bookmarks');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('title');?></th>
			<th><?php echo $this->Paginator->sort('url');?></th>
			<th><?php echo $this->Paginator->sort('created');?></th>
	</tr>
	<?php foreach ($bookmarks as $bookmark): ?>
	<tr>
		<td>
        <?php
            if ($bookmark['Bookmark']['local_path']) {
                echo $this->Html->link(h($bookmark['Bookmark']['title']), h($bookmark['Bookmark']['local_path']), array('target' => '_blank'));
            } else {
                echo h($bookmark['Bookmark']['title']);
            }
        ?>
        &nbsp;</td>
		<td><?php echo h($bookmark['Bookmark']['url']); ?>&nbsp;</td>
		<td><?php echo h($bookmark['Bookmark']['created']); ?>&nbsp;</td>
	</tr>
    <?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Bookmark'), array('action' => 'add')); ?> </li>
	</ul>
</div>
