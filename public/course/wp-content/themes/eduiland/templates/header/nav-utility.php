<ul class="navbar-nav nav-header nav-header-utility">
    <?php
    if(eduiland()->user->loggedinas) :
        if(eduiland()->user->loggedinas == 'teacher' || eduiland()->user->loggedinas == 'assistant-teacher') : ?>
        <li class="nav-item logout"><a href="/dashboard/loginas" class="nav-link"><i class="mr-2 fas fa-angle-left"></i> Back to Teacher</a></li>
        <?php else: ?>
        <li class="nav-item logout"><a href="/dashboard/loginas" class="nav-link"><i class="mr-2 fas fa-angle-left"></i> Back to Admin</a></li>
        <?php endif; ?>
    <?php endif; ?>

    <li class="nav-item menu-search toggle-search"><a href="#" class="nav-link">Search</a>
        <div class="header-search" role="search">
            <?php get_search_form(); ?>
        </div>
    </li>

    <?php if(eduiland()->user->role != 'student' && !is_posts() && eduiland()->announcement): ?>
        <li class="nav-item nav-item-announcement">
            <a href="/course/updates" data-toggle="tooltip" title="New Update"><i class="fas fa-bell"></i></a>
        </li>
    <?php endif; ?>

    <li class="nav-item position-relative<?php echo strstr(eduiland()->url, 'profile') ? ' current-menu-item' : ''; echo eduiland()->user->avatar ? ' nav-item-avatar' : ''?>">
        <a class="nav-link dropdown-toggle dropdown-toggle-header dropdown-toggle-profile" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?php if(eduiland()->user->avatar) : ?>
        <img src="<?php echo eduiland()->user->avatar ?>" class="nav-avatar" alt="<?php echo eduiland()->user->nickname ?: eduiland()->user->firstname ?>">
        <?php else: ?>
        <i class="fas fa-user nav-header-icon nav-header-icon-avatar"></i>
        <?php endif; ?>
        <?php echo eduiland()->user->nickname ?: eduiland()->user->firstname ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
            <li class="dmi<?php echo strstr(eduiland()->url, 'account') ? ' current_page_item' : '' ?>"><a class="dropdown-item" href="/edit/account">Edit Account</a></li>
            <?php if (eduiland()->user->role != 'student'): ?>
            <li><a class="dropdown-item" href="/support">Support</a></li>
            <?php endif;
            if(!eduiland()->user->loggedinas):
            ?>
            <li><a class="dropdown-item" href="/logout">Logout</a></li>
            <?php elseif (eduiland()->user->loggedinas):?>
            <li><a href="/dashboard/loginas" class="dropdown-item">Logout of <?php echo eduiland()->user->firstname . ' ' . eduiland()->user->lastname?></a></li>
            <?php endif; ?>
        </ul>
    </li>
</ul>
