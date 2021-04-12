<div class="masthead">
  <header class="page-title-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-lg-9"><h1 class="page-title page-title-wp">
        <?php
            if (is_page()):
                echo get_which_title();
            elseif (is_posts()):
                echo get_the_title(get_pageid_by_slug('updates'));
            elseif (is_search()):
                _e('Search Results ', 'eduiland');
            elseif (is_404()):
                _e('Not Found', 'eduiland');
            else:
                echo get_which_title();
            endif;
        ?>
        </h1></div>

        <?php
        if(class_exists('Edu') && eduiland()->user->role != 'student'):
        if(count(eduiland()->user->courseware_types) > 1) : ?>
        <div class="col-lg-3 d-flex justify-content-end">
            <form class="align-self-center w-100">
                <select class="custom-select select-url-change custom-select-courseware custom-select-light-arrow">
                    <option>Select Courseware...</option>
                    <?php
                        foreach(eduiland()->user->courseware_types as $key => $value) :
                        $type = $key > 2 ? 'hs' : 'em';
                    ?>
                    <option value="/course/<?php echo $type?>/?ct=<?php echo $key?>"<?php if(eduiland()->user->class_type == $key): echo ' selected'; endif; ?>><?php echo $value ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php else: ?>
        <div class="col-lg-3 d-flex justify-content-end">
          <span class="page-tagline align-self-center">
            <?php
                if(array_key_exists(eduiland()->user->class_type, eduiland()->user->courseware_types)) :
                    echo eduiland()->user->courseware_types[eduiland()->user->class_type];
                else :
                    echo 'Elementary Courseware';
                endif;
            ?>
          </span>
        </div>
        <?php endif; endif;?>

      </div>
    </div>
  </header>
</div>
