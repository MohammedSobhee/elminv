{{-- Admin Top Menu --}}
@role('admin', 'developer', 'manager')
<ul id="nav-header" class="navbar-nav mr-auto nav-header">
    <li class="nav-item{{ !preg_match('/eduadmin\/.+.*$/', $uri) ? ' current-menu-item' : '' }}"><a href="/eduadmin" class="nav-link"><i class="fas fa-tachometer-alt nav-header-icon mr-2"></i>Dashboard</a></li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="gotoDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Go to Courseware</a>
        <ul class="dropdown-menu" aria-labelledby="gotoDropdown">
            <li><a class="dropdown-item" href="/course/hs/?ct=4">High School</a></li>
            <li><a class="dropdown-item" href="/course/hs/?ct=3">Middle School</a></li>
            <li><a class="dropdown-item" href="/course/em/?ct=1">Elementary</a></li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="editDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Edit</a>
        <ul class="dropdown-menu" aria-labelledby="editDropdown">
            <li><a class="dropdown-item" href="/course/wp-admin">Edit Course Pages</a></li>
            <li><a class="dropdown-item" href="/course/wp-admin/edit.php">Edit/Add Announcements</a></li>
            <li><a class="dropdown-item" href="https://inventionlandinstitute.com/loginilandinst/" target="_blank">Edit Main Website</a></li>
            <li><a class="dropdown-item" href="https://inventionland-institute.myshopify.com/admin" target="_blank">Shopify</a></li>
            <li><a class="dropdown-item" href="https://www.constantcontact.com/login" target="_blank">Constant Contact</a></li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="editDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Tools</a>
        <ul class="dropdown-menu" aria-labelledby="editDropdown">
            <li><a class="dropdown-item" href="/course/updates">View Announcements</a></li>
            <li><a class="dropdown-item" href="https://bitbucket.org/flyingcork/edu2.inventionlandinstitute.com/commits/" target="_blank">Changelog</a></li>
            <li><a class="dropdown-item" href="https://bitbucket.org/flyingcork/edu2.inventionlandinstitute.com/issues" target="_blank">Issue Tracker</a></li>
            <li><a class="dropdown-item" href="/eduadmin/activity" target="_blank">User Activity Log</a></li>
            <li><a class="dropdown-item" href="/eduadmin/edit/screenshot">Replace Screenshot</a></li>
        </ul>
    </li>
</ul>
@endrole
