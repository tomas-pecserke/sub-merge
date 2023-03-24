<?php

namespace App\Entity;

enum VideoFileStatus: string {
    case Unknown = 'Unknown';
    case Discovered = 'Discovered';
    case Modified = 'Modified';
    case Deleted = 'Deleted';
}
