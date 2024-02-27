<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\SpamChecker;

class ConferenceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @param ConferenceRepository $conferenceRepository
     * @return Response
     */
    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
//        return $this->render('conference/index.html.twig', [
//            'conferences' => $conferenceRepository->findAll(),
//        ]);
        // variables conferences injecter dans tous les templates avec event subscriber
        return $this->render('conference/index.html.twig');
    }

    /**
     * @param Request $request
     * @param Conference $conference
     * @param CommentRepository $commentRepository
     * @param SpamChecker $spamChecker
     * @param string $photoDir
     * @return Response
     * @throws Exception
     */
//    #[Route('/conference/{id}', name: 'conference')]
    #[Route('/conference/{slug}', name: 'conference')]
    public function show(Request $request,
                         Conference $conference,
                         CommentRepository $commentRepository,
                         SpamChecker $spamChecker,
                         #[Autowire('%photo_dir%')] string $photoDir): Response
    {

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                $photo->move($photoDir, $filename);
                $comment->setPhotoFilename($filename);
            }
            $this->entityManager->persist($comment);

            // check if comment is a spam with askimet
            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];
            if (2 === $spamChecker->getSpamScore($comment, $context)) {
                throw new \RuntimeException('Blatant spam, go away!');
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('conference', [
                'slug' => $conference->getSlug()
            ]);
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
//            'comments' => $commentRepository->findBy(['conference' => $conference], ['createdAt' => 'DESC']),
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form,
        ]);
    }
}
