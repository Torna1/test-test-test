<?php

namespace Front\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Front\FrontBundle\Entity\RequestedCoursesVotes
 *
 * @ORM\Table(name="requested_courses_votes")
 * @ORM\Entity
 */
class RequestedCoursesVotes
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id; //fake attribute
    
    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string $ip
     *
     * @ORM\Column(name="ip", type="string", length=45, nullable=false)
     */
    private $ip;

    /**
     * @var integer $isActivated
     *
     * @ORM\Column(name="is_activated", type="integer", nullable=false)
     */
    private $isActivated;

    /**
     * @var integer $isDeleted
     *
     * @ORM\Column(name="is_deleted", type="integer", nullable=false)
     */
    private $isDeleted;

    /**
     * @var RequestedCourses
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="RequestedCourses")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="requested_courses_id", referencedColumnName="id")
     * })
     */
    private $requestedCourses;

    /**
     * @var Students
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="Students")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="students_id", referencedColumnName="id")
     * })
     */
    private $students;



    /**
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set ip
     *
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set isActivated
     *
     * @param integer $isActivated
     */
    public function setIsActivated($isActivated)
    {
        $this->isActivated = $isActivated;
    }

    /**
     * Get isActivated
     *
     * @return integer 
     */
    public function getIsActivated()
    {
        return $this->isActivated;
    }

    /**
     * Set isDeleted
     *
     * @param integer $isDeleted
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
    }

    /**
     * Get isDeleted
     *
     * @return integer 
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set requestedCourses
     *
     * @param Front\FrontBundle\Entity\RequestedCourses $requestedCourses
     */
    public function setRequestedCourses(\Front\FrontBundle\Entity\RequestedCourses $requestedCourses)
    {
        $this->requestedCourses = $requestedCourses;
    }

    /**
     * Get requestedCourses
     *
     * @return Front\FrontBundle\Entity\RequestedCourses 
     */
    public function getRequestedCourses()
    {
        return $this->requestedCourses;
    }

    /**
     * Set students
     *
     * @param Front\FrontBundle\Entity\Students $students
     */
    public function setStudents(\Front\FrontBundle\Entity\Students $students)
    {
        $this->students = $students;
    }

    /**
     * Get students
     *
     * @return Front\FrontBundle\Entity\Students 
     */
    public function getStudents()
    {
        return $this->students;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set $id
     * 
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}