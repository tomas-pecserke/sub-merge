<?php

namespace App\Controller;

use App\Configuration\Configuration;
use App\Configuration\Property;
use Cron\CronBundle\Entity\CronJob;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/settings', name: 'settings.')]
class SettingsController extends AbstractController {
    private const INTERVALS = [
        ['title' => 'Every 15 minutes', 'value' => '15m', 'schedule' => '*/15 * * * *'],
        ['title' => 'Hourly', 'value' => '1h', 'schedule' => '0 * * * *'],
        ['title' => 'Daily', 'value' => '1d', 'schedule' => '0 0 * * *'],
    ];

    private readonly Configuration $configuration;

    public function __construct(Configuration $configuration) {
        $this->configuration = $configuration;
    }

    #[Route('', name: 'index')]
    public function index(): Response {
        return $this->render('settings/index.html.twig');
    }

    #[Route('/setup', name: 'setup')]
    public function setup(): Response {
        return $this->render('settings/setup.html.twig', [
            'ready' => $this->isConfigValid()
        ]);
    }

    #[Route('/setup/finish', name: 'setup.finish')]
    public function finishSetup(): Response {
        if (!$this->isConfigValid()) {
            $this->addFlash('error', 'Configuration is not valid');
            return $this->redirectToRoute('settings.setup');
        }

        $this->configuration->setBoolean(Property::Initialized, true);
        return $this->redirectToRoute('dashboard.index');
    }

    public function watchedDirectories(): Response {
        $watchedDirs = $this->configuration->getArray(Property::WatchedDirs, []);
        $watchedDirs = array_map(
            fn($path) => [
                'path' => $path,
                'free_space' => disk_free_space($path),
                'total_space' => disk_total_space($path),
            ],
            $watchedDirs
        );

        return $this->render('settings/watched_directory/list.html.twig', ['watchedDirs' => $watchedDirs]);
    }

    public function synchronization(): Response {
        $syncInterval = $this->configuration->getString(Property::SynchronizationInterval);

        return $this->render('settings/synchronization/index.html.twig', [
            'syncInterval' => $syncInterval,
            'intervals' => self::INTERVALS,
        ]);
    }

    #[Route('/watched-directory/add', name: 'watched_directory.add')]
    public function addWatchedDirectory(Request $request): Response {
        $initialized = $this->configuration->getBoolean(Property::Initialized);
        $path = $request->get('path', Path::getRoot(getcwd()));

        $confirm = $request->getMethod() === 'POST';
        if ($confirm) {
            $watchedDirs = $this->configuration->getArray(Property::WatchedDirs, []);
            if (empty(array_filter($watchedDirs, fn($dir) => str_starts_with($path, $dir)))) {
                $watchedDirs [] = $path;
                $this->configuration->setArray(Property::WatchedDirs, $watchedDirs);
                $this->addFlash('success', 'Directory added to watched list');

                return $this->redirectToRoute($initialized ? 'settings.index' : 'settings.setup');
            }

            $this->addFlash('danger', 'Directory already watched');
        }

        $finder = new Finder();
        $dirs = iterator_to_array($finder->directories()->depth(0)->in($path)->sortByName());

        return $this->render('settings/watched_directory/add.html.twig', [
            'initialized' => $initialized,
            'parentPath' => Path::normalize(Path::join($path, '..')),
            'path' => $path,
            'dirs' => $dirs,
        ]);
    }

    #[Route('/watched-directory/remove', name: 'watched_directory.remove')]
    public function removeWatchedDirectory(Request $request): Response {
        $path = $request->get('path', Path::getRoot(getcwd()));
        $watchedDirs = $this->configuration->getArray(Property::WatchedDirs, []);
        $newWatchedDirs = array_filter($watchedDirs, fn($dir) => $dir !== $path);
        if (count($watchedDirs) === count($newWatchedDirs)) {
            $this->addFlash('danger', 'Directory not watched');
        } else {
            $this->configuration->setArray(Property::WatchedDirs, $newWatchedDirs);
            $this->addFlash('success', 'Directory removed from watched list');
        }

        $initialized = $this->configuration->getBoolean(Property::Initialized);
        return $this->redirectToRoute($initialized ? 'settings.index' : 'settings.setup');
    }

    #[Route('/synchronization/set-interval/{value}', name: 'synchronization.set-interval')]
    public function synchronizationSetInterval(string $value): Response {
        $initialized = $this->configuration->getBoolean(Property::Initialized);
        $intervals = array_filter(self::INTERVALS, fn($i) => $i['value'] === $value);
        if (empty($intervals)) {
            $this->addFlash('danger', 'Unsupported synchronization interval');

            return $this->redirectToRoute($initialized ? 'settings.index' : 'settings.setup');
        }

        $interval = $intervals[0];
        $this->configuration->setString(Property::SynchronizationInterval, $value);

        if ($initialized) {
            $job = new CronJob();
            $job->setName('files:sync');
            $job->setCommand($command);
            $job->setSchedule($interval['schedule']);
            $job->setDescription('Synchronize monitored video files');
            $job->setEnabled(true);
            $this->container->get('cron.manager')->saveJob($job);
        }

        return $this->redirectToRoute($initialized ? 'settings.index' : 'settings.setup');
    }

    private function isConfigValid(): bool {
        $watchedDirs = $this->configuration->getArray(Property::WatchedDirs);
        $syncInterval = $this->configuration->getString(Property::SynchronizationInterval);
        if (empty($watchedDirs) || empty($syncInterval)) {
            return false;
        }

        return true;
    }
}
