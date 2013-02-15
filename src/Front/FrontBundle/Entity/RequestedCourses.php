<?php

namespace Front\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Front\FrontBundle\Entity\RequestedCourses
 *
 * @ORM\Table(name="requested_courses")
 * @ORM\Entity
 */
class RequestedCourses
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * @var datetime $added
     *
     * @ORM\Column(name="added", type="datetime", nullable=false)
     */
    private $added;
    
    /**
     * @Assert\Type(type="Front\FrontBundle\Entity\RequestedCoursesTranslation")
     * @Assert\Valid
     */
    protected $requestedCourseTranslation;



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
     * Set added
     *
     * @param datetime $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
    }

    /**
     * Get added
     *
     * @return datetime 
     */
    public function getAdded()
    {
        return $this->added;
    }
    
    /**
     * Get requested_course_translation
     * 
     * @return RequestedCoursesTranslation
     */
    public function getRequestedCourseTranslation()
    {
        return $this->requestedCourseTranslation;
    }

    /**
     * Set requestedCourseTranslation
     * 
     * @param \Front\FrontBundle\Entity\RequestedCoursesTranslation $requestedCourseTranslation
     */
    public function setRequestedCourseTranslation(RequestedCoursesTranslation $requestedCourseTranslation = null)
    {
        $this->requestedCourseTranslation = $requestedCourseTranslation;
    }
}