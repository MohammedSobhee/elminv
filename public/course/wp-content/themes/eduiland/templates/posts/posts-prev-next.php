<?php
 $post_type = get_post_type() == 'eduiland_video' ? 'Video' : get_prevnext_text();
?>
<ul class="posts-next-prev">
    <li><?php previous_post_link('%link', 'Previous ' . $post_type); ?></li>
    <li><?php next_post_link('%link', 'Next ' . $post_type . ' ', ''); ?></li>
</ul>
