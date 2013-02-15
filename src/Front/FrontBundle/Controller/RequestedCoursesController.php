<?php

namespace Front\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Front\FrontBundle\Security\Auth as Auth;
use Front\FrontBundle\Form\RequestedCoursesType;
use Front\FrontBundle\Entity\RequestedCourses;
use Front\FrontBundle\Entity\RequestedCoursesVotes;
use Front\FrontBundle\Entity\Comments;
use Front\FrontBundle\Form\CommentsType;


/**
 * User controller.
 *
 */
class RequestedCoursesController extends Controller {

    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $request->setLocale('ro');
        $locale = $request->getLocale();

        //get courses in current locale language, activated an not deleted
        $results = $em->getRepository('FrontFrontBundle:RequestedCourses')->getAvailableCourses($locale, 1, 0);

        return $this->render('FrontFrontBundle:Default:requested_course_list.html.twig', array('course_list' => $results));
    }

    public function newAction(Request $request) {
        if (!Auth::isAuth()) {
            return $this->redirect($this->generateUrl('register'));
        }
        $requested_course = new RequestedCourses();

        $form = $this->createForm(new RequestedCoursesType(), $requested_course);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $request->setLocale('ro');
                $locale = $this->getRequest()->getLocale();
                
                $form->getData()->setIsActivated(1);
                $form->getData()->setIsDeleted(0);
                $form->getData()->setAdded(new \DateTime());

                $em = $this->getDoctrine()->getManager();
                $em->persist($requested_course);
                $em->flush();
                //save embedded form object
                $requestedCourseTranslation = $form->getData()->getRequestedCourseTranslation();
                $requestedCourseTranslation->setRequestedCourses($requested_course);
                $requestedCourseTranslation->setLanguage($locale);
                $em->persist($requestedCourseTranslation);
                $em->flush();

                return $this->redirect($this->generateUrl('requested_courses'));
            }
        }
        return $this->render('FrontFrontBundle:Default:requested_courses_new.html.twig', array(
                    'form' => $form->createView(),
                ));
    }

    public function voteAction(Request $request) {
        if ($request->isXmlHttpRequest()) {
            $course_id = $request->query->get('course_id');
            $result = false;
            
            if(!Auth::isAuth())
            {
                //redirect to login url
                $result['status'] = 1;
                $result['link'] = $this->generateUrl('register');
            }
            else if (!empty($course_id)) {
                $course_id = intval($course_id);
                
                $em = $this->getDoctrine()->getEntityManager();
                $request->setLocale('ro');
                $locale = $request->getLocale();                

                //get auth user_id
                $user_id = Auth::getAuthParam('id');

                //check if current user already  voted for this course
                $vote = $em->getRepository('FrontFrontBundle:RequestedCoursesVotes')
                        ->getVoteByCourseIdUserId($course_id, $user_id, 1, 0);
                //save new vote to Db
                if (empty($vote)) {
                    $ip = $request->getClientIp();
                    $date = date('Y-m-d H:i:s');
                    $new_vote = $em->getRepository('FrontFrontBundle:RequestedCoursesVotes')
                        ->insertNewVote($course_id, $user_id, 1, 0, $date, $ip);
                }
                
                //get course in current locale language, activated an not deleted
                $details = $em->getRepository('FrontFrontBundle:RequestedCourses')
                        ->getRequestedCourseById($locale, 1, 0, $course_id);
                
                //status is OK
                $result['status'] = 0;
                $result['id'] = $details[0]['id'];
                $result['votes'] = $details[0]['votes'];
            }
            else
            {
                $result['status'] = -1;
                $result['message'] = false;
            }

            return new Response(json_encode($result), 200);
        }

        throw new \Exception('Fatal error!');
    }

    public function detailsAction($id) {
        $course_id = intval($id);
        
        $em = $this->getDoctrine()->getEntityManager();
        $request = Request::createFromGlobals();
        $request->setLocale('ro');
        $locale = $request->getLocale();

        //get course in current locale language, activated an not deleted
        $details = $em->getRepository('FrontFrontBundle:RequestedCourses')
                    ->getRequestedCourseById($locale, 1, 0, $course_id);
        $course = $details[0];
        return $this->render('FrontFrontBundle:Default:requested_course_details.html.twig', array(
                    'course' => $course,
                ));
    }
    
    public function commentsFormAction(Request $request)
    {
        if(!Auth::isAuth())
        {
            return new Response('', 200);
        }
            
        $comments = new Comments();
        $form = $this->createForm(new CommentsType(), $comments);
//        $request->getSession()->setFlash('error', 'dasdasdasdasa sda sd sad as d sa dsa d a');
        if($request->isMethod('POST'))
        {
            $referer = $request->headers->get('referer');
            $form->bind($request);
            if ($form->isValid())
            {
                $user_id = Auth::getAuthParam('id');
                $url = $referer;
                
                $form->getData()->setUserId($user_id);
                $form->getData()->setUrl($url);
                $form->getData()->setAddedAt(new \DateTime());
                $form->getData()->setIsTeacher(0);
                $form->getData()->setIsActivated(0);
                $form->getData()->setIsDeleted(0);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($comments);
                $em->flush();
                
                $request->getSession()->setFlash('message_comment_form', 'Comentariul a fost adaugat cu succes!');
            }
            else
            {
                $error_msg = false;
                //global errors
                $errors = $form->getErrors();
                foreach( $errors as $error )
                {
                  if(!empty($error_msg))
                    {
                        $error_msg .= '<br />';
                    }
                    $error_msg .= $error->getMessageTemplate();
                }
                //fields errors
                $errors = $this->get('validator')->validate( $comments );
                foreach( $errors as $error )
                {
                    if(!empty($error_msg))
                    {
                        $error_msg .= '<br />';
                    }
                    $error_msg .= 'Campul "'.$error->getPropertyPath().'" : '.$error->getMessage();
                }
//                var_dump($error_msg); die;
                $request->getSession()->setFlash('error_comment_form', $error_msg);
            }
            
            return new RedirectResponse($referer);
//            $request->getSession()->setFlash('error', $exception->getMessage());
//            $request->getSession()->setFlash('message', 'dasdasdasdasa sda sd sad as d sa dsa d a');
            
        }
        return $this->render(
            'FrontFrontBundle:Default:comments_form.html.twig', array(
                'form' => $form->createView(),
            )
        );
    }
    
    public function commentsListAction($url)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $results = $em->getRepository('FrontFrontBundle:Comments')->getComments($url, 1, 0);
//        var_dump($results); die;
        return $this->render('FrontFrontBundle:Default:comments_list.html.twig', array('comments' => $results));
    }

}
