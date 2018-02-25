<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use UserBundle\Entity\Role;
use UserBundle\Entity\User;

class UserController extends Controller
{
    /**
     * @Route("/user", name="userIndex")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function userHtmlAction()
    {
        return $this->render('UserBundle:Default:users.html.twig');
    }

    /**
     * @Route("/user/roles", name="roleIndex")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function rolesHtmlAction()
    {
        return $this->render('UserBundle:Default:roles.html.twig');
    }

    /**
     * @Route("/user/users.json", name="users.json")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function userAction(Request $request)
    {
        if ($request->getMethod() == 'GET') {
            if (($roles = $this->getDoctrine()->getRepository("UserBundle:Role")->findAll()) == null) {
                $roles = [];
            }

            $retRoles = [];
            foreach ($roles as $role) {
                $retRole = new \stdClass();
                $retRole->id=$role->getId();
                $retRole->name = $role->getName();
                $retRoles[]=$retRole;
            }


            if (($users = $this->getDoctrine()->getRepository("UserBundle:User")->findAll()) == null) {
                $users = [];
            }

            $retUsers = [];
            foreach ($users as $user) {
                $retUser = new \stdClass();
                $retUser->id=$user->getId();
                $retUser->emailAddress = $user->getEmailAddress();
                $retUser->firstName = $user->getFirstName();
                $retUser->lastName = $user->getLastName();
                $retUser->roles = $user->getRoles();
                $retUsers[]=$retUser;
            }

            $response = new Response(json_encode(array('status' => true, 'error' => null, 'users' => $retUsers, 'roles' =>$retRoles)));
        } elseif ($request->getMethod() == "POST") {
            if (($userId = $request->get("id", -1))==-1) {
                $user = new User();
                $user->setPassword("");
                $user->setIsActive(true);
            } elseif (($user = $this->getDoctrine()->getRepository("UserBundle:User")->find($userId)) == null) {
                $user = new User();
                $user->setPassword("");
                $user->setIsActive(true);
            }

            if (($password = $request->get("password", ""))!="") {
                $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));
            }


            if ($request->get("emailAddress", null)!=null) {
                $user->setEmailAddress($request->get("emailAddress"));
            }
            if ($request->get("firstName", null)!=null) {
                $user->setFirstName($request->get("firstName"));
            }
            if ($request->get("lastName", null)!=null) {
                $user->setLastName($request->get("lastName"));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $response = new Response(json_encode(array('status' => true, 'error' => null)));
        } elseif ($request->getMethod() == "DELETE") {
            if (($user = $this->getDoctrine()->getRepository("UserBundle:User")->find($request->get("id"))) == null) {
                $response = new Response(json_encode(array('status' => false, 'error' => 'User not found')));
            } else {
                $em = $this->getDoctrine()->getManager();
                $em->remove($user);
                $em->flush();
                $response = new Response(json_encode(array('status' => true, 'error' => null)));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/user/roles.json", name="roles.json")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function rolesAction(Request $request)
    {
        if ($request->getMethod() == 'GET') {
            if (($roles = $this->getDoctrine()->getRepository("UserBundle:Role")->findAll()) == null) {
                $roles = [];
            }

            $retRoles = [];
            foreach ($roles as $role) {
                $retRole = new \stdClass();
                $retRole->id=$role->getId();
                $retRole->name = $role->getName();
                $retRoles[]=$retRole;
            }

            $response = new Response(json_encode(array('status' => true, 'error' => null, 'roles' => $retRoles)));
        } elseif ($request->getMethod() == "POST") {
            $role = new Role();
            $role->setName($request->get("name"));

            $em = $this->getDoctrine()->getManager();
            $em->persist($role);
            $em->flush();

            $response = new Response(json_encode(array('status' => true, 'error' => null)));
        } elseif ($request->getMethod() == "DELETE") {
            if (($role = $this->getDoctrine()->getRepository("UserBundle:Role")->find($request->get("id"))) == null) {
                $response = new Response(json_encode(array('status' => false, 'error' => 'Role not found')));
            } else {
                $em = $this->getDoctrine()->getManager();
                $em->remove($role);
                $em->flush();
                $response = new Response(json_encode(array('status' => true, 'error' => null)));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    /**
     * @Route("/user/userRoles.json", name="userRoles.json")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function userRolesAction(Request $request)
    {
        if (($user = $this->getDoctrine()->getRepository("UserBundle:User")->find($request->get("userId"))) == null) {
            $response = new Response(json_encode(array('status' => false, 'error' => 'User not found')));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }


        if ($request->getMethod() == "POST") {
            if (($role = $this->getDoctrine()->getRepository("UserBundle:Role")->findByName($request->get("roleName"))[0]) == null) {
                $response = new Response(json_encode(array('status' => false, 'error' => 'Role not found')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $user->addUserRole($role);
            $role->addUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($role);
            $em->persist($user);
            $em->flush();

            $response = new Response(json_encode(array('status' => true, 'error' => null)));
        } elseif ($request->getMethod() == "DELETE") {
            $roleName = $request->get("roleName");

            foreach ($user->getUserRoles() as $role) {
                if ($role->getName() == $roleName) {
                    $user->removeUserRole($role);
                    $role->removeUser($user);
                    break;
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $response = new Response(json_encode(array('status' => true, 'error' => null)));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
