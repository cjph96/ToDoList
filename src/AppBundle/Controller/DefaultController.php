<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Entity\TODO;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;
use AppBundle\Service\Notification;

class DefaultController extends Controller
{

    /**
     * @Route("/", methods={"POST"})
     */
    public function add(Request $request, LoggerInterface $logger)
    {
        if( $request->request ){
            $req = $request->request;
            $name = $req->get('name');

            if($name){
                $entityManager = $this->getDoctrine()->getManager();
                $todo = new TODO($name);

                //Añadir tarea al usuario que la crea
                $id_user = $this->getUser()->getId();
                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($id_user);
                
                $todo->setUser($user);

                $entityManager->persist($todo);
                $entityManager->flush();

                if( $request->isXmlHttpRequest() ) {
                    $logger->info("El usuario ". $user->getUsername(). " ha creado la tarea: ". $todo->getName(). "(ID:". $todo->getId() .")" );
                    return $this->json('TODO creado con exito');
                }

                return $this->redirectToRoute('homepage');
            }
        }

        $logger->error('Error al intentar crear una tarea TODO');
        return $this->json('Error');
    }

    /**
     * @Route("/users/add", methods={"POST"})
     */
    public function add_user(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if( $request->request ){
            $req = $request->request;
            $name = $req->get('name') ?? false;
            $pass = $req->get('password') ?? 1234;

            if($name){
                $entityManager = $this->getDoctrine()->getManager();
                $user = new User();
                $user->setUsername($name);
                $user->setPassword($pass);

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->json('Usuario creado con exito');

            }
        }

        return $this->json('Error',404);

    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $id_user = $this->getUser()->getId();
        
        $todoList = $this->getDoctrine()
            ->getRepository(TODO::class)
            ->getByUserID($id_user);

        return $this->render('todo/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'todoList' => $todoList,
        ]);
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function admin(Request $request)
    {
        $todoList = $this->getDoctrine()
            ->getRepository(TODO::class)
            ->findAll();

        return $this->render('todo/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'todoList' => $todoList,
        ]);
    }

    /**
     * @Route("/complete/{id_todo}")
     */
    public function complete(Request $request, $id_todo, LoggerInterface $logger, Notification $notification)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $todo = $this->getDoctrine()
            ->getRepository(TODO::class)
            ->find($id_todo);
        
        $id_user = $this->getUser()->getId();
        $id_user_owner = $todo->getUser()->getId();
        if( $id_user != $id_user_owner ){
            return $this->json('Solo el propietario puede cambiar el estado',403);
        }

        $todo->setStatus(1);
        $entityManager->persist($todo);
        $entityManager->flush();

        $text = "El usuario ". $todo->getUser()->getUsername(). " ha completado la tarea: ". $todo->getName(). "(ID:". $todo->getId() .")";
        $logger->info($text);

        $notification->send_email($text,'tarea completada');

        if( $request->isXmlHttpRequest() ) {
            return $this->json('TODO completado');
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/TODO/{id_todo}/{id_user}")
     */
    public function update_TODO_user(Request $request, $id_todo, $id_user, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $todo = $this->getDoctrine()
            ->getRepository(TODO::class)
            ->find($id_todo);

        $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($id_user)
                ?? false;
        if($user === false){
            $logger->error('Error al intentar cambiar el propietario de una tarea');
            return $this->json('No existe ningún usuario con ese id',404);
        }
            

        $todo->setUser($user);

        $entityManager->persist($todo);
        $entityManager->flush();
        $logger->info("La tarea: ". $todo->getName(). "(ID:". $todo->getId() .")"." ha cambiado de propietario" );
        return $this->json('Cambio de usuario completado');

    }

     /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {

    }
}
