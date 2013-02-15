<?php

namespace Front\FrontBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RequestedCoursesVotesRepository  extends EntityRepository
{
    public function getVoteByCourseIdUserId($course_id = false, $student_id = false, $activated = false, $deleted = false) {
        $sql_and = false;
        $params = array();
        
        if($activated !== false)
        {
            $sql_and .= "AND requested_courses_votes.is_activated = :active ";
            $params[':active'] = $activated;
        }
        if($deleted !== false)
        {
            $sql_and .= "AND requested_courses_votes.is_deleted = :deleted ";
            $params[':deleted'] = $deleted;
        }
        $sql = "
            SELECT
                requested_courses_votes.*
            FROM
                requested_courses_votes
            WHERE
                requested_courses_votes.requested_courses_id = :courses_id AND
                requested_courses_votes.students_id = :students_id 
                ".$sql_and."
            LIMIT 1
            ";
        $params[':courses_id'] = $course_id;
        $params[':students_id'] = $student_id;
        
        $q = $this->getEntityManager()->getConnection()->executeQuery($sql, $params);
        
        return $q->fetchAll();
    }
    
    public function insertNewVote($course_id, $student_id, $active, $deleted, $date, $ip) {
        $params = array();
        
        $query = "
            INSERT INTO requested_courses_votes(date, ip, is_activated, is_deleted, students_id, requested_courses_id)
            VALUES (:date, :ip, :is_activated, :is_deleted, :students_id, :requested_courses_id)
        ";
        
        $params[':date'] = $date;
        $params[':ip'] = $ip;
        $params[':is_activated'] = $active;
        $params[':is_deleted'] = $deleted;
        $params[':students_id'] = $student_id;
        $params[':requested_courses_id'] = $course_id;
        
        $q = $this->getEntityManager()->getConnection()->executeQuery($query, $params);
        return $this->getEntityManager()->getConnection()->lastInsertId();
    }
}