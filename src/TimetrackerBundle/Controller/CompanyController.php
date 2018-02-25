<?php

namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Doctrine\ORM\Mapping as ORM;

use TimetrackerBundle\Entity\Company;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class CompanyController extends Controller
{

    /**
     * @Route("/company/{id}/form", name="companyForm")
     * @Template()
     */
    public function companyFormAction($id, Request $request)
    {
        if (($company = $this->getDoctrine()->getRepository("TimetrackerBundle:Company")->find($id)) == null) {
            $company = new Company();
        }

        $form = $this->createFormBuilder($company)
            ->add('name', TextType::class)
            ->add('address', TextType::class)
            ->add('city', TextType::class)
            ->add('state', TextType::class)
            ->add('zip', TextType::class)
            ->add('country', TextType::class)
            ->getForm();

        $request = Request::createFromGlobals();


        if ($request->getMethod() == 'GET') {
            return $this->render('TimetrackerBundle:Default:form.html.twig', array('form' => $form->createView()));
        } elseif ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $ret = array();

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($company);
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

        if ($request->getMethod() == 'DELETE') {
            if (($company = $this->getDoctrine()->getRepository("TimetrackerBundle:Company")->find($id)) == null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "Company not found")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($company);
            $em->flush();

            // create a JSON-response with a 200 status code
            $response = new Response(json_encode(array('status' => true, 'message' => 'Deleted Company')));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('status' => false, 'message' => 'Post/Delete only')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/company", name="companyList")
     * @Template()
     */
    public function companyListAction()
    {
        $companies = $this->getDoctrine()->getRepository("TimetrackerBundle:Company")->findBy(array(), array('name' => 'ASC'));
        return $this->render('TimetrackerBundle:Default:companyList.html.twig', array('companies' => $companies));
    }

    /**
     * @Route("/company/{id}", name="companyDetail")
     * @Template()
     */
    public function companyDetailAction($id)
    {
        $company = $this->getDoctrine()->getRepository("TimetrackerBundle:Company")->find($id);
        return $this->render('TimetrackerBundle:Default:companyDetail.html.twig', array('company' => $company));
    }

    /**
     * @Route("/companies.json", name="companies.json")
     * @Template()
     */
    public function companiesJsonAction()
    {
        $companyObjs = $this->getDoctrine()->getRepository("TimetrackerBundle:Company")->findBy(array(), array('name' => 'ASC'));
        //return $this->render('TimetrackerBundle:Default:companyList.html.twig', array('companies' => $companies));

        $companies = [];

        foreach ($companyObjs as $companyObj) {
            $company = new \stdClass();
            $company->name = $companyObj->getName();
            $company->id = $companyObj->getId();
            $company->projects = [];

            foreach ($companyObj->getProjects() as $projectObj) {
                $project = new \stdClass();
                $project->name = $projectObj->getName();
                $project->id = $projectObj->getId();

                $company->projects[] = $project;
            }

            $companies[] = $company;
        }

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('companies' => $companies)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


}
