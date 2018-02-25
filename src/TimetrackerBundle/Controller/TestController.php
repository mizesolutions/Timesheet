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
use TimetrackerBundle\Entity\TrackedMonth;
use TimetrackerBundle\Entity\TrackedProject;
use TimetrackerBundle\Entity\Task;
use TimetrackerBundle\Entity\TrackedTask;
use TimetrackerBundle\Entity\Company;
use TimetrackerBundle\Entity\Project;
use TimetrackerBundle\Entity\CalendarMonth;
use TimetrackerBundle\Entity\Holiday;
use UserBundle\Entity\User;

class TestController extends Controller
{

    // /**
    // * @Route("/test-this")
    // */
    // public function dateTestAction(Request $request)
    // {
    //
    //     return new Response(
    //       '<html><body>Month: '.$month.'<br> Year: '.$fYear.'</body></html>'
    //       );
    // }


    /**
    * @Route("/getallmonths")
    */
    public function getAllMonths(Request $request)
    {
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

        $res = json_encode($allMonths);

        return new Response(
              '<html><body><br>All Months: '.$res.'</body></html>'
          );
    }




    /**
     * @Route("/default/{year}/{month}/", requirements={"year" = "\d+", "month" = "\d+"}, options = { "expose" = true })
     */
    public function calendarAction($year, $month)
    {
        $request = Request::createFromGlobals();
        $currentMonth = $month;
        $currentYear = $year;
        $create = 1;
        $createEmpty = 0;

        $user= $this->get('security.token_storage')->getToken()->getUser();

        // ===============================================================================
        // Check if Calendar Month, if is set calculate days in Calendar Month .
        // ===============================================================================
        if (($calendarMonth = $this->getDoctrine()->getRepository("TimetrackerBundle:CalendarMonth")->findOneBy(
                array('currentYear'=>$year, 'currentMonth'=>$month)))==null) {
            $response = new Response(json_encode(array('status' => false, 'canCreate'=>false, 'message'=>"Administration has not configured this month yet")));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            $date1 = date_create($calendarMonth->getStartYear().'-'.$calendarMonth->getStartMonth().'-'.$calendarMonth->getStartDay());
            $date2 = date_create($calendarMonth->getEndYear().'-'.$calendarMonth->getEndMonth().'-'.$calendarMonth->getEndDay());
            $diff = date_diff($date1, $date2);
            $daysInMonth = $diff->format("%a") +1;
        }

        $this->createDefaults($user, $month, $year, $create, $createEmpty, $currentMonth, $currentYear, $daysInMonth);

        if (($trackedMonth = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedMonth")->findOneBy(
                array('user' => $user, 'month' => $month, 'year' => $year))) == null) {
            $response = new Response(json_encode(array('status' => false, 'canCreate'=>true, 'message'=>"You do not havea timesheet for this month yet")));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $tm = $trackedMonth->getId();
        echo 'trackedMonth: '.$tm.'<br><br>';

        $companies = [];
        $cmp = json_encode($companies);
        echo 'companies: '.$cmp.'<br><br>';

        foreach ($trackedMonth->getTrackedProjects() as $trackedProject) {
            echo 'foreach $trackedMonth->getTrackedProjects() as $trackedProject <br>';
            $found = false;
            if ($found == false) {
                $fnum = 0;
            }
            echo 'found: '.$fnum.'<br><br>';
            foreach ($companies as $company) {
                echo 'foreach $companies as $company <br>';
                if ($company->name === $trackedProject->getProject()->getCompany()->getName()) {
                    $found = true;
                    if ($found) {
                        $fnum = 1;
                    }
                    echo 'found2: '.$fnum.'<br><br>';
                    break;
                }
            }

            if ($found==false) {
                echo 'if !found create and add company to companies <br>';
                $company = new \stdClass();
                $company->name = $trackedProject->getProject()->getCompany()->getName();
                $company->id = $trackedProject->getProject()->getCompany()->getId();
                $company->projects = [];
                $companies[] = $company;
            }
        }

        $cmp = json_encode($companies);
        echo 'companies: '.$cmp.'<br><br>';

        $subtotals = [];
        $dayHeader = [];
        $startYear = $calendarMonth->getStartYear();
        $startMonth = $calendarMonth->getStartMonth();
        $startDay = $calendarMonth->getStartDay();
        echo 'Start Year: '.$startYear.'<br>Start Month: '.$startMonth.'<br>Start Day: '.$startDay.'<br><br>';


        function calculateWeekend($startYear, $startMonth, $startDay)
        {
            //Date in YYYY-MM-DD format.
            $date = $startYear.'-'.$startMonth.'-'.$startDay;
            //Set this to FALSE until proven otherwise.
            $weekendDay = false;

            //Get the day that this particular date falls on.
            $day = date("D", strtotime($date));

            //Check to see if it is equal to Sat or Sun.
            if ($day == 'Sat' || $day == 'Sun') {
                //Set our $weekendDay variable to TRUE.
                $weekendDay = true;
            }

            return $weekendDay;
        }


        function isEditable($trackedEditable)
        {
            $res = false;
            if ($trackedEditable == 1) {
                $res = true;
            }
            return $res;
        }


        function incrementDays($startYear, $startMonth, $startDay)
        {
            $addDay = date("d", strtotime("+1 day", strtotime($startYear."-".$startMonth."-".$startDay)));

            return $addDay;
        }


        echo 'subtotal loop <br>';
        for ($i=0; $i<$daysInMonth; $i++) {
            $subtotal = new \stdClass();
            $subtotal->value = 0;
            $subtotal->isWeekend = calculateWeekend($startYear, $startMonth, $startDay);
            $subtotals[] = $subtotal;

            $dayHeader[] = $startDay;

            $startDay = incrementDays($startYear, $startMonth, $startDay);
            if ($startDay == 1) {
                $startMonth++;
            }
        }

        $trPro[] = $trackedMonth->getTrackedProjects();
        $trPro0 = json_encode($trPro[0]);

        echo '$trackedProject: '.$trPro0.' <br>';

        foreach ($trackedMonth->getTrackedProjects() as $trackedProject) {
            echo 'foreach $trackedMonth->getTrackedProjects() as $trackedProject <br>';
            foreach ($companies as &$company) {
                echo 'foreach $companies as $company <br>';
                if ($company->id == $trackedProject->getProject()->getCompany()->getId()) {
                    $project = new \stdClass();
                    $project->name = $trackedProject->getProject()->getName();
                    $project->trackedId = $trackedProject->getId();
                    $project->id = $trackedProject->getProject()->getId();
                    $project->pEditable = isEditable($trackedProject->getEditable());
                    $project->tasks = [];
                    $company->projects[] = $project;

                    echo 'foreach $trackedProject->getTrackedTasks() as $trackedTask <br>';
                    foreach ($trackedProject->getTrackedTasks() as $trackedTask) {
                        $task = new \stdClass();
                        $task->name = $trackedTask->getTask()->getName();
                        $task->id = $trackedTask->getId();
                        $task->pid = $trackedProject->getProject()->getId();
                        $task->tEditable = isEditable($trackedTask->getEditable());
                        $task->days = [];
                        $project->tasks[] = $task;

                        $days = $trackedTask->getTrackedDays();
                        if (count($days)==0) {
                            $trackedTask->generateTrackedDays($daysInMonth, $this->getDoctrine()->getManager());
                        }

                        $startYear = $calendarMonth->getStartYear();
                        $startMonth = $calendarMonth->getStartMonth();
                        $startDay = $calendarMonth->getStartDay();

                        foreach ($trackedTask->getTrackedDays() as $trackedDay) {
                            echo 'foreach $trackedProject->getTrackedTasks() as $trackedTask <br>';
                            $day = new \stdClass();
                            if (checkHoliday($startYear, $startMonth, $startDay)) {
                                $trackedDay->setHours(8);
                            }
                            $day->hours = $trackedDay->getHours();
                            $day->notes = $trackedDay->getNotes();
                            $day->id = $trackedDay->getId();
                            $day->isWeekend = calculateWeekend($startYear, $startMonth, $startDay);

                            $startDay = incrementDays($startYear, $startMonth, $startDay);
                            if ($startDay == 1) {
                                $startMonth++;
                            }

                            $task->days[] = $day;
                        }
                    }
                }
            }
        }

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('status' => true, 'companies' => $companies, 'year'=> $year, 'month'=>$month,'daysInMonth' => $daysInMonth,'dayHeader' => $dayHeader,'subtotals' => $subtotals)));
        $response->headers->set('Content-Type', 'application/json');
        // return $response;
        return new Response(
            '<html><body><br>Datat: '.$response.'</body></html>'
        );
    }


    /**
    * @Route("/create-defaults/{user}/{month}/{year}/{create}/{createEmpty}/{currentMonth}/{currentYear}/{daysInMonth}/", requirements={"user" = "\d+","year" = "\d+", "month" = "\d+"}, name="createDefaults")
    */
    public function createDefaults($user, $month, $year, $create, $createEmpty, $currentMonth, $currentYear, $daysInMonth)
    {
        // =========================================================================
        // Creating default company, project, and tasks, IF they don't exist.
        // =========================================================================
        if (($company = $this->getDoctrine()->getRepository('TimetrackerBundle:Company')->findOneBy(array('name' => 'Infinetix'))) == null) {
            $company = new Company();
            $company->setName('Infinetix');

            $em = $this->getDoctrine()->getManager();
            $em->persist($company);
            $em->flush();
        }

        if (($project = $this->getDoctrine()->getRepository('TimetrackerBundle:Project')->findOneBy(array('name' => 'Project'))) == null) {
            $project = new Project();
            $project->setCompany($company);
            $project->setName('Project');
            $project->setEditable(0);

            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();
        }

        $defaultTasks = array("Holiday", "Sick", "Vacation");

        foreach ($defaultTasks as $defaultTask) {
            if (($task = $this->getDoctrine()->getRepository('TimetrackerBundle:Task')->findOneBy(array('name' => $defaultTask))) == null) {
                $task = new Task();
                $task->setProject($project);
                $task->setName($defaultTask);
                $task->setEditable(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($task);
                $em->flush();
            }
        }

        // ===============================================================================
        // Check for trackedMonth by user, month, and year, if null create?, createEmpty?
        // ===============================================================================
        if (($trackedMonth = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedMonth")->findOneBy(
                array('user' => $user, 'month' => $month, 'year' => $year))) == null) {
            if ($create) {

                // =============================================================
                // Create trackedMonth with default tracked project and tasks
                // =============================================================
                $trackedMonth = new TrackedMonth();
                $trackedMonth->setMonth($month);
                $trackedMonth->setYear($year);
                $trackedMonth->setUser($user);

                $em = $this->getDoctrine()->getManager();
                $em->persist($trackedMonth);

                if ($createEmpty==false) {
                    if (($originalTrackedMonth = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedMonth")->findOneBy(
                           array('user' => $user, 'month' => $currentMonth, 'year' => $currentYear)
                        )) != null
                    ) {
                        foreach ($originalTrackedMonth->getTrackedProjects() as $originalTrackedProject) {
                            $trackedProject = new TrackedProject();
                            $trackedProject->setTrackedMonth($trackedMonth);
                            $trackedMonth->addTrackedProject($trackedProject);
                            $trackedProject->setProject($originalTrackedProject->getProject());

                            $em = $this->getDoctrine()->getManager();
                            $em->persist($trackedProject);

                            foreach ($originalTrackedProject->getTrackedTasks() as $originalTrackedTask) {
                                $em = $this->getDoctrine()->getManager();

                                $trackedTask = new TrackedTask();
                                $trackedTask->setTrackedProject($trackedProject);
                                $trackedTask->setEditable($originalTrackedTask->getEditable());
                                $trackedProject->addTrackedTask($trackedTask);
                                $trackedProject->setEditable($originalTrackedTask->getEditable());
                                $trackedTask->setTask($originalTrackedTask->getTask());
                                $trackedTask->generateTrackedDays($daysInMonth, $em);

                                $em->persist($trackedTask);
                            }
                        }
                    }
                }

                $tMId = $trackedMonth->getId();
                $project = $this->getDoctrine()->getRepository("TimetrackerBundle:Project")->findOneBy(array('name'=> 'Project'));
                $pId = $project->getId();

                // Creat default trackedProject if it doesn't exist
                if (($trackedProject = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedProject")->findOneBy(
                    array('project'=> $pId , 'trackedMonth'=> $tMId))) == null) {
                    $trackedProject = new TrackedProject();
                    $trackedProject->setProject($project);
                    $trackedProject->setEditable($project->getEditable());
                    $trackedProject->setTrackedMonth($trackedMonth);
                    $trackedMonth->addTrackedProject($trackedProject);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($trackedProject);
                }

                $tpId = $trackedProject->getId();

                foreach ($defaultTasks as $defaultTask) {
                    $task = $this->getDoctrine()->getRepository('TimetrackerBundle:Task')->findOneBy(array('name'=> $defaultTask));
                    $tId = $task->getId();

                    if (($trackedTask = $this->getDoctrine()->getRepository('TimetrackerBundle:TrackedTask')->findOneBy(
                        array('task' => $tId, 'trackedProject' => $tpId))) == null) {
                        $em = $this->getDoctrine()->getManager();
                        $trackedTask = new TrackedTask();
                        $trackedTask->setTask($task);
                        $trackedTask->setEditable(0);
                        $trackedTask->setTrackedProject($trackedProject);
                        $trackedTask->generateTrackedDays($daysInMonth, $em);
                        $trackedProject->addTrackedTask($trackedTask);

                        $em->persist($trackedTask);
                        $em->flush();
                    }
                }
                $em->flush();
            }
        }
    }


    /**
    * @Route("/check-holiday/{startYear}/{startMonth}/{startDay}/", name="checkHoliday")
    */
    public function checkHoliday($startYear, $startMonth, $startDay)
    {
        $holiday = false;
        if (($temp = $this->getDoctrine()->getRepository("TimetrackerBundle:Holiday")->findOneBy(
            array('year' => $startyear, 'month' => $startMonth, 'day' => $startDay))) != null) {
            $holiday = true;
        }
        return $holiday;
    }

// CODE tests =====================================================================
}
