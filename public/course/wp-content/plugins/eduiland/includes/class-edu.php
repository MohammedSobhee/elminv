<?php
if (!defined('ABSPATH')) exit;

class Edu {
    private static $_instance = NULL;
    public $db;
    public $session;
    public $hsParentID;
    public $emParentID;

	public static function instance() {
		if (is_null( self::$_instance )) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    public function __construct() {
        $this->db = (new EduDB())->connect();
        $this->session = new EduSession($this->db);
        $this->hsParentID = intval(get_option('eduiland_courseware_highschool_page'));
        $this->emParentID = intval(get_option('eduiland_courseware_elementary_page'));

        new EduSetup($this->session->user);
        new EduAnnouncements($this->db, $this->session);
        new EduShortCodes($this->session);
        new EduPostTypes();
        new EduAdminSetup();
        new EduEnqueue($this->session->user);
    }
}
