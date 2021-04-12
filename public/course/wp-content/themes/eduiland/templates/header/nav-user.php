<ul id="nav-header" class="navbar-nav mr-auto nav-header">
    <li class="nav-item"><a href="/dashboard" class="nav-link"><i class="fas fa-tachometer-alt nav-header-icon mr-2"></i>Dashboard</a></li>
    <?php if((eduiland()->user->role == 'student' || eduiland()->user->role == 'teacher' || eduiland()->user->role == 'assistant-teacher')) : ?>
    <li class="nav-item"><a href="/assignments" class="nav-link"><i class="fas fa-edit nav-header-icon mr-2"></i>Assignments</a></li>
    <?php endif ?>

    <li class="nav-item dropdown<?php echo !strpos(eduiland()->url, 'resources') ? ' current-menu-item' : ''?>">
        <a class="nav-link dropdown-toggle dropdown-toggle-header" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-book-reader nav-header-icon mr-2"></i>Course Material</a>
        <?php
            $ownuser_class = eduiland()->user->loggedinas ? ' nav-ownuser' : '';
            wp_nav_menu([
                'container' => false,
                'menu_class' => 'dropdown-menu' . $ownuser_class,
                'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                'theme_location' => 'header-menu',
                'depth' => 0,
                'fallback_cb' => false,
                'walker' => new BootstrapMenu()
            ]);
        ?>
    </li>

    <?php if(eduiland()->user->role == 'teacher' || eduiland()->user->role == 'assistant-teacher' || eduiland()->user->role == 'admin'): ?>
    <li class="nav-item dropdown<?php echo strpos(eduiland()->url, 'resources') ? ' current-menu-item' : ''?>">
        <a class="nav-link dropdown-toggle dropdown-toggle-header" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-toolbox nav-header-icon mr-2"></i>Teacher Tools</a>
        <ul class="dropdown-menu">
            <?php if(eduiland()->user->role == 'teacher' || eduiland()->user->role == 'assistant-teacher'): ?>
            <li><a class="dropdown-item" href="/gradebook">Gradebook</a></li>
            <li><a class="dropdown-item" href="/edit/class">Classes</a></li>
            <li><a class="dropdown-item" href="/edit/team">Teams</a></li>
            <li><a class="dropdown-item" href="/messages">Messaging</a></li>
            <li><a class="dropdown-item" href="/edit/assignments">Custom Assignments</a></li>
            <li><a class="dropdown-item" href="/edit/duedates">Set Due Dates</a></li>
                <?php if(eduiland()->user->school_admin): ?>
                <li><a class="dropdown-item" href="/schooladmin">School Administration</a></li>
                <?php endif; ?>
            <li><a class="dropdown-item" href="/edit/settings">Settings</a></li>
            <?php endif; ?>
            <li class="dmi<?php echo strpos(eduiland()->url, 'student-resources') ? ' current_page_item' : '' ?>"><a class="dropdown-item" href="/course/student-resources">Student Resources</a></li>
            <li class="dmi<?php echo strpos(eduiland()->url, 'resources') ? ' current_page_item' : '' ?>"><a class="dropdown-item" href="/course/teacher-resources">Teacher Resources</a></li>
            <li><a class="dropdown-item" href="/course/updates">Updates &amp; Announcements</a></li>

        </ul>
    </li>
    <?php endif; ?>

    <?php if(eduiland()->user->role == 'student'):?>
    <li class="nav-item<?php echo strpos(eduiland()->url, 'resources') ? ' current-menu-item' : '' ?>"><a href="/course/student-resources" class="nav-link">Student Resources</a></li>
    <?php endif; ?>

    <?php if(is_lara_admin()): ?>
    <li class="nav-item"><a href="/eduadmin" class="nav-link">Admin Area</a></li>
    <?php endif; ?>

    <?php if(eduiland()->user->role == 'school-admin'): ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle dropdown-toggle-header" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-toolbox nav-header-icon mr-2"></i>School Administrator Tools</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/dashboard/schooltree">Member Tree</a></li>
            <li><a class="dropdown-item" href="/dashboard#add-members">Add Members</a></li>
            <li><a class="dropdown-item" href="/course/updates">Inventionland Institute Updates</a></li>
        </ul>
    </li>
    <?php endif; ?>
</ul>
