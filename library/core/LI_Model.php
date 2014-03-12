<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Model extends CI_Model {

	function __construct($database='tizi')
	{
		parent::__construct();
		$this->db = $this->load->database($database,true);
	}

}
// END Model Class

/* End of file LI_Model.php */
/* Location: ./library/core/LI_Model.php */