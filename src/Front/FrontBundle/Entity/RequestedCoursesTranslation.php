<?php

namespace Front\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Front\FrontBundle\Entity\RequestedCoursesTranslation
 *
 * @ORM\Table(name="requested_courses_translation")
 * @ORM\Entity
 */
class RequestedCoursesTranslation
{
    /**
     * @var string $language
     *
     * @ORM\Column(name="language", type="string", length=4, nullable=false)
     */
    private $language;

    /**
     * @var string $courseName
     *
     * @ORM\Column(name="course_name", type="string", length=255, nullable=false)
     */
    private $courseName;

    /**
     * @var string $courseDetails
     *
     * @ORM\Column(name="course_details", type="string", length=255, nullable=true)
     */
    private $courseDetails;

    /**
     * @var RequestedCourses
     *
     * @ORM\ManyToOne(targetEntity="RequestedCourses")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="requested_courses_id", referencedColumnName="id")
     * })
     */
    private $requestedCourses;



    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }
    
    /**
     * Set language
     *
     * @param string language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Set courseName
     *
     * @param string $courseName
     */
    public function setCourseName($courseName)
    {
        $this->courseName = $courseName;
    }

    /**
     * Get courseName
     *
     * @return string 
     */
    public function getCourseName()
    {
        return $this->courseName;
    }

    /**
     * Set courseDetails
     *
     * @param string $courseDetails
     */
    public function setCourseDetails($courseDetails)
    {
        $this->courseDetails = $courseDetails;
    }

    /**
     * Get courseDetails
     *
     * @return string 
     */
    public function getCourseDetails()
    {
        return $this->courseDetails;
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
}