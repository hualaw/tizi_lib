<?php

require_once dirname(__FILE__) . '/Abstract.php';

class Searcher_Exercise extends Searcher_Abstract
{

    /**
     * 查询
     * @param array $query = array('keyword' => '', 'category_id' => '', 'course_id'=> '', 'level_id' => 'qtype_id' => '', 'subject_id' => '');
     * @param intval $page
     * @param intval $limit
     * @param string $sort = 'score desc, id desc'
     * @return boolean
     */
    public function search(Array $query = array(), $page = 1, $limit = 10, $sort = 'score desc, id desc')
    {
        $page = ($page >= 1) ? intval($page) : 1;
        $limit = $limit ? intval($limit) : 10;
        $start = $limit * ($page - 1);
        $cond = $this->_getQuery($query);
        $params = array();
        if ($sort) {
            $params['sort'] = $sort;
        }
        if ($cond['filterQuery']) {
            $params['fq'] = $cond['filterQuery'];
        }
        try {
            $exec_time = microtime(true);
            $result = $this->_getClient()->search($cond['keyword'], $start, $limit, $params);
            $exec_time = microtime(true) - $exec_time;
        } catch (Exception $ex) {
            return false;
        }
        $result = $result->response;
        $docs = array();
        if ($result->docs) {
            foreach ($result->docs as $doc) {
                //转换subject_id
                if (isset($query['subject_id']) AND $query['subject_id']) {
                    $doc->subject_id = $query['subject_id'];
                } else if (is_array($doc->subject_id)) {
                    $doc->subject_id = $doc->subject_id[0];
                }

                //转换category_name 和 category_id
                if (isset($query['category_id']) AND $query['category_id']) {
                    $doc->category_id = $query['category_id'];
                } else if (is_array($doc->category_id)) {
                    $doc->category_id = $doc->category_id[0];
                }
                $categoryNames = unserialize($doc->category_name);
                if ($categoryNames AND isset($categoryNames[$doc->category_id]) AND $categoryNames[$doc->category_id]) {
                    $doc->name = $doc->category_name = $categoryNames[$doc->category_id]['name'];
                    $doc->category_id = $categoryNames[$doc->category_id]['id'];
                } else {
                    $doc->name = $doc->category_name = '';
                    $doc->category_id = 0;
                }

                //转换category_name 和 category_id
                if (isset($query['course_id']) AND $query['course_id']) {
                    $doc->course_id = $query['course_id'];
                } else if (is_array($doc->course_id)) {
                    $doc->course_id = $doc->course_id[0];
                }
                $courseNames = unserialize($doc->course_name);
                if ($courseNames AND isset($courseNames[$doc->course_id]) AND $courseNames[$doc->course_id]) {
                    $doc->name = $doc->course_name = $courseNames[$doc->course_id]['name'];
                    $doc->course_id = $courseNames[$doc->course_id]['id'];
                } else {
                    $doc->name = $doc->course_name = '';
                    $doc->course_id = 0;
                }

                //转换时间
                $doc->date = date('Y-m-d H:i:s', $doc->date);
            }
        }
        $return = array(
            'cond' => $cond,
            'params' => $params,
            'total' => $result->numFound,
            'result' => $result->docs,
            'exec_time' => $exec_time,
        );
        return $return;
    }
    
    public function count(Array $query = array(), $facet_pivot)
    {
        $limit = 0;
        $start = 0;
        $cond = $this->_getQuery($query);
        $params = array();
        if ($cond['filterQuery']) {
            $params['fq'] = $cond['filterQuery'];
        }
        $params['facet'] = 'true'; 
        // $params['facet.field'] = $facet;
        // $params['facet.pivot'] = $facet;
        $params['facet.pivot'] = $facet_pivot;
        try {
            $exec_time = microtime(true);
            $query_result = $this->_getClient()->search($cond['keyword'], $start, $limit, $params);
            $exec_time = microtime(true) - $exec_time;
        } catch (Exception $ex) {
            return false;
        }
        // print_r($query_result->facet_counts);
        // print_r($query_result->response);
        if ($query_result->facet_counts) {
            foreach($query_result->facet_counts->facet_pivot->$params['facet.pivot'] as $facet ) {
                $result[$facet->value]['count'] = $facet->count;
                $result[$facet->value]['field'] = $facet->field; 
                $result[$facet->value]['pivot'] = array();
                if (empty($facet->pivot)) {
                    continue;
                }
                $pivot = array();
                foreach ($facet->pivot as $sub_facet) {
                    $pivot[$sub_facet->value]['count'] = $sub_facet->count; 
                    $pivot[$sub_facet->value]['field'] = $sub_facet->field; 
                }
                $result[$facet->value]['pivot'] = $pivot;
            }            
        }
        $return = array(
            'cond' => $cond,
            'params' => $params,
            'total' => $query_result->response->numFound,
            'result' => $result,
            'exec_time' => $exec_time,
        );
        return $return;
    }

    /**
     * 添加索引，如果连续添加，请将$withCommit 设置为false, 否则严重影响效率
     * @param Apache_Solr_Document $document
     * @param boolen $withCommit
     * @return boolean
     */
    public function add(Apache_Solr_Document $document, $withCommit = true)
    {
        try {
            $solr = $this->_getClient();
            $result = $solr->addDocument($document);
            if ($withCommit) {
                $solr->commit();
            }
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    protected function _getClient()
    {
        return parent::_getClient('exercise');
    }

    /**
     * 构建搜索语句
     * @param type $query
     * @return type
     */
    private function _getQuery($query = array())
    {
        $filterQuery = array();
        if (!isset($query['online'])) {
            $filterQuery[] = 'online:1';
        } else {
            $filterQuery[] = 'online:' . intval($query['online']);
        }
        if (isset($query['subject_id']) AND $query['subject_id']) {
            $filterQuery[] = 'subject_id:' . intval($query['subject_id']);
        }
        if (isset($query['course_id']) AND $query['course_id']) {
            $filterQuery[] = 'course_id:' . intval($query['course_id']);
        }
        if (isset($query['level_id']) AND $query['level_id']) {
            $filterQuery[] = 'level_id:' . intval($query['level_id']);
        }
        if (isset($query['qtype_id']) AND $query['qtype_id']) {
            $filterQuery[] = 'qtype_id:' . intval($query['qtype_id']);
        }
        if (isset($query['category_id']) AND $query['category_id']) {
            $filterQuery[] = 'category_id:' . intval($query['category_id']);
        }
        $keyword = '*:*';
        if (isset($query['keyword']) AND $query['keyword']) {
            $keyword = trim($query['keyword']);
            //@hack 如果关键词不完全是数字字母和中文，则启用完全匹配 （加双引号）
            $ret = preg_match("/[a-zA-Z0-9\x{4e00}-\x{9fa5}]+/u", $keyword);
            if (!$ret) {
                $keyword = '"' . addslashes($keyword) . '"';
            }
            $keyword = 'title:(' . $keyword . ') OR text:(' . $keyword . ')^0.2';
        }
        $data = array(
            'filterQuery' => $filterQuery,
            'keyword' => $keyword,
        );
        return $data;
    }

}
