<?php

namespace TimetrackerBundle\Controller;

use Doctrine\ORM\Mapping as ORM;

use TimetrackerBundle\Entity\TrackedMonth;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use TimetrackerBundle\Entity\TrackedProject;
use TimetrackerBundle\Entity\Task;
use TimetrackerBundle\Entity\TrackedTask;
use TimetrackerBundle\Entity\Holiday;
use TimetrackerBundle\Entity\Company;
use TimetrackerBundle\Entity\Project;
use UserBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/build", name="userBuild")
     */
    public function buildAction()
    {
        $user = new User();
        $user->setEmailAddress("kfarr@infinetix.com");
        $password = $this->get('security.password_encoder')->encodePassword($user, "password");
        $user->setPassword($password);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $response = new Response(json_encode(array('built' => false)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    /**
     * @Route("/", name="calendarIndex")
     */
    public function indexAction()
    {
        return $this->render('TimetrackerBundle:Default:calendar.html.twig');
    }


    /**
     * @Route("/calendar/{year}/{month}/", requirements={"year" = "\d+", "month" = "\d+"}, options = { "expose" = true }, name="calendarRoute")
     */
    public function calendarAction($year, $month)
    {
        $request = Request::createFromGlobals();
        $currentMonth = $request->query->get('currentMonth');
        $currentYear = $request->query->get('currentYear');
        $create = $request->query->get('create');
        $createEmpty = $request->query->get('createEmpty');

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
            $response = new Response(json_encode(array('status' => false, 'canCreate'=>true, 'message'=>"You do not have a timesheet for this month yet")));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $companies = [];

        foreach ($trackedMonth->getTrackedProjects() as $trackedProject) {
            $found = false;
            foreach ($companies as $company) {
                if ($company->name === $trackedProject->getProject()->getCompany()->getName()) {
                    $found = true;
                    break;
                }
            }

            if ($found==false) {
                $company = new \stdClass();
                $company->name = $trackedProject->getProject()->getCompany()->getName();
                $company->id = $trackedProject->getProject()->getCompany()->getId();
                $company->projects = [];
                $companies[] = $company;
            }
        }

        $subtotals = [];
        $dayHeader = [];
        $startYear = $calendarMonth->getStartYear();
        $startMonth = $calendarMonth->getStartMonth();
        $startDay = $calendarMonth->getStartDay();


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

        foreach ($trackedMonth->getTrackedProjects() as $trackedProject) {
            foreach ($companies as &$company) {
                if ($company->id == $trackedProject->getProject()->getCompany()->getId()) {
                    $project = new \stdClass();
                    $project->name = $trackedProject->getProject()->getName();
                    $project->trackedId = $trackedProject->getId();
                    $project->id = $trackedProject->getProject()->getId();
                    $project->pEditable = isEditable($trackedProject->getEditable());
                    $project->tasks = [];
                    $company->projects[] = $project;

                    foreach ($trackedProject->getTrackedTasks() as $trackedTask) {
                        $task = new \stdClass();
                        $task->name = $trackedTask->getTask()->getName();
                        $task->id = $trackedTask->getId();
                        $task->pid = $trackedProject->getProject()->getId();
                        $task->tEditable = isEditable($trackedTask->getEditable());
                        if ($trackedTask->getTask()->getName() == 'Holiday') {
                            $task->isHoliday = true;
                        } else {
                            $task->isHoliday = false;
                        }
                        $task->taskSubtotal = 0;
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
                            $day = new \stdClass();
                            if (($temp = $this->getDoctrine()->getRepository("TimetrackerBundle:Holiday")->findOneBy(
                                    array('year' => $startYear, 'month' => $startMonth, 'day' => $startDay))) != null
                                    && $trackedTask->getTask()->getName() == 'Holiday') {
                                $trackedDay->setHours($temp->getHours());
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
        return $response;
    }


    /**
    * @Route("/calendar/create-defaults/{user}/{month}/{year}/{create}/{createEmpty}/{currentMonth}/{currentYear}/{daysInMonth}/", requirements={"user" = "\d+","year" = "\d+", "month" = "\d+"}, name="createDefaults")
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
     * @Route("/calendar/project/{projectId}/tasks", requirements={"projectId" = "\d+"}, options = { "expose" = true }, name="projectTasks")
     */
    public function projectTasksAction($projectId)
    {
        if (($project = $this->getDoctrine()->getRepository("TimetrackerBundle:Project")->find($projectId))==null) {
            $response = new Response(json_encode(array('status' => false, 'message' => "No project found")));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $tasks=[];
        foreach ($project->getTasks() as $taskObj) {
            $task = new \stdClass();
            $task->name = $taskObj->getName();
            $task->id = $taskObj->getId();
            $task->editable = $taskObj->getEditable();
            $tasks[] = $task;
        }

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('status' => true, 'message'=> '', 'tasks'=> $tasks)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }



    /**
     * @Route("/calendar/project/{projectId}/addTask", requirements={"projectId" = "\d+"}, options = { "expose" = true }, name="projectAddTask")
     */
    public function projectAddTaskAction($projectId)
    {
        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'POST') {
            if (($project = $this->getDoctrine()->getRepository("TimetrackerBundle:Project")->find($projectId)) == null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "No project found")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            if (($task = $this->getDoctrine()->getRepository("TimetrackerBundle:Task")->findOneBy(array('project'=> $project->getId(), 'name' => $request->get("name")))) != null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "Task has already been created.")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $task = new Task();
            $task->setProject($project);
            $task->setName($request->get("name"));
            $task->setEditable($request->get("editable"));

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            $task = $this->getDoctrine()->getRepository("TimetrackerBundle:Task")->findOneBy(array('project'=> $project->getId(), 'name' => $request->get("name")));
            $taskId = $task->getId();
            $trackedProjectId = $request->get("trackedProjectId");

            // create a JSON-response with a 200 status code
            $response = new Response(json_encode(array('status' => true, 'message'=> '', 'taskId' => $taskId, 'trackedProjectId' => $trackedProjectId)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }


        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('status' => false, 'message'=> 'Post only')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }



    /**
     * @Route("/calendar/day/{trackedDayId}/updateHours", requirements={"trackedDayId" = "\d+"}, options = { "expose" = true }, name="dayUpdateHours")
     */
    public function updateTrackedDayAction($trackedDayId)
    {
        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'POST') {
            if (($trackedDayId = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedDay")->find($trackedDayId)) == null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "No day found")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $trackedDayId->setHours($request->get("hours"));

            $em = $this->getDoctrine()->getManager();
            $em->persist($trackedDayId);
            $em->flush();

            // create a JSON-response with a 200 status code
            $response = new Response(json_encode(array('status' => true, 'message'=> '')));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('status' => false, 'message'=> 'Post only')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }



    /**
     * @Route("/calendar/trackedProject/{trackedProjectId}/trackTask", requirements={"trackedProjectId" = "\d+"}, options = { "expose" = true }, name="projectTrackTask")
     */
    public function projectTrackTaskAction($trackedProjectId)
    {
        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'POST') {
            if (($trackedProject = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedProject")->find($trackedProjectId)) == null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "No tracked project found")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            if (($temp = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedTask")->findOneBy(array('task' => $request->get("taskId"), 'trackedProject' => $trackedProjectId))) != null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "Task has already been added. Please either create a new task, select a differnt task, or click close.")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            if (($task = $this->getDoctrine()->getRepository("TimetrackerBundle:Task")->find($request->get("taskId"))) == null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "Task not found")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $trackedTask = new TrackedTask();
            $trackedTask->setTask($task);
            $trackedTask->setEditable($task->getEditable());
            $trackedTask->setTrackedProject($trackedProject);
            $trackedProject->addTrackedTask($trackedTask);

            $em = $this->getDoctrine()->getManager();
            $em->persist($trackedTask);
            $em->persist($trackedProject);
            $em->flush();

            // create a JSON-response with a 200 status code
            $response = new Response(json_encode(array('status' => true, 'message'=> '')));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('status' => false, 'message'=> 'Post/Delete only')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }



    /**
     * @Route("/trackedTask/{trackedTaskId}/", requirements={"trackedTaskId" = "\d+"}, options = { "expose" = true }, name="deleteTrackedTask")
     */
    public function deleteTrackedTask($trackedTaskId)
    {
        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'DELETE') {
            if (($trackedTask = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedTask")->find($trackedTaskId))==null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "Task not found")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($trackedTask);
            $em->flush();

            // create a JSON-response with a 200 status code
            $response = new Response(json_encode(array('status' => true, 'message'=> 'Deleted Task')));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('status' => false, 'message'=> 'Post/Delete only')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }



    /**
     * @Route("/calendar/{year}/{month}/trackProject/{projectId}", requirements={"year" = "\d+", "month" = "\d+", "projectId" = "\d+"}, options = { "expose" = true }, name="calendarTrackProject")
     */
    public function calendarTrackProjectAction($year, $month, $projectId)
    {
        $user= $this->get('security.token_storage')->getToken()->getUser();
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            if (($trackedMonth = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedMonth")->findOneBy(
                    array('user' => $user, 'month' => $month, 'year' => $year)
                ))==null) {
                $trackedMonth = new TrackedMonth();
                $trackedMonth->setMonth($month);
                $trackedMonth->setYear($year);
                $trackedMonth->setUser($user);
                $em->persist($trackedMonth);
            }

            if (($temp = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedProject")->findOneBy(array('project'=> $projectId, 'trackedMonth' => $trackedMonth))) != null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "Project has already been added.<br>Please select another project or click close.")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            if (($project = $this->getDoctrine()->getRepository("TimetrackerBundle:Project")->find($projectId))==null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "No project found")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $trackedProject = new TrackedProject();
            $trackedProject->setProject($project);
            $trackedProject->setEditable($project->getEditable());
            $trackedProject->setTrackedMonth($trackedMonth);

            $em->persist($trackedProject);
            $em->flush();

            $pId = $this->getDoctrine()->getRepository('TimetrackerBundle:Project')->findOneBy(array('name' => 'Project'));
            $task1 = $this->getDoctrine()->getRepository('TimetrackerBundle:Task')->findOneBy(array('name'=> 'Holiday'));
            $tId1 = $task1->getId();
            $task2 = $this->getDoctrine()->getRepository('TimetrackerBundle:Task')->findOneBy(array('name'=> 'Sick'));
            $tId2 = $task2->getId();
            $task3 = $this->getDoctrine()->getRepository('TimetrackerBundle:Task')->findOneBy(array('name'=> 'Vacation'));
            $tId3 = $task3->getId();


            if (($trackedTask = $this->getDoctrine()->getRepository('TimetrackerBundle:TrackedTask')->findOneBy(array('task' => $tId1, 'trackedProject' => $pId))) == null) {
                $trackedTask = new TrackedTask();
                $trackedTask->setTask($task1);
                $trackedTask->setEditable(0);
                $trackedTask->setTrackedProject($trackedProject);

                $em = $this->getDoctrine()->getManager();
                $em->persist($trackedTask);
                $em->flush();
            }

            if (($trackedTask = $this->getDoctrine()->getRepository('TimetrackerBundle:TrackedTask')->findOneBy(array('task' =>  $tId2, 'trackedProject' => $pId))) == null) {
                $trackedTask = new TrackedTask();
                $trackedTask->setTask($task2);
                $trackedTask->setEditable(0);
                $trackedTask->setTrackedProject($trackedProject);

                $em = $this->getDoctrine()->getManager();
                $em->persist($trackedTask);
                $em->flush();
            }

            if (($trackedTask = $this->getDoctrine()->getRepository('TimetrackerBundle:TrackedTask')->findOneBy(array('task' => $tId3, 'trackedProject' => $pId))) == null) {
                $trackedTask = new TrackedTask();
                $trackedTask->setTask($task3);
                $trackedTask->setEditable(0);
                $trackedTask->setTrackedProject($trackedProject);

                $em = $this->getDoctrine()->getManager();
                $em->persist($trackedTask);
                $em->flush();
            }

            $response = new Response(json_encode(array('status' => true, 'message' => "Successfully added project")));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if ($request->getMethod() == 'DELETE') {
            if (($trackedMonth = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedMonth")->findOneBy(
                    array('user' => $user, 'month' => $month, 'year' => $year)))==null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "Month not found")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            if (($project = $this->getDoctrine()->getRepository("TimetrackerBundle:TrackedProject")->find($projectId))==null) {
                $response = new Response(json_encode(array('status' => false, 'message' => "No project found")));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($project);
            $em->flush();

            $response = new Response(json_encode(array('status' => true, 'message' => "Successfully added project")));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode(array('status' => false, 'message'=> 'Post/Delete only')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
