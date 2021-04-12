<?php
    $unread = is_array(eduiland()->announcement) && in_array(get_the_ID(), eduiland()->announcement);
?>

<h3 class="announcements-title<?php echo $unread ? ' announcements-unread' : '' ?>">
    <?php the_title() ?>
    <?php if ($unread): ?>
        <span class="announcements-unread-icon">Unread</span>
    <?php endif?>
</h3>
