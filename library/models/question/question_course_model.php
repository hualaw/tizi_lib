<?php
require_once('question_category_model.php');

class Question_Course_Model extends Question_Category_Model {
    
    public function __construct()
    {
        parent::__construct();
		$this->_table="course";
    }

}

/*end of sub_question_category_model.php*/
