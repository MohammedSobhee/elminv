</div></div>
</main>
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="copyright col-sm align-self-center">
                <?php echo __('&copy; ', 'eduiland') . roman_numeral(date( 'Y' )) . ' ' . get_bloginfo(); ?>
            </div>
            <div class="footer-social col-sm">
            <ul class="footer-social-buttons">
                <li><a href="https://www.facebook.com/pages/Inventionland-Institute/727086124070664?fref=ts" class="facebook" target="_blank"><span class="sr">Facebook</span></a></li>
                <li><a href="https://twitter.com/IL_Institute" class="twitter" target="_blank"><span class="sr">Twitter</span></a></li>
                <li><a href="https://www.instagram.com/il_institute" class="instagram" target="_blank"><span class="sr">Instagram</span></a></li>
            </ul>
            </div>
        </div>
    </div>
</footer>
<div class="modal fade" id="worksheetFindModal" tabindex="-1" role="dialog" aria-labelledby="worksheetFindModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-primary">
                <h5 class="modal-title" id="worksheetFindModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe src="" width="100%" height="60" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
            </div>
        </div>
    </div>
</div>
<?php wp_footer(); ?>
<img src="/wp/log" width="1" height="1" class="wp-log"/>
</body>
</html>
