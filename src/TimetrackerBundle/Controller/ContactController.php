<?php

namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Doctrine\ORM\Mapping as ORM;

use TimetrackerBundle\Entity\Contact;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ContactController extends Controller
{

    /**
     * @Route("/contact/{id}/form", name="contactForm")
     * @Template()
     */
    public function contactFormAction($id, Request $request)
    {
        if (($contact = $this->getDoctrine()->getRepository("TimetrackerBundle:Contact")->find($id)) == null) {
            $contact = new Contact();
        }

        $form = $this->createFormBuilder($contact)
            ->add('firstName', TextType::class)
            ->add('middleName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('emailAddress', TextType::class)
            ->add('phone1', TextType::class)
            ->add('phone2', TextType::class)
            ->getForm();

        $request = Request::createFromGlobals();


        if ($request->getMethod() == 'GET') {
            return $this->render('TimetrackerBundle:Default:form.html.twig', array('form' => $form->createView()));
        } elseif ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $ret = array();

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($contact);
                $em->flush();
                $ret['status'] = true;
            } else {
                $ret['status'] = false;
                $ret['message'] = $this->renderView('TimetrackerBundle:Default:form.html.twig', array('form' => $form->createView()));
            }

            $response = new Response(json_encode($ret));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    /**
     * @Route("/contact", name="companyList")
     * @Template()
     *
    public function companyListAction()
    {
        $companies = $this->getDoctrine()->getRepository("TimetrackerBundle:Company")->findBy(array(), array('name' => 'ASC'));
        return $this->render('TimetrackerBundle:Default:companyList.html.twig', array('companies' => $companies));
    }*/

    /**
     * @Route("/contact/{id}", name="contactDetail")
     * @Template()
     */
    public function contactDetailAction($id)
    {
        $contact = $this->getDoctrine()->getRepository("TimetrackerBundle:Contact")->find($id);
        $response = new Response(json_encode($contact));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
