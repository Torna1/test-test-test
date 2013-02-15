<?php

namespace Front\FrontBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RequestedCoursesRepository extends EntityRepository
{
    public function getAvailableCourses($language = false, $activated = false, $deleted = false, $course_id = false) {
        $sql_and = false;
        $params = array();
        
        if($activated !== false)
        {
            $sql_and .= "AND requested_courses.is_activated = :active ";
            $params[':active'] = $activated;
        }
        if($deleted !== false)
        {
            $sql_and .= "AND requested_courses.is_deleted = :deleted ";
            $params[':deleted'] = $deleted;
        }
        if(!empty($course_id))
        {
            $sql_and .= "AND requested_courses.id = :course_id ";
            $params[':course_id'] = $course_id;
        }
        $sql = "
            SELECT
            requested_courses.id,
            requested_courses.is_activated,
            requested_courses.is_deleted,
            requested_courses.added,
            requested_courses_translation.course_name,
            requested_courses_translation.course_details,
            requested_courses_translation.language,
            Count(requested_courses_votes.requested_courses_id) as votes
            FROM
            requested_courses
            Left Join requested_courses_votes ON requested_courses.id = requested_courses_votes.requested_courses_id ,
            requested_courses_translation
            WHERE
            requested_courses.id =  requested_courses_translation.requested_courses_id AND
            requested_courses_translation.language = :language 
            ".$sql_and."
            GROUP BY
            requested_courses.id
            ORDER BY
            requested_courses.added ASC
            ";
        
        $params[':language'] = $language;
        
        $q = $this->getEntityManager()->getConnection()->executeQuery($sql, $params);
        return $q->fetchAll();
    }
    
    public function getRequestedCourseById($language = false, $activated = false, $deleted = false, $course_id = false)
    {
        return $this->getAvailableCourses($language, $activated, $deleted, $course_id);
    }
}