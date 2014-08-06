<?php

class Dafen_Student_Model extends MY_Model {

    /**
     * 根据班级ID获取学生信息
     *
     * @param  int $class_id 班级ID
     * @param  string $field 需要获取的字段
     * @return array         结果集
     */
    public function g_student_by_class_id($class_id, $field = '*')
    {
        return $this->db->select($field)
            ->from('dafen_student_ticket')
            ->where('class_id', $class_id)
            ->where('status', 0)
            ->get()->result_array();
    }
}