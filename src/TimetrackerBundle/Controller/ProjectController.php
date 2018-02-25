<?php

namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\Mapping as ORM;

use TimetrackerBundle\Entity\Project;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProjectController extends Controller
{

    /**
     * @Route("/company/{companyId}/project/{projectId}/form", options={"expose"=true}, name="projectForm")
     * @Template()
     */
    public function projectFormAction($companyId, $projectId, Request $request)
    {
        if (($project = $this->getDoctrine()->getRepository("TimetrackerBundle:Project")->find($projectId)) == null) {
            $project = new Project();
            $project->setCompany($this->getDoctrine()->getRepository("TimetrackerBundle:Company")->find($companyId));
        }

        $form = $this->createFormBuilder($project)
            ->add('name', TextType::class)
            ->add('purchaseOrder', TextType::class)
            ->add('notes', TextareaType::class)
            ->add('editable', ChoiceType::class, array(
                'choices' => array(
                    'Yes' => '1',
                    'No' => '0'
                ),
            ))
            ->add('contact', null)
            ->add('addContact', ButtonType::class, array(
                'attr' => array('class' => 'add-contact-button w3-btn w3-ripple w3-blue w3-round'),
            ))
            ->getForm();

        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'GET') {
            return $this->render('TimetrackerBundle:Default:form.html.twig', array('form' => $form->createView()));
        } elseif ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $ret = array();

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($project);
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
     * @Route("/company/{companyId}/editProject/{projectId}/form", options={"expose"=true}, name="projectEditForm")
     * @Template()
     */
    public function editProjectFormAction($companyId, $projectId, Request $request)
    {
        if (($project = $this->getDoctrine()->getRepository("TimetrackerBundle:Project")->find($projectId)) == null) {
            $response = new Response(json_encode(array('status' => false, 'message' => "Project not found")));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $form = $this->createFormBuilder($project)
            ->add('name', TextType::class)
            ->add('purchaseOrder', TextType::class)
            ->add('notes', TextareaType::class)
            ->add('editable', ChoiceType::class, array(
                'choices' => array(
                    'Yes' => '1',
                    'No' => '0'
                ),
            ))
            ->add('contact', null)
            ->add('addContact', ButtonType::class, array(
                'attr' => array('class' => 'add-contact-button w3-btn w3-ripple w3-blue w3-round'),
            ))
            ->getForm();

        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'GET') {
            return $this->render('TimetrackerBundle:Default:form.html.twig', array('pid' => $projectId, 'form' => $form->createView()));
        } elseif ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $ret = array();

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($project);
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
     * @Route("/project", name="projectList")
     * @Template()
     */
    public function projectListAction()
    {
        $projects = $this->getDoctrine()->getRepository("TimetrackerBundle:Project")->findAll();

        return $this->render('TimetrackerBundle:Default:projectList.html.twig', array('projects' => $projects));
    }

    /**
     * @Route("/project/{id}", name="projectDetail")
     * @Template()
     */
    public function projectDetailAction($id)
    {
        $project = $this->getDoctrine()->getRepository("TimetrackerBundle:Project")->find($id);
        return $this->render('TimetrackerBundle:Default:projectDetail.html.twig', array('project' => $project));
    }


    /**
     * @Route("/projects", name="projectsJson")
     * @Template()
     */
    public function projectsJsonFormAction(Request $request)
    {
        $companyId = $request->query->get('companyId', '');
        $company = $this->getDoctrine()->getRepository("TimetrackerBundle:Company")->find($companyId);
        $projects = $company->getProjects();

        $response = array();
        $obj = array();
        foreach ($projects as $project) {
            $obj = array();
            $obj['name'] = $project->getName();
            $obj['id'] = $project->getId();
            $obj['description'] = $project->getDescription();
            $response[] = $obj;
        }

        $response = new Response(json_encode($response));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
