<?php

namespace TimetrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Doctrine\ORM\Mapping as ORM;

use TimetrackerBundle\Entity\Holiday;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Holiday controller.
 *
 * @Route("holidays")
 */
class HolidayController extends Controller
{


    /**
     * @Route("/holidays/{id}/form", name="holidayForm")
     * @Template()
     */
    public function holidayFormAction($id, Request $request)
    {
        $currentYear = date("Y");

        if (($holiday = $this->getDoctrine()->getRepository("TimetrackerBundle:Holiday")->find($id)) == null) {
            $holiday = new Holiday();
        }
        // if ($year == null){
        //     $year = "TextType::class";
        // }

        $form = $this->createFormBuilder($holiday)
            ->add('name', ChoiceType::class, array(
              'choices' => array(
                    'New Year\'s Day' => "New Year\'s Day",
                    'President\'s Day' => "President's Day",
                    'Memorial Day' => "Memorial day",
                    'Independence Day' => "Independence Day",
                    'Labor Day' => "Labor Day",
                    'Thanksgiving' => "Thanksgiving",
                    'Christmas' => "Christmas",
                  )))
            ->add('month', ChoiceType::class, array(
              'choices' => array(
                    '01' => 1,
                    '02' => 2,
                    '03' => 3,
                    '04' => 4,
                    '05' => 5,
                    '06' => 6,
                    '07' => 7,
                    '08' => 8,
                    '08' => 9,
                    '10' => 10,
                    '11' => 11,
                    '12' => 12,
                  )))
            ->add('day', ChoiceType::class, array(
              'choices' => array(
                    '01' => 1,
                    '02' => 2,
                    '03' => 3,
                    '04' => 4,
                    '05' => 5,
                    '06' => 6,
                    '07' => 7,
                    '08' => 8,
                    '08' => 9,
                    '10' => 10,
                    '11' => 11,
                    '12' => 12,
                    '13' => 13,
                    '14' => 14,
                    '15' => 15,
                    '16' => 16,
                    '17' => 17,
                    '18' => 18,
                    '19' => 19,
                    '20' => 20,
                    '21' => 21,
                    '22' => 22,
                    '23' => 23,
                    '24' => 24,
                    '25' => 25,
                    '26' => 26,
                    '27' => 27,
                    '28' => 28,
                    '29' => 29,
                    '30' => 30,
                    '31' => 31,
                )))
            ->add('year', ChoiceType::class, array(
              'placeholder' => $currentYear,
              'choices' => array(
                  date("Y")-5 => date("Y")-5,
                  date("Y")-4 => date("Y")-4,
                  date("Y")-3 => date("Y")-3,
                  date("Y")-2 => date("Y")-2,
                  date("Y")-1 => date("Y")-1,
                  date("Y") => date("Y"),
                  date("Y")+1 => date("Y")+1,
                  date("Y")+2 => date("Y")+2,
                  date("Y")+3 => date("Y")+3,
                  date("Y")+4 => date("Y")+4,
                  date("Y")+5 => date("Y")+5,
                )))
            ->add('hours', ChoiceType::class, array(
              'choices' => array(
                    '01' => 1,
                    '02' => 2,
                    '03' => 3,
                    '04' => 4,
                    '05' => 5,
                    '06' => 6,
                    '07' => 7,
                    '08' => 8,
                  )))
            ->getForm();

        $request = Request::createFromGlobals();


        if ($request->getMethod() == 'GET') {
            return $this->render('TimetrackerBundle:Default:form.html.twig', array('form' => $form->createView()));
        } elseif ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $ret = array();

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($holiday);
                $em->flush();
                $ret['status'] = true;
            } else {
                $ret['status'] = false;
                $ret['message'] = $this->renderView('TimetrackerBundle:Default:holidayForm.html.twig', array('form' => $form->createView()));
            }

            $response = new Response(json_encode($ret));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }


  /**
   * @Route("/holidays/{year}", name="holidayList", requirements={"year": "\d+"})
   * @Template()
   */
  public function holidayListAction($year = 0)
  {
      if ($year==0) {
          $year = date('Y');
      }

      $holidays = $this->getDoctrine()->getRepository("TimetrackerBundle:Holiday")->findBy(array('year' => $year), array('year' => 'ASC', 'month' => 'ASC', 'day' => 'ASC'));
      return $this->render('TimetrackerBundle:Default:holidays.html.twig', array('holidays' => $holidays, 'year' => $year));
  }

  /**
   * @Route("/holidays.json", name="holidays.json")
   * @Template()
   */
  public function holidaysJsonAction()
  {
      $holidayObjs = $this->getDoctrine()->getRepository("TimetrackerBundle:Holiday")->findBy(array(), array('year' => 'ASC', 'month' => 'ASC', 'day' => 'ASC'));
      //return $this->render('TimetrackerBundle:Default:holidayList.html.twig', array('holidays' => $holidays));

      $holidays = [];

      foreach ($holidayObjs as $holidayObj) {
          $holiday = new \stdClass();
          $holiday->name = $holidayObj->getName();
          $holiday->id = $holidayObj->getId();
          $holiday->year = $holidayObj->getYear();
          $holiday->month = $holidayObj->getMonth();
          $holiday->day = $holidayObj->getDay();
          $holiday->hours = $holidayObj->getHours();

          $holidays[] = $holiday;
      }

      // create a JSON-response with a 200 status code
      $response = new Response(json_encode(array('holidays' => $holidays)));
      $response->headers->set('Content-Type', 'application/json');
      return $response;
  }
}
