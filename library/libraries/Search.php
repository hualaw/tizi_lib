<?php

class Search
{
    //映射
    private static $maps = array(
        'paper' => 'Question', //兼容
        'question' => 'Question',
        'lesson' => 'Lesson',
        'homework' => 'Exercise',
        'exercise' => 'Exercise',
        'seolesson' => 'SeoLesson',
        'seo_question' => 'SeoQuestion',
    );

    public function __construct()
    {
        //
    }

    /**
     * Search_Question, Search_Lesson, Search_Exercise 的工厂函数
     * @param type $name
     * @return \className
     * @throws Expception
     */
    public function init($name, $useSolr = true)
    {
        $name = strtolower(trim($name));
        $name = isset(self::$maps[$name]) ? self::$maps[$name] : '';
        if (!$name) {
            //throw new Expception('There is no method');
            log_message('error_tizi', 'no search method');
            return;
        }
        
        $filename = 'Searcher/' . $name;
        $className = 'Searcher_' . $name;
        require_once dirname(__FILE__) . '/Search_Solr/lib/' . $filename . '.php';
        return new $className;
    }

}
