<?php

namespace TimetrackerBundle\Controller;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use TimetrackerBundle\Entity\CalendarMonth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Calendarmonth controller.
 *
 * @Route("calendar-months")
 */
class CalendarMonthController extends Controller
{
    /**
     * @Route("/calendar-months", name="calendarMonthsIndex")
     */
    public function indexAction()
    {
        return $this->render('TimetrackerBundle:Default:calendarMonths.html.twig');
    }

    /**
     * @Route("/calendar-months/{year}/{month}", requirements={"year" = "\d+", "month" = "\d+"}, options = { "expose" = true }, name="calendarMonth.json")
     */
    public function calendarMonthsAction($month, $year)
    {
        if (($calendarMonth = $this->getDoctrine()->getRepository("TimetrackerBundle:CalendarMonth")->findOneBy(array('currentMonth' => $month, 'currentYear' => $year)))==null) {
            $calendarMonth = new CalendarMonth();
            $calendarMonth->setCurrentMonth($month);
            $calendarMonth->setCurrentYear($year);


            $calendarMonth->setStartDay(1);
            $calendarMonth->setStartMonth($month);
            $calendarMonth->setStartYear($year);

            $calendarMonth->setEndDay(cal_days_in_month(CAL_GREGORIAN, $month, $year));
            $calendarMonth->setEndMonth($month);
            $calendarMonth->setEndYear($year);
        }

        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'POST') {
            $calendarMonth->setStartDay($request->get("startDay", $calendarMonth->getStartDay()));
            $calendarMonth->setStartMonth($request->get("startMonth", $calendarMonth->getStartMonth()));
            $calendarMonth->setStartYear($request->get("startYear", $calendarMonth->getStartYear()));

            $calendarMonth->setEndDay($request->get("endDay", $calendarMonth->getEndDay()));
            $calendarMonth->setEndMonth($request->get("endMonth", $calendarMonth->getEndMonth()));
            $calendarMonth->setEndYear($request->get("endYear", $calendarMonth->getEndYear()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($calendarMonth);
            $em->flush();
        }

        $ret = new \stdClass();
        $ret->id = ($calendarMonth->getId() == null ? -1 : $calendarMonth->getId());
        $ret->currentMonth = $calendarMonth->getCurrentMonth();
        $ret->currentYear = $calendarMonth->getCurrentYear();

        $ret->startDay = $calendarMonth->getStartDay();
        $ret->startMonth = $calendarMonth->getStartMonth();
        $ret->startYear = $calendarMonth->getStartYear();

        $ret->endDay = $calendarMonth->getEndDay();
        $ret->endMonth = $calendarMonth->getEndMonth();
        $ret->endYear = $calendarMonth->getEndYear();

        $allMonths = [];
        if (($calendarMonths = $this->getDoctrine()->getRepository("TimetrackerBundle:CalendarMonth")->findAll())!=null) {
            foreach ($calendarMonths as $calendarMonthObj) {
                $calendarMonth = new \stdClass();
                $calendarMonth->id = $calendarMonthObj->getId();
                $calendarMonth->currentYear = $calendarMonthObj->getCurrentYear();
                $calendarMonth->currentMonth = $calendarMonthObj->getCurrentMonth();
                $calendarMonth->startDay = $calendarMonthObj->getStartDay();
                $calendarMonth->startMonth = $calendarMonthObj->getStartMonth();
                $calendarMonth->startYear = $calendarMonthObj->getStartYear();
                $calendarMonth->startDay = $calendarMonthObj->getEndDay();
                $calendarMonth->startMonth = $calendarMonthObj->getEndMonth();
                $calendarMonth->startYear = $calendarMonthObj->getEndYear();

                $allMonths[] = $calendarMonth;
            }
        }


        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('status' => true, 'message'=> 'Time Period Set.', 'calendarMonth'=> $ret, 'allMonths' => $allMonths)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Creates a new calendarMonth entity.
     *
     * @Route("/new", name="calendar-months_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $calendarMonth = new Calendarmonth();
        $form = $this->createForm('TimetrackerBundle\Form\CalendarMonthType', $calendarMonth);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($calendarMonth);
            $em->flush();

            return $this->redirectToRoute('calendar-months_show', array('id' => $calendarMonth->getId()));
        }

        return $this->render('calendarmonth/new.html.twig', array(
            'calendarMonth' => $calendarMonth,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a calendarMonth entity.
     *
     * @Route("/{id}", name="calendar-months_show")
     * @Method("GET")
     */
    public function showAction(CalendarMonth $calendarMonth)
    {
        $deleteForm = $this->createDeleteForm($calendarMonth);

        return $this->render('calendarmonth/show.html.twig', array(
            'calendarMonth' => $calendarMonth,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing calendarMonth entity.
     *
     * @Route("/{id}/edit", name="calendar-months_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CalendarMonth $calendarMonth)
    {
        $deleteForm = $this->createDeleteForm($calendarMonth);
        $editForm = $this->createForm('TimetrackerBundle\Form\CalendarMonthType', $calendarMonth);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('calendar-months_edit', array('id' => $calendarMonth->getId()));
        }

        return $this->render('calendarmonth/edit.html.twig', array(
            'calendarMonth' => $calendarMonth,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a calendarMonth entity.
     *
     * @Route("/{id}", name="calendar-months_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CalendarMonth $calendarMonth)
    {
        $form = $this->createDeleteForm($calendarMonth);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($calendarMonth);
            $em->flush();
        }

        return $this->redirectToRoute('calendar-months_index');
    }

    /**
     * Creates a form to delete a calendarMonth entity.
     *
     * @param CalendarMonth $calendarMonth The calendarMonth entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CalendarMonth $calendarMonth)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('calendar-months_delete', array('id' => $calendarMonth->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
