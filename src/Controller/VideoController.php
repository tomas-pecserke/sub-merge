<?php

namespace App\Controller;

use App\Entity\VideoFile;
use App\Repository\VideoFileRepository;
use App\VideoFiles\Synchronizer;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/videos', name: 'video.')]
class VideoController extends AbstractController {
    const DEFAULT_PAGE_SIZE = 25;

    private readonly VideoFileRepository $videoRepository;
    private readonly Synchronizer $synchronizer;

    public function __construct(VideoFileRepository $videoRepository, Synchronizer $synchronizer) {
        $this->videoRepository = $videoRepository;
        $this->synchronizer = $synchronizer;
    }

    #[Route('', name: 'list')]
    public function index(Request $request, PaginatorInterface $paginator): Response {
        $page = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', self::DEFAULT_PAGE_SIZE);
        $sort = $request->query->get('sort', 'v.title');
        $direction = $request->query->get('direction', 'asc');

        if ($page < 1) {
            return $this->redirectToRoute(
                'video.list',
                array_merge($request->query->all(), ['page' => 1])
            );
        }
        if ($limit < 1) {
            return $this->redirectToRoute(
                'video.list',
                array_merge($request->query->all(), ['limit' => self::DEFAULT_PAGE_SIZE])
            );
        }

        $query = $this->videoRepository->findAllQuery($sort, $direction);
        $pagination = $paginator->paginate($query, $page, $limit);
        if ($page !== 1 && ($page - 1) * $limit >= $pagination->getTotalItemCount()) {
            return $this->redirectToRoute(
                'video.list',
                array_merge(
                    $request->query->all(),
                    ['page' => 1 + (int)floor($pagination->getTotalItemCount() / $limit)]
                )
            );
        }

        return $this->render('videos/list.html.twig', ['pagination' => $pagination]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(VideoFile $video): Response {
        return $this->render('videos/detail.html.twig', ['video' => $video]);
    }

    #[Route('/sync', name: 'sync')]
    public function sync(KernelInterface $kernel): Response {
        $process = new Process([
            Path::join('..', 'bin', 'console'),
            'files:sync',
            '--env=' . $kernel->getEnvironment()
        ]);
        $process->start();
        $this->addFlash('info', 'Synchronization started');

        return $this->redirectToRoute('video.list');
    }

    #[Route('/sync/{id}', name: 'sync_one')]
    public function syncOne(VideoFile $video): Response {
        $this->synchronizer->syncOne($video);
        $this->addFlash('success', 'Video file synchronized');
        return $this->redirectToRoute('video.detail', ['id' => $video->getId()]);
    }
}
