<?php

namespace Phore\Log;

enum LogTypeEnum: string
{
    case MESSAGE = 'message';
    case STEP = 'step';
    case SUCCESS = 'success';
    case FAILURE = 'failure';
    case SKIP = 'skip';
    case RESULT = 'result';
    case DETAIL = 'detail';
}
