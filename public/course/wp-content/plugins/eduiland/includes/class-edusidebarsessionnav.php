
<?php
if (!defined('ABSPATH')) exit;

class EduSidebarSessionNav {

    /**
     * Show inserted assignments
     *
     * @param  object $db
     * @param  object $user
     * @return void
     */
    public static function show() {
        $db = Edu::instance()->db;
        $user = Edu::instance()->session->user;

        $results = $db->get_row("SELECT * FROM users_session_data WHERE hash = " . "'" . $_COOKIE['tgui'] . "'");
        $parent_page = json_decode($results->parent_page);
        $page_list = json_decode($results->page_list);

        if($page_list):
            if(is_object($parent_page)): ?>
                <h3 id="sidebar-header-courseware" class="sidebar-header"><a href="<?php echo $parent_page->link ?>"><?php echo $parent_page->title ?></a></h3>
            <?php
            else: ?>
                <h3 id="sidebar-header-courseware" class="sidebar-header"><span><?php echo $parent_page ?></span></h3>
            <?php
            endif; ?>
            <ul id="sidebar-courseware" class="sidebar-menu">
                <?php
                if(is_object($page_list) || is_array($page_list)): ?>
                    <?php foreach($page_list as $page): ?>
                    <li class="page_item<?php echo $page->id == $results->page_last_visited ? ' current_page_item' : '' ?>"><a href="<?php echo $page->link ?>"><?php echo $page->title ?></a></li>
                    <?php endforeach ?>
                <?php
                else: ?>
                    <?php echo $page_list ?>
                </ul>
                <?php endif; ?>
            </ul>
        <?php
        // Default
        else: ?>
            <h3 id="sidebar-header-courseware" class="sidebar-header"><a href="<?php echo EduHelpers::getHomelink()->link ?>">Get Started</a></h3>
            <?php // High School
            $hs_id = Edu::instance()->hsParentID;
            $em_id = Edu::instance()->emParentID;
            if($user->class_type > 2):
                $args = array(
                    'child_of' => $hs_id,
                    'parent' => $hs_id,
                    'depth' => 1,
                    'title_li' => null,
                    'sort_column' => 'menu_order'
                );
            else: // Elementary
                $args = array(
                    'child_of' => $em_id,
                    'parent' => $em_id,
                    'depth' => 1,
                    'title_li' => null,
                    'sort_column' => 'menu_order'
                );
            endif;
            ?>
            <ul id="sidebar-courseware" class="sidebar-menu">
                <?php wp_list_pages($args); ?>
            </ul>
        <?php
        endif;
    }
}
