<?php

class Dafen_Class_Model extends MY_Model {

    /**
     * 通过班级ID获取班级准考证号前缀
     *
     * @param  int $class_id 班级ID
     * @return int           班级准考证号
     */
    public function g_ticket_by_class_id($class_id)
    {
        $res = $this->db->select('ticket_prefix')->where('class_id', $class_id)
            ->from('dafen_class_ticket')
            ->get()->row_array();

        // 整理前缀格式
        $class_ticket = empty($res['ticket_prefix']) ? 0 : $res['ticket_prefix'];
        $class_ticket = (9 < $class_ticket) ? $class_ticket : '0' . $class_ticket;
        return $class_ticket;
    }
}