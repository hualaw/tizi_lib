<?php
require_once("paper_download_log.php");
	
class Homework_Download_Log extends Paper_Download_Log {

    function __construct()
    {
        parent::__construct();
		$this->_table="homework_download_log";
		$this->_paper_table="homework_paper";
		$this->_paper_id="paper_id";
    }

}

/* end of paper_download_log.php */
