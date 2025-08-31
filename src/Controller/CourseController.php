<?php
namespace App\Controller;

use App\Entity\Course;
use App\Entity\Grade;
use App\Form\CourseType;
use App\Form\GradeType;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CourseController extends AbstractController
{
    // Liste des matières (cours) avec tri alphabétique
    #[Route('/cours', name: 'app_courses', methods: ['GET'])]
    public function index(CourseRepository $courseRepo): Response
    {
        $user = $this->getUser();
        return $this->render('courses/index.html.twig', [
            'courses' => $courseRepo->findBy(['user' => $user], ['name' => 'ASC']),
        ]);
    }

    // Création d’une matière
    #[Route('/cours/nouveau', name: 'app_course_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $course->setUser($this->getUser());
            $em->persist($course);
            $em->flush();
            return $this->redirectToRoute('app_courses');
        }

        return $this->render('courses/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Page d’une matière avec ses notes
    #[Route('/cours/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        if ($course->getUser() !== $this->getUser()) { throw $this->createNotFoundException(); }
        return $this->render('courses/show.html.twig', [
            'course' => $course,
            'average' => $course->getAverageScore(),
            'grades' => $course->getGrades(),
        ]);
    }

    // Ajout d’une note à une matière
    #[Route('/cours/{id}/notes/nouveau', name: 'app_course_grade_new', methods: ['GET','POST'])]
    public function addGrade(Course $course, Request $request, EntityManagerInterface $em): Response
    {
        if ($course->getUser() !== $this->getUser()) { throw $this->createNotFoundException(); }
        $grade = new Grade();
        $grade->setCourse($course);
        $form = $this->createForm(GradeType::class, $grade)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($grade);
            $em->flush();
            return $this->redirectToRoute('app_course_show', ['id' => $course->getId()]);
        }

        return $this->render('courses/grade_new.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    // Suppression d’une matière
    #[Route('/cours/{id}', name: 'app_course_delete', methods: ['POST'])]
    public function deleteCourse(Request $request, Course $course, EntityManagerInterface $em): Response
    {
        if ($course->getUser() !== $this->getUser()) { throw $this->createNotFoundException(); }
        if (!$this->isCsrfTokenValid('delete_course_'.$course->getId(), (string)$request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $em->remove($course);
        $em->flush();
        return $this->redirectToRoute('app_courses');
    }

    #[Route('/cours/{id}/notes/{grade}/supprimer', name: 'app_course_grade_delete', methods: ['POST'])]
    public function deleteGrade(
        Request $request,
        Course $course,
        Grade $grade,
        EntityManagerInterface $em
    ): Response {
        if ($course->getUser() !== $this->getUser()) { throw $this->createNotFoundException(); }
        if (!$this->isCsrfTokenValid('delete_grade_'.$grade->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        // Empêche la suppression d'une note qui n'appartient pas à ce cours
        if ($grade->getCourse() !== $course) {
            throw $this->createNotFoundException();
        }

        $em->remove($grade);
        $em->flush();

        return $this->redirectToRoute('app_course_show', ['id' => $course->getId()]);
    }
}
