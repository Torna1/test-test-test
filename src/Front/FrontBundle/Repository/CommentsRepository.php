<?php

namespace Front\FrontBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CommentsRepository extends EntityRepository
{
    public function getComments($url = false, $active = false, $deleted = false) {
        $params = array();
        
        $sql_and = false;
        if($url !== false)
        {
            $sql_and .= " AND c.url = :url";
            $params[':url'] = $url;
        }
        if($active !== false)
        {
            $sql_and .= " AND c.is_activated = :active";
            $params[':active'] = $active;
        }
        if($deleted !== false)
        {
            $sql_and .= " AND c.is_deleted = :deleted";
            $params[':deleted'] = $deleted;
        }
        
        $query = "
        SELECT
        c.id,
        c.user_id,
        c.url,
        c.comment,
        c.added_at,
        c.is_teacher,
        c.is_activated,
        c.is_deleted,
        t.email AS teacher,
        s.email AS student
        FROM
        comments AS c
        Left Join teachers AS t ON c.user_id = t.id AND c.is_teacher = 1
        Inner Join students AS s ON c.user_id = s.id AND c.is_teacher = 0
        WHERE
        1 = 1
        ".$sql_and." 
        ORDER BY
        c.added_at DESC";
        
        $q = $this->getEntityManager()->getConnection()->executeQuery($query, $params);

        return $q->fetchAll();
    }
}