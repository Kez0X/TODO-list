<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class TaskController extends AbstractController
{
    #[Route('/task', name: 'app_task')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            // L'utilisateur n'est pas connecté, rediriger vers la page de login
            return $this->redirectToRoute('app_login');
        }

        // Trouver toutes les tâches associées à l'utilisateur connecté
        $tasks = $entityManager->getRepository(Task::class)->findBy(['user' => $user]);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }


    #[Route('/task/new', name: 'app_task_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreatedAt(new \DateTimeImmutable());
            $task->setIsDone(false);
            $task->setUser($this->getUser());  // Associer la tâche à l'utilisateur connecté
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_task');
        }

        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/task/edit/{id}', name: 'app_task_edit')]
    public function edit(Task $task, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($task->getUser() !== $this->getUser()) {
            // L'utilisateur ne peut pas éditer une tâche qui ne lui appartient pas
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_task');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/task/delete/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($task->getUser() !== $this->getUser()) {
            // L'utilisateur ne peut pas supprimer une tâche qui ne lui appartient pas
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('app_task');
    }

    #[Route('/task/check/{id}', name: 'app_task_check', methods: ['POST'])]
    public function check(Task $task, EntityManagerInterface $entityManager): Response
    {
        // Vérifie si l'utilisateur est bien celui qui possède la tâche
        if ($task->getUser() !== $this->getUser()) {
            // L'utilisateur ne peut pas modifier une tâche qui ne lui appartient pas
            throw $this->createAccessDeniedException();
        }

        // Inverse l'état de la tâche (si elle est terminée, la marquer comme non terminée, et inversement)
        $task->setIsDone(!$task->isIsDone());

        // Sauvegarde les modifications dans la base de données
        $entityManager->persist($task);
        $entityManager->flush();

        // Redirige vers la liste des tâches
        return $this->redirectToRoute('app_task');
    }

}
