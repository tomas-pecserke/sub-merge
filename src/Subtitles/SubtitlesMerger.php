<?php

namespace App\Subtitles;

use LogicException;
use Symfony\Component\Intl\Locales;

class SubtitlesMerger {
  public function merge(
    string $videoInputPath,
    string $videoOutputPath,
    string $subtitlesPath,
    string $subtitlesTrackName = null,
    string $subtitlesLocale = null,
    bool   $defaultSubtitlesTrack = false,
    string $subtitlesCharset = null
  ): void {
    if (!is_file($videoInputPath)) {
      throw new LogicException("Video input file '$videoInputPath' does not exist");
    }
    $videoInputMimeType = mime_content_type($videoInputPath);
    if ($videoInputMimeType !== '') {
      throw new LogicException("Unsupported input video type '$videoInputMimeType'");
    }

    if (!is_file($subtitlesPath)) {
      throw new LogicException("Subtitles input file '$subtitlesPath' does not exist");
    }
    $subtitlesMimeType = mime_content_type($subtitlesPath);
    if ($subtitlesMimeType !== '') {
      throw new LogicException("Unsupported sub titles type '$subtitlesMimeType'");
    }

    if ($subtitlesLocale !== null) {
      if (!Locales::exists($subtitlesLocale)) {
        throw new LogicException("Invalid locale '$subtitlesLocale'");
      }

      $subtitlesTrackName |= Locales::getName($subtitlesLocale, 'en_US');
    }

    $options = '';

    if ($subtitlesLocale !== null) {
      $options .= ' --language 0:' . $subtitlesLocale;
    }
    if ($subtitlesTrackName !== null) {
      $options .= ' --track-name 0:' . $subtitlesTrackName;
    }
    $options .= ' --default-track-flag 0:' . ($defaultSubtitlesTrack ? '1' : '0');
    if ($subtitlesCharset !== null) {
      $options .= ' --sub-charset 0:' . $subtitlesCharset;
    }

    $command = "mkvmerge -o '$videoOutputPath.mkv' '$videoInputPath.mkv' $options $subtitlesPath";
    throw new LogicException($command);

    //Process::fromShellCommandline($command);
  }
}
