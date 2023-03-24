<?php

namespace App\VideoFiles;

use App\Configuration\Configuration;
use App\Configuration\Property;
use App\Entity\VideoFile;
use App\Entity\VideoFileStatus;
use App\Repository\VideoFileRepository;
use App\Util\Arrays;
use App\Util\Enums;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use FFMpeg\FFProbe;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Synchronizer {
    private readonly Configuration $config;
    private readonly VideoFileRepository $videoRepository;
    private readonly EntityManagerInterface $entityManager;
    private readonly Filesystem $fs;

    public function __construct(
        Configuration $config,
        VideoFileRepository $videoRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->config = $config;
        $this->videoRepository = $videoRepository;
        $this->entityManager = $entityManager;

        $this->fs = new Filesystem();
    }

    public function syncOne(
        VideoFile $video,
        DateTimeImmutable $dateTime = null,
        bool $flush = true
    ): FileSynchronizationResult {
        if ($dateTime === null) {
            $dateTime = new DateTimeImmutable();
        }

        $video->setLastSyncedAt($dateTime);
        if (!$this->fs->exists($video->getPath())) {
            $video->setStatus(VideoFileStatus::Deleted);
            $this->videoRepository->save($video, $flush);

            return FileSynchronizationResult::Removed;
        }

        $file = new SplFileInfo($video->getPath());
        $changed = $video->getMTime()->getTimestamp() !== $file->getMTime();
        if ($changed) {
            $this->update($file, $video);
        }
        $this->videoRepository->save($video, $flush);

        return $changed ? FileSynchronizationResult::Changed : FileSynchronizationResult::Present;
    }

    /**
     * @return array<string,int>
     */
    public function sync(): array {
        $now = new DateTimeImmutable();
        $discoverResult = $this->discoverNewFiles($now);
        $syncResult = $this->syncExistingFiles($now);

        return Arrays::sumAssoc($discoverResult, $syncResult);
    }

    public function discoverNewFiles(DateTimeImmutable $dateTime): array {
        $watchedDirs = $this->config->getArray(Property::WatchedDirs, []);
        if (empty($watchedDirs)) {
            return [];
        }

        $finder = new Finder();
        $finder->files()->in($watchedDirs)->ignoreVCS(true)->filter($this->isFileIncluded(...));
        $count = 0;
        $result = Arrays::mapTo(Enums::enumNames(FileSynchronizationResult::class), fn() => 0);
        foreach ($finder as $file) {
            $status = $this->syncFile($file, $dateTime, (++$count % 50) === 0);
            $result[$status->name] += 1;
        }
        $this->entityManager->flush();

        return $result;
    }

    public function isFileIncluded(SplFileInfo $file): bool {
        return str_starts_with(mime_content_type($file->getRealPath()), 'video/');
    }

    /**
     * @return array<string,int>
     */
    public function syncExistingFiles(DateTimeImmutable $dateTime): array {
        $videosToSync = $this->videoRepository->findByLastSyncedBefore($dateTime);
        $count = 0;
        $result = Arrays::mapTo(Enums::enumNames(FileSynchronizationResult::class), fn() => 0);
        foreach ($videosToSync as $video) {
            $status = $this->syncOne($video, $dateTime, (++$count % 50) === 0);
            $result[$status->name] += 1;
        }
        $this->entityManager->flush();

        return $result;
    }

    private function syncFile(
        SplFileInfo $file,
        DateTimeImmutable $dateTime,
        bool $flush = true
    ): FileSynchronizationResult {
        $video = $this->videoRepository->findOneBy(['path' => $file->getRealPath()]);
        $result = FileSynchronizationResult::Present;
        if ($video === null) {
            $video = new VideoFile();
            $video->setStatus(VideoFileStatus::Discovered);
            $this->update($file, $video);
            $result = FileSynchronizationResult::Discovered;
        }
        if ($video->getMTime()->getTimestamp() !== $file->getMTime()) {
            $this->update($file, $video);
            $video->setStatus(VideoFileStatus::Modified);
            $result = FileSynchronizationResult::Changed;
        }
        $video->setLastSyncedAt($dateTime);
        $this->videoRepository->save($video, $flush);

        return $result;
    }

    private function update(SplFileInfo $file, VideoFile $video): void {
        $probe = FFProbe::create();
        $streams = $probe->streams($file->getRealPath());
        $tags = $probe->format($file->getRealPath())->get('tags', []);
        $title = $tags['title'] ?: $file->getFilenameWithoutExtension();
        $videoCodec = $streams->videos()->first()->get('codec_name');
        $audioCodec = $streams->audios()->first()->get('codec_name');
        $format = $probe->format($file->getRealPath())->get('format_name');

        $video->setPath($file->getRealPath())
            ->setTitle($title)
            ->setSize($file->getSize())
            ->setVideoCodec($videoCodec)
            ->setAudioCodec($audioCodec)
            ->setContainer($format)
            ->setMTime(DateTimeImmutable::createFromFormat('U', $file->getMTime()));
    }
}
