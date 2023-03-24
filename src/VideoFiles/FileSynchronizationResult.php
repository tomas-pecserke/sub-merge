<?php

namespace App\VideoFiles;

enum FileSynchronizationResult {
    case Discovered;
    case Present;
    case Changed;
    case Removed;
}
