[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/maksimru/task-processor/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/maksimru/job-processor/?branch=master)
[![codecov](https://codecov.io/gh/maksimru/task-processor/branch/master/graph/badge.svg)](https://codecov.io/gh/maksimru/task-processor)
[![CircleCI](https://circleci.com/gh/maksimru/task-processor.svg?style=svg)](https://circleci.com/gh/maksimru/task-processor)
[![StyleCI](https://github.styleci.io/repos/190326093/shield?branch=master)](https://github.styleci.io/repos/184490009)

# Simple Job API and processor

Laravel queues were not used intentionally, because it makes that too easy

# Assumptions

- We assume that jobs don't fail and we don't have job failure handling
- We execute job right after we got it
- Job execution is replaced to random sleep

# Large scaling

1) Processor endpoint can be scaled out separately using ingress controller
   In fact it is better to use bundled command "php artisan job-processor:process" without web server 
2) For best performance and quality lock protection we should use redis cache driver

# Installation

Installation can be performed to any laravel application

```bash
composer require maksimru/task-processor
```

# Tests

Module is bundled with automatic tests covering all basic cases

# Endpoints

Module expose following endpoints

```
POST /task          create task

GET /task           get next task and execute

GET /task/{$id}     get task information
```

All of them covered with authentication, authenticating model can be replaced with any other model extending \MaksimM\JobProcessor\Models\User model

