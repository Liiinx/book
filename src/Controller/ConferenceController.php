<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
//use App\SpamChecker;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\CommentMessage;

class ConferenceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
    ) {}

    /**
     * @return Response
     */
    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        // variables conferences injecté dans tous les templates avec event subscriber. code en commentaire
//        return $this->render('conference/index.html.twig');

        return $this->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ])->setSharedMaxAge(3600); // met en cache la page pour une heure
    }

    #[Route('/conference_header', name: 'conference_header')]
    public function conferenceHeader(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/header.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ])->setSharedMaxAge(3600);
    }

    /**
     * @param Request $request
     * @param Conference $conference
     * @param CommentRepository $commentRepository
    //     * @param SpamChecker $spamChecker
     * @param string $photoDir
     * @return Response
     * @throws Exception
     */
//    #[Route('/conference/{id}', name: 'conference')]
    #[Route('/conference/{slug}', name: 'conference')]
    public function show(Request $request,
                         Conference $conference,
                         CommentRepository $commentRepository,
//                         SpamChecker $spamChecker,
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
            $this->entityManager->flush();

            // check if comment is a spam with askimet
            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];
//            if (2 === $spamChecker->getSpamScore($comment, $context)) {
//                throw new \RuntimeException('Blatant spam, go away!');
//            }
//            $this->entityManager->flush();

            // message dans le bus. Le gestionnaire décide alors ce qu'il en fait.
            $this->bus->dispatch(new CommentMessage($comment->getId(), $context));

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


    /**
     * test function to send mail
     * @throws TransportExceptionInterface
     */
    #[Route('/email', name: 'test-mail')]
    public function sendEmail(MailerInterface $mailer): response
    {
        $email = (new Email())
//        $email = (new NotificationEmail())
            ->from('test33333333@example.com')
            ->to('te3333333333before@plan-immobilier.fr')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
//        return $this->render('conference/index.html.twig');
        return $this->redirectToRoute('homepage');
    }

    /**
     * test function
     */
    #[Route('/test', name: 'test-something')]
    public function test(CommentRepository $commentRepository): response
    {
        $count = $this->entityManager->getRepository(Comment::class)->countOldRejected();
        dump($count);
        return new Response(<<<EOF
            <html>
                <body>
                    <p class="">Ma page de test !!</p>
                    <img src="/images/under-construction.gif" />
                </body>
            </html>
            EOF
        );
    }
}
